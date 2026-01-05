<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Resource\Entities\AccessTokenUser;
use Illuminate\Support\Str;

class AccessTokenService
{
    private const CACHE_EXPIRE_SECOND = 60 * 60 * 24;

    private const TOKEN_TO_USERID_AND_DEVICEID_CACHEKEY_PREFIX = 'token:userid:deviceid:';

    private const USERID_TO_TOKEN_CACHEKEY_PREFIX = 'userid:token:';

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    public function create(string $usrUserId, string $deviceId, ?string $accessToken = null): string
    {
        $accessToken = $accessToken ?? hash('sha256', Str::random(40));

        // TODO: アクセストークンをハッシュ化した状態で保存する

        // TODO: アクセストークンの重複チェック

        // TODO: アクセストークンからUserDeviceを引けるようにしておく
        // 他端末でのプレイを想定した場合、どのOSでプレイ中かを判断するには、
        // どの端末でログインしているかの情報が必要なので
        $client = $this->cacheClientManager->getCacheClient();
        $client->set(
            self::TOKEN_TO_USERID_AND_DEVICEID_CACHEKEY_PREFIX . $accessToken,
            $usrUserId . ',' . $deviceId,
            self::CACHE_EXPIRE_SECOND
        );
        $client->set(
            self::USERID_TO_TOKEN_CACHEKEY_PREFIX . $usrUserId,
            $accessToken,
            self::CACHE_EXPIRE_SECOND
        );

        return $accessToken;
    }

    public function findUser(string $accessToken): ?AccessTokenUser
    {
        $userIdAndDeviceId = $this->cacheClientManager->getCacheClient()->get(
            self::TOKEN_TO_USERID_AND_DEVICEID_CACHEKEY_PREFIX . $accessToken
        );
        if ($userIdAndDeviceId === null) {
            return null;
        }

        $userIdAndDeviceIdArray = explode(',', $userIdAndDeviceId);
        if (count($userIdAndDeviceIdArray) !== 2) {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }
        [$userId, $deviceId] = $userIdAndDeviceIdArray;
        if ($userId === '' || $deviceId === '') {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }

        // 複数のデバイスで同時にアクセスされる可能性があるため、
        // 念のため、逆引きして有効なアクセストークンか確認
        $validAccessToken = $this->findByUserId($userId);
        if ($accessToken !== $validAccessToken) {
            // 複数端末ログイン検出時は専用エラーコードを投げる
            throw new GameException(ErrorCode::MULTIPLE_DEVICE_LOGIN_DETECTED);
        }

        return new AccessTokenUser($userId, $deviceId);
    }

    public function findByUserId(string $userId): ?string
    {
        return $this->cacheClientManager->getCacheClient()->get(self::USERID_TO_TOKEN_CACHEKEY_PREFIX . $userId);
    }

    public function delete(string $userId): void
    {
        $accessToken = $this->findByUserId($userId);
        $client = $this->cacheClientManager->getCacheClient();
        $client->del(self::TOKEN_TO_USERID_AND_DEVICEID_CACHEKEY_PREFIX . $accessToken);
        $client->del(self::USERID_TO_TOKEN_CACHEKEY_PREFIX . $userId);
    }
}
