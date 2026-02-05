<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Common\Entities\CurrentUser;
use Closure;
use Illuminate\Http\Request;

/**
 * レスポンスにユーザーIDをヘッダーとして追加するミドルウェア
 * nginxログでユーザーIDを記録するために使用
 */
class AddUserIdToResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ユーザーIDを取得してレスポンスヘッダーに追加
        $user = $request->user();
        if ($user instanceof CurrentUser) {
            $response->header('X-User-Id', $user->getUsrUserId());
        }

        return $response;
    }
}
