<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;

class UsrCurrencyPaidRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
    }

    #[Test]
    public function getNextSeqNo_シーケンス番号を取得()
    {
        // Exercise
        $nextSeqNo = $this->usrCurrencyPaidRepository->getNextSeqNo('1');

        // Verify
        $this->assertEquals(1, $nextSeqNo);
    }

    #[Test]
    public function getNextSeqNo_すでにレコードがあるときはその次の番号になる()
    {
        // Setup
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            1,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId',
            true,
        );

        // Exercise
        $nextSeqNo = $this->usrCurrencyPaidRepository->getNextSeqNo('1');

        // Verify
        $this->assertEquals(2, $nextSeqNo);
    }

    #[Test]
    public function getNextSeqNo_別のユーザーのレコードがあっても自分のseq_noは1から始まる()
    {
        // Setup
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            1,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId',
            true,
        );

        // Exercise
        $nextSeqNo = $this->usrCurrencyPaidRepository->getNextSeqNo('1');

        // Verify
        $this->assertEquals(1, $nextSeqNo);
    }

    #[Test]
    public function insertCurrencyPaid_通貨管理情報が登録されること()
    {
        // Setup
        $purchaseAmount = 100;
        $purchasePrice = '0.01'; // '$0.01' -> '0.01
        $pricePerAmount = bcdiv($purchasePrice, (string)$purchaseAmount, 8); // '0.01' / 100 -> '0.0001'
        $vipPoint = 101;

        // Exercise
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            $purchaseAmount,
            $purchasePrice,
            $purchaseAmount,
            $pricePerAmount,
            $vipPoint,
            'USD',
            'receiptUniqueId',
            true,
        );

        // Verify
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);
        $this->assertEquals('0.010000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals(100, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00010000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals('USD', $usrCurrencyPaid->currency_code);
        $this->assertEquals('receiptUniqueId', $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(true, $usrCurrencyPaid->is_sandbox);
    }

    public static function sumPaidAmountData()
    {
        return [
            'app store' => [
                CurrencyConstants::PLATFORM_APPSTORE,
                210,
            ],
            'google play' => [
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                250,
            ],
        ];
    }

    #[Test]
    #[DataProvider('sumPaidAmountData')]
    public function sumPaidAmount_有償一次通貨の現在所持数(string $billingPlatform, int $expected)
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            110,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );
        //  GooglePlay
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            3,
            120,
            '0.01',
            120,
            '0.0001',
            121,
            'USD',
            'receiptUniqueId3',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            4,
            130,
            '0.01',
            130,
            '0.0001',
            131,
            'USD',
            'receiptUniqueId4',
            true,
        );

        // Exercise
        $sumPaidAmount = $this->usrCurrencyPaidRepository->sumPaidAmount('1', $billingPlatform);

        // Verify
        $this->assertEquals($expected, $sumPaidAmount);
    }

    #[Test]
    public function findAllByUserIdAndBillingPlatform_プラットフォーム別の有償一次通貨を全て取得()
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            110,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );

        //  GooglePlay
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            3,
            120,
            '0.01',
            120,
            '0.0001',
            121,
            'USD',
            'receiptUniqueId3',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            4,
            130,
            '0.01',
            130,
            '0.0001',
            131,
            'USD',
            'receiptUniqueId4',
            true,
        );

        // Exercise
        $usrCurrencyPaidsApple = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $usrCurrencyPaidsGoogle = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_GOOGLEPLAY);

        // Verify
        $this->assertCount(2, $usrCurrencyPaidsApple);
        $this->assertEquals('1', $usrCurrencyPaidsApple[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaidsApple[0]->billing_platform);
        $this->assertEquals(100, $usrCurrencyPaidsApple[0]->left_amount);

        $this->assertEquals('1', $usrCurrencyPaidsApple[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaidsApple[1]->billing_platform);
        $this->assertEquals(110, $usrCurrencyPaidsApple[1]->left_amount);

        $this->assertCount(2, $usrCurrencyPaidsGoogle);
        $this->assertEquals('1', $usrCurrencyPaidsGoogle[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $usrCurrencyPaidsGoogle[0]->billing_platform);
        $this->assertEquals(120, $usrCurrencyPaidsGoogle[0]->left_amount);

        $this->assertEquals('1', $usrCurrencyPaidsGoogle[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_GOOGLEPLAY, $usrCurrencyPaidsGoogle[1]->billing_platform);
        $this->assertEquals(130, $usrCurrencyPaidsGoogle[1]->left_amount);
    }

    #[Test]
    public function findAllAmountNotZeroPaidByUserIdAndBillingPlatform_優勝一次通貨のうち残高0ではないものを取得()
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            0,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            3,
            -100,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId3',
            true,
        );

        // Exercise
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllAmountNotZeroPaidByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);

        // Verify
        // left_amount=0 のレコードだけ取れてこない
        $this->assertCount(2, $usrCurrencyPaids);

        $this->assertEquals('1', $usrCurrencyPaids[0]->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaids[0]->seq_no);

        $this->assertEquals('1', $usrCurrencyPaids[1]->usr_user_id);
        $this->assertEquals(3, $usrCurrencyPaids[1]->seq_no);
    }

    #[Test]
    public function decrementPaidAmount_有償一次通貨レコードからの引き落とし()
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            110,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );

        // Exercise
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $id = $usrCurrencyPaids[0]->id;
        $this->usrCurrencyPaidRepository->decrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $id, 50);

        // Verify
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $this->assertCount(2, $usrCurrencyPaids);
        $this->assertEquals('1', $usrCurrencyPaids[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[0]->billing_platform);
        $this->assertEquals(50, $usrCurrencyPaids[0]->left_amount);

        $this->assertEquals('1', $usrCurrencyPaids[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[1]->billing_platform);
        $this->assertEquals(110, $usrCurrencyPaids[1]->left_amount);
    }

    #[Test]
    public function findByUserIdAndReceiptUniqueIdAndBillingPlatform_正常取得(): void
    {
        // Setup
        $userId = '1';
        $receiptUniqueId = 'receiptUniqueId1';
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $billingPlatform,
            1,
            100,
            '100',
            100,
            '1',
            100,
            'JPY',
            $receiptUniqueId,
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $billingPlatform,
            2,
            100,
            '100',
            100,
            '1',
            100,
            'JPY',
            'receiptUniqueId2',
            true,
        );

        // Exercise
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository
            ->findByUserIdAndReceiptUniqueIdAndBillingPlatform(
                $userId,
                $receiptUniqueId,
                $billingPlatform
            );

        // Verify
        //  取得対象のレシートユニークIDの情報が取れているかチェック
        $this->assertNotNull($usrCurrencyPaid);
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
    }

    #[Test]
    public function findByUserIdAndReceiptUniqueIdAndBillingPlatform_取得結果がnull(): void
    {
        // Setup
        $userId = '1';
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '100',
            100,
            '1',
            100,
            'JPY',
            'receiptUniqueId1',
            true,
        );

        // Exercise
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository
            ->findByUserIdAndReceiptUniqueIdAndBillingPlatform(
                $userId,
                'receiptUniqueId2',
                CurrencyConstants::PLATFORM_APPSTORE
            );

        // Verify
        $this->assertNull($usrCurrencyPaid);
    }

    #[Test]
    public function incrementPaidAmount_有償一次通貨レコードの加算()
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            50,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            110,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );

        // Exercise
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $id = $usrCurrencyPaids[0]->id;
        $this->usrCurrencyPaidRepository->incrementPaidAmount('1', CurrencyConstants::PLATFORM_APPSTORE, $id, 50);

        // Verify
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $this->assertCount(2, $usrCurrencyPaids);
        $this->assertEquals('1', $usrCurrencyPaids[0]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[0]->billing_platform);
        $this->assertEquals(100, $usrCurrencyPaids[0]->left_amount);

        $this->assertEquals('1', $usrCurrencyPaids[1]->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaids[1]->billing_platform);
        $this->assertEquals(110, $usrCurrencyPaids[1]->left_amount);
    }

    #[Test]
    public function softDeleteByUserId_論理削除する()
    {
        // Setup
        // 所持している通貨を複数追加
        //  AppStore
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId1',
            true,
        );
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            2,
            110,
            '0.01',
            110,
            '0.0001',
            111,
            'USD',
            'receiptUniqueId2',
            true,
        );
        // 別のユーザー情報
        $this->usrCurrencyPaidRepository->insertUsrCurrencyPaid(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            100,
            '0.01',
            100,
            '0.0001',
            101,
            'USD',
            'receiptUniqueId2-1',
            true,
        );

        // Exercise
        $this->usrCurrencyPaidRepository->softDeleteByUserId('1');

        // Verify
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findAllByUserIdAndBillingPlatform('1', CurrencyConstants::PLATFORM_APPSTORE);
        $this->assertCount(0, $usrCurrencyPaids);

        // 論理削除されていることを確認する
        $usrCurrencyPaids = UsrCurrencyPaid::withTrashed()
            ->where('usr_user_id', '1')
            ->get()
            ->all();
        $this->assertCount(2, $usrCurrencyPaids);
        $this->assertEquals('1', $usrCurrencyPaids[0]->usr_user_id);
        $this->assertNotNull($usrCurrencyPaids[0]->deleted_at);
        $this->assertEquals('1', $usrCurrencyPaids[1]->usr_user_id);
        $this->assertNotNull($usrCurrencyPaids[1]->deleted_at);

        // 別のユーザー情報は削除されていないことを確認する
        $usrCurrencyPaids = UsrCurrencyPaid::query()
            ->where('usr_user_id', '2')
            ->get()
            ->all();
        $this->assertCount(1, $usrCurrencyPaids);
    }
}
