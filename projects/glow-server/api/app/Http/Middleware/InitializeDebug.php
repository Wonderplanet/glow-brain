<?php

namespace App\Http\Middleware;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;

class InitializeDebug
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = null;
        try {
            $user = $request->user();
        } catch (GameException $e) {
            // 認証エラーが発生した場合はそのまま継続
        }
        $this->initializeDebugClock($user?->getUsrUserId());

        return $next($request);
    }

    private function initializeDebugClock(?string $userId): void
    {
        if (isset($userId)) {
            // デバッグユーザー個別時刻設定を取得
            $debugUserTimeSettingRepository = app()->make(DebugUserTimeSettingRepository::class);
            $debugSetting = $debugUserTimeSettingRepository->get($userId);
            if (isset($debugSetting)) {
                $userTime = $debugSetting->getUserTime();
                CarbonImmutable::setTestNow($userTime);

                return;
            }
        }
        // デバッグユーザー全体時刻設定を取得
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $debugAllSetting = $debugUserAllTimeSettingRepository->get();
        if (isset($debugAllSetting)) {
            $userAllTime = $debugAllSetting->getUserAllTime();
            CarbonImmutable::setTestNow($userAllTime);
        }
    }
}
