<?php
namespace WouterLagerwerf\LaravelSnsQueueDriver;

use Illuminate\Queue\Failed\DatabaseUuidFailedJobProvider as BaseProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

class DatabaseUuidFailedJobProvider extends BaseProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     * @param \Throwable $exception
     * @return string|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $decodedPayload = json_decode($payload, true);
        $uuid = $decodedPayload['uuid'] ?? ($decodedPayload["MessageId"] ?? Str::uuid());

        $this->getTable()->insert([
            'uuid' => $uuid,
            'connection' => $connection,
            'queue' => $queue,
            'payload' => $payload,
            'exception' => (string) mb_convert_encoding($exception, 'UTF-8'),
            'failed_at' => Date::now(),
        ]);

        return $uuid;
    }
}
