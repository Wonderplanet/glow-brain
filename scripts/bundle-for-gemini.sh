#!/bin/bash

set -e

echo "🚀 Geminiアップロード用ファイル統合スクリプト"

# 出力ディレクトリの設定
OUTPUT_DIR="gemini/bundles"
INDEX_FILE="$OUTPUT_DIR/index.md"
CONTENT_FILE="$OUTPUT_DIR/content.md"

# 出力ディレクトリを作成
mkdir -p "$OUTPUT_DIR"

# 既存ファイルを削除
rm -f "$INDEX_FILE" "$CONTENT_FILE"

echo "📁 ファイル一覧を取得中..."

# 対象ファイルを取得（.git と .DS_Store を除外）
FILES=$(find . -type f \
  -not -path './.git/*' \
  -not -name '.DS_Store' \
  -not -path "./dist/*" \
  | sort)

FILE_COUNT=$(echo "$FILES" | wc -l | tr -d ' ')

echo "📝 $FILE_COUNT ファイルを処理します"

# content.md を生成
echo "📄 content.md を生成中..."

current_line=1
declare -a file_lines

echo "# glow-brain-gemini 全ソースコード" > "$CONTENT_FILE"
echo "" >> "$CONTENT_FILE"
echo "生成日時: $(date '+%Y-%m-%d %H:%M:%S')" >> "$CONTENT_FILE"
echo "総ファイル数: $FILE_COUNT" >> "$CONTENT_FILE"
echo "" >> "$CONTENT_FILE"
echo "---" >> "$CONTENT_FILE"
echo "" >> "$CONTENT_FILE"

current_line=8

file_num=0
for file in $FILES; do
  file_num=$((file_num + 1))

  # 拡張子を取得
  ext="${file##*.}"
  if [[ "$file" == *.* ]]; then
    ext="${file##*.}"
  else
    ext="txt"
  fi

  # ファイル開始行を記録
  file_lines[$file_num]="$file|$current_line"

  # ファイルヘッダー
  echo "<!-- FILE: $file -->" >> "$CONTENT_FILE"
  echo "## $file" >> "$CONTENT_FILE"
  echo "" >> "$CONTENT_FILE"
  echo "\`\`\`$ext" >> "$CONTENT_FILE"

  current_line=$((current_line + 4))

  # ファイル内容を追加
  if [ -f "$file" ]; then
    cat "$file" >> "$CONTENT_FILE"
    file_line_count=$(wc -l < "$file" | tr -d ' ')
    current_line=$((current_line + file_line_count))
  fi

  echo "\`\`\`" >> "$CONTENT_FILE"
  echo "" >> "$CONTENT_FILE"
  echo "---" >> "$CONTENT_FILE"
  echo "" >> "$CONTENT_FILE"

  current_line=$((current_line + 4))

  # 進捗表示
  if [ $((file_num % 50)) -eq 0 ]; then
    echo "  処理済み: $file_num / $FILE_COUNT"
  fi
done

echo "✅ content.md 完了"

# index.md を生成
echo "📋 index.md を生成中..."

cat > "$INDEX_FILE" << 'EOF'
# glow-brain-gemini ソースコード目次

## 概要
EOF

echo "- 総ファイル数: $FILE_COUNT" >> "$INDEX_FILE"
echo "- 生成日時: $(date '+%Y-%m-%d %H:%M:%S')" >> "$INDEX_FILE"
echo "" >> "$INDEX_FILE"

echo "## ファイル一覧" >> "$INDEX_FILE"
echo "" >> "$INDEX_FILE"
echo "| No | ファイルパス | content.md内の行番号 |" >> "$INDEX_FILE"
echo "|----|-------------|---------------------|" >> "$INDEX_FILE"

for i in "${!file_lines[@]}"; do
  IFS='|' read -r filepath linenum <<< "${file_lines[$i]}"
  echo "| $i | $filepath | L$linenum |" >> "$INDEX_FILE"
done

echo "" >> "$INDEX_FILE"
echo "## 使い方" >> "$INDEX_FILE"
echo "" >> "$INDEX_FILE"
echo "1. \`index.md\` でファイル一覧を確認" >> "$INDEX_FILE"
echo "2. 該当ファイルの行番号を確認" >> "$INDEX_FILE"
echo "3. \`content.md\` の該当行番号にジャンプしてファイル内容を参照" >> "$INDEX_FILE"

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
echo "  - $CONTENT_FILE"
