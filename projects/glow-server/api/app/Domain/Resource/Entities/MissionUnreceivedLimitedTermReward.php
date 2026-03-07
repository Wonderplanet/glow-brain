<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use Illuminate\Support\Collection;

/**
 * 期間限定ミッションの報酬未受け取り数情報をイベントカテゴリ毎に格納するデータクラス
 */
class MissionUnreceivedLimitedTermReward
{
    /**
     * @param int $adventBattleCount
     * @param Collection<string, int> $artworkPanelCountMap key: mstArtworkPanelMissionId, value: count
     */
    public function __construct(
        private int $adventBattleCount,
        private Collection $artworkPanelCountMap,
    ) {
        $this->adventBattleCount = $adventBattleCount;
        $this->artworkPanelCountMap = $artworkPanelCountMap;
    }

    /**
     * 降臨バトル関連ミッションの報酬未受け取り数取得
     * @return int
     */
    public function getAdventBattleCount(): int
    {
        return $this->adventBattleCount;
    }

    /**
     * 原画パネルミッション関連の報酬未受け取り数をmst_artwork_panel_missions.id毎に取得
     * @return Collection<string, int> key: mstArtworkPanelMissionId, value: count
     */
    public function getArtworkPanelCountMap(): Collection
    {
        return $this->artworkPanelCountMap;
    }
}
