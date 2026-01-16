#!/bin/bash

set -e

echo "🚀 Geminiアップロード用ファイル統合スクリプト"

# 設定
OUTPUT_DIR="gemini/bundles"
INDEX_FILE="$OUTPUT_DIR/index.md"
MAX_LINES_PER_FILE=5000  # 1ファイルあたりの行数上限

# 出力ディレクトリを作成
mkdir -p "$OUTPUT_DIR"

# 既存の出力ファイルを削除
rm -f "$INDEX_FILE" "$OUTPUT_DIR"/content_*.md

echo "📁 ファイル一覧を取得中..."

# 対象ファイルを取得（.git と .DS_Store を除外）
FILES=$(find . -type f \
  -not -path './.git/*' \
  -not -name '.DS_Store' \
  -not -path "./dist/*" \
  -not -path "./gemini/bundles/*" \
  -not -path "./scripts/bundle-for-gemini.sh" \
  | sort)

FILE_COUNT=$(echo "$FILES" | wc -l | tr -d ' ')

echo "📝 $FILE_COUNT ファイルを処理します"
echo "📄 content_XXX.md を生成中..."

# 変数初期化
file_index=1
current_file_lines=0
file_num=0
declare -a file_metadata  # "filepath|bundle|linenum" 形式で保存

# 最初のファイルを作成
current_content_file="$OUTPUT_DIR/content_$(printf '%03d' $file_index).md"

# ヘッダーを書き込む
echo "# glow-brain-gemini 全ソースコード (Part $file_index)" > "$current_content_file"
echo "" >> "$current_content_file"
echo "生成日時: $(date '+%Y-%m-%d %H:%M:%S')" >> "$current_content_file"
echo "" >> "$current_content_file"
echo "---" >> "$current_content_file"
echo "" >> "$current_content_file"

current_file_lines=6

while IFS= read -r file; do
  file_num=$((file_num + 1))

  # 拡張子を取得
  ext="${file##*.}"
  if [[ "$file" != *.* ]]; then
    ext="txt"
  fi

  # このファイルを追加した場合の行数を計算
  file_line_count=0
  if [ -f "$file" ]; then
    file_line_count=$(wc -l < "$file" | tr -d ' ')
  fi

  # ヘッダー + 内容 + フッター = 4 + file_line_count + 4 = 8 + file_line_count
  lines_to_add=$((8 + file_line_count))

  # 追加すると上限を超える場合は新しいファイルを開始
  if [ $current_file_lines -gt 0 ] && [ $((current_file_lines + lines_to_add)) -gt $MAX_LINES_PER_FILE ]; then
    # 次のファイルに移行
    file_index=$((file_index + 1))
    current_content_file="$OUTPUT_DIR/content_$(printf '%03d' $file_index).md"

    # 新しいファイルのヘッダー
    echo "# glow-brain-gemini 全ソースコード (Part $file_index)" > "$current_content_file"
    echo "" >> "$current_content_file"
    echo "生成日時: $(date '+%Y-%m-%d %H:%M:%S')" >> "$current_content_file"
    echo "" >> "$current_content_file"
    echo "---" >> "$current_content_file"
    echo "" >> "$current_content_file"

    current_file_lines=6
  fi

  # メタデータを記録（現在の行番号を記録）
  bundle_name="content_$(printf '%03d' $file_index).md"
  file_metadata[$file_num]="$file|$bundle_name|$((current_file_lines + 1))"

  # ファイル内容を追加
  echo "<!-- FILE: $file -->" >> "$current_content_file"
  echo "## $file" >> "$current_content_file"
  echo "" >> "$current_content_file"
  echo "\`\`\`$ext" >> "$current_content_file"

  if [ -f "$file" ]; then
    cat "$file" >> "$current_content_file"
  fi

  echo "\`\`\`" >> "$current_content_file"
  echo "" >> "$current_content_file"
  echo "---" >> "$current_content_file"
  echo "" >> "$current_content_file"

  current_file_lines=$((current_file_lines + lines_to_add))

  # 進捗表示
  if [ $((file_num % 50)) -eq 0 ]; then
    echo "  処理済み: $file_num / $FILE_COUNT (現在: content_$(printf '%03d' $file_index).md)"
  fi
done <<< "$FILES"

echo "✅ $file_index 個のcontent_XXX.md ファイルを生成完了"

# index.md を生成
echo "📋 index.md を生成中..."

cat > "$INDEX_FILE" << EOF
# glow-brain-gemini ソースコード目次

## 概要
- 総ファイル数: $FILE_COUNT
- 生成日時: $(date '+%Y-%m-%d %H:%M:%S')
- 分割ファイル数: $file_index

## ファイル一覧

| No | ファイルパス | バンドル | 行番号 |
|----|-------------|---------|--------|
EOF

for i in "${!file_metadata[@]}"; do
  IFS='|' read -r filepath bundle linenum <<< "${file_metadata[$i]}"
  echo "| $i | $filepath | $bundle | L$linenum |" >> "$INDEX_FILE"
done

cat >> "$INDEX_FILE" << 'EOF'

## 使い方

1. `index.md` でファイル一覧を確認
2. 該当ファイルのバンドルと行番号を確認
3. 該当するバンドルファイルを開き、行番号にジャンプしてファイル内容を参照
EOF

echo "✅ index.md 完了"

# サイズ確認
echo ""
echo "📊 生成結果:"
echo "---"
ls -lh "$OUTPUT_DIR"
echo ""
du -sh "$OUTPUT_DIR"
echo ""
echo "✨ 完了！以下のファイルをGeminiにアップロードしてください:"
echo "  - $INDEX_FILE"
for ((i=1; i<=file_index; i++)); do
  echo "  - $OUTPUT_DIR/content_$(printf '%03d' $i).md"
done
