<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrMessageInterface extends UsrModelInterface
{
    public function getMngMessageId(): ?string;

    public function setMngMessageId(?string $mngMessageId): void;

    public function getMessageSource(): ?string;

    public function setMessageSource(?string $messageSource): void;

    public function getRewardGroupId(): ?string;

    public function setRewardGroupId(?string $rewardGroupId): void;

    public function getResourceType(): ?string;

    public function setResourceType(string $resourceType): void;

    public function getResourceId(): ?string;

    public function setResourceId(string $resourceId): void;

    public function getResourceAmount(): ?int;

    public function setResourceAmount(int $resourceAmount): void;

    public function getTitle(): ?string;

    public function setTitle(string $title): void;

    public function getBody(): ?string;

    public function setBody(string $body): void;

    public function getOpenedAt(): ?string;

    public function setOpenedAt(CarbonImmutable $openedAt): void;

    public function getReceivedAt(): ?string;

    public function setReceivedAt(CarbonImmutable $receivedAt): void;

    public function getIsReceived(): bool;

    public function setIsReceived(bool $isReceived): void;

    public function getExpiredAt(): ?string;

    public function setExpiredAt(CarbonImmutable $expiredAt): void;

    public function isExpired(CarbonImmutable $now): bool;

    public function getCreatedAt(): ?string;

    public function receive(CarbonImmutable $receivedAt): void;
}
