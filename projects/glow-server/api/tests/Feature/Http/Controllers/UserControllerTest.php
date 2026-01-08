<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Common\Constants\System;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Models\UsrOsPlatform;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\UseCases\UserChangeAvatarUseCase;
use App\Domain\User\UseCases\UserChangeEmblemUseCase;
use App\Domain\User\UseCases\UserChangeNameUseCase;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\ResultData\UserChangeAvatarResultData;
use App\Http\Responses\ResultData\UserChangeEmblemResultData;
use Mockery\MockInterface;

class UserControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/user/';

    /**
     * @test
     */
    public function change_name_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);
        $this->createDiamond($usrUser->getId(), 6, 7, 8);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'is_change_name' => 1,
            'name_update_at' => null,
        ]);

        $name = "hoge";
        // Exercise
        $this->mock(UserChangeNameUseCase::class, function (MockInterface $mock) {
            $mock->shouldReceive('exec')->andReturn([]);
        });
        $response = $this->sendRequest('change_name', ['name' => $name]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function change_avatar_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'is_change_name' => 1,
            'mst_unit_id' => '2',
            'name_update_at' => null,
        ]);

        $resultData = new UserChangeAvatarResultData(
            $usrUserProfile
        );

        $mstUnitId = "1";
        // Exercise
        $this->mock(UserChangeAvatarUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });
        $response = $this->sendRequest('change_avatar', ['mstUnitId' => $mstUnitId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function change_emblem_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'is_change_name' => 1,
            'mst_unit_id' => '2',
            'mst_emblem_id' => '2',
            'name_update_at' => null,
        ]);

        $resultData = new UserChangeEmblemResultData(
            $usrUserProfile
        );

        $mstEmblemId = "3";
        // Exercise
        $this->mock(UserChangeEmblemUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });
        $response = $this->sendRequest('change_emblem', ['mstEmblemId' => $mstEmblemId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function change_emblem_エンブレムを外す場合もリクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'is_change_name' => 1,
            'mst_unit_id' => '2',
            'mst_emblem_id' => '2',
            'name_update_at' => null,
        ]);

        $resultData = new UserChangeEmblemResultData(
            $usrUserProfile
        );

        $mstEmblemId = "";
        // Exercise
        $this->mock(UserChangeEmblemUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });
        $response = $this->sendRequest('change_emblem', ['mstEmblemId' => $mstEmblemId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function change_emblem_nullが入ってきた場合もリクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => 'test',
            'is_change_name' => 1,
            'mst_unit_id' => '2',
            'mst_emblem_id' => '2',
            'name_update_at' => null,
        ]);

        $resultData = new UserChangeEmblemResultData(
            $usrUserProfile
        );

        $mstEmblemId = null;
        // Exercise
        $this->mock(UserChangeEmblemUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });
        $response = $this->sendRequest('change_emblem', ['mstEmblemId' => $mstEmblemId]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function testBuyStaminaAd_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
        ]);
        $this->createDiamond($usrUser->getId());
        MstUserLevel::factory()->create([
            'level' => 1,
            'stamina' => 10,
        ]);

        $response = $this->sendRequest('buy_stamina_ad');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('usrBuyCount', $response);
    }

    public function testBuyStaminaDiamond_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'stamina' => 0
        ]);
        $this->createDiamond($usrUser->getId(), 100);
        MstUserLevel::factory()->create([
            'level' => 1,
            'stamina' => 10,
        ]);
        $this->createDiamond($usrUser->getId(), 100);

        $response = $this->sendRequest('buy_stamina_diamond');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('usrBuyCount', $response);
    }

    public function testLinkBnid_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        // apiリクエストユーザー
        $usrUserId = $this->createUsrUser()->getId();
        $platform = System::PLATFORM_ANDROID;
        $usrDeviceId = UsrDevice::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $platform
        ])->getId();

        // 連携済みユーザー
        $bnIdLinkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        UsrDevice::factory()->create([
            'usr_user_id' => $bnIdLinkedUsrUser->getId(),
            'os_platform' => System::PLATFORM_IOS
        ]);

        // Exercise
        $accessToken = 'access-token';
        $this->setToRedis("token:userid:deviceid:$accessToken", "$usrUserId,$usrDeviceId");
        $this->setToRedis("userid:token:$usrUserId", $accessToken);
        $response = $this
            ->withHeaders([System::HEADER_ACCESS_TOKEN => $accessToken])
            ->sendRequest('link_bnid', ['code' => 'test_code', 'isHome' => false]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('idToken', $response);
        $this->assertArrayHasKey('bnidLinkedAt', $response);
    }

    public function testUnlinkBnid_リクエストを送ると200OKが返ってくる()
    {
        // Setup
        $usrUserId = $this->createUsrUser(['bn_user_id' => 'dummy_user_id'])->getId();
        $platform = System::PLATFORM_ANDROID;
        $usrDeviceId = UsrDevice::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => $platform,
            'bnid_linked_at' => now()->toDateTimeString(),
        ])->getId();

        // Exercise
        $accessToken = 'access-token';
        $this->setToRedis("token:userid:deviceid:$accessToken", "$usrUserId,$usrDeviceId");
        $this->setToRedis("userid:token:$usrUserId", $accessToken);
        $response = $this
            ->withHeaders([System::HEADER_ACCESS_TOKEN => $accessToken])
            ->sendRequest('unlink_bnid');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }
}
