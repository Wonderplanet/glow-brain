<?php

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Enums\StageResetType;
use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageService;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StageServiceTest extends TestCase
{
    private StageService $stageService;
    private UsrUserParameterRepository $usrUserParameterRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageService = app(StageService::class);
        $this->usrUserParameterRepository = app(UsrUserParameterRepository::class);
    }

    /**
     * @test
     */
    public function validateCanEnd_セッションがなくSTAGE_NOT_STARTエラーが出る()
    {
        // Setup
        $mstStageId = '1';

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
        ])->toEntity();
        $usrStageSession = null;
        $usrStage = UsrStage::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'clear_count' => 5,

        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_NOT_START);

        // Exercise
        $this->stageService->validateCanEnd($mstStage, $usrStageSession, $usrStage, null);

        // Verify
    }

    /**
     * @test
     */
    public function validateCanEnd_startしておらずSTAGE_NOT_STARTエラーが出る()
    {
        // Setup
        $mstStageId = '2';

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
        ])->toEntity();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'is_valid' => 0,
        ]);
        $usrStage = UsrStage::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'clear_count' => 5,

        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_NOT_START);

        // Exercise
        $this->stageService->validateCanEnd($mstStage, $usrStageSession, $usrStage, null);

        // Verify
    }

    /**
     * @test
     */
    public function validateCanEnd_usrStageレコードが存在せずSTAGE_NOT_STARTエラーが出る()
    {
        // Setup
        $mstStageId = '3';

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
        ])->toEntity();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
        ]);
        $usrStage = null;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_NOT_START);

        // Exercise
        $this->stageService->validateCanEnd($mstStage, $usrStageSession, $usrStage, null);

        // Verify
    }

    public static function params_test_validateCanAutoLap_エラーパターンチェック()
    {
        return [
            '正常 1周実行' => [
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorMsg' => '',
                'lapCount' => 1,
                'autoLapType' => null,
                'maxAutoLapCount' => 1,
                'usrStageClearCount' => null,
            ],
            '正常 2周実行(クリア後スタミナブースト化)' => [
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorMsg' => '',
                'lapCount' => 2,
                'autoLapType' => StageAutoLapType::AFTER_CLEAR->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
            '正常 2周実行(常時スタミナブースト化)' => [
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorMsg' => '',
                'lapCount' => 2,
                'autoLapType' => StageAutoLapType::INITIAL->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
            'エラー 不正回数実行' => [
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorMsg' => 'invalid lap count: 0',
                'lapCount' => 0,
                'autoLapType' => StageAutoLapType::INITIAL->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
            'エラー 広告視聴時スタミナブースト不可' => [
                'isChallengeAd' => true,
                'isThrowError' => true,
                'errorMsg' => 'cannot auto lap while challenge ad',
                'lapCount' => 2,
                'autoLapType' => StageAutoLapType::INITIAL->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
            'エラー ステージがスタミナブースト不可' => [
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorMsg' => 'stage does not support auto lap (mst_stage_id: stage_1)',
                'lapCount' => 2,
                'autoLapType' => null,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
            'エラー ステージクリア条件で未クリア' => [
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorMsg' => 'stage is after-clear auto lap but user has not cleared it',
                'lapCount' => 2,
                'autoLapType' => StageAutoLapType::AFTER_CLEAR->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => null,
            ],
            'エラー ステージクリア条件で挑戦済み未クリア' => [
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorMsg' => 'stage is after-clear auto lap but user has not cleared it',
                'lapCount' => 2,
                'autoLapType' => StageAutoLapType::AFTER_CLEAR->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 0,
            ],
            'エラー 最大周回数超過' => [
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorMsg' => 'lap count exceeds max auto lap count: 3',
                'lapCount' => 3,
                'autoLapType' => StageAutoLapType::AFTER_CLEAR->value,
                'maxAutoLapCount' => 2,
                'usrStageClearCount' => 1,
            ],
        ];
    }

    #[DataProvider('params_test_validateCanAutoLap_エラーパターンチェック')]
    public function test_validateCanAutoLap_エラーパターンチェック(
        bool $isChallengeAd,
        bool $isThrowError,
        string $errorMsg,
        int $lapCount,
        ?string $autoLapType,
        int $maxAutoLapCount,
        ?int $usrStageClearCount,
    ) {
        // Setup
        $mstStageId = 'stage_1';
        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'auto_lap_type' => $autoLapType,
            'max_auto_lap_count' => $maxAutoLapCount,
        ])->toEntity();

        $usrStage = null;
        if ($usrStageClearCount !== null) {
            $usrStage = UsrStage::factory()->createAndConvert([
                'usr_user_id' => $usrUser->getId(),
                'mst_stage_id' => $mstStageId,
                'clear_count' => $usrStageClearCount,
            ]);
        }

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::STAGE_CAN_NOT_AUTO_LAP);
            $this->expectExceptionMessage($errorMsg);
        }

        // Exercise
        $this->stageService->validateCanAutoLap(
            $mstStage,
            $usrStage,
            $isChallengeAd,
            $lapCount,
        );

        // Verify
        $this->assertTrue(true);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_resetStageEvent_マスターが無い場合もエラーにならないこと()
    {
        // Setup
        $mstStageId = '3';

        $usrUser = $this->createUsrUser();
        // 開放したいステージのマスターを用意しない
        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'reset_clear_count' => 4,
        ]);

        // Exercise
        $this->stageService->resetStageEvent($usrUser->getId(), CarbonImmutable::now());

        // Verify
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_resetStageEvent_ユーザデータが無い場合もエラーにならないこと()
    {
        // Setup
        $mstStageId = '3';

        $usrUser = $this->createUsrUser();
        // 開放したいステージのマスターを用意しない
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => 'stage1',
            'clearable_count' => 5,
            'ad_challenge_count' => 3,
        ]);

        // Exercise
        $this->stageService->resetStageEvent($usrUser->getId(), CarbonImmutable::now());

        // Verify
    }

    /**
     * @dataProvider params_test_resetStageEvent__リセットの確認
     */
    public function test_resetStageEvent_リセットの確認(
        string $latestResetAt,
        string $now,
        int $beforeCount,
        int $afterCount,
    ) {
        // Setup
        $mstStageId = 'stage_1';
        $now = CarbonImmutable::parse($now, 'Asia/Tokyo');
        CarbonImmutable::setTestNow($now);

        $latestResetAt = CarbonImmutable::parse($latestResetAt);

        $usrUser = $this->createUsrUser();

        MstStageEventSetting::factory()->create([
            'mst_stage_id' => $mstStageId,
            'reset_type' => StageResetType::DAILY->value,
            'clearable_count' => 5,
            'ad_challenge_count' => 3,
        ]);

        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'latest_reset_at' => $latestResetAt,
            'reset_clear_count' => $beforeCount,
            'reset_ad_challenge_count' => $beforeCount,
        ]);

        // Exercise
        $this->stageService->resetStageEvent($usrUser->getId(), $now);
        $this->saveAll();

        // Verify
        $usrStageEvent = UsrStageEvent::where('usr_user_id', $usrUser->getId())
            ->where('mst_stage_id', $mstStageId)
            ->first();
        $this->assertEquals($afterCount, $usrStageEvent->getResetClearCount());
        $this->assertEquals($afterCount, $usrStageEvent->getResetAdChallengeCount());
        if ($afterCount === 0) {
            $this->assertEquals($now->toDateTimeString(), $usrStageEvent->getLatestResetAt());
        } else {
            $this->assertEquals($latestResetAt->toDateTimeString(), $usrStageEvent->getLatestResetAt());
        }
    }

    public static function params_test_resetStageEvent__リセットの確認()
    {
        return [
            '日付を跨ぐ場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-22 10:34:55', 'beforeCount' => 3, 'afterCount' => 0],
            '日付をまたがない場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-21 23:59:59', 'beforeCount' => 3, 'afterCount' => 3],
        ];
    }

    public function test_abort_セッションがある場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstStageId = '1';

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '1',
            'is_valid' => StageSessionStatus::STARTED,
            'party_no' => 1,
            'continue_count' => StageConstant::CONTINUE_MAX_COUNT,
        ]);

        // Exercise
        $this->stageService->abort($usrUserId);
        $this->saveAll();

        // Verify
        $usrStageSession->refresh();
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_abort_セッションがない場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // Exercise
        $this->stageService->abort($usrUserId);
    }

    public function test_SpeedAttackReset()
    {
        $now = $this->fixTime();
        $usrStageEvents = UsrStageEvent::factory()->count(2)->sequence(
            [
                'mst_stage_id' => 'speed_no_reset',
                'reset_clear_count' => 1,
                'reset_ad_challenge_count' => 1,
                'reset_clear_time_ms' => 1000,
                'clear_time_ms' => 1000,
                'latest_reset_at' => $now->toDateTimeString(),
                'latest_event_setting_end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'mst_stage_id' => 'speed_reset',
                'reset_clear_count' => 0,
                'reset_ad_challenge_count' => 0,
                'reset_clear_time_ms' => 1000,
                'clear_time_ms' => 1000,
                'latest_reset_at' => $now->toDateTimeString(),
                'latest_event_setting_end_at' => $now->subDays(10)->toDateTimeString(),
            ],
            [
                'mst_stage_id' => 'none',
                'reset_clear_count' => 0,
                'reset_ad_challenge_count' => 0,
                'reset_clear_time_ms' => 1000,
                'clear_time_ms' => 1000,
                'latest_reset_at' => $now->toDateTimeString(),
                'latest_event_setting_end_at' => $now->subDays(10)->toDateTimeString(),
            ],
        )->create([
                    'usr_user_id' => '1',
                ]);

        MstStageEventSetting::factory()->count(2)->sequence(
            [
                'mst_stage_id' => 'speed_no_reset',
                'ad_challenge_count' => 0,
                'start_at' => $now->subDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'mst_stage_id' => 'speed_reset',
                'ad_challenge_count' => 0,
                'start_at' => $now->subDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'mst_stage_id' => 'none',
                'ad_challenge_count' => 0,
                'start_at' => $now->subDays(5)->toDateTimeString(),
                'end_at' => $now->subDays(4)->toDateTimeString(),
            ],
        )->create();
        MstInGameSpecialRule::factory()->createMany([
            [
                'content_type' => InGameContentType::STAGE,
                'target_id' => 'speed_no_reset',
                'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
                'start_at' => $now->subDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'content_type' => InGameContentType::STAGE,
                'target_id' => 'speed_reset',
                'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
                'start_at' => $now->subDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
        ]);

        $usrStageEvents = $this->stageService->resetStageEventSpeedAttack($now, $usrStageEvents);

        $noResetUsrStage = $usrStageEvents->firstWhere('mst_stage_id', 'speed_no_reset');
        $this->assertEquals(1000, $noResetUsrStage->getResetClearTimeMs());
        $this->assertEquals(1000, $noResetUsrStage->getClearTimeMs());

        $resetUsrStage = $usrStageEvents->firstWhere('mst_stage_id', 'speed_reset');
        $this->assertNull($resetUsrStage->getResetClearTimeMs());
        $this->assertEquals(1000, $resetUsrStage->getClearTimeMs());

        $noneUsrStage = $usrStageEvents->firstWhere('mst_stage_id', 'none');
        $this->assertNull($noneUsrStage);
    }
}
