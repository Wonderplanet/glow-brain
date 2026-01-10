<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrCheatSessionInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function getContentType(): string;

    public function getTargetId(): string;

    public function getPartyStatus(): string;

    /**
     * @param string $contentType
     * @param string $targetId
     * @param array<array<mixed>> $partyStatuses
     */
    public function setPartyStatus(string $contentType, string $targetId, array $partyStatuses): void;
}
