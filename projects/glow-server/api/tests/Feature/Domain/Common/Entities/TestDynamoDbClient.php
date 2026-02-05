<?php

namespace Tests\Feature\Domain\Common\Entities;

use App\Infrastructure\DynamoDB\DynamoDbClient;
use Aws\DynamoDb\DynamoDbClient as AwsDynamoDbClient;

class TestDynamoDbClient extends DynamoDbClient
{
    /**
     * DynamoDBアクセスのポリシーはEC2やECSタスクロールとして付与するので
     * テストとしてローカルからアクセスする場合は、アクセスキーとシークレットキーを設定してアクセスする
     * そのために、テスト用にオーバーライドする
     *
     * @param array<mixed> $config
     */
    public function getClient(array $config): AwsDynamoDbClient
    {
        $this->client = new AwsDynamoDbClient([
            'version' => $config['version'],
            'region' => $config['region'],
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        return $this->client;
    }
}
