<?php

namespace App\Operators;

use App\Services\AwsCredentialProvider;
use App\Traits\NotificationTrait;
use Aws\Lambda\LambdaClient;
use Illuminate\Support\Facades\Log;

class LambdaOperator
{
    use NotificationTrait;

    public function __construct()
    {
    }

    private function getClient(array $config): LambdaClient
    {
        $credentials = [
            'key' => $config['key'] ?? null,
            'secret' => $config['secret'] ?? null,
        ];
        if (is_null($credentials['key']) || is_null($credentials['secret'])) {
            $credentials = AwsCredentialProvider::getRoleBasedCredentialProvider();
        }

        return new LambdaClient([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);
    }

    public function invokeFunction(array $config, array $payload): bool
    {
        $client = $this->getClient($config);

        $functionName = $config['function_name'] ?? null;
        if (is_null($functionName)) {
            return false;
        }

        try {
            $client->invoke([
                'FunctionName' => $functionName,
                'Payload' => json_encode($payload),
            ]);
        } catch (\Exception $e) {
            Log::error('Lambda invokeFunction Error: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Lambda関数の指定環境変数を変更し、再デプロイする
     *
     * @param array $config AWS接続情報（region, key, secret, など）
     * @param string $functionName Lambda関数名
     * @param string $envKey 変更したい環境変数名
     * @param string $envValue 新しい値
     * @return bool 成功時true、失敗時false
     */
    public function updateEnvironmentVariableAndRedeploy(array $config, string $envKey, string $envValue): void
    {
        $client = $this->getClient($config);
        $functionName = $config['function_name'] ?? null;
        try {
            // 現在の環境変数を取得
            $result = $client->getFunctionConfiguration([
                'FunctionName' => $functionName,
            ]);
            $envVars = $result['Environment']['Variables'] ?? [];
            // 指定の環境変数を更新
            $envVars[$envKey] = $envValue;
            // updateFunctionConfigurationで反映（これで再デプロイされる）
            $client->updateFunctionConfiguration([
                'FunctionName' => $functionName,
                'Environment' => [
                    'Variables' => $envVars,
                ],
            ]);

            $this->sendProcessCompletedNotification(
                'Lambda関数の環境変数を更新しました',
                "関数名: {$functionName}, 環境変数: {$envKey} = {$envValue}",
            );

        } catch (\Exception $e) {
            Log::error('Lambda updateEnvironmentVariableAndRedeploy Error: ' . $e->getMessage());

            $this->sendDangerNotification(
                'Lambda関数の環境変数の更新に失敗しました',
                "関数名: {$functionName}, 環境変数: {$envKey} = {$envValue}, エラー: {$e->getMessage()}",
            );

            throw $e;
        }
    }
}
