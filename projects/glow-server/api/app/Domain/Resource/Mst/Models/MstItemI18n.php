<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstItemI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstItemI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mst_items_i18n";

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_item_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'mst_item_id',
        'language',
        'name',
        'description',
        'release_key',
    ];

    public function toEntity(): MstItemI18nEntity
    {
        return new MstItemI18nEntity(
            $this->id,
            $this->mst_item_id,
            $this->language,
            $this->name,
            $this->description,
        );
    }
}
