<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\UseCases\UnitGradeUpUseCase;
use App\Domain\Unit\UseCases\UnitLevelUpUseCase;
use App\Domain\Unit\UseCases\UnitRankUpUseCase;
use App\Domain\Unit\UseCases\UnitReceiveGradeUpRewardUseCase;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\ResultData\UnitGradeUpResultData;
use App\Http\Responses\ResultData\UnitLevelUpResultData;
use App\Http\Responses\ResultData\UnitRankUpResultData;
use App\Http\Responses\ResultData\UnitReceiveGradeUpRewardResultData;
use Mockery\MockInterface;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class UnitControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/unit/';

    private CurrencyDelegator $currencyDelegator;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
    }

    /**
     * @test
     */
    public function grade_up_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tos_version' => 1,
            'privacy_policy_version' => 3,
        ]);
        $usrUnit = UsrUnit::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
        ]);
        $usrItems = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrItems->push(
                UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => (string)$i,
                    'amount' => $i,
                ])
            );
        }
        // Setup

        $resultData = new UnitGradeUpResultData(
            $usrUnit,
            $usrItems,
            collect(),
            collect(),
            collect()
        );

        $this->mock(UnitGradeUpUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->sendRequest('grade_up', ['usrUnitId' => $usrUnit->getId()]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);


        $this->assertEquals($usrItems[0]->getMstItemId(), $response['usrItems'][0]['mstItemId']);
        $this->assertEquals($usrItems[0]->getAmount(), $response['usrItems'][0]['amount']);

        $this->assertEquals($usrUnit->getMstUnitId(), $response['usrUnit']['mstUnitId']);
        $this->assertEquals($usrUnit->getLevel(), $response['usrUnit']['level']);
        $this->assertEquals($usrUnit->getRank(), $response['usrUnit']['rank']);
        $this->assertEquals($usrUnit->getGradeLevel(), $response['usrUnit']['gradeLevel']);
        $this->assertEquals($usrUnit->getLastRewardGradeLevel(), $response['usrUnit']['lastRewardGradeLevel']);

        // unitGradeUpRewardsの確認
        $this->assertIsArray($response['unitGradeUpRewards']);
        $this->assertEmpty($response['unitGradeUpRewards']);
    }

    /**
     * @test
     */
    public function levelup_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tos_version' => 1,
            'privacy_policy_version' => 3,
        ]);
        $usrUnit = UsrUnit::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
        ]);

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);

        $unit_id = $usrUnit->getId();

        $resultData = new UnitLevelUpResultData(
            $usrUnit,
            new UsrParameterData(
                $usrUserParameter->getLevel(),
                $usrUserParameter->getExp(),
                $usrUserParameter->getCoin(),
                $usrUserParameter->getStamina(),
                $usrUserParameter->getStaminaUpdatedAt(),
                0,
                0,
                0,
            )
        );

        $this->mock(UnitLevelUpUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // 課金データを作成
        $this->currencyDelegator->createUser(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            6,
            0
        );

        // Exercise
        $response = $this->sendRequest('level_up', ['usrUnitId' => $unit_id, 'level' => $usrUnit->getLevel() + 1]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function testRankUp_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tos_version' => 1,
            'privacy_policy_version' => 3,
        ]);
        $usrUnit = UsrUnit::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
        ]);
        $usrItems = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrItems->push(
                UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => (string)$i,
                    'amount' => $i,
                ])
            );
        }
        // Setup

        $resultData = new UnitRankUpResultData(
            $usrUnit,
            $usrItems
        );

        $this->mock(UnitRankUpUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->sendRequest('rank_up', ['usrUnitId' => $usrUnit->getId()]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);


        $this->assertEquals($usrItems[0]->getMstItemId(), $response['usrItems'][0]['mstItemId']);
        $this->assertEquals($usrItems[0]->getAmount(), $response['usrItems'][0]['amount']);

        $this->assertEquals($usrUnit->getMstUnitId(), $response['usrUnit']['mstUnitId']);
        $this->assertEquals($usrUnit->getLevel(), $response['usrUnit']['level']);
        $this->assertEquals($usrUnit->getRank(), $response['usrUnit']['rank']);
        $this->assertEquals($usrUnit->getGradeLevel(), $response['usrUnit']['gradeLevel']);
    }

    public function testReceiveGradeUpReward_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tos_version' => 1,
            'privacy_policy_version' => 3,
        ]);
        $usrUnit = UsrUnit::factory()->createAndConvert([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 5,
            'last_reward_grade_level' => 0,
        ]);

        $usrArtworks = collect();
        for ($i = 1; $i <= 2; $i++) {
            $usrArtworks->push(
                UsrArtwork::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_artwork_id' => (string)$i,
                ])
            );
        }

        $usrArtworkFragments = collect();
        for ($i = 1; $i <= 2; $i++) {
            $usrArtworkFragments->push(
                UsrArtworkFragment::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_artwork_id' => (string)$i,
                ])
            );
        }

        $resultData = new UnitReceiveGradeUpRewardResultData(
            $usrUnit,
            $usrArtworks,
            $usrArtworkFragments,
            collect()
        );

        $this->mock(UnitReceiveGradeUpRewardUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->sendRequest('receive_grade_up_reward', ['usrUnitId' => $usrUnit->getId()]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // usrUnitの確認
        $this->assertEquals($usrUnit->getMstUnitId(), $response['usrUnit']['mstUnitId']);
        $this->assertEquals($usrUnit->getLevel(), $response['usrUnit']['level']);
        $this->assertEquals($usrUnit->getRank(), $response['usrUnit']['rank']);
        $this->assertEquals($usrUnit->getGradeLevel(), $response['usrUnit']['gradeLevel']);
        $this->assertEquals($usrUnit->getLastRewardGradeLevel(), $response['usrUnit']['lastRewardGradeLevel']);

        // usrArtworksの確認
        $this->assertCount(2, $response['usrArtworks']);
        $this->assertEquals($usrArtworks[0]->getMstArtworkId(), $response['usrArtworks'][0]['mstArtworkId']);

        // usrArtworkFragmentsの確認
        $this->assertCount(2, $response['usrArtworkFragments']);
        $this->assertEquals($usrArtworkFragments[0]->getMstArtworkId(), $response['usrArtworkFragments'][0]['mstArtworkId']);
        $this->assertEquals($usrArtworkFragments[0]->getMstArtworkFragmentId(), $response['usrArtworkFragments'][0]['mstArtworkFragmentId']);

        // unitGradeUpRewardsの確認
        $this->assertIsArray($response['unitGradeUpRewards']);
        $this->assertEmpty($response['unitGradeUpRewards']);
    }
}
