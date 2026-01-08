---
name: "Integrating BNE External Payment Platform"
description: BNE外部決済システムのApple/Googleプラットフォーム連携を実装する際に使用。Apple StoreKitとGoogle Play Billingから国コード・通貨コード取得、プラットフォーム別仕様差異対応、UnityIAP連携、ユーザー情報のDB保存・更新を実装する。
---

# Integrating BNE External Payment Platform

BNE外部決済システムにおけるApple（iOS）とGoogle（Android）の決済プラットフォーム連携、特に国コード・通貨コード取得の実装ガイド。

## Instructions

### 1. プラットフォーム連携の概要を理解する

Apple/Googleプラットフォームから取得する情報と、BNE外部決済システムでの使用目的を理解する。

参照: **[platform-overview.md](platform-overview.md)**

### 2. Apple StoreKit連携を実装する

iOS向けに国コード・通貨コード取得処理を実装する。

**取得API**:
- 国コード: `SKPaymentQueue.storefront`
- 通貨コード: `SKProduct.priceLocale`

**公式ドキュメント**: platform-overview.mdを参照

### 3. Google Play Billing連携を実装する

Android向けに国コード・通貨コード取得処理を実装する。

**取得API**:
- 国コード: `BillingConfig`
- 通貨コード: `ProductDetails.OneTimePurchaseOfferDetails`

**公式ドキュメント**: platform-overview.mdを参照

### 4. プラットフォーム判定とデータ変換を実装する

クライアントから送信されたプラットフォーム情報を元に、適切な取得処理を選択する。

### 5. ユーザー情報の保存・更新を実装する

取得した国コード・通貨コードをDBに保存・更新する。

**DB設計**: platform-overview.mdのDB設計セクションを参照

### 6. UnityIAPとの連携を考慮する

UnityIAPを使用している場合の制約と対応方法を理解する。

**制約**: platform-overview.mdのUnityIAPセクションを参照

## 実装後チェック

- [ ] Apple StoreKit連携を実装した
- [ ] Google Play Billing連携を実装した
- [ ] プラットフォーム判定ロジックを実装した
- [ ] 国コード・通貨コードのDB保存処理を実装した
- [ ] UnityIAP使用時の制約を考慮した
- [ ] テストを実装した

## 関連スキル

- **bne-external-payment-webhook** - ウェブフック処理（国コード・通貨コードをレスポンスに含める）
- **bne-external-payment-purchase** - 購入検証（国コードによる制限チェック）
- **api-test-implementation** - テスト実装

## 参照ドキュメント

- **[platform-overview.md](platform-overview.md)** - プラットフォーム連携概要（取得方法、DB設計、UnityIAP制約含む）
