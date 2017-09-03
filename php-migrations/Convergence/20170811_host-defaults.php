<?php

// skip conditions
$skipped = true;
if (!static::tableExists('convergence_hosts')) {
    printf("Skipping migration because table `convergence_hosts` does not exist yet\n");
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn('convergence_hosts', 'Hostname')) {
    print("Setting default Hostname value to `convergence_hosts`");
    DB::nonQuery("ALTER TABLE `convergence_hosts` CHANGE COLUMN `Hostname` `Hostname` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `CreatorID`;");
    $skipped = false;
}

// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;
