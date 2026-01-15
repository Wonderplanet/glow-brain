<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrTutorialInterface extends UsrModelInterface
{
    public function getMstTutorialId(): string;
}
