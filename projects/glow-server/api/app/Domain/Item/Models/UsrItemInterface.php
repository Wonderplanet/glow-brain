<?php

declare(strict_types=1);

namespace App\Domain\Item\Models;

use App\Domain\Resource\Usr\Entities\UsrItemEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrItemInterface extends UsrModelInterface
{
    public function getMstItemId(): string;

    public function getAmount(): int;

    public function addItemAmount(int $addAmount): void;

    public function setItemAmount(int $amount): void;

    public function subtractItemAmount(int $subtractAmount): void;

    public function toEntity(): UsrItemEntity;
}
