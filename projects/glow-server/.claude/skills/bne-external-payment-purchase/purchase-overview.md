# 購入処理概要

## 購入フロー

```
1. ユーザーがアイテムを選択
   ↓
2. 決済事前確認ウェブフック（webstore-webhook-handler）
   - 年齢制限チェック
   - 在庫確認（必要な場合）
   ↓
3. Xsollaで決済処理
   ↓
4. 注文支払い成功ウェブフック（このスキルで実装）
   - アイテム付与
   - 購入履歴保存
   ↓
5. アプリ側でアイテム反映
```

## 注文支払い成功ウェブフック

### リクエスト例

```json
{
  "notification_type": "payment",
  "purchase": {
    "virtual": {
      "items": [
        {
          "sku": "diamond_pack_100",
          "amount": 1,
          "type": "virtual_good"
        }
      ]
    }
  },
  "user": {
    "id": "V2HSRCGHNntfJZHXVUJtnctA",
    "email": "user@example.com"
  },
  "transaction": {
    "id": 123456789,
    "payment_method": "credit_card"
  },
  "payment_details": {
    "payment": {
      "currency": "JPY",
      "amount": 1000
    }
  },
  "custom_parameters": {
    "store_code": "web-store-jp"
  }
}
```

### レスポンス

```json
{}
```

**HTTPステータス**: `200 OK`（空のJSONオブジェクトを返す）

## items配列の処理

### 重要な注意点

WebStoreにはカート機能がないため、複数アイテムを一度に購入することは基本的にありませんが、販売方法によっては `items` 配列に複数のアイテムが含まれることがあります。

### 処理ルール

✅ **付与対象**: `type=virtual_good` のアイテム**のみ**

❌ **付与対象外**: `type` が `virtual_good` 以外のアイテムは無視

### items配列の例

```json
{
  "items": [
    {
      "sku": "diamond_pack_100",
      "amount": 1,
      "type": "virtual_good"  // ← これを付与
    },
    {
      "sku": "bonus_item",
      "amount": 1,
      "type": "bonus"  // ← これは無視
    }
  ]
}
```

**保証**: `type=virtual_good` のアイテムは必ず1件含まれます。

## 年齢制限

### アソビストア・日本版WebStore

- **全年齢**: ログイン可能
- **18歳未満**: 無料アイテムとクーポンのみ利用可能（有料購入不可）

### 海外版WebStore

- **13歳以下**: ログイン不可
- **14歳以上のこどもアカウント**: 無料アイテムとクーポンのみ利用可能

## 国コード制限（海外版のみ）

### ダウンロード国とBNID居住国の照合

```
if (ダウンロード国 != BNID居住国) {
    return "購入不可";
}
```

### アクセス国とBNID居住国の照合

```
if (アクセス国 != BNID居住国) {
    ログ記録("不正の可能性");
    return "購入可能（監視対象）";
}
```

## 購入金額・通貨コードの検証

### 使用する値

Xsollaから送信される以下の値を使用：

```json
{
  "payment_details": {
    "payment": {
      "currency": "JPY",
      "amount": 1000
    }
  }
}
```

### 検証ポイント

1. **金額の整合性**: クライアントが送信した金額とXsollaの金額を照合
2. **通貨コードの整合性**: 想定される通貨コードと一致するか確認
3. **価格マスターとの照合**: mst_shop_items の価格と一致するか確認

## 同時購入の制御

同一ユーザーによる同時購入リクエストを以下の方法で制御：

### 1. トランザクションIDの重複チェック

購入履歴テーブルに `transaction_id` を保存し、重複チェックを実施。

### 2. ロック機構

```php
DB::transaction(function () use ($userId, $transactionId) {
    // ユーザーレコードをロック
    $user = User::where('usr_user_id', $userId)->lockForUpdate()->first();

    // 重複チェック
    if (PurchaseHistory::where('transaction_id', $transactionId)->exists()) {
        throw new DuplicatePurchaseException();
    }

    // アイテム付与と購入履歴保存
    // ...
});
```

## 無料アイテム・クーポン

### 特徴

- **決済を行わない**: Xsollaでの決済処理はスキップ
- **ウェブフックの違い**:
  - 「ユーザーの検証」ウェブフックは通常通り送信
  - 「決済事前確認」ウェブフックは**送信されない**
  - アイテム付与は別の仕組みで実施

### 処理フロー

```
1. ユーザーがクーポンコード入力 or 無料アイテムを選択
   ↓
2. ユーザーの検証ウェブフック
   ↓
3. （決済事前確認ウェブフックはスキップ）
   ↓
4. アイテム付与（別エンドポイント）
```

## キャンセル・返金

### 対応が必要なケース

1. **ユーザーによるキャンセル**: 決済完了前にキャンセル
2. **返金リクエスト**: 決済完了後に返金
3. **チャージバック**: クレジットカード会社による返金処理

### 処理内容

- 付与済みアイテムの回収
- 購入履歴の更新（ステータス変更）
- ログ記録

詳細は **[guides/refund-handling.md](guides/refund-handling.md)** を参照してください。

## アプリ側でのアイテム反映

### タイミング

1. **ゲーム再起動時**
2. **ショップ画面遷移時**

### 反映方法

アプリ側でポップアップ通知してユーザーに知らせる（推奨仕様）。

## DBスキーマ設計

購入履歴管理に必要なテーブル：

### log_webstore_purchases

```sql
CREATE TABLE log_webstore_purchases (
    log_webstore_purchase_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usr_user_id BIGINT UNSIGNED NOT NULL,
    transaction_id VARCHAR(255) NOT NULL UNIQUE,
    sku VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    currency VARCHAR(3) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    store_code VARCHAR(50) NOT NULL,
    payment_method VARCHAR(100),
    status ENUM('completed', 'refunded', 'cancelled') DEFAULT 'completed',
    purchased_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usr_user_id (usr_user_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_purchased_at (purchased_at)
);
```

詳細は **[examples/purchase-history-schema.md](examples/purchase-history-schema.md)** を参照してください。

## 参考資料

- WEB STORE開発ドキュメント: `/Users/junki.mizutani/Downloads/web_store_docs_20250501/index.html`
- 「実装における注意点」セクション参照
