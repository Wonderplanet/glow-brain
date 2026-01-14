# ドメインの4つの分類

`api/app/Domain` 配下のドメインは、役割と依存関係によって4つに分類されます。

## 目次

- [1. 通常ドメイン](#1-通常ドメイン)
- [2. Gameドメイン（基盤ドメイン）](#2-gameドメイン基盤ドメイン)
- [3. Resourceドメイン（部分共通ドメイン）](#3-resourceドメイン部分共通ドメイン)
- [4. Commonドメイン（全体共通ドメイン）](#4-commonドメイン全体共通ドメイン)
- [ドメイン分類の判断フローチャート](#ドメイン分類の判断フローチャート)

## 1. 通常ドメイン

### 概要

機能別のビジネスロジックを持つドメイン。最も一般的なドメインの形態。

### 該当ドメイン例

- `Unit` - ユニット管理
- `Shop` - ショップ機能
- `Party` - パーティ編成
- `Stage` - ステージ進行
- `Gacha` - ガチャ機能
- `Mission` - ミッション管理
- `Item` - アイテム管理
- `Tutorial` - チュートリアル
- その他多数

### フォルダ構成

```
api/app/Domain/Unit/
├── Constants/          # 定数クラス
├── Delegators/         # 他ドメインから呼び出される公開インターフェース
├── Enums/              # 列挙型
├── Entities/           # ドメイン固有のEntity（DomainEntity）
├── Models/             # DBモデル（UsrModel）とInterface
│   ├── Eloquent/       # Eloquent実装の補助クラス
│   ├── UsrUnit.php
│   └── UsrUnitInterface.php
├── Repositories/       # DBアクセス層
├── UseCases/           # ビジネスロジックのエントリーポイント
└── Services/           # ドメインサービス（複雑なビジネスロジック）
```

### 特徴

- **他の通常ドメインへの直接依存は禁止**（Delegator経由のみ）
- **Game、Resource、Commonドメインへの依存は可能**
- **Delegatorのreturnでは、DomainEntityとUsrModelInterfaceは使用禁止**

### 依存関係

```
通常ドメイン（例: Unit）
  ↓ 依存可能
  ├── Game（基盤ドメイン）
  ├── Resource（部分共通）
  ├── Common（全体共通）
  └── 他の通常ドメイン（Delegator経由のみ）
```

## 2. Gameドメイン（基盤ドメイン）

### 概要

どのドメインからも依存しない最上位の基盤ドメイン。ゲームへのログイン、データフェッチなど、ゲーム全体の基盤機能を提供。

### パス

`api/app/Domain/Game/`

### フォルダ構成

```
api/app/Domain/Game/
├── Services/           # ゲーム全体の基盤サービス
│   ├── GameService.php
│   ├── IgnService.php
│   └── ...
└── UseCases/           # ゲーム全体のユースケース
    ├── GameFetchUseCase.php
    ├── GameVersionUseCase.php
    └── ...
```

### 特徴

- **他のどのドメインからも依存されない**（最上位レイヤー）
- **他ドメインへDelegatorなしで依存可能**（特権を持つ）
- **構成がシンプル**（Services、UseCasesのみ）
- **Constants、Delegators、Entities、Models、Repositoriesは持たない**

### 役割

- ゲームログイン処理
- 全体データフェッチ（`/api/game/fetch`）
- バージョン管理
- ゲーム全体の状態管理

### 依存関係

```
Game（基盤ドメイン）
  ↓ Delegator経由なしで依存可能
  ├── 通常ドメイン（Unit, Shop, Party等）
  ├── Resource（部分共通）
  └── Common（全体共通）
```

### 実装例

`api/app/Domain/Game/Services/GameService.php`:
```php
class GameService
{
    public function __construct(
        // 他ドメインのDelegatorを直接DIできる
        private AuthDelegator $authDelegator,
        private UserDelegator $userDelegator,
        private StageDelegator $stageDelegator,
        private ShopDelegator $shopDelegator,
        private MissionDelegator $missionDelegator,
        // ...
    ) {
    }

    public function fetch(string $usrUserId, CarbonImmutable $now): GameFetchData
    {
        // 全ドメインのデータを集約してレスポンス作成
    }
}
```

## 3. Resourceドメイン（部分共通ドメイン）

### 概要

特定の複数ドメインから参照可能な共通ファイル。Commonよりも依存するドメインの数が少ないイメージ。マスタデータリポジトリはここに含まれる。

### パス

`api/app/Domain/Resource/`

### フォルダ構成

```
api/app/Domain/Resource/
├── Constants/          # 共通定数
├── Dtos/               # データ転送オブジェクト
├── Dyn/                # 動的データ
├── Entities/           # 共通Entity（ResourceEntity）
├── Enums/              # 共通列挙型
├── Traits/             # 共通トレイト
├── Mst/                # マスタデータ（全ドメイン共通）
│   ├── Models/         # マスタデータのEloquent Model
│   ├── Repositories/   # マスタデータのRepository
│   ├── Entities/       # マスタデータのEntity（MstModelEntity）
│   ├── Services/
│   └── Traits/
├── Usr/                # ユーザーデータの共通Entity
│   ├── Entities/       # UsrModelEntity（Delegatorのreturnで使用可）
│   ├── Models/
│   ├── Repositories/
│   └── Services/
├── Log/                # ログデータの共通Repository
│   └── Repositories/
├── Mng/                # 管理データの共通Repository
│   └── Repositories/
└── Sys/                # システムデータの共通Repository
    └── Repositories/
```

### 特徴

- **Delegatorのreturnで使用可能**（ResourceEntity、MstModelEntity、UsrModelEntity）
- **全ドメインから参照可能**
- **マスタデータ（Mst）は全ドメイン共有可能**

### 役割

- マスタデータのModel、Repository、Entity
- ユーザーデータの共通Entity（UsrModelEntity）
- 複数ドメインで共有される共通Entity（ResourceEntity）
- データ転送オブジェクト（Dto）

### 実装例

`api/app/Domain/Resource/Entities/CheatCheckUnit.php`:
```php
namespace App\Domain\Resource\Entities;

class CheatCheckUnit
{
    // 複数ドメインで共有されるEntity
    // Delegatorのreturnで使用可能
}
```

`api/app/Domain/Resource/Usr/Entities/UsrUnitEntity.php`:
```php
namespace App\Domain\Resource\Usr\Entities;

class UsrUnitEntity
{
    // UsrModelInterfaceをtoEntity()で変換した結果
    // Delegatorのreturnで使用可能
}
```

`api/app/Domain/Resource/Mst/Repositories/MstUnitRepository.php`:
```php
namespace App\Domain\Resource\Mst\Repositories;

class MstUnitRepository
{
    // 全ドメインから参照可能なマスタデータRepository
}
```

## 4. Commonドメイン（全体共通ドメイン）

### 概要

全ドメインから参照可能な共通ファイル。ドメインロジックに依存しない純粋な汎用クラス。

### パス

`api/app/Domain/Common/`

### フォルダ構成

```
api/app/Domain/Common/
├── Constants/          # 全体共通定数
├── Entities/           # 全体共通Entity（CommonEntity）
├── Enums/              # 全体共通列挙型
├── Exceptions/         # 共通例外クラス
├── Facades/            # Facadeパターン
├── Factories/          # Factoryパターン
├── Managers/           # Manager（複雑な管理ロジック）
├── Models/             # 共通モデル
├── Notifications/      # 通知関連
├── Repositories/       # 共通Repository
├── Services/           # 全体共通サービス
├── Traits/             # 全体共通トレイト
└── Utils/              # ユーティリティクラス
```

### 特徴

- **Delegatorのreturnで使用可能**（CommonEntity）
- **全ドメインから参照可能**
- **ドメインロジックに依存しない純粋な汎用クラス**
- **他のCommonEntity同士の相互参照は可能**

### 役割

- 全ドメインで使用される汎用Entity（例: Clock、DateTimeRange）
- 共通例外（GameException）
- 共通定数（ErrorCode）
- ユーティリティクラス（StringUtil）

### 実装例

`api/app/Domain/Common/Entities/Clock.php`:
```php
namespace App\Domain\Common\Entities;

class Clock
{
    // ドメインロジックに依存しない純粋な時刻管理Entity
    // Delegatorのreturnで使用可能
}
```

`api/app/Domain/Common/Utils/StringUtil.php`:
```php
namespace App\Domain\Common\Utils;

class StringUtil
{
    public static function convertToISO8601(string $datetime): string
    {
        // 全ドメインで使用される汎用ユーティリティ
    }
}
```

`api/app/Domain/Common/Exceptions/GameException.php`:
```php
namespace App\Domain\Common\Exceptions;

class GameException extends Exception
{
    // 全ドメインで使用される共通例外
}
```

## ドメイン分類の判断フローチャート

新規ドメイン作成時に、どの分類に属するかを判断するフローチャート:

```
質問1: ゲーム全体の基盤機能か？（ログイン、データフェッチ等）
  ├─ YES → Game（基盤ドメイン）
  └─ NO  → 質問2へ

質問2: 全ドメインから参照される汎用機能か？
  ├─ YES → 質問3へ
  └─ NO  → 通常ドメイン

質問3: ドメインロジックに依存しない純粋な汎用クラスか？
  ├─ YES → Common（全体共通ドメイン）
  └─ NO  → Resource（部分共通ドメイン）
```

### 具体例での判断

| 機能 | 判断 | 理由 |
|------|------|------|
| ユニット管理 | 通常ドメイン | 特定の機能別ビジネスロジック |
| ショップ機能 | 通常ドメイン | 特定の機能別ビジネスロジック |
| ゲームログイン | Game | ゲーム全体の基盤機能 |
| マスタデータ | Resource | 全ドメインから参照されるデータ |
| 共通例外クラス | Common | ドメインロジックに依存しない汎用クラス |
| 日時処理ユーティリティ | Common | ドメインロジックに依存しない汎用クラス |

## チェックリスト

新規ドメイン実装時に以下を確認してください:

- [ ] ドメインの分類（通常、Game、Resource、Common）は正しいか
- [ ] Gameドメインの場合、他ドメインから依存されていないか
- [ ] Resourceドメインの場合、複数ドメインで共有される想定か
- [ ] Commonドメインの場合、ドメインロジックに依存していないか
- [ ] 通常ドメインの場合、他の通常ドメインへの依存はDelegator経由か
