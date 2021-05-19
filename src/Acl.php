<?php

namespace OUTRAGEdns\PdnsProxy\Environment\Account;

use Laminas\Db\Adapter\Adapter as Adapter;
use Laminas\Db\Sql\Sql;
use OUTRAGEdns\PdnsProxy\Api\AclInterface;
use OUTRAGEdns\PdnsProxy\Api\Environment;

class Acl implements AclInterface
{
    private $sql;
    private $environment;

    /**
     *  Constructor
     */
    public function __construct(Adapter $adapter, Environment $environment)
    {
        $this->sql = new Sql($adapter);
        $this->environment = $environment;
    }

    /**
     *  Can this user/session perform this action?
     */
    public function can(string $operationId, array $constraints = []): bool
    {
        switch ($operationId) {
            case 'error':
                return true;

            case 'listServer':
            case 'listServers':
                return true;

            case 'cacheFlushByName':
                return true;

            case 'listZones':
            case 'createZone':
                return true;

            case 'axfrExportZone':
            case 'axfrRetrieveZone':
            case 'createCryptokey':
            case 'createMetadata':
            case 'deleteCryptokey':
            case 'deleteMetadata':
            case 'deleteZone':
            case 'getCryptokey':
            case 'getMetadata':
            case 'listCryptokeys':
            case 'listMetadata':
            case 'listZone':
            case 'modifyCryptokey':
            case 'modifyMetadata':
            case 'notifyZone':
            case 'patchZone':
            case 'putZone':
            case 'rectifyZone':
                return $this->canModifyZone($constraints['zone_id']);

            case 'getConfig':
            case 'getConfigSetting':
            case 'getStats':
                return true;

            case 'searchData':
                return true;

            case 'listTSIGKeys':
            case 'createTSIGKey':
            case 'getTSIGKey':
            case 'putTSIGKey':
            case 'deleteTSIGKey':
                return true;
        }

        return false;
    }

    /**
     *  Can this account modify a zone?
     */
    protected function canModifyZone(string $zoneId): bool
    {
        $select = $this->sql->select()
            ->from('domains')
            ->columns([ 'id' ])
            ->where([ 'account' => $this->environment->getAccount() ])
            ->where([ 'name' => trim($zoneId, '.') ]);

        return $this->sql->prepareStatementForSqlObject($select)->execute()->count() === 1;
    }
}
