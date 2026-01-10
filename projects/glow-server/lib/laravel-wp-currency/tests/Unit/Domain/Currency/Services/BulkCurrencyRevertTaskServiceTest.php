<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Carbon\CarbonImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetRevertHistoryLog;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetRepository;
use WonderPlanet\Domain\Currency\Services\BulkCurrencyRevertTaskService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class BulkCurrencyRevertTaskServiceTest extends TestCase
{
    private BulkCurrencyRevertTaskService $bulkCurrencyRevertTaskService;

    // 課金基盤のサービス
    // テストなので直接使う
    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bulkCurrencyRevertTaskService = $this->app->make(BulkCurrencyRevertTaskService::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
    }

    #[Test]
    public function registerTask_一括通貨返却タスクを登録する()
    {
        // Setup
        $admUserId = 1;
        $fileName = 'test.csv';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $totalCount = 3;

        // Exercise
        $task = $this->bulkCurrencyRevertTaskService->registerTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            $totalCount,
        );

        // Verify
        $bulkCurrencyRevertTaskId = $task->id;
        $this->assertIsString($bulkCurrencyRevertTaskId);

        // レコードのチェック
        $result = AdmBulkCurrencyRevertTask::find($bulkCurrencyRevertTaskId);
        $this->assertNotNull($result);

        $this->assertEquals($bulkCurrencyRevertTaskId, $result->id);
        $this->assertEquals($admUserId, $result->adm_user_id);
        $this->assertEquals($fileName, $result->file_name);
        $this->assertEquals($revertCurrencyNum, $result->revert_currency_num);
        $this->assertEquals($comment, $result->comment);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Ready, $result->status);
        $this->assertEquals($totalCount, $result->total_count);
        $this->assertEquals(0, $result->success_count);
        $this->assertEquals(0, $result->error_count);
    }

    #[Test]
    public function registerTaskTargets_タスクの処理対象を登録()
    {
        // Setup
        $bulkCurrencyRevertTaskId = 'bulkCurrencyRevertTaskId-1';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $chunkSize = 2;

        $expectedTargets = [
            [
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
            ],
            [
                'usr_user_id' => 'user-2',
                'consumed_at' => '2021-01-02 00:00:00+09:00',
                'trigger_type' => 'trigger_type-2',
                'trigger_id' => 'trigger-2',
                'trigger_name' => 'trigger_name-2',
                'request_id' => 'request-2',
                'sum_log_change_amount_paid' => 200,
                'sum_log_change_amount_free' => 200,
                'log_currency_paid_ids' => '4,5,6',
                'log_currency_free_ids' => '4,5,6',
            ],
            [
                'usr_user_id' => 'user-3',
                'consumed_at' => '2021-01-03 00:00:00+09:00',
                'trigger_type' => 'trigger_type-3',
                'trigger_id' => 'trigger-3',
                'trigger_name' => 'trigger_name-3',
                'request_id' => 'request-3',
                'sum_log_change_amount_paid' => 300,
                'sum_log_change_amount_free' => 300,
                'log_currency_paid_ids' => '7,8,9',
                'log_currency_free_ids' => '7,8,9',
            ],
        ];

        // Exercise
        $targets = $this->bulkCurrencyRevertTaskService->registerTaskTargets(
            $bulkCurrencyRevertTaskId,
            $revertCurrencyNum,
            $comment,
            $expectedTargets,
            $chunkSize,
        );

        // Verify
        // 対象ユーザーのレコードのチェック
        $results = AdmBulkCurrencyRevertTaskTarget::orderBy('consumed_at')->get();
        $this->assertCount(count($expectedTargets), $results);

        foreach (collect($expectedTargets)->sortBy('consumed_at') as $index => $expected) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $results[$index]->adm_bulk_currency_revert_task_id);
            $this->assertEquals($revertCurrencyNum, $results[$index]->revert_currency_num);
            $this->assertEquals($expected['usr_user_id'], $results[$index]->usr_user_id);
            $this->assertEquals(
                CarbonImmutable::parse($expected['consumed_at'])->setTimezone('UTC')->format('Y-m-d H:i:s'),
                $results[$index]->consumed_at->setTimezone('UTC')->format('Y-m-d H:i:s')
            );
            $this->assertEquals($expected['trigger_type'], $results[$index]->trigger_type);
            $this->assertEquals($expected['trigger_id'], $results[$index]->trigger_id);
            $this->assertEquals($expected['trigger_name'], $results[$index]->trigger_name);
            $this->assertEquals($expected['request_id'], $results[$index]->request_id);
            $this->assertEquals($expected['sum_log_change_amount_paid'], $results[$index]->sum_log_change_amount_paid);
            $this->assertEquals($expected['sum_log_change_amount_free'], $results[$index]->sum_log_change_amount_free);
            $this->assertEquals($comment, $results[$index]->comment);

            // ログの照合
            $ids = explode(',', $expected['log_currency_paid_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $results[$index]->paidLogs->pluck('log_currency_paid_id')->sort()->values()->toArray()
            );

            $ids = explode(',', $expected['log_currency_free_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $results[$index]->freeLogs->pluck('log_currency_free_id')->sort()->values()->toArray()
            );

            $this->assertCount(0, $results[$index]->revertHistoryLogs);
        }

        // 戻り値のチェック
        $this->assertCount(count($expectedTargets), $targets);
        $targets = $targets->sortBy('consumed_at')->values();
        foreach ($expectedTargets as $index => $expected) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $targets[$index]['adm_bulk_currency_revert_task_id']);
            $this->assertEquals($revertCurrencyNum, $targets[$index]['revert_currency_num']);
            $this->assertEquals($expected['usr_user_id'], $targets[$index]['usr_user_id']);
            $this->assertEquals(
                CarbonImmutable::parse($expected['consumed_at'])->format('Y-m-d H:i:s'),
                $targets[$index]['consumed_at']->setTimeZone('Asia/Tokyo')->format('Y-m-d H:i:s')
            );
            $this->assertEquals($expected['trigger_type'], $targets[$index]['trigger_type']);
            $this->assertEquals($expected['trigger_id'], $targets[$index]['trigger_id']);
            $this->assertEquals($expected['trigger_name'], $targets[$index]['trigger_name']);
            $this->assertEquals($expected['request_id'], $targets[$index]['request_id']);
            $this->assertEquals($expected['sum_log_change_amount_paid'], $targets[$index]['sum_log_change_amount_paid']);
            $this->assertEquals($expected['sum_log_change_amount_free'], $targets[$index]['sum_log_change_amount_free']);
            $this->assertEquals($comment, $targets[$index]['comment']);

            // ログの照合
            $ids = explode(',', $expected['log_currency_paid_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $targets[$index]->paidLogs->pluck('log_currency_paid_id')->sort()->values()->toArray()
            );

            $ids = explode(',', $expected['log_currency_free_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $targets[$index]->freeLogs->pluck('log_currency_free_id')->sort()->values()->toArray()
            );

            $this->assertCount(0, $targets[$index]->revertHistoryLogs);
        }
    }

    #[Test]
    public function startTask_タスクを開始するステータスにする()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Ready,
        ]);

        // Exercise
        $this->bulkCurrencyRevertTaskService->startTask($task);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Processing, $result->status);
    }

    #[Test]
    public function revertCurrencyFromTarget_対象データに対して通貨を返却する()
    {
        // Setup
        $userId = 'user-1';
        // 対象になるユーザーデータを作成
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償通貨の登録
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $amount = 100;
        $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('add', '', '', '')
        );
        // 有償通貨の消費
        $this->currencyService->useCurrency(
            $userId,
            $osPlatform,
            $billingPlatform,
            200,
            new Trigger(
                'unit_test',
                '1',
                '',
                ''
            )
        );

        // 返却データの作成
        $paidIds = implode(
            ',',
            LogCurrencyPaid::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );
        $freeIds = implode(
            ',',
            LogCurrencyFree::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );

        $admUserId = 1;
        $fileName = 'test.csv';
        $revertCurrencyNum = 150;
        $comment = 'comment-1';
        $chunkSize = 2;

        $expectedTargets = [
            [
                'usr_user_id' => $userId,
                'consumed_at' => '2021-01-01 00:00:00+09:00',
                'trigger_type' => 'unit_test',
                'trigger_id' => '1',
                'trigger_name' => '',
                'request_id' => 'request-1',
                'sum_log_change_amount_paid' => 50,
                'sum_log_change_amount_free' => 100,
                'log_currency_paid_ids' => $paidIds,
                'log_currency_free_ids' => $freeIds,
            ],
        ];
        $task = $this->bulkCurrencyRevertTaskService->registerTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            count($expectedTargets),
        );
        $targets = $this->bulkCurrencyRevertTaskService->registerTaskTargets(
            $task->id,
            $revertCurrencyNum,
            $comment,
            $expectedTargets,
            $chunkSize,
        );

        // コールバックの通過を確認するためのフラグ
        $beforeTargetRevertCallbackCalled = false;
        $afterTargetRevertCallbackCalled = false;
        $finishedTargetCallbackCalled = false;
        $errorTargetCallbackCalled = false;

        // Exercise
        $result = $this->bulkCurrencyRevertTaskService->revertCurrencyFromTarget(
            target: $targets[0],
            revertCurrencyNum: $revertCurrencyNum,
            comment: $comment,
            beforeTargetRevertCallback: function() use (&$beforeTargetRevertCallbackCalled) {
                $beforeTargetRevertCallbackCalled = true;
            },
            afterTargetRevertCallback: function() use (&$afterTargetRevertCallbackCalled) {
                $afterTargetRevertCallbackCalled = true;
            },
            finishedTargetCallback: function() use (&$finishedTargetCallbackCalled) {
                $finishedTargetCallbackCalled = true;
            },
            errorTargetCallback: function() use (&$errorTargetCallbackCalled) {
                $errorTargetCallbackCalled = true;
            },
        );

        // Verify
        // グルーピングされた返却データは1つになるため、返ってくるIDも1つ
        $this->assertCount(1, $result);

        // 返却されていることの確認
        // 返却結果の詳細はrevertCurrencyFromLogのテストなどで確認しているので、ここでは簡易的な確認のみ
        $summary = $this->currencyService->getCurrencySummary($userId);
        $this->assertEquals(100 + 100 - 200 + 150, $summary->getTotalAmount());
        $this->assertEquals(100, $summary->getTotalPaidAmount());
        $this->assertEquals(50, $summary->getFreeAmount());

        // ターゲットのステータスが完了になっていることの確認
        $target = AdmBulkCurrencyRevertTaskTarget::find($targets[0]->id);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Finished, $target->status);

        // 返却ログとタスクが紐づいていることの確認
        $actual = AdmBulkCurrencyRevertTaskTargetRevertHistoryLog::query()
            ->where('adm_bulk_currency_revert_task_target_id', $targets[0]->id)
            ->get();
        $this->assertCount(1, $actual);
        $this->assertEquals($target->usr_user_id, $actual[0]->usr_user_id);
        $this->assertEquals($result[0], $actual[0]->log_currency_revert_history_id);

        // コールバックの確認
        $this->assertTrue($beforeTargetRevertCallbackCalled);
        $this->assertTrue($afterTargetRevertCallbackCalled);
        $this->assertTrue($finishedTargetCallbackCalled);
        $this->assertFalse($errorTargetCallbackCalled);
    }

    #[Test]
    public function revertCurrencyFromTarget_エラー時のコールバックが呼ばれる()
    {
        // Setup
        $userId = 'user-1';
        // 対象になるユーザーデータを作成
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償通貨の登録
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $amount = 100;
        $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('add', '', '', '')
        );
        // 有償通貨の消費
        $this->currencyService->useCurrency(
            $userId,
            $osPlatform,
            $billingPlatform,
            200,
            new Trigger(
                'unit_test',
                '1',
                '',
                ''
            )
        );

        // 返却データの作成
        $paidIds = implode(
            ',',
            LogCurrencyPaid::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );
        $freeIds = implode(
            ',',
            LogCurrencyFree::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );

        $admUserId = 1;
        $fileName = 'test.csv';
        $revertCurrencyNum = 150;
        $comment = 'comment-1';
        $chunkSize = 2;

        $expectedTargets = [
            [
                'usr_user_id' => $userId,
                'consumed_at' => '2021-01-01 00:00:00+09:00',
                'trigger_type' => 'unit_test',
                'trigger_id' => '1',
                'trigger_name' => '',
                'request_id' => 'request-1',
                'sum_log_change_amount_paid' => 50,
                'sum_log_change_amount_free' => 100,
                'log_currency_paid_ids' => $paidIds,
                'log_currency_free_ids' => $freeIds,
            ],
        ];
        $task = $this->bulkCurrencyRevertTaskService->registerTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            count($expectedTargets),
        );
        $targets = $this->bulkCurrencyRevertTaskService->registerTaskTargets(
            $task->id,
            $revertCurrencyNum,
            $comment,
            $expectedTargets,
            $chunkSize,
        );
        // コールバックの通過を確認するためのフラグ
        $beforeTargetRevertCallbackCalled = false;
        $afterTargetRevertCallbackCalled = false;
        $finishedTargetCallbackCalled = false;
        $errorTargetCallbackCalled = false;

        // エラーを発生させるためにAdmBulkCurrencyRevertTaskTargetRepositoryのモックを使う
        $expectedException = new \Exception('Error occurred');
        $mock = Mockery::mock(AdmBulkCurrencyRevertTaskTargetRepository::class);
        $mock->shouldReceive('updateStatusToProcessing')
            ->andThrow($expectedException);
        app()->instance(AdmBulkCurrencyRevertTaskTargetRepository::class, $mock);
        // モックを使わせるためにBulkCurrencyRevertTaskServiceを再生成
        /** @var BulkCurrencyRevertTaskService $bulkCurrencyRevertTaskService */
        $bulkCurrencyRevertTaskService = app()->make(BulkCurrencyRevertTaskService::class);

        // Exercise
        // Exceptionを検知
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedException->getMessage());
        try {
            $result = $bulkCurrencyRevertTaskService->revertCurrencyFromTarget(
                target: $targets[0],
                revertCurrencyNum: $revertCurrencyNum,
                comment: $comment,
                beforeTargetRevertCallback: function () use (&$beforeTargetRevertCallbackCalled) {
                    $beforeTargetRevertCallbackCalled = true;
                },
                afterTargetRevertCallback: function () use (&$afterTargetRevertCallbackCalled) {
                    $afterTargetRevertCallbackCalled = true;
                },
                finishedTargetCallback: function () use (&$finishedTargetCallbackCalled) {
                    $finishedTargetCallbackCalled = true;
                },
                errorTargetCallback: function () use (&$errorTargetCallbackCalled) {
                    $errorTargetCallbackCalled = true;
                },
            );
        } catch (\Exception $e) {

            // Verify
            // コールバックフラグの確認
            $this->assertFalse($beforeTargetRevertCallbackCalled);
            $this->assertFalse($afterTargetRevertCallbackCalled);
            $this->assertFalse($finishedTargetCallbackCalled);
            $this->assertTrue($errorTargetCallbackCalled);

            throw $e;
        }
    }

    #[Test]
    public function finishBulkCurrencyRevertTask_タスクを完了する()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 10,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        AdmBulkCurrencyRevertTaskTarget::factory()->count(6)->sequence(function ($sequence) use ($task) {
                return [
                    'seq_no' => $sequence->index + 1,
                    'adm_bulk_currency_revert_task_id' => $task->id,
                    'status' => AdmBulkCurrencyRevertTaskTargetStatus::Finished,
                ];
            })->create();
        AdmBulkCurrencyRevertTaskTarget::factory()->count(4)->sequence(function ($sequence) use ($task) {
                return [
                    'seq_no' => $sequence->index + 7,
                    'adm_bulk_currency_revert_task_id' => $task->id,
                    'status' => AdmBulkCurrencyRevertTaskTargetStatus::Error,
                ];
            })->create();

        // Exercise
        $this->bulkCurrencyRevertTaskService->finishBulkCurrencyRevertTask($task->id);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Finished, $result->status);
        $this->assertEquals(10, $result->total_count);
        $this->assertEquals(6, $result->success_count);
        $this->assertEquals(4, $result->error_count);
    }

    #[Test]
    public function updateTaskToError_タスクのステータスをエラーにする()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 10,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        AdmBulkCurrencyRevertTaskTarget::factory()->count(6)->sequence(function ($sequence) use ($task) {
            return [
                'seq_no' => $sequence->index + 1,
                'adm_bulk_currency_revert_task_id' => $task->id,
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Finished,
            ];
        })->create();
        AdmBulkCurrencyRevertTaskTarget::factory()->count(4)->sequence(function ($sequence) use ($task) {
            return [
                'seq_no' => $sequence->index + 7,
                'adm_bulk_currency_revert_task_id' => $task->id,
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Error,
            ];
        })->create();
        $error = new \Exception('Error occurred');
        $errorMessage = json_encode([
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);

        // Exercise
        $this->bulkCurrencyRevertTaskService->updateTaskToError($task->id, $error);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Error, $result->status);
        $this->assertEquals(10, $result->total_count);
        $this->assertEquals(6, $result->success_count);
        $this->assertEquals(4, $result->error_count);
        $this->assertEquals($errorMessage, $result->error_message);
    }

    #[Test]
    public function updateTargetToError_ターゲットのステータスをエラーにする()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 1,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create([
            'adm_bulk_currency_revert_task_id' => $task->id,
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Processing,
        ]);
        $error = new \Exception('Error occurred');
        $errorMessage = json_encode([
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);

        // Exercise
        $this->bulkCurrencyRevertTaskService->updateTargetToError($target->id, $error);

        // Verify
        $result = AdmBulkCurrencyRevertTaskTarget::find($target->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Error, $result->status);
        $this->assertEquals($errorMessage, $result->error_message);
    }
}
