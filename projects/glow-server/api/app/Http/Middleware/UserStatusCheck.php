<?php

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Common\Exceptions\GameException;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;

/**
 * ユーザーの利用停止状態をチェックするミドルウェア
 * チェックに引っかかった場合は、エラーを返して、API処理を中断する
 */
class UserStatusCheck
{
    /**
     * @throws GameException
     * @throws BindingResolutionException
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset(System::USER_STATUS_CHECK_THROUGH_API[$request->path()])) {
            // チェック対象外APIの場合
            return $next($request);
        }

        /**
         * @var CurrentUser $user
         */
        $user = $request->user();
        $now = app(Clock::class)->now();
        if ($user->isSuspended($now)) {
            switch ($user->getStatus()) {
                case UserStatus::BAN_TEMPORARY_CHEATING->value:
                    throw new GameException(ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING);
                case UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value:
                    throw new GameException(ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY);
                case UserStatus::BAN_PERMANENT->value:
                    throw new GameException(ErrorCode::USER_ACCOUNT_BAN_PERMANENT);
                case UserStatus::DELETED->value:
                    throw new GameException(ErrorCode::USER_ACCOUNT_DELETED);
                case UserStatus::REFUNDING->value:
                    throw new GameException(ErrorCode::USER_ACCOUNT_REFUNDING);
            }
        }

        return $next($request);
    }
}
