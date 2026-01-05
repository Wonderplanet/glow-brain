<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\LogCurrencyFreeInsertEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogCurrencyFreeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogCurrencyFreeRepository $logCurrencyFreeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
    }

    #[Test]
    public function insertFreeLog_ログが追加されていること()
    {
        // Exercise
        $this->logCurrencyFreeRepository->insertFreeLog(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            100,
            110,
            120,
            10,
            20,
            30,
            110,
            130,
            150,
            new Trigger('pf_log', 'unittest', 'unittest name', 'unittest details')
        );

        // Verify
        // 登録情報の確認
        $logCurrencyFree = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(100, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(10, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(20, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(30, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(110, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(130, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(150, $logCurrencyFree->current_reward_amount);
        $this->assertEquals('pf_log', $logCurrencyFree->trigger_type);
        $this->assertEquals('unittest', $logCurrencyFree->trigger_id);
        $this->assertEquals('unittest name', $logCurrencyFree->trigger_name);
        $this->assertEquals('unittest details', $logCurrencyFree->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyFree->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyFree->request_id);
    }

    #[Test]
    public function bulkInsertFreeLogs_ログが複数追加されていること()
    {
        // Setup
        $insertEntities = [
            new LogCurrencyFreeInsertEntity(
                100,
                110,
                120,
                10,
                20,
                30,
                110,
                130,
                150,
                new Trigger('pf_log1', 'unittest1', 'unittest name1', 'unittest details1')
            ),
            new LogCurrencyFreeInsertEntity(
                200,
                210,
                220,
                20,
                30,
                40,
                220,
                240,
                260,
                new Trigger('pf_log2', 'unittest2', 'unittest name2', 'unittest details2')
            ),
        ];

        // Exercise
        $logIds = $this->logCurrencyFreeRepository->bulkInsertFreeLogs('1', CurrencyConstants::OS_PLATFORM_IOS, $insertEntities);

        // Verify
        // 登録情報の確認
        $logCurrencyFrees = $this->logCurrencyFreeRepository->findByUserId('1');
        $this->assertCount(2, $logCurrencyFrees);

        $logCurrencyFree1 = $logCurrencyFrees[0];
        $this->assertEquals($logIds[0], $logCurrencyFree1->id);
        $this->assertEquals('1', $logCurrencyFree1->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree1->os_platform);
        $this->assertEquals(100, $logCurrencyFree1->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree1->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree1->before_reward_amount);
        $this->assertEquals(10, $logCurrencyFree1->change_ingame_amount);
        $this->assertEquals(20, $logCurrencyFree1->change_bonus_amount);
        $this->assertEquals(30, $logCurrencyFree1->change_reward_amount);
        $this->assertEquals(110, $logCurrencyFree1->current_ingame_amount);
        $this->assertEquals(130, $logCurrencyFree1->current_bonus_amount);
        $this->assertEquals(150, $logCurrencyFree1->current_reward_amount);
        $this->assertEquals('pf_log1', $logCurrencyFree1->trigger_type);
        $this->assertEquals('unittest1', $logCurrencyFree1->trigger_id);
        $this->assertEquals('unittest name1', $logCurrencyFree1->trigger_name);
        $this->assertEquals('unittest details1', $logCurrencyFree1->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyFree1->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyFree1->request_id);

        $logCurrencyFree2 = $logCurrencyFrees[1];
        $this->assertEquals('1', $logCurrencyFree2->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree2->os_platform);
        $this->assertEquals(200, $logCurrencyFree2->before_ingame_amount);
        $this->assertEquals(210, $logCurrencyFree2->before_bonus_amount);
        $this->assertEquals(220, $logCurrencyFree2->before_reward_amount);
        $this->assertEquals(20, $logCurrencyFree2->change_ingame_amount);
        $this->assertEquals(30, $logCurrencyFree2->change_bonus_amount);
        $this->assertEquals(40, $logCurrencyFree2->change_reward_amount);
        $this->assertEquals(220, $logCurrencyFree2->current_ingame_amount);
        $this->assertEquals(240, $logCurrencyFree2->current_bonus_amount);
        $this->assertEquals(260, $logCurrencyFree2->current_reward_amount);
        $this->assertEquals('pf_log2', $logCurrencyFree2->trigger_type);
        $this->assertEquals('unittest2', $logCurrencyFree2->trigger_id);
        $this->assertEquals('unittest name2', $logCurrencyFree2->trigger_name);
        $this->assertEquals('unittest details2', $logCurrencyFree2->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyFree2->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyFree2->request_id);

        // logging_noが連続している
        $this->assertEquals($logCurrencyFree1->logging_no + 1, $logCurrencyFree2->logging_no);
    }
}
