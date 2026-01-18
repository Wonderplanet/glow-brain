<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

use App\Domain\Mission\Enums\MissionType;

interface MstMissionEntityInterface
{
    public function getId(): string;

    public function getReleaseKey(): int;

    public function getCriterionType(): string;

    public function getCriterionValue(): ?string;

    public function getCriterionCount(): int;

    public function getUnlockCriterionType(): ?string;

    public function getUnlockCriterionValue(): ?string;

    public function getUnlockCriterionCount(): int;

    public function getGroupKey(): ?string;

    public function getBonusPoint(): int;

    public function getMstMissionRewardGroupId(): string;

    public function getEventCategory(): ?string;

    public function getSortOrder(): int;

    public function hasUnlockCriterion(): bool;

    public function getCriterionKey(): string;

    public function getUnlockCriterionKey(): ?string;

    public function isCompositeMission(): bool;

    public function isBonusPointMission(): bool;

    public function getMissionType(): MissionType;

    /**
     * ミッション進捗データをグルーピングしてレスポンスするために使うデータを返す
     *
     * 例：イベントミッションの場合
     *   - イベントごとに表示を分けているので、mst_event_id ごとにグルーピングしてレスポンスする
     */
    public function getResponseGroupId(): ?string;
}
