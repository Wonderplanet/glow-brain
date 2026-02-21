<?php

declare(strict_types=1);

namespace Feature\Domain\Game\Services;

use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Common\Enums\Language;
use Carbon\CarbonImmutable;
use App\Domain\Game\Services\GameService;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\GameBadgeData;
use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\Data\MissionStatusData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrStageEnhanceStatusData;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    private GameService $gameService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameService = app(GameService::class);
    }

    public function testFetch()
    {
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => $now,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), 6, 7, 8);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'my_id' => '9999999999',
            'name' => 'test',
            'is_change_name' => 1,
            'name_update_at' => $now->copy()->sub('2 hour'),
        ]);

        $usrUserBuyCount = UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => $now->toDateTimeString(),
        ]);

        [$usrStages, $usrStageEvents, $usrStageEnhances, $usrItems, $usrUnits] = $this->generateStageItemUnit($usrUser);
        $usrAdventBattles = $this->generateAdventBattle($usrUser);

        $gameFetchData = new GameFetchData(
            new UsrParameterData(
                $usrUserParameter->getLevel(),
                $usrUserParameter->getExp(),
                $usrUserParameter->getCoin(),
                $usrUserParameter->getStamina(),
                $usrUserParameter->getStaminaUpdatedAt(),
                6,
                7,
                8,
            ),
            $usrStages,
            $usrStageEvents,
            $usrStageEnhances,
            $usrAdventBattles,
            new GameBadgeData(0, 0, 0, collect(), 0, collect()),
            $usrUserBuyCount,
            new MissionStatusData(false),
        );

        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $responseFetchData = $this->gameService->fetch($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);

        $this->assertEquals($gameFetchData->usrUserParameter->getLevel(), $responseFetchData->usrUserParameter->getLevel());
        $this->assertEquals($gameFetchData->usrUserParameter->getExp(), $responseFetchData->usrUserParameter->getExp());
        $this->assertEquals($gameFetchData->usrUserParameter->getCoin(), $responseFetchData->usrUserParameter->getCoin());
        $this->assertEquals($gameFetchData->usrUserParameter->getStamina(), $responseFetchData->usrUserParameter->getStamina());
        $this->assertEquals($gameFetchData->usrUserParameter->getStaminaUpdatedAt(), $responseFetchData->usrUserParameter->getStaminaUpdatedAt());
        $this->assertEquals(6, $responseFetchData->usrUserParameter->getFreeDiamond());
        $this->assertEquals(7, $responseFetchData->usrUserParameter->getPaidDiamondIos());
        $this->assertEquals(8, $responseFetchData->usrUserParameter->getPaidDiamondAndroid());

        $this->assertCount(3, $responseFetchData->usrStages);
        $stage = $responseFetchData->usrStages->sort(fn($a, $b) => $a->getMstStageId() <=> $b->getMstStageId())->values();
        $this->assertEquals($gameFetchData->usrStages[0]->getMstStageId(), $stage[0]->getMstStageId());
        $this->assertEquals($gameFetchData->usrStages[0]->getClearCount(), $stage[0]->getClearCount());

        $this->assertCount(3, $responseFetchData->usrStageEvents);
        $stage = $responseFetchData->usrStageEvents->sort(fn($a, $b) => $a->getMstStageId() <=> $b->getMstStageId())->values();
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getMstStageId(), $stage[0]->getMstStageId());
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getResetClearCount(), $stage[0]->getResetClearCount());
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getResetAdChallengeCount(), $stage[0]->getResetAdChallengeCount());

        $this->assertCount(3, $responseFetchData->usrStageEnhanceStatusDataList);
        $actuals = $responseFetchData->usrStageEnhanceStatusDataList->keyBy(fn(UsrStageEnhanceStatusData $data) => $data->getMstStageId());
        // リセットされないデータ
        /** @var UsrStageEnhanceStatusData $actual */
        $actual = $actuals->get('1');
        $this->assertEquals(1, $actual->getResetChallengeCount());
        $this->assertEquals(1, $actual->getResetAdChallengeCount());
        $this->assertEquals(1, $actual->getMaxScore());
        // リセットされるデータ
        $actual = $actuals->get('3');
        $this->assertEquals(0, $actual->getResetChallengeCount());
        $this->assertEquals(0, $actual->getResetAdChallengeCount());
        $this->assertEquals(3, $actual->getMaxScore());

        // 降臨バトル
        $this->assertCount(3, $responseFetchData->usrAdventBattles);
        $actuals = $responseFetchData->usrAdventBattles->keyBy(fn(UsrAdventBattleInterface $data) => $data->getMstAdventBattleId());
        /** @var UsrAdventBattleInterface $actual */
        $actual = $actuals->get('1');
        $this->assertEquals(1, $actual->getResetChallengeCount());
        $this->assertEquals(1, $actual->getResetAdChallengeCount());
        $this->assertEquals(1, $actual->getMaxScore());
        $this->assertEquals(2, $actual->getTotalScore());
        // リセットされるデータ
        $actual = $actuals->get('3');
        $this->assertEquals(0, $actual->getResetChallengeCount());
        $this->assertEquals(0, $actual->getResetAdChallengeCount());
        $this->assertEquals(3, $actual->getMaxScore());
        $this->assertEquals(6, $actual->getTotalScore());

    }

    private function generateStageItemUnit(UsrUser $usrUser): array
    {
        $usrStages = collect();
        $usrStageEvents = collect();
        $usrStageEnhances = collect();
        $usrItems = collect();
        $usrUnits = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrStages->push(
                UsrStage::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string)$i,
                    'clear_count' => $i,
                    
                ])
            );
            $usrStageEvents->push(
                UsrStageEvent::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string)$i,
                    'clear_count' => $i,
                    'reset_clear_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'latest_reset_at' => now(),
                ])
            );
            $usrStageEnhances->push(
                UsrStageEnhance::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string)$i,
                    'clear_count' => $i,
                    'reset_challenge_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'max_score' => $i,
                    'latest_reset_at' => $i == 3
                        ? now()->subDay()->toDateTimeString() // i=3はリセットする
                        : now()->toDateTimeString() // i=1,2はリセットしない
                ])
            );
            $usrItems->push(
                UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => (string)$i,
                    'amount' => $i,
                ])
            );
            $usrUnits->push(
                UsrUnit::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_unit_id' => (string)$i,
                    'level' => $i,
                    'rank' => $i,
                    'grade_level' => $i,
                ])
            );
        }
        return [$usrStages, $usrStageEvents, $usrStageEnhances, $usrItems, $usrUnits];
    }

    private function generateAdventBattle(UsrUser $usrUser): Collection
    {
        $usrAdventBattles = collect();
        for ($i = 1; $i <= 3; $i++) {
            MstAdventBattle::factory()->create([
                'id' => (string)$i,
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => now()->subDay()->toDateTimeString(),
                'end_at' => now()->addDay()->toDateTimeString(),
            ]);
            $usrAdventBattles->push(
                UsrAdventBattle::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_advent_battle_id' => (string)$i,
                    'max_score' => $i,
                    'total_score' => $i * 2,
                    'reset_challenge_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'latest_reset_at' => $i == 3
                        ? now()->subDay()->toDateTimeString() // i=3はリセットする
                        : now()->toDateTimeString() // i=1,2はリセットしない
                ])
            );
        }
        return $usrAdventBattles;
    }
}
