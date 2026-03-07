<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstExchangeI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $release_key
 * @property string $mst_exchange_id
 * @property string $language
 * @property string $name
 * @property string $asset_key
 */
class MstExchangeI18n extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_exchange_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'asset_key' => 'string',
    ];

    protected $guarded = [];

    public function toEntity(): MstExchangeI18nEntity
    {
        return new MstExchangeI18nEntity(
            $this->id,
            $this->mst_exchange_id,
            $this->language,
            $this->name,
            $this->asset_key,
        );
    }
}
