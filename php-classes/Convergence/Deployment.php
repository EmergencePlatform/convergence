<?php

namespace Convergence;

class Deployment extends \ActiveRecord
{
    public static $tableName = 'convergence_deployment';
    public static $singularNoun = 'deployment';
    public static $pluralNoun = 'deployments';
    public static $collectionRoute = 'deployments';

    public static $defaultHostname;
    public static $defaultParentHostname = 'skeleton-temp.sandbox02.jarv.us'; // 'skeleton-v2.emr.ge';
    public static $defaultParentInheritanceKey = '3CsAitz4GyB0MVs7'; // 'lKhjNhwXoM8rLbXw';
    public static $onBeforeStagingDeployment;
    public static $onBeforeProductionDeployment;
    public static $onAfterDeployment;

    public static $fields = [
        'Label' => [
            'required' => true
        ],
        'Status' => [
            'type' => 'enum',
            'values'=> ['draft', 'pending', 'provisioning', 'available', 'suspended', 'interrupted'],
            'default' => 'draft'
        ],
        'ParentSiteID' => 'uint',
        'HostID' => 'uint'
    ];

    public static $relationships = [
        'ParentSite' => [
            'type' => 'one-one',
            'class' => Site::class
        ],
        'Sites' => [
            'type' => 'one-many',
            'class' => Site::class
        ],
        'Admins' => [
            'type' => 'one-many',
            'class' => DeploymentAdmin::class
        ],
        'Host' => [
            'type' => 'one-one',
            'class' => Host::class
        ]
    ];

    /*
     * Create staging and production site for deployment
     *
     * @return array
     */
    public function deploy()
    {
        if ($this->Status !== 'pending') {
            return false;
        }

        // Update store status
        $this->Status = 'provisioning';
        $this->save();

        // Create unique handle
        $hostname = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $this->Label)));
        $handle = \HandleBehavior::getUniqueHandle(\Convergence\Site::class, substr($hostname, 0, 14));

        // Set up staging params
        $stagingConfig = [
            'handle' => $handle . '-s',
            'hostnames' => [],
            'inheritance_key' => '',
            'label' => $this->Label . ' (Staging)'
        ];

        // Set the parent site configs
        if ($this->ParentSite) {
            $stagingConfig['parent_hostname'] = $this->ParentSite->PrimaryHostname->Hostname;
            $stagingConfig['parent_key'] = $this->ParentSite->InheritanceKey;
        } else {
            $stagingConfig['parent_hostname'] = static::$defaultParentHostname;
            $stagingConfig['parent_key'] = static::$defaultParentInheritanceKey;
        }

        // Determine base hostname
        if (static::$defaultHostname) {
            $baseHostname = static::$defaultHostname;
        } else {
            $baseHostname = $_SERVER['HTTP_HOST'];
        }

        // Set staging hostname
        $stagingConfig['primary_hostname'] = 'staging.' . $hostname . '.' . $baseHostname;

        // On before staging deploy
        if (is_callable(static::$onBeforeStagingDeployment)) {
            $stagingConfig = call_user_func(static::$onBeforeStagingDeployment, $this, $stagingConfig);
        }

        // Create staging site in kernel
        $stagingResponse = $this->Host->createSite($stagingConfig);

        // Create matching convergence site
        $StagingSite = Site::create([
            'Label' => $stagingResponse['data']['label'],
            'Handle' => $stagingResponse['data']['handle'],
            'InheritanceKey' => $stagingResponse['data']['inheritance_key'],
            'PrimaryHostname' => Hostname::create(['Hostname' => $stagingResponse['data']['primary_hostname']]),
            'DeploymentID' => $this->ID,
            'HostID' => $this->HostID,
            'ParentSiteID' => $this->ParentSiteID
        ], true);

        // Run the pull tool
        // @todo must use parent with this file available
        // $StagingSite->updateFileSystem();

        // Create production site configs
        $prodConfig = [
            'handle' => $handle,
            'hostnames' => [],
            'inheritance_key' => '',
            'label' => $this->Label,
            'parent_hostname' => $StagingSite->PrimaryHostname->Hostname,
            'parent_key' => $StagingSite->InheritanceKey,
            'primary_hostname' => $hostname . '.' . $baseHostname
        ];

        // On before production deploy
        if (is_callable(static::$onBeforeProductionDeployment)) {
            $prodConfig = call_user_func(static::$onBeforeProductionDeployment, $this, $prodConfig);
        }

        // Create production site
        $prodResponse = $this->Host->createSite($prodConfig);

        // Create matching convergence site
        $ProdSide = Site::create([
            'Label' => $prodResponse['data']['label'],
            'Handle' => $prodResponse['data']['handle'],
            'InheritanceKey' => $prodResponse['data']['inheritance_key'],
            'PrimaryHostname' => Hostname::create(['Hostname' => $prodResponse['data']['primary_hostname']]),
            'DeploymentID' => $this->ID,
            'HostID' => $this->HostID,
            'ParentSiteID' => $StagingSite->ID
        ], true);
        
        // Run the pull tool
        // @todo must use parent site with the SiteUpdater file available
        // $ProdSite->updateFileSystem();

        // Update local cursors
        // @todo must use parent site with the SiteUpdater file available
        // $StagingSite->updateLocalCursor();
        // $ProdSite->updateLocalCursor();

        // Update provisioning status
        $this->Status = 'available';
        $this->save();

        // On after deployment
        if (is_callable(static::$onAfterDeployment)) {
            call_user_func(static::$onAfterDeployment, $this, $StagingSite, $ProdSite);
        }
    }
}