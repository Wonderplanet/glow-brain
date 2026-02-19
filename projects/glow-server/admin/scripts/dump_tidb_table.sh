#!/bin/bash

# TiDB dumpling実行スクリプト
# 単一テーブルをCSV.gz形式でダンプする

set -euo pipefail

# 使用方法
usage() {
    cat << EOF
使用方法: $0 [オプション]
  -h HOST         TiDBホスト (必須)
  -P PORT         TiDBポート (デフォルト: 4000)
  -u USER         TiDBユーザー (必須)
  -p PASSWORD     TiDBパスワード (必須)
  -d DATABASE     データベース名 (必須)
  -t TABLE        テーブル名 (必須)
  -o OUTPUT_DIR   出力ディレクトリ (必須)
  -w WHERE        WHERE条件 (オプション)
  -c CREDENTIALS  GCS認証ファイルパス (オプション)
  --help          このヘルプを表示

例:
  $0 -h localhost -P 4000 -u root -p password -d staging -t usr_items -o /tmp/dump

環境変数での指定も可能:
  TIDB_HOST, TIDB_PORT, TIDB_USER, TIDB_PASS, TIDB_DATABASE
EOF
}

# ログ出力用
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" >&2
}

# デフォルト値
TIDB_PORT="${TIDB_PORT:-4000}"
WHERE_CLAUSE=""
GCS_CREDENTIALS_FILE=""

# パラメータ解析
while [[ $# -gt 0 ]]; do
    case $1 in
        -h)
            TIDB_HOST="$2"
            shift 2
            ;;
        -P)
            TIDB_PORT="$2"
            shift 2
            ;;
        -u)
            TIDB_USER="$2"
            shift 2
            ;;
        -p)
            TIDB_PASS="$2"
            shift 2
            ;;
        -d)
            TIDB_DATABASE="$2"
            shift 2
            ;;
        -t)
            TABLE_NAME="$2"
            shift 2
            ;;
        -o)
            OUTPUT_DIR="$2"
            shift 2
            ;;
        -w)
            WHERE_CLAUSE="$2"
            shift 2
            ;;
        -c)
            GCS_CREDENTIALS_FILE="$2"
            shift 2
            ;;
        --help)
            usage
            exit 0
            ;;
        *)
            log "エラー: 不正なオプション: $1"
            usage
            exit 1
            ;;
    esac
done

# 必須パラメータチェック
if [[ -z "${TIDB_HOST:-}" || -z "${TIDB_USER:-}" || -z "${TIDB_PASS:-}" || -z "${TIDB_DATABASE:-}" || -z "${TABLE_NAME:-}" || -z "${OUTPUT_DIR:-}" ]]; then
    log "エラー: 必須パラメータが不足しています"
    usage
    exit 1
fi

# dumplingがインストールされているかチェック
if ! command -v dumpling &> /dev/null; then
    log "エラー: dumpling がインストールされていません"
    log "先に install_dumpling.sh を実行してください"
    exit 1
fi

# 出力ディレクトリを作成
mkdir -p "$OUTPUT_DIR"

log "dumpling実行開始: テーブル=$TABLE_NAME"
log "接続情報: ${TIDB_HOST}:${TIDB_PORT}/${TIDB_DATABASE}"
log "出力先: $OUTPUT_DIR"

# dumplingコマンドを構築・実行
DUMPLING_CMD=(
    dumpling
    -h "$TIDB_HOST"
    -P "$TIDB_PORT"
    -u "$TIDB_USER"
    -p"$TIDB_PASS"
    -B "$TIDB_DATABASE"
    -T "${TIDB_DATABASE}.${TABLE_NAME}"
    -o "$OUTPUT_DIR"
    # --no-header
    --filetype csv
    --compress gzip
    -t 8
    -F 256MiB
    --output-filename-template "{{fn .DB}}.{{fn .Table}}.part{{printf \"%09s\" .Index}}"
)

# WHERE条件がある場合は追加
if [[ -n "$WHERE_CLAUSE" ]]; then
    DUMPLING_CMD+=(--where "$WHERE_CLAUSE")
    log "WHERE条件: $WHERE_CLAUSE"
fi

# GCS認証ファイルがある場合は追加
if [[ -n "$GCS_CREDENTIALS_FILE" ]]; then
    DUMPLING_CMD+=(--gcs.credentials-file "$GCS_CREDENTIALS_FILE")
    log "GCS認証ファイル: $GCS_CREDENTIALS_FILE"
fi

# dumplingコマンドを実行
log "実行コマンド: ${DUMPLING_CMD[*]}"
if "${DUMPLING_CMD[@]}"; then
    log "dumpling実行成功: テーブル=$TABLE_NAME"

    # 生成されたファイル一覧を表示
    find "$OUTPUT_DIR" -name "*.csv.gz" -type f | while read -r file; do
        log "生成ファイル: $(basename "$file") ($(du -h "$file" | cut -f1))"
    done
else
    log "エラー: dumpling実行失敗: テーブル=$TABLE_NAME"
    exit 1
fi
