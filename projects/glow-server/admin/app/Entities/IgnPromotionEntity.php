<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Mng\MngInGameNotice;
use App\Models\Mng\MngInGameNoticeI18n;
use Illuminate\Support\Collection;

class IgnPromotionEntity
{
    private const KEY_MNG_IN_GAME_NOTICE = 'mngInGameNotice';
    private const KEY_MNG_IN_GAME_NOTICE_I18N = 'mngInGameNoticeI18n';

    /**
     * @param Collection<MngInGameNotice> $mngInGameNotices
     * @param Collection<MngInGameNoticeI18n> $mngInGameNoticeI18ns
     */
    public function __construct(
        private Collection $mngInGameNotices,
        private Collection $mngInGameNoticeI18ns,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_MNG_IN_GAME_NOTICE => $this->mngInGameNotices
                ->map(fn(MngInGameNotice $notice) => $notice->formatToResponse())
                ->values()
                ->all(),
            self::KEY_MNG_IN_GAME_NOTICE_I18N => $this->mngInGameNoticeI18ns
                ->map(fn(MngInGameNoticeI18n $i18n) => $i18n->formatToResponse())
                ->values()
                ->all(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $mngInGameNotices = collect($response[self::KEY_MNG_IN_GAME_NOTICE] ?? [])
            ->map(fn($item) => MngInGameNotice::createFromResponseArray($item));

        $mngInGameNoticeI18ns = collect($response[self::KEY_MNG_IN_GAME_NOTICE_I18N] ?? [])
            ->map(fn($item) => MngInGameNoticeI18n::createFromResponseArray($item));

        return new self($mngInGameNotices, $mngInGameNoticeI18ns);
    }

    public function isEmpty(): bool
    {
        return $this->mngInGameNotices->isEmpty();
    }

    /**
     * @return Collection<MngInGameNotice>
     */
    public function getMngInGameNotices(): Collection
    {
        return $this->mngInGameNotices;
    }

    /**
     * @return Collection<MngInGameNoticeI18n>
     */
    public function getMngInGameNoticeI18ns(): Collection
    {
        return $this->mngInGameNoticeI18ns;
    }
}
