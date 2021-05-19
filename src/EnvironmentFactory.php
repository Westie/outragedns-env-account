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
        $adapter = new Adapter($this->config['db']);

        $environment = new Environment();
        $environment->setAcl(new Acl($adapter, $environment));
        $environment->setBaseApiKey($this->config['api']['key']);
        $environment->setBaseUrl($this->config['api']['base_url']);

        if (!empty($this->config['account'])) {
            $environment->setAccount($this->config['account']);
        } elseif (!empty($this->config['authenticator'])) {
            $environment->setAuthenticator($this->config['authenticator']);
        }

        $environment->getRequestBodyPipelineProvider()
            ->add([ 'createZone', 'patchZone', 'putZone' ], new Pipes\CreateZoneFilter($environment));

        $environment->getResponseBodyPipelineProvider()
            ->add([ 'listZones' ], new Pipes\ListZoneFilter($environment));

        return $environment;
    }
}
