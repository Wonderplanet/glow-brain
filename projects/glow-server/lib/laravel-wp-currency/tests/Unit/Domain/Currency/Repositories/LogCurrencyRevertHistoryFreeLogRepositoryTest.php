<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryFreeLog;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryFreeLogRepository;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogCurrencyRevertHistoryFreeLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogCurrencyRevertHistoryFreeLogRepository $logCurrencyRevertFreeLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logCurrencyRevertFreeLogRepository = $this->app->make(LogCurrencyRevertHistoryFreeLogRepository::class);
    }

    #[Test]
    public function insertRevertHistoryFreeLog_ログが追加されていること()
    {
        // Exercise
        $this->logCurrencyRevertFreeLogRepository->insertRevertHistoryFreeLog(
            '1',
            'revert id 1',
            'log id 1',
            'revert log id 1',
        );

        // Verify
        // 登録情報の確認
        $logCurrencyRevertFree = LogCurrencyRevertHistoryFreeLog::query()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $logCurrencyRevertFree->usr_user_id);
        $this->assertEquals('revert id 1', $logCurrencyRevertFree->log_currency_revert_history_id);
        $this->assertEquals('log id 1', $logCurrencyRevertFree->log_currency_free_id);
        $this->assertEquals('revert log id 1', $logCurrencyRevertFree->revert_log_currency_free_id);
    }
}
