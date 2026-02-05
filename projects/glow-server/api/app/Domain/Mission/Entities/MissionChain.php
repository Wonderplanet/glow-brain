<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use Illuminate\Support\Collection;

class MissionChain
{
    /**
     * @var Collection<MissionState>
     */
    private Collection $states;

    /** @var bool これ以上進捗変更がないとみなしたらtrue */
    private bool $isFinalized = false;

    private int $checkedIndex = -1; // -1: 未チェック

    /**
     * @param Collection<MissionState> $orderedStates
     * mst_mission_xxx_dependenciesのunlock_order順に並んだMissionStateのCollection
     */
    public function __construct(
        Collection $orderedStates,
    ) {
        $this->states = $orderedStates->values();
    }

    /**
     * chainの変動がないかどうか
     * true: これ以上の変動はない、false: まだ変動の可能性がある
     *
     * @return boolean
     */
    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }

    /**
     * これ以上の変動がないchainとみなす
     */
    public function markAsFinalized(): void
    {
        $this->isFinalized = true;
    }

    /**
     * 開放判定のために順番に進捗を確認する
     */
    public function stepForOpen(): bool
    {
        if ($this->isFinalized() || $this->states->isEmpty()) {
            // これ以上変動がないchainなので、処理不要
            return $this->isFinalized();
        }

        // チェック済みの次のミッションからチェックを開始する
        $checkFirstIndex = $this->checkedIndex + 1;
        $depth = $this->states->count();

        for ($i = $checkFirstIndex; $i < $depth; $i++) {
            $state = $this->states->get($i);
            if ($state === null) {
                // 判定対象がないなら、これ以上変動がないとみなす
                $this->markAsFinalized();
                break;
            }

            // 開放
            $state->open();

            if ($state->isCompleted()) {
                // 完了済みなら次のミッションを開放するために、次のループへ
                $this->checkedIndex = $i;
                continue;
            } else {
                if (!$state->isCompositeMission()) {
                    /**
                     * 複合ミッションなら、この後の達成進捗判定で達成する可能性があるが、
                     * そうでないミッションなら、達成進捗に変動はもうないので、達成になることはない。
                     * そのため、変動がないchainとみなす
                     */
                    $this->markAsFinalized();
                }
                /**
                 * 未完了なら、次ミッションを開放できないので、chainの進行を中断。
                 * 進行再開時は、再度本ループのstateからチェックを始める。
                 */
                break;
            }
        }

        if (($this->checkedIndex + 1) === $depth) {
            // chain内の全ミッションが完了しているので、これ以上変動がないchainとみなす
            $this->markAsFinalized();
        }

        return $this->isFinalized();
    }

    /**
     * chain中の未達成の複合ミッションの数を返す
     * @return int
     */
    public function calcUnclearCompositeMissionCount(): int
    {
        if ($this->isFinalized()) {
            return 0;
        }

        return $this->states->filter(function (MissionState $state) {
            return $state->isCompositeMission() && !$state->isClear();
        })->count();
    }
}
