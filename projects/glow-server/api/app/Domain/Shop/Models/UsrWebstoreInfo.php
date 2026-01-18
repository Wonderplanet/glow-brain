<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $country_code
 * @property string|null $os_platform
 * @property string|null $ad_id
 * @property string $created_at
 * @property string $updated_at
 */
class UsrWebstoreInfo extends UsrEloquentModel implements UsrWebstoreInfoInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'country_code',
        'os_platform',
        'ad_id',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getOsPlatform(): ?string
    {
        return $this->os_platform;
    }

    public function setOsPlatform(string $osPlatform): void
    {
        $this->os_platform = $osPlatform;
    }

    public function getAdId(): ?string
    {
        return $this->ad_id;
    }

    public function setAdId(string $adId): void
    {
        $this->ad_id = $adId;
    }
}
