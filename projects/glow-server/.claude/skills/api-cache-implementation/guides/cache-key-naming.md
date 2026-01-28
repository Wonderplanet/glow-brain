# キャッシュキーの命名規則

CacheKeyUtilを使用したキャッシュキーの命名規則と実装方法について説明します。

## 基本原則

### 1. CacheKeyUtilで一元管理

すべてのキャッシュキーは`CacheKeyUtil`クラスで一元管理します。

**理由:**
- キー名の重複を防ぐ
- キー構造を統一する
- 変更時の影響範囲を限定する

```php
// ✅ 正しい実装
$cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);

// ❌ 間違った実装（ハードコード）
$cacheKey = "pvp:{$sysPvpSeasonId}:ranking";
```

### 2. キー構造の統一

キーは階層構造で命名し、コロン（`:`）で区切ります。

**基本フォーマット:**
```
{機能名}:{識別子}:{サブキー}
```

**例:**
- `gacha:123:probability` - ガチャID 123の提供割合
- `pvp:2025001:ranking` - PVPシーズン2025001のランキング
- `advent_battle:456:raid:total_score` - 降臨バトル456のレイド累計スコア

## 命名パターン

### パターン1: 単一リソース

単一のリソースを識別するキー。

```php
public static function getPvpRankingKey(string $sysPvpSeasonId): string
{
    return "pvp:{$sysPvpSeasonId}:ranking";
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getPvpRankingKey('2025001');
// 結果: "pvp:2025001:ranking"
```

### パターン2: ユーザー固有リソース

ユーザーIDを含むキー。

```php
public static function getShopPurchaseHistoryKey(string $usrUserId): string
{
    return "shop:purchaseHistory:{$usrUserId}";
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getShopPurchaseHistoryKey('user_123');
// 結果: "shop:purchaseHistory:user_123"
```

### パターン3: 複合キー（複数のパラメータ）

複数のパラメータを組み合わせたキー。

```php
public static function getPvpOpponentCandidateKey(
    string $sysPvpSeasonId,
    string $rankClassType,
    int $rankClassLevel
): string {
    return "pvp:{$sysPvpSeasonId}:opponent_candidate:{$rankClassType}{$rankClassLevel}";
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getPvpOpponentCandidateKey('2025001', 'Bronze', 3);
// 結果: "pvp:2025001:opponent_candidate:Bronze3"
```

### パターン4: バージョン付きキー

キー構造を変更する際、古いキーとの互換性を維持するためにバージョンを含める。

```php
public static function getPvpOpponentStatusKey(string $sysPvpSeasonId, string $myId): string
{
    return "pvp:{$sysPvpSeasonId}:opponent_status:v1_2_1:{$myId}";
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getPvpOpponentStatusKey('2025001', 'user_123');
// 結果: "pvp:2025001:opponent_status:v1_2_1:user_123"
```

**バージョンが必要なケース:**
- キャッシュデータの構造を変更する場合
- 古いデータと新しいデータを共存させたい場合

### パターン5: キャッシュ用キー（レスポンスデータ全体）

APIレスポンス全体をキャッシュする場合、`_cache`サフィックスを付ける。

```php
public static function getPvpRankingCacheKey(string $sysPvpSeasonId): string
{
    return "pvp:{$sysPvpSeasonId}:ranking_cache";
}
```

**使用例:**
```php
$cacheKey = CacheKeyUtil::getPvpRankingCacheKey('2025001');
// 結果: "pvp:2025001:ranking_cache"
```

**使い分け:**
- `pvp:{id}:ranking` → Sorted Setで個別のスコアを管理
- `pvp:{id}:ranking_cache` → 計算済みのランキング全体をキャッシュ

## CacheKeyUtilへの追加手順

新しいキャッシュキーを追加する際の手順です。

### ステップ1: メソッドの追加

`CacheKeyUtil`クラスにstaticメソッドを追加します。

```php
/**
 * {機能の説明}
 * @param string $param1
 * @param int $param2
 * @return string
 */
public static function getNewFeatureCacheKey(string $param1, int $param2): string
{
    return "new_feature:{$param1}:{$param2}";
}
```

### ステップ2: PHPDocの記述

メソッドには必ずPHPDocを記述します。

**記述内容:**
- 機能の説明（1行）
- パラメータの型と説明
- 戻り値の型

### ステップ3: 命名規則の確認

以下の点を確認します。

**チェックリスト:**
- [ ] 既存のキーと重複していないか
- [ ] 階層構造が適切か（コロン区切り）
- [ ] 機能名が明確か
- [ ] パラメータが必要十分か

### ステップ4: 使用例の確認

実際にキーを生成して、期待通りの形式になることを確認します。

```php
// テストで確認
$key = CacheKeyUtil::getNewFeatureCacheKey('test', 123);
// 期待値: "new_feature:test:123"
```

## 実装例

### 実装例1: マスターデータのキー

```php
/**
 * MngMessageBundleのキャッシュキー
 * @param string $language
 * @return string
 */
public static function getMngMessageBundleKey(string $language): string
{
    return "mng:mng_message_bundle:{$language}";
}
```

**使用:**
```php
$cacheKey = CacheKeyUtil::getMngMessageBundleKey('ja');
// 結果: "mng:mng_message_bundle:ja"
```

### 実装例2: プラットフォーム別キー

```php
/**
 * MngAssetReleaseVersionのキャッシュキー
 * @param int $platform
 * @return string
 */
public static function getMngAssetReleaseVersionKey(int $platform): string
{
    return "mng:mng_asset_release_version:{$platform}";
}
```

**使用:**
```php
$cacheKey = CacheKeyUtil::getMngAssetReleaseVersionKey(1); // iOS
// 結果: "mng:mng_asset_release_version:1"
```

### 実装例3: 外部API用キー

```php
/**
 * BNIDのアクセストークンAPIから取得したIDのキャッシュキー
 * @param string $code
 * @return string
 */
public static function getBnidUserIdKey(string $code): string
{
    return "bnid:{$code}:user_id";
}
```

**使用:**
```php
$cacheKey = CacheKeyUtil::getBnidUserIdKey('auth_code_xyz');
// 結果: "bnid:auth_code_xyz:user_id"
```

## ベストプラクティス

### DO（推奨）

✅ CacheKeyUtilを使用する
✅ 階層構造を明確にする（コロン区切り）
✅ PHPDocを記述する
✅ パラメータの型を明示する
✅ メソッド名は`get{Feature}{Type}Key`形式

### DON'T（非推奨）

❌ ハードコードでキーを指定する
❌ 階層構造が深すぎる（4階層以上）
❌ 省略しすぎた名前（`pvp:r:123`など）
❌ 動的な生成ロジックをサービスクラスに書く
❌ 日本語を含める
