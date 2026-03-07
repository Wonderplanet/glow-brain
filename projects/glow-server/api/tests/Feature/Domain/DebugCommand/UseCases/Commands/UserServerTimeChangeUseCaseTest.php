<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Debug\Entities\DebugUserAllTimeSetting;
use App\Domain\Debug\Entities\DebugUserTimeSetting;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;
use App\Domain\DebugCommand\UseCases\DebugCommandExecUseCase;
use App\Domain\User\Constants\UserConstant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class UserServerTimeChangeUseCaseTest extends TestCase
{
    private DebugUserTimeSettingRepository $debugUserTimeSettingRepository;
    private DebugUserAllTimeSettingRepository $debugUserAllTimeSettingRepository;

    public function setUp(): void
    {
        parent::setUp();
        // 前テストのsetTestNow汚染をリセット
        CarbonImmutable::setTestNow(null);
        Cache::flush();
        $this->debugUserTimeSettingRepository = app(DebugUserTimeSettingRepository::class);
        $this->debugUserAllTimeSettingRepository = app(DebugUserAllTimeSettingRepository::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow(null);
        Cache::flush();
        parent::tearDown();
    }

    public function test_UserServerTimeChange_全体サーバー時間が未設定の状態でユーザー時間を変更できること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $targetYear = 2030;
        $targetMonth = 6;
        $targetDay = 15;
        $targetHour = 12;
        $targetMinute = 30;

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'UserServerTimeChange', $platform, [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
            'hour' => $targetHour,
            'minute' => $targetMinute,
        ]);

        // Verify
        $this->assertTrue($this->debugUserTimeSettingRepository->exists($usrUser->getId()));

        $setting = $this->debugUserTimeSettingRepository->get($usrUser->getId());
        $this->assertNotNull($setting);

        // getUserTimeで取得した時刻が指定した時刻に近いことを確認（数秒の誤差を許容）
        $userTime = $setting->getUserTime(CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo'));
        $expectedDateTime = CarbonImmutable::create($targetYear, $targetMonth, $targetDay, $targetHour, $targetMinute, 0, 'Asia/Tokyo');
        $diffInSeconds = abs($userTime->diffInSeconds($expectedDateTime));
        $this->assertLessThan(5, $diffInSeconds, "ユーザー時間が指定時刻と5秒以上ずれています: expected={$expectedDateTime}, actual={$userTime}");
    }

    public function test_UserServerTimeChange_全体サーバー時間が設定済みの状態でもユーザー時間が正しく設定されること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        // 全体サーバー時間を2035年に変更（CarbonImmutable::now()が2035年を返すようになる）
        $allTimeTarget = CarbonImmutable::create(2035, 1, 1, 0, 0, 0, 'Asia/Tokyo');
        $realNow = CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo');
        $allTimeSetting = new DebugUserAllTimeSetting($allTimeTarget, $realNow);
        $this->debugUserAllTimeSettingRepository->put($allTimeSetting);

        // この時点でCarbonImmutable::now()は2035年付近を返す（JSTで確認）
        $this->assertEquals(2035, CarbonImmutable::now()->setTimezone('Asia/Tokyo')->year);

        // ユーザー時間を2030年6月15日に設定
        $targetYear = 2030;
        $targetMonth = 6;
        $targetDay = 15;
        $targetHour = 12;
        $targetMinute = 0;

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'UserServerTimeChange', $platform, [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
            'hour' => $targetHour,
            'minute' => $targetMinute,
        ]);

        // Verify
        $setting = $this->debugUserTimeSettingRepository->get($usrUser->getId());
        $this->assertNotNull($setting);

        // getUserTimeで取得した時刻が指定した時刻に近いことを確認（数秒の誤差を許容）
        $userTime = $setting->getUserTime(CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo'));
        $expectedDateTime = CarbonImmutable::create($targetYear, $targetMonth, $targetDay, $targetHour, $targetMinute, 0, 'Asia/Tokyo');
        $diffInSeconds = abs($userTime->diffInSeconds($expectedDateTime));
        $this->assertLessThan(5, $diffInSeconds, "全体サーバー時間が設定済みの状態でユーザー時間が正しく計算されていません: expected={$expectedDateTime}, actual={$userTime}");
    }

    public function test_UserServerTimeChange_全体サーバー時間が過去に設定されていてもユーザー時間が正しく設定されること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        // 全体サーバー時間を2020年（過去）に変更
        $allTimeTarget = CarbonImmutable::create(2020, 3, 1, 0, 0, 0, 'Asia/Tokyo');
        $realNow = CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo');
        $allTimeSetting = new DebugUserAllTimeSetting($allTimeTarget, $realNow);
        $this->debugUserAllTimeSettingRepository->put($allTimeSetting);

        // CarbonImmutable::now()は2020年付近を返す（JSTで確認）
        $this->assertEquals(2020, CarbonImmutable::now()->setTimezone('Asia/Tokyo')->year);

        // ユーザー時間を2028年に設定
        $targetYear = 2028;
        $targetMonth = 12;
        $targetDay = 25;
        $targetHour = 18;
        $targetMinute = 30;

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'UserServerTimeChange', $platform, [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
            'hour' => $targetHour,
            'minute' => $targetMinute,
        ]);

        // Verify
        $setting = $this->debugUserTimeSettingRepository->get($usrUser->getId());
        $this->assertNotNull($setting);

        // getUserTimeで取得した時刻が指定した時刻に近いことを確認（数秒の誤差を許容）
        $userTime = $setting->getUserTime(CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo'));
        $expectedDateTime = CarbonImmutable::create($targetYear, $targetMonth, $targetDay, $targetHour, $targetMinute, 0, 'Asia/Tokyo');
        $diffInSeconds = abs($userTime->diffInSeconds($expectedDateTime));
        $this->assertLessThan(5, $diffInSeconds, "全体サーバー時間が過去に設定されている状態でユーザー時間が正しく計算されていません: expected={$expectedDateTime}, actual={$userTime}");
    }

    public function test_UserServerTimeChange_ユーザー個別時間が既に設定済みでも上書きできること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        // 先にユーザー時間を設定
        $firstTarget = CarbonImmutable::create(2025, 1, 1, 0, 0, 0, 'Asia/Tokyo');
        $firstSetting = new DebugUserTimeSetting($firstTarget, CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo'));
        $this->debugUserTimeSettingRepository->put($usrUser->getId(), $firstSetting);

        // 別の時間に上書き
        $targetYear = 2032;
        $targetMonth = 8;
        $targetDay = 20;
        $targetHour = 15;
        $targetMinute = 45;

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'UserServerTimeChange', $platform, [
            'year' => $targetYear,
            'month' => $targetMonth,
            'day' => $targetDay,
            'hour' => $targetHour,
            'minute' => $targetMinute,
        ]);

        // Verify
        $setting = $this->debugUserTimeSettingRepository->get($usrUser->getId());
        $this->assertNotNull($setting);

        // getUserTimeで取得した時刻が指定した時刻に近いことを確認（数秒の誤差を許容）
        $userTime = $setting->getUserTime(CarbonImmutable::createFromTimestamp(time(), 'Asia/Tokyo'));
        $expectedDateTime = CarbonImmutable::create($targetYear, $targetMonth, $targetDay, $targetHour, $targetMinute, 0, 'Asia/Tokyo');
        $diffInSeconds = abs($userTime->diffInSeconds($expectedDateTime));
        $this->assertLessThan(5, $diffInSeconds, "ユーザー時間の上書きが正しく行われていません: expected={$expectedDateTime}, actual={$userTime}");
    }
}
