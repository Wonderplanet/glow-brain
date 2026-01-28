<?php

namespace Tests\Unit\Auth;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Repositories\UsrDeviceRepository;
use App\Domain\Auth\Services\IdTokenService;
use App\Domain\Auth\UseCases\SignUpUseCase;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Resource\Log\Enums\BankKPIF001EventId;
use App\Domain\Resource\Log\Services\LogBankService;
use App\Domain\Resource\Mng\Models\MngDeletedMyId;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Repositories\MstUserLevelRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Domain\User\Models\UsrOsPlatform;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use Carbon\CarbonImmutable;
use Tests\Support\Traits\TestBankKpiTrait;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class SignUpUseCaseTest extends TestCase
{
    use TestBankKpiTrait;

    /**
     * @test
     */
    public function アカウントが作成される()
    {
        // Setup
        $idToken = 'hoge';
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        $now = CarbonImmutable::now();

        // UseCaseの作成
        $clock = \Mockery::mock(Clock::class);
        $mstUserLevelRepository = \Mockery::mock(MstUserLevelRepository::class);
        $userService = \Mockery::mock(UsrUserRepository::class);
        $usrDeviceRepository = \Mockery::mock(UsrDeviceRepository::class);
        $usrUserLoginRepository = \Mockery::mock(UsrUserLoginRepository::class);
        $idTokenService = \Mockery::mock(IdTokenService::class);
        $currencyDelegator = \Mockery::mock(CurrencyDelegator::class);
        $appCurrencyDelegator = \Mockery::mock(AppCurrencyDelegator::class);
        $usrUserParameterService = \Mockery::mock(UsrUserParameterRepository::class);
        $idleIncentiveDelegator = \Mockery::mock(IdleIncentiveDelegator::class);
        $userDelegator = \Mockery::mock(UserDelegator::class);
        $outpostDelegator = \Mockery::mock(OutpostDelegator::class);
        $emblemDelegator = \Mockery::mock(EmblemDelegator::class);
        $missionDelegator = \Mockery::mock(MissionDelegator::class);
        $logBankService = \Mockery::mock(LogBankService::class);

        $useCase = new SignUpUseCase(
            $this->usrModelManager,
            $clock,
            $mstUserLevelRepository,
            $userService,
            $usrUserLoginRepository,
            $usrDeviceRepository,
            $idTokenService,
            $usrUserParameterService,
            $logBankService,
            $currencyDelegator,
            $appCurrencyDelegator,
            $idleIncentiveDelegator,
            $outpostDelegator,
            $emblemDelegator,
            $missionDelegator,
            $userDelegator,
        );

        // 期待する処理の流れを宣言
        $clock->shouldReceive('now')
            ->andReturn($now);

        $mstUserLevelRepository->shouldReceive('getByLevel')
            ->andReturn(MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity());

        $userService->shouldReceive('make')
            ->andReturn(
                UsrUser::factory()->make(['id' => '1'])
            );
        $userService->shouldReceive('syncModel')->andReturn();
        $userService->shouldReceive('findRecentlyCreatedAtByClientUuid')
            ->andReturn(null);

        $usrDeviceRepository->shouldReceive('create')
            ->andReturn(
                UsrDevice::factory()->make(['usr_user_id' => '1'])
            );

        $idTokenService->shouldReceive('create')
            ->andReturn($idToken);

        $currencyDelegator->shouldReceive('createUser')
            ->andReturn(UsrCurrencySummary::factory()->make()->getModelEntity());

        $appCurrencyDelegator->shouldReceive('getOsPlatform')
            ->with($platform)
            ->andReturn(CurrencyConstants::OS_PLATFORM_ANDROID);
        $appCurrencyDelegator->shouldReceive('addIngameFreeDiamond');
        $usrUserParameterService->shouldReceive('create')
            ->andReturn(
                UsrUserParameter::factory()->make(['usr_user_id' => '1'])
            );
        $usrUserLoginRepository->shouldReceive('create')
            ->andReturn();

        $userDelegator->shouldReceive('createUsrUserProfile')
            ->andReturn();

        $logBankService->shouldReceive('createLogBankRegistered');

        $outpostDelegator->shouldReceive('registerInitialOutpost');

        $emblemDelegator->shouldReceive('registerInitialEmblems');

        $missionDelegator->shouldReceive('unlockMission');

        $idleIncentiveDelegator->shouldReceive('createUsrIdleIncentive');

        $userDelegator->shouldReceive('addFreeDiamond');
        $userDelegator->shouldReceive('addCoin');
        $userDelegator->shouldReceive('getMstUserLevelByLevel')
            ->andReturn(MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity());
        $userDelegator->shouldReceive('createUsrOsPlatform')
            ->andReturn(UsrOsPlatform::factory()->make(['usr_user_id' => '1', 'os_platform' => $platform]));

        // Exercise
        $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

        // Verify
        $this->assertArrayHasKey('id_token', $result);
        $this->assertEquals($idToken, $result['id_token']);
    }

    public function test_exec_正常動作()
    {
        // Setup
        $this->fixTime('2025-04-01 00:00:00');
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity();

        // UseCaseの作成
        /** @var SignUpUseCase $useCase */
        $useCase = app()->make(SignUpUseCase::class);

        // Exercise
        $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

        // Verify
        $this->assertArrayHasKey('id_token', $result);
        $usrUserId = $result['currency_summary']->getUserId();
        $this->checkBankLogByEventId(
            $usrUserId,
            BankKPIF001EventId::USER_REGISTERED->value
        );

        $usrOsPlatform = UsrOsPlatform::query()->where('usr_user_id', $usrUserId)
            ->where('os_platform', $platform)
            ->first();
        $this->assertNotNull($usrOsPlatform);
    }

    public function test_exec_正常動作_リセマラ()
    {
        // Setup
        $now = CarbonImmutable::now();
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity();
        UsrUser::factory()->create([
            'id' => '1',
            'client_uuid' => $clientUuid,
            'created_at' => $now->startOfDay(),
        ]);

        // UseCaseの作成
        /** @var SignUpUseCase $useCase */
        $useCase = app()->make(SignUpUseCase::class);

        // Exercise
        $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

        // Verify
        $this->assertArrayHasKey('id_token', $result);
        // 前のユーザーにevent_id:200が送信されること
        $this->checkBankLogByEventId(
            '1',
            BankKPIF001EventId::USER_DISABLED->value
        );
        // 新規ユーザーにevent_id:100が送信されること
        $this->checkBankLogByEventId(
            $result['currency_summary']->getUserId(),
            BankKPIF001EventId::USER_REGISTERED->value
        );
    }

    public function test_exec_正常動作_ユーザー削除データが有る状態でデータ生成できる()
    {
        // Setup
        $this->fixTime('2025-04-01 00:00:00');
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity();
        MngDeletedMyId::factory()->create();

        // UseCaseの作成
        /** @var SignUpUseCase $useCase */
        $useCase = app()->make(SignUpUseCase::class);

        // Exercise
        $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

        // Verify
        $this->assertArrayHasKey('id_token', $result);
        $this->checkBankLogByEventId(
            $result['currency_summary']->getUserId(),
            BankKPIF001EventId::USER_REGISTERED->value
        );
    }

    public function test_exec_正常動作_ユーザー削除データが有る状態で重複エラー()
    {
        // Setup
        $this->fixTime('2025-04-01 00:00:00');
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        $testMyId = 'A1000000001';

        // Mockeryを使用してprotectedメソッドをモック
        $mockRepository = \Mockery::mock(UsrUserProfileRepository::class)->makePartial();
        $mockRepository->shouldAllowMockingProtectedMethods();
        $mockRepository->shouldReceive('makeMyIdNumString')
            ->andReturn('1000000001');

        // DIコンテナにモックを登録
        $this->app->instance(UsrUserProfileRepository::class, $mockRepository);

        MstUserLevel::factory()->create(['level' => 1, 'stamina' => 10])->toEntity();
        MngDeletedMyId::factory()->create([
            'my_id' => $testMyId,
        ]);

        // UseCaseの作成
        /** @var SignUpUseCase $useCase */
        $useCase = app()->make(SignUpUseCase::class);

        // Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_CREATE_FAILED);

        // Exercise
        $useCase->exec($platform, $billingPlatform, $clientUuid);
    }

    /**
     * @test
     */
    public function アカウント作成に失敗するとエラーが発生()
    {
        // Setup
        $platform = System::PLATFORM_ANDROID;
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $clientUuid = fake()->uuid();

        $now = CarbonImmutable::now();

        // UseCaseの作成
        $clock = \Mockery::mock(Clock::class);
        $mstUserLevelRepository = \Mockery::mock(MstUserLevelRepository::class);
        $userService = \Mockery::mock(UsrUserRepository::class);
        $usrDeviceRepository = \Mockery::mock(UsrDeviceRepository::class);
        $usrUserLoginRepository = \Mockery::mock(UsrUserLoginRepository::class);
        $idTokenService = \Mockery::mock(IdTokenService::class);
        $currencyDelegator = \Mockery::mock(CurrencyDelegator::class);
        $appCurrencyDelegator = \Mockery::mock(AppCurrencyDelegator::class);
        $usrUserParameterService = \Mockery::mock(UsrUserParameterRepository::class);
        $idleIncentiveDelegator = \Mockery::mock(IdleIncentiveDelegator::class);
        $userDelegator = \Mockery::mock(UserDelegator::class);
        $outpostDelegator = \Mockery::mock(OutpostDelegator::class);
        $emblemDelegator = \Mockery::mock(EmblemDelegator::class);
        $missionDelegator = \Mockery::mock(MissionDelegator::class);
        $logBankService = \Mockery::mock(LogBankService::class);

        $useCase = new SignUpUseCase(
            $this->usrModelManager,
            $clock,
            $mstUserLevelRepository,
            $userService,
            $usrUserLoginRepository,
            $usrDeviceRepository,
            $idTokenService,
            $usrUserParameterService,
            $logBankService,
            $currencyDelegator,
            $appCurrencyDelegator,
            $idleIncentiveDelegator,
            $outpostDelegator,
            $emblemDelegator,
            $missionDelegator,
            $userDelegator,
        );

        // 期待する処理の流れを宣言
        $clock->shouldReceive('now')
            ->andReturn($now);
        $userService->shouldReceive('make')
            ->andThrow(new \Exception());

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_CREATE_FAILED);

        // Exercise
        $useCase->exec($platform, $billingPlatform, $clientUuid);
    }
}
