<?php

namespace Convergence;

class Job extends \ActiveRecord
{
    public static $tableName = 'convergence_jobs';
    public static $singularNoun = 'job';
    public static $pluralNoun = 'jobs';
    public static $collectionRoute = 'jobs';

    public static $fields = [
        'Status' => [
            'type' => 'enum',
            'values' => [
                'pending',
                'completed',
                'failed'
            ],
            'default' => 'pending'
        ],
        'UID',
        'Action',
        'Received' => 'timestamp',
        'Started' => 'timestamp',
        'Completed' => 'timestamp',
        'Command' => 'json',
        'Result' => 'json',
        'SiteID' => 'uint',
        'HostID' => 'uint'
    ];

    public static $relationships = [
        'Site' => [
            'type' => 'one-one',
            'class' => Site::class
        ],
        'Host' => [
            'type' => 'one-one',
            'class' => Host::class
        ]
    ];

    /*
     * Creates jobs that coorelate with the results from
     * a job request to the kernel
     *
     * @params object $Host
     * @params array $results
     * @return void
     */
    public static function createFromJobsRequest($Host, $results)
    {
        if ($results['success'] == true) {
            $jobs = [];

            if (is_array($results['jobs'])) {
                $jobs = $results['jobs'];

            } elseif (!empty($results['job'])) {
                array_push($jobs, $results['job']);
            }

            foreach ($jobs as $job) {
                $Site = Site::getByField('Handle', $job['handle']);

                if (!$Site) {
                    continue;
                }

                static::create([
                    'UID' => $job['uid'],
                    'Received' => $job['received'] / 1000,
                    'Action' => $job['command']['action'],
                    'Command' => $job['command'],
                    'SiteID' => $Site->ID,
                    'HostID' => $Host->ID
                ], true);
            }
        }
    }

    /*
     * Attempts to update any pending jobs with server
     *
     * @return void
     */
    public static function syncActiveJobs()
    {
        $hosts = Host::getAll();

        foreach ($hosts as $Host) {

            // Get pending jobs for host
            $pendingJobs = static::getAllByWhere([
                'Status' => 'pending',
                'HostID' => $Host->ID
            ]);

            if (!$pendingJobs) {
                continue;
            }

            // Get active jobs from host
            $activeJobs = $Host->executeRequest('/jobs', 'GET')['jobs'];

            // Search active jobs for pending jobs to find updates
            foreach ($pendingJobs as $PendingJob) {

                // Update any lost jobs
                if (empty($activeJobs[$PendingJob->Site->Handle])) {
                    $PendingJob->Status = 'failed';
                    $PendingJob->Result = 'lost on server';
                    if ($PendingJob->Site) {
                        $PendingJob->Site->Updating = false;
                    }
                    $PendingJob->save();
                    continue;
                }

                $activeJob = $activeJobs[$PendingJob->Site->Handle][$PendingJob->UID];

                if ($activeJob && $activeJob['status'] !== 'pending') {

                    // Update job
                    $PendingJob->Status = $activeJob['status'];
                    $PendingJob->Started = $activeJob['started'] / 1000;
                    $PendingJob->Completed = $activeJob['completed'] / 1000;
                    if (!empty($activeJob['command']['result'])) {
                        $PendingJob->Result = $activeJob['command']['result'];
                    } elseif (!empty($activeJob['message'])) {
                        $PendingJob->Result = $activeJob['message'];
                    }
                    $PendingJob->save();

                    // Update site on vfs update
                    if ($PendingJob->Action == 'vfs-update') {

                        // Skip vfs-update actions on failed job
                        if ($PendingJob->Status == 'failed') {
                            $Site->Updating = false;
                            $Site->save();
                            continue;
                        }

                        // Get updated site record
                        $Site = Site::getByID($PendingJob->SiteID);

                        // Update pending job's site
                        $initialUpdate = !boolval($Site->ParentCursor);
                        $Site->ParentCursor = $activeJob['command']['result']['parentCursor'];
                        $Site->LocalCursor = $activeJob['command']['result']['localCursor'];
                        $Site->Updating = false;
                        $Site->save();

                        // Conditionally update child site
                        if (!empty($activeJob['command']['updateChild']) && $activeJob['command']['updateChild'] === true) {
                            $childSites = Site::getAllByField('ParentSiteID', $Site->ID);
                            foreach ($childSites as $ChildSite) {
                                $ChildSite->requestFileSystemUpdate();
                            }
                        }

                        // Fire initial vfs update event
                        if ($initialUpdate) {
                            \Emergence\EventBus::fireEvent(
                                'afterInitialVFSUpdate',
                                $Site->getRootClass(),
                                [
                                    'Record' => $Site,
                                    'Job' => $PendingJob
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
