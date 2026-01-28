<?php

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Enums\StageRewardCategory;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Services\StageEndNormalQuestService;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class StageEndNormalQuestServiceTest extends TestCase
{
    private StageEndNormalQuestService $stageEndNormalQuestService;
    private RewardDelegator $rewardDelegator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageEndNormalQuestService = $this->app->make(StageEndNormalQuestService::class);
        $this->rewardDelegator = $this->app->make(RewardDelegator::class);
    }

    public static function params_test_isQuestFirstClear_クエスト初クリアの判定ができる()
    {
        return [
            'true stage3を初クリア時にクエスト初クリア' => [
                'endMstStageId' => 'stage3',
                'expectedResult' => true,
                // stage1,2,3のように順番にクリア回数を格納した配列
                'stageClearCounts' => [2, 2, 1],
            ],
            'true stage1を初クリア時にクエスト初クリア' => [
                'endMstStageId' => 'stage1',
                'expectedResult' => true,
                'stageClearCounts' => [1, 2, 2],
            ],
            'false 対象ステージが未クリア' => [
                'endMstStageId' => 'stage3',
                'expectedResult' => false,
                'stageClearCounts' => [1, 2, 0],
            ],
            'false 他のステージが1つ未クリア' => [
                'endMstStageId' => 'stage3',
                'expectedResult' => false,
                'stageClearCounts' => [1, 0, 1],
            ],
            'false 既にクエスト初クリア済み' => [
                'endMstStageId' => 'stage2',
                'expectedResult' => false,
                'stageClearCounts' => [1, 2, 1],
            ],
        ];
    }

    /**
     * @dataProvider params_test_isQuestFirstClear_クエスト初クリアの判定ができる
     *
     * @param array<int> $stageClearCounts
     */
    public function test_isQuestFirstClear_クエスト初クリアの判定ができる(
        string $endMstStageId,
        bool $expectedResult,
        array $stageClearCounts,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'Normal',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);
        // ステージに開放順がない前提で確認
        $mstStages = MstStage::factory()->createMany([
            ['id' => 'stage1', 'mst_quest_id' => 'quest1'],
            ['id' => 'stage2', 'mst_quest_id' => 'quest1'],
            ['id' => 'stage3', 'mst_quest_id' => 'quest1'],
        ])->mapWithKeys(function ($mstStage) {
            $entity = $mstStage->toEntity();
            return [$entity->getId() => $entity];
        });
        $usrStages = UsrStage::factory()->createManyAndConvert([
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage1', 'clear_count' => $stageClearCounts[0]],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage2', 'clear_count' => $stageClearCounts[1]],
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage3', 'clear_count' => $stageClearCounts[2]],
        ])->mapWithKeys(function ($usrStage) {
            return [$usrStage->getMstStageId() => $usrStage];
        });

        $mstStage = $mstStages->get($endMstStageId);
        $usrStage = $usrStages->get($endMstStageId);

        // Reflectionでprotected 属性にアクセス
        $reflection = new \ReflectionClass($usrStage);
        $attrProperty = $reflection->getProperty('attributes');
        $attrProperty->setAccessible(true);

        // 各ステージのclear_count指定から1引いた値をセットしてincrementする
        $attributes = $attrProperty->getValue($usrStage);
        $mstStageIds = ['stage1', 'stage2', 'stage3'];
        $attributes['clear_count'] = $stageClearCounts[array_search($endMstStageId, $mstStageIds)] - 1;
        $attrProperty->setValue($usrStage, $attributes);
        $usrStage->incrementClearCount();

        // Exercise
        $result = $this->stageEndNormalQuestService->isQuestFirstClear($mstStage, $usrStage);

        // Verify
        $this->assertEquals($expectedResult, $result);
    }


    /**
     * @dataProvider params_test_calcRewards_指定したステージをクリアした際に獲得できる報酬を算出できている
     */
    public function test_calcRewards_指定したステージをクリアした際に獲得できる報酬を算出できている(
        int $clearCount,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 10,
            'exp' => 0,
            'coin' => 0,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 1000],
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 10);

        $mstStage = MstStage::factory()->create([
            'id' => '10',
            'exp' => 1000,
            'coin' => 1000,
        ]);
        MstStageReward::factory()->create([
            'mst_stage_id' => '10',
            'reward_category' => StageRewardCategory::FIRST_CLEAR,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 10,
            'percentage' => 100,
        ]);
        MstStageReward::factory()->create([
            'mst_stage_id' => '10',
            'reward_category' => StageRewardCategory::ALWAYS,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'percentage' => 100,
        ]);
        $usrStage = UsrStage::factory()->createAndConvert([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '10',
            'clear_count' => $clearCount,

        ]);

        // Exercise
        $this->stageEndNormalQuestService->addMstStageRewards($mstStage->toEntity(), $usrStage, collect(), 1);
        $this->rewardDelegator->sendRewards($usrUserId, 1, CarbonImmutable::now());
        $this->saveAll();

        // Verify
        if ($usrStage->isFirstClear()) {
            // 初クリア時
            $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
            $diamond = $this->getDiamond($usrUserId);

            $this->assertEquals(1000, $usrUserParameter->getExp());
            $this->assertEquals(1100, $usrUserParameter->getCoin());
            $this->assertEquals(10 + 10, $diamond->getFreeAmount());
        } else {
            // 2回目以降
            $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
            $diamond = $this->getDiamond($usrUserId);

            $this->assertEquals(1000, $usrUserParameter->getExp());
            $this->assertEquals(1100, $usrUserParameter->getCoin());
            $this->assertEquals(10, $diamond->getFreeAmount());
        }
    }

    public static function params_test_calcRewards_指定したステージをクリアした際に獲得できる報酬を算出できている()
    {
        return [
            '初クリア' => ['clearCount' => 1],
            '2回目以降' => ['clearCount' => 2],
        ];
    }
}
