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

    public static function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath())
        {
            case 'update':
                return static::handleSystemUpdateRequest();
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    public static function handleRecordRequest(\ActiveRecord $Record, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'update':
                return static::handleUpdateSiteFileSystemRequest($Record);
            default:
                return parent::handleRecordRequest($Record, $action);
        }
    }

    public static function handleUpdateSiteFileSystemRequest($Record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $summary = $Record->updateFileSystem();
        }

        static::respond('deployments/deploymentUpdate', [
            'success' => true,
            'data' => $Record,
            'summary' => $summary
        ]);
    }

    public static function handleSystemUpdateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \JSON::respond([
                'success' => false,
                'error' => 'POST Request Required'
            ]);
        }

        set_time_limit(0);

        // Get update level, 0 = all, 1 = staging, 2 = production
        $level = intval($_POST['level']);

        // Loop over all deployments
        $deployments = Deployment::getAll();

        foreach ($deployments as $Deployment) {

            // Set all sites in all deployments to updated
            if ($level == 0) {

                foreach ($Deployment->Sites as $Site) {
                    $Site->Updating = 1;
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
                    $StagingSite->Updating = 1;
                    $StagingSite->save();

                // Only set the production sites as updating
                } else {
                    $ProductionSite = Site::getByWhere([
                        'DeploymentID' => $Deployment->ID,
                        'ParentSiteID' => $StagingSite->ID
                    ]);

                    $ProductionSite->Updating = 1;
                    $ProductionSite->save();
                }
            }
        }

        // Finish request without exiting
        \JSON::respond(['success' => true], false);

        foreach ($deployments as $Deployment) {

            if ($level == 0) {
                $Deployment->updateFileSystem();

            } else {

                $StagingSite = Site::getByWhere([
                    'DeploymentID' => $Deployment->ID,
                    'ParentSiteID' => $Deployment->ParentSiteID
                ]);

                // Only update the staging site
                if ($level == 1) {
                    $StagingSite->updateFileSystem();

                // Only update the production site
                } else {

                    $ProductionSite = Site::getByWhere([
                        'DeploymentID' => $Deployment->ID,
                        'ParentSiteID' => $StagingSite->ID
                    ]);
                    $ProductionSite->updateFileSystem();
                }
            }
        }
    }
}
