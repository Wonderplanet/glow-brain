<?php

namespace Tests\Feature\Domain\IdleIncentive\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\IdleIncentive\Services\IdleIncentiveService;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class IdleIncentiveServiceTest extends TestCase
{
    private IdleIncentiveService $idleIncentiveService;

    public function setUp(): void
    {
        parent::setUp();

        $this->idleIncentiveService = $this->app->make(IdleIncentiveService::class);
    }

    /**
     * @dataProvider params_calcElapsedTimeMinutes_経過時間算出ができることを確認
     */
    public function test_calcElapsedTimeMinutes_経過時間算出ができることを確認(
        int $max_idle_hours,
        CarbonImmutable $idleStartedAt,
        CarbonImmutable $now,
        int $expected
    ) {
        // Setup
        $mstIdleIncentive = MstIdleIncentive::factory()->create([
            'max_idle_hours' => $max_idle_hours,
        ])->toEntity();

        // Exercise
        $result = $this->idleIncentiveService->calcElapsedTimeMinutes(
            $mstIdleIncentive,
            $idleStartedAt,
            $now
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function params_calcElapsedTimeMinutes_経過時間算出ができることを確認()
    {
        $now = CarbonImmutable::now();

        return [
            '最大放置時間未満' => [
                'max_idle_hours' => 100,
                'idleStartedAt' => $now->subHours(50),
                'now' => $now,
                'expected' => 50 * 60,
            ],
            '最大放置時間超過したら最大時間が返る' => [
                'max_idle_hours' => 100,
                'idleStartedAt' => $now->subHours(150),
                'now' => $now,
                'expected' => 100 * 60,
            ],
            '未来の時間が渡された場合は経過時間なしとみなす' => [
                'max_idle_hours' => 100,
                'idleStartedAt' => $now->addHours(50),
                'now' => $now,
                'expected' => 0,
            ],
        ];
    }

    /**
     * @dataProvider params_validateReceivable_報酬受取可能か判定できることを確認
     */
    public function test_validateReceivable_報酬受取可能か判定できることを確認(
        int $initial_reward_receive_minutes,
        int $elapsedMinutes,
        bool $expected
    ) {
        // Setup
        $mstIdleIncentive = MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => $initial_reward_receive_minutes,
        ])->toEntity();

        if ($expected === false) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::IDLE_INCENTIVE_CANNOT_RECEIVE_REWARD);
        }

        // Exercise
        $this->idleIncentiveService->validateReceivable(
            $mstIdleIncentive,
            $elapsedMinutes,
        );

        // Verify
        $this->assertTrue($expected);
    }

    public static function params_validateReceivable_報酬受取可能か判定できることを確認()
    {
        return [
            '受取不可' => [
                'initial_reward_receive_minutes' => 30,
                'elapsedMinutes' => 29,
                'expected' => false,
            ],
            '受取可能' => [
                'initial_reward_receive_minutes' => 30,
                'elapsedMinutes' => 30,
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider params_validateDiamondQuickReceivable_一次通貨使用でクイック探索が可能か判定する
     */
    public function test_validateDiamondQuickReceivable_一次通貨使用でクイック探索が可能か判定する(
        int $maxCount,
        int $usrCount,
        bool $expected
    ) {
        // Setup
        $mstIdleIncentive = MstIdleIncentive::factory()->create([
            'max_daily_diamond_quick_receive_amount' => $maxCount,
        ])->toEntity();

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'diamond_quick_receive_count' => $usrCount,
        ]);

        if ($expected === false) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::IDLE_INCENTIVE_QUICK_RECEIVE_COUNT_LIMIT);
        }

        // Exercise
        $this->idleIncentiveService->validateDiamondQuickReceivable(
            $mstIdleIncentive,
            $usrIdleIncentive,
            0,
        );

        // Verify
        $this->assertTrue($expected);
    }

    public static function params_validateDiamondQuickReceivable_一次通貨使用でクイック探索が可能か判定する()
    {
        return [
            'クイック探索可能' => [
                'maxCount' => 3,
                'usrCount' => 2,
                'expected' => true,
            ],
            'クイック探索不可 制限回数ちょうど' => [
                'maxCount' => 3,
                'usrCount' => 3,
                'expected' => false,
            ],
            'クイック探索不可 制限回数超過' => [
                'maxCount' => 3,
                'usrCount' => 4,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider params_validateAdQuickReceivable_広告視聴でクイック探索が可能か判定する
     */
    public function test_validateAdQuickReceivable_広告視聴でクイック探索が可能か判定する(
        int $maxCount,
        int $usrCount,
        bool $expected
    ) {
        // Setup
        $mstIdleIncentive = MstIdleIncentive::factory()->create([
            'max_daily_ad_quick_receive_amount' => $maxCount,
        ])->toEntity();

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'ad_quick_receive_count' => $usrCount,
        ]);

        if ($expected === false) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::IDLE_INCENTIVE_QUICK_RECEIVE_COUNT_LIMIT);
        }

        // Exercise
        $this->idleIncentiveService->validateAdQuickReceivable(
            $mstIdleIncentive,
            $usrIdleIncentive,
            0,
        );

        // Verify
        $this->assertTrue($expected);
    }

    public static function params_validateAdQuickReceivable_広告視聴でクイック探索が可能か判定する()
    {
        return [
            'クイック探索可能' => [
                'maxCount' => 3,
                'usrCount' => 2,
                'expected' => true,
            ],
            'クイック探索不可 制限回数ちょうど' => [
                'maxCount' => 3,
                'usrCount' => 3,
                'expected' => false,
            ],
            'クイック探索不可 制限回数超過' => [
                'maxCount' => 3,
                'usrCount' => 4,
                'expected' => false,
            ],
        ];
    }
}
