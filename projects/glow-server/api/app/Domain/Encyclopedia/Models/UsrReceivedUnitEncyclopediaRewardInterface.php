<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrReceivedUnitEncyclopediaRewardInterface extends UsrModelInterface
{
    public function getMstUnitEncyclopediaRewardId(): string;
}
