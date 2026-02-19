<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUserLevelBonusEntity
{
    public function __construct(
        private string $id,
        private int $level,
        private string $mst_user_level_bonus_group_id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getMstUserLevelBonusGroupId(): string
    {
        return $this->mst_user_level_bonus_group_id;
    }
}
