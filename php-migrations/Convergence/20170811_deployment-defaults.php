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

if (static::getColumn('convergence_deployment', 'ParentSiteID')) {
    print("Setting default ParentSiteID value to `convergence_deployment`");
    DB::nonQuery("ALTER TABLE `convergence_deployment` CHANGE COLUMN `ParentSiteID` `ParentSiteID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `PrimaryHostname`;");
    $skipped = false;
}

if (static::getColumn('convergence_deployment', 'Label')) {
    print("Setting default Label value to `convergence_deployment`");
    DB::nonQuery("ALTER TABLE `convergence_deployment` CHANGE COLUMN `Label` `Label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `CreatorID`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;
