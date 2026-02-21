<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrArtworkEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_artwork_id
 * @property int $is_new_encyclopedia
 * @property int $grade_level
 */
class UsrArtwork extends UsrEloquentModel implements UsrArtworkInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_artwork_id',
        'grade_level',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_artwork_id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mst_artwork_id;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->is_new_encyclopedia;
    }

    public function getGradeLevel(): int
    {
        return $this->grade_level;
    }

    public function markAsCollected(): void
    {
        $this->is_new_encyclopedia = EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function isAlreadyCollected(): bool
    {
        return $this->is_new_encyclopedia === EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function incrementGradeLevel(): void
    {
        $this->grade_level++;
    }

    public function toEntity(): UsrArtworkEntity
    {
        return new UsrArtworkEntity(
            $this->usr_user_id,
            $this->mst_artwork_id,
            $this->grade_level,
        );
    }
}
