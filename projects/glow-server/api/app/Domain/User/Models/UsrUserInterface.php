<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Entities\UsrUserEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrUserInterface extends UsrModelInterface
{
    public function getTutorialStatus(): string;

    public function setTutorialStatus(string $tutorialStatus): void;

    public function isTutorialUnplayed(): bool;

    public function isMainPartTutorialCompleted(): bool;

    public function getTosVersion(): int;

    public function setTosVersion(int $tosVersion): void;

    public function getPrivacyPolicyVersion(): int;

    public function setPrivacyPolicyVersion(int $privacyPolicyVersion): void;

    public function getGlobalConsentVersion(): int;

    public function setGlobalConsentVersion(int $globalConsentVersion): void;

    public function getStatus(): int;

    public function setBnUserId(string $bnUserId): void;

    public function getBnUserId(): ?string;

    public function hasBnUserId(): bool;

    public function isAccountLinkingRestricted(): bool;

    public function setIsAccountLinkingRestricted(int $isAccountLinkingRestricted): void;

    public function getClientUuid(): ?string;

    public function getSuspendEndAt(): ?string;

    public function getGameStartAt(): ?string;

    public function getCreatedAt(): ?CarbonImmutable;

    public function toEntity(): UsrUserEntity;

    public function getIaaVersion(): int;

    public function setIaaVersion(int $iaaVersion): void;
}
