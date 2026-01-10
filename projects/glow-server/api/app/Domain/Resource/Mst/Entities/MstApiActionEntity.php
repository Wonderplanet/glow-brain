<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstApiActionEntity
{
    public function __construct(
        private string $id,
        private string $api_path,
        private ?int $through_master = null,
        private ?int $through_asset = null,
        private ?int $through_app = null,
        private ?int $through_date = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getApiPath(): string
    {
        return $this->api_path;
    }

    public function throughMasterVersionCheck(): bool
    {
        return $this->through_master === 1;
    }

    public function throughAssetVersionCheck(): bool
    {
        return $this->through_asset === 1;
    }

    public function throughAppVersionCheck(): bool
    {
        return $this->through_app === 1;
    }

    public function throughDateCheck(): bool
    {
        return $this->through_date === 1;
    }
}
