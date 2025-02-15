<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\ServiceDatabase;
use App\Models\StandaloneMariadb;
use App\Models\StandaloneMongodb;
use App\Models\StandaloneMysql;
use App\Models\StandalonePostgresql;
use App\Models\StandaloneRedis;
use Illuminate\Console\Command;

class CleanupStuckedResources extends Command
{
    protected $signature = 'cleanup:stucked-resources';
    protected $description = 'Cleanup Stucked Resources';

    public function handle()
    {
        ray('Running cleanup stucked resources.');
        echo "Running cleanup stucked resources.\n";
        $this->cleanup_stucked_resources();
    }
    private function cleanup_stucked_resources()
    {

        try {
            $applications = Application::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($applications as $application) {
                echo "Deleting stuck application: {$application->name}\n";
                $application->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck application: {$e->getMessage()}\n";
        }
        try {
            $postgresqls = StandalonePostgresql::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($postgresqls as $postgresql) {
                echo "Deleting stuck postgresql: {$postgresql->name}\n";
                $postgresql->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck postgresql: {$e->getMessage()}\n";
        }
        try {
            $redis = StandaloneRedis::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($redis as $redis) {
                echo "Deleting stuck redis: {$redis->name}\n";
                $redis->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck redis: {$e->getMessage()}\n";
        }
        try {
            $mongodbs = StandaloneMongodb::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($mongodbs as $mongodb) {
                echo "Deleting stuck mongodb: {$mongodb->name}\n";
                $mongodb->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck mongodb: {$e->getMessage()}\n";
        }
        try {
            $mysqls = StandaloneMysql::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($mysqls as $mysql) {
                echo "Deleting stuck mysql: {$mysql->name}\n";
                $mysql->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck mysql: {$e->getMessage()}\n";
        }
        try {
            $mariadbs = StandaloneMariadb::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($mariadbs as $mariadb) {
                echo "Deleting stuck mariadb: {$mariadb->name}\n";
                $mariadb->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck mariadb: {$e->getMessage()}\n";
        }
        try {
            $services = Service::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($services as $service) {
                echo "Deleting stuck service: {$service->name}\n";
                $service->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck service: {$e->getMessage()}\n";
        }
        try {
            $serviceApps = ServiceApplication::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($serviceApps as $serviceApp) {
                echo "Deleting stuck serviceapp: {$serviceApp->name}\n";
                $serviceApp->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck serviceapp: {$e->getMessage()}\n";
        }
        try {
            $serviceDbs = ServiceDatabase::withTrashed()->whereNotNull('deleted_at')->get();
            foreach ($serviceDbs as $serviceDb) {
                echo "Deleting stuck serviceapp: {$serviceDb->name}\n";
                $serviceDb->forceDelete();
            }
        } catch (\Throwable $e) {
            echo "Error in cleaning stuck serviceapp: {$e->getMessage()}\n";
        }

        // Cleanup any resources that are not attached to any environment or destination or server
        try {
            $applications = Application::all();
            foreach ($applications as $application) {
                if (!data_get($application, 'environment')) {
                    echo 'Application without environment: ' . $application->name . '\n';
                    $application->forceDelete();
                    continue;
                }
                if (!$application->destination()) {
                    echo 'Application without destination: ' . $application->name . '\n';
                    $application->forceDelete();
                    continue;
                }
                if (!data_get($application, 'destination.server')) {
                    echo 'Application without server: ' . $application->name . '\n';
                    $application->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in application: {$e->getMessage()}\n";
        }
        try {
            $postgresqls = StandalonePostgresql::all()->where('id', '!=', 0);
            foreach ($postgresqls as $postgresql) {
                if (!data_get($postgresql, 'environment')) {
                    echo 'Postgresql without environment: ' . $postgresql->name . '\n';
                    $postgresql->forceDelete();
                    continue;
                }
                if (!$postgresql->destination()) {
                    echo 'Postgresql without destination: ' . $postgresql->name . '\n';
                    $postgresql->forceDelete();
                    continue;
                }
                if (!data_get($postgresql, 'destination.server')) {
                    echo 'Postgresql without server: ' . $postgresql->name . '\n';
                    $postgresql->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in postgresql: {$e->getMessage()}\n";
        }
        try {
            $redis = StandaloneRedis::all();
            foreach ($redis as $redis) {
                if (!data_get($redis, 'environment')) {
                    echo 'Redis without environment: ' . $redis->name . '\n';
                    $redis->forceDelete();
                    continue;
                }
                if (!$redis->destination()) {
                    echo 'Redis without destination: ' . $redis->name . '\n';
                    $redis->forceDelete();
                    continue;
                }
                if (!data_get($redis, 'destination.server')) {
                    echo 'Redis without server: ' . $redis->name . '\n';
                    $redis->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in redis: {$e->getMessage()}\n";
        }

        try {
            $mongodbs = StandaloneMongodb::all();
            foreach ($mongodbs as $mongodb) {
                if (!data_get($mongodb, 'environment')) {
                    echo 'Mongodb without environment: ' . $mongodb->name . '\n';
                    $mongodb->forceDelete();
                    continue;
                }
                if (!$mongodb->destination()) {
                    echo 'Mongodb without destination: ' . $mongodb->name . '\n';
                    $mongodb->forceDelete();
                    continue;
                }
                if (!data_get($mongodb, 'destination.server')) {
                    echo 'Mongodb without server:  ' . $mongodb->name . '\n';
                    $mongodb->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in mongodb: {$e->getMessage()}\n";
        }

        try {
            $mysqls = StandaloneMysql::all();
            foreach ($mysqls as $mysql) {
                if (!data_get($mysql, 'environment')) {
                    echo 'Mysql without environment: ' . $mysql->name . '\n';
                    $mysql->forceDelete();
                    continue;
                }
                if (!$mysql->destination()) {
                    echo 'Mysql without destination: ' . $mysql->name . '\n';
                    $mysql->forceDelete();
                    continue;
                }
                if (!data_get($mysql, 'destination.server')) {
                    echo 'Mysql without server: ' . $mysql->name . '\n';
                    $mysql->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in mysql: {$e->getMessage()}\n";
        }

        try {
            $mariadbs = StandaloneMariadb::all();
            foreach ($mariadbs as $mariadb) {
                if (!data_get($mariadb, 'environment')) {
                    echo 'Mariadb without environment: ' . $mariadb->name . '\n';
                    $mariadb->forceDelete();
                    continue;
                }
                if (!$mariadb->destination()) {
                    echo 'Mariadb without destination: ' . $mariadb->name . '\n';
                    $mariadb->forceDelete();
                    continue;
                }
                if (!data_get($mariadb, 'destination.server')) {
                    echo 'Mariadb without server: ' . $mariadb->name . '\n';
                    $mariadb->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in mariadb: {$e->getMessage()}\n";
        }

        try {
            $services = Service::all();
            foreach ($services as $service) {
                if (!data_get($service, 'environment')) {
                    echo 'Service without environment: ' . $service->name . '\n';
                    $service->forceDelete();
                    continue;
                }
                if (!$service->destination()) {
                    echo 'Service without destination: ' . $service->name . '\n';
                    $service->forceDelete();
                    continue;
                }
                if (!data_get($service, 'server')) {
                    echo 'Service without server: ' . $service->name . '\n';
                    $service->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in service: {$e->getMessage()}\n";
        }
        try {
            $serviceApplications = ServiceApplication::all();
            foreach ($serviceApplications as $service) {
                if (!data_get($service, 'service')) {
                    echo 'ServiceApplication without service: ' . $service->name . '\n';
                    $service->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in serviceApplications: {$e->getMessage()}\n";
        }
        try {
            $serviceDatabases = ServiceDatabase::all();
            foreach ($serviceDatabases as $service) {
                if (!data_get($service, 'service')) {
                    echo 'ServiceDatabase without service: ' . $service->name . '\n';
                    $service->forceDelete();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            echo "Error in ServiceDatabases: {$e->getMessage()}\n";
        }
    }
}
