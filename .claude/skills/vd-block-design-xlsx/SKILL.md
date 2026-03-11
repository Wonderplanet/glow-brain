---
name: vd-block-design-xlsx
description: VDブロック基礎設計xlsxをdesign.mdとgenerated CSVから自動生成するスキル。design.mdとCSVからデータを読み取り、テンプレートxlsxにデータを入力します。「ブロック基礎設計シート作成」「design xlsx作成」「基礎設計xlsx」「設計シート作成」「vd_xxx_design」などのキーワードで使用します。
---

# VDブロック基礎設計xlsx生成スキル

## 概要

`design.md` と `generated/` 配下のCSVからデータを読み取り、テンプレートxlsxに書き込んで
`{block_name}.xlsx` を生成するスキル。生成されたCSVは各シートとして同一xlsx内に追加される。

**役割分担**:
- **Claude が担う**: design.md / generated CSV を読み、どのデータをどのセルに入れるかを判断
- **スクリプトが担う**: xlsx の機械的な生成（テンプレートコピー・シート名変更・セル書き込み）

---

## ワークフロー

### Step 1: 対象ブロックフォルダの確認

ユーザーからブロック名またはフォルダパスを受け取る。

作業フォルダ:
```
domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/{block_name}/
```

### Step 2: データ読み込み（Claudeが実施）

以下のファイルを全文読み込む:
- `{block_name}/design.md`
- `{block_name}/generated/MstInGame.csv`
- `{block_name}/generated/MstEnemyOutpost.csv`
- `{block_name}/generated/MstKomaLine.csv`
- `{block_name}/generated/MstAutoPlayerSequence.csv`

### Step 3: 入力データの特定（Claudeが実施）

下記の「セルマッピング表」を参照して、各セルへの入力値を決定する。
判断が必要な場合（既存参照 / 新規生成の区別など）は design.md の記述を優先する。

### Step 4: スクリプト実行

```bash
python .claude/skills/vd-block-design-xlsx/scripts/create_design_xlsx.py \
  --block-dir "domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/{block_name}" \
  --data-json '{JSON文字列}'
```

### Step 5: 完了確認

```bash
python -c "
import openpyxl, warnings
warnings.filterwarnings('ignore')
wb = openpyxl.load_workbook('{block_name}.xlsx のフルパス')
print('Sheets:', wb.sheetnames)
for ws in wb.worksheets:
    print(f'--- {ws.title} ---')
    for row in ws.iter_rows(max_row=2):
        print([c.value for c in row if c.value is not None])
"
```

問題があれば修正して再実行する。

---

## セルマッピング表

| セル | 意味 | データソース | 補足 |
|------|------|------------|------|
| B1 | タイトル | block_name | `ブロック基礎設計_{block_name}` |
| B4 | 要件テキスト | design.md | `## インゲーム要件テキスト` セクション本文（改行含む） |
| B9 | インゲームID | MstInGame.csv `id` | |
| C9 | ブロック種別 | MstInGame.csv `stage_type` | `vd_normal`→`Normal`, `vd_boss`→`Boss` |
| D9 | 敵ゲートHP | MstEnemyOutpost.csv `hp` | Normal=100固定, Boss=1000固定 |
| E9 | コマ行数 | MstKomaLine.csv の行数 | `3行` or `1行` |
| F9 | グループ切り替え | 固定値 | `なし（デフォルトグループのみ）`（VDはSwitchSequenceGroup禁止） |
| G9 | シーケンス行数 | MstAutoPlayerSequence.csv の行数 | `7行` など |
| H9 | リリースキー | MstInGame.csv `release_key` | |
| B14〜F16 | コマ効果（行1-3） | MstKomaLine.csv | 行名/コマ数/アセット/None/幅文字列 |
| B26〜I?? | 登場敵パラメータ | design.md 敵キャラステータステーブル | 敵ID/名前/属性/ロール/HP/ATK/SPD/役割 |
| B40〜H?? | シーケンス構成 | MstAutoPlayerSequence.csv | 行番号/trigger/条件値/敵ID/キャラ名/体数/備考 |
| C58 | バトルヒント | design.md（任意） | `result_tips.ja` 相当 |
| C59 | ステージ説明文 | design.md（任意） | `description.ja` 相当 |

### コマ行の幅文字列フォーマット

MstKomaLine.csv から各行の `koma1_width`, `koma2_width`, `koma3_width`, `koma4_width` を読み取り、
空でない値を ` / ` で結合した文字列を生成する。

例: `koma1_width=0.25, koma2_width=0.50, koma3_width=0.25` → `0.25 / 0.50 / 0.25`

### コマ数の算出

MstKomaLine.csv の各行で `koma1_width`〜`koma4_width` のうち空でない列数を数える。

例: koma1_width=0.25, koma2_width=0.50, koma3_width=0.25, koma4_width=（空） → `3コマ`

### 属性表記の変換

design.md の敵パラメータテーブルから読み取る。表記例:
- `Green` → `Green（緑属性）`
- `Colorless` → `Colorless（無属性）`
- `Red` → `Red（赤属性）`
- `Blue` → `Blue（青属性）`
- `Yellow` → `Yellow（黄属性）`

---

## JSONスキーマ（`--data-json` に渡す形式）

```json
{
  "block_name": "vd_osh_normal_00001",
  "title": "ブロック基礎設計_vd_osh_normal_00001",
  "requirements_text": "【推しの子】の世界観を...",
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

**注意**: `stage_description` は任意フィールド。design.md に記述がなければ省略可。

---

## パス定数

| 定数 | 値 |
|------|-----|
| テンプレートxlsx | `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/限界チャレンジ(VD)_アウトゲーム関連_ブロック基礎設計テンプレート.xlsx` |
| 出力ファイル名 | `{block_name}.xlsx`（作業フォルダ直下） |

---

## ガードレール

1. **データ判断はClaudeが行う**: スクリプトに渡すJSONをClaudeが生成する
2. **要件テキストは改行を保持する**: `\n` をそのままJSON文字列に含める
3. **コマ行は3行固定（normalブロック）**: `koma_rows` 配列は3要素
4. **bossブロックはコマ行1行**: `koma_rows` 配列は1要素
5. **シーケンス備考は design.md から推測**: MstAutoPlayerSequence.csv にないコメントはdesign.mdの表から補う
