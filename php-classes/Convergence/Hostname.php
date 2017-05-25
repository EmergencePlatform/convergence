<?php

namespace Convergence;

class Hostname extends \ActiveRecord
{
    public static $tableName = 'convergence_hostnames';
    public static $singularNoun = 'hostname';
    public static $pluralNoun = 'hostnames';

    public static $fields = [
        'Hostname' => [
        'unique' => true
    ],
        'SiteID' => 'uint'
    ];

    public static $validators = [
        'Hostname' => 'FQDN'
    ];

    public static $relationships = [
        'Site' => [
            'type' => 'one-one',
            'class' => Site::class
        ]
    ];

    public function __toString()
    {
        return $this->Hostname;
    }

    public function getHandle()
    {
        return $this->Hostname;
    }

    public static function getByHandle($handle)
    {
        return static::getByField('Hostname', $handle);
    }

    public function validate($deep = true)
    {
        parent::validate($deep);

        $Hostname = static::getByField('Hostname', $this->Hostname);
        if ($Hostname && $Hostname->ID != $this->ID) {
            $this->_validator->addError('Hostname', 'Hostname taken');
        }

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if (!$this->isNew && $this->isFieldDirty('Hostname') && $this->SiteID) {
            $params = ['primary_hostname' => $this->Hostname];
            $response = $this->Site->executeRequest('', 'PATCH', $params);
        }
    }
}
