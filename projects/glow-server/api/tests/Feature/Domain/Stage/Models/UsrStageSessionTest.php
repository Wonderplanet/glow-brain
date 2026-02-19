<?php

namespace Tests\Feature\Domain\User;

use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\UsrStageSession;
use Tests\TestCase;

class UsrStageSessionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function isStarted_ステージ開始済みであることを確認()
    {
        // Setup
        $mstStageId = '1';
        $usrUser = $this->createUsrUser();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
        ]);

        // Exercise

        // Verify
        $isStarted = $usrStageSession->isStartedByMstStageId($mstStageId);
        $this->assertEquals($isStarted, true);

        $isStarted = $usrStageSession->isStartedByMstStageId('2');
        $this->assertEquals($isStarted, false);
    }

    /**
     * @test
     */
    public function closeSession_ステージセッションが閉じることを確認()
    {
        // Setup
        $mstStageId = '1';
        $usrUser = $this->createUsrUser();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
        ]);

        // Exercise
        $usrStageSession->closeSession();

        // Verify
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());
    }

    /**
     * @test
     */
    public function startSession_ステージセッションが開始することを確認()
    {
        // Setup
        $mstStageId = '1';
        $usrUser = $this->createUsrUser();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => '0',
            'is_valid' => 0,
        ]);

        // Exercise
        $oprCampaignIds = collect(['1', '2']);
        $usrStageSession->startSession($mstStageId, 3, $oprCampaignIds, false, 1);

        // Verify
        $this->assertEquals($usrStageSession->getMstStageId(), $mstStageId);
        $this->assertEquals(StageSessionStatus::STARTED, $usrStageSession->getIsValid());
        $this->assertEquals(3, $usrStageSession->getPartyNo());
        $this->assertEquals(0, $usrStageSession->getContinueCount());
        $this->assertEquals($oprCampaignIds, $usrStageSession->getOprCampaignIds());
    }
}
