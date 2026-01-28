<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrInGameStatusData
{
    public function __construct(
        private readonly bool $isStartedSession = false,
        private readonly ?string $inGameContentType = null,
        private readonly string $targetMstId = '',
        private readonly int $partyNo = 0,
        private readonly int $continueCount = 0,
        private readonly int $continueAdCount = 0,
    ) {
    }

    public function getIsStartedSession(): bool
    {
        return $this->isStartedSession;
    }

    public function getInGameContentType(): ?string
    {
        return $this->inGameContentType;
    }

    public function getTargetMstId(): string
    {
        return $this->targetMstId;
    }

    public function getPartyNo(): int
    {
        return $this->partyNo;
    }

    public function getContinueCount(): int
    {
        return $this->continueCount;
    }

    public function getContinueAdCount(): int
    {
        return $this->continueAdCount;
    }
}
