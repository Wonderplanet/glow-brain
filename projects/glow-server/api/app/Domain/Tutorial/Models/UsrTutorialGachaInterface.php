<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrTutorialGachaInterface extends UsrModelInterface
{
    /**
     * @return array<mixed>
     */
    public function getGachaResultJson(): array;

    /**
     * @param array<mixed> $gachaResultJson
     */
    public function setGachaResultJson(array $gachaResultJson): void;

    public function getConfirmedAt(): ?string;

    public function isConfirmed(): bool;

    public function confirm(CarbonImmutable $now): void;
}
