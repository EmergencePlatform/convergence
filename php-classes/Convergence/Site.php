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
            'action' => 'vfs-summary'
        ]]);

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

        // Add new job to queue
        $jobsQueue = $this->Host->getJobsQueue();

        if (!is_array($jobsQueue[$this->Handle])) {
            $jobsQueue[$this->Handle] = [$result['jobs'][0]];
        } else {
            array_push($jobsQueue[$this->Handle], $result['jobs'][0]);
        }

        // Update jobs queue for host
        $this->Host->updateJobsQueue($jobsQueue);

        return $result;
    }

    /*
     * Update sites based on pending job status
     *
     * @return void
     */
    public function syncFileSystemUpdates()
    {
        $activeJobs = $this->getJobsSummary()['jobs'];
        $jobsQueue = $this->Host->getJobsQueue();

        if (empty($jobsQueue[$this->Handle])) {
            return true;
        }

        // Check each job assigned to site
        foreach ($jobsQueue[$this->Handle] as $index => $job) {
            $jobFound = false;

            // Find the coorelated active job
            foreach ($activeJobs as $activeJob) {

                if ($activeJob['uid'] == $job['uid']) {
                    $jobFound = true;

                    if ($activeJob['command']['action'] == 'vfs-update') {

                        // Update site if vfs-update has completed or failed
                        if (in_array($activeJob['status'], ['completed', 'failed'])) {

                            // Flag initial update
                            if ($Site->ParentCursor == 0) {
                                $initialUpdate = true;
                            } else {
                                $initialUpdate = false;
                            }

                            if ($activeJob['status'] == 'completed') {
                                $this->ParentCursor = $activeJob['command']['result']['parentCursor'];
                                $this->LocalCursor = $activeJob['command']['result']['localCursor'];
                            }

                            $this->Updating = false;
                            $this->save();

                            if ($initialUpdate) {
                                \Emergence\EventBus::fireEvent('afterInitialVFSSync', $this->getRootClass(), array(
                                    'Record' => $this,
                                    'Job' => $activeJobs[$handle][$job['uid']]
                                ));
                            }

                            // Remove job from queue
                            unset($jobsQueue[$this->Handle][$index]);
                        }
                    }
                }
            }

            // If job status isn't available, mark site as updated = false
            if (!$jobFound) {
                $this->Updating = false;
                $this->save();
                unset($jobsQueue[$this->Handle][$index]);
            }
        }

        // Update jobs queue
        $this->Host->updateJobsQueue($jobsQueue);
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
