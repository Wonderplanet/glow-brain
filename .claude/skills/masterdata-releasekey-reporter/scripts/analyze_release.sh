#!/bin/bash

set -euo pipefail

# リリースキー分析スクリプト
# 使用方法: ./analyze_release.sh <RELEASE_KEY>

if [ $# -eq 0 ]; then
    echo "エラー: リリースキーを指定してください"
    echo "使用方法: $0 <RELEASE_KEY>"
    echo "例: $0 202512020"
    exit 1
fi

RELEASE_KEY="$1"
MASTERDATA_DIR="projects/glow-masterdata"
OUTPUT_DIR="マスタデータ/リリース/${RELEASE_KEY}"
OUTPUT_RAW="${OUTPUT_DIR}/release_${RELEASE_KEY}_raw_data.txt"
OUTPUT_REPORT="${OUTPUT_DIR}/release_${RELEASE_KEY}_report.md"

# マスタデータディレクトリの存在確認
if [ ! -d "$MASTERDATA_DIR" ]; then
    echo "エラー: マスタデータディレクトリが見つかりません: $MASTERDATA_DIR"
    echo "glow-brainリポジトリのルートディレクトリで実行してください"
    exit 1
fi

# リリースキーを含むファイルを検索
files=$(grep -l "$RELEASE_KEY" "$MASTERDATA_DIR"/*.csv 2>/dev/null | sort || true)

if [ -z "$files" ]; then
    echo "エラー: リリースキー ${RELEASE_KEY} を含むデータが見つかりませんでした"
    exit 1
fi

# 出力ディレクトリを作成
mkdir -p "$OUTPUT_DIR"

echo "=== リリースキー ${RELEASE_KEY} マスタデータ抽出 ==="
echo ""

# 初期化
> "$OUTPUT_RAW"

file_count=0
total_rows=0
declare -A table_stats

# rawデータ抽出
for file in $files; do
    filename=$(basename "$file")
    table_name="${filename%.csv}"

    # ヘッダー行を取得
    header=$(head -n 1 "$file")

    # リリースキーを含む行を抽出
    data_lines=$(grep "$RELEASE_KEY" "$file" | wc -l | tr -d ' ')

    if [ "$data_lines" -gt 0 ]; then
        file_count=$((file_count + 1))
        total_rows=$((total_rows + data_lines))
        table_stats["$table_name"]=$data_lines

        # rawデータファイルに追記
        echo "######## $table_name ########" >> "$OUTPUT_RAW"
        echo "$header" >> "$OUTPUT_RAW"
        grep "$RELEASE_KEY" "$file" >> "$OUTPUT_RAW"
        echo "" >> "$OUTPUT_RAW"
    fi
done

echo "抽出完了: ${file_count}テーブル、${total_rows}行"
echo ""

# レポート生成
cat > "$OUTPUT_REPORT" << EOF
# リリースキー ${RELEASE_KEY} マスタデータレポート

## 概要

リリースキー${RELEASE_KEY}のマスタデータ投入内容を分析したレポートです。

- **リリースキー**: ${RELEASE_KEY}
- **対象テーブル数**: ${file_count}テーブル
- **総データ行数**: ${total_rows}行
- **抽出日時**: $(date +%Y-%m-%d)

---

## データ投入サマリー

### テーブル別データ数

| テーブル名 | 行数 |
|-----------|------|
EOF

# テーブル統計をソート済みで出力
for table_name in $(printf '%s\n' "${!table_stats[@]}" | sort); do
    echo "| $table_name | ${table_stats[$table_name]}行 |" >> "$OUTPUT_REPORT"
done

cat >> "$OUTPUT_REPORT" << EOF

---

## カテゴリ別集計

EOF

# カテゴリ別集計
mst_count=0
opr_count=0
mst_rows=0
opr_rows=0

for table_name in "${!table_stats[@]}"; do
    rows=${table_stats[$table_name]}
    if [[ $table_name == Mst* ]]; then
        mst_count=$((mst_count + 1))
        mst_rows=$((mst_rows + rows))
    elif [[ $table_name == Opr* ]]; then
        opr_count=$((opr_count + 1))
        opr_rows=$((opr_rows + rows))
    fi
done

cat >> "$OUTPUT_REPORT" << EOF
### マスターデータ (Mst)
- **テーブル数**: ${mst_count}
- **総行数**: ${mst_rows}行

### 運営データ (Opr)
- **テーブル数**: ${opr_count}
- **総行数**: ${opr_rows}行

---

## 主要テーブルの内容

EOF

# 主要テーブルの詳細（行数が多い順にトップ10）
echo "### データ投入量トップ10" >> "$OUTPUT_REPORT"
echo "" >> "$OUTPUT_REPORT"

for table_name in $(for key in "${!table_stats[@]}"; do
    echo "${table_stats[$key]} $key"
done | sort -rn | head -10 | awk '{print $2}'); do
    rows=${table_stats[$table_name]}
    echo "#### $table_name" >> "$OUTPUT_REPORT"
    echo "- **行数**: ${rows}行" >> "$OUTPUT_REPORT"
    echo "" >> "$OUTPUT_REPORT"
done

cat >> "$OUTPUT_REPORT" << EOF

---

## ファイル情報

- **rawデータ**: \`release_${RELEASE_KEY}_raw_data.txt\`
- **レポート**: \`release_${RELEASE_KEY}_report.md\`

---

**レポート生成日時**: $(date +%Y-%m-%d\ %H:%M:%S)
**抽出元**: glow-masterdata リポジトリ
EOF

echo "レポート生成完了:"
echo "  - rawデータ: $OUTPUT_RAW"
echo "  - レポート: $OUTPUT_REPORT"
