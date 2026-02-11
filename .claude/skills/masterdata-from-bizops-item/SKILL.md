---
name: masterdata-from-bizops-item
description: アイテムの運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 2個(MstItem, MstItemI18n)。キャラクター欠片、キャラメモリー、ガチャチケット等のアイテムマスタデータを精度高く作成します。
---

# アイテム マスタデータ作成スキル

## 概要

アイテムの運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の2テーブルを自動生成:

**アイテム基本情報**:
- **MstItem** - アイテムの基本情報(タイプ、レアリティ、効果値等)
- **MstItemI18n** - アイテム名・説明文(多言語対応)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **item_type** | アイテムタイプ | `CharacterFragment`(欠片)、`RankUpMaterial`(メモリー)、`Ticket`(チケット) |
| **start_date** | 開始日時 | `2026-01-16 15:00:00` |
| **end_date** | 終了日時 | `2037-12-31 23:59:59`(恒久的なアイテムの場合) |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
アイテムの運営仕様書からマスタデータを作成してください。

添付ファイル:
- ヒーロー設計書_地獄楽_いいジャン祭.xlsx
- クエスト設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- item_type: CharacterFragment,RankUpMaterial
- start_date: 2026-01-16 15:00:00
- end_date: 2037-12-31 23:59:59
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- アイテムID(キャラクター欠片、キャラメモリー、チケット等)
- アイテム名
- アイテムタイプ(CharacterFragment、RankUpMaterial、Ticket等)
- レアリティ(UR、SSR、SR、R、N)
- 開始・終了日時

**任意情報**:
- 効果値(キャラメモリーの場合はキャラID)(記載がない場合は推測)
- アイテム説明文(記載がない場合は推測)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstItem** - アイテムの基本設定
2. **MstItemI18n** - アイテム名・説明文(多言語対応)

#### データ依存関係の自動管理

**重要**: 親テーブルを作成した際は、依存する子テーブルも自動的に生成してください。

**依存関係定義** (`config/table_dependencies.json` 参照):
```json
{
  "MstItem": ["MstItemI18n"]
}
```

**自動生成ロジック**:
1. **MstItem**を作成 → **MstItemI18n**を自動生成
   - id: `{parent_id}_{language}` (例: `piece_jig_00401_ja`)
   - mst_item_id: `{parent_id}`
   - name、descriptionを運営仕様書から抽出

**実装の流れ**:
```
1. MstItem作成
   ↓ (自動)
2. MstItemI18n生成
```

この自動生成により、親テーブル未生成による子テーブル欠落を防止できます。

#### ID採番ルール

アイテムのIDは以下の形式で採番します:

```
キャラクター欠片: piece_{series_id}_{連番5桁}
キャラメモリー: memory_chara_{series_id}_{連番5桁}
チケット: ticket_glo_{連番5桁}
```

**例**:
```
piece_jig_00401 (地獄楽 キャラ401の欠片)
memory_chara_jig_00601 (地獄楽 キャラ601のメモリー)
ticket_glo_00003 (GLOWレアガチャチケット)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(type、rarity等)
- [ ] 効果値が適切に設定されているか(キャラメモリーの場合はキャラIDを設定)
- [ ] 日時形式が妥当か(YYYY-MM-DD HH:MM:SS形式)

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstItem.effect_value`: キャラメモリーの効果値(対応するキャラID)(推測値)
- `MstItem.sort_order`: 表示順序(推測値)
- `MstItemI18n.description`: アイテム説明文(推測値)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の2シートを作成:
  1. MstItem
  2. MstItemI18n

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstItem.effect_value
- 値: chara_jig_00601
- 理由: 設計書にキャラメモリーの効果値(対応するキャラID)の記載がなかったため、アイテムIDから推測して設定
- 確認事項: 正しいキャラIDを確認し、必要に応じて差し替えてください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstItem シート

| ENABLE | id | type | group_type | rarity | asset_key | effect_value | sort_order | start_date | end_date | release_key | item_type | destination_opr_product_id |
|--------|----|----|----------|--------|----------|------------|-----------|----------|---------|------------|----------|--------------------------|
| e | memory_chara_jig_00601 | RankUpMaterial | Etc | SR | memory_chara_jig_00601 | chara_jig_00601 | 1013 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | RankUpMaterial | |
| e | piece_jig_00401 | CharacterFragment | Etc | UR | piece_jig_00401 | | 1 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | CharacterFragment | |

### MstItemI18n シート

| ENABLE | release_key | id | mst_item_id | language | name | description |
|--------|------------|----|------------|---------|------|-----------|
| e | 202601010 | memory_chara_jig_00601_ja | memory_chara_jig_00601 | ja | 民谷 巌鉄斎のメモリー | 民谷 巌鉄斎のLv.上限開放に使用するアイテム |
| e | 202601010 | piece_jig_00401_ja | piece_jig_00401 | ja | 賊王 亜左 弔兵衛のかけら | 賊王 亜左 弔兵衛のグレードアップに使用するアイテム |

### 推測値レポート

#### MstItem.effect_value
- **値**: chara_jig_00601
- **理由**: 設計書にキャラメモリーの効果値(対応するキャラID)の記載がなかったため、アイテムIDから推測して設定
- **確認事項**: 正しいキャラIDを確認し、必要に応じて差し替えてください

#### MstItemI18n.description
- **値**: 民谷 巌鉄斎のLv.上限開放に使用するアイテム
- **理由**: 設計書にアイテム説明文の記載がなかったため、標準的な説明文を推測して設定
- **確認事項**: 正しいアイテム説明文を確認し、必要に応じて差し替えてください

## 注意事項

### アイテムタイプについて

MstItemのtypeとitem_typeは同じ値を設定してください:

| type | 説明 | 用途 |
|------|------|------|
| **CharacterFragment** | キャラクター欠片 | キャラクターのグレードアップに使用 |
| **RankUpMaterial** | ランクアップ素材 | キャラクターのランクアップに使用 |
| **Ticket** | チケット | ガチャ等に使用するチケット |
| **MemoryFragment** | メモリーフラグメント | 汎用的な育成素材 |
| **Memory** | メモリー | 汎用的な育成素材 |
| **Coin** | コイン | ゲーム内通貨 |

**重要**: 大文字小文字を正確に一致させてください。

### レアリティについて

MstItemのrarityは以下のいずれかを設定してください:

| rarity | 説明 |
|--------|------|
| **UR** | Ultra Rare(最高レアリティ) |
| **SSR** | Super Super Rare(非常に高レアリティ) |
| **SR** | Super Rare(高レアリティ) |
| **R** | Rare(中レアリティ) |
| **N** | Normal(通常レアリティ) |

**ポイント**: キャラクター欠片やキャラメモリーのレアリティは、対応するキャラクターのレアリティと一致させてください。

### 効果値の設定について

type=RankUpMaterial(キャラメモリー)の場合:
- effect_valueに対応するキャラID(`chara_jig_00601`等)を必ず設定

type=CharacterFragment(キャラ欠片)の場合:
- effect_valueは空欄

**例**:
```
memory_chara_jig_00601 → effect_value: chara_jig_00601
piece_jig_00401 → effect_value: (空欄)
```

### 日時設定について

- start_date: イベント開始日時を設定(例: `2026-01-16 15:00:00`)
- end_date: 恒久的なアイテムは `2037-12-31 23:59:59`、期間限定アイテムはイベント終了日時を設定

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstItemI18n.mst_item_id` → `MstItem.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: enum値のエラーが発生する

**エラー**:
```
Invalid type: characterfragment (expected: CharacterFragment)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `characterfragment` → `CharacterFragment`, `ur` → `UR`

### Q2: キャラメモリーの効果値が設定されていない

**エラー**:
```
RankUpMaterial requires effect_value
```

**対処法**:
- type=RankUpMaterial(キャラメモリー)の場合、effect_valueに対応するキャラID(`chara_jig_00601`等)を必ず設定

### Q3: アイテムIDが重複している

**原因**: 連番の採番ミス

**対処法**:
- 既存のアイテムIDと重複しないように連番を採番
- 連番はゼロパディング5桁で設定(例: 00001、00002、...)

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
