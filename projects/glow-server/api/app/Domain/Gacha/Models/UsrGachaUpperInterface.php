<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrGachaUpperInterface extends UsrModelInterface
{
    public function getUpperGroup(): string;

    public function getUpperType(): string;

    public function getCount(): int;

    public function addCount(): void;

    public function resetCount(): void;
}
