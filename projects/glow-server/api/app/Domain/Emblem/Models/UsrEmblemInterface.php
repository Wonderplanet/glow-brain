<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrEmblemInterface extends UsrModelInterface
{
    public function getMstEmblemId(): string;
    public function getIsNewEncyclopedia(): int;
    public function markAsCollected(): void;
    public function isAlreadyCollected(): bool;
}
