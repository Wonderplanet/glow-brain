<?php

declare(strict_types=1);

namespace App\Repositories\Adm;

use App\Models\Adm\AdmBankF003;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class AdmBankF003Repository
{
    public function createModel(
        string $appId,
        string $platformId,
        int $date,
        float $totalSales,
        string $data,
        float $directTotalSales,
        string $directData,
        float $subscriptionTotalSales,
        string $subscriptionData,
    ): AdmBankF003 {

        $model = new AdmBankF003();
        $model->setAppId($appId);
        $model->setPlatformId($platformId);
        $model->setDate($date);
        $model->setTotalSales($totalSales);
        $model->setData($data);
        $model->setDirectTotalSales($directTotalSales);
        $model->setDirectData($directData);
        $model->setSubscriptionTotalSales($subscriptionTotalSales);
        $model->setSubscriptionData($subscriptionData);
        return $model;
    }

    /**
     * @param Collection<AdmBankF003> $models
     * @return bool
     */
    public function bulkInsert(Collection $models, CarbonImmutable $now): bool
    {
        $inputs = $models->map(fn ($row) => [
            'id' => (string) Uuid::uuid4(),
            'app_id' => $row->getAppId(),
            'platform_id' => $row->getPlatformId(),
            'date' => $row->getDate(),
            'total_sales' => $row->getTotalSales(),
            'data' => $row->getData(),
            'direct_total_sales' => $row->getDirectTotalSales(),
            'direct_data' => $row->getDirectData(),
            'subscription_total_sales' => $row->getSubscriptionTotalSales(),
            'subscription_data' => $row->getSubscriptionData(),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ])->toArray();

        return AdmBankF003::query()->insert($inputs);
    }
}
