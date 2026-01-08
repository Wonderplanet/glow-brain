<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEnemyOutpost;
use App\Domain\Resource\Mst\Models\MstEnemyStageParameter;
use App\Domain\Resource\Mst\Models\MstInGame as BaseMstInGame;
use App\Utils\AssetUtil;

class MstInGame extends BaseMstInGame implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function makeAssetPath(): ?string
    {
        if (empty($this->bgm_asset_key)) {
            return null;
        }
        return AssetUtil::makeClientBgmPath($this->bgm_asset_key . '.wav');
    }
    
    public function makeBgPath(): ?string
    {
        return AssetUtil::makeBgItemIconFramePathByRarity($this->rarity);
    }

    public function mst_auto_player_sequence()
    {
        return $this->hasOne(MstAutoPlayerSequence::class, 'id', 'mst_auto_player_sequence_id');
    }

    public function mst_koma_line()
    {
        return $this->hasOne(MstKomaLine::class, 'mst_page_id', 'mst_page_id');
    }
    
    public function mst_enemy_outpost()
    {
        return $this->hasOne(MstEnemyOutpost::class, 'id', 'mst_enemy_outpost_id');
    }

    public function mst_enemy_stage_parameter()
    {
        return $this->hasOne(MstEnemyStageParameter::class, 'id', 'mst_defense_target_id');
    }
}
