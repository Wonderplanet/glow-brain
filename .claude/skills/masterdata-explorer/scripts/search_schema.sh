#!/bin/bash

# search_schema.sh
# GLOWマスターデータのDBスキーマを検索するヘルパースクリプト

set -e

SCHEMA_FILE="projects/glow-server/api/database/schema/exports/master_tables_schema.json"

# 使い方を表示
usage() {
  cat <<EOF
Usage: $0 <command> [options]

Commands:
  tables [pattern]              - テーブル名を検索（部分一致、省略時は全テーブル）
  columns <table>               - 指定テーブルの全カラム名を表示
  search-column <column_name>   - 指定カラム名を持つ全テーブルを検索
  enum <table> <column>         - 指定カラムのenum値を表示
  foreign-keys <table>          - 指定テーブルの全外部キーを表示
  references <table>            - 指定テーブルを参照している全テーブルを検索
  nullable <table>              - 指定テーブルのNULL許可カラムを表示
  not-null <table>              - 指定テーブルのNOT NULLカラムを表示

Examples:
  $0 tables event               # 「event」を含むテーブルを検索
  $0 columns mst_units          # mst_unitsの全カラムを表示
  $0 search-column reward_group_id  # reward_group_idを持つテーブルを検索
  $0 enum opr_gachas gacha_type     # gacha_typeのenum値を表示
  $0 foreign-keys mst_stages    # mst_stagesの外部キー一覧を表示
  $0 references mst_reward_groups  # mst_reward_groupsを参照しているテーブルを検索
  $0 nullable mst_stages        # mst_stagesのNULL許可カラムを表示
  $0 not-null mst_stages        # mst_stagesのNOT NULLカラムを表示

EOF
  exit 1
}

# スキーマファイルの存在確認
check_schema_file() {
  if [ ! -f "$SCHEMA_FILE" ]; then
    echo "Error: Schema file not found: $SCHEMA_FILE"
    exit 1
  fi
}

# jqがインストールされているか確認
check_jq() {
  if ! command -v jq &> /dev/null; then
    echo "Error: jq is not installed. Please install it first:"
    echo "  brew install jq"
    exit 1
  fi
}

# テーブル名検索
search_tables() {
  local pattern="$1"

  if [ -z "$pattern" ]; then
    # パターン指定なしの場合は全テーブル
    jq -r '.databases.mst.tables | keys | .[]' "$SCHEMA_FILE"
  else
    # パターン指定ありの場合は部分一致検索
    jq -r ".databases.mst.tables | keys | map(select(test(\"$pattern\"; \"i\"))) | .[]" "$SCHEMA_FILE"
  fi
}

# 指定テーブルの全カラム名を表示
list_columns() {
  local table="$1"

  if [ -z "$table" ]; then
    echo "Error: Table name is required"
    usage
  fi

  jq -r ".databases.mst.tables.${table}.columns | keys | .[]" "$SCHEMA_FILE" 2>/dev/null || {
    echo "Error: Table '$table' not found"
    exit 1
  }
}

# 指定カラム名を持つ全テーブルを検索
search_column() {
  local column="$1"

  if [ -z "$column" ]; then
    echo "Error: Column name is required"
    usage
  fi

  jq -r ".databases.mst.tables | to_entries | map(select(.value.columns | has(\"$column\"))) | map(.key) | .[]" "$SCHEMA_FILE"
}

# 指定カラムのenum値を表示
show_enum() {
  local table="$1"
  local column="$2"

  if [ -z "$table" ] || [ -z "$column" ]; then
    echo "Error: Table name and column name are required"
    usage
  fi

  jq -r ".databases.mst.tables.${table}.columns.${column}.enum | .[]" "$SCHEMA_FILE" 2>/dev/null || {
    echo "Error: Table '$table' or column '$column' not found, or column is not an enum type"
    exit 1
  }
}

# 指定テーブルの全外部キーを表示
list_foreign_keys() {
  local table="$1"

  if [ -z "$table" ]; then
    echo "Error: Table name is required"
    usage
  fi

  jq -r ".databases.mst.tables.${table}.columns | to_entries | map(select(.value.foreign_key != null)) | map(\"\(.key) -> \(.value.foreign_key)\") | .[]" "$SCHEMA_FILE" 2>/dev/null || {
    echo "Error: Table '$table' not found"
    exit 1
  }
}

# 指定テーブルを参照している全テーブルを検索
find_references() {
  local table="$1"

  if [ -z "$table" ]; then
    echo "Error: Table name is required"
    usage
  fi

  jq -r ".databases.mst.tables | to_entries | map({table: .key, columns: (.value.columns | to_entries | map(select(.value.foreign_key != null and (.value.foreign_key | test(\"$table\")))) | map(.key))}) | select(.columns | length > 0) | \"\(.table): \(.columns | join(\", \"))\"" "$SCHEMA_FILE"
}

# 指定テーブルのNULL許可カラムを表示
list_nullable() {
  local table="$1"

  if [ -z "$table" ]; then
    echo "Error: Table name is required"
    usage
  fi

  jq -r ".databases.mst.tables.${table}.columns | to_entries | map(select(.value.nullable == true)) | map(.key) | .[]" "$SCHEMA_FILE" 2>/dev/null || {
    echo "Error: Table '$table' not found"
    exit 1
  }
}

# 指定テーブルのNOT NULLカラムを表示
list_not_null() {
  local table="$1"

  if [ -z "$table" ]; then
    echo "Error: Table name is required"
    usage
  fi

  jq -r ".databases.mst.tables.${table}.columns | to_entries | map(select(.value.nullable == false)) | map(.key) | .[]" "$SCHEMA_FILE" 2>/dev/null || {
    echo "Error: Table '$table' not found"
    exit 1
  }
}

# メイン処理
main() {
  # 引数チェック
  if [ $# -eq 0 ]; then
    usage
  fi

  # jqインストール確認
  check_jq

  # スキーマファイル存在確認
  check_schema_file

  # コマンド実行
  local command="$1"
  shift

  case "$command" in
    tables)
      search_tables "$@"
      ;;
    columns)
      list_columns "$@"
      ;;
    search-column)
      search_column "$@"
      ;;
    enum)
      show_enum "$@"
      ;;
    foreign-keys)
      list_foreign_keys "$@"
      ;;
    references)
      find_references "$@"
      ;;
    nullable)
      list_nullable "$@"
      ;;
    not-null)
      list_not_null "$@"
      ;;
    help|--help|-h)
      usage
      ;;
    *)
      echo "Error: Unknown command: $command"
      echo
      usage
      ;;
  esac
}

# スクリプト実行
main "$@"
