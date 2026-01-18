<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitGradeCoefficientEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $release_key
 * @property string $unit_label
 * @property int $grade_level
 * @property int $coefficient
 */
class MstUnitGradeCoefficient extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'unit_label' => 'string',
        'grade_level' => 'integer',
        'coefficient' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->unit_label,
            $this->grade_level,
            $this->coefficient,
        );
    }
}
