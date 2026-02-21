<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Models;

use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrEmblem extends UsrEloquentModel implements UsrEmblemInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'mst_emblem_id',
        'is_new_encyclopedia',
    ];

    public function getMstEmblemId(): string
    {
        return $this->mst_emblem_id;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->is_new_encyclopedia;
    }

    public function markAsCollected(): void
    {
        $this->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function isAlreadyCollected(): bool
    {
        return $this->is_new_encyclopedia === EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }
}
