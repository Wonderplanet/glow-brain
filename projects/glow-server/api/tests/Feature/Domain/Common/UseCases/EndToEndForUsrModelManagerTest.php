<?php

namespace Tests\Feature\Domain\Common\UseCases;

use App\Domain\Common\Constants\System;
use Tests\Support\Entities\CurrentUser;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Models\LogBank;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Resource\Mst\Models\OprAssetRelease;
use App\Domain\Resource\Mst\Models\OprAssetReleaseVersion;
use App\Domain\Resource\Mst\Models\OprMasterReleaseControl;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Stage\Enums\StageRewardCategory;
use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

/**
 * UsrModelManagerの動作確認をするために複数のAPI実行を通して動作確認するend-to-endテストです。
 *
 * 現在(2024/02/16)あるほとんどのAPIを連続で実行して、ユーザーのプレイの流れを簡易的に再現した状態で、
 * 各ユーザーテーブルの更新が正しく行われることを確認します。
 */
class EndToEndForUsrModelManagerTest extends BaseControllerTestCase
{

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_end_to_end()
    {
        $header = [
            'Client-Version' => '0.0.0',
            System::HEADER_LANGUAGE => 'ja',
            System::HEADER_PLATFORM => 1,
            System::HEADER_ASSET_VERSION => "1.0.0",
        ];

        $mstUnitIds = ['unit1', 'unit2', 'unit3', '1', '2'];
        $this->createMasterData($mstUnitIds);

        $this->assertCount(0, UsrUserLogin::query()->get());
        $this->assertCount(0, LogBank::query()->get());

        // 現在時刻を固定
        $now = $this->fixTime('2025-02-04 15:00:00');

        /**
         * Auth
         */

        // Setup

        // Exercise
        $result = $this->sendRequest('sign_up');
        $usrUserId = UsrUser::all()->first()->getId();
        $this->setUsrUserId($usrUserId);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('id_token', $result);
        $idToken = $result['id_token'];

        $usrUser = UsrUser::query()->first();
        $this->assertNotNull($usrUser);

        // ユーザーの初期データを用意
        $usrUserId = $usrUser->getId();
        $user = new CurrentUser($usrUserId);
        $this->setUsrUserId($usrUserId);

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserParameter);

        $usrUserProfile = UsrUserProfile::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserProfile);
        $this->assertEquals('', $usrUserProfile->getMstUnitId());

        $this->assertEquals(
            $now->toDateTimeString(),
            UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first()?->getHourlyAccessedAt()
        );
        $this->assertCount(1, LogBank::query()->get());


        // api/sign_in

        // Setup

        // Exercise
        $result = $this->sendRequest('sign_in', ['id_token' => $idToken]);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('access_token', $result);

        $header['Access-Token'] = $result['access_token'];

        $this->assertEquals(
            $now->toDateTimeString(),
            UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first()?->getHourlyAccessedAt()
        );
        $this->assertCount(1, LogBank::query()->get());

        /**
         * Game (login sequence)
         */

        // api/game/server_time

        // Setup

        // Exercise
        $result = $this->withHeaders($header)
            ->sendGetRequest('game/server_time');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);


        // api/game/version

        // Setup

        // Exercise
        $result = $this->withHeaders($header)
            ->sendGetRequest('game/version');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertEquals(
            $now->toDateTimeString(),
            UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first()?->getHourlyAccessedAt()
        );
        $this->assertCount(1, LogBank::query()->get());


        // api/game/update_and_fetch

        // Setup

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('game/update_and_fetch');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);


        // api/game/fetch

        // Setup

        // Exercise
        $result = $this->withHeaders($header)
            ->sendGetRequest('game/fetch');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        /**
         * Stage
         */

        // api/stage/start

        $mstStageId = '1';

        // Setup

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('stage/start', ['mstStageId' => $mstStageId, 'partyNo' => 0, 'isChallengeAd' => false]);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $usrStage = UsrStage::query()->where('mst_stage_id', $mstStageId)->first();
        $this->assertNotNull($usrStage);

        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstStageId, $usrStageSession->getMstStageId());
        $this->assertEquals(StageSessionStatus::STARTED, $usrStageSession->getIsValid());


        // api/stage/continue_diamond

        // Setup
        $this->createDiamond($usrUserId, freeDiamond: 150, paidDiamondIos: 50);
        $diamond = $this->getDiamond($usrUserId);
        $beforeDiamond = $diamond->getFreeAmount();

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('stage/continue_diamond', ['mstStageId' => $mstStageId]);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($beforeDiamond - StageConstant::CONTINUE_DIAMOND_COST, $diamond->getFreeAmount());


        // api/stage/end

        // Setup

        $usrUserParameter->refresh();
        $beforeExp = $usrUserParameter->getExp();
        $beforeCoin = $usrUserParameter->getCoin();
        $beforeStamina = $usrUserParameter->getStamina();

        // Exercise
        $param = [
            'mstStageId' => $mstStageId,
            'inGameBattleLog' => [
                'partyStatus' => [
                    [
                        'usrUnitId' => 'usrUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                        'hp' => 1,
                        'atk' => 1,
                        'moveSpeed' => 1,
                        'summonCost' => 1,
                        'summonCoolTime' => 1,
                        'damageKnockBackCount' => 1,
                        'specialAttackMstAttackId' => '1001',
                        'attackDelay' => 1,
                        'nextAttackInterval' => 1,
                        'mstUnitAbility1' => '2001',
                        'mstUnitAbility2' => '3001',
                        'mstUnitAbility3' => '4001',
                    ]
                ],
            ],
        ];
        $result = $this->withHeaders($header)
            ->sendRequest('stage/end', $param);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        /** @var UsrStage $usrStage */
        $usrStage = UsrStage::query()->where('mst_stage_id', $mstStageId)->first();
        $this->assertNotNull($usrStage);
        $this->assertEquals(1, $usrStage->clear_count);

        $usrStageSession = UsrStageSession::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());

        $usrUserParameter->refresh();


        /**
         * User
         */

        // api/user/change_name

        // Setup

        $now = $this->fixTime('2025-02-04 15:10:00');

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('user/change_name', ['name' => 'newName']);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $usrUserProfile->refresh();
        $this->assertEquals('newName', $usrUserProfile->getName());

        $this->assertEquals(
            '2025-02-04 15:00:00',
            UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first()?->getHourlyAccessedAt()
        );
        $this->assertCount(1, LogBank::query()->get());

        /**
         * Shop
         */

        // api/shop/trade_shop_item

        // Setup
        $mstShopItemId = '1';

        $usrUserParameter->refresh();
        $beforeCoin = $usrUserParameter->getCoin();

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('shop/trade_shop_item', ['mstShopItemId' => $mstShopItemId]);

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $usrShopItem = UsrShopItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_item_id', $mstShopItemId)
            ->first();
        $this->assertNotNull($usrShopItem);
        $this->assertEquals(1, $usrShopItem->getTradeCount());

        $usrUserParameter->refresh();
        $this->assertEquals($beforeCoin - 100, $usrUserParameter->getCoin());

        $usrItem1 = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'item1')
            ->first();
        $this->assertEquals(50, $usrItem1->getAmount());

        /**
         * IdleIncentive
         */

        // api/idle_incentive/receive

        // 放置時間を30分にするために、固定時間を30分進める
        $now = $this->fixTime('2025-02-04 15:30:00');
        $nowDateTimeString = $now->toDateTimeString();

        // Setup
        $usrIdleIncentive = UsrIdleIncentive::query()->where('usr_user_id', $usrUserId)->first();
        // game/update_and_fetchで生成済みの想定
        $this->assertNotNull($usrIdleIncentive);

        // 放置開始時刻を30分前に設定して報酬が受け取れるようにする
        $usrIdleIncentive->idle_started_at = '2025-02-04 15:00:00';
        $usrIdleIncentive->save();

        $usrUserParameter->refresh();
        $beforeCoin = $usrUserParameter->getCoin();
        $beforeExp = $usrUserParameter->getExp();

        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('idle_incentive/receive');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $usrIdleIncentive->refresh();
        $this->assertEquals($nowDateTimeString, $usrIdleIncentive->getIdleStartedAt());

        $usrUserParameter->refresh();
        $this->assertTrue($beforeCoin < $usrUserParameter->getCoin());
        $this->assertTrue($beforeExp < $usrUserParameter->getExp());

        $usrItem2 = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'item2')
            ->first();
        $this->assertNotNull($usrItem2);
        $this->assertTrue(0 < $usrItem2->getAmount());

        $this->assertCount(1, LogBank::query()->get());


        // api/idle_incentive/quick_receive_by_diamond

        // Setup
        $usrUserParameter->refresh();
        $beforeCoin = $usrUserParameter->getCoin();
        $beforeExp = $usrUserParameter->getExp();

        $now = $this->fixTime('2025-02-04 16:00:01');

        $usrItem2->refresh();
        $beforeItem2Amount = $usrItem2->getAmount();

        $diamond = $this->getDiamond($usrUserId);
        $beforeDiamond = $diamond->getFreeAmount();


        // Exercise
        $result = $this->withHeaders($header)
            ->sendRequest('idle_incentive/quick_receive_by_diamond');

        // Verify
        $result->assertStatus(HttpStatusCode::SUCCESS);

        $usrIdleIncentive->refresh();
        $this->assertEquals($nowDateTimeString, $usrIdleIncentive->getIdleStartedAt());

        $usrUserParameter->refresh();
        $this->assertTrue($beforeCoin < $usrUserParameter->getCoin());
        $this->assertTrue($beforeExp < $usrUserParameter->getExp());

        $usrItem1->refresh();
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($beforeDiamond - 15 , $diamond->getFreeAmount());

        $usrItem2->refresh();
        $this->assertTrue($beforeItem2Amount < $usrItem2->getAmount());

        $this->assertEquals(
            '2025-02-04 16:00:01',
            UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first()?->getHourlyAccessedAt()
        );
        $this->assertCount(2, LogBank::query()->get());
    }

    private function createMasterData(array $mstUnitIds): void
    {
        // Set idle incentive initial reward stage id
        MstConfig::factory()->create([
            'key' => MstConfigConstant::IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID,
            'value' => '1',
        ]);

        $data = [
            MngMasterReleaseVersion::class => [
                [
                    'id' => 1,
                    'release_key' => 1,
                    'git_revision' => 'test1',
                    'master_schema_version' => '1.0.0',
                    'data_hash' => 'test1',
                    'server_db_hash' => 'test1',
                    'client_mst_data_hash' => 'test1',
                    'client_mst_data_i18n_ja_hash' => 'test1',
                    'client_mst_data_i18n_en_hash' => 'test1',
                    'client_mst_data_i18n_zh_hash' => 'test1',
                    'client_opr_data_hash' => 'test1',
                    'client_opr_data_i18n_ja_hash' => 'test1',
                    'client_opr_data_i18n_en_hash' => 'test1',
                    'client_opr_data_i18n_zh_hash' => 'test1',
                    'created_at' => '2023-12-01 00:00:00',
                    'updated_at' => '2023-12-01 00:00:00',
                ],
            ],
            MngMasterRelease::class => [
                [
                    'id' => 1,
                    'release_key' => 1,
                    'enabled' => 1,
                    'target_release_version_id' => 1,
                    'client_compatibility_version' => '0.0.0',
                    'description' => 'test1',
                    'created_at' => '2023-12-01 00:00:00',
                    'updated_at' => '2023-12-01 00:00:00',
                ],
            ],
            MngAssetRelease::class => [
                [
                    'id' => '1',
                    'release_key' => 1,
                    'platform' => UserConstant::PLATFORM_IOS,
                    'enabled' => 1,
                    'target_release_version_id' => "1",
                    'client_compatibility_version' => '0.0.0',
                    'created_at' => "2023-12-30 00:00:00",
                ],
            ],
            MngAssetReleaseVersion::class => [
                [
                    'id' => '1',
                    'release_key' => 1,
                    'git_revision' => 'test1',
                    'git_branch' => 'test1',
                    'catalog_hash' => 'test1',
                    'platform' => UserConstant::PLATFORM_IOS,
                    'build_client_version' => '0.0.0',
                    'asset_total_byte_size' => 100,
                    'catalog_byte_size' => 100,
                    'catalog_file_name' => 'test1',
                    'catalog_hash_file_name' => 'test1',
                    'created_at' => '2023-12-30 00:00:00',
                ],
            ],
            MstUnit::class => [
                ...collect($mstUnitIds)->map(function ($id) {
                    return ['id' => $id, 'fragment_mst_item_id' => $id,];
                })->toArray(),
            ],
            MstItem::class => [
                ...collect($mstUnitIds)->map(function ($id) {
                    return ['id' => 'item'.(string)$id, 'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07'];
                })->toArray(),
                [
                    'id' => 'rank_up_material',
                    'type' => ItemType::RANK_UP_MATERIAL->value,
                    'effect_value' => UnitColorType::COLORLESS->value
                ],
            ],
            MstStage::class => [
                [
                    'id' => '1', 'prev_mst_stage_id' => null, 'mst_quest_id' => '1', 'sort_order' => 1, 'cost_stamina' => 5,
                ],
            ],
            MstStageReward::class => [
                [
                    'id' => '1', 'mst_stage_id' => '1', 'reward_category' => StageRewardCategory::ALWAYS->value,
                    'resource_type' => RewardType::COIN->value, 'resource_id' => null, 'resource_amount' => 999, 'percentage' => 100,
                ],
            ],
            MstQuest::class => [
                ['id' => '1', 'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07',],
            ],
            MstShopItem::class => [
                ['id' => '1', 'is_first_time_free' => 0, 'cost_type' => 'coin', 'cost_amount' => 100,
                'resource_type' => 'item', 'resource_id' => 'item1', 'resource_amount' => 50,
                'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07',],
            ],
            MstUserLevel::class => [
                ['id' => '1', 'level' => 1, 'stamina' => 10, 'exp' => 100],
                ['id' => '2', 'level' => 2, 'stamina' => 11, 'exp' => 200],
                ['id' => '3', 'level' => 3, 'stamina' => 12, 'exp' => 300],
                ['id' => '4', 'level' => 4, 'stamina' => 13, 'exp' => 400],
                ['id' => '5', 'level' => 5, 'stamina' => 14, 'exp' => 500],
                ['id' => '6', 'level' => 6, 'stamina' => 15, 'exp' => 600],
                ['id' => '7', 'level' => 7, 'stamina' => 16, 'exp' => 700],
                ['id' => '8', 'level' => 8, 'stamina' => 17, 'exp' => 800],
                ['id' => '9', 'level' => 9, 'stamina' => 18, 'exp' => 900],
                ['id' => '10', 'level' => 10, 'stamina' => 19, 'exp' => 10000],
            ],
            MstUserLevelBonus::class => [
                ['id' => '1', 'level' => 1, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '2', 'level' => 2, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '3', 'level' => 3, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '4', 'level' => 4, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '5', 'level' => 5, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '6', 'level' => 6, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '7', 'level' => 7, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '8', 'level' => 8, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '9', 'level' => 9, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
                ['id' => '10', 'level' => 10, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
            ],
            MstUserLevelBonusGroup::class => [
                ['id' => '1', 'mst_user_level_bonus_group_id' => '1', 'resource_type' => 'free_diamond', 'resource_id' => '1', 'resource_amount' => 1, 'release_key' => 1],
            ],

            // IdleIncentive
            MstIdleIncentive::class => [
                [
                    'id' => '1', 'initial_reward_receive_minutes' => 10, 'max_idle_hours' => 100, 'reward_increase_interval_minutes' => 10,
                    'required_quick_receive_diamond_amount' => 15, 'quick_idle_minutes' => 120,
                ],
            ],
            MstIdleIncentiveReward::class => [
                ['id' => '1', 'mst_stage_id' => '1', 'base_coin_amount' => 10, 'base_exp_amount' => 5,
                'mst_idle_incentive_item_group_id' => '1'],
            ],
            MstIdleIncentiveItem::class => [
                ['id' => '1', 'mst_idle_incentive_item_group_id' => '1', 'mst_item_id' => 'item2', 'base_amount' => 30],
            ],
        ];

        foreach ($data as $class => $rows) {
            if (class_exists($class) === false) {
                continue;
            }
            foreach ($rows as $row) {
                $model = $class::find($row['id']);

                if (is_null($model)) {
                    $class::factory()->create($row);
                } else {
                    $model->update($row);
                }
            }
        }
    }
}
