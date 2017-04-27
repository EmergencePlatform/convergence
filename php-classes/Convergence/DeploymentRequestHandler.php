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
}