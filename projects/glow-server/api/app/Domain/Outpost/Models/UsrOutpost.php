<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrOutpostEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $mst_outpost_id
 * @property string|null $mst_artwork_id
 * @property int $is_used
 */
class UsrOutpost extends UsrEloquentModel implements UsrOutpostInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'mst_outpost_id',
        'mst_artwork_id',
        'is_used',
    ];

    protected $casts = [
        'id' => 'string',
        'usr_user_id' => 'string',
        'mst_outpost_id' => 'string',
        'mst_artwork_id' => 'string',
        'is_used' => 'integer',
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        // 例：usr_user_id と　column1 でユニーク制約があるテーブルの場合
        return $this->usr_user_id . $this->mst_outpost_id;
    }

    public function getMstOutpostId(): string
    {
        return $this->mst_outpost_id;
    }

    public function getMstArtworkId(): ?string
    {
        return $this->mst_artwork_id;
    }

    public function setMstOutpostId(string $mstOutpostId): void
    {
        $this->mst_outpost_id = $mstOutpostId;
    }

    public function setMstArtworkId(?string $mstArtworkId): void
    {
        $this->mst_artwork_id = $mstArtworkId;
    }

    public function getIsUsed(): int
    {
        return $this->is_used;
    }

    public function setIsUsed(int $isUsed): void
    {
        $this->is_used = $isUsed;
    }

    public function toEntity(): UsrOutpostEntity
    {
        return new UsrOutpostEntity(
            $this->usr_user_id,
            $this->mst_outpost_id,
            $this->mst_artwork_id,
        );
    }
}
