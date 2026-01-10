<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitFragmentConvertEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $unit_label
 * @property int    $convert_amount
 * @property int    $release_key
 */
class MstUnitFragmentConvert extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'unit_label' => 'string',
        'convert_amount' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->unit_label,
            $this->convert_amount,
            $this->release_key,
        );
    }
}
