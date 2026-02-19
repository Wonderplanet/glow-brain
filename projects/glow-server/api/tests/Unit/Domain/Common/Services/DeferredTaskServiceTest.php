<?php

namespace Tests\Unit\Domain\Common\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\DeferredTaskService;
use PHPUnit\Framework\TestCase;

class DeferredTaskServiceTest extends TestCase
{
    private DeferredTaskService $deferredTaskService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deferredTaskService = new DeferredTaskService();
    }

    public function test_registerAfterTransaction_タスクが正常に登録される()
    {
        // Setup
        $executed = false;
        $task = function () use (&$executed) {
            $executed = true;
        };

        // Exercise
        $this->deferredTaskService->registerAfterTransaction($task);

        // Verify - 登録時点では実行されない
        $this->assertFalse($executed);
    }

    public function test_executeAfterTransactionTasks_登録されたタスクが実行される()
    {
        // Setup
        $executed1 = false;
        $executed2 = false;

        $task1 = function () use (&$executed1) {
            $executed1 = true;
        };
        $task2 = function () use (&$executed2) {
            $executed2 = true;
        };

        // Exercise
        $this->deferredTaskService->registerAfterTransaction($task1);
        $this->deferredTaskService->registerAfterTransaction($task2);
        $this->deferredTaskService->executeAfterTransactionTasks();

        // Verify
        $this->assertTrue($executed1);
        $this->assertTrue($executed2);
    }

    public function test_executeAfterTransactionTasks_実行後にタスクがクリアされる()
    {
        // Setup
        $executeCount = 0;
        $task = function () use (&$executeCount) {
            $executeCount++;
        };

        // Exercise
        $this->deferredTaskService->registerAfterTransaction($task);
        $this->deferredTaskService->executeAfterTransactionTasks();
        $this->deferredTaskService->executeAfterTransactionTasks(); // 2回目の実行

        // Verify - 1回目の実行でタスクがクリアされるため、2回目では実行されない
        $this->assertEquals(1, $executeCount);
    }

    public function test_executeAfterTransactionTasks_複数タスクの実行順序()
    {
        // Setup
        $executionOrder = [];

        $task1 = function () use (&$executionOrder) {
            $executionOrder[] = 'task1';
        };
        $task2 = function () use (&$executionOrder) {
            $executionOrder[] = 'task2';
        };
        $task3 = function () use (&$executionOrder) {
            $executionOrder[] = 'task3';
        };

        // Exercise
        $this->deferredTaskService->registerAfterTransaction($task1);
        $this->deferredTaskService->registerAfterTransaction($task2);
        $this->deferredTaskService->registerAfterTransaction($task3);
        $this->deferredTaskService->executeAfterTransactionTasks();

        // Verify - 登録順に実行される
        $this->assertEquals(['task1', 'task2', 'task3'], $executionOrder);
    }

    public function test_executeAfterTransactionTasks_タスクが未登録の場合は何も実行されない()
    {
        // Setup - タスクを登録しない

        // Exercise & Verify - 例外が発生しないことを確認
        $this->expectNotToPerformAssertions();
        $this->deferredTaskService->executeAfterTransactionTasks();
    }

    public function test_複数タスク登録があって途中で例外が発生したらそれ以降のタスクは実行されない()
    {
        // Setup
        $executed1 = false;
        $executed3 = false;

        $task1 = function () use (&$executed1) {
            $executed1 = true;
        };
        $task2 = function () {
            throw new GameException(ErrorCode::UNKNOWN_ERROR, 'Test exception');
        };
        $task3 = function () use (&$executed3) {
            $executed3 = true;
        };

        // Exercise
        $this->deferredTaskService->registerAfterTransaction($task1);
        $this->deferredTaskService->registerAfterTransaction($task2);
        $this->deferredTaskService->registerAfterTransaction($task3);

        // Verify - task2で例外が発生するが、task1とtask3は正常に実行される
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::UNKNOWN_ERROR);
        $this->expectExceptionMessage('Test exception');

        try {
            $this->deferredTaskService->executeAfterTransactionTasks();
        } catch (\RuntimeException $e) {
            // task1は例外発生前に実行される
            $this->assertTrue($executed1);
            // task3は例外発生後なので実行されない
            $this->assertFalse($executed3);
        }
    }
}
