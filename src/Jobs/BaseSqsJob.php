<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver\Jobs;

use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Container\Container;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\Jobs\JobName;
use Illuminate\Contracts\Container\BindingResolutionException;

class BaseSqsJob extends SqsJob
{
    /**
     * Fire the job.
     */
    public function fire(): void
    {
        $payload = $this->formatPayload($this->payload());
        [$class, $method] = JobName::parse($payload['job']);

        // Resolve the job class and call the method
        ($this->instance = $this->resolve($class))->{$method}($this, $payload['data']);
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $e
     */
    protected function failed($e): void
    {
        $payload = $this->formatPayload($this->payload());
        [$class, $method] = JobName::parse($payload['job']);

        $this->instance = $this->resolve($class);
        if (method_exists($this->instance, 'failed')) {
            $this->instance->failed($payload['data'], $e, $payload['uuid'] ?? '');
        }
    }

    /**
     * Get the name of the job.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->formatPayload($this->payload())['job'];
    }

    /**
     * Format the payload for the job.
     *
     * @param string $payload
     * @return array
     */
    private function formatPayload($payload): array
    {
        $message = json_decode($payload["Body"], true)["Message"];
        return json_decode($message, true);
    }

    /**
     * Get the job ID.
     *
     * @return string
     */
    public function getJobId(): string
    {
        return $this->job['MessageId'];
    }

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody(): string
    {
        return json_encode($this->job);
    }
}
