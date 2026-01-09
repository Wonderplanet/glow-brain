<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_unit_encyclopedia_reward_id
 */
class UsrReceivedUnitEncyclopediaReward extends UsrEloquentModel implements UsrReceivedUnitEncyclopediaRewardInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_unit_encyclopedia_reward_id',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_unit_encyclopedia_reward_id;
    }

    public function getMstUnitEncyclopediaRewardId(): string
    {
        return $this->mst_unit_encyclopedia_reward_id;
    }
}
