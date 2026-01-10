<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Repositories;

use App\Domain\Resource\Log\Models\LogBank;
use Carbon\CarbonImmutable;

class LogBankRepository extends LogModelRepository
{
    protected string $modelClass = LogBank::class;

    public function create(
        string $usrUserId,
        string $eventId,
        string $platformUserId,
        CarbonImmutable $userFirstCreatedAt,
        string $userAgent,
        int $osPlatform,
        string $osVersion,
        string $countryCode,
        string $adId,
        CarbonImmutable $requestAt,
    ): void {
        $model = new LogBank();

        $model->setUsrUserId($usrUserId);
        $model->setEventId($eventId);
        $model->setPlatformUserId($platformUserId);
        $model->setUserFirstCreatedAt($userFirstCreatedAt);
        $model->setUserAgent($userAgent);
        $model->setOsPlatform($osPlatform);
        $model->setOsVersion($osVersion);
        $model->setCountryCode($countryCode);
        $model->setAdId($adId);
        $model->setRequestAt($requestAt);

        $this->addModel($model);
    }
}
