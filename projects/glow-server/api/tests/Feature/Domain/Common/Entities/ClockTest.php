<?php

namespace Tests\Feature\Domain\Common\Entities;

use App\Domain\Common\Entities\Clock;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClockTest extends TestCase
{
    private Clock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = app(Clock::class);
    }

    #[DataProvider('params_isFirstToday_日跨ぎ判定を確認する')]
    public function test_isFirstToday_日跨ぎ判定を確認する(bool $expected, string $targetAt, string $now)
    {
        // Setup
        $now = $this->fixTime($now);

        // Exercise
        $result = $this->clock->isFirstToday($targetAt);

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function params_isFirstToday_日跨ぎ判定を確認する()
    {
        // 日跨ぎするかどうか, 判定したい日時, 現在日時
        return [
            '日跨ぎしている' => [true, '2025-04-10 18:59:59', '2025-04-11 19:00:00'],
            '日跨ぎしている 跨ぎ時間ちょうど' => [true, '2025-04-10 19:00:00', '2025-04-11 19:00:00'],
            '日跨ぎしていない' => [false, '2025-04-11 04:00:00', '2025-04-10 04:00:00'],
            '日跨ぎしていない 跨ぎ時間直前' => [false, '2025-04-09 19:00:00', '2025-04-10 18:59:59'],
        ];
    }

    public static function params_test_isFirstWeek_週跨ぎ判定できる()
    {
        // 2024-04-08は月曜日で週初め
        return [
            '週跨ぎしている' => [
                'nowString' => '2024-04-10 18:00:00',
                'targetAtString' => '2024-04-03 19:00:00',
                'expected' => true,
            ],
            '週跨ぎしている 跨ぎ時間ちょうど' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-03 19:00:00',
                'expected' => true,
            ],
            '週跨ぎしていない' => [
                'nowString' => '2024-04-10 19:00:00',
                'targetAtString' => '2024-04-09 19:00:00',
                'expected' => false,
            ],
            '週跨ぎしていない 跨ぎ時間直前' => [
                'nowString' => '2024-04-07 18:59:59',
                'targetAtString' => '2024-04-03 19:00:00',
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('params_test_isFirstWeek_週跨ぎ判定できる')]
    public function test_isFirstWeek_週跨ぎ判定できる(
        string $nowString,
        string $targetAtString,
        bool $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->isFirstWeek($targetAtString);

        // Verify
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('params_test_isFirstMonth_月跨ぎ判定できる')]
    public function test_isFirstMonth_月跨ぎ判定できる(
        string $nowString,
        string $targetAtString,
        bool $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->isFirstMonth($targetAtString);

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function params_test_isFirstMonth_月跨ぎ判定できる()
    {
        return [
            '月跨ぎしている' => [
                'nowString' => '2024-11-10 19:00:00',
                'targetAtString' => '2024-10-10 19:00:00',
                'expected' => true,
            ],
            '月跨ぎしている 跨ぎ時間ちょうど' => [
                'nowString' => '2024-10-31 19:00:00',
                'targetAtString' => '2024-10-31 18:59:59',
                'expected' => true,
            ],
            '月跨ぎしていない' => [
                'nowString' => '2024-04-10 19:00:00',
                'targetAtString' => '2024-04-09 19:00:00',
                'expected' => false,
            ],
            '月跨ぎしていない 跨ぎ時間直前' => [
                'nowString' => '2024-10-31 18:59:59',
                'targetAtString' => '2024-10-05 19:00:00',
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('params_test_getMonthStartDatetime_月の始まりの日時を取得できる')]
    public function test_getMonthStartDatetime_月の始まりの日時を取得できる(
        string $nowString,
        string $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->getMonthStartDatetime();

        // Verify
        $this->assertEquals($expected, $result->toDateTimeString());
    }

    public static function params_test_getMonthStartDatetime_月の始まりの日時を取得できる()
    {
        return [
            '月跨ぎ時間ちょうど' => [
                'nowString' => '2024-04-30 19:00:00',
                'expected' => '2024-04-30 19:00:00',
            ],
            '月跨ぎ時間を過ぎている' => [
                'nowString' => '2024-05-05 19:00:00',
                'expected' => '2024-04-30 19:00:00',
            ],
            '月跨ぎ時間の直前' => [
                'nowString' => '2024-04-30 18:59:59',
                'expected' => '2024-03-31 19:00:00',
            ],
            '月跨ぎ時間の直後' => [
                'nowString' => '2024-03-31 19:00:01',
                'expected' => '2024-03-31 19:00:00',
            ],
        ];
    }

    public static function params_test_calcDayStartDatetime_指定した日の始まりの時刻を取得できる()
    {
        return [
            '日跨ぎ時間ちょうど' => [
                'targetAtString' => '2024-01-01 19:00:00',
                'expected' => '2024-01-01 19:00:00',
            ],
            '日跨ぎ時間を過ぎている' => [
                'targetAtString' => '2024-01-02 20:00:00',
                'expected' => '2024-01-02 19:00:00',
            ],
            '日跨ぎ時間の直前' => [
                'targetAtString' => '2024-01-03 18:59:59',
                'expected' => '2024-01-02 19:00:00',
            ],
            '日跨ぎ時間の直後' => [
                'targetAtString' => '2024-01-03 19:00:01',
                'expected' => '2024-01-03 19:00:00',
            ],
        ];
    }

    #[DataProvider('params_test_calcDayStartDatetime_指定した日の始まりの時刻を取得できる')]
    public function test_calcDayStartDatetime_指定した日の始まりの時刻を取得できる(
        string $targetAtString,
        string $expected,
    ) {
        // Setup
        $targetAt = CarbonImmutable::parse($targetAtString);

        // Exercise
        $result = $this->clock->calcDayStartDatetime($targetAt);

        // Verify
        $this->assertEquals($expected, $result->toDateTimeString());
    }

    public static function params_test_getWeekStartDatetime_週の始まりの時刻を取得できる()
    {
        // 2024-04-08は月曜日で週初め
        return [
            '週跨ぎ時間ちょうど' => [
                'nowString' => '2024-04-07 19:00:00',
                'expected' => '2024-04-07 19:00:00',
            ],
            '週跨ぎ時間を過ぎている' => [
                'nowString' => '2024-04-09 19:00:00',
                'expected' => '2024-04-07 19:00:00',
            ],
            '週跨ぎ時間の直前' => [
                'nowString' => '2024-04-07 18:59:59',
                'expected' => '2024-03-31 19:00:00',
            ],
            '週跨ぎ時間の直後' => [
                'nowString' => '2024-04-07 19:00:01',
                'expected' => '2024-04-07 19:00:00',
            ],
        ];
    }

    #[DataProvider('params_test_getWeekStartDatetime_週の始まりの時刻を取得できる')]
    public function test_getWeekStartDatetime_週の始まりの時刻を取得できる(
        string $nowString,
        string $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->getWeekStartDatetime();

        // Verify
        $this->assertEquals($expected, $result->toDateTimeString());
    }

    public static function params_test_calcWeekStartDatetime_指定した週の始まりの時刻を取得できる()
    {
        // 2024-04-08は月曜日で週初め
        return [
            '週跨ぎ時間ちょうど' => [
                'targetAtString' => '2024-04-07 19:00:00',
                'expected' => '2024-04-07 19:00:00',
            ],
            '週跨ぎ時間を過ぎている' => [
                'targetAtString' => '2024-04-09 19:00:00',
                'expected' => '2024-04-07 19:00:00',
            ],
            '週跨ぎ時間の直前' => [
                'targetAtString' => '2024-04-07 18:59:59',
                'expected' => '2024-03-31 19:00:00',
            ],
            '週跨ぎ時間の直後' => [
                'targetAtString' => '2024-04-07 19:00:01',
                'expected' => '2024-04-07 19:00:00',
            ],
        ];
    }

    #[DataProvider('params_test_calcWeekStartDatetime_指定した週の始まりの時刻を取得できる')]
    public function test_calcWeekStartDatetime_指定した週の始まりの時刻を取得できる(
        string $targetAtString,
        string $expected,
    ) {
        // Setup
        $targetAt = CarbonImmutable::parse($targetAtString);

        // Exercise
        $result = $this->clock->calcWeekStartDatetime($targetAt);

        // Verify
        $this->assertEquals($expected, $result->toDateTimeString());
    }

    public static function params_test_diffDays_現在日時と指定した日時の日数差分を取得できる()
    {
        return [
            '同日 同時刻 0' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-07 19:00:00',
                'expected' => 0,
            ],
            '同日 異なる時刻 0' => [
                'nowString' => '2024-04-08 19:15:15',
                'targetAtString' => '2024-04-08 19:00:00',
                'expected' => 0,
            ],
            '同日 日跨ぎちょうど と 日跨ぎ直後 0' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-07 19:00:01',
                'expected' => 0,
            ],
            '別日 日跨ぎちょうど と 日跨ぎ直前 1' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-07 18:59:59',
                'expected' => 1,
            ],
            '別日 日跨ぎちょうど と 翌日の日跨ぎちょうど 1' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-08 19:00:00',
                'expected' => 1,
            ],
            '別日 2' => [
                'nowString' => '2024-04-07 19:00:00',
                'targetAtString' => '2024-04-09 19:00:00',
                'expected' => 2,
            ],
            '別日 月跨ぎ 1' => [
                'nowString' => '2024-04-30 19:00:00',
                'targetAtString' => '2024-04-30 18:59:59', // 2024年4月は30日まで
                'expected' => 1,
            ],
            '別日 50' => [
                'nowString' => '2024-04-30 19:00:00',
                'targetAtString' => '2024-03-11 19:00:00', // 2024年4月は30日まで
                'expected' => 50,
            ],
            '閏年が間にある 1' => [
                'nowString' => '2024-02-28 19:00:00',
                'targetAtString' => '2024-02-27 19:00:00',
                'expected' => 1,
            ],
            '閏年が間にある 40' => [
                'nowString' => '2024-02-28 19:00:00',
                'targetAtString' => '2024-01-19 19:00:00',
                'expected' => 40,
            ],
        ];
    }

    #[DataProvider('params_test_diffDays_現在日時と指定した日時の日数差分を取得できる')]
    public function test_diffDays_現在日時と指定した日時の日数差分を取得できる(
        string $nowString,
        string $targetAtString,
        int $expected,
    ) {
        // Setup
        $now = $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->diffDays($targetAtString);

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function params_test_isContinuousLogin_連続日ログインの判定ができる()
    {
        return [
            'true 日跨ぎちょうど' => [
                'beforeAtString' => '2024-04-06 19:00:00',
                'nowString' => '2024-04-07 19:00:00',
                'expected' => true,
            ],
            'true 日跨ぎ直前 と 日跨ぎちょうど' => [
                'beforeAtString' => '2024-04-06 18:59:59',
                'nowString' => '2024-04-06 19:00:00',
                'expected' => true,
            ],
            'false 日跨ぎちょうど と 日跨ぎ直後' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'nowString' => '2024-04-07 19:00:01',
                'expected' => false,
            ],
            'false 同日' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'nowString' => '2024-04-08 05:00:00',
                'expected' => false,
            ],
            'false 同日 同時刻' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'nowString' => '2024-04-07 19:00:00',
                'expected' => false,
            ],
            'false 1日分の間がある' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'nowString' => '2024-04-09 19:00:00',
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('params_test_isContinuousLogin_連続日ログインの判定ができる')]
    public function test_isContinuousLogin_連続日ログインの判定ができる(
        string $beforeAtString,
        string $nowString,
        bool $expected,
    ) {
        // Setup
        $now = $this->fixTime($nowString);

        $beforeAt = CarbonImmutable::parse($beforeAtString);

        // Exercise
        $result = $this->clock->isContinuousLogin($beforeAt);

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function params_test_calcDayStartAtFromElapsedDays_指定日数前の日の開始日時を取得できる()
    {
        return [
            '同日' => [
                'nowString' => '2024-04-07 19:00:00',
                'elapsedDays' => 0,
                'expected' => '2024-04-07 19:00:00',
            ],
            '2日前 現在時刻が日跨ぎちょうど' => [
                'nowString' => '2024-04-07 19:00:00',
                'elapsedDays' => 2,
                'expected' => '2024-04-05 19:00:00',
            ],
            '2日前 現在時刻が日跨ぎ直後' => [
                'nowString' => '2024-04-07 19:00:01',
                'elapsedDays' => 2,
                'expected' => '2024-04-05 19:00:00',
            ],
            '2日前 現在時刻が途中' => [
                'nowString' => '2024-04-08 05:10:10',
                'elapsedDays' => 2,
                'expected' => '2024-04-05 19:00:00',
            ],
        ];
    }

    #[DataProvider('params_test_calcDayStartAtFromElapsedDays_指定日数前の日の開始日時を取得できる')]
    public function test_calcDayStartAtFromElapsedDays_指定日数前の日の開始日時を取得できる(
        string $nowString,
        int $elapsedDays,
        string $expected,
    ) {
        // Setup
        $this->fixTime($nowString);

        // Exercise
        $result = $this->clock->calcDayStartAtFromElapsedDays($elapsedDays);

        // Verify
        $this->assertEquals($expected, $result->toDateTimeString());
    }

    public static function params_test_isAfterDay_翌日以降の別日であると判定できる()
    {
        return [
            'true 日跨ぎちょうど' => [
                'beforeAtString' => '2024-04-06 19:00:00',
                'afterAtString' => '2024-04-07 19:00:00',
                'expected' => true,
            ],
            'true 日跨ぎ直前 と 日跨ぎちょうど' => [
                'beforeAtString' => '2024-04-06 18:59:59',
                'afterAtString' => '2024-04-07 19:00:00',
                'expected' => true,
            ],
            'true 1日分の間がある' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'afterAtString' => '2024-04-09 19:00:00',
                'expected' => true,
            ],
            'true 3日分の間がある' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'afterAtString' => '2024-04-11 19:00:00',
                'expected' => true,
            ],
            'false 日跨ぎちょうど と 日跨ぎ直後' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'afterAtString' => '2024-04-07 19:00:01',
                'expected' => false,
            ],
            'false 同日' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'afterAtString' => '2024-04-08 05:00:00',
                'expected' => false,
            ],
            'false 同日 同時刻' => [
                'beforeAtString' => '2024-04-07 19:00:00',
                'afterAtString' => '2024-04-07 19:00:00',
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('params_test_isAfterDay_翌日以降の別日であると判定できる')]
    public function test_isAfterDay_翌日以降の別日であると判定できる(
        string $beforeAtString,
        string $afterAtString,
        bool $expected,
    ) {
        // Setup
        $this->fixTime($afterAtString);

        // Exercise
        $result = $this->clock->isAfterDay($beforeAtString, $afterAtString);

        // Verify
        $this->assertEquals($expected, $result);
    }
}
