<?php

namespace Convergence;

class SitesRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Site::class;
    static public $accountLevelRead = 'Administrator';
    static public $accountLevelComment = 'Administrator';
    static public $accountLevelBrowse = 'Administrator';
    static public $accountLevelWrite = 'Administrator';
    static public $accountLevelAPI = 'Administrator';
    public static $browseLimitDefault = 100;
    public static $browseOrder = ['ID' => 'ASC'];

    public static function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath())
        {
            case 'update':
                return static::handleSystemUpdateRequest();
            case 'update-status':
                return static::handleUpdateStatusRequest();
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    public static function handleRecordRequest(\ActiveRecord $Record, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'update':
                return static::handleUpdateSiteFileSystemRequest($Record);
            case 'update-cursor':
                return static::handleUpdateSiteCursorRequest($Record);
            default:
                return parent::handleRecordRequest($Record, $action);
        }
    }

    protected static function applyRecordDelta(\ActiveRecord $Record, $data)
    {
        // Create / assign primary hostname
        if (!empty($data['PrimaryHostname'])) {
            if ($PrimaryHostname = Hostname::getByField('Hostname', $data['PrimaryHostname'])) {
                $Record->PrimaryHostname = $PrimaryHostname;
            } else {
                $Record->PrimaryHostname = Hostname::create(['Hostname' => $data['PrimaryHostname']]);
            }
        }

        // Create / assign parent site
        if ($Record->isPhantom) {

            // Lookup parent site by key
            if (empty($data['ParentID'])) {
                if ($Parent = Site::getByField('InheritanceKey', $_POST['ParentKey'])) {
                    $Record->ParentSite = $Parent;
                }
            }

            // Sanitize handle
            if (empty($data['Handle'])) {
                $handle = strtolower($data['Label']);
            } else {
                $handle = strtolower($data['Handle']);
            }

            $data['Handle'] = substr(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $handle)), 0, 16);
        }

        return parent::applyRecordDelta($Record, $data);
    }

    protected static function onRecordSaved(\ActiveRecord $Record, $data)
    {
        // Create Emergence site for new records
        if ($Record->isNew) {
            $configs = [
                'handle' => $Record->Handle,
                'hostnames' => [],
                'inheritance_key' => '',
                'label' => $Record->Label,
                'parent_hostname' => $Record->ParentSite->PrimaryHostname->Hostname,
                'parent_key' => $Record->ParentSite->InheritanceKey,
                'primary_hostname' => $Record->PrimaryHostname->Hostname
            ];

            $siteResponse = $Record->Host->createSite($configs);
            $Record->InheritanceKey = $siteResponse['data']['inheritance_key'];
            $Record->save();
        
        // Patch primary hostname update to the kernel
        } else {
            // Pull into an event handler
            // https://github.com/SlateFoundation/slate-cbl/blob/develop/event-handlers/Slate/CBL/Demonstrations/DemonstrationSkill/afterRecordSave/50_complete-competency.php
            $Record->executeRequest([
                'label' => $Record->Label,
                'primary_hostname' => $Record->PrimaryHostname->Hostname
            ], 'PATCH');
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

        set_time_limit(0);

        if (!empty($_POST['level'])) {
            $level = intval($_POST['level']);
        } else {
            $level = 0;
        }

        // All site
        if ($level == 0) {

            $sites = Site::getAll();
            foreach ($sites as $Site) {
                $Site->Updating = 1;
                $Site->save();
            }

        } else {

            $sites = Site::getAllByWhere(['ParentSiteID' => 0]);

            // Skeleton Site
            if ($level == 1) {

                foreach ($sites as $Site) {
                    $Site->Updating = 1;
                    $Site->save();
                }

                $sitesToUpdate = $sites;

            } else {

                $parentIDs = [];
                foreach ($sites as $Site) {
                    array_push($parentIDs, $Site->ID);
                }

                $stagingSites = Site::getChildren($parentIDs);

                // Staging sites
                if ($level == 2) {

                    foreach ($stagingSites as $Site) {
                        $Site->Updating = 1;
                        $Site->save();
                    }

                    $sitesToUpdate = $stagingSites;

                // Production sites
                } else {
                    $stagingIDs = [];
                    foreach ($stagingSites as $Site) {
                        array_push($stagingIDs, $Site->ID);
                    }

                    $productionSites = Site::getChildren($stagingIDs);

                    foreach ($productionSites as $Site) {
                        $Site->Updating = 1;
                        $Site->save();
                    }

                    $sitesToUpdate = $productionSites;
                }
            }
        }

        // Finish request without exiting
        \JSON::respond(['success' => true], false);

        // Update all sites
        if ($level == 0) {

            // Get all top level sites
            $sites = \Convergence\Site::getAllByWhere(['ParentSiteID' => 0]);
            $parentIDs = [];

            // Update all parent sites
            foreach ($sites as $Site) {
                $Site->updateFileSystem();
                array_push($parentIDs, $Site->ID);
            }

            // Recursively update child sites
            while ($childSites = Site::getUpdateQueue($parentIDs)) {
                foreach ($childSites as $Site) {
                    $Site->updateFileSystem();
                    array_push($parentIDs, $Site->ID);
                }
            }

        // Update white listed sites
        } else {
            foreach ($sitesToUpdate as $Site) {
                $Site->updateFileSystem();
            }
        }
    }

    public static function handleUpdateStatusRequest()
    {
        \JSON::respond([
            'success' => true,
            'updating' => Site::getUpdateProgress()
        ]);
    }

    public static function handleUpdateSiteFileSystemRequest($Record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $summary = $Record->updateFileSystem();
        }

        static::respond('sites/siteUpdate', [
            'success' => true,
            'data' => $Record,
            'summary' => $summary
        ]);
    }

    public static function handleUpdateSiteCursorRequest($Record)
    {
        $Record->updateLocalCursor();
        \Site::redirect('/sites/' . $Record->Handle . '?cursorupdated=1');
    }
}