<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Auth\Delegators\AuthDelegator;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\CacheService;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Log\Services\LogBankService;
use App\Domain\User\Delegators\UserDelegator;
use App\Domain\User\Enums\BnidLinkActionType;
use App\Domain\User\Models\UsrUserInterface;
use App\Domain\User\Repositories\LogBnidLinkRepository;
use App\Domain\User\Repositories\UsrUserParameterPublicRepository;
use App\Domain\User\Repositories\UsrUserProfilePublicRepository;
use App\Domain\User\Repositories\UsrUserPublicRepository;
use App\Http\Responses\Data\BnidLinkedUserData;
use App\Http\Responses\Data\LinkBnidData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class UserAccountLinkService
{
    public function __construct(
        // Repository
        private UsrUserParameterPublicRepository $usrUserParameterPublicRepository,
        private UsrUserProfilePublicRepository $usrUserProfilePublicRepository,
        private UsrUserPublicRepository $usrUserPublicRepository,
        private LogBnidLinkRepository $logBnidLinkRepository,
        // Service
        private \App\Domain\User\Repositories\UsrUserRepository $userService,
        // Common
        private LogBankService $logBankService,
        private CacheService $cacheService,
        // Delegator
        private AuthDelegator $authDelegator,
        private MissionDelegator $missionDelegator,
        private UserDelegator $userDelegator,
    ) {
    }

    public function linkBnidConfirm(string $code, string $ip): BnidLinkedUserData
    {
        $bnidUserId = $this->fetchBnidUserId($code, $ip);

        $bnidLinkedUsrUser = $this->usrUserPublicRepository->getByBnUserId($bnidUserId);
        if (is_null($bnidLinkedUsrUser)) {
            // 連携済みのアカウントがない
            $name = null;
            $level = null;
            $myId = null;
        } else {
            $this->validateAccountLinkingRestriction($bnidLinkedUsrUser, myAccount: false);

            // 連携済みのアカウントがあるのでデバイスを追加し連携済みユーザーでログインするためにIDトークンを発行
            $usrUserId = $bnidLinkedUsrUser->getId();
            $usrUserParameter = $this->usrUserParameterPublicRepository->findByUsrUserId($usrUserId);
            $usrUserProfile = $this->usrUserProfilePublicRepository->findByUsrUserId($usrUserId);
            $name = $usrUserProfile->getName();
            $level = $usrUserParameter->getLevel();
            $myId = $usrUserProfile->getMyId();
        }
        return new BnidLinkedUserData($name, $level, $myId);
    }

    /**
     * @param string          $usrUserId
     * @param string          $platform
     * @param string          $code
     * @param bool            $isHome true: ホーム画面からの呼び出し false: タイトル画面からの呼び出し
     * @param string          $accessToken
     * @param CarbonImmutable $now
     * @return LinkBnidData
     * @throws GameException
     */
    public function linkBnid(
        string $usrUserId,
        string $platform,
        string $code,
        bool $isHome,
        string $accessToken,
        string $ip,
        CarbonImmutable $now
    ): LinkBnidData {
        $bnUserId = $this->fetchBnidUserId($code, $ip);
        $bnIdLinkedUsrUser = $this->usrUserPublicRepository->getByBnUserId($bnUserId);
        $beforeBnUserId = $bnIdLinkedUsrUser?->getBnUserId();
        if (is_null($bnIdLinkedUsrUser)) {
            // 未連携のため連携情報を登録
            $this->userService->linkBnid($usrUserId, $bnUserId);
            $usrDeviceId = $this->authDelegator->linkBnidByAccessToken($accessToken, $now);
            $idToken = null;
        } else {
            if ($isHome) {
                // ホーム画面からの場合は連携済のBNIDへの紐づけはできない
                throw new GameException(
                    ErrorCode::USER_BNID_LINKED_OTHER_USER,
                    'bnid user is already linked to another user'
                );
            }
            $this->validateAccountLinkingRestriction($bnIdLinkedUsrUser, myAccount: false);

            // usr_os_platformsテーブルにプラットフォーム情報がない場合は登録
            $this->userDelegator->createUsrOsPlatformPublicIfEmpty($bnIdLinkedUsrUser->getId());

            // 連携済みのアカウントがあるのでデバイスを追加し連携済みユーザーでログインするためにIDトークンを発行
            $userDevice = $this->authDelegator->createUserDevice(
                $bnIdLinkedUsrUser->getId(),
                null,
                $now->toDateTimeString(),
                $platform,
            );
            $idToken = $this->authDelegator->createIdToken($userDevice->getUuid());
            $usrDeviceId = $userDevice->getUsrDeviceId();

            // 新しいデバイスが新しいプラットフォームだった場合、Bank用に新規登録Logを生成
            $isNewPlatform = $this->userDelegator->isNewOsPlatform(
                $bnIdLinkedUsrUser->getId(),
                $platform,
            );
            if ($isNewPlatform) {
                // プラットフォーム情報を登録
                $this->userDelegator->createUsrOsPlatformPublic(
                    $bnIdLinkedUsrUser->getId(),
                    $platform
                );
                $this->logBankService->createLogBankRegisteredLinkBnid(
                    $bnIdLinkedUsrUser->getId(),
                    $now
                );
            }
        }
        $bnidLinkedAt = $now->toDateTimeString();

        // 連携ログを登録
        $this->logBnidLinkRepository->create(
            $usrUserId,
            $isHome ? BnidLinkActionType::LINK_FROM_HOME : BnidLinkActionType::LINK_FROM_TITLE,
            $beforeBnUserId,
            $bnUserId,
            $usrDeviceId,
            $platform,
        );

        // AccountCompletedミッションをトリガー
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::ACCOUNT_COMPLETED->value,
                null,
                1,
            )
        );

        return new LinkBnidData($idToken, $bnidLinkedAt);
    }

    private function fetchBnidUserId(string $code, string $ip): string
    {
        $env = config('app.env');
        if ($env === 'local_test') {
            // テストの場合はAPIリクエストを実行しない
            return 'dummy_user_id';
        }

        $bnidUserId = $this->cacheService->getBnidUserIdFromCache($code);
        if (!is_null($bnidUserId)) {
            return $bnidUserId;
        }

        $url = env('BNID_ACCESS_TOKEN_URL', 'https://cp-sys-api.bandainamcoid.com/auth/accessToken');
        $response = Http::withHeaders([
            'X-BNID-RemoteAddress' => $ip,
            'X-BNID-ClientID' => env('X_BNID_CLIENT_ID'),
            'X-BNID-ClientSecret' => env('X_BNID_CLIENT_SECRET'),
            'X-BNID-AuthCode' => $code,
        ])->get($url);

        if ($response->failed()) {
            $status = $response->status();
            throw new GameException(
                ErrorCode::USER_BNID_ACCESS_TOKEN_API_ERROR,
                "Failed to fetch bnid access token. status: $status"
            );
        }

        $bnidUserId = $response->json()['result']['userID'];

        // bnidLinkConfirm→bnidLinkの順で呼ばれる想定ため、何度もリクエスト行わなくて良いようにbnidUserIdをキャッシュしておく
        $this->cacheService->setBnidUserIdToCache($code, $bnidUserId);

        return $bnidUserId;
    }

    public function unlinkBnid(string $usrUserId, string $accessToken, string $platform): void
    {
        $usrUser = $this->userService->findById($usrUserId);
        $beforeBnUserId = $usrUser->getBnUserId();

        $accessTokenUser = $this->authDelegator->findUserByAccessToken($accessToken);
        if ($accessTokenUser === null || $accessTokenUser->getUsrUserId() !== $usrUserId) {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }

        $usrDevice = $this->authDelegator->findUsrDevice($accessTokenUser->getDeviceId());
        if ($usrDevice->getUsrUserId() !== $usrUserId) {
            // アクセストークンから取得したデバイスとユーザーが一致しない
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }

        if ($usrDevice->getBnidLinkedAt() === null) {
            // 未連携のデバイス
            throw new GameException(ErrorCode::USER_BNID_NOT_LINKED);
        }

        $usrDeviceId = $usrDevice->getUsrDeviceId();

        // デバイスのos_platformがusr_os_platformsテーブルに登録されていない場合は登録
        $this->userDelegator->createUsrOsPlatformIfNotRegistered($usrUserId, $platform);

        // 連携解除により端末のアカウント自体がリセットになるのでデバイスとアクセストークンを削除
        $this->authDelegator->deleteUsrDevice($usrDevice->getUsrDeviceId(), $usrUserId);
        $this->authDelegator->deleteAccessToken($usrUserId);

        // 連携解除ログを登録
        $this->logBnidLinkRepository->create(
            $usrUserId,
            BnidLinkActionType::UNLINK,
            $beforeBnUserId,
            null,
            $usrDeviceId,
            $platform
        );
    }

    /**
     * ユーザーのアカウント連携制限を確認
     * @param UsrUserInterface $usrUser
     * @param bool             $myAccount true: 自身のアカウント false: 連携先のアカウント
     * @throws GameException
     */
    public function validateAccountLinkingRestriction(UsrUserInterface $usrUser, bool $myAccount): void
    {
        if ($usrUser->isAccountLinkingRestricted()) {
            $errorCode = $myAccount
                ? ErrorCode::USER_ACCOUNT_LINKING_RESTRICTED_MY_ACCOUNT
                : ErrorCode::USER_ACCOUNT_LINKING_RESTRICTED_OTHER_ACCOUNT;
            throw new GameException($errorCode, 'User account linking is restricted');
        }
    }
}
