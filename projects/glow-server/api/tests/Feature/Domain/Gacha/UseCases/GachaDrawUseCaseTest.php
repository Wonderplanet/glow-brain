<?php

namespace Tests\Feature\Domain\Gacha\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Gacha\Repositories\UsrGachaRepository;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class GachaDrawUseCaseTest extends TestCase
{
    private GachaDrawUseCase $useCase;
    private CurrencyDelegator $currencyDelegator;
    private UsrGachaRepository $usrGachaRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(GachaDrawUseCase::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->usrGachaRepository = $this->app->make(UsrGachaRepository::class);
    }

    private function createBaseData()
    {
        $fragmentMstItemId = 'fragment1';
        $mstItems = MstItem::factory(10)->create()->map(fn($mstItem) => $mstItem->toEntity());
        MstItem::factory()->createMany([
            ['id' => 'ticket_item'],
            ['id' => $fragmentMstItemId],
        ]);

        $mstUnits = MstUnit::factory(10)
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->map(fn($mstUnit) => $mstUnit->toEntity());

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'language' => Language::Ja->value,
        ]);

        $gachaPrizeList = [];
        foreach ($mstItems as $mstItem) {
            $gachaPrizeList[] = [
                'group_id' => 'prize_group_id',
                'resource_type' => RewardType::ITEM,
                'resource_id' => $mstItem->getId()
            ];
        }
        foreach ($mstUnits as $mstUnit) {
            $gachaPrizeList[] = [
                'group_id' => 'prize_group_id',
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId()
            ];
        }
        OprGachaPrize::factory()->createMany($gachaPrizeList);
    }

    private function createNormalGachaData()
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::NORMAL->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::AD,
                'cost_id' => '',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => 'ticket_item',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
        ]);
    }

    public function createNormalGachaUnlimitedData()
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::NORMAL->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => null,
            'total_play_limit_count' => null,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::AD,
                'cost_id' => '',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => 'ticket_item',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
        ]);
    }

    private function createPremiumGachaData(GachaType $gachaType = GachaType::PREMIUM)
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => $gachaType->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::AD,
                'cost_id' => null,
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => 'ticket_item',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => '',
                'cost_num' => 300,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => '',
                'cost_num' => 2800,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
        ]);
    }

    private function createPremiumUpperGachaData(): void
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PREMIUM->value,
            'upper_group' => 'Premium',
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 0,
            'prize_group_id' => 'prize_group_id',
            'fixed_prize_group_id' => null,
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);
        OprGachaUpper::factory()->create([
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 100,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 100,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 1000,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
        ]);

        // 天井排出用ユニットの追加
        $mstUnitId = MstUnit::factory()
            ->create(['fragment_mst_item_id' => 'fragment1', 'rarity' => RarityType::UR->value])
            ->toEntity()
            ->getId();
        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnitId,
            'pickup' => 0
        ]);
    }

    private function createPremiumFixedGachaForItemCostData(string $itemId): string
    {
        $this->createBaseData();

        $fixedPrizeGroupId = 'fixed_prize_group1';
        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PREMIUM->value,
            'upper_group' => 'None',
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 1,
            'prize_group_id' => 'prize_group_id',
            'fixed_prize_group_id' => $fixedPrizeGroupId,
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => $itemId,
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
        ]);
        // 確定枠排出用ユニットを追加する
        $fixedMstUnitId = MstUnit::factory()
            ->create(['fragment_mst_item_id' => 'fragment1'])
            ->toEntity()
            ->getId();
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $fixedMstUnitId,
            'weight' => 1,
        ]);
        return $fixedMstUnitId;
    }

    private function createPremiumFixedGachaData(): string
    {
        $this->createBaseData();

        $fixedPrizeGroupId = 'fixed_prize_group1';
        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PREMIUM->value,
            'upper_group' => 'None',
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 1,
            'prize_group_id' => 'prize_group_id',
            'fixed_prize_group_id' => $fixedPrizeGroupId,
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 100,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 900,
                'draw_count' => 9,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 1000,
                'draw_count' => 10,
                'cost_priority' => 3,
            ],
        ]);
        // 確定枠排出用ユニットを追加する
        $fixedMstUnitId = MstUnit::factory()
            ->create(['fragment_mst_item_id' => 'fragment1'])
            ->toEntity()
            ->getId();
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $fixedMstUnitId,
            'weight' => 1,
        ]);
        return $fixedMstUnitId;
    }

    private function createPickupGachaData()
    {
        // プレミアムと同じデータ構造なので
        $this->createPremiumGachaData(GachaType::PICKUP);
    }

    private function createPickupUpperGachaData(): void
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PICKUP->value,
            'upper_group' => 'Pickup',
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 0,
            'prize_group_id' => 'prize_group_id',
            'fixed_prize_group_id' => null,
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);
        OprGachaUpper::factory()->createMany([
            [
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 100,
            ],
            [
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::PICKUP->value,
                'count' => 200,
            ]
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 100,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 1000,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
        ]);

        // 天井排出用ユニットの追加
        $mstUnitIds = MstUnit::factory()
            ->createMany([
                ['fragment_mst_item_id' => 'fragment1', 'rarity' => RarityType::UR->value],
                ['fragment_mst_item_id' => 'fragment1', 'rarity' => RarityType::UR->value]
            ])
            ->map(fn($mstUnit) => $mstUnit->toEntity()->getId());
        OprGachaPrize::factory()->createMany([
            [
                'group_id' => 'prize_group_id',
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnitIds->first(),
                'pickup' => 0
            ],
            [
                'group_id' => 'prize_group_id',
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnitIds->last(),
                'pickup' => 1
            ]
        ]);
    }

    private function createFestivalGachaData()
    {
        // プレミアムと同じデータ構造なので
        $this->createPremiumGachaData(GachaType::FESTIVAL);
    }

    private function createTicketGachaData()
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::TICKET,
            'enable_ad_play' => false,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => null,
            'daily_play_limit_count' => null,
            'total_play_limit_count' => null,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => 'ticket_item',
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
        ]);
    }

    private function createFreeGachaData()
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::FREE,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => null,
            'daily_play_limit_count' => 3,
            'total_play_limit_count' => 10,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::FREE,
                'cost_id' => null,
                'cost_num' => 1,
                'draw_count' => 1,
                'cost_priority' => 1,
            ],
        ]);
    }

    private function createPaidOnlyGachaData()
    {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PAID_ONLY->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
            'ad_play_interval_time' => null,
            'daily_play_limit_count' => null,
            'total_play_limit_count' => null,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::ITEM,
                'cost_id' => 'ticket_item',
                'cost_num' => 1,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::PAID_DIAMOND,
                'cost_id' => null,
                'cost_num' => 1500,
                'draw_count' => 10,
                'cost_priority' => 2,
            ],
        ]);
    }

    private function createGachaData(GachaType $gachaType)
    {
        switch ($gachaType->value) {
            case GachaType::NORMAL->value:
                $this->createNormalGachaData();
                break;
            case GachaType::PREMIUM->value:
                $this->createPremiumGachaData();
                break;
            case GachaType::PICKUP->value:
                $this->createPickupGachaData();
                break;
            case GachaType::FREE->value:
                $this->createFreeGachaData();
                break;
            case GachaType::TICKET->value:
                $this->createTicketGachaData();
                break;
            case GachaType::FESTIVAL->value:
                $this->createFestivalGachaData();
                break;
            case GachaType::PAID_ONLY->value:
                $this->createPaidOnlyGachaData();
                break;
            default:
                break;
        }
    }

    public static function params_一次通貨で1連を引くことができる()
    {
        return [
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
        ];
    }

    /**
     * @dataProvider params_一次通貨で1連を引くことができる
     */
    public function testExec_一次通貨で1連を引くことができる(GachaType $gachaType)
    {
        $now = $this->fixTime();
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $this->assertEquals(1, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertNull($gachaDrawResultData->usrGacha->getAdPlayedAt());
        $this->assertEquals($now->toDateTimeString(), $gachaDrawResultData->usrGacha->getPlayedAt());
        $this->assertEquals(4700, $currencySummary->getTotalAmount());// 5000 - 300

        // DB確認
        $usrGacha = UsrGacha::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('opr_gacha_id', 'opr_gacha_id')
            ->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(1, $usrGacha->count);
        $this->assertEquals(1, $usrGacha->daily_count);
        $this->assertEquals(0, $usrGacha->ad_count);
        $this->assertEquals(0, $usrGacha->ad_daily_count);

        // キャッシュ登録チェック
        $cache = $this->getFromRedis(CacheKeyUtil::getGachaHistoryKey($usrUser->getId()));
        $this->assertCount(1, $cache);
        $gachaHistoryArray = $cache->first()->formatToResponse();
        $this->assertEquals('opr_gacha_id', $gachaHistoryArray['oprGachaId']);
        $this->assertEquals(CostType::DIAMOND->value, $gachaHistoryArray['costType']);
        $this->assertNull($gachaHistoryArray['costId']);
        $this->assertEquals(300, $gachaHistoryArray['costNum']);
        $this->assertEquals(1, $gachaHistoryArray['drawCount']);
        $this->assertEquals(StringUtil::convertToISO8601($now->toDateTimeString()), $gachaHistoryArray['playedAt']);
        $this->assertCount(1, $gachaHistoryArray['results']);
    }

    public static function params_一次通貨で10連を引くことができる()
    {
        return [
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
        ];
    }

    /**
     * @dataProvider params_一次通貨で10連を引くことができる
     */
    public function testExec_一次通貨で10連を引くことができる(GachaType $gachaType)
    {
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            10,
            null,
            2800,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $this->assertEquals(10, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(2200, $currencySummary->getTotalAmount());// 5000 - 2800
    }

    public static function params_チケットで1連を引くことができる()
    {
        return [
            'ノーマルガシャ' => [GachaType::NORMAL],
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
            'チケットガシャ' => [GachaType::TICKET],
        ];
    }

    /**
     * @dataProvider params_チケットで1連を引くことができる
     */
    public function testExec_チケットで1連を引くことができる(GachaType $gachaType)
    {
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $usrItem = UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            'ticket_item',
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $usrItem->refresh();
        $this->assertEquals(1, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(99, $usrItem->getAmount());// 100 - 1
        $this->assertEquals(5000, $currencySummary->getTotalAmount());// 消費されてないこと
    }

    public static function params_チケットで10連を引くことができる()
    {
        return [
            'ノーマルガシャ' => [GachaType::NORMAL],
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
            'チケットガシャ' => [GachaType::TICKET],
        ];
    }

    /**
     * @dataProvider params_チケットで10連を引くことができる
     */
    public function testExec_チケットで10連を引くことができる(GachaType $gachaType)
    {
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $usrItem = UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            10,
            'ticket_item',
            10,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $usrItem->refresh();
        $this->assertEquals(10, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(90, $usrItem->getAmount());// 100 - 10
        $this->assertEquals(5000, $currencySummary->getTotalAmount());// 消費されてないこと
    }

    public static function params_チケットで5連を引くことができる()
    {
        return [
            'ノーマルガシャ' => [GachaType::NORMAL],
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
            'チケットガシャ' => [GachaType::TICKET],
        ];
    }

    /**
     * @dataProvider params_チケットで5連を引くことができる
     */
    public function testExec_チケットで5連を引くことができる(GachaType $gachaType)
    {
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $usrItem = UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            5,
            'ticket_item',
            5,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $usrItem->refresh();
        $this->assertEquals(5, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(5, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(5, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(95, $usrItem->getAmount());// 100 - 5
        $this->assertEquals(5000, $currencySummary->getTotalAmount());// 消費されてないこと
    }

    public static function params_広告でガシャを引くことができる()
    {
        return [
            'ノーマルガシャ' => [GachaType::NORMAL],
            'プレミアムガシャ' => [GachaType::PREMIUM],
            'ピックアップガシャ' => [GachaType::PICKUP],
            'フェスガシャ' => [GachaType::FESTIVAL],
        ];
    }

    /**
     * @dataProvider params_広告でガシャを引くことができる
     */
    public function testExec_広告でガシャを引くことができる(GachaType $gachaType)
    {
        $now = $this->fixTime();
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );

        $this->assertEquals(1, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals($now->toDateTimeString(), $gachaDrawResultData->usrGacha->getAdPlayedAt());
        $this->assertEquals($now->toDateTimeString(), $gachaDrawResultData->usrGacha->getPlayedAt());

        // DB確認
        $usrGacha = UsrGacha::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('opr_gacha_id', 'opr_gacha_id')
            ->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(1, $usrGacha->count);
        $this->assertEquals(1, $usrGacha->daily_count);
        $this->assertEquals(1, $usrGacha->ad_count);
        $this->assertEquals(1, $usrGacha->ad_daily_count);

        // log_ad_free_playsの確認
        $this->assertDatabaseHas('log_ad_free_plays', [
            'usr_user_id' => $usrUser->getId(),
            'content_type' => ContentType::GACHA->value,
            'target_id' => 'opr_gacha_id',
            'play_at' => $now->toDateTimeString(),
        ]);
    }

    public function testExec_無料でガシャを引くことができる()
    {
        $this->createGachaData(GachaType::FREE);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        $this->assertEquals(1, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(1, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
    }

    public static function params_testExec_有償限定ガシャを有償一次通貨で引くことができる()
    {
        return [
            'iOSの有償通貨のみで引く' => [
                'paidDiamondIos' => 1500,
                'paidDiamondAndroid' => 0,
                'paidDiamondWebstore' => 0,
                'platform' => UserConstant::PLATFORM_IOS,
                'billingPlatform' => CurrencyConstants::PLATFORM_APPSTORE,
            ],
            'Androidの有償通貨のみで引く' => [
                'paidDiamondIos' => 0,
                'paidDiamondAndroid' => 1500,
                'paidDiamondWebstore' => 0,
                'platform' => UserConstant::PLATFORM_ANDROID,
                'billingPlatform' => CurrencyConstants::PLATFORM_GOOGLEPLAY,
            ],
            'WebStoreの有償通貨のみで引く(iOS)' => [
                'paidDiamondIos' => 0,
                'paidDiamondAndroid' => 0,
                'paidDiamondWebstore' => 1500,
                'platform' => UserConstant::PLATFORM_IOS,
                'billingPlatform' => CurrencyConstants::PLATFORM_APPSTORE,
            ],
            'WebStoreの有償通貨のみで引く(Android)' => [
                'paidDiamondIos' => 0,
                'paidDiamondAndroid' => 0,
                'paidDiamondWebstore' => 1500,
                'platform' => UserConstant::PLATFORM_ANDROID,
                'billingPlatform' => CurrencyConstants::PLATFORM_GOOGLEPLAY,
            ],
            'iOSとWebStoreの有償通貨合算で引く' => [
                'paidDiamondIos' => 750,
                'paidDiamondAndroid' => 0,
                'paidDiamondWebstore' => 750,
                'platform' => UserConstant::PLATFORM_IOS,
                'billingPlatform' => CurrencyConstants::PLATFORM_APPSTORE,
            ],
            'AndroidとWebStoreの有償通貨合算で引く' => [
                'paidDiamondIos' => 0,
                'paidDiamondAndroid' => 750,
                'paidDiamondWebstore' => 750,
                'platform' => UserConstant::PLATFORM_ANDROID,
                'billingPlatform' => CurrencyConstants::PLATFORM_GOOGLEPLAY,
            ],
        ];
    }

    #[DataProvider('params_testExec_有償限定ガシャを有償一次通貨で引くことができる')]
    public function testExec_有償限定ガシャを有償一次通貨で引くことができる(
        int $paidDiamondIos,
        int $paidDiamondAndroid,
        int $paidDiamondWebstore,
        int $platform,
        string $billingPlatform
    ) {
        $this->createGachaData(GachaType::PAID_ONLY);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createDiamond($usrUser->getId(), 0, $paidDiamondIos, $paidDiamondAndroid, $paidDiamondWebstore);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            10,
            null,
            1500,
            $platform,
            $billingPlatform,
            CostType::PAID_DIAMOND
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($usrUser->getId());
        $this->assertEquals(10, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(0, $currencySummary->getTotalAmount());
    }

    public function testExec_有償限定ガシャを補填チケットで引くことができる()
    {
        $this->createGachaData(GachaType::PAID_ONLY);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $usrItem = UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $gachaDrawResultData = $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            10,
            'ticket_item',
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );

        $usrItem->refresh();
        $this->assertEquals(10, $gachaDrawResultData->gachaRewards->count());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getCount());
        $this->assertEquals(10, $gachaDrawResultData->usrGacha->getDailyCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdCount());
        $this->assertEquals(0, $gachaDrawResultData->usrGacha->getAdDailyCount());
        $this->assertEquals(99, $usrItem->getAmount());// 100 - 1
    }

    public function testExec_ガシャの既に引いている数がクライアントと違う(): void
    {
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_DREW_COUNT_DIFFERENT);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            1,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    public function testExec_広告によるガシャ実行インターバル中(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->toDateTimeString(),
            'played_at' => $now->toDateTimeString(),
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_CANNOT_AD_INTERVAL_DRAW);
        $this->expectExceptionMessage('gacha cannot ad draw time');
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    public function testExec_ガシャ実行デイリー回数制限中(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'count' => 30,
            'daily_count' => 30,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_PLAY_LIMIT);
        $this->expectExceptionMessage('gacha cannot ad draw daily count');
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            30,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExec_ガシャ実行デイリー回数制限直前まで引けること(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'count' => 29,
            'daily_count' => 29,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            29,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    public function testExec_ガシャ実行回数制限中(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'count' => 100,
            'daily_count' => 0,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_PLAY_LIMIT);
        $this->expectExceptionMessage('gacha cannot draw total count');
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            100,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExec_ガシャ実行回数制限直前まで引けること(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'count' => 99,
            'daily_count' => 0,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            99,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    public function testExec_広告によるガシャ実行デイリー回数制限中(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->subHours(1)->toDateTimeString(),
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'ad_count' => 3,
            'ad_daily_count' => 3,
            'count' => 3,
            'daily_count' => 3,
        ]);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_CANNOT_AD_LIMIT_DRAW);
        $this->expectExceptionMessage('gacha cannot ad draw daily count');
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            3,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExec_回数無制限のガシャが引けること(): void
    {
        $now = $this->fixTime();
        $this->createNormalGachaUnlimitedData(GachaType::NORMAL);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->subHours(1)->toDateTimeString(),
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'ad_count' => 1000,
            'ad_daily_count' => 1000,
            'count' => 1000,
            'daily_count' => 1000,
        ]);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            1000,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExec_広告によるガシャ実行デイリー回数制限直前まで引けること(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->subHours(1)->toDateTimeString(),
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'ad_count' => 2,
            'ad_daily_count' => 2,
            'count' => 2,
            'daily_count' => 2,
        ]);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            2,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    public function testExec_広告によるガシャ実行回数制限中(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->subHours(1)->toDateTimeString(),
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'ad_count' => 10,
            'ad_daily_count' => 0,
            'count' => 10,
            'daily_count' => 0,
        ]);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_CANNOT_AD_LIMIT_DRAW);
        $this->expectExceptionMessage('gacha cannot ad draw total count');
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            10,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExec_広告によるガシャ実行回数制限直前まで引けること(): void
    {
        $now = $this->fixTime();
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'ad_played_at' => $now->subHours(1)->toDateTimeString(),
            'played_at' => $now->subHours(1)->toDateTimeString(),
            'ad_count' => 9,
            'ad_daily_count' => 0,
            'count' => 9,
            'daily_count' => 0,
        ]);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            9,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::AD
        );
    }

    public function testExec_不正なコストによる引き方(): void
    {
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_UNJUST_COSTS);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    /**
     * @return array
     */
    public static function params_許可されてない引き方(): array
    {
        return [
            'ノーマルガシャを一次通貨で引く' => [GachaType::NORMAL, CostType::DIAMOND],
            'ノーマルガシャを有償一次通貨のみで引く' => [GachaType::NORMAL, CostType::PAID_DIAMOND],
            'ノーマルガシャを無料で引く' => [GachaType::NORMAL, CostType::FREE],
            'プレミアムガシャを有償一次通貨のみで引く' => [GachaType::PREMIUM, CostType::PAID_DIAMOND],
            'プレミアムガシャを無料で引く' => [GachaType::PREMIUM, CostType::FREE],
            'ピックアップガシャを有償一次通貨のみで引く' => [GachaType::PICKUP, CostType::PAID_DIAMOND],
            'ピックアップガシャを無料で引く' => [GachaType::PICKUP, CostType::FREE],
            '無料ガシャを一次通貨で引く' => [GachaType::FREE, CostType::DIAMOND],
            '無料ガシャを有償一次通貨のみで引く' => [GachaType::FREE, CostType::PAID_DIAMOND],
            '無料ガシャをチケットで引く' => [GachaType::FREE, CostType::ITEM],
            '無料ガシャを広告で引く' => [GachaType::FREE, CostType::AD],
            'チケットガシャを一次通貨で引く' => [GachaType::TICKET, CostType::DIAMOND],
            'チケットガシャを有償一次通貨のみで引く' => [GachaType::TICKET, CostType::PAID_DIAMOND],
            'チケットガシャを無料で引く' => [GachaType::TICKET, CostType::FREE],
            'チケットガシャを広告で引く' => [GachaType::TICKET, CostType::AD],
            'フェスガシャを有償一次通貨のみで引く' => [GachaType::FESTIVAL, CostType::PAID_DIAMOND],
            'フェスガシャを無料で引く' => [GachaType::FESTIVAL, CostType::FREE],
            '有償限定ガシャを一次通貨で引く' => [GachaType::PAID_ONLY, CostType::DIAMOND],
            '有償限定ガシャを無料で引く' => [GachaType::PAID_ONLY, CostType::FREE],
            '有償限定ガシャを広告で引く' => [GachaType::PAID_ONLY, CostType::AD],
        ];
    }

    /**
     * @dataProvider params_許可されてない引き方
     */
    public function testExec_許可されてない引き方(GachaType $gachaType, CostType $costType): void
    {
        $this->createGachaData($gachaType);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 5000, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_TYPE_UNEXPECTED);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $costType
        );
    }

    /**
     * @return array
     */
    public static function params_想定外のN連指定(): array
    {
        return [
            '0以下を指定' => [0],
            'multi_draw_countより大きい値を指定' => [11],
        ];
    }

    /**
     * @dataProvider params_想定外のN連指定
     */
    public function testExec_想定外のN連指定(int $playNum): void
    {
        $this->createGachaData(GachaType::PREMIUM);
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUser->getId()]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'ticket_item',
            'amount' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 5000, 0, 0);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_NOT_EXPECTED_PLAY_NUM);
        $this->useCase->exec(
            new CurrentUser($usrUser->getId()),
            'opr_gacha_id',
            0,
            $playNum,
            'ticket_item',
            $playNum,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );
    }

    public function testExec_プレミアムガチャで3体目までに最高レアが確定で当たる(): void
    {
        // Setup
        $this->createPremiumUpperGachaData();
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 97
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $drewCount = 0;

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            10,
            null,
            1000,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($currentUser->getId());

        // Verify
        $isGetMaxRarity = false;
        $this->assertEquals(10, $resultData->gachaRewards->count());
        /** @var OprGachaPrizeEntity $prize */
        foreach ($resultData->gachaRewards as $cnt => $prize) {
            /** @var GachaReward $prize */
            switch ($prize->getType()) {
                case RewardType::UNIT->value:
                    $mst = MstUnit::query()->where('id', $prize->getResourceId())->firstOrFail()->toEntity();
                    break;
                case RewardType::ITEM->value:
                    $mst = MstItem::query()->where('id', $prize->getResourceId())->firstOrFail()->toEntity();
                    break;
                default:
                    assert(false, 'invalid resource distribution type');
            }
            if ($mst->getRarity() === RarityType::UR->value) {
                $isGetMaxRarity = true;
                break;
            }
            if ($cnt === 2) {
                break;
            }
        }

        $this->assertTrue($isGetMaxRarity);
        $this->assertEquals(0, $currencySummary->getTotalAmount());
    }

    public function testExec_プレミアムガチャで最高レアが当たり天井の回数がリセットされる(): void
    {
        // Setup
        $mstUnit = MstUnit::factory()->create(['rarity' => RarityType::UR->value])->toEntity();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PREMIUM->value,
            'upper_group' => 'Premium',
            'prize_group_id' => 'prize_group_id',
        ]);
        OprGachaUpper::factory()->create([
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 100,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'language' => Language::Ja->value,
        ]);
        // 確実にURが出るようにURのユニット1体のみを設定
        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId()
        ]);

        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'cost_type' => CostType::DIAMOND,
            'cost_id' => null,
            'cost_num' => 100,
            'draw_count' => 1,
            'cost_priority' => 2,
        ]);

        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'upper_group' => 'Premium',
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 50
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            1,
            null,
            100,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
        $this->saveAll();

        // Verify
        /** @var GachaReward $prize */
        $prize = $resultData->gachaRewards->first();
        $mstUnit = MstUnit::query()->where('id', $prize->getResourceId())->firstOrFail()->toEntity();
        $this->assertEquals(RarityType::UR->value, $mstUnit->getRarity());

        // URを引き天井カウントがリセットされていること
        $usrGachaUpper = UsrGachaUpper::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('upper_type', UpperType::MAX_RARITY->value)
            ->firstOrFail();
        $this->assertEquals(0, $usrGachaUpper->getCount());
    }

    public function testExec_ピックアップガチャで3体目までにピックアップキャラが確定で当たる(): void
    {
        // Setup
        $this->createPickupUpperGachaData();
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->createMany([
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 0
            ],
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::PICKUP->value,
                'count' => 197
            ]
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $drewCount = 0;

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            10,
            null,
            1000,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        $currencySummary = $this->currencyDelegator->getCurrencySummary($currentUser->getId());

        // Verify
        $isGetPickUpUnit = false;
        $this->assertEquals(10, $resultData->gachaRewards->count());
        $oprGachaPrizes = OprGachaPrize::query()
            ->get()
            ->map(fn($oprGachaPrize) => $oprGachaPrize->toEntity())
            ->groupBy(fn($oprGachaPrize) => $oprGachaPrize->getResourceType())
            ->map(fn($oprGachaPrizes) => $oprGachaPrizes->keyBy(fn($oprGachaPrize) => $oprGachaPrize->getResourceId()));
        foreach ($resultData->gachaRewards as $cnt => $reward) {
            /** @var GachaReward $reward */
            switch ($reward->getType()) {
                case RewardType::UNIT->value:
                    $mst = MstUnit::query()->where('id', $reward->getResourceId())->firstOrFail()->toEntity();
                    break;
                case RewardType::ITEM->value:
                    $mst = MstItem::query()->where('id', $reward->getResourceId())->firstOrFail()->toEntity();
                    break;
                default:
                    assert(false, 'invalid resource distribution type');
            }
            $oprGachaPrize = $oprGachaPrizes->get($reward->getType())->get($reward->getResourceId());
            if ($mst->getRarity() === RarityType::UR->value && $oprGachaPrize->getPickup()) {
                $isGetPickUpUnit = true;
                break;
            }
            if ($cnt === 2) {
                break;
            }
        }

        $this->assertTrue($isGetPickUpUnit);
        $this->assertEquals(0, $currencySummary->getTotalAmount());
    }

    public function testExec_ピックアップガチャでピックアップURが当たり天井の回数がリセットされる(): void
    {
        // Setup
        $upperGroup = 'Pickup';
        $mstUnit = MstUnit::factory()->create(['rarity' => RarityType::UR->value])->toEntity();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PICKUP->value,
            'upper_group' => 'Pickup',
            'prize_group_id' => 'prize_group_id',
        ]);
        OprGachaUpper::factory()->createMany([
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 100,
            ],
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::PICKUP->value,
                'count' => 150,
            ]
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'language' => Language::Ja->value,
        ]);
        // 確実にピックアップURが出るようにユニット1体のみを設定
        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId(),
            'pickup' => true
        ]);

        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'cost_type' => CostType::DIAMOND,
            'cost_id' => null,
            'cost_num' => 100,
            'draw_count' => 1,
            'cost_priority' => 2,
        ]);

        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->createMany([
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 50
            ],
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::PICKUP->value,
                'count' => 100
            ]
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            1,
            null,
            100,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
        $this->saveAll();

        // Verify
        /** @var GachaReward $prize */
        $prize = $resultData->gachaRewards->first();
        $mstUnit = MstUnit::query()->where('id', $prize->getResourceId())->firstOrFail()->toEntity();
        $this->assertEquals(RarityType::UR->value, $mstUnit->getRarity());

        // URを引き天井カウントがリセットされていること
        $usrGachaUppers = UsrGachaUpper::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('upper_group', $upperGroup)
            ->get();
        $this->assertCount(2, $usrGachaUppers);
        foreach ($usrGachaUppers as $usrGachaUpper) {
            $this->assertEquals(0, $usrGachaUpper->getCount());
        }
    }

    public function testExec_ピックアップガチャでURではないピックアップの場合は天井の回数がリセットされない(): void
    {
        // Setup
        $upperGroup = 'Pickup';
        // 最高レアリティではないピックアップキャラ
        $mstUnit = MstUnit::factory()->create(['rarity' => RarityType::SR->value])->toEntity();

        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PICKUP->value,
            'upper_group' => 'Pickup',
            'prize_group_id' => 'prize_group_id',
        ]);
        OprGachaUpper::factory()->createMany([
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 100,
            ],
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::PICKUP->value,
                'count' => 150,
            ]
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'language' => Language::Ja->value,
        ]);
        // 確実にピックアップが出るようにユニット1体のみを設定
        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId(),
            'pickup' => true
        ]);

        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'cost_type' => CostType::DIAMOND,
            'cost_id' => null,
            'cost_num' => 100,
            'draw_count' => 1,
            'cost_priority' => 2,
        ]);

        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->createMany([
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 50
            ],
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::PICKUP->value,
                'count' => 50
            ]
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            1,
            null,
            100,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
        $this->saveAll();

        // Verify
        /** @var GachaReward $prize */
        $prize = $resultData->gachaRewards->first();
        $mstUnit = MstUnit::query()->where('id', $prize->getResourceId())->firstOrFail()->toEntity();
        $this->assertEquals(RarityType::SR->value, $mstUnit->getRarity());

        // URを引き天井カウントがリセットされていること
        $usrGachaUppers = UsrGachaUpper::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('upper_group', $upperGroup)
            ->get();
        $this->assertCount(2, $usrGachaUppers);
        foreach ($usrGachaUppers as $usrGachaUpper) {
            $this->assertEquals(50 + 1, $usrGachaUpper->getCount());
        }
    }

    public function testExec_コインが排出されるガシャ(): void
    {
        // Setup
        $oprGacha = OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::NORMAL->value,
            'prize_group_id' => 'prize_group_id',
        ])->toEntity();
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGacha->getId(),
            'language' => Language::Ja->value,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => $oprGacha->getPrizeGroupId(),
            'resource_type' => RewardType::COIN,
            'resource_id' => null,
            'resource_amount' => 1000
        ]);
        $ticketItemId = MstItem::factory()->create()->toEntity()->getId();
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGacha->getId(),
            'cost_type' => CostType::ITEM,
            'cost_id' => $ticketItemId,
            'cost_num' => 1,
            'draw_count' => 1,
            'cost_priority' => 2,
        ]);

        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'coin' => 1000
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_item_id' => $ticketItemId,
            'amount' => 1,
        ]);

        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            1,
            $ticketItemId,
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );
        $this->saveAll();

        // Verify
        $actual = $resultData->gachaRewards->first()->getAmount();

        $this->assertEquals(1000, $actual);
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $currentUser->getId())->first();
        $this->assertEquals(1000 + 1000, $usrUserParameter->getCoin());
    }

    // public function testExec_探索N時間コインアイテムが排出されるガシャ(): void
    // {
    //     // Setup
    //     $oprGacha = OprGacha::factory()->create([
    //         'id' => 'opr_gacha_id',
    //         'gacha_type' => GachaType::NORMAL->value,
    //         'prize_group_id' => 'prize_group_id',
    //     ])->toEntity();
    //     OprGachaI18n::factory()->create([
    //         'opr_gacha_id' => $oprGacha->getId(),
    //         'language' => Language::Ja->value,
    //     ]);
    //     $mstItemId = MstItem::factory()->create([
    //         'type' => ItemType::IDLE_COIN_BOX->value,
    //         'effect_value' => 2
    //     ])->toEntity()->getId();
    //     OprGachaPrize::factory()->create([
    //         'group_id' => $oprGacha->getPrizeGroupId(),
    //         'resource_type' => RewardType::ITEM,
    //         'resource_id' => $mstItemId,
    //         'resource_amount' => 1
    //     ]);

    //     $ticketItemId = MstItem::factory()->create()->toEntity()->getId();
    //     OprGachaUseResource::factory()->create([
    //         'opr_gacha_id' => $oprGacha->getId(),
    //         'cost_type' => CostType::ITEM,
    //         'cost_id' => $ticketItemId,
    //         'cost_num' => 1,
    //         'draw_count' => 1,
    //         'cost_priority' => 2,
    //     ]);
    //     $mstQuest = MstQuest::factory()->create([
    //         'quest_type' => QuestType::NORMAL->value,
    //     ])->toEntity();
    //     $mstStageId = MstStage::factory()->create([
    //         'mst_quest_id' => $mstQuest->getId(),
    //     ])->toEntity()->getId();
    //     MstIdleIncentive::factory()->create([
    //         'initial_reward_receive_minutes' => 10,
    //         'reward_increase_interval_minutes' => 10,
    //     ]);
    //     MstIdleIncentiveReward::factory()->create([
    //         'mst_stage_id' => $mstStageId,
    //         'base_coin_amount' => 100,
    //     ]);

    //     $user = $this->createUsrUser();
    //     $currentUser = new CurrentUser($user->getId());
    //     UsrUserParameter::factory()->create([
    //         'usr_user_id' => $currentUser->getId(),
    //         'coin' => 1000
    //     ]);
    //     UsrStage::factory()->create([
    //         'usr_user_id' => $user->getId(),
    //         'mst_stage_id' => $mstStageId,
    //
    //     ]);
    //     UsrItem::factory()->create([
    //         'usr_user_id' => $user->getId(),
    //         'mst_item_id' => $ticketItemId,
    //         'amount' => 1,
    //     ]);

    //     $this->createDiamond($currentUser->getId(), 1000);

    //     // Exercise
    //     $resultData = $this->useCase->exec(
    //         $currentUser,
    //         'opr_gacha_id',
    //         0,
    //         1,
    //         null,
    //         1,
    //         UserConstant::PLATFORM_IOS,
    //         CurrencyConstants::PLATFORM_APPSTORE,
    //         CostType::ITEM
    //     );
    //     $this->saveAll();

    //     // Verify
    //     $actual = $resultData->gachaRewards->first()->getAmount();

    //     /**
    //      * 100: mst_idle_incentive_reward.base_coin_amount
    //      * 120: mst_item.effect_value * 60
    //      * 10: mst_idle_incentive.reward_increase_interval_minutes
    //      */
    //     $expectedAcquiredCoin = 100 * 120 / 10;

    //     $this->assertEquals($expectedAcquiredCoin, $actual);
    //     $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $currentUser->getId())->first();
    //     $this->assertEquals(1000 + $expectedAcquiredCoin, $usrUserParameter->getCoin());
    // }

    // public function testExec_探索N時間リミテッドメモリーアイテムが排出されるガシャ(): void
    // {
    //     // Setup
    //     $oprGacha = OprGacha::factory()->create([
    //         'id' => 'opr_gacha_id',
    //         'gacha_type' => GachaType::NORMAL->value,
    //         'prize_group_id' => 'prize_group_id',
    //     ])->toEntity();
    //     OprGachaI18n::factory()->create([
    //         'opr_gacha_id' => $oprGacha->getId(),
    //         'language' => Language::Ja->value,
    //     ]);
    //     $mstItemId = MstItem::factory()->create([
    //         'type' => ItemType::IDLE_RANK_UP_MATERIAL_BOX->value,
    //         'effect_value' => 2
    //     ])->toEntity()->getId();
    //     $limitedMemoryItemId = MstItem::factory()->create([
    //         'type' => ItemType::RANK_UP_MATERIAL->value,
    //         'effect_value' => UnitColorType::COLORLESS->value
    //     ])->toEntity()->getId();
    //     $ticketItemId = MstItem::factory()->create()->toEntity()->getId();
    //     OprGachaPrize::factory()->create([
    //         'group_id' => $oprGacha->getPrizeGroupId(),
    //         'resource_type' => RewardType::ITEM,
    //         'resource_id' => $mstItemId,
    //         'resource_amount' => 1
    //     ]);
    //     OprGachaUseResource::factory()->create([
    //         'opr_gacha_id' => $oprGacha->getId(),
    //         'cost_type' => CostType::ITEM,
    //         'cost_id' => $ticketItemId,
    //         'cost_num' => 1,
    //         'draw_count' => 1,
    //         'cost_priority' => 2,
    //     ]);
    //     $mstQuest = MstQuest::factory()->create([
    //         'quest_type' => QuestType::NORMAL->value,
    //     ])->toEntity();
    //     $mstStageId = MstStage::factory()->create([
    //         'mst_quest_id' => $mstQuest->getId(),
    //     ])->toEntity()->getId();
    //     MstIdleIncentive::factory()->create([
    //         'initial_reward_receive_minutes' => 10,
    //         'reward_increase_interval_minutes' => 10,
    //     ]);
    //     MstIdleIncentiveReward::factory()->create([
    //         'mst_stage_id' => $mstStageId,
    //     ]);

    //     $user = $this->createUsrUser();
    //     $currentUser = new CurrentUser($user->getId());
    //     UsrUserParameter::factory()->create([
    //         'usr_user_id' => $currentUser->getId(),
    //     ]);
    //     UsrStage::factory()->create([
    //         'usr_user_id' => $user->getId(),
    //         'mst_stage_id' => $mstStageId,
    //
    //     ]);
    //     UsrItem::factory()->createMany([
    //         [
    //             'usr_user_id' => $user->getId(),
    //             'mst_item_id' => $limitedMemoryItemId,
    //             'amount' => 10,
    //         ],
    //         [
    //             'usr_user_id' => $user->getId(),
    //             'mst_item_id' => $ticketItemId,
    //             'amount' => 1,
    //         ]
    //     ]);

    //     $this->createDiamond($currentUser->getId(), 1000);

    //     // Exercise
    //     $resultData = $this->useCase->exec(
    //         $currentUser,
    //         'opr_gacha_id',
    //         0,
    //         1,
    //         null,
    //         1,
    //         UserConstant::PLATFORM_IOS,
    //         CurrencyConstants::PLATFORM_APPSTORE,
    //         CostType::ITEM
    //     );
    //     $this->saveAll();

    //     // Verify
    //     $actual = $resultData->gachaRewards->first()->getAmount();

    //     /**
    //      * 20: mst_idle_incentive_reward.base_rank_up_material_amount
    //      * 120: mst_item.effect_value * 60
    //      * 10: mst_idle_incentive.reward_increase_interval_minutes
    //      */
    //     $expectedAcquiredItem = 20 * 120 / 10;

    //     $this->assertEquals($expectedAcquiredItem, $actual);
    //     $usrItem = UsrItem::query()
    //         ->where('usr_user_id', $user->getId())
    //         ->where('mst_item_id', $limitedMemoryItemId)
    //         ->first();
    //     $this->assertEquals(10 + $expectedAcquiredItem, $usrItem->getAmount());
    // }

    public function testExec_最高レアリティとピックアップの天井が重複する場合(): void
    {
        // Setup
        $this->createPickupUpperGachaData();
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrGachaUpper::factory()->createMany([
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::MAX_RARITY->value,
                'count' => 99
            ],
            [
                'usr_user_id' => $currentUser->getId(),
                'upper_group' => 'Pickup',
                'upper_type' => UpperType::PICKUP->value,
                'count' => 199
            ]
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $drewCount = 0;
        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            1,
            null,
            100,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(1, $resultData->gachaRewards->count());

        /** @var GachaReward $reward */
        $reward = $resultData->gachaRewards->first();
        $mstUnit = MstUnit::query()->where('id', $reward->getResourceId())->first()->toEntity();
        $this->assertEquals($mstUnit->getRarity(), RarityType::UR->value);

        $oprGachaPrize = OprGachaPrize::query()
            ->where('group_id', 'prize_group_id')
            ->where('resource_type', RewardType::UNIT->value)
            ->where('resource_id', $reward->getResourceId())
            ->first()
            ->toEntity();

        $this->assertTrue($oprGachaPrize->getPickup());
    }

    public function testExec_10連確定枠がある場合(): void
    {
        // Setup
        $fixedMstUnitId = $this->createPremiumFixedGachaData();
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $drewCount = 0;
        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            10,
            null,
            1000,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        /** @var GachaReward $reward */
        $reward = $resultData->gachaRewards->last();
        $this->assertEquals($fixedMstUnitId, $reward->getResourceId());
    }

    public static function params_testExec_10連確定枠があるがガシャ回数が10回未満の場合(): array
    {
        return [
            '1回' => ['drawCount' => 1, 'cost' => 100],
            '9回' => ['drawCount' => 9, 'cost' => 900],
        ];
    }

    /**
     * @dataProvider params_testExec_10連確定枠があるがガシャ回数が10回未満の場合
     */
    public function testExec_10連確定枠があるがガシャ回数が10回未満の場合(int $drawCount, int $cost): void
    {
        // Setup
        $fixedMstUnitId = $this->createPremiumFixedGachaData();
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $drewCount = 0;
        $this->createDiamond($currentUser->getId(), 1000);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            $drawCount,
            null,
            $cost,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals($drawCount, $resultData->gachaRewards->count());

        // 確定枠で排出されるキャラが排出されていないこと
        /** @var GachaReward $reward */
        $reward = $resultData->gachaRewards->last();
        $this->assertNotEquals($fixedMstUnitId, $reward->getResourceId());
    }

    public function testExec_アイテム10個で10連したときに確定枠が使われる(): void
    {
        $itemId = 'gacha_ticket_item';
        // Setup
        $fixedMstUnitId = $this->createPremiumFixedGachaForItemCostData($itemId);
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $currentUser->getId(),
        ]);
        $this->createDiamond($currentUser->getId(), 1000);
        $drewCount = 0;

        // アイテム1個を10個分作成
        MstItem::factory()->create(['id' => $itemId]);
        UsrItem::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'mst_item_id' => $itemId,
            'amount' => 10,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            $drewCount,
            10,
            $itemId,
            10,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::ITEM
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // 確定枠でキャラが排出されることを確認
        /** @var GachaReward $reward */
        $reward = $resultData->gachaRewards->last();
        $this->assertEquals($fixedMstUnitId, $reward->getResourceId());

        // アイテムが消費されていることを確認
        $item = UsrItem::where('usr_user_id', $currentUser->getId())
            ->where('mst_item_id', $itemId)
            ->first();
        $this->assertEquals(0, $item->count);
    }
}
