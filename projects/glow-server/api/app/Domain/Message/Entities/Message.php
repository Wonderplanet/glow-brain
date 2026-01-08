<?php

declare(strict_types=1);

namespace App\Domain\Message\Entities;

use App\Domain\Message\Models\UsrMessageInterface;
use Illuminate\Support\Collection;

class Message
{
    public function __construct(
        readonly private ?string $mngMessageId,
        readonly private string $startAt,
        readonly private string $title,
        readonly private string $body,
        readonly private UsrMessageInterface $usrMessage,
        readonly private Collection $messageRewards,
    ) {
    }

    public function getUsrMessageId(): string
    {
        return $this->usrMessage->getId();
    }

    public function getMngMessageId(): ?string
    {
        return $this->mngMessageId;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getOpenedAt(): ?string
    {
        return $this->usrMessage->getOpenedAt();
    }

    public function getReceivedAt(): ?string
    {
        return $this->usrMessage->getReceivedAt();
    }

    public function getExpiredAt(): ?string
    {
        return $this->usrMessage->getExpiredAt();
    }

    /**
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    public function getMessageRewards(): Collection
    {
        return $this->messageRewards;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
