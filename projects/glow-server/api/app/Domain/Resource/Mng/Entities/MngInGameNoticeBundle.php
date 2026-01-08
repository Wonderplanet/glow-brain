<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;

/**
 * mng_in_game_notice_id単位でまとめられたMngInGameNoticeに関連するEntityを束ねるクラス
 */
class MngInGameNoticeBundle
{
    public function __construct(
        private MngInGameNoticeEntity $mngInGameNotice,
        private MngInGameNoticeI18nEntity $mngInGameNoticeI18n,
    ) {
    }

    public function getMngInGameNotice(): MngInGameNoticeEntity
    {
        return $this->mngInGameNotice;
    }

    public function getMngInGameNoticeI18n(): MngInGameNoticeI18nEntity
    {
        return $this->mngInGameNoticeI18n;
    }

    public function isActive(CarbonImmutable $now): bool
    {
        return $this->mngInGameNotice->isActive($now);
    }
}
