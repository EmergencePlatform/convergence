<?php

// skip conditions
$skipped = true;
if (!static::tableExists('convergence_sites')) {
    printf("Skipping migration because table `convergence_sites` does not exist yet\n");
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn('convergence_sites', 'LocalCursor')) {
    print("Setting default LocalCursor value on `convergence_sites`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `LocalCursor` `LocalCursor` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `InheritanceKey`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'ParentCursor')) {
    print("Setting default ParentCursor value to `convergence_sites`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `ParentCursor` `ParentCursor` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `LocalCursor`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'DeploymentID')) {
    print("Setting default DeploymentID value to `convergence_sites`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `DeploymentID` `DeploymentID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `Updating`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'HostID')) {
    print("Setting default Received value to `HostID`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `HostID` `HostID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `DeploymentID`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'PrimaryHostnameID')) {
    print("Setting default PrimaryHostnameID value to `convergence_sites`");
    DB::nonQuery("ALTER TABLE`convergence_sites` CHANGE COLUMN `PrimaryHostnameID` `PrimaryHostnameID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `HostID`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'ParentSiteID')) {
    print("Setting default ParentSiteID value to `convergence_sites`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `ParentSiteID` `ParentSiteID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `PrimaryHostnameID`;");
    $skipped = false;
}

if (static::getColumn('convergence_sites', 'Label')) {
    print("Setting default Label value to `convergence_sites`");
    DB::nonQuery("ALTER TABLE `convergence_sites` CHANGE COLUMN `Label` `Label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `CreatorID`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;











