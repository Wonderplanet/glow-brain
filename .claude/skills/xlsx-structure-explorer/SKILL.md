---
name: xlsx-structure-explorer
description: XLSXファイルの構造（シート一覧・セル範囲・数式・結合セル・プルダウン）を一発で調査するスキル。dungeon-ingame-xlsx-generatorや他のXLSX操作タスクで、毎回書き捨てていた探索コードを置き換えます。「XLSXの構造確認」「セル範囲調査」「プルダウン確認」「数式確認」「複数ファイル比較」などのキーワードで使用します。
---

# XLSX構造調査スキル

XLSXファイルの構造を「一発で」調査できるスキルです。

**このスキルは参照専用**です。ファイルの変更は行いません。

---

## クイックスタート

### 1. シート一覧を確認する（overview）

```bash
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --mode overview \
  domain/tasks/dungeon-ingame-xlsx-generator/work/検証用コピー_【ストーリー】必ず生きて帰る.xlsx
```

出力例:
```
# XLSX構造レポート: ...xlsx

## シート一覧
| # | シート名 | 最終行 | 最終列 |
|---|---------|--------|--------|
| 1 | 1話     | 120    | CV(100) |
| 2 | 通常ブロック | 85 | BZ(78) |
```

### 2. セル範囲の値・数式を確認する（cells）

```bash
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --sheet 1話 --mode cells \
  --row-range 28-35 --col-range BE-CV \
  domain/tasks/dungeon-ingame-xlsx-generator/work/検証用コピー_【ストーリー】必ず生きて帰る.xlsx
```

出力例:
```
## セル範囲: 1話シート BE28〜CV35

| 行 | BE | BF | BG | ... |
|----|----|----|----| ... |
| 28 | ★MstPage投入用ヘッダー | page_id | ... |
| 29 | (数式) | =IF($E$13="","",... | ... |
```

### 3. プルダウン（データバリデーション）を確認する（validations）

```bash
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --sheet 通常ブロック --mode validations \
  domain/tasks/dungeon-ingame-xlsx-generator/raw/検証用コピー_未完成_限界チャレンジ(VD)_アウトゲーム関連.xlsx
```

---

## 全モード一覧

| モード | 説明 | 必須オプション |
|--------|------|--------------|
| `overview` | シート一覧 + 最終行・列 | なし |
| `cells` | 指定範囲のセル値・数式を表形式で表示 | `--sheet` + `--row-range` + `--col-range` |
| `formulas` | 指定範囲の数式のみ抽出 | `--sheet` |
| `merges` | 結合セル一覧 | `--sheet` |
| `validations` | プルダウン（データバリデーション）一覧 | `--sheet` |
| `compare` | 複数ファイルの同一シートを並べて比較 | `--sheet` + `--files` |

---

## オプション一覧

```
python3 explore.py [OPTIONS] <xlsx-path>

--sheet NAME        シート名（cells/formulas/merges/validationsモードで必須）
--mode MODE         調査モード（デフォルト: overview）
--row-range N-M     行範囲 例: 28-35
--col-range X-Y     列範囲 例: BE-CV または 57-100
--files A,B,C       compareモード時のファイルリスト（カンマ区切り）
--data-only         キャッシュ値を表示（数式の計算結果を見たい場合）
```

---

## 使用例（3パターン）

### パターン1: dungeon-ingame-xlsx-generator でXLSX全体を把握する

```bash
# workディレクトリのXLSX一覧 → シート一覧 → 1話シートのMstPage投入エリア確認
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --mode overview \
  domain/tasks/dungeon-ingame-xlsx-generator/work/対象ファイル.xlsx

python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --sheet 1話 --mode cells --row-range 27-30 --col-range A-G \
  domain/tasks/dungeon-ingame-xlsx-generator/work/対象ファイル.xlsx
```

### パターン2: 数式テンプレートの内容を確認する

```bash
# rawとworkの数式差分を調べる（まず数式一覧を取得）
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --sheet 1話 --mode formulas --row-range 29-40 --col-range BE-CV \
  domain/tasks/dungeon-ingame-xlsx-generator/raw/テンプレート.xlsx
```

### パターン3: 複数ファイルの同じシート構造を比較する

```bash
python3 .claude/skills/xlsx-structure-explorer/scripts/explore.py \
  --mode compare --sheet 1話 \
  --row-range 28-35 --col-range BE-BJ \
  --files \
  "domain/tasks/dungeon-ingame-xlsx-generator/raw/A.xlsx,domain/tasks/dungeon-ingame-xlsx-generator/raw/B.xlsx" \
  dummy.xlsx
```

---

## 依存ライブラリ

- `openpyxl`（標準的なPython XLSXライブラリ、追加インストール不要な場合が多い）

インストール（未インストールの場合）:
```bash
pip install openpyxl
```

---

## スクリプトのパス

```
.claude/skills/xlsx-structure-explorer/scripts/explore.py
```

glow-brainリポジトリのルートから実行してください。

---

## よくあるトラブル

| 問題 | 原因・解決策 |
|------|------------|
| `FileNotFoundError` | XLSXパスが相対パスの場合、glow-brainルートから実行しているか確認 |
| `シートが存在しません` | まず `--mode overview` でシート名を正確に確認する |
| 数式が`None`になる | `--data-only`なしで実行すると数式文字列が読める |
| `openpyxl`がない | `pip install openpyxl` でインストール |
