<?php

namespace Wout\LaravelSnsQueueDriver\Jobs;

use Illuminate\Queue\Jobs\Job as BaseJob;
use Illuminate\Queue\Jobs\JobName;

class BaseSqsJob extends BaseJob
{
    /**
     * Fire the job.
     * change the fire method to call the handle method of the job class to accept the format of SNS messages instead of SQS messages.
     * @return void
     */
    public function fire()
    {
        $payload = $this->formatPayload($this->payload())['job'];
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
        $payload = $this->formatPayload($this->payload())['job'];
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
        $payload = $this->payload();
        $message = $this->payload()['Message'];
        return json_decode($message, true);
    }
}
