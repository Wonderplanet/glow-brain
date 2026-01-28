<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEnemyCharacter as BaseMstEnemyCharacter;
use App\Utils\AssetUtil;

class MstEnemyCharacter extends BaseMstEnemyCharacter implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    protected static $displayedEncyclopediaOptions = [
        '1' => '表示',
        '0' => '非表示',
    ];

    public function mst_enemy_character_i18n()
    {
        return $this->hasOne(MstEnemyCharacterI18n::class, 'mst_enemy_character_id', 'id');
    }

    public function mst_series()
    {
        return $this->hasOne(MstSeries::class, 'id', 'mst_series_id');
    }

    public function mst_enemy_stage_parameter()
    {
        return $this->hasOne(MstEnemyStageParameter::class, 'mst_enemy_character_id', 'id');
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::findAssetPathFromTemplates(
            ['unit_enemy_icon/unit_enemy_icon!{release_key}/unit_enemy_icon_{asset_key}.png'],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return null;
    }

    public static function getDisplayedEncyclopediaOptions(): array
    {
        return self::$displayedEncyclopediaOptions;
    }

    public function getIsDisplayedEncyclopediaLabelAttribute(): string
    {
        return self::$displayedEncyclopediaOptions[$this->is_displayed_encyclopedia] ?? '非表示';
    }

    public function getDisplayedEncyclopediaColor(): string
    {
        return $this->is_displayed_encyclopedia == 1 ? 'success' : 'gray';
    }
}
