<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use App\Domain\Unit\Models\UsrUnitSummaryInterface;

/**
 * @property string $usr_user_id
 * @property int $grade_level_total_count
 */
class UsrUnitSummary extends UsrEloquentModel implements UsrUnitSummaryInterface
{
    use HasFactory;

    protected static string $tableName = 'usr_unit_summaries';
    protected $primaryKey = 'usr_user_id';

    public function getGradeLevelTotalCount(): int
    {
        return $this->grade_level_total_count;
    }

    public function setGradeLevelTotalCount(int $gradeLevelTotalCount): void
    {
        $this->grade_level_total_count = $gradeLevelTotalCount;
    }

    public function incrementGradeLevel(): void
    {
        $this->grade_level_total_count++;
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id;
    }
}
