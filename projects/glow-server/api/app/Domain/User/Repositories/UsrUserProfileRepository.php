<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mng\Repositories\MngDeletedMyIdRepository;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Models\UsrUserProfileInterface;
use Illuminate\Support\Collection;

class UsrUserProfileRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserProfile::class;

    private const MAX_MY_ID = 9999999999;
    private const MIN_MY_ID = 1000000000;

    /**
     * @param string $usrUserId
     * @return UsrUserProfileInterface
     * @throws GameException
     * @api
     */
    public function findByUsrUserId(string $usrUserId): UsrUserProfileInterface
    {
        $usrUserProfile = $this->cachedGetOne($usrUserId);
        if ($usrUserProfile === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        return $usrUserProfile;
    }

    /**
     * 他人のデータを取得するメソッドなので、キャッシュを通さない
     *
     * @return Collection<UsrUserProfileInterface>
     */
    public function findByUsrUserIds(Collection $usrUserIds): Collection
    {
        return UsrUserProfile::query()
            ->whereIn('usr_user_id', $usrUserIds)
            ->get();
    }

    public function create(string $usrUserId): UsrUserProfileInterface
    {
        $usrUserProfile = new UsrUserProfile();
        $usrUserProfile->id = $usrUserProfile->newUniqueId();
        $usrUserProfile->name = '';
        $usrUserProfile->usr_user_id = $usrUserId;
        $usrUserProfile->my_id = $this->generateMyId();
        $usrUserProfile->birth_date = '';
        $usrUserProfile->mst_unit_id = '';
        $usrUserProfile->mst_emblem_id = '';

        $this->syncModel($usrUserProfile);

        return $usrUserProfile;
    }

    protected function makeMyIdNumString(): string
    {
        return strval(random_int(self::MIN_MY_ID, self::MAX_MY_ID));
    }

    private function generateMyId(): string
    {
        // 現状海外対応どうするか決めていないので決め打ち
        $prefix = UserConstant::REGION_MY_ID_PREFIX['JP'];
        $mngDeletedMyIdRepository = app()->make(MngDeletedMyIdRepository::class);

        $attempt = 0;
        do {
            $myId = $prefix . $this->makeMyIdNumString();
            $attempt++;
            if ($attempt >= 10) {
                // 無限ループ回避のフェールセーフとして10回生成しても重複があればエラー
                throw new GameException(ErrorCode::USER_CREATE_FAILED, 'Failed to generate myId');
            }
        } while (
            $this->isDuplicateMyId($myId)
            || $mngDeletedMyIdRepository->existsByMyId($myId)
        );
        return $myId;
    }

    /**
     * 生成したmyIdがusr_user_profilesテーブル内で重複しているか
     * @param string $myId
     * @return bool true:重複している false:重複していない
     */
    private function isDuplicateMyId(string $myId): bool
    {
        return UsrUserProfile::query()->where('my_id', $myId)->exists();
    }

    // テストでしか使っていないので、コメントアウト
    // /**
    //  * @param string $myId
    //  * @return UsrUserProfileInterface
    //  * @throws GameException
    //  * @api
    //  */
    // public function findByMyId(string $myId): UsrUserProfileInterface
    // {
    //     $usrUserProfile = UsrUserProfile::query()
    //         ->where('my_id', $myId)
    //         ->first();

    //     if ($usrUserProfile === null) {
    //         throw new GameException(ErrorCode::USER_NOT_FOUND);
    //     }

    //     return $usrUserProfile;
    // }
}
