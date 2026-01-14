<?php

declare(strict_types=1);

namespace App\Domain\Auth\Guards;

use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * セッション認証機能の独自実装
 * Laravel標準の仕組み（Laravel Sanctum）だとDB管理だが、キャッシュ管理にしたかったので自作した
 * クロージャリクエストガードとしてAuthServiceProviderで登録することを想定
 * https://readouble.com/laravel/9.x/ja/authentication.html#closure-request-guards
 */
class AccessTokenAuthentication
{
    public function __construct(
        private AccessTokenService $accessTokenService,
    ) {
    }

    public function __invoke(Request $request): ?CurrentUser
    {
        $access_token = $request->header(System::HEADER_ACCESS_TOKEN);
        if ($access_token === null) {
            throw new GameException(ErrorCode::VALIDATION_ERROR);
        }

        // 有効なアクセストークンか確認
        $accessTokenUser = $this->accessTokenService->findUser($access_token);
        if ($accessTokenUser === null) {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }

        $usrUserId = $accessTokenUser->getUsrUserId();

        /**
         * 有効なユーザーか確認
         *
         * ここでは下記の理由で特例として、Repositoryを介さず、クエリビルダを使って、DBから直接取得しています。
         * - UsrModelManagerインスタンスが生成されるのはユーザー認証(本処理)終了後のため
         * - EloquentModelを使うことによるオーバーヘッドを少しでも減らすため
         */
        $usrUser = DB::table('usr_users')->where('id', $usrUserId)->first();
        if ($usrUser === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        // 互換性のため、CurrentUserに変換して返す
        return new CurrentUser(
            $usrUserId,
            $usrUser->game_start_at,
            $usrUser->status,
            $usrUser->suspend_end_at,
        );
    }
}
