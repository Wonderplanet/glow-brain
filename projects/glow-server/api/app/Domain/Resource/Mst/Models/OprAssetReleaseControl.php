<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Models\Contracts\OprAssetReleaseControlInterface;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OprAssetReleaseControl extends MstModel implements OprAssetReleaseControlInterface
{
    use HasUuids;
    use HasFactory;

    public $timestamps = true;

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getBranch(): string
    {
        return $this->branch;
    }

    public function getReleaseAt(): string
    {
        return $this->release_at;
    }

    public function getReleaseDescription(): string
    {
        return $this->release_description;
    }

    public function getVersionNo(): string
    {
        return (string) $this->version_no;
    }

    public function getUrl(): string
    {
        return $this->branch . '/' . $this->hash;
    }

    public function isRequireUpdate(string $hash): bool
    {
        return $this->hash !== $hash;
    }
}
