<?php

namespace Convergence;

class Hostname extends \ActiveRecord
{
    public static $tableName = 'convergence_hostnames';
    public static $singularNoun = 'hostname';
    public static $pluralNoun = 'hostnames';

    public static $fields = [
        'Hostname' => [
            'unique' => true,
            'default' => null
        ],
        'SiteID' => [
            'type' => 'uint',
            'default' => 0
        ]
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

        if (!$this->isNew && $this->isFieldDirty('Hostname') && $this->Site && $this->Site->PrimaryHostnameID == $this->ID) {
            $params = ['primary_hostname' => $this->Hostname];
            $result = $this->Site->executeRequest('', 'PATCH', $params);
            Job::createFromJobsRequest($this->Site->Host, $result);
        }
    }

    public static function setHostnames($Site, $hostnames)
    {
        // Update / create hours
        $hostnameIDs = [$Site->PrimaryHostnameID];
        $secondaryHostnames = [];

        foreach ($hostnames as $name) {

            $Hostname = static::getByWhere([
                'Hostname' => $name,
                'SiteID' => $Site->ID
            ]);

            if (!$Hostname) {
                $Hostname = Hostname::create([
                    'Hostname' => $name,
                    'SiteID' => $Site->ID
                ]);
            }

            // Skip invalid hostnames
            if (!$Hostname->validate()) {
                continue;
            }

            $Hostname->save();
            array_push($hostnameIDs, $Hostname->ID);
            array_push($secondaryHostnames, $Hostname->Hostname);
        }

        // Delete stale hostnames
        try {
            \DB::query(
                'DELETE FROM `%s` WHERE SiteID = %u AND ID NOT IN (%s)',
                [
                    Hostname::$tableName,
                    $Site->ID,
                    count($hostnameIDs) ? join(',', $hostnameIDs) : '0'
                ]
            );
        } catch (\TableNotFoundException $e) {
            // No hostnames need to be deleted
        }

        // Patch the site object
        $result = $Site->executeRequest('', 'PATCH', [
            'hostnames' => $secondaryHostnames
        ]);
        Job::createFromJobsRequest($Site->Host, $result);
    }
}
