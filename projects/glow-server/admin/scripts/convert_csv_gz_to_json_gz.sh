#!/bin/bash

# CSV.gzからJSONL.gzに高速変換するスクリプト（pigz + mlr使用）
# 使用法: convert_csv_gz_to_json_gz.sh <csv_gz_file> <json_gz_file>

set -euo pipefail

# ログ出力用
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" >&2
}

# 引数チェック
if [ $# -lt 2 ]; then
    log "エラー: 引数が不足しています"
    log "使用法: $0 <csv_gz_file> <json_gz_file>"
    exit 1
fi

CSV_GZ_FILE="$1"
JSON_GZ_FILE="$2"

# 基本情報のみログ出力
log "CSV.gz → JSONL.gz変換開始"

# ファイル存在チェック
if [ ! -f "$CSV_GZ_FILE" ]; then
    log "エラー: CSV.gzファイルが見つかりません: $CSV_GZ_FILE"
    exit 1
fi

# 必要なコマンドの存在チェック
if ! command -v pigz &> /dev/null; then
    log "エラー: pigzコマンドが見つかりません"
    exit 1
fi

if ! command -v mlr &> /dev/null; then
    log "エラー: mlrコマンドが見つかりません"
    exit 1
fi

start_time=$(date +%s)

# pigz + mlr を使用してCSV.gz → JSONL.gz変換
pigz -dc "$CSV_GZ_FILE" | sed 's/\\"/""/g' | mlr --icsv  --allow-ragged-csv-input --jvquoteall --ojsonl json-stringify -f trigger_detail,discovered_enemies,party_status,received_reward then cat | pigz -c > "$JSON_GZ_FILE"

end_time=$(date +%s)
duration=$((end_time - start_time))

# 結果確認
if [ -f "$JSON_GZ_FILE" ]; then
    file_size_output=$(stat -f%z "$JSON_GZ_FILE" 2>/dev/null || stat -c%s "$JSON_GZ_FILE" 2>/dev/null || echo "不明")
    log "変換完了: ${duration}秒, サイズ: $(echo "scale=1; $file_size_output / 1024 / 1024" | bc -l 2>/dev/null || echo "不明")MB"
else
    log "エラー: JSONL.gzファイルの作成に失敗しました"
    exit 1
fi
