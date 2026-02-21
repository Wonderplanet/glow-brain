# API実装全体概要設計

## 1. ドキュメント情報
- 対象機能: 外部決済（WEB STORE連携）
- 作成日: 2025-12-02
- 参照ドキュメント: 05_サーバーAPI要件書.md

## 2. API実装全体像

### 2.1 関連するAPIエンドポイント一覧

| エンドポイント | 種別 | 関連要件ID | 概要 |
|-------------|------|-----------|------|
| POST /api/shop/webstore | 新規作成 | REQ-F-001〜REQ-F-005 | 外部決済ウェブフック統合エンドポイント |

**備考**:
- 仕様書に「ウェブフックのURLは単一のURLに対してすべて通知を行う。※ウェブフックごとにURLをわけることはできません。」との記載があるため、すべてのウェブフックを1つのエンドポイントで受ける
- `notification_type`フィールドでウェブフック種別を判定し、内部で適切な処理にルーティング
  - `web_store_user_validation` → W1: ユーザー情報取得
  - `web_store_payment_validation` → W2: 決済事前確認
  - `user_validation` → W3: Xsollaユーザー検証
  - `payment` → W4: Xsolla支払い通知受信
  - `order_paid` → W5: Xsolla注文支払い成功
- 既存のShopController（アプリ内課金用）とは独立

### 2.2 新規作成APIエンドポイント

#### POST /api/shop/webstore（統合ウェブフックエンドポイント）
- **関連要件:** REQ-F-001〜REQ-F-005, REQ-F-006〜REQ-F-016, REQ-I-001, REQ-I-002
- **役割:** Xsollaからの全ウェブフック通知を受信し、`notification_type`で処理を振り分け
- **新規作成が必要な理由:**
  - 仕様書の要求「ウェブフックのURLは単一のURLに対してすべて通知を行う」
  - 既存APIは認証済みユーザー向けで、外部システム（Xsolla）からの未認証リクエストに対応していない
  - 外部決済固有のトランザクション管理、べき等性保証が必要
  - 署名検証やIP制限など特殊なセキュリティ要件

**処理フロー概要:**
1. リクエスト受信時に`notification_type`を判定
2. 対応するUseCaseに処理を委譲
3. 共通のログ記録処理（log_webstore_webhooks）

**notification_type別処理:**

##### W1: `web_store_user_validation`（ユーザー情報取得）
- **関連要件:** REQ-F-001, REQ-F-006, REQ-F-007, REQ-F-008
- **主要処理:**
  - バンダイナムコIDからアプリユーザーIDを検索
  - 国コード・通貨コード・誕生日情報を返却
  - 年齢制限（13歳以下は海外のみログイン不可）
  - 国コード照合（ダウンロード国と居住国の不一致チェック）

##### W2: `web_store_payment_validation`（決済事前確認）
- **関連要件:** REQ-F-002, REQ-F-006, REQ-F-007, REQ-F-009, REQ-F-013, REQ-F-014
- **主要処理:**
  - 年齢制限チェック（18歳未満は有料商品購入不可）
  - 購入回数制限チェック
  - トランザクションID発行（UUID v4、有効期限24時間）
  - 国コード不一致のログ記録

##### W3: `user_validation`（Xsollaユーザー検証）
- **関連要件:** REQ-F-003
- **主要処理:**
  - カスタムパラメータからinternal_idを取得
  - ユーザー存在確認

##### W4: `payment`（Xsolla支払い通知）
- **関連要件:** REQ-F-004, REQ-F-011
- **主要処理:**
  - 署名検証
  - sandboxモード判定（dry_run=1の場合）
  - トランザクションログ記録

##### W5: `order_paid`（Xsolla注文支払い成功）
- **関連要件:** REQ-F-005, REQ-F-010, REQ-F-011, REQ-F-015, REQ-I-001, REQ-I-002
- **主要処理:**
  - 署名検証
  - 重複チェック（べき等性保証）
  - トランザクションIDの照合と有効期限チェック
  - アイテム付与処理
  - Bank連携（購入データ送信）
  - Adjust連携（クライアントIP送信）
  - 購入回数更新

### 2.3 既存API改修エンドポイント

**該当なし**

**理由:**
- 外部決済機能は既存のアプリ内課金機能と完全に独立
- 既存APIエンドポイントへの影響なし
- 新規ウェブフックエンドポイントとして実装

## 3. 要件とAPIの対応関係

### 3.1 要件 REQ-F-001: W1 ユーザー情報取得（web_store_user_validation）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_user_validation）
- **実装概要:**
  - バンダイナムコIDからアプリユーザーを検索
  - 国コード・通貨コード・誕生日情報を返却
  - 年齢制限・国コード照合を実施
- **備考:** Web Storeログイン時の最初のウェブフック

### 3.2 要件 REQ-F-002: W2 決済事前確認（web_store_payment_validation）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
- **実装概要:**
  - 年齢・購入回数制限をチェック
  - トランザクションID発行
  - 国コード不一致のログ記録
- **備考:** 購入前の権限検証、REQ-F-009（購入回数制限）も実現

### 3.3 要件 REQ-F-003: W3 ユーザー検証（user_validation - Xsolla）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: user_validation）
- **実装概要:**
  - internal_idからユーザー存在確認
- **備考:** Xsolla購入直前の最終確認

### 3.4 要件 REQ-F-004: W4 支払い（payment - Xsolla）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: payment）
- **実装概要:**
  - 署名検証
  - sandboxモード判定
  - トランザクションログ記録
- **備考:** REQ-F-011（sandboxモード判定）も実現

### 3.5 要件 REQ-F-005: W5 注文支払い成功（order_paid - Xsolla）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - 重複チェック（べき等性保証）
  - トランザクションIDの照合
  - アイテム付与処理
  - Bank/Adjust連携
- **備考:**
  - REQ-F-010（べき等性）、REQ-F-015（無料アイテム対応）を実現
  - REQ-I-001（Bank連携）、REQ-I-002（Adjust連携）も実現

### 3.6 要件 REQ-F-006: 年齢制限（日本）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
- **実装概要:**
  - 18歳未満は有料商品購入不可
  - order.amount == 0 または order.currency == null の場合は許可
- **備考:** W2で実現

### 3.7 要件 REQ-F-007: 年齢制限（海外）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_user_validation）
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
- **実装概要:**
  - 13歳以下: W1でログイン拒否
  - 14-17歳: W2で有料商品購入拒否
- **備考:** W1とW2で実現

### 3.8 要件 REQ-F-008: 国コード制御（海外のみ）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_user_validation）
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
- **実装概要:**
  - W1: ダウンロード国と居住国の照合（不一致時はログイン不可）
  - W2: アクセス国と居住国の不一致ログ記録
- **備考:** W1とW2で実現

### 3.9 要件 REQ-F-009: 購入回数制限
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - W2: 購入回数制限チェック
  - W5: 購入回数更新
- **備考:** usr_external_payment_productsテーブルで管理

### 3.10 要件 REQ-F-010: べき等性の保証（重複処理防止）
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - order.idで重複チェック
  - 既に処理済みの場合は1回目の処理結果を返す
- **備考:** usr_store_product_histories.receipt_unique_idにユニークインデックス

### 3.11 要件 REQ-F-011: sandboxモードの判定
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: payment）
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - W4: transaction.dry_run=1の場合はテスト決済
  - W5: order.mode="sandbox"の場合はテスト決済
  - is_sandboxフラグを記録
- **備考:** テスト決済と本番決済を明確に区別

### 3.12 要件 REQ-F-012: 国コード・通貨コードの管理
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_user_validation）
- **実装概要:**
  - W1で国コード・通貨コードを返却
  - 未登録の場合はusr_webstore_infosに登録
  - 一度登録したら更新しない
- **備考:** usr_webstore_infosテーブルで管理

### 3.13 要件 REQ-F-013: トランザクションID管理
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - W2: トランザクションID発行（UUID v4、有効期限24時間）
  - W5: トランザクションIDと照合、有効期限チェック
- **備考:** log_webstore_transactionsテーブルで管理

### 3.14 要件 REQ-F-014: items配列のフィルタリング
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: web_store_payment_validation）
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - items配列からtype=virtual_goodのみをフィルタ
  - 他のタイプ（クーポン、無料アイテム等）は無視
- **備考:** W2とW5で実現

### 3.15 要件 REQ-F-015: 無料アイテム・クーポンの特殊処理
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - order.invoice_id, order.currencyがNULLの場合に対応
  - usr_store_product_histories.invoice_id, currency_codeをNULL許容に変更
  - 年齢制限の判定で、無料アイテムは許可
- **備考:** W5で実現

### 3.16 要件 REQ-F-016: キャンセルウェブフックの処理
- **実現に必要なAPI:**
  - （実装しない）
- **実装概要:**
  - キャンセル通知が来た場合は500エラーを返す
  - システム的なキャンセル連携は行わない
- **備考:** MVPでは対応しない

### 3.17 要件 REQ-I-001: Bank連携
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - アイテム付与成功後にBank送信
  - 失敗時はログ記録 + 非同期再送（最大3回）
- **備考:** 既存のLogBankServiceを使用

### 3.18 要件 REQ-I-002: Adjust連携
- **実現に必要なAPI:**
  - POST /api/shop/webstore（notification_type: order_paid）
- **実装概要:**
  - アイテム付与成功後にAdjust送信
  - 失敗時はログ記録 + 非同期再送（最大3回）
- **備考:** 既存のAdjustService（存在する場合）を使用、MVPから除外も可

## 4. 既存APIだけでは実現困難な項目

### 4.1 外部システム（Xsolla）からのウェブフック受信
- **困難な理由:**
  - 既存APIはすべて認証済みユーザー向け（auth:apiミドルウェア）
  - ウェブフックは外部システムからの未認証リクエスト
  - 署名検証、IP制限など特殊なセキュリティ要件が必要
  - べき等性の保証が必須（Xsollaのリトライ対応）
- **解決策:** 新規ウェブフックエンドポイントを作成
  - POST /api/shop/webstore（notification_typeで処理振り分け）

### 4.2 バンダイナムコIDベースのユーザー検索
- **困難な理由:**
  - 既存APIはアプリのユーザーIDベース
  - W1（ユーザー情報取得）ではバンダイナムコIDからアプリユーザーを検索する必要がある
  - 既存のUserControllerではこの逆引き処理に対応していない
- **解決策:** 新規エンドポイント POST /api/shop/webstore でW1処理を実装

### 4.3 外部決済固有のトランザクション管理
- **困難な理由:**
  - 既存のallowance APIはアプリ内課金用で、トランザクションID管理がない
  - 外部決済では、決済事前確認（W2）でトランザクションID発行、注文支払い成功（W5）で照合が必要
  - トランザクションIDの有効期限管理（24時間）が必要
- **解決策:** 新規エンドポイント POST /api/shop/webstore でW2/W5処理を実装

### 4.4 外部決済独自の年齢・購入回数制限
- **困難な理由:**
  - 既存のShopControllerはアプリ内課金用で、外部決済独自の制限ロジックがない
  - 外部決済では、usr_external_payment_productsテーブルで購入回数を別管理
  - 年齢制限のロジックも外部決済独自（18歳未満は有料商品購入不可）
- **解決策:** 新規エンドポイント POST /api/shop/webstore でW2処理を実装

### 4.5 無料アイテム・クーポン交換の特殊処理
- **困難な理由:**
  - 既存のpurchase APIは有料課金を前提
  - 外部決済では、order.invoice_id, order.currencyがNULLの無料アイテムやクーポン交換に対応が必要
  - usr_store_product_histories.invoice_id, currency_codeをNULL許容に変更する必要がある
- **解決策:** 新規エンドポイント POST /api/shop/webstore でW5処理を実装

### 4.6 既存APIの改修で対応可能な項目
- **該当なし**
- 外部決済機能は既存のアプリ内課金機能と完全に独立しているため、既存API改修は不要

## 5. 実装優先順位

### 5.1 優先度：高（基盤となる処理）
1. POST /api/shop/webstore - 統合エンドポイントの基本実装
   - notification_type振り分けロジック
   - 共通ログ記録処理
2. W1処理（web_store_user_validation） - Web Storeログインの入口
3. W2処理（web_store_payment_validation） - 購入権限検証
4. W5処理（order_paid） - アイテム付与処理の中核

### 5.2 優先度：中（連動機能の処理）
1. W3処理（user_validation） - Xsolla仕様に必須
2. W4処理（payment） - 決済フロー完結に必要

### 5.3 実装順序の推奨
1. **フェーズ1: エンドポイント基盤とW1/W2/W5実装**
   - POST /api/shop/webstore エンドポイント作成
   - W1: ユーザー情報取得処理
   - W2: 決済事前確認処理
   - W5: 注文支払い成功処理
   - 理由: この3つでWeb Store→購入→アイテム付与の基本フローが完成

2. **フェーズ2: W3/W4実装**
   - W3: Xsollaユーザー検証処理
   - W4: Xsolla支払い通知受信処理
   - 理由: Xsolla仕様に準拠した完全なウェブフックフローを実現

3. **フェーズ3: 外部連携実装**
   - Bank連携（W5処理内）
   - Adjust連携（W5処理内、MVPから除外も可）
   - 理由: 購入成功に直接影響しないため、後回し可能

### 5.4 実装時の注意事項
1. **セキュリティ実装を最優先**
   - 署名検証（Xsollaウェブフック）
   - IP制限（インフラレベル推奨）
   - これらがないと本番環境で使用不可

2. **べき等性の保証**
   - POST /webhooks/xsolla/order_paidでの重複チェックは必須
   - Xsollaは最大19回リトライするため、べき等性がないと重複付与が発生

3. **ログ記録の徹底**
   - すべてのウェブフックリクエストを記録（log_webstore_webhooks）
   - トランザクションログを記録（log_webstore_transactions）
   - 不正購入検知ログを記録（log_webstore_fraud_detection）

4. **エラーハンドリングの明確化**
   - 永続エラー（400）と一時エラー（500）を明確に区別
   - アイテム付与失敗時の挙動をエラー種別により分岐

## 6. ルーティング設計

### 6.1 ルーティンググループ

```php
// api/routes/api.php

// 外部決済ウェブフック（統合エンドポイント）
// Xsolla署名検証は必要に応じてMiddlewareで実施
// IP制限はインフラレベル（ALB/CloudFront）推奨
Route::post('/shop/webstore', [WebStoreWebhookController::class, 'handleWebhook']);
```

**設計方針:**
- 仕様書の要求「ウェブフックのURLは単一のURLに対してすべて通知を行う」に準拠
- `notification_type`フィールドでウェブフック種別を判定
- Controller内で各UseCaseに処理を委譲

### 6.2 ミドルウェア設計

1. **xsolla_signature_verification**: Xsolla署名検証（オプション）
   - Xsollaからのウェブフックの署名を検証
   - notification_typeが`user_validation`、`payment`、`order_paid`の場合のみ検証
   - 環境変数XSOLLA_WEBHOOK_SECRETを使用

2. **IP制限**: インフラレベルでの実装を推奨
   - ALB/NLBまたはCloudFront + WAFで実装
   - アプリケーション層ではなくインフラ層で制御

### 6.3 コントローラー設計

**WebStoreWebhookController**: 統合ウェブフックコントローラー
- `handleWebhook()`: エントリーポイント
  - リクエストから`notification_type`を取得
  - 対応するUseCaseに処理を委譲
  - 共通ログ記録（log_webstore_webhooks）

**処理振り分け:**
```php
public function handleWebhook(Request $request)
{
    $notificationType = $request->input('notification_type');

    switch ($notificationType) {
        case 'web_store_user_validation':
            return $this->userValidationUseCase->exec(...);
        case 'web_store_payment_validation':
            return $this->paymentValidationUseCase->exec(...);
        case 'user_validation':
            return $this->xsollaUserValidationUseCase->exec(...);
        case 'payment':
            return $this->xsollaPaymentUseCase->exec(...);
        case 'order_paid':
            return $this->xsollaOrderPaidUseCase->exec(...);
        default:
            throw new GameException(ErrorCode::WEBSTORE_INVALID_NOTIFICATION_TYPE);
    }
}
```

## 7. APIグループと責務

### 7.1 外部決済ウェブフック（統合エンドポイント）
- **責務:** Xsollaからの全ウェブフック通知の受信・処理
- **エンドポイント:**
  - POST /api/shop/webstore
- **処理内容:**
  - W1/W2: ユーザー認証、購入権限検証、トランザクションID発行
  - W3/W4/W5: 決済処理、アイテム付与、外部連携

### 7.2 既存ショップAPI（アプリ内課金）
- **責務:** アプリ内課金（Apple/Google課金）
- **影響:** なし（外部決済とは独立）
- **エンドポイント:**
  - POST /api/shop/allowance
  - POST /api/shop/purchase_pass
  - その他既存エンドポイント

## 8. 依存関係と実装順序

### 8.1 テーブル依存関係
1. **優先度：最高（実装前に必須）**
   - usr_webstore_infos（国コード・通貨コード管理）
   - usr_external_payment_products（購入回数管理）
   - log_webstore_transactions（トランザクション管理）
   - log_webstore_webhooks（ウェブフックログ）

2. **優先度：高（MVP実装に必須）**
   - log_webstore_fraud_detection（不正購入検知）
   - log_webstore_item_grant_failures（アイテム付与失敗記録）
   - log_webstore_integration_failures（外部連携失敗記録）

3. **優先度：中（MVP後に実装可能）**
   - usr_webstore_debug_overrides（デバッグ用）

4. **既存テーブルの変更**
   - usr_store_product_histories（order_id, invoice_id, transaction_idカラム追加）

### 8.2 実装順序
1. **マイグレーション実装**
   - 新規テーブル作成
   - 既存テーブル変更

2. **セキュリティ実装**
   - 署名検証ミドルウェア（オプション）
   - IP制限はインフラレベル推奨

3. **統合エンドポイント実装**
   - POST /api/shop/webstore エンドポイント作成
   - notification_type振り分けロジック
   - 共通ログ記録処理

4. **各ウェブフック処理実装**
   - W1: ユーザー情報取得処理
   - W2: 決済事前確認処理
   - W5: 注文支払い成功処理
   - W3: Xsollaユーザー検証処理
   - W4: Xsolla支払い通知受信処理

5. **外部連携実装**
   - Bank連携
   - Adjust連携

## 9. まとめ

### 9.1 新規作成が必要なAPI数
- **合計1エンドポイント**（新規作成）
  - POST /api/shop/webstore - 統合ウェブフックエンドポイント
  - notification_typeで5種類のウェブフック処理を振り分け

### 9.2 既存API改修
- **該当なし**
- 外部決済機能は既存のアプリ内課金機能と完全に独立

### 9.3 実装規模の見積もり
- **新規テーブル:** 7テーブル + 既存1テーブル変更
- **新規コントローラー:** 1コントローラー（1エンドポイント、notification_type振り分け）
- **新規ミドルウェア:** 1ミドルウェア（署名検証、オプション）
- **ドメイン層:** 5UseCase + 関連Services/Repositories
- **テスト:** 各ウェブフック処理3-5シナリオ

### 9.4 重要な設計判断
1. **単一エンドポイントで全ウェブフックを受信**
   - 理由: 仕様書の要求「ウェブフックのURLは単一のURLに対してすべて通知を行う」
   - 影響: notification_typeで処理を振り分けるController設計

2. **ウェブフックエンドポイントは既存APIから独立**
   - 理由: 認証方式、セキュリティ要件、処理フローが異なる
   - 影響: 既存のShopController改修は不要

3. **外部決済の購入回数管理は別テーブル**
   - 理由: アプリ内課金とは別管理
   - 影響: usr_external_payment_productsテーブルを新規作成

4. **べき等性の保証はDB制約で実現**
   - 理由: アプリケーション層だけでは不十分
   - 影響: usr_store_product_histories.receipt_unique_idにユニークインデックス

---

**最終更新**: 2025-12-02