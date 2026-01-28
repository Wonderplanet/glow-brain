<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstShopPassI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 */
class MstShopPassI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_shop_passes_i18n';

    protected $casts = [
        'id' => 'string',
        'mst_shop_pass_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_shop_pass_id,
            $this->language,
            $this->name,
            $this->release_key,
        );
    }
}
