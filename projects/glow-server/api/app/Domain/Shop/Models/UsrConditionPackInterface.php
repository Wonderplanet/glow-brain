<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Entities\UsrConditionPackEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrConditionPackInterface extends UsrModelInterface
{
    public function getMstPackId(): string;

    public function getStartDate(): string;

    public function toEntity(): UsrConditionPackEntity;
}
