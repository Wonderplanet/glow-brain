<?php

declare(strict_types=1);

namespace Unit\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\LogCurrencyUnionModel;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UnionLogCurrencyRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use function Symfony\Component\String\s;

class UnionLogCurrencyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UnionLogCurrencyRepository $unionLogCurrencyRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;
    private CurrencyService $currencyService;

    public function setUp(): void
    {
        parent::setUp();

        $this->unionLogCurrencyRepository = $this->app->make(UnionLogCurrencyRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('param_getUnionQueryWithExcelSelect_検索する日時Carbonのタイムゾーンが変わっても検索が問題ないか確認')]
    public function getUnionQueryWithExcelSelect_検索する日時Carbonのタイムゾーンが変わっても検索が問題ないか確認(CarbonImmutable $startAt, CarbonImmutable $endAt)
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        // 期間前のログを作成
        $this->setTestNow(new Carbon('2022-12-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 期間内のログを作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('2', 100, 'id2');
        $this->consumeCurrency('2', 90, $triggerType, $triggerId, 'テストガチャ1');
        $this->setTestNow(new Carbon('2023-01-02 00:00:00', 'Asia/Tokyo'));
        $this->addFree('3', 100);
        $this->consumeCurrency('3', 90, $triggerType, $triggerId, 'テストガチャ1');
        $this->setTestNow(new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('4', 100, 'id3');
        $this->addFree('4', 100);
        $this->consumeCurrency('4', 110, $triggerType, $triggerId, 'テストガチャ1');
        // 期間外のログを作成
        $this->setTestNow(new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('5', 100, 'id4');
        $this->consumeCurrency('5', 90, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->unionLogCurrencyRepository->getUnionQueryWithExcelSelect(
            $startAt,
            $endAt,
            $triggerType,
            $triggerId,
            false,
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get()->toArray();
        $this->assertCount(3, $actual);
        $row = $actual[0];
        $this->assertEquals('4', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-31 14:59:59', $carbon->utc()->toDateTimeString());
        $this->assertEquals('-10', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-100', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[1];
        $this->assertEquals('3', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-01 15:00:00', $carbon->utc()->toDateTimeString());
        $this->assertEquals('0', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-90', $row['sum_log_change_amount_free']);
        $this->assertEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[2];
        $this->assertEquals('2', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2022-12-31 15:00:00',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('-90', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
    }

    public static function param_getUnionQueryWithExcelSelect_検索する日時Carbonのタイムゾーンが変わっても検索が問題ないか確認()
    {
        return [
            'TZがJST' => [new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo'), new CarbonImmutable('2023-01-31 23:59:59', 'Asia/Tokyo')],
            'TZがUTC' => [new CarbonImmutable('2022-12-31 15:00:00'), new CarbonImmutable('2023-01-31 14:59:59')],
        ];
    }

    #[Test]
    #[DataProvider('param_getUnionQueryWithExcelSelect_消費ログかつサンドボックスログの検索')]
    public function getUnionQueryWithExcelSelect_消費ログかつサンドボックスログの検索(bool $isConcludeSandbox, int $expectedCount)
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $searchStartAt = new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchEndAt = new CarbonImmutable('2023-01-02 23:59:59', 'Asia/Tokyo');
        // 有償通貨の消費ログを1件作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 有償通貨の消費ログを1件作成 - サンドボックス
        $this->addPaid('2', 100, 'id2', true);
        $this->consumeCurrency('2', 80, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->unionLogCurrencyRepository->getUnionQueryWithExcelSelect(
            $searchStartAt,
            $searchEndAt,
            $triggerType,
            $triggerId,
            $isConcludeSandbox
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount($expectedCount, $actual);
        $row = $actual->where('usr_user_id', '1')->first();
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2022-12-31 15:00:00', $carbon->utc()->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals('-90', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
        if ($isConcludeSandbox) {
            $row = $actual->where('usr_user_id', '2')->first();
            $carbon = new Carbon($row['created_at']);
            $this->assertEquals('2022-12-31 15:00:00', $carbon->utc()->toDatetimeString());
            $this->assertEquals($triggerType, $row['trigger_type']);
            $this->assertEquals($triggerId, $row['trigger_id']);
            $this->assertEquals('-80', $row['sum_log_change_amount_paid']);
            $this->assertEquals('0', $row['sum_log_change_amount_free']);
            $this->assertNotEmpty($row['log_currency_paid_ids']);
            $this->assertEmpty($row['log_currency_free_ids']);
        }
    }

    public static function param_getUnionQueryWithExcelSelect_消費ログかつサンドボックスログの検索()
    {
        return [
            'サンドボックスを含める' => [true, 2],
            'サンドボックスを含めない' => [false, 1],
        ];
    }

    #[Test]
    public function getUnionQueryWithExcelSelect_グルーピングと表示用カラムの確認()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        // 期間前のログを作成
        $this->setTestNow(new Carbon('2022-12-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 期間内のログを作成
        // 有償通貨のみ
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('2', 100, 'id2');
        $this->consumeCurrency('2', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 別リクエストでの消費(groupingは別になる)
        $this->setTestNow(new Carbon('2023-01-01 00:00:01', 'Asia/Tokyo'));
        $this->consumeCurrency('2', 10, $triggerType, $triggerId, 'テストガチャ1');
        // 無償通貨のみ
        $this->setTestNow(new Carbon('2023-01-02 00:00:00', 'Asia/Tokyo'));
        $this->addFree('3', 100);
        $this->consumeCurrency('3', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 別リクエストでの消費(groupingは別になる)
        $this->setTestNow(new Carbon('2023-01-02 00:00:01', 'Asia/Tokyo'));
        $this->consumeCurrency('3', 10, $triggerType, $triggerId, 'テストガチャ1');
        // 有償・無償
        $this->setTestNow(new Carbon('2023-01-31 23:59:58', 'Asia/Tokyo'));
        $this->addPaid('4', 100, 'id3');
        $this->addFree('4', 100);
        $this->consumeCurrency('4', 110, $triggerType, $triggerId, 'テストガチャ1');
        // 別リクエストでの消費(groupingは別になる)
        $this->setTestNow(new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo'));
        $this->consumeCurrency('4', 20, $triggerType, $triggerId, 'テストガチャ1');
        // 期間外のログを作成
        $this->setTestNow(new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('5', 100, 'id4');
        $this->consumeCurrency('5', 90, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->unionLogCurrencyRepository->getUnionQueryWithExcelSelect(
            new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo'),
            new CarbonImmutable('2023-01-31 23:59:59', 'Asia/Tokyo'),
            $triggerType,
            $triggerId,
            false,
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get()->toArray();
        $this->assertCount(6, $actual);
        $row = $actual[0];
        $this->assertEquals('4', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-31 14:59:59', $carbon->utc()->toDateTimeString());
        $this->assertEquals('-20', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
        $row = $actual[1];
        $this->assertEquals('4', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-31 14:59:58', $carbon->utc()->toDateTimeString());
        $this->assertEquals('-10', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-100', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[2];
        $this->assertEquals('3', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-01 15:00:01',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('0', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-10', $row['sum_log_change_amount_free']);
        $this->assertEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[3];
        $this->assertEquals('3', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-01 15:00:00',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('0', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-90', $row['sum_log_change_amount_free']);
        $this->assertEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[4];
        $this->assertEquals('2', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2022-12-31 15:00:01',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('-10', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
        $row = $actual[5];
        $this->assertEquals('2', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2022-12-31 15:00:00',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('-90', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
    }

    #[Test]
    public function getUnionQueryWithExcelSelect_ソートの確認()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        // 期間前のログを作成
        $this->setTestNow(new Carbon('2022-12-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 期間内のログを作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('2', 100, 'id2');
        $this->consumeCurrency('2', 90, $triggerType, $triggerId, 'テストガチャ1');
        $this->setTestNow(new Carbon('2023-01-02 00:00:00', 'Asia/Tokyo'));
        $this->addFree('3', 100);
        $this->consumeCurrency('3', 90, $triggerType, $triggerId, 'テストガチャ1');
        // 有償・無償
        $this->setTestNow(new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('4', 100, 'id3');
        $this->addFree('4', 100);
        $this->consumeCurrency('4', 110, $triggerType, $triggerId, 'テストガチャ1');
        // 期間外のログを作成
        $this->setTestNow(new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('5', 100, 'id4');
        $this->consumeCurrency('5', 90, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->unionLogCurrencyRepository->getUnionQueryWithExcelSelect(
            new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo'),
            new CarbonImmutable('2023-01-31 23:59:59', 'Asia/Tokyo'),
            $triggerType,
            $triggerId,
            false,
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get()->toArray();
        $this->assertCount(3, $actual);
        $row = $actual[0];
        $this->assertEquals('4', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-31 14:59:59', $carbon->utc()->toDateTimeString());
        $this->assertEquals('-10', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-100', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[1];
        $this->assertEquals('3', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2023-01-01 15:00:00', $carbon->utc()->toDateTimeString());
        $this->assertEquals('0', $row['sum_log_change_amount_paid']);
        $this->assertEquals('-90', $row['sum_log_change_amount_free']);
        $this->assertEmpty($row['log_currency_paid_ids']);
        $this->assertNotEmpty($row['log_currency_free_ids']);
        $row = $actual[2];
        $this->assertEquals('2', $row['usr_user_id']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $carbon = new Carbon($row['created_at']);
        $this->assertEquals('2022-12-31 15:00:00',  $carbon->utc()->toDateTimeString());
        $this->assertEquals('-90', $row['sum_log_change_amount_paid']);
        $this->assertEquals('0', $row['sum_log_change_amount_free']);
        $this->assertNotEmpty($row['log_currency_paid_ids']);
        $this->assertEmpty($row['log_currency_free_ids']);
    }

    #[Test]
    #[DataProvider('param_getConsumeLogWithUnionQuery_消費ログかつサンドボックスログの検索')]
    public function getConsumeLogWithUnionQuery_消費ログかつサンドボックスログの検索(bool $isConcludeSandbox, int $expectedCount)
    {
        // Setup
        $triggerType = '';
        $triggerId = '';
        $usrUserId = '';
        $searchStartAt = '';
        $searchEndAt = '';
        // 有償通貨の消費ログを1件作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, 'Gacha', 'test01', 'テストガチャ1');
        // 有償通貨の消費ログを1件作成 - サンドボックス
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('2', 100, 'id2', true);
        $this->consumeCurrency('2', 80, 'Gacha', 'test02', 'テストガチャ2');

        // Exercise
        $resultBuilder = $this->unionLogCurrencyRepository->getConsumeLogWithUnionQuery(
            $searchStartAt,
            $searchEndAt,
            $triggerType,
            $triggerId,
            $usrUserId,
            $isConcludeSandbox
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount($expectedCount, $actual);
        $row = $actual->where('usr_user_id', '1')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $this->assertEquals('paid', $row['log_currency_type']);
        $this->assertEquals('-90', $row['log_change_amount_paid']);
        $this->assertEquals('0', $row['log_change_amount_free']);
        $this->assertEquals('-90', $row['log_change_amount']);
        $this->assertEquals(0, $row['is_sandbox']);
        if ($isConcludeSandbox) {
            $row = $actual->where('usr_user_id', '2')->first();
            $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
            $this->assertEquals('-80', $row['change_amount']);
            $this->assertEquals('Gacha', $row['trigger_type']);
            $this->assertEquals('test02', $row['trigger_id']);
            $this->assertEquals('paid', $row['log_currency_type']);
            $this->assertEquals('-80', $row['log_change_amount_paid']);
            $this->assertEquals('0', $row['log_change_amount_free']);
            $this->assertEquals('-80', $row['log_change_amount']);
            $this->assertEquals(1, $row['is_sandbox']);
        }
    }

    public static function param_getConsumeLogWithUnionQuery_消費ログかつサンドボックスログの検索()
    {
        return [
            'サンドボックスを含める' => [true, 2],
            'サンドボックスを含めない' => [false, 1],
        ];
    }

    #[Test]
    public function getUnionQuery_paidログ_1件()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $usrUserId = '1';
        $carbon = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $searchEndAt = '';
        // 有償通貨の消費ログを1件作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid($usrUserId, 100, 'id1');
        $this->consumeCurrency($usrUserId, 90, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'getUnionQuery',
            [$searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(1, $actual);
        $row = $actual->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('paid', $row['log_currency_type']);
        $this->assertEquals('-90', $row['log_change_amount_paid']);
        $this->assertEquals('0', $row['log_change_amount_free']);
        $this->assertEquals('-90', $row['log_change_amount']);
    }

    #[Test]
    public function getUnionQuery_paidログ_複数件()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $usrUserId = '1';
        $carbon = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $searchEndAt = '';
        // 有償通貨の消費ログを複数件作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid($usrUserId, 100, 'id1');
        $this->addPaid($usrUserId, 100, 'id2');
        $this->consumeCurrency($usrUserId, 110, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'getUnionQuery',
            [$searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(2, $actual);
        $row = $actual->where('change_amount', '-100')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('paid', $row['log_currency_type']);
        $this->assertEquals('-100', $row['log_change_amount_paid']);
        $this->assertEquals('0', $row['log_change_amount_free']);
        $this->assertEquals('-100', $row['log_change_amount']);
        $row = $actual->where('change_amount', '-10')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('paid', $row['log_currency_type']);
        $this->assertEquals('-10', $row['log_change_amount_paid']);
        $this->assertEquals('0', $row['log_change_amount_free']);
        $this->assertEquals('-10', $row['log_change_amount']);
    }

    #[Test]
    public function getUnionQuery_paidログとfreeログ()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $usrUserId = '1';
        $carbon = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $searchEndAt = '';
        // 有償通貨と無償通貨の消費ログを作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid($usrUserId, 100, 'id1');
        $this->addFree($usrUserId, 100);
        $this->consumeCurrency($usrUserId, 110, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'getUnionQuery',
            [$searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(2, $actual);
        $row = $actual->where('log_currency_type', 'free')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('-100', $row['change_ingame_amount']);
        $this->assertEquals('0', $row['log_change_amount_paid']);
        $this->assertEquals('-100', $row['log_change_amount_free']);
        $this->assertEquals('-100', $row['log_change_amount']);
        $row = $actual->where('log_currency_type', 'paid')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('-10', $row['change_amount']);
        $this->assertEquals('-10', $row['log_change_amount_paid']);
        $this->assertEquals('0', $row['log_change_amount_free']);
        $this->assertEquals('-10', $row['log_change_amount']);
    }

    #[Test]
    public function getUnionQuery_freeログ()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $usrUserId = '1';
        $carbon = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $searchEndAt = '';
        // 有償通貨と無償通貨の消費ログを作成
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        $this->addFree($usrUserId, 100);
        $this->addFree($usrUserId, 100, 'reward');
        $this->consumeCurrency($usrUserId, 120, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'getUnionQuery',
            [$searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(1, $actual);
        $row = $actual->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals($triggerType, $row['trigger_type']);
        $this->assertEquals($triggerId, $row['trigger_id']);
        $this->assertEquals($usrUserId, $row['usr_user_id']);
        $this->assertEquals('-100', $row['change_ingame_amount']);
        $this->assertEquals('-20', $row['change_reward_amount']);
        $this->assertEquals('free', $row['log_currency_type']);
        $this->assertEquals('0', $row['log_change_amount_paid']);
        $this->assertEquals('-120', $row['log_change_amount_free']);
        $this->assertEquals('-120', $row['log_change_amount']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_startAtで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $carbon = new Carbon('2023-02-02 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $searchEndAt = '';
        $triggerType = null;
        $triggerId = null;
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(6, $actual);
        $row = $actual->where('usr_user_id', '5')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '5')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '6')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '6')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '7')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '7')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);

    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_endAtで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $carbon = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $searchEndAt = $carbon->utc()->toDateTimeString();
        $searchStartAt = '';
        $triggerType = null;
        $triggerId = null;
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(2, $actual);
        $row = $actual->where('usr_user_id', '1')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '1')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2022-12-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_startAtとendAtで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $carbon = new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $carbon = new Carbon('2023-02-01 23:59:59', 'Asia/Tokyo');
        $searchEndAt = $carbon->utc()->toDateTimeString();
        $triggerType = null;
        $triggerId = null;
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(6, $actual);
        $row = $actual->where('usr_user_id', '2')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '2')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '3')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '3')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '4')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '4')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_usrUserIdで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $searchStartAt = null;
        $searchEndAt = null;
        $triggerType = null;
        $triggerId = null;
        $usrUserId = '6';

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(2, $actual);
        $row = $actual->where('usr_user_id', '6')->where('trigger_type', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('100', $row['change_amount']);
        $row = $actual->where('usr_user_id', '6')->where('trigger_type', '!=', 'purchased')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_triggerTypeで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $searchStartAt = null;
        $searchEndAt = null;
        $triggerType = 'TradeShopItem';
        $triggerId = '';
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(4, $actual);
        $row = $actual->where('usr_user_id', '1')->where('change_amount', '-10')->first();
        $this->assertEquals('2023-01-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '1')->where('change_amount', '-80')->first();
        $this->assertEquals('2023-01-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '3')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '6')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('TradeShopItem', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_triggerIdで絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $searchStartAt = null;
        $searchEndAt = null;
        $triggerType = '';
        $triggerId = 'test02';
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(4, $actual);
        $row = $actual->where('usr_user_id', '1')->where('change_amount', '-20')->first();
        $this->assertEquals('2023-01-31 14:59:59', $row['created_at']->toDatetimeString());
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '1')->where('change_amount', '-70')->first();
        $this->assertEquals('2023-01-31 14:59:59', $row['created_at']->toDatetimeString());
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '4')->first();
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
        $row = $actual->where('usr_user_id', '7')->first();
        $this->assertEquals('2023-02-01 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('-90', $row['change_amount']);
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_一括一次通貨返却の検索条件で絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $carbon = new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo');
        $searchStartAt = $carbon->utc()->toDateTimeString();
        $carbon = new Carbon('2023-02-01 23:59:59', 'Asia/Tokyo');
        $searchEndAt = $carbon->utc()->toDateTimeString();
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $usrUserId = null;

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(1, $actual);
        $row = $actual->first();
        $this->assertEquals('2', $row['usr_user_id']);
        $this->assertEquals('2023-01-31 15:00:00', $row['created_at']->toDatetimeString());
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test01', $row['trigger_id']);
        $this->assertEquals('-90', $row['change_amount']);
    }

    #[Test]
    public function buildQuery_検索条件で絞り込めるか_一次通貨返却詳細の検索条件で絞り込み()
    {
        // Setup
        $this->setTestDataForBuildQuery();
        $builder = LogCurrencyPaid::query();
        $searchStartAt = null;
        $searchEndAt = null;
        $triggerType = 'Gacha';
        $triggerId = 'test02';
        $usrUserId = '1';

        // Exercise
        $resultBuilder = $this->callMethod(
            $this->unionLogCurrencyRepository,
            'buildQuery',
            [$builder, $searchStartAt, $searchEndAt, $triggerType, $triggerId, $usrUserId]
        );

        // Verify
        /** @var Collection $actual */
        $actual = $resultBuilder->get();
        $this->assertCount(2, $actual);
        $row = $actual->where('change_amount', '-20')->first();
        $this->assertEquals('1', $row['usr_user_id']);
        $this->assertEquals('2023-01-31 14:59:59', $row['created_at']->toDatetimeString());
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
        $row = $actual->where('change_amount', '-70')->first();
        $this->assertEquals('1', $row['usr_user_id']);
        $this->assertEquals('2023-01-31 14:59:59', $row['created_at']->toDatetimeString());
        $this->assertEquals('Gacha', $row['trigger_type']);
        $this->assertEquals('test02', $row['trigger_id']);
    }

    private function setTestDataForBuildQuery(): void
    {
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        // 有償通貨ログ1つ
        $this->addPaid('1', 100, 'id1');
        $this->consumeCurrency('1', 90, 'Gacha', 'test01', 'テスト1');
        // 有償通貨ログ2つ
        $this->setTestNow(new Carbon('2023-01-02 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id2');
        $this->consumeCurrency('1', 90, 'TradeShopItem', 'test01', 'テスト1');
        $this->setTestNow(new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo'));
        $this->addPaid('1', 100, 'id3');
        $this->consumeCurrency('1', 90, 'Gacha', 'test02', 'テスト2');
        // ユーザー違い
        $this->setTestNow(new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('2', 100, 'id4');
        $this->consumeCurrency('2', 90, 'Gacha', 'test01', 'テスト1');
        $this->addPaid('3', 100, 'id5');
        $this->consumeCurrency('3', 90, 'TradeShopItem', 'test01', 'テスト1');
        $this->addPaid('4', 100, 'id6');
        $this->consumeCurrency('4', 90, 'Gacha', 'test02', 'テスト2');
        $this->setTestNow(new Carbon('2023-02-02 00:00:00', 'Asia/Tokyo'));
        $this->addPaid('5', 100, 'id7');
        $this->consumeCurrency('5', 90, 'Gacha', 'test01', 'テスト1');
        $this->addPaid('6', 100, 'id8');
        $this->consumeCurrency('6', 90, 'TradeShopItem', 'test01', 'テスト1');
        $this->addPaid('7', 100, 'id9');
        $this->consumeCurrency('7', 90, 'Gacha', 'test02', 'テスト2');
    }

    /**
     * 有償通貨を配布
     * @param string $usrUserId
     * @param int $addAmount
     * @param string $reciptId
     * @param bool $isSandbox
     * @return void
     */
    private function addPaid(
        string $usrUserId,
        int $addAmount,
        string $reciptId,
        bool $isSandbox = false,
    ): void {
        // 通貨管理の登録
        // 既にデータがある場合はそのデータが返ってくる
        $this->currencyService->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $addAmount,
            'JPY',
            '100',
            101,
            $reciptId,
            $isSandbox,
            new Trigger('purchased', '1', '', 'add currency'),
        );
    }

    /**
     * 無償通貨を配布
     * @param string $usrUserId
     * @param int $addAmount
     * @param string $type
     * @return void
     */
    private function addFree(
        string $usrUserId,
        int $addAmount,
        string $type = 'ingame'
    ): void {
        // 通貨管理の登録
        // 既にデータがある場合はそのデータが返ってくる
        $this->currencyService->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 無償一次通貨の登録　(ログも一緒)
        $this->currencyService->addFree(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $addAmount,
            $type,
            new Trigger('purchased', '1', '', 'add currency'),
        );
    }

    /**
     * 通貨の消費を実行
     * @param string $usrUserId
     * @param int $consumeCount
     * @param string $triggerType
     * @param string $triggerId
     * @param string $triggerName
     * @return void
     */
    private function consumeCurrency(string $usrUserId, int $consumeCount, string $triggerType, string $triggerId, string $triggerName): void
    {
        // 通貨の消費
        $this->currencyService->useCurrency(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $consumeCount,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );
    }
}
