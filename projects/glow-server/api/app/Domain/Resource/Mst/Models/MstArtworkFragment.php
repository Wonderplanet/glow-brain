<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkFragmentEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string      $id
 * @property string      $mst_artwork_id
 * @property string|null $drop_group_id
 * @property int|null    $drop_percentage
 * @property string      $rarity
 * @property int         $asset_num
 * @property int         $release_key
 */
class MstArtworkFragment extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_artwork_fragments';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'drop_group_id' => 'string',
        'drop_percentage' => 'integer',
        'rarity' => 'string',
        'asset_num' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkFragmentEntity
    {
        // rarityはサーバー側では使わないのでentityには含めない
        return new MstArtworkFragmentEntity(
            $this->id,
            $this->mst_artwork_id,
            $this->drop_group_id,
            $this->drop_percentage,
            $this->asset_num,
            $this->release_key,
        );
    }
}
