<?php

namespace App\Actions\Database;

use App\Models\ServiceDatabase;
use App\Models\StandaloneMariadb;
use App\Models\StandaloneMongodb;
use App\Models\StandaloneMysql;
use App\Models\StandalonePostgresql;
use App\Models\StandaloneRedis;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Yaml\Yaml;

class StartDatabaseProxy
{
    use AsAction;

    public function handle(StandaloneRedis|StandalonePostgresql|StandaloneMongodb|StandaloneMysql|StandaloneMariadb|ServiceDatabase $database)
    {
        $internalPort = null;
        $type = $database->getMorphClass();
        $network = data_get($database, 'destination.network');
        $server = data_get($database, 'destination.server');
        $containerName = data_get($database, 'uuid');
        $proxyContainerName = "{$database->uuid}-proxy";
        if ($database->getMorphClass() === 'App\Models\ServiceDatabase') {
            $databaseType = $database->databaseType();
            $network = data_get($database, 'service.destination.network');
            $server = data_get($database, 'service.destination.server');
            $proxyContainerName = "{$database->service->uuid}-proxy";
            switch ($databaseType) {
                case 'standalone-mariadb':
                    $type = 'App\Models\StandaloneMariadb';
                    $containerName = "mariadb-{$database->service->uuid}";
                    break;
                case 'standalone-mongodb':
                    $type = 'App\Models\StandaloneMongodb';
                    $containerName = "mongodb-{$database->service->uuid}";
                    break;
                case 'standalone-mysql':
                    $type = 'App\Models\StandaloneMysql';
                    $containerName = "mysql-{$database->service->uuid}";
                    break;
                case 'standalone-postgresql':
                    $type = 'App\Models\StandalonePostgresql';
                    $containerName = "postgresql-{$database->service->uuid}";
                    break;
                case 'standalone-redis':
                    $type = 'App\Models\StandaloneRedis';
                    $containerName = "redis-{$database->service->uuid}";
                    break;
            }
        }
        if ($type === 'App\Models\StandaloneRedis') {
            $internalPort = 6379;
        } else if ($type === 'App\Models\StandalonePostgresql') {
            $internalPort = 5432;
        } else if ($type === 'App\Models\StandaloneMongodb') {
            $internalPort = 27017;
        } else if ($type === 'App\Models\StandaloneMysql') {
            $internalPort = 3306;
        } else if ($type === 'App\Models\StandaloneMariadb') {
            $internalPort = 3306;
        }
        $configuration_dir = database_proxy_dir($database->uuid);
        $nginxconf = <<<EOF
    user  nginx;
    worker_processes  auto;

    error_log  /var/log/nginx/error.log;

    events {
        worker_connections  1024;
    }
    stream {
       server {
            listen $database->public_port;
            proxy_pass $containerName:$internalPort;
       }
    }
    EOF;
        $dockerfile = <<< EOF
    FROM nginx:stable-alpine

    COPY nginx.conf /etc/nginx/nginx.conf
    EOF;
        $docker_compose = [
            'version' => '3.8',
            'services' => [
                $proxyContainerName => [
                    'build' => [
                        'context' => $configuration_dir,
                        'dockerfile' => 'Dockerfile',
                    ],
                    'image' => "nginx:stable-alpine",
                    'container_name' => $proxyContainerName,
                    'restart' => RESTART_MODE,
                    'ports' => [
                        "$database->public_port:$database->public_port",
                    ],
                    'networks' => [
                        $network,
                    ],
                    'healthcheck' => [
                        'test' => [
                            'CMD-SHELL',
                            'stat /etc/nginx/nginx.conf || exit 1',
                        ],
                        'interval' => '5s',
                        'timeout' => '5s',
                        'retries' => 3,
                        'start_period' => '1s'
                    ],
                ]
            ],
            'networks' => [
                $network => [
                    'external' => true,
                    'name' => $network,
                    'attachable' => true,
                ]
            ]
        ];
        $dockercompose_base64 = base64_encode(Yaml::dump($docker_compose, 4, 2));
        $nginxconf_base64 = base64_encode($nginxconf);
        $dockerfile_base64 = base64_encode($dockerfile);
        instant_remote_process([
            "mkdir -p $configuration_dir",
            "echo '{$dockerfile_base64}' | base64 -d > $configuration_dir/Dockerfile",
            "echo '{$nginxconf_base64}' | base64 -d > $configuration_dir/nginx.conf",
            "echo '{$dockercompose_base64}' | base64 -d > $configuration_dir/docker-compose.yaml",
            "docker compose --project-directory {$configuration_dir} pull",
            "docker compose --project-directory {$configuration_dir} up --build -d",
        ], $server);
    }
}
