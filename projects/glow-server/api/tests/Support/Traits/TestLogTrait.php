<?php

declare(strict_types=1);

namespace Tests\Support\Traits;

use App\Domain\Emblem\Models\LogEmblem;
use App\Domain\Item\Models\LogItem;
use App\Domain\Resource\Constants\LogConstant;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\User\Models\LogCoin;
use App\Domain\User\Models\LogExp;
use App\Domain\User\Models\LogStamina;
use DB;

trait TestLogTrait
{
    protected function checkLogResourcesByGet(
        string $usrUserId,
        RewardType $rewardType,
        string $nginxRequestId,
        array $expectedAmounts,
        array $expectedTriggers,
    ): void {
        $expectedLogCount = count($expectedTriggers);

        $logModelClass = null;
        $resourceIdKey = null;
        switch ($rewardType) {
            case RewardType::COIN:
                $logModelClass = LogCoin::class;
                break;
            case RewardType::STAMINA:
                $logModelClass = LogStamina::class;
                break;
            case RewardType::EXP:
                $logModelClass = LogExp::class;
                break;
            case RewardType::EMBLEM:
                $logModelClass = LogEmblem::class;
                $resourceIdKey = 'mst_emblem_id';
                break;
            case RewardType::ITEM:
                $logModelClass = LogItem::class;
                $resourceIdKey = 'mst_item_id';
                break;
            default:
                $this->fail('未対応のRewardTypeです');
        }
        $query = $logModelClass::query()
            ->where('usr_user_id', $usrUserId);
        if ($rewardType !== RewardType::EMBLEM) {
            // エンブレムは獲得のみでaction_type列がないため指定不要
            $query->where('action_type', LogResourceActionType::GET->value);
        }
        $logModels = $query->get();

        // ログ数が一致するかチェック
        $this->assertCount($expectedLogCount, $logModels);

        // nginx_request_idが一致していることを確認
        $this->assertCount(1, $logModels->pluck('nginx_request_id')->unique());
        $this->assertEquals($nginxRequestId, $logModels->first()->nginx_request_id);

        // action_typeが全てGetであることを確認
        // emblemを消費することはないので、Getの確認は不要
        if ($rewardType !== RewardType::EMBLEM) {
            $this->assertCount(
                $expectedLogCount,
                $logModels->where('action_type', LogResourceActionType::GET->value),
            );
        }

        // 変動前後の量が一致するかレコード単位で順不同でチェック
        switch ($rewardType) {
            case RewardType::COIN:
            case RewardType::STAMINA:
            case RewardType::EXP:
                $logModelAttributes = $logModels->map(function ($logModel) {
                    return [
                        'before_amount' => $logModel->before_amount,
                        'after_amount' => $logModel->after_amount,
                    ];
                })->toArray();
                $this->assertEqualsCanonicalizing(
                    expected: $expectedAmounts,
                    actual: $logModelAttributes,
                    message: json_encode($logModelAttributes, JSON_PRETTY_PRINT),
                );
                break;
            case RewardType::ITEM:
            case RewardType::EMBLEM:

                $groupedLogModels = $logModels->groupBy(function ($logModel) use ($resourceIdKey) {
                    return $logModel->$resourceIdKey;
                })->toArray();

                foreach ($expectedAmounts as $resourceId => $expectedAmountList) {
                    $logModelsByResourceId = $groupedLogModels[$resourceId] ?? null;
                    if ($logModelsByResourceId === null) {
                        $this->fail('リソースID(' . $resourceId . ')のログが存在しません');
                    }

                    switch ($rewardType) {
                        case RewardType::ITEM:
                            $logModelAttributes = array_map(function ($logModel) {
                                return [
                                    'before_amount' => $logModel['before_amount'],
                                    'after_amount' => $logModel['after_amount'],
                                ];
                            }, $logModelsByResourceId);
                            break;
                        case RewardType::EMBLEM:
                            $logModelAttributes = array_map(function ($logModel) {
                                return [
                                    'before_amount' => 0,
                                    // 'after_amount' => $logModel['amount'],
                                    'after_amount' => 1,
                                ];
                            }, $logModelsByResourceId);
                            break;
                    }

                    $this->assertEqualsCanonicalizing(
                        expected: $expectedAmountList,
                        actual: $logModelAttributes,
                        message: json_encode($logModelAttributes, JSON_PRETTY_PRINT),
                    );
                }

                break;
        }

        // 各列の値が一致するかレコード単位で順不同でチェック
        $this->assertEqualsCanonicalizing(
            $expectedTriggers,
            $logModels->map(function ($logModel) {
                return [
                    'trigger_source' => $logModel->trigger_source,
                    'trigger_value' => $logModel->trigger_value,
                    'trigger_option' => $logModel->trigger_option,
                ];
            })->toArray()
        );
    }

    protected function checkLogResourcesByUse(
        string $usrUserId,
        RewardType $rewardType,
        string $nginxRequestId,
        array $expectedAmounts,
        array $expectedTriggers,
    ): void {
        $expectedLogCount = count($expectedTriggers);

        $logModelClass = null;
        $resourceIdKey = null;
        switch ($rewardType) {
            case RewardType::COIN:
                $logModelClass = LogCoin::class;
                break;
            case RewardType::STAMINA:
                $logModelClass = LogStamina::class;
                break;
            case RewardType::EXP:
                $logModelClass = LogExp::class;
                break;
            case RewardType::ITEM:
                $logModelClass = LogItem::class;
                $resourceIdKey = 'mst_item_id';
                break;
            default:
                $this->fail('未対応のRewardTypeです');
        }
        $logModels = $logModelClass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('action_type', LogResourceActionType::USE->value)
            ->get();

        // ログ数が一致するかチェック
        $this->assertCount($expectedLogCount, $logModels);

        // nginx_request_idが一致していることを確認
        $this->assertCount(1, $logModels->pluck('nginx_request_id')->unique());
        $this->assertEquals($nginxRequestId, $logModels->first()->nginx_request_id);

        // action_typeが全てUseであることを確認
        $this->assertCount(
            $expectedLogCount,
            $logModels->where('action_type', LogResourceActionType::USE->value),
        );

        // 変動前後の量が一致するかレコード単位で順不同でチェック
        switch ($rewardType) {
            case RewardType::COIN:
            case RewardType::STAMINA:
            case RewardType::EXP:
                $logModelAttributes = $logModels->map(function ($logModel) {
                    return [
                        'before_amount' => $logModel->before_amount,
                        'after_amount' => $logModel->after_amount,
                    ];
                })->toArray();
                $this->assertEqualsCanonicalizing(
                    expected: $expectedAmounts,
                    actual: $logModelAttributes,
                    message: json_encode($logModelAttributes, JSON_PRETTY_PRINT),
                );
                break;
            case RewardType::ITEM:
                $groupedLogModels = $logModels->groupBy(function ($logModel) use ($resourceIdKey) {
                    return $logModel->$resourceIdKey;
                })->toArray();

                foreach ($expectedAmounts as $resourceId => $expectedAmountList) {
                    $logModelsByResourceId = $groupedLogModels[$resourceId] ?? null;
                    if ($logModelsByResourceId === null) {
                        $this->fail('リソースID(' . $resourceId . ')のログが存在しません');
                    }

                    $logModelAttributes = array_map(function ($logModel) {
                        return [
                            'before_amount' => $logModel['before_amount'],
                            'after_amount' => $logModel['after_amount'],
                        ];
                    }, $logModelsByResourceId);

                    $this->assertEqualsCanonicalizing(
                        expected: $expectedAmountList,
                        actual: $logModelAttributes,
                        message: json_encode($logModelAttributes, JSON_PRETTY_PRINT),
                    );
                }

                break;
        }

        // 各列の値が一致するかレコード単位で順不同でチェック
        $this->assertEqualsCanonicalizing(
            $expectedTriggers,
            $logModels->map(function ($logModel) {
                return [
                    'trigger_source' => $logModel->trigger_source,
                    'trigger_value' => $logModel->trigger_value,
                    'trigger_option' => $logModel->trigger_option,
                ];
            })->toArray()
        );
    }

    /**
     * 全logテーブルを通してlogging_noが連番になっていることを確認
     */
    protected function checkLoggingNo(string $usrUserId, int $totalRecordCount): void
    {
        // 'log_'で始まるテーブル名を取得
        $tables = DB::select("SHOW TABLES LIKE 'log_%'");

        // テーブル名の取得結果を整形
        $tableNames = array_map(function ($table) {
            return array_values((array)$table)[0];
        }, $tables);
        // log_api_requestsと課金基盤のテーブルを除外
        $tableNames = array_filter($tableNames, function ($tableName) {
            return $tableName !== 'log_api_requests'
                && $tableName !== 'log_allowances'
                && $tableName !== 'log_stores'
                && !preg_match('/log_currency_/', $tableName);
        });

        $loggingNoList = collect();

        // 各テーブルに対してwhere句を適用
        foreach ($tableNames as $tableName) {
            $query = DB::table($tableName);

            // logging_no列がある場合のみwhere句を適用
            if (!DB::getSchemaBuilder()->hasColumn($tableName, 'logging_no')) {
                continue;
            }

            $query->where('usr_user_id', $usrUserId);

            $queryResults = $query->get();

            // logging_noをloggingNoListに追加
            $loggingNoList = $loggingNoList->merge($queryResults->pluck('logging_no'));
        }

        $maxNo = $loggingNoList->max();
        $minNo = $loggingNoList->min();

        $this->assertCount($totalRecordCount, $loggingNoList);
        $this->assertEquals(LogConstant::LOGGING_NO_INITAL_VALUE, $minNo);
        $this->assertEquals($totalRecordCount, $maxNo + 1);

        // ログ番号の連番チェック
        $this->assertEquals(
            range(LogConstant::LOGGING_NO_INITAL_VALUE, $totalRecordCount - 1),
            $loggingNoList->sort()->values()->toArray()
        );
    }
}
