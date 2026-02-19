<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

/**
 * 期間限定ミッションの報酬未受け取り数情報をイベントカテゴリ毎に格納するデータクラス
 */
class MissionUnreceivedLimitedTermReward
{
    public function __construct(
        private int $adventBattleCount,
    ) {
        $this->adventBattleCount = $adventBattleCount;
    }

    /**
     * 降臨バトル関連ミッションの報酬未受け取り数取得
     * @return int
     */
    public function getAdventBattleCount(): int
    {
        return $this->adventBattleCount;
    }
}
