<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignTargetIdType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageStartEnhanceQuestService;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StageStartEnhanceQuestServiceTest extends TestCase
{
    private StageStartEnhanceQuestService $stageStartEnhanceQuestService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageStartEnhanceQuestService = app(StageStartEnhanceQuestService::class);
    }

    public function test_start_ステージ開始処理が実行できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => QuestType::ENHANCE->value,
            'difficulty' => 'Normal',
            // 現在日時が有効範囲になるように設定
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
            'mst_quest_id' => 'quest1',
            'cost_stamina' => 10, // スタミナ消費しないが、誤って消費していないことを確認するためにデータ追加
        ])->toEntity();

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'continue_count' => 3,
            'latest_reset_at' => $now->subDays(2), // 2日前に最終リセット日をセット
        ]);

        $oprCampaigns = OprCampaign::factory()->createMany([
            // 通常挑戦可能回数+1
            [
                'id' => 'campaign6',
                'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
                'target_type' => QuestType::ENHANCE->value . 'Quest',
                'effect_value' => 1,
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest1',
                // 現在日時が有効範囲になるように設定
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ])->map(fn($entity) => $entity->toEntity());

        // 最大で3(通常挑戦)+1(キャンペーン効果)+2(広告視聴挑戦)回挑戦できる設定
        MstConfig::factory()->createMany([
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_LIMIT',
                'value' => 3,
            ],
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_AD_LIMIT',
                'value' => 2,
            ],
        ]);

        MstUnit::factory()->create([
            'id' => 'mst_unit1',
        ]);

        // usr
        $usrStageEnhance = UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'clear_count' => 6,
            // 挑戦可能回数上限に達していて、リセットしないと挑戦できない状態にする
            'reset_challenge_count' => 4,
            'reset_ad_challenge_count' => 2,
            'max_score' => 999,
            'latest_reset_at' => $now->subDays(1), // リセットされる状態にする
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 100,
        ]);
        UsrUnit::factory()->create([
            'id' => 'unit1',
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'mst_unit1',
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => 5,
            'usr_unit_id_1' => 'unit1',
        ]);

        // Exercise
        $this->stageStartEnhanceQuestService->start(
            usrUserId: $usrUserId,
            partyNo: 5,
            mstStage: $mstStage,
            mstQuest: $mstQuest,
            isChallengeAd: false, // 通常挑戦
            lapCount: 1,
            now: $now,
        );
        $this->saveAll();

        // Verify
        // セッション開始できている
        $usrStageSession = UsrStageSession::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrStageSession);
        $this->assertEquals('stage1', $usrStageSession->getMstStageId());
        $this->assertEquals(5, $usrStageSession->getPartyNo());
        // daily_continue_ad_countは日跨ぎでリセットされている
        $this->assertEquals(0, $usrStageSession->getDailyContinueAdCount());
        $oprCampaignIds = $oprCampaigns->map(fn($entity) => $entity->getId());
        $this->assertEqualsCanonicalizing($oprCampaignIds, $usrStageSession->getOprCampaignIds());

        // スタミナを消費していない
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getStamina());

        // リセット直後に挑戦できたので、挑戦回数が増えている
        $usrStageEnhance->refresh();
        $this->assertEquals(1, $usrStageEnhance->getResetChallengeCount());
        $this->assertEquals(0, $usrStageEnhance->getResetAdChallengeCount());
        $this->assertEquals(6, $usrStageEnhance->getClearCount());
        $this->assertEquals(999, $usrStageEnhance->getMaxScore()); // スコアはリセットされていない
        $this->assertEquals($now->toDateTimeString(), $usrStageEnhance->getLatestResetAt()); // 現在日時でリセットされている

        $usrUnit = UsrUnit::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_unit_id', 'mst_unit1')
            ->first();
        $this->assertEquals(1, $usrUnit->getBattleCount());
    }

    public function test_start_スタミナブースト指定でステージ開始処理が実行できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $lapCount = 3;

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => QuestType::ENHANCE->value,
            'difficulty' => 'Normal',
            // 現在日時が有効範囲になるように設定
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
            'mst_quest_id' => 'quest1',
            'auto_lap_type' => StageAutoLapType::INITIAL->value,
            'max_auto_lap_count' => 10,
            'cost_stamina' => 10, // スタミナ消費しないが、誤って消費していないことを確認するためにデータ追加
        ])->toEntity();

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'continue_count' => 3,
            'latest_reset_at' => $now->subDays(2), // 2日前に最終リセット日をセット
        ]);

        $oprCampaigns = OprCampaign::factory()->createMany([
            // 通常挑戦可能回数+1
            [
                'id' => 'campaign6',
                'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
                'target_type' => QuestType::ENHANCE->value . 'Quest',
                'effect_value' => 1,
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest1',
                // 現在日時が有効範囲になるように設定
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ])->map(fn($entity) => $entity->toEntity());

        // 最大で3(通常挑戦)+1(キャンペーン効果)+2(広告視聴挑戦)回挑戦できる設定
        MstConfig::factory()->createMany([
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_LIMIT',
                'value' => 3,
            ],
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_AD_LIMIT',
                'value' => 2,
            ],
        ]);

        MstUnit::factory()->create([
            'id' => 'mst_unit1',
        ]);

        // usr
        $usrStageEnhance = UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'clear_count' => 6,
            // 挑戦可能回数上限に達していて、リセットしないと挑戦できない状態にする
            'reset_challenge_count' => 4,
            'reset_ad_challenge_count' => 2,
            'max_score' => 999,
            'latest_reset_at' => $now->subDays(1), // リセットされる状態にする
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 100,
        ]);
        UsrUnit::factory()->create([
            'id' => 'unit1',
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'mst_unit1',
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => 5,
            'usr_unit_id_1' => 'unit1',
        ]);

        // Exercise
        $this->stageStartEnhanceQuestService->start(
            usrUserId: $usrUserId,
            partyNo: 5,
            mstStage: $mstStage,
            mstQuest: $mstQuest,
            isChallengeAd: false, // 通常挑戦
            lapCount: $lapCount,
            now: $now,
        );
        $this->saveAll();

        // Verify
        // セッション開始できている
        $usrStageSession = UsrStageSession::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrStageSession);
        $this->assertEquals('stage1', $usrStageSession->getMstStageId());
        $this->assertEquals(5, $usrStageSession->getPartyNo());
        // daily_continue_ad_countは日跨ぎでリセットされている
        $this->assertEquals(0, $usrStageSession->getDailyContinueAdCount());
        $oprCampaignIds = $oprCampaigns->map(fn($entity) => $entity->getId());
        $this->assertEqualsCanonicalizing($oprCampaignIds, $usrStageSession->getOprCampaignIds());

        // スタミナを消費していない
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getStamina());

        // リセット直後に挑戦できたので、挑戦回数が増えている
        $usrStageEnhance->refresh();
        $this->assertEquals(1, $usrStageEnhance->getResetChallengeCount());
        $this->assertEquals(0, $usrStageEnhance->getResetAdChallengeCount());
        $this->assertEquals(6, $usrStageEnhance->getClearCount());
        $this->assertEquals(999, $usrStageEnhance->getMaxScore()); // スコアはリセットされていない
        $this->assertEquals($now->toDateTimeString(), $usrStageEnhance->getLatestResetAt()); // 現在日時でリセットされている

        $usrUnit = UsrUnit::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_unit_id', 'mst_unit1')
            ->first();
        $this->assertEquals(1, $usrUnit->getBattleCount());
    }

    public static function params_test_validateCanStart_強化クエストの挑戦可能回数を確認()
    {
        return [
            '通常挑戦時 挑戦できる' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => false,
                'isThrowError' => false,
            ],
            '通常挑戦時 挑戦できない 上限ちょうど' => [
                'resetChallengeCount' => 4,
                'resetAdChallengeCount' => 0, // 広告視聴の挑戦ではないので、関係ない
                'isChallengeAd' => false,
                'isThrowError' => true,
            ],
            '通常挑戦時 挑戦できない 上限超え' => [
                'resetChallengeCount' => 10,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => false,
                'isThrowError' => true,
            ],
            '広告視聴挑戦時 挑戦できる' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => true,
                'isThrowError' => false,
            ],
            '広告視聴挑戦時 挑戦できない 上限ちょうど' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 2,
                'isChallengeAd' => true,
                'isThrowError' => true,
            ],
            '広告視聴挑戦時 挑戦できない 上限超え' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 10,
                'isChallengeAd' => true,
                'isThrowError' => true,
            ],
        ];
    }

    #[DataProvider('params_test_validateCanStart_強化クエストの挑戦可能回数を確認')]
    public function test_validateCanStart_強化クエストの挑戦可能回数を確認(
        int $resetChallengeCount,
        int $resetAdChallengeCount,
        bool $isChallengeAd,
        bool $isThrowError,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => QuestType::ENHANCE->value,
            'difficulty' => 'Normal',
            // 現在日時が有効範囲になるように設定
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
            'mst_quest_id' => 'quest1',
        ])->toEntity();

        $oprCampaigns = OprCampaign::factory()->createMany([
            // 通常挑戦可能回数+1
            [
                'id' => 'campaign6',
                'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
                'target_type' => QuestType::ENHANCE->value . 'Quest',
                'effect_value' => 1,
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest1',
                // 現在日時が有効範囲になるように設定
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ])->map(fn($entity) => $entity->toEntity());

        // 最大で3(通常挑戦)+1(キャンペーン効果)+2(広告視聴挑戦)回挑戦できる設定
        MstConfig::factory()->createMany([
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_LIMIT',
                'value' => 3,
            ],
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_AD_LIMIT',
                'value' => 2,
            ],
        ]);

        // usr
        $usrStageEnhance = UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'reset_challenge_count' => $resetChallengeCount,
            'reset_ad_challenge_count' => $resetAdChallengeCount,
            'latest_reset_at' => $now->toDateTimeString(), // リセットされない状態で確認する
        ]);

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::STAGE_CANNOT_START);
        }

        // Exercise
        $this->stageStartEnhanceQuestService->validateCanStart($usrStageEnhance, $isChallengeAd, $oprCampaigns, 1);
        $this->saveAll();

        // Verify
        $this->assertTrue(true);
    }


    public static function params_test_validateCanStart_スタミナブースト指定で強化クエストの挑戦可能回数を確認()
    {
        return [
            '通常挑戦時 挑戦できる' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 5,
            ],
            '通常挑戦時 挑戦できる 上限ちょうど' => [
                'resetChallengeCount' => 4,
                'resetAdChallengeCount' => 0, // 広告視聴の挑戦ではないので、関係ない
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 12,
            ],
            '通常挑戦時 挑戦できない 上限超え' => [
                'resetChallengeCount' => 10,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorCode' => ErrorCode::STAGE_CAN_NOT_AUTO_LAP_CHALLENGE_LIMIT,
                'lapCount' => 7,
            ],
            '広告視聴挑戦時 挑戦できる' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 0,
                'isChallengeAd' => true,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 1,
            ],
            '広告視聴挑戦時 挑戦できない 広告視聴挑戦時はスタミナブースト不可' => [
                'resetChallengeCount' => 0,
                'resetAdChallengeCount' => 2,
                'isChallengeAd' => true,
                'isThrowError' => true,
                'errorCode' => ErrorCode::STAGE_CANNOT_START,
                'lapCount' => 5,
            ],
        ];
    }

    #[DataProvider('params_test_validateCanStart_スタミナブースト指定で強化クエストの挑戦可能回数を確認')]
    public function test_validateCanStart_スタミナブースト指定で強化クエストの挑戦可能回数を確認(
        int $resetChallengeCount,
        int $resetAdChallengeCount,
        bool $isChallengeAd,
        bool $isThrowError,
        ?int $errorCode,
        int $lapCount,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => QuestType::ENHANCE->value,
            'difficulty' => 'Normal',
            // 現在日時が有効範囲になるように設定
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
            'auto_lap_type' => StageAutoLapType::INITIAL->value,
            'max_auto_lap_count' => 10,
            'mst_quest_id' => 'quest1',
        ])->toEntity();

        $oprCampaigns = OprCampaign::factory()->createMany([
            // 通常挑戦可能回数+1
            [
                'id' => 'campaign6',
                'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
                'target_type' => QuestType::ENHANCE->value . 'Quest',
                'effect_value' => 1,
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest1',
                // 現在日時が有効範囲になるように設定
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ])->map(fn($entity) => $entity->toEntity());

        // 最大で15(通常挑戦)+1(キャンペーン効果)+2(広告視聴挑戦)回挑戦できる設定
        MstConfig::factory()->createMany([
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_LIMIT',
                'value' => 15,
            ],
            [
                'key' => 'ENHANCE_QUEST_CHALLENGE_AD_LIMIT',
                'value' => 2,
            ],
        ]);

        // usr
        $usrStageEnhance = UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'reset_challenge_count' => $resetChallengeCount,
            'reset_ad_challenge_count' => $resetAdChallengeCount,
            'latest_reset_at' => $now->toDateTimeString(), // リセットされない状態で確認する
        ]);

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->stageStartEnhanceQuestService->validateCanStart($usrStageEnhance, $isChallengeAd, $oprCampaigns, $lapCount);
        $this->saveAll();

        // Verify
        $this->assertTrue(true);
    }
}
