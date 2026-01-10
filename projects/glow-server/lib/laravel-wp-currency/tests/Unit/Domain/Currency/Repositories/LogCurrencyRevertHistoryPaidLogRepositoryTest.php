<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryPaidLogRepository;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogCurrencyRevertHistoryPaidLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogCurrencyRevertHistoryPaidLogRepository $logCurrencyRevertPaidLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logCurrencyRevertPaidLogRepository = $this->app->make(LogCurrencyRevertHistoryPaidLogRepository::class);
    }

    #[Test]
    public function insertRevertHistoryPaidLog_ログが追加されていること()
    {
        // Exercise
        $this->logCurrencyRevertPaidLogRepository->insertRevertHistoryPaidLog(
            '1',
            'revert id 1',
            'log id 1',
            'revert log id 1',
        );

        // Verify
        // 登録情報の確認
        $logCurrencyRevertPaid = LogCurrencyRevertHistoryPaidLog::query()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $logCurrencyRevertPaid->usr_user_id);
        $this->assertEquals('revert id 1', $logCurrencyRevertPaid->log_currency_revert_history_id);
        $this->assertEquals('log id 1', $logCurrencyRevertPaid->log_currency_paid_id);
        $this->assertEquals('revert log id 1', $logCurrencyRevertPaid->revert_log_currency_paid_id);
    }

    #[Test]
    #[DataProvider('getAllRevertLogCurrencyPaidIdsData')]
    public function getRevertLogCurrencyPaidIdsByStartAt_取得チェック(
        Carbon $startAtJst,
        int $expectedCount,
        array $expectedList
    ): void {
        // Setup
        //  指定開始日時前
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '1',
                    'usr_user_id' => '1',
                    'log_currency_revert_history_id' => 'revert id 1',
                    'log_currency_paid_id' => 'log id 1',
                    'revert_log_currency_paid_id' => 'revert log id 1',
                    'created_at' => '2024-02-18 23:59:59+09:00',
                    'updated_at' => '2024-02-18 23:59:59+09:00',
                ],
            );
        //  指定開始日時以降
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '2',
                    'usr_user_id' => '2',
                    'log_currency_revert_history_id' => 'revert id 2',
                    'log_currency_paid_id' => 'log id 2',
                    'revert_log_currency_paid_id' => 'revert log id 2',
                    'created_at' => '2024-02-19 00:00:00+09:00',
                    'updated_at' => '2024-02-19 00:00:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '3',
                    'usr_user_id' => '3',
                    'log_currency_revert_history_id' => 'revert id 3',
                    'log_currency_paid_id' => 'log id 3',
                    'revert_log_currency_paid_id' => 'revert log id 3',
                    'created_at' => '2024-02-19 00:00:01+09:00',
                    'updated_at' => '2024-02-19 00:00:01+09:00',
                ],
            );

        // Exercise
        $result = $this->logCurrencyRevertPaidLogRepository
            ->getRevertLogCurrencyPaidIdsByStartAt($startAtJst);

        // Verify
        //  要素数チェック
        $this->assertCount($expectedCount, $result);
        //  配列の中身のチェック(中身の順序は無視)
        $this->assertEqualsCanonicalizing($expectedList, $result);
    }

    /**
     * @return array[]
     */
    public static function getAllRevertLogCurrencyPaidIdsData(): array
    {
        return [
            '返却データが2件' => [
                Carbon::create(2024, 2, 19, 0, 0, 0, 'Asia/Tokyo'),
                2,
                [
                    [
                        'log_currency_paid_id' => 'log id 2',
                         'revert_log_currency_paid_id' => 'revert log id 2',
                    ],
                    [
                        'log_currency_paid_id' => 'log id 3',
                        'revert_log_currency_paid_id' => 'revert log id 3',
                    ],
                ],
            ],
            '返却データが3件' => [
                Carbon::create(2024, 2, 18, 0, 0, 0, 'Asia/Tokyo'),
                3,
                [
                    [
                        'log_currency_paid_id' => 'log id 1',
                        'revert_log_currency_paid_id' => 'revert log id 1',
                    ],
                    [
                        'log_currency_paid_id' => 'log id 2',
                        'revert_log_currency_paid_id' => 'revert log id 2',
                    ],
                    [
                        'log_currency_paid_id' => 'log id 3',
                        'revert_log_currency_paid_id' => 'revert log id 3',
                    ],
                ],
            ],
            '返却データなし' => [
                Carbon::create(2024, 2, 20, 0, 0, 0, 'Asia/Tokyo'),
                0,
                []
            ]
        ];
    }
}
