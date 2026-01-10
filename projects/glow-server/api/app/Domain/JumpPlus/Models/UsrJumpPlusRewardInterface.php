<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrJumpPlusRewardInterface extends UsrModelInterface
{
    public function getMngJumpPlusRewardScheduleId(): string;
}
