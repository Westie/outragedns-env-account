<?php

namespace OUTRAGEdns\PdnsProxy\Environment\Account\Pipes;

use OUTRAGEdns\PdnsProxy\Api\EnvironmentInterface;

class ListZoneFilter
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
        $account = $this->environment->getAccount();

        foreach ($data as $key => $row) {
            if (!array_key_exists('account', $row) || $row['account'] !== $account) {
                unset($data[$key]);
            }
        }

        return array_values($data);
    }
}
