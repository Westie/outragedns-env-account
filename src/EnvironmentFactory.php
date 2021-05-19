<?php

namespace OUTRAGEdns\PdnsProxy\Environment\Account;

use Laminas\Db\Adapter\Adapter as Adapter;
use OUTRAGEdns\PdnsProxy\Api\Environment;
use OUTRAGEdns\PdnsProxy\Api\EnvironmentFactoryInterface;
use OUTRAGEdns\PdnsProxy\Api\EnvironmentInterface;
use OUTRAGEdns\PdnsProxy\Api\PipelineProvider;
use Psr\Http\Message\RequestInterface;

class EnvironmentFactory implements EnvironmentFactoryInterface
{
    private $config;

    /**
     *  Constructor
     */
    public function __construct(array $config)
    {
        if (!empty($config['db'])) {
            if (empty($config['db']['driver'])) {
                $config['db']['driver'] = 'Pdo';
            }
        }

        $this->config = $config;
    }

    /**
     *  Create Environment
     */
    public function createEnvironment(RequestInterface $request): EnvironmentInterface
    {
        $adapter = $this->getSqlAdapter();

        // create our environment
        $environment = new Environment();
        $environment->setAccount($this->config['account']);
        $environment->setApiKey($this->config['api']['key']);
        $environment->setBaseUrl($this->config['api']['base_url']);
        $environment->setAcl(new Acl($adapter, $environment));

        // insert our pipes
        if ($pipelineProvider = $this->getRequestBodyPipelineProvider($environment)) {
            $environment->setRequestBodyPipelineProvider($pipelineProvider);
        }
        if ($pipelineProvider = $this->getResponseBodyPipelineProvider($environment)) {
            $environment->setResponseBodyPipelineProvider($pipelineProvider);
        }

        return $environment;
    }

    /**
     *  Get SQL adapter
     */
    private function getSqlAdapter()
    {
        return new Adapter($this->config['db']);
    }

    /**
     *  Set up request body pipeline provider
     */
    private function getRequestBodyPipelineProvider(Environment $environment): PipelineProvider
    {
        $pipelineProvider = new PipelineProvider();

        // force all zones to have a corresponding account reference
        $pipelineProvider->add([ 'createZone', 'patchZone', 'putZone' ], function ($data) use ($environment) {
            $account = $environment->getAccount();
            if (!empty($account)) {
                $data['account'] = $account;
            }
            return $data;
        });

        return $pipelineProvider;
    }

    /**
     *  Set up response body pipeline provider
     */
    private function getResponseBodyPipelineProvider(Environment $environment): PipelineProvider
    {
        $pipelineProvider = new PipelineProvider();

        // hide zones that are not associated with an account
        $pipelineProvider->add('listZones', function ($data) use ($environment) {
            $account = $environment->getAccount();
            if (!empty($account)) {
                foreach ($data as $key => $row) {
                    if (!array_key_exists('account', $row) || $row['account'] !== $account) {
                        unset($data[$key]);
                    }
                }
                $data = array_values($data);
            }
            return $data;
        });

        return $pipelineProvider;
    }
}
