<?php

namespace Convergence;

// Patch kernel with updated label config
if (!$_EVENT['Record']->isNew && !empty($_EVENT['Record']->originalValues['Label'])) {

    $data = [
        'label' => $_EVENT['Record']->Label
    ];

    $result = $_EVENT['Record']->executeRequest('', 'PATCH', $data);
    Job::createFromJobsRequest($_EVENT['Record']->Host, $result);
}
