<?php

namespace Tests\Unit\Auth;

use App\Domain\Auth\Models\UsrDeviceInterface as UsrDevice;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Auth\Services\UserDeviceService;
use App\Domain\Auth\UseCases\SignInUseCase;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Tests\Unit\BaseUseCaseTestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

class SignInUseCaseTest extends BaseUseCaseTestCase
{
    /**
     * @test
     */
    public function アクセストークンが発行される()
    {
        // Setup
        $userId = '1';
        $deviceId = 'device1';
        $idToken = 'hoge';
        $accessToken = 'fuga';

        // UseCaseの作成
        $userDeviceService = \Mockery::mock(UserDeviceService::class);
        $accessTokenService = \Mockery::mock(AccessTokenService::class);
        $billingDelegator = \Mockery::mock(BillingDelegator::class);
        $useCase = new SignInUseCase($userDeviceService, $accessTokenService);

        // 期待する処理の流れを宣言
        $userDevice = \Mockery::mock(UsrDevice::class);
        $userDeviceService->shouldReceive('findByIdToken')
            ->andReturn($userDevice);

        $userDevice->shouldReceive('getUsrUserId')
            ->andReturn($userId);
        $userDevice->shouldReceive('getId')
            ->andReturn($deviceId);

        $accessTokenService->shouldReceive('create')
            ->andReturn($accessToken);
        $billingDelegator->shouldReceive('getStoreInfo')
            ->andReturn(\Mockery::mock(UsrStoreInfoEntity::class));

        // Exercise
        $result = $useCase($idToken);

        // Verify
        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals($accessToken, $result['access_token']);
    }

    /**
     * @test
     */
    public function IDトークンのパースに失敗すると例外が発生()
    {
        // Setup
        $idToken = 'hoge';

        // UseCaseの作成
        $userDeviceService = \Mockery::mock(UserDeviceService::class);
        $accessTokenService = \Mockery::mock(AccessTokenService::class);
        $billingDelegator = \Mockery::mock(BillingDelegator::class);
        $useCase = new SignInUseCase($userDeviceService, $accessTokenService);

        // 期待する処理の流れを宣言
        $userDeviceService->shouldReceive('findByIdToken')
            ->andThrow(new \UnexpectedValueException());
        $billingDelegator->shouldReceive('getStoreInfo')
            ->andReturn(\Mockery::mock(UsrStoreInfo::class));

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_ID_TOKEN);

        // Exercise
        $useCase($idToken);

        // Verify
    }

    /**
     * @test
     */
    public function ユーザーデバイスが見つからない場合は例外が発生()
    {
        // Setup
        $idToken = 'hoge';

        // UseCaseの作成
        $userDeviceService = \Mockery::mock(UserDeviceService::class);
        $accessTokenService = \Mockery::mock(AccessTokenService::class);
        $billingDelegator = \Mockery::mock(BillingDelegator::class);
        $useCase = new SignInUseCase($userDeviceService, $accessTokenService);

        // 期待する処理の流れを宣言
        $userDeviceService->shouldReceive('findByIdToken')
            ->andReturn(null);
        $billingDelegator->shouldReceive('getStoreInfo')
            ->andReturn(\Mockery::mock(UsrStoreInfo::class));

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_NOT_FOUND);

        // Exercise
        $useCase($idToken);
    }
}
