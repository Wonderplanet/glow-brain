<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mst_units_i18n";

    protected $casts = [
        'id' => 'string',
        'mst_unit_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
        'detail' => 'string',
    ];

    protected $guarded = [];

    public function toEntity(): MstUnitI18nEntity
    {
        return new MstUnitI18nEntity(
            $this->id,
            $this->mst_unit_id,
            $this->language,
            $this->name,
            $this->description,
            $this->detail,
        );
    }
}
