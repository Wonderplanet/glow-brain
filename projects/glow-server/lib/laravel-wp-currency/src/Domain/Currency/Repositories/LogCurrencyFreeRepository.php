<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Entities\LogCurrencyFreeInsertEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;

/**
 * 無償一次通貨のリポジトリ
 */
class LogCurrencyFreeRepository
{
    /**
     * 無償一次通貨のログを追加する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param integer $beforeIngameAmount
     * @param integer $beforeBonusAmount
     * @param integer $beforeRewardAmount
     * @param integer $changeIngameAmount
     * @param integer $changeBonusAmount
     * @param integer $changeRewardAmount
     * @param integer $currentIngameAmount
     * @param integer $currentBonusAmount
     * @param integer $currentRewardAmount
     * @param Trigger $trigger
     *
     * @return string 作成したログのID
     */
    public function insertFreeLog(
        string $userId,
        string $osPlatform,
        int $beforeIngameAmount,
        int $beforeBonusAmount,
        int $beforeRewardAmount,
        int $changeIngameAmount,
        int $changeBonusAmount,
        int $changeRewardAmount,
        int $currentIngameAmount,
        int $currentBonusAmount,
        int $currentRewardAmount,
        Trigger $trigger
    ): string {
        // 複数登録と処理がずれないように、bulkInsertFreeLogsを使用
        $entitiy = new LogCurrencyFreeInsertEntity(
            $beforeIngameAmount,
            $beforeBonusAmount,
            $beforeRewardAmount,
            $changeIngameAmount,
            $changeBonusAmount,
            $changeRewardAmount,
            $currentIngameAmount,
            $currentBonusAmount,
            $currentRewardAmount,
            $trigger
        );

        return $this->bulkInsertFreeLogs($userId, $osPlatform, [$entitiy])[0];
    }

    /**
     * 無償一次通貨のログを複数追加する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param array<LogCurrencyFreeInsertEntity> $insertEntities
     * @return array<string> 作成したログのID
     */
    public function bulkInsertFreeLogs(
        string $userId,
        string $osPlatform,
        array $insertEntities,
    ): array {
        // 登録用の日付を取得
        // Eloquentモデルで生成される時刻とずれないよう、空のモデルを生成して取得する
        $now = (new LogCurrencyFree())->freshTimestamp();

        // seq_noの取得
        $loggingNo = LogCurrencyFree::createLoggingNo();

        $data = [];
        $logIds = [];

        // リクエストID
        $requestIdData = CurrencyCommon::getRequestUniqueIdData();
        $frontRequestId = CurrencyCommon::getFrontRequestId();

        /** @var LogCurrencyFreeInsertEntity $insertEntity */
        foreach ($insertEntities as $insertEntity) {
            // insert用のデータを作成する
            $id = LogCurrencyFree::generateId();
            $logIds[] = $id;
            $data[] = [
                'logging_no' => $loggingNo,
                'usr_user_id' => $userId,
                'os_platform' => $osPlatform,
                'before_ingame_amount' => $insertEntity->beforeIngameAmount,
                'before_bonus_amount' => $insertEntity->beforeBonusAmount,
                'before_reward_amount' => $insertEntity->beforeRewardAmount,
                'change_ingame_amount' => $insertEntity->changeIngameAmount,
                'change_bonus_amount' => $insertEntity->changeBonusAmount,
                'change_reward_amount' => $insertEntity->changeRewardAmount,
                'current_ingame_amount' => $insertEntity->currentIngameAmount,
                'current_bonus_amount' => $insertEntity->currentBonusAmount,
                'current_reward_amount' => $insertEntity->currentRewardAmount,
                'trigger_type' => $insertEntity->trigger->triggerType,
                'trigger_id' => $insertEntity->trigger->triggerId,
                'trigger_name' => $insertEntity->trigger->triggerName,
                'trigger_detail' => $insertEntity->trigger->triggerDetail,
                'request_id_type' => $requestIdData->getRequestIdType()->value,
                'request_id' => $requestIdData->getRequestId(),
                'nginx_request_id' => $frontRequestId,

                // Eloquentモデルで生成される部分がスキップされてしまうため、ここで生成
                'id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            // シーケンス番号をインクリメント
            $loggingNo++;
        }

        // 登録はbluk insertで行う
        LogCurrencyFree::query()->insert($data);

        // 登録したログのIDを返す
        return $logIds;
    }

    /**
     * ユーザーIDのログを返す
     *
     * @param string $userId
     * @return array<LogCurrencyFree>
     */
    public function findByUserId(string $userId): array
    {
        return LogCurrencyFree::query()
            ->where('usr_user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->orderBy('logging_no', 'asc')
            ->get()
            ->all();
    }

    /**
     * 複数のIDから無償一次通貨ログを取得する
     *
     * @param array<string> $ids
     * @return array<LogCurrencyFree>
     */
    public function findByIds(array $ids): array
    {
        return LogCurrencyFree::query()
            ->whereIn('id', $ids)
            ->get()
            ->all();
    }
}
