<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\User\UseCases;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\UseCases\UserChangeAvatarUseCase;
use Tests\TestCase;

class UserChangeAvatarUseCaseTest extends TestCase
{
    private UserChangeAvatarUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(UserChangeAvatarUseCase::class);
    }

    public function test_exec_リーダーアバターを登録する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $beforeMstUnitId = '1';
        $mstUnitId = '2';
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $beforeMstUnitId,
        ]);
        UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnitId,
        ]);
        MstUnit::factory()->create([
            'id' => $mstUnitId,
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstUnitId);

        // Verify
        $this->assertEquals($mstUnitId, $result->usrUserProfile->getMstUnitId());

        // DB確認
        $usrUserProfile->refresh();
        $this->assertEquals($mstUnitId, $usrUserProfile->getMstUnitId());
    }
}
