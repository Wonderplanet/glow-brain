<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\User\UseCases;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\UseCases\UserChangeEmblemUseCase;
use Tests\TestCase;

class UserChangeEmblemUseCaseTest extends TestCase
{
    private UserChangeEmblemUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(UserChangeEmblemUseCase::class);
    }

    public function test_exec_エンブレムを登録する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $beforeMstEmblemId = '1';
        $mstEmblemId = '2';
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        MstEmblem::factory()->create([
            'id' => $mstEmblemId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $mstEmblemId,
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstEmblemId);

        // Verify
        $this->assertEquals($mstEmblemId, $result->usrUserProfile->getMstEmblemId());

        // DB確認
        $usrUserProfile->refresh();
        $this->assertEquals($mstEmblemId, $usrUserProfile->getMstEmblemId());
    }

    public function test_exec_エンブレムを外す()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $beforeMstEmblemId = '1';
        $mstEmblemId = '';
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstEmblemId);

        // Verify
        $this->assertEquals($mstEmblemId, $result->usrUserProfile->getMstEmblemId());

        // DB確認
        $usrUserProfile->refresh();
        $this->assertEquals($mstEmblemId, $usrUserProfile->getMstEmblemId());
    }
}
