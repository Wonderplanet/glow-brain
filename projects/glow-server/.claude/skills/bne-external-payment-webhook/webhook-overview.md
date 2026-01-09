# WebStore Webhook 概要

## システム概要

WEB STOREは外部決済代行システム（Xsolla）を使用して決済を行います。購入後の有償通貨データはXsollaから直接アプリサーバーに通信されるため、WEB STORE自体にはユーザーの購入データは保持されません。

## ウェブフックの種類

glow-serverで実装が必要なウェブフックは以下の2つです：

### 1. ユーザ情報の取得（User Validation）

**notification_type**: `web_store_user_validation`

**タイミング**: ユーザーがWebStoreにログインした時

**目的**:
- アプリのユーザー情報を返却
- ユーザーの存在確認
- 国コード・通貨コードの取得（Apple/Googleプラットフォームから）

**エンドポイント**: `POST /ユーザ情報の取得`（実装時に適切なパスに変更）

### 2. 決済事前確認（Payment Validation）

**notification_type**: `web_store_payment_validation`

**タイミング**: ユーザーが購入ボタンをクリックした時（決済実行前）

**目的**:
- 年齢制限チェック
- 在庫確認（必要な場合）
- 購入可能かどうかの事前確認

**エンドポイント**: `POST /決済事前確認`（実装時に適切なパスに変更）

### 3. 注文支払い成功（将来実装予定）

購入完了後にアイテムを付与するウェブフック。webstore-purchase-validationスキルで実装します。

## リクエストフォーマット

すべてのウェブフックは以下の共通構造を持ちます：

```json
{
  "notification_type": "web_store_user_validation",
  "user": {
    "user_id": "V2HSRCGHNntfJZHXVUJtnctA",
    "mbid": "100000001",
    "person_id": "pi00.7BwKu22uUY6jWspri5OsVA--",
    "language": "ja"
  },
  "custom_parameters": {
    "user_ip": "192.168.1.1",
    "store_code": "web-store-jp"
  }
}
```

## レスポンスフォーマット

### 成功時（200 OK）

```json
{
  "user": {
    "id": "user_display_id",
    "internal_id": "usr_user_id",
    "name": "ユーザー名",
    "level": 50,
    "birthday": "19900101",
    "country": "JP",
    "currency": "JPY"
  }
}
```

### エラー時

エラーの種類に応じて適切なHTTPステータスコードとエラーメッセージを返します。

詳細は **[guides/error-handling.md](guides/error-handling.md)** を参照してください。

## セキュリティ

### 署名検証

Xsollaからのリクエストには署名ヘッダー（Signature）が付与されます。この署名を検証することで、リクエストが正当なものであることを確認します。

署名検証の詳細は **[guides/signature-verification.md](guides/signature-verification.md)** を参照してください。

### IPアドレス制限

Xsollaからの通知元IPアドレスは特定の範囲に限定されています。必要に応じてIPアドレス制限を実装してください。

## 実装の流れ

1. **署名検証ミドルウェア作成** - すべてのウェブフックで共通
2. **ユーザ情報取得エンドポイント実装** - ログイン時の処理
3. **決済事前確認エンドポイント実装** - 購入前のバリデーション
4. **テスト実装** - 各ウェブフックの正常系・異常系テスト

## 参考情報

- WEB STORE開発ドキュメント: `/Users/junki.mizutani/Downloads/web_store_docs_20250501/index.html`
- Xsolla公式ドキュメント: https://developers.xsolla.com/
