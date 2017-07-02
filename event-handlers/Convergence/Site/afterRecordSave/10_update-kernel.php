<?php

namespace Convergence;

// Patch kernel with updated config
if (!$_EVENT['Record']->isNew) {
    $_EVENT['Record']->executeRequest('', 'PATCH', [
        'label' => $_EVENT['Record']->Label,
        'primary_hostname' => $_EVENT['Record']->PrimaryHostname->Hostname
    ]);
}
