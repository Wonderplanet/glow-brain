# 複雑な検索条件の実装パターン

全件キャッシュからの複雑なフィルタリングパターンを解説します。

## 基本方針

**原則**: 可能な限り全件キャッシュ（`getAll()`）を使い、PHP層でフィルタリングします。

**理由**:
1. キャッシュヒット率の向上（1つのキャッシュで複数の検索条件に対応）
2. SQLクエリの削減
3. メモリ効率の最適化

## パターン1: 単一カラムでのフィルタ

### 実装例

```php
public function getByType(string $type, CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($type, $now) {
            return $entity->getItemType() === $type
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });
}
```

### 使用例

```php
$rankUpMaterials = $this->mstItemRepository->getByType(
    ItemType::RANK_UP_MATERIAL->value,
    $now
);
```

## パターン2: 複数カラムでのフィルタ

### 実装例

```php
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
```

### 使用例

```php
// 赤属性のランクアップ素材を取得
$redMaterials = $this->mstItemRepository->getActiveItemsByItemTypeAndEffectValue(
    ItemType::RANK_UP_MATERIAL->value,
    'red',
    $now
);
```

## パターン3: whereInパターン（MstRepositoryTrait利用）

### Traitのメソッド

```php
// MstRepositoryTrait
protected function filterWhereIn(Collection $entities, string $getterMethod, Collection $values): Collection
{
    if ($values->isEmpty()) {
        return collect();
    }

    $targetValues = $values->mapWithKeys(function ($value) {
        return [$value => true];
    });

    return $entities->filter(function ($entity) use ($getterMethod, $targetValues) {
        return $targetValues->has($entity->{$getterMethod}());
    });
}
```

### 実装例

```php
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;

class MstItemRepository
{
    use MstRepositoryTrait;

    /**
     * 指定されたアイテムタイプの有効なアイテムを取得
     * @param Collection<string> $itemTypes
     */
    public function getActiveItemsByTypes(Collection $itemTypes, CarbonImmutable $now): Collection
    {
        $allItems = $this->getAll();

        // whereIn フィルタ
        $filtered = $this->filterWhereIn($allItems, 'getItemType', $itemTypes);

        // 期間フィルタ
        return $filtered->filter(function (Entity $entity) use ($now) {
            return $this->isActiveEntity($entity, $now);
        });
    }
}
```

### 使用例

```php
$types = collect([
    ItemType::RANK_UP_MATERIAL->value,
    ItemType::RANK_UP_MEMORY_FRAGMENT->value,
]);

$items = $this->mstItemRepository->getActiveItemsByTypes($types, $now);
```

## パターン4: ネストした条件

### 実装例

```php
public function getRankUpMaterialByColor(
    string $color,
    CarbonImmutable $now,
    bool $isThrowError = false
): Entity {
    $entities = $this->getAll()
        ->filter(function (Entity $entity) use ($color, $now) {
            return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                && $entity->getEffectValue() === $color
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });

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
```

### 使用例

```php
$redMaterial = $this->mstItemRepository->getRankUpMaterialByColor('red', $now, isThrowError: true);
```

## パターン5: OR条件のフィルタ

### 実装例

```php
public function getIdleBoxes(CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($now) {
            $isIdleBox = $entity->getItemType() === ItemType::IDLE_COIN_BOX->value
                || $entity->getItemType() === ItemType::IDLE_RANK_UP_MATERIAL_BOX->value;

            return $isIdleBox
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });
}
```

### Entityにビジネスロジックを追加する方法

```php
// MstItemEntity
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

// Repository
public function getIdleBoxes(CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($now) {
            return $entity->isIdleBox()
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        });
}
```

## パターン6: レアリティでのフィルタ

### 実装例

```php
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
```

### 使用例

```php
// SSRの選択かけらボックスを取得
$ssrBox = $this->mstItemRepository->getByTypeAndRarity(
    ItemType::SELECT_FRAGMENT_BOX->value,
    'SSR',
    $now,
    isThrowError: true
);
```

## パターン7: 複数データの取得（mapWithKeys）

### 実装例

```php
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
```

**解説**:
- `mapWithKeys`でハッシュマップを作成（O(1)検索）
- `has()`で高速な存在チェック

### 使用例

```php
// 全属性のランクアップ素材を取得
$materials = $this->mstItemRepository->getRankUpMaterials($now);
```

## パターン8: ソートとリミット

### 実装例

```php
public function getTopRarityItems(string $itemType, int $limit, CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($itemType, $now) {
            return $entity->getItemType() === $itemType
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        })
        ->sortByDesc(function (Entity $entity) {
            return $entity->getSortOrder();
        })
        ->take($limit);
}
```

### 使用例

```php
// ランクアップ素材の上位5件を取得
$topMaterials = $this->mstItemRepository->getTopRarityItems(
    ItemType::RANK_UP_MATERIAL->value,
    5,
    $now
);
```

## パターン9: グループ化

### 実装例

```php
public function getActiveItemsGroupedByType(CarbonImmutable $now): Collection
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($now) {
            return $now->between($entity->getStartDate(), $entity->getEndDate());
        })
        ->groupBy(function (Entity $entity) {
            return $entity->getItemType();
        });
}
```

### 使用例

```php
$groupedItems = $this->mstItemRepository->getActiveItemsGroupedByType($now);
// [
//   'RANK_UP_MATERIAL' => Collection [...],
//   'RANK_UP_MEMORY_FRAGMENT' => Collection [...],
// ]
```

## パターン10: カスタムロジック（reduce）

### 実装例

```php
public function sumEffectValues(string $itemType, CarbonImmutable $now): int
{
    return $this->getAll()
        ->filter(function (Entity $entity) use ($itemType, $now) {
            return $entity->getItemType() === $itemType
                && $now->between($entity->getStartDate(), $entity->getEndDate());
        })
        ->reduce(function ($carry, Entity $entity) {
            return $carry + (int)$entity->getEffectValue();
        }, 0);
}
```

## パフォーマンスTips

### 1. 早期リターンでメモリ節約

```php
public function getByIds(Collection $ids): Collection
{
    if ($ids->isEmpty()) {
        return collect();  // 早期リターン
    }

    return $this->getAll()->only($ids->toArray());
}
```

### 2. ハッシュマップで高速検索

```php
// Good: O(1)の検索
$targetIds = $ids->mapWithKeys(fn($id) => [$id => true]);
$filtered = $entities->filter(fn($entity) => $targetIds->has($entity->getId()));

// Bad: O(n)の検索
$filtered = $entities->filter(fn($entity) => $ids->contains($entity->getId()));
```

### 3. チェーンの順序を最適化

```php
// Good: 少ない結果を先にフィルタ
return $this->getAll()
    ->filter(fn($entity) => $entity->getRarity() === 'SSR')  // 絞り込み
    ->filter(fn($entity) => $this->isActiveEntity($entity, $now));  // さらに絞り込み

// Bad: 重い処理を先にやる
return $this->getAll()
    ->filter(fn($entity) => $this->isActiveEntity($entity, $now))  // 全件チェック
    ->filter(fn($entity) => $entity->getRarity() === 'SSR');       // 少数を絞り込み
```

## 実装チェックリスト

- [ ] 全件キャッシュ（`getAll()`）から始める
- [ ] `filter()`で条件絞り込み
- [ ] 早期リターンでメモリ節約
- [ ] ハッシュマップで高速検索
- [ ] エラーハンドリング（`isThrowError`パラメータ）
- [ ] 型アノテーション（`@return`等）
- [ ] ビジネスロジックはEntityに実装
- [ ] チェーンの順序を最適化

この実装パターンに従うことで、柔軟かつパフォーマンスの高い検索機能を実装できます。
