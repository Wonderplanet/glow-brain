<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models\Contracts;

interface OprAssetReleaseControlInterface
{
    public function getHash(): string;

    public function getUrl(): string;

    public function getVersionNo(): string;

    public function isRequireUpdate(string $hash): bool;

    public function getPlatform(): int;
}
