<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Entities\CurrencyRevertTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Enums\RequestIdType;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryRepository;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogCurrencyRevertHistoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogCurrencyRevertHistoryRepository $logCurrencyRevertRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logCurrencyRevertRepository = $this->app->make(LogCurrencyRevertHistoryRepository::class);
    }

    #[Test]
    public function insertRevertHistoryLog_ログが追加されていること()
    {
        // Exercise
        $this->logCurrencyRevertRepository->insertRevertHistoryLog(
            '1',
            'comment',
            'gacha',
            'gacha_id 1',
            'gacha name 1',
            'gacha detail',
            RequestIdType::Request->value,
            'request1',
            '2023-01-01 00:00:00',
            100,
            110,
            new CurrencyRevertTrigger()
        );

        // Verify
        // 登録情報の確認
        $logCurrencyRevert = LogCurrencyRevertHistory::query()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $logCurrencyRevert->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevert->comment);
        $this->assertEquals('gacha', $logCurrencyRevert->log_trigger_type);
        $this->assertEquals('gacha_id 1', $logCurrencyRevert->log_trigger_id);
        $this->assertEquals('gacha name 1', $logCurrencyRevert->log_trigger_name);
        $this->assertEquals('gacha detail', $logCurrencyRevert->log_trigger_detail);
        $this->assertEquals(RequestIdType::Request->value, $logCurrencyRevert->log_request_id_type);
        $this->assertEquals('request1', $logCurrencyRevert->log_request_id);
        $this->assertEquals('2023-01-01 00:00:00', $logCurrencyRevert->log_created_at);
        $this->assertEquals(100, $logCurrencyRevert->log_change_paid_amount);
        $this->assertEquals(110, $logCurrencyRevert->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevert->trigger_type);
        $this->assertEquals('', $logCurrencyRevert->trigger_id);
        $this->assertEquals('', $logCurrencyRevert->trigger_name);
        $this->assertEquals('', $logCurrencyRevert->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevert->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevert->request_id);
    }
}
