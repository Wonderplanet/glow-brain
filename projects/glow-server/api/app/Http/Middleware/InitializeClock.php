<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * 実行している処理の時間を固定するためのミドルウェア
 *
 * Carbon::setTestNowにより、Carbonの時間を固定する
 */
class InitializeClock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // リクエスト処理中に期間設定の境界を跨ぐのを防ぐため、現在時刻を固定する
        // ユーザー時刻操作の設定が存在する場合は、InitializeDebugのMiddlewareにて上書きされる
        //
        // Octaneでは$_SERVER['REQUEST_TIME']がサービス起動時間で固定されてしまうため、
        // $request->server->getInt('REQUEST_TIME', 0)で取得する。
        // time()はシステム現在時刻になるので厳密には$requestに設定された時刻とずれてしまうので、
        // laravelの処理としては渡されてくる$requestを使用してリクエスト時刻の扱いに一貫性を担保したいと考えた。
        //
        // Octane経由の場合でも、REQUEST_TIMEパラメータは
        // swoole-server内のOpenSwoole\Http\Request時点でリクエストの時刻に設定されており、リクエスト時刻として使用できる。
        $requestTimeUnix = $request->server->getInt('REQUEST_TIME', 0);
        if ($requestTimeUnix === 0) {
            $requestTimeUnix = time();
        }
        $requestTimeCarbon = Carbon::createFromTimestamp($requestTimeUnix);

        // Carbonの時間を固定する
        // Illuminate\Support\Carbon::setTestNowを使用することで、
        // CarbonとCarbonImmutableの両方を設定している。
        // SuportCarbonはlaravelのクラスとなるため、laravelのMiddlewareの実装はこちらを使用する
        Carbon::setTestNow($requestTimeCarbon);

        return $next($request);
    }
}
