# パック・商品販売 要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstStoreProduct.csv`（新規Nパック分のストア商品定義）
> - `MstStoreProductI18n.csv`（新規Nパック分の価格情報）
> - `OprProduct.csv`（新規Nパック分の運営商品定義）
> - `OprProductI18n.csv`（新規Nパック分のアセットキー）
> - `MstPack.csv`（新規Nパック分のパック設定）
> - `MstPackI18n.csv`（新規Nパック分のパック名称）
> - `MstPackContent.csv`（パック内容物、1パックにつき1行以上）

---

## テンプレート

```
# パック・商品販売 要件テキスト

## 基本情報

- リリースキー: {このリリースのリリースキーを記入}
  例: 202603010
- 販売開始: YYYY-MM-DD HH:MM（JST）
- 販売終了: YYYY-MM-DD HH:MM（JST）

## パック一覧

{パックを1件ずつ記入する。パック数に応じて繰り返す。}

---
### パック {N}

- パック名（UI表示）: {プレイヤーに見えるパック名}
  ※ 慣例: 先頭に「【お一人様X回まで購入可】\n」を付けることが多い
- パックID: {mst_packs.id に設定するID}
  例: event_item_pack_35
- パックタイプ: Normal / Daily
  ※ Normal = 期間内にtradable_count回まで / Daily = 毎日リセット
- 購入可能回数（tradable_count）: {回数。無制限の場合は 0 と記入}
- コスト種別: Cash / Diamond / PaidDiamond / Ad / Free
- 価格（円）: {日本円の価格。Cash以外は不要}
- バナー画像キー（asset_key）: {pack_00001 など。不明の場合は「未定」と記入}
- 期限表示: あり / なし
  ※「あり」= プレイヤーに残り期間を表示する（期間限定パックは「あり」が標準）
- おすすめ表示: あり / なし
- パック装飾: なし / Gold

#### パック内容物（MstPackContent）

{含まれるアイテムを1行ずつ列挙する}

内容物 1: {アイテム種別} {アイテム名またはID} × {数量}
内容物 2: （以降必要な分だけ記入）

※ is_bonus（おまけ）にするアイテムは末尾に「（おまけ）」と書く

---
```

---

## パックタイプとコスト種別の選択肢

### パックタイプ（pack_type）

| 値 | 説明 | tradable_count の意味 |
|----|------|----------------------|
| `Normal` | 通常パック。購入回数に上限がある（1回限りが標準） | 販売期間内の累計購入上限（0は無制限） |
| `Daily` | デイリーパック。毎日リセット | 1日あたりの購入上限 |

> 実績値: 全リリースを通じてほぼすべてのパックが `Normal` + `tradable_count=1`（1回限り）。
> 複数回購入可能なパックは `tradable_count` に回数を設定する（例: 2、5、10）。

### コスト種別（cost_type）

| 値 | 説明 | 価格設定 |
|----|------|---------|
| `Cash` | 課金通貨（円） | MstStoreProduct / MstStoreProductI18n に価格を設定 |
| `Diamond` | 有償・無償ダイヤ問わず消費 | cost_amount に消費量を設定 |
| `PaidDiamond` | 有償ダイヤのみ消費 | cost_amount に消費量を設定 |
| `Ad` | 広告視聴 | cost_amount は 0 |
| `Free` | 無料 | cost_amount は 0 |

> 運営パック（イベント・月次系）はほぼすべて `Cash`。

---

## パック内容物の種別（resource_type）

| 表記 | resource_type | resource_id | 備考 |
|------|--------------|-------------|------|
| プリズム | `FreeDiamond` | （空） | 無償ダイヤ |
| コイン | `Coin` | （空） | コイン |
| ピックアップガシャチケット | `Item` | `ticket_glo_00003` | ピックアップ対象を引ける |
| スペシャルガシャチケット | `Item` | `ticket_glo_00002` | どのガシャでも使える |
| SPガシャチケット（新） | `Item` | `ticket_glo_00004` | 最新SPガシャ対応 |
| スタミナドリンク（小） | `Item` | `stamina_item_glo_00001` | スタミナ回復アイテム |
| キャラクター | `Unit` | `chara_xxx_00001` 等 | ユニットID直接指定 |

### カラーメモリー（Lv上限開放素材）

| 表記 | アイテムID | 属性 |
|------|-----------|------|
| カラーメモリー・グレー | `memory_glo_00001` | 無属性 |
| カラーメモリー・レッド | `memory_glo_00002` | 赤 |
| カラーメモリー・ブルー | `memory_glo_00003` | 青 |
| カラーメモリー・イエロー | `memory_glo_00004` | 黄 |
| カラーメモリー・グリーン | `memory_glo_00005` | 緑 |

### メモリーフラグメント（Lv上限開放素材・色共通）

| 表記 | アイテムID | レアリティ |
|------|-----------|-----------|
| メモリーフラグメント・初級 | `memoryfragment_glo_00001` | SR相当 |
| メモリーフラグメント・中級 | `memoryfragment_glo_00002` | SSR相当 |
| メモリーフラグメント・上級 | `memoryfragment_glo_00003` | UR相当 |

### その他アイテム

| 表記 | アイテムID |
|------|-----------|
| ガシャチケット（天井） | `ticket_glo_00203` |
| 探求の宝箱 | `box_glo_00008` |
| 原画強化素材（初級） | `artwork_enhance_glo_00001` |
| 原画強化素材（中級） | `artwork_enhance_glo_00002` |
| 原画強化素材（上級） | `artwork_enhance_glo_00003` |

> **上記にないアイテム**: アイテムIDを直接記入するか「アイテム名（item_xxx_xxxxx）× N個」と記入する。

---

## 記入済みサンプル（実データ: 202603010 より）

### サンプル1: イベント記念パック（アイテムパック・3,000円・1回限り）

```
# パック・商品販売 要件テキスト

## 基本情報

- リリースキー: 202603010
- 販売開始: 2026-03-02 15:00（JST）
- 販売終了: 2026-03-16 10:59（JST）

## パック一覧

---
### パック 1

- パック名（UI表示）: 【お一人様1回まで購入可】\nいいジャン祭 開催記念パック
- パックID: event_item_pack_33
- パックタイプ: Normal
- 購入可能回数（tradable_count）: 0
- コスト種別: Cash
- 価格（円）: 3000
- バナー画像キー（asset_key）: pack_00005
- 期限表示: あり
- おすすめ表示: なし
- パック装飾: なし

#### パック内容物

内容物 1: メモリーフラグメント・初級 × 50
内容物 2: メモリーフラグメント・中級 × 30
内容物 3: メモリーフラグメント・上級 × 3
内容物 4: ピックアップガシャチケット × 10
```

### サンプル2: キャラパック（ユニット付き・1,980円・1回限り）

```
# パック・商品販売 要件テキスト

## 基本情報

- リリースキー: 202603010
- 販売開始: 2026-03-10 15:00（JST）
- 販売終了: 2026-03-31 10:59（JST）

## パック一覧

---
### パック 1

- パック名（UI表示）: 【お一人様1回まで購入可】\n「<黄昏> ロイド」キャラパック
- パックID: event_item_pack_25
- パックタイプ: Normal
- 購入可能回数（tradable_count）: 0
- コスト種別: Cash
- 価格（円）: 1980
- バナー画像キー（asset_key）: pack_00033
- 期限表示: あり
- おすすめ表示: なし
- パック装飾: なし

#### パック内容物

内容物 1: キャラクター（chara_spy_00101） × 1
内容物 2: ピックアップガシャチケット × 1
内容物 3: カラーメモリー・イエロー × 750
内容物 4: メモリーフラグメント・初級 × 50
内容物 5: メモリーフラグメント・中級 × 30
内容物 6: コイン × 15000（おまけ）
```

### サンプル3: 複数パックをまとめて申請（202603025 アニバーサリーパック群）

```
# パック・商品販売 要件テキスト

## 基本情報

- リリースキー: 202603025
- 販売開始: 2026-03-30 15:00（JST）
- 販売終了: 2026-04-13 10:59（JST）

## パック一覧

---
### パック 1（スタミナドリンクパック）

- パック名（UI表示）: 【お一人様10回まで購入可】\nアニバ限定いいジャン!スタミナドリンクパック
- パックID: event_item_pack_44
- パックタイプ: Normal
- 購入可能回数（tradable_count）: 10
- コスト種別: Cash
- 価格（円）: 300
- バナー画像キー（asset_key）: pack_00043
- 期限表示: あり
- おすすめ表示: なし
- パック装飾: なし

#### パック内容物

内容物 1: スタミナドリンク（小） × 10

---
### パック 2（DXお得パック 松・上位パック）

- パック名（UI表示）: 【お一人様1回まで購入可】\nアニバ限定 DXお得パック 松
- パックID: event_item_pack_48
- パックタイプ: Normal
- 購入可能回数（tradable_count）: 0
- コスト種別: Cash
- 価格（円）: 4980
- バナー画像キー（asset_key）: pack_00041
- 期限表示: あり
- おすすめ表示: なし
- パック装飾: なし

#### パック内容物

内容物 1: ガシャチケット（天井） × 1
内容物 2: 探求の宝箱 × 50
内容物 3: SPガシャチケット（新） × 5
内容物 4: コイン × 50000（おまけ）
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、パック・商品販売のマスタデータCSVを生成してください。

【生成対象】
- MstStoreProduct.csv（新規Nパック分。既存最大IDの続き番号から採番）
- MstStoreProductI18n.csv（新規Nパック分。id = {mst_store_product_id}_{言語コード}）
- OprProduct.csv（新規Nパック分。id は mst_store_product_id と同値）
- OprProductI18n.csv（新規Nパック分。id = {opr_product_id}_{言語コード}）
- MstPack.csv（新規Nパック分）
- MstPackI18n.csv（新規Nパック分。id = {mst_pack_id}_{言語コード}）
- MstPackContent.csv（新規Nパック分のアイテム群。id は既存最大IDの続き番号から採番）

【ID採番】
- MstStoreProduct.id および OprProduct.id は整数の連番。既存CSVの最大値+1から採番する。
- MstPackContent.id は整数の連番。既存CSVの最大値+1から採番する。

【参照してほしいファイル】
- 既存CSVの最大ID確認: projects/glow-masterdata/MstStoreProduct.csv, MstPackContent.csv

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係（4層構造）

```
[層1] MstStoreProduct（ストア商品登録）
  └─ id: 整数連番（例: 85）
  └─ product_id_ios: BNEI0434_XXXX
  └─ product_id_android: com.bandainamcoent.jumble_XXXX
    ↓（1対1）
[層1] MstStoreProductI18n（価格情報）
  └─ id: {mst_store_product_id}_{言語コード}（例: 85_ja）
  └─ mst_store_product_id: 85
  └─ price_ios / price_android: 日本円価格
    ↓（1対多 ※通常1レコード）
[層2] OprProduct（運営商品・販売期間設定）
  └─ id: MstStoreProduct.id と同値（例: 85）
  └─ mst_store_product_id: 85
  └─ product_type: Pack
  └─ purchasable_count: 購入可能回数（NULLで無制限、1で1回限り）
  └─ start_date / end_date: 販売期間（JST）
    ↓（1対1）
[層2] OprProductI18n（商品アセットキー）
  └─ id: {opr_product_id}_{言語コード}（例: 85_ja）
  └─ opr_product_id: 85
  └─ asset_key: 通常は空（特別なデザインがある場合のみ設定）
    ↓（OprProduct.id → MstPack.product_sub_id）
[層3] MstPack（パック設定）
  └─ id: パックID文字列（例: event_item_pack_33）
  └─ product_sub_id: OprProduct.id（例: 85）
  └─ pack_type: Normal / Daily
  └─ tradable_count: 購入可能回数（0 = 1回限り）
  └─ cost_type: Cash
  └─ is_display_expiration: 1（期間限定パックは必ず1）
    ↓（1対1）
[層3] MstPackI18n（パック名称）
  └─ id: {mst_pack_id}_{言語コード}（例: event_item_pack_33_ja）
  └─ mst_pack_id: event_item_pack_33
  └─ language: ja
  └─ name: パック名（UI表示。改行は \n で記述）
    ↓（1対多）
[層4] MstPackContent（パック内容物）
  └─ id: 整数連番（例: 236, 237, ...）
  └─ mst_pack_id: event_item_pack_33
  └─ resource_type: Item / FreeDiamond / Coin / Unit
  └─ resource_id: アイテムID（FreeDiamond / Coin は空）
  └─ resource_amount: 数量
  └─ is_bonus: おまけフラグ（通常は空、おまけは 1）
  └─ display_order: 表示順（数値が小さいほど上位）
```

---

## 注意事項

- **MstStoreProduct.id と OprProduct.id は同一の整数値を使う**: 実データを通じて一貫してこのルールが守られている
- **tradable_count = 0 は「1回限り」を意味する**: 0 と NULL の扱いが異なる可能性があるため、無制限にする場合は必ずNULLを指定する
- **OprProduct.purchasable_count は別の概念**: `tradable_count`（MstPack）がパックの購入回数制限を管理し、`purchasable_count`（OprProduct）が同ストア商品への購入回数を管理する。通常パックは `purchasable_count=1`
- **プロダクトIDは変更禁止**: 一度AppStore / Google Playに登録した `product_id_ios` / `product_id_android` は変更できない（リストア機能に影響）
- **バナー画像キー（asset_key）の採番**: `pack_00001` 形式で連番。既存CSVの最大値の続きからアサイン。未定の場合は空欄で渡し、後で補完する
- **MstPackContent.display_order**: 数値が小さいほど上位表示。おまけアイテム（is_bonus=1）は慣例として最小の display_order（1）を使用することが多い
- **is_display_expiration**: 期間限定パック（イベント系・月次系すべて）は `1` を設定する。プレイヤーに販売期限を明示するため
- **cost_amount**: `Cash` の場合は `0` を設定する（価格はMstStoreProductI18nで管理する）
- **OprProductI18n.asset_key**: 通常は空文字で設定する。特別なキービジュアルが必要な場合のみ非空値を設定する
- **時刻はすべてJST前提**: 要件テキストに記入した時刻がそのままCSVに出力される（UTC変換不要）
- **MstStoreProductI18n の WebStore カラム**: 現行の通常パックはWebStore非対応のため `price_webstore`・`paid_diamond_price_*` はすべて `0` を設定する
