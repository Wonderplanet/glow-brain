<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $mst_outpost_id
 * @property string $mst_outpost_enhancement_id
 * @property int $level
 */
class UsrOutpostEnhancement extends UsrEloquentModel implements UsrOutpostEnhancementInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'mst_outpost_id',
        'mst_outpost_enhancement_id',
        'level',
    ];

    protected $casts = [
        'id' => 'string',
        'usr_user_id' => 'string',
        'mst_outpost_id' => 'string',
        'mst_outpost_enhancement_id' => 'string',
        'level' => 'integer',
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_outpost_id . $this->mst_outpost_enhancement_id;
    }

    public function getMstOutpostId(): string
    {
        return $this->mst_outpost_id;
    }

    public function setMstOutpostId(string $mstOutpostId): void
    {
        $this->mst_outpost_id = $mstOutpostId;
    }

    public function getMstOutpostEnhancementId(): string
    {
        return $this->mst_outpost_enhancement_id;
    }

    public function setMstOutpostEnhancementId(string $mstOutpostEnhancementId): void
    {
        $this->mst_outpost_enhancement_id = $mstOutpostEnhancementId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function toEntity(): UsrOutpostEnhancementEntity
    {
        return new UsrOutpostEnhancementEntity(
            $this->mst_outpost_id,
            $this->mst_outpost_enhancement_id,
            $this->level,
        );
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mst_outpost_id' => $this->mst_outpost_id,
            'mst_outpost_enhancement_id' => $this->mst_outpost_enhancement_id,
            'level' => $this->level,
        ];
    }
}
