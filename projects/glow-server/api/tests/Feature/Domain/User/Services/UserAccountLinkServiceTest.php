<?php

namespace Feature\Domain\User\Services;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Log\Models\LogBank;
use App\Domain\User\Enums\BnidLinkActionType;
use App\Domain\User\Models\LogBnidLink;
use App\Domain\User\Models\UsrOsPlatform;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Services\UserAccountLinkService;
use Tests\TestCase;

class UserAccountLinkServiceTest extends TestCase
{
    private UserAccountLinkService $userAccountLinkService;
    private AccessTokenService $accessTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userAccountLinkService = app(UserAccountLinkService::class);
        $this->accessTokenService = app(AccessTokenService::class);
    }

    public function testLinkBnid_新規にアカウント連携ができる()
    {
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $platform = System::PLATFORM_ANDROID;
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId()
        ]);
        $accessToken = $this->accessTokenService->create($usrUser->getId(), $usrDevice->getId(), $now);
        $ip = '0.0.0.0';

        $code = 'dummy';
        $linkBnidData = $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, false, $accessToken, $ip, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        $this->assertNull($linkBnidData->getIdToken());
        $this->assertEquals($now->toDateTimeString(), $linkBnidData->getBnidLinkedAt());

        $usrUser = UsrUser::query()->where('id', $usrUser->getId())->first();
        $this->assertEquals('dummy_user_id', $usrUser->getBnUserId());
        $usrDevice = UsrDevice::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($now->toDateTimeString(), $usrDevice->getBnidLinkedAt());

        // ログの確認
        $logBnidLink = LogBnidLink::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUser->getId(), $logBnidLink->getUsrUserId());
        $this->assertEquals(BnidLinkActionType::LINK_FROM_TITLE->value, $logBnidLink->action_type);
        $this->assertEquals(null, $logBnidLink->before_bn_user_id);
        $this->assertEquals('dummy_user_id', $logBnidLink->after_bn_user_id);
        $this->assertEquals($usrDevice->id, $logBnidLink->usr_device_id);
        $this->assertEquals($platform, $logBnidLink->os_platform);

        $logBank = LogBank::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertNull($logBank);
    }

    public function testLinkBnid_1台目と2台目のプラットフォームが同じアカウント連携()
    {
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        $platform = System::PLATFORM_IOS;
        $linkedUsrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString(),
            'os_platform' => $platform
        ]);
        UsrOsPlatform::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'os_platform' => $platform
        ]);

        $usrUser = $this->createUsrUser();
        UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => null,//$now->toDateTimeString(),
            'os_platform' => $platform
        ]);

        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, false, 'token', $ip, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        $usrUser = UsrUser::query()->where('id', $linkedUsrUser->getId())->first();
        $this->assertEquals('dummy_user_id', $usrUser->getBnUserId());
        $usrDevices = UsrDevice::query()->where('usr_user_id', $linkedUsrUser->getId())->get();
        $this->assertCount(2, $usrDevices);

        foreach ($usrDevices as $usrDevice) {
            if ($usrDevice->getId() === $linkedUsrDevice->getId()) {
                // 1台目デバイスの連携日時は変更されていない
                $this->assertEquals($linkedUsrDevice->getBnidLinkedAt(), $usrDevice->getBnidLinkedAt());
            } else {
                // 2台目デバイスの連携日時が更新されている
                $this->assertEquals($now->toDateTimeString(), $usrDevice->getBnidLinkedAt());
            }
        }

        // プラットフォームは1つのまま
        $usrOsPlatforms = UsrOsPlatform::query()->where('usr_user_id', $linkedUsrUser->getId())->get();
        $this->assertCount(1, $usrOsPlatforms);
        $this->assertEquals($platform, $usrOsPlatforms->first()->getOsPlatform());

        // アカウント連携済み端末と新しくアカウント連携する端末が同じ場合はBankのログは生成されない
        $logBank = LogBank::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertNull($logBank);
    }

    public function testLinkBnid_1台目と2台目のプラットフォームが同じアカウント連携でusr_os_platformsレコードがない()
    {
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        $platform = System::PLATFORM_IOS;
        UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString(),
            'os_platform' => $platform
        ]);

        $usrUser = $this->createUsrUser();
        UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => null,//$now->toDateTimeString(),
            'os_platform' => $platform
        ]);

        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, false, 'token', $ip, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        // プラットフォームデータが作成されていること
        $usrOsPlatforms = UsrOsPlatform::query()->where('usr_user_id', $linkedUsrUser->getId())->get();
        $this->assertCount(1, $usrOsPlatforms);
        $this->assertEquals($platform, $usrOsPlatforms->first()->getOsPlatform());
    }

    public function testLinkBnid_1台目と2台目のプラットフォームが異なるアカウント連携()
    {
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        // アカウント連携済みのプラットフォームがandroidで新たに連携するプラットフォームがios
        $linkedUsrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString(),
            'os_platform' => System::PLATFORM_ANDROID
        ]);
        UsrOsPlatform::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'os_platform' => System::PLATFORM_ANDROID
        ]);

        $usrUser = $this->createUsrUser();
        $platform = System::PLATFORM_IOS;
        UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => null,//$now->toDateTimeString(),
            'os_platform' => $platform
        ]);

        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, false, 'token', $ip, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        $usrUser = UsrUser::query()->where('id', $linkedUsrUser->getId())->first();
        $this->assertEquals('dummy_user_id', $usrUser->getBnUserId());
        $usrDevices = UsrDevice::query()->where('usr_user_id', $linkedUsrUser->getId())->get();
        $this->assertCount(2, $usrDevices);

        foreach ($usrDevices as $usrDevice) {
            if ($usrDevice->getId() === $linkedUsrDevice->getId()) {
                // 1台目デバイスの連携日時は変更されていない
                $this->assertEquals($linkedUsrDevice->getBnidLinkedAt(), $usrDevice->getBnidLinkedAt());
            } else {
                // 2台目デバイスの連携日時が更新されている
                $this->assertEquals($now->toDateTimeString(), $usrDevice->getBnidLinkedAt());
            }
        }

        // プラットフォームは2つに増えている
        $usrOsPlatforms = UsrOsPlatform::query()->where('usr_user_id', $linkedUsrUser->getId())->get();
        $this->assertCount(2, $usrOsPlatforms);
        $platforms = $usrOsPlatforms->map(fn(UsrOsPlatform $usrOsPlatform) => $usrOsPlatform->getOsPlatform());
        $this->assertContains(System::PLATFORM_ANDROID, $platforms);
        $this->assertContains(System::PLATFORM_IOS, $platforms);

        $logBank = LogBank::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(100, $logBank->event_id);
    }

    public function testLinkBnid_ホーム画面からの連携は新規連携のみできる()
    {
        $now = $this->fixTime();
        // BNID連携済みユーザーを作成
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString()
        ]);

        // 連携用ユーザー
        $usrUser = $this->createUsrUser();
        UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => null,//$now->toDateTimeString()
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_BNID_LINKED_OTHER_USER);

        $platform = System::PLATFORM_IOS;
        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, true, 'token', $ip, $now);
    }

    public function testLinkBnid_連携されているアカウントが連携制限アカウント()
    {
        // Setup
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id', 'is_account_linking_restricted' => 1]);
        UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString()
        ]);

        $usrUser = $this->createUsrUser();
        UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => $now->toDateTimeString()
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_ACCOUNT_LINKING_RESTRICTED_OTHER_ACCOUNT);

        $platform = System::PLATFORM_IOS;
        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnid($usrUser->getId(), $platform, $code, false, 'token', $ip, $now);
    }

    public function testLinkBnidConfirm_紐づくデータが取得できる()
    {
        $level = 10;
        $name = 'linked_user';
        $myId = fake()->uuid();
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id']);
        UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString()
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'level' => $level
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'name' => $name,
            'my_id' => $myId,
        ]);

        $code = 'dummy';
        $ip = '0.0.0.0';
        $bnidLinkedUserData = $this->userAccountLinkService->linkBnidConfirm($code, $ip);
        $this->assertEquals($name, $bnidLinkedUserData->getName());
        $this->assertEquals($level, $bnidLinkedUserData->getLevel());
        $this->assertEquals($myId, $bnidLinkedUserData->getMyId());
    }

    public function testLinkBnidConfirm_連携されているアカウントが連携制限アカウント()
    {
        // Setup
        $level = 10;
        $name = 'linked_user';
        $now = $this->fixTime();
        $linkedUsrUser = UsrUser::factory()->create(['bn_user_id' => 'dummy_user_id', 'is_account_linking_restricted' => 1]);
        UsrDevice::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'bnid_linked_at' => $now->subHour()->toDateTimeString()
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'level' => $level
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $linkedUsrUser->getId(),
            'name' => $name
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_ACCOUNT_LINKING_RESTRICTED_OTHER_ACCOUNT);

        $code = 'dummy';
        $ip = '0.0.0.0';
        $this->userAccountLinkService->linkBnidConfirm($code, $ip);
    }

    public function testUnlinkBnid_連携解除ができる()
    {
        // Setup
        $now = $this->fixTime();
        $platform = System::PLATFORM_ANDROID;
        $usrUser = $this->createUsrUser([
            'bn_user_id' => 'before_bn_user_1'
        ]);
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => $now->subDays(31)->toDateTimeString()
        ]);
        UsrOsPlatform::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'os_platform' => $platform
        ]);
        $accessToken = $this->accessTokenService->create($usrUser->getId(), $usrDevice->getId(), $now);

        // Exercise
        $this->userAccountLinkService->unlinkBnid($usrUser->getId(), $accessToken, $platform);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrDeviceId = $usrDevice->getId();
        $usrDevice = UsrDevice::query()->where('id', $usrDeviceId)->where('usr_user_id', $usrUser->getId())->first();
        $this->assertNull($usrDevice);

        $usrOsPlatform = UsrOsPlatform::query()->where('usr_user_id', $usrUser->getId())
            ->where('os_platform', $platform)
            ->first();
        $this->assertNotNull($usrOsPlatform);

        $userIdKey = $this->getClassPrivateConstantValue(
            AccessTokenService::class,
            'TOKEN_TO_USERID_AND_DEVICEID_CACHEKEY_PREFIX'
        ) . $accessToken;
        $this->assertNull($this->getFromRedis($userIdKey));

        $accessTokenKey = $this->getClassPrivateConstantValue(
            AccessTokenService::class,
            'USERID_TO_TOKEN_CACHEKEY_PREFIX'
        ) . $usrUser->getId();
        $this->assertNull($this->getFromRedis($accessTokenKey));

        // ログの確認
        $logBnidLink = LogBnidLink::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUser->getId(), $logBnidLink->getUsrUserId());
        $this->assertEquals(BnidLinkActionType::UNLINK->value, $logBnidLink->action_type);
        $this->assertEquals('before_bn_user_1', $logBnidLink->before_bn_user_id);
        $this->assertEquals(null, $logBnidLink->after_bn_user_id);
        $this->assertEquals($usrDeviceId, $logBnidLink->usr_device_id);
        $this->assertEquals($platform, $logBnidLink->os_platform);
    }

    public function testUnlinkBnid_連携解除時にusr_os_platformsが未登録の場合は登録される()
    {
        // Setup
        $now = $this->fixTime();
        $platform = System::PLATFORM_ANDROID;
        $usrUser = $this->createUsrUser([
            'bn_user_id' => 'before_bn_user_1'
        ]);
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => $now->subDays(31)->toDateTimeString()
        ]);
        $accessToken = $this->accessTokenService->create($usrUser->getId(), $usrDevice->getId(), $now);

        // Exercise
        $this->userAccountLinkService->unlinkBnid($usrUser->getId(), $accessToken, $platform);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrOsPlatforms = UsrOsPlatform::query()->where('usr_user_id', $usrUser->getId())
            ->where('os_platform', $platform)
            ->get();
        $this->assertCount(1, $usrOsPlatforms);
        $usrOsPlatform = $usrOsPlatforms->first();
        $this->assertNotNull($usrOsPlatform);
    }

    public function testUnlinkBnid_未連携のため連携解除できない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => null
        ]);
        $accessToken = $this->accessTokenService->create($usrUser->getId(), $usrDevice->getId(), $now);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_BNID_NOT_LINKED);

        $this->userAccountLinkService->unlinkBnid($usrUser->getId(), $accessToken, System::PLATFORM_ANDROID);
    }
}
