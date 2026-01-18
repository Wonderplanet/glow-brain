<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

use App\Domain\Mission\Enums\MissionType;

interface MstMissionEntityReceiveRewardInterface
{
    public function getId(): string;

    public function getBonusPoint(): int;

    public function getMstMissionRewardGroupId(): string;

    public function getMissionType(): MissionType;

    public function getStartAt(): string;

    public function getEndAt(): string;
}
