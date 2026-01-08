<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstExchange;
use App\Domain\Resource\Mst\Models\MstExchangeCost;
use App\Domain\Resource\Mst\Models\MstExchangeLineup;
use App\Domain\Resource\Mst\Models\MstExchangeReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use PHPUnit\Framework\Attributes\DataProvider;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class ExchangeControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/exchange/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $this->usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $this->usrUserId]);
        $this->createMasterRelease();
    }

    /**
     * 正常系: 交換が成功し、正しいレスポンス構造が返る
     */
    public function test_trade_正常系レスポンス確認()
    {
        // Setup
        $this->fixTime('2024-01-05 00:00:00');

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
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
        $response = $this->sendRequest('trade', [
            'mstExchangeId' => $mstExchange->id,
            'mstExchangeLineupId' => $mstLineup->id,
            'tradeCount' => 2,
        ]);

        // Verify - HTTPステータス
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // Verify - レスポンス構造（glow-schemaの定義と一致）
        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertArrayHasKey('usrItems', $response);
        $this->assertArrayHasKey('usrEmblems', $response);
        $this->assertArrayHasKey('usrUnits', $response);
        $this->assertArrayHasKey('usrArtworks', $response);
        $this->assertArrayHasKey('usrArtworkFragments', $response);
        $this->assertArrayHasKey('usrExchangeLineups', $response);
        $this->assertArrayHasKey('exchangeRewards', $response);

        // usrExchangeLineupsの構造確認
        $this->assertNotEmpty($response['usrExchangeLineups']);
        $lineup = $response['usrExchangeLineups'][0];
        $this->assertArrayHasKey('mstExchangeId', $lineup);
        $this->assertArrayHasKey('mstExchangeLineupId', $lineup);
        $this->assertArrayHasKey('tradeCount', $lineup);
    }

    /**
     * バリデーションエラー: mstExchangeIdが必須
     */
    public function test_trade_mstExchangeIdが必須()
    {
        // Exercise
        $response = $this->sendRequest('trade', [
            'mstExchangeLineupId' => fake()->uuid(),
            'tradeCount' => 1,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::ERROR);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * バリデーションエラー: mstExchangeLineupIdが必須
     */
    public function test_trade_mstExchangeLineupIdが必須()
    {
        // Exercise
        $response = $this->sendRequest('trade', [
            'mstExchangeId' => fake()->uuid(),
            'tradeCount' => 1,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::ERROR);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * バリデーションエラー: tradeCountが必須
     */
    public function test_trade_tradeCountが必須()
    {
        // Exercise
        $response = $this->sendRequest('trade', [
            'mstExchangeId' => fake()->uuid(),
            'mstExchangeLineupId' => fake()->uuid(),
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::ERROR);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * バリデーションエラー: tradeCountは1以上の整数
     */
    public static function params_test_trade_tradeCountのバリデーション()
    {
        return [
            'tradeCountが0' => [0],
            'tradeCountが負の数' => [-1],
            'tradeCountが文字列' => ['abc'],
        ];
    }

    #[DataProvider('params_test_trade_tradeCountのバリデーション')]
    public function test_trade_tradeCountは1以上の整数($tradeCount)
    {
        // Exercise
        $response = $this->sendRequest('trade', [
            'mstExchangeId' => fake()->uuid(),
            'mstExchangeLineupId' => fake()->uuid(),
            'tradeCount' => $tradeCount,
        ]);

        // Verify
        $response->assertStatus(HttpStatusCode::ERROR);
    }

    /**
     * 正常系: 複数回実行して交換回数が積算される
     */
    public function test_trade_複数回実行でtradeCountが積算される()
    {
        // Setup
        $this->fixTime('2024-01-05 00:00:00');

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 20,
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
            'resource_amount' => 1,
        ]);

        UsrUserParameter::where('usr_user_id', $this->usrUserId)->update(['coin' => 100000]);

        // Exercise - 1回目: 3回交換
        $response1 = $this->sendRequest('trade', [
            'mstExchangeId' => $mstExchange->id,
            'mstExchangeLineupId' => $mstLineup->id,
            'tradeCount' => 3,
        ]);

        // Verify - 1回目
        $response1->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals(3, $response1['usrExchangeLineups'][0]['tradeCount']);

        // Exercise - 2回目: 5回交換
        $response2 = $this->sendRequest('trade', [
            'mstExchangeId' => $mstExchange->id,
            'mstExchangeLineupId' => $mstLineup->id,
            'tradeCount' => 5,
        ]);

        // Verify - 2回目（積算）
        $response2->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals(8, $response2['usrExchangeLineups'][0]['tradeCount']); // 3 + 5
    }

    /**
     * 対象原画未所持の状態で複数原画を交換した時、1つは原画として獲得し残りはコインに変換される
     */
    public function test_trade_未所持原画複数交換時に1つは原画で残りはコインに変換される(): void
    {
        // Setup
        $this->fixTime('2024-01-05 00:00:00');

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
        ]);
        $mstArtwork = MstArtwork::factory()->create();
        MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->id,
            'resource_amount' => 3,
        ]);

        $initialCoin = 10000;
        UsrUserParameter::where('usr_user_id', $this->usrUserId)->update(['coin' => $initialCoin]);

        // Exercise
        $response = $this->sendRequest('trade', [
            'mstExchangeId' => $mstExchange->id,
            'mstExchangeLineupId' => $mstLineup->id,
            'tradeCount' => 1,
        ]);

        // Verify - HTTPステータス
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // Verify - 原画は1つ獲得
        $usrArtworks = UsrArtwork::query()->where('usr_user_id', $this->usrUserId)->get();
        $this->assertEquals(1, $usrArtworks->count());
        $this->assertEquals($mstArtwork->id, $usrArtworks->first()->getMstArtworkId());

        // Verify - 残り2つ分はコインに変換される
        $convertAmount = EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $expectedCoin = $initialCoin - 100 + (2 * $convertAmount);
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $this->usrUserId)->first();
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());

        // Verify - exchangeRewardsのレスポンス構造
        $this->assertArrayHasKey('exchangeRewards', $response);
        $exchangeRewards = $response['exchangeRewards'];
        $this->assertCount(2, $exchangeRewards);

        // 初獲得原画分
        $artworkReward = collect($exchangeRewards)->first(fn($r) => $r['reward']['resourceType'] === 'Artwork');
        $this->assertNotNull($artworkReward);
        $this->assertEquals($mstArtwork->id, $artworkReward['reward']['resourceId']);
        $this->assertEquals(1, $artworkReward['reward']['resourceAmount']);
        $this->assertNull($artworkReward['reward']['preConversionResource']);

        // コイン変換分（preConversionResourceに原画情報が残る）
        $coinReward = collect($exchangeRewards)->first(fn($r) => $r['reward']['resourceType'] === 'Coin');
        $this->assertNotNull($coinReward);
        $this->assertEquals(2 * $convertAmount, $coinReward['reward']['resourceAmount']);
        $this->assertNotNull($coinReward['reward']['preConversionResource']);
        $this->assertEquals('Artwork', $coinReward['reward']['preConversionResource']['resourceType']);
        $this->assertEquals($mstArtwork->id, $coinReward['reward']['preConversionResource']['resourceId']);
        $this->assertEquals(2, $coinReward['reward']['preConversionResource']['resourceAmount']);
    }
}
