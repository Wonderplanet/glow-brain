#!/bin/bash

parse_options() {
  # 許可されたデータベース名、環境名、コマンド名をローカル変数として定義
  local ALLOWED_DATABASES=("tidb" "mng" "admin" "mst")
  local ALLOWED_ENVS=("review" "prod")
  local ALLOWED_COMMANDS=("migrate" "status")

  for i in "$@"; do
    case $i in
      --database=*)
        DATABASE_NAME="${i#*=}"
        ;;
      --command=*)
        COMMAND_NAME="${i#*=}"
        ;;
      *)
        show_help
        ;;
    esac
  done

  # 必須パラメータのチェック
  if [ -z "$DATABASE_NAME" ] || [ -z "$COMMAND_NAME" ]; then
    echo "Error: --database と --command オプションは必須です。" >&2
    show_help
  fi

  # データベース名が許可リストに含まれているかチェック
  if [[ ! " ${ALLOWED_DATABASES[*]} " =~ " ${DATABASE_NAME} " ]]; then
    echo "Error: '$DATABASE_NAME'は許可されていないデータベース名です。" >&2
    show_help
  fi

  # 環境名が許可リストに含まれているかチェック
  if [[ ! " ${ALLOWED_ENVS[*]} " =~ " ${MIGRATION_ENV} " ]]; then
    echo "Error: '$MIGRATION_ENV'は許可されていない環境名です。" >&2
    show_help
  fi

  # コマンド名が許可リストに含まれているかチェック
  if [[ ! " ${ALLOWED_COMMANDS[*]} " =~ " ${COMMAND_NAME} " ]]; then
    echo "Error: '$COMMAND_NAME' は許可されていないコマンドです。" >&2
    show_help
  fi
}

run_migration() {
  # 実行コマンドを決定
  local BASE_COMMAND="php artisan migrate"
  if [ "$COMMAND_NAME" = "status" ]; then
    BASE_COMMAND="php artisan migrate:status"
  fi

  # このスクリプトが存在するディレクトリに移動
  cd "$(dirname "$0")"

  # データベースごとのディレクトリに移動
  if [ "$DATABASE_NAME" == "admin" ]; then
    cd ../../admin
  else
    cd ../../api
  fi

  echo "マイグレーションを開始します..."
  echo "ターゲットデータベース: $DATABASE_NAME"
  echo "ターゲット環境: $MIGRATION_ENV"
  local PATH_SUFFIX=""
  if [[ "$DATABASE_NAME" == "mng" || "$DATABASE_NAME" == "mst" ]]; then
    PATH_SUFFIX="/${DATABASE_NAME}"
  fi

  echo "コマンド: ${BASE_COMMAND} --database=\"${MIGRATION_ENV}_${DATABASE_NAME}\" --path=database/migrations${PATH_SUFFIX}"
  $BASE_COMMAND --database="${MIGRATION_ENV}_${DATABASE_NAME}" --path=database/migrations${PATH_SUFFIX}

  echo "完了しました。"
}

show_help() {
  echo "Usage: $0 --database=<database_name> --command=<command_name>"
  echo "  --database=<database_name>  マイグレーションを実行するデータベース名 (必須) tidb, mng, admin, mst のいずれか"
  echo "  --command=<command_name>    実行するコマンド (必須) migrate, status のいずれか"
  exit 1
}
