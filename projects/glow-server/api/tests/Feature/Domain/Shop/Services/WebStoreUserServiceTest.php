<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebStoreUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebStoreUserService $webStoreUserService;

    public function setUp(): void
    {
        parent::setUp();
        $this->webStoreUserService = $this->app->make(WebStoreUserService::class);
    }

    public function testValidateUser_正常系_ユーザー情報が正しく取得できること(): void
    {
        // Setup
        $bnUserId = 'bn_user_123';

        // ユーザー作成
        $usrUser = UsrUser::factory()->create(['bn_user_id' => $bnUserId]);
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);

        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'my_id' => 'MY123456',
            'name' => 'テストユーザー',
            'birth_date' => Crypt::encryptString('20000101'),
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 10
        ]);
        $usrWebstoreInfo = UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'country_code' => 'JP',
        ]);

        // Exercise
        $actual = $this->webStoreUserService->validateUser($bnUserId);

        // Verify
        $this->assertSame($usrUserProfile->getMyId(), $actual->id);
        $this->assertSame($usrUserId, $actual->internalId);
        $this->assertSame($usrUserProfile->getName(), $actual->name);
        $this->assertSame($usrUserParameter->getLevel(), $actual->level);
        $this->assertSame('20000101', $actual->birthday);
        $this->assertSame('200001', $actual->birthdayMonth);
        $this->assertSame($usrWebstoreInfo->getCountryCode(), $actual->country);
    }

    public function testValidateUser_異常系_ユーザーが存在しない場合(): void
    {
        // Setup
        $bnUserId = 'non_existent_bn_user';

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_USER_NOT_FOUND);

        $this->webStoreUserService->validateUser($bnUserId);
    }

    public function testValidateUser_異常系_誕生日が未設定の場合(): void
    {
        // Setup
        $bnUserId = 'bn_user_456';
        $usrUser = UsrUser::factory()->create(['bn_user_id' => $bnUserId]);
        $this->setUsrUserId($usrUser->getId());

        // 誕生日なしのプロフィール
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'birth_date' => '',
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_BIRTHDAY_REQUIRED);

        $this->webStoreUserService->validateUser($bnUserId);
    }

    public function testValidateUser_異常系_国コードが未登録の場合(): void
    {
        // Setup
        $bnUserId = 'bn_user_789';
        $usrUser = UsrUser::factory()->create(['bn_user_id' => $bnUserId]);
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString('20000101'),
        ]);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);

        // WebStore情報なし

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_COUNTRY_NOT_REGISTERED);

        $this->webStoreUserService->validateUser($bnUserId);
    }

    public function testRegisterWebStoreInfo_正常系_新規レコードが作成されること(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $countryCode = 'JP';
        $platform = UserConstant::PLATFORM_IOS;
        $adId = 'test_ad_id_123';

        // Exercise
        $this->webStoreUserService->registerWebStoreInfo($usrUserId, $countryCode, $platform, $adId);
        $this->saveAll();

        // Verify
        $this->assertDatabaseHas('usr_webstore_infos', [
            'usr_user_id' => $usrUserId,
            'country_code' => $countryCode,
            'os_platform' => System::PLATFORM_IOS,
            'ad_id' => $adId,
        ]);
    }

    public function testRegisterWebStoreInfo_正常系_既存レコードのnull項目が更新されること(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);

        // 既存レコード作成（os_platformとad_idがnull）
        UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'country_code' => 'JP',
            'os_platform' => null,
            'ad_id' => null,
        ]);

        $countryCode = 'JP'; // 同じ国コード
        $platform = UserConstant::PLATFORM_ANDROID;
        $adId = 'new_ad_id_456';

        // Exercise
        $this->webStoreUserService->registerWebStoreInfo($usrUserId, $countryCode, $platform, $adId);
        $this->saveAll();

        // Verify: os_platformとad_idが更新されている
        $this->assertDatabaseHas('usr_webstore_infos', [
            'usr_user_id' => $usrUserId,
            'country_code' => $countryCode, // 変更なし
            'os_platform' => System::PLATFORM_ANDROID,
            'ad_id' => $adId,
        ]);
    }

    public function testRegisterWebStoreInfo_正常系_countryCodeがnullの場合は何もしないこと(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $countryCode = null;
        $platform = UserConstant::PLATFORM_IOS;
        $adId = 'test_ad_id';

        // Exercise
        $this->webStoreUserService->registerWebStoreInfo($usrUserId, $countryCode, $platform, $adId);
        $this->saveAll();

        // Verify
        $this->assertDatabaseMissing('usr_webstore_infos', [
            'usr_user_id' => $usrUserId,
        ]);
    }

    public static function params_testCheckPurchaseRestriction()
    {
        return [
            '未成年の無料商品' => [
                'birthDate' => 20070101,
                'isPaidOrder' => false,
                'errorCode' => null,
            ],
            '未成年の有料商品' => [
                'birthDate' => 20070101,
                'isPaidOrder' => true,
                'errorCode' => ErrorCode::WEBSTORE_AGE_RESTRICTION,
            ],
            '成年の無料商品' => [
                'birthDate' => 20060101,
                'isPaidOrder' => false,
                'errorCode' => null,
            ],
            '成年の有料商品' => [
                'birthDate' => 20060101,
                'isPaidOrder' => true,
                'errorCode' => null,
            ],
        ];
    }

    #[DataProvider('params_testCheckPurchaseRestriction')]
    public function testCheckPurchaseRestriction_年齢制限チェックができていること(int $birthDate, bool $isPaidOrder, ?int $errorCode): void
    {
        // Setup
        $now = $this->fixTime('2024-01-15 12:00:00');

        if (!is_null($errorCode)) {
            // Exercise & Verify
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->webStoreUserService->checkPurchaseRestriction($birthDate, $isPaidOrder, $now);

        $this->assertTrue(true);
    }
}
