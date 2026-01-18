<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRankEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_advent_battle_id
 * @property string $rank_type
 * @property int    $rank_level
 * @property int    $required_lower_score
 * @property string $asset_key
 * @property int    $release_key
 */
class MstAdventBattleRank extends MstModel
{
    use HasFactory;

    protected $table = 'mst_advent_battle_ranks';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_advent_battle_id' => 'string',
        'rank_type' => 'string',
        'rank_level' => 'integer',
        'required_lower_score' => 'integer',
        'asset_key' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_advent_battle_id,
            $this->rank_type,
            $this->rank_level,
            $this->required_lower_score,
        );
    }
}
