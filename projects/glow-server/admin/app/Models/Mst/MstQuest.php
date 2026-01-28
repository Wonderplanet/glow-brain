<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\QuestType;
use App\Domain\Resource\Mst\Models\MstQuest as BaseMstQuest;
use App\Utils\AssetUtil;

class MstQuest extends BaseMstQuest implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_quest_i18n()
    {
        return $this->hasOne(MstQuestI18n::class, 'mst_quest_id', 'id');
    }

    public function mst_stages()
    {
        return $this->hasMany(MstStage::class, 'mst_quest_id', 'id');
    }

    public function mst_event()
    {
        return $this->hasOne(MstEvent::class, 'id', 'mst_event_id');
    }

    public function makeAssetPath(): ?string
    {
        $isEvent = ($this->quest_type === QuestType::EVENT->value);

        if ($isEvent) {
            return AssetUtil::findAssetPathFromTemplates(
                [
                    'event/event!{release_key}/{asset_key}_quest_select_cell.png',
                    'quest_image/quest_image/event/quest_image_{asset_key}.png', // 旧パス（互換性）
                ],
                $this->asset_key,
                $this->release_key
            );
        }

        return AssetUtil::findAssetPathFromTemplates(
            ['quest_image/quest_image!{release_key}/quest_image_{asset_key}.png'],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return null; // 背景なし
    }
}
