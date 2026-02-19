<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Mst\Entities\MstPvpRankEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpRank extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'rank_class_type' => 'string',
        'rank_class_level' => 'integer',
        'required_lower_score' => 'integer',
        'win_add_point' => 'integer',
        'lose_sub_point' => 'integer',
        'asset_key' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            PvpRankClassType::from($this->rank_class_type),
            $this->rank_class_level,
            $this->required_lower_score,
            $this->win_add_point,
            $this->lose_sub_point,
        );
    }
}
