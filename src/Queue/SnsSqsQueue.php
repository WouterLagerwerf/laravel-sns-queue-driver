<?php

namespace WouterLagerwerf\LaravelSnsQueueDriver\Queue;

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

    /**
     * Pop the next job off of the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue = $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (! is_null($response['Messages']) && count($response['Messages']) > 0) {
            return new BaseSqsJob(
                $this->container, $this->sqs, $response['Messages'][0],
                $this->connectionName, $queue
            );
        }
    }
}
