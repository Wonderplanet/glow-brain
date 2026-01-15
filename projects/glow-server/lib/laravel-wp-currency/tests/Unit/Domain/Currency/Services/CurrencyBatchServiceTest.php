<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyBatchService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyBatchServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;
    private CurrencyBatchService $currencyBatchService;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->currencyBatchService = $this->app->make(CurrencyBatchService::class);
    }

    #[Test]
    #[DataProvider('collectAndAddFreeCurrencyByBatchData')]
    public function collectFreeCurrencyByBatch_無償一次通貨を回収する(string $type): void
    {
        // Setup
        // 通貨管理の登録(ingameに100が付与される)
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            500,
            $type,
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->currencyBatchService->collectFreeCurrencyByBatch(
            '1',
            $type,
            100,
            'collect free currency by batch'
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(400, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals(500, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(400, $usrCurrencyFree->reward_amount);
                break;
        }

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1');
        $log = collect($logs)->first(
            fn ($row) => $row['trigger_type'] === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_FREE_BATCH
        );
        $this->assertEquals('1', $log->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_BATCH, $log->os_platform);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_FREE_BATCH, $log->trigger_type);
        $this->assertEquals('', $log->trigger_id);
        $this->assertEquals('', $log->trigger_name);
        $this->assertEquals('collect free currency by batch', $log->trigger_detail);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(100, $log->before_ingame_amount);
                $this->assertEquals(500, $log->before_bonus_amount);
                $this->assertEquals(0, $log->before_reward_amount);
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals(-100, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals(100, $log->current_ingame_amount);
                $this->assertEquals(400, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals(600, $log->before_ingame_amount);
                $this->assertEquals(0, $log->before_bonus_amount);
                $this->assertEquals(0, $log->before_reward_amount);
                $this->assertEquals(-100, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals(500, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(100, $log->before_ingame_amount);
                $this->assertEquals(0, $log->before_bonus_amount);
                $this->assertEquals(500, $log->before_reward_amount);
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals(-100, $log->change_reward_amount);
                $this->assertEquals(100, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals(400, $log->current_reward_amount);
                break;
        }
    }

    #[Test]
    #[DataProvider('collectAndAddFreeCurrencyByBatchData')]
    public function addFreeCurrencyByBatch_正常処理チェック(string $type): void
    {
        // Setup
        $userId = '100';
        $amount = 999;
        $osPlatform = CurrencyConstants::OS_PLATFORM_BATCH;
        $triggerDetail = 'add free currency by batch test';
        //  通貨情報を登録
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->currencyBatchService->addFreeCurrencyByBatch(
            $userId,
            $type,
            $amount,
            $triggerDetail
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        $this->assertEquals($userId, $usrCurrencySummary->usr_user_id);
        $this->assertEquals($amount, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $this->assertEquals($userId, $usrCurrencyFree->usr_user_id);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals($amount, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
                $this->assertEquals($amount, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals($amount, $usrCurrencyFree->reward_amount);
                break;
        }

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId($userId);
        $log = collect($logs)->first(
            fn ($row) => $row['trigger_type'] === Trigger::TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH
        );
        $this->assertEquals($userId, $log->usr_user_id);
        $this->assertEquals($osPlatform, $log->os_platform);
        $this->assertEquals(0, $log->before_ingame_amount);
        $this->assertEquals(0, $log->before_bonus_amount);
        $this->assertEquals(0, $log->before_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH, $log->trigger_type);
        $this->assertEquals('', $log->trigger_id);
        $this->assertEquals('', $log->trigger_name);
        $this->assertEquals($triggerDetail, $log->trigger_detail);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals($amount, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals($amount, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals($amount, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals(0, $log->current_ingame_amount);
                $this->assertEquals($amount, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals($amount, $log->change_reward_amount);
                $this->assertEquals(0, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals($amount, $log->current_reward_amount);
                break;
        }
    }

    /**
     * collectFreeCurrencyByBatch、addFreeCurrencyByBatchの正常テストで使用
     *
     * @return array[]
     */
    public static function collectAndAddFreeCurrencyByBatchData(): array
    {
        return [
            '対象がbonus' => [CurrencyConstants::FREE_CURRENCY_TYPE_BONUS],
            '対象がingame' => [CurrencyConstants::FREE_CURRENCY_TYPE_INGAME],
            '対象がreward' => [CurrencyConstants::FREE_CURRENCY_TYPE_REWARD],
        ];
    }
}
