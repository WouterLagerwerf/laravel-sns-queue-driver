<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver\Jobs;

use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Container\Container;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\Jobs\JobName;

class BaseSqsJob extends SqsJob
{
    public function __construct(
        Container $container, 
        SqsClient $sqs, 
        array $job, 
        $connectionName, 
        $queue)
    {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);
    }

    public function fire()
    {
        $payload = $this->formatPayload($this->payload());
        [$class, $method] = JobName::parse($payload['job']);

        ($this->instance = $this->resolve($class))->{$method}($this, $payload['data']);
    }

    protected function failed($e)
    {
        $payload = $this->formatPayload($this->payload());
        [$class, $method] = JobName::parse($payload['job']);

        if (method_exists($this->instance = $this->resolve($class), 'failed')) {
            $this->instance->failed($payload['data'], $e, $payload['uuid'] ?? '');
        }
    }

    public function getName()
    {
        return $this->formatPayload($this->payload())['job'];
    }

    private function formatPayload($payload)
    {
        $message = $payload['Message'];
        return json_decode($message, true);
    }

    public function getJobId()
    {
        return $this->job['MessageId'];
    }

    public function getRawBody()
    {
        return json_encode($this->job);
    }
}
