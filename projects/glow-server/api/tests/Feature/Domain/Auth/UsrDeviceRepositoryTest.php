<?php

namespace Tests\Feature\Domain\Auth;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Repositories\UsrDeviceRepository;
use App\Domain\Auth\Services\IdTokenService;
use App\Domain\Auth\Services\UserDeviceService;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class UsrDeviceRepositoryTest extends TestCase
{
    private UsrDeviceRepository $usrDeviceRepository;

    private UserDeviceService $userDeviceService;

    private IdTokenService $idTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrDeviceRepository = $this->app->make(UsrDeviceRepository::class);
        $this->userDeviceService = $this->app->make(UserDeviceService::class);
        $this->idTokenService = $this->app->make(IdTokenService::class);
    }

    public function testDeleteByUserId_ユーザーに紐付くデバイスが削除される()
    {
        // Setup
        $userDevice = UsrDevice::factory()->create();

        // Exercise
        $this->usrDeviceRepository->deleteByUserId($userDevice->usr_user_id);

        // Verify
        $idToken = $this->idTokenService->create($userDevice->uuid);
        $result = $this->userDeviceService->findByIdToken($idToken);
        $this->assertNull($result);
    }

    public function testCreate_デバイスが作成される()
    {
        // Setup
        $userId = '1';
        $uuid = 'hoge';

        // Exercise
        $this->usrDeviceRepository->create($userId, $uuid);

        // Verify
        $idToken = $this->idTokenService->create($uuid);
        $result = $this->userDeviceService->findByIdToken($idToken);
        $this->assertEquals($userId, $result->usr_user_id);
        $this->assertEquals($uuid, $result->getUuid());
    }

    public function testCreate_UUIDが重複したらユニーク制約エラー()
    {
        // Setup
        $userId = '1';
        $uuid = 'hoge';
        UsrDevice::factory()->state([
            'usr_user_id' => $userId,
            'uuid' => $uuid,
        ])->create();

        $otherUserId = 2;
        $this->expectException(QueryException::class);
        $this->expectExceptionCode(23000);

        // Exercise
        $this->usrDeviceRepository->create($otherUserId, $uuid);

        // Verify
    }
}
