<?php

declare(strict_types=1);

namespace App\Models\Adm;

/**
 * @property string $fluentd_tag
 * @property string $version
 * @property string $event_id
 * @property string $event_time
 * @property string $app_id
 * @property string $app_user_id
 * @property string $app_system_prefix
 * @property string $user_id
 * @property string $person_id
 * @property string $mbid
 * @property string $ktid
 * @property string $platform_id
 * @property string $platform_version
 * @property string $platform_user_id
 * @property string $user_agent
 * @property string $created_time
 * @property string $country_code
 * @property string $ad_id
 */
class AdmBankF001 extends AdmModel
{
    protected $table = 'adm_bank_f001';

    public function setFluentdTag(string $fluentdTag): void
    {
        $this->fluentd_tag = $fluentdTag;
    }

    public function getFluentdTag(): string
    {
        return $this->fluentd_tag;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setEventId(string $eventId): void
    {
        $this->event_id = $eventId;
    }

    public function getEventId(): string
    {
        return $this->event_id;
    }

    public function setEventTime(string $eventTime): void
    {
        $this->event_time = $eventTime;
    }

    public function getEventTime(): string
    {
        return $this->event_time;
    }

    public function setAppId(string $appId): void
    {
        $this->app_id = $appId;
    }

    public function getAppId(): string
    {
        return $this->app_id;
    }

    public function setAppUserId(string $appUserId): void
    {
        $this->app_user_id = $appUserId;
    }

    public function getAppUserId(): string
    {
        return $this->app_user_id;
    }

    public function setAppSystemPrefix(string $appSystemPrefix): void
    {
        $this->app_system_prefix = $appSystemPrefix;
    }

    public function getAppSystemPrefix(): string
    {
        return $this->app_system_prefix;
    }

    public function setUserId(string $userId): void
    {
        $this->user_id = $userId;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function setPersonId(string $personId): void
    {
        $this->person_id = $personId;
    }

    public function getPersonId(): string
    {
        return $this->person_id;
    }

    public function setMbid(string $mbid): void
    {
        $this->mbid = $mbid;
    }

    public function getMbid(): string
    {
        return $this->mbid;
    }

    public function setKtid(string $ktid): void
    {
        $this->ktid = $ktid;
    }

    public function getKtid(): string
    {
        return $this->ktid;
    }

    public function setPlatformId(string $platformId): void
    {
        $this->platform_id = $platformId;
    }

    public function getPlatformId(): string
    {
        return $this->platform_id;
    }

    public function setPlatformVersion(string $platformVersion): void
    {
        $this->platform_version = $platformVersion;
    }

    public function getPlatformVersion(): string
    {
        return $this->platform_version;
    }

    public function setPlatformUserId(string $platformUserId): void
    {
        $this->platform_user_id = $platformUserId;
    }

    public function getPlatformUserId(): string
    {
        return $this->platform_user_id;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->user_agent = $userAgent;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    public function setCreatedTime(string $createdTime): void
    {
        $this->created_time = $createdTime;
    }

    public function getCreatedTime(): string
    {
        return $this->created_time;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->country_code = $countryCode;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function setAdId(string $adId): void
    {
        $this->ad_id = $adId;
    }

    public function getAdId(): string
    {
        return $this->ad_id;
    }
}
