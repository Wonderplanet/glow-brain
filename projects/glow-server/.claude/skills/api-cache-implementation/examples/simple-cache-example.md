# シンプルなキャッシュ実装例

MngCacheRepositoryを使ったシンプルなキャッシュ実装の例を紹介します。

## 実装例: MngCacheRepository

マスターデータのキャッシュを管理するRepositoryです。

### ファイル構成

```
api/app/Infrastructure/
└── MngCacheRepository.php

api/app/Domain/Resource/Mng/Repositories/
├── MngMessageBundleRepository.php
├── MngMasterReleaseVersionRepository.php
└── MngAssetReleaseVersionRepository.php
```

### MngCacheRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class MngCacheRepository
{
    private const DEFAULT_TTL_SECONDS = 86400; // 1日

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * キャッシュを取得、作成する
     * @param string $cacheKey
     * @param callable $closure
     * @return mixed
     */
    public function getOrCreateCache(string $cacheKey, callable $closure): mixed
    {
        $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = $closure();
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $data, self::DEFAULT_TTL_SECONDS);

        return $data;
    }

    /**
     * キャッシュを削除する
     * @param string $cacheKey
     * @return void
     */
    public function deleteCache(string $cacheKey): void
    {
        $this->cacheClientManager->getCacheClient()->del($cacheKey);

        Log::debug('MngCacheRepository: deleteCache', ['cacheKey' => $cacheKey]);
    }

    /**
     * CarbonImmutable::setTestNow()の影響を受けない時刻を取得する
     *
     * デバッグ時間変更機能が有効な場合でも、キャッシュ生成時は実時刻を基準にする
     */
    public function getCacheBaseTime(CarbonImmutable $now): CarbonImmutable
    {
        // 固定時間設定がなかったり、本番環境ならそのまま返す
        if (!CarbonImmutable::hasTestNow() || app()->isProduction()) {
            return $now;
        }

        // 現在時刻のデータが十分含まれる程度の過去の時間にする
        $realNow = new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone')));
        return CarbonImmutable::createFromTimestamp(
            $realNow->getTimestamp(),
            config('app.timezone'),
        )->subMonths(6);
    }
}
```

### 使用例1: MngMessageBundleRepository

メッセージバンドルをキャッシュする例。

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngMessageBundle;
use App\Infrastructure\MngCacheRepository;
use Illuminate\Support\Collection;

class MngMessageBundleRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * 言語別のメッセージバンドルを取得
     */
    public function getByLanguage(string $language): Collection
    {
        $cacheKey = CacheKeyUtil::getMngMessageBundleKey($language);

        return $this->mngCacheRepository->getOrCreateCache(
            $cacheKey,
            fn() => MngMessageBundle::query()
                ->where('language', $language)
                ->get()
        );
    }

    /**
     * キャッシュを削除（管理画面から更新時）
     */
    public function clearCache(string $language): void
    {
        $cacheKey = CacheKeyUtil::getMngMessageBundleKey($language);
        $this->mngCacheRepository->deleteCache($cacheKey);
    }
}
```

**使用例:**
```php
// サービスクラスから呼び出し
$messages = $this->mngMessageBundleRepository->getByLanguage('ja');

// 管理画面で更新後にキャッシュクリア
$this->mngMessageBundleRepository->clearCache('ja');
```

### 使用例2: MngMasterReleaseVersionRepository

マスターリリースバージョンをキャッシュする例。

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngMasterReleaseVersion;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;

class MngMasterReleaseVersionRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * 最新のマスターリリースバージョンを取得
     */
    public function getLatestVersion(): ?MngMasterReleaseVersion
    {
        $cacheKey = CacheKeyUtil::getMngMasterReleaseVersionKey();

        return $this->mngCacheRepository->getOrCreateCache(
            $cacheKey,
            function () {
                $now = CarbonImmutable::now();
                $baseTime = $this->mngCacheRepository->getCacheBaseTime($now);

                return MngMasterReleaseVersion::query()
                    ->where('release_date', '<=', $baseTime)
                    ->orderBy('release_date', 'desc')
                    ->first();
            }
        );
    }

    /**
     * キャッシュを削除
     */
    public function clearCache(): void
    {
        $cacheKey = CacheKeyUtil::getMngMasterReleaseVersionKey();
        $this->mngCacheRepository->deleteCache($cacheKey);
    }
}
```

**使用例:**
```php
// 最新バージョン取得
$latestVersion = $this->mngMasterReleaseVersionRepository->getLatestVersion();

// 新バージョンリリース後にキャッシュクリア
$this->mngMasterReleaseVersionRepository->clearCache();
```

### 使用例3: MngAssetReleaseVersionRepository

プラットフォーム別のアセットバージョンをキャッシュする例。

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngAssetReleaseVersion;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;

class MngAssetReleaseVersionRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * プラットフォーム別の最新アセットバージョンを取得
     */
    public function getLatestVersionByPlatform(int $platform): ?MngAssetReleaseVersion
    {
        $cacheKey = CacheKeyUtil::getMngAssetReleaseVersionKey($platform);

        return $this->mngCacheRepository->getOrCreateCache(
            $cacheKey,
            function () use ($platform) {
                $now = CarbonImmutable::now();
                $baseTime = $this->mngCacheRepository->getCacheBaseTime($now);

                return MngAssetReleaseVersion::query()
                    ->where('platform', $platform)
                    ->where('release_date', '<=', $baseTime)
                    ->orderBy('release_date', 'desc')
                    ->first();
            }
        );
    }

    /**
     * プラットフォーム別にキャッシュを削除
     */
    public function clearCacheByPlatform(int $platform): void
    {
        $cacheKey = CacheKeyUtil::getMngAssetReleaseVersionKey($platform);
        $this->mngCacheRepository->deleteCache($cacheKey);
    }

    /**
     * 全プラットフォームのキャッシュを削除
     */
    public function clearAllCache(): void
    {
        // iOS, Android, Web など全プラットフォーム
        $platforms = [1, 2, 3];
        foreach ($platforms as $platform) {
            $this->clearCacheByPlatform($platform);
        }
    }
}
```

**使用例:**
```php
// iOS用の最新アセットバージョン取得
$latestVersion = $this->mngAssetReleaseVersionRepository->getLatestVersionByPlatform(1);

// 新アセットリリース後にキャッシュクリア
$this->mngAssetReleaseVersionRepository->clearCacheByPlatform(1); // iOS
$this->mngAssetReleaseVersionRepository->clearCacheByPlatform(2); // Android
```

## デバッグ時間変更への対応

### 問題

デバッグ時間変更機能（`Carbon::setTestNow()`）を使用すると、未来時刻でキャッシュが作成され、現在時刻でデータが取得できなくなる問題があります。

### 解決策

`getCacheBaseTime()`メソッドを使用して実時刻を基準にします。

```php
public function getLatestVersion(): ?MngMasterReleaseVersion
{
    $cacheKey = CacheKeyUtil::getMngMasterReleaseVersionKey();

    return $this->mngCacheRepository->getOrCreateCache(
        $cacheKey,
        function () {
            $now = CarbonImmutable::now();
            // デバッグ時間の影響を受けない基準時刻を取得
            $baseTime = $this->mngCacheRepository->getCacheBaseTime($now);

            return MngMasterReleaseVersion::query()
                ->where('release_date', '<=', $baseTime)
                ->orderBy('release_date', 'desc')
                ->first();
        }
    );
}
```

## テスト実装例

### MngMessageBundleRepositoryTest.php

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngMessageBundle;
use App\Domain\Resource\Mng\Repositories\MngMessageBundleRepository;
use Tests\TestCase;

class MngMessageBundleRepositoryTest extends TestCase
{
    private MngMessageBundleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(MngMessageBundleRepository::class);
    }

    public function test_getByLanguage_キャッシュがない場合はDBから取得する(): void
    {
        // Arrange
        $language = 'ja';
        MngMessageBundle::factory()->count(5)->create(['language' => $language]);

        // Act
        $result = $this->repository->getByLanguage($language);

        // Assert
        $this->assertCount(5, $result);
    }

    public function test_getByLanguage_キャッシュがある場合はキャッシュから取得する(): void
    {
        // Arrange
        $language = 'ja';
        MngMessageBundle::factory()->count(5)->create(['language' => $language]);

        // 1回目はDBから取得（キャッシュに保存される）
        $firstResult = $this->repository->getByLanguage($language);

        // DBのデータを削除
        MngMessageBundle::query()->delete();

        // Act（2回目はキャッシュから取得）
        $secondResult = $this->repository->getByLanguage($language);

        // Assert（キャッシュから取得できている）
        $this->assertCount(5, $secondResult);
    }

    public function test_clearCache_キャッシュを削除できる(): void
    {
        // Arrange
        $language = 'ja';
        MngMessageBundle::factory()->count(5)->create(['language' => $language]);

        // キャッシュに保存
        $this->repository->getByLanguage($language);

        // Act（キャッシュ削除）
        $this->repository->clearCache($language);

        // DBのデータを削除
        MngMessageBundle::query()->delete();

        // Assert（キャッシュが削除されているので空になる）
        $result = $this->repository->getByLanguage($language);
        $this->assertCount(0, $result);
    }
}
```

## まとめ

MngCacheRepositoryを使ったシンプルなキャッシュ実装では、以下のパターンが基本となります。

1. **getOrCreateCache()**: キャッシュがあれば取得、なければDB取得してキャッシュに保存
2. **deleteCache()**: キャッシュを削除（管理画面からの更新時など）
3. **getCacheBaseTime()**: デバッグ時間変更機能への対応

この実装パターンは、マスターデータや更新頻度の低いデータのキャッシュに適しています。
