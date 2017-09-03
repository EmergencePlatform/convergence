<?php

namespace Convergence;

class DeploymentAdmin extends \ActiveRecord
{
    public static $tableName = 'convergence_deployment_admin';
    public static $singularNoun = 'deployment admin';
    public static $pluralNoun = 'deployment admins';

    public static $fields = [
        'DeploymentID' => [
            'type' => 'uint',
            'default' => 0
        ],
        'UserID' => [
            'type' => 'uint',
            'default' => 0
        ]
    ];

    public static $relationships = [
        'Deployment' => [
            'type' => 'one-one',
            'class' => Deployment::class
        ],
        'User' => [
            'type' => 'one-one',
            'class' => \Emergence\People\Person::class
        ]
    ];
}