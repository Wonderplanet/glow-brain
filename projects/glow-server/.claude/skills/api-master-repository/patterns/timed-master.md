# 期間指定マスタの実装パターン

`start_at`/`end_at`や`start_date`/`end_date`カラムを持つマスタデータの実装パターンを解説します。

## 期間指定マスタの種類

### タイプ1: start_at/end_at（datetime型）

イベントやキャンペーンなど、**秒単位で期間管理が必要なマスタ**に使用します。

**例**: MstEvent, MstPvp, OprGacha

```sql
CREATE TABLE mst_events (
    id VARCHAR(255) PRIMARY KEY,
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    ...
);
```

### タイプ2: start_date/end_date（datetime型、日次管理）

アイテムなど、**日単位で期間管理するマスタ**に使用します。

**例**: MstItem, MstShopItem

```sql
CREATE TABLE mst_items (
    id VARCHAR(255) PRIMARY KEY,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    ...
);
```

## Model実装

### start_at/end_atの場合

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEventEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_series_id
 * @property string $start_at
 * @property string $end_at
 * @property string $asset_key
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
        'start_at' => 'string',
        'end_at' => 'string',
        'asset_key' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_series_id,
            $this->start_at,
            $this->end_at,
            $this->asset_key,
        );
    }
}
```

## Entity実装

### 期間判定メソッドを含む実装

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
        private string $start_at,
        private string $end_at,
        private string $asset_key,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstSeriesId(): string
    {
        return $this->mst_series_id;
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

    /**
     * 指定された日時がイベント期間内かどうか
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
}
```

## Repository実装

### パターン1: getDayActives()を使う（推奨）

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

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
     * 現在有効なイベントを全て取得
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
     * 特定IDの有効なイベントを取得
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
     * 複数IDの有効なイベントを取得
     * @param Collection<string> $ids
     * @return Collection<string, Entity>
     */
    public function getActiveEvents(Collection $ids, CarbonImmutable $now): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->getAllActiveEvents($now)->only($ids->toArray());
    }
}
```

**ポイント**:
- `MstRepositoryTrait`をuse
- `getDayActives()`で当日有効なデータをキャッシュ
- `isActiveEntity()`で厳密な期間判定

### パターン2: 全件キャッシュを使う

```php
class MstItemRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
        // start_date/end_dateの場合はgetter名をカスタマイズ
        $this->setStartGetterMethod('getStartDate');
        $this->setEndGetterMethod('getEndDate');
    }

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItem::class);
    }

    public function getActiveItemById(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($entity !== null && !$this->isActiveEntity($entity, $now)) {
            $entity = null;
        }

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_items record is not found. (id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<string, Entity>
     */
    public function getActiveItemsById(Collection $ids, CarbonImmutable $now, bool $isThrowError = false): Collection
    {
        $targetIds = $ids->unique();

        $entities = $this->getAll()->only($targetIds->toArray())
            ->filter(function (Entity $entity) use ($now) {
                return $this->isActiveEntity($entity, $now);
            });

        if ($isThrowError && $targetIds->count() !== $entities->count()) {
            $missingIds = $targetIds->diff($entities->keys());
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items records are not found. (ids: %s)',
                    implode(', ', $missingIds->toArray())
                ),
            );
        }

        return $entities;
    }

    /**
     * 特定タイプの有効なアイテムを取得
     */
    public function getActiveItemsByItemType(string $itemType, CarbonImmutable $now): Collection
    {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $now) {
                return $entity->getItemType() === $itemType
                    && $this->isActiveEntity($entity, $now);
            });
    }
}
```

**ポイント**:
- `setStartGetterMethod()`/`setEndGetterMethod()`でカスタマイズ
- 全件キャッシュから期間フィルタリング

## getDayActives()と全件キャッシュの使い分け

| 条件 | 推奨方式 | 理由 |
|------|---------|------|
| 有効データが全体の10%未満 | getDayActives() | メモリ節約 |
| データ量が1000件以上 | getDayActives() | メモリ節約 |
| 有効期間が短い（数日～数週間） | getDayActives() | キャッシュ効率 |
| 全データが常時有効 | 全件キャッシュ | シンプル |
| データ量が少ない（100件未満） | 全件キャッシュ | シンプル |

**具体例**:
- **MstEvent**: イベントは期間限定が多い → `getDayActives()`
- **MstItem**: ほとんどのアイテムが常時有効 → 全件キャッシュ
- **OprGacha**: ガチャは期間限定 → `getDayActives()`

## テスト実装

### Factoryでの期間設定

```php
class MstEventFactory extends Factory
{
    protected $model = MstEvent::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'mst_series_id' => 'series_1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-12-31 23:59:59',
            'asset_key' => 'event_asset',
        ];
    }

    // 期間をカスタマイズするステート
    public function between(string $startAt, string $endAt): self
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);
    }
}
```

### 期間判定のテスト

```php
public function test_getActiveEvent_期間内のイベントを取得できる()
{
    // Setup
    $now = $this->fixTime('2025-01-15 12:00:00');
    MstEvent::factory()->createMany([
        ['id' => 'event_1', 'start_at' => '2025-01-01 00:00:00', 'end_at' => '2025-01-31 23:59:59'],
        ['id' => 'event_2', 'start_at' => '2025-02-01 00:00:00', 'end_at' => '2025-02-28 23:59:59'],
    ]);

    // Exercise
    $result = $this->repository->getAllActiveEvents($now);

    // Verify
    $this->assertCount(1, $result);
    $this->assertEquals('event_1', $result->first()->getId());
}

public function test_getDayActives_日跨ぎでキャッシュが更新される()
{
    if (ini_get('apc.enable_cli') != 1) {
        self::markTestSkipped('APCu is not enabled for CLI.');
    }

    // Setup
    apcu_clear_cache();
    MstEvent::factory()->create([
        'id' => 'event_1',
        'start_at' => '2025-01-27 15:00:00',  // JST 2025-01-28 00:00:00
        'end_at' => '2025-01-28 14:59:59',    // JST 2025-01-28 23:59:59
    ]);

    // Exercise & Verify
    $now1 = $this->fixTime('2025-01-27 15:00:00');  // JST 2025-01-28 00:00:00
    $this->repository->getAllActiveEvents($now1);
    $cache1 = apcu_cache_info()['cache_list'];

    $now2 = $this->fixTime('2025-01-28 15:00:00');  // JST 2025-01-29 00:00:00
    $this->repository->getAllActiveEvents($now2);
    $cache2 = apcu_cache_info()['cache_list'];

    $this->assertCount(1, $cache1);  // 1日目のキャッシュ
    $this->assertCount(2, $cache2);  // 1日目 + 2日目のキャッシュ
}
```

## 実装チェックリスト

- [ ] Modelにstart_at/end_at（またはstart_date/end_date）カラムが存在
- [ ] Entityにgetterメソッドが実装されている
- [ ] RepositoryでMstRepositoryTraitをuse
- [ ] 期間指定がstart_date/end_dateの場合、setStartGetterMethod()等を呼び出し
- [ ] getDayActives()または全件キャッシュのどちらかを選択
- [ ] isActiveEntity()で期間判定を実装
- [ ] Factoryで期間カスタマイズのステートを実装
- [ ] 期間判定のテストを作成

この実装パターンに従うことで、期間指定マスタを効率的に扱えます。
