<?php

declare(strict_types=1);

namespace Feature\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\LogGacha;
use App\Domain\Gacha\Models\LogGachaAction;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Models\UsrTutorialGacha;
use App\Domain\Tutorial\UseCases\TutorialGachaConfirmUseCase;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TutorialGachaConfirmUseCaseTest extends TestCase
{
    private TutorialGachaConfirmUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(TutorialGachaConfirmUseCase::class);
    }

    private function createMstData()
    {
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 1,
                'function_name' => 'beforeGachaConfirmed',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => 'afterGachaConfirmed',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
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
        MstItem::factory()->createMany([
            ['id' => 'fragment_unit_1'],
            ['id' => 'fragment_unit_2'],
            ['id' => 'fragment_unit_3'],
        ]);
        OprGacha::factory()->create([
            'id' => 'tutorial_gacha_1',
            'gacha_type' => GachaType::TUTORIAL->value,
            'multi_draw_count' => 10,
            'start_at' => '2020-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
    }

    public function test_exec_チュートリアルガシャを確定させる()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => 'beforeGachaConfirmed',
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        $this->createMstData();

        // usr
        $rewardType = RewardType::UNIT->value;
        $prizeType = GachaPrizeType::REGULAR->value;
        $usrTutorialGacha = UsrTutorialGacha::factory()->create([
            'usr_user_id' => $usrUserId,
            'gacha_result_json' => json_encode([
                'opr_gacha_id' => 'tutorial_gacha_1',
                'result' => [
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_2', 'resource_amount' => 1, 'id' => 'prize_2', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_2', 'resource_amount' => 1, 'id' => 'prize_2', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ['resource_type' => $rewardType, 'resource_id' => 'unit_1', 'resource_amount' => 1, 'id' => 'prize_1', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                ],
                'prize_types' => array_fill(0, 10, $prizeType)
            ]),
            'confirmed_at' => null,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
        $usrTutorialGacha->refresh();
        $this->assertEquals($now->toDateTimeString(), $usrTutorialGacha->confirmed_at);

        // 変換された状態で配布されている

        // resultDataの確認
        $actuals = $resultData->gachaRewards;
        // 配布ユニット
        $gachaRewards = $actuals->groupBy->getType()->get(RewardType::UNIT->value, collect());
        $this->assertCount(3, $gachaRewards);
        $this->assertEqualsCanonicalizing(
            ['unit_1', 'unit_2', 'unit_3'],
            $gachaRewards->map->getResourceId()->toArray(),
        );
        $this->assertEqualsCanonicalizing(
            // index順にユニットを獲得しているので、初獲得時のインデックスを確認する
            [0, 4, 9],
            $gachaRewards->map->getSortOrder()->toArray(),
        );
        // 重複ユニットがキャラのかけらアイテムに変換されている
        $gachaRewards = $actuals->groupBy->getType()->get(RewardType::ITEM->value, collect());
        $this->assertCount(7, $gachaRewards);
        $this->assertEqualsCanonicalizing(
            [
                'fragment_unit_2',
                'fragment_unit_3', 'fragment_unit_3', 'fragment_unit_3',
                'fragment_unit_3', 'fragment_unit_3', 'fragment_unit_3',
            ],
            $gachaRewards->map->getResourceId()->toArray(),
        );

        // DBの確認
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->get()->keyBy('mst_item_id');
        $this->assertEquals(6 * 10, $usrItems['fragment_unit_3']->getAmount());
        $this->assertEquals(1 * 20, $usrItems['fragment_unit_2']->getAmount());

        $logGachaActions = LogGachaAction::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $logGachaActions);
        $logGachaAction = $logGachaActions->first();
        $this->assertEquals('tutorial_gacha_1', $logGachaAction->opr_gacha_id);
        $this->assertEquals(CostType::DIAMOND->value, $logGachaAction->cost_type);
        $this->assertEquals(10, $logGachaAction->draw_count);
        $this->assertEquals(0, $logGachaAction->max_rarity_upper_count);
        $this->assertEquals(0, $logGachaAction->pickup_upper_count);

        $logGacha = LogGacha::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($logGacha);
        $this->assertEquals('tutorial_gacha_1', $logGacha->opr_gacha_id);
        $this->assertEquals(CostType::DIAMOND->value, $logGacha->cost_type);
        $this->assertEquals(10, $logGacha->draw_count);
        $gachaResultJson = json_decode($usrTutorialGacha->gacha_result_json, true);
        $expected = [];
        foreach ($gachaResultJson['result'] as $index => $item) {
            $prizeType = $gachaResultJson['prize_types'][$index] ?? '';
            $expected[] = [
                'resource_type' => $item['resource_type'],
                'resource_id' => $item['resource_id'],
                'resource_amount' => $item['resource_amount'],
                'id' => $item['id'],
                'group_id' => $item['group_id'],
                'weight' => $item['weight'],
                'pickup' => $item['pickup'],
                'rarity' => $item['rarity'],
                'prize_type' => $prizeType,
                'opr_gacha_id' => $gachaResultJson['opr_gacha_id'],
                'index' => $index,
            ];
        }
        $this->assertEquals($expected, unserialize($logGacha->result));

        // キャッシュ登録検証
        $cache = $this->getFromRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId));
        $this->assertCount(1, $cache);
        $gachaHistoryArray = $cache->first()->formatToResponse();
        $this->assertEquals('tutorial_gacha_1', $gachaHistoryArray['oprGachaId']);
        $this->assertEquals(CostType::DIAMOND->value, $gachaHistoryArray['costType']);
        $this->assertNull($gachaHistoryArray['costId']);
        $this->assertEquals(0, $gachaHistoryArray['costNum']);
        $this->assertEquals(10, $gachaHistoryArray['drawCount']);
        $this->assertEquals(StringUtil::convertToISO8601($now->toDateTimeString()), $gachaHistoryArray['playedAt']);
        $this->assertCount(10, $gachaHistoryArray['results']);

        $expectedCacheResults = [
            [
                'sortOrder' => 0,
                'reward' => [
                    'resourceType' => 'Unit',
                    'resourceId' => 'unit_3',
                    'resourceAmount' => 1,
                    'preConversionResource' => null,
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 1,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 2,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 3,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 4,
                'reward' => [
                    'resourceType' => 'Unit',
                    'resourceId' => 'unit_2',
                    'resourceAmount' => 1,
                    'preConversionResource' => null,
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 5,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 6,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_2',
                    'resourceAmount' => 20,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_2',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 7,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 8,
                'reward' => [
                    'resourceType' => 'Item',
                    'resourceId' => 'fragment_unit_3',
                    'resourceAmount' => 10,
                    'preConversionResource' => [
                        'resourceType' => 'Unit',
                        'resourceId' => 'unit_3',
                        'resourceAmount' => 1,
                    ],
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
            [
                'sortOrder' => 9,
                'reward' => [
                    'resourceType' => 'Unit',
                    'resourceId' => 'unit_1',
                    'resourceAmount' => 1,
                    'preConversionResource' => null,
                    'unreceivedRewardReasonType' => 'None'
                ]
            ],
        ];
        usort($expectedCacheResults, fn ($a, $b) => $a['sortOrder'] <=> $b['sortOrder']);
        usort($gachaHistoryArray['results'], fn ($a, $b) => $a['sortOrder'] <=> $b['sortOrder']);
        $this->assertEquals($expectedCacheResults, $gachaHistoryArray['results']);
    }

    public static function params_test_exec_チュートリアルガシャの確定ができない()
    {
        return [
            'すでに確定済み' => [
                'currentTutorialStatus' => 'beforeGachaConfirmed',
                'isExistUsrTutorialGacha' => true,
                'confirmedAt' => '2037-01-01 00:00:00',
                'errorCode' => ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            ],
            'まだガシャを引いてない' => [
                'currentTutorialStatus' => 'beforeGachaConfirmed',
                'isExistUsrTutorialGacha' => false,
                'confirmedAt' => '2037-01-01 00:00:00',
                'errorCode' => ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            ],
            'チュートリアル進捗が想定外' => [
                'currentTutorialStatus' => 'afterGachaConfirmed',
                'isExistUsrTutorialGacha' => true,
                'confirmedAt' => null,
                'errorCode' => ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            ],
        ];
    }

    #[DataProvider('params_test_exec_チュートリアルガシャの確定ができない')]
    public function test_exec_チュートリアルガシャの確定ができない(
        string $currentTutorialStatus,
        bool $isExistUsrTutorialGacha,
        ?string $confirmedAt,
        int $errorCode,
    ) {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $currentTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        $this->createMstData();

        // usr
        if ($isExistUsrTutorialGacha) {
            UsrTutorialGacha::factory()->create([
                'usr_user_id' => $usrUserId,
                'gacha_result_json' => json_encode([
                    'opr_gacha_id' => 'tutorial_gacha_1',
                    'result' => [
                        ['resource_type' => RewardType::UNIT->value, 'resource_id' => 'unit_3', 'resource_amount' => 1, 'id' => 'prize_3', 'group_id' => '14', 'weight' => 1, 'pickup' => false, 'rarity' => 'R'],
                    ],
                    'prize_types' => ['Regular']
                ]),
                'confirmed_at' => $confirmedAt,
            ]);
        }

        // exception
        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
    }
}
