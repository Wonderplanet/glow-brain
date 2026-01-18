<?php

namespace Tests\Feature\Domain\User;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Repositories\UsrUserRepository;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UsrUserRepository $userService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->app->make(UsrUserRepository::class);
    }

    public function test_findById_IDが存在しない場合はnullが返ってくる()
    {
        // Setup
        $userId = 'invalid_user_id';

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_NOT_FOUND);

        // Exercise
        $result = $this->userService->findById($userId);

        // Verify
        $this->assertNull($result);
    }

    public function test_findById_IDが存在した場合はUserモデルが返ってくる()
    {
        // Setup
        UsrUser::factory()->create([
            'id' => 'user1',
        ]);

        // Exercise
        $result = $this->userService->findById('user1');

        // Verify
        $this->assertEquals('user1', $result->getId());
    }

    // /**
    //  * @test
    //  */
    // public function create_Userモデルが作成される()
    // {
    //     // Exercise
    //     $now = $this->fixTime();
    //     $user = $this->userService->create($now);

    //     // Verify
    //     $result = $this->userService->findById($user->getId());
    //     $this->assertEquals($user->getId(), $result->getId());
    // }
}
