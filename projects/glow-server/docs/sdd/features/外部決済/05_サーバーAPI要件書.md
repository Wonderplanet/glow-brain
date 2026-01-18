# 外部決済機能 - サーバーAPI要件書

**機能名**: 外部決済（WEB STORE連携）
**作成日**: 2025-12-02
**バージョン**: 1.0
**ステータス**: 要件確定版

---

## 目次

1. [機能概要](#1-機能概要)
2. [機能要件](#2-機能要件)
3. [データ要件](#3-データ要件)
4. [セキュリティ要件](#4-セキュリティ要件)
5. [連携要件](#5-連携要件)
6. [制約要件](#6-制約要件)
7. [非機能要件](#7-非機能要件)
8. [エラーハンドリング要件](#8-エラーハンドリング要件)
9. [残課題と注意事項](#9-残課題と注意事項)

---

## 1. 機能概要

### 1.1. 概要

外部決済機能は、ユーザーがWeb Store（Xsolla基盤）を通じてゲーム内アイテムを購入できる機能です。サーバー側では、Xsollaからのウェブフックを受信し、ユーザー認証、購入検証、アイテム付与、外部連携（Bank/Adjust）を行います。

**トレーサビリティ**:
- 01_サーバー要件抽出.md: 概要セクション
- 04_ゲーム体験仕様確認結果まとめ.md: Q1-Q8

### 1.2. 主要機能

1. **ウェブフック受信**: Xsollaから5種類のウェブフックを受信
2. **ユーザー認証**: バンダイナムコIDとアプリユーザーの紐付け
3. **購入権限検証**: 年齢・国・購入回数による制御
4. **アイテム付与**: 決済完了後のアイテム付与
5. **外部連携**: Bank/Adjustへのデータ送信
6. **不正購入防止**: 署名検証、IP制限、重複防止

---

## 2. 機能要件

### 2.1. ウェブフックエンドポイント一覧

#### W1: ユーザー情報取得（web_store_user_validation）

**要件ID**: REQ-F-001
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件17
- 02_サーバー要件_コード調査追記.md: 実装パターン推奨事項
- 04_ゲーム体験仕様確認結果まとめ.md: Q1, Q7

**機能概要**:
- WebStoreログイン時にアプリのユーザー情報を返却
- バンダイナムコIDからアプリのユーザーIDを検索
- 国コード、通貨コード、誕生日情報を返却

**リクエスト要素**:
- `notification_type`: "get_user"
- `user.id`: バンダイナムコID
- `custom_parameters`: カスタムパラメータ（任意）

**レスポンス要素（200 OK）**:
```json
{
  "user": {
    "id": "バンダイナムコID",
    "internal_id": "usr_user_id",
    "name": "ユーザー名",
    "level": 1,
    "birthday": "20050408",
    "birthday_month": "04",
    "country": "JP",
    "currency": "JPY"
  }
}
```

**エラーレスポンス**:
- 400 `USER_NOT_FOUND`: ユーザーが未登録
- 400 `BIRTHDAY_REQUIRED`: 誕生日情報が未登録
- 400 `USER_TOO_YOUNG`: 13歳以下（海外のみ）
- 400 `COUNTRY_MISMATCH`: ダウンロード国と居住国が不一致
- 503 `MAINTENANCE`: メンテナンス中

**処理フロー**:
1. バンダイナムコIDから`usr_users.bn_user_id`を検索
2. ユーザーが存在しない場合: 400エラー
3. メンテナンス中の場合: 503エラー
4. 誕生日情報（`usr_user_profiles.birth_date`）を確認、NULLの場合: 400エラー
   - `UsrUserProfile::getBirthDate()`で復号化して取得
   - `hasBirthDate()`で存在確認
5. 国コード・通貨コード（`usr_webstore_infos`）を取得
6. 居住国（`usr_users.bn_country`）を取得
7. ダウンロード国と居住国を照合、不一致の場合: 400エラー
8. レスポンスを返却

**制約事項**:
- 誕生日がNULLの場合、エラーメッセージでユーザーに誕生日登録を促す
- 誕生日は暗号化されており、`getBirthDate()`で復号化してint型（YYYYMMDDフォーマット）で取得
- 国コード・通貨コードは一度登録したら更新しない

---

#### W2: 決済事前確認（web_store_payment_validation）

**要件ID**: REQ-F-002
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件19, 20, 21
- 02_サーバー要件_コード調査追記.md: 追加要件15, 17
- 04_ゲーム体験仕様確認結果まとめ.md: Q2, Q3, Q8

**機能概要**:
- 購入前にユーザーの購入権限を検証
- トランザクションIDを発行
- 年齢制限、購入回数制限をチェック

**リクエスト要素**:
- `notification_type`: "payment"
- `user`: { `id`, `birthday`, `country` }
- `custom_parameters`: { `internal_id`, `store_code`, `country_from_ip`, `is_country_mismatch` }
- `purchase.items`: [{ `sku`, `type`, `amount` }]

**レスポンス要素（200 OK）**:
```json
{
  "transaction_id": "UUID形式のトランザクションID"
}
```

**エラーレスポンス**:
- 400 `PURCHASE_NOT_ALLOWED`: 購入権限なし
- 400 `PURCHASE_NOT_ALLOWED_FOR_MINOR`: 未成年が有料商品を購入しようとした（現在の基準: 日本18歳）
- 400 `PURCHASE_NOT_ALLOWED_CHILD_ACCOUNT`: 子供アカウントが有料商品を購入しようとした
- 400 `PURCHASE_COUNT_LIMIT`: 購入回数上限
- 503 `MAINTENANCE`: メンテナンス中

**処理フロー**:
1. `items`配列から`type=virtual_good`のみをフィルタ
2. ユーザーの年齢をチェック（日本: 未成年は無料のみ（現在の基準: 18歳）、海外: 14-17歳は無料のみ）
3. 購入回数制限をチェック（`usr_external_payment_products`テーブル）
4. 国コード不一致のログ記録（`is_country_mismatch=true`の場合）
5. トランザクションIDを発行（UUID v4）
6. トランザクション情報を`log_webstore_transactions`に保存（有効期限: 24時間）
7. レスポンスを返却

**制約事項**:
- トランザクションIDの有効期限は24時間
- items配列に`type=virtual_good`以外が含まれる場合も処理を継続（フィルタして処理）
- 無料アイテム（`order.amount == 0`）は年齢制限の対象外

---

#### W3: ユーザー検証（user_validation - Xsolla）

**要件ID**: REQ-F-003
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件9
- 03_サーバー仕様レビュー.md: W3セクション

**機能概要**:
- Xsollaが購入直前にユーザーの存在を確認

**リクエスト要素**:
- Xsolla仕様に準拠
- `custom_parameters.internal_id`: アプリのユーザーID

**レスポンス要素**:
- 200: ユーザーが存在する
- 400: ユーザーが存在しない

**処理フロー**:
1. カスタムパラメータから`internal_id`を取得
2. `usr_users`テーブルでユーザー存在確認
3. 存在する場合: 200、存在しない場合: 400

---

#### W4: 支払い（payment - Xsolla）

**要件ID**: REQ-F-004
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件10, 24
- 02_サーバー要件_コード調査追記.md: 追加要件4

**機能概要**:
- 決済実行時の支払い通知を受け取る
- sandboxモードを判定

**リクエスト要素**:
- `transaction.dry_run`: 0 or 1（1の場合はテスト決済）

**レスポンス要素**:
- 200: 支払いを受け付ける
- 500: 一時エラー（リトライ可能）

**処理フロー**:
1. 署名検証（後述のセキュリティ要件参照）
2. `transaction.dry_run`をチェック（1の場合はテスト決済）
3. トランザクションログを記録
4. 200を返却

**制約事項**:
- テスト決済（`dry_run=1`）と本番決済を明確に区別

---

#### W5: 注文支払い成功（order_paid - Xsolla）

**要件ID**: REQ-F-005
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件11, 22, 23, 25, 28
- 02_サーバー要件_コード調査追記.md: 追加要件6, 8, 9, 16, 18, 21
- 03_サーバー仕様レビュー.md: W5セクション
- 04_ゲーム体験仕様確認結果まとめ.md: Q4, Q5, Q6

**機能概要**:
- 決済完了後にアイテムを付与
- Bank/Adjustに購入データを送信
- べき等性を保証（重複処理防止）

**リクエスト要素**:
- `order`: { `id`, `invoice_id`, `currency`, `amount`, `mode` }
- `items`: [{ `sku`, `type`, `amount` }]
- `custom_parameters`: { `internal_id`, `transaction_id`, `user_ip`, `store_code`, `country_from_ip`, `is_country_mismatch` }

**レスポンス要素（200 OK）**:
```json
{
  "result": "success",
  "order_id": "注文ID"
}
```

**エラーレスポンス**:
- 400: 永続エラー（返金が必要）
- 500: 一時エラー（リトライ可能）

**処理フロー**:
1. 署名検証
2. `order.id`で重複チェック（`usr_store_product_histories.receipt_unique_id`）
3. 既に処理済みの場合: 1回目の処理結果を返す（べき等性）
4. `order.mode`が"sandbox"の場合、テスト決済フラグを立てる
5. `items`配列から`type=virtual_good`のみをフィルタ
6. トランザクションIDと照合（カスタムパラメータの`transaction_id`）
7. トランザクションIDの有効期限チェック（24時間以内）
8. `usr_store_product_histories`にレコード挿入
   - `receipt_unique_id`: `order.id`
   - `billing_platform`: "webstore"
   - `currency_code`: `order.currency`（無料アイテムの場合はNULL）
   - `purchase_price`: `order.amount`（無料アイテムの場合は0）
   - `is_sandbox`: `order.mode == "sandbox" ? 1 : 0`
   - `order_id`: `order.id`（新規カラム）
   - `invoice_id`: `order.invoice_id`（新規カラム、無料アイテムの場合はNULL）
   - `transaction_id`: カスタムパラメータの`transaction_id`（新規カラム）
9. 購入回数を更新（`usr_external_payment_products`）
10. アイテム付与処理（`RewardDelegator::sendRewards`）
    - 失敗時: エラー種別により処理を分岐
      - 一時エラー: 500エラーを返してリトライさせる
      - 永続エラー: ログ記録 + アラート通知 + 200返却
11. Bank連携（購入データ送信）
    - 失敗時: ログ記録 + 非同期再送
12. Adjust連携（クライアントIP送信）
    - 失敗時: ログ記録 + 非同期再送
13. `log_webstore_transactions`にトランザクション完了ログを記録
14. 200を返却

**制約事項**:
- べき等性の保証: 同じ`order.id`で複数回リクエストが来た場合、1回目の処理結果を返す
- 無料アイテム・クーポン交換の場合: `order.invoice_id`, `order.currency`がNULL
- Bank/Adjust連携失敗は購入成功に影響しない（ログ記録 + 非同期再送）
- アイテム付与失敗時の挙動はエラー種別により異なる

---

### 2.2. 年齢・国による制御

#### 年齢制限（日本）

**要件ID**: REQ-F-006
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件2
- 02_サーバー要件_コード調査追記.md: 追加要件2
- 04_ゲーム体験仕様確認結果まとめ.md: Q8-2

**制御ルール**:
- 全年齢: ログイン可能
- 未成年: 無料アイテムとプロモーションコードのみ利用可能（現在: 日本18歳未満）
  - 判定: `order.amount == 0`または`order.currency == null`の場合はOK
  - それ以外: 400エラー「PURCHASE_NOT_ALLOWED_FOR_MINOR」

**実装箇所**:
- W2（決済事前確認）: 年齢チェック + 有料/無料判定

---

#### 年齢制限（海外）

**要件ID**: REQ-F-007
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件3
- 04_ゲーム体験仕様確認結果まとめ.md: Q8-3

**制御ルール**:
- 13歳以下: ログイン不可
  - W1（ユーザー情報取得）で400エラー「USER_TOO_YOUNG」
- 14歳以上で未成年（子供アカウント）: 無料アイテムとプロモーションコードのみ利用可能（現在: 18歳未満）
  - W2（決済事前確認）で有料商品の場合は400エラー「PURCHASE_NOT_ALLOWED_CHILD_ACCOUNT」

**判定方法**:
- 年齢のみで判定（14-17歳を子供アカウントとして扱う）
- バンダイナムコIDのアカウント種別フラグは使用しない

**実装箇所**:
- W1（ユーザー情報取得）: 13歳以下のログイン拒否
- W2（決済事前確認）: 14-17歳の有料購入拒否

---

#### 国コード制御（海外のみ）

**要件ID**: REQ-F-008
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件4, 5
- 02_サーバー要件_コード調査追記.md: 追加要件24
- 04_ゲーム体験仕様確認結果まとめ.md: Q7

**制御ルール**:
1. **ダウンロード国と居住国の照合**
   - アプリのダウンロード国（`usr_webstore_infos.country_code`）とバンダイナムコIDの居住国（`usr_users.bn_country`）が異なる場合: ログインできない
   - W1（ユーザー情報取得）で400エラー「COUNTRY_MISMATCH」

2. **アクセス国と居住国の不一致ログ記録**
   - カスタムパラメータ`country_from_ip`と居住国が異なる場合: ログインは可能だが、ログを記録
   - `is_country_mismatch=true`の場合、`log_webstore_fraud_detection`テーブルに記録

**実装箇所**:
- W1（ユーザー情報取得）: ダウンロード国と居住国の照合
- W2（決済事前確認）: アクセス国と居住国の不一致ログ記録

---

### 2.3. 購入回数制限

**要件ID**: REQ-F-009
**優先度**: 必須
**トレーサビリティ**:
- 02_サーバー要件_コード調査追記.md: 追加要件3
- 04_ゲーム体験仕様確認結果まとめ.md: Q2

**制御ルール**:
- 外部決済は既存のアプリ内課金とは別管理
- 新規テーブル（`usr_external_payment_products`）で購入回数を管理
- `purchase_count`: 期間内の購入回数
- `purchase_total_count`: 累計購入回数
- `last_reset_at`: リセット日時

**実装箇所**:
- W2（決済事前確認）: 購入回数制限チェック
- W5（注文支払い成功）: 購入回数更新

---

### 2.4. 不正購入防止

#### べき等性の保証（重複処理防止）

**要件ID**: REQ-F-010
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件25
- 02_サーバー要件_コード調査追記.md: 追加要件6, 25
- 03_サーバー仕様レビュー.md: 2.8節

**制御ルール**:
- W5（注文支払い成功）で、同一`order.id`で複数回リクエストが来た場合、1回目の処理結果を返す
- `usr_store_product_histories.receipt_unique_id`にユニークインデックスを設定
- 重複挿入はDB制約で防ぐ

**実装方式**:
1. `order.id`で既存レコードを検索
2. 存在する場合: 1回目の処理結果を返す
3. 存在しない場合: 新規処理を開始
4. トランザクション内で`usr_store_product_histories`に挿入
5. ユニーク制約違反の場合: 別のリクエストが先に処理を完了したため、重複チェックのロジックに戻る

---

#### sandboxモードの判定

**要件ID**: REQ-F-011
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件24
- 02_サーバー要件_コード調査追記.md: 追加要件4
- 03_サーバー仕様レビュー.md: 2.17節

**制御ルール**:
- W4（支払い）: `transaction.dry_run=1`の場合はテスト決済
- W5（注文支払い成功）: `order.mode="sandbox"`の場合はテスト決済
- `is_sandbox`フラグを`usr_store_product_histories`と`log_webstore_transactions`に記録
- テスト決済は本番データと明確に区別

---

### 2.5. 国コード・通貨コードの管理

**要件ID**: REQ-F-012
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件31, 32
- 02_サーバー要件_コード調査追記.md: 追加要件11
- 03_サーバー仕様レビュー.md: 2.5節

**制御ルール**:
- ユーザー情報取得API（W1）で国コードを返す
- 起動時（ログイン時）にアプリサーバー側で国コード情報を保持していない場合はアプリサーバーに登録する
- **一度登録した国コードは更新しない**
- デバッグ用に特定ユーザーの国コードを上書き可能（MVP範囲外）

**データ保存先**:
- 新規テーブル`usr_webstore_infos`を作成
  - `usr_user_id`: ユーザーID（主キー）
  - `country_code`: 国コード（ISO 3166-1 alpha-2、ストアフロントから取得）
  - `registered_at`: 登録日時
  - `created_at`, `updated_at`

**デバッグ用上書き**:
- 新規テーブル`usr_webstore_debug_overrides`を作成（MVP範囲外）
- SQLで直接レコードを挿入（管理画面は後から追加可能）

**既存ログインAPI（POST /sign_in）の変更**:

WebStore利用のため、既存のログインAPIに国コード登録機能を追加します。

**リクエストパラメータ追加**:
| パラメータ | 型 | 必須 | 説明 |
|-----------|------|------|------|
| country_code | string | - | ISO 3166-1 alpha-2形式の国コード（例: "JP", "US", "GB"）<br>省略可能。WebStore利用時のみ送信 |

**重要な仕様**:
- 国コードはApple/Googleのストアフロント情報から取得（デバイス設定ではない）
  - Android: BillingConfig APIから取得
  - iOS: SKPaymentQueue.storefrontから取得
- 一度登録した国コードは更新しない（仕様書P.28要件）
- パラメータはオプショナル（従来のログイン処理との互換性維持）

**処理フロー（国コード登録部分）**:
1. リクエストに`country_code`パラメータが含まれる場合のみ以下を実行
2. `usr_webstore_infos`に該当ユーザーのレコードが既に存在するか確認
3. 既存レコードがある場合: 何もしない（更新しない）
4. 既存レコードがない場合: `usr_webstore_infos`に新規レコードを作成
   - `usr_user_id`: 認証済みユーザーID
   - `country_code`: リクエストの`country_code`
   - `registered_at`: 現在時刻

**レスポンス**:
- 従来通り `access_token` を返却（国コード登録処理はサイレントに実行）

---

### 2.6. トランザクションID管理

**要件ID**: REQ-F-013
**優先度**: 必須
**トレーサビリティ**:
- 02_サーバー要件_コード調査追記.md: 追加要件15
- 03_サーバー仕様レビュー.md: 2.16節
- 04_ゲーム体験仕様確認結果まとめ.md: Q3

**制御ルール**:
- W2（決済事前確認）でトランザクションID（UUID v4）を発行
- トランザクションIDの有効期限: 24時間
- W5（注文支払い成功）でトランザクションIDと照合
- 有効期限切れの場合: 400エラー「TRANSACTION_EXPIRED」

**データ保存先**:
- `log_webstore_transactions`テーブル
  - `transaction_id`: トランザクションID
  - `usr_user_id`: ユーザーID
  - `order_id`: Xsollaの注文ID（W5で更新）
  - `status`: 'pending', 'completed', 'failed'
  - `created_at`: 発行日時

---

### 2.7. items配列のフィルタリング

**要件ID**: REQ-F-014
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件21
- 02_サーバー要件_コード調査追記.md: 追加要件17
- 03_サーバー仕様レビュー.md: 2.18節

**制御ルール**:
- W2（決済事前確認）とW5（注文支払い成功）で、`items`配列から`type=virtual_good`のみをフィルタ
- 他のタイプ（クーポン、無料アイテム等）は無視
- `type=virtual_good`が1件もない場合: エラー

---

### 2.8. 無料アイテム・クーポンの特殊処理

**要件ID**: REQ-F-015
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件28
- 02_サーバー要件_コード調査追記.md: 追加要件18
- 03_サーバー仕様レビュー.md: 2.12節
- 04_ゲーム体験仕様確認結果まとめ.md: Q8-2

**制御ルール**:
- 無料アイテムやクーポン交換の場合、以下の挙動に対応:
  - `order.invoice_id`がNULL
  - `order.currency`がNULL
  - 「ユーザーの検証（W3）」「支払い（W4）」ウェブフックが発生しない
- `usr_store_product_histories`の`invoice_id`, `currency_code`カラムをNULL許容に変更
- 年齢制限の判定で、無料アイテムは許可（18歳未満でもOK）

---

### 2.9. キャンセルウェブフックの処理

**要件ID**: REQ-F-016
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件29
- 02_サーバー要件_コード調査追記.md: 追加要件19
- 03_サーバー仕様レビュー.md: 2.13節

**制御ルール**:
- Xsollaからキャンセル通知が来た場合は、500エラーを返す
- システム的なキャンセルの連携は行わない
- キャンセル通知のログ記録は任意

---

## 3. データ要件

### 3.1. 新規テーブル

#### 3.1.1. usr_webstore_infos

**要件ID**: REQ-D-010
**優先度**: 必須
**トレーサビリティ**:
- 仕様書P.28: 国コード取得・登録要件
- 07_サーバーAPI設計書.md: usr_webstore_infosテーブル

**テーブル構造**:
```sql
CREATE TABLE usr_webstore_infos (
  usr_user_id VARCHAR(255) PRIMARY KEY,
  country_code VARCHAR(2) NOT NULL,
  registered_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  FOREIGN KEY (usr_user_id) REFERENCES usr_users(usr_user_id)
);
```

**カラム定義**:
| 列名 | データ型 | 制約 | 説明 |
|------|---------|------|------|
| usr_user_id | VARCHAR(255) | PRIMARY KEY | ユーザーID |
| country_code | VARCHAR(2) | NOT NULL | ISO 3166-1 alpha-2形式の国コード（例: "JP", "US", "GB"） |
| registered_at | TIMESTAMP | NOT NULL | 国コード登録日時 |
| created_at | TIMESTAMP | NOT NULL | 作成日時 |
| updated_at | TIMESTAMP | NOT NULL | 更新日時 |

**重要な仕様**:
- 国コードはApple/Googleのストアフロント情報から取得（デバイス設定ではない）
  - Android: BillingConfig APIから取得
  - iOS: SKPaymentQueue.storefrontから取得
- 一度登録した国コードは更新しない（仕様書P.28要件）
- ユーザーごとに1レコードのみ（usr_user_idがPK）

**利用箇所**:
- `POST /sign_in`: 国コード初回登録（country_codeパラメータが送信された場合のみ）
- W1（ユーザー情報取得）: country_code存在チェック、レスポンスで返却
- W5（注文支払い成功）: 不正検出（is_country_mismatch）の判定に使用

---

#### 3.1.2. ~~usr_webstore_debug_overrides~~ (削除)

**変更理由**: MVP範囲外。デバッグ機能は後回し

---

#### 3.1.3. ~~usr_external_payment_products~~ (削除)

**変更理由**: 既存の`usr_store_products`テーブルで購入回数を管理。外部決済専用テーブルは不要。

---

#### 3.1.4. ~~log_webstore_webhooks~~ (削除)

**変更理由**: Nginxログとアプリケーションログで管理。専用テーブル不要。

---

#### 3.1.5. usr_webstore_transactions（軽量化版）

**要件ID**: REQ-D-005
**優先度**: 必須
**トレーサビリティ**:
- 07_サーバーAPI設計書.md: usr_webstore_transactionsテーブル

**テーブル構造**:
```sql
CREATE TABLE usr_webstore_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usr_user_id VARCHAR(255) NOT NULL,
  transaction_id VARCHAR(255) NOT NULL UNIQUE,
  order_id VARCHAR(255),
  is_sandbox TINYINT NOT NULL DEFAULT 0,
  status VARCHAR(50) NOT NULL,
  error_code VARCHAR(100),
  item_grant_status VARCHAR(50),
  bank_status VARCHAR(50),
  adjust_status VARCHAR(50),
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  INDEX idx_usr_user_id (usr_user_id),
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
);
```

**用途**:
- トランザクション状態管理（12カラムの軽量テーブル）
- status: 'pending', 'completed', 'failed'
- item_grant_status: 'success', 'failed_temporary', 'failed_permanent'
- bank_status/adjust_status: 'pending', 'success', 'failed'

**詳細情報の取得方法:**
- 購入詳細（商品ID、金額、通貨等）: `order_id`から`usr_store_product_histories`を検索
- エラー詳細: `error_code`で識別、詳細はアプリケーションログを参照
- リクエストトレース: `transaction_id`でアプリケーションログ/Nginxログを検索
- 購入アイテム情報: `usr_store_product_histories`から取得

---

#### 3.1.6. ~~log_webstore_fraud_detection~~ (削除)

**変更理由**: `is_country_mismatch`フラグのみ記録。詳細ログはアプリケーションログで管理

---

#### 3.1.7. ~~log_webstore_integration_failures~~ (削除)

**変更理由**: Laravel Queueの`failed_jobs`テーブルで再送管理。`usr_webstore_transactions`の`bank_status`/`adjust_status`で最終結果を記録

---

#### 3.1.8. ~~log_webstore_item_grant_failures~~ (削除)

**変更理由**: `usr_webstore_transactions`の`item_grant_status`で管理。詳細はアプリケーションログで記録

---

### 3.2. 既存テーブルの変更

#### 3.2.1. usr_store_product_histories

**要件ID**: REQ-D-009
**優先度**: 必須
**トレーサビリティ**:
- 02_サーバー要件_コード調査追記.md: 追加要件1
- 03_サーバー仕様レビュー.md: 6.1節

**変更内容**:
```sql
ALTER TABLE usr_store_product_histories
ADD COLUMN order_id VARCHAR(255) AFTER receipt_unique_id,
ADD COLUMN invoice_id VARCHAR(255) AFTER order_id,
ADD COLUMN transaction_id VARCHAR(255) AFTER invoice_id,
ADD UNIQUE INDEX idx_order_id (order_id);

-- invoice_id, currency_codeをNULL許容に変更（無料アイテム対応）
ALTER TABLE usr_store_product_histories
MODIFY COLUMN invoice_id VARCHAR(255) NULL,
MODIFY COLUMN currency_code VARCHAR(16) NULL;
```

**変更理由**:
- 外部決済固有の情報（order.id, order.invoice_id, transaction_id）を保存
- 無料アイテム・クーポン交換の場合、invoice_id, currency_codeがNULL

---

#### 3.2.2. usr_users

**要件ID**: REQ-D-010
**優先度**: 高（確認が必要）
**トレーサビリティ**:
- 04_ゲーム体験仕様確認結果まとめ.md: Q1, Q7

**確認事項**:
- `usr_users.birthday`カラムの存在確認
  - 存在しない場合: マイグレーションでカラム追加
- `usr_users.bn_country`カラムの存在確認
  - 存在しない場合: マイグレーションでカラム追加、またはバンダイナムコID APIから取得

**追加が必要な場合のマイグレーション**:
```sql
-- birthdayカラム（必要な場合）
ALTER TABLE usr_users
ADD COLUMN birthday DATE NULL AFTER bn_user_id;

-- bn_countryカラム（必要な場合）
ALTER TABLE usr_users
ADD COLUMN bn_country VARCHAR(2) NULL AFTER birthday;
```

---

## 4. セキュリティ要件

### 4.1. ウェブフック署名検証

**要件ID**: REQ-S-001
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件14
- 02_サーバー要件_コード調査追記.md: 追加要件12
- 03_サーバー仕様レビュー.md: 2.7節

**制御ルール**:
- Xsollaからのウェブフックリクエストの署名を検証
- 署名検証に失敗した場合は、400エラーを返す
- Xsollaの署名検証アルゴリズムに準拠（SHA-256ハッシュ等）

**実装方式**:
- Laravel Middlewareで実装
- 環境変数`XSOLLA_WEBHOOK_SECRET`を使用
- W3, W4, W5のエンドポイントに適用

**環境変数**:
```env
XSOLLA_WEBHOOK_SECRET=your_secret_key
```

---

### 4.2. ウェブフック通知元IPアドレスの制限

**要件ID**: REQ-S-002
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件15
- 02_サーバー要件_コード調査追記.md: 追加要件12
- 03_サーバー仕様レビュー.md: 2.7節

**制御ルール**:
- ウェブフックエンドポイントへのアクセスを特定のIPアドレスに制限
- 許可IPアドレス以外からのリクエストは拒否

**実装方式（推奨）**:
- インフラレベル（ALB/NLB、CloudFront + WAF）でIP制限
- 理由: アプリケーション層に到達する前にブロック、DDoS攻撃への対応が容易

**代替案**:
- Laravel Middlewareで実装
- 環境変数`XSOLLA_ALLOWED_IPS`にカンマ区切りでIPアドレスを設定

---

## 5. 連携要件

### 5.1. Bank連携

**要件ID**: REQ-I-001
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件13
- 02_サーバー要件_コード調査追記.md: 追加要件9
- 03_サーバー仕様レビュー.md: 2.10節
- 04_ゲーム体験仕様確認結果まとめ.md: Q4

**制御ルール**:
- 外部決済の購入データをBankシステムに送信
- W5（注文支払い成功）で、アイテム付与成功後にBank送信
- 送信失敗時: ログ記録 + 非同期再送（最大3回）
- 再送が3回失敗した場合: アラート通知（Slack/メール）

**送信内容**:
- `usr_user_id`: ユーザーID
- `event_id`: "100"（購入イベント）
- `country_code`: カスタムパラメータの`country_from_ip`または`usr_webstore_infos.country_code`
- `currency_code`: `order.currency`
- `purchase_amount`: `order.amount`
- その他必要な情報

**実装方式**:
- 既存の`LogBankService`を使用
- 失敗時は`log_webstore_integration_failures`に記録
- Laravel Queueで非同期再送

---

### 5.2. Adjust連携

**要件ID**: REQ-I-002
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件12
- 02_サーバー要件_コード調査追記.md: 追加要件10
- 03_サーバー仕様レビュー.md: 2.10節
- 04_ゲーム体験仕様確認結果まとめ.md: Q6

**制御ルール**:
- アイテム付与時にAdjustへクライアントIPアドレスを含む情報を送信
- W5（注文支払い成功）で、アイテム付与成功後にAdjust送信
- 送信失敗時: ログ記録 + 非同期再送（最大3回）
- 再送が3回失敗した場合: アラート通知（Slack/メール）

**送信内容**:
- `user_ip`: カスタムパラメータの`user_ip`
- `usr_user_id`: カスタムパラメータの`internal_id`
- `event_token`: 環境変数`ADJUST_WEBSTORE_PURCHASE_TOKEN`
- `revenue`: `order.amount`
- `currency`: `order.currency`

**実装方式**:
- 既存の`AdjustService`を使用（存在する場合）
- 存在しない場合: 新規実装、またはMVPから除外
- 失敗時は`log_webstore_integration_failures`に記録
- Laravel Queueで非同期再送

**環境変数**:
```env
ADJUST_WEBSTORE_PURCHASE_TOKEN=abc123xyz
```

---

## 6. 制約要件

### 6.1. デッドロック対策

**要件ID**: REQ-C-001
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件26
- 02_サーバー要件_コード調査追記.md: 追加要件14
- 03_サーバー仕様レビュー.md: 2.9節

**制御ルール**:
- アプリ内課金とWeb課金が同時発生した場合、デッドロックが発生しないようにする

**実装方式**:
1. **テーブルへのアクセス順序を統一**
   - 常に以下の順序でロック取得
     1. `usr_users`
     2. `usr_store_infos`
     3. `usr_store_products` / `usr_external_payment_products`
     4. `usr_items`
     5. `usr_store_product_histories`（最後に挿入）

2. **楽観的ロック（推奨）**
   - `usr_store_infos`に`version`カラムを追加
   - 更新時にバージョンをチェック

3. **トランザクション分離レベルの調整**
   - `READ COMMITTED`または`REPEATABLE READ`を使用
   - デッドロック発生時のリトライロジックを実装

---

### 6.2. ウェブフックリトライへの対応

**要件ID**: REQ-C-002
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件33, 34
- 02_サーバー要件_コード調査追記.md: 追加要件25

**制御ルール**:
- Xsolla側が自動的にリトライを行うため、サーバー側はべき等性を保証する必要がある
- 同じ`order.id`で複数回リクエストが来た場合、1回目の処理結果を返す実装が必要
- リトライ回数の上限:
  - W4（支払い）: 最大12回
  - W5（注文支払い成功）: 最大19回

---

### 6.3. 誕生日情報の必須化

**要件ID**: REQ-C-003
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件27
- 02_サーバー要件_コード調査追記.md: 追加要件22
- 04_ゲーム体験仕様確認結果まとめ.md: Q1

**制御ルール**:
- ユーザー情報取得API（W1）で`birthday`と`birthday_month`を返す必要がある
- 誕生日情報がない場合、空文字で返すとログインできない
- `usr_users.birthday`がNULLの場合: 400エラー「BIRTHDAY_REQUIRED」を返す
- エラーメッセージ: "Birthday information is required. Please register your birthday in the profile settings."

---

### 6.4. 国コード・通貨コードの不変性

**要件ID**: REQ-C-004
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件31

**制御ルール**:
- 一度登録した国コード情報は更新しない
- `usr_webstore_infos.registered_at`で初回登録日時を記録
- 既に登録されている場合、国コードの更新リクエストは無視

---

## 7. 非機能要件

### 7.1. ログ記録とトレーサビリティ

**要件ID**: REQ-N-001
**優先度**: 必須
**トレーサビリティ**:
- 02_サーバー要件_コード調査追記.md: 追加要件21
- 03_サーバー仕様レビュー.md: 2.15節

**制御ルール**:
- 以下のログを記録する:
  1. **ウェブフック受信ログ**: すべてのウェブフックリクエストを`log_webstore_webhooks`に記録
  2. **処理結果ログ**: 成功/失敗、エラー内容を`log_webstore_transactions`に記録
  3. **不正購入検知ログ**: 国コード不一致、年齢制限違反等を`log_webstore_fraud_detection`に記録
  4. **外部連携失敗ログ**: Bank/Adjust連携失敗を`log_webstore_integration_failures`に記録
  5. **アイテム付与失敗ログ**: アイテム付与失敗を`log_webstore_item_grant_failures`に記録

**ログ形式**:
- リクエストボディ全体をJSON形式で保存
- `request_id`, `nginx_request_id`を記録
- エラーコード、エラーメッセージを記録

---

### 7.2. ウェブフックの死活監視

**要件ID**: REQ-N-002
**優先度**: 推奨
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件30
- 02_サーバー要件_コード調査追記.md: 追加要件20
- 03_サーバー仕様レビュー.md: 2.14節

**制御ルール**:
- ウェブフックエンドポイントの正常性を監視
- 異常検知時にアラート通知

**実装方式**:
1. **ヘルスチェックエンドポイントの実装**
   - DB接続確認を含む
2. **CloudWatch Alarmsでエラー率を監視**
   - 5xx系エラーが一定以上発生した場合、アラート
   - SNS経由でSlack/メール通知
3. **定期的なテストリクエストの送信（オプション）**
   - Xsolla側から定期的にヘルスチェックを送信

---

### 7.3. パフォーマンス要件

**要件ID**: REQ-N-003
**優先度**: 必須
**トレーサビリティ**: 推測（明示的な記載なし）

**制御ルール**:
- ウェブフックのレスポンス時間: 5秒以内
- アイテム付与処理: 10秒以内
- Bank/Adjust連携が遅い場合でも、ウェブフックのレスポンスには影響しない（非同期処理）

---

## 8. エラーハンドリング要件

### 8.1. エラーコード一覧

**要件ID**: REQ-E-001
**優先度**: 必須
**トレーサビリティ**:
- 02_サーバー要件_コード調査追記.md: 追加要件13
- 03_サーバー仕様レビュー.md: 2.11節
- 04_ゲーム体験仕様確認結果まとめ.md: 4.3節

| エラーコード | HTTPステータス | 説明 | トリガー |
|------------|---------------|------|---------|
| `WEBSTORE_USER_NOT_FOUND` | 400 | ユーザーが未登録 | W1 |
| `WEBSTORE_BIRTHDAY_REQUIRED` | 400 | 誕生日情報が未登録 | W1 |
| `WEBSTORE_USER_TOO_YOUNG` | 400 | 13歳以下（海外）でログイン不可 | W1 |
| `WEBSTORE_COUNTRY_MISMATCH` | 400 | ダウンロード国と居住国が不一致 | W1 |
| `WEBSTORE_PURCHASE_NOT_ALLOWED` | 400 | 購入権限なし | W2 |
| `WEBSTORE_PURCHASE_NOT_ALLOWED_FOR_MINOR` | 400 | 未成年で有料商品を購入しようとした（現在の基準: 日本18歳） | W2 |
| `WEBSTORE_PURCHASE_NOT_ALLOWED_CHILD_ACCOUNT` | 400 | 子供アカウント（海外）で有料商品を購入しようとした | W2 |
| `WEBSTORE_PURCHASE_COUNT_LIMIT` | 400 | 購入回数上限 | W2 |
| `WEBSTORE_DUPLICATE_ORDER` | 400 | 同一order.idで重複リクエスト | W5 |
| `WEBSTORE_TRANSACTION_NOT_FOUND` | 400 | トランザクションIDが見つからない | W5 |
| `WEBSTORE_TRANSACTION_EXPIRED` | 400 | トランザクションIDの有効期限切れ | W5 |
| `WEBSTORE_NO_VIRTUAL_GOOD_ITEMS` | 400 | items配列にtype=virtual_goodが存在しない | W2, W5 |
| `WEBSTORE_SIGNATURE_INVALID` | 400 | 署名検証失敗 | W3, W4, W5 |
| `WEBSTORE_MAINTENANCE` | 503 | メンテナンス中 | W1, W2, W5 |
| `WEBSTORE_INTERNAL_ERROR` | 500 | 内部エラー（リトライ可能） | W1-W5 |

---

### 8.2. 永続エラーと一時エラーの区別

**要件ID**: REQ-E-002
**優先度**: 必須
**トレーサビリティ**:
- 03_サーバー仕様レビュー.md: 2.11節

**永続エラー（400）**:
- リトライしても解決しないエラー
- 返金が必要
- 例: 年齢制限違反、購入回数上限、国コード不一致、重複注文

**一時エラー（500）**:
- リトライすれば解決する可能性があるエラー
- 例: DB接続エラー、タイムアウト、一時的なリソース不足

**メンテナンスエラー（503）**:
- メンテナンス中
- Xsolla側でリトライ

---

### 8.3. アイテム付与失敗時の挙動

**要件ID**: REQ-E-003
**優先度**: 必須
**トレーサビリティ**:
- 04_ゲーム体験仕様確認結果まとめ.md: Q5

**制御ルール**:
アイテム付与失敗時の挙動はエラーの種類により異なる:

**1. リトライ可能なエラー（一時エラー）**:
- DB接続エラー、タイムアウト等
- 500エラーを返してXsollaにリトライさせる
- Xsollaは最大19回まで自動リトライ

**2. リトライ不可能なエラー（永続エラー）**:
- リソース上限超過、データ不整合等
- 以下の処理を実行:
  1. エラーログを記録
  2. `log_webstore_item_grant_failures`テーブルに保存
  3. CloudWatch Alarmsでアラート送信（Slack/メール）
  4. 200を返す（購入は成功扱い）
  5. 運用チームが手動で付与

---

### 8.4. 返金処理の実装

**要件ID**: REQ-E-004
**優先度**: 必須
**トレーサビリティ**:
- 01_サーバー要件抽出.md: 要件34
- 02_サーバー要件_コード調査追記.md: 追加要件7
- 03_サーバー仕様レビュー.md: 2.19節

**制御ルール**:
- W5（注文支払い成功）で永続エラーが発生した場合、400エラーを返す
- 400エラーを返すと、Xsolla側でリトライは行わない
- 返金処理は本実装範囲外（別途対応）

**実装方針**:
1. 永続エラー発生時、`usr_webstore_transactions.status = 'failed'`に設定
2. `error_code`に詳細なエラーコードを記録
3. アプリケーションログに詳細情報を記録
4. CloudWatch Alarmsでアラート送信

---

## 9. 残課題と注意事項

### 9.1. 実装前に確認が必要な項目

#### ⚠️ ブロッカー確認事項

**要件ID**: REQ-R-001
**優先度**: 最高
**トレーサビリティ**:
- 04_ゲーム体験仕様確認結果まとめ.md: 5.2節

1. **`usr_users.birthday`カラムの存在確認**
   - 確認方法: テーブル定義を確認
   - 存在しない場合: マイグレーションでカラム追加
   - 影響: W1（ユーザー情報取得）を実装できない

2. **`usr_users.bn_country`カラムの存在確認**
   - 確認方法: テーブル定義を確認
   - 存在しない場合: マイグレーションでカラム追加、またはバンダイナムコID APIから取得
   - 影響: 国コード照合処理を実装できない

3. **`AdjustService`の実装確認**
   - 確認方法: コードベース調査
   - 存在しない場合: 新規実装、またはMVPから除外
   - 影響: Adjust連携を実装できない

---

### 9.2. プランナー確認が必要な仮定

**要件ID**: REQ-R-002
**優先度**: 高
**トレーサビリティ**:
- 04_ゲーム体験仕様確認結果まとめ.md: 1節

⚠️ **重要**: 本要件書は、技術的に妥当な仮定を基に作成されています。以下の仮定について、実際のプランナー確認が必須です。

1. **誕生日情報の取得方法**: `usr_users.birthday`カラムから取得（Q1）
2. **購入回数制限の管理**: 外部決済は別管理（Q2）
3. **トランザクションIDの有効期限**: 24時間（Q3）
4. **Bank/Adjust連携失敗時**: ログ記録 + 200返却（Q4）
5. **アイテム付与失敗時**: ログ記録 + アラート通知 + 手動対応（Q5）
6. **Adjust連携**: 既存の`AdjustService`を活用（Q6）
7. **バンダイナムコID居住国**: `usr_users.bn_country`から取得（Q7）
8. **その他**:
   - アイテム反映のポップアップ通知: クライアント側で実装
   - 無料アイテムの年齢制限: 適用しない
   - 子供アカウントの判定: 年齢のみで判定（14-17歳）

---

### 9.3. 実装の優先順位

**要件ID**: REQ-R-003
**優先度**: 中
**トレーサビリティ**: 推測

#### MVP（最小限の製品）に必須の機能:
1. W1-W5のウェブフックエンドポイント実装
2. ユーザー認証とアプリ連携
3. 年齢・国による制御
4. アイテム付与処理
5. べき等性の保証（重複処理防止）
6. セキュリティ実装（署名検証、IP制限）

#### MVP後に実装可能な機能:
1. Adjust連携（MVPから除外も可）
2. ウェブフックの死活監視
3. デバッグ用国コード上書き機能
4. 管理ツールでの失敗一覧画面

---

### 9.4. スケジュール見積もり

**要件ID**: REQ-R-004
**優先度**: 参考情報
**トレーサビリティ**:
- 03_サーバー仕様レビュー.md: 5節

#### 最小ケース（すべての不明点が迅速に解決）:
- 実装期間: 4-6週間
- 内訳:
  - 設計・レビュー: 1週間
  - 実装: 2-3週間
  - テスト: 1-2週間

#### 最大ケース（誕生日入力画面の追加が必要）:
- 実装期間: 6-9週間
- 内訳:
  - 設計・レビュー: 1-2週間
  - 実装: 3-5週間（画面追加を含む）
  - テスト: 2週間

---

### 9.5. リスク評価

**要件ID**: REQ-R-005
**優先度**: 参考情報
**トレーサビリティ**:
- 03_サーバー仕様レビュー.md: 5節
- 04_ゲーム体験仕様確認結果まとめ.md: 7.2節

| リスク | 深刻度 | 対応策 |
|-------|--------|--------|
| 仮定が実際の仕様と異なる | 高 | プランナー確認の早期実施 |
| `birthday`カラムが存在しない | 中 | テーブル定義確認 + マイグレーション |
| `bn_country`カラムが存在しない | 中 | テーブル定義確認 + API実装 |
| `AdjustService`が存在しない | 低 | MVPから除外も可 |
| デッドロックの発生 | 中 | テーブルアクセス順序の統一 + 楽観的ロック |
| 返金処理の運用負荷 | 中 | 管理ツールでの返金対象一覧画面を実装 |

---

### 9.6. 開発着手の判断基準

**要件ID**: REQ-R-006
**優先度**: 最高
**トレーサビリティ**:
- 04_ゲーム体験仕様確認結果まとめ.md: 7.3節

**着手可能な条件**:
- [ ] ブロッカー確認事項（3件）の解消
  - [ ] `usr_users.birthday`カラムの確認
  - [ ] `usr_users.bn_country`カラムの確認
  - [ ] `AdjustService`の実装確認
- [ ] プランナーへの確認依頼（Q1-Q8）の送付
- [ ] 新規テーブル設計が確定
- [ ] エラーコードが確定

**確認結果の待機中に実施可能な作業**:
- テーブル設計のドラフト作成
- エラーコードの定義
- ログ設計
- 既存コードの詳細調査（Adjust連携、デッドロック対策等）

---

### 9.7. 参照ドキュメント

**要件ID**: REQ-R-007
**優先度**: 参考情報

**本要件書の入力ドキュメント**:
1. `docs/sdd/features/外部決済/01_サーバー要件抽出.md`
   - ゲーム体験仕様書PDFから抽出した34個の要件
2. `docs/sdd/features/外部決済/02_サーバー要件_コード調査追記.md`
   - 既存コードベース調査で判明した27個の追加要件
3. `docs/sdd/features/外部決済/03_サーバー仕様レビュー.md`
   - サーバー観点での仕様詳細化と8個の不明点
4. `docs/sdd/features/外部決済/04_ゲーム体験仕様確認結果まとめ.md`
   - プランナー確認結果（仮定による暫定版）

**関連ドキュメント**:
- ゲーム体験仕様書PDF: `docs/sdd/features/外部決済/ゲーム体験仕様書.pdf`
- WEB STORE開発ドキュメント (2025.05.01)

---

## 10. 要件トレーサビリティマトリックス

| 要件ID | 要件名 | 出典 | ステータス |
|-------|-------|------|-----------|
| REQ-F-001 | W1: ユーザー情報取得 | Stage 1, 2, 4 | 確定（要確認） |
| REQ-F-002 | W2: 決済事前確認 | Stage 1, 2, 4 | 確定（要確認） |
| REQ-F-003 | W3: ユーザー検証 | Stage 1, 3 | 確定 |
| REQ-F-004 | W4: 支払い | Stage 1, 2 | 確定 |
| REQ-F-005 | W5: 注文支払い成功 | Stage 1, 2, 3, 4 | 確定（要確認） |
| REQ-F-006 | 年齢制限（日本） | Stage 1, 2, 4 | 確定 |
| REQ-F-007 | 年齢制限（海外） | Stage 1, 4 | 確定（要確認） |
| REQ-F-008 | 国コード制御 | Stage 1, 2, 4 | 確定（要確認） |
| REQ-F-009 | 購入回数制限 | Stage 2, 4 | 確定（要確認） |
| REQ-F-010 | べき等性の保証 | Stage 1, 2, 3 | 確定 |
| REQ-F-011 | sandboxモード判定 | Stage 1, 2, 3 | 確定 |
| REQ-F-012 | 国コード・通貨コード管理 | Stage 1, 2, 3 | 確定 |
| REQ-F-013 | トランザクションID管理 | Stage 2, 3, 4 | 確定（要確認） |
| REQ-F-014 | items配列フィルタリング | Stage 1, 2, 3 | 確定 |
| REQ-F-015 | 無料アイテム特殊処理 | Stage 1, 2, 3, 4 | 確定（要確認） |
| REQ-F-016 | キャンセルウェブフック | Stage 1, 2, 3 | 確定 |
| REQ-D-001~008 | 新規テーブル設計 | Stage 2, 3, 4 | 確定 |
| REQ-D-009~010 | 既存テーブル変更 | Stage 2, 3, 4 | 確定（要確認） |
| REQ-S-001~002 | セキュリティ要件 | Stage 1, 2, 3 | 確定 |
| REQ-I-001~002 | 連携要件 | Stage 1, 2, 3, 4 | 確定（要確認） |
| REQ-C-001~004 | 制約要件 | Stage 1, 2, 4 | 確定 |
| REQ-N-001~003 | 非機能要件 | Stage 2, 3 | 確定 |
| REQ-E-001~004 | エラーハンドリング | Stage 2, 3, 4 | 確定（要確認） |
| REQ-R-001~007 | 残課題・注意事項 | Stage 3, 4 | 確定（要確認） |

**ステータス凡例**:
- **確定**: 仕様が確定し、実装可能
- **確定（要確認）**: 技術的に妥当な仮定を基に確定しているが、プランナー確認が必要

---

## 11. 変更履歴

| 日付 | バージョン | 変更内容 | 変更者 |
|-----|----------|---------|--------|
| 2025-12-02 | 1.0 | 初版作成（Stage 1-4統合） | Claude Code |

---

**最終更新**: 2025-12-02
**次回更新**: プランナー確認完了後、ブロッカー確認事項解消後
