<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use WonderPlanet\Domain\Billing\Models\LogAllowance;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;

/**
 * 購入許可ログを管理するRepository
 */
class LogAllowanceRepository
{
    /**
     * 購入許可ログを登録する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $productId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $deviceId
     * @param Trigger $trigger
     * @return string
     */
    public function insertAllowanceLog(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $productId,
        string $mstStoreProductId,
        string $productSubId,
        string $deviceId,
        Trigger $trigger
    ): string {
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();

        $logAllowance = new LogAllowance();

        $logAllowance->usr_user_id = $userId;
        $logAllowance->os_platform = $osPlatform;
        $logAllowance->billing_platform = $billingPlatform;
        $logAllowance->device_id = $deviceId;
        $logAllowance->product_id = $productId;
        $logAllowance->mst_store_product_id = $mstStoreProductId;
        $logAllowance->product_sub_id = $productSubId;
        $logAllowance->trigger_type = $trigger->triggerType;
        $logAllowance->trigger_id = $trigger->triggerId;
        $logAllowance->trigger_name = $trigger->triggerName;
        $logAllowance->trigger_detail = $trigger->triggerDetail;
        $logAllowance->request_id_type = $requestUniqueIdData->getRequestIdType()->value;
        $logAllowance->request_id = $requestUniqueIdData->getRequestId();
        $logAllowance->nginx_request_id = CurrencyCommon::getFrontRequestId();

        $logAllowance->save();

        return $logAllowance->id;
    }

    /**
     * IDから購入許可ログを取得する
     *
     * @param string $id
     * @return LogAllowance|null
     */
    public function findById(string $id): ?LogAllowance
    {
        return LogAllowance::query()
            ->find($id);
    }

    /**
     * ユーザーの購入許可ログを取得する
     *
     * @param string $userId
     * @return array<LogAllowance>
     */
    public function findByUserId(string $userId): array
    {
        return LogAllowance::query()
            ->where('usr_user_id', $userId)
            ->get()
            ->all();
    }

    /**
     * ユーザーの購入許可ログを全て削除する
     *
     * @param string $userId
     * @return void
     */
    public function deleteAllByUserId(string $userId): void
    {
        LogAllowance::query()
            ->where('usr_user_id', $userId)
            ->delete();
    }
}
