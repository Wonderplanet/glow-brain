<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use Illuminate\Support\Collection;

class MissionCompositeClearCount
{
    /**
     * @param integer $allClearCount ミッションのクリア数
     * @param Collection<string, int> $groupClearCounts
     *   key: SpecificMissionClearCountミッションの進捗カウント対象となるグループキー, value: グループ内のクリア数
     */
    public function __construct(
        private int $allClearCount,
        private Collection $groupClearCounts,
    ) {
        $this->allClearCount = $allClearCount;
        $this->groupClearCounts = $groupClearCounts;
    }

    public function getAllClearCount(): int
    {
        return $this->allClearCount;
    }

    public function getGroupClearCount(string $groupKey): int
    {
        return $this->groupClearCounts->get($groupKey, 0);
    }
}
