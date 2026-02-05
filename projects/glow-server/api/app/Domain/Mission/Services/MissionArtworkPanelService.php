<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Resource\Mst\Entities\MstArtworkPanelMissionEntity;
use Illuminate\Support\Collection;

/**
 * 原画パネルミッションのサービスクラス
 */
class MissionArtworkPanelService
{
    public function __construct(
        private EncyclopediaDelegator $encyclopediaDelegator,
    ) {
    }

    /**
     * 原画パネルミッションの初期開放かけらを開放する(未所持の場合のみ)
     * @param Collection<MstArtworkPanelMissionEntity> $mstArtworkPanelMissions
     */
    public function createInitialUsrArtworkFragmentsIfNeeded(
        string $usrUserId,
        Collection $mstArtworkPanelMissions,
    ): void {
        $initialOpenMstArtworkFragmentIds = collect();
        foreach ($mstArtworkPanelMissions as $mstArtworkPanelMission) {
            if ($mstArtworkPanelMission->hasInitialOpenArtworkFragment()) {
                $initialOpenMstArtworkFragmentIds->push(
                    $mstArtworkPanelMission->getInitialOpenMstArtworkFragmentId()
                );
            }
        }

        if ($initialOpenMstArtworkFragmentIds->isEmpty()) {
            // 開放かけらなしなので何もしない
            return;
        }

        $this->encyclopediaDelegator->createUnownedUsrArtworkFragments(
            $usrUserId,
            $initialOpenMstArtworkFragmentIds,
        );
    }
}
