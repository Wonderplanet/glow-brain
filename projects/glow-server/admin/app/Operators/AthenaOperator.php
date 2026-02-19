<?php

namespace App\Operators;

use App\Constants\AthenaConstant;
use App\Entities\Athena\AthenaQueryResultEntity;
use App\Services\AwsCredentialProvider;
use App\Traits\NotificationTrait;
use Aws\Athena\AthenaClient;
use Illuminate\Support\Facades\Log;

class AthenaOperator
{
    use NotificationTrait;

    public function __construct()
    {
    }

    /**
     * Athenaクライアントを生成する
     * configにkey, secretが指定されていない場合はECS/EC2ロールを使用する
     *
     * @param array $config
     * @return AthenaClient
     */
    private function getClient(array $config): AthenaClient
    {
        $credentials = [
            'key' => $config['key'] ?? null,
            'secret' => $config['secret'] ?? null,
        ];

        if (is_null($credentials['key']) || is_null($credentials['secret'])) {
            $credentials = AwsCredentialProvider::getRoleBasedCredentialProvider();
        }

        return new AthenaClient([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);
    }

    /**
     * Athenaでクエリを実行し、結果を取得する
     *
     * @param array $config 設定配列（region, database, workgroup?, output_location?, key?, secret?）
     * @param string $query SQLクエリ
     * @param int|null $maxResults 最大結果件数（デフォルト: 1000）
     * @return array|null クエリ結果の配列、失敗時はnull
     */
    public function executeQuery(
        array $config,
        string $query,
        ?int $maxResults = null,
    ): ?AthenaQueryResultEntity {
        $client = $this->getClient($config);

        try {
            // クエリ実行パラメータを構築
            $params = [
                'QueryString' => $query,
                'QueryExecutionContext' => [
                    'Database' => $config['database'],
                ],
                'ResultReuseConfiguration' => [
                    'ResultReuseByAgeConfiguration' => [
                        'Enabled' => true,
                        'MaxAgeInMinutes' => AthenaConstant::ATHENA_QUERY_RESULT_REUSE_MAX_AGE_MINUTES,
                    ],
                ],
            ];

            // workgroupが指定されている場合
            if (!empty($config['workgroup'])) {
                $params['WorkGroup'] = $config['workgroup'];
            }

            // クエリを実行
            $result = $client->startQueryExecution($params);

            $queryExecutionId = $result['QueryExecutionId'];

            // クエリの完了を待つ
            $executionResult = $this->waitForQueryCompletion($client, $queryExecutionId);

            if (!$executionResult) {
                return null;
            }

            if (is_null($maxResults)) {
                $maxResults = AthenaConstant::ATHENA_MAX_RESULTS_PER_PAGE;
            } else {
                $maxResults = min($maxResults, AthenaConstant::ATHENA_MAX_RESULTS_PER_PAGE);
            }

            // 結果を取得
            return $this->getQueryResults(
                $client,
                $queryExecutionId,
                $maxResults,
            );

        } catch (\Exception $e) {
            Log::error('Athena executeQuery Error: ' . $e->getMessage(), [
                'query' => $query,
            ]);
            $this->sendDangerNotification('Athena executeQuery Error', $e->getMessage());
            return null;
        }
    }

    /**
     * 一定間隔でポーリングしてクエリの完了を待つ
     *
     * @param AthenaClient $client
     * @param string $queryExecutionId
     * @return bool 成功時true、失敗時false
     */
    private function waitForQueryCompletion(AthenaClient $client, string $queryExecutionId): bool
    {
        $startTime = time();

        while (time() - $startTime < AthenaConstant::ATHENA_QUERY_POLL_MAX_WAIT_TIME) {
            try {
                $result = $client->getQueryExecution([
                    'QueryExecutionId' => $queryExecutionId,
                ]);

                $state = $result['QueryExecution']['Status']['State'];

                if ($state === AthenaConstant::ATHENA_QUERY_EXECUTION_STATE_SUCCEEDED) {
                    return true;
                }

                if (in_array($state, [AthenaConstant::ATHENA_QUERY_EXECUTION_STATE_FAILED, AthenaConstant::ATHENA_QUERY_EXECUTION_STATE_CANCELLED])) {
                    $reason = $result['QueryExecution']['Status']['StateChangeReason'] ?? 'Unknown error';
                    Log::error('Athena Query Failed: ' . $reason, [
                        'queryExecutionId' => $queryExecutionId,
                        'state' => $state,
                    ]);
                    $this->sendDangerNotification('Athena Query Failed', $reason);
                    return false;
                }

                // まだ実行中の場合は待機
                sleep(AthenaConstant::ATHENA_QUERY_POLL_INTERVAL_SECONDS);

            } catch (\Exception $e) {
                Log::error('Athena getQueryExecution Error: ' . $e->getMessage(), [
                    'queryExecutionId' => $queryExecutionId,
                ]);
                $this->sendDangerNotification('Athena getQueryExecution Error', $e->getMessage());
                return false;
            }
        }

        // タイムアウト
        Log::error('Athena Query Timeout', [
            'queryExecutionId' => $queryExecutionId,
            'maxWaitTime' => AthenaConstant::ATHENA_QUERY_POLL_MAX_WAIT_TIME,
        ]);
        $this->sendDangerNotification(
            'Athena Query Timeout',
            "Query execution timed out after " . AthenaConstant::ATHENA_QUERY_POLL_MAX_WAIT_TIME . " seconds",
        );
        return false;
    }

    /**
     * クエリ結果を取得する
     *
     * @param AthenaClient $client
     * @param string $queryExecutionId
     * @param int $maxResults
     * @return AthenaQueryResultEntity|null クエリ結果のエンティティ、失敗時はnull
     */
    private function getQueryResults(
        AthenaClient $client,
        string $queryExecutionId,
        int $maxResults,
    ): ?AthenaQueryResultEntity {
        try {
            $result = $client->getQueryResults([
                'QueryExecutionId' => $queryExecutionId,
                'MaxResults' => $maxResults,
            ]);

            $rows = $result['ResultSet']['Rows'] ?? [];

            if (count($rows) === 0) {
                return AthenaQueryResultEntity::createEmpty();
            }

            // ヘッダー行を取得
            $headers = [];
            if (!empty($rows)) {
                $headerRow = array_shift($rows);
                foreach ($headerRow['Data'] as $column) {
                    $headers[] = $column['VarCharValue'] ?? '';
                }
            }

            // データ行を処理
            $formattedResults = [];
            foreach ($rows as $row) {
                $rowData = [];
                foreach ($row['Data'] as $index => $column) {
                    $columnName = $headers[$index] ?? "column_$index";
                    $rowData[$columnName] = $column['VarCharValue'] ?? null;
                }
                $formattedResults[] = $rowData;
            }

            return new AthenaQueryResultEntity(
                headers: $headers,
                rows: $formattedResults,
                totalRows: count($formattedResults),
            );

        } catch (\Exception $e) {
            Log::error('Athena getQueryResults Error: ' . $e->getMessage(), [
                'queryExecutionId' => $queryExecutionId,
            ]);
            $this->sendDangerNotification('Athena getQueryResults Error', $e->getMessage());
            return null;
        }
    }
}
