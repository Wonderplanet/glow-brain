<?php

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignTargetIdType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageStartEventQuestService;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StageStartEventQuestServiceTest extends TestCase
{
    private StageStartEventQuestService $stageStartEventQuestService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageStartEventQuestService = app(StageStartEventQuestService::class);
    }

    #[DataProvider('params_test_startSession_イベントステージを開始したステータスに更新できる')]
    public function test_startSession_イベントステージを開始したステータスに更新できる(int $lapCount)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
        ])->toEntity();
        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
        ]);
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'Event',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $oprCampaigns = OprCampaign::factory()->createMany([
            [
                'id' => 'campaign1',
                'campaign_type' => CampaignType::EXP->value,
                'target_type' => 'NormalQuest',
                'effect_value' => 150,
            ],
            [
                'id' => 'campaign2',
                'campaign_type' => CampaignType::COIN_DROP->value,
                'target_type' => 'NormalQuest',
                'effect_value' => 20,
            ],
        ])->map(fn($campaign) => $campaign->toEntity());

        // Exercise
        $this->stageStartEventQuestService->startSession(
            $usrUserId,
            $now,
            $mstStage,
            $mstQuest,
            5,
            $oprCampaigns,
            false,
            $lapCount
        );
        $this->saveAll();

        // Verify
        $usrStageSession = UsrStageSession::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrStageSession);
        $this->assertEquals('stage1', $usrStageSession->getMstStageId());
        $this->assertEquals(5, $usrStageSession->getPartyNo());
        $oprCampaignIds = $oprCampaigns->map(fn($entity) => $entity->getId());
        $this->assertEquals($oprCampaignIds, $usrStageSession->getOprCampaignIds());
    }

    public static function params_test_startSession_イベントステージを開始したステータスに更新できる()
    {
        return [
            'スタミナブーストなし' => ['lapCount' => 1],
            'スタミナブーストあり' => ['lapCount' => 3],
        ];
    }

    #[DataProvider('params_test_validateCanStart_イベントステージのクリア可能回数を確認')]
    public function test_validateCanStart_イベントステージのクリア可能回数を確認(
        int $clearCount,
        int $adChallengeCount,
        bool $isChallengeAd,
        bool $isThrowError,
        int $lapCount,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
        ])->toEntity();
        $usrStageEvent = UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'reset_clear_count' => $clearCount,
            'reset_ad_challenge_count' => $adChallengeCount,
        ]);
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'Event',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $mstStageEventSetting = MstStageEventSetting::factory()->create([
            'mst_stage_id' => 'stage1',
            'clearable_count' => 5,
            'ad_challenge_count' => 3,
        ])->toEntity();

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::STAGE_CANNOT_START);
        }

        // Exercise
        $this->stageStartEventQuestService->validateCanStart(
            $mstStageEventSetting,
            $usrStageEvent,
            $isChallengeAd,
            collect(),
            $lapCount,
        );
        $this->saveAll();

        // Verify
        $this->assertTrue(true);
    }

    public static function params_test_validateCanStart_イベントステージのクリア可能回数を確認()
    {
        return [
            'クリア可能回数あり' => ['clearCount' => 4, 'adChallengeCount' => 2, 'isChallengeAd' => false, 'isThrowError' => false, 'lapCount' => 1],
            'クリア可能回数オーバー' => ['clearCount' => 5, 'adChallengeCount' => 2, 'isChallengeAd' => false, 'isThrowError' => true, 'lapCount' => 1],
            '広告挑戦回数あり' => ['clearCount' => 5, 'adChallengeCount' => 2, 'isChallengeAd' => true, 'isThrowError' => false, 'lapCount' => 1],
            '広告挑戦回数オーバー' => ['clearCount' => 5, 'adChallengeCount' => 3, 'isChallengeAd' => true, 'isThrowError' => true, 'lapCount' => 1],
            'クリア可能回数あり スタミナブーストあり' => ['clearCount' => 2, 'adChallengeCount' => 2, 'isChallengeAd' => false, 'isThrowError' => false, 'lapCount' => 3],
            'クリア可能回数オーバー スタミナブーストあり' => ['clearCount' => 5, 'adChallengeCount' => 2, 'isChallengeAd' => false, 'isThrowError' => true, 'lapCount' => 3],
        ];
    }

    /**
     * @return array
     */
    public static function params_ルールに適した編成(): array
    {
        return [
            'キャラ編成数制限(5人以下)' => [
                'mst_stage_id_1',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_n',
                    'usr_unit_id_2' => 'mst_unit_rarity_r',
                    'usr_unit_id_3' => 'mst_unit_rarity_sr',
                    'usr_unit_id_4' => 'mst_unit_series_1',
                    'usr_unit_id_5' => 'mst_unit_series_2',
                ],
            ],
            'レアリティ制限(N)' => [
                'mst_stage_id_2',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_n',
                ],
            ],
            '作品制限(mst_series_1)' => [
                'mst_stage_id_3',
                [
                    'usr_unit_id_1' => 'mst_unit_series_1',
                ],
            ],
            '射程制限(Short)' => [
                'mst_stage_id_4',
                [
                    'usr_unit_id_1' => 'mst_unit_attack_range_short',
                ],
            ],
            'ロール制限(Attack)' => [
                'mst_stage_id_5',
                [
                    'usr_unit_id_1' => 'mst_unit_role_attack',
                ],
            ],
            'リーダーP上限制限(100以下)' => [
                'mst_stage_id_6',
                [
                    'usr_unit_id_1' => 'mst_unit_summon_cost_99',
                    'usr_unit_id_2' => 'mst_unit_summon_cost_100',
                ],
            ],
            'リーダーP下限制限(100以上)' => [
                'mst_stage_id_7',
                [
                    'usr_unit_id_1' => 'mst_unit_summon_cost_100',
                    'usr_unit_id_2' => 'mst_unit_summon_cost_101',
                ],
            ],
            'レアリティ制限(N, R)' => [
                'mst_stage_id_8',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_n',
                    'usr_unit_id_2' => 'mst_unit_rarity_r',
                ],
            ],
            '作品制限(mst_series_1, mst_series_2)' => [
                'mst_stage_id_9',
                [
                    'usr_unit_id_1' => 'mst_unit_series_1',
                    'usr_unit_id_2' => 'mst_unit_series_2',
                ],
            ],
            '射程制限(Short, Middle)' => [
                'mst_stage_id_10',
                [
                    'usr_unit_id_1' => 'mst_unit_attack_range_short',
                    'usr_unit_id_2' => 'mst_unit_attack_range_middle',
                ],
            ],
            'ロール制限(Attack, Balance)' => [
                'mst_stage_id_11',
                [
                    'usr_unit_id_1' => 'mst_unit_role_attack',
                    'usr_unit_id_2' => 'mst_unit_role_balance',
                ],
            ],
            'レアリティ制限(N, R)、作品制限(mst_series_1)' => [
                'mst_stage_id_12',
                [
                    'usr_unit_id_1' => 'mst_unit_mst_series_1_rarity_n',
                    'usr_unit_id_2' => 'mst_unit_mst_series_1_rarity_r',
                ],
            ],
        ];
    }

    #[DataProvider('params_ルールに適した編成')]
    public function test_start_ルールに適した編成(string $mstStageId, array $addUsrParty)
    {
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $this->createLimitedStageData($usrUser->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 100,
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'party_no' => 1,
            ...$addUsrParty
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => 'mst_quest_id'
        ])->toEntity();
        $mstQuest = MstQuest::factory()->create([
            'id' => 'mst_quest_id',
            'quest_type' => 'Event',
        ])->toEntity();

        $this->stageStartEventQuestService->start(
            $usrUser->getId(),
            1,
            $mstStage,
            $mstQuest,
            false,
            1,
            $now,
        );
        $this->saveAll();

        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $usrUser->getId())
            ->whereIn('mst_unit_id', array_values($addUsrParty))
            ->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public static function params_test_start_スタミナブースト指定で開始(): array
    {
        return [
            '通常挑戦時 挑戦できる' => [
                'stamina' => 100,
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 5,
            ],
            '通常挑戦時 挑戦できる スタミナ上限ちょうど' => [
                'stamina' => 50,
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 5,
            ],
            '通常挑戦時 挑戦できない スタミナ上限超え' => [
                'stamina' => 49,
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorCode' => ErrorCode::LACK_OF_RESOURCES,
                'lapCount' => 5,
            ],
            '通常挑戦時 挑戦できる クリア上限ちょうど' => [
                'stamina' => 150,
                'isChallengeAd' => false,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 10,
            ],
            '通常挑戦時 挑戦できない クリア上限超え' => [
                'stamina' => 150,
                'isChallengeAd' => false,
                'isThrowError' => true,
                'errorCode' => ErrorCode::STAGE_CAN_NOT_AUTO_LAP_CHALLENGE_LIMIT,
                'lapCount' => 11,
            ],
            '広告視聴挑戦時 挑戦できる' => [
                'stamina' => 100,
                'isChallengeAd' => true,
                'isThrowError' => false,
                'errorCode' => null,
                'lapCount' => 1,
            ],
            '広告視聴挑戦時 挑戦できない 広告視聴挑戦時はスタミナブースト不可' => [
                'stamina' => 100,
                'isChallengeAd' => true,
                'isThrowError' => true,
                'errorCode' => ErrorCode::STAGE_CAN_NOT_AUTO_LAP,
                'lapCount' => 5,
            ],
        ];
    }
    #[DataProvider('params_test_start_スタミナブースト指定で開始')]
    public function test_start_スタミナブースト指定で開始(
        int $stamina,
        bool $isChallengeAd,
        bool $isThrowError,
        ?int $errorCode,
        int $lapCount,
    ) {
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $mstStageId = 'mst_stage_id_lap';
        $addUsrParty = [
            'usr_unit_id_1' => 'mst_unit_rarity_n',
            'usr_unit_id_2' => 'mst_unit_rarity_r',
            'usr_unit_id_3' => 'mst_unit_rarity_sr',
            'usr_unit_id_4' => 'mst_unit_series_1',
            'usr_unit_id_5' => 'mst_unit_series_2',
        ];
        $this->createLimitedStageData($usrUser->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => $stamina,
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'party_no' => 1,
            ...$addUsrParty
        ]);
        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => 'mst_quest_id',
            'cost_stamina' => 10,
            'auto_lap_type' => StageAutoLapType::INITIAL->value,
            'max_auto_lap_count' => 15,
        ])->toEntity();
        $mstQuest = MstQuest::factory()->create([
            'id' => 'mst_quest_id',
            'quest_type' => 'Event',
        ])->toEntity();
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => $mstStageId,
            'clearable_count' => 5,
            'ad_challenge_count' => 5,
        ]);
        MstInGameSpecialRule::factory()->create([
            // 1つのルール
            'content_type' => InGameContentType::STAGE,
            'target_id' => $mstStageId,
            'rule_type' => 'PartyUnitNum',
            'rule_value' => '5'
        ]);
        OprCampaign::factory()->create([
            'id' => 'campaign1',
            'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
            'target_type' => QuestType::EVENT->value . 'Quest',
            'target_id_type' => CampaignTargetIdType::QUEST->value,
            'target_id' => 'mst_quest_id',
            'effect_value' => 5,
        ]);

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        $this->stageStartEventQuestService->start(
            $usrUser->getId(),
            1,
            $mstStage,
            $mstQuest,
            $isChallengeAd,
            $lapCount,
            $now,
        );
        $this->saveAll();

        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $usrUser->getId())
            ->whereIn('mst_unit_id', array_values($addUsrParty))
            ->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    /**
     * @return array
     */
    public static function params_ルールに適してない編成(): array
    {
        return [
            'キャラ編成数制限(5人以下)_6人編成' => [
                'mst_stage_id_1',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_n',
                    'usr_unit_id_2' => 'mst_unit_rarity_r',
                    'usr_unit_id_3' => 'mst_unit_rarity_sr',
                    'usr_unit_id_4' => 'mst_unit_series_1',
                    'usr_unit_id_5' => 'mst_unit_series_2',
                    'usr_unit_id_6' => 'mst_unit_series_3',
                ],
            ],
            'レアリティ制限(N)_Rを編成' => [
                'mst_stage_id_2',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_r',
                ],
            ],
            'レアリティ制限(N)_SRを編成' => [
                'mst_stage_id_2',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_sr',
                ],
            ],
            '作品制限(mst_series_1)_mst_series_2を編成' => [
                'mst_stage_id_3',
                [
                    'usr_unit_id_1' => 'mst_unit_series_2',
                ],
            ],
            '作品制限(mst_series_1)_mst_series_3を編成' => [
                'mst_stage_id_3',
                [
                    'usr_unit_id_1' => 'mst_unit_series_3',
                ],
            ],
            '射程制限(Short)_Middleを編成' => [
                'mst_stage_id_4',
                [
                    'usr_unit_id_1' => 'mst_unit_attack_range_middle',
                ],
            ],
            '射程制限(Short)_Longを編成' => [
                'mst_stage_id_4',
                [
                    'usr_unit_id_1' => 'mst_unit_attack_range_long',
                ],
            ],
            'ロール制限(Attack)_Balanceを編成' => [
                'mst_stage_id_5',
                [
                    'usr_unit_id_1' => 'mst_unit_role_balance',
                ],
            ],
            'ロール制限(Attack)_Defenseを編成' => [
                'mst_stage_id_5',
                [
                    'usr_unit_id_1' => 'mst_unit_role_defense',
                ],
            ],
            'リーダーP上限制限(100以下)_101を編成' => [
                'mst_stage_id_6',
                [
                    'usr_unit_id_1' => 'mst_unit_summon_cost_101',
                ],
            ],
            'リーダーP下限制限(100以上)_99を編成' => [
                'mst_stage_id_7',
                [
                    'usr_unit_id_1' => 'mst_unit_summon_cost_99',
                ],
            ],
            'レアリティ制限(N, R)_SRを編成' => [
                'mst_stage_id_8',
                [
                    'usr_unit_id_1' => 'mst_unit_rarity_sr',
                ],
            ],
            '作品制限(mst_series_1, mst_series_2)_mst_series_3を編成' => [
                'mst_stage_id_9',
                [
                    'usr_unit_id_1' => 'mst_unit_series_3',
                ],
            ],
            '射程制限(Short, Middle)_Longを編成' => [
                'mst_stage_id_10',
                [
                    'usr_unit_id_1' => 'mst_unit_attack_range_long',
                ],
            ],
            'ロール制限(Attack, Balance)_Defenseを編成' => [
                'mst_stage_id_11',
                [
                    'usr_unit_id_1' => 'mst_unit_role_defense',
                ],
            ],
            'レアリティ制限(N, R)、作品制限(mst_series_1)_SRを編成' => [
                'mst_stage_id_12',
                [
                    'usr_unit_id_1' => 'mst_unit_mst_series_1_rarity_sr',
                ],
            ],
            'レアリティ制限(N, R)、作品制限(mst_series_1)_mst_series_2を編成' => [
                'mst_stage_id_12',
                [
                    'usr_unit_id_1' => 'mst_unit_mst_series_2_rarity_n',
                ],
            ],
            'ルールに適しているキャラと合わせて編成' => [
                'mst_stage_id_12',
                [
                    'usr_unit_id_1' => 'mst_unit_mst_series_1_rarity_n',
                    'usr_unit_id_2' => 'mst_unit_mst_series_1_rarity_r',
                    'usr_unit_id_3' => 'mst_unit_mst_series_2_rarity_n',
                ],
            ],
        ];
    }

    #[DataProvider('params_ルールに適してない編成')]
    public function test_start_ルールに適してない編成(string $mstStageId, array $addUsrParty)
    {
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $this->createLimitedStageData($usrUser->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 100,
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'party_no' => 1,
            ...$addUsrParty
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => 'mst_quest_id'
        ])->toEntity();
        $mstQuest = MstQuest::factory()->create([
            'id' => 'mst_quest_id',
            'quest_type' => 'Event',
        ])->toEntity();

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_EVENT_PARTY_VIOLATION_RULE);
        $this->stageStartEventQuestService->start(
            $usrUser->getId(),
            1,
            $mstStage,
            $mstQuest,
            false,
            1,
            $now,
        );
        $this->saveAll();

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(0, $usrUnit->getBattleCount());
        }
    }

    private function createLimitedStageData(string $usrUserId)
    {
        MstUnit::factory()->createMany([
            ['id' => 'mst_unit_rarity_n', 'rarity' => 'N'],
            ['id' => 'mst_unit_rarity_r', 'rarity' => 'R'],
            ['id' => 'mst_unit_rarity_sr', 'rarity' => 'SR'],
            ['id' => 'mst_unit_series_1', 'mst_series_id' => 'mst_series_1'],
            ['id' => 'mst_unit_series_2', 'mst_series_id' => 'mst_series_2'],
            ['id' => 'mst_unit_series_3', 'mst_series_id' => 'mst_series_3'],
            ['id' => 'mst_unit_attack_range_short', 'attack_range_type' => 'Short'],
            ['id' => 'mst_unit_attack_range_middle', 'attack_range_type' => 'Middle'],
            ['id' => 'mst_unit_attack_range_long', 'attack_range_type' => 'Long'],
            ['id' => 'mst_unit_role_attack', 'role_type' => 'Attack'],
            ['id' => 'mst_unit_role_balance', 'role_type' => 'Balance'],
            ['id' => 'mst_unit_role_defense', 'role_type' => 'Defense'],
            ['id' => 'mst_unit_summon_cost_99', 'summon_cost' => '99'],
            ['id' => 'mst_unit_summon_cost_100', 'summon_cost' => '100'],
            ['id' => 'mst_unit_summon_cost_101', 'summon_cost' => '101'],
            ['id' => 'mst_unit_mst_series_1_rarity_n', 'mst_series_id' => 'mst_series_1', 'rarity' => 'N'],
            ['id' => 'mst_unit_mst_series_1_rarity_r', 'mst_series_id' => 'mst_series_1', 'rarity' => 'R'],
            ['id' => 'mst_unit_mst_series_1_rarity_sr', 'mst_series_id' => 'mst_series_1', 'rarity' => 'SR'],
            ['id' => 'mst_unit_mst_series_2_rarity_n', 'mst_series_id' => 'mst_series_2', 'rarity' => 'N'],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'mst_unit_rarity_n', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_rarity_n'],
            ['id' => 'mst_unit_rarity_r', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_rarity_r'],
            ['id' => 'mst_unit_rarity_sr', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_rarity_sr'],
            ['id' => 'mst_unit_series_1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_series_1'],
            ['id' => 'mst_unit_series_2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_series_2'],
            ['id' => 'mst_unit_series_3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_series_3'],
            ['id' => 'mst_unit_attack_range_short', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_attack_range_short'],
            ['id' => 'mst_unit_attack_range_middle', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_attack_range_middle'],
            ['id' => 'mst_unit_attack_range_long', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_attack_range_long'],
            ['id' => 'mst_unit_role_attack', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_role_attack'],
            ['id' => 'mst_unit_role_balance', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_role_balance'],
            ['id' => 'mst_unit_role_defense', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_role_defense'],
            ['id' => 'mst_unit_summon_cost_99', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_summon_cost_99'],
            ['id' => 'mst_unit_summon_cost_100', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_summon_cost_100'],
            ['id' => 'mst_unit_summon_cost_101', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_summon_cost_101'],
            ['id' => 'mst_unit_mst_series_1_rarity_n', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_mst_series_1_rarity_n'],
            ['id' => 'mst_unit_mst_series_1_rarity_r', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_mst_series_1_rarity_r'],
            ['id' => 'mst_unit_mst_series_1_rarity_sr', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_mst_series_1_rarity_sr'],
            ['id' => 'mst_unit_mst_series_2_rarity_n', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'mst_unit_mst_series_2_rarity_n'],
        ]);
        MstStageEventSetting::factory()->createMany([
            ['mst_stage_id' => 'mst_stage_id_1', ],
            ['mst_stage_id' => 'mst_stage_id_2', ],
            ['mst_stage_id' => 'mst_stage_id_3', ],
            ['mst_stage_id' => 'mst_stage_id_4', ],
            ['mst_stage_id' => 'mst_stage_id_5', ],
            ['mst_stage_id' => 'mst_stage_id_6', ],
            ['mst_stage_id' => 'mst_stage_id_7', ],
            ['mst_stage_id' => 'mst_stage_id_8', ],
            ['mst_stage_id' => 'mst_stage_id_9', ],
            ['mst_stage_id' => 'mst_stage_id_10',],
            ['mst_stage_id' => 'mst_stage_id_11',],
            ['mst_stage_id' => 'mst_stage_id_12',],
        ]);
        UsrStageEvent::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_1'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_2'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_3'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_4'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_5'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_6'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_7'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_8'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_9'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_10'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_11'],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'mst_stage_id_12'],
        ]);
        MstInGameSpecialRule::factory()->createMany([
            // 1つのルール
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_1', 'rule_type' => 'PartyUnitNum', 'rule_value' => '5'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_2', 'rule_type' => 'PartyRarity', 'rule_value' => 'N'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_3', 'rule_type' => 'PartySeries', 'rule_value' => 'mst_series_1'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_4', 'rule_type' => 'PartyAttackRangeType', 'rule_value' => 'Short'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_5', 'rule_type' => 'PartyRoleType', 'rule_value' => 'Attack'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_6', 'rule_type' => 'PartySummonCostUpperEqual', 'rule_value' => '100'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_7', 'rule_type' => 'PartySummonCostLowerEqual', 'rule_value' => '100'],
            // 同rule_typeのルール(例：レアリティがNかRなど)
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_8', 'rule_type' => 'PartyRarity', 'rule_value' => 'N'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_8', 'rule_type' => 'PartyRarity', 'rule_value' => 'R'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_9', 'rule_type' => 'PartySeries', 'rule_value' => 'mst_series_1'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_9', 'rule_type' => 'PartySeries', 'rule_value' => 'mst_series_2'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_10', 'rule_type' => 'PartyAttackRangeType', 'rule_value' => 'Short'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_10', 'rule_type' => 'PartyAttackRangeType', 'rule_value' => 'Middle'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_11', 'rule_type' => 'PartyRoleType', 'rule_value' => 'Attack'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_11', 'rule_type' => 'PartyRoleType', 'rule_value' => 'Balance'],
            // 複合ルール
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_12', 'rule_type' => 'PartyRarity', 'rule_value' => 'N'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_12', 'rule_type' => 'PartyRarity', 'rule_value' => 'R'],
            ['content_type' => InGameContentType::STAGE, 'target_id' => 'mst_stage_id_12', 'rule_type' => 'PartySeries', 'rule_value' => 'mst_series_1'],
        ]);
    }
}
