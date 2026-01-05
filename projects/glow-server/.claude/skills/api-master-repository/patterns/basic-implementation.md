# 基本的な実装パターン

Model/Entity/Repositoryの標準的な実装パターンを解説します。

## 実装の3要素

マスタデータの実装には、以下の3つのクラスが必要です:

1. **Model**: データベーステーブルとのマッピング
2. **Entity**: 不変オブジェクトとしてのビジネスロジック用データ
3. **Repository**: データ取得とキャッシュ管理

## ファイル配置

```
api/app/Domain/Resource/Mst/
├── Models/
│   └── MstItem.php
├── Entities/
│   └── MstItemEntity.php
└── Repositories/
    └── MstItemRepository.php
```

## Model実装

### 基本構造

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

### ポイント

1. **継承**: `MstModel`を継承（MstModelはEloquentのModelを継承）
2. **@propertyアノテーション**: カラムの型情報を明示（IDEの補完とPHPStanの解析に有効）
3. **$guarded**: 空配列で全カラムをマスアサイン可能に
4. **$casts**: カラムの型キャストを定義
5. **toEntity()**: EntityオブジェクトへのConverter

### MstModelの機能

```php
// MstModel.phpより抜粋
abstract class MstModel extends Model
{
    protected $connection = 'mst';  // 自動的にmstDBに接続
    public $timestamps = false;      // created_at/updated_atなし
}
```

## Entity実装

### 基本構造

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

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
}
```

### ポイント

1. **private プロパティ**: コンストラクタで初期化し、不変性を保証
2. **getter only**: setterは実装しない（Entityは変更不可）
3. **nullableの明示**: `?string`でnull許容を明示
4. **ビジネスロジック**: 必要に応じてビジネスロジックメソッドを追加可能

### ビジネスロジックメソッドの追加例

```php
public function isRankUpMaterial(): bool
{
    return $this->type === ItemType::RANK_UP_MATERIAL->value;
}

public function isActiveAt(CarbonImmutable $now): bool
{
    return $now->between(
        CarbonImmutable::parse($this->start_date),
        CarbonImmutable::parse($this->end_date)
    );
}
```

## Repository実装

### 基本構造

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstItemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstItem;
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
}
```

### ポイント

1. **DI**: MasterRepositoryをコンストラクタインジェクション
2. **getAll()**: 全件取得メソッドは必須
3. **型アノテーション**: PHPDocで詳細な型情報を提供
4. **キャッシュ活用**: `getAll()`を使い回す実装

## テスト実装

### Factoryの準備

```php
// database/factories/Mst/MstItemFactory.php
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
}
```

### RepositoryTest

```php
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
            ['id' => 'item_1', 'type' => 'TYPE_A'],
            ['id' => 'item_2', 'type' => 'TYPE_B'],
        ]);

        // Exercise
        $result = $this->repository->getAll();

        // Verify
        $this->assertCount(2, $result);
        $this->assertInstanceOf(MstItemEntity::class, $result->get('item_1'));
    }
}
```

## 実装チェックリスト

### Model

- [ ] MstModelを継承
- [ ] @propertyアノテーションを記述
- [ ] $castsでカラム型を定義
- [ ] toEntity()メソッドを実装
- [ ] HasFactoryトレイトを使用（テスト用）

### Entity

- [ ] privateプロパティ + コンストラクタ初期化
- [ ] getterのみ実装（setterは作らない）
- [ ] nullable型を明示（?string等）
- [ ] ビジネスロジックメソッドを必要に応じて追加

### Repository

- [ ] MasterRepositoryをDI
- [ ] getAll()メソッドを実装
- [ ] 型アノテーション（@return等）を記述
- [ ] キャッシュを使い回す実装

### Test

- [ ] Factoryを作成
- [ ] RepositoryTestを作成
- [ ] キャッシュ動作を検証

この基本パターンに従うことで、統一性のあるマスタデータアクセス層を構築できます。
