<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Repositories\Contracts\IMissionCriterion;

abstract class MissionCriterion implements IMissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::NONE;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    /**
     * criterionの大半は達成条件としてのみ利用するので、デフォルトをCLEAR(達成条件)のみにしておく
     *
     * @var array<MissionCriterionConditionType>
     */
    protected array $conditionTypes = [
        MissionCriterionConditionType::CLEAR,
    ];

    private ?string $value = null;

    protected int $progress = 0;
    protected int $initProgress = 0;

    protected bool $isProgressChanged = false;

    public function __construct(
        ?string $value,
        ?int $progress = null,
    ) {
        $this->value = $value;
        $this->progress = $progress ?? $this->initProgress;
    }

    public function getType(): MissionCriterionType
    {
        return $this->type;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getInitProgress(): int
    {
        return $this->initProgress;
    }

    public function markProgressChanged(): void
    {
        $this->isProgressChanged = true;
    }

    /**
     * ユーザーデータの更新が必要かどうか
     * true: 不要、false: 必要
     */
    public function isUpdateNotNeeded(): bool
    {
        return !$this->isProgressChanged;
    }

    public function setProgress(int $progress): void
    {
        $this->progress = $progress;

        $this->markProgressChanged();
    }

    /**
     * 進捗値の集約
     */
    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey(
            $this->type->value,
            $this->value,
        );
    }

    public function getProgressAggregationMethod(): MissionProgressAggregationMethod
    {
        return $this->progressAggregationMethod;
    }

    public function aggregateProgress(int $targetProgress): void
    {
        $this->progress = $this->getAggregatedProgress(
            $this->getProgress(),
            $targetProgress,
        );

        $this->markProgressChanged();
    }

    public function getAggregatedProgress(int $beforeProgress, int $targetProgress): int
    {
        $progresses = collect([$beforeProgress, $targetProgress]);

        switch ($this->getProgressAggregationMethod()) {
            case MissionProgressAggregationMethod::SUM:
                return $progresses->sum();
            case MissionProgressAggregationMethod::MAX:
                return $progresses->max();
            case MissionProgressAggregationMethod::BINARY:
                return $targetProgress > 0 ? 1 : 0;
            case MissionProgressAggregationMethod::SYNC:
                return $targetProgress;
            default:
                return $beforeProgress;
        }
    }

    public function getBestProgress(int $mstProgress): int
    {
        switch ($this->getProgressAggregationMethod()) {
            case MissionProgressAggregationMethod::SUM:
            case MissionProgressAggregationMethod::MAX:
            case MissionProgressAggregationMethod::BINARY:
            case MissionProgressAggregationMethod::SYNC:
                return min($this->getProgress(), $mstProgress);
            default:
                return $this->getProgress();
        }
    }

    /**
     * @return array<MissionCriterionConditionType>
     */
    public function getConditionTypes(): array
    {
        return $this->conditionTypes;
    }

    public function isClearCondition(): bool
    {
        return in_array(
            MissionCriterionConditionType::CLEAR,
            $this->conditionTypes,
        );
    }

    public function isUnlockCondition(): bool
    {
        return in_array(
            MissionCriterionConditionType::UNLOCK,
            $this->conditionTypes,
        );
    }

    /**
     * 達成判定
     */

    abstract public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool;

    protected function isCountGteForTypeAndValue(
        string $mstValue,
        int $mstCount,
    ): bool {
        return $this->value === $mstValue &&
            $this->progress >= $mstCount;
    }

    protected function isCountGteForType(
        int $mstCount,
    ): bool {
        return $this->progress >= $mstCount;
    }

    protected function isValueEqualForType(
        string $mstValue,
    ): bool {
        return $this->value === $mstValue;
    }

    /**
     * valueとcountを見る必要がない達成条件の場合に使用する。
     * 例：review_completed ストアレビューをしたかしてないかが判定基準であり、
     *    その判定をAPIサーバーでは行わないので、MissionTriggerが判定対象となったら、常にtrueを返す
     *
     * @return boolean
     */
    protected function alwaysTrue(): bool
    {
        return true;
    }
}
