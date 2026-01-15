<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetRevertHistoryLog;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository;

class AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepositoryTest extends TestCase
{
    private AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository $admBulkCurrencyRevertTaskTargetRevertHistoryLogRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admBulkCurrencyRevertTaskTargetRevertHistoryLogRepository = new AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository();
    }

    #[Test]
    public function bulkInsert_データ登録()
    {
        // Setup
        $admBulkCurrencyRevertTaskTargetId = 'adm_bulk_currency_revert_task_target_id';
        $usrUserId = 'usr_user_id';
        $logCurrencyRevertHistoryIds = [
            'log_currency_revert_history_id_1',
            'log_currency_revert_history_id_2',
        ];

        // Exercise
        $result = $this->admBulkCurrencyRevertTaskTargetRevertHistoryLogRepository->bulkInsert(
            $admBulkCurrencyRevertTaskTargetId,
            $usrUserId,
            $logCurrencyRevertHistoryIds
        );

        // Verify
        $this->assertTrue($result);

        $actual = AdmBulkCurrencyRevertTaskTargetRevertHistoryLog::query()
            ->orderBy('log_currency_revert_history_id')
            ->get();
        $this->assertCount(2, $actual);
        $this->assertEquals($admBulkCurrencyRevertTaskTargetId, $actual[0]->adm_bulk_currency_revert_task_target_id);
        $this->assertEquals($usrUserId, $actual[0]->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistoryIds[0], $actual[0]->log_currency_revert_history_id);
        
        $this->assertEquals($admBulkCurrencyRevertTaskTargetId, $actual[1]->adm_bulk_currency_revert_task_target_id);
        $this->assertEquals($usrUserId, $actual[1]->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistoryIds[1], $actual[1]->log_currency_revert_history_id);
    }
}
