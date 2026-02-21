<?php

namespace Tests\Feature\Domain\DebugCommand\UseCases;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Constants\AppCurrencyConstants;
use App\Domain\DebugCommand\UseCases\DebugCommandExecUseCase;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Item\Constants\ItemConstant;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Outpost\Enums\OutpostEnhancementType;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class DebugCommandExecUseCaseTest extends TestCase
{

    public function test_GrantStaminaMaxUseCase_スタミナ回復のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 0,
        ]);
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $usrUserParameter = UsrUserParameter::query()
            ->where('usr_user_id', $currentUser->id)
            ->first();
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'GrantStaminaMax', $platform);

        // Verify
        $usrUserParameter = UsrUserParameter::query()
            ->where('usr_user_id', $currentUser->id)
            ->first();
        $this->assertEquals(UserConstant::MAX_STAMINA_RECOVERY, $usrUserParameter->getStamina());
    }

    public function test_AddAllUnitUseCaseユニット付与のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $mstUnitId = "1";
        MstUnit::factory()->create(['id' => $mstUnitId]);

        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();
        $this->assertEquals(0, count($usrUnits));

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'AddAllUnit', $platform);

        // Verify
        $usrUnit = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->first();
        $this->assertEquals($mstUnitId, $usrUnit->getMstUnitId());
    }

    public function test_AddUserExpUseCase_経験値付与のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'exp' => 5,
            'stamina' => 0,
        ]);
        for ($i = 1; $i <= 3; $i++) {
            MstUserLevel::factory()->create([
                'id' => (string) $i,
                'level' => 1 * $i,
                'exp' => 10 * $i
            ]);
        }
        $this->assertEquals(1, $usrUserParameter->getLevel());
        $this->assertEquals(5, $usrUserParameter->getExp());

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'AddUserExp', $platform);

        // Verify
        $usrUserParameter = UsrUserParameter::query()
            ->where('usr_user_id', $currentUser->id)
            ->first();

        $this->assertEquals(1, $usrUserParameter->getLevel());
        $this->assertEquals(19, $usrUserParameter->getExp());
    }

    public function test_AddAllEmblemUseCase_エンブレム全付与のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $mstEmblemIds = [
            ['id' => "emblem1"],
            ['id' => "emblem2"]
        ];
        MstEmblem::factory()->createMany($mstEmblemIds);
        $usrEmblems = UsrEmblem::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();
        $this->assertEquals(0, count($usrEmblems));

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'AddAllEmblem', $platform);

        $usrEmblems = UsrEmblem::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();

        $this->assertEquals(count($mstEmblemIds), count($usrEmblems));
    }

    public function test_ResetNameUpdateAtUseCase_ユーザー名更新時間リセットのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $usrUserProfile = \App\Domain\User\Models\UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test1',
            'name_update_at' => '2024-04-10 10:00:00',
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'ResetNameUpdateAt', $platform);

        // Verify
        /** @var UsrUserProfile $resultUserProfile */
        $resultUserProfile = UsrUserProfile::query()->find($usrUser->getId());
        // name_update_atが空になっているか
        $this->assertEquals('', $resultUserProfile->getNameUpdateAt());
    }

    public function test_NotCommandTestUseCase_デバッグコマンドがなかった場合のエラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $platform = UserConstant::PLATFORM_IOS;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 1,
        ]);
        $currentUser = new CurrentUser($usrUser->getId());
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ADMIN_DEBUG_FAILED);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'NotCommandTest', $platform);
    }

    public function test_TutorialMainPartCompleteUseCase_チュートリアルメインパートをスキップできる(): void
    {
        // Setup
        $this->fixTime('2024-04-10 10:00:00');
        $usrUser = $this->createUsrUser([
            'tutorial_status' => 'Tutorial3',
        ]);
        $currentUser = new CurrentUser($usrUser->getId());
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $platform = UserConstant::PLATFORM_IOS;

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'exp' => 5,
            'stamina' => 0,
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'mst_unit_id' => '',
            'mst_emblem_id' => 'emblem1',
        ]);

        MstTutorial::factory()->createMany([
            ['id' => 'tutorial1', 'type' => TutorialType::INTRO, 'function_name' => 'Tutorial1', 'sort_order' => 1, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
            ['id' => 'tutorial2', 'type' => TutorialType::MAIN, 'function_name' => 'Tutorial2', 'sort_order' => 2, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
            ['id' => 'tutorial3', 'type' => TutorialType::MAIN, 'function_name' => 'Tutorial3', 'sort_order' => 3, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
            ['id' => 'tutorial4', 'type' => TutorialType::MAIN, 'function_name' => 'GachaConfirmed', 'sort_order' => 4, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
            ['id' => 'tutorial5', 'type' => TutorialType::MAIN, 'function_name' => 'MainPartCompleted', 'sort_order' => 5, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
            // 無視される
            ['id' => 'freeTutorial1', 'type' => TutorialType::FREE, 'function_name' => 'FreeTutorial1', 'sort_order' => 999, 'start_at' => '2024-04-01 10:00:00', 'end_at' => '2024-04-10 10:00:00'],
        ]);

        // mst
        OprGacha::factory()->create([
            'id' => 'tutorial_gacha_1',
            'gacha_type' => GachaType::TUTORIAL,
            'upper_group' => 'None',
            'enable_ad_play' => 0,
            'enable_add_ad_play_upper' => 0,
            'ad_play_interval_time' => null,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 1,
            'daily_play_limit_count' => null,
            'total_play_limit_count' => null,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
            'prize_group_id' => 'prize_group_1',
            'fixed_prize_group_id' => 'fixed_prize_group_1',
            'appearance_condition' => 'Always',
            'start_at' => '2020-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);

        // 未使用のデータも用意。チュートリアルのガシャデータを取得することを確認するため
        OprGacha::factory()->createMany([
            ['gacha_type' => GachaType::NORMAL],
            ['gacha_type' => GachaType::PREMIUM],
        ]);
        OprGachaPrize::factory()->createMany([
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 1,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 0,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_3',
                'resource_amount' => 1,
                'weight' => 98,
                'pickup' => 0,
            ],
            [
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
            [
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'fragment_unit_1'],
            ['id' => 'fragment_unit_2'],
            ['id' => 'fragment_unit_3'],
        ]);
        MstUnit::factory()->createMany([
            ['id' => 'unit_1', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_1'],
            ['id' => 'unit_2', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_2'],
            ['id' => 'unit_3', 'unit_label' => UnitLabel::DROP_SR, 'fragment_mst_item_id' => 'fragment_unit_3'],
        ]);
        MstUnitFragmentConvert::factory()->createMany([
            ['unit_label' => UnitLabel::DROP_SR, 'convert_amount' => 10],
            ['unit_label' => UnitLabel::DROP_UR, 'convert_amount' => 20],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'TutorialMainPartComplete', $platform);

        // Verify
        $usrUser->refresh();
        $this->assertEquals('MainPartCompleted', $usrUser->getTutorialStatus());

        // パーティ設定がある
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();
        $this->assertNotEquals(0, count($usrUnits));
        // ユニットを所持している(PartyConstant::INITIAL_PARTY_COUNT分)
        $usrParty = UsrParty::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();
        $this->assertEquals(PartyConstant::INITIAL_PARTY_COUNT, count($usrParty));
        
        // アバターが設定されている
        $usrUserProfile = UsrUserProfile::query()
            ->where('usr_user_id', $currentUser->id)
            ->first();
        $this->assertNotNull($usrUserProfile);
        $firstUnit = $usrUnits->first();
        $this->assertEquals($firstUnit->getMstUnitId(), $usrUserProfile->getMstUnitId());
    }

    public function test_DeleteStageUseCase_ユーザーの解放済みステージの一斉解除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $usrUserProfile = \App\Domain\User\Models\UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test1',
            'name_update_at' => '2024-04-10 10:00:00',
        ]);

        $mstStageId = "1";
        MstStage::factory()->create([
            'id' => $mstStageId,
            'start_at' => '2022-01-01 00:00:00',
            'end_at' => '2030-12-21 12:34:56',
        ]);

        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            
        ]);
        // 削除前のUsrStageレコード数を確認
        $this->assertEquals(1, UsrStage::count());

        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => 'stage1',
        ]);
        // 削除前のUsrStageEvenレコード数を確認
        $this->assertEquals(1, UsrStageEvent::count());

        UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => 'enhance_quest_stage',
            'clear_count' => 0,
            'reset_challenge_count' => 0,
            'reset_ad_challenge_count' => 0,
            'max_score' => 100,
        ]);
        // 削除前のUsrStageEnhanceレコード数を確認
        $this->assertEquals(1, UsrStageEnhance::count());

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteStage', $platform);

        // 削除後のUsrStageレコード数を確認
        $this->assertEquals(0, UsrStage::count());
        // 削除前のUsrStageEventレコード数を確認
        $this->assertEquals(0, UsrStageEvent::count());
        // 削除前のUsrStageEnhanceレコード数を確認
        $this->assertEquals(0, UsrStageEnhance::count());

    }

    public function test_DeleteAllUnitUseCase_所持ユニットの一斉削除のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ]);

        $usrUnits = UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit1', 'level' => 1, 'rank' => 1, 'grade_level' => 1],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit2', 'level' => 1, 'rank' => 1, 'grade_level' => 1],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit3', 'level' => 1, 'rank' => 1, 'grade_level' => 1],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllUnit', $platform);

        // Verify
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();

        $this->assertEquals(0, count($usrUnits));
    }

    public function test_InitAllUnitStatusUseCase_所持ユニットの初期化のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ]);

        $usrUnits = UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit1', 'level' => 2, 'rank' => 2, 'grade_level' => 2],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit2', 'level' => 3, 'rank' => 3, 'grade_level' => 2],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit3', 'level' => 4, 'rank' => 4, 'grade_level' => 2],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'InitAllUnitStatus', $platform);

        // Verify
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();

        $this->assertEquals(3, count($usrUnits));

        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getLevel());
            $this->assertEquals(0, $usrUnit->getRank());
            $this->assertEquals(1, $usrUnit->getGradeLevel());
            $this->assertEquals(0, $usrUnit->getbattleCount());
        }
    }

    public function test_DeleteAllItemUseCase_ユーザーの所持アイテムの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => '1', 'amount' => 100],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => '2', 'amount' => 100],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => '3', 'amount' => 100],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => '4', 'amount' => 100],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => '5', 'amount' => 100],
        ]);
        // 削除前のUsrItemレコード数を確認
        $this->assertEquals(5, UsrItem::count());

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllItem', $platform);

        // 削除後のUsrItemレコード数を確認
        $this->assertEquals(0, UsrItem::count());
    }

    public function test_DeleteAllEmblemUseCase_所持エンブレムの一斉削除のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test1',
            'name_update_at' => '2024-04-10 10:00:00',
            'mst_emblem_id' => 'emblem_1',
        ]);

        $mstEmblemIds = [
            ['id' => "1"],
            ['id' => "2"]
        ];
        MstEmblem::factory()->createMany($mstEmblemIds);
        UsrEmblem::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_emblem_id' => '1'],
            ['usr_user_id' => $usrUser->getId(), 'mst_emblem_id' => '2'],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllEmblem', $platform);

        $usrUserProfile = UsrUserProfile::query()->where('usr_user_id', $usrUser->getId())->first();

        $this->assertEquals('', $usrUserProfile->getMstEmblemId());
        $this->assertEquals(0, UsrEmblem::count());
    }

    public function test_DeleteAllArtworkUseCase_所持原画の一斉削除のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrArtwork::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_artwork_id' => 'artwork1'],
            ['usr_user_id' => $usrUser->getId(), 'mst_artwork_id' => 'artwork2'],
            ['usr_user_id' => $usrUser->getId(), 'mst_artwork_id' => 'artwork3'],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllArtwork', $platform);

        $this->assertEquals(0, UsrArtwork::count());
    }

    public function test_InitEncyclopediaRankUseCase_ユーザーの図鑑ランクの初期化のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ]);

        UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit1', 'level' => 1, 'rank' => 1, 'grade_level' => 3],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit2', 'level' => 1, 'rank' => 1, 'grade_level' => 4],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit3', 'level' => 1, 'rank' => 1, 'grade_level' => 5],
        ]);

        UsrReceivedUnitEncyclopediaReward::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_encyclopedia_reward_id' => '1'],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_encyclopedia_reward_id' => '2'],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'InitEncyclopediaRank', $platform);

        // Verify
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();

        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(0, $usrUnit->getGradeLevel());
        }

        $this->assertEquals(0, UsrReceivedUnitEncyclopediaReward::count());
    }

    public function test_DeleteOutPostUseCase_ユーザーの所持ゲートの初期化のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $mstOutpostEnhancementEntity = MstOutpostEnhancement::factory()->create()->toEntity();
        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_outpost_id' => $mstOutpostEnhancementEntity->getMstOutpostId(),
            'mst_outpost_enhancement_id' => $mstOutpostEnhancementEntity->getId(),
            'level' => 3,
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteOutPost', $platform);

        $usrOutpostEnhancement = UsrOutpostEnhancement::query()
            ->first();

        // 初期化後のUsrOutpostレコード数を確認
        $this->assertEquals(1, $usrOutpostEnhancement->getLevel());

    }

    public function test_DeleteAllMessageUseCase_メールBOXの一斉削除のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2025-03-10 00:00:00')
            ->create();

        UsrMessage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mng_message_id' => 'message_1',
            'message_source' => MessageSource::MNG_MESSAGE,
            'opened_at' => '2025-03-10 01:00:00',
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMessage', $platform);

        // Verify
        $usrMessages = UsrMessage::query()
            ->where('usr_user_id', $currentUser->id)
            ->get();

        $this->assertEquals(0, count($usrMessages));
    }

    public function test_DeleteAllGachaUseCase_ガシャの一斉削除のデバッグコマンドを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->toDateTimeString(),
            'played_at' => $now->toDateTimeString(),
        ]);

        UsrGachaUpper::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 10,
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllGacha', $platform);

        $this->assertEquals(0, UsrGacha::count());
        $this->assertEquals(0, UsrGachaUpper::count());

    }

    public function test_DeleteAllMissionAchivementUseCase_ユーザーのアチーブメントミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionNormal::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
                'mst_mission_id' => 'achievement_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
                'mst_mission_id' => 'achievement_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::BEGINNER->getIntValue(),
                'mst_mission_id' => 'beginner_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionAchivement', $platform);

        // 削除後のUsrMissionNormalのアチーブメントのレコード数を確認
        $this->assertEquals(0, UsrMissionNormal::where('mission_type', MissionType::ACHIEVEMENT->getIntValue())->count());
        // 削除後のUsrMissionNormalレコード数を確認
        $this->assertEquals(1, UsrMissionNormal::count());
    }

    public function test_DeleteAllMissionBeginnerUseCase_ユーザーの初心者ミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionNormal::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::BEGINNER->getIntValue(),
                'mst_mission_id' => 'beginner_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::BEGINNER->getIntValue(),
                'mst_mission_id' => 'beginner_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
                'mst_mission_id' => 'achievement_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // usr
        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'beginner_mission_status' => MissionBeginnerStatus::FULLY_UNLOCKED->value, // 全ミッション開放済で完了判定処理を進められる状態に設定
            'mission_unlocked_at' => '2025-03-12 00:00:00',
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionBeginner', $platform);

        // 削除後のUsrMissionNormalのアチーブメントのレコード数を確認
        $this->assertEquals(0, UsrMissionNormal::where('mission_type', MissionType::BEGINNER->getIntValue())->count());
        // 削除後のUsrMissionNormalレコード数を確認
        $this->assertEquals(1, UsrMissionNormal::count());
        $this->assertEquals(0, UsrMissionStatus::count());
    }

    public function test_DeleteAllMissionDailyUseCase_ユーザーのデイリーミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionNormal::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::DAILY->getIntValue(),
                'mst_mission_id' => 'daily_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::DAILY->getIntValue(),
                'mst_mission_id' => 'daily_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
                'mst_mission_id' => 'achievement_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionDaily', $platform);

        // 削除後のUsrMissionNormalのアチーブメントのレコード数を確認
        $this->assertEquals(0, UsrMissionNormal::where('mission_type', MissionType::DAILY->getIntValue())->count());
        // 削除後のUsrMissionNormalレコード数を確認
        $this->assertEquals(1, UsrMissionNormal::count());
    }

    public function test_DeleteAllMissionWeeklyUseCaseユーザーのウィークリーミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionNormal::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::WEEKLY->getIntValue(),
                'mst_mission_id' => 'weekly_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::WEEKLY->getIntValue(),
                'mst_mission_id' => 'weekly_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
                'mst_mission_id' => 'achievement_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionWeekly', $platform);

        // 削除後のUsrMissionNormalのウィークリーのレコード数を確認
        $this->assertEquals(0, UsrMissionNormal::where('mission_type', MissionType::WEEKLY->getIntValue())->count());
        // 削除後のUsrMissionNormalレコード数を確認
        $this->assertEquals(1, UsrMissionNormal::count());
    }

    public function test_DeleteAllAdventBattleUseCase_降臨バトル情報の一斉削除のデバッグコマンドを実行()
    {

        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => 'advent1',
            'max_score' => 10,
            'total_score' => 100,
        ]);

        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => "20",
            'party_no' => 5,
        ]);
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllAdventBattle', $platform);

        // Verify
        $this->assertEquals(0, UsrAdventBattle::count());
        $this->assertEquals(0, UsrAdventBattleSession::count());
    }

    public function test_DeleteAllMissionEventUseCaseユーザーのイベントミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionEvent::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'event_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'event_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'event_daily_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => '1',
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'event_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionEvent', $platform);

        // 削除後のUsrMissionEventのイベントの対象ユーザーのレコード数を確認
        $this->assertEquals(0, UsrMissionEvent::where('mission_type', MissionType::EVENT->getIntValue())->where('usr_user_id', $usrUser->getId())->count());
        // 削除後のUsrMissionEventレコード数を確認
        $this->assertEquals(2, UsrMissionEvent::count());
        // 削除後のUsrMissionEventのイベントの別ユーザーのレコード数を確認
        $this->assertEquals(1, UsrMissionEvent::where('mission_type', MissionType::EVENT->getIntValue())->where('usr_user_id', 1)->count());
    }

    public function test_DeleteAllMissionEventDailyUseCaseユーザーのイベントデイリーミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionEvent::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'event_daily_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'event_daily_2',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'event_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => '1',
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'event_daily_1',
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionEventDaily', $platform);

        // 削除後のUsrMissionEventのイベントデイリーの対象ユーザーのレコード数を確認
        $this->assertEquals(0, UsrMissionEvent::where('mission_type', MissionType::EVENT_DAILY->getIntValue())->where('usr_user_id', $usrUser->getId())->count());
        // 削除後のUsrMissionEventレコード数を確認
        $this->assertEquals(2, UsrMissionEvent::count());
        // 削除後のUsrMissionEventのイベントデイリーの別ユーザーのレコード数を確認
        $this->assertEquals(1, UsrMissionEvent::where('mission_type', MissionType::EVENT_DAILY->getIntValue())->where('usr_user_id', 1)->count());
    }

    public function test_DeleteAllMissionLimitedTermUseCaseユーザーの期間限定ミッションの一斉削除のデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        UsrMissionLimitedTerm::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_mission_limited_term_id' => 'limited_term_1',
                'status' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_mission_limited_term_id' => 'limited_term_2',
                'status' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
            [
                'usr_user_id' => '1',
                'mst_mission_limited_term_id' => 'limited_term_1',
                'status' => 1,
                'cleared_at' => '2025-03-12 00:00:00',
                'received_reward_at' => '2025-03-12 00:00:00',
            ],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteAllMissionLimitedTerm', $platform);

        // 削除後のUsrMissionLimitedTermの期間限定の対象ユーザーのレコード数を確認
        $this->assertEquals(0, UsrMissionLimitedTerm::where('usr_user_id', $usrUser->getId())->count());
        // 削除後のUsrMissionLimitedTermの期間限定のレコード数を確認
        $this->assertEquals(1, UsrMissionLimitedTerm::count());
        // 削除後のUsrMissionLimitedTermの期間限定の対象外ユーザーのレコード数を確認
        $this->assertEquals(1, UsrMissionLimitedTerm::where('usr_user_id', 1)->count());
    }

    public function test_GrantUserItemMaxUseCaseユーザーの所持アイテムMAXのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $now = $this->fixTime();

        $addMstItems = [
            [
                'id' => '1_1_1',
                'type' => ItemType::CHARACTER_FRAGMENT->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '2_1_1',
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '3_1_1',
                'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '4_1_1',
                'type' => ItemType::STAGE_MEDAL->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '5_1_1',
                'type' => ItemType::IDLE_COIN_BOX->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '6_1_1',
                'type' => ItemType::IDLE_RANK_UP_MATERIAL_BOX->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '7_1_1',
                'type' => ItemType::RANDOM_FRAGMENT_BOX->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '8_1_1',
                'type' => ItemType::SELECTION_FRAGMENT_BOX->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '9_1_1',
                'type' => ItemType::GACHA_TICKET->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '10_1_1',
                'type' => ItemType::GACHA_MEDAL->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ],
            [
                'id' => '11_1_1',
                'type' => ItemType::ETC->value,
                'start_date' => $now->toDateTimeString(),
                'end_date' => $now->addDay(1)->toDateTimeString(),
            ]
        ];
        MstItem::factory()->createMany($addMstItems);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'GrantUserItemMax', $platform);

        // Verify
        $usrItems = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())->get();

        $this->assertEquals(count($usrItems), count($addMstItems));

        foreach ($usrItems as $usrItem) {
            $this->assertEquals(ItemConstant::MAX_POSESSION_ITEM_COUNT, $usrItem->getAmount());
        }
    }

    public function test_GrantUserUnitMaxUseCaseユーザーの所持ユニットMAXのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $targetMaxLevel = 100;
        $targetMaxRank = 10;
        $targetSpecificMaxRank = 12;
        $targetMaxGradeLevel = 10;

        $insertMstUnitArray = [
            ['id' => 'unit0', 'has_specific_rank_up' => 1],
            ['id' => 'unit1', 'has_specific_rank_up' => 0],
            ['id' => 'unit2', 'has_specific_rank_up' => 0],
            ['id' => 'unit3', 'has_specific_rank_up' => 0],
        ];
        MstUnit::factory()->createMany($insertMstUnitArray);

        MstUnitLevelUp::factory()->createMany([
            ['id' => 'unit0', 'unit_label' => 'DropUR', 'level' => 90, 'required_coin' => '1'],
            ['id' => 'unit0-2', 'unit_label' => 'DropUR', 'level' => $targetMaxLevel, 'required_coin' => '1'],
            ['id' => 'unit1', 'unit_label' => 'DropR', 'level' => 90, 'required_coin' => '1'],
            ['id' => 'unit1-2', 'unit_label' => 'DropR', 'level' => $targetMaxLevel, 'required_coin' => '1'],
            ['id' => 'unit2', 'unit_label' => 'DropSR', 'level' => 90, 'required_coin' => '1'],
            ['id' => 'unit2-2', 'unit_label' => 'DropSR', 'level' => $targetMaxLevel, 'required_coin' => '1'],
            ['id' => 'unit3', 'unit_label' => 'DropSSR', 'level' => 90, 'required_coin' => '1'],
            ['id' => 'unit3-3', 'unit_label' => 'DropSSR', 'level' => $targetMaxLevel, 'required_coin' => '1'],
        ]);

        MstUnitGradeUp::factory()->createMany([
            ['id' => 'unit0', 'unit_label' => 'DropUR', 'grade_level' => 1, 'require_amount' => '1'],
            ['id' => 'unit0-2', 'unit_label' => 'DropUR', 'grade_level' => $targetMaxGradeLevel, 'require_amount' => '1'],
            ['id' => 'unit1', 'unit_label' => 'DropR', 'grade_level' => 1, 'require_amount' => '1'],
            ['id' => 'unit1-2', 'unit_label' => 'DropR', 'grade_level' => $targetMaxGradeLevel, 'require_amount' => '1'],
            ['id' => 'unit2', 'unit_label' => 'DropSR', 'grade_level' => 1, 'require_amount' => '1'],
            ['id' => 'unit2-2', 'unit_label' => 'DropSR', 'grade_level' => $targetMaxGradeLevel, 'require_amount' => '1'],
            ['id' => 'unit3', 'unit_label' => 'DropSSR', 'grade_level' => 1, 'require_amount' => '1'],
            ['id' => 'unit3-2', 'unit_label' => 'DropSSR', 'grade_level' => $targetMaxGradeLevel, 'require_amount' => '1'],
        ]);

        MstUnitRankUp::factory()->createMany([
            ['id' => 'unit0', 'unit_label' => 'DropUR', 'rank' => 1, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit0-2', 'unit_label' => 'DropUR', 'rank' => $targetMaxRank, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit1', 'unit_label' => 'DropR', 'rank' => 1, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit1-2', 'unit_label' => 'DropR', 'rank' => $targetMaxRank, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit2', 'unit_label' => 'DropSR', 'rank' => 1, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit2-2', 'unit_label' => 'DropSR', 'rank' => $targetMaxRank, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit3', 'unit_label' => 'DropSSR', 'rank' => 1, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit3-2', 'unit_label' => 'DropSSR', 'rank' => $targetMaxRank, 'amount' => '1', 'require_level' => '1'],
        ]);

        MstUnitSpecificRankUp::factory()->createMany([
            ['id' => 'unit0', 'mst_unit_id' => 'unit0', 'rank' => 1, 'amount' => '1', 'require_level' => '1'],
            ['id' => 'unit0-2', 'mst_unit_id' => 'unit0', 'rank' => $targetSpecificMaxRank, 'amount' => '1', 'require_level' => '1'],
        ]);

        $exsistsUnitMtsId = 'unit0';
        UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => 'unit0', 'level' => 1, 'rank' => 1, 'grade_level' => 1],
        ]);
        // 上で入れたUsrUnitのUUIDを保持しておく
        $exsistsUsrUnitId = UsrUnit::query()
            ->where('usr_user_id', $usrUser->getId())->first()->getId();

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'GrantUserUnitMax', $platform);

        // Verify
        $usrUnits = UsrUnit::query()
            ->where('usr_user_id', $usrUser->getId())->get();

        $this->assertEquals(count($usrUnits), count($insertMstUnitArray));

        $mstUnitHasSpecificRankUp = array_column($insertMstUnitArray, 'has_specific_rank_up', 'id');

        foreach ($usrUnits as $usrUnit) {
            if ($usrUnit->getMstUnitId() === $exsistsUnitMtsId) {
                // 既存のユニットはUUIDが同じであることを確認
                $this->assertEquals($exsistsUsrUnitId, $usrUnit->getId());
            }

            // 付与したユニット/もともと所持していたユニットの両方のレベル、ランク、グレードレベルがMAXになっていることを確認
            $this->assertEquals($targetMaxLevel, $usrUnit->getLevel());
            $this->assertEquals($targetMaxGradeLevel, $usrUnit->getGradeLevel());

            if ($mstUnitHasSpecificRankUp[$usrUnit->getMstUnitId()] === 1) {
                // 特殊ランクアップがあるユニットは、特殊ランクMAXになっていることを確認
                $this->assertEquals($targetSpecificMaxRank, $usrUnit->getRank());
            } else {
                // 特殊ランクアップがないユニットは、通常のランクMAXになっていることを確認
                $this->assertEquals($targetMaxRank, $usrUnit->getRank());
            }
        }
    }

    public function test_GrantUserUnitMaxUseCaseユーザーの所持コイン無償プリズムMAXのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $maxAmount = 999999999;
        Config::set('wp_currency.store.max_owned_free_currency_amount', $maxAmount);

        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => 100,
        ]);

        $this->createDiamond($usrUser->getId(), freeDiamond: 150);
        $diamond = $this->getDiamond($usrUser->getId());

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'GrantUserCurrencyMax', $platform);

        // Verify
        // 所持コインがMAXになっていることを確認
        $usrUserParameter = UsrUserParameter::query()
            ->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(UserConstant::MAX_COIN_COUNT, $usrUserParameter->getCoin());

        // 無償プリズムがMAXになっていることを確認
        $diamond = $this->getDiamond($usrUser->getId());
        $this->assertEquals($maxAmount, $diamond->getFreeAmount());
    }

    public function test_DeleteUsrShopUseCaseショップ購入履歴の削除(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);

        UsrShopPass::factory()->createMany([
            [
                'id' => 'test',
                'usr_user_id' => $usrUserId,
                'mst_shop_pass_id' => fake()->uuid(),
                'daily_reward_received_count' => 1,
                'daily_latest_received_at' => $now->subDays(1)->toDateTimeString(),
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDay(7)->toDateTimeString(),
            ],
            [
                'id' => 'test2',
                'usr_user_id' => $usrUserId,
                'mst_shop_pass_id' => fake()->uuid(),
                'daily_reward_received_count' => 1,
                'daily_latest_received_at' => $now->subDays(1)->toDateTimeString(),
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDay(7)->toDateTimeString(),
            ],
        ]);

        UsrShopItem::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_shop_item_id' => 'pack_160_1_framework',
                'trade_count' => 1,
                'trade_total_count' => 1,
                'last_reset_at' => $now->subDays(1)->toDateTimeString(),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_shop_item_id' => 'pack_160_2_framework',
                'trade_count' => 1,
                'trade_total_count' => 1,
                'last_reset_at' => $now->subDays(1)->toDateTimeString(),
            ],
        ]);

        UsrStoreProduct::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'product_sub_id' => 'product_sub_id_1',
                'purchase_count' => 1,
                'purchase_total_count' => 1,
                'last_reset_at' => $now->subDays(1)->toDateTimeString(),
            ],
        ]);

        $beforeUsrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $beforeUsrShopItem = UsrShopItem::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $beforeUsrStoreProduct = UsrStoreProduct::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'DeleteUsrShop', $platform);

        // Verify
        $this->assertEquals(2, $beforeUsrShopPass->count());
        $this->assertEquals(2, $beforeUsrShopItem->count());
        $this->assertEquals(1, $beforeUsrStoreProduct->count());
        // ショップ購入履歴の削除確認
        $usrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertEquals(0, $usrShopPass->count());
        $usrShopItem = UsrShopItem::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertEquals(0, $usrShopItem->count());

        $usrStoreProduct = UsrStoreProduct::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
        $this->assertEquals(0, $usrStoreProduct->count());
    }

    public function test_GrantUserOutpostMaxUseCaseユーザーの所持ゲートのレベルMAXのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_1';
        $targetMaxLevel = 10;

        // OutpostEnhancementTypeの数分だけMstOutpostEnhancement用のUUIDを作成
        $mstOutpostEnhancementIdList = [];
        for ($i = 0; $i < count(OutpostEnhancementType::cases()); $i++) {
            $mstOutpostEnhancementIdList[$i] = fake()->uuid();
        }

        MstOutpost::factory()->create([
            'id' => $mstOutpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);

        $mstOutpostEnhancementAddList = [];
        $mstOutpostEnhancementLevelAddList = [];
        $i = 0;
        foreach (OutpostEnhancementType::cases() as $outpostEnhancementType) {
            // MstOutpostEnhancement投入用配列を作る
            $mstOutpostEnhancementAddList[] = [
                'id' => $mstOutpostEnhancementIdList[$i],
                'mst_outpost_id' => $mstOutpostId,
                'outpost_enhancement_type' => $outpostEnhancementType->value,
                'asset_key' => fake()->uuid(),
            ];

            // MstOutpostEnhancementLevel投入用配列を作る(レベルの高い方検証で１と１０を用意)
            $mstOutpostEnhancementLevelAddList[] = [
                'id' => fake()->uuid(),
                'mst_outpost_enhancement_id' => $mstOutpostEnhancementIdList[$i],
                'level' => 1,
                'cost_coin' => 10,
                'enhancement_value' => 1.0,
            ];
            $mstOutpostEnhancementLevelAddList[] = [
                'id' => fake()->uuid(),
                'mst_outpost_enhancement_id' => $mstOutpostEnhancementIdList[$i],
                'level' => $targetMaxLevel,
                'cost_coin' => 10,
                'enhancement_value' => 10.0,
            ];
            $i++;
        }

        MstOutpostEnhancement::factory()->createMany($mstOutpostEnhancementAddList);
        MstOutpostEnhancementLevel::factory()->createMany($mstOutpostEnhancementLevelAddList);

        UsrOutpost::factory()->create(['id' => 'usrOutpostId', 'usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => $mstOutpostId, 'mst_artwork_id' => $mstArtworkId]);

        UsrOutpostEnhancement::factory()->createMany([
            ['id' => 'usrOutpostEnhancementId_1', 'usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => $mstOutpostId, 'mst_outpost_enhancement_id' => $mstOutpostEnhancementIdList[0], 'level' => 1],
            ['id' => 'usrOutpostEnhancementId_2', 'usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => $mstOutpostId, 'mst_outpost_enhancement_id' => $mstOutpostEnhancementIdList[1], 'level' => 1],
            ['id' => 'usrOutpostEnhancementId_3', 'usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => $mstOutpostId, 'mst_outpost_enhancement_id' => $mstOutpostEnhancementIdList[2], 'level' => 1],
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'GrantUserOutpostMax', $platform);

        // Verify
        // UsrOutpostEnhancementのレベルがMAXになっていることを確認
        $afterUsrOutpostEnhancements = UsrOutpostEnhancement::query()
            ->where('usr_user_id', $usrUser->getId())->get();

        foreach ($afterUsrOutpostEnhancements as $afterUsrOutpostEnhancement) {
            $this->assertEquals($targetMaxLevel, $afterUsrOutpostEnhancement->getLevel());
        }
    }

    public function test_UserServerTimeResetUseCase_ユーザーサーバー時間リセットのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;

        // キャッシュにユーザー時間設定をセット
        $repository = app()->make(\App\Domain\Debug\Repositories\DebugUserTimeSettingRepository::class);
        $debugUserTimeSetting = new \App\Domain\Debug\Entities\DebugUserTimeSetting(
            \Carbon\CarbonImmutable::create(2025, 6, 15, 12, 0, 0, 'Asia/Tokyo')
        );
        $repository->put($usrUser->getId(), $debugUserTimeSetting);
        $this->assertTrue($repository->exists($usrUser->getId()));

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'UserServerTimeReset', $platform);

        // Verify
        $this->assertFalse($repository->exists($usrUser->getId()));
    }

    public function test_ResetLimitCountContentsUseCase回数制限リセットのデバッグコマンドを実行(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_count' => 10,
            'ad_daily_count' => 10,
            'count' => 10,
            'ad_played_at' => $now->toDateTimeString(),
            'played_at' => $now->toDateTimeString(),
        ]);

        UsrGachaUpper::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 10,
        ]);

        // 降臨バトルの状況をリセット
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => 'advent1',
            'reset_challenge_count' => 10,
            'reset_ad_challenge_count' => 10,
        ]);

        // 探索の状況をリセット
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'diamond_quick_receive_count' => 10,
            'ad_quick_receive_count' => 10,
            'idle_started_at' => $now->toDateTimeString(),
            'diamond_quick_receive_at' => $now->toDateTimeString(),
            'ad_quick_receive_at' => $now->toDateTimeString(),
        ]);

        ///イベントステージ？
        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => 'stageevent1',
            'reset_clear_count' => 10,
            'reset_ad_challenge_count' => 10,
            'reset_clear_time_ms' => 10,
            'clear_time_ms' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        // 強化クエストステージ
        UsrStageEnhance::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => 'stageenhance1',
            'clear_count' => 10,
            'reset_challenge_count' => 10,
            'reset_ad_challenge_count' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => 'stagesession1',
            'daily_continue_ad_count' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'daily_buy_stamina_ad_count' => 10,
            'daily_buy_stamina_ad_at' => $now->toDateTimeString(),
        ]);

        UsrShopItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_shop_item_id' => 'mstshopitem1',
            'trade_count' => 10,
            'trade_total_count' => 10,
            'last_reset_at' => $now->toDateTimeString(),
        ]);

        UsrStoreProduct::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'product_sub_id' => 'shopproduct1',
            'purchase_count' => 10,
            'purchase_total_count' => 10,
            'last_reset_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $useCase = new DebugCommandExecUseCase();
        $useCase->exec($currentUser, 'ResetLimitCountContents', $platform);

        // Verify
        $resetCountValue = 0;
        $resetMsValue = null;
        $resetDateTimeValue = '2000-01-01 00:00:00';
        // ガチャ
        $usrGachas = UsrGacha::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrGachas as $usrGacha) {
            $this->assertEquals($resetCountValue, $usrGacha->ad_count);
            $this->assertEquals($resetCountValue, $usrGacha->ad_daily_count);
            $this->assertEquals($resetCountValue, $usrGacha->count);
            $this->assertEquals($resetDateTimeValue, $usrGacha->ad_played_at);
            $this->assertEquals($resetDateTimeValue, $usrGacha->played_at);
        }
        // ガチャ天井
        $usrGachaUppers = UsrGachaUpper::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrGachaUppers as $usrGachaUpper) {
            $this->assertEquals($resetCountValue, $usrGachaUpper->count);
        }
        // 降臨バトル
        $usrAdventBattles = UsrAdventBattle::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrAdventBattles as $usrAdventBattle) {
            $this->assertEquals($resetCountValue, $usrAdventBattle->reset_challenge_count);
            $this->assertEquals($resetCountValue, $usrAdventBattle->reset_ad_challenge_count);
        }
        // 探索
        $usrIdleIncentives = UsrIdleIncentive::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrIdleIncentives as $usrIdleIncentive) {
            $this->assertEquals($resetCountValue, $usrIdleIncentive->diamond_quick_receive_count);
            $this->assertEquals($resetCountValue, $usrIdleIncentive->ad_quick_receive_count);
            $this->assertEquals($resetDateTimeValue, $usrIdleIncentive->idle_started_at);
            $this->assertEquals($resetDateTimeValue, $usrIdleIncentive->diamond_quick_receive_at);
            $this->assertEquals($resetDateTimeValue, $usrIdleIncentive->ad_quick_receive_at);
        }
        //イベントステージ
        $usrStageEvents = UsrStageEvent::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrStageEvents as $usrStageEvent) {
            $this->assertEquals($resetCountValue, $usrStageEvent->reset_clear_count);
            $this->assertEquals($resetCountValue, $usrStageEvent->reset_ad_challenge_count);
            $this->assertEquals($resetMsValue, $usrStageEvent->reset_clear_time_ms);
            $this->assertEquals($resetMsValue, $usrStageEvent->clear_time_ms);
            $this->assertEquals($resetDateTimeValue, $usrStageEvent->latest_reset_at);
        }
        // 強化クエストステージ
        $usrStageEnhances = UsrStageEnhance::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrStageEnhances as $usrStageEnhance) {
            $this->assertEquals($resetCountValue, $usrStageEnhance->clear_count);
            $this->assertEquals($resetCountValue, $usrStageEnhance->reset_challenge_count);
            $this->assertEquals($resetCountValue, $usrStageEnhance->reset_ad_challenge_count);
            $this->assertEquals($resetDateTimeValue, $usrStageEnhance->latest_reset_at);
        }
        $usrStageSessions = UsrStageSession::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrStageSessions as $usrStageSession) {
            $this->assertEquals($resetCountValue, $usrStageSession->daily_continue_ad_count);
            $this->assertEquals($resetDateTimeValue, $usrStageSession->latest_reset_at);
        }
        $usrUserBuyCounts = UsrUserBuyCount::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrUserBuyCounts as $usrUserBuyCount) {
            $this->assertEquals($resetCountValue, $usrUserBuyCount->daily_buy_stamina_ad_count);
            $this->assertEquals($resetDateTimeValue, $usrUserBuyCount->daily_buy_stamina_ad_at);
        }
        $usrShopItems = UsrShopItem::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrShopItems as $usrShopItem) {
            $this->assertEquals($resetCountValue, $usrShopItem->trade_count);
            $this->assertEquals($resetCountValue, $usrShopItem->trade_total_count);
            $this->assertEquals($resetDateTimeValue, $usrShopItem->last_reset_at);
        }
        $usrStoreProducts = UsrStoreProduct::query()->where('usr_user_id', $usrUser->getId())->get();
        foreach ($usrStoreProducts as $usrStoreProduct) {
            $this->assertEquals($resetCountValue, $usrStoreProduct->purchase_count);
            $this->assertEquals($resetCountValue, $usrStoreProduct->purchase_total_count);
            $this->assertEquals($resetDateTimeValue, $usrStoreProduct->last_reset_at);
        }
    }
}
