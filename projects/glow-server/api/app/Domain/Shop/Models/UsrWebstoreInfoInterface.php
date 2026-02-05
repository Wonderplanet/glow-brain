<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrWebstoreInfoInterface extends UsrModelInterface
{
    public function getCountryCode(): string;

    public function getOsPlatform(): ?string;

    public function setOsPlatform(string $osPlatform): void;

    public function getAdId(): ?string;

    public function setAdId(string $adId): void;
}
