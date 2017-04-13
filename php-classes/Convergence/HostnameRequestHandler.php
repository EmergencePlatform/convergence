<?php

namespace Convergence;

class HostnamesRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Hostname::class;
    static public $accountLevelRead = 'Administrator';
    static public $accountLevelComment = 'Administrator';
    static public $accountLevelBrowse = 'Administrator';
    static public $accountLevelWrite = 'Administrator';
    static public $accountLevelAPI = 'Administrator';
}