<?php

declare(strict_types=1);

namespace Feature\Domain\User\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\UseCases\UserLinkBnidConfirmUseCase;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class UserLinkBnidConfirmUseCaseTest extends TestCase
{
    private UserLinkBnidConfirmUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(UserLinkBnidConfirmUseCase::class);
    }

    public function test_exec_連携先アカウントの情報が取得できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUser::factory()->create([
            'id' => 'dummy_user',
            'bn_user_id' => 'dummy_user_id',
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => 'dummy_user',
            'level' => 5,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => 'dummy_user',
            'name' => 'dummy_name',
            'my_id' => 'dummy_my_id',
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, 'dummy_code', '127.0.0.1');

        // Verify
        $this->assertEquals($usrUserParameter->level, $result->bnidLinkedUserData->getLevel());
        $this->assertEquals($usrUserProfile->name, $result->bnidLinkedUserData->getName());
        $this->assertEquals($usrUserProfile->my_id, $result->bnidLinkedUserData->getMyId());
    }

    public function test_exec_自身がアカウント連携凍結の場合はエラー()
    {
        // Setup
        $usrUserId = $this->createUsrUser(['is_account_linking_restricted' => 1])->getId();
        $currentUser = new CurrentUser($usrUserId);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_ACCOUNT_LINKING_RESTRICTED_MY_ACCOUNT);
        $this->useCase->exec($currentUser, 'dummy_code', '127.0.0.1');
    }
}
