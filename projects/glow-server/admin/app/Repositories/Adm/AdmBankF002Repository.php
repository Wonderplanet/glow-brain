<?php

declare(strict_types=1);

namespace App\Repositories\Adm;

use App\Models\Adm\AdmBankF002;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class AdmBankF002Repository
{
    public function createModel(
        string $fluentdTag,
        string $version,
        string $appId,
        string $appUserId,
        string $appSystemPrefix,
        string $platformId,
        int $buyCoin,
        float $buyAmount,
        int $payCoin,
        float $payAmount,
        float $directAmount,
        float $subscriptionAmount,
        string $itemId,
        string $insertTime,
        string $countryCode,
        string $currencyCode,
    ): AdmBankF002 {

        $model = new AdmBankF002();
        $model->setFluentdTag($fluentdTag);
        $model->setVersion($version);
        $model->setAppId($appId);
        $model->setAppUserId($appUserId);
        $model->setAppSystemPrefix($appSystemPrefix);
        $model->setPlatformId($platformId);
        $model->setBuyCoin($buyCoin);
        $model->setBuyAmount($buyAmount);
        $model->setPayCoin($payCoin);
        $model->setPayAmount($payAmount);
        $model->setDirectAmount($directAmount);
        $model->setSubscriptionAmount($subscriptionAmount);
        $model->setItemId($itemId);
        $model->setInsertTime($insertTime);
        $model->setCountryCode($countryCode);
        $model->setCurrencyCode($currencyCode);
        return $model;
    }

    /**
     * @param Collection<AdmBankF002> $models
     * @return bool
     */
    public function bulkInsert(Collection $models, CarbonImmutable $now): bool
    {
        $inputs = $models->map(fn ($row) => [
            'id' => (string) Uuid::uuid4(),
            'fluentd_tag' => $row->getFluentdTag(),
            'version' => $row->getVersion(),
            'app_id' => $row->getAppId(),
            'app_user_id' => $row->getAppUserId(),
            'app_system_prefix' => $row->getAppSystemPrefix(),
            'platform_id' => $row->getPlatformId(),
            'buy_coin' => $row->getBuyCoin(),
            'buy_amount' => $row->getBuyAmount(),
            'pay_coin' => $row->getPayCoin(),
            'pay_amount' => $row->getPayAmount(),
            'direct_amount' => $row->getDirectAmount(),
            'subscription_amount' => $row->getSubscriptionAmount(),
            'item_id' => $row->getItemId(),
            'insert_time' => $row->getInsertTime(),
            'country_code' => $row->getCountryCode(),
            'currency_code' => $row->getCurrencyCode(),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ])->toArray();

        return AdmBankF002::query()->insert($inputs);
    }
}
