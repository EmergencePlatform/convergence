<?php

namespace Convergence;

// Patch kernel with updated config
if (!$_EVENT['Record']->isNew) {
    $data = [
        'label' => $_EVENT['Record']->Label,
        'primary_hostname' => $_EVENT['Record']->PrimaryHostname->Hostname
    ];

    $sslPath = Deployment::getAvailableSSLCert($_EVENT['Record']->PrimaryHostname->Hostname);

    if ($sslPath) {
        $data['ssl'] = [
            "certificate" => $sslPath . ".crt",
            "certificate_key" => $sslPath . ".key",
        ];
    } else {
        $data['ssl'] = [];
    }

    $_EVENT['Record']->executeRequest('', 'PATCH', $data);
}
