# スプレッドシートフォルダ一括移動

## 概要

Google Driveから取得したスプレッドシートフォルダを、`file_lists`のCSVファイルに記載された正しいパス構造に基づいて一括移動するプロンプトです。

移動元のフォルダ構造は柔軟に対応可能で、任意の場所にある任意の階層のフォルダを、file_listsに基づいて適切な場所に移動できます。

## 対象ファイル構造の例

```
domain/raw-data/google-drive/spread-sheet/GLOW/
├── [移動元フォルダ（任意の場所・任意の名前）]/
│   ├── フォルダA/                          # 移動対象
│   ├── フォルダB/                          # 移動対象
│   └── spreadsheet_list.csv               # 通常は移動対象外
├── file_lists/                             # フォルダパス情報を含むCSVファイル群
│   ├── file_list-010_企画・仕様.csv
│   ├── file_list-031_レベルデザイン.csv
│   └── file_list-080_運営.csv
├── 010_企画・仕様/                         # 移動先カテゴリフォルダ
├── 031_レベルデザイン/                     # 移動先カテゴリフォルダ
└── 080_運営/                               # 移動先カテゴリフォルダ
```

**重要:** 移動元フォルダの場所や名前は問いません。`temp/`配下である必要もありません。

## 前提条件

1. **file_listsのフォルダパス形式**
   - `domain/raw-data/google-drive/spread-sheet/GLOW` をルートとしたパス
   - 例: `/080_運営/いいジャン祭（施策）/運営_仕様書/20260101_推しの子`

2. **移動対象の特定方法**
   - 移動元フォルダ内のサブフォルダ名が、file_listsのCSVの「ファイル名」列と一致する
   - そのCSVの「フォルダパス」列の値が、正しい移動先パス
   - フォルダ名は完全一致が必要（特殊文字を含む）

3. **必要なツール**
   - DuckDB: CSVファイルのクエリ
   - jq: JSONの解析
   - bash: ファイル操作

## 実行手順

### ステップ0: 移動元フォルダパスの指定

まず、移動対象のフォルダが格納されている親フォルダのパスを特定します。

```bash
BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/google-drive/spread-sheet/GLOW"

# 移動元フォルダパス（例）
SOURCE_DIR="$BASE_DIR/temp/GLOW-050_QA-03_データQA-2025年施策-202512020(推しの子&年末年始)"
# または
# SOURCE_DIR="$BASE_DIR/任意のフォルダ名"
# または絶対パス
# SOURCE_DIR="/path/to/your/folder"
```

### ステップ1: 移動対象フォルダの確認

```bash
# 移動対象フォルダのリストを取得
ls -1 "$SOURCE_DIR"

# または、spreadsheet_list.csv以外のフォルダのみ表示
ls -1 "$SOURCE_DIR" | grep -v "spreadsheet_list.csv"
```

### ステップ2: DuckDBでフォルダパスを検索

```bash
cd "$BASE_DIR/file_lists"

# オプション1: 手動でフォルダ名を指定
# 移動対象フォルダ名の配列を作成（実際のフォルダ名に置き換え）
FOLDER_NAMES=(
  'フォルダA'
  'フォルダB'
  'フォルダC'
)

# オプション2: 移動元フォルダから自動取得（spreadsheet_list.csvを除外）
# より推奨される方法
mapfile -t FOLDER_NAMES < <(ls -1 "$SOURCE_DIR" | grep -v "spreadsheet_list.csv" | grep -v "^\.DS_Store$")

# フォルダ名を確認
echo "=== 検索対象フォルダ名 ==="
printf '%s\n' "${FOLDER_NAMES[@]}"
echo ""

# DuckDBでマッピングを作成（フォルダ名配列から動的に生成）
FOLDER_LIST=$(printf "'%s'," "${FOLDER_NAMES[@]}" | sed 's/,$//')

duckdb -json -c "
WITH folder_names AS (
  SELECT unnest([${FOLDER_LIST}]) AS folder_name
),
all_files AS (
  SELECT 'file_list-010_企画・仕様.csv' as source, \"ファイル名\" as file_name, \"フォルダパス\" as folder_path
  FROM read_csv_auto('file_list-010_企画・仕様.csv')
  UNION ALL
  SELECT 'file_list-031_レベルデザイン.csv', \"ファイル名\", \"フォルダパス\"
  FROM read_csv_auto('file_list-031_レベルデザイン.csv')
  UNION ALL
  SELECT 'file_list-080_運営.csv', \"ファイル名\", \"フォルダパス\"
  FROM read_csv_auto('file_list-080_運営.csv')
)
SELECT
  f.folder_name,
  a.folder_path,
  a.source
FROM folder_names f
JOIN all_files a ON a.file_name = f.folder_name
ORDER BY f.folder_name
" > /tmp/move_mapping.json
```

### ステップ3: マッピング結果の確認

```bash
# 読みやすい形式で表示
cat /tmp/move_mapping.json | jq -r '.[] | "\(.folder_name) -> \(.folder_path)"'
```

### ステップ4: 一括移動の実行

```bash
#!/bin/bash

# 変数はステップ0で定義済みのものを使用
# BASE_DIR と SOURCE_DIR が設定されていることを確認

echo "=== フォルダ移動処理開始 ==="
echo "移動元: $SOURCE_DIR"
echo "移動先ベース: $BASE_DIR"
echo ""

# JSONファイルから読み込んで処理
cat /tmp/move_mapping.json | jq -r '.[] | @json' | while read -r item; do
  folder_name=$(echo "$item" | jq -r '.folder_name')
  folder_path=$(echo "$item" | jq -r '.folder_path')

  source_path="$SOURCE_DIR/$folder_name"
  target_path="$BASE_DIR$folder_path"

  # ソースが存在するか確認
  if [ ! -e "$source_path" ]; then
    echo "✗ スキップ: ソースが存在しません - $folder_name"
    continue
  fi

  # ターゲットディレクトリを作成
  mkdir -p "$target_path"

  # 移動
  if mv "$source_path" "$target_path/"; then
    echo "✓ 移動完了: $folder_name"
    echo "  → $folder_path"
  else
    echo "✗ 移動失敗: $folder_name"
  fi
  echo ""
done

echo "=== 処理完了 ==="
```

### ステップ5: 移動結果の確認

```bash
# tempフォルダの残存確認
ls -la "$BASE_DIR/temp"

# 元のフォルダの状態確認
ls -la "$SOURCE_DIR"

# 移動先のサンプル確認
ls "$BASE_DIR/080_運営/いいジャン祭（施策）/運営_仕様書/" | head -5
ls "$BASE_DIR/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子" | head -5
```

## 簡易版: 全ステップを1つのスクリプトで実行

上記のステップを1つにまとめた実行スクリプト：

```bash
#!/bin/bash

# ========================================
# 設定: ここを変更
# ========================================
BASE_DIR="/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/google-drive/spread-sheet/GLOW"
SOURCE_DIR="$BASE_DIR/temp/任意のフォルダ名"  # ← 移動元フォルダパスを指定

# ========================================
# 処理開始
# ========================================
echo "=== 移動元フォルダの確認 ==="
ls -1 "$SOURCE_DIR" | grep -v "spreadsheet_list.csv" | head -10
echo ""

# フォルダ名を自動取得
cd "$BASE_DIR/file_lists"
mapfile -t FOLDER_NAMES < <(ls -1 "$SOURCE_DIR" | grep -v "spreadsheet_list.csv" | grep -v "^\.DS_Store$")
FOLDER_LIST=$(printf "'%s'," "${FOLDER_NAMES[@]}" | sed 's/,$//')

echo "=== DuckDBでマッピング作成中 ==="
duckdb -json -c "
WITH folder_names AS (
  SELECT unnest([${FOLDER_LIST}]) AS folder_name
),
all_files AS (
  SELECT 'file_list-010_企画・仕様.csv' as source, \"ファイル名\" as file_name, \"フォルダパス\" as folder_path
  FROM read_csv_auto('file_list-010_企画・仕様.csv')
  UNION ALL
  SELECT 'file_list-031_レベルデザイン.csv', \"ファイル名\", \"フォルダパス\"
  FROM read_csv_auto('file_list-031_レベルデザイン.csv')
  UNION ALL
  SELECT 'file_list-080_運営.csv', \"ファイル名\", \"フォルダパス\"
  FROM read_csv_auto('file_list-080_運営.csv')
)
SELECT f.folder_name, a.folder_path
FROM folder_names f
JOIN all_files a ON a.file_name = f.folder_name
ORDER BY f.folder_name
" > /tmp/move_mapping.json

echo "マッピング結果:"
cat /tmp/move_mapping.json | jq -r '.[] | "\(.folder_name) -> \(.folder_path)"'
echo ""

# 移動実行の確認
read -p "移動を実行しますか？ (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "キャンセルしました"
    exit 0
fi

echo "=== フォルダ移動処理開始 ==="
cat /tmp/move_mapping.json | jq -r '.[] | @json' | while read -r item; do
  folder_name=$(echo "$item" | jq -r '.folder_name')
  folder_path=$(echo "$item" | jq -r '.folder_path')
  source_path="$SOURCE_DIR/$folder_name"
  target_path="$BASE_DIR$folder_path"

  if [ ! -e "$source_path" ]; then
    echo "✗ スキップ: $folder_name"
    continue
  fi

  mkdir -p "$target_path"
  if mv "$source_path" "$target_path/"; then
    echo "✓ 移動完了: $folder_name → $folder_path"
  else
    echo "✗ 移動失敗: $folder_name"
  fi
done

echo "=== 処理完了 ==="
```

## 注意事項

1. **spreadsheet_list.csvは移動対象外**
   - 各フォルダ内のspreadsheet_list.csvは移動されずに残る
   - これは意図的な動作

2. **フォルダ名の完全一致が必要**
   - file_listsのCSVの「ファイル名」列と、移動対象のフォルダ名が完全一致する必要がある
   - 特殊文字（【】など）も含めて完全一致

3. **file_listsに存在しないフォルダ**
   - マッピングで見つからなかったフォルダは移動されない
   - 手動で確認して適切な場所に移動する

4. **既存フォルダとの重複**
   - 移動先に同名フォルダが存在する場合、mvコマンドの動作に依存
   - 通常は上書き確認が求められる

5. **移動元フォルダの柔軟性**
   - `temp/`配下である必要はない
   - 任意の場所、任意の名前のフォルダから移動可能
   - 階層の深さも問わない

## トラブルシューティング

### DuckDBでマッチしない場合

```bash
# 特定のフォルダ名を部分一致で検索
duckdb -c "
SELECT \"ファイル名\", \"フォルダパス\"
FROM read_csv_auto('file_list-080_運営.csv')
WHERE \"ファイル名\" LIKE '%キーワード%'
LIMIT 10
"
```

### 移動先パスの確認

```bash
# 特定のカテゴリの全パスを確認
duckdb -c "
SELECT DISTINCT \"フォルダパス\"
FROM read_csv_auto('file_list-080_運営.csv')
WHERE \"フォルダパス\" LIKE '/080_運営/%'
ORDER BY \"フォルダパス\"
" | less
```

## 応用例

### 特定のカテゴリのみを移動

```bash
# 080_運営カテゴリのみを抽出
duckdb -json -c "
WITH folder_names AS (...)
SELECT f.folder_name, a.folder_path
FROM folder_names f
JOIN all_files a ON a.file_name = f.folder_name
WHERE a.folder_path LIKE '/080_運営/%'
" > /tmp/move_mapping_080.json
```

### ドライランモード（移動せずに確認のみ）

```bash
# mvコマンドの代わりにechoで確認
if echo "移動予定: $source_path → $target_path/"; then
  echo "✓ 移動確認: $folder_name"
fi
```

## 関連ドキュメント

- `domain/raw-data/google-drive/spread-sheet/GLOW/file_lists/README.md` - file_listsの説明
- `.claude/commands/prompts/マスターテーブル一覧調査方法.md` - DuckDBを使った調査方法

## 更新履歴

- 2026-02-10 v1.1: 移動元フォルダ構造の柔軟性を向上（tempフォルダ以外にも対応）、簡易版スクリプト追加
- 2026-02-10 v1.0: 初版作成（推しの子&年末年始施策の移動作業を元に作成）
