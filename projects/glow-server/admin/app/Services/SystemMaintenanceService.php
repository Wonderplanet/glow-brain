<?php

namespace App\Services;

use App\Constants\SystemConstants;
use App\Operators\DynamoDbOperator;
use App\Operators\EventBridgeSchedulerOperator;
use App\Operators\LambdaOperator;
use Carbon\CarbonImmutable;

class SystemMaintenanceService
{
    public function __construct(
        private ConfigGetService $configGetService,
        private DynamoDbOperator $dynamoDbOperator,
        private EventBridgeSchedulerOperator $eventBridgeSchedulerOperator,
        private LambdaOperator $lambdaOperator,
    ) {
    }

    /**
     * メンテナンスデータの登録
     */
    public function create(CarbonImmutable $startAt, CarbonImmutable $endAt, string $text): void
    {
        $this->dynamoDbOperator->putItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            item: [
                'PK' => [
                    'S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK,
                ],
                'SK' => [
                    'S' => strval($endAt->getTimestamp()),
                ],
                'start_at' => [
                    'N' => strval($startAt->getTimestamp())
                ],
                'end_at' => [
                    'N' => strval($endAt->getTimestamp())
                ],
                'text' => [
                    'S' => $text
                ],
                'is_valid' => [
                    'BOOL' => false,
                ],
            ]
        );
    }


    /**
     * メンテナンスを有効状態にする
     */
    public function available(string $SK): bool
    {
        return $this->updateIsValid($SK, true);
    }


    /**
     * メンテナンスを無効状態にする
     * @param string $SK
     * @return bool
     */
    public function unavailable(string $SK): bool
    {
        return $this->updateIsValid($SK, false);
    }


    /**
     * 有効・無効フラグを更新する
     */
    private function updateIsValid(string $SK, bool $isValid): bool
    {
        return $this->dynamoDbOperator->updateItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            key: [
                'PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                'SK' => ['S' => $SK],
            ],
            updateExpression: 'SET #NV = :NV',
            expressionAttributeNames: [
                '#NV' => 'is_valid',
            ],
            expressionAttributeValues: [
                ':NV' => [ 'BOOL' => $isValid ],
            ],
        );
    }

    /**
     * 終了時刻を更新する
     */
    public function updateEndAt(string $SK, CarbonImmutable $endAt): bool
    {
        return $this->dynamoDbOperator->updateItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            key: [
                'PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                'SK' => ['S' => $SK],
            ],
            updateExpression: 'SET #endAt = :endAt',
            expressionAttributeNames: [
                '#endAt' => 'end_at',
            ],
            expressionAttributeValues: [
                ':endAt' => [ 'N' => strval($endAt->getTimestamp()) ],
            ],
        );
    }

    /**
     * メンテナンスデータのメンテ期間とテキストを更新する
     */
    public function updatePeriodAndText(string $SK, CarbonImmutable $startAt, CarbonImmutable $endAt, string $text): bool
    {
        return $this->dynamoDbOperator->updateItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            key: [
                'PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                'SK' => ['S' => $SK],
            ],
            updateExpression: 'SET #startAt = :startAt, #endAt = :endAt, #text = :text',
            expressionAttributeNames: [
                '#startAt' => 'start_at',
                '#endAt' => 'end_at',
                '#text' => 'text',
            ],
            expressionAttributeValues: [
                ':startAt' => [ 'N' => strval($startAt->getTimestamp()) ],
                ':endAt' => [ 'N' => strval($endAt->getTimestamp()) ],
                ':text' => [ 'S' => $text ],
            ],
        );
    }

    /**
     * メンテナンスデータを削除する
     */
    public function delete(string $SK): bool
    {
        return $this->dynamoDbOperator->deleteItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            key: [
                'PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                'SK' => ['S' => $SK],
            ],
        );
    }

    /**
     * メンテナンスデータを取得する
     */
    public function getData(): array
    {
        $result = $this->dynamoDbOperator->query(
            config: $this->configGetService->getDynamoDbMaintenance(),
            keyConditionExpression: '#PK = :PK and #SK >= :SK',
            expressionAttributeNames: ['#PK' => 'PK', '#SK' => 'SK'],
            expressionAttributeValues: [
                ':PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                ':SK' => ['S' => '0'],
            ],
        );

        return $result['Items'] ?? [];
    }

    /**
     * 単一の指定したメンテナンスデータを取得する
     */
    public function getDataByKey(string $SK): array
    {
        $result = $this->dynamoDbOperator->getItem(
            config: $this->configGetService->getDynamoDbMaintenance(),
            key: [
                'PK' => ['S' => SystemConstants::MAINTENANCE_DYNAMODB_TABLE_PK],
                'SK' => ['S' => $SK],
            ],
        );

        return $result['Item'] ?? [];
    }

    /**
     * EventBridge Scheduler メンテ開始と終了のスケジュールを有効にする
     */
    public function enableSchedule(CarbonImmutable $start, CarbonImmutable $end): void
    {
        // 開始スケジューラ
        $startConfig = $this->configGetService->getEventBridgeStartSchedulerMaintenance();
        $startScheduleArgs = $this->eventBridgeSchedulerOperator->getSchedule(
            config: $startConfig,
        );
        $startScheduleArgs['ScheduleExpression'] = sprintf('at(%s)', $start->format(SystemConstants::DATETIME_ISO_FORMAT));
        $startScheduleArgs['ScheduleExpressionTimezone'] = SystemConstants::TIMEZONE_UTC;
        $startScheduleArgs['State'] = 'ENABLED';
        $this->eventBridgeSchedulerOperator->updateSchedule(
            config: $startConfig,
            args: $startScheduleArgs,
        );

        // 終了スケジューラ
        $endConfig = $this->configGetService->getEventBridgeEndSchedulerMaintenance();
        $endScheduleArgs = $this->eventBridgeSchedulerOperator->getSchedule(
            config: $endConfig,
        );
        $endScheduleArgs['ScheduleExpression'] = sprintf('at(%s)', $end->format(SystemConstants::DATETIME_ISO_FORMAT));
        $endScheduleArgs['ScheduleExpressionTimezone'] = SystemConstants::TIMEZONE_UTC;
        $endScheduleArgs['State'] = 'ENABLED';
        $this->eventBridgeSchedulerOperator->updateSchedule(
            config: $endConfig,
            args: $endScheduleArgs,
        );
    }

    /**
     * EventBridge Scheduler メンテ開始と終了のスケジュールを無効にする
     */
    public function disableSchedule(): void
    {
        // 開始スケジューラ
        $startConfig = $this->configGetService->getEventBridgeStartSchedulerMaintenance();
        $startScheduleArgs = $this->eventBridgeSchedulerOperator->getSchedule(
            config: $startConfig,
        );
        $startScheduleArgs['State'] = 'DISABLED';
        $this->eventBridgeSchedulerOperator->updateSchedule(
            config: $startConfig,
            args: $startScheduleArgs,
        );

        // 終了スケジューラ
        $endConfig = $this->configGetService->getEventBridgeEndSchedulerMaintenance();
        $endScheduleArgs = $this->eventBridgeSchedulerOperator->getSchedule(
            config: $endConfig,
        );
        $endScheduleArgs['State'] = 'DISABLED';
        $this->eventBridgeSchedulerOperator->updateSchedule(
            config: $endConfig,
            args: $endScheduleArgs,
        );
    }


    /**
     * メンテナンスを強制的に即時開始する
     *
     * - DynamoDB データ終了時刻を無期限に設定
     * - DynamoDB 動作フラグを有効な状態に設定
     * - Scheduler 開始 ステータスを DISABLE に設定
     * - Scheduler 終了 ステータスを DISABLE に設定
     * - ロードバランサー設定用の Lambda 関数を実行
     *
     * この機能を使ってメンテナンス状態にした場合、既存のスケジュール設定がある場合はそれが変更される
     * 終了時刻は実質無期限として実行時刻から一年後に設定されるため、メンテナンス終了時間を適当に設定するか
     * 強制停止機能の実行もしくは AWS コンソールを直接操作することによって停止させること
     */
    public function startMaintenanceImmediately(string $text): void
    {
        $startAt = CarbonImmutable::now(SystemConstants::TIMEZONE_UTC);
        $endAt = $startAt->addDays(365);


        // DynamoDB のデータを生成
        // TODO: 下記のvqコメントについて運用フロー確認しておく
        // 事前にメンテナンス設定データは削除しておく想定になっている
        $this->create(startAt: $startAt, endAt: $endAt, text: $text);
        $this->available(SK: strval($endAt->getTimestamp()));

        // メンテナンス設定を実行する Lambda 関数を呼び出す
        $this->invokeLambdaFunction();
    }



    /**
     * メンテナンスを強制的に即時終了する
     *
     * - DynamoDB データの終了時刻を現在時刻で設定
     * - ロードバランサー設定用の Lambda 関数を実行
     */
    public function stopMaintenanceImmediately(string $SK): void
    {
        // 現時刻が終了時刻となるように設定
        $endAt = CarbonImmutable::now(SystemConstants::TIMEZONE_UTC);
        $this->updateEndAt(
            SK: $SK,
            endAt: $endAt
        );

        // メンテナンス設定を実行する Lambda 関数を呼び出す
        $this->invokeLambdaFunction();
    }

    /**
     * メンテナンス中にするための Lambda 関数を呼び出す
     */
    public function invokeLambdaFunction(): void
    {
        $this->lambdaOperator->invokeFunction(
            config: $this->configGetService->getLambdaMaintenance(),
            payload: [],
        );
    }
}
