<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskRepository;

class AdmBulkCurrencyRevertTaskRepositoryTest extends TestCase
{
    private AdmBulkCurrencyRevertTaskRepository $admBulkCurrencyRevertTaskRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->admBulkCurrencyRevertTaskRepository = app()->make(AdmBulkCurrencyRevertTaskRepository::class);
    }

    #[Test]
    public function create_タスクを登録する(): void
    {
        // Setup
        $admUserId = 1;
        $fileName = 'fileName-1';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $totalCount = 100;
        $status = AdmBulkCurrencyRevertTaskStatus::Ready;

        // Exercise
        $this->admBulkCurrencyRevertTaskRepository
            ->create(
                $admUserId,
                $fileName,
                $revertCurrencyNum,
                $comment,
                $totalCount
            );

        // Verify
        $results = AdmBulkCurrencyRevertTask::all();
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals($admUserId, $result->adm_user_id);
        $this->assertEquals($fileName, $result->file_name);
        $this->assertEquals($revertCurrencyNum, $result->revert_currency_num);
        $this->assertEquals($comment, $result->comment);
        $this->assertEquals($status, $result->status);
        $this->assertEquals($totalCount, $result->total_count);
        $this->assertEquals(0, $result->success_count);
        $this->assertEquals(0, $result->error_count);

        $this->assertTrue($result->isReady());
        $this->assertFalse($result->isRegistered());
        $this->assertFalse($result->isFinished());
        $this->assertFalse($result->isError());
    }

    #[Test]
    public function updateStatusToProcessing_ステータスをProcessingに更新(): void
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Ready,
        ]);

        // Exercise
        $this->admBulkCurrencyRevertTaskRepository->updateStatusToProcessing($task->id);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertTrue($result->isProcessing());
    }
}
