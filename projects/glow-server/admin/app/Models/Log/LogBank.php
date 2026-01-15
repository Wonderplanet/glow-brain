<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Domain\Resource\Log\Models\LogBank as BaseLogBank;

class LogBank extends BaseLogBank
{
    protected $connection = Database::TIDB_CONNECTION;

    public function getEventId(): string
    {
        return $this->event_id;
    }

    public function getPlatformUserId(): string
    {
        return $this->platform_user_id;
    }

    public function getUserFirstCreatedAt(): string
    {
        return $this->user_first_created_at;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    public function getOsPlatform(): int
    {
        return $this->os_platform;
    }

    public function getOsVersion(): string
    {
        return $this->os_version;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getAdId(): string
    {
        return $this->ad_id;
    }

    public function getRequestAt(): string
    {
        return $this->request_at;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at->toDateTimeString();
    }
}
