<?php

namespace App\Operators;

use App\Traits\NotificationTrait;
use Aws\Credentials\Credentials;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class DynamoDbOperator
{
    use NotificationTrait;

    public function __construct()
    {
    }

    private function getClient(array $config): DynamoDbClient
    {
        $credentials = new Credentials(
            key: $config['key'],
            secret: $config['secret'],
            token: null,
            expires: null,
        );
        $client = new DynamoDbClient([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);

        return $client;
    }

    public function putItem(array $config, array $item): bool
    {
        $client = $this->getClient($config);

        try {
            $client->putItem([
                'TableName' => $config['table'],
                'Item' => $item,
            ]);
        } catch (DynamoDbException $e) {
            \Log::error('DynamoDB putItem Error: ' . $e->getMessage());
            $this->sendDangerNotification('DynamoDB putItem Error', $e->getMessage());
            return false;
        }

        return true;
    }

    public function updateItem(
        array $config,
        array $key,
        string $updateExpression,
        array $expressionAttributeNames,
        array $expressionAttributeValues,
    ): bool {
        $client = $this->getClient($config);

        try {
            $client->updateItem([
                'TableName' => $config['table'],
                'Key' => $key,
                'UpdateExpression' => $updateExpression,
                'ExpressionAttributeNames' => $expressionAttributeNames,
                'ExpressionAttributeValues' => $expressionAttributeValues,
            ]);
        } catch (\Exception $e) {
            \Log::error('DynamoDB updateItem Error: ' . $e->getMessage());
            $this->sendDangerNotification('DynamoDB updateItem Error', $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param array $config
     * @param string $keyConditionExpression
     * @param array $expressionAttributeNames
     * @param array $expressionAttributeValues
     * @return array|null 取得失敗時にnullを返す
     */
    public function query(
        array $config,
        string $keyConditionExpression,
        array $expressionAttributeNames,
        array $expressionAttributeValues,
    ): ?array {
        $client = $this->getClient($config);

        try {
            $result = $client->query([
                'TableName' => $config['table'],
                'KeyConditionExpression' => $keyConditionExpression,
                'ExpressionAttributeNames' => $expressionAttributeNames,
                'ExpressionAttributeValues' => $expressionAttributeValues,
            ]);
        } catch (\Exception $e) {
            \Log::error('DynamoDB query Error: ' . $e->getMessage());
            $this->sendDangerNotification('DynamoDB query Error', $e->getMessage());
            return null;
        }

        return $result->toArray();
    }

    /**
     * @param array $config
     * @param array $key
     * @return array|null 取得失敗時にnullを返す
     */
    public function getItem(array $config, array $key): ?array
    {
        $client = $this->getClient($config);

        try {
            $result = $client->getItem([
                'TableName' => $config['table'],
                'Key' => $key,
            ]);
        } catch (\Exception $e) {
            \Log::error('DynamoDB getItem Error: ' . $e->getMessage());
            $this->sendDangerNotification('DynamoDB getItem Error', $e->getMessage());
            return null;
        }

        return $result->toArray();
    }

    public function deleteItem(array $config, array $key): bool
    {
        $client = $this->getClient($config);

        try {
            $client->deleteItem([
                'TableName' => $config['table'],
                'Key' => $key,
            ]);
        } catch (\Exception $e) {
            \Log::error('DynamoDB delete Error: ' . $e->getMessage());
            $this->sendDangerNotification('DynamoDB delete Error', $e->getMessage());
            return false;
        }

        return true;
    }
}
