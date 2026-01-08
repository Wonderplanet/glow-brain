<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Domain\Resource\Mng\Models\MngClientVersion as BaseMngClientVersion;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;

class MngClientVersion extends BaseMngClientVersion
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    public const IS_FORCE_UPDATE_OPTIONS = [
        0 => '任意',
        1 => '強制',
    ];

    /**
     * プラットフォーム名を取得
     */
    public function getPlatformLabelAttribute(): string
    {
        return PlatformConstant::PLATFORM_STRING_LIST[$this->platform] ?? '不明';
    }

    public function getPlatformColor(): string
    {
        return match ($this->platform) {
            PlatformConstant::PLATFORM_IOS => 'info',
            PlatformConstant::PLATFORM_ANDROID => 'success',
            default => 'gray',
        };
    }

    /**
     * 強制アップデートステータスのラベルを取得
     */
    public function getIsForceUpdateLabelAttribute(): string
    {
        return self::IS_FORCE_UPDATE_OPTIONS[(int)$this->is_force_update] ?? '不明';
    }

    /**
     * 強制アップデートステータスの色を取得
     */
    public function getIsForceUpdateColor(): string
    {
        return $this->is_force_update ? 'danger' : 'success';
    }
}
