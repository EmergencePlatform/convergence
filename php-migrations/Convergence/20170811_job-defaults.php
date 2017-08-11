<?php

// skip conditions
$skipped = true;
if (!static::tableExists('convergence_jobs')) {
    printf("Skipping migration because table `convergence_jobs` does not exist yet\n");
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn('convergence_jobs', 'Received')) {
    print("Setting default Received value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `Received` `Received` TIMESTAMP NULL DEFAULT NULL  COMMENT '' AFTER `Action`;");
    $skipped = false;
}

if (static::getColumn('convergence_jobs', 'Started')) {
    print("Setting default Started value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `Started` `Started` TIMESTAMP NULL DEFAULT NULL  COMMENT '' AFTER `Received`;");
    $skipped = false;
}

if (static::getColumn('convergence_jobs', 'Completed')) {
    print("Setting default Completed value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `Completed` `Completed` TIMESTAMP NULL DEFAULT NULL  COMMENT '' AFTER `Started`;");
    $skipped = false;
}

if (static::getColumn('convergence_jobs', 'SiteID')) {
    print("Setting default SiteID value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `SiteID` `SiteID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `Result`;");
    $skipped = false;
}

if (static::getColumn('convergence_jobs', 'HostID')) {
    print("Setting default HostID value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `HostID` `HostID` INT(10) UNSIGNED NOT NULL DEFAULT 0  COMMENT '' AFTER `SiteID`;");
    $skipped = false;
}

if (static::getColumn('convergence_jobs', 'Result')) {
    print("Setting default Result value to `convergence_jobs`");
    DB::nonQuery("ALTER TABLE `convergence_jobs` CHANGE COLUMN `Result` `Result` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL  COMMENT '' AFTER `Command`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;
