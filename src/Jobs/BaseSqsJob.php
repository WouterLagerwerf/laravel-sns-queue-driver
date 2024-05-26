<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver\Jobs;

use Illuminate\Queue\Jobs\Job as BaseJob;
use Illuminate\Queue\Jobs\JobName;

class BaseSqsJob extends BaseJob
{
    protected $job;

    public function __construct($container, $sqs, $queue, $job)
    {
        $this->container = $container;
        $this->sqs = $sqs;
        $this->queue = $queue;
        $this->job = $job;
    }

    /**
     * Fire the job.
     * change the fire method to call the handle method of the job class to accept the format of SNS messages instead of SQS messages.
     * @return void
     */
    public function fire()
    {
        $payload = $this->formatPayload($this->payload());
        [$class, $method] = JobName::parse($payload['job']);

        ($this->instance = $this->resolve($class))->{$method}($this, $payload['data']);
    }

    /**
     * Save the failed job.
     * change the failed method to call the handle method of the job class to accept the format of SNS messages instead of SQS messages.
     * @return void
     */
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
