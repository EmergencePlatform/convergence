<?php

namespace Convergence;

if (!$_EVENT['Record']->HostID) {

    // Set host if missing then save
    if ($Host = Host::getAvailable()) {
        $_EVENT['Record']->Host = $Host;
        $_EVENT['Record']->save();
        return;

    // Otherwise revert record to draft
    } else {
        if ($_EVENT['Record']->Status != 'draft') {
            $_EVENT['Record']->Status = 'draft';
            $_EVENT['Record']->save();
        }
        return;
    }
}

// Deploy pending
if ($_EVENT['Record']->Status == 'pending') {
    $_EVENT['Record']->deploy();
}
