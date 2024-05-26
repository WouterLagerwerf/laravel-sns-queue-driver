<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver;

use WouterLagerwerf\LaravelSnsQueueDriver\Queue\Connectors\SnsSqsConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class SnsQueueDriverServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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