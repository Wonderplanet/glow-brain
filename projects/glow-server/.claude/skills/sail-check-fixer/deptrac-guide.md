# Deptrac ガイド: アーキテクチャ違反の修正

## 目次

1. [概要](#概要)
2. [基本的な使い方](#基本的な使い方)
3. [レイヤーアーキテクチャの理解](#レイヤーアーキテクチャの理解)
4. [依存関係ルール](#依存関係ルール)
5. [よくある違反パターン](#よくある違反パターン)
6. [修正方法](#修正方法)
7. [トラブルシューティング](#トラブルシューティング)

## 概要

### Deptracとは

Deptrac (Dependency Tracker) は、レイヤー間の依存関係を管理・検証するツール。

**目的**:
- アーキテクチャの一貫性を保つ
- 不適切な依存関係を防ぐ
- コードの保守性を向上させる
- ドメインの境界を明確にする

### 設定ファイル

**場所**: `api/deptrac.yaml`

**主要設定**:
- レイヤー定義（Controller, UseCase, Service, Delegator, Entity等）
- 依存関係ルール（どのレイヤーがどのレイヤーに依存できるか）

## 基本的な使い方

### 全ファイルを解析

```bash
# 全対象ファイルを解析
./tools/bin/sail-wp deptrac

# 実行されるコマンド（内部）
# docker-compose exec php vendor/bin/deptrac analyse
```

### エラー表示形式

```
Violations: 3

Controller must not depend on Service (Controller -> Service)
app/Http/Controllers/ExampleController.php:15
  -> app/Domain/Example/Services/ExampleService.php:10

UseCase must not depend on DomainEntity from other domain (UseCase -> DomainEntity)
app/Domain/Shop/UseCases/PurchaseUseCase.php:45
  -> app/Domain/Item/Entities/ItemEntity.php:20

Delegator must not return DomainEntity (Delegator -> DomainEntity)
app/Domain/Example/Delegators/ExampleDelegator.php:30
  -> app/Domain/Example/Entities/ExampleEntity.php:15
```

### 成功時の出力

```
Violations: 0
```

## レイヤーアーキテクチャの理解

### レイヤー構造

```
┌─────────────────────────────────────────────┐
│              Controller                     │  HTTPリクエストを受け取る
├─────────────────────────────────────────────┤
│               UseCase                       │  ビジネスロジックの実行
├─────────────────────────────────────────────┤
│   Service          Delegator                │  ドメイン内ロジック / ドメイン間連携
├─────────────────────────────────────────────┤
│   Entity (Domain/Resource/Common)           │  データ構造
├─────────────────────────────────────────────┤
│   Model / Repository                        │  データアクセス層
└─────────────────────────────────────────────┘
```

### 主要レイヤーの説明

#### 1. Controller (HTTPレイヤー)

**役割**: HTTPリクエストを受け取り、UseCaseを呼び出す

**依存可能**: UseCase のみ

**配置**: `api/app/Http/Controllers/*`

#### 2. UseCase (アプリケーション層)

**役割**: ビジネスロジックの実行、トランザクション管理

**依存可能**:
- Service（同一ドメイン内）
- Delegator（他ドメインへのアクセス）
- Entity（DomainEntity, ResourceEntity, CommonEntity）
- Repository
- MstModel系

**配置**: `api/app/Domain/*/UseCases/*`

#### 3. Service (ドメイン層)

**役割**: ドメイン固有のビジネスロジック

**依存可能**:
- Entity
- Repository
- Delegator
- MstModel系

**依存不可**: Controller, UseCase

**配置**: `api/app/Domain/*/Services/*`

#### 4. Delegator (ドメイン間連携層)

**役割**: 他ドメインへの依存を抽象化し、ドメイン間を疎結合に保つ

**依存可能**:
- Service（同一ドメイン内）
- Repository
- ResourceEntity, CommonEntity, UsrModelEntity（返却値として使用可）

**依存不可**: DomainEntity（ドメイン固有のEntityは返却できない）

**配置**: `api/app/Domain/*/Delegators/*`

#### 5. Entity (エンティティ層)

**種類**:

##### DomainEntity
- ドメイン固有のEntity
- **Delegatorの返却値として使用不可**
- UsrModelInterfaceへの依存が可能（ユーザデータ操作が可能）
- 配置: `api/app/Domain/{DomainName}/Entities/*`

##### ResourceEntity
- 複数ドメインで共有可能なEntity
- **Delegatorの返却値として使用可**
- 配置: `api/app/Domain/Resource/Entities/*`

##### CommonEntity
- 全ドメインで利用可能なEntity
- **Delegatorの返却値として使用可**
- 配置: `api/app/Domain/Common/Entities/*`

#### 6. Model / Repository (データアクセス層)

**役割**: データベースへのアクセス、ORMの操作

**配置**: `api/app/Domain/*/Models/*`, `api/app/Domain/*/Repositories/*`

## 依存関係ルール

### Controller層

```yaml
Controller:
  - UseCase  # ✅ UseCaseのみ依存可能
```

**✅ 正しい例**:
```php
class ExampleController extends Controller
{
    public function __construct(
        private readonly ExampleUseCase $useCase,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $result = $this->useCase->exec($request->input('user_id'));
        return response()->json($result);
    }
}
```

**❌ 間違った例**:
```php
class ExampleController extends Controller
{
    public function __construct(
        private readonly ExampleService $service,  // ❌ Serviceを直接呼び出し
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $result = $this->service->process($request->input('user_id'));
        return response()->json($result);
    }
}
```

### UseCase層

```yaml
UseCase:
  - Service
  - Delegator
  - DomainEntity
  - ResourceEntity
  - CommonEntity
  - Repository
  - MstModel系
```

**✅ 正しい例**:
```php
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,           // ✅ 同一ドメインのService
        private readonly ItemDelegator $itemDelegator,       // ✅ 他ドメインへはDelegator経由
    ) {}

    public function exec(string $userId, string $itemId): array
    {
        // Serviceを使用
        $result = $this->shopService->purchase($userId, $itemId);

        // 他ドメインへはDelegator経由
        $items = $this->itemDelegator->getItems($userId);

        return $result;
    }
}
```

**❌ 間違った例**:
```php
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly ItemService $itemService,  // ❌ 他ドメインのServiceを直接依存
    ) {}

    public function exec(string $userId, string $itemId): array
    {
        $result = $this->shopService->purchase($userId, $itemId);
        $items = $this->itemService->getItems($userId);  // ❌ Delegator経由すべき
        return $result;
    }
}
```

### Service層

```yaml
Service:
  - Service（同一ドメイン内）
  - Delegator
  - Entity
  - Repository
  - MstModel系
```

**✅ 正しい例**:
```php
class ShopService
{
    public function __construct(
        private readonly ItemDelegator $itemDelegator,       // ✅ 他ドメインへはDelegator経由
        private readonly UsrShopRepository $shopRepository,  // ✅ Repository
    ) {}

    public function purchase(string $userId, string $itemId): void
    {
        $items = $this->itemDelegator->getItems($userId);
        // ...
    }
}
```

### Delegator層

```yaml
Delegator:
  - Service（同一ドメイン内）
  - Repository
  - ResourceEntity  # ✅ 返却値として使用可
  - CommonEntity    # ✅ 返却値として使用可
  - UsrModelEntity  # ✅ 返却値として使用可
```

**重要**: DomainEntityは返却できない

**✅ 正しい例**:
```php
class ItemDelegator
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    /**
     * @return array<UsrItemEntity>  // ✅ UsrModelEntity（ResourceEntity）を返却
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getItems($userId);
    }
}
```

**❌ 間違った例**:
```php
class ItemDelegator
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    /**
     * @return array<ItemDomainEntity>  // ❌ DomainEntityを返却
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getDetailedItems($userId);
    }
}
```

## よくある違反パターン

### 違反1: ControllerがServiceを直接呼び出し

**エラー**:
```
Controller must not depend on Service
app/Http/Controllers/ShopController.php:15
  -> app/Domain/Shop/Services/ShopService.php:10
```

**原因**: ControllerはUseCaseを経由すべき

**修正方法**: UseCaseを作成して、Controllerからはそれを呼び出す

**修正前**:
```php
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {}

    public function purchase(Request $request): JsonResponse
    {
        $this->shopService->purchase(
            $request->input('user_id'),
            $request->input('item_id'),
        );
        return response()->json(['success' => true]);
    }
}
```

**修正後**:
```php
// 1. UseCaseを作成
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $this->shopService->purchase($userId, $itemId);
    }
}

// 2. ControllerからUseCaseを呼び出し
class ShopController extends Controller
{
    public function __construct(
        private readonly ShopPurchaseUseCase $useCase,
    ) {}

    public function purchase(Request $request): JsonResponse
    {
        $this->useCase->exec(
            $request->input('user_id'),
            $request->input('item_id'),
        );
        return response()->json(['success' => true]);
    }
}
```

### 違反2: UseCaseが他ドメインのServiceを直接呼び出し

**エラー**:
```
UseCase must not depend on Service from other domain
app/Domain/Shop/UseCases/PurchaseUseCase.php:45
  -> app/Domain/Item/Services/ItemService.php:10
```

**原因**: 他ドメインへはDelegatorを経由すべき

**修正方法**: Delegatorを作成して、それを経由する

**修正前**:
```php
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly ItemService $itemService,  // ❌ 他ドメインのService
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $items = $this->itemService->getItems($userId);  // ❌ 直接呼び出し
        $this->shopService->purchase($userId, $itemId);
    }
}
```

**修正後**:
```php
// 1. Delegatorを作成
class ItemDelegator
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    /**
     * @return array<UsrItemEntity>
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getItems($userId);
    }
}

// 2. UseCaseからDelegatorを使用
class ShopPurchaseUseCase
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly ItemDelegator $itemDelegator,  // ✅ Delegator経由
    ) {}

    public function exec(string $userId, string $itemId): void
    {
        $items = $this->itemDelegator->getItems($userId);  // ✅ Delegator経由
        $this->shopService->purchase($userId, $itemId);
    }
}
```

### 違反3: DelegatorがDomainEntityを返却

**エラー**:
```
Delegator must not return DomainEntity
app/Domain/Example/Delegators/ExampleDelegator.php:30
  -> app/Domain/Example/Entities/ExampleDomainEntity.php:15
```

**原因**: Delegatorは他ドメインに公開するため、ドメイン固有のEntityは返せない

**修正方法**: ResourceEntityまたはUsrModelEntityに変換して返す

**修正前**:
```php
class ItemDelegator
{
    /**
     * @return array<ItemDomainEntity>  // ❌ DomainEntityを返却
     */
    public function getItems(string $userId): array
    {
        return $this->itemService->getDetailedItems($userId);
    }
}
```

**修正後**:
```php
class ItemDelegator
{
    /**
     * @return array<UsrItemEntity>  // ✅ ResourceEntity（UsrModelEntity）を返却
     */
    public function getItems(string $userId): array
    {
        $domainEntities = $this->itemService->getDetailedItems($userId);

        // DomainEntityをUsrModelEntityに変換
        return array_map(
            fn($entity) => $entity->toUsrItemEntity(),
            $domainEntities
        );
    }
}
```

### 違反4: Repositoryが他ドメインのModelに依存

**エラー**:
```
UsrModelRepository must not depend on UsrModel from other domain
```

**原因**: Repositoryは自ドメインのModelのみ扱うべき

**修正方法**: Delegatorを経由して他ドメインのデータを取得

## 修正方法

### ステップ1: 違反箇所を特定

```bash
./tools/bin/sail-wp deptrac
```

エラーメッセージから以下を確認：
- どのレイヤーがどのレイヤーに違反しているか
- 違反しているファイルと行番号

### ステップ2: 適切なアーキテクチャパターンを選択

| 状況 | 解決策 |
|------|--------|
| ControllerがServiceを呼び出し | UseCaseを作成 |
| UseCaseが他ドメインのServiceを呼び出し | Delegatorを作成 |
| DelegatorがDomainEntityを返却 | ResourceEntityに変換 |
| Repository間の依存 | Delegatorを経由 |

### ステップ3: コードを修正

各パターンに応じて修正を実施。

### ステップ4: 再度チェック

```bash
./tools/bin/sail-wp deptrac
```

## トラブルシューティング

### 問題1: Delegatorをどこに配置すべきか分からない

**答え**: データを提供する側のドメインに配置

例: ShopドメインがItemドメインのデータを取得したい場合
→ `app/Domain/Item/Delegators/ItemDelegator.php` に配置

### 問題2: ResourceEntityがない場合

**対処法**: ResourceEntityを新規作成

```php
// app/Domain/Resource/Usr/Entities/UsrItemEntity.php
namespace App\Domain\Resource\Usr\Entities;

class UsrItemEntity
{
    public function __construct(
        public readonly string $usrUserId,
        public readonly string $mstItemId,
        public readonly int $amount,
    ) {}
}
```

### 問題3: 既存のコードが大量に違反している

**対処法**: 段階的に修正

1. 新規コードは必ず準拠
2. 修正対象のコードから順次対応
3. 違反箇所を記録して、計画的に修正

### 問題4: Gameレイヤーの扱い

`app/Domain/Game` は特別なレイヤーで、全ドメインに対して上位レイヤー。

**特徴**:
- 他ドメインへDelegateなしで依存可能
- ゲーム全体の統括ロジックを担当

## チェックリスト

- [ ] deptracを実行して違反0を確認
- [ ] ControllerはUseCaseのみに依存している
- [ ] UseCaseは他ドメインのServiceに直接依存していない
- [ ] DelegatorがDomainEntityを返していない
- [ ] Repositoryは自ドメインのModelのみに依存している
- [ ] 新規作成したDelegatorが適切な場所に配置されている
- [ ] ResourceEntityが適切に定義されている
- [ ] コミットメッセージが適切に記載されている
