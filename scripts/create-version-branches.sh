#!/bin/bash

# バージョン環境ブランチ作成スクリプト
# config/versions.json に定義された全バージョン環境のブランチを自動作成します

set -euo pipefail

# ====================================
# カラー出力設定
# ====================================
readonly COLOR_RED='\033[0;31m'
readonly COLOR_GREEN='\033[0;32m'
readonly COLOR_YELLOW='\033[1;33m'
readonly COLOR_BLUE='\033[0;34m'
readonly COLOR_RESET='\033[0m'

# ====================================
# パス設定
# ====================================
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
readonly CONFIG_FILE="${PROJECT_ROOT}/config/versions.json"
readonly PROJECTS_DIR="${PROJECT_ROOT}/projects"
readonly BASE_BRANCH="version-env-base"

# ====================================
# ログ出力関数
# ====================================
info() {
    echo -e "${COLOR_BLUE}[INFO]${COLOR_RESET} $*"
}

success() {
    echo -e "${COLOR_GREEN}[SUCCESS]${COLOR_RESET} $*"
}

error() {
    echo -e "${COLOR_RED}[ERROR]${COLOR_RESET} $*" >&2
}

warning() {
    echo -e "${COLOR_YELLOW}[WARNING]${COLOR_RESET} $*"
}

# ====================================
# 前提条件チェック
# ====================================
check_prerequisites() {
    if ! command -v jq &> /dev/null; then
        error "jq コマンドが見つかりません"
        error "インストール方法:"
        error "  macOS: brew install jq"
        error "  Ubuntu/Debian: sudo apt-get install jq"
        error "  CentOS/RHEL: sudo yum install jq"
        exit 1
    fi

    if [ ! -f "${CONFIG_FILE}" ]; then
        error "設定ファイルが見つかりません: ${CONFIG_FILE}"
        exit 1
    fi
}

# ====================================
# JSON 読み込み関数
# ====================================
get_all_versions() {
    jq -r '.versions | keys[]' "${CONFIG_FILE}"
}

# ====================================
# Git 操作関数
# ====================================
ensure_on_base_branch() {
    info "version-env-base の最新を取得しています..."

    cd "${PROJECT_ROOT}"

    # 未コミット変更チェック
    if ! git diff-index --quiet HEAD -- 2>/dev/null; then
        error "未コミットの変更があります"
        error "変更をコミットするか、破棄してから再実行してください"
        exit 1
    fi

    # Untrackedファイルをチェック
    if [ -n "$(git ls-files --others --exclude-standard)" ]; then
        error "未追跡ファイルがあります"
        error "ファイルを追加するか、削除してから再実行してください"
        exit 1
    fi

    # version-env-base に切り替え
    git fetch origin "${BASE_BRANCH}"
    git checkout "${BASE_BRANCH}"
    git reset --hard "origin/${BASE_BRANCH}"

    success "version-env-base に切り替わりました"
}

delete_remote_branch_if_exists() {
    local branch="$1"

    # リモートブランチの存在確認
    if git ls-remote --heads origin "${branch}" | grep -q "${branch}"; then
        info "リモートブランチ ${branch} を削除しています..."
        git push origin --delete "${branch}"
        success "リモートブランチ ${branch} を削除しました"
    else
        info "リモートブランチ ${branch} は存在しません（スキップ）"
    fi
}

delete_local_branch_if_exists() {
    local branch="$1"

    # 現在のブランチを取得
    local current_branch
    current_branch=$(git rev-parse --abbrev-ref HEAD)

    # 現在のブランチと同じ場合はスキップ
    if [ "${current_branch}" = "${branch}" ]; then
        info "現在のブランチ ${branch} は削除できません（後で切り替えます）"
        return
    fi

    # ローカルブランチの存在確認
    if git branch --list "${branch}" | grep -q "${branch}"; then
        info "ローカルブランチ ${branch} を削除しています..."
        git branch -D "${branch}"
        success "ローカルブランチ ${branch} を削除しました"
    else
        info "ローカルブランチ ${branch} は存在しません（スキップ）"
    fi
}

create_fresh_branch() {
    local branch="$1"

    info "新しいブランチを作成しています..."
    git checkout -b "${branch}"
    success "ブランチ ${branch} を作成しました"
}

# ====================================
# ブランチ作成フロー関数
# ====================================
modify_gitignore_for_version_branch() {
    local gitignore_file="${PROJECT_ROOT}/.gitignore"

    info ".gitignore を調整しています..."

    # sed を使って "projects/*" の行をコメントアウト
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' 's/^projects\/\*$/#projects\/*/g' "${gitignore_file}"
    else
        # Linux
        sed -i 's/^projects\/\*$/#projects\/*/g' "${gitignore_file}"
    fi

    # git add
    git add "${gitignore_file}"

    success ".gitignore を調整しました"
}

run_setup_for_version() {
    local version="$1"

    info "setup.sh を実行しています（バージョン: ${version}）..."
    info "（大容量のため時間がかかる場合があります）"

    # PROJECT_ROOT に移動してから実行
    cd "${PROJECT_ROOT}"

    # setup.sh を実行
    if ! "${SCRIPT_DIR}/setup.sh" "${version}"; then
        error "setup.sh の実行に失敗しました"
        return 1
    fi

    success "setup.sh の実行が完了しました"
}

commit_projects_directory() {
    local version="$1"

    info "projects/ をコミットしています..."
    info "（大容量のため時間がかかる場合があります）"

    git add projects/
    git add .gitignore

    git commit -m "[バージョン環境] ${version} のプロジェクトファイルを追加

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

    success "コミットが完了しました"
}

push_to_remote() {
    local branch="$1"

    info "リモートに push しています..."
    info "（大容量のため時間がかかる場合があります）"

    git push -f origin "${branch}"

    success "push が完了しました"
}

# ====================================
# エラーハンドリング
# ====================================
cleanup_on_error() {
    local version="${1:-unknown}"

    error "エラーが発生しました。クリーンアップを実行します..."

    # 1. version-env-base に戻る
    cd "${PROJECT_ROOT}"
    if git branch --list "${BASE_BRANCH}" &>/dev/null; then
        git checkout "${BASE_BRANCH}" 2>/dev/null || true
    fi

    # 2. .gitignore を元に戻す
    git checkout .gitignore 2>/dev/null || true

    # 3. projects/ ディレクトリを削除（エラー状態のファイルを残さない）
    if [ -d "${PROJECTS_DIR}" ]; then
        warning "projects/ ディレクトリを削除します..."
        rm -rf "${PROJECTS_DIR}"
    fi

    # 4. 作成途中のローカルブランチを削除
    if [ "${version}" != "unknown" ]; then
        git branch -D "${version}" 2>/dev/null || true
    fi

    error "クリーンアップが完了しました"
    exit 1
}

# trap でエラー時に自動クリーンアップ
trap 'cleanup_on_error' ERR

# ====================================
# ユーザー確認
# ====================================
confirm_execution() {
    local versions=("$@")

    echo ""
    warning "========================================="
    warning "⚠️  重要な確認"
    warning "========================================="
    echo ""
    warning "以下のブランチを作成します:"
    for v in "${versions[@]}"; do
        echo "  • ${v}"
    done
    echo ""
    warning "既存のリモートブランチがある場合は削除されます。"
    echo ""

    read -p "続行しますか? (yes/no): " answer

    if [ "${answer}" != "yes" ]; then
        info "処理をキャンセルしました"
        exit 0
    fi

    echo ""
}

# ====================================
# 単一バージョン処理
# ====================================
process_single_version() {
    local version="$1"

    # リモートブランチ削除
    delete_remote_branch_if_exists "${version}"

    # ローカルブランチ削除
    delete_local_branch_if_exists "${version}"

    # version-env-base に切り替え
    ensure_on_base_branch

    # 新ブランチ作成
    create_fresh_branch "${version}"

    # .gitignore 修正
    modify_gitignore_for_version_branch

    # setup.sh 実行
    run_setup_for_version "${version}"

    # projects/ コミット
    commit_projects_directory "${version}"

    # リモートに push
    push_to_remote "${version}"

    success "${version} の処理が完了しました！"
}

# ====================================
# メイン処理
# ====================================
main() {
    info "========================================="
    info "バージョン環境ブランチ作成スクリプト"
    info "========================================="
    echo ""

    # 前提条件チェック
    check_prerequisites

    # 全バージョンリストを取得
    local versions=()
    while IFS= read -r version; do
        versions+=("${version}")
    done < <(get_all_versions)

    # バージョンが存在しない場合
    if [ ${#versions[@]} -eq 0 ]; then
        error "config/versions.json にバージョンが定義されていません"
        exit 1
    fi

    # 作成予定のブランチを表示
    info "作成予定のブランチ:"
    for v in "${versions[@]}"; do
        echo "  • ${v}"
    done
    echo ""

    # ユーザー確認
    confirm_execution "${versions[@]}"

    # 各バージョンを順次処理
    local total=${#versions[@]}
    local current=0

    for version in "${versions[@]}"; do
        current=$((current + 1))
        echo ""
        info "========================================="
        info "${current}/${total}: ${version} の処理を開始"
        info "========================================="
        echo ""

        process_single_version "${version}"
    done

    # version-env-base に戻る
    echo ""
    info "version-env-base に戻っています..."
    ensure_on_base_branch

    # 完了サマリー
    echo ""
    success "========================================="
    success "全ての処理が完了しました！"
    success "========================================="
    echo ""

    info "作成されたブランチ:"
    for v in "${versions[@]}"; do
        echo "  ✓ ${v}"
    done
    echo ""

    info "現在のブランチ: ${BASE_BRANCH}"
    echo ""

    info "次のステップ:"
    info "  • ブランチを確認: git branch -a"
    info "  • 特定バージョンに切り替え: git checkout <version-name>"
    echo ""
}

# スクリプト実行
main "$@"
