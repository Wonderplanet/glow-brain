# ルーティング定義の追加

APIエンドポイントのルーティング定義を `api/routes/api.php` に追加する方法を説明します。

## 目次

1. [基本パターン](#基本パターン)
2. [ミドルウェアグループ](#ミドルウェアグループ)
3. [コントローラーグループ](#コントローラーグループ)
4. [命名規則](#命名規則)

---

## 基本パターン

### ルート定義の構造

`api/routes/api.php` では、以下の構造でルートを定義します：

```php
Route::middleware([...])->group(function () {
    Route::controller(Controllers\{Domain}Controller::class)->group(function () {
        Route::post('/{domain}/{action}', '{methodName}');
    });
});
```

### 実例: Stage API

```php
Route::controller(Controllers\StageController::class)->group(function () {
    Route::post('/stage/start', 'start');
    Route::post('/stage/end', 'end');
    Route::post('/stage/continue_diamond', 'continueDiamond');
    Route::post('/stage/abort', 'abort');
});
```

**ファイルパス:** `api/routes/api.php:121-128`

---

## ミドルウェアグループ

### 認証不要のエンドポイント

サインアップ、サインインなど認証が不要なエンドポイント：

```php
Route::middleware([
    'encrypt',
    // 'client_version_check',  // コメントアウト
    // 'asset_version_check',
    // 'master_version_check',
])->group(function () {
    Route::controller(Controllers\AuthController::class)->group(function () {
        Route::post('/sign_up', 'signUp');
        Route::post('/sign_in', 'signIn');
    });
});
```

**ファイルパス:** `api/routes/api.php:18-28`

**適用ミドルウェア:**
- `encrypt` - リクエスト/レスポンスの暗号化

---

### 認証必須のエンドポイント（標準）

ほとんどのAPIエンドポイントはこのグループに属します：

```php
Route::middleware([
    'encrypt',
    'auth:api',
    'block_multiple_access',
    'user_status_check',
    'client_version_check',
    'asset_version_check',
    'master_version_check',
    'cross_day_check',
])->group(function () {
    // メンテナンスチェック不要なAPI群
    Route::controller(Controllers\UserController::class)->group(function () {
        Route::get('/user/info', 'info');
        Route::post('/user/change_name', 'changeName');
    });
});
```

**ファイルパス:** `api/routes/api.php:31-53`

**適用ミドルウェア:**
- `encrypt` - リクエスト/レスポンスの暗号化
- `auth:api` - JWT認証
- `block_multiple_access` - 多重アクセス防止
- `user_status_check` - ユーザー状態チェック（BAN等）
- `client_version_check` - クライアントバージョン確認
- `asset_version_check` - アセットバージョン確認
- `master_version_check` - マスターデータバージョン確認
- `cross_day_check` - 日跨ぎチェック

---

### メンテナンスチェック対象のエンドポイント

ゲームコンテンツ系のAPIエンドポイント（Stage、Gacha、PvP等）：

```php
Route::middleware([
    'encrypt',
    'auth:api',
    'block_multiple_access',
    'user_status_check',
    'client_version_check',
    'asset_version_check',
    'master_version_check',
    'cross_day_check',
])->group(function () {
    // メンテナンスチェック対象のドメイン群
    Route::middleware(['content_maintenance_check'])->group(function () {
        Route::controller(Controllers\StageController::class)->group(function () {
            Route::post('/stage/start', 'start');
            Route::post('/stage/end', 'end');
        });

        Route::controller(Controllers\GachaController::class)->group(function () {
            Route::get('/gacha/prize', 'prize');
            Route::post('/gacha/draw/diamond', 'drawDiamond');
        });
    });
});
```

**ファイルパス:** `api/routes/api.php:119-174`

**追加ミドルウェア:**
- `content_maintenance_check` - コンテンツメンテナンス中チェック

---

## コントローラーグループ

### ドメイン別にグループ化

各ドメインのエンドポイントはコントローラーグループでまとめます：

```php
Route::controller(Controllers\StageController::class)->group(function () {
    Route::post('/stage/start', 'start');
    Route::post('/stage/end', 'end');
    Route::post('/stage/continue_diamond', 'continueDiamond');
    Route::post('/stage/continue_ad', 'continueAd');
    Route::post('/stage/abort', 'abort');
    Route::post('/stage/cleanup', 'cleanup');
});
```

**利点:**
- エンドポイントが整理される
- コントローラー名を繰り返さなくて済む
- 可読性が向上

---

## 命名規則

### URLパス命名規則

- **スネークケース** を使用（`buy_stamina_ad`、`change_opponent`）
- **動詞 + 名詞** パターン（`start`、`end`、`draw`、`update_and_fetch`）
- **複数単語はアンダースコア** で区切る（`continue_diamond`、`bulk_receive_reward`）

### メソッド名命名規則

- **キャメルケース** を使用（`start`、`continueDiamond`、`bulkReceiveReward`）
- URLパスのスネークケースをキャメルケースに変換

**対応例:**
- `/stage/start` → `start()`
- `/stage/continue_diamond` → `continueDiamond()`
- `/mission/bulk_receive_reward` → `bulkReceiveReward()`
- `/user/buy_stamina_ad` → `buyStaminaAd()`

---

## ミドルウェア選択のフローチャート

```
認証が必要か?
├─ NO → encryptのみのグループ
└─ YES → 認証必須グループ
    ├─ ゲームコンテンツ系か? (Stage, Gacha, PvP, Shop等)
    │   └─ YES → content_maintenance_checkも追加
    └─ NO → 標準ミドルウェアのみ (User, Game, Unit等)
```

詳細は **[middleware.md](middleware.md)** を参照してください。

---

## よくある間違い

### ❌ 間違った例

```php
// 1. メソッド名がスネークケースのまま
Route::post('/stage/continue_diamond', 'continue_diamond');

// 2. コントローラーグループを使わない
Route::post('/stage/start', [Controllers\StageController::class, 'start']);
Route::post('/stage/end', [Controllers\StageController::class, 'end']);

// 3. 不要なミドルウェアを追加
Route::middleware(['unnecessary_middleware'])->group(function () {
    // ...
});
```

### ✅ 正しい例

```php
// 1. メソッド名はキャメルケース
Route::post('/stage/continue_diamond', 'continueDiamond');

// 2. コントローラーグループを使う
Route::controller(Controllers\StageController::class)->group(function () {
    Route::post('/stage/start', 'start');
    Route::post('/stage/end', 'end');
});

// 3. 必要最小限のミドルウェアのみ
Route::middleware([
    'encrypt',
    'auth:api',
    // ... 必要なものだけ
])->group(function () {
    // ...
});
```

---

## 追加時の注意点

### 1. 既存のグループに追加する

新規エンドポイントは、既存の適切なミドルウェアグループに追加してください。新しいグループを作らないでください。

### 2. コントローラーグループの配置

同じドメインのエンドポイントは、既存のコントローラーグループに追加します。新しいドメインの場合のみ、新しいコントローラーグループを作成します。

### 3. メンテナンスチェックの判断

ゲームコンテンツに影響するエンドポイント（Stage、Gacha、PvP、Shop等）は、`content_maintenance_check` ミドルウェアグループ内に配置します。

ユーザー情報やゲーム設定取得系（User、Game等）は、メンテナンスチェック不要なグループに配置します。

---

## 実装手順

1. `api/routes/api.php` を開く
2. 適切なミドルウェアグループを選択
3. 既存のコントローラーグループを探す（同じドメインがあれば）
4. コントローラーグループ内にルート定義を追加
5. URLパスとメソッド名の対応を確認
6. ファイルを保存

次は **[controller.md](controller.md)** でController実装を進めてください。
