<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEnhanceRewardParam;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Services\StageEndEnhanceQuestService;
use App\Domain\User\Models\UsrUserParameter;
use Tests\TestCase;

class StageEndEnhanceQuestServiceTest extends TestCase
{
    private StageEndEnhanceQuestService $stageEndEnhanceQuestService;
    private RewardDelegator $rewardDelegator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageEndEnhanceQuestService = app(StageEndEnhanceQuestService::class);
        $this->rewardDelegator = app(RewardDelegator::class);
    }

    public static function params_test_consumeLapStaminaCost_lapCountで消費スタミナが増加する()
    {
        return [
            'lapCountが１の場合スタミナを消費しない' => [
                'lapCount' => 1,
                'costStamina' => 10,
                'beforeStamina' => 100,
                'afterStamina' => 100,
            ],
            'lapCountが2の場合スタミナを1回分消費する' => [
                'lapCount' => 2,
                'costStamina' => 10,
                'beforeStamina' => 100,
                'afterStamina' => 90,
            ],
            'lapCountが5の場合スタミナを4回分消費する' => [
                'lapCount' => 5,
                'costStamina' => 10,
                'beforeStamina' => 100,
                'afterStamina' => 60,
            ],
        ];
    }

    /**
     * @dataProvider params_test_consumeLapStaminaCost_lapCountで消費スタミナが増加する
     */
    public function test_consumeLapStaminaCost_lapCountに対応したスタミナを消費する(
        int $lapCount,
        int $costStamina,
        int $beforeStamina,
        int $afterStamina,
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => $beforeStamina,
        ]);

        $this->stageEndEnhanceQuestService->consumeLapStaminaCost(
            usrUserId: $usrUserId,
            mstStage: MstStage::factory()->create([
                'cost_stamina' => $costStamina,
            ])->toEntity(),
            now: $this->fixTime(),
            oprCampaigns: collect(),
            lapCount: $lapCount,
        );

        // スタミナチェック
        $this->saveAll();
        $usrUserParameter->refresh();
        $this->assertEquals( $afterStamina, $usrUserParameter->getStamina());
    }

    public static function params_test_lapCount分報酬を習得できる()
    {
        return [
            'lapCountが1の場合1回分の報酬を獲得できる' => [
                'lapCount' => 1,
                'coinRewardAmount' => 100,
            ],
            'lapCountが3の場合3回分の報酬を獲得できる' => [
                'lapCount' => 3,
                'coinRewardAmount' => 300,
            ],
        ];
    }

    /**
     * @dataProvider params_test_lapCount分報酬を習得できる
     */
    public function test_addCoinRewards_mstStageEnhanceRewardParamで設定した報酬がlapCount分獲得できている(
        int $lapCount,
        int $coinRewardAmount,
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 100,
            'coin' => 0,
        ]);

        $mstStage = MstStage::factory()->create()->toEntity();
        MstStageEnhanceRewardParam::factory()->create([
            'min_threshold_score' => 0,
            'coin_reward_amount' => $coinRewardAmount,
        ]);

        $this->stageEndEnhanceQuestService->addCoinRewards(
            usrUserId: $usrUserId,
            mstStage: $mstStage,
            oprCampaigns: collect(),
            score: 0,
            partyNo: 1,
            now: $this->fixTime(),
            lapCount: $lapCount,
        );

        $this->rewardDelegator->sendRewards($usrUserId, 1, $this->fixTime());
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($coinRewardAmount * $lapCount, $usrUserParameter->getCoin());
    }

}
