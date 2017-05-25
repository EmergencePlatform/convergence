<?php

namespace Convergence;

class DeploymentRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Deployment::class;
    static public $accountLevelRead = 'Administrator';
    static public $accountLevelComment = 'Administrator';
    static public $accountLevelBrowse = 'Administrator';
    static public $accountLevelWrite = 'Administrator';
    static public $accountLevelAPI = 'Administrator';
    static public $browseLimitDefault = 20;
    public static $browseOrder = ['Label' => 'DESC'];

    public static function handleRecordRequest(\ActiveRecord $Record, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'update':
                return static::handleUpdateFileSystemRequest($Record);
            default:
                return parent::handleRecordRequest($Record, $action);
        }
    }

    public static function handleUpdateFileSystemRequest($Record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($_POST['action']) {
                case 'vfs-update':
                    $Record->requestFileSystemUpdates();
                    break;
                case 'vfs-summary':
                    $Record->requestFileSystemSummary();
                    break;
                case 'jobs-sync':
                    Job::syncActiveJobs();
                    break;
            }
        }

        static::respond('deployments/deploymentUpdate', [
            'success' => true,
            'data' => $Record
        ]);
    }

    public static function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath())
        {
            case 'update':
                return static::handleSystemUpdateRequest();
            case 'update-status':
                return static::handleUpdateStatusRequest();
            default:
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    Job::syncActiveJobs();
                }

                return parent::handleRecordsRequest($action);
        }
    }

    public static function handleSystemUpdateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \JSON::respond([
                'success' => false,
                'error' => 'POST Request Required'
            ]);
        }

        // Get update level, 0 = all, 1 = staging, 2 = production
        $level = intval($_POST['level']);

        foreach (Host::getAll() as $Host) {
            $jobsData = [];

            foreach ($Host->Deployments as $Deployment) {

                // Set all sites in all deployments to updated
                if ($level == 0) {

                    // Get staging site and use child flag
                    $StagingSite = Site::getByWhere([
                        'DeploymentID' => $Deployment->ID,
                        'ParentSiteID' => $Deployment->ParentSiteID
                    ]);

                    array_push($jobsData, [
                        'handle' => $StagingSite->Handle,
                        'action' => 'vfs-update',
                        'cursor' => $StagingSite->ParentCursor,
                        'updateChild' => true
                    ]);

                    foreach ($Deployment->Sites as $Site) {
                        $Site->Updating = true;
                        $Site->save();
                    }

                // Only set production or staging sites to updated
                } else {

                    $StagingSite = Site::getByWhere([
                        'DeploymentID' => $Deployment->ID,
                        'ParentSiteID' => $Deployment->ParentSiteID
                    ]);

                    // Only update the staging sites as updating
                    if ($level == 1) {
                        $StagingSite->Updating = true;
                        $StagingSite->save();

                        array_push($jobsData, [
                            'handle' => $StagingSite->Handle,
                            'action' => 'vfs-update',
                            'cursor' => $StagingSite->ParentCursor
                        ]);

                    // Only set the production sites as updating
                    } else {
                        $ProductionSite = Site::getByWhere([
                            'DeploymentID' => $Deployment->ID,
                            'ParentSiteID' => $StagingSite->ID
                        ]);

                        $ProductionSite->Updating = true;
                        $ProductionSite->save();

                        array_push($jobsData, [
                            'handle' => $ProductionSite->Handle,
                            'action' => 'vfs-update',
                            'cursor' => $ProductionSite->Cursor
                        ]);
                    }
                }
            }

            if ($jobsData) {
                $Host->executeBulkJobsRequest($jobsData);
            }
        }

        \JSON::respond(['success' => true], false);
    }

    /*
     * Get percentage of site that are currently updating
     *
     * @return void
     */
    public static function handleUpdateStatusRequest()
    {
        \JSON::respond([
            'success' => true,
            'updating' => Site::getUpdateProgress()
        ]);
    }
}
