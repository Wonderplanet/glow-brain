<?php

namespace Tests;

use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Enums\Language;
use App\Domain\Constants\Database;
use App\Domain\Debug\Services\DebugForDevelopService;
use App\Domain\Mission\Services\MissionUpdateHandleService;
use App\Domain\Resource\Mng\Models\MngClientVersion;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Infrastructure\LogModelManager;
use App\Infrastructure\UsrModelManager;
use Artisan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use ReflectionClass;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;
use WonderPlanet\Tests\Traits\ReflectionTrait;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use ReflectionTrait;

    protected array $connectionsToTransact = [
        Database::MST_CONNECTION,
        Database::MNG_CONNECTION,
        Database::TIDB_CONNECTION,
        Database::ADMIN_CONNECTION,
    ];

    protected string $usrUserId;

    protected UsrModelManager $usrModelManager;

    protected MissionUpdateHandleService $missionUpdateHandleService;

    private CurrencyService $currencyService;

    /**
     * スキーマエクスポートが完了したかどうかのフラグ
     * migrate:fresh実行後の最初の1回だけtrueにする
     */
    private static bool $needDbExportSchema = false;

    protected function setUp(): void
    {
        parent::setUp();

        $usrUserId = fake()->uuid();
        $this->setUsrUserId($usrUserId);

        $this->missionUpdateHandleService = app(MissionUpdateHandleService::class);

        $this->currencyService = app(CurrencyService::class);

        $this->mockDebugForDevelopService();

        $this->mockMngCacheRepository();

        // laravel-wp-currencyのテストがfalse前提で実装されているため、テストではfalseをデフォルトとしておく
        Config::set('wp_currency.store.separate_currency_limit_check', false);
    }

    protected function tearDown(): void
    {
        // テスト後にRedisのデータを削除
        Redis::connection()->flushall();
        parent::tearDown();
    }

    /**
     * SKIP_TESTSがtrueの場合、テストをスキップする
     * デフォルトでSKIP_TESTSがtrueになっているため、テストを実行する場合はfalseに設定する必要がある
     * @return void
     */
    protected function skipIfSkipTestsEnabled(string $addMessage = ''): void
    {
        if (env('SKIP_TESTS', true)) {
            $this->markTestSkipped('SKIP_TESTS is true. Skipping test.' . $addMessage);
        }
    }

    protected function setUsrUserId(string $usrUserId): void
    {
        $this->usrUserId = $usrUserId;
        $this->actingAs($this->createDummyUser());

        $this->usrModelManager = $this->app->make(UsrModelManager::class);
        $this->usrModelManager->setUsrUserId($usrUserId);

    }

    protected function fixTime(?string $dateTime = null): CarbonImmutable
    {
        // $dateTime=nullの場合は現在時刻になる
        $dateTime = CarbonImmutable::parse($dateTime);

        CarbonImmutable::setTestNow($dateTime);
        \Illuminate\Support\Carbon::setTestNow($dateTime);

        return $dateTime;
    }

    /**
     * API開発途中のみ使うデバッグ用ロジックをまとめたクラスのメソッドをすべてモックする
     * 何も処理をさせないメソッドにモックして、他テストへの悪影響を排除する
     */
    protected function mockDebugForDevelopService(): void
    {
        $className = DebugForDevelopService::class;
        $reflection = new ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $this->mock(DebugForDevelopService::class, function ($mock) use ($methods) {
            // 取得した全メソッドをモックに設定
            foreach ($methods as $method) {
                $methodName = $method->getName();
                $mock->shouldReceive($methodName)->andReturn('mocked' . ucfirst($methodName));
            }
        });
    }

    /**
     * MngCacheRepositoryのgetCacheBaseTimeメソッドをモックする
     * 本番挙動と同じになるように、getCacheBaseTimeがfixTimeで設定した時間を返すようにする
     */
    protected function mockMngCacheRepository(): void
    {
        $this->instance(
            \App\Infrastructure\MngCacheRepository::class,
            \Mockery::mock(
                \App\Infrastructure\MngCacheRepository::class,
                [app()->make(\App\Domain\Common\Managers\Cache\CacheClientManager::class)]
            )
                ->makePartial()
                ->shouldReceive('getCacheBaseTime')
                ->andReturnUsing(fn($now) => $now)
                ->getMock()
        );
    }

    protected function createDummyUser()
    {
        return new class(id: $this->usrUserId) extends CurrentUser implements Authenticatable
        {
            public function getAuthIdentifierName()
            {
                throw new \BadMethodCallException();
            }

            public function getAuthIdentifier()
            {
                throw new \BadMethodCallException();
            }

            public function getAuthPasswordName()
            {
                throw new \BadMethodCallException();
            }

            public function getAuthPassword()
            {
                throw new \BadMethodCallException();
            }

            public function getRememberToken()
            {
                throw new \BadMethodCallException();
            }

            public function setRememberToken($value)
            {
                throw new \BadMethodCallException();
            }

            public function getRememberTokenName()
            {
                throw new \BadMethodCallException();
            }
        };
    }

    protected function saveAll(): void
    {
        $this->usrModelManager->saveAll();
    }

    protected function saveAllLogModel(): void
    {
        $logModelManager = app()->make(LogModelManager::class);
        $logModelManager->saveAll();
    }

    /**
     * UsrModelManagerにsetされたユーザーIDでUsrUserモデルインスタンスを作成し、DBに保存する
     */
    protected function createUsrUser(array $attributes = []): UsrUser
    {
        return UsrUser::factory()->create(array_merge([
            'id' => $this->usrUserId,
        ], $attributes));
    }

    /**
     * privateメソッドをテストするためのメソッド
     */
    public function execPrivateMethod($targetClass, string $methodName, array $args = [])
    {
        $reflectionClass = new ReflectionClass($targetClass);
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($targetClass, $args);
    }

    /**
     * private定数を取得するためのメソッド
     */
    public function getClassPrivateConstantValue(string $targetClass, string $constantName)
    {
        $reflectionClass = new ReflectionClass($targetClass);
        $constant = $reflectionClass->getReflectionConstant($constantName);
        return $constant->getValue();
    }

    /**
     * テストで確認するために、UsrModelManagerのprivate変数を取得するメソッド
     */
    public function getUsrModelManagerPrivateVariable(string $variableName)
    {
        $reflectionClass = new ReflectionClass($this->usrModelManager);
        $property = $reflectionClass->getProperty($variableName);
        $property->setAccessible(true);
        return $property->getValue($this->usrModelManager);
    }

    /**
     * テストで確認するために、UsrModelManagerのprivate変数へ値を設定するメソッド
     */
    public function setUsrModelManagerPrivateVariable(string $variableName, $value)
    {
        $reflectionClass = new ReflectionClass($this->usrModelManager);
        $property = $reflectionClass->getProperty($variableName);
        $property->setAccessible(true);
        $property->setValue($this->usrModelManager, $value);
    }

    /**
     * JSONでPOSTリクエストを送信する
     *
     * 共通情報を入れるためオーバーライド
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @param  int  $options
     * @return \Illuminate\Testing\TestResponse
     */
    public function postJson($uri, array $data = [], array $headers = [], $options = 0)
    {
        // テスト用にデフォルトのheaderを用意する
        if (!isset($headers[System::HEADER_PLATFORM])) {
            $headers[System::HEADER_PLATFORM] = UserConstant::PLATFORM_IOS;
        }
        if (!isset($headers[System::HEADER_LANGUAGE])) {
            $headers[System::HEADER_LANGUAGE] = Language::Ja->value;
        }
        if (!isset($headers[System::CLIENT_VERSION])) {
            $headers[System::CLIENT_VERSION] = '0.0.0';
        }
        if (!isset($headers[Config::get('wp_encryption.disable_header')])) {
            $headers[Config::get('wp_encryption.disable_header')] = true;
        }

        return parent::postJson($uri, $data, $headers, $options);
    }

    /**
     * RefreshDatabase::beforeRefreshingDatabase() のオーバーライド
     *
     * @return void
     */
    protected function beforeRefreshingDatabase()
    {
        // 未適用のマイグレーションがあるので、migrate:freshを実行する
        // RefreshDatabaseState::$migrated = false; の際にmigrate:freshが実行される
        if (
            RefreshDatabaseState::$migrated === false
            && $this->hasPendingMigrations() === false
        ) {
            RefreshDatabaseState::$migrated = true;
            self::$needDbExportSchema = true;

            echo "\nskipped migrate:fresh in RefreshDatabase.php refreshTestDatabase " . PHP_EOL;
        }

        // migrate:freshを実行する場合は、コンソールにメッセージを表示する
        // マイグレーション実行中にログが表示されず、何が起きているのか分からないため
        if (RefreshDatabaseState::$migrated === false) {
            fwrite(STDOUT, "exec migrate:fresh\n");
        }
    }

    /**
     * migrate:fresh実行後のフック処理
     *
     * テスト実行時のmigrate:fresh完了後に自動的にスキーマエクスポートを実行する。
     * migrate:fresh実行時の最初の1回のみ実行される。
     * 環境判定はDbExportSchemaCommand内で実施されるため、ここでは無条件に実行。
     *
     * @return void
     */
    protected function afterRefreshingDatabase()
    {
        // 既にスキーマエクスポートが完了している場合はスキップ
        if (self::$needDbExportSchema) {
            return;
        }

        try {
            Artisan::call('db:export-schema', ['--format' => 'json']);
            self::$needDbExportSchema = true;
        } catch (\Throwable $e) {
            // ログ出力のみ行い、マイグレーション処理には影響させない
            \Illuminate\Support\Facades\Log::warning(
                'Failed to export schema after migration',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * 未適用のマイグレーションがあるかどうかを確認する
     * true: 未適用のマイグレーションがある, false: 全てのマイグレーションが適用済み
     * @return bool
     */
    private function hasPendingMigrations(): bool
    {
        $statusCommandParameters = [
            [], // usr, log DB
            [
                '--database' => 'mst',
                '--path' => 'database/migrations/mst',
            ],
        ];

        foreach ($statusCommandParameters as $parameters) {
            Artisan::call('migrate:status', $parameters);
            $output = Artisan::output();

            // 出力に"Pending"という文字列が含まれているかを確認
            if (strpos($output, 'Pending') !== false) {
                return true; // 未適用のマイグレーションがある
            }
        }

        return false; // 全てのマイグレーションが適用済み
    }

    /**
     * 課金基盤を使ってダイヤを用意する
     * TODO: モックなどに置き換える必要あり。テストなのに課金基盤のserviceをそのまま使ってしまっている。
     *
     * @param string $usrUserId
     * @param integer $freeDiamond
     * @param integer $paidDiamondIos
     * @param integer $paidDiamondAndroid
     * @return void
     */
    protected function createDiamond(
        string $usrUserId,
        int $freeDiamond = 0,
        int $paidDiamondIos = 0,
        int $paidDiamondAndroid = 0,
    ): void {
        // 課金基盤情報
        $this->currencyService->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
            0,
        );

        if ($freeDiamond > 0) {
            $this->currencyService->addFree(
                $usrUserId,
                CurrencyConstants::OS_PLATFORM_IOS,
                $freeDiamond,
                CurrencyConstants::FREE_CURRENCY_TYPE_BONUS,
                new Trigger('test', '', '', ''),
            );
        }

        if ($paidDiamondIos > 0) {
            $this->currencyService->addCurrencyPaid(
                $usrUserId,
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $paidDiamondIos,
                'JPY',
                '100',
                100,
                'test-apple',
                true,
                new Trigger('test', '', '', ''),
            );
        }

        if ($paidDiamondAndroid > 0) {
            $this->currencyService->addCurrencyPaid(
                $usrUserId,
                CurrencyConstants::OS_PLATFORM_ANDROID,
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                $paidDiamondAndroid,
                'JPY',
                '100',
                100,
                'test-google',
                true,
                new Trigger('test', '', '', ''),
            );
        }
    }

    /**
     * 課金基盤を使ってダイヤの所持数を取得する
     * TODO: モックなどに置き換える必要あり。テストなのに課金基盤のserviceをそのまま使ってしまっている。
     *
     * @param string $usrUserId
     */
    protected function getDiamond(string $usrUserId): UsrCurrencySummaryEntity
    {
        return $this->currencyService->getCurrencySummary($usrUserId);
    }

    protected function setToRedis(string $key, mixed $value): void
    {
        Redis::connection()->set($key, serialize($value));
    }

    protected function getFromRedis(string $key): mixed
    {
        $value = Redis::connection()->get($key);
        if ($value === null) {
            return null;
        }
        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            // シリアライズされていない場合はそのまま返す
            // 主にテストでRedis::connection()->set()などを直接呼び出してセットし取得はRedisCacheClientのgetを取得するときなど
            return $value;
        }
    }

    protected function createMasterRelease(): void
    {
        MngMasterRelease::factory()->create([
            'id' => 1,
            'release_key' => 1,
            'enabled' => 1,
            'target_release_version_id' => 1,
            'client_compatibility_version' => '0.0.0',
            'description' => '',
        ]);
        MngMasterReleaseVersion::factory()->create([
            'id' => 1,
            'release_key' => 1,
            'git_revision' => 'test',
            'master_schema_version' => 'test',
            'data_hash' => 'test',
            'server_db_hash' => 'test',
            'client_mst_data_hash' => 'test',
            'client_mst_data_i18n_ja_hash' => 'test',
            'client_mst_data_i18n_en_hash' => 'test',
            'client_mst_data_i18n_zh_hash' => 'test',
            'client_opr_data_hash' => 'test',
            'client_opr_data_i18n_ja_hash' => 'test',
            'client_opr_data_i18n_en_hash' => 'test',
            'client_opr_data_i18n_zh_hash' => 'test',
        ]);

        MngClientVersion::factory()->createMany([
            [
                'client_version' => '0.0.0',
                'platform' => UserConstant::PLATFORM_IOS,
                'is_force_update' => 0,
            ],
            [
                'client_version' => '0.0.0',
                'platform' => UserConstant::PLATFORM_ANDROID,
                'is_force_update' => 1,
            ],
        ]);
    }

    /**
     * libのテストが通るように追加。GLOW側のコードでは使わない予定
     *
     * テスト用の日時を設定する
     *
     * `$datetime`は`Carbon|CarbonImmutable|string|null`を受け取る
     * 空文字またはnullを指定すると設定がクリアされる
     *
     * ※Carbon3から`setTestNow`の設定でタイムゾーンの挙動が変わるようになった。
     *  `setTestNow`で設定されたタイムゾーンが新しく生成されたオブジェクトにも引き継がれるようになり、
     *  さらに9時間ずれる事象が起きるためタイムゾーンを確認してUTCで登録するように調整しています。
     * ref: https://github.com/Wonderplanet/laravel-wp-framework/pull/1248#discussion_r2060051391
     *
     * @param Carbon|CarbonImmutable|string|null $datetime
     * @return void
     */
    protected function setTestNow(Carbon|CarbonImmutable|string|null $datetime = null): void
    {
        $carbon = CarbonImmutable::parse($datetime, 'UTC');
        if ($carbon->timezoneName !== 'UTC') {
            $carbon = $carbon->utc(); // UTC以外の場合はUTCに変換
        }

        // `\Carbon\Carbon`と`\Carbon\CarbonImmutable`の`setTestNow`が実行される
        \Illuminate\Support\Carbon::setTestNow($carbon);
    }
}
