#!/bin/bash

# QA環境データレイク転送テストスクリプト
# 使用方法: ./tools/datalake/qa-datalake-test-transfer.sh
#
# 処理の流れ:
#   1. 前提条件チェック（aws, gsutil, jqコマンドの確認）
#   2. 対象テーブル名の入力（対象日付は前日固定）
#   3. ステップ1: QA環境ECSでデータレイク転送を実行（前日データ）
#   4. ステップ2: GCSから構造変更テーブルのデータをダウンロード
#   5. ステップ3: ダウンロードしたファイルの内容を検証
#   6. ステップ4: テーブル定義書をECSで生成してS3にアップロード
#   7. ステップ5: 先方へのSlack報告用テンプレートを生成

set -e

# スクリプトのディレクトリを取得
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# プロジェクトルートディレクトリを取得（tools/datalakeから2階層上）
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

# 色付き出力用
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ログ出力用関数
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 設定
AWS_PROFILE="glow-qa"
GCP_ACCOUNT="jump-plus@bandainamco-dmp-pj-dev.iam.gserviceaccount.com"
CLUSTER_NAME="glow-qa-cluster"
SERVICE_NAME="glow-qa-admin-service"
CONTAINER_NAME="php-admin"
DOWNLOAD_DIR="${PROJECT_ROOT}/tools/datalake/downloads"

# GCP認証情報を環境変数で指定（gsutil/gcloudコマンドで使用される）
export CLOUDSDK_CORE_ACCOUNT="${GCP_ACCOUNT}"

# GCSバケットパス
GCS_MYSQL_BASE="gs://bandainamco-dmp-pj-dev__jump_plus__masters/mysql__qa_mst-full-qa"
GCS_TIDB_FULL_BASE="gs://bandainamco-dmp-pj-dev__jump_plus__masters/tidb__qa-full-qa"
GCS_TIDB_DIFF_BASE="gs://bandainamco-dmp-pj-dev__jump_plus__masters/tidb__qa-diff-qa"

# 検証サマリ用の配列
declare -a TYPE_WARNING_LIST=()
declare -a TYPE_WARNING_DETAILS=()
declare -a DATA_ISSUE_TABLES=()
declare -a DOWNLOAD_FAILED_TABLES=()
declare -a MISSING_FILE_TABLES=()

# 関数: 必要なコマンドの確認
check_requirements() {
    log_info "必要なコマンドを確認しています..."

    local missing_commands=()

    if ! command -v aws &> /dev/null; then
        missing_commands+=("aws")
    fi

    if ! command -v gcloud &> /dev/null; then
        missing_commands+=("gcloud")
    fi

    if ! command -v gsutil &> /dev/null; then
        missing_commands+=("gsutil")
    fi

    if ! command -v jq &> /dev/null; then
        missing_commands+=("jq")
    fi

    if [ ${#missing_commands[@]} -ne 0 ]; then
        log_error "以下のコマンドがインストールされていません: ${missing_commands[*]}"
        exit 1
    fi

    log_success "必要なコマンドが揃っています"

    # GCPアカウントの確認
    log_info "GCPアカウントを確認しています..."
    if gcloud auth list --filter="account:${GCP_ACCOUNT}" --format="value(account)" 2>/dev/null | grep -q "${GCP_ACCOUNT}"; then
        log_success "GCPアカウント: ${GCP_ACCOUNT} (環境変数で指定済み)"
    else
        log_warning "GCPアカウント '${GCP_ACCOUNT}' が認証されていません"
        log_warning "認証済みアカウント:"
        gcloud auth list
        log_error "gcloud auth activate-service-account または gcloud auth login で認証してください"
        exit 1
    fi
}

# 関数: 対象日付設定（前日固定）
set_target_date() {
    # app:datalake-first-transfer-commandは前日のデータを転送するため、前日固定
    TARGET_DATE=$(date -v-1d '+%Y/%m/%d' 2>/dev/null || date -d 'yesterday' '+%Y/%m/%d')
    TARGET_DATE_COMPACT=$(date -v-1d '+%Y%m%d' 2>/dev/null || date -d 'yesterday' '+%Y%m%d')

    log_info "対象日付: ${TARGET_DATE} (${TARGET_DATE_COMPACT}) ※前日固定"
}

# 関数: テーブル名入力
input_table_names() {
    log_info "構造変更があったテーブル名を入力してください（スペース区切りで複数可）"
    echo -n "テーブル名: "
    read -r input_tables

    if [ -z "$input_tables" ]; then
        log_error "テーブル名が入力されていません"
        exit 1
    fi

    # 配列に変換
    IFS=' ' read -r -a CHANGED_TABLES <<< "$input_tables"
    log_info "対象テーブル: ${CHANGED_TABLES[*]}"
}

# 関数: テーブルのDB種別を判定
get_db_type() {
    local table_name=$1

    # テーブル名のプレフィックスで判定
    if [[ $table_name == mst_* ]] || [[ $table_name == mng_* ]] || [[ $table_name == opr_* ]]; then
        echo "mysql"
    elif [[ $table_name == usr_* ]]; then
        echo "tidb-full"
    elif [[ $table_name == log_* ]] || [[ $table_name == sys_* ]]; then
        echo "tidb-diff"
    else
        echo "unknown"
    fi
}

# 関数: ステップ1 - ECS転送実行
step1_ecs_transfer() {
    log_info "=========================================="
    log_info "ステップ1: QA環境でテスト転送実行"
    log_info "=========================================="

    log_info "ECSタスクARNを取得中..."
    TASK_ARN=$(aws ecs list-tasks --cluster ${CLUSTER_NAME} --service-name ${SERVICE_NAME} --query 'taskArns[0]' --output text --profile ${AWS_PROFILE})

    if [ -z "$TASK_ARN" ] || [ "$TASK_ARN" == "None" ]; then
        log_error "ECSタスクが見つかりません"
        exit 1
    fi

    log_info "タスクARN: ${TASK_ARN}"
    log_info "データレイク転送コマンドを実行中..."
    echo ""

    # ECSコンテナ内でコマンドを実行
    aws ecs execute-command \
        --profile ${AWS_PROFILE} \
        --cluster ${CLUSTER_NAME} \
        --task ${TASK_ARN} \
        --container ${CONTAINER_NAME} \
        --interactive \
        --command "su -s /bin/bash nginx -c 'cd /var/www/admin && php artisan app:datalake-first-transfer-command'"

    if [ $? -eq 0 ]; then
        log_success "データレイク転送コマンドが完了しました"
    else
        log_error "データレイク転送コマンドの実行に失敗しました"
        exit 1
    fi

    echo ""
    log_success "ステップ1完了"
}

# 関数: ステップ2 - データダウンロード
step2_download_data() {
    log_info "=========================================="
    log_info "ステップ2: 構造変更テーブルのデータダウンロード"
    log_info "=========================================="

    # ダウンロードディレクトリ作成
    mkdir -p "${DOWNLOAD_DIR}/${TARGET_DATE_COMPACT}"

    for table in "${CHANGED_TABLES[@]}"; do
        log_info "テーブル: ${table} のデータをダウンロード中..."

        db_type=$(get_db_type "$table")

        case $db_type in
            mysql)
                gcs_path="${GCS_MYSQL_BASE}/${TARGET_DATE}/${table}/${table}-${TARGET_DATE_COMPACT}.part000000000000.json.gz"
                ;;
            tidb-full)
                gcs_path="${GCS_TIDB_FULL_BASE}/${TARGET_DATE}/${table}/${table}-${TARGET_DATE_COMPACT}.part000000000000.json.gz"
                ;;
            tidb-diff)
                gcs_path="${GCS_TIDB_DIFF_BASE}/${TARGET_DATE}/${table}/${table}-${TARGET_DATE_COMPACT}.part000000000000.json.gz"
                ;;
            *)
                log_error "テーブル ${table} のDB種別を判定できません"
                continue
                ;;
        esac

        output_file="${DOWNLOAD_DIR}/${TARGET_DATE_COMPACT}/${table}.json.gz"

        # gsutilは環境変数CLOUDSDK_CORE_ACCOUNTで指定されたアカウントを使用
        if gsutil cp "$gcs_path" "$output_file" 2>/dev/null; then
            log_success "ダウンロード完了: ${output_file}"
        else
            log_error "ダウンロード失敗: ${table}"
            log_error "GCSパス: ${gcs_path}"
            DOWNLOAD_FAILED_TABLES+=("${table} (${gcs_path})")
        fi
    done

    log_success "ステップ2完了"
}

# 関数: ステップ3 - データ検証
step3_verify_data() {
    log_info "=========================================="
    log_info "ステップ3: ダウンロードファイルの検証"
    log_info "=========================================="

    for table in "${CHANGED_TABLES[@]}"; do
        file_path="${DOWNLOAD_DIR}/${TARGET_DATE_COMPACT}/${table}.json.gz"

        if [ ! -f "$file_path" ]; then
            log_warning "ファイルが見つかりません: ${file_path}"
            MISSING_FILE_TABLES+=("${table} (${file_path})")
            continue
        fi

        log_info "テーブル: ${table}"
        log_info "ファイル: ${file_path}"

        echo ""
        echo -e "${GREEN}=== データ構造チェック ===${NC}"

        # 総件数を確認
        total_lines=$(gzcat "$file_path" | wc -l | tr -d ' ')
        sample_size=$(( total_lines < 100 ? total_lines : 100 ))

        log_info "総件数: ${total_lines}件（サンプル: ${sample_size}件）"

        # 共通列名を取得（最初の1件から列名を抽出）
        log_info "列名を抽出中..."
        common_columns=$(gzcat "$file_path" | head -n 1 | jq -r 'keys[]' 2>/dev/null)

        if [ -z "$common_columns" ]; then
            log_warning "列名の抽出に失敗しました"
        else
            echo "列名一覧:"
            echo "$common_columns" | tr '\n' ',' | sed 's/,$/\n/'
        fi

        echo ""

        # 各列のデータ型をチェック
        if [ -n "$common_columns" ]; then
            log_info "各列のデータ型をチェック中（サンプル: ${sample_size}件）..."
            temp_type_check="/tmp/type_check_$$.txt"

            gzcat "$file_path" | head -n "$sample_size" | jq -r 'to_entries | .[] | "\(.key):\(if .value == null then "null" elif (.value | type) == "object" then "object" elif (.value | type) == "array" then "array" else (.value | type) end)"' 2>/dev/null | sort > "$temp_type_check"

            # 列ごとにデータ型を集計
            temp_warning_file="/tmp/warnings_$$.txt"
            temp_detail_file="/tmp/warning_details_$$.txt"

            echo "$common_columns" | while read -r column; do
                types=$(grep "^${column}:" "$temp_type_check" | cut -d':' -f2 | sort -u)
                type_count=$(echo "$types" | wc -l | tr -d ' ')

                if [ "$type_count" -gt 1 ]; then
                    type_list=$(echo $types | tr '\n' ',' | sed 's/,$//' | tr ' ' ',')
                    echo -e "${YELLOW}[WARNING]${NC} ${column}: ${type_list}"
                    echo "${table}.${column}: ${type_list}" >> "$temp_warning_file"

                    # 各型の値の例を収集
                    for t in $types; do
                        example=$(gzcat "$file_path" | head -n "$sample_size" | jq -r --arg col "$column" --arg vtype "$t" 'select(has($col) and ((.[$col] | type) == $vtype or (.[$col] == null and $vtype == "null"))) | .[$col]' 2>/dev/null | head -1)
                        if [ -n "$example" ] || [ "$t" == "null" ]; then
                            # null の場合は "null" という文字列にする
                            [ "$t" == "null" ] && example="null"
                            echo "${table}.${column}|${t}|${example}" >> "$temp_detail_file"
                        fi
                    done
                elif [ -n "$types" ]; then
                    echo "${column}: ${types}"
                else
                    echo "${column}: (no data)"
                fi
            done

            # warningがあれば記録
            if [ -f "$temp_warning_file" ]; then
                while IFS= read -r warning; do
                    TYPE_WARNING_LIST+=("$warning")
                done < "$temp_warning_file"
                rm -f "$temp_warning_file"
            fi

            # 詳細情報があれば記録
            if [ -f "$temp_detail_file" ]; then
                while IFS= read -r detail; do
                    TYPE_WARNING_DETAILS+=("$detail")
                done < "$temp_detail_file"
                rm -f "$temp_detail_file"
            fi

            rm -f "$temp_type_check"
        fi

        echo ""
        log_info "ファイルの全内容を確認しますか？ (y/n)"
        read -r view_response
        if [[ $view_response =~ ^[Yy]$ ]]; then
            zless "$file_path"
        fi

        echo ""
        echo -e "${GREEN}=== 先頭10件 ===${NC}"
        gzcat "$file_path" | head -n 10

        echo ""
        echo -e "${GREEN}=== 末尾10件 ===${NC}"
        gzcat "$file_path" | tail -n 10

        echo ""
        log_info "データの中身に問題はありませんか？ (y/n)"
        read -r verify_response
        if [[ ! $verify_response =~ ^[Yy]$ ]]; then
            log_warning "データに問題がある可能性があります"
            DATA_ISSUE_TABLES+=("$table")
        fi

        echo ""
        echo "=========================================="
        echo ""
    done

    log_success "ステップ3完了"
}

# 関数: ステップ4 - テーブル定義書生成
step4_generate_schema_doc() {
    log_info "=========================================="
    log_info "ステップ4: テーブル定義書生成"
    log_info "=========================================="

    log_info "ECSタスクARNを取得中..."
    TASK_ARN=$(aws ecs list-tasks --cluster ${CLUSTER_NAME} --service-name ${SERVICE_NAME} --query 'taskArns[0]' --output text --profile ${AWS_PROFILE})

    if [ -z "$TASK_ARN" ] || [ "$TASK_ARN" == "None" ]; then
        log_error "ECSタスクが見つかりません"
        exit 1
    fi

    log_info "タスクARN: ${TASK_ARN}"
    log_info "テーブル定義書生成コマンドを実行中..."
    echo ""

    # 出力を一時ファイルに保存
    temp_output="/tmp/schema_doc_output_$$.txt"

    # ECSコンテナ内でコマンドを実行し、出力を保存
    aws ecs execute-command \
        --profile ${AWS_PROFILE} \
        --cluster ${CLUSTER_NAME} \
        --task ${TASK_ARN} \
        --container ${CONTAINER_NAME} \
        --interactive \
        --command "su -s /bin/bash nginx -c 'cd /var/www/admin && php artisan app:generate-table-schema-document'" \
        | tee "$temp_output"

    if [ $? -eq 0 ]; then
        log_success "テーブル定義書生成コマンドが完了しました"

        # S3 URLを抽出（出力からs3://またはhttps://で始まるURLを探す）
        SCHEMA_DOC_URL=$(grep -oE '(s3://[^ ]+|https://[^ ]+\.s3[^ ]*|https://s3[^ ]*)' "$temp_output" | head -1)

        if [ -n "$SCHEMA_DOC_URL" ]; then
            log_success "テーブル定義書URL: ${SCHEMA_DOC_URL}"

            # S3からテーブル定義書をダウンロード
            if [[ "$SCHEMA_DOC_URL" =~ ^s3:// ]]; then
                # S3 URLからファイル名を抽出
                schema_filename=$(basename "$SCHEMA_DOC_URL")
                schema_download_path="${DOWNLOAD_DIR}/${schema_filename}"

                log_info "テーブル定義書をダウンロード中..."
                if aws s3 cp "$SCHEMA_DOC_URL" "$schema_download_path" --profile ${AWS_PROFILE} 2>/dev/null; then
                    log_success "ダウンロード完了: ${schema_download_path}"
                    SCHEMA_DOC_LOCAL_PATH="$schema_download_path"
                else
                    log_error "テーブル定義書のダウンロードに失敗しました"
                    log_warning "手動でダウンロードしてください: ${SCHEMA_DOC_URL}"
                fi
            else
                log_warning "S3 URL形式ではないため、自動ダウンロードをスキップしました"
            fi
        else
            log_warning "S3 URLを自動取得できませんでした"
            log_info "テーブル定義書のS3 URLを入力してください（空欄可）:"
            read -r schema_doc_url
            if [ -n "$schema_doc_url" ]; then
                SCHEMA_DOC_URL="$schema_doc_url"
            fi
        fi

        # 一時ファイルを削除
        rm -f "$temp_output"
    else
        log_error "テーブル定義書生成コマンドの実行に失敗しました"
        rm -f "$temp_output"
        exit 1
    fi

    echo ""
    log_success "ステップ4完了"
}

# 関数: ステップ5 - Slack報告テンプレート生成
step5_generate_slack_report() {
    log_info "=========================================="
    log_info "ステップ5: Slack報告テンプレート生成"
    log_info "=========================================="

    echo ""
    echo "=========================================="
    echo "テーブル構造の変更に伴うデータ連携に関するご連絡になります。"
    echo "ご確認のほど、よろしくお願いいたします。"
    echo ""
    echo "■ 本番リリース予定日：MM/DD"
    echo ""
    echo "■ 変更内容まとめ"
    echo "- テーブル追加"
    echo "  - （テーブル名を記載）"
    echo "- 列追加"
    echo "  - （テーブル.列名を記載）"
    echo "- 列削除"
    echo "  - （テーブル.列名を記載）"
    echo "- 列変更"
    echo "  - （テーブル.列名を記載）"
    echo ""
    echo "■ テストデータ配信先"
    echo "${GCS_MYSQL_BASE}/${TARGET_DATE}/"
    echo "${GCS_TIDB_DIFF_BASE}/${TARGET_DATE}/"
    echo "${GCS_TIDB_FULL_BASE}/${TARGET_DATE}/"
    echo "=========================================="
    echo ""

    log_success "ステップ5完了"
}

# 関数: 検証サマリ表示
show_verification_summary() {
    echo ""
    log_info "=========================================="
    log_info "検証サマリ"
    log_info "=========================================="
    echo ""

    # ダウンロード失敗のサマリ
    if [ ${#DOWNLOAD_FAILED_TABLES[@]} -gt 0 ]; then
        log_error "ダウンロードに失敗したテーブル:"
        for table in "${DOWNLOAD_FAILED_TABLES[@]}"; do
            echo "  - ${table}"
        done
    else
        log_success "ダウンロード失敗: なし"
    fi

    echo ""

    # ファイル不在のサマリ
    if [ ${#MISSING_FILE_TABLES[@]} -gt 0 ]; then
        log_error "ファイルが見つからなかったテーブル:"
        for table in "${MISSING_FILE_TABLES[@]}"; do
            echo "  - ${table}"
        done
    else
        log_success "ファイル不在: なし"
    fi

    echo ""

    # データ型warningのサマリ
    if [ ${#TYPE_WARNING_LIST[@]} -gt 0 ]; then
        log_warning "データ型の不整合が検出されました:"
        for warning in "${TYPE_WARNING_LIST[@]}"; do
            warning_key=$(echo "$warning" | cut -d':' -f1)
            echo "  - ${warning}"

            # この警告に関連する詳細を表示
            for detail in "${TYPE_WARNING_DETAILS[@]}"; do
                detail_key=$(echo "$detail" | cut -d'|' -f1)
                if [ "$detail_key" == "$warning_key" ]; then
                    detail_type=$(echo "$detail" | cut -d'|' -f2)
                    detail_example=$(echo "$detail" | cut -d'|' -f3-)
                    echo "      ${detail_type}の例: ${detail_example}"
                fi
            done
        done
    else
        log_success "データ型の不整合: なし"
    fi

    echo ""

    # データ内容問題のサマリ
    if [ ${#DATA_ISSUE_TABLES[@]} -gt 0 ]; then
        log_warning "データ内容に問題がある可能性のあるテーブル:"
        for table in "${DATA_ISSUE_TABLES[@]}"; do
            echo "  - ${table}"
        done
    else
        log_success "データ内容の問題: なし"
    fi

    echo ""

    # 総合判定
    total_issues=$((${#DOWNLOAD_FAILED_TABLES[@]} + ${#MISSING_FILE_TABLES[@]} + ${#TYPE_WARNING_LIST[@]} + ${#DATA_ISSUE_TABLES[@]}))

    if [ $total_issues -eq 0 ]; then
        log_success "全ての検証項目が正常です"
    else
        log_warning "検出された問題の合計: ${total_issues}件"
        log_warning "上記の内容を確認してください。"
    fi

    echo ""
}

# メイン処理
main() {
    echo ""
    log_info "=========================================="
    log_info "QA環境データレイク転送テストスクリプト"
    log_info "=========================================="
    echo ""

    # 実行モード選択
    log_info "実行モードを選択してください:"
    echo "  1) 全ステップ実行（転送 → ダウンロード → 検証 → 定義書生成 → 報告テンプレート）"
    echo "  2) 検証のみ実行（既存ファイルの検証のみ）"
    echo -n "選択 (1 or 2): "
    read -r mode_choice

    # 前提条件チェック
    check_requirements

    # 対象日付設定（前日固定）
    set_target_date

    # 入力情報収集
    input_table_names

    echo ""
    log_info "以下の設定で処理を開始します:"
    log_info "- 対象日付: ${TARGET_DATE}"
    log_info "- 対象テーブル: ${CHANGED_TABLES[*]}"
    log_info "- ダウンロードディレクトリ: ${DOWNLOAD_DIR}"
    echo ""
    log_info "続行しますか？ (y/n)"
    read -r confirm

    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        log_warning "処理を中止しました"
        exit 0
    fi

    # 各ステップ実行
    if [[ "$mode_choice" == "2" ]]; then
        # 検証のみモード
        log_info "検証のみモードで実行します"
        step3_verify_data
        show_verification_summary
    else
        # 全ステップモード
        step1_ecs_transfer
        step2_download_data
        step3_verify_data
        step4_generate_schema_doc
        step5_generate_slack_report
        show_verification_summary
    fi

    echo ""
    log_success "=========================================="
    log_success "全ての処理が完了しました！"
    log_success "=========================================="
    echo ""
    log_info "次のステップ:"
    log_info "1. 上記のSlack報告テンプレートをコピーして内容を調整"
    log_info "2. 先方へSlackで報告（テーブル定義書を添付する）"
    if [ -n "$SCHEMA_DOC_LOCAL_PATH" ]; then
        log_info "3. テーブル定義書をGoogleドライブにアップロード"
        log_info "   ローカルファイル: ${SCHEMA_DOC_LOCAL_PATH}"
        log_info "   アップロード先: https://drive.google.com/drive/folders/1JZPWT9tA2MduIGvwHnJMBGfyrRa1iDAq"
    else
        log_info "3. テーブル定義書をGoogleドライブにアップロード"
        log_info "   https://drive.google.com/drive/folders/1JZPWT9tA2MduIGvwHnJMBGfyrRa1iDAq"
    fi
    echo ""
}

# スクリプト実行
main
