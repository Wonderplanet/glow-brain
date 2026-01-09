<?php

namespace Tests\Feature\Domain\Auth;

use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Tests\TestCase;

class AccessTokenServiceTest extends TestCase
{
    private const DUMMY_USER_ID = '1';

    private const DUMMY_DEVICE_ID = 'device1';

    private const DUMMY_ACCESS_TOKEN = '0000000000000000000000000000000000000000000000000000000000000000';

    private AccessTokenService $accessTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->accessTokenService = $this->app->make(AccessTokenService::class);
        $this->accessTokenService->create(self::DUMMY_USER_ID, self::DUMMY_DEVICE_ID, self::DUMMY_ACCESS_TOKEN);
    }

    public function testFindUser_保存したアクセストークンからuser_idを検索できる()
    {
        // Exercise
        $result = $this->accessTokenService->findUser(self::DUMMY_ACCESS_TOKEN);

        // Verify
        $this->assertEquals(self::DUMMY_USER_ID, $result->getUsrUserId());
        $this->assertEquals(self::DUMMY_DEVICE_ID, $result->getDeviceId());
    }

    public function testFindUser_不正なアクセストークンの場合は戻り値がnull()
    {
        // Exercise
        $result = $this->accessTokenService->findUser('hoge');

        // Verify
        $this->assertNull($result);
    }

    public function testFindUser_逆引き検索に失敗した場合は戻り値がnull()
    {
        // Setup
        $accessToken = 'hoge';
        $userId = '2';
        $this->setToRedis('token:userid:' . $accessToken, $userId);

        // Exercise
        $result = $this->accessTokenService->findUser($accessToken);

        // Verify
        $this->assertNull($result);
    }

    public function testFindUser_同じユーザーIDの別端末でログインされたらアクセストークンが無効となる()
    {
        $usrUserId = 'user1';
        $deviceId1 = 'device1';
        $deviceId2 = 'device2';

        // 1端末目
        $accessToken = $this->accessTokenService->create($usrUserId, $deviceId1);
        $accessTokenUser = $this->accessTokenService->findUser($accessToken);

        $this->assertNotNull($accessTokenUser);
        $this->assertEquals($usrUserId, $accessTokenUser->getUsrUserId());
        $this->assertEquals($deviceId1, $accessTokenUser->getDeviceId());

        // 2端末目
        $accessToken2 = $this->accessTokenService->create($usrUserId, $deviceId2);
        $accessTokenUser2 = $this->accessTokenService->findUser($accessToken2);

        $this->assertNotNull($accessTokenUser2);
        $this->assertEquals($usrUserId, $accessTokenUser2->getUsrUserId());
        $this->assertEquals($deviceId2, $accessTokenUser2->getDeviceId());

        // 1端末目のアクセストークンは無効になり、専用例外が投げられる
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MULTIPLE_DEVICE_LOGIN_DETECTED);
        $this->accessTokenService->findUser($accessToken);
    }

    public function testFindByUserId_user_idからアクセストークンを取得できる()
    {
        // Exercise
        $result = $this->accessTokenService->findByUserId(self::DUMMY_USER_ID);

        // Verify
        $this->assertEquals(self::DUMMY_ACCESS_TOKEN, $result);
    }

    public function testCreate_アクセストークンが作成される()
    {
        // Setup
        $userId = '2';

        // Exercise
        $result = $this->accessTokenService->create($userId, self::DUMMY_DEVICE_ID, self::DUMMY_ACCESS_TOKEN);

        // Verify
        $this->assertEquals(self::DUMMY_ACCESS_TOKEN, $result);
    }

    public function testDelete_アクセストークンが削除される()
    {
        // Exercise
        $this->accessTokenService->delete(self::DUMMY_USER_ID);

        // Verify
        $result = $this->accessTokenService->findUser(self::DUMMY_ACCESS_TOKEN);
        $this->assertNull($result);

        $accessToken = $this->accessTokenService->findByUserId(self::DUMMY_USER_ID);
        $this->assertNull($accessToken);
    }
}
