<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpI18nEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'mst_pvps_i18n';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_pvp_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
        );
    }
}
