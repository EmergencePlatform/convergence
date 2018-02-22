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
    public static $browseLimitDefault = 20;
    public static $browseOrder = ['ID' => 'ASC'];

    public static function handleRecordRequest(\ActiveRecord $Record, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'update':
                return static::handleUpdateSiteFileSystemRequest($Record);
            default:
                return parent::handleRecordRequest($Record, $action);
        }
    }

    protected static function applyRecordDelta(\ActiveRecord $Record, $data)
    {
        // Create / assign primary hostname
        if (!empty($data['PrimaryHostname'])) {
            if ($Record->PrimaryHostname) {
                $Record->PrimaryHostname->Hostname = $data['PrimaryHostname'];
            } else {
                $Record->PrimaryHostname = Hostname::create([
                    'Hostname' => $data['PrimaryHostname'],
                    'Site' => $Record
                ]);
            }
        }

        // @todo validate secondary hostnames

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
        if (!empty($data['Hostnames'])) {
            Hostname::setHostnames($Record, $data['Hostnames']);
        }

        // Create Emergence site for new records
        if ($Record->isNew) {
            $configs = [
                'handle' => $Record->Handle,
                'hostnames' => [],
                'inheritance_key' => '',
                'label' => $Record->Label,
                'primary_hostname' => $Record->PrimaryHostname->Hostname
            ];

            // @todo validate one or another is available
            if ($Record->ParentSite) {
                $configs['parent_hostname'] = $Record->PrimaryHostname->Hostname;
                $configs['parent_key'] = $Record->ParentSite->InheritanceKey;
            } else {
                $configs['parent_key'] = $data[''];
                $configs['parent_key'] = $data[''];
            }

            // Get ssl path for site
            if ($sslPath = Deployment::getAvailableSSLCert($Record->PrimaryHostname->Hostname)) {
                $configs['ssl'] = [
                    "certificate" => $sslPath . ".crt",
                    "certificate_key" => $sslPath . ".key",
                ];
            }

            //$siteResponse = $Record->Host->createSite($configs);
            //$Record->InheritanceKey = $siteResponse['data']['inheritance_key'];
            //$Record->save();

        } elseif (!empty($data['UpdateSSL'])) {
            if ($sslPath = Deployment::getAvailableSSLCert($Record->PrimaryHostname->Hostname)) {
                $configs = [
                    'ssl' => [
                        "certificate" => $sslPath . ".crt",
                        "certificate_key" => $sslPath . ".key"
                    ]
                ];

                $result = $Record->executeRequest('', 'PATCH', $configs);
                Job::createFromJobsRequest($Record->Host, $result);
            }
        }
    }

    public static function handleUpdateSiteFileSystemRequest($Record)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($_POST['action']) {
                case 'vfs-update':
                    $Record->requestFileSystemUpdate();
                    $Record->Updating = true;
                    $Record->save();
                    break;
                case 'vfs-summary':
                    $Record->requestFileSystemSummary();
                    break;
                case 'jobs-sync':
                    Job::syncActiveJobs();
                    $Record = Site::getByID($Record->ID);
                    break;
            }
        }

        static::respond('sites/siteUpdate', [
            'success' => true,
            'data' => $Record
        ]);
    }
}
