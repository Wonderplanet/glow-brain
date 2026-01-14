<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\AppStorePlatformService;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * AppStoreプラットフォームと連携するServiceのテスト
 *
 * ユニットテスト時にプラットフォームへの問い合わせを行うため、通常は実行しないようにしているテストがある。
 * 実行する際は--group wp_billing_appstoreを指定すること。
 *
 * またユニットテストを実行する場合は、bundle_idなど.envに設定するストア関連の設定を環境変数に設定する。
 * .env.local_testがGitHubにコミットされているため、そこにIDを記載したくないので
 * テストするときは環境変数で上書きを行う。
 */
class AppStorePlatformServiceTest extends TestCase
{
    private AppStorePlatformService $appStorePlatformService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appStorePlatformService = $this->app->make(AppStorePlatformService::class);
    }

    #[Test]
    #[Group('wp_billing_appstore')]
    public function verifyReceipt_レシートの確認()
    {
        // Setup
        //  テスト用のレシートを読み込む
        $receipt = $this->getSandboxReceipt();

        // Exercise
        $storeReceipt = $this->appStorePlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'ios_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        //  receiptの内容によって結果が変わってくるため、詳細な称号は行わない
        print_r([
            'unique id' => $storeReceipt->getUnitqueId(),
            'sandbox' => $storeReceipt->isSandboxReceipt(),
        ]);
        $this->assertTrue(true);
    }

    #[Test]
    #[Group('wp_billing_appstore')]
    public function verifyReceiptToAppStoreApi_ストアAPIへレシートを問い合わせ()
    {
        // Setup
        $receipt = $this->getSandboxReceipt();
        $receiptJson = json_decode($receipt, true);

        // Exercise
        $response = $this->callMethod(
            $this->appStorePlatformService,
            'verifyReceiptToAppStoreApi',
            [$receiptJson['Payload']]
        );

        // Verify
        //  receiptの内容によって結果が変わってくるため、詳細な照合は行わない
        //  responseの出力のみ行う
        print_r($response);
        $this->assertTrue(true);
    }

    #[Test]
    #[Group('wp_billing_appstore')]
    public function getPurchaseDate_購入日時が取得できる()
    {
        // Setup
        $receipt = $this->getSandboxReceipt();

        // Exercise
        $storeReceipt = $this->appStorePlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'ios_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        // verifyReceiptToAppStoreApi前のレシート情報では購入日時がわからないため日時フォーマットで取得できているか確認する
        $date = $storeReceipt->getPurchaseDate()->toDateTimeString();
        $this->assertTrue(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date) === 1);
    }


    /**
     * テストに使用するサンドボックスレシートを読み込む
     *
     * @return string
     */
    private function getSandboxReceipt(): string
    {
        $receiptPath = config('wp_currency.store_test.app_store.apple_sandbox_receipt');
        $receipt = file_get_contents($receiptPath);

        return $receipt;
    }

    #[Test]
    #[Group('wp_billing_appstore')]
    public function getProductIds_ProductIDが取得できる()
    {
        // Setup
        //  テスト用のレシートを読み込む
        $receipt = $this->getSandboxReceipt();

        // Exercise
        $storeReceipt = $this->appStorePlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'ios_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        $this->assertEquals(true, !empty($storeReceipt->getProductIds()[0]));
    }
}
