<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic認証用ミドルウェア
 */
class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment() === 'local') {
            // ローカル環境では認証を表示しない
            return $next($request);
        }

        // `larastan.noEnvCallsOutsideOfConfig`エラーを無視する
        /** @phpstan-ignore-next-line */
        $basicAuthUser = env('BASIC_AUTH_USER');
        // `larastan.noEnvCallsOutsideOfConfig`エラーを無視する
        /** @phpstan-ignore-next-line */
        $basicAuthPassword = env('BASIC_AUTH_PASSWORD');
        if (blank($basicAuthUser) || blank($basicAuthPassword)) {
            // .envにベーシック認証用の設定がない or 設定が空文字 or 設定がnull だったらエラー
            throw new \Exception('Missing basic auth user or password with env file');
        }

        if (
            $request->getUser() === $basicAuthUser
            && $request->getPassword() === $basicAuthPassword
        ) {
            return $next($request);
        }

        return response('Unauthorized', Response::HTTP_UNAUTHORIZED, ['WWW-Authenticate' => 'Basic']);
    }
}
