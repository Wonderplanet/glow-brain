# フォルダ構造と各サブフォルダの役割

通常ドメインの標準的なフォルダ構成と、各サブフォルダの役割を説明します。

## 目次

- [標準的なフォルダ構成](#標準的なフォルダ構成)
- [各サブフォルダの役割](#各サブフォルダの役割)
- [命名規則](#命名規則)
- [ファイル配置の判断基準](#ファイル配置の判断基準)

## 標準的なフォルダ構成

通常ドメイン（例: Unit）の標準的な構成:

```
api/app/Domain/Unit/
├── Constants/          # 定数クラス
├── Delegators/         # 他ドメインから呼び出される公開インターフェース
├── Enums/              # 列挙型
├── Entities/           # ドメイン固有のEntity（DomainEntity）
├── Models/             # DBモデル（UsrModel）とInterface
│   ├── Eloquent/       # Eloquent実装の補助クラス（任意）
│   ├── UsrUnit.php
│   └── UsrUnitInterface.php
├── Repositories/       # DBアクセス層
├── UseCases/           # ビジネスロジックのエントリーポイント
├── Services/           # ドメインサービス（複雑なビジネスロジック）
└── Manager/            # Manager（特定のドメインで使用される管理クラス）（任意）
```

**注意:** すべてのサブフォルダが必須ではありません。ドメインの性質に応じて必要なフォルダのみ作成します。

## 各サブフォルダの役割

### 1. Constants/

**役割:** ドメイン固有の定数クラス

**配置するファイル:**
- ドメイン内でのみ使用される定数
- マジックナンバーの定義
- デフォルト値

**実装例:**

`api/app/Domain/Unit/Constants/UnitConstant.php`:
```php
namespace App\Domain\Unit\Constants;

class UnitConstant
{
    // ユニットの初期グレードレベル
    public const FIRST_UNIT_GRADE_LEVEL = 1;

    // ランクアップに必要な最小レベル
    public const RANK_UP_MIN_LEVEL = 10;
}
```

**使い分け:**
- ドメイン固有の定数 → `Constants/`
- 全ドメイン共通の定数 → `App\Domain\Common\Constants\`
- マスタデータ関連の定数 → `App\Domain\Resource\Constants\`

### 2. Delegators/

**役割:** 他ドメインから呼び出される公開インターフェース。ドメイン間の疎結合を実現。

**配置するファイル:**
- 他ドメインから呼び出される公開メソッド
- ドメイン固有のデータを外部公開可能な形に変換

**実装例:**

`api/app/Domain/Unit/Delegators/UnitDelegator.php`:
```php
namespace App\Domain\Unit\Delegators;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;

class UnitDelegator
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private UnitService $unitService,
    ) {
    }

    /**
     * @return Collection<UsrUnitEntity>  ← UsrModelEntityを返す（OK）
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

**重要な制約:**
- ❌ DomainEntityをreturnで使用禁止
- ❌ UsrModelInterfaceをreturnで使用禁止
- ✅ ResourceEntity、CommonEntity、MstModelEntity、UsrModelEntityはreturn可能

詳細は **[delegator-guide.md](delegator-guide.md)** を参照。

### 3. Enums/

**役割:** 列挙型（Enum）の定義

**配置するファイル:**
- 状態や種別を表す列挙型
- PHP 8.1以降のEnum

**実装例:**

`api/app/Domain/Shop/Enums/ShopItemType.php`:
```php
namespace App\Domain\Shop\Enums;

enum ShopItemType: int
{
    case NORMAL = 1;
    case LIMITED = 2;
    case SPECIAL = 3;
}
```

**使い分け:**
- ドメイン固有の列挙型 → `Enums/`
- 全ドメイン共通の列挙型 → `App\Domain\Common\Enums\`
- Resource関連の列挙型 → `App\Domain\Resource\Enums\`

### 4. Entities/

**役割:** ドメイン固有のEntity（DomainEntity）。ビジネスロジックを持つ値オブジェクト。

**配置するファイル:**
- ドメイン固有のビジネスロジックを持つEntity
- 複数の値をまとめた値オブジェクト
- レスポンス用のデータ構造

**実装例:**

`api/app/Domain/Shop/Entities/CurrencyPurchase.php`:
```php
namespace App\Domain\Shop\Entities;

class CurrencyPurchase
{
    public function __construct(
        private string $purchasePrice,
        private int $purchaseAmount,
        private string $currencyCode,
        private string $purchaseAt,
    ) {
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

**重要な制約:**
- ❌ **Delegatorのreturnで使用禁止**（ドメイン外へ渡さない）
- ✅ UseCase、Service内での使用はOK

詳細は **[entity-guide.md](entity-guide.md)** を参照。

### 5. Models/

**役割:** DBモデル（Eloquent Model）とそのInterface

**配置するファイル:**
- UsrModel（ユーザーデータのEloquent Model）
- UsrModelInterface（Modelのインターフェース）
- Eloquent実装の補助クラス（`Eloquent/`サブフォルダ）

**実装例:**

`api/app/Domain/Unit/Models/UsrUnitInterface.php`:
```php
namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;

interface UsrUnitInterface
{
    public function getId(): string;
    public function getMstUnitId(): string;
    public function getLevel(): int;

    // UsrModelEntityへの変換
    public function toEntity(): UsrUnitEntity;

    // 状態変更メソッド
    public function incrementBattleCount(): void;
    public function markAsCollected(): void;
}
```

`api/app/Domain/Unit/Models/UsrUnit.php`:
```php
namespace App\Domain\Unit\Models;

use Illuminate\Database\Eloquent\Model;

class UsrUnit extends Model implements UsrUnitInterface
{
    protected $connection = 'usr';
    protected $table = 'usr_units';

    public function toEntity(): UsrUnitEntity
    {
        return new UsrUnitEntity(
            $this->id,
            $this->mst_unit_id,
            $this->level,
            // ...
        );
    }
}
```

**重要な制約:**
- ❌ **UsrModelInterfaceはDelegatorのreturnで使用禁止**
- ✅ `toEntity()` でUsrModelEntityに変換すればDelegatorのreturnで使用可能

### 6. Repositories/

**役割:** DBアクセス層。Modelの取得・保存・削除を担当。

**配置するファイル:**
- UsrModelのRepository
- LogModelのRepository
- データアクセスロジック

**実装例:**

`api/app/Domain/Unit/Repositories/UsrUnitRepository.php`:
```php
namespace App\Domain\Unit\Repositories;

use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UsrUnitRepository
{
    /**
     * @return Collection<UsrUnitInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return UsrUnit::where('usr_user_id', $usrUserId)->get();
    }

    public function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
    {
        return UsrUnit::create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnitId,
            'level' => 1,
        ]);
    }

    public function syncModel(UsrUnitInterface $usrUnit): void
    {
        $usrUnit->save();
    }
}
```

**責務:**
- データの取得（find, get, where等）
- データの作成（create, insert）
- データの保存（save, update）
- データの削除（delete）
- **ビジネスロジックは含めない**（Serviceで実装）

### 7. UseCases/

**役割:** ビジネスロジックのエントリーポイント。主にControllerから呼び出される。

**配置するファイル:**
- 1つのAPI操作に対応するユースケース
- トランザクション管理
- 複数のServiceの組み合わせ

**実装例:**

`api/app/Domain/Unit/UseCases/UnitLevelUpUseCase.php`:
```php
namespace App\Domain\Unit\UseCases;

use App\Domain\Unit\Services\UnitLevelUpService;

class UnitLevelUpUseCase
{
    public function __construct(
        private UnitLevelUpService $unitLevelUpService,
    ) {
    }

    public function __invoke(string $usrUserId, string $usrUnitId): void
    {
        DB::transaction(function () use ($usrUserId, $usrUnitId) {
            $this->unitLevelUpService->levelUp($usrUserId, $usrUnitId);
        });
    }
}
```

**責務:**
- API操作全体のフロー制御
- トランザクション管理
- 複数Serviceの呼び出し
- **詳細なビジネスロジックはServiceに委譲**

### 8. Services/

**役割:** ドメインサービス。複雑なビジネスロジックを実装。

**配置するファイル:**
- ビジネスロジックの実装
- 複雑な計算処理
- 複数ModelやRepositoryの組み合わせ

**実装例:**

`api/app/Domain/Unit/Services/UnitService.php`:
```php
namespace App\Domain\Unit\Services;

class UnitService
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private MstUnitRepository $mstUnitRepository,
    ) {
    }

    /**
     * 指定ユニットを新規獲得する
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        $mstUnitIds = $mstUnitIds->unique();
        $validMstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);

        foreach ($validMstUnits as $mstUnit) {
            $this->usrUnitRepository->create($usrUserId, $mstUnit->getId());
        }
    }

    /**
     * 所持済みユニットの場合は、別リソースへ変換する
     */
    public function convertDuplicatedUnitToItem(
        string $usrUserId,
        Collection $rewards,
    ): void {
        // 複雑なビジネスロジック
    }
}
```

**責務:**
- ビジネスロジックの実装
- 複数Repositoryの組み合わせ
- ドメイン知識の集約
- **データアクセスはRepository経由**

### 9. Manager/（任意）

**役割:** 複雑な管理ロジック。Serviceより更に複雑な処理を分離。

**配置するファイル:**
- 非常に複雑な管理ロジック
- 複数Serviceの組み合わせ

**実装例:**

`api/app/Domain/Party/Manager/PartyManager.php`:
```php
namespace App\Domain\Party\Manager;

class PartyManager
{
    // 複雑なパーティ編成管理ロジック
}
```

**使い分け:**
- Service: 通常のビジネスロジック
- Manager: Serviceより更に複雑な管理ロジック（必要な場合のみ）

## 命名規則

### ファイル名

| 種別 | 命名規則 | 例 |
|------|---------|-----|
| Delegator | `{ドメイン名}Delegator.php` | `UnitDelegator.php` |
| Entity | `{名詞}.php` | `CurrencyPurchase.php` |
| Model | `{テーブル名}.php` | `UsrUnit.php` |
| Interface | `{テーブル名}Interface.php` | `UsrUnitInterface.php` |
| Repository | `{テーブル名}Repository.php` | `UsrUnitRepository.php` |
| UseCase | `{機能名}UseCase.php` | `UnitLevelUpUseCase.php` |
| Service | `{機能名}Service.php` | `UnitLevelUpService.php` |
| Constant | `{ドメイン名}Constant.php` | `UnitConstant.php` |
| Enum | `{名詞}Type.php` 等 | `ShopItemType.php` |

### クラス名

- PascalCase を使用
- ファイル名と同じ名前

### メソッド名

- camelCase を使用
- 動詞から始める（get, create, update, delete, fetch等）

## ファイル配置の判断基準

新規ファイルを作成する際、どのサブフォルダに配置すべきか判断するフローチャート:

```
質問1: DBアクセスを行うか？
  ├─ YES → Repository または Model
  └─ NO  → 質問2へ

質問2: 他ドメインから呼び出されるか？
  ├─ YES → Delegator
  └─ NO  → 質問3へ

質問3: ビジネスロジックを持つか？
  ├─ YES → 質問4へ
  └─ NO  → 質問5へ

質問4: Controllerから直接呼び出されるか？
  ├─ YES → UseCase
  └─ NO  → Service

質問5: データ構造を表すか？
  ├─ YES → Entity
  └─ NO  → 質問6へ

質問6: 列挙型か？
  ├─ YES → Enum
  └─ NO  → Constant
```

## チェックリスト

新規ファイル作成時に以下を確認してください:

- [ ] 適切なサブフォルダに配置されているか
- [ ] 命名規則に従っているか
- [ ] namespace が正しいか（`App\Domain\{ドメイン名}\{サブフォルダ名}`）
- [ ] Delegatorの場合、return型制約を守っているか
- [ ] Entityの場合、DomainEntityとして適切か（Delegatorのreturnで使用しないか）
- [ ] Repositoryの場合、ビジネスロジックを含めていないか
- [ ] Serviceの場合、データアクセスはRepository経由か
