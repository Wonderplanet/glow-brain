<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvp extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'ranking_min_pvp_rank_class' => 'string',
        'max_daily_challenge_count' => 'integer',
        'max_daily_item_challenge_count' => 'integer',
        'item_challenge_cost_amount' => 'integer',
        'initial_battle_point' => 'integer',
        'mst_in_game_id' => 'string',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getRankingMinPvpRankClass(): ?string
    {
        return $this->ranking_min_pvp_rank_class;
    }

    public function getMaxDailyChallengeCount(): int
    {
        return $this->max_daily_challenge_count;
    }

    public function getMaxDailyItemChallengeCount(): int
    {
        return $this->max_daily_item_challenge_count;
    }

    public function getItemChallengeCostAmount(): int
    {
        return $this->item_challenge_cost_amount;
    }

    public function getMstInGameId(): string
    {
        return $this->mst_in_game_id;
    }

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->ranking_min_pvp_rank_class,
            $this->max_daily_challenge_count,
            $this->max_daily_item_challenge_count,
            $this->item_challenge_cost_amount,
            $this->mst_in_game_id,
        );
    }
}
