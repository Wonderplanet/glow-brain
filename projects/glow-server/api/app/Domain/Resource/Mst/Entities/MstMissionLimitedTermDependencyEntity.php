<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Resource\Mst\Entities\Contracts\MstMissionDependencyEntityInterface;

class MstMissionLimitedTermDependencyEntity implements MstMissionDependencyEntityInterface
{
    public function __construct(
        private string $id,
        private string $groupId,
        private string $mstMissionLimitedTermId,
        private int $unlockOrder,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getMstMissionId(): string
    {
        return $this->mstMissionLimitedTermId;
    }

    public function getUnlockOrder(): int
    {
        return $this->unlockOrder;
    }
}
