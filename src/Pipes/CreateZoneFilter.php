<?php

namespace OUTRAGEdns\PdnsProxy\Environment\Account\Pipes;

use OUTRAGEdns\PdnsProxy\Api\EnvironmentInterface;

class CreateZoneFilter
{
    private $environment;

    /**
     *  Constructor
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     *  Invoke
     */
    public function __invoke(array $data): array
    {
        $data['account'] = $this->environment->getAccount();

        return $data;
    }
}
