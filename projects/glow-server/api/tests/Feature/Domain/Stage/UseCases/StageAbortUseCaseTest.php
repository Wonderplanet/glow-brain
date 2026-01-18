<?php

namespace Tests\Feature\Domain\Stage;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Stage\Enums\LogStageResult;
use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageAbortUseCase;
use Tests\TestCase;


class StageAbortUseCaseTest extends TestCase
{
    private StageAbortUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(StageAbortUseCase::class);
    }

    /**
     * @dataProvider params_exec_正常実行
     */
    public function test_exec_正常実行(LogStageResult $abortType)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstStageId = "10";
        
        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => StageSessionStatus::STARTED,
        ]);

        // Exercise
        $results = $this->useCase->exec($currentUser, $abortType->value);
        $this->saveAll();

        // Verify
        // DB確認
        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());
    }

    public static function params_exec_正常実行()
    {
        return [
            '敗北' => [LogStageResult::DEFEAT],
            'リタイア' => [LogStageResult::RETIRE],
            '中断復帰キャンセル' => [LogStageResult::CANCEL],
            '期限切れ' => [LogStageResult::CANCEL], // 期限切れはCANCEL扱い
        ];
    }

    public function test_exec_セッション無しの場合もエラーなく実行される()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        // Exercise
        $this->useCase->exec($currentUser, LogStageResult::RETIRE->value);

        // Verify
        $this->assertTrue(true);
    }
}
