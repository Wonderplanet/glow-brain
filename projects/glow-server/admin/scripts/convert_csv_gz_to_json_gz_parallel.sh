#!/bin/bash

# 複数のCSV.gzからJSONL.gzに並列高速変換するスクリプト（GNU parallel + pigz + mlr使用）
# 使用法: convert_csv_gz_to_json_gz_parallel.sh <input_dir> <output_dir> [parallel_jobs]

set -euo pipefail

# ログ出力用
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" >&2
}

# 引数チェック
if [ $# -lt 2 ]; then
    log "エラー: 引数が不足しています"
    log "使用法: $0 <input_dir> <output_dir> [parallel_jobs]"
    exit 1
fi

INPUT_DIR="$1"
OUTPUT_DIR="$2"
PARALLEL_JOBS="${3:-$(nproc)}"  # デフォルトはCPUコア数

log "CSV.gz → JSONL.gz並列変換開始 (並列数: $PARALLEL_JOBS)"

# ディレクトリ存在チェック
if [ ! -d "$INPUT_DIR" ]; then
    log "エラー: 入力ディレクトリが見つかりません: $INPUT_DIR"
    exit 1
fi

if [ ! -d "$OUTPUT_DIR" ]; then
    mkdir -p "$OUTPUT_DIR"
fi

# 必要なコマンドの存在チェック
for cmd in parallel pigz mlr; do
    if ! command -v "$cmd" &> /dev/null; then
        log "エラー: ${cmd}コマンドが見つかりません"
        exit 1
    fi
done

# CSV.gzファイル一覧を取得
csv_files=("$INPUT_DIR"/*.csv.gz)
if [ ! -e "${csv_files[0]}" ]; then
    log "警告: CSV.gzファイルが見つかりません: $INPUT_DIR"
    exit 0
fi

log "処理対象ファイル数: ${#csv_files[@]}"

# 1ファイル変換用関数
convert_single_file() {
    local csv_file="$1"
    local output_dir="$2"

    local basename=$(basename "$csv_file" .csv.gz)
    local json_file="$output_dir/${basename}.json.gz"

    local start_time=$(date +%s)

    # pigz + mlr を使用してCSV.gz → JSONL.gz変換
    # json-stringifyで指定カラムを文字列化し、putで全フィールドの空配列[]を文字列"[]"に変換
    # json-stringify の変換対象のデータはそのままにするために、json-stringify 後に put を使用
    pigz -dc "$csv_file" \
        | sed 's/\\"/""/g' \
        | mlr --icsv --allow-ragged-csv-input --jvquoteall --ojsonl \
            json-stringify -f trigger_detail,discovered_enemies,party_status,received_reward then \
            put '
                for (k, v in $*) {
                    if (v == "[]") {
                        $[k] = "\"[]\"";
                    }
                }
            ' then cat \
        | pigz -c > "$json_file"

    local end_time=$(date +%s)
    local duration=$((end_time - start_time))

    if [ -f "$json_file" ]; then
        local file_size=$(stat -f%z "$json_file" 2>/dev/null || stat -c%s "$json_file" 2>/dev/null || echo "0")
        local file_size_mb=$(echo "scale=1; $file_size / 1024 / 1024" | bc -l 2>/dev/null || echo "不明")
        echo "[$(date +'%Y-%m-%d %H:%M:%S')] 完了: $(basename "$csv_file") → $(basename "$json_file") (${duration}秒, ${file_size_mb}MB)" >&2
    else
        echo "[$(date +'%Y-%m-%d %H:%M:%S')] エラー: $(basename "$csv_file") の変換に失敗" >&2
        return 1
    fi
}

# 関数をexportしてparallelで使用可能にする
export -f convert_single_file

start_time=$(date +%s)

# GNU parallelで並列実行
printf '%s\n' "${csv_files[@]}" | \
parallel -j "$PARALLEL_JOBS" convert_single_file {} "$OUTPUT_DIR"

end_time=$(date +%s)
total_duration=$((end_time - start_time))

# 結果確認
converted_count=$(find "$OUTPUT_DIR" -name "*.json.gz" | wc -l)
log "並列変換完了: ${converted_count}ファイル, 総時間: ${total_duration}秒"

if [ "$converted_count" -ne "${#csv_files[@]}" ]; then
    log "警告: 一部のファイル変換に失敗した可能性があります"
    exit 1
fi
