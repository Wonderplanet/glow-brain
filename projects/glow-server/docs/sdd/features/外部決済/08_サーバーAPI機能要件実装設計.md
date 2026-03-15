# サーバーAPI機能要件実装設計

## 1. ドキュメント情報

- **対象機能**: 外部決済（WEB STORE連携）
- **作成日**: 2025-12-02
- **参照ドキュメント**:
  - 05_サーバーAPI要件書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

---

## 2. 実装設計概要

### 2.1 実装方針

外部決済機能は、Xsolla Web Storeからのウェブフック受信を中心とした実装となります。以下の基本方針で実装します：

1. **新規ドメインの追加**: `Domain/Shop/WebStore`サブドメインを新規作成
2. **既存パターンの踏襲**: 決済処理はShopドメイン、報酬付与はRewardドメインのパターンを参考
3. **セキュリティ優先**: Middleware層での署名検証、IP制限とメンテナンスはインフラレベル（WAF、ALB）で実装
4. **べき等性の保証**: トランザクション内での重複チェックとユニーク制約
5. **段階的実装**: W1-W5の順に実装、Bank/Adjust連携は後回し可能

### 2.2 実装の全体像

#### 新規追加コンポーネント

**ドメイン層:**
- `Domain/Shop/WebStore` - Shopドメイン配下のウェブフック処理サブドメイン
  - Controller: 既存の`ShopController`にwebstoreメソッドを追加
  - UseCases（各ウェブフック処理のビジネスロジック）
  - Services（年齢制限、国コード検証、トランザクション管理）
  - Repositories（新規テーブルへのアクセス）
  - Entities（ウェブフック関連データ構造）

**データベース:**
- 新規テーブル2件（usrDB: 2件）
- 既存テーブル変更5件（mst_store_products、usr_store_product_histories、usr_currency_paids、usr_currency_summaries、usr_users）

**ミドルウェア:**
- `XsollaSignatureVerificationMiddleware` - 署名検証

**インフラ側実装:**
- AWS WAF - IP制限（`/api/shop/webstore`のみ対象）
- ALBリスナールール - メンテナンス時503レスポンス（`/api/shop/webstore`のみ対象）

#### 既存コンポーネントへの影響

**低影響:**
- `Domain/Reward/Delegators/RewardDelegator` - 報酬付与で使用（変更不要）
- `Domain/Currency` - 通貨情報取得で参照（変更不要）
- `usr_store_product_histories` - カラム追加のみ

**中影響:**
- `usr_user_profiles` - 既存のbirth_dateカラムを使用（変更不要、利用のみ）

#### アーキテクチャ上の考慮点

1. **クリーンアーキテクチャの維持**:
   - Controller → UseCase → Service → Repository の責務分離
   - 他ドメインへの依存はDelegator経由

2. **トランザクション管理**:
   - UseCaseTraitのapplyUserTransactionChanges()を活用
   - べき等性保証のための重複チェックロジック

3. **エラーハンドリング**:
   - 永続エラー（400）と一時エラー（500）の明確な区別
   - GameException使用、ErrorCode定義

4. **非同期処理**:
   - Bank/Adjust連携失敗時のキュー再送
   - Laravel Queueを活用

---

## 3. 機能要件別実装設計

### 3.1 ウェブフックエンドポイント（W1-W5）

#### 要件 W1: ユーザー情報取得（web_store_user_validation）

##### 3.1.1 要件概要

- **要件ID:** REQ-F-001
- **実現内容:** WebStoreログイン時にアプリのユーザー情報を返却。バンダイナムコIDからユーザーを検索し、国コード・通貨コード・誕生日情報を返す。

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/api/shop/webstore`
- HTTPメソッド: POST
- 認証: 不要（署名検証Middlewareで検証）
- notification_type: `web_store_user_validation`

**リクエストパラメータ（JSON形式）:**
```json
{
  "notification_type": "web_store_user_validation",
  "user": {
    "id": "bn_user_id_12345"
  }
}
```

**リクエストパラメータ説明:**

| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| notification_type | string | ○ | 通知タイプ | 'web_store_user_validation'固定 |
| user.id | string | ○ | バンダイナムコID | - |

**レスポンス構造（JSON形式）:**
```json
{
  "user": {
    "id": "bn_user_id_12345",
    "internal_id": "usr_user_id_uuid",
    "name": "Player001",
    "level": 1,
    "birthday": "20050408",
    "birthday_month": "202001",
    "country": "JP"
  }
}
```

**レスポンスフィールド説明:**

| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| user.id | string | バンダイナムコID |
| user.internal_id | string | アプリのユーザーID |
| user.name | string | ユーザー名 |
| user.level | integer | レベル（固定値1、Xsolla仕様で必須だが実際には使用しない） |
| user.birthday | string | 誕生日（YYYYMMDD形式） |
| user.birthday_month | string | 誕生年月（YYYYMM形式）*年月しか保持していない場合にのみ利用 |
| user.country | string | 国コード（ISO 3166-1 alpha-2） |

**エラーレスポンス:**

| ステータス | エラーコード | 説明 |
|-----------|------------|------|
| 400 | WEBSTORE_USER_NOT_FOUND | ユーザーが未登録 |
| 400 | WEBSTORE_BIRTHDAY_REQUIRED | 誕生日情報が未登録 |
| 400 | WEBSTORE_COUNTRY_NOT_REGISTERED | 国コード未登録 |

**注意:** メンテナンス中（503）はALBリスナールールで返されるため、アプリケーション層のエラーコードは不要です。

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 新規ドメイン追加

**対象ドメイン:**
- ドメイン分類: 通常ドメイン
- ドメイン名: `Domain\Shop\WebStore`

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Http/Controllers/ShopController.php` - webstoreメソッドを追加
- [x] `api/app/Domain/Shop/WebStore/UseCases/UserValidationUseCase.php`
- [x] `api/app/Domain/Shop/WebStore/Services/UserValidationService.php`
- [x] `api/app/Domain/Shop/WebStore/Repositories/UsrWebstoreInfoRepository.php`
- [x] `api/app/Domain/Shop/WebStore/Entities/WebhookRequestEntity.php`
- [x] `api/app/Domain/Shop/WebStore/Entities/UserValidationResultEntity.php`
- [x] `api/app/Domain/Shop/WebStore/Models/UsrWebstoreInfo.php`
- [x] `api/app/Http/Middleware/XsollaSignatureVerificationMiddleware.php`
- [x] `api/app/Http/ResponseFactories/WebStoreResponseFactory.php`

*改修が必要な既存ファイル:*
- [x] `api/app/Domain/User/Models/UsrUserProfile.php` - 既存のgetBirthDate()、hasBirthDate()を使用

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ShopController | webstore() | W1エンドポイント | リクエスト受信、バリデーション、UseCase呼び出し |
| UserValidationUseCase | exec() | ユーザー検証の実行 | ビジネスロジックのオーケストレーション |
| UserValidationService | validateUser() | ユーザー存在確認 | bn_user_idからusrUserを検索 |
| UsrWebstoreInfoRepository | findByUsrUserId() | 国コード・通貨取得 | usr_webstore_infosからデータ取得 |
| UsrWebstoreInfoRepository | createOrGet() | 初回登録処理 | 未登録なら新規作成 |

**Entity設計:**

- **WebhookRequestEntity**: リクエスト全体の構造
  - Entityタイプ: Plain Entity
  - 主要プロパティ: `notificationType`, `user`, `customParameters`
  - 用途: リクエストボディの型安全な扱い

- **UserValidationResultEntity**: レスポンス構造
  - Entityタイプ: Plain Entity
  - 主要プロパティ: `id`, `internalId`, `name`, `level`, `birthday`, `birthdayMonth`, `country`
  - 用途: レスポンス生成
  - 注意: `level`は固定値1、`birthdayMonth`はYYYYMM形式

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）
- [x] テーブル構造変更（既存テーブル変更）

**新規テーブル作成: usr_webstore_infos**

*テーブル名:* `usr_webstore_infos`
*DB:* usr (TiDB)

*CREATE TABLE文:*
```sql
CREATE TABLE usr_webstore_infos (
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'ユーザーID',
    country_code VARCHAR(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2形式の国コード（例: JP, US, GB）',
    registered_at TIMESTAMP NOT NULL COMMENT '国コード登録日時',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (usr_user_id),
    FOREIGN KEY (usr_user_id) REFERENCES usr_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WebStore国コード情報';
```

*テーブル設計の注意事項:*
- PRIMARY KEYはusr_user_id（1:1関係）
- 一度登録した国コードは更新しない制約（アプリケーション層で実装）
- registered_atで国コード登録日時を記録
- 通貨コードは管理しない（Apple/Googleのストアフロントから動的に取得）

*カラム説明:*

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID |
| country_code | VARCHAR(2) | NOT NULL | - | 国コード（ISO 3166-1 alpha-2） |
| registered_at | TIMESTAMP | NOT NULL | - | 国コード登録日時 |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

*重要な仕様:*
- 国コードはApple/Googleのストアフロント情報から取得（デバイス設定ではない）
  - Android: BillingConfig APIから取得
  - iOS: SKPaymentQueue.storefrontから取得
- 一度登録した国コードは更新しない（仕様書P.28要件）
- ユーザーごとに1レコードのみ（usr_user_idがPK）

*利用箇所:*
- `POST /game/update_and_fetch`: 国コード初回登録（country_codeパラメータが送信された場合のみ）
- W1（ユーザー情報取得）: country_code存在チェック、レスポンスで返却
- W5（注文支払い成功）: is_country_mismatchをログ記録のみに使用（エラーにはしない）

*注記:*
- `birthday`情報は`usr_user_profiles.birth_date`カラムに既存（暗号化済み）
- `UsrUserProfile::getBirthDate()`で復号化してint型（YYYYMMDDフォーマット）で取得
- `UsrUserProfile::hasBirthDate()`で誕生日の有無を確認
- 国コード情報は`usr_webstore_infos`テーブルで管理（`usr_users`テーブルには追加しない）

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| `ErrorCode::WEBSTORE_USER_NOT_FOUND` | WEBSTORE_USER_NOT_FOUND | bn_user_idに対応するユーザーが存在しない | User not found. Please login to the app first. | 400エラー返却 |
| `ErrorCode::WEBSTORE_BIRTHDAY_REQUIRED` | WEBSTORE_BIRTHDAY_REQUIRED | usr_user_profiles.birth_dateがNULL | Birthday information is required. Please register your birthday in the profile settings. | 400エラー返却 |
| `ErrorCode::WEBSTORE_COUNTRY_NOT_REGISTERED` | WEBSTORE_COUNTRY_NOT_REGISTERED | usr_webstore_infos.country_codeがNULL | Country code not registered. Please update the app and try again. | 400エラー返却 |

**注意:** メンテナンス中（503）はALBリスナールールで返されるため、アプリケーション層のエラーコードは不要です。

**エラーハンドリングの実装方針:**
- エラーはUserValidationServiceでthrow
- GameExceptionを使用
- UseCaseでcatchして適切なHTTPステータスを返却

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- usr_webstore_infos取得時のN+1問題回避: 単一クエリで取得
- usr_users.bn_user_idにインデックスが既存（MUL確認済み）
- デバッグ上書き確認は都度実行（頻度低いため許容）

**セキュリティ考慮点:**
- 署名検証: XsollaSignatureVerificationMiddlewareで実装
- リクエストボディ全体をログ記録（デバッグ・監査用）
- 誕生日情報の扱い: NULLの場合はエラー返却（情報漏洩防止）

**データ整合性:**
- usr_webstore_infosの初回登録はトランザクション不要（単一INSERT）
- registered_atで初回登録日時を記録し、更新不可とする
- デバッグ上書きが存在する場合はそちらを優先

**既存実装との整合性:**
- 類似機能: 認証系処理（AuthDelegator）を参考
- ユーザー情報取得パターンを踏襲
- レスポンス形式はXsolla仕様に準拠（既存APIとは異なる）

---

#### 要件 W2: 決済事前確認（web_store_payment_validation）

##### 3.2.1 要件概要

- **要件ID:** REQ-F-002
- **実現内容:** 購入前にユーザーの購入権限を検証。年齢制限、購入回数制限をチェックし、トランザクションIDを発行。

##### 3.2.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/api/shop/webstore`
- HTTPメソッド: POST
- 認証: 不要（署名検証Middlewareで検証）
- notification_type: `web_store_payment_validation`

**リクエストパラメータ（JSON形式）:**
```json
{
  "notification_type": "web_store_payment_validation",
  "user": {
    "id": "bn_user_id_12345",
    "birthday": "20050408",
    "country": "JP"
  },
  "custom_parameters": {
    "internal_id": "usr_user_id_uuid",
    "store_code": "JP",
    "country_from_ip": "JP",
    "is_country_mismatch": false
  },
  "purchase": {
    "items": [
      {
        "sku": "diamond_100",
        "type": "virtual_good",
        "amount": 990
      }
    ]
  },
  "order": {
    "amount": 990,
    "currency": "JPY"
  }
}
```

**リクエストパラメータ説明:**

| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| notification_type | string | ○ | 通知タイプ | 'web_store_payment_validation'固定 |
| user.id | string | ○ | バンダイナムコID | - |
| user.birthday | string | ○ | 誕生日 | YYYYMMDD形式 |
| user.country | string | ○ | 国コード | ISO 3166-1 alpha-2 |
| custom_parameters.internal_id | string | ○ | アプリのユーザーID | - |
| custom_parameters.country_from_ip | string | - | アクセス元国コード | - |
| custom_parameters.is_country_mismatch | boolean | - | 国コード不一致フラグ | - |
| purchase.items | array | ○ | 購入アイテム一覧 | - |
| purchase.items[].sku | string | ○ | 商品SKU | - |
| purchase.items[].type | string | ○ | アイテムタイプ | - |
| purchase.items[].amount | int | ○ | 金額 | - |
| order.amount | int | ○ | 合計金額 | - |
| order.currency | string | - | 通貨コード | - |

**レスポンス構造（JSON形式）:**
```json
{
  "transaction_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**レスポンスフィールド説明:**

| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| transaction_id | string | トランザクションID（UUID v4） |

**エラーレスポンス:**

| ステータス | エラーコード | 説明 |
|-----------|------------|------|
| 400 | WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR | 未成年で有料商品を購入しようとした |
| 400 | WEBSTORE_PURCHASE_COUNT_LIMIT | 購入回数上限 |
| 400 | WEBSTORE_NO_VIRTUAL_GOOD_ITEMS | virtual_goodが存在しない |

**注意:** メンテナンス中（503）はALBリスナールールで返されるため、アプリケーション層のエラーコードは不要です。

##### 3.2.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修（WebStoreドメイン内に追加）

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Shop/WebStore/UseCases/PaymentValidationUseCase.php`
- [x] `api/app/Domain/Shop/WebStore/Services/PaymentValidationService.php`
- [x] `api/app/Domain/Shop/WebStore/Services/PurchaseLimitService.php`
- [x] `api/app/Domain/Shop/WebStore/Services/TransactionService.php`
- [x] `api/app/Domain/Shop/WebStore/Repositories/UsrWebstoreTransactionRepository.php`
- [x] `api/app/Domain/Shop/WebStore/Entities/PaymentValidationResultEntity.php`
- [x] `api/app/Domain/Shop/WebStore/Models/UsrWebstoreTransaction.php`
- [x] `api/app/Domain/Shop/WebStore/Enums/TransactionStatus.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ShopController | webstore() | W2エンドポイント | リクエスト受信、UseCase呼び出し |
| PaymentValidationUseCase | exec() | 決済事前確認の実行 | ビジネスロジックのオーケストレーション |
| PaymentValidationService | filterVirtualGoodItems() | virtual_goodフィルタ | items配列からtype=virtual_goodのみ抽出 |
| AgeRestrictionService | checkPurchaseRestriction() | 年齢制限チェック | 有料/無料判定、年齢制限判定 |
| PurchaseLimitService | checkPurchaseLimit() | 購入回数制限チェック | usr_store_productsで回数確認（既存テーブル使用） |
| TransactionService | issueTransactionId() | トランザクションID発行 | UUID v4生成、usr_webstore_transactionsに保存 |

**Entity設計:**

- **PaymentValidationResultEntity**: レスポンス構造
  - Entityタイプ: Plain Entity
  - 主要プロパティ: `transactionId`
  - 用途: レスポンス生成

##### 3.2.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）

**注記: 購入回数管理について**

外部決済（WebStore）の購入回数管理には、既存の`usr_store_products`テーブルを使用します。このテーブルは、Apple/Google/WebStore全プラットフォームで購入回数を共通管理するために設計されています。新規テーブル `usr_external_payment_products` は作成しません。

参照: 07_サーバーAPI設計書.md「usr_store_products（既存・変更なし）」セクション

**新規テーブル作成: usr_webstore_transactions**

*テーブル名:* `usr_webstore_transactions`
*DB:* usr (TiDB)

**目的:**
WebStoreトランザクションの状態、アイテム付与結果、外部連携結果を一元管理する状態管理テーブル。ステータス更新を伴うためユーザーテーブル（usr）として定義。

*CREATE TABLE文:*
```sql
CREATE TABLE usr_webstore_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'レコードID',
    usr_user_id VARCHAR(255) NOT NULL COMMENT 'usr_users.usr_user_id',
    transaction_id VARCHAR(255) NOT NULL COMMENT 'トランザクションID（UUID v4）',
    order_id VARCHAR(255) DEFAULT NULL COMMENT 'Xsollaの注文ID（決済完了後に設定）',
    is_sandbox TINYINT NOT NULL DEFAULT 0 COMMENT 'テスト決済フラグ（0: 本番, 1: テスト）',
    status VARCHAR(50) NOT NULL COMMENT 'ステータス（pending, completed, failed）',
    error_code VARCHAR(100) DEFAULT NULL COMMENT 'エラーコード（最小限の情報のみ）',
    item_grant_status VARCHAR(50) DEFAULT NULL COMMENT 'アイテム付与ステータス（success, failed_temporary, failed_permanent）',
    bank_status VARCHAR(50) DEFAULT NULL COMMENT 'Bank連携ステータス（pending, success, failed）',
    adjust_status VARCHAR(50) DEFAULT NULL COMMENT 'Adjust連携ステータス（pending, success, failed）',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_transaction_id (transaction_id),
    INDEX idx_usr_user_id (usr_user_id),
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WebStoreトランザクション状態管理';
```

*テーブル設計の注意事項:*
- PRIMARY KEYはAUTO_INCREMENTのid
- transaction_idにはユニーク制約（UUID v4で生成）
- W2（決済事前確認）でレコード挿入（status: 'pending'、transaction_idのみ設定）
- W5（注文支払い成功）でorder_id設定とステータス更新
- 詳細情報（商品ID、金額、通貨等）はorder_idからusr_store_product_historiesを検索
- エラー詳細はerror_codeで識別、詳細はアプリケーションログを参照
- リクエストトレースはtransaction_idでアプリケーションログ/Nginxログを検索

*カラム説明:*

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | BIGINT UNSIGNED | NOT NULL | AUTO_INCREMENT | レコードID |
| usr_user_id | VARCHAR(255) | NOT NULL | - | ユーザーID |
| transaction_id | VARCHAR(255) | NOT NULL | - | トランザクションID（UUID v4） |
| order_id | VARCHAR(255) | NULL | NULL | Xsolla注文ID（決済完了後に設定） |
| is_sandbox | TINYINT | NOT NULL | 0 | テスト決済フラグ（0: 本番, 1: テスト） |
| status | VARCHAR(50) | NOT NULL | - | ステータス（'pending', 'completed', 'failed'） |
| error_code | VARCHAR(100) | NULL | NULL | エラーコード（最小限の情報のみ） |
| item_grant_status | VARCHAR(50) | NULL | NULL | アイテム付与ステータス（'success', 'failed_temporary', 'failed_permanent'） |
| bank_status | VARCHAR(50) | NULL | NULL | Bank連携ステータス（'pending', 'success', 'failed'） |
| adjust_status | VARCHAR(50) | NULL | NULL | Adjust連携ステータス（'pending', 'success', 'failed'） |
| created_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**statusの説明:**
- `pending`: トランザクション発行済み、決済未完了
- `completed`: 決済完了、アイテム付与成功
- `failed`: 決済失敗（一時エラー・永続エラー含む。詳細はerror_codeやitem_grant_statusで判断）

**管理内容:**
1. **トランザクション状態**: `status`, `transaction_id`, `order_id`
2. **アイテム付与状態**: `item_grant_status`
3. **外部連携状態**: `bank_status`, `adjust_status`

**レコード記録タイミング:**
- **W2（決済事前確認）**: トランザクションID発行時（status: 'pending'）
- **W5（注文支払い成功）**: 処理完了時（status更新、アイテム付与・外部連携結果を記録）

**利用箇所:**
- W2（決済事前確認）: レコード挿入（status: 'pending', transaction_idのみ設定）
- W5（注文支払い成功）: order_id設定、ステータス更新（status, item_grant_status, bank_status, adjust_status, error_code）

**運用メリット:**
- 必要最小限の状態管理に特化
- 詳細情報は他テーブル/ログから取得する明確な分離
- データ保持期間管理が1テーブルで完結

参照: 07_サーバーAPI設計書.md「usr_webstore_transactions（新規テーブル）」セクション

**既存テーブル変更: mst_store_products**

*テーブル名:* `mst_store_products`
*DB:* mst (TiDB)

**変更内容:**
3プラットフォーム共通の商品IDマスターとして使用。WebStore商品SKU管理のため、`product_id_webstore`カラムを追加します。

*追加カラム:*
```sql
ALTER TABLE mst_store_products
ADD COLUMN product_id_webstore VARCHAR(255) NULL COMMENT 'WebStoreのSKU（空文字: モバイルアプリ専用商品）',
MODIFY COLUMN product_id_ios VARCHAR(255) NULL,
MODIFY COLUMN product_id_android VARCHAR(255) NULL,
ADD INDEX idx_product_id_webstore (product_id_webstore);
```

*運用ルール:*
- WebStore専用商品は`product_id_ios`, `product_id_android`を空文字('')にする
- モバイルアプリ専用商品は`product_id_webstore`を空文字('')にする
- `product_id_ios`, `product_id_android`をNULL許容に変更

*利用箇所:*
- W5（注文支払い成功）: `items[].sku`から`product_id_webstore`で検索し、`mst_store_products.id`を取得

参照: 07_サーバーAPI設計書.md「mst_store_products（既存テーブル変更）」セクション

**既存テーブル変更: usr_currency_paids**

*テーブル名:* `usr_currency_paids`
*DB:* usr (TiDB)

**変更内容:**
有償一次通貨（有償通貨）の残高管理テーブル。WebStore購入の有償通貨を管理するため、billing_platformカラムを拡張。

*変更箇所:*
- `billing_platform`カラムの値に**'webstore'**を追加（既存: 'google', 'apple'）
- WebStore購入の有償通貨は独立したプラットフォームとして管理

*クロスプラットフォーム消費:*
- **WebStore購入の有償通貨（billing_platform='webstore'）は、iOS/Androidどちらのプラットフォームからでも消費可能**
- Apple/Google購入の有償通貨は従来通り、購入したプラットフォームでのみ消費可能
- 消費優先順序:
  1. 無償一次通貨（ingame → reward → bonus）
  2. **WebStore購入の有償通貨（billing_platform='webstore'）** ← iOS/Androidどちらからでも消費可能
  3. プラットフォーム固有の有償通貨（Apple/Google購入分）

*利用箇所:*
- W5（注文支払い成功）: 有償通貨購入時に`billing_platform='webstore'`でレコード挿入

*ALTER TABLE文:*
（注：billing_platformカラムがENUM型の場合のみ以下を実行）
```sql
ALTER TABLE usr_currency_paids
MODIFY COLUMN billing_platform ENUM('apple','google','webstore') NOT NULL;
```
（注：billing_platformカラムがVARCHAR型の場合は、ALTER不要。アプリケーション層で'webstore'の値を挿入するのみ）

参照: 07_サーバーAPI設計書.md「usr_currency_paids（既存テーブル変更）」セクション

**既存テーブル変更: usr_currency_summaries**

*テーブル名:* `usr_currency_summaries`
*DB:* usr (TiDB)

**変更内容:**
有償・無償一次通貨の所持数サマリーテーブル。WebStore購入の有償通貨をクロスプラットフォーム消費可能にするため、新しいカラムを追加。

*追加カラム:*
```sql
ALTER TABLE usr_currency_summaries
ADD COLUMN paid_amount_share BIGINT NOT NULL DEFAULT 0 COMMENT 'プラットフォーム間で共有可能な有償通貨残高（iOS/Androidどちらからでも消費可能）';
```

*プラットフォーム間共有消費の実現方法:*
1. **WebStore購入時**: `paid_amount_share`カラムに有償通貨を加算
2. **消費時（iOS/Androidどちらでも）**: `paid_amount_share`から優先的に消費
3. **残高取得時**: ユーザーの現在のプラットフォームに関係なく、`paid_amount_share`を合算して表示

*実装への影響:*
- `CurrencyService::useCurrency()`メソッドの消費ロジック変更が必要
- `UsrCurrencySummaryEntity`に`getPaidAmountShare()`メソッド追加が必要
- `PlatformPaidTrait::getPlatformPaidAmount()`メソッドの変更が必要（全プラットフォームで`paid_amount_share`を加算）

*利用箇所:*
- W5（注文支払い成功）: 有償通貨購入時に`paid_amount_share`を更新
- 通貨消費API: `paid_amount_share`を優先的に消費

参照: 07_サーバーAPI設計書.md「usr_currency_summaries（既存テーブル変更）」セクション

**既存テーブル変更: usr_store_product_histories**

*テーブル名:* `usr_store_product_histories`
*DB:* usr (TiDB)

**変更内容:**
購入履歴テーブル。外部決済固有の情報を追加。

*追加カラム:*
```sql
ALTER TABLE usr_store_product_histories
ADD COLUMN order_id VARCHAR(255) NULL COMMENT 'Xsollaの注文ID（外部決済の場合のみ設定）',
ADD COLUMN invoice_id VARCHAR(255) NULL COMMENT 'Xsollaの請求書ID（無料アイテム・クーポン交換の場合はNULL）',
ADD COLUMN transaction_id VARCHAR(255) NULL COMMENT 'W2で発行したトランザクションID（外部決済の場合のみ設定）',
ADD UNIQUE INDEX idx_order_id (order_id),
MODIFY COLUMN billing_platform ENUM('apple','google','webstore') NOT NULL,
MODIFY COLUMN currency_code VARCHAR(16) NULL;
```

*追加カラムの説明:*
- `order_id`: Xsollaの注文ID。外部決済の場合のみ設定。べき等性保証のためユニークインデックスを設定。
- `invoice_id`: Xsollaの請求書ID。無料アイテム・クーポン交換の場合はNULL。
- `transaction_id`: W2（決済事前確認）で発行したトランザクションID。外部決済の場合のみ設定。

*変更内容:*
- `invoice_id`, `currency_code`をNULL許容に変更（無料アイテム・クーポン交換対応）
- `billing_platform`に'webstore'を追加

*利用箇所:*
- W5（注文支払い成功）: レコード挿入、べき等性チェック

参照: 07_サーバーAPI設計書.md「usr_store_product_histories（既存テーブル変更）」セクション

##### 3.2.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| `ErrorCode::WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR` | WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR | 未成年で有料商品を購入しようとした（年齢が購入可能年齢未満かつorder.amount > 0、現在の基準: 18歳） | Purchase not allowed for minors. | 400エラー返却 |
| `ErrorCode::WEBSTORE_PURCHASE_COUNT_LIMIT` | WEBSTORE_PURCHASE_COUNT_LIMIT | 購入回数上限に達した | Purchase count limit reached. | 400エラー返却 |
| `ErrorCode::WEBSTORE_NO_VIRTUAL_GOOD_ITEMS` | WEBSTORE_NO_VIRTUAL_GOOD_ITEMS | items配列にvirtual_goodが存在しない | No virtual goods in purchase items. | 400エラー返却 |

**エラーハンドリングの実装方針:**
- エラーはPaymentValidationService、AgeRestrictionService、PurchaseLimitServiceでthrow
- トランザクションIDは発行前にエラーチェックを完了

##### 3.2.6 実装上の注意事項

**パフォーマンス考慮点:**
- usr_store_products取得: 商品ごとに個別取得（購入回数制限チェック、既存テーブル使用）
- トランザクションID生成: UUID v4（衝突リスク極小）

**セキュリティ考慮点:**
- トランザクションID有効期限チェック（created_atで判定）
- 国コード不一致のログ記録: 不正購入の追跡に使用

**データ整合性:**
- トランザクションID発行はトランザクション不要（単一INSERT）
- status='pending'で保存、W5で'completed'に更新

**既存実装との整合性:**
- 類似機能: ガチャの購入前確認処理を参考
- 購入回数制限は外部決済専用（既存のアプリ内課金とは別管理）

---

#### 要件 W3: ユーザー検証（user_validation - Xsolla）

##### 3.3.1 要件概要

- **要件ID:** REQ-F-003
- **実現内容:** Xsollaが購入直前にユーザーの存在を確認。internal_idでユーザーを検索し、存在確認のみ実施。

##### 3.3.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/api/shop/webstore`
- HTTPメソッド: POST
- 認証: 不要（署名検証Middlewareで検証）
- notification_type: `user_validation`

**リクエストパラメータ（JSON形式）:**
```json
{
  "notification_type": "user_validation",
  "custom_parameters": {
    "internal_id": "usr_user_id_uuid"
  }
}
```

**リクエストパラメータ説明:**

| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| notification_type | string | ○ | 通知タイプ | 'user_validation'固定 |
| custom_parameters.internal_id | string | ○ | アプリのユーザーID | - |

**レスポンス構造（JSON形式）:**
```json
{}
```

*成功時は空のJSONオブジェクトを返却（200 OK）*

**エラーレスポンス:**

| ステータス | エラーコード | 説明 |
|-----------|------------|------|
| 400 | WEBSTORE_USER_NOT_FOUND | ユーザーが存在しない |

##### 3.3.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修（WebStoreドメイン内に追加）

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Shop/WebStore/UseCases/UserVerificationUseCase.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ShopController | webstore() | W3エンドポイント | シンプルな存在確認 |
| UserVerificationUseCase | exec() | ユーザー存在確認 | UsrUserRepositoryで検索 |

##### 3.3.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

*既存テーブル（usr_users）のみ使用、新規テーブル不要*

##### 3.3.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| `ErrorCode::WEBSTORE_USER_NOT_FOUND` | WEBSTORE_USER_NOT_FOUND | internal_idに対応するユーザーが存在しない | User not found. | 400エラー返却 |

##### 3.3.6 実装上の注意事項

**パフォーマンス考慮点:**
- usr_users.idによる検索（PRIMARY KEY）
- 単純な存在確認のみ、追加クエリ不要

**セキュリティ考慮点:**
- 署名検証Middleware必須
- ユーザー情報は返却しない（存在確認のみ）

**既存実装との整合性:**
- 類似機能: AuthDelegatorのユーザー存在確認を参考
- シンプルな実装（UseCase内で直接Repository呼び出し）

---

#### 要件 W4: 支払い（payment - Xsolla）

##### 3.4.1 要件概要

- **要件ID:** REQ-F-004
- **実現内容:** 決済実行時の支払い通知を受け取る。sandboxモードを判定し、ログに記録。

##### 3.4.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/api/shop/webstore`
- HTTPメソッド: POST
- 認証: 不要（署名検証Middlewareで検証）
- notification_type: `payment`

**リクエストパラメータ（JSON形式）:**
```json
{
  "notification_type": "payment",
  "transaction": {
    "id": "xsolla_transaction_id_12345",
    "dry_run": 0
  }
}
```

**リクエストパラメータ説明:**

| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| notification_type | string | ○ | 通知タイプ | 'payment'固定 |
| transaction.id | string | ○ | トランザクションID | - |
| transaction.dry_run | integer | ○ | テスト決済フラグ | 0 or 1 |

**レスポンス構造（JSON形式）:**
```json
{}
```

*成功時は空のJSONオブジェクトを返却（200 OK）*

**エラーレスポンス:**

| ステータス | エラーコード | 説明 |
|-----------|------------|------|
| 500 | WEBSTORE_INTERNAL_ERROR | 一時エラー（リトライ可能） |

##### 3.4.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修（WebStoreドメイン内に追加）

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Shop/WebStore/UseCases/PaymentUseCase.php`

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ShopController | webstore() | W4エンドポイント | 支払い通知受信 |
| PaymentUseCase | exec() | 支払い処理 | sandboxモード判定、ログ記録 |

##### 3.4.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

*既存テーブル（usr_webstore_transactions）のみ使用*

##### 3.4.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| `ErrorCode::WEBSTORE_INTERNAL_ERROR` | WEBSTORE_INTERNAL_ERROR | 内部エラー | Internal error occurred. | 500エラー返却 |

##### 3.4.6 実装上の注意事項

**パフォーマンス考慮点:**
- ログ記録のみ、ビジネスロジックなし
- 高速なレスポンス返却

**セキュリティ考慮点:**
- 署名検証Middleware必須
- transaction.dry_runでテスト決済を明確に区別

**既存実装との整合性:**
- シンプルなログ記録処理
- W5（注文支払い成功）で実際のアイテム付与を実施

---

#### 要件 W5: 注文支払い成功（order_paid - Xsolla）

##### 3.5.1 要件概要

- **要件ID:** REQ-F-005
- **実現内容:** 決済完了後にアイテムを付与。Bank/Adjustに購入データを送信。べき等性を保証。

##### 3.5.2 API設計

**新規API追加 / 既存API改修:**
- [x] 新規API追加

**対象エンドポイント:**
- エンドポイント: `/api/shop/webstore`
- HTTPメソッド: POST
- 認証: 不要（署名検証Middlewareで検証）
- notification_type: `order_paid`

**リクエストパラメータ（JSON形式）:**
```json
{
  "notification_type": "order_paid",
  "order": {
    "id": "order_12345",
    "invoice_id": "invoice_67890",
    "currency": "JPY",
    "amount": 990,
    "mode": "live"
  },
  "items": [
    {
      "sku": "diamond_100",
      "type": "virtual_good",
      "amount": 990
    }
  ],
  "custom_parameters": {
    "internal_id": "usr_user_id_uuid",
    "transaction_id": "550e8400-e29b-41d4-a716-446655440000",
    "user_ip": "192.168.1.1",
    "store_code": "JP",
    "country_from_ip": "JP",
    "is_country_mismatch": false
  }
}
```

**リクエストパラメータ説明:**

| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| notification_type | string | ○ | 通知タイプ | 'order_paid'固定 |
| order.id | string | ○ | 注文ID | - |
| order.invoice_id | string | - | 請求ID | 無料アイテムの場合NULL |
| order.currency | string | - | 通貨コード | 無料アイテムの場合NULL |
| order.amount | int | ○ | 合計金額 | - |
| order.mode | string | ○ | 決済モード | 'live' or 'sandbox' |
| items | array | ○ | 購入アイテム一覧 | - |
| items[].sku | string | ○ | 商品SKU | - |
| items[].type | string | ○ | アイテムタイプ | - |
| items[].amount | int | ○ | 金額 | - |
| custom_parameters.internal_id | string | ○ | アプリのユーザーID | - |
| custom_parameters.transaction_id | string | - | トランザクションID | W2で発行 |
| custom_parameters.user_ip | string | - | クライアントIP | - |
| custom_parameters.country_from_ip | string | - | アクセス元国コード | - |
| custom_parameters.is_country_mismatch | boolean | - | 国コード不一致フラグ | - |

**レスポンス構造（JSON形式）:**
```json
{
  "result": "success",
  "order_id": "order_12345"
}
```

**レスポンスフィールド説明:**

| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | string | 処理結果（"success"固定） |
| order_id | string | 注文ID |

**エラーレスポンス:**

| ステータス | エラーコード | 説明 |
|-----------|------------|------|
| 400 | WEBSTORE_DUPLICATE_ORDER | 同一order.idで重複リクエスト |
| 400 | WEBSTORE_TRANSACTION_NOT_FOUND | トランザクションIDが見つからない、または有効期限切れ |
| 400 | WEBSTORE_NO_VIRTUAL_GOOD_ITEMS | virtual_goodが存在しない |
| 500 | WEBSTORE_INTERNAL_ERROR | 一時エラー（リトライ可能） |

##### 3.5.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [x] 既存ドメイン改修（WebStoreドメイン内に追加）

**ファイル構成:**

*新規作成が必要なファイル:*
- [x] `api/app/Domain/Shop/WebStore/UseCases/OrderPaidUseCase.php`
- [x] `api/app/Domain/Shop/WebStore/Services/OrderPaidService.php`
- [x] `api/app/Domain/Shop/WebStore/Services/IdempotencyService.php`
- [x] `api/app/Domain/Shop/WebStore/Services/BankIntegrationService.php`
- [x] `api/app/Domain/Shop/WebStore/Services/AdjustIntegrationService.php`
- [x] `api/app/Domain/Shop/WebStore/Jobs/RetryBankIntegrationJob.php`
- [x] `api/app/Domain/Shop/WebStore/Jobs/RetryAdjustIntegrationJob.php`
- [x] `api/app/Domain/Shop/WebStore/Repositories/UsrStoreProductHistoryRepository.php`

*改修が必要な既存ファイル:*
- [x] `api/app/Domain/Resource/Usr/Models/UsrStoreProductHistory.php` - 新規カラム追加（order_id、invoice_id、transaction_id）

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| ShopController | webstore() | W5エンドポイント | 注文支払い成功受信 |
| OrderPaidUseCase | exec() | 注文処理の実行 | トランザクション管理 |
| IdempotencyService | checkDuplicateOrder() | 重複チェック | order_idで重複確認 |
| OrderPaidService | processOrder() | 注文処理 | アイテム付与、履歴保存 |
| OrderPaidService | grantItems() | アイテム付与処理 | 商品タイプに応じた付与 |
| PaymentValidationService | filterVirtualGoodItems() | virtual_goodフィルタ | W2と同じロジック |
| TransactionService | validateTransaction() | トランザクション検証 | 有効期限チェック |
| BillingDelegator | purchasedForWebStore() | 有償通貨付与 | usr_currency_paidsに挿入 |
| RewardDelegator | sendRewards() | その他アイテム付与 | 既存報酬送付機能使用 |
| BankIntegrationService | sendPurchaseData() | Bank連携 | 購入データ送信 |
| AdjustIntegrationService | sendPurchaseEvent() | Adjust連携 | イベント送信 |
| UsrStoreProductHistoryRepository | create() | 購入履歴保存 | usr_store_product_historiesに保存 |
| RetryBankIntegrationJob | handle() | Bank再送Job | 失敗時のリトライ |
| RetryAdjustIntegrationJob | handle() | Adjust再送Job | 失敗時のリトライ |

**Entity設計:**

- **OrderPaidResultEntity**: レスポンス構造
  - Entityタイプ: Plain Entity
  - 主要プロパティ: `result`, `orderId`
  - 用途: レスポンス生成

**アイテム付与処理の実装詳細:**

OrderPaidService::grantItems()では、商品タイプに応じて異なるDelegatorを使用してアイテムを付与します。

*処理フロー:*
1. items配列からvirtual_goodタイプのアイテムを抽出
2. 各アイテムのSKUから`mst_store_products.product_id_webstore`で検索
3. `mst_store_products.id`から有効な`opr_product`を取得（`start_date <= now < end_date`）
4. `opr_products.product_type`から商品タイプを判定
5. 商品タイプに応じた付与処理を実行

*商品タイプ別の処理:*

**有償通貨（Diamond）の場合:**
- `BillingDelegator::purchasedForWebStore()`を呼び出し
- `usr_currency_paids`テーブルに挿入
- `billing_platform='webstore'`を設定
- パラメータ例:
  ```php
  $this->billingDelegator->purchasedForWebStore(
      $usrUserId,
      $productId,
      $amount,
      $currencyCode,
      $orderId,
      $invoiceId,
      $transactionId
  );
  ```

**その他アイテムの場合:**
- `RewardDelegator::sendRewards()`を呼び出し
- 既存の報酬送付機能を使用
- 各種アイテム（キャラクター、装備、素材など）を付与

*既存実装との整合性:*
- アプリ内課金では`BillingDelegator::purchased()`を使用（コールバック方式）
- WebStoreでは`BillingDelegator::purchasedForWebStore()`を使用（直接呼び出し方式）
- どちらも最終的に`usr_currency_paids`に`billing_platform`を区別して記録

##### 3.5.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [x] テーブル構造追加（新規テーブル作成）
- [x] テーブル構造変更（既存テーブル変更）

**既存テーブル変更: usr_store_product_histories**

*テーブル名:* `usr_store_product_histories`

*ALTER TABLE文:*
```sql
-- 外部決済固有のカラム追加
ALTER TABLE usr_store_product_histories
ADD COLUMN order_id VARCHAR(255) DEFAULT NULL COMMENT 'Xsolla注文ID' AFTER receipt_unique_id,
ADD COLUMN invoice_id VARCHAR(255) DEFAULT NULL COMMENT 'Xsolla請求ID' AFTER order_id,
ADD COLUMN transaction_id VARCHAR(255) DEFAULT NULL COMMENT 'トランザクションID' AFTER invoice_id;

-- order_idにユニークインデックス追加（べき等性保証）
ALTER TABLE usr_store_product_histories
ADD UNIQUE INDEX idx_order_id (order_id);

-- 無料アイテム対応のためNULL許容に変更
ALTER TABLE usr_store_product_histories
MODIFY COLUMN currency_code VARCHAR(16) NULL;
```

*変更内容説明:*
- カラム追加: `order_id` - Xsolla注文ID（べき等性キー）
- カラム追加: `invoice_id` - Xsolla請求ID（無料アイテムの場合NULL）
- カラム追加: `transaction_id` - トランザクションID（W2で発行したID）
- インデックス追加: `idx_order_id` - 重複チェック用ユニークインデックス
- カラム変更: `currency_code` - 無料アイテムの場合NULLを許容

##### 3.5.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| `ErrorCode::WEBSTORE_DUPLICATE_ORDER` | WEBSTORE_DUPLICATE_ORDER | 同一order.idで重複リクエスト | Duplicate order detected. | 400エラー、1回目の処理結果を返却 |
| `ErrorCode::WEBSTORE_TRANSACTION_NOT_FOUND` | WEBSTORE_TRANSACTION_NOT_FOUND | トランザクションIDが見つからない、または有効期限切れ | Transaction not found. | 400エラー返却 |
| `ErrorCode::WEBSTORE_NO_VIRTUAL_GOOD_ITEMS` | WEBSTORE_NO_VIRTUAL_GOOD_ITEMS | items配列にvirtual_goodが存在しない | No virtual goods in purchase items. | 400エラー返却 |
| `ErrorCode::WEBSTORE_ITEM_GRANT_FAILED_PERMANENT` | WEBSTORE_ITEM_GRANT_FAILED_PERMANENT | アイテム付与失敗（永続エラー） | Item grant failed. Contact support. | ログ記録 + アラート + 200返却 |
| `ErrorCode::WEBSTORE_INTERNAL_ERROR` | WEBSTORE_INTERNAL_ERROR | 内部エラー（一時エラー） | Internal error occurred. | 500エラー返却 |

**エラーハンドリングの実装方針:**
- べき等性: order_idで重複チェック、既存レコードがあれば1回目の結果を返却
- トランザクションID検証: usr_webstore_transactionsで存在確認、有効期限チェック
- アイテム付与失敗（一時エラー）: 500エラーを返してXsollaにリトライさせる
- アイテム付与失敗（永続エラー）: ログ記録 + アラート通知 + 200返却（手動対応）
- Bank/Adjust連携失敗: ログ記録 + 非同期再送（購入成功には影響しない）

##### 3.5.6 実装上の注意事項

**パフォーマンス考慮点:**
- 重複チェック: order_idのユニークインデックスで高速検索
- トランザクション管理: applyUserTransactionChanges()使用
- Bank/Adjust連携: 非同期Job化（購入処理をブロックしない）

**セキュリティ考慮点:**
- 署名検証Middleware必須
- order_idのユニーク制約で重複防止
- トランザクションID有効期限チェック

**データ整合性:**
- トランザクション内での処理:
  1. 重複チェック
  2. usr_store_product_histories挿入
  3. usr_store_products更新（既存テーブル使用）
  4. アイテム付与（商品タイプに応じてBillingDelegatorまたはRewardDelegator）
  5. usr_webstore_transactions更新
- Bank/Adjust連携は非同期（トランザクション外）
- ユニーク制約違反の場合: 別リクエストが先に処理完了、重複チェックロジックに戻る

**既存実装との整合性:**
- 類似機能: アプリ内課金のレシート検証・アイテム付与処理を参考
- 有償通貨: BillingDelegator::purchasedForWebStore()を使用
- その他アイテム: RewardDelegator::sendRewards()を使用
- UseCaseTraitのapplyUserTransactionChanges()でトランザクション管理

---

### 3.2 既存API変更: POST /game/update_and_fetch

#### 3.2.1 機能概要

WebStore利用のため、既存の`game/update_and_fetch` APIに国コード登録機能を追加します。

**重要な仕様:**
- 国コードはApple/Googleのストアフロント情報から取得（デバイス設定ではない）
  - Android: BillingConfig APIから取得
  - iOS: SKPaymentQueue.storefrontから取得
- 一度登録した国コードは更新しない（仕様書要件）
- パラメータはオプショナル（WebStore非利用時は省略可能）
- **クライアント側で対応いただく必要あり**

#### 3.2.2 API仕様変更

**新規API追加 / 既存API改修:**
- [x] 既存API改修（リクエストパラメータ追加、処理ロジック追加）

**エンドポイント:**
- パス: `/api/game/update_and_fetch`
- HTTPメソッド: POST
- 認証: 必要（JWT）

**リクエストパラメータ追加:**

| パラメータ | 型 | 必須 | 説明 | バリデーション |
|-----------|------|------|------|---------------|
| country_code | string | - | ISO 3166-1 alpha-2形式の国コード（例: "JP", "US", "GB"）<br>省略可能。WebStore利用時のみ送信 | ISO 3166-1 alpha-2形式、2文字 |

**処理フロー（国コード登録部分）:**

1. リクエストに`country_code`パラメータが含まれる場合のみ以下を実行
2. `usr_webstore_infos`に該当ユーザーのレコードが既に存在するか確認
3. 既存レコードがある場合: 何もしない（更新しない）
4. 既存レコードがない場合: `usr_webstore_infos`に新規レコードを作成
   - `usr_user_id`: 認証済みユーザーID
   - `country_code`: リクエストの`country_code`
   - `registered_at`: 現在時刻

**レスポンス:**
従来通りのゲームデータを返却（国コード登録処理はサイレントに実行）

#### 3.2.3 実装設計

**UseCase/Service:**
- `GameUpdateAndFetchUseCase`: 国コード登録処理を追加
- `WebStoreCountryRegistrationService`: 国コード登録ロジックを実装

**Domain層:**
- `WebStoreCountryRegistrationService::registerCountryCode()`
  - 入力: `usr_user_id`, `country_code`
  - 処理: `usr_webstore_infos`へのレコード挿入（既存レコードがある場合は何もしない）
  - 出力: void

**Repository:**
- `UsrWebStoreInfoRepository::findByUsrUserId()`: 既存レコード確認
- `UsrWebStoreInfoRepository::create()`: 新規レコード作成

**Entity:**
- `UsrWebStoreInfoEntity`: Plain Entity
  - プロパティ: `usrUserId`, `countryCode`, `registeredAt`

#### 3.2.4 DB設計

このセクションでは新規テーブル作成は不要。`usr_webstore_infos`テーブルは「3.1.4 DB設計」セクションで既に定義済み。

参照: 07_サーバーAPI設計書.md「POST /game/update_and_fetch」セクション

---

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装（2-3週間）**

1. **DB設計・マイグレーション実施**
   - REQ-D-001: usr_webstore_infos
   - REQ-D-002: usr_webstore_transactions
   - REQ-D-003: mst_store_products変更（product_id_webstore追加）
   - REQ-D-004: usr_store_product_histories変更（外部決済カラム追加）
   - REQ-D-005: usr_currency_paids変更（billing_platform拡張）
   - REQ-D-006: usr_currency_summaries変更（paid_amount_share追加）

2. **ドメイン基盤実装**
   - WebStoreドメイン作成
   - Model、Repository基本実装
   - Entity、Enum定義

3. **Middleware実装**
   - XsollaSignatureVerificationMiddleware
   - （IP制限はWAFで実装するためMiddlewareは不要）

**フェーズ2: コア機能実装（3-4週間）**

1. **REQ-F-001: W1（ユーザー情報取得）**
   - UserValidationUseCase
   - UserValidationService
   - AgeRestrictionService
   - CountryValidationService
   - UsrWebstoreInfoRepository
   - ShopController（W1エンドポイント）

2. **REQ-F-002: W2（決済事前確認）**
   - PaymentValidationUseCase
   - PaymentValidationService
   - PurchaseLimitService
   - TransactionService
   - UsrStoreProductRepository（既存）
   - UsrWebstoreTransactionRepository
   - ShopController（W2エンドポイント）

3. **REQ-F-003: W3（ユーザー検証）**
   - UserVerificationUseCase
   - ShopController（W3エンドポイント）

4. **REQ-F-004: W4（支払い）**
   - PaymentUseCase
   - ShopController（W4エンドポイント）

5. **REQ-F-005: W5（注文支払い成功）**
   - OrderPaidUseCase
   - OrderPaidService
   - IdempotencyService
   - UsrStoreProductHistoryRepository
   - ShopController（W5エンドポイント）

**フェーズ3: 拡張機能実装（1-2週間）**

1. **Bank/Adjust連携**
   - BankIntegrationService
   - AdjustIntegrationService
   - RetryBankIntegrationJob
   - RetryAdjustIntegrationJob

2. **エラーハンドリング強化**
   - アイテム付与失敗時のアラート通知

### 4.2 依存関係マップ

```
フェーズ1: 基盤実装
  ├→ DB設計・マイグレーション
  ├→ ドメイン基盤
  └→ Middleware

        ↓

フェーズ2: コア機能実装
  ├→ REQ-F-001 (W1)
  │   └→ フェーズ1完了が前提
  │
  ├→ REQ-F-002 (W2)
  │   └→ フェーズ1完了が前提
  │   └→ REQ-F-001の一部Service共用
  │
  ├→ REQ-F-003 (W3)
  │   └→ フェーズ1完了が前提
  │
  ├→ REQ-F-004 (W4)
  │   └→ フェーズ1完了が前提
  │
  └→ REQ-F-005 (W5)
      └→ REQ-F-002（トランザクションID検証）
      └→ REQ-F-002（購入回数更新）

        ↓

フェーズ3: 拡張機能実装
  ├→ Bank/Adjust連携
  │   └→ REQ-F-005完了が前提
  │
  └→ エラーハンドリング強化
      └→ REQ-F-005完了が前提
```

### 4.3 実装時の注意点

1. **フェーズ1を完了してからフェーズ2に進む**
   - DB設計が確定していないとコア機能実装が困難
   - Middleware未実装では署名検証ができない

2. **各フェーズ内でも依存関係を考慮した順序で実装する**
   - W1→W2→W3→W4→W5の順序推奨
   - W1とW2は並行実装可能（Service一部共用）
   - W3、W4は独立しているため並行実装可能

3. **テストは各要件実装後に都度実施する**
   - ユニットテスト: Service、Repository層
   - 機能テスト: UseCase、Controller層
   - シナリオテスト: W1→W2→W5の一連フロー

4. **Bank/Adjust連携は後回し可能**
   - MVPではログ記録のみでも可
   - 実際の連携は後から追加実装

5. **エラーハンドリングは段階的に強化**
   - フェーズ2: 基本的なエラーハンドリング
   - フェーズ3: アラート通知、リトライ機能

---

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- Domain層の各Service、UseCase、Repository
- ビジネスロジックの正常系・異常系

**テストケース例:**

| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| UserValidationService::validateCountryCode() | usr_webstore_infos.country_codeがNULL | GameException(WEBSTORE_COUNTRY_NOT_REGISTERED) |
| AgeRestrictionService::checkPurchaseRestriction() | 日本ユーザー、17歳、有料商品 | GameException(WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR) |
| AgeRestrictionService::checkPurchaseRestriction() | 日本ユーザー、17歳、無料商品 | エラーなし |
| PurchaseLimitService::checkPurchaseLimit() | 購入回数上限達成 | GameException(WEBSTORE_PURCHASE_COUNT_LIMIT) |
| TransactionService::validateTransaction() | トランザクションID有効期限切れ | GameException(WEBSTORE_TRANSACTION_NOT_FOUND) |
| IdempotencyService::checkDuplicateOrder() | 同一order_idで2回目リクエスト | 1回目の処理結果を返却 |

### 5.2 機能テスト

**テスト対象:**
- APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**

| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /api/shop/webstore (W1: web_store_user_validation) | 正常なリクエスト、ユーザー存在 | 200 OK、ユーザー情報返却 |
| POST /api/shop/webstore (W1: web_store_user_validation) | bn_user_idが存在しない | 400 Bad Request、WEBSTORE_USER_NOT_FOUND |
| POST /api/shop/webstore (W1: web_store_user_validation) | birthdayがNULL | 400 Bad Request、WEBSTORE_BIRTHDAY_REQUIRED |
| POST /api/shop/webstore (W2: web_store_payment_validation) | 正常なリクエスト | 200 OK、transaction_id返却 |
| POST /api/shop/webstore (W2: web_store_payment_validation) | 18歳未満、有料商品 | 400 Bad Request、WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR |
| POST /api/shop/webstore (W2: web_store_payment_validation) | 購入回数上限 | 400 Bad Request、WEBSTORE_PURCHASE_COUNT_LIMIT |
| POST /api/shop/webstore (W5: web_store_order_paid) | 正常なリクエスト | 200 OK、アイテム付与、履歴保存 |
| POST /api/shop/webstore (W5: web_store_order_paid) | 同一order_idで2回目リクエスト | 200 OK、1回目の結果返却、アイテム付与なし |
| POST /api/shop/webstore (W5: web_store_order_paid) | トランザクションID有効期限切れ | 400 Bad Request、WEBSTORE_TRANSACTION_NOT_FOUND |

### 5.3 シナリオテスト

**テストシナリオ例:**

**シナリオ1: 正常な購入フロー**
1. W1: ユーザー情報取得API呼び出し → 200 OK
2. W2: 決済事前確認API呼び出し → 200 OK、transaction_id取得
3. W3: ユーザー検証API呼び出し → 200 OK
4. W4: 支払いAPI呼び出し → 200 OK
5. W5: 注文支払い成功API呼び出し → 200 OK
6. usr_store_product_historiesにレコード挿入確認
7. usr_store_products.purchase_count更新確認（既存テーブル使用）
8. ユーザーのアイテム所持数確認

**シナリオ2: 年齢制限による購入拒否**
1. W1: ユーザー情報取得API呼び出し → 200 OK（17歳）
2. W2: 決済事前確認API呼び出し（有料商品） → 400 Bad Request、WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR

**シナリオ3: べき等性の確認**
1. W5: 注文支払い成功API呼び出し（1回目） → 200 OK
2. usr_store_product_historiesにレコード挿入確認
3. W5: 同一order_idで注文支払い成功API呼び出し（2回目） → 200 OK、1回目の結果返却
4. usr_store_product_historiesのレコード数確認（増加なし）
5. アイテム所持数確認（増加なし）

**シナリオ4: Bank/Adjust連携失敗時のリトライ**
1. W5: 注文支払い成功API呼び出し → 200 OK（Bank連携失敗をモック）
2. usr_webstore_transactionsのbank_statusが'failed'になることを確認
3. RetryBankIntegrationJobが起動確認
4. 再送成功後、bank_statusが'success'に更新確認

---

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**

**usrDB関連:**
- [x] `YYYY_MM_DD_HHMMSS_create_usr_webstore_infos_table.php` - usr_webstore_infosテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_create_usr_webstore_transactions_table.php` - usr_webstore_transactionsテーブル作成
- [x] `YYYY_MM_DD_HHMMSS_add_webstore_columns_to_usr_store_product_histories.php` - usr_store_product_histories変更（order_id, invoice_id, transaction_id追加）

**mstDB関連:**
- [x] `YYYY_MM_DD_HHMMSS_add_product_id_webstore_to_mst_store_products.php` - mst_store_products変更（product_id_webstore追加）

### 6.2 マイグレーション実行順序

**ステップ1: 基盤テーブルの作成（他テーブルから参照されるテーブル）**
1. `create_usr_webstore_infos_table` - usr_webstore_infosテーブル
2. `create_usr_webstore_transactions_table` - usr_webstore_transactionsテーブル

**ステップ2: マスターテーブルの変更**
3. `add_product_id_webstore_to_mst_store_products` - mst_store_products変更

**ステップ3: 既存テーブルへのカラム追加・変更**
4. `add_webstore_columns_to_usr_store_product_histories` - usr_store_product_histories変更

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装する
- ロールバック時のデータ保全:
  - usr_webstore_infos: 削除（登録された国コードは失われる）
  - usr_webstore_transactions: 削除（トランザクション状態情報は失われる）
  - usr_store_product_histories: カラム削除（order_id、invoice_id、transaction_id）
  - mst_store_products: カラム削除（product_id_webstore）
- ロールバック実行前にバックアップ推奨

### 6.4 マイグレーション実行時の注意事項

1. **usr_store_product_historiesへのカラム追加**
   - order_id、invoice_id、transaction_idはNULL許容で追加
   - 既存の購入履歴には影響なし（新規購入から使用）

3. **外部キー制約**
   - usr_webstore_infos.usr_user_idはusr_users.idを参照
   - 外部キー制約を設定（ON DELETE CASCADE）

4. **インデックス追加**
   - usr_store_product_histories.order_idにユニークインデックス
   - パフォーマンス向上とべき等性保証

---

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**

- `migration`: マイグレーションファイルの作成・実行
  - 使用タイミング: DB設計時、フェーズ1

- `domain-layer`: ドメインレイヤーの実装パターン参照
  - 使用タイミング: Service、UseCase、Repository実装時、フェーズ2

- `api-request-validation`: リクエストパラメータのバリデーション実装
  - 使用タイミング: Controller実装時、フェーズ2

- `api-response`: レスポンス構造の実装
  - 使用タイミング: ResponseFactory実装時、フェーズ2

- `reward-send-service`: RewardDelegatorの使用方法、報酬付与パターン
  - 使用タイミング: W5実装時、フェーズ2

- `api-test-implementation`: テストコードの実装
  - 使用タイミング: 各機能実装後、フェーズ2-3

- `sail-check-fixer`: コード品質チェック・修正
  - 使用タイミング: 実装完了後、PR作成前

**使用タイミング:**
- **フェーズ1（基盤実装）**: `migration`
- **フェーズ2（コア機能実装）**: `domain-layer`, `api-request-validation`, `api-response`, `reward-send-service`, `api-test-implementation`
- **フェーズ3（拡張機能実装）**: `api-test-implementation`, `sail-check-fixer`

---

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

#### 8.1.1 IP制限の実装方法

**項目:** ウェブフック通知元IPアドレスの制限

**決定事項:** AWS WAFで実装（インフラレベル）

**実装内容:**
- **対象パス**: `/api/shop/webstore`のみ
- **HTTPメソッド**: POST
- **許可IP**: XsollaおよびWebStore側のIPアドレスリスト（07_サーバーAPI設計書.md参照）
- **アクション**: 許可IP以外からのアクセスは403 Forbiddenで拒否
- **適用先**: ALBに紐付け

**メリット:**
- インフラレベルでブロック（アプリケーション層に到達しない）
- 特定エンドポイントのみに制限可能（他のShop APIには影響なし）
- DDoS攻撃への対応が容易
- アプリケーション側でのIPチェック実装が不要

**注意:**
- アプリケーション層でのIP制限Middleware（`XsollaIpRestrictionMiddleware`）は実装不要

#### 8.1.2 メンテナンス実装方法

**項目:** メンテナンス中の503レスポンス

**決定事項:** ALBリスナールール（優先度制御）で実装

**実装内容:**

**優先度1（高）: Webフックエンドポイント専用メンテナンスルール**
- **条件**: パス = `/api/shop/webstore`
- **アクション**: 固定レスポンス 503 Service Unavailable
- **レスポンスヘッダー**: `Content-Type: application/json`
- **レスポンスボディ**:
```json
{
  "error": {
    "code": "SERVICE_UNAVAILABLE",
    "message": "Service is under maintenance"
  }
}
```

**利点:**
- インフラレベルで完結（アプリケーション実装不要）
- 既存のメンテナンス仕組み（299）に影響なし
- Webフックのみ503を返せる
- W1/W2の処理フローでのメンテナンスチェック要件を満たす

**注意:**
- アプリケーション層でのメンテナンスチェックは不要
- `ErrorCode::WEBSTORE_MAINTENANCE`のようなエラーコードは不要

#### 8.1.3 Bank/Adjust連携の実装タイミング

**項目:** Bank/Adjust連携をMVPに含めるか

**選択肢A: MVPに含める（フェーズ2で実装）**
- メリット:
  - 初回リリースから完全な機能を提供
  - 後から追加するリスクがない
- デメリット:
  - 実装期間が長くなる
  - Bank/Adjust APIの仕様確認が必要

**選択肢B: MVPから除外（フェーズ3で実装）**
- メリット:
  - 実装期間を短縮
  - コア機能（ウェブフック受信、アイテム付与）に集中
  - ログ記録のみ先行実装、連携は後から追加可能
- デメリット:
  - 初回リリース時は連携なし（ログのみ）

**推奨:** 選択肢B（MVPから除外）
- 理由: コア機能の早期リリースを優先。Bank/Adjust連携失敗は購入成功に影響しないため、後から追加可能。

#### 8.1.4 トランザクションID有効期限のチェック方法

**項目:** トランザクションIDの有効期限チェック

**選択肢A: created_atで有効期限をチェック**
- メリット:
  - シンプルな実装
  - 追加カラム不要
- デメリット:
  - タイムゾーンの考慮が必要

**選択肢B: expired_atカラムを追加**
- メリット:
  - 有効期限が明確
  - タイムゾーンの問題なし
- デメリット:
  - カラム追加が必要
  - 冗長なデータ

**推奨:** 選択肢A（created_atで有効期限をチェック）
- 理由: シンプルな実装、タイムゾーンはUTC統一で対応可能。有効期限の具体的な期間は実装時に決定。

### 8.2 仕様の解釈

#### 8.2.1 誕生日情報の取得方法

**項目:** 誕生日情報の取得元

**✅ 確認済み:**
- `usr_user_profiles.birth_date`カラムに既存（暗号化済み）
- `UsrUserProfile::getBirthDate()`で復号化してint型（YYYYMMDDフォーマット）で取得
- `UsrUserProfile::hasBirthDate()`で誕生日の有無を確認
- W1で誕生日がNULLの場合は400エラー返却

**実装方針:**
- 既存カラムを使用するため、新規マイグレーション不要
- 年齢計算ロジックは既存実装を参考にする

#### 8.2.2 AdjustServiceの実装確認

**項目:** AdjustServiceが既存コードベースに存在するか

**解釈A: 既存のAdjustServiceを活用**
- この解釈の場合の実装:
  - 既存のAdjustServiceを使用
  - WebStore専用のイベントトークンを追加

**解釈B: 新規実装**
- この解釈の場合の実装:
  - AdjustIntegrationServiceを新規実装
  - Adjust APIの仕様確認が必要

**確認先:** 既存コードベース調査、インフラ担当者

**現在の仮定:** 解釈Bを想定（新規実装）、ただしMVPから除外可能

---

## 9. 補足情報

### 9.1 参考にすべき既存実装

**類似機能の実装例:**

1. **認証系処理（AuthDelegator、SignUpUseCase）**
   - ユーザー情報取得パターン
   - トランザクション管理パターン
   - Delegator経由での他ドメイン操作

2. **ガチャ購入処理（GachaDrawUseCase）**
   - 購入前確認処理
   - 報酬付与パターン（RewardDelegator使用）
   - トランザクション管理

3. **アプリ内課金処理（Shop関連）**
   - レシート検証パターン
   - 購入履歴保存（usr_store_product_histories）
   - べき等性保証（重複チェック）

4. **Reward付与処理（RewardDelegator、RewardSendService）**
   - RewardDelegator::sendRewards()の使用方法
   - RewardSendPolicyによるエラーハンドリング
   - アイテム付与の実装パターン

5. **Middleware実装（既存の認証Middleware）**
   - 署名検証パターン
   - リクエストログ記録
   - エラーレスポンス返却

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
  - Controller、UseCase、Service、Repositoryの実装ガイドライン
  - テスト実装パターン

- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー（本機能では直接関係ないが、アーキテクチャ理解に有用）

- **Xsolla公式ドキュメント**:
  - ウェブフック仕様
  - 署名検証アルゴリズム
  - エラーコード定義

- **Bank/Adjust API仕様書**:
  - Bank連携エンドポイント
  - Adjust連携エンドポイント
  - リトライポリシー

### 9.3 実装時のTips

**1. べき等性の実装**
- order_idのユニークインデックスを活用
- 重複チェックはトランザクション開始前に実施
- 既存レコードがある場合は1回目の処理結果を返却

**2. トランザクション管理**
- UseCaseTraitのapplyUserTransactionChanges()を使用
- コールバック内で他ドメイン操作（Delegator経由）
- Bank/Adjust連携は非同期Job化（トランザクション外）

**3. エラーハンドリング**
- 永続エラー（400）と一時エラー（500）を明確に区別
- GameExceptionを使用、ErrorCodeを定義

**4. ログ記録**
- リクエストボディ全体をMEDIUMTEXTで保存
- request_id、nginx_request_idで追跡
- webhook_typeで種別を識別

**5. パフォーマンス最適化**
- インデックスを適切に設定
- N+1問題の回避（単一クエリで取得）
- Bank/Adjust連携の非同期化

**6. よくあるハマりポイント**
- タイムゾーン: UTC統一、StringUtil::convertToISO8601()使用
- NULL許容: 無料アイテムの場合、invoice_id、currency_codeがNULL
- 署名検証: リクエストボディ全体を使用、順序に注意
- トランザクションID有効期限チェック（created_atで判定）

**7. デバッグ方法**
- usr_webstore_transactionsでトランザクション状態を追跡
- アプリケーションログで詳細なエラー情報を確認

---

## 10. 次のステップ

### 10.1 開発着手前の確認事項

**ブロッカー確認事項（必須）:**
- [x] usr_user_profiles.birth_dateカラムの存在確認 → ✅ 既存（暗号化済み）
- [ ] AdjustServiceの実装確認（既存活用 or 新規実装 or MVPから除外）

**プランナー確認事項（推奨）:**
- [x] 誕生日情報の取得方法（Q1） → ✅ usr_user_profiles.birth_dateを使用
- [ ] 購入回数制限の管理方法（Q2）
- [ ] トランザクションIDの有効期限（Q3）
- [ ] Bank/Adjust連携失敗時の挙動（Q4）
- [ ] アイテム付与失敗時の挙動（Q5）
- [ ] Adjust連携の優先度（Q6）
- [ ] バンダイナムコID居住国の取得方法（Q7）
- [ ] 無料アイテムの年齢制限（Q8）

### 10.2 開発開始後の作業

**フェーズ1（基盤実装）:**
1. マイグレーションファイル作成（10件）
2. マイグレーション実行（開発環境）
3. Modelクラス作成（8件）
4. Repositoryクラス作成（8件）
5. Entityクラス作成（5件）
6. Enumクラス作成（1件）
7. Middleware作成（1-2件）

**フェーズ2（コア機能実装）:**
1. W1実装（UserValidation）
2. W2実装（PaymentValidation）
3. W3実装（UserVerification）
4. W4実装（Payment）
5. W5実装（OrderPaid）
6. ResponseFactory実装
7. ユニットテスト実装
8. 機能テスト実装

**フェーズ3（拡張機能実装）:**
1. Bank/Adjust連携実装
2. リトライJob実装
3. アラート通知実装
4. シナリオテスト実装
5. コード品質チェック
6. ドキュメント更新

### 10.3 リリース準備

**テスト環境での検証:**
- [ ] W1-W5の全エンドポイント動作確認
- [ ] べき等性の確認（重複リクエスト）
- [ ] 年齢制限・国コード制御の確認
- [ ] トランザクションID有効期限の確認
- [ ] Bank/Adjust連携の確認（MVPに含む場合）
- [ ] エラーハンドリングの確認
- [ ] ログ記録の確認

**本番環境へのデプロイ準備:**
- [ ] マイグレーション実行計画
- [ ] ロールバック計画
- [ ] 監視設定（CloudWatch Alarms）
- [ ] アラート通知設定（Slack/メール）
- [ ] 運用マニュアル作成
- [ ] 障害対応フロー整理

---

**最終更新**: 2025-12-02
**次回更新**: ブロッカー確認事項解消後、プランナー確認完了後
