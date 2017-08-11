<?php

// skip conditions
$skipped = true;
if (!static::tableExists('convergence_deployment')) {
    printf("Skipping migration because table `convergence_deployment` does not exist yet\n");
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn('convergence_deployment', 'HostID')) {
    print("Setting default HostID value to `convergence_deployment`");
    DB::nonQuery("ALTER TABLE `convergence_deployment` CHANGE COLUMN `HostID` `HostID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `ParentSiteID`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;
