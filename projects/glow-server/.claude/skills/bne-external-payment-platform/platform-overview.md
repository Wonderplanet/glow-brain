# プラットフォーム連携概要

## 目的

WEB STOREで不正購入を防止するため、ユーザーのアプリダウンロード国と通貨情報を取得してバンダイナムコIDの居住国と照合します。

## 取得する情報

### 国コード（Country Code）

**形式**: ISO 3166-1 alpha-2 または alpha-3（例: `JP`, `US`, `JPN`）

**用途**:
- 不正購入防止（居住国との照合）
- 年齢制限の適用
- ログ記録

**優先度**: **高**（国コードを優先的に送信）

### 通貨コード（Currency Code）

**形式**: ISO 4217（例: `JPY`, `USD`）

**用途**:
- 不正購入防止（国コードが取得できない場合の代替）
- 価格表示
- ログ記録

**優先度**: 中（国コードが取得できない場合のみ使用）

## プラットフォーム別取得方法

### Apple（iOS）

| 情報 | API | クラス/メソッド |
|------|-----|----------------|
| 国コード | SKPaymentQueue | `storefront` プロパティ |
| 通貨コード | SKProduct | `priceLocale` プロパティ |

**公式ドキュメント**:
- 国コード: https://developer.apple.com/documentation/storekit/skpaymentqueue/3182430-storefront
- 通貨コード: https://developer.apple.com/documentation/storekit/skproduct/pricelocale

### Google（Android）

| 情報 | API | クラス/メソッド |
|------|-----|----------------|
| 国コード | BillingClient | `BillingConfig` |
| 通貨コード | ProductDetails | `OneTimePurchaseOfferDetails` |

**公式ドキュメント**:
- 国コード: https://developer.android.com/reference/com/android/billingclient/api/BillingConfig
- 通貨コード: https://developer.android.com/reference/com/android/billingclient/api/ProductDetails.OneTimePurchaseOfferDetails

## データフロー

```
1. アプリ起動時
   ↓
2. Apple/Google APIから国コード・通貨コードを取得
   ↓
3. glow-serverに送信してDBに保存
   ↓
4. WebStoreログイン時
   ↓
5. ユーザ情報取得ウェブフックで国コード・通貨コードを返却
   ↓
6. WebStore側でバンダイナムコIDの居住国と照合
```

## 送信タイミング

国コード・通貨コードをglow-serverに送信するタイミング：

1. **アプリ起動時**（最優先）
2. **ショップ画面遷移時**
3. **定期的な更新**（例: 1日1回）

## 優先順位

### 国コードが取得できる場合

```json
{
  "country": "JP",
  "currency": "JPY"
}
```

### 国コードが取得できない場合

```json
{
  "country": null,
  "currency": "JPY"
}
```

**重要**: 海外版WebStoreでは、`country` または `currency` のどちらか必須です。

## UnityIAPの制約

UnityIAPを使用している場合：

### 問題点

- UnityIAPは国コード取得処理を実装していない
- Android で UnityIAP を使わずに国コード取得処理を実装すると、UnityIAP と Google Play Billing の両方が初期化されてエラーになる

### 対策

1. **推奨**: UnityIAPを使用せず、ネイティブAPIを直接使用
2. **代替案**: 通貨コードのみを送信（国コードは諦める）

詳細は **[guides/unity-iap.md](guides/unity-iap.md)** を参照してください。

## DB設計

### usr_users テーブルに追加するカラム

```sql
ALTER TABLE usr_users
ADD COLUMN usr_country_code VARCHAR(3) DEFAULT NULL COMMENT 'ダウンロード国コード ISO 3166-1',
ADD COLUMN usr_currency_code VARCHAR(3) DEFAULT NULL COMMENT '通貨コード ISO 4217',
ADD COLUMN usr_platform_info_updated_at DATETIME DEFAULT NULL COMMENT 'プラットフォーム情報更新日時';
```

## エラーハンドリング

### 国コード・通貨コードが取得できない場合

1. **日本版WebStore**: ログインは可能（制限なし）
2. **海外版WebStore**: ログイン不可（country または currency が必須）

### 国コードとBNID居住国が一致しない場合

1. **アプリダウンロード国とBNID居住国が異なる**: ログイン不可
2. **アクセス国とBNID居住国が異なる**: ログイン可能（ログ記録）

## セキュリティ

### 不正購入防止のロジック

```
if (アプリダウンロード国 != BNID居住国) {
    return "ログイン不可";
}

if (アクセス国 != BNID居住国) {
    ログ記録("不正の可能性");
    return "ログイン可能（監視対象）";
}
```

## 参考資料

- WEB STORE開発ドキュメント: `/Users/junki.mizutani/Downloads/web_store_docs_20250501/index.html`
- 「国及び通貨コードの取得」セクション参照
