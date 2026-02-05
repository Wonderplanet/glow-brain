<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Repositories\MstArtworkRepository;
use Illuminate\Support\Collection;

class EncyclopediaMissionTriggerService
{
    public function __construct(
        private MstArtworkRepository $mstArtworkRepository,
        // Delegator
        private MissionDelegator $missionDelegator,
    ) {
    }

    /**
     * 原画完成系のミッションの内で、初めて完成したもののみを対象とした重複なしでカウントするミッションのトリガーを送信する
     *
     * @param Collection<string> $mstArtworkIds mst_artwork.id 初完成した原画のID
     */
    public function sendNewArtworkTrigger(
        Collection $mstArtworkIds,
    ): void {
        $mstArtworks = $this->mstArtworkRepository->getByIds($mstArtworkIds);

        foreach ($mstArtworks as $mstArtwork) {
            $mstArtworkId = $mstArtwork->getId();
            $mstSeriesId = $mstArtwork->getMstSeriesId();

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::ARTWORK_COMPLETED_COUNT->value,
                    null,
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT->value,
                    $mstSeriesId,
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT->value,
                    $mstArtworkId,
                    1,
                )
            );
        }
    }

    /**
     * 原画グレードアップのミッショントリガーを送信する
     *
     * @param UsrArtworkInterface $usrArtwork グレードアップした原画
     */
    public function sendArtworkGradeUpTrigger(
        UsrArtworkInterface $usrArtwork,
    ): void {
        // 特定の原画のグレードレベル
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_ARTWORK_GRADE_LEVEL->value,
                $usrArtwork->getMstArtworkId(),
                $usrArtwork->getGradeLevel(),
            )
        );
    }
}
