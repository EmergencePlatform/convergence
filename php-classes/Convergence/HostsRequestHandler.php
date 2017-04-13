<?php

namespace Convergence;

class HostsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Host::class;
    static public $accountLevelRead = 'Administrator';
    static public $accountLevelComment = 'Administrator';
    static public $accountLevelBrowse = 'Administrator';
    static public $accountLevelWrite = 'Administrator';
    static public $accountLevelAPI = 'Administrator';
}