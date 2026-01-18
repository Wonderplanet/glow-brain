<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitGradeUpEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitGradeUp extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'unit_label' => 'string',
        'grade_level' => 'integer',
        'require_amount' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'unit_label',
        'grade_level',
        'require_amount',
    ];

    public function toEntity(): MstUnitGradeUpEntity
    {
        return new MstUnitGradeUpEntity(
            $this->id,
            $this->unit_label,
            $this->grade_level,
            $this->require_amount,
        );
    }
}
