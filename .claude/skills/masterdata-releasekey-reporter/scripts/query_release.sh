#!/bin/bash

set -euo pipefail

# query_release.sh - DuckDBでリリースデータをクエリ
#
# Usage:
#   query_release.sh <RELEASE_KEY> <COMMAND> [OPTIONS]
#
# Commands:
#   table <TABLE_NAME>           - 指定テーブルの全データ取得
#   category <CATEGORY>          - カテゴリ別テーブル一覧（Mst/Opr/I18n）
#   search <PATTERN>             - ID/asset_keyパターン検索
#   sql <QUERY>                  - 任意SQLの実行
#   stats                        - 統計情報の表示

if [ $# -lt 2 ]; then
    echo "エラー: 引数が不足しています"
    echo ""
    echo "使用方法: $0 <RELEASE_KEY> <COMMAND> [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  table <TABLE_NAME>    - 指定テーブルの全データ取得"
    echo "  category <CATEGORY>   - カテゴリ別テーブル一覧（Mst/Opr/I18n）"
    echo "  search <PATTERN>      - ID/asset_keyパターン検索"
    echo "  sql <QUERY>           - 任意SQLの実行"
    echo "  stats                 - 統計情報の表示"
    echo ""
    echo "例:"
    echo "  $0 202512020 table MstEvent"
    echo "  $0 202512020 category Mst"
    echo "  $0 202512020 search quest_raid"
    echo "  $0 202512020 stats"
    exit 1
fi

RELEASE_KEY="$1"
COMMAND="$2"
TABLES_DIR="domain/raw-data/masterdata/released/${RELEASE_KEY}/tables"
STATS_DIR="domain/raw-data/masterdata/released/${RELEASE_KEY}/stats"
DUCKDB_INIT=".claude/skills/masterdata-releasekey-reporter/.duckdbrc"

# DuckDBの存在確認
if ! command -v duckdb &> /dev/null; then
    echo "エラー: duckdbコマンドが見つかりません。インストールしてください。"
    echo "  macOS: brew install duckdb"
    echo "  Linux: https://duckdb.org/docs/installation/"
    exit 1
fi

# ディレクトリの存在確認
if [ ! -d "$TABLES_DIR" ]; then
    echo "エラー: テーブルディレクトリが見つかりません: $TABLES_DIR"
    echo "先に extract_release_data.sh を実行してください"
    exit 1
fi

case "$COMMAND" in
    table)
        if [ $# -lt 3 ]; then
            echo "エラー: テーブル名を指定してください"
            echo "使用方法: $0 $RELEASE_KEY table <TABLE_NAME>"
            exit 1
        fi
        TABLE_NAME="$3"
        TABLE_FILE="${TABLES_DIR}/${TABLE_NAME}.csv"

        if [ ! -f "$TABLE_FILE" ]; then
            echo "エラー: テーブルファイルが見つかりません: $TABLE_FILE"
            exit 1
        fi

        duckdb -c "SELECT * FROM read_csv('${TABLE_FILE}', AUTO_DETECT=TRUE, nullstr='__NULL__');"
        ;;

    category)
        if [ $# -lt 3 ]; then
            echo "エラー: カテゴリを指定してください（Mst/Opr/I18n）"
            echo "使用方法: $0 $RELEASE_KEY category <CATEGORY>"
            exit 1
        fi
        CATEGORY="$3"

        echo "=== カテゴリ: $CATEGORY のテーブル一覧 ==="
        ls "${TABLES_DIR}/" | grep "^${CATEGORY}" | sed 's/\.csv$//' || echo "該当テーブルなし"
        ;;

    search)
        if [ $# -lt 3 ]; then
            echo "エラー: 検索パターンを指定してください"
            echo "使用方法: $0 $RELEASE_KEY search <PATTERN>"
            exit 1
        fi
        PATTERN="$3"

        echo "=== パターン '$PATTERN' で検索中 ==="

        # 全テーブルから検索（エラーは無視）
        for csv in "${TABLES_DIR}"/*.csv; do
            table_name=$(basename "$csv" .csv)

            # id カラムと asset_key カラムで検索
            result=$(duckdb -csv -noheader -c "
                SELECT '${table_name}' as table_name, *
                FROM read_csv('$csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
                WHERE id LIKE '%${PATTERN}%'
                   OR (TRY_CAST(asset_key AS VARCHAR) IS NOT NULL AND asset_key LIKE '%${PATTERN}%')
                LIMIT 5;
            " 2>/dev/null || true)

            if [ -n "$result" ]; then
                echo ""
                echo "--- $table_name ---"
                echo "$result"
            fi
        done
        ;;

    sql)
        if [ $# -lt 3 ]; then
            echo "エラー: SQLクエリを指定してください"
            echo "使用方法: $0 $RELEASE_KEY sql <QUERY>"
            exit 1
        fi
        SQL="${@:3}"

        duckdb -c "$SQL"
        ;;

    stats)
        if [ ! -f "${STATS_DIR}/summary.json" ]; then
            echo "エラー: 統計ファイルが見つかりません"
            exit 1
        fi

        echo "=== リリースキー $RELEASE_KEY 統計情報 ==="
        cat "${STATS_DIR}/summary.json" | jq .
        ;;

    *)
        echo "エラー: 不明なコマンド: $COMMAND"
        echo ""
        echo "使用可能なコマンド: table, category, search, sql, stats"
        exit 1
        ;;
esac
