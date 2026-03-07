<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;
use App\Domain\BoxGacha\UseCases\BoxGachaDrawUseCase;
use App\Domain\BoxGacha\UseCases\BoxGachaInfoUseCase;
use App\Domain\BoxGacha\UseCases\BoxGachaResetUseCase;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\ResultData\BoxGachaDrawResultData;
use App\Http\Responses\ResultData\BoxGachaInfoResultData;
use App\Http\Responses\ResultData\BoxGachaResetResultData;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

class BoxGachaControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/box_gacha/';

    // ========================================
    // info API
    // ========================================

    public function test_info_drawPrizesありの場合のレスポンスを確認する(): void
    {
        $this->mockBoxGachaInfoUseCase(
            $this->createUsrBoxGacha(
                mstBoxGachaId: 'box_gacha_data_test',
                resetCount: 2,
                totalDrawCount: 15,
                currentBoxLevel: 3,
                drawPrizes: collect(['prize_1' => 5, 'prize_2' => 3]),
            )
        );

        $response = $this->sendGetRequest('info', ['mstBoxGachaId' => 'box_gacha_data_test']);

        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJson([
            'usrBoxGacha' => [
                'mstBoxGachaId' => 'box_gacha_data_test',
                'resetCount' => 2,
                'totalDrawCount' => 15,
                'currentBoxLevel' => 3,
                'drawPrizes' => [
                    ['mstBoxGachaPrizeId' => 'prize_1', 'count' => 5],
                    ['mstBoxGachaPrizeId' => 'prize_2', 'count' => 3],
                ],
            ],
        ]);
    }

    public function test_info_drawPrizesが空の場合のレスポンスを確認する(): void
    {
        $this->mockBoxGachaInfoUseCase($this->createUsrBoxGacha());

        $response = $this->sendGetRequest('info', ['mstBoxGachaId' => 'box_gacha_empty_test']);

        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJson([
            'usrBoxGacha' => [
                'mstBoxGachaId' => 'box_gacha_test',
                'resetCount' => 0,
                'totalDrawCount' => 0,
                'currentBoxLevel' => 1,
                'drawPrizes' => [],
            ],
        ]);
    }

    // ========================================
    // draw API
    // ========================================

    public function test_draw_レスポンス確認(): void
    {
        $resourceItemId = 'item_001';
        $resourceItemAmount = 10;

        $resourceUnitId = 'unit_001';
        $resourceUnitAmount = 1;

        $this->mockBoxGachaDrawUseCase(
            $this->createDrawResultData(
                usrParameterData: new UsrParameterData(10, 500, 1000, 100, now()->toDateTimeString(), 50, 30, 20),
                usrItems: collect([$this->createUsrItem($resourceItemId, $resourceItemAmount)]),
                usrUnits: collect([$this->createUsrUnit($resourceUnitId)]),
                usrBoxGacha: $this->createUsrBoxGacha(
                    mstBoxGachaId: 'box_gacha_test',
                    resetCount: 0,
                    totalDrawCount: 0,
                    currentBoxLevel: 1,
                    drawPrizes: collect([
                        'item_1' => 1,
                        'unit_1' => 1,
                    ]),
                ),
                boxGachaRewards: collect([
                    $this->createBoxGachaReward('Item', $resourceItemId, $resourceItemAmount),
                    $this->createBoxGachaReward('Unit', $resourceUnitId, $resourceUnitAmount),
                ]),
            )
        );

        $response = $this->sendRequest('draw', [
            'mstBoxGachaId' => 'box_gacha_draw_test',
            'drawCount' => 1,
            'currentBoxLevel' => 1,
        ]);

        $response->assertStatus(HttpStatusCode::SUCCESS);
        
        // レスポンスデータを取得
        $responseData = $response->json();
        
        // usrParameterの検証
        $this->assertEquals(10, $responseData['usrParameter']['level']);
        $this->assertEquals(500, $responseData['usrParameter']['exp']);
        $this->assertEquals(1000, $responseData['usrParameter']['coin']);
        $this->assertEquals(100, $responseData['usrParameter']['stamina']);
        $this->assertEquals(50, $responseData['usrParameter']['freeDiamond']);
        $this->assertEquals(30, $responseData['usrParameter']['paidDiamondIos']);
        $this->assertEquals(20, $responseData['usrParameter']['paidDiamondAndroid']);
        
        // usrItemsの検証
        $this->assertCount(1, $responseData['usrItems']);
        $this->assertEquals($resourceItemId, $responseData['usrItems'][0]['mstItemId']);
        $this->assertEquals($resourceItemAmount, $responseData['usrItems'][0]['amount']);
        
        // usrUnitsの検証（usrUnitIdは動的生成されるのでスキップ）
        $this->assertCount(1, $responseData['usrUnits']);
        $this->assertEquals('unit_001', $responseData['usrUnits'][0]['mstUnitId']);
        $this->assertEquals(1, $responseData['usrUnits'][0]['level']);
        $this->assertEquals(1, $responseData['usrUnits'][0]['rank']);
        $this->assertEquals(1, $responseData['usrUnits'][0]['gradeLevel']);
        $this->assertEquals(1, $responseData['usrUnits'][0]['isNewEncyclopedia']);
        
        // usrArtworksとusrArtworkFragmentsの検証
        $this->assertEmpty($responseData['usrArtworks']);
        $this->assertEmpty($responseData['usrArtworkFragments']);
        
        // usrBoxGachaの検証
        $this->assertEquals('box_gacha_test', $responseData['usrBoxGacha']['mstBoxGachaId']);
        $this->assertEquals(0, $responseData['usrBoxGacha']['resetCount']);
        $this->assertEquals(0, $responseData['usrBoxGacha']['totalDrawCount']);
        $this->assertEquals(1, $responseData['usrBoxGacha']['currentBoxLevel']);
        $this->assertCount(2, $responseData['usrBoxGacha']['drawPrizes']);
        
        // boxGachaRewardsの検証（unreceivedRewardReasonTypeとpreConversionResourceを含む）
        $this->assertCount(2, $responseData['boxGachaRewards']);
        $this->assertEquals('None', $responseData['boxGachaRewards'][0]['reward']['unreceivedRewardReasonType']);
        $this->assertEquals('Item', $responseData['boxGachaRewards'][0]['reward']['resourceType']);
        $this->assertEquals($resourceItemId, $responseData['boxGachaRewards'][0]['reward']['resourceId']);
        $this->assertEquals($resourceItemAmount, $responseData['boxGachaRewards'][0]['reward']['resourceAmount']);
        $this->assertNull($responseData['boxGachaRewards'][0]['reward']['preConversionResource']);
        
        $this->assertEquals('None', $responseData['boxGachaRewards'][1]['reward']['unreceivedRewardReasonType']);
        $this->assertEquals('Unit', $responseData['boxGachaRewards'][1]['reward']['resourceType']);
        $this->assertEquals($resourceUnitId, $responseData['boxGachaRewards'][1]['reward']['resourceId']);
        $this->assertEquals($resourceUnitAmount, $responseData['boxGachaRewards'][1]['reward']['resourceAmount']);
        $this->assertNull($responseData['boxGachaRewards'][1]['reward']['preConversionResource']);
    }

    // ========================================
    // reset API
    // ========================================

    public function test_reset_リセット後のレスポンスを確認する(): void
    {
        $this->mockBoxGachaResetUseCase(
            $this->createUsrBoxGacha(
                mstBoxGachaId: 'box_gacha_reset_test',
                resetCount: 1,
                totalDrawCount: 60,
                currentBoxLevel: 2,
            )
        );

        $response = $this->sendRequest('reset', [
            'mstBoxGachaId' => 'box_gacha_reset_test',
            'currentBoxLevel' => 1,
        ]);

        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJson([
            'usrBoxGacha' => [
                'mstBoxGachaId' => 'box_gacha_reset_test',
                'resetCount' => 1,
                'totalDrawCount' => 60,
                'currentBoxLevel' => 2,
                'drawPrizes' => [],
            ],
        ]);
    }

    // ========================================
    // UseCase モックヘルパー
    // ========================================

    private function mockBoxGachaInfoUseCase(UsrBoxGachaInterface $usrBoxGacha): void
    {
        $this->mock(BoxGachaInfoUseCase::class, function (MockInterface $mock) use ($usrBoxGacha) {
            $mock->shouldReceive('exec')
                ->once()
                ->andReturn(new BoxGachaInfoResultData($usrBoxGacha));
        });
    }

    private function mockBoxGachaDrawUseCase(BoxGachaDrawResultData $resultData): void
    {
        $this->mock(BoxGachaDrawUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')
                ->once()
                ->andReturn($resultData);
        });
    }

    private function mockBoxGachaResetUseCase(UsrBoxGacha $usrBoxGacha): void
    {
        $this->mock(BoxGachaResetUseCase::class, function (MockInterface $mock) use ($usrBoxGacha) {
            $mock->shouldReceive('exec')
                ->once()
                ->andReturn(new BoxGachaResetResultData($usrBoxGacha));
        });
    }

    // ========================================
    // ResultData ファクトリ
    // ========================================

    /**
     * @param Collection<int, UsrItem>|null $usrItems
     * @param Collection<int, UsrUnit>|null $usrUnits
     * @param Collection<int, BoxGachaReward>|null $boxGachaRewards
     */
    private function createDrawResultData(
        ?UsrParameterData $usrParameterData = null,
        ?Collection $usrItems = null,
        ?Collection $usrUnits = null,
        ?UsrBoxGacha $usrBoxGacha = null,
        ?Collection $boxGachaRewards = null,
    ): BoxGachaDrawResultData {
        return new BoxGachaDrawResultData(
            $usrParameterData ?? new UsrParameterData(1, 0, 0, 100, now()->toDateTimeString(), 0, 0, 0),
            $usrItems ?? collect([]),
            $usrUnits ?? collect([]),
            collect([]), // usrEmblems
            collect([]), // usrArtworks
            collect([]), // usrArtworkFragments
            $usrBoxGacha ?? $this->createUsrBoxGacha(),
            $boxGachaRewards ?? collect([]),
        );
    }

    // ========================================
    // ユーザーデータヘルパー
    // ========================================

    /**
     * @param Collection<string, int>|null $drawPrizes
     */
    private function createUsrBoxGacha(
        string $mstBoxGachaId = 'box_gacha_test',
        int $resetCount = 0,
        int $totalDrawCount = 0,
        int $currentBoxLevel = 1,
        ?Collection $drawPrizes = null,
    ): UsrBoxGacha {
        return UsrBoxGacha::factory()->make([
            'mst_box_gacha_id' => $mstBoxGachaId,
            'reset_count' => $resetCount,
            'total_draw_count' => $totalDrawCount,
            'current_box_level' => $currentBoxLevel,
            'draw_prizes' => json_encode($drawPrizes ?? [])
        ]);
    }

    private function createUsrItem(string $mstItemId, int $amount): UsrItem
    {
        return UsrItem::factory()->make([
            'mst_item_id' => $mstItemId,
            'amount' => $amount,
        ]);
    }

    private function createUsrUnit(string $mstUnitId): UsrUnit
    {
        return UsrUnit::factory()->make([
            'id' => 'usr_unit_001',
            'mst_unit_id' => $mstUnitId,
        ]);
    }

    private function createBoxGachaReward(
        string $resourceType = 'Item',
        string $resourceId = 'item_001',
        int $resourceAmount = 10,
    ): BoxGachaReward {
        return new BoxGachaReward(
            $resourceType,
            $resourceId,
            $resourceAmount,
            'box_gacha_test',
            'prize_001',
            0,
        );
    }
}
