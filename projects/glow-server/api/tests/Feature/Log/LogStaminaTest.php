<?php

namespace Tests\Feature\Log;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestLogTrait;

class LogStaminaTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_stage_start_ステージに挑戦しスタミナ消費ログが保存される()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $mstQuestId = 'quest1';
        $mstStageId = 'stage1';
        MstQuest::factory()->create([
            'id' => $mstQuestId,
        ]);
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => $mstQuestId,
            'cost_stamina' => 5,
        ])->toEntity();
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId);
        MstUserLevel::factory()->createMany([
            ['level' => 1]
        ]);

        // Exercise
        $requestData = [
            'mstStageId' => $mstStageId,
            'partyNo' => 1,
            'isChallengeAd' => false,
        ];
        $response = $this->sendRequest('stage/start', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->checkLogResourcesByUse(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::STAMINA,
            expectedAmounts: [
                ['before_amount' => 100, 'after_amount' => 95],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => LogResourceTriggerSource::STAGE_CHALLENGE_COST->value,
                    'trigger_value' => 'stage1',
                    'trigger_option' => '1',
                ],
            ]
        );
    }
}
