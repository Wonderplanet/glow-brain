<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpDummyEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpDummy extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_dummy_user_id' => 'string',
        'rank_class_type' => 'string',
        'rank_class_level' => 'integer',
        'matching_type' => 'string',
    ];
    public function getId(): string
    {
        return $this->id;
    }

    public function getMstDummyUserId(): string
    {
        return $this->mst_dummy_user_id;
    }

    public function getRankClassType(): string
    {
        return $this->rank_class_type;
    }

    public function getRankClassLevel(): int
    {
        return $this->rank_class_level;
    }

    public function getMatchingType(): string
    {
        return $this->matching_type;
    }

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_dummy_user_id,
            $this->rank_class_type,
            $this->rank_class_level,
            $this->matching_type
        );
    }
}
