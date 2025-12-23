#!/bin/bash

# glow-brain-2 セットアップ・更新スクリプト
# バージョンごとの3つのリポジトリ（glow-server, glow-masterdata, glow-client）を管理します

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
get_current_version() {
    jq -r '.current_version' "${CONFIG_FILE}"
}

get_repository_url() {
    local repo_name="$1"
    jq -r ".repositories.\"${repo_name}\"" "${CONFIG_FILE}"
}

get_branch_name() {
    local version="$1"
    local repo_name="$2"
    jq -r ".versions.\"${version}\".\"${repo_name}\"" "${CONFIG_FILE}"
}

version_exists() {
    local version="$1"
    local result
    result=$(jq -r ".versions.\"${version}\" != null" "${CONFIG_FILE}")
    [ "${result}" = "true" ]
}

# ====================================
# JSON 書き込み関数
# ====================================
update_current_version() {
    local new_version="$1"
    local tmp_file="${CONFIG_FILE}.tmp"

    jq ".current_version = \"${new_version}\"" "${CONFIG_FILE}" > "${tmp_file}"
    mv "${tmp_file}" "${CONFIG_FILE}"

    success "current_version を ${new_version} に更新しました"
}

# ====================================
# リポジトリチェック関数
# ====================================
repo_exists() {
    local repo_name="$1"
    [ -d "${PROJECTS_DIR}/${repo_name}/.git" ]
}

has_uncommitted_changes() {
    local repo_path="$1"

    cd "${repo_path}"

    # 未コミットの変更をチェック
    if ! git diff-index --quiet HEAD --; then
        return 0  # 変更あり
    fi

    # Untrackedファイルをチェック
    if [ -n "$(git ls-files --others --exclude-standard)" ]; then
        return 0  # 変更あり
    fi

    return 1  # 変更なし
}

# ====================================
# リポジトリクローン関数
# ====================================
clone_repository() {
    local repo_name="$1"
    local branch="$2"
    local repo_url="$3"

    info "${repo_name} をクローンしています（ブランチ: ${branch}）..."

    if [ "${repo_name}" = "glow-client" ]; then
        # glow-client は軽量化クローン
        GIT_LFS_SKIP_SMUDGE=1 git clone \
            --depth 1 \
            --filter=blob:none \
            --sparse \
            --branch "${branch}" \
            "${repo_url}" \
            "${PROJECTS_DIR}/${repo_name}"

        cd "${PROJECTS_DIR}/${repo_name}"
        git sparse-checkout init --cone
        git sparse-checkout set Assets/GLOW/Scripts Assets/Framework/Scripts

        success "${repo_name} のクローンが完了しました（軽量化版）"
    else
        # glow-server と glow-masterdata は通常クローン
        git clone \
            --branch "${branch}" \
            "${repo_url}" \
            "${PROJECTS_DIR}/${repo_name}"

        success "${repo_name} のクローンが完了しました"
    fi
}

# ====================================
# リポジトリ更新関数
# ====================================
update_repository() {
    local repo_name="$1"
    local target_branch="$2"
    local repo_path="${PROJECTS_DIR}/${repo_name}"

    info "${repo_name} を更新しています（ブランチ: ${target_branch}）..."

    cd "${repo_path}"

    # 未コミット変更チェック（読み取り専用ポリシー）
    if has_uncommitted_changes "${repo_path}"; then
        error "${repo_name} に未コミットの変更があります"
        error "glow-brain-2 は参照専用プロジェクトのため、リポジトリへの変更は禁止されています"
        error "変更を破棄するか、別の場所に退避してから再実行してください"
        exit 1
    fi

    # 現在のブランチを取得
    local current_branch
    current_branch=$(git rev-parse --abbrev-ref HEAD)

    if [ "${repo_name}" = "glow-client" ]; then
        # glow-client の軽量化更新処理

        # sparse checkout 設定の確認・再設定
        git sparse-checkout init --cone
        git sparse-checkout set Assets/GLOW/Scripts Assets/Framework/Scripts

        # ブランチ切り替えと更新
        if [ "${current_branch}" != "${target_branch}" ]; then
            info "ブランチを ${current_branch} から ${target_branch} に切り替えています..."
            git fetch origin "${target_branch}:${target_branch}" --depth 1
            git switch "${target_branch}"
        else
            info "最新の変更を取得しています..."
            git fetch origin "${target_branch}" --depth 1
            git merge --ff-only "origin/${target_branch}"
        fi

        success "${repo_name} の更新が完了しました（軽量化版）"
    else
        # glow-server と glow-masterdata の通常更新

        if [ "${current_branch}" != "${target_branch}" ]; then
            info "ブランチを ${current_branch} から ${target_branch} に切り替えています..."
            git fetch origin "${target_branch}"
            git switch "${target_branch}"
        fi

        info "最新の変更を取得しています..."
        git fetch origin "${target_branch}"

        # Fast-forward のみで更新
        if git merge-base --is-ancestor HEAD "origin/${target_branch}"; then
            git merge --ff-only "origin/${target_branch}"
            success "${repo_name} の更新が完了しました"
        else
            warning "${repo_name} は既に最新です"
        fi
    fi
}

# ====================================
# リポジトリ処理（クローンまたは更新）
# ====================================
process_repository() {
    local repo_name="$1"
    local version="$2"

    local branch
    branch=$(get_branch_name "${version}" "${repo_name}")

    if [ "${branch}" = "null" ] || [ -z "${branch}" ]; then
        error "バージョン ${version} に ${repo_name} のブランチ設定が見つかりません"
        exit 1
    fi

    local repo_url
    repo_url=$(get_repository_url "${repo_name}")

    if repo_exists "${repo_name}"; then
        update_repository "${repo_name}" "${branch}"
    else
        clone_repository "${repo_name}" "${branch}" "${repo_url}"
    fi

    # Gitフックのセットアップ
    setup_git_hooks "${repo_name}"
}

# ====================================
# Gitフックセットアップ関数
# ====================================
setup_git_hooks() {
    local repo_name="$1"
    local repo_path="${PROJECTS_DIR}/${repo_name}"
    local hooks_dir="${repo_path}/.git/hooks"
    local source_hooks_dir="${SCRIPT_DIR}/hooks"

    info "${repo_name} に保護用Gitフックを設定しています..."

    # フックファイル一覧
    local hooks=("pre-commit" "pre-push" "pre-merge-commit")

    for hook in "${hooks[@]}"; do
        local source_hook="${source_hooks_dir}/${hook}"
        local target_hook="${hooks_dir}/${hook}"

        # 既存フックのバックアップ
        if [ -f "${target_hook}" ] && [ ! -L "${target_hook}" ]; then
            local backup_name="${target_hook}.backup-$(date +%Y%m%d-%H%M%S)"
            mv "${target_hook}" "${backup_name}"
            warning "既存のフックをバックアップしました: $(basename ${backup_name})"
        fi

        # フックのインストール
        cp "${source_hook}" "${target_hook}"
        chmod +x "${target_hook}"
    done

    success "${repo_name} の保護用Gitフックを設定しました"
}

# ====================================
# 現在の構成を表示
# ====================================
show_current_configuration() {
    local version="$1"

    echo ""
    info "========================================="
    info "現在の構成（バージョン: ${version}）"
    info "========================================="

    for repo in glow-server glow-masterdata glow-client; do
        if repo_exists "${repo}"; then
            cd "${PROJECTS_DIR}/${repo}"
            local current_branch
            current_branch=$(git rev-parse --abbrev-ref HEAD)
            local commit_hash
            commit_hash=$(git rev-parse --short HEAD)
            echo -e "  ${COLOR_GREEN}✓${COLOR_RESET} ${repo}: ${current_branch} (${commit_hash})"
        else
            echo -e "  ${COLOR_RED}✗${COLOR_RESET} ${repo}: 未セットアップ"
        fi
    done

    echo ""
}

# ====================================
# メイン処理
# ====================================
main() {
    info "glow-brain-2 セットアップスクリプトを開始します"
    echo ""

    # 前提条件チェック
    check_prerequisites

    # バージョン決定
    local version="${1:-}"

    if [ -z "${version}" ]; then
        version=$(get_current_version)
        info "引数が指定されていないため、current_version (${version}) を使用します"
    else
        info "指定されたバージョン: ${version}"
    fi

    # バージョン存在確認
    if ! version_exists "${version}"; then
        error "バージョン ${version} は config/versions.json に定義されていません"
        error "利用可能なバージョン:"
        jq -r '.versions | keys[]' "${CONFIG_FILE}" | while read -r v; do
            error "  - ${v}"
        done
        exit 1
    fi

    # projects ディレクトリの作成
    if [ ! -d "${PROJECTS_DIR}" ]; then
        info "projects ディレクトリを作成します"
        mkdir -p "${PROJECTS_DIR}"
    fi

    # 各リポジトリの処理
    echo ""
    info "========================================="
    info "リポジトリの処理を開始します"
    info "========================================="
    echo ""

    process_repository "glow-server" "${version}"
    echo ""
    process_repository "glow-masterdata" "${version}"
    echo ""
    process_repository "glow-client" "${version}"
    echo ""

    # current_version の更新
    local current_version
    current_version=$(get_current_version)

    if [ "${current_version}" != "${version}" ]; then
        update_current_version "${version}"
    fi

    # 完了メッセージと現在の構成表示
    success "すべての処理が完了しました！"
    show_current_configuration "${version}"

    info "次のステップ:"
    info "  - 各リポジトリは参照専用です（変更は禁止）"
    info "  - バージョンを切り替える場合: ./scripts/setup.sh <version>"
    info "  - 最新に更新する場合: ./scripts/setup.sh"
    echo ""
}

# スクリプト実行
main "$@"
