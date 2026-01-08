<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Exchange\UseCases;

use App\Domain\Exchange\UseCases\ExchangeTradeUseCase;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstExchange;
use App\Domain\Resource\Mst\Models\MstExchangeCost;
use App\Domain\Resource\Mst\Models\MstExchangeLineup;
use App\Domain\Resource\Mst\Models\MstExchangeReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Http\Responses\ResultData\ExchangeTradeResultData;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class ExchangeTradeUseCaseTest extends TestCase
{
    private ExchangeTradeUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = app(ExchangeTradeUseCase::class);
        $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $this->usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $this->usrUserId]);
        $this->createMasterRelease();
    }

    /**
     * 正常系: 交換が成功しResultDataが正しく返る
     */
    public function test_exec_交換が成功しResultDataが正しく返る()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $currentUser = new CurrentUser($this->usrUserId);

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => null, // 上限なし
        ]);

        $mstCostItem = MstItem::factory()->create();
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Item',
            'cost_id' => $mstCostItem->id,
            'cost_amount' => 3,
        ]);

        $mstRewardItem = MstItem::factory()->create();
        $mstReward = MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstRewardItem->id,
            'resource_amount' => 10,
        ]);

        UsrItem::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_item_id' => $mstCostItem->id,
            'amount' => 50,
        ]);

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $mstExchange->id,
            $mstLineup->id,
            5, // 5回交換
            UserConstant::PLATFORM_IOS
        );

        // Verify - ResultDataの構造確認
        $this->assertInstanceOf(ExchangeTradeResultData::class, $result);
        $this->assertNotNull($result->usrUserParameter);
        $this->assertNotNull($result->usrItems);
        $this->assertNotNull($result->usrEmblems);
        $this->assertNotNull($result->usrUnits);
        $this->assertNotNull($result->usrArtworks);
        $this->assertNotNull($result->usrArtworkFragments);
        $this->assertNotNull($result->usrExchangeLineups);
        $this->assertNotNull($result->exchangeTradeRewards);

        // 交換履歴がResultDataに含まれる
        $this->assertCount(1, $result->usrExchangeLineups);
        $this->assertEquals($mstLineup->id, $result->usrExchangeLineups->first()->getMstExchangeLineupId());
    }

    /**
     * 正常系: 変更されたUsrItemsがResultDataに含まれる
     */
    public function test_exec_変更されたアイテムがResultDataに含まれる()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $currentUser = new CurrentUser($this->usrUserId);

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
        ]);
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_amount' => 100,
        ]);
        $mstItem = MstItem::factory()->create();
        $mstReward = MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstItem->id,
            'resource_amount' => 5,
        ]);

        UsrUserParameter::where('usr_user_id', $this->usrUserId)->update(['coin' => 1000]);

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $mstExchange->id,
            $mstLineup->id,
            2,
            UserConstant::PLATFORM_IOS
        );

        // Verify - UsrItemsに変更があることを確認
        $this->assertNotEmpty($result->usrItems);
        $changedItem = $result->usrItems->first(fn($item) => $item->getMstItemId() === $mstItem->id);
        $this->assertNotNull($changedItem);
        $this->assertEquals(10, $changedItem->getAmount()); // 5 * 2 = 10
    }
}
