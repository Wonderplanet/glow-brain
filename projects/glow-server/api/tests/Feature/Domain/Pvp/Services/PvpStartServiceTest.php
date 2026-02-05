<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\Services;

use App\Domain\Common\Enums\ErrorCode;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpStartService;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpUnitData;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

class PvpStartServiceTest extends TestCase
{
    private PvpStartService $pvpStartService;
    private UnitDelegator $unitDelegator;
    private OutpostDelegator $outpostDelegator;
    private EncyclopediaDelegator $encyclopediaDelegator;
    private EncyclopediaEffectDelegator $encyclopediaEffectDelegator;
    private UsrPvpSessionRepository $usrPvpSessionRepository;
    private PvpMissionTriggerService $pvpMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitDelegator = Mockery::mock(UnitDelegator::class);
        $this->outpostDelegator = Mockery::mock(OutpostDelegator::class);
        $this->encyclopediaDelegator = Mockery::mock(EncyclopediaDelegator::class);
        $this->encyclopediaEffectDelegator = Mockery::mock(EncyclopediaEffectDelegator::class);
        $this->usrPvpSessionRepository = Mockery::mock(UsrPvpSessionRepository::class);
        $this->mstConfigService = Mockery::mock(MstConfigService::class);
        $this->itemDelegator = Mockery::mock(ItemDelegator::class);
        $this->pvpMissionTriggerService = Mockery::mock(PvpMissionTriggerService::class);

        $this->pvpStartService = new PvpStartService(
            $this->unitDelegator,
            $this->outpostDelegator,
            $this->encyclopediaDelegator,
            $this->encyclopediaEffectDelegator,
            $this->usrPvpSessionRepository,
            $this->mstConfigService,
            $this->itemDelegator,
            $this->pvpMissionTriggerService,
        );
    }

    public function testStartPvpSession_新しいセッションを開始できることを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $sysPvpSeasonId = '456';
        $partyNo = 1;
        $opponentUsrUserId = 'opponent_789';
        $opponentScore = 1500;
        $now = CarbonImmutable::now();

        $pvpUnits = collect([
            new PvpUnitData('unit_001', 50, 5, 3),
            new PvpUnitData('unit_002', 45, 4, 2),
        ]);

        $opponentSelectStatusData = new OpponentSelectStatusData(
            'opponent_name',
            'opponent_avatar',
            '1000',
            'opponent_123',
            100,
             collect([]),
            100
        );

        $pvpEncyclopediaEffects = collect([
            new PvpEncyclopediaEffect('effect_001'),
            new PvpEncyclopediaEffect('effect_002'),
        ]);

        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            collect([]),
            $pvpEncyclopediaEffects,
            collect(['artwork_001', 'artwork_002']),
            collect([])
        );

        $usrPvpSession = Mockery::mock(UsrPvpSession::class);

        $this->usrPvpSessionRepository
            ->shouldReceive('findOrCreate')
            ->once()
            ->with($usrUserId, (string)$sysPvpSeasonId)
            ->andReturn($usrPvpSession);

        $usrPvpSession
            ->shouldReceive('startSession')
            ->once()
            ->with(
                (string)$sysPvpSeasonId,
                $partyNo,
                $opponentUsrUserId,
                $opponentPvpStatusData,
                $opponentScore,
                Mockery::type(CarbonImmutable::class),
                0,
            );

        $this->usrPvpSessionRepository
            ->shouldReceive('syncModel')
            ->once()
            ->with($usrPvpSession);

        $this->pvpMissionTriggerService
            ->shouldReceive('sendStartTriggers')
            ->once();

        // Act
        $this->pvpStartService->startPvpSession(
            $usrUserId,
            $sysPvpSeasonId,
            $partyNo,
            $opponentUsrUserId,
            $opponentPvpStatusData,
            $opponentScore,
            $now,
            false,
        );

        // Assert
        // モックの期待値が満たされていることを確認
        $this->assertTrue(true);
    }

    public function testGetOpponentUnits_対戦相手のユニット情報を取得できることを確認する(): void
    {
        // Arrange
        $opponentId = 'opponent_123';
        $mstUnitIds = collect(['tes_unit_001', 'test_unit_002']);

        $usrUnitEntity1 = Mockery::mock(\App\Domain\Resource\Usr\Entities\UsrUnitEntity::class);
        $usrUnitEntity1->shouldReceive('getMstUnitId')->andReturn('unit_001');
        $usrUnitEntity1->shouldReceive('getLevel')->andReturn(50);
        $usrUnitEntity1->shouldReceive('getRank')->andReturn(5);
        $usrUnitEntity1->shouldReceive('getGradeLevel')->andReturn(3);

        $usrUnitEntity2 = Mockery::mock(\App\Domain\Resource\Usr\Entities\UsrUnitEntity::class);
        $usrUnitEntity2->shouldReceive('getMstUnitId')->andReturn('unit_002');
        $usrUnitEntity2->shouldReceive('getLevel')->andReturn(45);
        $usrUnitEntity2->shouldReceive('getRank')->andReturn(4);
        $usrUnitEntity2->shouldReceive('getGradeLevel')->andReturn(2);

        $usrUnitEntities = collect([$usrUnitEntity1, $usrUnitEntity2]);

        $this->unitDelegator
            ->shouldReceive('getByMstUnitIds')
            ->once()
            ->with($opponentId, Mockery::any())
            ->andReturn($usrUnitEntities);

        // Act
        $result = $this->pvpStartService->getOpponentUnits($opponentId, $mstUnitIds);

        // Assert
        $this->assertCount(2, $result);
        $this->assertInstanceOf(PvpUnitData::class, $result->first());
        $this->assertEquals('unit_001', $result->first()->getMstUnitId());
        $this->assertEquals(50, $result->first()->getLevel());
    }

    public function testGetOpponentOutpostEnhancements_対戦相手の前哨基地強化情報を取得できることを確認する(): void
    {
        // Arrange
        $opponentId = 'opponent_123';

        $enhancement1 = Mockery::mock(\App\Domain\Outpost\Models\UsrOutpostEnhancementInterface::class);
        $enhancement1->shouldReceive('getMstOutpostId')->andReturn('outpost_001');
        $enhancement1->shouldReceive('getMstOutpostEnhancementId')->andReturn('enhancement_001');
        $enhancement1->shouldReceive('getLevel')->andReturn(5);

        $enhancement2 = Mockery::mock(\App\Domain\Outpost\Models\UsrOutpostEnhancementInterface::class);
        $enhancement2->shouldReceive('getMstOutpostId')->andReturn('outpost_002');
        $enhancement2->shouldReceive('getMstOutpostEnhancementId')->andReturn('enhancement_002');
        $enhancement2->shouldReceive('getLevel')->andReturn(3);

        $enhancements = collect([$enhancement1, $enhancement2]);

        $this->outpostDelegator
            ->shouldReceive('getOutpostEnhancements')
            ->once()
            ->with($opponentId)
            ->andReturn($enhancements);

        // Act
        $result = $this->pvpStartService->getOpponentOutpostEnhancements($opponentId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('outpost_001', $result->first()->getMstOutpostId());
        $this->assertEquals('enhancement_001', $result->first()->getMstOutpostEnhancementId());
        $this->assertEquals(5, $result->first()->getLevel());
    }

    public function testGetOpponentEncyclopediaEffects_対戦相手の図鑑効果を取得できることを確認する(): void
    {
        // Arrange
        $opponentId = 'opponent_123';

        $effect1 = new PvpEncyclopediaEffect('encyclopedia_effect_001');
        $effect2 = new PvpEncyclopediaEffect('encyclopedia_effect_002');
        $effects = collect([$effect1, $effect2]);

        $this->encyclopediaEffectDelegator
            ->shouldReceive('getUserEncyclopediaEffects')
            ->once()
            ->with($opponentId)
            ->andReturn($effects);

        // Act
        $result = $this->pvpStartService->getOpponentEncyclopediaEffects($opponentId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertInstanceOf(PvpEncyclopediaEffect::class, $result->first());
        $this->assertEquals('encyclopedia_effect_001', $result->first()->getMstEncyclopediaEffectId());
        $this->assertEquals('encyclopedia_effect_002', $result->last()->getMstEncyclopediaEffectId());
    }

    public function testGetOpponentArtworks_対戦相手のアートワーク情報を取得できることを確認する(): void
    {
        // Arrange
        $opponentId = 'opponent_123';

        $artwork1 = Mockery::mock(\App\Domain\Encyclopedia\Models\UsrArtworkInterface::class);
        $artwork1->shouldReceive('getMstArtworkId')->andReturn('artwork_001');

        $artwork2 = Mockery::mock(\App\Domain\Encyclopedia\Models\UsrArtworkInterface::class);
        $artwork2->shouldReceive('getMstArtworkId')->andReturn('artwork_002');

        $artworks = collect([$artwork1, $artwork2]);

        $this->encyclopediaDelegator
            ->shouldReceive('getUsrArtworks')
            ->once()
            ->with($opponentId)
            ->andReturn($artworks);

        // Act
        $result = $this->pvpStartService->getOpponentArtworks($opponentId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('artwork_001', $result->first());
        $this->assertEquals('artwork_002', $result->last());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
