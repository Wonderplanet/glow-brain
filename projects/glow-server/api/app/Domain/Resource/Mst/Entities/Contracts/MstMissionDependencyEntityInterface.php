<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

interface MstMissionDependencyEntityInterface
{
    public function getId(): string;

    public function getGroupId(): string;

    public function getMstMissionId(): string;

    public function getUnlockOrder(): int;
}
