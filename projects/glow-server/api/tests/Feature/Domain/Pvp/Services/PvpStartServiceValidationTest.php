<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Pvp\Services\PvpStartService;
use App\Domain\Resource\Mst\Entities\MstPvpEntity;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Pvp\Services\PvpMissionTriggerService;
use App\Domain\Resource\Usr\Entities\UsrItemEntity;
use App\Domain\Unit\Delegators\UnitDelegator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PvpStartServiceValidationTest extends TestCase
{
    private PvpStartService $pvpStartService;
    private $itemDelegator;
    private $mstConfigService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock all dependencies
        $unitDelegator = Mockery::mock(UnitDelegator::class);
        $outpostDelegator = Mockery::mock(OutpostDelegator::class);
        $encyclopediaDelegator = Mockery::mock(EncyclopediaDelegator::class);
        $encyclopediaEffectDelegator = Mockery::mock(EncyclopediaEffectDelegator::class);
        $usrPvpSessionRepository = Mockery::mock(UsrPvpSessionRepository::class);
        $this->mstConfigService = Mockery::mock(MstConfigService::class);
        $this->itemDelegator = Mockery::mock(ItemDelegator::class);
        $pvpMissionTriggerService = Mockery::mock(PvpMissionTriggerService::class);

        $this->mstConfigService->shouldReceive('getPvpChallengeItemId')->andReturn('pvp_challenge_item_id');
        
        $this->pvpStartService = new PvpStartService(
            $unitDelegator,
            $outpostDelegator,
            $encyclopediaDelegator,
            $encyclopediaEffectDelegator,
            $usrPvpSessionRepository,
            $this->mstConfigService,
            $this->itemDelegator,
            $pvpMissionTriggerService,
        );
    }

    #[DataProvider('pvpChallengeCountValidationProvider')]
    public function testValidateCanStart_挑戦回数バリデーション(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        int $isUseItem,
        bool $shouldThrowException,
        string $expectedMessage = ''
    ): void {
        // Arrange
        $usrPvp = Mockery::mock(UsrPvpInterface::class);
        $usrPvp->shouldReceive('getDailyRemainingChallengeCount')->andReturn($remainingChallengeCount);
        $usrPvp->shouldReceive('getDailyRemainingItemChallengeCount')->andReturn($remainingItemChallengeCount);
        $usrPvp->shouldReceive('getUsrUserId')->andReturn(1);

        $mstPvp = Mockery::mock(MstPvpEntity::class);

        // アイテム使用時のMock設定
        if ($isUseItem === 1) {
            $mstPvp->shouldReceive('getItemChallengeCostAmount')->andReturn(1);
            
            // アイテム所持状況のMock（十分な数を持っているとする）
            $usrItem = new UsrItemEntity('1', 'test_item_id', 10);
            $this->itemDelegator->shouldReceive('getUsrItemByMstItemId')->andReturn($usrItem);
        }

        // Act & Assert
        if ($shouldThrowException) {
            $this->expectException(GameException::class);
            $this->expectExceptionMessage($expectedMessage);
        }

        $this->pvpStartService->validateCanStart($usrPvp, $mstPvp, (bool)$isUseItem);
        
        if (!$shouldThrowException) {
            $this->assertTrue(true); // バリデーションが成功した場合
        }
    }

    public static function pvpChallengeCountValidationProvider(): array
    {
        return [
            'デイリー挑戦回数使用_挑戦可能' => [
                'remainingChallengeCount' => 5,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
            'デイリー挑戦回数使用_挑戦不可_回数0' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 0,
                'shouldThrowException' => true,
                'expectedMessage' => 'daily challenge count is over',
            ],
            'アイテム挑戦回数使用_挑戦可能_デイリー回数0' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
            'アイテム挑戦回数使用_挑戦不可_回数0' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'item challenge count is over',
            ],
            'アイテム挑戦回数使用_挑戦不可_デイリー回数残り' => [
                'remainingChallengeCount' => 2,
                'remainingItemChallengeCount' => 5,
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'daily challenge count must be 0 to use item challenge count',
            ],
            'デイリー挑戦回数使用_挑戦可能_アイテム回数0' => [
                'remainingChallengeCount' => 1,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
            'アイテム挑戦回数使用_挑戦可能_最大回数' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 999,
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
            'デイリー挑戦回数使用_挑戦可能_最大回数' => [
                'remainingChallengeCount' => 999,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
        ];
    }

    #[DataProvider('pvpItemValidationProvider')]
    public function testValidateCanStart_アイテム所持数バリデーション(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        int $isUseItem,
        ?int $itemAmount,
        int $itemChallengeCostAmount,
        bool $shouldThrowException,
        string $expectedMessage = ''
    ): void {
        // Arrange
        $usrPvp = Mockery::mock(UsrPvpInterface::class);
        $usrPvp->shouldReceive('getDailyRemainingChallengeCount')->andReturn($remainingChallengeCount);
        $usrPvp->shouldReceive('getDailyRemainingItemChallengeCount')->andReturn($remainingItemChallengeCount);
        $usrPvp->shouldReceive('getUsrUserId')->andReturn('test_user_id');

        $mstPvp = Mockery::mock(MstPvpEntity::class);
        $mstPvp->shouldReceive('getItemChallengeCostAmount')->andReturn($itemChallengeCostAmount);

        // ItemDelegatorのモック設定
        if ($isUseItem && $itemAmount !== null) {
            if ($itemAmount > 0) {
                $usrItem = new UsrItemEntity('test_user_id', 'pvp_challenge_item_id', $itemAmount);
                $this->itemDelegator->shouldReceive('getUsrItemByMstItemId')
                    ->with('test_user_id', 'pvp_challenge_item_id')
                    ->andReturn($usrItem);
            } else {
                $this->itemDelegator->shouldReceive('getUsrItemByMstItemId')
                    ->with('test_user_id', 'pvp_challenge_item_id')
                    ->andReturn(null);
            }
        }

        // Act & Assert
        if ($shouldThrowException) {
            $this->expectException(GameException::class);
            $this->expectExceptionMessage($expectedMessage);
        }

        $this->pvpStartService->validateCanStart($usrPvp, $mstPvp, (bool)$isUseItem);
        
        if (!$shouldThrowException) {
            $this->assertTrue(true); // バリデーションが成功した場合
        }
    }

    public static function pvpItemValidationProvider(): array
    {
        return [
            'アイテム使用_所持数十分_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 1,
                'itemAmount' => 10,
                'itemChallengeCostAmount' => 5,
                'shouldThrowException' => false,
            ],
            'アイテム使用_所持数不足_挑戦不可' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 1,
                'itemAmount' => 3,
                'itemChallengeCostAmount' => 5,
                'shouldThrowException' => true,
                'expectedMessage' => 'not enough item for PVP challenge',
            ],
            'アイテム使用_アイテム未所持_挑戦不可' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 1,
                'itemAmount' => 0, // null を表現するために0を使用
                'itemChallengeCostAmount' => 5,
                'shouldThrowException' => true,
                'expectedMessage' => 'not enough item for PVP challenge',
            ],
            'アイテム使用_所持数ちょうど_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 1,
                'isUseItem' => 1,
                'itemAmount' => 5,
                'itemChallengeCostAmount' => 5,
                'shouldThrowException' => false,
            ],
            'デイリー使用_アイテム関係なし_挑戦可能' => [
                'remainingChallengeCount' => 1,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'itemAmount' => null, // デイリー使用時はアイテムチェックしない
                'itemChallengeCostAmount' => 5,
                'shouldThrowException' => false,
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
