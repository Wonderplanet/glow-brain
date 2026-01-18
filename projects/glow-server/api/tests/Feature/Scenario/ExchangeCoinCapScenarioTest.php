<?php

namespace Tests\Feature\Scenario;

use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstExchange;
use App\Domain\Resource\Mst\Models\MstExchangeCost;
use App\Domain\Resource\Mst\Models\MstExchangeLineup;
use App\Domain\Resource\Mst\Models\MstExchangeReward;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;

/**
 * 交換所でのコイン上限キャップシナリオテスト
 *
 * UsrModelManagerのsyncModelsがisChanged=falseでもキャッシュを上書きする修正が
 * 実際の交換所シナリオで正しく機能していることを確認する統合テスト。
 *
 * 修正前は、キャッシュが上書きされず、コイン消費後の値のままデグレしていた。
 */
class ExchangeCoinCapScenarioTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/';

    /**
     * コイン上限キャップシナリオでキャッシュデグレが発生しないことを確認
     *
     * シナリオ概要:
     * 1. コイン999,999,999を所持している状態（上限値）
     * 2. 交換所でコイン500を消費して、原画3個を交換
     * 3. 原画1個は初獲得、残り2個は重複でコイン30,000に変換（合計60,000コイン）
     * 4. コイン999,999,499 + 60,000 = 1,000,059,499 → 上限キャップで999,999,999
     * 5. 最終的なコインが999,999,999であることを確認（デグレして999,999,499になっていないことを確認）
     *
     * このシナリオは、以下のような複雑な処理フローを経る:
     * - 交換でコイン消費（UsrUserParameter更新）
     * - 原画獲得と重複チェック
     * - 重複原画のコイン変換
     * - コイン上限キャップ適用
     * - 各段階でのキャッシュ更新
     *
     * UsrModelManagerのsyncModelsが正しく動作していないと、
     * コイン上限キャップ適用後の値（999,999,999）ではなく、
     * コイン消費後の値（999,999,499）がキャッシュに残ってしまう。
     */
    public function test_交換所でコイン上限キャップ時にキャッシュデグレが発生しない()
    {
        // Setup
        $this->fixTime('2024-01-05 10:00:00');

        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $this->createMasterRelease();

        // コイン上限を999,999,999に設定（テストシナリオ用）
        $coinMaxAmount = 999999999;
        MstConfig::factory()->create([
            'key' => MstConfigConstant::USER_COIN_MAX_AMOUNT,
            'value' => (string) $coinMaxAmount,
        ]);

        // 交換所マスターデータ作成
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 23:59:59',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);
        MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 500,
        ]);
        $mstArtwork = MstArtwork::factory()->create();
        MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Artwork',
            'resource_id' => $mstArtwork->id,
            'resource_amount' => 3,
        ]);

        // 1. 初期コインを999,999,999に設定
        UsrUserParameter::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'coin' => $coinMaxAmount,
        ]);

        // 初期状態確認: コイン999,999,999を所持
        $initialUsrUserParameter = UsrUserParameter::where('usr_user_id', $this->usrUserId)->first();
        $this->assertEquals($coinMaxAmount, $initialUsrUserParameter->getCoin(), '初期状態でコイン999,999,999を所持');

        // Exercise
        // 2. 交換所で原画3個を交換（コイン500消費）
        //    原画1個は初獲得、残り2個は重複でコイン30,000に変換（30,000 × 2 = 60,000）
        $exchangeResponse = $this->sendRequest('exchange/trade', [
            'mstExchangeId' => $mstExchange->id,
            'mstExchangeLineupId' => $mstLineup->id,
            'tradeCount' => 1,
        ]);

        // Verify - HTTPステータス
        $exchangeResponse->assertStatus(HttpStatusCode::SUCCESS);

        // Verify - 原画は1つ獲得
        $usrArtworks = UsrArtwork::query()->where('usr_user_id', $this->usrUserId)->get();
        $this->assertEquals(1, $usrArtworks->count(), '原画は1つ獲得される');
        $this->assertEquals($mstArtwork->id, $usrArtworks->first()->getMstArtworkId(), '獲得した原画のIDが正しい');

        // Verify - コイン計算の確認
        // コイン999,999,499（消費後）+ 60,000（重複変換: 30,000 × 2）= 1,000,059,499
        // → 上限キャップで999,999,999
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $this->usrUserId)->first();
        $actualCoin = $usrUserParameter->getCoin();

        // 重要: 最終的なコインが999,999,999であることを確認
        // デグレが発生している場合、999,999,499（消費後のまま）になってしまう
        $this->assertEquals(
            $coinMaxAmount,
            $actualCoin,
            "【重要】コイン上限キャップ後のコインが正しく{$coinMaxAmount}になっていること。" .
            PHP_EOL .
            "もし999,999,499の場合、UsrModelManagerのキャッシュ上書き処理でデグレが発生している。" .
            PHP_EOL .
            "このテストは、コイン消費→重複原画のコイン変換→上限キャップという" .
            PHP_EOL .
            "複雑な処理フローでキャッシュが正しく更新されることを確認する。"
        );

        // Verify - exchangeRewardsのレスポンス構造
        $this->assertArrayHasKey('exchangeRewards', $exchangeResponse);
        $exchangeRewards = $exchangeResponse['exchangeRewards'];
        $this->assertCount(2, $exchangeRewards, '原画報酬とコイン変換報酬の2件が含まれる');

        // 初獲得原画分
        $artworkReward = collect($exchangeRewards)->first(fn($r) => $r['reward']['resourceType'] === 'Artwork');
        $this->assertNotNull($artworkReward, '原画報酬が含まれる');
        $this->assertEquals($mstArtwork->id, $artworkReward['reward']['resourceId'], '原画IDが正しい');
        $this->assertEquals(1, $artworkReward['reward']['resourceAmount'], '原画は1個');
        $this->assertNull($artworkReward['reward']['preConversionResource'], '初獲得なので変換前リソースはnull');

        // コイン変換分（preConversionResourceに原画情報が残る）
        $coinReward = collect($exchangeRewards)->first(fn($r) => $r['reward']['resourceType'] === 'Coin');
        $this->assertNotNull($coinReward, 'コイン変換報酬が含まれる');
        $convertAmount = EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $this->assertEquals(
            2 * $convertAmount,
            $coinReward['reward']['resourceAmount'],
            'コイン変換額は30,000 × 2 = 60,000'
        );
        $this->assertNotNull($coinReward['reward']['preConversionResource'], '変換前リソース情報が含まれる');
        $this->assertEquals(
            'Artwork',
            $coinReward['reward']['preConversionResource']['resourceType'],
            '変換前は原画'
        );
        $this->assertEquals(
            $mstArtwork->id,
            $coinReward['reward']['preConversionResource']['resourceId'],
            '変換前の原画IDが正しい'
        );
        $this->assertEquals(
            2,
            $coinReward['reward']['preConversionResource']['resourceAmount'],
            '変換された原画は2個'
        );
    }
}
