<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Entities\Rewards\StepupGachaStepReward;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * ガシャ1回分(n連)の履歴
 */
class GachaHistory
{
    public function __construct(
        private string $oprGachaId,
        private string $costType,
        private ?string $costId,
        private int $costNum,
        private int $drawCount,
        private CarbonImmutable $playedAt,
        private Collection $results,
        private ?int $stepNumber = null,
        private ?int $loopCount = null,
        private ?Collection $stepRewards = null,
    ) {
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getStepNumber(): ?int
    {
        return $this->stepNumber;
    }

    public function getLoopCount(): ?int
    {
        return $this->loopCount;
    }

    public function getPlayedAt(): CarbonImmutable
    {
        return $this->playedAt;
    }

    /**
     * ステップアップ情報が必要かどうかを判定する
     */
    public function hasStepupInfo(): bool
    {
        return ($this->stepNumber !== null) && ($this->loopCount !== null);
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        $stepupInfo = null;
        if ($this->hasStepupInfo()) {
            $stepupInfo = [
                'stepNumber' => $this->stepNumber,
                'loopCount' => $this->loopCount,
            ];
        }

        $stepRewardsArray = null;
        if ($this->stepRewards !== null && $this->stepRewards->isNotEmpty()) {
            $stepRewardsArray = $this->stepRewards->map(function (StepupGachaStepReward $reward) {
                return $reward->formatToResponse();
            })->toArray();
        }

        return [
            'oprGachaId' => $this->oprGachaId,
            'costType' => $this->costType,
            'costId' => $this->costId,
            'costNum' => $this->costNum,
            'drawCount' => $this->drawCount,
            'playedAt' => StringUtil::convertToISO8601($this->playedAt->toDateTimeString()),
            'results' => $this->results->map(function (GachaReward $reward) {
                return [
                    'sortOrder' => $reward->getSortOrder(),
                    'reward' => $reward->formatToResponse(),
                ];
            })->toArray(),
            'stepupInfo' => $stepupInfo,
            'stepRewards' => $stepRewardsArray,
        ];
    }

    /**
     * シリアライゼーション時のデータを定義
     * PHP 7.4+の__serialize()を使用して後方互換性を確保
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'oprGachaId' => $this->oprGachaId,
            'costType' => $this->costType,
            'costId' => $this->costId,
            'costNum' => $this->costNum,
            'drawCount' => $this->drawCount,
            'playedAt' => $this->playedAt,
            'results' => $this->results,
            'stepNumber' => $this->stepNumber,
            'loopCount' => $this->loopCount,
            'stepRewards' => $this->stepRewards,
        ];
    }

    /**
     * デシリアライゼーション時のデータ復元を定義
     * 旧データ（stepNumber等がない）にも対応
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->oprGachaId = $data['oprGachaId'];
        $this->costType = $data['costType'];
        $this->costId = $data['costId'];
        $this->costNum = $data['costNum'];
        $this->drawCount = $data['drawCount'];
        $this->playedAt = $data['playedAt'];
        $this->results = $data['results'];
        // 後方互換性: 旧データには存在しないプロパティにデフォルト値を設定
        $this->stepNumber = $data['stepNumber'] ?? null;
        $this->loopCount = $data['loopCount'] ?? null;
        $this->stepRewards = $data['stepRewards'] ?? null;
    }
}
