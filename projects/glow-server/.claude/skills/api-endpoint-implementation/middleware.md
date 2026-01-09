# ミドルウェアの選択と設定

APIエンドポイントに適切なミドルウェアを設定する方法を説明します。

## 目次

1. [ミドルウェアの種類](#ミドルウェアの種類)
2. [ミドルウェアグループ](#ミドルウェアグループ)
3. [選択フローチャート](#選択フローチャート)
4. [各ミドルウェアの詳細](#各ミドルウェアの詳細)

---

## ミドルウェアの種類

glow-serverで使用されるミドルウェア一覧：

| ミドルウェア名 | 役割 | 必須度 |
|---|---|---|
| `encrypt` | リクエスト/レスポンスの暗号化 | 全API必須 |
| `auth:api` | JWT認証 | 認証必須API |
| `block_multiple_access` | 多重アクセス防止 | 認証必須API |
| `user_status_check` | ユーザー状態チェック（BAN等） | 認証必須API |
| `client_version_check` | クライアントバージョン確認 | 認証必須API |
| `asset_version_check` | アセットバージョン確認 | 認証必須API |
| `master_version_check` | マスターデータバージョン確認 | 認証必須API |
| `cross_day_check` | 日跨ぎチェック | 認証必須API |
| `content_maintenance_check` | コンテンツメンテナンス中チェック | ゲームコンテンツ系API |

---

## ミドルウェアグループ

### グループ1: 認証不要（サインアップ/サインイン）

```php
Route::middleware([
    'encrypt',
])->group(function () {
    Route::controller(Controllers\AuthController::class)->group(function () {
        Route::post('/sign_up', 'signUp');
        Route::post('/sign_in', 'signIn');
    });
});
```

**適用対象:**
- `/sign_up` - ユーザー登録
- `/sign_in` - ログイン

**ミドルウェア:**
- `encrypt` のみ

**ファイルパス:** `api/routes/api.php:18-28`

---

### グループ2: 認証必須（標準）

ユーザー情報系、ゲーム設定取得系のAPI：

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
    Route::controller(Controllers\UserController::class)->group(function () {
        Route::get('/user/info', 'info');
        Route::post('/user/change_name', 'changeName');
    });

    Route::controller(Controllers\GameController::class)->group(function () {
        Route::get('/game/version', 'version');
        Route::post('/game/update_and_fetch', 'updateAndFetch');
    });
});
```

**適用対象:**
- User系API（ユーザー情報取得、設定変更）
- Game系API（バージョン情報、設定取得）
- Unit系API（ユニット育成）
- Item系API（アイテム使用）
- Party系API（パーティ編成）

**ミドルウェア:**
- 基本セット全て（`content_maintenance_check` を除く）

**ファイルパス:** `api/routes/api.php:31-117`

---

### グループ3: 認証必須 + コンテンツメンテナンス対象

ゲームコンテンツ系のAPI：

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
    Route::middleware(['content_maintenance_check'])->group(function () {
        Route::controller(Controllers\StageController::class)->group(function () {
            Route::post('/stage/start', 'start');
            Route::post('/stage/end', 'end');
        });

        Route::controller(Controllers\GachaController::class)->group(function () {
            Route::get('/gacha/prize', 'prize');
            Route::post('/gacha/draw/diamond', 'drawDiamond');
        });

        Route::controller(Controllers\PvpController::class)->group(function () {
            Route::post('/pvp/start', 'start');
            Route::post('/pvp/end', 'end');
        });

        Route::controller(Controllers\ShopController::class)->group(function () {
            Route::post('/shop/trade_shop_item', 'tradeShopItem');
            Route::post('/shop/purchase_pass', 'purchase');
        });
    });
});
```

**適用対象:**
- Stage系API（ステージプレイ）
- Gacha系API（ガチャ実行）
- PvP系API（対戦）
- Shop系API（課金・購入）
- AdventBattle系API（降臨バトル）

**ミドルウェア:**
- 基本セット全て + `content_maintenance_check`

**ファイルパス:** `api/routes/api.php:119-174`

---

## 選択フローチャート

```
新規エンドポイントを追加する
    ↓
認証が必要か?
├─ NO → グループ1（encryptのみ）
│   └─ 例: sign_up, sign_in
│
└─ YES → 認証必須グループ
    ↓
    ゲームコンテンツ系か?
    ├─ YES → グループ3（content_maintenance_check追加）
    │   └─ 例: Stage, Gacha, PvP, Shop, AdventBattle
    │
    └─ NO → グループ2（標準ミドルウェア）
        └─ 例: User, Game, Unit, Item, Party
```

---

## 各ミドルウェアの詳細

### encrypt

**役割:** リクエスト/レスポンスの暗号化

**適用:** 全API必須

**処理内容:**
- リクエストボディを復号化
- レスポンスボディを暗号化

---

### auth:api

**役割:** JWT認証

**適用:** 認証必須のAPI

**処理内容:**
- Authorization ヘッダーからトークンを取得
- トークンの検証（RSAキー署名検証）
- ユーザー情報を `$request->user()` で取得可能にする

---

### block_multiple_access

**役割:** 多重アクセス防止

**適用:** 認証必須のAPI

**処理内容:**
- 同一ユーザーからの同時リクエストをブロック
- Redis lockで制御

---

### user_status_check

**役割:** ユーザー状態チェック

**適用:** 認証必須のAPI

**処理内容:**
- ユーザーがBANされていないか確認
- 利用停止状態をチェック

**実装:** `api/app/Http/Middleware/UserStatusCheck.php`

---

### client_version_check

**役割:** クライアントバージョン確認

**適用:** 認証必須のAPI（サインアップ/サインインを除く）

**処理内容:**
- `X-Client-Version` ヘッダーを検証
- 最小バージョン要件をチェック
- 強制アップデート判定

---

### asset_version_check

**役割:** アセットバージョン確認

**適用:** 認証必須のAPI（サインアップ/サインインを除く）

**処理内容:**
- `X-Asset-Version` ヘッダーを検証
- アセット更新が必要か判定

---

### master_version_check

**役割:** マスターデータバージョン確認

**適用:** 認証必須のAPI（サインアップ/サインインを除く）

**処理内容:**
- マスターデータのバージョンを検証
- 更新が必要か判定

---

### cross_day_check

**役割:** 日跨ぎチェック

**適用:** 認証必須のAPI

**処理内容:**
- 前回ログイン日時と現在時刻を比較
- 日跨ぎの場合、デイリーリセット処理を実行

---

### content_maintenance_check

**役割:** コンテンツメンテナンス中チェック

**適用:** ゲームコンテンツ系API（Stage、Gacha、PvP、Shop等）

**処理内容:**
- コンテンツがメンテナンス中かチェック
- メンテナンス中の場合、エラーを返す

**実装:** `api/app/Http/Middleware/ContentMaintenanceCheck.php`

**メンテナンス対象外:**
- User系（ユーザー情報取得、設定変更）
- Game系（バージョン情報、設定取得）
- cleanup系エンドポイント

---

## 実装チェックリスト

ミドルウェア設定時に以下を確認：

- [ ] 認証が必要かどうかを判断した
- [ ] ゲームコンテンツ系APIかどうかを判断した
- [ ] 適切なミドルウェアグループに追加した
- [ ] 不要なミドルウェアを追加していない
- [ ] 既存の類似エンドポイントと同じグループに配置した

---

## 次のステップ

ミドルウェアの設定が完了したら、**[controller.md](controller.md)** でController実装を進めてください。
