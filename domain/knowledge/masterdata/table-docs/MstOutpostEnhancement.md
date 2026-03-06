# MstOutpostEnhancement 詳細説明

> CSVパス: `projects/glow-masterdata/MstOutpostEnhancement.csv`
> i18n CSVパス: `projects/glow-masterdata/MstOutpostEnhancementI18n.csv`

---

## 概要

`MstOutpostEnhancement` は**拠点（アウトポスト/ゲート）が持つ強化可能な項目を定義するテーブル**。各拠点に対して、強化できる能力の種類（強化タイプ）とUIアセットを設定する。

1つの拠点（`mst_outpost_id`）に対して複数の強化項目が紐付き、プレイヤーはコインを消費して各項目を強化できる。各項目のレベルごとの詳細設定は `MstOutpostEnhancementLevel` で管理される。

`MstOutpostEnhancementI18n` テーブルと連携して強化項目名を多言語対応する。

### ゲームプレイへの影響

- `outpost_enhancement_type` によって強化の効果が決まる（速度・HP・上限値など）
- `asset_key` でUIに表示する強化アイコンを参照する
- 現状の拠点（`outpost_1`）には6種類の強化項目が定義されている

---

## 全カラム一覧

### mst_outpost_enhancements カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_outpost_id` | varchar(255) | 不可 | - | 紐付く拠点ID（`mst_outposts.id`） |
| `outpost_enhancement_type` | enum | 不可 | - | 強化タイプ（後述のenum参照） |
| `asset_key` | varchar(255) | 不可 | - | 強化項目のUIアセットキー |
| `release_key` | bigint | 不可 | 1 | リリースキー |

### MstOutpostEnhancementI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_outpost_enhancement_id` | varchar(255) | 不可 | - | 親テーブルID（`mst_outpost_enhancements.id`） |
| `language` | varchar(255) | 不可 | - | 言語コード（`ja` など） |
| `name` | varchar(255) | 不可 | - | 強化項目名（UI表示用） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## OutpostEnhancementType（強化タイプ）

| 値 | 説明 | 実データのname（ja） |
|----|------|-------------------|
| `LeaderPointSpeed` | リーダーポイントの増加速度 | リーダーP速度 |
| `LeaderPointLimit` | リーダーポイントの上限値 | リーダーP上限 |
| `OutpostHp` | 拠点のHP | ゲートHP |
| `SummonInterval` | ユニット再召喚の間隔 | 再召喚速度 |
| `LeaderPointUp` | リーダーポイントの獲得量 | リーダーP獲得量 |
| `RushChargeSpeed` | RUSHゲージのチャージ速度 | RUSHゲージ速度 |

---

## 命名規則 / IDの生成ルール

- `id`: `enhance_{拠点番号}_{項目番号}` 形式（例: `enhance_1_3`）
- `MstOutpostEnhancementI18n.id`: `{mst_outpost_enhancement_id}_{言語コード}` 形式（例: `enhance_1_3_ja`）

---

## 他テーブルとの連携

```
MstOutpost
  └─ id → MstOutpostEnhancement.mst_outpost_id（1拠点 = 複数強化項目）

MstOutpostEnhancement
  └─ id → MstOutpostEnhancementLevel.mst_outpost_enhancement_id（1強化項目 = 複数レベル）
  └─ id → MstOutpostEnhancementI18n.mst_outpost_enhancement_id（多言語名称）
```

---

## 実データ例

**パターン1: リーダーポイント速度強化**

| ENABLE | id | mst_outpost_id | outpost_enhancement_type | asset_key | release_key |
|--------|-----|----------------|--------------------------|-----------|-------------|
| e | enhance_1_3 | outpost_1 | LeaderPointSpeed | readerpointspeed | 202509010 |
| e | enhance_1_4 | outpost_1 | LeaderPointLimit | readerpointupperllimit | 202509010 |

**パターン2: I18nデータ**

| ENABLE | release_key | id | mst_outpost_enhancement_id | language | name |
|--------|-------------|-----|---------------------------|----------|------|
| e | 202509010 | enhance_1_3_ja | enhance_1_3 | ja | リーダーP速度 |
| e | 202509010 | enhance_1_5_ja | enhance_1_5 | ja | ゲートHP |

---

## 設定時のポイント

1. **強化タイプは6種類が定義済み**: `LeaderPointSpeed`・`LeaderPointLimit`・`OutpostHp`・`SummonInterval`・`LeaderPointUp`・`RushChargeSpeed` のいずれかを指定する
2. **I18nとセットで作成する**: 強化項目のIDを作成したら必ず対応するI18nレコードも作成する。nameが未設定だとUI上で空欄になる
3. **asset_keyはクライアントと一致させる**: UIアイコンアセットのキーをクライアントチームと確認のうえ設定する
4. **各強化項目に対してレベル設定も必要**: このテーブルだけ作成しても不十分で、`MstOutpostEnhancementLevel` に各レベルの設定を追加する必要がある
5. **拠点IDと一致させる**: `mst_outpost_id` に存在しない拠点IDを指定すると、外部キー制約違反でエラーになる
