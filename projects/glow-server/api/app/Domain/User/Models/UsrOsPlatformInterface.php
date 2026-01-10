<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrOsPlatformInterface extends UsrModelInterface
{
    public function getOsPlatform(): string;

    public function setOsPlatform(string $osPlatform): void;
}
