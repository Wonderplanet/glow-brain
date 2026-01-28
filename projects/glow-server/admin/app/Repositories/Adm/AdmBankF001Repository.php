<?php

declare(strict_types=1);

namespace App\Repositories\Adm;

use App\Models\Adm\AdmBankF001;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class AdmBankF001Repository
{
    public function createModel(
        string $fluentdTag,
        string $version,
        string $eventId,
        string $eventTime,
        string $appId,
        string $appUserId,
        string $appSystemPrefix,
        string $userId,
        string $personId,
        string $mbid,
        string $ktid,
        string $platformId,
        string $platformVersion,
        string $platformUserId,
        string $userAgent,
        string $createdTime,
        string $countryCode,
        string $adId
    ): AdmBankF001 {

        $model = new AdmBankF001();
        $model->setFluentdTag($fluentdTag);
        $model->setVersion($version);
        $model->setEventId($eventId);
        $model->setEventTime($eventTime);
        $model->setAppId($appId);
        $model->setAppUserId($appUserId);
        $model->setAppSystemPrefix($appSystemPrefix);
        $model->setUserId($userId);
        $model->setPersonId($personId);
        $model->setMbid($mbid);
        $model->setKtid($ktid);
        $model->setPlatformId($platformId);
        $model->setPlatformVersion($platformVersion);
        $model->setPlatformUserId($platformUserId);
        $model->setUserAgent($userAgent);
        $model->setCreatedTime($createdTime);
        $model->setCountryCode($countryCode);
        $model->setAdId($adId);
        return $model;
    }

    /**
     * @param Collection<AdmBankF001> $models
     * @param CarbonImmutable $now
     * @return bool
     */
    public function bulkInsert(Collection $models, CarbonImmutable $now): bool
    {
        $inputs = $models->map(fn ($row) => [
            'id' => (string) Uuid::uuid4(),
            'fluentd_tag' => $row->getFluentdTag(),
            'version' => $row->getVersion(),
            'event_id' => $row->getEventId(),
            'event_time' => $row->getEventTime(),
            'app_id' => $row->getAppId(),
            'app_user_id' => $row->getAppUserId(),
            'app_system_prefix' => $row->getAppSystemPrefix(),
            'user_id' => $row->getUserId(),
            'person_id' => $row->getPersonId(),
            'mbid' => $row->getMbid(),
            'ktid' => $row->getKtid(),
            'platform_id' => $row->getPlatformId(),
            'platform_version' => $row->getPlatformVersion(),
            'platform_user_id' => $row->getPlatformUserId(),
            'user_agent' => $row->getUserAgent(),
            'created_time' => $row->getCreatedTime(),
            'country_code' => $row->getCountryCode(),
            'ad_id' => $row->getAdId(),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ])->toArray();

        return AdmBankF001::query()->insert($inputs);
    }
}
