<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Entities\Criteria\MissionCriterion;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;

/**
 * ミッションマスタデータ1つに対する進捗状況を管理するクラス
 *
 * 進捗ステータスの種類
 * - 開放済: 下記のいずれかの状態
 *   - 開放条件設定がなく、初期開放されている状態
 *   - unlock_criterion_type,value,count の条件を達成した状態
 *   - 依存関係グループに含まれるミッションで、開放順が1つ前のミッションを達成した状態
 * - 達成済: criterion_type,value,count の条件を達成した状態
 * - 完了済: 開放済 かつ 達成済。報酬受取可能な状態
 */
class MissionState
{
    /** @var bool 達成したかどうか */
    private bool $isCleared = false;

    /** @var bool 判定する前から達成済みかどうか。true: 判定前から達成済み、false: 判定前は未達成 */
    private bool $isAlreadyCleared = false;

    /** @var bool 複合ミッションの進捗値に加算されたかどうか */
    private bool $isAddedCompositeMissionProgress = false;

    /**
     * 開放済みかどうか
     * true: 開放済み、false: 未開放
     *
     * ※ 未開放であっても進捗値とステータス更新は行います
     * @var bool
     */
    private bool $isOpened = false;

    /** @var bool 判定する前から開放済みかどうか。true: 判定前から開放済み、false: 判定前は未開放 */
    private bool $isAlreadyOpened = false;

    /**
     * @var bool ミッション依存関係グループ設定によって未開放状態になっているかどうか
     * false: 依存関係によってロックされていない。unlock_criterion_type,value,countによる開放条件設定がなければ、開放済
     * true:  依存関係によってロック中。依存関係グループのうちで1つ前のミッションをクリアしないと開放できない。
     * */
    private bool $isDependencyLocked = false;

    private MstMissionEntityInterface $mstMission;

    private null|IUsrMission $usrMission;

    /** @var MissionCriterion 新規の進捗値の保持と、達成条件判定ロジックの指定 */
    private MissionCriterion $criterion;

    /** @var ?MissionCriterion 新規の開放用進捗値の保持と、開放条件判定ロジックの指定 */
    private ?MissionCriterion $unlockCriterion;

    public function __construct(
        MstMissionEntityInterface $mstMission,
        null|IUsrMission $usrMission,
        MissionCriterion $criterion,
        ?MissionCriterion $unlockCriterion,
    ) {
        $this->mstMission = $mstMission;
        $this->usrMission = $usrMission;
        $this->criterion = $criterion;
        $this->unlockCriterion = $unlockCriterion;

        $usrMission?->isClear() && $this->alreadyCleared();
        $usrMission?->isOpen() && $this->alreadyOpened();
    }

    public function getMstMission(): MstMissionEntityInterface
    {
        return $this->mstMission;
    }

    public function getMstMissionId(): string
    {
        return $this->getMstMission()->getId();
    }

    public function getUsrMission(): null|IUsrMission
    {
        return $this->usrMission;
    }

    public function getCriterion(): MissionCriterion
    {
        return $this->criterion;
    }

    /**
     * マスタデータの条件値を最良の進捗値とみなして、その値からはみ出ていない値を取得する
     * @return int
     */
    public function getProgress(): int
    {
        return $this->criterion->getBestProgress(
            $this->getMstMission()->getCriterionCount(),
        );
    }

    public function getUnlockProgress(): int
    {
        $mstMission = $this->getMstMission();
        if (
            !$mstMission->hasUnlockCriterion()
            || $this->unlockCriterion === null
        ) {
            return MissionConstant::PROGRESS_INITIAL_VALUE;
        }

        return $this->unlockCriterion->getBestProgress(
            $mstMission->getUnlockCriterionCount(),
        );
    }

    /**
     * 達成済みかどうか
     */
    public function isClear(): bool
    {
        return $this->isCleared;
    }

    /**
     * 完了済み(開放済みかつ達成済み)かどうか
     * true: 完了済み、false: 未完了
     */
    public function isCompleted(): bool
    {
        return $this->isClear() && $this->isOpen();
    }

    /**
     * 達成可能かどうか
     */
    private function canClear(): bool
    {
        if ($this->isClear()) {
            return true;
        }

        return $this->criterion->canClear(
            $this->getMstMission()->getCriterionValue(),
            $this->getMstMission()->getCriterionCount(),
        );
    }

    /**
     * 「達成」ステータスに変更する
     */
    private function clear(): void
    {
        $this->isCleared = true;
    }

    /**
     * 既に判定前から達成済みで、進捗判定が不要な場合に実行して、
     * 「既にクリア済み」ステータスにする
     */
    private function alreadyCleared(): void
    {
        $this->isAlreadyCleared = true;
        $this->isCleared = true;
    }

    /**
     * 達成可能かどうかを確認し、可能なら達成する
     */
    public function checkAndClear(): void
    {
        if ($this->isClear()) {
            return;
        }

        if ($this->canClear()) {
            $this->clear();
        }
    }

    /**
     * 達成と開放の判定を実行し、必要ならステータス更新する
     */
    public function checkAndUpdate(): void
    {
        if ($this->isCompleted()) {
            // 開放済みかつ達成済みなら、この後の処理は不要
            return;
        }

        // 達成判定
        $this->checkAndClear();

        // 開放判定
        // 未開放でも、達成判定と進捗更新は行うので、達成判定の後に実行しています
        $this->checkAndOpen();
    }

    /**
     * 開放済みかどうか
     *
     * @return boolean
     */
    public function isOpen(): bool
    {
        return $this->isOpened;
    }

    /**
     * 既に判定前から開放済みで、開放進捗判定が不要な場合に実行して、
     * 「既に開放済み」ステータスにする
     */
    private function alreadyOpened(): void
    {
        $this->isAlreadyOpened = true;
        $this->isOpened = true;
    }

    /**
     * 開放
     */
    public function open(): void
    {
        $this->isOpened = true;
        $this->isDependencyLocked = false;
    }

    /**
     * 開放可能かどうか
     */
    public function canOpen(): bool
    {
        if ($this->isOpen()) {
            return true;
        }

        if ($this->isDependencyLocked()) {
            // 依存関係グループ設定によってロックされているので、開放できない
            return false;
        }

        $mstMission = $this->getMstMission();

        // 開放条件がないなら初期開放されている
        if ($mstMission->hasUnlockCriterion() === false) {
            return true;
        }

        // 開放条件があるはずだが、unlockCriterionがないので、開放させない
        // 開放条件がトリガーされていない場合に、nullとするケースがあり、その際に開放させないため
        if ($this->unlockCriterion === null) {
            return false;
        }

        return $this->unlockCriterion->canClear(
            $mstMission->getUnlockCriterionValue(),
            $mstMission->getUnlockCriterionCount(),
        );
    }

    /**
     * 開放可能かどうかを確認し、可能なら開放する
     */
    public function checkAndOpen(): void
    {
        if ($this->isOpen()) {
            return;
        }

        if ($this->canOpen()) {
            $this->open();
        }
    }

    /**
     * 依存関係グループによってロック中の状態としてマークする
     */
    public function dependencyLock(): void
    {
        $this->isDependencyLocked = true;
    }

    public function isDependencyLocked(): bool
    {
        return $this->isDependencyLocked;
    }

    /**
     * ユーザーデータの更新が不要かどうか
     * true: いらない、false: いる
     *
     * 判定前から既に完了済であれば、更新が不要
     */
    public function isUpdateNotNeeded(): bool
    {
        return $this->isAlreadyCleared && $this->isAlreadyOpened;
    }

    /**
     * 初クリアかどうか
     * true: 初クリア、false: 既にクリア済みまたは未クリア
     */
    private function isFirstClear(): bool
    {
        return $this->isAlreadyCleared === false && $this->isClear();
    }

    /**
     * 初開放かどうか
     * true: 初開放、false: 既に開放済みまたは未開放
     * @return bool
     */
    private function isFirstOpen(): bool
    {
        return $this->isAlreadyOpened === false && $this->isOpen();
    }

    /**
     * 初完了かどうか
     * true: 初完了、false: 既に完了済みまたは未完了
     *
     * 開放と達成には決まった順序がないので、初クリアと初開放のORで条件を確認しています。
     * 未開放中も進捗は進み、達成状態になるケースがあるため
     *
     * @return bool
     */
    private function isFirstCompleted(): bool
    {
        return $this->isCompleted() && ($this->isFirstClear() || $this->isFirstOpen());
    }

    /**
     * 複合ミッションかどうか
     */
    public function isCompositeMission(): bool
    {
        return $this->getMstMission()->isCompositeMission();
    }

    /**
     * 複合ミッションの進捗値に加算したら実行して、加算済みフラグを立てて、重複加算しないようにする
     */
    public function markAddedCompositeMissionProgress(): void
    {
        $this->isAddedCompositeMissionProgress = true;
    }

    /**
     * 複合ミッションの進捗値に加算して良いかどうか
     * true: 加算して良い、false: すでに加算済みなので加算しない
     */
    public function isAddableCompositeMissionProgress(): bool
    {
        return MissionCriterionType::isCountableForCompositeMission($this->criterion->getType()->value)
            && $this->isAddedCompositeMissionProgress === false
            && $this->isFirstCompleted(); // 達成していても未開放なら、加算しない
    }
}
