<?php

namespace Feature\Http\Controllers;

use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Enums\EncyclopediaType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUp;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUpCost;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class EncyclopediaControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/encyclopedia/';

    public function testReceiveReward_結合テスト()
    {
        // Setup
        // 時刻を固定
        $now = $this->fixTime();

        // mst
        $mstItem = MstItem::factory()->create()->toEntity();
        $mstUnitEncyclopediaRewards = MstUnitEncyclopediaReward::factory()->createMany([
            [
                'unit_encyclopedia_rank' => 1,
                'resource_type' => RewardType::EXP->value,
                'resource_id' => NULL,
                'resource_amount' => 10
            ],
            [
                'unit_encyclopedia_rank' => 2,
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItem->getId(),
                'resource_amount' => 10
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);
        $mstEmblem = MstEmblem::factory()->create()->toEntity();
        $mstUserLevelBonus = MstUserLevelBonus::factory()->create([
            'level' => 2,
            'mst_user_level_bonus_group_id' => 20
        ])->toEntity();
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => $mstUserLevelBonus->getMstUserLevelBonusGroupId(),
            'resource_type' => RewardType::EMBLEM->value,
            'resource_id' => $mstEmblem->getId(),
            'resource_amount' => 1
        ]);
        $oprProduct = OprProduct::factory()->create(['product_type' => ProductType::PACK->value])->toEntity();
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'sale_condition_value' => 2
        ])->toEntity();

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 90,
        ]);
        UsrEmblem::factory()->create(['usr_user_id' => $usrUser->getId(), 'mst_emblem_id' => $mstEmblem->getId()]);
        
        $tagetGradeLevel = 2;
        UsrUnit::factory()->create(['usr_user_id' => $usrUser->getId(), 'grade_level' => $tagetGradeLevel]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => 10
        ]);
        $this->createDiamond($usrUser->getId());

        // ユーザの図鑑ランクを取得するためにUsrUnitSummaryを作成
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'grade_level_total_count' => $tagetGradeLevel,
        ]);

        // Exercise
        $rewardIds = $mstUnitEncyclopediaRewards->map(
            fn($mstUnitEncyclopediaReward) => $mstUnitEncyclopediaReward->toEntity()->getId()
        );
        $response = $this->sendRequest('receive_reward', ['mstUnitEncyclopediaRewardIds' => $rewardIds]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $json = $response->json();

        // レスポンス内容確認
        $this->assertArrayHasKey('usrReceivedUnitEncyclopediaRewards', $json);
        foreach ($json['usrReceivedUnitEncyclopediaRewards'] as $reward) {
            $this->assertContains($reward['mstUnitEncyclopediaRewardId'], $rewardIds);
        }

        $this->assertArrayHasKey('unitEncyclopediaRewards', $json);
        $this->assertCount(2, $json['unitEncyclopediaRewards']);

        $this->assertArrayHasKey('isEmblemDuplicated', $json);
        $this->assertFalse($json['isEmblemDuplicated']);

        $this->assertArrayHasKey('usrItems', $json);
        $this->assertCount(1, $json['usrItems']);
        $this->assertEquals($mstItem->getId(), $json['usrItems'][0]['mstItemId']);
        $this->assertEquals(10 + 10, $json['usrItems'][0]['amount']);

        $this->assertArrayHasKey('usrParameter', $json);
        $usrParameter = $response->json()['usrParameter'];
        // エンブレム重複分のコインが加算されている
        $this->assertEquals(100 + 1000, $usrParameter['coin']);
        $this->assertEquals(90 + 10, $usrParameter['exp']);

        $this->assertArrayHasKey('userLevel', $json);
        $userLevel = $json['userLevel'];
        $this->assertEquals(90, $userLevel['beforeExp']);
        $this->assertEquals(90 + 10, $userLevel['afterExp']);
        $this->assertTrue($userLevel['isEmblemDuplicated']);
        $this->assertCount(1, $userLevel['usrLevelReward']);

        $this->assertArrayHasKey('usrConditionPacks', $json);
        $this->assertCount(1, $json['usrConditionPacks']);

        // DB確認
        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(2, $usrUserParameter->getLevel());
        $this->assertEquals(100 + 1000, $usrUserParameter->getCoin());
        $this->assertEquals(90 + 10, $usrUserParameter->getExp());

        $usrConditionPack = UsrConditionPack::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($mstPack->getId(), $usrConditionPack->getMstPackId());

        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())->get()->keyBy(fn($usrItem) => $usrItem->getMstItemId());
        $this->assertCount(1, $usrItems);
        $this->assertEquals(10 + 10, $usrItems->get($mstItem->getId())->getAmount());
    }

    public function test_receiveFirstCollectionReward_発見エネミー更新テスト()
    {
        // Setup
        $targetMstId = 'TEST';
        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 90,
        ]);
        $usrUserId  = $usrUser->getId();
        $this->createDiamond($usrUser->getId());
        UsrEnemyDiscovery::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => $targetMstId,],
        ]);
        $setFreeDiamond = 10;
        MstConfig::factory()->create([
            'id' => $targetMstId,
            'release_key' => 1,
            'value' => $setFreeDiamond,
            'key' => MstConfigConstant::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT,
        ]);
        $encyclopediaType = EncyclopediaType::ENEMY_DISCOVERY->value;
        
        // Exercise
        $response = $this->sendRequest('receive_first_collection_reward', ['encyclopediaType' => $encyclopediaType, 'encyclopediaId' => $targetMstId]);
        $json = $response->json();
        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $afterUsrUserParameter = UsrCurrencySummary::where('usr_user_id', $usrUser->getId())->first();
        $afterUsrEnemyDiscovery = UsrEnemyDiscovery::where(['usr_user_id' => $usrUser->getId(), 'mst_enemy_character_id' => $targetMstId])->first();

        $this->assertEquals($afterUsrUserParameter->free_amount, $setFreeDiamond);
        $this->assertEquals($afterUsrEnemyDiscovery->is_new_encyclopedia, EncyclopediaCollectStatus::IS_NOT_NEW->value); 
        $this->assertArrayHasKey('encyclopediaFirstCollectionRewards', $json);
        $this->assertCount(1, $json['encyclopediaFirstCollectionRewards']);
    }

    public function test_receiveFirstCollectionReward_エンブレム更新テスト()
    {
        // Setup
        $targetMstId = 'TEST';

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 90,
        ]);
        $usrUserId  = $usrUser->getId();
        $this->createDiamond($usrUser->getId());

        MstEmblem::factory()->create([
            'id' => $targetMstId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $targetMstId,
        ]);
        $setFreeDiamond = 10;
        MstConfig::factory()->create([
            'id' => $targetMstId,
            'release_key' => 1,
            'value' => $setFreeDiamond,
            'key' => MstConfigConstant::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT,
        ]);
        $encyclopediaType = EncyclopediaType::EMBLEM->value;

        // Exercise
        $response = $this->sendRequest('receive_first_collection_reward', ['encyclopediaType' => $encyclopediaType, 'encyclopediaId' => $targetMstId]);
        $json = $response->json();
         // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        
        $afterUsrUserParameter = UsrCurrencySummary::where('usr_user_id', $usrUser->getId())->first();
        $afterUsrEmblem = UsrEmblem::where(['usr_user_id' => $usrUser->getId(), 'mst_emblem_id' => $targetMstId])->first();

        $this->assertEquals($afterUsrUserParameter->free_amount, $setFreeDiamond);
        $this->assertEquals($afterUsrEmblem->is_new_encyclopedia, EncyclopediaCollectStatus::IS_NOT_NEW->value);
        $this->assertArrayHasKey('encyclopediaFirstCollectionRewards', $json);
        $this->assertCount(1, $json['encyclopediaFirstCollectionRewards']);
    }

    public function test_receiveFirstCollectionReward_ユニット更新テスト()
    {
        // Setup
        $targetMstId = 'TEST';

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 90,
        ]);
        $usrUserId  = $usrUser->getId();

        $this->createDiamond($usrUser->getId());
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => $targetMstId],
        ]);

        $setFreeDiamond = 10;
        MstConfig::factory()->create([
            'id' => $targetMstId,
            'release_key' => 1,
            'value' => $setFreeDiamond,
            'key' => MstConfigConstant::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT,
        ]);
        $encyclopediaType = EncyclopediaType::UNIT->value;

        // Exercise
        $response = $this->sendRequest('receive_first_collection_reward', ['encyclopediaType' => $encyclopediaType, 'encyclopediaId' => $targetMstId]);
        $json = $response->json();
         // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        
        $afterUsrUserParameter = UsrCurrencySummary::where('usr_user_id', $usrUser->getId())->first();
        $afterUsrUnit = UsrUnit::where(['usr_user_id' => $usrUser->getId(), 'mst_unit_id' => $targetMstId])->first();

        $this->assertEquals($afterUsrUserParameter->free_amount, $setFreeDiamond);
        $this->assertEquals($afterUsrUnit->is_new_encyclopedia, EncyclopediaCollectStatus::IS_NOT_NEW->value);     
        $this->assertArrayHasKey('encyclopediaFirstCollectionRewards', $json);
        $this->assertCount(1, $json['encyclopediaFirstCollectionRewards']);
    }

    public function test_receiveFirstCollectionReward_アートワーク更新テスト()
    {
        // Setup
        $targetMstId = 'TEST';

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 90,
        ]);
        $usrUserId  = $usrUser->getId();

        $this->createDiamond($usrUser->getId());

        UsrArtwork::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => $targetMstId,],
        ]);
        $setFreeDiamond = 10;
        MstConfig::factory()->create([
            'id' => $targetMstId,
            'release_key' => 1,
            'value' => $setFreeDiamond,
            'key' => MstConfigConstant::ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT,
        ]);
        $encyclopediaType = EncyclopediaType::ARTWORK->value;
        
        // Exercise
        $response = $this->sendRequest('receive_first_collection_reward', ['encyclopediaType' => $encyclopediaType, 'encyclopediaId' => $targetMstId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $json = $response->json();
        
        $afterUsrUserParameter = UsrCurrencySummary::where('usr_user_id', $usrUser->getId())->first();
        $afterUsrArtwork = UsrArtwork::where(['usr_user_id' => $usrUser->getId(), 'mst_artwork_id' => $targetMstId])->first();

        $this->assertEquals($afterUsrUserParameter->free_amount, $setFreeDiamond);
        $this->assertEquals($afterUsrArtwork->is_new_encyclopedia, EncyclopediaCollectStatus::IS_NOT_NEW->value);
        $this->assertArrayHasKey('encyclopediaFirstCollectionRewards', $json);
        $this->assertCount(1, $json['encyclopediaFirstCollectionRewards']);
    }

    /**
     * 正常系: 原画グレードアップAPIが200OKを返しレスポンスが正しい
     */
    public function test_artworkGradeUp_正常にグレードアップできる(): void
    {
        // mst
        $mstArtwork = MstArtwork::factory()->create();
        $mstItem = MstItem::factory()->create();

        $mstArtworkGradeUp = MstArtworkGradeUp::factory()->create([
            'mst_series_id' => $mstArtwork->mst_series_id,
            'rarity' => $mstArtwork->rarity,
            'grade_level' => 2,
        ]);
        MstArtworkGradeUpCost::factory()->create([
            'mst_artwork_grade_up_id' => $mstArtworkGradeUp->id,
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => $mstArtwork->id,
            'grade_level' => 1,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->id,
            'amount' => 10,
        ]);

        // Exercise
        $response = $this->sendRequest('artwork/grade_up', ['mstArtworkId' => $mstArtwork->id]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $json = $response->json();

        // usrArtworkのレスポンス確認
        $this->assertArrayHasKey('usrArtwork', $json);
        $this->assertEquals($mstArtwork->id, $json['usrArtwork']['mstArtworkId']);
        $this->assertEquals(2, $json['usrArtwork']['gradeLevel']);

        // usrItemsのレスポンス確認（コストが消費されている）
        $this->assertArrayHasKey('usrItems', $json);
        $this->assertCount(1, $json['usrItems']);
        $this->assertEquals($mstItem->id, $json['usrItems'][0]['mstItemId']);
        $this->assertEquals(0, $json['usrItems'][0]['amount']);

        // DB確認
        $updatedUsrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_artwork_id', $mstArtwork->id)
            ->first();
        $this->assertEquals(2, $updatedUsrArtwork->grade_level);

        $updatedUsrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(0, $updatedUsrItem->amount);
    }
}
