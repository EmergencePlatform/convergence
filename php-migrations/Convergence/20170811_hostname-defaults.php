<?php

// skip conditions
$skipped = true;
if (!static::tableExists('convergence_hostnames')) {
    printf("Skipping migration because table `convergence_hostnames` does not exist yet\n");
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn('convergence_hostnames', 'Hostname')) {
    print("Setting default Hostname value to `convergence_hostnames`");
    DB::nonQuery("ALTER TABLE `convergence_hostnames` CHANGE COLUMN `Hostname` `Hostname` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `CreatorID`;");
    $skipped = false;
}

if (static::getColumn('convergence_hostnames', 'SiteID')) {
    print("Setting default SiteID value to `convergence_hostnames`");
    DB::nonQuery("ALTER TABLE `convergence_hostnames` CHANGE COLUMN `SiteID` `SiteID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `Hostname`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;






