
# Laravel SNS Queue Driver

The Laravel SNS Queue Driver package extends Laravel's queue system to support Amazon SNS (Simple Notification Service), enabling seamless integration with microservice messaging architectures.

## Installation

You can install the package via Composer:

```bash
composer require wout/laravel-sns-queue-driver
```

## Configuration

After installing the package, add the following configuration to your `config/queue.php` file:

```php
'connections' => [
    'sns' => [
        'driver' => 'sns',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'queue' => env('SQS_QUEUE', 'default'),
        'suffix' => env('SQS_SUFFIX'),
        'after_commit' => false,
        'endpoint' => env('AWS_ENDPOINT'),
        'sns_topic_arn' => env('SNS_TOPIC_ARN', 'arn:aws:sns:us-east-1:your-account-id:topic'),
    ],
],
```

Make sure to replace the placeholder values with your actual AWS credentials and SNS topic ARN.

## AWS Configuration
For this package make sure that you have setup a SNS topic and a SQS queue that is subscribed to the SNS topic. The SNS topic ARN should be set in the `sns_topic_arn` configuration value.

## Usage

Once configured, you can use the SNS queue driver just like any other queue driver in Laravel:

```php
use Illuminate\Support\Facades\Queue;

Queue::push(function ($job) {
    // Process the job
    $job->delete();
});
```

## Testing

You can run the package tests with:

```bash
composer test
```

## Contributing

Contributions are welcome! If you find a bug or want to suggest a new feature, feel free to open an issue or submit a pull request.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

