<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrTemporaryIndividualMessageInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function setUsrUserId(string $usrUserId): void;

    public function getMngMessageId(): ?string;

    public function setMngMessageId(string $mngMessageId): void;
}
