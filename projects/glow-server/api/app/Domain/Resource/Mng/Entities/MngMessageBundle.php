<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * mng_message_id単位でまとめられたMngMessageに関連するEntityを束ねるクラス
 */
class MngMessageBundle
{
    /**
     * @param Collection<MngMessageRewardEntity> $mngMessageRewards
     */
    public function __construct(
        private MngMessageEntity $mngMessage,
        private MngMessageI18nEntity $mngMessageI18n,
        private Collection $mngMessageRewards,
    ) {
    }

    public function getMngMessage(): MngMessageEntity
    {
        return $this->mngMessage;
    }

    public function getMngMessageI18n(): MngMessageI18nEntity
    {
        return $this->mngMessageI18n;
    }

    /**
     * @return Collection<MngMessageRewardEntity>
     */
    public function getMngMessageRewards(): Collection
    {
        return $this->mngMessageRewards;
    }

    public function isActive(CarbonImmutable $now): bool
    {
        return $this->mngMessage->isActive($now);
    }
}
