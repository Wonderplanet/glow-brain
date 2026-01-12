<?php

declare(strict_types=1);

namespace WonderPlanet\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Foundation\Testing\TestCase as TestingBaseTestCase;
use WonderPlanet\Tests\Traits\ReflectionTrait;

/**
 * テストケースの基底クラス
 *
 * API/admin/libで共通となるテスト向けの処理はこちらに記述する
 * 
 * TODO: laravelのテストケースでcreateApplicationを実装する必要がある。
 *       ここではlaravelプロジェクトに依存した$appを作成して返しているため、
 *       現在はライブラリ側で独立させることができない。
 *       ライブラリ側でテストを書くにあたり、これをどうするかが課題になっている。
 */
abstract class BaseTestCase extends TestingBaseTestCase
{
    use ReflectionTrait;

    /**
     * バックアップ対象のキーを指定する
     * 
     * ここに列挙されたキーは、setUpでバックアップされ、tearDownでリストアされる
     *
     * @var array
     */
    protected $backupConfigKeys = [];

    /**
     * 設定値のバックアップ
     * setupで取得し、tearDownでリストアする
     *
     * @var array
     */
    protected $backupConfig = [];

    /**
     * コンストラクタ
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // メモリ上限設定
        // 共通で上限を設定する
        ini_set('memory_limit', '512M');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の時刻を現在時刻に固定
        // テスト内での時刻変更が必要になる場合は、それぞれで行う
        $this->setTestNow(date('Y-m-d H:i:sP'));

        // 設定値のバックアップ
        foreach ($this->backupConfigKeys as $key) {
            $this->backupConfig[$key] = config($key);
        }
    }

    protected function tearDown(): void
    {
        // 設定値のレストア
        foreach ($this->backupConfig as $key => $value) {
            config([$key => $value]);
        }

        // 時刻の設定を解除
        $this->setTestNow();

        parent::tearDown();
    }

    /**
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

    /**
     * system_logged_atの判定に使用する現在日時を返す
     *
     * @return CarbonImmutable
     * @throws \DateInvalidTimeZoneException
     */
    protected function getSystemNow(): CarbonImmutable
    {
        $systemNow = new DateTime();
        $systemNow->setTimezone(new \DateTimeZone(config('app.timezone')));
        return CarbonImmutable::createFromTimestamp($systemNow->getTimestamp(), $systemNow->getTimezone());
    }
}
