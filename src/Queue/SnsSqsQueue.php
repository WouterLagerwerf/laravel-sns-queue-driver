<?php

namespace Wout\LaravelSnsQueueDriver\Queue;

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Illuminate\Queue\SqsQueue;
use App\Jobs\BaseSqsJob;

class SnsSqsQueue extends SqsQueue
{
    protected SnsClient $sns;

    protected string $topicArn;

    public function __construct(
        SnsClient $sns,
        string $topicArn,
        SqsClient $sqs,
        $default,
        $prefix = '',
        $suffix = '',
        $dispatchAfterCommit = false
    ) {
        $this->sns = $sns;
        $this->topicArn = $topicArn;

        parent::__construct($sqs, $default, $prefix, $suffix, $dispatchAfterCommit);
    }

    /**
     * Push a new job onto the queue.
     */
    protected function createJob($queue, $job, $data)
    {
        return new BaseSqsJob(
            $this->container, $this, $job,
            $queue, $data['Attempts'] ?? null,
            $data['MessageId'] ?? null
        );
    }

    /**
     * Push a new job onto the queue.
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->sns->publish([
            'TopicArn' => $this->topicArn,
            'Message' => $payload,
        ])->get('MessageId');
    }

    /**
     * Push a new job onto the queue after a delay.
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue ?: $this->default, $data),
            $queue,
            $delay,
            function ($payload, $queue, $delay) {
                return $this->sns->publish([
                    'TopicArn' => $this->topicArn,
                    'MessageBody' => $payload,
                    'DelaySeconds' => $this->secondsUntil($delay),
                ])->get('MessageId');
            }
        );
    }
}
