---
name: "Handling BNE External Payment Webhooks"
description: BNE外部決済システム（Xsolla WebStore）からのウェブフック受信・処理を実装する際に使用。ユーザ情報取得（web_store_user_validation）、決済事前確認（web_store_payment_validation）、注文支払い成功ウェブフックの処理、署名検証、カスタムパラメータ処理、エラーハンドリングを実装する。
---

# Handling BNE External Payment Webhooks

BNE外部決済システム（Xsolla WebStore）からのウェブフック（Webhook）を受信・処理するAPIエンドポイントの実装ガイド。

## Instructions

### 1. ウェブフックの概要と仕様を理解する

Xsollaから送信される各ウェブフックの役割、リクエスト/レスポンス形式、処理フローを理解する。

参照: **[webhook-overview.md](webhook-overview.md)**

### 2. 署名検証を実装する

Xsollaからのリクエストが正当なものであることを確認するため、SHA-1ハッシュによる署名検証を実装する。

参照: **[guides/signature-verification.md](guides/signature-verification.md)**

### 3. ウェブフックエンドポイントを実装する

**3-1. ユーザ情報取得ウェブフック**

ユーザーログイン時にアプリのユーザー情報を返却するエンドポイントを実装する。

参照: **[examples/user-validation.md](examples/user-validation.md)**

**3-2. 決済事前確認ウェブフック**

購入前に在庫確認や年齢制限チェックを行うエンドポイントを実装する（実装例は今後追加予定）。

**3-3. 注文支払い成功ウェブフック（将来実装）**

購入完了後にアイテムを付与するエンドポイントを実装する（bne-external-payment-purchaseスキルと連携）。

### 4. カスタムパラメータを処理する

store_code、user_ipなどのカスタムパラメータを適切に処理する。

参照: **[guides/custom-parameters.md](guides/custom-parameters.md)**

### 5. エラーハンドリングを実装する

ウェブフック処理中のエラーを適切にハンドリングし、Xsollaに正しいレスポンスを返す。

参照: **[guides/error-handling.md](guides/error-handling.md)**

### 6. テストを実装する

ウェブフック処理のテストを実装する（api-test-implementationスキルと連携）。

## 実装後チェック

- [ ] 署名検証を実装した
- [ ] ユーザ情報取得ウェブフックを実装した
- [ ] 決済事前確認ウェブフックを実装した
- [ ] カスタムパラメータを適切に処理した
- [ ] エラーハンドリングを実装した
- [ ] テストを実装した

## 関連スキル

- **bne-external-payment-platform** - Apple/Google プラットフォーム連携（国コード・通貨コード取得）
- **bne-external-payment-purchase** - 購入処理・検証・アイテム付与
- **api-response** - APIレスポンス作成
- **api-test-implementation** - テスト実装

## 参照ドキュメント

- **[webhook-overview.md](webhook-overview.md)** - ウェブフック概要と仕様
- **[guides/signature-verification.md](guides/signature-verification.md)** - 署名検証実装ガイド
- **[guides/custom-parameters.md](guides/custom-parameters.md)** - カスタムパラメータ処理ガイド
- **[guides/error-handling.md](guides/error-handling.md)** - エラーハンドリングガイド
- **[examples/user-validation.md](examples/user-validation.md)** - ユーザ情報取得の実装例
