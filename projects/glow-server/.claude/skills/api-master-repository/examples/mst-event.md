# MstEvent完全実装例

期間指定マスタ（start_at/end_at）の実装例として、MstEventを解説します。

## テーブル定義

```sql
CREATE TABLE mst_events (
    id VARCHAR(255) PRIMARY KEY,
    mst_series_id VARCHAR(255) NOT NULL,
    is_displayed_series_logo TINYINT NOT NULL,
    is_displayed_jump_plus TINYINT NOT NULL,
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    asset_key VARCHAR(255) NOT NULL,
    release_key INT NOT NULL,
    -- 他のカラム省略
);
```

## Model実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Models/MstEvent.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEventEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_series_id
 * @property int $is_displayed_series_logo
 * @property int $is_displayed_jump_plus
 * @property string $start_at
 * @property string $end_at
 * @property string $asset_key
 * @property int $release_key
 */
class MstEvent extends MstModel
{
    use HasFactory;

    public $timestamps = false;
    protected $connection = 'mst';
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_series_id' => 'string',
        'is_displayed_series_logo' => 'integer',
        'is_displayed_jump_plus' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
        'asset_key' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_series_id,
            $this->is_displayed_series_logo,
            $this->is_displayed_jump_plus,
            $this->start_at,
            $this->end_at,
            $this->asset_key,
            $this->release_key,
        );
    }
}
```

## Entity実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Entities/MstEventEntity.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use Carbon\CarbonImmutable;

class MstEventEntity
{
    public function __construct(
        private string $id,
        private string $mst_series_id,
        private int $is_displayed_series_logo,
        private int $is_displayed_jump_plus,
        private string $start_at,
        private string $end_at,
        private string $asset_key,
        private int $release_key,
    ) {}

    // === Getters ===

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstSeriesId(): string
    {
        return $this->mst_series_id;
    }

    public function isDisplayedSeriesLogo(): bool
    {
        return $this->is_displayed_series_logo === 1;
    }

    public function isDisplayedJumpPlus(): bool
    {
        return $this->is_displayed_jump_plus === 1;
    }

    public function getStartAt(): string
    {
        return $this->start_at;
    }

    public function getEndAt(): string
    {
        return $this->end_at;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }

    // === ビジネスロジック ===

    /**
     * イベントが現在有効かどうか
     */
    public function isActiveAt(CarbonImmutable $now): bool
    {
        return $now->between(
            CarbonImmutable::parse($this->start_at),
            CarbonImmutable::parse($this->end_at)
        );
    }

    /**
     * イベントが既に終了しているか
     */
    public function isExpired(CarbonImmutable $now): bool
    {
        return $now->greaterThan(CarbonImmutable::parse($this->end_at));
    }

    /**
     * イベントがまだ開始していないか
     */
    public function isNotStarted(CarbonImmutable $now): bool
    {
        return $now->lessThan(CarbonImmutable::parse($this->start_at));
    }

    /**
     * イベントの残り時間（秒）を取得
     */
    public function getRemainingSeconds(CarbonImmutable $now): int
    {
        $endAt = CarbonImmutable::parse($this->end_at);
        if ($now->greaterThanOrEqualTo($endAt)) {
            return 0;
        }
        return $now->diffInSeconds($endAt);
    }
}
```

## Repository実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Repositories/MstEventRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstEventEntity as Entity;
use App\Domain\Resource\Mst\Models\MstEvent as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstEventRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    /**
     * start_atとend_at内の対象IDのレコードを取得
     * @param string $id
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getActiveEvent(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAllActiveEvents($now)->get($id);

        $this->throwMstNotFoundException(
            $isThrowError,
            Model::class,
            $entity,
            ['id' => $id],
        );

        return $entity;
    }

    /**
     * start_atとend_at内の対象レコードを全て取得
     * @param CarbonImmutable $now
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(Model::class, $now)
            ->filter(function (Entity $entity) use ($now) {
                return $this->isActiveEntity($entity, $now);
            });
    }

    /**
     * 複数ID指定で有効なイベントを取得
     * @param Collection<string> $eventIds
     * @param CarbonImmutable $now
     * @return Collection<string, Entity>
     */
    public function getActiveEvents(Collection $eventIds, CarbonImmutable $now): Collection
    {
        if ($eventIds->isEmpty()) {
            return collect();
        }

        return $this->getAllActiveEvents($now)->only($eventIds->toArray());
    }

    /**
     * シリーズIDで有効なイベントを取得
     * @param string $seriesId
     * @param CarbonImmutable $now
     * @return Collection<string, Entity>
     */
    public function getActiveEventsBySeriesId(string $seriesId, CarbonImmutable $now): Collection
    {
        return $this->getAllActiveEvents($now)
            ->filter(function (Entity $entity) use ($seriesId) {
                return $entity->getMstSeriesId() === $seriesId;
            });
    }

    /**
     * ジャンプ+表示対象の有効なイベントを取得
     * @param CarbonImmutable $now
     * @return Collection<string, Entity>
     */
    public function getJumpPlusDisplayEvents(CarbonImmutable $now): Collection
    {
        return $this->getAllActiveEvents($now)
            ->filter(function (Entity $entity) {
                return $entity->isDisplayedJumpPlus();
            });
    }
}
```

**ポイント**:
- `MstRepositoryTrait`をuse
- `getDayActives()`で当日有効なイベントをキャッシュ
- `isActiveEntity()`でさらに厳密な期間チェック（秒単位）

## Factory実装

**ファイルパス**: `database/factories/Mst/MstEventFactory.php`

```php
<?php

namespace Database\Factories\Mst;

use App\Domain\Resource\Mst\Models\MstEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstEventFactory extends Factory
{
    protected $model = MstEvent::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'mst_series_id' => 'series_1',
            'is_displayed_series_logo' => 1,
            'is_displayed_jump_plus' => 0,
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-12-31 23:59:59',
            'asset_key' => 'event_asset',
            'release_key' => 1,
        ];
    }

    public function between(string $startAt, string $endAt): self
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);
    }

    public function seriesId(string $seriesId): self
    {
        return $this->state(fn (array $attributes) => [
            'mst_series_id' => $seriesId,
        ]);
    }

    public function jumpPlusDisplayed(bool $isDisplayed = true): self
    {
        return $this->state(fn (array $attributes) => [
            'is_displayed_jump_plus' => $isDisplayed ? 1 : 0,
        ]);
    }
}
```

## テスト実装

**ファイルパス**: `api/tests/Feature/Domain/Resource/Mst/Repositories/MstEventRepositoryTest.php`

```php
<?php

namespace Tests\Feature\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstEventRepositoryTest extends TestCase
{
    private MstEventRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MstEventRepository::class);
    }

    public function test_getAllActiveEvents_有効なイベントを全て取得できる()
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        MstEvent::factory()->createMany([
            // 有効なイベント
            ['id' => 'event_1', 'start_at' => '2025-01-01 00:00:00', 'end_at' => '2025-01-31 23:59:59'],
            ['id' => 'event_2', 'start_at' => '2025-01-10 00:00:00', 'end_at' => '2025-01-20 23:59:59'],
            // 無効なイベント（期間外）
            ['id' => 'event_3', 'start_at' => '2025-02-01 00:00:00', 'end_at' => '2025-02-28 23:59:59'],
            ['id' => 'event_4', 'start_at' => '2024-12-01 00:00:00', 'end_at' => '2024-12-31 23:59:59'],
        ]);

        // Exercise
        $result = $this->repository->getAllActiveEvents($now);

        // Verify
        $this->assertCount(2, $result);
        $this->assertTrue($result->has('event_1'));
        $this->assertTrue($result->has('event_2'));
    }

    public function test_getActiveEvent_IDで有効なイベントを取得できる()
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        MstEvent::factory()->create([
            'id' => 'event_1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-01-31 23:59:59',
        ]);

        // Exercise
        $result = $this->repository->getActiveEvent('event_1', $now);

        // Verify
        $this->assertNotNull($result);
        $this->assertEquals('event_1', $result->getId());
    }

    public function test_getActiveEvent_期間外のイベントはnullを返す()
    {
        // Setup
        $now = $this->fixTime('2025-03-15 12:00:00');
        MstEvent::factory()->create([
            'id' => 'event_1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-01-31 23:59:59',
        ]);

        // Exercise
        $result = $this->repository->getActiveEvent('event_1', $now);

        // Verify
        $this->assertNull($result);
    }

    public function test_getActiveEventsBySeriesId_シリーズIDで絞り込める()
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        MstEvent::factory()->createMany([
            [
                'id' => 'event_1',
                'mst_series_id' => 'series_a',
                'start_at' => '2025-01-01 00:00:00',
                'end_at' => '2025-01-31 23:59:59',
            ],
            [
                'id' => 'event_2',
                'mst_series_id' => 'series_a',
                'start_at' => '2025-01-01 00:00:00',
                'end_at' => '2025-01-31 23:59:59',
            ],
            [
                'id' => 'event_3',
                'mst_series_id' => 'series_b',
                'start_at' => '2025-01-01 00:00:00',
                'end_at' => '2025-01-31 23:59:59',
            ],
        ]);

        // Exercise
        $result = $this->repository->getActiveEventsBySeriesId('series_a', $now);

        // Verify
        $this->assertCount(2, $result);
        $this->assertTrue($result->has('event_1'));
        $this->assertTrue($result->has('event_2'));
    }

    public function test_getDayActivesの動作確認()
    {
        if (ini_get('apc.enable_cli') != 1) {
            self::markTestSkipped('APCu is not enabled for CLI.');
        }

        // Setup
        apcu_clear_cache();
        MstEvent::factory()->create([
            'id' => 'event_1',
            'start_at' => '2025-01-27 15:00:00',  // JST: 2025-01-28 00:00:00
            'end_at' => '2025-01-28 14:59:59',    // JST: 2025-01-28 23:59:59
        ]);

        // Exercise & Verify
        // JST: 2025-01-28 00:00:00
        $now1 = $this->fixTime('2025-01-27 15:00:00');
        $this->repository->getAllActiveEvents($now1);
        $cache1 = apcu_cache_info()['cache_list'];

        // JST: 2025-01-29 00:00:00（日跨ぎ）
        $now2 = $this->fixTime('2025-01-28 15:00:00');
        $this->repository->getAllActiveEvents($now2);
        $cache2 = apcu_cache_info()['cache_list'];

        $this->assertCount(1, $cache1);  // 1日目のキャッシュ
        $this->assertCount(2, $cache2);  // 1日目 + 2日目のキャッシュ
    }
}
```

## 使用例（Service層）

```php
class EventService
{
    public function __construct(
        private MstEventRepository $mstEventRepository,
    ) {}

    /**
     * 現在有効なイベント一覧を取得
     */
    public function getActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->mstEventRepository->getAllActiveEvents($now);
    }

    /**
     * 特定イベントの詳細を取得
     */
    public function getEventDetail(string $eventId, CarbonImmutable $now): MstEventEntity
    {
        return $this->mstEventRepository->getActiveEvent($eventId, $now, isThrowError: true);
    }

    /**
     * イベントの残り時間を計算
     */
    public function getEventRemainingTime(string $eventId, CarbonImmutable $now): int
    {
        $event = $this->mstEventRepository->getActiveEvent($eventId, $now, isThrowError: true);
        return $event->getRemainingSeconds($now);
    }

    /**
     * ジャンプ+表示用イベント一覧
     */
    public function getJumpPlusEvents(CarbonImmutable $now): Collection
    {
        return $this->mstEventRepository->getJumpPlusDisplayEvents($now);
    }
}
```

## 実装のポイント

### 1. getDayActives()の活用

イベントは期間限定が多いため、`getDayActives()`を使うことで:
- 当日有効なイベントのみキャッシュ（メモリ節約）
- 日跨ぎで自動的に新しいキャッシュ作成（手動クリア不要）

### 2. Traitの活用

`MstRepositoryTrait`を使うことで:
- `isActiveEntity()`で期間判定を統一
- `throwMstNotFoundException()`でエラーメッセージを統一

### 3. Entityにビジネスロジック

期間関連のロジックはEntityに集約:
- `isActiveAt()`, `isExpired()`, `isNotStarted()`
- `getRemainingSeconds()`

### 4. タイムゾーン考慮

MasterRepositoryは内部でUTC→JST変換を行うため、Repository層では意識不要です。

この実装パターンに従うことで、期間指定マスタを効率的に扱えます。
