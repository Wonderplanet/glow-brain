<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class UsrCurrencySummaryRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;

    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
    }

    #[Test]
    public function insertCurrencySummary_通貨管理情報が登録されること()
    {
        // Exercise
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function updateCurrencySummaryPaid_AppStoreの有償一次通貨が更新されること()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 200, 300);

        // Exercise
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_APPSTORE, 500);

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_share);
        $this->assertEquals(300, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function updateCurrncySummaryPaid_GooglePlayの有償一次通貨が更新されること()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 200, 300);

        // Exercise
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaid('1', CurrencyConstants::PLATFORM_GOOGLEPLAY, 500);

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(500, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_share);
        $this->assertEquals(300, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function updateCurrencySummaryFree_無償一次通貨が更新されること()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 200, 300);

        // Exercise
        $this->usrCurrencySummaryRepository->updateCurrencySummaryFree('1', 500);

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_share);
        $this->assertEquals(500, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function updateCurrencySummaryPaidAndFree_有償・無償一次通貨が更新されること()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 200, 300);

        // Exercise
        $this->usrCurrencySummaryRepository->updateCurrencySummaryPaidAndFree('1', CurrencyConstants::PLATFORM_APPSTORE, 500, 600);

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_share);
        $this->assertEquals(600, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function softDeleteByUserId_論理削除する()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 300, 400);
        // 別のユーザー情報
        $this->createUsrCurrencySummary('2', 100, 200, 300, 400);

        // Exercise
        $this->usrCurrencySummaryRepository->softDeleteByUserId('1');

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertNull($usrCurrencySummary);

        // 論理削除されていることを確認する
        $usrCurrencySummary = UsrCurrencySummary::withTrashed()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertNotNull($usrCurrencySummary->deleted_at);

        // 別のユーザー情報は削除されていないことを確認する
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('2');
        $this->assertEquals('2', $usrCurrencySummary->usr_user_id);
        $this->assertNull($usrCurrencySummary->deleted_at);
    }

    #[Test]
    public function updateCurrencySummaryToZero_サマリーの所持数を0にする()
    {
        // Setup
        // 初期値を設定するため直接createする
        $this->createUsrCurrencySummary('1', 100, 200, 200, 300);

        // Exercise
        $this->usrCurrencySummaryRepository->updateCurrencySummaryToZero('1');

        // Verify
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_share);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);
    }
}
