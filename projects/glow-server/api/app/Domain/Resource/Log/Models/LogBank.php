<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Models;

use App\Domain\Resource\Traits\HasFactory;
use Carbon\CarbonImmutable;

/**
 * @property string $event_id
 * @property string $platform_user_id
 * @property string $user_first_created_at
 * @property string $user_agent
 * @property int $os_platform
 * @property string $os_version
 * @property string $country_code
 * @property string $ad_id
 * @property string $request_at
 */
class LogBank extends LogModel
{
    use HasFactory;

    public function setEventId(string $eventId): void
    {
        $this->event_id = $eventId;
    }

    public function setPlatformUserId(string $platformUserId): void
    {
        $this->platform_user_id = $platformUserId;
    }

    public function setUserFirstCreatedAt(CarbonImmutable $userFirstCreatedAt): void
    {
        $this->user_first_created_at = $userFirstCreatedAt->toDateTimeString();
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->user_agent = $userAgent;
    }

    public function setOsPlatform(int $osPlatform): void
    {
        $this->os_platform = $osPlatform;
    }

    public function setOsVersion(string $osVersion): void
    {
        $this->os_version = $osVersion;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->country_code = $countryCode;
    }

    public function setAdId(string $adId): void
    {
        $this->ad_id = $adId;
    }

    public function setRequestAt(CarbonImmutable $requestAt): void
    {
        $this->request_at = $requestAt->toDateTimeString();
    }
}
