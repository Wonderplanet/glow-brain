<?php

namespace Tests\Feature\Domain\Auth;

use App\Domain\Auth\Guards\AccessTokenAuthentication;
use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserLogin;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class AccessTokenAuthenticationTest extends TestCase
{
    private const DUMMY_ACCESS_TOKEN = '0000000000000000000000000000000000000000000000000000000000000000';

    private AccessTokenAuthentication $accessTokenAuthentication;
    private AccessTokenService $accessTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->accessTokenAuthentication = $this->app->make(AccessTokenAuthentication::class);
        $this->accessTokenService = $this->app->make(AccessTokenService::class);
    }

    /**
     * @test
     */
    public function __invoke_正規のアクセストークンを渡すとユーザーが返される()
    {
        // Setup
        $user = UsrUser::factory()->create();
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $user->id,
        ]);
        UsrUserLogin::factory()->create([
            'usr_user_id' => $user->id,
        ]);
        $accessToken = $this->accessTokenService->create($user->id, $usrDevice->getId());

        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($accessToken) {
            $mock->shouldReceive('header')
                ->andReturn($accessToken);
        });

        // Exercise
        $result = ($this->accessTokenAuthentication)($mockedRequest);

        // Verify
        $this->assertEquals($user->id, $result->id);
    }

    /**
     * @test
     */
    public function __invoke_存在しないアクセストークンを渡すとExceptionが発生する()
    {
        // Setup
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header')
                ->andReturn(self::DUMMY_ACCESS_TOKEN);
        });

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_ACCESS_TOKEN);

        // Exercise
        ($this->accessTokenAuthentication)($mockedRequest);

        // Verify
        // SetupでexpectExceptionを置いてるので、ここでは何もしない
    }

    public function test__invoke_他端末ログインでアクセストークンが無効化されると専用Exceptionが発生する()
    {
        // Setup - 最初の端末でログイン
        $usrUserId = $this->createUsrUser()->getId();
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $firstAccessToken = $this->accessTokenService->create($usrUserId, $usrDevice->getId());

        // Setup - 2台目の端末でログイン（1台目のトークンを無効化）
        $secondDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $secondAccessToken = $this->accessTokenService->create($usrUserId, $secondDevice->getId());

        // Setup - 1台目のトークンでリクエスト
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($firstAccessToken) {
            $mock->shouldReceive('header')
                ->andReturn($firstAccessToken);
        });

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MULTIPLE_DEVICE_LOGIN_DETECTED);

        // Exercise - 1台目のトークン（無効化済み）で認証試行
        ($this->accessTokenAuthentication)($mockedRequest);

        // Verify
        // SetupでexpectExceptionを置いてるので、ここでは何もしない
    }
}
