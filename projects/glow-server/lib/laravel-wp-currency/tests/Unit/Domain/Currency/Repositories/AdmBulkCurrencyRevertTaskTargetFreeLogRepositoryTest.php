<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetFreeLog;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetFreeLogRepository;

class AdmBulkCurrencyRevertTaskTargetFreeLogRepositoryTest extends TestCase
{
    private AdmBulkCurrencyRevertTaskTargetFreeLogRepository $admBulkCurrencyRevertTaskTargetFreeLogRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admBulkCurrencyRevertTaskTargetFreeLogRepository =
             $this->app->make(AdmBulkCurrencyRevertTaskTargetFreeLogRepository::class);
    }

    #[Test]
    public function bulkInsert_一括登録()
    {
        // Setup
        $now = now();
        $data = [
            [
                'id' => '1',
                'adm_bulk_currency_revert_task_target_id' => 1,
                'usr_user_id' => 'user-1',
                'log_currency_free_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => '2',
                'adm_bulk_currency_revert_task_target_id' => 2,
                'usr_user_id' => 'user-2',
                'log_currency_free_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => '3',
                'adm_bulk_currency_revert_task_target_id' => 3,
                'usr_user_id' => 'user-3',
                'log_currency_free_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Exercise
        $ret = $this->admBulkCurrencyRevertTaskTargetFreeLogRepository->bulkInsert($data);

        // Verify
        $this->assertTrue($ret);

        $actual = AdmBulkCurrencyRevertTaskTargetFreeLog::all()->sortBy('adm_bulk_currency_revert_task_target_id');
        $this->assertCount(3, $actual);
        $this->assertEquals(1, $actual[0]->adm_bulk_currency_revert_task_target_id);
        $this->assertEquals('user-1', $actual[0]->usr_user_id);
        $this->assertEquals(1, $actual[0]->log_currency_free_id);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[0]->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[0]->updated_at->format('Y-m-d H:i:s'));

        $this->assertEquals(2, $actual[1]->adm_bulk_currency_revert_task_target_id);
        $this->assertEquals('user-2', $actual[1]->usr_user_id);
        $this->assertEquals(2, $actual[1]->log_currency_free_id);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[1]->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[1]->updated_at->format('Y-m-d H:i:s'));

        $this->assertEquals(3, $actual[2]->adm_bulk_currency_revert_task_target_id);
        $this->assertEquals('user-3', $actual[2]->usr_user_id);
        $this->assertEquals(3, $actual[2]->log_currency_free_id);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[2]->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[2]->updated_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function makeRecordsFromTargetData_入力データからレコードデータを作成()
    {
        // Setup
        $now = now()->toImmutable();
        $bulkCurrencyRevertTaskTargetId = 'bulk-currency-revert-task-target-1';
        $target = [
            'usr_user_id' => 'user-1',
            'consumed_at' => '2021-01-01 00:00:00+09:00',
            'trigger_type' => 'trigger_type-1',
            'trigger_id' => 'trigger-1',
            'trigger_name' => 'trigger_name-1',
            'request_id' => 'request-1',
            'sum_log_change_amount_paid' => 100,
            'sum_log_change_amount_free' => 100,
            'log_currency_paid_ids' => '1,2,3',
            'log_currency_free_ids' => '4,5,6',
        ];

        // Exercise
        $actual = $this->admBulkCurrencyRevertTaskTargetFreeLogRepository->makeRecordsFromTargetData(
            $bulkCurrencyRevertTaskTargetId,
            $now,
            $target
        );

        // Verify
        $this->assertCount(3, $actual);
        $this->assertEquals('bulk-currency-revert-task-target-1', $actual[0]['adm_bulk_currency_revert_task_target_id']);
        $this->assertEquals('user-1', $actual[0]['usr_user_id']);
        $this->assertEquals(4, $actual[0]['log_currency_free_id']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[0]['created_at']->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[0]['updated_at']->format('Y-m-d H:i:s'));

        $this->assertEquals('bulk-currency-revert-task-target-1', $actual[1]['adm_bulk_currency_revert_task_target_id']);
        $this->assertEquals('user-1', $actual[1]['usr_user_id']);
        $this->assertEquals(5, $actual[1]['log_currency_free_id']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[1]['created_at']->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[1]['updated_at']->format('Y-m-d H:i:s'));
        
        $this->assertEquals('bulk-currency-revert-task-target-1', $actual[2]['adm_bulk_currency_revert_task_target_id']);
        $this->assertEquals('user-1', $actual[2]['usr_user_id']);
        $this->assertEquals(6, $actual[2]['log_currency_free_id']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[2]['created_at']->format('Y-m-d H:i:s'));
        $this->assertEquals($now->format('Y-m-d H:i:s'), $actual[2]['updated_at']->format('Y-m-d H:i:s'));
    }
}
