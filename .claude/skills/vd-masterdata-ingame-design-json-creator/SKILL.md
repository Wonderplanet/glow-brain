---
name: vd-masterdata-ingame-design-json-creator
description: VDインゲーム設計書（design.md）とgenerated CSVからdesign.jsonを生成するスキル。
  「ブロックJSON作成」「design.json作成」「VDブロック設計JSON」「インゲームJSON生成」などのキーワードで使用します。
---

# VDインゲームデザインJSON生成スキル

## 概要

`design.md` と `generated/` 配下のCSVからデータを読み取り、`design.json` を自動生成するスキル。

**パイプライン位置づけ**:
```
[1] vd-masterdata-ingame-design-creator → design.md
[2] vd-masterdata-ingame-data-creator   → generated/*.csv
[3] このスキル（今回）                  → design.json
```

**役割分担**:
- **Claude が担う**: design.md / generated CSV を読み、JSONフィールドを構築してファイルに書き込む
- **スクリプト不要**: JSONの書き込みは Write ツールで完結する

---

## ワークフロー

### Step 1: 対象ブロックフォルダの確認

ユーザーからブロック名またはフォルダパスを受け取る。

作業フォルダの例:
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/{block_name}/
```

**注意**: タスクフォルダ名はタスクによって異なる。ユーザーが指定したパスを優先する。
ブロック名のみ指定された場合は、`domain/tasks/` 配下を検索して対象フォルダを特定する。

以下のファイルが存在することを確認する:
- `{block_dir}/design.md`
- `{block_dir}/generated/MstInGame.csv`
- `{block_dir}/generated/MstEnemyOutpost.csv`
- `{block_dir}/generated/MstKomaLine.csv`
- `{block_dir}/generated/MstAutoPlayerSequence.csv`

### Step 2: データ読み込み（Claudeが実施）

以下のファイルを全文読み込む:
- `{block_dir}/design.md`
- `{block_dir}/generated/MstInGame.csv`
- `{block_dir}/generated/MstEnemyOutpost.csv`
- `{block_dir}/generated/MstKomaLine.csv`
- `{block_dir}/generated/MstAutoPlayerSequence.csv`

### Step 3: JSONデータ構築・ファイル保存（Claudeが実施）

下記「フィールドマッピング表」を参照してJSONを構築し、
Write ツールで `{block_dir}/design.json` に書き込む。

### Step 4: 完了確認

生成した `design.json` の構造をサマリー出力する。

```bash
python3 -c "
import json
with open('{block_dir}/design.json') as f:
    d = json.load(f)
print('block_name:', d['block_name'])
print('enemies:', len(d['enemies']))
print('sequences:', len(d['sequences']))
print('koma_rows:', len(d['koma_rows']))
print('basic_info:', d['basic_info'])
"
```

主要フィールドの値を確認し、問題があれば修正する。

---

## フィールドマッピング表

| JSONフィールド | データソース | 抽出方法 |
|-------------|-----------|---------|
| `block_name` | フォルダ名 | パスの末尾コンポーネント |
| `title` | block_name | `"ブロック基礎設計_{block_name}"` |
| `requirements_text` | design.md | `## インゲーム要件テキスト` セクション本文（改行保持） |
| `basic_info.id` | MstInGame.csv | `id` 列 |
| `basic_info.block_type` | MstInGame.csv | `stage_type`: `vd_normal`→`Normal`, `vd_boss`→`Boss` |
| `basic_info.gate_hp` | MstEnemyOutpost.csv | `hp` 列（**数値型**） |
| `basic_info.koma_rows` | MstKomaLine.csv | データ行数 + `"行"` |
| `basic_info.group_switch` | 固定値 | `"なし（デフォルトグループのみ）"` |
| `basic_info.sequence_rows` | MstAutoPlayerSequence.csv | データ行数 + `"行"` |
| `basic_info.release_key` | MstInGame.csv | `release_key` 列（**数値型**） |
| `koma_rows[].row` | MstKomaLine.csv | `"行{n}"` （1-indexed） |
| `koma_rows[].count` | MstKomaLine.csv | `koma1〜4_width` の非空列数 + `"コマ"` |
| `koma_rows[].asset` | MstKomaLine.csv | `koma1_asset_key` |
| `koma_rows[].effect` | MstKomaLine.csv | `koma1_effect_type`（例: `"None"`） |
| `koma_rows[].widths` | MstKomaLine.csv | 非空 koma_width 値を `" / "` で結合 |
| `enemies[].id` | design.md | 敵キャラステータス表の `MstEnemyStageParameter ID` |
| `enemies[].name` | design.md | 敵キャラステータス表の日本語名 |
| `enemies[].color` | design.md | color + 日本語表記（例: `"Yellow（黄属性）"`） |
| `enemies[].role` | design.md | `role_type` |
| `enemies[].hp` | design.md | `hp`（**数値型**、カンマ除去） |
| `enemies[].atk` | design.md | `attack_power`（**数値型**） |
| `enemies[].spd` | design.md | `move_speed`（**数値型**） |
| `enemies[].desc` | design.md | キャラ役割（例: `"雑魚（e_キャラ）"`、`"ボス"` 等） |
| `sequences[].num` | MstAutoPlayerSequence.csv | `elem` 列（**数値型**） |
| `sequences[].trigger` | MstAutoPlayerSequence.csv | `condition_type` 列 |
| `sequences[].value` | MstAutoPlayerSequence.csv | 条件値列（ms等、**数値型**） |
| `sequences[].enemy_id` | MstAutoPlayerSequence.csv | 敵キャラID列 |
| `sequences[].name` | design.md | シーケンス表の日本語敵名 |
| `sequences[].count` | MstAutoPlayerSequence.csv | 出現数列（**数値型**） |
| `sequences[].notes` | design.md | シーケンス表の備考（なければ `""`） |
| `stage_description.battle_hint` | MstInGame.csv | `result_tips.ja`（空なら `""`） |
| `stage_description.stage_text` | MstInGame.csv | `description.ja`（空なら `""`） |

### 属性表記の変換

| design.md の表記 | JSON に書く値 |
|----------------|-------------|
| `Green` | `Green（緑属性）` |
| `Colorless` | `Colorless（無属性）` |
| `Red` | `Red（赤属性）` |
| `Blue` | `Blue（青属性）` |
| `Yellow` | `Yellow（黄属性）` |

### コマ数・幅文字列の算出

MstKomaLine.csv の各行について:
- **コマ数**: `koma1_width`〜`koma4_width` のうち空でない列数 → `"{n}コマ"`
- **幅文字列**: 空でない koma_width 値を `" / "` で結合

例: `koma1_width=0.25, koma2_width=0.50, koma3_width=0.25, koma4_width=（空）`
→ count=`"3コマ"`, widths=`"0.25 / 0.50 / 0.25"`

---

## JSONスキーマ（出力形式）

```json
{
  "block_name": "vd_osh_normal_00001",
  "title": "ブロック基礎設計_vd_osh_normal_00001",
  "requirements_text": "【推しの子】の世界観を...\n...",
  "basic_info": {
    "id": "vd_osh_normal_00001",
    "block_type": "Normal",
    "gate_hp": 100,
    "koma_rows": "3行",
    "group_switch": "なし（デフォルトグループのみ）",
    "sequence_rows": "7行",
    "release_key": 202604010
  },
  "koma_rows": [
    {"row": "行1", "count": "3コマ", "asset": "", "effect": "None", "widths": "0.25 / 0.50 / 0.25"},
    {"row": "行2", "count": "2コマ", "asset": "", "effect": "None", "widths": "0.50 / 0.50"},
    {"row": "行3", "count": "3コマ", "asset": "", "effect": "None", "widths": "0.40 / 0.20 / 0.40"}
  ],
  "enemies": [
    {
      "id": "c_osh_00201_vd_Normal_Green",
      "name": "星野 ルビー",
      "color": "Green（緑属性）",
      "role": "Attack",
      "hp": 50000,
      "atk": 300,
      "spd": 30,
      "desc": "雑魚（c_キャラ）"
    }
  ],
  "sequences": [
    {
      "num": 1,
      "trigger": "ElapsedTime",
      "value": 250,
      "enemy_id": "e_glo_00001_vd_Normal_Colorless",
      "name": "ファントム",
      "count": 5,
      "notes": "開幕5体・序盤の圧力"
    }
  ],
  "stage_description": {
    "battle_hint": "",
    "stage_text": ""
  }
}
```

---

## ガードレール

1. `requirements_text` は改行 `\n` を保持する
2. `gate_hp`・`release_key` は文字列でなく**数値型**で出力する
3. `enemies[].hp`/`atk`/`spd` のカンマ（`25,000` など）を除去して**数値化**する
4. normalブロック: `koma_rows` は3要素、bossブロック: 1要素
5. `enemies[].desc` は design.md のキャラ役割記述（例: `雑魚（e_キャラ）`）から推測する
6. `sequences[].notes` は design.md のシーケンス表の備考欄から取得し、なければ `""` とする

---

## 出力

```
{block_dir}/
└── design.json   ← このスキルが生成するファイル
```
