<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrArtworkFragmentInterface extends UsrModelInterface
{
    public function getMstArtworkId(): string;
    public function getMstArtworkFragmentId(): string;
}
