<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Currency\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyDelegatorTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyDelegator $currencyDelegator;
    private CurrencyService $currencyService;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->admForeignCurrencyRateRepository = $this->app->make(AdmForeignCurrencyRateRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
    }

    #[Test]
    public function createUser_通貨管理情報が登録されること()
    {
        // Exercise
        $usrCurrencySummary = $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);

        // Verify
        $this->assertEquals(1, $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function createUser_すでに登録されていたらその情報が戻ること()
    {
        // Setup
        $userId = '1';
        $freeAmount = 100;
        // 最初の登録
        $usrCurrencySummary = $this->currencyDelegator->createUser($userId, CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, $freeAmount);

        // Exercise
        // おなじユーザーで再度登録
        $usrCurrencySummary = $this->currencyDelegator->createUser($userId, CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 200);

        // Verify
        $this->assertEquals(1, $usrCurrencySummary->usr_user_id);
        $this->assertEquals($freeAmount, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function addFree_無償一次通貨の追加()
    {
        // Setup
        $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);

        // Exercise
        $this->currencyDelegator->addFree('1', CurrencyConstants::OS_PLATFORM_IOS, 100, CurrencyConstants::FREE_CURRENCY_TYPE_INGAME, new Trigger('', '', '', ''));

        // Verify
        $usrCurrencySummary = $this->currencyDelegator->getCurrencySummary('1');
        $this->assertEquals(200, $usrCurrencySummary->free_amount);

        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(200, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);
    }

    #[Test]
    public function addFrees_無償一次通貨の複数追加()
    {
        // Setup
        $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 10);
        // 登録する無償一次通貨
        $freeCurrencies = [
            FreeCurrencyAddEntity::fromType(CurrencyConstants::FREE_CURRENCY_TYPE_INGAME, 100, new Trigger('unit_test ingame', 'ingame id', 'ingame name', 'ingame')),
            FreeCurrencyAddEntity::fromType(CurrencyConstants::FREE_CURRENCY_TYPE_BONUS, 110, new Trigger('unit_test bonus', 'bonus id', 'bonus name', 'bonus')),
            FreeCurrencyAddEntity::fromType(CurrencyConstants::FREE_CURRENCY_TYPE_REWARD, 120, new Trigger('unit_test reward', 'reward id', 'reward name', 'reward')),
        ];

        // Exercise
        $this->currencyDelegator->addFrees('1', CurrencyConstants::OS_PLATFORM_IOS, $freeCurrencies);

        // Verify
        $usrCurrencySummary = $this->currencyDelegator->getCurrencySummary('1');
        $this->assertEquals(10 + 100 + 110 + 120, $usrCurrencySummary->free_amount);

        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(10 + 100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);
    }
    
    #[Test]
    public function usePaid_有償一次通貨の消費()
    {
        // Setup
        $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);
        // 有償一次通貨を追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $this->currencyDelegator->usePaid('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 99, new Trigger('', '', '', ''));

        // Verify
        // 有償一次通貨レコードの確認
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals(1, $usrCurrencyPaid->left_amount);

        // サマリーの確認
        $usrCurrencySummary = $this->currencyDelegator->getCurrencySummary('1');
        $this->assertEquals(1, $usrCurrencySummary->paid_amount_apple);
    }

    #[Test]
    public function useCurrency_無償・有償一次通貨の消費()
    {
        // Setup
        $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);

        // Exercise
        $this->currencyDelegator->useCurrency('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 99, new Trigger('', '', '', ''));

        // Verify
        // 無償一次通貨レコードの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(1, $usrCurrencyFree->ingame_amount);

        // サマリーの確認
        $usrCurrencySummary = $this->currencyDelegator->getCurrencySummary('1');
        $this->assertEquals(1, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function getCurrencyPaid_有償一次通貨情報を取得する()
    {
        // Setup
        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);
        // 有償一次通貨レコードを追加
        $this->usrStoreProductHistoryRepository
            ->insertStoreProductHistory(
                '1',
                'device1',
                20,
                'dummy receipt 1',
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'product1',
                'store_product1',
                'mst_product1',
                'USD',
                'bundle_id1',
                'purchase_token1',
                100,
                0,
                '0.01',
                '0.00010000',
                101,
                true,
            );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $result = $this->currencyDelegator->getCurrencyPaid('1', CurrencyConstants::PLATFORM_APPSTORE);

        // Verify
        $this->assertEquals(1, count($result));
        $this->assertEquals('1', $result[0]->usr_user_id);
        $this->assertEquals(1, $result[0]->seq_no);
        $this->assertEquals(100, $result[0]->left_amount);
        //  usr_store_product_history_entity product_sub_idに絞ってチェック
        $this->assertEquals('product1', $result[0]->getUsrStoreProductHistoryEntity()->getProductSubId());
    }

    #[Test]
    public function softDeleteCurrencyAndBillingDataByUserId_課金・通貨データを論理削除する()
    {
        // テスト内容は疎通程度とする。削除対象が多いため、CurrencyとBillingでひとつづつ対象にチェックする。
        // 詳細なテストはService側で行う

        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100, 100);

        //  ショップ情報を登録
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->currencyDelegator->softDeleteCurrencyAndBillingDataByUserId('1', CurrencyConstants::OS_PLATFORM_IOS);

        // Verify
        // 通貨管理の確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertNull($usrCurrencySummary);

        // ショップ情報の確認
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertNull($usrStoreInfo);
    }

    #[Test]
    public function getMaxOwnedCurrencyAmount_一次通貨の最大所持数取得()
    {
        // Exercise
        $actual = $this->currencyDelegator->getMaxOwnedCurrencyAmount();

        // Verify
        $this->assertEquals(999999999, $actual);
    }

    #[Test]
    public function isMaxOwnedCurrencyAmountUnlimited_一次通貨上限が設定されている()
    {
        // Exercise
        $actual = $this->currencyDelegator->isMaxOwnedCurrencyAmountUnlimited();

        // Verify
        $this->assertEquals(false, $actual);
    }
}
