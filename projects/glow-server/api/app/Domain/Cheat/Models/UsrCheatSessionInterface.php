<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Models;

use App\Domain\Cheat\Entities\CheatSessionParty;
use App\Domain\Cheat\Entities\CheatSessionPartyWithOpponent;
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
     * @param CheatSessionParty $cheatSessionParty
     */
    public function setPartyStatus(string $contentType, string $targetId, CheatSessionParty $cheatSessionParty): void;

    /**
     * 対戦相手がいる場合のパーティステータスを設定（PvP等で使用）
     *
     * @param string $contentType
     * @param string $targetId
     * @param CheatSessionPartyWithOpponent $sessionWithOpponent
     */
    public function setPartyStatusWithOpponent(
        string $contentType,
        string $targetId,
        CheatSessionPartyWithOpponent $sessionWithOpponent
    ): void;
}
