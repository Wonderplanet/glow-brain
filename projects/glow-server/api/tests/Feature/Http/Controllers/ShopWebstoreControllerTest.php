<?php

declare(strict_types=1);

namespace Feature\Http\Controllers;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Shop\Models\UsrWebstoreTransaction;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Exceptions\HttpStatusCode;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class ShopWebstoreControllerTest extends BaseControllerTestCase
{
    use FakeStoreReceiptTrait;

    private CurrencyDelegator $currencyDelegator;

    protected string $baseUrl = '/api/shop/';

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
    }

    /**
     * Xsolla署名を生成するヘルパーメソッド
     *
     * @param array $payload リクエストボディ
     * @param string $secret Webhookシークレット
     * @return string SHA-1署名
     */
    private function generateXsollaSignature(array $payload, string $secret): string
    {
        $jsonPayload = json_encode($payload);
        return sha1($jsonPayload . $secret);
    }

    /**
     * WebStoreウェブフック用のリクエスト送信メソッド
     *
     * @param array $payload リクエストボディ
     * @param string $webhookSecret Webhookシークレット
     * @return \Illuminate\Testing\TestResponse
     */
    private function sendWebStoreRequest(array $payload, string $webhookSecret = 'test_webhook_secret'): \Illuminate\Testing\TestResponse
    {
        // Configをモック
        config(['services.xsolla.webhook_secret' => $webhookSecret]);

        // 署名を生成
        $signature = $this->generateXsollaSignature($payload, $webhookSecret);

        // リクエスト送信（署名検証ミドルウェアを通す）
        return $this->withHeaders([
            'Authorization' => 'Signature ' . $signature,
            'Content-Type' => 'application/json',
        ])->postJson($this->baseUrl . 'webstore', $payload);
    }

    /**
     * W1: web_store_user_validation - ユーザー情報取得
     */
    public function test_webstore_w1_userValidation_正常系()
    {
        // Setup
        $bnUserId = 'test_bn_user_' . fake()->uuid();
        $usrUser = $this->createUsrUser([
            'bn_user_id' => $bnUserId,
        ]);
        $usrUserId = $usrUser->getId();

        // UsrUserProfileを作成（誕生日登録済み）
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString('19900101'),
            'name' => 'Test User',
        ]);

        // UsrUserParameterを作成（レベル情報）
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 10,
        ]);

        // UsrWebstoreInfoを作成（国コード登録済み）
        UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'country_code' => 'JP',
        ]);

        $payload = [
            'notification_type' => 'web_store_user_validation',
            'user' => [
                'user_id' => $bnUserId,
            ],
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'internal_id',
                'name',
                'level',
                'birthday',
                'birthday_month',
                'country',
            ],
        ]);

        $this->assertEquals($usrUserProfile->getMyId(), $response->json('user.id'));
        $this->assertEquals($usrUserId, $response->json('user.internal_id'));
        $this->assertEquals('Test User', $response->json('user.name'));
        $this->assertEquals(10, $response->json('user.level'));
        $this->assertEquals('19900101', $response->json('user.birthday'));
        $this->assertEquals('199001', $response->json('user.birthday_month'));
        $this->assertEquals('JP', $response->json('user.country'));
    }

    /**
     * W1: web_store_user_validation - BANユーザーの場合エラーとなること
     */
    public function test_webstore_w1_userValidation_異常系_BANユーザー()
    {
        // Setup
        $bnUserId = 'test_bn_user_' . fake()->uuid();
        $usrUser = $this->createUsrUser([
            'bn_user_id' => $bnUserId,
            'status' => UserStatus::BAN_PERMANENT->value,
        ]);
        $usrUserId = $usrUser->getId();

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString('19900101'),
        ]);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'country_code' => 'JP',
        ]);

        $payload = [
            'notification_type' => 'web_store_user_validation',
            'user' => [
                'user_id' => $bnUserId,
            ],
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(400);
        $this->assertEquals(ErrorCode::USER_ACCOUNT_BAN_PERMANENT, $response->json('error.code'));
    }

    /**
     * W2: web_store_payment_validation - 決済事前確認
     */
    public function test_webstore_w2_paymentValidation_正常系()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        $birthDate = '19900101';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString($birthDate),
        ]);

        // 通貨管理データを作成
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0
        );

        // マスターデータ作成
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => 'test_webstore_sku_001',
        ])->toEntity();

        OprProduct::factory()->create([
            'id' => 'test_product_001',
            'product_type' => ProductType::DIAMOND->value,
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'paid_amount' => 100,
        ]);

        $payload = [
            'notification_type' => 'web_store_payment_validation',
            'custom_parameters' => [
                'internal_id' => $usrUserId,
            ],
            'user' => [
                'birthday' => $birthDate,
            ],
            'purchase' => [
                'items' => [
                    [
                        'type' => 'virtual_good',
                        'sku' => 'test_webstore_sku_001',
                        'amount' => 100,
                    ],
                ],
            ],
            'is_sandbox' => false,
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJsonStructure([
            'transaction_id',
        ]);

        $transactionId = $response->json('transaction_id');

        // usr_webstore_transactionsに保存されたことを確認
        $this->assertDatabaseHas('usr_webstore_transactions', [
            'usr_user_id' => $usrUserId,
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_PENDING,
            'is_sandbox' => 0,
        ]);
    }

    /**
     * W2: web_store_payment_validation - BANユーザーの場合エラーとなること
     */
    public function test_webstore_w2_paymentValidation_異常系_BANユーザー()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'status' => UserStatus::BAN_PERMANENT->value,
        ]);
        $usrUserId = $usrUser->getId();

        $birthDate = '19900101';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString($birthDate),
        ]);

        // 通貨管理データを作成
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0
        );

        // マスターデータ作成
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => 'test_webstore_sku_001',
        ])->toEntity();

        OprProduct::factory()->create([
            'id' => 'test_product_001',
            'product_type' => ProductType::DIAMOND->value,
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'paid_amount' => 100,
        ]);

        $payload = [
            'notification_type' => 'web_store_payment_validation',
            'custom_parameters' => [
                'internal_id' => $usrUserId,
            ],
            'user' => [
                'birthday' => $birthDate,
            ],
            'purchase' => [
                'items' => [
                    [
                        'type' => 'virtual_good',
                        'sku' => 'test_webstore_sku_001',
                        'amount' => 100,
                    ],
                ],
            ],
            'is_sandbox' => false,
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(400);
        $this->assertEquals(ErrorCode::USER_ACCOUNT_BAN_PERMANENT, $response->json('error.code'));
    }

    /**
     * W3: user_validation - ユーザー検証
     */
    public function test_webstore_w3_userValidation_正常系()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        $payload = [
            'notification_type' => 'user_validation',
            'user' => [
                'id' => $usrUserId,
            ],
            'custom_parameters' => [
                'internal_id' => $usrUserId
            ]
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(HttpStatusCode::NO_CONTENT);
    }

    /**
     * W3: user_validation - BANユーザーの場合エラーとなること
     */
    public function test_webstore_w3_userValidation_異常系_BANユーザー()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'status' => UserStatus::BAN_PERMANENT->value,
        ]);
        $usrUserId = $usrUser->getId();

        $payload = [
            'notification_type' => 'user_validation',
            'user' => [
                'id' => $usrUserId,
            ],
            'custom_parameters' => [
                'internal_id' => $usrUserId
            ]
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(400);
        $this->assertEquals(ErrorCode::USER_ACCOUNT_BAN_PERMANENT, $response->json('error.code'));
    }

    /**
     * W4: payment - 支払い通知
     */
    public function test_webstore_w4_payment_正常系()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 通貨管理データを作成（リソース上限チェックに必要）
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0
        );

        // マスターデータ作成
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => 'test_webstore_sku_w4_001',
        ])->toEntity();

        OprProduct::factory()->create([
            'id' => 'test_product_w4_001',
            'product_type' => ProductType::DIAMOND->value,
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'paid_amount' => 100,
        ]);

        $payload = [
            'notification_type' => 'payment',
            'transaction' => [
                'dry_run' => 0,
            ],
            'custom_parameters' => [
                'transaction_id' => 'test_transaction_001',
                'internal_id' => $usrUserId,
            ],
            'purchase' => [
                'order' => [
                    'lineitems' => [
                        [
                            'sku' => 'test_webstore_sku_w4_001',
                            'amount' => 1,
                            'price' => [
                                'amount' => 100,
                                'currency' => 'JPY',
                            ]
                        ],
                    ],
                ],
            ],
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(HttpStatusCode::NO_CONTENT);
    }

    /**
     * W4: payment - BANユーザーの場合エラーとなること
     */
    public function test_webstore_w4_payment_異常系_BANユーザー()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'status' => UserStatus::BAN_PERMANENT->value,
        ]);
        $usrUserId = $usrUser->getId();

        $payload = [
            'notification_type' => 'payment',
            'transaction' => [
                'dry_run' => 0,
            ],
            'custom_parameters' => [
                'transaction_id' => 'test_transaction_001',
                'internal_id' => $usrUserId,
            ],
            'purchase' => [
                'order' => [
                    'lineitems' => [],
                ],
            ],
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(400);
        $this->assertEquals(ErrorCode::USER_ACCOUNT_BAN_PERMANENT, $response->json('error.code'));
    }

    /**
     * W5: order_paid - 注文支払い成功（ダイヤモンド商品）
     */
    public function test_webstore_w5_orderPaid_正常系_ダイヤモンド()
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);

        // 通貨管理データを作成
        $this->currencyDelegator->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0
        );

        $birthDate = '19900101';
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString($birthDate),
        ]);

        // トランザクションを事前に作成（W2で発行されたと仮定）
        $transactionId = Str::uuid()->toString();
        UsrWebstoreTransaction::factory()->create([
            'usr_user_id' => $usrUserId,
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_PENDING,
            'is_sandbox' => 0,
        ]);

        UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => System::PLATFORM_IOS,
            'country_code' => 'US',
        ]);

        // マスターデータ作成
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => 'test_webstore_sku_w5_001',
        ])->toEntity();
        $mstStoreProduct2 = MstStoreProduct::factory()->create([
            'product_id_webstore' => 'test_webstore_sku_w5_002',
        ])->toEntity();

        OprProduct::factory()->createMany([
            [
                'id' => 'test_product_w5_001',
                'product_type' => ProductType::DIAMOND->value,
                'mst_store_product_id' => $mstStoreProduct->getId(),
                'paid_amount' => 100,
            ],
            [
                'id' => 'test_product_w5_002',
                'product_type' => ProductType::DIAMOND->value,
                'mst_store_product_id' => $mstStoreProduct2->getId(),
                'paid_amount' => 10,
            ]
        ]);

        $orderId = 12345;
        $payload = [
            'notification_type' => 'order_paid',
            'order' => [
                'id' => $orderId,
                'invoice_id' => 'test_invoice_001',
                'currency' => 'USD',
                'amount' => 100,
                'mode' => 'live',
            ],
            'items' => [
                [
                    'type' => 'virtual_good',
                    'sku' => $mstStoreProduct->getProductIdWebstore(),
                    'amount' => 100,
                ],
                [
                    'type' => 'virtual_good',
                    'sku' => $mstStoreProduct2->getProductIdWebstore(),
                    'amount' => 0,
                ],
            ],
            'custom_parameters' => [
                'internal_id' => $usrUserId,
                'transaction_id' => $transactionId,
                'user_ip' => '192.168.1.1',
            ],
        ];

        // Exercise
        $response = $this->sendWebStoreRequest($payload);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // トランザクションステータスが'completed'に更新されたことを確認
        $this->assertDatabaseHas('usr_webstore_transactions', [
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_COMPLETED,
            'order_id' => $orderId,
        ]);
    }
}
