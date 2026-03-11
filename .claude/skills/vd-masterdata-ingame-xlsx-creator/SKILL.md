---
name: vd-masterdata-ingame-xlsx-creator
description: VDインゲーム全ブロックを統合したxlsxファイルを1つ生成するスキル。vd-ingame-design-creator/以下の全ブロックのdesign.jsonとCSVを読み込み、ブロック基礎設計シートとテーブルシートを1つのxlsxにまとめます。「全ブロックxlsx作成」「vd_all xlsx」「統合設計シート」「全ブロック統合」などのキーワードで使用します。
allowed-tools: Bash(python *:*)
---

# VDインゲーム全ブロック統合xlsx生成スキル

## 概要

`vd-ingame-design-creator/` 配下の全ブロックの `design.json` と `generated/*.csv` を読み込み、テンプレートxlsxをベースに1つの xlsx ファイルに統合する**全自動スキル**。

**特徴**:
- Claude によるデータ判断不要（スクリプトが全自動で処理）
- 将来ブロックが追加されても自動対応（ブロックフォルダを動的探索）
- 全ブロックの設計シートとCSVシートを1ファイルに統合

---

## ワークフロー

### Step 1: タスクディレクトリの確認

ユーザーからタスクディレクトリを確認する。
デフォルト: `domain/tasks/20260311_202700_vd_masterdata_ingame_generation`

### Step 2: スクリプトを実行

```bash
python .claude/skills/vd-masterdata-ingame-xlsx-creator/scripts/create_all_xlsx.py \
  --task-dir "domain/tasks/20260311_202700_vd_masterdata_ingame_generation"
```

### Step 3: 出力確認

```bash
python -c "
import openpyxl, warnings
warnings.filterwarnings('ignore')
wb = openpyxl.load_workbook('domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd_all/vd_all.xlsx')
print('シート一覧:', wb.sheetnames)
for ws in wb.worksheets:
    print(f'--- {ws.title} ---')
    for row in ws.iter_rows(max_row=2):
        print([c.value for c in row if c.value is not None])
"
```

---

## 入出力

| 種類 | パス |
|------|------|
| **入力（ブロック）** | `{task-dir}/vd-ingame-design-creator/{block_name}/design.json` |
| **入力（CSV）** | `{task-dir}/vd-ingame-design-creator/{block_name}/generated/*.csv` |
| **入力（MstEnemyStageParameter）** | `{task-dir}/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv` |
| **入力（テンプレート）** | `{task-dir}/specs/限界チャレンジ(VD)_アウトゲーム関連_ブロック基礎設計テンプレート.xlsx` |
| **出力** | `{task-dir}/vd_all/vd_all.xlsx` |

---

## 生成されるシート構成

```
ブロック基礎設計_{block_name1}   ← design.json から（テンプレートコピー）
ブロック基礎設計_{block_name2}   ← design.json から（テンプレートコピー）
...（ブロック数分）
MstInGame                        ← 全ブロックの generated/MstInGame.csv をまとめる
MstEnemyOutpost                  ← 全ブロックの generated/MstEnemyOutpost.csv をまとめる
MstKomaLine                      ← 全ブロックの generated/MstKomaLine.csv をまとめる
MstAutoPlayerSequence            ← 全ブロックの generated/MstAutoPlayerSequence.csv をまとめる
MstPage                          ← 全ブロックの generated/MstPage.csv をまとめる
MstEnemyStageParameter           ← vd_all/data/MstEnemyStageParameter.csv から
```

---

## スクリプト引数

| 引数 | 説明 | デフォルト |
|------|------|-----------|
| `--task-dir` | タスクディレクトリのパス | 必須 |
| `--template` | テンプレートxlsxのパス | `{task-dir}/specs/限界チャレンジ(VD)_アウトゲーム関連_ブロック基礎設計テンプレート.xlsx` |
