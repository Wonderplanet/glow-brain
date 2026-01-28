<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Illuminate\Support\Carbon;
use WonderPlanet\Domain\Currency\Entities\CurrencyRevertTrigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory;

/**
 * 一次通貨の返却を行なった際のログを管理するリポジトリ
 */
class LogCurrencyRevertHistoryRepository
{
    /**
     * ログを追加する
     *
     * @param string $userId
     * @param string $comment
     * @param string $logTriggerType
     * @param string $logTriggerId
     * @param string $logTriggerName
     * @param string $logTriggerDetail
     * @param string $logRequestIdType
     * @param string $logRequestId
     * @param string $logCreatedAt
     * @param integer $logChangePaidAmount
     * @param integer $logChangeFreeAmount
     * @param CurrencyRevertTrigger $trigger
     * @return string
     */
    public function insertRevertHistoryLog(
        string $userId,
        string $comment,
        string $logTriggerType,
        string $logTriggerId,
        string $logTriggerName,
        string $logTriggerDetail,
        string $logRequestIdType,
        string $logRequestId,
        string $logCreatedAt,
        int $logChangePaidAmount,
        int $logChangeFreeAmount,
        CurrencyRevertTrigger $trigger,
    ): string {
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();

        $logCurrencyRevert = new LogCurrencyRevertHistory();
        $logCurrencyRevert->usr_user_id = $userId;
        $logCurrencyRevert->comment = $comment;
        $logCurrencyRevert->log_trigger_type = $logTriggerType;
        $logCurrencyRevert->log_trigger_id = $logTriggerId;
        $logCurrencyRevert->log_trigger_name = $logTriggerName;
        $logCurrencyRevert->log_trigger_detail = $logTriggerDetail;
        $logCurrencyRevert->log_request_id_type = $logRequestIdType;
        $logCurrencyRevert->log_request_id = $logRequestId;
        $logCurrencyRevert->log_created_at = new Carbon($logCreatedAt);
        $logCurrencyRevert->log_change_paid_amount = $logChangePaidAmount;
        $logCurrencyRevert->log_change_free_amount = $logChangeFreeAmount;
        $logCurrencyRevert->trigger_type = $trigger->triggerType;
        $logCurrencyRevert->trigger_id = $trigger->triggerId;
        $logCurrencyRevert->trigger_name = $trigger->triggerName;
        $logCurrencyRevert->trigger_detail = $trigger->triggerDetail;
        $logCurrencyRevert->request_id_type = $requestUniqueIdData->getRequestIdType()->value;
        $logCurrencyRevert->request_id = $requestUniqueIdData->getRequestId();
        $logCurrencyRevert->nginx_request_id = CurrencyCommon::getFrontRequestId();
        $logCurrencyRevert->save();

        return $logCurrencyRevert->id;
    }
}
