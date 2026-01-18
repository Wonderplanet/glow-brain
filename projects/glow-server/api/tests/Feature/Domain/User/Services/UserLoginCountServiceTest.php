<?php

namespace Tests\Feature\Domain\User\Services;

use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\User\Models\LogLogin;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Services\UserLoginCountService;
use Tests\TestCase;

class UserLoginCountServiceTest extends TestCase
{
    private UserLoginCountService $userLoginCountService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userLoginCountService = app(UserLoginCountService::class);
    }

    public function test_updateLoginCount_連続で実行して各ログインカウントが算出できている()
    {
        // Setup
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();

        // Exercise 新規アカウント作成日からX日目ごとに実行する

        // 1日目 ログイン1回目 レコード生成
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-01 15:00:00',
            expectedLoginCount: 1,
            expectedLoginDayCount: 1,
            expectedLoginContinueDayCount: 1,
            expectedComebackDayCount: 0,
            expectedIsDayFirstLogin: true,
        );
        // 2日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-02 15:00:00',
            expectedLoginCount: 2,
            expectedLoginDayCount: 2,
            expectedLoginContinueDayCount: 2,
            expectedComebackDayCount: 0,
            expectedIsDayFirstLogin: true,
        );
        // 3日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-03 15:00:00',
            expectedLoginCount: 3,
            expectedLoginDayCount: 3,
            expectedLoginContinueDayCount: 3,
            expectedComebackDayCount: 0,
            expectedIsDayFirstLogin: true,
        );
        // 4日目 未ログイン
        // 5日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-05 15:00:00',
            expectedLoginCount: 4,
            expectedLoginDayCount: 4,
            expectedLoginContinueDayCount: 1,
            expectedComebackDayCount: 2,
            expectedIsDayFirstLogin: true,
        );
        // 5日目 ログイン2回目 カウントアップしない
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-05 15:00:01',
            expectedLoginCount: 5,
            expectedLoginDayCount: 4,
            expectedLoginContinueDayCount: 1,
            expectedComebackDayCount: 2,
            expectedIsDayFirstLogin: false,
        );
        // 6日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-06 15:00:01',
            expectedLoginCount: 6,
            expectedLoginDayCount: 5,
            expectedLoginContinueDayCount: 2,
            expectedComebackDayCount: 0,
            expectedIsDayFirstLogin: true,
        );
        // 7日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-07 15:00:01',
            expectedLoginCount: 7,
            expectedLoginDayCount: 6,
            expectedLoginContinueDayCount: 3,
            expectedComebackDayCount: 0,
            expectedIsDayFirstLogin: true,
        );
        // 8日目 未ログイン
        // 9日目 未ログイン
        // 10日目 未ログイン
        // 11日目 未ログイン
        // 12日目 未ログイン
        // 13日目 ログイン1回目 カウントアップ
        $this->execLoginAndCheckCount(
            $usrUserId,
            firstLoginAtString: '2024-04-01 15:00:00',
            nowString: '2024-04-13 15:00:01',
            expectedLoginCount: 8,
            expectedLoginDayCount: 7,
            expectedLoginContinueDayCount: 1,
            expectedComebackDayCount: 6,
            expectedIsDayFirstLogin: true,
        );
    }

    private function execLoginAndCheckCount(
        string $usrUserId,
        string $firstLoginAtString,
        string $nowString,
        int $expectedLoginCount,
        int $expectedLoginDayCount,
        int $expectedLoginContinueDayCount,
        int $expectedComebackDayCount,
        bool $expectedIsDayFirstLogin,
    ) {
        // Setup
        $now = $this->fixTime($nowString);

        // Verify
        $this->userLoginCountService->updateLoginCount($usrUserId, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserLogin, 'usr_user_logins is null');
        $this->assertEquals($firstLoginAtString, $usrUserLogin->getFirstLoginAt(), 'first_login_at');
        $this->assertEquals($now->toDateTimeString(), $usrUserLogin->getLastLoginAt(), 'last_login_at');
        $this->assertEquals($expectedLoginCount, $usrUserLogin->getLoginCount(), 'login_count');
        $this->assertEquals($expectedLoginDayCount, $usrUserLogin->getLoginDayCount(), 'login_day_count');
        $this->assertEquals($expectedLoginContinueDayCount, $usrUserLogin->getLoginContinueDayCount(), 'login_continue_day_count');
        $this->assertEquals($expectedComebackDayCount, $usrUserLogin->getComebackDayCount(), 'comeback_day_count');

        // ログインログの確認
        $logLogin = LogLogin::query()->where('usr_user_id', $usrUserId)->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($logLogin, 'log_logins is null');
        $this->assertEquals($usrUserId, $logLogin->usr_user_id, 'usr_user_id');
        $this->assertEquals($expectedLoginCount, $logLogin->login_count, 'login_count');
        $this->assertEquals((int)$expectedIsDayFirstLogin, $logLogin->is_day_first_login, 'is_day_first_login');
        $this->assertEquals($expectedLoginDayCount, $logLogin->login_day_count, 'login_day_count');
        $this->assertEquals($expectedLoginContinueDayCount, $logLogin->login_continue_day_count, 'login_continue_day_count');
        $this->assertEquals($expectedComebackDayCount, $logLogin->comeback_day_count, 'comeback_day_count');
    }
}
