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
            $Record->executeRequest('', 'PATCH', [
                'label' => $Record->Label,
                'primary_hostname' => $Record->PrimaryHostname->Hostname
            ]);
        }
    }

    public static function handleUpdateSiteFileSystemRequest($Record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['updatevfs'])) {
                $Record->requestFileSystemUpdate();
                $Record->Updating = true;
                $Record->save();
            } else {
                $Record->requestFileSystemSummary();
            }
        }

        $Record->syncFileSystemUpdates();
        $jobs = $Record->getJobsSummary();

        static::respond('sites/siteUpdate', [
            'success' => true,
            'data' => $Record,
            'summary' => $summary,
            'jobs' => $jobs
        ]);
    }

    public static function handleUpdateSiteCursorRequest($Record)
    {
        $Record->updateLocalCursor();
        \Site::redirect('/sites/' . $Record->Handle . '?cursorupdated=1');
    }
}