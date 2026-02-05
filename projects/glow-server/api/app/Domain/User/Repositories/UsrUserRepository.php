<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserInterface;
use Carbon\CarbonImmutable;

class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;

    /**
     * 他のユーザーテーブルとは違い、ユーザーIDの列名がusr_user_idではなくidであるため、
     * 直接DBから取得するメソッドをオーバーライドする
     * @param string $usrUserId
     */
    protected function dbSelectOne(string $usrUserId): ?UsrUserInterface
    {
        return UsrUser::query()->where('id', $usrUserId)->first();
    }

    /**
     * @throws GameException
     * @api
     */
    public function findById(string $userId): UsrUserInterface
    {
        $user = $this->cachedGetOne($userId);
        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * モデルインスタンスの生成のみを実行する
     */
    public function make(CarbonImmutable $now, ?string $clientUuid = null): UsrUserInterface
    {
        $usrUser = new UsrUser();
        $usrUser->status = UserStatus::NORMAL->value;
        $usrUser->tutorial_status = '';
        $usrUser->tos_version = 0;
        $usrUser->privacy_policy_version = 0;
        $usrUser->global_consent_version = 0;
        $usrUser->iaa_version = 0;
        $usrUser->bn_user_id = null;
        $usrUser->client_uuid = $clientUuid;
        $usrUser->suspend_end_at = null;
        $usrUser->game_start_at = $now->toDateTimeString();

        return $usrUser;
    }

    /**
     * BNID連携情報を設定する
     * @param string          $usrUserId
     * @param string          $bnUserId
     * @return void
     * @throws GameException
     */
    public function linkBnid(string $usrUserId, string $bnUserId): void
    {
        $usrUser = $this->findById($usrUserId);
        $usrUser->setBnUserId($bnUserId);
        $this->syncModel($usrUser);
    }

    /**
     * 直近に指定client_uuidで作成されたユーザーを取得する
     *
     * APIリクエストしたユーザーとは別ユーザーのデータを取得するケースがあるので、
     * ユーザーキャッシュを介さずに、DBから直接取得する
     *
     * @param string $clientUuid
     * @return UsrUserInterface|null
     */
    public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
    {
        return UsrUser::where('client_uuid', $clientUuid)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * バンダイナムコIDからユーザーを検索する
     *
     * @param string $bnUserId
     * @return UsrUserInterface|null
     */
    public function findByBnUserId(string $bnUserId): ?UsrUserInterface
    {
        return UsrUser::where('bn_user_id', $bnUserId)->first();
    }
}
