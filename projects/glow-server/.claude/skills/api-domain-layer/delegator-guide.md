# Delegatorの役割と実装パターン

Delegatorはドメイン間の疎結合を実現するための重要なレイヤーです。他ドメインから呼び出される公開インターフェースとして機能します。

## 目次

- [Delegatorの役割](#delegatorの役割)
- [return型の制約](#return型の制約)
- [実装パターン](#実装パターン)
- [UsrModelからUsrModelEntityへの変換](#usrmodelからusrmodelentityへの変換)
- [よくある間違いと対処法](#よくある間違いと対処法)

## Delegatorの役割

### 1. ドメイン間の疎結合

Delegatorを使うことで、ドメイン間の直接依存を避けます。

**❌ 間違った実装（直接依存）:**

```php
// App\Domain\Shop\Services\ShopService
class ShopService
{
    public function __construct(
        private UnitService $unitService,  // ❌ 他ドメインのServiceに直接依存
    ) {
    }
}
```

**✅ 正しい実装（Delegator経由）:**

```php
// App\Domain\Shop\Services\ShopService
class ShopService
{
    public function __construct(
        private UnitDelegator $unitDelegator,  // ✅ Delegator経由で依存
    ) {
    }

    public function processShop(string $usrUserId): void
    {
        // Delegator経由でユニット情報を取得
        $units = $this->unitDelegator->getUsrUnitsByUsrUserId($usrUserId);
    }
}
```

### 2. ドメイン固有データの変換

ドメイン固有のデータ（DomainEntity、UsrModelInterface）を、外部公開可能な形（ResourceEntity、UsrModelEntity等）に変換します。

**実装例:**

```php
class UnitDelegator
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
    ) {
    }

    /**
     * ドメイン固有のUsrModelInterfaceを
     * 公開可能なUsrModelEntityに変換してreturn
     *
     * @return Collection<UsrUnitEntity>
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        // UsrModelInterfaceを取得
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);

        // UsrModelEntityに変換
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

### 3. ビジネスロジックの実行

Serviceを呼び出してビジネスロジックを実行し、結果を返します。

**実装例:**

```php
class UnitDelegator
{
    public function __construct(
        private UnitService $unitService,
    ) {
    }

    /**
     * ユニットを一括作成する
     *
     * @param Collection<string> $mstUnitIds
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        // Service経由でビジネスロジックを実行
        $this->unitService->bulkCreate($usrUserId, $mstUnitIds);
    }
}
```

## return型の制約

Delegatorのreturn型には厳密な制約があります。deptracでチェックされます。

### ✅ 使用可能な型

| 型 | 説明 | 例 |
|---|------|-----|
| CommonEntity | 全ドメイン共通Entity | `App\Domain\Common\Entities\Clock` |
| ResourceEntity | 部分共通Entity | `App\Domain\Resource\Entities\CheatCheckUnit` |
| MstModelEntity | マスタデータEntity | `App\Domain\Resource\Mst\Entities\MstUnitEntity` |
| UsrModelEntity | ユーザーデータEntity | `App\Domain\Resource\Usr\Entities\UsrUnitEntity` |
| プリミティブ型 | string, int, bool, float等 | `string`, `int` |
| Collection | 上記の型を含む | `Collection<UsrUnitEntity>` |
| void | 戻り値なし | `void` |

### ❌ 使用禁止の型

| 型 | 説明 | 理由 |
|---|------|------|
| DomainEntity | ドメイン固有Entity | ドメイン境界を超えてしまう |
| UsrModelInterface | DBモデルのInterface | ドメイン固有の実装を公開してしまう |
| UsrModel | DBモデル | ドメイン固有の実装を公開してしまう |

### 正しい実装例

**✅ ResourceEntityをreturn:**

```php
class UnitDelegator
{
    /**
     * @return Collection<CheatCheckUnit>  ← ResourceEntity（OK）
     */
    public function getCheatCheckUnits(string $usrUserId): Collection
    {
        return $this->unitService->getCheatCheckUnits($usrUserId);
    }
}
```

**✅ UsrModelEntityをreturn:**

```php
class UnitDelegator
{
    /**
     * @return Collection<UsrUnitEntity>  ← UsrModelEntity（OK）
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

**✅ プリミティブ型をreturn:**

```php
class UnitDelegator
{
    public function getGradeLevelTotalCount(string $usrUserId): int
    {
        return $this->usrUnitSummaryRepository->getGradeLevelTotalCount($usrUserId);
    }
}
```

**✅ voidをreturn:**

```php
class UnitDelegator
{
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $this->unitService->incrementBattleCount($usrUserId, $usrUnitIds);
    }
}
```

### 間違った実装例

**❌ DomainEntityをreturn:**

```php
class ShopDelegator
{
    // ❌ DomainEntityをreturnするのは禁止
    public function getPurchase(): CurrencyPurchase
    {
        return new CurrencyPurchase(...);
    }
}
```

**修正方法:**

```php
class ShopDelegator
{
    // ✅ ResourceEntityに変換してreturn
    public function getPurchase(): PurchaseEntity
    {
        $domainPurchase = new CurrencyPurchase(...);
        return PurchaseEntity::fromDomainEntity($domainPurchase);
    }

    // または

    // ✅ プリミティブ型で返す
    public function getPurchaseAmount(): int
    {
        $purchase = new CurrencyPurchase(...);
        return $purchase->getPurchaseAmount();
    }
}
```

**❌ UsrModelInterfaceをreturn:**

```php
class UnitDelegator
{
    // ❌ UsrModelInterfaceをreturnするのは禁止
    public function getUsrUnit(string $usrUserId): UsrUnitInterface
    {
        return $this->usrUnitRepository->findByUsrUserId($usrUserId);
    }
}
```

**修正方法:**

```php
class UnitDelegator
{
    // ✅ UsrModelEntityに変換してreturn
    public function getUsrUnit(string $usrUserId): UsrUnitEntity
    {
        $usrModel = $this->usrUnitRepository->findByUsrUserId($usrUserId);
        return $usrModel->toEntity();
    }
}
```

## 実装パターン

### パターン1: データ取得系

他ドメインがデータを取得するためのメソッド。

**実装例:**

`api/app/Domain/Unit/Delegators/UnitDelegator.php`:
```php
class UnitDelegator
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
    ) {
    }

    /**
     * ユーザーの全ユニットを取得
     *
     * @return Collection<UsrUnitEntity>
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);

        // UsrModelInterfaceをUsrModelEntityに変換
        return $usrModels->map(fn($model) => $model->toEntity());
    }

    /**
     * 指定されたマスタIDのユニットを取得
     *
     * @param Collection<string> $mstUnitIds
     * @return Collection<string, UsrUnitEntity> key: mst_units.id
     */
    public function getByMstUnitIds(string $usrUserId, Collection $mstUnitIds): Collection
    {
        $usrModels = $this->usrUnitRepository->getByMstUnitIds($usrUserId, $mstUnitIds);

        $entities = collect();
        foreach ($usrModels as $usrModel) {
            $entity = $usrModel->toEntity();
            $entities->put($entity->getMstUnitId(), $entity);
        }

        return $entities;
    }
}
```

### パターン2: ビジネスロジック実行系

他ドメインがビジネスロジックを実行するためのメソッド。

**実装例:**

```php
class UnitDelegator
{
    public function __construct(
        private UnitService $unitService,
    ) {
    }

    /**
     * ユニットを一括作成
     *
     * @param Collection<string> $mstUnitIds
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        $this->unitService->bulkCreate($usrUserId, $mstUnitIds);
    }

    /**
     * バトル回数をインクリメント
     *
     * @param Collection<string> $usrUnitIds
     */
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $this->unitService->incrementBattleCount($usrUserId, $usrUnitIds);
    }
}
```

### パターン3: バリデーション系

他ドメインが検証を実行するためのメソッド。

**実装例:**

```php
class UnitDelegator
{
    public function __construct(
        private UnitService $unitService,
    ) {
    }

    /**
     * ユニットを所持しているか検証
     *
     * @throws GameException
     */
    public function validateHasUsrUnitByMstUnitId(string $usrUserId, string $mstUnitId): void
    {
        $this->unitService->validateHasUsrUnitByMstUnitId($usrUserId, $mstUnitId);
    }
}
```

### パターン4: 変換系

他ドメインがデータ変換を実行するためのメソッド。

**実装例:**

```php
class UnitDelegator
{
    public function __construct(
        private UnitService $unitService,
        private UnitStatusService $unitStatusService,
    ) {
    }

    /**
     * 重複ユニットをアイテムに変換
     *
     * @param Collection<BaseReward> $rewards
     */
    public function convertDuplicatedUnitToItem(
        string $usrUserId,
        Collection $rewards,
    ): void {
        $this->unitService->convertDuplicatedUnitToItem($usrUserId, $rewards);
    }

    /**
     * ユニットデータをステータスデータに変換
     *
     * @param Collection<CheatCheckUnit> $units
     * @return Collection<UnitAudit>
     */
    public function convertUnitDataListToUnitStatusDataList(Collection $units): Collection
    {
        return $this->unitStatusService->convertUnitDataListToUnitStatusDataList($units);
    }
}
```

## UsrModelからUsrModelEntityへの変換

UsrModelInterface（ドメイン固有）をUsrModelEntity（公開可能）に変換する標準パターン。

### UsrModelInterfaceの実装

`api/app/Domain/Unit/Models/UsrUnitInterface.php`:
```php
namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;

interface UsrUnitInterface
{
    public function getId(): string;
    public function getMstUnitId(): string;
    public function getLevel(): int;

    // UsrModelEntityへの変換メソッド
    public function toEntity(): UsrUnitEntity;
}
```

### UsrModelの実装

`api/app/Domain/Unit/Models/UsrUnit.php`:
```php
namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use Illuminate\Database\Eloquent\Model;

class UsrUnit extends Model implements UsrUnitInterface
{
    public function toEntity(): UsrUnitEntity
    {
        return new UsrUnitEntity(
            id: $this->id,
            usrUserId: $this->usr_user_id,
            mstUnitId: $this->mst_unit_id,
            level: $this->level,
            grade: $this->grade,
            rank: $this->rank,
            // ...
        );
    }
}
```

### UsrModelEntityの実装

`api/app/Domain/Resource/Usr/Entities/UsrUnitEntity.php`:
```php
namespace App\Domain\Resource\Usr\Entities;

class UsrUnitEntity
{
    public function __construct(
        private string $id,
        private string $usrUserId,
        private string $mstUnitId,
        private int $level,
        private int $grade,
        private int $rank,
        // ...
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    // ... getters
}
```

### Delegatorでの使用

```php
class UnitDelegator
{
    /**
     * @return Collection<UsrUnitEntity>
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        // UsrModelInterfaceを取得
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);

        // toEntity()でUsrModelEntityに変換
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

## よくある間違いと対処法

### 間違い1: DomainEntityをそのままreturn

**❌ 間違った実装:**

```php
class ShopDelegator
{
    public function getPurchaseData(): CurrencyPurchase  // ❌ DomainEntity
    {
        return new CurrencyPurchase(...);
    }
}
```

**✅ 正しい修正:**

```php
class ShopDelegator
{
    public function getPurchaseData(): array  // ✅ 配列で返す
    {
        $purchase = new CurrencyPurchase(...);
        return $purchase->formatToResponse();
    }

    // または

    public function getPurchaseData(): PurchaseEntity  // ✅ ResourceEntityに変換
    {
        $domainPurchase = new CurrencyPurchase(...);
        return new PurchaseEntity(
            $domainPurchase->getPurchasePrice(),
            $domainPurchase->getPurchaseAmount(),
        );
    }
}
```

### 間違い2: UsrModelInterfaceをそのままreturn

**❌ 間違った実装:**

```php
class UnitDelegator
{
    public function getUsrUnit(string $usrUserId): UsrUnitInterface  // ❌ Interface
    {
        return $this->usrUnitRepository->findByUsrUserId($usrUserId);
    }
}
```

**✅ 正しい修正:**

```php
class UnitDelegator
{
    public function getUsrUnit(string $usrUserId): UsrUnitEntity  // ✅ Entity
    {
        $usrModel = $this->usrUnitRepository->findByUsrUserId($usrUserId);
        return $usrModel->toEntity();
    }
}
```

### 間違い3: 変換し忘れ

**❌ 間違った実装:**

```php
class UnitDelegator
{
    public function getUsrUnits(string $usrUserId): Collection
    {
        // ❌ UsrModelInterfaceのCollectionをそのままreturn
        return $this->usrUnitRepository->getListByUsrUserId($usrUserId);
    }
}
```

**✅ 正しい修正:**

```php
class UnitDelegator
{
    /**
     * @return Collection<UsrUnitEntity>
     */
    public function getUsrUnits(string $usrUserId): Collection
    {
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);

        // ✅ toEntity()でUsrModelEntityに変換
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

## チェックリスト

Delegator実装時に以下を確認してください:

- [ ] Delegator名は `{ドメイン名}Delegator.php` か
- [ ] DomainEntityをreturnで使用していないか
- [ ] UsrModelInterfaceをreturnで使用していないか
- [ ] UsrModelをreturnで使用していないか
- [ ] UsrModelInterfaceをUsrModelEntityに変換しているか（toEntity()）
- [ ] return型のdocコメントは正しいか（`@return Collection<UsrUnitEntity>`等）
- [ ] deptracチェックが通るか（`./tools/bin/sail-wp deptrac`）
