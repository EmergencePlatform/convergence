<?php

namespace Convergence;

class Host extends \ActiveRecord
{
    public static $tableName = 'convergence_hosts';
    public static $singularNoun = 'host';
    public static $pluralNoun = 'hosts';
    public static $collectionRoute = 'hosts';

    public static $fields = [
        'Hostname' => [
            'unique' => true,
            'default' => null
        ],
        'MaxSites' => [
            'type' => 'uint',
            'default' => 25
        ],
        'KernelVersion' => [
            'default' => null
        ],
        'ApiUsername' => [
            'default' => null,
            'accountLevelEnumerate' => 'Administrator'
        ],
        'ApiKey' => [
            'default' => null,
            'accountLevelEnumerate' => 'Administrator'
        ]
    ];

    public static $validators = [
        'Hostname' => 'FQDN',
        'MaxSites' => [
            'validator' => 'number',
            'min' => 1
        ],
        'KernelVersion' => [
            'validator' => 'handle',
            'required' => false,
            'pattern' => '/^\d+(\\.\d+)*$/'
        ]
    ];

    public static $relationships = [
        'Sites' => [
            'type' => 'one-many',
            'class' => Site::class
        ],
        'Deployments' => [
            'type' => 'one-many',
            'class' => Deployment::class
        ]
    ];

    public function getHandle()
    {
        return $this->Hostname;
    }

    public static function getByHandle($handle)
    {
        return static::getByField('Hostname', $handle);
    }

    public static function getAvailable()
    {
        // TODO: return least-used host under maximum site load
        return static::getByWhere('1', ['order' => 'RAND()']);
    }

    /*
     * Generate site for host
     *
     * @params array $configs
     * @return array
     */
    public function createSite($configs)
    {
        $response = $this->executeRequest('/sites', 'POST', $configs);

        if (empty($response['success'])) {
            \Emergence\Logger::general_critical('Failed to create site', [
                'response' => json_encode($response)
            ]);
            throw new \Exception('Failed to create site');
        }

        return $response;
    }

    /*
     * Execute bulk jobs request
     *
     * @return array
     */
    public function executeBulkJobsRequest($jobsData)
    {
        // Submit bulk jobs request
        $results = $this->executeRequest('/jobs', 'POST', $jobsData);

        // Create jobs from result
        Job::createFromJobsRequest($this, $results);

        return $result;
    }

    /*
     * Push request to Emergence kernel
     *
     * @params string $path
     * @params string $requestMethod
     * @params array $params
     * @params array $headers
     * @return array
     */
    public function executeRequest($path, $requestMethod = 'GET', $params = [], $headers = [])
    {
        $url = 'http://' . $this->Hostname . ':9083' . $path;
        //\Debug::dumpVar($params, false, "$requestMethod $url", false);

        // initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set authentication
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->ApiUsername . ':' . $this->ApiKey);

        // process method and params
        if ($requestMethod == 'GET') {
            $url .= '?' . (is_string($params) ? $params : http_build_query($params));
        } else {
            if ($requestMethod == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
            }

            if (is_array($params)) {
                $headers[] = 'Content-Type: application/json';
                $params = json_encode($params);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // execute request
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        //\Debug::dumpVar(['info' => $info, 'output' => $output], false, 'request executed');

        // return response
        if ($info['content_type'] == 'application/json' && (!isset($options['autoDecode']) || $options['autoDecode'])) {
            $output = json_decode($output, true);
        }

        return $output;
    }
}
