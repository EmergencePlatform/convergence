<?php

namespace Convergence;

class HostnameRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Hostname::class;
    static public $accountLevelRead = 'Administrator';
    static public $accountLevelComment = 'Administrator';
    static public $accountLevelBrowse = 'Administrator';
    static public $accountLevelWrite = 'Administrator';
    static public $accountLevelAPI = 'Administrator';

    static public function handleRecordsRequest($action = false)
    {
        switch($action ? $action : $action = static::shiftPath())
        {
            case 'available':
                return static::handleHostnameAvailabilityRequest($action);
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    /**
     * Allow users to check if hostname is availalbe before submitting to server
     */
    public static function handleHostnameAvailabilityRequest()
    {
        $results = [
            'success' => false,
            'data' => [
                'hostname' => null,
                'available' => false
            ]
        ];

        if (!empty($_GET['hostname'])) {
            $results['success'] = true;
            $results['data']['hostname'] = $_GET['hostname'];

            // Search for hostname
            $Hostname = Hostname::getByWhere(['Hostname' => $results['data']['hostname']]);

            if (!$Hostname) {
                $results['data']['available'] = true;
            }

        } else {
            $results['error'] = 'Missing Hostname GET param';
        }

        \JSON::respond($results);
    }
}
