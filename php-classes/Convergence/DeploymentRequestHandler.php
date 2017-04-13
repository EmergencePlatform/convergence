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
}