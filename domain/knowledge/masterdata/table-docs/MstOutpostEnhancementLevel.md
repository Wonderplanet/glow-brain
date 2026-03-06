# MstOutpostEnhancementLevel 詳細説明

> CSVパス: `projects/glow-masterdata/MstOutpostEnhancementLevel.csv`
> i18n CSVパス: `projects/glow-masterdata/MstOutpostEnhancementLevelI18n.csv`

---

## 概要

`MstOutpostEnhancementLevel` は**拠点強化項目のレベルごとの設定テーブル**。各強化項目（`MstOutpostEnhancement`）に対して、レベルごとの消費コイン量と強化値を定義する。

レベル1は初期状態（コスト0、強化値0）として設定し、レベル2以降からコインを消費して強化値が上昇していく構造になっている。

`MstOutpostEnhancementLevelI18n` テーブルと連携して強化時の説明文を多言語対応する。

### ゲームプレイへの影響

- プレイヤーが強化するたびにレベルが上がり `cost_coin` のコインを消費する
- `enhancement_value` が実際のゲームパラメータへ適用される強化量（小数点対応）
- レベル1の強化値は `0.0` が一般的で、強化なしの初期状態を表す

---

## 全カラム一覧

### mst_outpost_enhancement_levels カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_outpost_enhancement_id` | varchar(255) | 不可 | - | 強化項目ID（`mst_outpost_enhancements.id`） |
| `level` | int unsigned | 不可 | - | レベル番号（1始まり） |
| `cost_coin` | int unsigned | 不可 | - | このレベルへ強化するための消費コイン数 |
| `enhancement_value` | double(8,2) | 不可 | - | このレベルでの強化値（小数点2桁） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

### MstOutpostEnhancementLevelI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_outpost_enhancement_level_id` | varchar(255) | 不可 | - | 親テーブルID（`mst_outpost_enhancement_levels.id`） |
| `language` | varchar(255) | 不可 | - | 言語コード（`ja` など） |
| `description` | varchar(255) | 不可 | - | 強化時の説明文（レベル1はNULLの場合あり） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_outpost_enhancement_id}_{レベル番号}` 形式（例: `enhance_1_3_1`、`enhance_1_3_10`）
- `MstOutpostEnhancementLevelI18n.id`: `{mst_outpost_enhancement_level_id}_{言語コード}` 形式（例: `enhance_1_3_1_ja`）

---

## 他テーブルとの連携

```
MstOutpostEnhancement
  └─ id → MstOutpostEnhancementLevel.mst_outpost_enhancement_id（1項目 = 複数レベル）

MstOutpostEnhancementLevel
  └─ id → MstOutpostEnhancementLevelI18n.mst_outpost_enhancement_level_id（多言語説明文）
```

---

## 実データ例

**パターン1: リーダーP速度強化（enhance_1_3）のレベル一覧**

| ENABLE | id | mst_outpost_enhancement_id | level | cost_coin | enhancement_value | release_key |
|--------|-----|---------------------------|-------|-----------|-------------------|-------------|
| e | enhance_1_3_1 | enhance_1_3 | 1 | 0 | 0.0 | 202509010 |
| e | enhance_1_3_2 | enhance_1_3 | 2 | 500 | 0.1 | 202509010 |
| e | enhance_1_3_3 | enhance_1_3 | 3 | 1000 | 0.2 | 202509010 |
| e | enhance_1_3_5 | enhance_1_3 | 5 | 1500 | 0.6 | 202509010 |
| e | enhance_1_3_10 | enhance_1_3 | 10 | 10000 | 1.5 | 202509010 |

**パターン2: I18nデータ**

| ENABLE | release_key | id | mst_outpost_enhancement_level_id | language | description |
|--------|-------------|-----|----------------------------------|----------|-------------|
| e | 202509010 | enhance_1_3_1_ja | enhance_1_3_1 | ja | NULL |
| e | 202509010 | enhance_1_3_2_ja | enhance_1_3_2 | ja | リーダーPの増加スピードがアップします。 |

---

## 設定時のポイント

1. **レベル1はコスト0・強化値0が基本**: 初期状態（未強化）を表すレベル1は `cost_coin = 0`、`enhancement_value = 0.0` で設定する
2. **enhancement_valueは小数点対応**: `double(8,2)` 型なので小数点2桁まで設定可能。強化タイプに応じた意味合いの単位（倍率・秒数など）を確認のうえ設定する
3. **コストの段階的増加**: レベルが上がるにつれてコストが増加するよう設計する（500 → 1000 → 1500 → 3000 → 6000 → …）
4. **I18nの説明文**: レベル1は説明文をNULLにするケースが多い。レベル2以降は「XXXがアップします。」の形式で統一されている
5. **連続したレベルを定義する**: レベルに抜けがあるとゲームの強化システムが正常に動作しない可能性があるため、1から連番で定義する
6. **I18nとセットで作成する**: 各レベルIDに対応するI18nレコードも忘れずに作成する
