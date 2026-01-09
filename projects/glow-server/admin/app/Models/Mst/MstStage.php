<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstStage as BaseMstStage;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Models\Usr\UsrStageEvent;

class MstStage extends BaseMstStage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_stage_i18n()
    {
        return $this->hasOne(MstStageI18n::class, 'mst_stage_id', 'id');
    }

    public function mst_stage_rewards()
    {
        return $this->hasMany(
            MstStageReward::class,
            'mst_stage_id',
            'id',
        );
    }

    public function mst_stage_event_rewards()
    {
        return $this->hasMany(
            MstStageEventReward::class,
            'mst_stage_id',
            'id',
        );
    }

    public function mst_stage_clear_time_rewards()
    {
        return $this->hasMany(
            MstStageClearTimeReward::class,
            'mst_stage_id',
            'id',
        );
    }

    public function mst_quests()
    {
        return $this->belongsTo(MstQuest::class, 'mst_quest_id', 'id');
    }

    public function mst_stage_event_setting()
    {
        return $this->hasOne(MstStageEventSetting::class, 'mst_stage_id', 'id');
    }

    public function usr_stage_event()
    {
        return $this->hasOne(UsrStageEvent::class, 'mst_stage_id', 'id');
    }

    public function mst_in_game()
    {
        return $this->hasOne(MstInGame::class, 'id', 'mst_in_game_id');
    }

    public function mst_artwork_fragment()
    {
        return $this->hasOne(MstArtworkFragment::class, 'drop_group_id', 'mst_artwork_fragment_drop_group_id');
    }

    public function getAutoLapTypeLabel(): string
    {
        return sprintf(
            '%s可能',
            match ($this->auto_lap_type) {
                null => '不',
                StageAutoLapType::AFTER_CLEAR->value => 'クリア後',
                default => '即時',
            }
        );
    }

    public function getAutoLapLabel(): string
    {
        $autoLapLabel = $this->getAutoLapTypeLabel();
        if ($this->auto_lap_type === null) {
            return $autoLapLabel;
        }
        return sprintf(
            '%s(最大%s周)',
            $autoLapLabel,
            $this->max_auto_lap_count
        );
    }
}
