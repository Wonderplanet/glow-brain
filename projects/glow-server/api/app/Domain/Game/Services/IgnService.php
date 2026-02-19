<?php

declare(strict_types=1);

namespace App\Domain\Game\Services;

use App\Domain\Resource\Mng\Repositories\MngInGameNoticeBundleRepository;
use App\Http\Responses\Data\MngInGameNoticeData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * インゲームノーティス(IGN)のサービスクラス
 */
class IgnService
{
    public function __construct(
        private MngInGameNoticeBundleRepository $mngInGameNoticeBundleRepository,
    ) {
    }

    /**
     * @return Collection<MngInGameNoticeData>
     */
    public function fetchMngInGameNoticeDataList(string $language, CarbonImmutable $now): Collection
    {
        $mngInGameNoticeBundles = $this->mngInGameNoticeBundleRepository
            ->getActiveMngInGameNoticeBundlesByLanguage($language, $now);
        return $this->makeMngInGameNoticeDataList($mngInGameNoticeBundles);
    }

    /**
     * @return Collection<MngInGameNoticeData>
     */
    private function makeMngInGameNoticeDataList(Collection $mngInGameNoticeBundles): Collection
    {
        $result = collect();
        foreach ($mngInGameNoticeBundles as $mngInGameNoticeBundle) {
            /** @var \App\Domain\Resource\Mng\Entities\MngInGameNoticeBundle $mngInGameNoticeBundle */
            $mngInGameNotice = $mngInGameNoticeBundle->getMngInGameNotice();
            $mngInGameNoticeI18n = $mngInGameNoticeBundle->getMngInGameNoticeI18n();

            $result->push(
                new MngInGameNoticeData(
                    $mngInGameNotice->getId(),
                    $mngInGameNotice->getDisplayType(),
                    $mngInGameNotice->getDestinationType(),
                    $mngInGameNotice->getDestinationPath(),
                    $mngInGameNotice->getDestinationPathDetail(),
                    $mngInGameNotice->getPriority(),
                    $mngInGameNotice->getDisplayFrequencyType(),
                    $mngInGameNoticeI18n->getTitle(),
                    $mngInGameNoticeI18n->getDescription(),
                    $mngInGameNoticeI18n->getBannerUrl(),
                    $mngInGameNoticeI18n->getButtonTitle(),
                )
            );
        }

        return $result;
    }
}
