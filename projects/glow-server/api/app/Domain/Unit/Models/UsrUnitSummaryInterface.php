<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrUnitSummaryInterface extends UsrModelInterface
{
    public function getGradeLevelTotalCount(): int;
    public function setGradeLevelTotalCount(int $gradeLevelTotalCount): void;
    public function incrementGradeLevel(): void;
}
