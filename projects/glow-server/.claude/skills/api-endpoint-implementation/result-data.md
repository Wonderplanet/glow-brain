# ResultData実装パターン

UseCaseからResponseFactoryへのデータ受け渡し用のResultDataクラスを実装する方法を説明します。

## 目次

1. [ResultDataの役割](#resultdataの役割)
2. [基本構造](#基本構造)
3. [実装パターン](#実装パターン)
4. [命名規則](#命名規則)

---

## ResultDataの役割

### データフローにおける位置づけ

```
Controller
    ↓
UseCase.exec() → ResultData
    ↓
ResponseFactory.createXxxResponse(ResultData) → JsonResponse
```

### 責務

- **データの受け渡し専用** - ロジックを持たない
- **型安全性** - プロパティの型を明示
- **不変性** - readonlyプロパティで構成

---

## 基本構造

### 最小限のResultData

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\{DataClass};

class {Action}ResultData
{
    public function __construct(
        public {DataClass} ${propertyName},
    ) {
    }
}
```

### 実例: StageStartResultData

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrStageStatusData;

class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus
    ) {
    }
}
```

**ファイルパス:** `api/app/Http/Responses/ResultData/StageStartResultData.php`

---

## 実装パターン

### パターン1: 単一のDataクラス

```php
class UserInfoResultData
{
    public function __construct(
        public UsrUserData $usrUser,
    ) {
    }
}
```

### パターン2: 複数のDataクラス

```php
class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus,
    ) {
    }
}
```

### パターン3: Collectionを含む

```php
use Illuminate\Support\Collection;

class StageEndResultData
{
    public function __construct(
        public Collection $stageFirstClearRewards,  // Collection<StageFirstClearReward>
        public Collection $stageAlwaysClearRewards, // Collection<StageAlwaysClearReward>
        public Collection $usrItems,                // Collection<UsrItemEntity>
        public Collection $usrUnits,                // Collection<UsrUnitEntity>
    ) {
    }
}
```

**PHPDocで型を明示:**

```php
/**
 * @param Collection<StageFirstClearReward> $stageFirstClearRewards
 * @param Collection<StageAlwaysClearReward> $stageAlwaysClearRewards
 */
public function __construct(
    public Collection $stageFirstClearRewards,
    public Collection $stageAlwaysClearRewards,
) {
}
```

### パターン4: プリミティブ型を含む

```php
class GameVersionResultData
{
    public function __construct(
        public string $clientVersion,
        public string $assetVersion,
        public string $masterVersion,
        public bool $isMaintenanceMode,
    ) {
    }
}
```

### パターン5: Nullable型

```php
class PvpTopResultData
{
    public function __construct(
        public UsrPvpStatusData $usrPvpStatus,
        public ?OpponentSelectStatusData $opponentSelectStatus,  // nullable
    ) {
    }
}
```

---

## 命名規則

### クラス名

- `{Action}ResultData` パターンを使用
- アクション名はパスカルケース
- `ResultData` サフィックスを必ず付ける

**例:**
- `StageStartResultData`
- `GachaDrawResultData`
- `MissionBulkReceiveRewardResultData`
- `UserBuyStaminaAdResultData`

### プロパティ名

- **キャメルケース** を使用
- glow-schemaのYAML定義のレスポンスキーと対応させる
- プレフィックス（`usr`, `mst`, `opr`等）を保持

**例:**
```php
public UsrParameterData $usrUserParameter,     // usrParameter に対応
public UsrStageStatusData $usrStageStatus,     // usrStageStatus に対応
public Collection $stageFirstClearRewards,     // stageFirstClearRewards に対応
```

### ファイル配置

- **ディレクトリ:** `api/app/Http/Responses/ResultData/`
- **ファイル名:** `{Action}ResultData.php`

---

## 実装手順

### 1. glow-schemaでレスポンス構造を確認

**[api-schema-reference](../api-schema-reference/SKILL.md)** スキルを使って、レスポンスに含めるデータを確認します。

### 2. 必要なDataクラスを確認

レスポンスに含めるデータに対応するDataクラスが存在するか確認：

```bash
# Dataクラスを検索
ls api/app/Http/Responses/Data/
```

存在しない場合は、新規作成が必要です（通常は既存のDataクラスを再利用）。

### 3. ResultDataクラスを作成

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\{必要なDataクラス};
use Illuminate\Support\Collection;

class {Action}ResultData
{
    public function __construct(
        public {DataClass} ${propertyName},
        // ... 必要なプロパティを全て定義
    ) {
    }
}
```

### 4. UseCaseでResultDataを返す

UseCaseの `exec()` メソッドで、作成したResultDataを返します：

```php
public function exec(...): {Action}ResultData
{
    // ビジネスロジック

    return new {Action}ResultData(
        $usrUserParameter,
        $usrStageStatus,
    );
}
```

---

## よくある間違い

### ❌ 間違った例

```php
// 1. ロジックを含めている
class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
    ) {
    }

    // ❌ ResultDataにロジックを持たせない
    public function calculateTotal(): int
    {
        return $this->usrUserParameter->getStamina() * 2;
    }
}

// 2. プロパティがprivate
class StageStartResultData
{
    public function __construct(
        private UsrParameterData $usrUserParameter,  // ❌ privateではなくpublic
    ) {
    }
}

// 3. Collection型にPHPDocがない
class StageEndResultData
{
    public function __construct(
        public Collection $rewards,  // ❌ PHPDocで型を明示すべき
    ) {
    }
}
```

### ✅ 正しい例

```php
// 1. データの受け渡し専用
class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus,
    ) {
    }
    // ロジックなし
}

// 2. プロパティがpublic
class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,  // ✅ public
        public UsrStageStatusData $usrStageStatus,  // ✅ public
    ) {
    }
}

// 3. Collection型にPHPDocを記述
class StageEndResultData
{
    /**
     * @param Collection<StageFirstClearReward> $stageFirstClearRewards
     */
    public function __construct(
        public Collection $stageFirstClearRewards,  // ✅ PHPDocで型を明示
    ) {
    }
}
```

---

## 実装例

### 例1: シンプルなResultData

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;

class UserBuyStaminaAdResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
    ) {
    }
}
```

### 例2: 複数のDataクラス

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;

class PvpTopResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrPvpStatusData $usrPvpStatus,
        public ?OpponentSelectStatusData $opponentSelectStatus,
    ) {
    }
}
```

### 例3: Collectionを含む

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Usr\Entities\UsrItemEntity;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Http\Responses\Data\UserLevelUpData;
use Illuminate\Support\Collection;

class StageEndResultData
{
    /**
     * @param Collection<StageFirstClearReward> $stageFirstClearRewards
     * @param Collection<StageAlwaysClearReward> $stageAlwaysClearRewards
     * @param Collection<UsrItemEntity> $usrItems
     * @param Collection<UsrUnitEntity> $usrUnits
     */
    public function __construct(
        public Collection $stageFirstClearRewards,
        public Collection $stageAlwaysClearRewards,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrItems,
        public Collection $usrUnits,
    ) {
    }
}
```

---

## 次のステップ

ResultData実装が完了したら、**[api-response](../api-response/SKILL.md)** スキルでResponseFactoryを実装してください。
