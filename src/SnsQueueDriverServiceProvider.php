<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver;

use WouterLagerwerf\LaravelSnsQueueDriver\Queue\Connectors\SnsSqsConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use WouterLagerwerf\LaravelSnsQueueDriver\DatabaseUuidFailedJobProvider;

class SnsQueueDriverServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FailedJobProviderInterface::class, function ($app) {
            return new DatabaseUuidFailedJobProvider(
                $app['db'],
                $app['config']['queue.failed.database'],
                $app['config']['queue.failed.table']
            );
        });

        $this->app->afterResolving(QueueManager::class, function (QueueManager $manager) {
            $manager->addConnector('sns', function () {
                return new SnsSqsConnector();
            });
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}