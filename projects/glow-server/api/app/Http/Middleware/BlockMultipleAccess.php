<?php

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use Closure;
use Illuminate\Http\Request;

/**
 * 同一リクエストによる多重アクセスをブロックする
 */
class BlockMultipleAccess
{
    private const REQUEST_ID_CACHEKEY_PREFIX = 'requestidlock:';

    private const CACHE_ONE_HOUR_TTL = 3600;

    // パフォーマンス的な観点で、多重アクセスを許容するAPI
    private const ALLOW_ASYNC_EXEC_APIS = [
    ];

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws GameException
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (is_null($user)) {
            // ユーザー情報が取得できない場合はスキップ
            return $next($request);
        }

        // 現状不要の為一旦コメントアウト
        $path = $request->path();
        // @phpstan-ignore-next-line ALLOW_ASYNC_EXEC_APISを設定するまでsail checkをスルーさせる
        if (in_array($path, self::ALLOW_ASYNC_EXEC_APIS, true)) {
            return $next($request);
        }

        $userId = $user->getUsrUserId();
        $uniqueRequestId = $request->header('Unique-Request-Identifier');
        if (is_null($uniqueRequestId)) {
            return $next($request);
        }

        $cacheClient = $this->cacheClientManager->getCacheClient();

        // ユーザーID＋API単位でのロックデータの登録
        $apiLockKey = $this->createRequestIdCacheKey($userId, $uniqueRequestId);
        if ($cacheClient->setIfNotExists($apiLockKey, 1, self::CACHE_ONE_HOUR_TTL) === false) {
            throw new GameException(
                ErrorCode::API_MULTIPLE_ACCESS_ERROR,
                "Multiple access detected for user: {$userId}, request ID: {$uniqueRequestId} on API: {$path}");
        }

        $response = $next($request);

        // ロックデータの削除
        $cacheClient->del($apiLockKey);

        return $response;
    }

    private function createRequestIdCacheKey(string $userId, string $requestId): string
    {
        return self::REQUEST_ID_CACHEKEY_PREFIX . $userId . ':' . $requestId;
    }
}
