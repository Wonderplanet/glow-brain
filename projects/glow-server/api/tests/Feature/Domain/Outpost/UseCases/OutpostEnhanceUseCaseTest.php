<?php

declare(strict_types=1);

namespace Feature\Domain\Outpost\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Outpost\UseCases\OutpostEnhanceUseCase;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use App\Domain\User\Models\UsrUserParameter;
use App\Http\Responses\ResultData\OutpostEnhanceResultData;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;

class OutpostEnhanceUseCaseTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private OutpostEnhanceUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(OutpostEnhanceUseCase::class);
    }

    public static function params_testExec_ゲートの強化ができる()
    {
        return [
            '1レベル強化' => [
                'beforeLevel' => 1,
                'afterLevel' => 2,
                'expectedCoin' => 900,
            ],
            '複数レベル強化' => [
                'beforeLevel' => 1,
                'afterLevel' => 3,
                'expectedCoin' => 700,
            ],
        ];
    }

    /**
     * @dataProvider params_testExec_ゲートの強化ができる
     */
    public function testExec_ゲートの強化ができる(int $beforeLevel, int $afterLevel, int $expectedCoin)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstOutpost = MstOutpost::factory()->create()->toEntity();
        $mstOutpostEnhancement = MstOutpostEnhancement::factory()->create([
            'mst_outpost_id' => $mstOutpost->getId(),
        ])->toEntity();
        MstOutpostEnhancementLevel::factory()->createMany([
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 1, 'cost_coin' => 0],
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 2, 'cost_coin' => 100],
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 3, 'cost_coin' => 200],
        ]);

        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1000]);
        $this->createDiamond($usrUserId);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstOutpostEnhancement->getId(), $afterLevel);

        // Verify
        $this->assertInstanceOf(OutpostEnhanceResultData::class, $result);

        $this->assertEquals($beforeLevel, $result->beforeLevel);
        $this->assertEquals($afterLevel, $result->afterLevel);
        $this->assertEquals($expectedCoin, $result->usrUserParameter->getCoin());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());

        $usrOutpostEnhancement = UsrOutpostEnhancement::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_outpost_id', $mstOutpost->getId())
            ->where('mst_outpost_enhancement_id', $mstOutpostEnhancement->getId())
            ->first();
        $this->assertEquals($afterLevel, $usrOutpostEnhancement->getLevel());
    }

    public function testExec_レベルパラメータが不正な場合はエラーとなる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstOutpost = MstOutpost::factory()->create()->toEntity();
        $mstOutpostEnhancement = MstOutpostEnhancement::factory()->create([
            'mst_outpost_id' => $mstOutpost->getId()
        ])->toEntity();
        MstOutpostEnhancementLevel::factory()->createMany([
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 1, 'cost_coin' => 0],
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 2, 'cost_coin' => 100],
            ['mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(), 'level' => 3, 'cost_coin' => 200],
        ]);

        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1000]);
        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => $mstOutpost->getId(),
            'mst_outpost_enhancement_id' => $mstOutpostEnhancement->getId(),
            'level' => 3,
        ]);
        $this->createDiamond($usrUserId);

        // 現在のレベル以下を指定してエラーとなる
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        // Exercise
        $this->useCase->exec($currentUser, $mstOutpostEnhancement->getId(), 3);
    }
}
