<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\ContentMaintenanceService;
use Closure;
use Illuminate\Http\Request;

/**
 * コンテンツメンテナンス状態をチェックするミドルウェア
 * 
 * リクエストパスから自動的にコンテンツタイプを判定し、
 * リクエストパラメータから対応するIDを取得してメンテナンス状態をチェックする
 * メンテナンス中の場合は、エラーを返してAPI処理を中断する
 * 
 * パフォーマンス向上のため、routes/api.phpでメンテナンス対象のドメインのみに適用し、
 * cleanupAPIは除外して使用することを推奨
 * 
 * 使用例:
 * // メンテナンス対象のAPI群
 * Route::middleware(['content_maintenance_check'])->group(function () {
 *     Route::post('/stage/start', [StageController::class, 'start']);
 *     Route::post('/stage/end', [StageController::class, 'end']);
 * });
 * 
 * // cleanupAPIは除外
 * Route::controller(StageController::class)->group(function () {
 *     Route::post('/stage/cleanup', 'cleanup');
 * });
 */
readonly class ContentMaintenanceCheck
{
    private const NEED_CLEANUP_API_LIST = [
        'api/stage/end' => true,
        'api/stage/continue' => true,
        'api/stage/continue_ad' => true,
        'api/stage/abort' => true,

        'api/pvp/end' => true,
        'api/pvp/abort' => true,
        'api/pvp/resume' => true,

        'api/advent_battle/end' => true,
        'api/advent_battle/abort' => true,
    ];

    private const CLEANUP_API_LIST = [
        'api/stage/cleanup' => true,
        'api/pvp/cleanup' => true,
        'api/advent_battle/cleanup' => true,
    ];

    public function __construct(
        private ContentMaintenanceService $contentMaintenanceService,
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
        $result = $this->contentMaintenanceService->checkMaintenanceStatus($request);

        // cleanupAPIの場合は、部分メンテナンス外でエラー
        if ($this->isCleanupApi($request->path())) {
            if ($result->shouldBlockCleanupAccess()) {
                throw new GameException(ErrorCode::CONTENT_MAINTENANCE_OUTSIDE);
            }
        // それ以外のAPIの場合は、メンテナンス中でエラー
        } else {
            if ($result->shouldBlockAccess()) {
                $errorCode = $this->getErrorCode($request->path());
                throw new GameException($errorCode);
            }
        }

        return $next($request);
    }

    private function getErrorCode(string $path): int
    {
        // cleanupAPIを叩く必要がある場合はCONTENT_MAINTENANCE_NEED_CLEANUPを返す
        if (isset(self::NEED_CLEANUP_API_LIST[$path])) {
            return ErrorCode::CONTENT_MAINTENANCE_NEED_CLEANUP;
        }
        return ErrorCode::CONTENT_MAINTENANCE;
    }

    private function isCleanupApi(string $path): bool
    {
        return isset(self::CLEANUP_API_LIST[$path]);
    }
}
