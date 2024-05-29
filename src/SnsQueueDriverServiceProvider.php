<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver;

use App\Providers\DatabaseUuidFailedJobProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Database\DatabaseManager;
use WouterLagerwerf\LaravelSnsQueueDriver\Queue\Connectors\SnsSqsConnector;

class SnsQueueDriverServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerFailedJobProvider();
    }

    public function boot(QueueManager $queueManager)
    {
        $this->registerSnsConnector($queueManager);
    }

    protected function registerFailedJobProvider()
    {
        $this->app->singleton(FailedJobProviderInterface::class, 
            function (DatabaseManager $db) {
            return new DatabaseUuidFailedJobProvider(
                $db,
                config('queue.failed.database'),
                config('queue.failed.table')
            );
        });
    }

    protected function registerSnsConnector(QueueManager $queueManager)
    {
        $queueManager->addConnector('sns', function () {
            return new SnsSqsConnector();
        });
    }
}
