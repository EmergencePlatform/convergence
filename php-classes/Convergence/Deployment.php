<?php

namespace Convergence;

class Deployment extends \ActiveRecord
{
    public static $tableName = 'convergence_deployment';
    public static $singularNoun = 'deployment';
    public static $pluralNoun = 'deployments';
    public static $collectionRoute = 'deployments';

    public static $defaultBaseHostname;
    public static $defaultParentHostname = 'skeleton-v2.emr.ge';
    public static $defaultParentInheritanceKey = 'lKhjNhwXoM8rLbXw';
    public static $blacklistedHostnames = [];
    public static $sslPaths = [
        // 'example.com' => '/emergence/sites/example/ssl/example.com',
        // '*.example.com' => '/emergence/sites/example/ssl/example.com',
    ];
    public static $onBeforeStagingDeployment;
    public static $onBeforeProductionDeployment;

    public static $fields = [
        'Label' => [
            'required' => true
        ],
        'Status' => [
            'type' => 'enum',
            'values'=> ['draft', 'pending', 'provisioning', 'available', 'suspended', 'interrupted'],
            'default' => 'draft'
        ],
        'PrimaryHostname',
        'ParentSiteID' => [
            'type' => 'uint',
            'default' => 0
        ],
        'HostID' => 'uint'
    ];

    public static $relationships = [
        'ParentSite' => [
            'type' => 'one-one',
            'class' => Site::class
        ],
        'Sites' => [
            'type' => 'one-many',
            'class' => Site::class
        ],
        'Admins' => [
            'type' => 'one-many',
            'class' => DeploymentAdmin::class
        ],
        'Host' => [
            'type' => 'one-one',
            'class' => Host::class
        ]
    ];

	public static $validators = [
        'PrimaryHostname' => [
            'validator' => 'FQDN',
            'required' => false
        ]
	];

    /*
     * Create staging and production site for deployment
     *
     * @return array
     */
    public function deploy()
    {
        if ($this->Status !== 'pending') {
            return false;
        }

        // Update store status
        $this->Status = 'provisioning';
        $this->save();

        // Determine base hostname
        if (static::$defaultBaseHostname) {
            $baseHostname = static::$defaultBaseHostname;
        } else {
            $baseHostname = $_SERVER['HTTP_HOST'];
        }

        // Generate unique handle
        $len = 14;
        $hostname = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $this->Label)));
        $handle = \HandleBehavior::getUniqueHandle(\Convergence\Site::class, substr($hostname, 0, $len));
        while ($handle > 14) {
            $len--;
            $handle = \HandleBehavior::getUniqueHandle(\Convergence\Site::class, substr($hostname, 0, $len));
        }

        // Set up staging configs
        $stagingConfig = [
            'handle' => $handle . '-s',
            'hostnames' => [],
            'inheritance_key' => '',
            'label' => $this->Label . ' (Staging)'
        ];

        // Use predefined hostname
        if ($this->PrimaryHostname) {
            $primaryHostname = $this->PrimaryHostname;

        // Create unique hostname from label
        } else {
            $labelSanitized = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $this->Label)));
            $primaryHostname = $labelSanitized . '.' . $baseHostname;
            $ExistingHostname = Hostname::getByField('Hostname', $primaryHostname);
            $cnt = 1;
            while ($ExistingHostname) {
                $primaryHostname = $labelSanitized . $cnt . '.' . $baseHostname;
                $ExistingHostname = Hostname::getByField('Hostname', $primaryHostname);
                $cnt++;
            }
        }

        $stagingConfig['primary_hostname'] = 'staging.' . $primaryHostname;

        // Set the parent site configs
        if ($this->ParentSite) {
            $stagingConfig['parent_hostname'] = $this->ParentSite->PrimaryHostname->Hostname;
            $stagingConfig['parent_key'] = $this->ParentSite->InheritanceKey;
        } else {
            $stagingConfig['parent_hostname'] = static::$defaultParentHostname;
            $stagingConfig['parent_key'] = static::$defaultParentInheritanceKey;
        }

        // Check if ssl cert is avaiable for hostname
        $stagingCertPath = static::getAvailableSSLCert($stagingConfig['primary_hostname']);
        if ($stagingCertPath) {
            $stagingConfig['ssl'] = [
                'certificate' => $stagingCertPath . '.crt',
                'certificate_key' => $stagingCertPath . '.key'
            ];
        }

        // On before staging deploy
        if (is_callable(static::$onBeforeStagingDeployment)) {
            $stagingConfig = call_user_func(static::$onBeforeStagingDeployment, $this, $stagingConfig);
        }

        // Create staging site in kernel
        $stagingResponse = $this->Host->createSite($stagingConfig);

        // Create matching convergence site
        $StagingSite = Site::create([
            'Label' => $stagingResponse['data']['label'],
            'Handle' => $stagingResponse['data']['handle'],
            'InheritanceKey' => $stagingResponse['data']['inheritance_key'],
            'PrimaryHostname' => Hostname::create(['Hostname' => $stagingResponse['data']['primary_hostname']]),
            'DeploymentID' => $this->ID,
            'HostID' => $this->HostID,
            'ParentSiteID' => $this->ParentSiteID
        ], true);

        // Create production site configs
        $prodConfig = [
            'handle' => $handle,
            'hostnames' => [],
            'inheritance_key' => '',
            'label' => $this->Label,
            'parent_hostname' => $StagingSite->PrimaryHostname->Hostname,
            'parent_key' => $StagingSite->InheritanceKey,
            'primary_hostname' => $primaryHostname
        ];

        // Check if ssl cert is avaiable for hostname
        $prodCertPath = static::getAvailableSSLCert($primaryHostname);
        if ($prodCertPath) {
            $prodConfig['ssl'] = [
                'certificate' => $prodCertPath . '.crt',
                'certificate_key' => $prodCertPath . '.key'
            ];
        }

        // On before production deploy
        if (is_callable(static::$onBeforeProductionDeployment)) {
            $prodConfig = call_user_func(static::$onBeforeProductionDeployment, $this, $prodConfig);
        }

        // Create production site
        $prodResponse = $this->Host->createSite($prodConfig);

        // Create matching convergence site
        $ProdSite = Site::create([
            'Label' => $prodResponse['data']['label'],
            'Handle' => $prodResponse['data']['handle'],
            'InheritanceKey' => $prodResponse['data']['inheritance_key'],
            'PrimaryHostname' => Hostname::create(['Hostname' => $prodResponse['data']['primary_hostname']]),
            'DeploymentID' => $this->ID,
            'HostID' => $this->HostID,
            'ParentSiteID' => $StagingSite->ID
        ], true);

        // Update provisioning status
        $this->Status = 'available';
        $this->save();

        // On after deployment event
        \Emergence\EventBus::fireEvent('afterDeploymentCompleted', $this->getRootClass(), [
            'Record' => $this,
            'StagingSite' => $StagingSite,
            'ProductionSite' => $ProdSite
        ]);

        // Update new site file systems
        $this->requestFileSystemUpdates();
    }

    /*
     * Returns an ssl crt / key path for given hostname
     *
     * @params string $hostname
     * @return mixed
     */
    public static function getAvailableSSLCert($hostname)
    {
        if (!empty(static::$sslPaths[$hostname])) {
            return static::$sslPaths[$hostname];
        }

        // Check for wildcard
        $wildcardPattern = '*' . substr($hostname, strpos($hostname, '.'));
        if (!empty(static::$sslPaths[$wildcardPattern])) {
            return static::$sslPaths[$wildcardPattern];
        }

        return false;
    }

    /*
     * Updates the file system of all sites
     *
     * @return void
     */
    public function requestFileSystemUpdates()
    {
        $Site = Site::getByWhere([
            'DeploymentID' => $this->ID,
            'ParentSiteID' => $this->ParentSiteID
        ]);

        // Create update job on top level deployment site
        $jobsData = [[
            'handle' => $Site->Handle,
            'action' => 'vfs-update',
            'cursor' => $Site->ParentCursor,
            'updateChild' => true
        ]];

        // Set all deployment sites to updating
        foreach ($this->Sites as $Site) {
            $Site->Updating = true;
            $Site->save();
        }

        $this->Host->executeBulkJobsRequest($jobsData);
    }

    /*
     * Creates a maintenance request for each site's summary
     *
     * @return void
     */
    public function requestFileSystemSummary()
    {
        $jobsData = [];

        foreach ($this->Sites as $Site) {
            array_push($jobsData, [
                'handle' => $Site->Handle,
                'action' => 'vfs-summary',
                'cursor' => $Site->ParentCursor
            ]);
        }

        $this->Host->executeBulkJobsRequest($jobsData);
    }

    /*
     * Determines if one or mutiple sites in deployment are currently updating
     *
     * @return bool
     */
    public function isUpdating()
    {
        if ($this->Sites) {
            foreach ($this->Sites as $Site) {
                if ($Site->Updating) {
                    return true;
                }
            }
        }

        return false;
    }

    /*
     * Additional validation checks
     *
     * @params bool $deep
     * @return void
     */
    public function validate($deep = true)
    {
        parent::validate($deep);

        // Verify primary hostname is available
        if ($this->PrimaryHostname && ($this->Status == 'pending' || $this->Status == 'draft')) {
            if (Hostname::getByField('Hostname', $this->PrimaryHostname)) {
                $this->_validator->addError('PrimaryHostname', 'Primary hostname is not available.');
            }

            if (in_array($this->PrimaryHostname, static::$blacklistedHostnames)) {
                $this->_validator->addError('PrimaryHostname', 'Primary hostname is not available.');
            }
        }

        return $this->finishValidation();
    }
}
