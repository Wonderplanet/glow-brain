<?php

declare(strict_types=1);

namespace Feature\Domain\User\Services;

use App\Domain\User\Services\UserMissionTriggerService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserMissionTriggerServiceTest extends TestCase
{
    private UserMissionTriggerService $userMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userMissionTriggerService = app(UserMissionTriggerService::class);
    }

    public static function params_test_calcWeeklyLoginContinueCount_週の連続ログイン日数を計算できる()
    {
        // 生涯累積の連続ログイン日数, 現在時刻, 期待値
        // UTC:2024-04-14 15:00:00 は JST:2024-04-15 00:00:00 (月) で週初日
        return [
            // 連続ログイン日数が、週単位のものと生涯累積のもので同じ値の場合
            '生涯累積値と同じ 1' => [1, '2024-04-14 19:00:00', 1],
            '生涯累積値と同じ 2' => [2, '2024-04-15 19:00:00', 2],
            '生涯累積値と同じ 3' => [3, '2024-04-16 19:00:00', 3],
            '生涯累積値と同じ 4' => [4, '2024-04-17 19:00:00', 4],
            '生涯累積値と同じ 5' => [5, '2024-04-18 19:00:00', 5],
            '生涯累積値と同じ 6' => [6, '2024-04-19 19:00:00', 6],
            '生涯累積値と同じ 7' => [7, '2024-04-20 19:00:00', 7],
            // 連続ログイン日数が、週単位のもの < 生涯累積のもの の場合
            '生涯累積値の方が大きい 1' => [8, '2024-04-21 19:00:00', 1],
            '生涯累積値の方が大きい 2' => [9, '2024-04-22 19:00:00', 2],
            '生涯累積値の方が大きい 3' => [10, '2024-04-23 19:00:00', 3],
            '生涯累積値の方が大きい 4' => [11, '2024-04-24 19:00:00', 4],
            '生涯累積値の方が大きい 5' => [12, '2024-04-25 19:00:00', 5],
            '生涯累積値の方が大きい 6' => [13, '2024-04-26 19:00:00', 6],
            '生涯累積値の方が大きい 7' => [14, '2024-04-27 19:00:00', 7],
            '生涯累積値の方が大きい 1(2週目)' => [15, '2024-04-28 19:00:00', 1],
            // 週の途中から連続ログイン開始した場合 (JST:2024-04-24 00:00:00 (水) から連続ログイン開始)
            '週途中からログイン開始 1' => [1, '2024-04-23 19:00:00', 1],
            '週途中からログイン開始 2' => [2, '2024-04-24 19:00:00', 2],
            '週途中からログイン開始 3' => [3, '2024-04-25 19:00:00', 3],
            '週途中からログイン開始 4' => [4, '2024-04-26 19:00:00', 4],
            '週途中からログイン開始 5' => [5, '2024-04-27 19:00:00', 5],
            '週途中からログイン開始 1(2週目)' => [6, '2024-04-28 19:00:00', 1],
        ];
    }

    #[DataProvider('params_test_calcWeeklyLoginContinueCount_週の連続ログイン日数を計算できる')]
    public function test_calcWeeklyLoginContinueCount_週の連続ログイン日数を計算できる(
        int $loginContinueDayCount,
        string $nowString,
        int $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->userMissionTriggerService->calcWeeklyLoginContinueCount($loginContinueDayCount);

        // Verify
        $this->assertEquals($expected, $result);
    }
}
