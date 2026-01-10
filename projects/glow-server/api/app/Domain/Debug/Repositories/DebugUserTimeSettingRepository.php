<?php

declare(strict_types=1);

namespace App\Domain\Debug\Repositories;

use App\Domain\Debug\Entities\DebugUserTimeSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class DebugUserTimeSettingRepository
{
    public function put(string $userId, DebugUserTimeSetting $debugUserTimeSetting): bool
    {
        CarbonImmutable::setTestNow($debugUserTimeSetting->getUserTime());

        return Cache::put($this->getCacheKey($userId), $debugUserTimeSetting);
    }

    public function get(string $userId): ?DebugUserTimeSetting
    {
        return Cache::get($this->getCacheKey($userId));
    }

    public function delete(string $userId): bool
    {
        CarbonImmutable::setTestNow(null);

        return Cache::forget($this->getCacheKey($userId));
    }

    public function exists(string $userId): bool
    {
        $value = $this->get($userId);
        return $value !== null;
    }

    private function getCacheKey(string $userId): string
    {
        return 'debug:UserTime:' . $userId;
    }
}
