# 幼稚園WARS いいジャン祭ピックアップガシャ マスタデータ生成レポート

## 概要

**生成日時**: 2025年12月26日  
**仕様書**: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/マスタデータ/仕様/20260202_幼稚園WARS いいジャン祭_仕様書`  
**出力先**: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/マスタデータ/仕様/幼稚園WARS いいジャン祭ピックアップガシャ/`

このレポートは、「幼稚園WARS いいジャン祭」イベントのピックアップガシャとパック販売のマスタデータ生成について記録します。

## 生成されたマスタデータ

### 1. OprGacha.csv - ピックアップガシャ

**テーブル**: `opr_gachas`  
**レコード数**: 1

**内容**:
- **ガシャID**: `Pickup_you_001`
- **ガシャタイプ**: `Pickup`（ピックアップガシャ）
- **開催期間**: 2026年2月2日 15:00 〜 2026年3月2日 10:59
- **天井設定**: 100回（`upper_group`: `Pickup_you_001`）
- **確定枠**: 10枠目（`multi_fixed_prize_count`: 1）
- **10連ガシャ**: 有効（`multi_draw_count`: 10）
- **ガシャ名**: 「幼稚園WARS いいジャン祭ピックアップガシャ」
- **訴求文言**: 「元殺し屋の新人教諭 リタ」と「ルーク」の出現率UP中!
- **天井表示**: ピックアップURキャラ1体確定!

**主要設定値**:
```csv
id: Pickup_you_001
gacha_type: Pickup
upper_group: Pickup_you_001
enable_ad_play: 1
enable_add_ad_play_upper: 1
ad_play_interval_time: 0
multi_draw_count: 10
multi_fixed_prize_count: 1
prize_group_id: Pickup_you_001
fixed_prize_group_id: Pickup_you_001_fixed
appearance_condition: Always
unlock_condition_type: None
start_at: 2026-02-02 15:00:00
end_at: 2026-03-02 10:59:00
display_information_id: 51e46f83-106f-4561-9b0e-c91d4017e918
gacha_priority: 80
release_key: 202512260
```

**参照元仕様書**:
- `幼稚園WARS いいジャン祭ピックアップガシャ_設計書.html`

**備考**:
- `display_information_id`は新規生成UUID: `51e46f83-106f-4561-9b0e-c91d4017e918`
- `prize_group_id`と`fixed_prize_group_id`は別途定義が必要（OprGachaPrizeテーブル）
- バナーアセット: `you_00001`、`hometop_gacha_you_00001`

---

### 2. OprProduct.csv - いいジャン祭パック

**テーブル**: `opr_products`  
**レコード数**: 1

**内容**:
- **商品ID**: `50`
- **ストア商品ID**: `you_pack_001`
- **商品タイプ**: `Pack`
- **価格**: 3,000円（税込）
- **購入可能回数**: 1回（お一人様1回まで）
- **販売期間**: 2026年2月2日 15:00 〜 2026年2月16日 10:59
- **表示優先度**: 100
- **リリースキー**: `202512260`

**主要設定値**:
```csv
id: 50
mst_store_product_id: you_pack_001
product_type: Pack
purchasable_count: 1
paid_amount: 3000
display_priority: 100
start_date: 2026-02-02 15:00:00
end_date: 2026-02-16 10:59:00
release_key: 202512260
asset_key.ja: you_pack_001_banner
```

**パック内容（仕様書より）**:
- メモリーフラグメント・初級 ×50
- メモリーフラグメント・中級 ×30
- メモリーフラグメント・上級 ×3
- ピックアップガシャチケット ×10

**参照元仕様書**:
- `07_いいジャン祭パック_設計書.html`

**備考**:
- 実際のパック内容物は`MstStoreProduct`および`MstStoreProductReward`テーブルで別途定義が必要
- 通常価格4,040円から25.74%割引の特別価格
- 2週間限定販売（いいジャン祭の前半期間）

---

## スキーマ検証と修正

### 検証実施内容

1. **NOT NULL制約のチェック**:
   - OprGachaテーブルの全NOT NULL制約カラムを確認
   - OprProductテーブルの全NOT NULL制約カラムを確認

2. **実施した修正**:
   - `enable_ad_play`: `__NULL__` → `1`（広告視聴有効）
   - `enable_add_ad_play_upper`: `__NULL__` → `1`（広告視聴天井加算有効）
   - `ad_play_interval_time`: `__NULL__` → `0`（リセット時間なし）
   - `display_information_id`: 空文字 → UUID `51e46f83-106f-4561-9b0e-c91d4017e918`

### スキーマ検証結果

✅ **OprGacha**: 全てのNOT NULL制約を満たしています  
✅ **OprProduct**: 全てのNOT NULL制約を満たしています

---

## 依存するマスタデータ（別途作成が必要）

本レポートで生成したマスタデータは、以下のマスタデータに依存しています。これらは別途作成する必要があります：

### 1. OprGachaPrize（ガシャ排出テーブル）

**必要なprize_group_id**:
- `Pickup_you_001` - 通常枠の排出設定
- `Pickup_you_001_fixed` - 確定枠（10枠目）の排出設定

**ラインナップ内容**（仕様書より）:
- ピックアップUR: 元殺し屋の新人教諭 リタ（0.75%）
- ピックアップSSR: ルーク（1.50%）
- UR: 13キャラ（合計2.25%、1体あたり約0.173%）
- SSR: 9キャラ（合計8.50%、1体あたり約0.944%）
- SR: 10キャラ（合計35.00%、1体あたり3.50%）
- R: 9キャラ（合計52.00%、1体あたり約5.78%）
- **合計**: 43キャラ

**確定枠（10枠目）のラインナップ**:
- SR以上確定（R除外）
- ピックアップUR: 0.75%
- UR: 2.25%
- ピックアップSSR: 1.50%
- SSR: 8.50%
- SR: 87.00%

### 2. OprGachaUpper（天井設定テーブル）

**必要な設定**:
- `upper_group`: `Pickup_you_001`
- 天井回数: 100回
- 天井報酬: ピックアップURキャラ1体確定

### 3. MstStoreProduct（ストア商品マスタ）

**必要な商品ID**:
- `you_pack_001` - いいジャン祭パック

**設定内容**:
- 商品名、説明文、アイコン画像などの基本情報
- プラットフォーム別課金ID（iOS/Android）

### 4. MstStoreProductReward（商品報酬テーブル）

**you_pack_001の報酬内容**:
- `memory_fragment_low` × 50（メモリーフラグメント・初級）
- `memory_fragment_mid` × 30（メモリーフラグメント・中級）
- `memory_fragment_high` × 3（メモリーフラグメント・上級）
- `ticket_glo_00003` × 10（ピックアップガシャチケット）

### 5. MstUnit（キャラクターマスタ）

**新規キャラ**:
- `chara_you_00001` - 元殺し屋の新人教諭 リタ（UR）
- `chara_you_00101` - ルーク（SSR）

※既存キャラ41体も排出対象に含まれます（仕様書参照）

### 6. MstItem（アイテムマスタ）

**新規アイテム**:
- メモリーフラグメント・初級
- メモリーフラグメント・中級
- メモリーフラグメント・上級
- ピックアップガシャチケット（`ticket_glo_00003`）
- キャラのかけら（各キャラ分）

### 7. MstDisplayInformation（表示情報テーブル）

**必要なdisplay_information_id**:
- `51e46f83-106f-4561-9b0e-c91d4017e918` - ガシャ詳細情報

### 8. アセット関連

**必要なバナー・画像**:
- `you_00001` - ガシャバナー
- `hometop_gacha_you_00001` - ホーム画面バナー
- `gacha_banner_you_00001` - ガシャ一覧バナー
- `you_pack_001_banner` - パックバナー

---

## 実装における注意事項

### ガシャ実装

1. **排出確率の検証**:
   - OprGachaPrizeの合計確率が100%になることを確認
   - 確定枠の確率も100%になることを確認

2. **天井システム**:
   - 100回引いた時点でピックアップURが確定で排出される
   - 天井カウントは`upper_group`単位で管理

3. **確定枠**:
   - 10連ガシャの10枠目はSR以上確定
   - 通常枠とは異なる排出テーブル（`Pickup_you_001_fixed`）を使用

4. **広告視聴**:
   - 広告視聴によるガシャは有効（`enable_ad_play: 1`）
   - 広告視聴回数の上限: 1日3回（`daily_ad_limit_count: 0`は未設定扱い）

### パック実装

1. **購入回数制限**:
   - お一人様1回まで（`purchasable_count: 1`）
   - 期間内に再販はなし

2. **販売期間**:
   - 2週間限定（2/2〜2/16）
   - ガシャ開催期間（〜3/2）より短い

3. **価格設定**:
   - 実際の課金額は3,000円（税込）
   - ストア設定で正確な価格を設定すること

4. **報酬内容**:
   - ガシャチケット10枚で10連1回分
   - メモリーフラグメントは育成素材として機能

---

## テスト項目

### ガシャ機能

- [ ] ガシャが正常に表示される
- [ ] 単発・10連ガシャが引ける
- [ ] 10枠目がSR以上確定になっている
- [ ] 100回引いた時点で天井が発動する
- [ ] 天井でピックアップURが確定排出される
- [ ] 排出確率が仕様通りである
- [ ] ガシャチケットで引ける
- [ ] 広告視聴でガシャが引ける

### パック機能

- [ ] ショップにパックが表示される
- [ ] 価格が3,000円で表示される
- [ ] 購入できる
- [ ] 報酬が正しく付与される
- [ ] 2回目の購入ができない
- [ ] 期間外は表示されない

---

## 今後の作業

1. **依存マスタデータの作成**:
   - OprGachaPrize（排出テーブル）の詳細設計と作成
   - OprGachaUpper（天井設定）の作成
   - MstStoreProduct、MstStoreProductRewardの作成
   - 新規キャラ・アイテムマスタの作成

2. **アセット準備**:
   - バナー画像の作成と配置
   - キャラクターアセットの準備

3. **動作確認**:
   - 開発環境での動作テスト
   - 排出確率の検証
   - 購入フローの確認

4. **リリース準備**:
   - ストア申請（iOS App Store、Google Play）
   - 課金テスト
   - 本番環境へのデプロイ

---

## 変更履歴

| 日付 | 変更内容 | 担当者 |
|------|---------|--------|
| 2025-12-26 | 初版作成 | GitHub Copilot |

---

## 補足資料

### 参照した仕様書ファイル

- `企画仕様書_目次.html`
- `01_概要.html`
- `幼稚園WARS いいジャン祭ピックアップガシャ_設計書.html`
- `07_いいジャン祭パック_設計書.html`
- `仕様ファイル構成.md`

### スキーマ参照

- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-masterdata/sheet_schema/OprGacha.csv`
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-masterdata/sheet_schema/OprProduct.csv`
