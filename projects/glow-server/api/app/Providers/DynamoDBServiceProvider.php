<?php

namespace App\Providers;

use App\Infrastructure\DynamoDB\DynamoDbClient;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DynamoDBServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(DynamoDbClient::class, function () {
            return new DynamoDbClient();
        });
    }

    /**
     * @return array<mixed>
     */
    public function provides(): array
    {
        return [
            DynamoDbClient::class,
        ];
    }
}
