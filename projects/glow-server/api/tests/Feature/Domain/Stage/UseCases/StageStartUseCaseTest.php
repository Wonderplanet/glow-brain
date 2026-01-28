<?php

namespace Tests\Feature\Domain\Stage;

use App\Domain\Campaign\Enums\CampaignTargetIdType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageStartUseCase;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;


class StageStartUseCaseTest extends TestCase
{
    private StageStartUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(StageStartUseCase::class);
    }

    public static function params_test_exec_ステージ開始時指定したメソッドが呼ばれる()
    {
        return [
            '通常挑戦' => [
                'mstStageId' => '10',
                'lapCount' => 1,
            ],
            'クリア条件スタミナブースト挑戦' => [
                'mstStageId' => '101',
                'lapCount' => 3,
            ],
            '無条件スタミナブースト挑戦' => [
                'mstStageId' => '102',
                'lapCount' => 3,
            ],
        ];
    }

    #[DataProvider('params_test_exec_ステージ開始時指定したメソッドが呼ばれる')]
    public function test_exec_ステージ開始時指定したメソッドが呼ばれる(
        string $mstStageId,
        int $lapCount
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);
        $partyNo = 3;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 20,
        ]);
        $this->createDiamond($usrUserId);

        $this->createTestData($usrUserId);
        $beforeStamina = $usrUserParameter->getStamina();
        $this->createUsrParty($usrUserId, $partyNo);

        // Exercise
        $results = $this->useCase->exec($currentUser, $mstStageId, $partyNo, false, $lapCount);
        $this->saveAll();

        // Verify
        // ResultData確認
        $usrUserParameter->refresh();
        $usrCurrencySummary = $this->getDiamond($usrUserId);
        $this->assertEquals($usrUserParameter->getCoin(), $results->usrUserParameter->getCoin());
        $this->assertEquals($usrUserParameter->getExp(), $results->usrUserParameter->getExp());
        $this->assertEquals($usrUserParameter->getStamina(), $results->usrUserParameter->getStamina());
        $this->assertEquals($usrUserParameter->getLevel(), $results->usrUserParameter->getLevel());

        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrStageSession->getContinueCount(), $results->usrStageStatus->getContinueCount());
        $this->assertEquals($usrStageSession->getMstStageId(), $results->usrStageStatus->getTargetMstId());
        $this->assertEquals($usrStageSession->getPartyNo(), $results->usrStageStatus->getPartyNo());
        $this->assertEquals(InGameContentType::STAGE->value, $results->usrStageStatus->getInGameContentType());

        // DB確認
        $this->assertEquals($beforeStamina - 5, $usrUserParameter->getStamina());
        $this->assertEquals(0, $usrStageSession->getContinueCount());
        $this->assertEquals($mstStageId, $usrStageSession->getMstStageId());
        $this->assertEquals($partyNo, $usrStageSession->getPartyNo());

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public function test_exec_イベントステージ開始時指定したメソッドが呼ばれる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        $mstStageId = "12";
        $partyNo = 3;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUserId);

        $this->createTestData($usrUserId);
        $beforeStamina = $usrUserParameter->getStamina();
        $this->createUsrParty($usrUserId, $partyNo);

        // Exercise
        $results = $this->useCase->exec($currentUser, $mstStageId, $partyNo, false, 1);
        $this->saveAll();

        // Verify
        // ResultData確認
        $usrUserParameter->refresh();
        $usrCurrencySummary = $this->getDiamond($usrUserId);
        $this->assertEquals($usrUserParameter->getCoin(), $results->usrUserParameter->getCoin());
        $this->assertEquals($usrUserParameter->getExp(), $results->usrUserParameter->getExp());
        $this->assertEquals($usrUserParameter->getStamina(), $results->usrUserParameter->getStamina());
        $this->assertEquals($usrUserParameter->getLevel(), $results->usrUserParameter->getLevel());

        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrStageSession->getContinueCount(), $results->usrStageStatus->getContinueCount());
        $this->assertEquals($usrStageSession->getMstStageId(), $results->usrStageStatus->getTargetMstId());
        $this->assertEquals($usrStageSession->getPartyNo(), $results->usrStageStatus->getPartyNo());
        $this->assertEquals(InGameContentType::STAGE->value, $results->usrStageStatus->getInGameContentType());

        // DB確認
        $this->assertEquals($beforeStamina - 5, $usrUserParameter->getStamina());
        $this->assertEquals(0, $usrStageSession->getContinueCount());
        $this->assertEquals($mstStageId, $usrStageSession->getMstStageId());
        $this->assertEquals($partyNo, $usrStageSession->getPartyNo());

        $usrStageEvent = UsrStageEvent::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_stage_id', $mstStageId)
            ->first();
        $this->assertNotNull($usrStageEvent);
        $this->assertEquals($now->toDateTimeString(), $usrStageEvent->getLastChallengedAt());

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public static function params_testExec_スタミナキャンペーンが適用される場合(): array
    {
        return [
            'キャンペーンで1/2分スタミナを消費する' => [
                'effectValue' => 50,
                'costStamina' => 9,
                'beforeStamina' => 10,
                'expectedStamina' => 6, // 10 - floor(9 * 0.5)
            ],
            'キャンペーン倍率をかけると1を下回る場合1が最低保証となる' => [
                'effectValue' => 10,
                'costStamina' => 5,
                'beforeStamina' => 10,
                'expectedStamina' => 9,
            ],
        ];
    }

    /**
     * @dataProvider params_testExec_スタミナキャンペーンが適用される場合
     */
    public function testExec_スタミナキャンペーンが適用される場合(
        int $effectValue,
        int $costStamina,
        int $beforeStamina,
        int $expectedStamina
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);
        $partyNo = 1;

        $mstQuest = MstQuest::factory()->create([
            'quest_type' => 'Normal'
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'mst_quest_id' => $mstQuest->getId(),
            'cost_stamina' => $costStamina,
        ])->toEntity();
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 1000],
        ]);
        OprCampaign::factory()->create([
            'campaign_type' => CampaignType::STAMINA->value,
            'target_type' => 'NormalQuest',
            'target_id_type' => CampaignTargetIdType::QUEST->value,
            'target_id' => $mstQuest->getId(),
            'effect_value' => $effectValue,
        ]);

        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStage->getId(),
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => $beforeStamina,
        ]);
        $this->createDiamond($usrUserId);
        $this->createUsrParty($usrUserId, $partyNo);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstStage->getId(), $partyNo, false, 1);
        $this->saveAll();

        // Verify
        /** @var UsrUserParameter $usrUserParameter */
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());
        $this->assertEquals($usrUserParameter->getStamina(), $result->usrUserParameter->getStamina());

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public function testExec_挑戦回数キャンペーンが適用される場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);
        $partyNo = 1;

        $mstQuest = MstQuest::factory()->create([
            'quest_type' => 'Event'
        ])->toEntity();
        $mstStage = MstStage::factory()->create([
            'mst_quest_id' => $mstQuest->getId(),
            'cost_stamina' => 1,
            'prev_mst_stage_id' => null,
        ])->toEntity();
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => $mstStage->getId(),
            'reset_type' => null,
            'clearable_count' => 1,
            'ad_challenge_count' => 0,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 1000],
        ]);
        OprCampaign::factory()->create([
            'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
            'target_type' => 'EventQuest',
            'target_id_type' => CampaignTargetIdType::QUEST->value,
            'target_id' => $mstQuest->getId(),
            'effect_value' => 1,
        ]);

        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStage->getId(),
            'clear_count' => 1,
            'reset_clear_count' => 1,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUserId);
        $this->createUsrParty($usrUserId, $partyNo);

        // Exercise
        // 挑戦回数上限でキャンペーンで+1になっているときにエラーにならないこと
        $this->useCase->exec($currentUser, $mstStage->getId(), $partyNo, false, 1);
        $this->saveAll();

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }

        // Verify
        $this->assertTrue(true);
    }

    #[DataProvider('params_exec_広告視聴時スタミナが消費されない')]
    public function test_exec_広告視聴時スタミナが消費されない(string $mstStageId): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);
        $partyNo = 3;

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 2,
            'stamina' => 20,
        ]);
        $this->createDiamond($usrUserId);

        $this->createTestData($usrUserId);
        $beforeStamina = $usrUserParameter->getStamina();
        $this->createUsrParty($usrUserId, $partyNo);

        // Exercise
        // 広告視聴時（isChallengeAd=true）はスタミナ消費なし
        $results = $this->useCase->exec($currentUser, $mstStageId, $partyNo, true, 1);

        // Verify
        // ResultData確認
        $usrUserParameter->refresh();
        $this->assertEquals($usrUserParameter->getCoin(), $results->usrUserParameter->getCoin());
        $this->assertEquals($usrUserParameter->getExp(), $results->usrUserParameter->getExp());
        $this->assertEquals($usrUserParameter->getStamina(), $results->usrUserParameter->getStamina());
        $this->assertEquals($usrUserParameter->getLevel(), $results->usrUserParameter->getLevel());

        // DB確認 - 広告視聴時はスタミナが消費されないことを検証
        $this->assertEquals($beforeStamina, $usrUserParameter->getStamina(), '広告視聴時はスタミナが消費されないこと');

        // ステージセッションが正常に作成されていることを確認
        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstStageId, $usrStageSession->getMstStageId());
        $this->assertEquals($partyNo, $usrStageSession->getPartyNo());
        $this->assertTrue($usrStageSession->isChallengeAd(), '広告視聴フラグが設定されていること');
    }

    /**
     * @return array<string, array{mstStageId: string, description: string}>
     */
    public static function params_exec_広告視聴時スタミナが消費されない(): array
    {
        return [
            '通常クエスト' => [
                'mstStageId' => '10',
                'description' => '通常ステージ',
            ],
            'イベントクエスト' => [
                'mstStageId' => '12',
                'description' => 'イベントステージ',
            ],
        ];
    }

    private function createTestData(string $usrUserId): void
    {
        UsrStage::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '10',
                'clear_count' => 0,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '101',
                'clear_count' => 1,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '102',
                'clear_count' => 0,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '11',
                'clear_count' => 0,
            ],
        ]);

        UsrStageEvent::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '12',
                'clear_count' => 0,
                'reset_clear_count' => 0,
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '13',
                'clear_count' => 0,
                'reset_clear_count' => 0,
            ],
        ]);

        MstQuest::factory()->createMany([
            [
                'id' => '10',
                'quest_type' => 'Normal',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2037-01-01 00:00:00',
            ],
            [
                'id' => '11',
                'quest_type' => 'Normal',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2024-01-01 00:00:00',
            ],
            [
                'id' => '12',
                'quest_type' => 'Event',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2037-01-01 00:00:00',
            ],
            [
                'id' => '13',
                'quest_type' => 'Event',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2024-01-01 00:00:00',
            ],
        ]);
        MstStage::factory()->createMany([
            [
                'id' => '10',
                'mst_quest_id' => '10',
                'cost_stamina' => 5,
            ],
            [
                'id' => '101',
                'mst_quest_id' => '10',
                'cost_stamina' => 5,
                'auto_lap_type' => StageAutoLapType::AFTER_CLEAR,
                'max_auto_lap_count' => 5,
            ],
            [
                'id' => '102',
                'mst_quest_id' => '10',
                'cost_stamina' => 5,
                'auto_lap_type' => StageAutoLapType::INITIAL,
                'max_auto_lap_count' => 5,
            ],
            [
                'id' => '11',
                'mst_quest_id' => '11',
                'cost_stamina' => 5,
            ],
            [
                'id' => '12',
                'mst_quest_id' => '12',
                'cost_stamina' => 5,
            ],
            [
                'id' => '13',
                'mst_quest_id' => '13',
                'cost_stamina' => 5,
            ],
        ]);
        MstStageEventSetting::factory()->createMany([
            [
                'id' => '1',
                'mst_stage_id' => '12',
            ],
            [
                'id' => '2',
                'mst_stage_id' => '13',
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);
    }

    public function createUsrParty(string $usrUserId, int $partyNo): void
    {
        MstUnit::factory()->createMany([
            ['id' => 'mst_unit1'],
            ['id' => 'mst_unit2'],
            ['id' => 'mst_unit3'],
            ['id' => 'mst_unit4'],
            ['id' => 'mst_unit5'],
        ]);
        UsrUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'usr_user_id' => $usrUserId,
                'mst_unit_id' => 'mst_unit1',
            ],
            [
                'id' => 'unit2',
                'usr_user_id' => $usrUserId,
                'mst_unit_id' => 'mst_unit2',
            ],
            [
                'id' => 'unit3',
                'usr_user_id' => $usrUserId,
                'mst_unit_id' => 'mst_unit3',
            ],
            [
                'id' => 'unit4',
                'usr_user_id' => $usrUserId,
                'mst_unit_id' => 'mst_unit4',
            ],
            [
                'id' => 'unit5',
                'usr_user_id' => $usrUserId,
                'mst_unit_id' => 'mst_unit5',
            ],
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => $partyNo,
            'usr_unit_id_1' => 'unit1',
            'usr_unit_id_2' => 'unit2',
            'usr_unit_id_3' => 'unit3',
            'usr_unit_id_4' => 'unit4',
            'usr_unit_id_5' => 'unit5',
        ]);
    }
}
