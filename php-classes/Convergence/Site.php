<?php

namespace Convergence;

class Site extends \ActiveRecord
{
    public static $tableName = 'convergence_sites';
    public static $singularNoun = 'site';
    public static $pluralNoun = 'sites';
    public static $collectionRoute = 'sites';

	public static $fields = [
        'Label',
		'Handle' => [
			'unique' => true
		],
    	'InheritanceKey' => [
			'notnull' => false
		],
        'LocalCursor' => 'uint',
        'ParentCursor' => 'uint',
        'Updating' => [
            'type' => 'boolean',
            'default' => false
        ],
        'DeploymentID' => 'uint',
    	'HostID' => 'uint',
		'PrimaryHostnameID' => 'uint',
        'ParentSiteID' => 'uint'
	];

	public static $indexes = [
		'HostHandle' => [
			'fields' => ['HostID', 'Handle'],
			'unique' => true
		]
	];

	public static $validators = [
        'Handle' => [
            'validator' => 'handle',
            'required' => true
        ],
		'Deployment' => 'require-relationship',
    	'Host' => 'require-relationship',
        'PrimaryHostname' => 'require-relationship',
	];

	public static $relationships = [
        'Deployment' => [
            'type' => 'one-one',
            'class' => Deployment::class
        ],
		'Host' => [
			'type' => 'one-one',
			'class' => Host::class
		],
		'PrimaryHostname' => [
			'type' => 'one-one',
			'class' => Hostname::class
		],
        'SecondaryHostnames' => [
            'type' => 'one-many',
            'class' => Hostname::class,
           // @todo 'conditions' => 
        ],
        'ParentSite' => [
            'type' => 'one-one',
            'class' => Site::class
        ]
	];

    public static $searchConditions = [
        'Handle' => array(
            'qualifiers' => ['any', 'handle'],
            'points' => 1,
            'sql' => 'Handle LIKE "%%%1$s%%"'
        )
    ];

    public function validate($deep = true)
    {
        parent::validate();

        // Check handle uniqueness
        if ($this->isDirty && !$this->_validator->hasErrors('Handle') && $this->Handle) {

            $ExistingSite = Site::getByField('Handle', $this->Handle);

            if ($ExistingSite && ($ExistingSite->ID != $this->ID)) {
                $this->_validator->addError('Handle', 'A site with this handle already exists');
            }
        }

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if ($this->isFieldDirty('PrimaryHostnameID') && $this->PrimaryHostnameID) {
            $this->PrimaryHostname->SiteID = $this->ID;
            $this->PrimaryHostname->save(false);
        }
    }
    
    public function executeRequest($request, $method = 'POST')
    {
        return $this->Host->executeRequest('/sites/' . $this->Handle . '/php-shell', $method, $request);
    }

    public function getFileSystemSummary()
    {
        $key = $this->InheritanceKey;
        $hostname = $this->PrimaryHostname->Hostname;
        $cursor = $this->ParentCursor;
        $code = "Emergence\SiteAdmin\SiteUpdater::handleFileSystemSummary('$hostname', '$key', $cursor);";
        $response = $this->Host->executeRequest('/sites/' . $this->Handle . '/php-shell', 'POST', $code);
        $response = json_decode($response, true);
        return $response;
    }

    public function updateFileSystem()
    {
        $hostname = $this->PrimaryHostname->Hostname;
        $key = $this->InheritanceKey;
        $cursor = intval($this->ParentCursor);

        // Fire update site request
        $code = "Emergence\SiteAdmin\SiteUpdater::handleUpdateSite('$hostname', '$key', $cursor);";
        $response = $this->executeRequest($code);
        $response = json_decode($response, true);

        // Update Site with cursors and updating status
        if ($response['parentCursor'] !== 0) {
            $this->ParentCursor = $response['parentCursor'];
        }
        $this->LocalCursor = $response['localCursor'];
        $this->Updating = false;
        $this->save();

        // Clear vfs cache to ensure all file are available
        $this->clearVFSCache();

        return $response;
    }

    public function updateLocalCursor()
    {
        $code = "Emergence\SiteAdmin\SiteUpdater::handleGetLocalCursor();";
        $response = $this->executeRequest($code);
        $response = json_decode($response, true);
        $this->LocalCursor = $response['localCursor'];
        $this->save();
    }

    // @todo move into emergence kernel powered request
    public function clearVFSCache()
    {
        if (\Ontray\Store::$enableSSL) {
            $url = 'https://';
        } else {
            $url = 'http://';
        }

        $url .= $this->PrimaryHostname->Hostname . '/clear-vfs-cache';

        // @todo add a validation to this POST
        // $fields = 'StoreID=' . $Store->ID;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public static function getChildren($parentIDs = [])
    {
        try {
            $children = \Convergence\Site::getAllByQuery('
                SELECT * FROM %s
                WHERE ParentSiteID IN ("%s")
            ', [
                Site::$tableName,
                join($parentIDs, '","')
            ]);
        } catch (\TableNotFoundException $e) {
            $children = [];
        }

        return $children;
    }

    public static function getUpdateQueue($parentIDs = [])
    {
        try {
            $children = \Convergence\Site::getAllByQuery('
                SELECT * FROM %s
                WHERE Updating = 1
                AND ParentSiteID IN ("%s")
            ', [
                Site::$tableName,
                join($parentIDs, '","')
            ]);
        } catch (\TableNotFoundException $e) {
            $children = [];
        }

        return $children;
    }

    /*
     *  Returns percentage of sites currently updateing
     *
     *  @return float
     */
    public static function getUpdateProgress()
    {
        try {
            $progress = \DB::oneValue('
                SELECT ((Select COUNT(*) from `%1$s` where Updating = 0) / count(*)) as Updating
                FROM `%1$s`
            ', [
                Site::$tableName
            ]);
        } catch (\TableNotFoundException $e) {
            $progress = 1;
        }

        return $progress * 100;
    }

    public function addUsers($data)
    {
        $userCode = '$userClass = User::getStaticDefaultClass();';
        $userCode .= 'foreach ('.var_export($data, true).' AS $userData): ';
        $userCode .= '$user = $userClass::create($userData, true);';
        $userCode .= '$user->setClearPassword($userData["Password"]);';
        $userCode .= '$user->save();';
        $userCode .= 'endforeach;';

        return $this->Host->executeRequest('/sites/'.$this->Handle.'/php-shell', 'POST', $userCode);
    }
}