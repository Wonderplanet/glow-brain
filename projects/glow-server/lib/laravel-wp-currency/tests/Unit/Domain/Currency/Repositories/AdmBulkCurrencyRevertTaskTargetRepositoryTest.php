<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetRepository;

class AdmBulkCurrencyRevertTaskTargetRepositoryTest extends TestCase
{
    private AdmBulkCurrencyRevertTaskTargetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(AdmBulkCurrencyRevertTaskTargetRepository::class);
    }

    #[Test]
    public function bulkInsert_複数レコード登録を行う()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $chunkSize = 2;

        $records = [
            [
                'id' => '1',
                'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
                'seq_no' => 1,
                'usr_user_id' => 'user-1',
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Ready,
                'revert_currency_num' => $revertCurrencyNum,
                'consumed_at' => '2021-01-01 00:00:00',
                'trigger_type' => 'type-1',
                'trigger_id' => 'trigger-1',
                'trigger_name' => 'trigger-name-1',
                'request_id' => 'request-1',
                'sum_log_change_amount_paid' => 10,
                'sum_log_change_amount_free' => 20,
                'comment' => $comment,
                'error_message' => '',
            ],
            [
                'id' => '2',
                'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
                'seq_no' => 2,
                'usr_user_id' => 'user-2',
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Ready,
                'revert_currency_num' => $revertCurrencyNum,
                'consumed_at' => '2021-01-02 00:00:00',
                'trigger_type' => 'type-2',
                'trigger_id' => 'trigger-2',
                'trigger_name' => 'trigger-name-2',
                'request_id' => 'request-2',
                'sum_log_change_amount_paid' => 30,
                'sum_log_change_amount_free' => 40,
                'comment' => $comment,
                'error_message' => '',
            ],
            [
                'id' => '3',
                'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
                'seq_no' => 3,
                'usr_user_id' => 'user-3',
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Ready,
                'revert_currency_num' => $revertCurrencyNum,
                'consumed_at' => '2021-01-03 00:00:00',
                'trigger_type' => 'type-3',
                'trigger_id' => 'trigger-3',
                'trigger_name' => 'trigger-name-3',
                'request_id' => 'request-3',
                'sum_log_change_amount_paid' => 50,
                'sum_log_change_amount_free' => 60,
                'comment' => $comment,
                'error_message' => '',
            ],
        ];

        // Exercise
        $ret = $this->repository->bulkInsert(
            $records,
            $chunkSize,
        );

        // Verify
        $this->assertTrue($ret);

        $results = AdmBulkCurrencyRevertTaskTarget::query()->orderBy('consumed_at')->get();
        $this->assertCount(count($records), $results);

        $expectedSeqNo = 1;
        foreach (collect($records)->sortBy('consumed_at') as $index => $target) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $results[$index]->adm_bulk_currency_revert_task_id);
            $this->assertEquals($expectedSeqNo++, $results[$index]->seq_no);
            $this->assertEquals($revertCurrencyNum, $results[$index]->revert_currency_num);
            $this->assertEquals($target['usr_user_id'], $results[$index]->usr_user_id);
            $this->assertEquals(
                CarbonImmutable::parse($target['consumed_at'])->setTimezone('UTC')->format('Y-m-d H:i:s'),
                $results[$index]->consumed_at
            );
            $this->assertEquals($target['trigger_type'], $results[$index]->trigger_type);
            $this->assertEquals($target['trigger_id'], $results[$index]->trigger_id);
            $this->assertEquals($target['trigger_name'], $results[$index]->trigger_name);
            $this->assertEquals($target['request_id'], $results[$index]->request_id);
            $this->assertEquals($target['sum_log_change_amount_paid'], $results[$index]->sum_log_change_amount_paid);
            $this->assertEquals($target['sum_log_change_amount_free'], $results[$index]->sum_log_change_amount_free);
            $this->assertEquals($comment, $results[$index]->comment);
        }
    }

    #[Test]
    public function makeRecordFromTargetData_入力データからレコードデータを作成()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $seqNo = 1;
        $now = CarbonImmutable::now();
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
            'log_currency_free_ids' => '1,2,3',
        ];

        // Exercise
        $actual = $this->repository->makeRecordFromTargetData(
            $bulkCurrencyRevertTaskId,
            $revertCurrencyNum,
            $comment,
            $seqNo,
            $now,
            $target,
        );

        // Verify
        $this->assertEquals($bulkCurrencyRevertTaskId, $actual['adm_bulk_currency_revert_task_id']);
        $this->assertEquals($seqNo, $actual['seq_no']);
        $this->assertEquals($target['usr_user_id'], $actual['usr_user_id']);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Ready, $actual['status']);
        $this->assertEquals($revertCurrencyNum, $actual['revert_currency_num']);
        $this->assertEquals(CarbonImmutable::parse($target['consumed_at']), $actual['consumed_at']);
        $this->assertEquals($target['trigger_type'], $actual['trigger_type']);
        $this->assertEquals($target['trigger_id'], $actual['trigger_id']);
        $this->assertEquals($target['trigger_name'], $actual['trigger_name']);
        $this->assertEquals($target['request_id'], $actual['request_id']);
        $this->assertEquals($target['sum_log_change_amount_paid'], $actual['sum_log_change_amount_paid']);
        $this->assertEquals($target['sum_log_change_amount_free'], $actual['sum_log_change_amount_free']);
        $this->assertEquals($comment, $actual['comment']);
        $this->assertEquals('', $actual['error_message']);
        $this->assertEquals($now, $actual['created_at']);
        $this->assertEquals($now, $actual['updated_at']);
    }

    #[Test]
    public function findByBulkCurrencyRevertTaskId_タスクIDに紐づくターゲットを取得()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        AdmBulkCurrencyRevertTaskTarget::factory()
            ->count(3)
            ->sequence(fn(Sequence $sequence) => [
                'seq_no' => $sequence->index + 1,
            ])
            ->create([
                'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
            ]);
        // 別のタスクIDのデータ
        AdmBulkCurrencyRevertTaskTarget::factory()->count(2)->create();

        // Exercise
        $results = $this->repository->findByBulkCurrencyRevertTaskId($bulkCurrencyRevertTaskId);

        // Verify
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $result->adm_bulk_currency_revert_task_id);
        }
    }

    #[Test]
    public function updateStatusToProcessing_ステータスをProcessingに更新()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create([
            'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
            'status' => 'Ready',
        ]);

        // Exercise
        $result = $this->repository->updateStatusToProcessing(
            $target->id,
        );

        // Verify
        $this->assertTrue($result);

        $result = AdmBulkCurrencyRevertTaskTarget::find($target->id);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Processing, $result->status);
    }

    #[Test]
    public function updateStatusToFinished_ステータスをFinishedに更新()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create([
            'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Processing,
        ]);

        // Exercise
        $result = $this->repository->updateStatusToFinished(
            $target->id,
        );

        // Verify
        $this->assertTrue($result);

        $result = AdmBulkCurrencyRevertTaskTarget::find($target->id);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Finished, $result->status);
    }

    #[Test]
    public function updateStatusToError_ステータスをErrorに更新()
    {
        // Setup
        $bulkCurrencyRevertTaskId = '1';
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create([
            'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Processing,
        ]);

        // Exercise
        $result = $this->repository->updateStatusToError(
            $target->id,
            'error-message',
        );

        // Verify
        $this->assertTrue($result);

        $result = AdmBulkCurrencyRevertTaskTarget::find($target->id);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Error, $result->status);
        $this->assertEquals('error-message', $result->error_message);
    }
}
