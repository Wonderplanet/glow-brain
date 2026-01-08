<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities\Contracts;

interface IMstUnitRankUpEntity
{
    public function getAmount(): int;

    public function getUnitMemoryAmount(): int;

    public function getRequireLevel(): int;

    public function getSrMemoryFragmentAmount(): int;

    public function getSsrMemoryFragmentAmount(): int;

    public function getUrMemoryFragmentAmount(): int;

    public function getMstUnitId(): string;

    /**
     * キャラランクアップにキャラ個別メモリーの消費が必要かどうか
     * true: 必要, false: 不要
     */
    public function needUnitMemory(): bool;
}
