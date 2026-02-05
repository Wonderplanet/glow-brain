<?php

namespace Feature\Domain\Auth;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Services\IdTokenService;
use App\Domain\Auth\Services\UserDeviceService;
use Tests\TestCase;

class UserDeviceServiceTest extends TestCase
{
    private UserDeviceService $userDeviceService;

    private IdTokenService $idTokenService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userDeviceService = $this->app->make(UserDeviceService::class);
        $this->idTokenService = $this->app->make(IdTokenService::class);
    }

    public function testFindByIdToken_IDトークンからデバイスを検索できる()
    {
        // Setup
        $userDevice = UsrDevice::factory()->create();
        $idToken = $this->idTokenService->create($userDevice->uuid);

        // Exercise
        $result = $this->userDeviceService->findByIdToken($idToken);

        // Verify
        $this->assertEquals($userDevice->uuid, $result->getUuid());
    }
}
