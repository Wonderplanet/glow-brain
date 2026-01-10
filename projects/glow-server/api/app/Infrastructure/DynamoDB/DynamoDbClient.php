<?php

namespace App\Infrastructure\DynamoDB;

use Aws\DynamoDb\DynamoDbClient as AwsDynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Illuminate\Support\Facades\Log;

class DynamoDbClient
{
    protected AwsDynamoDbClient $client;

    public function __construct()
    {
        $this->client = $this->getClient(config('services.dynamodb'));
    }

    /**
     * @param array<mixed> $config
     */
    public function getClient(array $config): AwsDynamoDbClient
    {
        $this->client = new AwsDynamoDbClient([
            'version' => $config['version'],
            'region' => $config['region'],
        ]);

        return $this->client;
    }

    private function sendLogError(string $message, DynamoDbException $e): void
    {
        Log::error($message, [
            'message' => $e->getMessage(),
            'code' => $e->getStatusCode(),
            'aws_error' => $e->getAwsErrorCode(),
        ]);
    }

    /**
     * @param array<mixed> $params
     * @return array<mixed>|null エラーまたはレスポンスがない場合はnullを返す。空配列の場合は該当データがなかったことを示す
     */
    public function query(array $params): ?array
    {
        try {
            $result = $this->client->query($params);
        } catch (DynamoDbException $e) {
            $this->sendLogError("DynamoDBClient query error", $e);

            return null;
        }

        return $result->toArray();
    }

    /**
     * @param array<mixed> $params
     * @return bool true: 更新できた, false: 更新できなかった
     */
    public function updateItem(array $params): bool
    {
        try {
            $this->client->updateItem($params);
        } catch (DynamoDbException $e) {
            $this->sendLogError("DynamoDBClient updateItem error", $e);

            return false;
        }
        return true;
    }

    /**
     * @param array<mixed> $params
     * @return bool true: 追加できた, false: 追加できなかった
     */
    public function putItem(array $params): bool
    {
        try {
            $this->client->putItem($params);
        } catch (DynamoDbException $e) {
            $this->sendLogError("DynamoDBClient putItem error", $e);

            return false;
        }

        return true;
    }

    /**
     * @param array<mixed> $params
     * @return bool true: 削除できた, false: 削除できなかった
     */
    public function deleteItem(array $params): bool
    {
        try {
            $this->client->deleteItem($params);
        } catch (DynamoDbException $e) {
            $this->sendLogError("DynamoDBClient deleteItem error", $e);

            return false;
        }

        return true;
    }

    /**
     * @param array<mixed> $params
     * @return array<mixed>|null エラーまたはレスポンスがない場合はnullを返す。空配列の場合は該当データがなかったことを示す
     */
    public function batchGetItem(array $params): ?array
    {
        try {
            $result = $this->client->batchGetItem($params);
        } catch (DynamoDbException $e) {
            $this->sendLogError("DynamoDBClient batchGetItem error", $e);

            return null;
        }

        return $result->toArray();
    }
}
