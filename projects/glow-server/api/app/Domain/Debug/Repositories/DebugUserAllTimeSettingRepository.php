<?php

declare(strict_types=1);

namespace App\Domain\Debug\Repositories;

use App\Domain\Debug\Entities\DebugUserAllTimeSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class DebugUserAllTimeSettingRepository
{
    public function put(DebugUserAllTimeSetting $debugUserAllTimeSetting): bool
    {
        CarbonImmutable::setTestNow($debugUserAllTimeSetting->getUserAllTime());

        return Cache::put($this->getCacheKey(), $debugUserAllTimeSetting);
    }

    public function get(): ?DebugUserAllTimeSetting
    {
        return Cache::get($this->getCacheKey());
    }

    public function delete(): bool
    {
        CarbonImmutable::setTestNow(null);

        return Cache::forget($this->getCacheKey());
    }

    public function exists(): bool
    {
        $value = $this->get();
        return $value !== null;
    }

    private function getCacheKey(): string
    {
        return 'debug:UserAllTime';
    }
}
