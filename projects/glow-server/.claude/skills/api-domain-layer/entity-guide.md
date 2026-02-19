# Entity種別と使用ルール

Entityは3つの種別があり、それぞれ使用範囲と制約が異なります。正しく使い分けることでドメイン境界を明確にし、保守性を向上させます。

## 目次

- [Entityの3つの種別](#entityの3つの種別)
- [DomainEntity（ドメイン固有Entity）](#domainentityドメイン固有entity)
- [ResourceEntity（部分共有Entity）](#resourceentity部分共有entity)
- [CommonEntity（全体共有Entity）](#commonentity全体共有entity)
- [使い分けの判断フローチャート](#使い分けの判断フローチャート)
- [よくある間違いと対処法](#よくある間違いと対処法)

## Entityの3つの種別

| 種別 | パス | 使用範囲 | Delegator return | 用途 |
|------|------|---------|------------------|------|
| DomainEntity | `App\Domain\[ドメイン名]\Entities\*` | ドメイン内のみ | ❌ 使用禁止 | ドメイン固有のビジネスロジック |
| ResourceEntity | `App\Domain\Resource\Entities\*` | 複数ドメイン | ✅ 使用可能 | 複数ドメインで共有される共通Entity |
| CommonEntity | `App\Domain\Common\Entities\*` | 全ドメイン | ✅ 使用可能 | 全ドメインで使用される汎用Entity |

## DomainEntity（ドメイン固有Entity）

### 概要

ドメイン固有のビジネスロジックを持つEntity。ドメイン外へは渡さない。

### パス

```
api/app/Domain/[ドメイン名]/Entities/*.php
```

例:
- `api/app/Domain/Shop/Entities/CurrencyPurchase.php`
- `api/app/Domain/Gacha/Entities/GachaResultData.php`
- `api/app/Domain/Mission/Entities/MissionProgress.php`

### 特徴

- ドメイン固有のビジネスロジックを持つ
- UsrModelInterfaceへの依存は可能
- ResourceEntity、CommonEntity、MstModelEntity、UsrModelEntityへの依存は可能

### 制約

- ❌ **Delegatorのreturnで使用禁止**（ドメイン外へ渡さない）
- ✅ UseCase、Service内での使用はOK
- ✅ 同じドメイン内での使用はOK

### 実装例

`api/app/Domain/Shop/Entities/CurrencyPurchase.php`:
```php
namespace App\Domain\Shop\Entities;

use App\Domain\Common\Utils\StringUtil;

class CurrencyPurchase
{
    public function __construct(
        private string $purchasePrice,
        private int $purchaseAmount,
        private string $currencyCode,
        private string $purchaseAt,
    ) {
    }

    public function getPurchasePrice(): string
    {
        return $this->purchasePrice;
    }

    public function formatToResponse(): array
    {
        return [
            'purchasePrice' => $this->purchasePrice,
            'purchaseAmount' => $this->purchaseAmount,
            'currencyCode' => $this->currencyCode,
            'purchaseAt' => StringUtil::convertToISO8601($this->purchaseAt),
        ];
    }
}
```

### 使用場所

**✅ 正しい使用例:**

```php
// UseCase内での使用
class ShopPurchaseUseCase
{
    public function __invoke(string $usrUserId): void
    {
        // DomainEntityを作成して使用（OK）
        $purchase = new CurrencyPurchase(...);
        $this->shopService->processPurchase($purchase);
    }
}

// Service内での使用
class ShopService
{
    public function processPurchase(CurrencyPurchase $purchase): void
    {
        // DomainEntityを受け取って処理（OK）
    }
}
```

**❌ 間違った使用例:**

```php
// Delegatorのreturnで使用（NG）
class ShopDelegator
{
    // ❌ DomainEntityをreturnするのは禁止
    public function getPurchase(): CurrencyPurchase
    {
        return new CurrencyPurchase(...);
    }
}
```

**✅ 正しい修正例:**

```php
// ResourceEntityまたはCommonEntityに変換してreturn
class ShopDelegator
{
    // ✅ ResourceEntityをreturnする
    public function getPurchase(): PurchaseEntity
    {
        $domainPurchase = new CurrencyPurchase(...);
        return new PurchaseEntity(...); // ResourceEntityに変換
    }
}
```

## ResourceEntity（部分共有Entity）

### 概要

複数ドメインで共有可能なEntity。Delegatorのreturnで使用可能。

### パス

```
api/app/Domain/Resource/Entities/*.php
```

例:
- `api/app/Domain/Resource/Entities/CheatCheckUnit.php`
- `api/app/Domain/Resource/Entities/UnitAudit.php`
- `api/app/Domain/Resource/Entities/Rewards/BaseReward.php`

### 特徴

- 複数ドメインで共有される共通Entity
- マスタデータやユーザーデータの共通Entity
- ドメイン間で受け渡し可能

### 制約

- ✅ **Delegatorのreturnで使用可能**
- ✅ 全ドメインから参照可能
- ✅ CommonEntity、MstModelEntity、UsrModelEntityへの依存はOK

### 実装例

`api/app/Domain/Resource/Entities/CheatCheckUnit.php`:
```php
namespace App\Domain\Resource\Entities;

class CheatCheckUnit
{
    public function __construct(
        private string $usrUnitId,
        private int $level,
        private int $grade,
        private int $rank,
    ) {
    }

    public function getUsrUnitId(): string
    {
        return $this->usrUnitId;
    }

    // ... getters
}
```

### 使用場所

**✅ 正しい使用例:**

```php
// Delegatorのreturnで使用（OK）
class UnitDelegator
{
    /**
     * @return Collection<CheatCheckUnit>
     */
    public function getCheatCheckUnits(string $usrUserId): Collection
    {
        // ResourceEntityをreturn（OK）
        return $this->unitService->getCheatCheckUnits($usrUserId);
    }
}

// 他ドメインから使用（OK）
class CheatService
{
    public function checkUnits(string $usrUserId): void
    {
        // UnitDelegator経由でResourceEntityを取得（OK）
        $units = $this->unitDelegator->getCheatCheckUnits($usrUserId);
        foreach ($units as $unit) {
            // チェック処理
        }
    }
}
```

## CommonEntity（全体共有Entity）

### 概要

全ドメインで利用可能な汎用Entity。ドメインロジックに依存しない純粋な値オブジェクト。

### パス

```
api/app/Domain/Common/Entities/*.php
```

例:
- `api/app/Domain/Common/Entities/Clock.php`
- `api/app/Domain/Common/Entities/DateTimeRange.php`
- `api/app/Domain/Common/Entities/CurrentUser.php`

### 特徴

- ドメインロジックに依存しない純粋な値オブジェクト
- 全ドメインで使用可能
- 他のCommonEntity同士の相互参照は可能

### 制約

- ✅ **Delegatorのreturnで使用可能**
- ✅ 全ドメインから参照可能
- ✅ CommonEntity同士の依存はOK

### 実装例

`api/app/Domain/Common/Entities/DateTimeRange.php`:
```php
namespace App\Domain\Common\Entities;

use Carbon\CarbonImmutable;

class DateTimeRange
{
    public function __construct(
        private CarbonImmutable $start,
        private CarbonImmutable $end,
    ) {
    }

    public function contains(CarbonImmutable $dateTime): bool
    {
        return $dateTime->gte($this->start) && $dateTime->lte($this->end);
    }

    public function getStart(): CarbonImmutable
    {
        return $this->start;
    }

    public function getEnd(): CarbonImmutable
    {
        return $this->end;
    }
}
```

### 使用場所

**✅ 正しい使用例:**

```php
// Delegatorのreturnで使用（OK）
class CampaignDelegator
{
    public function getCampaignPeriod(): DateTimeRange
    {
        // CommonEntityをreturn（OK）
        return new DateTimeRange($start, $end);
    }
}

// 全ドメインから使用（OK）
class ShopService
{
    public function isInCampaignPeriod(CarbonImmutable $now): bool
    {
        // CommonEntityを使用（OK）
        $period = $this->campaignDelegator->getCampaignPeriod();
        return $period->contains($now);
    }
}
```

## 使い分けの判断フローチャート

新規Entityを作成する際、どの種別にすべきか判断するフローチャート:

```
質問1: ドメインロジックに依存しない純粋な汎用クラスか？
  ├─ YES → CommonEntity
  └─ NO  → 質問2へ

質問2: 複数ドメインで共有される想定か？
  ├─ YES → ResourceEntity
  └─ NO  → DomainEntity

質問3: Delegatorのreturnで使用する予定か？
  ├─ YES → ResourceEntity または CommonEntity に変更を検討
  └─ NO  → DomainEntity
```

### 具体例での判断

| Entity | 判断 | 理由 |
|--------|------|------|
| `Shop\Entities\CurrencyPurchase` | DomainEntity | Shop固有のビジネスロジック、他ドメインで使用しない |
| `Resource\Entities\CheatCheckUnit` | ResourceEntity | 複数ドメインで共有、Delegatorのreturnで使用 |
| `Common\Entities\DateTimeRange` | CommonEntity | ドメインロジックに依存しない純粋な日時範囲 |
| `Unit\Entities\UnitLevelUpResult` | DomainEntity | Unit固有の結果、他ドメインで使用しない |
| `Resource\Usr\Entities\UsrUnitEntity` | ResourceEntity | UsrModelからtoEntity()で変換、Delegatorのreturnで使用 |

## よくある間違いと対処法

### 間違い1: DomainEntityをDelegatorでreturn

**❌ 間違った実装:**

```php
class UnitDelegator
{
    // ❌ DomainEntityをreturnしている
    public function getUnitResult(): UnitLevelUpResult
    {
        return new UnitLevelUpResult(...);
    }
}
```

**✅ 正しい修正:**

方法1: ResourceEntityに変換
```php
class UnitDelegator
{
    // ✅ ResourceEntityに変換してreturn
    public function getUnitResult(): UnitResultEntity
    {
        $domainResult = new UnitLevelUpResult(...);
        return UnitResultEntity::fromDomainEntity($domainResult);
    }
}
```

方法2: プリミティブ型やCollectionで返す
```php
class UnitDelegator
{
    // ✅ プリミティブ型で返す
    public function getUnitLevel(): int
    {
        $result = new UnitLevelUpResult(...);
        return $result->getNewLevel();
    }
}
```

### 間違い2: 全ドメインで使うEntityをDomainEntityとして作成

**❌ 間違った実装:**

```php
// api/app/Domain/Shop/Entities/Price.php
namespace App\Domain\Shop\Entities;

class Price  // ❌ 他のドメインでも使いたいのにDomainEntity
{
    public function __construct(
        private int $amount,
        private string $currencyCode,
    ) {
    }
}
```

**✅ 正しい修正:**

CommonEntityとして作成
```php
// api/app/Domain/Common/Entities/Price.php
namespace App\Domain\Common\Entities;

class Price  // ✅ CommonEntityとして作成
{
    public function __construct(
        private int $amount,
        private string $currencyCode,
    ) {
    }
}
```

### 間違い3: ResourceEntityに不要な依存を追加

**❌ 間違った実装:**

```php
namespace App\Domain\Resource\Entities;

use App\Domain\Unit\Services\UnitService;  // ❌ Serviceへの依存

class UnitAudit
{
    public function __construct(
        private UnitService $unitService,  // ❌ ServiceをDI
    ) {
    }
}
```

**✅ 正しい修正:**

純粋なデータ構造として作成
```php
namespace App\Domain\Resource\Entities;

class UnitAudit
{
    public function __construct(
        private string $usrUnitId,
        private int $level,
        // ... データのみ
    ) {
    }

    // ビジネスロジックは持たない
}
```

## チェックリスト

新規Entity作成時に以下を確認してください:

- [ ] Entity種別（DomainEntity、ResourceEntity、CommonEntity）は正しいか
- [ ] DomainEntityをDelegatorのreturnで使用していないか
- [ ] 複数ドメインで共有する想定ならResourceEntityまたはCommonEntityか
- [ ] ドメインロジックに依存しない汎用クラスならCommonEntityか
- [ ] Entityは純粋なデータ構造か（ServiceやRepositoryへの依存はないか）
- [ ] namespaceが正しいか（`App\Domain\[ドメイン名]\Entities\*`等）
