<?php

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use Closure;
use Illuminate\Http\Request;

class CrossDayCheck
{
    public function __construct(
        private UsrUserLoginRepository $usrUserLoginRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset(System::CROSS_DAY_CHECK_THROUGH_API[$request->path()])) {
            // チェック対象外APIの場合
            return $next($request);
        }

        $user = $request->user();
        if (is_null($user)) {
            // ユーザー取得できなかった場合
            return $next($request);
        }
        $usrUserLogin = $this->usrUserLoginRepository->get($user->getUsrUserId());
        if (is_null($usrUserLogin) || is_null($usrUserLogin->getLastLoginAt())) {
            // ログイン情報が取得できなかった場合
            return $next($request);
        }
        if ($this->clock->isFirstToday($usrUserLogin->getLastLoginAt())) {
            // 日跨ぎしている場合
            throw new GameException(ErrorCode::CROSS_DAY, 'cross day');
        }

        return $next($request);
    }
}
