<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Constants\System;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\User\Models\UsrUserProfile;
use App\Exceptions\HttpStatusCode;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class WithMiddlewareControllerTest extends BaseControllerWithMiddlewareTestCase
{
    use FakeStoreReceiptTrait;

    private AccessTokenService $accessTokenService;
    private CurrencyDelegator $currencyDelegator;
    private BillingDelegator $billingDelegator;

    protected string $baseUrl = '/api/shop/';

    public function setUp(): void
    {
        parent::setUp();

        $this->accessTokenService = $this->app->make(AccessTokenService::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->billingDelegator = $this->app->make(BillingDelegator::class);
    }

    public function test_ショップのAPIでmiddleware正常動作確認()
    {
        // Setup
        $this->createMasterRelease();
        $now = $this->fixTime();
        $url = 'allowance';
        $params = [
            'productSubId' => 'edmo_pack_160_1_framework',
            'productId' => 'ios_edmo_pack_160_1_framework',
            'currencyCode' => 'JPY',
            'price' => '100.000',
        ];
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();
        $usrUser = $this->createUsrUser();

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'birth_date' => '1980-07-01',
        ]);
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUser->getId()
        ]);

        $accessToken = $this->accessTokenService->create($usrUser->getId(), $usrDevice->getId(), $now->toDateTimeString());
        $requestId = 'client-request-id';

        // 購入管理データを作成
        $this->currencyDelegator->createUser(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
            0
        );

        // 年齢確認用ショップデータを作成
        $this->billingDelegator->setStoreInfo($usrUser->getId(), 20, null);

        // Exercise
        $response = $this->withHeaders([
            System::HEADER_ACCESS_TOKEN => $accessToken,
            'Unique-Request-Identifier' => $requestId,
        ])->sendRequest($url, $params);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals('edmo_pack_160_1_framework', $response->json('productSubId'));
        $this->assertEquals('ios_edmo_pack_160_1_framework', $response->json('productId'));
    }
}
