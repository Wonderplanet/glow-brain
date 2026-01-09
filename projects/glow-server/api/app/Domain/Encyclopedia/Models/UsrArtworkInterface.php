<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Usr\Entities\UsrArtworkEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrArtworkInterface extends UsrModelInterface
{
    public function getMstArtworkId(): string;
    public function getIsNewEncyclopedia(): int;
    public function markAsCollected(): void;
    public function isAlreadyCollected(): bool;
    public function toEntity(): UsrArtworkEntity;
}
