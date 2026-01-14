# MstItem完全実装例

MstItemの実装を通して、マスタデータの標準的な実装パターンを解説します。

## テーブル定義

```sql
CREATE TABLE mst_items (
    id VARCHAR(255) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    group_type VARCHAR(255) NOT NULL,
    rarity VARCHAR(255) NOT NULL,
    asset_key VARCHAR(255) NOT NULL,
    effect_value VARCHAR(255),
    sort_order INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    -- 他のカラム省略
);
```

## Model実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Models/MstItem.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstItemEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $type
 * @property string $group_type
 * @property string $rarity
 * @property string $asset_key
 * @property string $effect_value
 * @property int $sort_order
 * @property string $start_date
 * @property string $end_date
 */
class MstItem extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'type' => 'string',
        'group_type' => 'string',
        'rarity' => 'string',
        'asset_key' => 'string',
        'effect_value' => 'string',
        'sort_order' => 'integer',
        'start_date' => 'string',
        'end_date' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->type,
            $this->group_type,
            $this->rarity,
            $this->asset_key,
            $this->effect_value,
            $this->sort_order,
            $this->start_date,
            $this->end_date,
        );
    }
}
```

**ポイント**:
- MstModelを継承（自動的に`$connection = 'mst'`が設定される）
- `@property`でカラムの型を明示
- `toEntity()`でEntity変換

## Entity実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Entities/MstItemEntity.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Item\Enums\ItemType;

class MstItemEntity
{
    public function __construct(
        private string $id,
        private string $type,
        private string $group_type,
        private string $rarity,
        private string $asset_key,
        private ?string $effect_value,
        private int $sort_order,
        private string $start_date,
        private string $end_date,
    ) {}

    // === Getters ===

    public function getId(): string
    {
        return $this->id;
    }

    public function getItemType(): string
    {
        return $this->type;
    }

    public function getGroupType(): string
    {
        return $this->group_type;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getEffectValue(): ?string
    {
        return $this->effect_value;
    }

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }

    // === ビジネスロジック ===

    public function isIdleBox(): bool
    {
        return $this->isIdleCoinBox() || $this->isIdleRankUpMaterialBox();
    }

    public function isIdleCoinBox(): bool
    {
        return $this->type === ItemType::IDLE_COIN_BOX->value;
    }

    public function isIdleRankUpMaterialBox(): bool
    {
        return $this->type === ItemType::IDLE_RANK_UP_MATERIAL_BOX->value;
    }

    public function isRankUpMaterial(): bool
    {
        return $this->type === ItemType::RANK_UP_MATERIAL->value;
    }

    public function getIdleBoxMinutes(): int
    {
        if ($this->isIdleBox() && !is_null($this->effect_value)) {
            return (int) $this->effect_value;
        }
        return 0;
    }
}
```

**ポイント**:
- `private`プロパティで不変性を保証
- getterのみ実装（setterは作らない）
- ビジネスロジックメソッドを追加してドメイン知識を集約

## Repository実装

**ファイルパス**: `api/app/Domain/Resource/Mst/Repositories/MstItemRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Entities\MstItemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Unit\Enums\UnitColorType;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstItemRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {}

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItem::class);
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<string, Entity> key: id
     */
    public function getByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->getAll()->only($ids->toArray());
    }

    /**
     * ID指定で有効なアイテムを取得
     */
    public function getActiveItemById(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($entity !== null && !$now->between($entity->getStartDate(), $entity->getEndDate())) {
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
     * 複数ID指定で有効なアイテムを取得
     * @param Collection<string> $ids
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getActiveItemsById(Collection $ids, CarbonImmutable $now, bool $isThrowError = false): Collection
    {
        $targetIds = $ids->unique();

        $entities = $this->getAll()->only($targetIds->toArray())
            ->filter(function (Entity $entity) use ($now) {
                return $now->between($entity->getStartDate(), $entity->getEndDate());
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
     * アイテムタイプに該当するアイテムを取得する
     */
    public function getActiveItemsByItemType(string $itemType, CarbonImmutable $now): Collection
    {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $now) {
                return $entity->getItemType() === $itemType
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * アイテムタイプと効果値に該当するアイテムを取得する
     */
    public function getActiveItemsByItemTypeAndEffectValue(
        string $itemType,
        string $effectValue,
        CarbonImmutable $now
    ): Collection {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $effectValue, $now) {
                return $entity->getItemType() === $itemType
                    && $entity->getEffectValue() === $effectValue
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * ランクアップ用アイテムを属性指定で取得する
     */
    public function getRankUpMaterialByColor(string $color, CarbonImmutable $now, bool $isThrowError = false): Entity
    {
        $entities = $this->getActiveItemsByItemTypeAndEffectValue(ItemType::RANK_UP_MATERIAL->value, $color, $now);
        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, effectValue: %s)',
                    ItemType::RANK_UP_MATERIAL->value,
                    $color
                ),
            );
        }
        return $entities->first();
    }

    /**
     * ランクアップ用アイテムの全属性を取得する
     */
    public function getRankUpMaterials(CarbonImmutable $now): Collection
    {
        $unitColorTypes = collect(UnitColorType::cases())->mapWithKeys(
            fn($case) => [$case->value => true]
        );

        return $this->getAll()
            ->filter(function (Entity $entity) use ($now, $unitColorTypes) {
                return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                    && $unitColorTypes->has($entity->getEffectValue())
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * 指定したアイテムタイプとレアリティに該当するアイテムを取得する
     */
    public function getByTypeAndRarity(
        string $itemType,
        string $rarity,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $rarity, $now) {
                return $entity->getItemType() === $itemType
                    && $entity->getRarity() === $rarity
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, rarity: %s)',
                    $itemType,
                    $rarity
                ),
            );
        }

        return $entities->first();
    }

    /**
     * ランクアップ用メモリーフラグメントを取得
     */
    public function getRankUpMemoryFragments(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getByColumn(
            MstItem::class,
            'type',
            ItemType::RANK_UP_MEMORY_FRAGMENT->value,
        )->filter(function (Entity $entity) use ($now) {
            return $now->between($entity->getStartDate(), $entity->getEndDate());
        });
    }

    /**
     * ランクアップ用メモリーフラグメントをレアリティ指定で取得
     */
    public function getRankUpMemoryFragmentByRarity(
        string $rarity,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($rarity, $now) {
                return $entity->getItemType() === ItemType::RANK_UP_MEMORY_FRAGMENT->value
                    && $entity->getRarity() === $rarity
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, rarity: %s)',
                    ItemType::RANK_UP_MEMORY_FRAGMENT->value,
                    $rarity,
                ),
            );
        }

        return $entities->first();
    }

    /**
     * ランクアップ用キャラ個別メモリーアイテムを取得する
     */
    public function getUnitMemoryByMstUnitId(
        string $mstUnitId,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($mstUnitId, $now) {
                return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                    && $entity->getEffectValue() === $mstUnitId
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, effectValue: %s)',
                    ItemType::RANK_UP_MATERIAL->value,
                    $mstUnitId
                ),
            );
        }

        return $entities->first();
    }
}
```

**ポイント**:
- 全件キャッシュ（`getAll()`）を使い回す
- 複雑なフィルタリングはPHP層で実装
- `isThrowError`パラメータでエラーハンドリング

## Factory実装

**ファイルパス**: `database/factories/Mst/MstItemFactory.php`

```php
<?php

namespace Database\Factories\Mst;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MstItemFactory extends Factory
{
    protected $model = MstItem::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'group_type' => 'group_1',
            'rarity' => 'SSR',
            'asset_key' => 'item_asset',
            'effect_value' => 'red',
            'sort_order' => 1,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2099-12-31 23:59:59',
        ];
    }

    public function type(string $type): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    public function rarity(string $rarity): self
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => $rarity,
        ]);
    }

    public function effectValue(?string $effectValue): self
    {
        return $this->state(fn (array $attributes) => [
            'effect_value' => $effectValue,
        ]);
    }

    public function between(string $startDate, string $endDate): self
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
```

## テスト実装

**ファイルパス**: `api/tests/Feature/Domain/Item/Repositories/MstItemRepositoryTest.php`

```php
<?php

namespace Tests\Feature\Domain\Item\Repositories;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstItemRepositoryTest extends TestCase
{
    private MstItemRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MstItemRepository::class);
    }

    public function test_getAll_全件取得できる()
    {
        // Setup
        MstItem::factory()->createMany([
            ['id' => 'item_1'],
            ['id' => 'item_2'],
        ]);

        // Exercise
        $result = $this->repository->getAll();

        // Verify
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('item_1', $result->toArray());
        $this->assertArrayHasKey('item_2', $result->toArray());
    }

    public function test_getActiveItemById_有効なアイテムを取得できる()
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        MstItem::factory()->createMany([
            ['id' => 'item_1', 'start_date' => '2025-01-01 00:00:00', 'end_date' => '2025-01-31 23:59:59'],
            ['id' => 'item_2', 'start_date' => '2025-02-01 00:00:00', 'end_date' => '2025-02-28 23:59:59'],
        ]);

        // Exercise
        $result = $this->repository->getActiveItemById('item_1', $now);

        // Verify
        $this->assertNotNull($result);
        $this->assertEquals('item_1', $result->getId());
    }

    public function test_getActiveItemById_期間外のアイテムはnullを返す()
    {
        // Setup
        $now = $this->fixTime('2025-03-15 12:00:00');
        MstItem::factory()->create([
            'id' => 'item_1',
            'start_date' => '2025-01-01 00:00:00',
            'end_date' => '2025-01-31 23:59:59',
        ]);

        // Exercise
        $result = $this->repository->getActiveItemById('item_1', $now);

        // Verify
        $this->assertNull($result);
    }

    public function test_getRankUpMaterialByColor_属性指定でランクアップ素材を取得できる()
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        MstItem::factory()->createMany([
            [
                'id' => 'red_material',
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => 'red',
                'start_date' => '2025-01-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
            ],
            [
                'id' => 'blue_material',
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => 'blue',
                'start_date' => '2025-01-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
            ],
        ]);

        // Exercise
        $result = $this->repository->getRankUpMaterialByColor('red', $now);

        // Verify
        $this->assertEquals('red_material', $result->getId());
        $this->assertEquals('red', $result->getEffectValue());
    }
}
```

## 使用例（Service層）

```php
class ItemService
{
    public function __construct(
        private MstItemRepository $mstItemRepository,
    ) {}

    public function getActiveItems(Collection $itemIds, CarbonImmutable $now): Collection
    {
        // 有効なアイテムのみ取得（見つからない場合は例外）
        return $this->mstItemRepository->getActiveItemsById($itemIds, $now, isThrowError: true);
    }

    public function getRankUpMaterialByColor(string $color, CarbonImmutable $now): MstItemEntity
    {
        return $this->mstItemRepository->getRankUpMaterialByColor($color, $now, isThrowError: true);
    }
}
```

この実装パターンに従うことで、堅牢で保守性の高いマスタデータアクセス層を構築できます。
