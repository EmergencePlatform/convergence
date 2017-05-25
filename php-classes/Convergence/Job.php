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

                $activeJob = $activeJobs[$PendingJob->Site->Handle][$PendingJob->UID];

                if ($activeJob && $activeJob['status'] !== 'pending') {

                    // Update job
                    $PendingJob->Status = $activeJob['status'];
                    $PendingJob->Started = $activeJob['started'] / 1000;
                    $PendingJob->Completed = $activeJob['completed'] / 1000;
                    $PendingJob->Result = $activeJob['command']['result'];
                    $PendingJob->save();

                    // Update site on vfs update
                    if ($PendingJob->Action == 'vfs-update') {

                        $initialUpdate = boolval($PendingJob->Site->ParentCursor);
                        $PendingJob->Site->ParentCursor = $activeJob['command']['result']['parentCursor'];
                        $PendingJob->Site->LocalCursor = $activeJob['command']['result']['localCursor'];
                        $PendingJob->Site->Updating = false;
                        $PendingJob->Site->save();

                        if ($initialUpdate) {
                            \Emergence\EventBus::fireEvent(
                                'afterInitialVFSUpdate',
                                $PendingJob->Site->getRootClass(),
                                [
                                    'Record' => $PendingJob->Site,
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
