# outragedns-env-account
PowerDNS account based environment wrapper

## Configuration
```PHP
$container->add(EnvironmentFactoryInterface::class, function () {
    $config['db'] = [
        'dsn' => 'mysql:host=172.31.3.55;dbname=pdns;charset=utf8mb4',
        'username' => 'pdns',
        'password' => 'pdnspw',
    ];

    $config['account'] = 'westie@localhost';

    $config['api'] = [
        'base_url' => 'http://172.31.3.55:8088',
        'key' => '216f6e36f7557887cdaf65b689e2e6a2',
    ];

    return new EnvironmentFactory($config);
});
```
