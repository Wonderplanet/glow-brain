<?php

namespace Tests\Feature\Domain\User;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use Tests\TestCase;

class UsrUserProfileServiceTest extends TestCase
{
    private UsrUserProfileRepository $usrUserProfileService;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrUserProfileService = $this->app->make(UsrUserProfileRepository::class);
    }

    /**
     * @test
     */
    public function findByUsrUserId_userIDが存在しない場合はnullが返ってくる()
    {
        // Setup
        $userId = 1;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_NOT_FOUND);

        // Exercise
        $result = $this->usrUserProfileService->findByUsrUserId($userId);

        // Verify
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function findByUsrUserId_userIdがある場合はUsrUserProfileモデルを返す()
    {
        // Setup
        $userId = '1';
        $user = UsrUserProfile::factory()->create(['usr_user_id' => $userId]);

        // Exercise
        $result = $this->usrUserProfileService->findByUsrUserId($user->getUsrUserId());

        // Verify
        $this->assertEquals($user->getUsrUserId(), $result->getUsrUserId());
    }

    // /**
    //  * @test
    //  */
    // public function getUsrMyId_MyIdが存在しない場合はnullが返ってくる()
    // {
    //     // Setup
    //     $myId = 1;

    //     $this->expectException(GameException::class);
    //     $this->expectExceptionCode(ErrorCode::USER_NOT_FOUND);

    //     // Exercise
    //     $result = $this->usrUserProfileService->findByMyId($myId);

    //     // Verify
    //     $this->assertNull($result);
    // }

    // /**
    //  * @test
    //  */
    // public function findByMyId_MyIdがある場合はUsrUserProfileモデルを返す()
    // {
    //     // Setup
    //     $myId = fake()->uuid();
    //     $user = UsrUserProfile::factory()->create(['my_id' => $myId]);

    //     // Exercise
    //     $result = $this->usrUserProfileService->findByMyId($user->getMyId());

    //     // Verify
    //     $this->assertEquals($user->getMyId(), $result->getMyId());
    // }

    /**
     * @test
     */
    public function generateMyId_myIdが先頭が0ではない10桁になっているか確認()
    {
        // Setup

        // Exercise
        $result = $this->execPrivateMethod($this->usrUserProfileService, 'generateMyId');

        $jpPrefix = UserConstant::REGION_MY_ID_PREFIX['JP'];

        $resultPrefix = substr($result, 0, 1);
        $resultId = substr($result, 1);

        // Verify
        $this->assertEquals($jpPrefix, $resultPrefix);
        $this->assertGreaterThanOrEqual((int)$resultId, 9999999999);
        $this->assertLessThanOrEqual((int)$resultId, 1000000000);
    }
}
