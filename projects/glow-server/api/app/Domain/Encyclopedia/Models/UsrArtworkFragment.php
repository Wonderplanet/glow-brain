<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_artwork_id
 * @property string $mst_artwork_fragment_id
 */
class UsrArtworkFragment extends UsrEloquentModel implements UsrArtworkFragmentInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_artwork_id',
        'mst_artwork_fragment_id',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_artwork_fragment_id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mst_artwork_id;
    }

    public function getMstArtworkFragmentId(): string
    {
        return $this->mst_artwork_fragment_id;
    }
}
