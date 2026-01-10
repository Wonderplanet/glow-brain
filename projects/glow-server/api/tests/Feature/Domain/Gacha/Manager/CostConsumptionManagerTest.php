<?php

namespace Tests\Feature\Domain\Gacha\Manager;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Manager\CostConsumptionManager;
use App\Domain\Gacha\Models\LogGachaAction;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode as WpErrorCode;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CostConsumptionManagerTest extends TestCase
{
    private CostConsumptionManager $costConsumptionManager;
    private CurrencyDelegator $currencyDelegator;
    private CurrencyService $currencyService;
    private UserDelegator $userDelegator;

    public function setUp(): void
    {
        parent::setUp();

        $this->costConsumptionManager = $this->app->make(CostConsumptionManager::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->userDelegator = $this->app->make(UserDelegator::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
    }

    /**
     * @test
     */
    public function exec_アイテムを消費することができる(): void
    {
        $userItemAmount = 20;
        $costNum = 10;
        $mstItemId = "1";
        $oprGachaId = "1";
        $now = CarbonImmutable::now();

        // Setup
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        $usrItem = UsrItem::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'mst_item_id' => $mstItemId,
            'amount'      => $userItemAmount,
        ]);
        $gachaTrigger = new GachaTrigger($oprGachaId, "テストガチャ");

        // Exercise
        $this->costConsumptionManager->setConsumeResource(
            $user->getId(),
            $mstItemId,
            $costNum,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::ITEM,
            $gachaTrigger
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());
        $this->saveAll();

        $usrItem->refresh();

        // Verify
        $this->assertEquals($userItemAmount - $costNum, $usrItem->getAmount());
    }

    /**
     * @test
     */
    public function exec_ダイヤを消費することができる(): void
    {
        // Setup
        $userDiamondAmount = 200;
        $costNum = 100;
        $oprGachaId = "1";
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        $now = CarbonImmutable::now();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);

        // 課金データを作成
        $this->currencyDelegator->createUser(
            $currentUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $userDiamondAmount,
            0,
        );

        $gachaTrigger = new GachaTrigger($oprGachaId, "テストガチャ");

        // Exercise
        $this->costConsumptionManager->setConsumeResource(
            $user->getId(),
            null,
            $costNum,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::DIAMOND,
            $gachaTrigger
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());

        $currencySummary = $this->currencyDelegator->getCurrencySummary($currentUser->getId());

        // Verify
        $this->assertEquals($userDiamondAmount - $costNum, $currencySummary->getTotalAmount());
    }

    /**
     * @test
     */
    public function exec_有償ダイヤを消費することができる(): void
    {
        // Setup
        $userDiamondPaidAmount = 200;
        $costNum = 100;
        $oprGachaId = "1";
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        $now = CarbonImmutable::now();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);

        // 課金データを作成
        $this->currencyDelegator->createUser(
            $currentUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
            0,
        );

        $this->currencyService->addCurrencyPaid(
            $currentUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $userDiamondPaidAmount,
            'JPY',
            '100',
            100,
            'test-apple',
            true,
            new Trigger('test', '', '', ''),
        );

        $gachaTrigger = new GachaTrigger($oprGachaId, "テストガチャ");

        // Exercise
        $this->costConsumptionManager->setConsumeResource(
            $user->getId(),
            null,
            $costNum,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::PAID_DIAMOND,
            $gachaTrigger
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());

        $currencySummary = $this->currencyDelegator->getCurrencySummary($currentUser->getId());

        // Verify
        $this->assertEquals($userDiamondPaidAmount - $costNum, $currencySummary->getTotalPaidAmount());
    }

    public function testExecConsumeResource_アイテムが足りない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId());

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);
        $this->costConsumptionManager->setConsumeResource(
            $usrUser->getId(),
            'gacha_ticket',
            1,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::ITEM,
            new GachaTrigger('test_gacha_id', "テストガチャ")
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());
    }

    public function testExecConsumeResource_ダイヤが足りない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId());

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(WpErrorCode::NOT_ENOUGH_CURRENCY);
        $this->costConsumptionManager->setConsumeResource(
            $usrUser->getId(),
            'gacha_ticket',
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::DIAMOND,
            new GachaTrigger('test_gacha_id', "テストガチャ")
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());
    }

    public function testExecConsumeResource_有償ダイヤが足りない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId());

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);
        $this->costConsumptionManager->setConsumeResource(
            $usrUser->getId(),
            'gacha_ticket',
            100,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            false,
            CostType::PAID_DIAMOND,
            new GachaTrigger('test_gacha_id', "テストガチャ")
        );
        $this->costConsumptionManager->execConsumeResource(new LogGachaAction());
    }
}
