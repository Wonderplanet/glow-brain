---
name: masterdata-from-bizops-gacha
description: ガチャの運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 6個(OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n)。ピックアップガチャ、プレミアムガチャ等のマスタデータを精度高く作成します。
---

# ガチャ マスタデータ作成スキル

## 概要

ガチャの運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の6テーブルを自動生成:

**ガチャ基本情報**:
- **OprGacha** - ガチャの基本設定(タイプ、天井、期間、10連設定等)
- **OprGachaI18n** - ガチャ名・説明文(多言語対応)

**ガチャ排出内容**:
- **OprGachaPrize** - ガチャ排出内容(排出キャラ、重み、ピックアップ設定)
- **OprGachaUpper** - ガチャ天井設定(回数、タイプ)

**ガチャコスト**:
- **OprGachaUseResource** - ガチャ実行コスト(ダイヤ、チケット等)

**ガチャ表示**:
- **OprGachaDisplayUnitI18n** - ガチャ画面表示キャラの説明文(多言語対応)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **opr_gacha_id** | ガチャID | `Pickup_jig_001` |
| **gacha_name** | ガチャ名 | `地獄楽 いいジャン祭ピックアップガシャ A` |
| **gacha_type** | ガチャタイプ | `Pickup`(Pickup/Premium/Free等) |
| **start_at** | 開催開始日時 | `2026-01-16 12:00:00` |
| **end_at** | 開催終了日時 | `2026-02-16 10:59:59` |
| **upper_count** | 天井回数 | `100` |
| **multi_draw_count** | N連数 | `10` |
| **multi_fixed_prize_count** | N連確定枠数 | `1` |
| **pickup_unit_ids** | ピックアップキャラID(カンマ区切り) | `chara_jig_00401,chara_jig_00501` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
ガチャの運営仕様書からマスタデータを作成してください。

添付ファイル:
- ガチャ設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- opr_gacha_id: Pickup_jig_001
- gacha_name: 地獄楽 いいジャン祭ピックアップガシャ A
- gacha_type: Pickup
- start_at: 2026-01-16 12:00:00
- end_at: 2026-02-16 10:59:59
- upper_count: 100
- multi_draw_count: 10
- multi_fixed_prize_count: 1
- pickup_unit_ids: chara_jig_00401,chara_jig_00501
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- ガチャID(例: Pickup_jig_001)
- ガチャタイプ(Pickup、Premium、Festival、Free等)
- 開催期間(start_at、end_at)
- ガチャ名・説明文
- 排出キャラクター一覧(ピックアップキャラ、レアリティ別排出率)
- 天井設定(上限グループ、回数)
- コスト設定(単発、10連、チケット)
- 10連設定(確定枠数)

**任意情報**:
- 表示設定(バナー、ロゴ、背景色、表示サイズ)(記載がない場合は推測)
- Strapi管理UUID(記載がない場合は仮UUID)
- ガチャ表示優先度(記載がない場合はデフォルト値)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **OprGacha** - ガチャの基本設定
2. **OprGachaI18n** - ガチャ名・説明文(多言語対応)
3. **OprGachaPrize** - ガチャ排出内容(通常排出と確定枠排出の2種類)
4. **OprGachaUpper** - ガチャ天井設定
5. **OprGachaUseResource** - ガチャ実行コスト(チケット、ダイヤ単発、ダイヤ10連の3種類)
6. **OprGachaDisplayUnitI18n** - ガチャ画面表示キャラの説明文

#### データ依存関係の自動管理

**重要**: 親テーブルを作成した際は、依存する子テーブルも自動的に生成してください。

**依存関係定義** (`config/table_dependencies.json` 参照):
```json
{
  "OprGacha": ["OprGachaI18n"]
}
```

**自動生成ロジック**:
1. **OprGacha**を作成 → **OprGachaI18n**を自動生成
   - id: `{parent_id}_{language}` (例: `Pickup_jig_001_ja`)
   - opr_gacha_id: `{parent_id}`
   - name、descriptionを運営仕様書から抽出

**実装の流れ**:
```
1. OprGacha作成
   ↓ (自動)
2. OprGachaI18n生成

3. OprGachaPrize作成
4. OprGachaUpper作成
5. OprGachaUseResource作成
6. OprGachaDisplayUnitI18n作成
```

この自動生成により、親テーブル未生成による子テーブル欠落を防止できます。

#### ID採番ルール

ガチャのIDは以下の形式で採番します:

```
OprGacha.id: {gacha_type}_{series_id}_{連番3桁}
OprGacha.fixed_prize_group_id: fixd_{opr_gacha_id}
OprGachaI18n.id: {opr_gacha_id}_{language}
OprGachaPrize.id: {group_id}_{連番}
OprGachaDisplayUnitI18n.id: {opr_gacha_id}_{mst_unit_id}_{language}
```

**例**:
```
Pickup_jig_001 (地獄楽ピックアップガチャ1)
fixd_Pickup_jig_001 (確定枠排出グループID)
Pickup_jig_001_ja (日本語I18n)
Pickup_jig_001_1 (通常排出内容1)
Pickup_jig_001_chara_jig_00401_ja (表示ユニット説明文)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(gacha_type、upper_type、cost_type、resource_type等)
- [ ] 開催期間が妥当か(start_at < end_at)
- [ ] 排出重みの合計が適切か(通常排出: 約1,000,000、確定枠排出: 約25,000,000)
- [ ] ピックアップフラグが正しく設定されているか
- [ ] 確定枠の設定が正しいか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `OprGacha.display_information_id`: Strapi管理のUUID(仮設定)
- `OprGacha.display_gacha_caution_id`: Strapi管理のUUID(仮設定)
- `OprGacha.gacha_priority`: ガチャ表示優先度(推測値)
- `OprGachaPrize.weight`: 排出重み(レアリティから計算)
- `OprGachaI18n.logo_asset_key`: ロゴアセットキー(推測値)
- `OprGachaI18n.description`: ガチャ説明文(推測値)
- `OprGachaDisplayUnitI18n.description`: キャラ説明文(推測値)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の6シートを作成(OprGachaPrizeは通常排出と確定枠排出の2種類):
  1. OprGacha
  2. OprGachaI18n
  3. OprGachaPrize(通常排出)
  4. OprGachaPrize(確定枠排出)
  5. OprGachaUpper
  6. OprGachaUseResource
  7. OprGachaDisplayUnitI18n

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### OprGacha.display_information_id
- 値: 84b93bca-1b92-42df-9d6e-3a593fa76a69(仮UUID)
- 理由: 設計書にStrapi管理IDの記載がなかったため、仮のUUIDを生成
- 確認事項: Strapiで該当のガチャ情報を作成し、正しいUUIDに差し替えてください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### OprGacha シート

| ENABLE | id | gacha_type | upper_group | enable_ad_play | enable_add_ad_play_upper | ad_play_interval_time | multi_draw_count | multi_fixed_prize_count | daily_play_limit_count | total_play_limit_count | daily_ad_limit_count | total_ad_limit_count | prize_group_id | fixed_prize_group_id | appearance_condition | unlock_condition_type | unlock_duration_hours | start_at | end_at | display_information_id | dev-qa_display_information_id | display_gacha_caution_id | gacha_priority | release_key |
|--------|----|-----------|-----------|--------------|-----------------------|---------------------|----------------|----------------------|---------------------|---------------------|-------------------|-------------------|--------------|-------------------|-------------------|-------------------|-------------------|---------|-------|---------------------|---------------------------|----------------------|--------------|-------------|
| e | Pickup_jig_001 | Pickup | Pickup_jig_001 | | | __NULL__ | 10 | 1 | __NULL__ | __NULL__ | 0 | __NULL__ | Pickup_jig_001 | fixd_Pickup_jig_001 | Always | None | __NULL__ | 2026-01-16 12:00:00 | 2026-02-16 10:59:59 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 16d9cd62-8b4a-44c5-922a-6a6b7889ce06 | 66 | 202601010 |

### OprGachaI18n シート

| ENABLE | release_key | id | opr_gacha_id | language | name | description | max_rarity_upper_description | pickup_upper_description | fixed_prize_description | banner_url | logo_asset_key | logo_banner_url | gacha_background_color | gacha_banner_size |
|--------|------------|----|-----------|----|------|------------|--------------------------|----------------------|----------------------|----------|--------------|---------------|---------------------|-----------------|
| e | 202601010 | Pickup_jig_001_ja | Pickup_jig_001 | ja | 地獄楽 いいジャン祭ピックアップガシャ A | 「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中! | | ピックアップURキャラ1体確定! | SR以上1体確定 | | jig_00001 | | Yellow | SizeL |

### OprGachaPrize シート(通常排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | Pickup_jig_001_1 | Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 7020 | 1 | 202601010 |
| e | Pickup_jig_001_2 | Pickup_jig_001 | Unit | chara_jig_00501 | 1 | 7020 | 1 | 202601010 |

### OprGachaPrize シート(確定枠排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | fixd_Pickup_jig_001_1 | fixd_Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 175500 | 1 | 202601010 |
| e | fixd_Pickup_jig_001_2 | fixd_Pickup_jig_001 | Unit | chara_jig_00501 | 1 | 175500 | 1 | 202601010 |

### OprGachaUpper シート

| ENABLE | id | upper_group | upper_type | count | release_key |
|--------|----|------------|----------|-------|-------------|
| e | 1 | Pickup_jig_001 | Pickup | 100 | 202601010 |

### OprGachaUseResource シート

| ENABLE | id | opr_gacha_id | cost_type | cost_id | cost_num | draw_count | cost_priority | release_key |
|--------|----|-----------|---------|----|--------|-----------|--------------|-------------|
| e | 1 | Pickup_jig_001 | Item | ticket_glo_00003 | 1 | 1 | 2 | 202601010 |
| e | 2 | Pickup_jig_001 | Diamond | | 150 | 1 | 3 | 202601010 |
| e | 3 | Pickup_jig_001 | Diamond | | 1500 | 10 | 3 | 202601010 |

### OprGachaDisplayUnitI18n シート

| ENABLE | release_key | id | opr_gacha_id | mst_unit_id | language | sort_order | description |
|--------|------------|----|-----------|-----------|----|-----------|------------|
| e | 202601010 | Pickup_jig_001_chara_jig_00401_ja | Pickup_jig_001 | chara_jig_00401 | ja | 1 | 体力の状態に応じて戦闘スタイルが変化する戦術キャラ！ |
| e | 202601010 | Pickup_jig_001_chara_jig_00501_ja | Pickup_jig_001 | chara_jig_00501 | ja | 2 | サポート特化で味方を強化する支援キャラ！ |

### 推測値レポート

#### OprGacha.display_information_id
- **値**: 84b93bca-1b92-42df-9d6e-3a593fa76a69(仮UUID)
- **理由**: 設計書にStrapi管理IDの記載がなかったため、仮のUUIDを生成
- **確認事項**: Strapiで該当のガチャ情報を作成し、正しいUUIDに差し替えてください

#### OprGacha.gacha_priority
- **値**: 66
- **理由**: 設計書にガチャ表示優先度の記載がなかったため、標準的な値を設定
- **確認事項**: 他のガチャとの表示順序を確認し、必要に応じて調整してください

## 注意事項

### 排出内容の設定について

OprGachaPrizeは、以下の2種類のgroup_idで作成してください:

**通常排出(prize_group_id)**:
- group_id: `{opr_gacha_id}`(例: `Pickup_jig_001`)
- 単発・10連の通常排出枠
- 重み合計: 約1,000,000

**確定枠排出(fixed_prize_group_id)**:
- group_id: `fixd_{opr_gacha_id}`(例: `fixd_Pickup_jig_001`)
- 10連の確定枠専用排出(R/N排出を除外)
- 重み合計: 約25,000,000(通常排出の約25倍)

### 排出重みの計算について

排出重みは、レアリティと排出率から計算します。

**基本的な排出率**:
- UR: 3%(合計重み: 30,000)
- SSR: 8%(合計重み: 80,000)
- SR: 15%(合計重み: 150,000)
- R: 27%(合計重み: 270,000)
- N: 47%(合計重み: 470,000)

**ピックアップ時の排出率UP**:
- ピックアップURキャラ: 約2%(重み: 7,020)
- 通常URキャラ: 残りの1%を均等分散(重み: 1,755)
- ピックアップSSRキャラ: 約5%(重み: 14,040)
- 通常SSRキャラ: 残りの3%を均等分散(重み: 8,840)

**確定枠排出の重み**:
- 通常排出の重みに対して約25倍の値を設定
- 例: 通常排出のピックアップURキャラ(7,020)→ 確定枠排出(175,500)

### 天井設定について

OprGachaUpperは、以下の形式で作成してください:

- upper_group: OprGacha.upper_groupと同じ値
- upper_type: `Pickup`(ピックアップ天井)または `MaxRarity`(最高レアリティ天井)
- count: 天井回数(通常100回)

例:
```
ENABLE,id,upper_group,upper_type,count,release_key
e,17,Pickup_jig_001,Pickup,100,202601010
```

**注意**: idは既存のテーブルと重複しないように連番で採番します。

### コスト設定について

OprGachaUseResourceは、以下の3種類のコストを設定してください:

1. **チケット単発**:
   - cost_type: `Item`
   - cost_id: `ticket_glo_00003`(レアガチャチケット)
   - cost_num: `1`
   - draw_count: `1`
   - cost_priority: `2`

2. **ダイヤ単発**:
   - cost_type: `Diamond`
   - cost_id: 空欄
   - cost_num: `150`
   - draw_count: `1`
   - cost_priority: `3`

3. **ダイヤ10連**:
   - cost_type: `Diamond`
   - cost_id: 空欄
   - cost_num: `1500`
   - draw_count: `10`
   - cost_priority: `3`

**注意**: cost_priorityは、数値が小さいほど優先(チケット優先)。

### UUID管理について

以下のカラムは、管理ツールで別途作成する「お知らせ」「ガシャ注意事項」データのIDです:

- OprGacha.display_information_id（お知らせID）
- OprGacha.dev-qa_display_information_id（開発・QA用お知らせID）
- OprGacha.display_gacha_caution_id（ガシャ注意事項ID）

**推奨設定**: 空欄で問題ありません。必要に応じて、管理ツールで対象コンテンツを作成し、発行されたIDを入力してください。

### 開催期間について

開催期間は、以下の形式で設定してください:

```
start_at: YYYY-MM-DD HH:MM:SS
end_at:   YYYY-MM-DD HH:MM:SS
```

**注意点**:
- ガチャはイベント開始時刻より早く開始することが多い(例: イベント15:00開始、ガチャ12:00開始)
- 終了時刻は、イベント終了時刻と同じかそれより前

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `OprGacha.upper_group` → `OprGachaUpper.upper_group`
- `OprGacha.prize_group_id` → `OprGachaPrize.group_id`
- `OprGacha.fixed_prize_group_id` → `OprGachaPrize.group_id`
- `OprGachaI18n.opr_gacha_id` → `OprGacha.id`
- `OprGachaUseResource.opr_gacha_id` → `OprGacha.id`
- `OprGachaDisplayUnitI18n.opr_gacha_id` → `OprGacha.id`
- `OprGachaDisplayUnitI18n.mst_unit_id` → `MstUnit.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: 排出重みの合計が1,000,000にならない

**原因**: ピックアップキャラ数とレアリティ別キャラ数の計算が不正確

**対処法**:
1. ピックアップURキャラ: 各7,020(約2%)
2. 通常URキャラ: 残りの約1%を均等分散
3. 全体合計が1,000,000になるように調整

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid gacha_type: pickup (expected: Pickup)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `pickup` → `Pickup`, `premium` → `Premium`

### Q3: 確定枠排出が正しく動作しない

**原因**: fixed_prize_group_idの設定漏れまたはOprGachaPrizeの重み設定が不正確

**対処法**:
- OprGacha.fixed_prize_group_id: `fixd_{opr_gacha_id}`
- OprGachaPrize(確定枠排出)のgroup_id: `fixd_{opr_gacha_id}`
- 確定枠排出の重み: 通常排出の約25倍

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
