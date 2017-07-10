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
        'ParentSiteID' => [
            'type' => 'uint',
            'default' => 0
        ]
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
        'PrimaryHostname' => 'require-relationship'
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
        'Hostnames' => [
            'type' => 'one-many',
            'class' => Hostname::class
        ],
        'ParentSite' => [
            'type' => 'one-one',
            'class' => Site::class
        ],
        'Jobs' => [
            'type' => 'one-many',
            'class' => Job::class,
            'order' => 'ID DESC'
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

    /*
     * Execute request for given site
     *
     * @params string $path
     * @params string $method
     * @params array $requestData
     * @params array $headers
     * @return array
     */
    public function executeRequest($path = '', $method = 'POST', $requestData = [], $headers = [])
    {
        return $this->Host->executeRequest('/sites/' . $this->Handle . '/' . $path, $method, $requestData, $headers);
    }

    /*
     * Detemines if ssl cert is avaiable for site
     *
     * @return bool
     */
    public function sslEnabled()
    {
        if (Deployment::getAvailableSSLCert($this->PrimaryHostname->Hostname)) {
            return true;
        }

        return false;
    }

    /*
     * Get list of jobs from memory
     *
     * @return array
     */
    public function getJobsSummary()
    {
        $jobsRequest = $this->executeRequest('jobs', 'GET');

        // Sort jobs if available
        if ($jobsRequest && $jobsRequest['jobs'] !== false) {
            usort($jobsRequest['jobs'], function($a, $b) {
                if ($a['received'] == $b['received']) {
                    return 0;
                }
                if (!$a['received']) {
                    return -1;
                } elseif (!$b['received']) {
                    return 1;
                }
                return ($a['received'] > $b['received']) ? -1 : 1;
            });
        }

        return $jobsRequest;
    }

    /*
     * Create maintence request to retrieve the file system summary
     *
     * @return array
     */
    public function requestFileSystemSummary()
    {
        $result = $this->executeRequest('jobs', 'POST', [[
            'action' => 'vfs-summary',
            'cursor' => $this->ParentCursor
        ]]);

        // Create coorelated job
        Job::createFromJobsRequest($this->Host, $result);

        return $result;
    }

    /*
     * Create job request to update the file system
     *
     * @return array
     */
    public function requestFileSystemUpdate()
    {
        // Create job request
        $result = $this->executeRequest('jobs', 'POST', [[
            'action' => 'vfs-update',
            'cursor' => $this->ParentCursor,
        ]]);

        // Create coorelated job
        Job::createFromJobsRequest($this->Host, $result);

        return $result;
    }

    /*
     *  Returns percentage of sites currently updating
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

    /*
     *  Runs php-shell command to create one or multiple
     *  user accounts for $this site.
     *
     *  @params array $data
     *  @return array
     */
    public function addUsers($data)
    {
        $userCode = '$userClass = User::getStaticDefaultClass();';
        $userCode .= 'foreach ('.var_export($data, true).' AS $userData): ';
        $userCode .= '$user = $userClass::create($userData, true);';
        $userCode .= '$user->setClearPassword($userData["Password"]);';
        $userCode .= '$user->save();';
        $userCode .= 'endforeach;';

        return $this->executeRequest('php-shell', 'POST', $userCode);
    }
}
