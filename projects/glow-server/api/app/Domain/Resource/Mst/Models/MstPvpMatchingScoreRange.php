<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpMatchingScoreRangeEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpMatchingScoreRange extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'rank_class_type' => 'string',
        'rank_class_level' => 'integer',
        'upper_rank_max_score' => 'integer',
        'upper_rank_min_score' => 'integer',
        'same_rank_max_score' => 'integer',
        'same_rank_min_score' => 'integer',
        'lower_rank_max_score' => 'integer',
        'lower_rank_min_score' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->rank_class_type,
            $this->rank_class_level,
            $this->upper_rank_max_score,
            $this->upper_rank_min_score,
            $this->same_rank_max_score,
            $this->same_rank_min_score,
            $this->lower_rank_max_score,
            $this->lower_rank_min_score,
        );
    }
}
