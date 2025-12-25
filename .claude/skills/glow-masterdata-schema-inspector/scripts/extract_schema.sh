#!/bin/bash
# スキーマJSONからテーブル定義を抽出するスクリプト
# 引数: テーブル名
# 出力: カラム定義のJSON（整形済み）

set -euo pipefail

if [ $# -eq 0 ]; then
    echo "Usage: $0 <table_name>" >&2
    echo "Example: $0 opr_gachas" >&2
    exit 1
fi

TABLE_NAME="$1"

# プロジェクトルートからの相対パス
MASTER_SCHEMA="projects/glow-server/api/database/schema/exports/master_tables_schema.json"
USER_SCHEMA="projects/glow-server/api/database/schema/exports/user_tables_schema.json"

# マスタテーブルかユーザーテーブルかを判定
if [[ "$TABLE_NAME" =~ ^(mst_|opr_) ]]; then
    SCHEMA_FILE="$MASTER_SCHEMA"
    DB_NAME="mst"
else
    SCHEMA_FILE="$USER_SCHEMA"
    DB_NAME="usr"
fi

if [ ! -f "$SCHEMA_FILE" ]; then
    echo "Error: Schema file not found: $SCHEMA_FILE" >&2
    exit 1
fi

# テーブル定義を取得
TABLE_DEF=$(jq -r ".databases.$DB_NAME.tables.\"$TABLE_NAME\"" "$SCHEMA_FILE")

if [ "$TABLE_DEF" == "null" ]; then
    echo "Error: Table '$TABLE_NAME' not found in schema" >&2
    exit 1
fi

# 整形して出力
echo "$TABLE_DEF" | jq '.'
