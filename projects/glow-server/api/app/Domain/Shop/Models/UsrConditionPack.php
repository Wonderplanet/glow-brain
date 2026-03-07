<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Entities\UsrConditionPackEntity;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_pack_id
 * @property string $start_date
 */
class UsrConditionPack extends UsrEloquentModel implements UsrConditionPackInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_pack_id',
        'start_date',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_pack_id;
    }

    public function getMstPackId(): string
    {
        return $this->mst_pack_id;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function toEntity(): UsrConditionPackEntity
    {
        return new UsrConditionPackEntity(
            $this->usr_user_id,
            $this->mst_pack_id,
            $this->start_date,
        );
    }
}
