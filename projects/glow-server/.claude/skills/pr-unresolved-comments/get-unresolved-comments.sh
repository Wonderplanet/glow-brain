#!/bin/bash
#
# PR未解決コメント取得スクリプト（読み取り専用）
#
# このスクリプトはGitHub APIから情報を取得するのみで、
# リソースの作成・更新・削除は一切行いません。
#
# 使用方法:
#   bash get-unresolved-comments.sh [PR番号] [--json]
#
# オプション:
#   PR番号: 省略時は現在のブランチに対応するPRを自動検出
#   --json: JSON形式で出力（省略時は整形出力）
#

set -e
set -o pipefail

# 現在のリポジトリを自動検出（git remoteから解析、SSH/HTTPS/Enterprise対応）
REMOTE_URL=$(git remote get-url origin 2>/dev/null)
if [ -z "$REMOTE_URL" ]; then
    echo "エラー: Gitリポジトリが見つかりません。Gitリポジトリ内で実行してください。" >&2
    exit 1
fi
# URLからowner/repoを抽出（SSH/HTTPS対応、.gitを除去）
# 例: git@github.com:owner/repo.git -> owner/repo
#     https://github.com/owner/repo.git -> owner/repo
REPO_PATH=$(echo "$REMOTE_URL" | sed -E 's#^.*(github\.com[:/])##' | sed 's/\.git$//')
OWNER=$(echo "$REPO_PATH" | cut -d'/' -f1)
REPO_NAME=$(echo "$REPO_PATH" | cut -d'/' -f2)
REPO="${OWNER}/${REPO_NAME}"

PR_NUMBER=""
JSON_OUTPUT=false

# 引数解析
for arg in "$@"; do
    case $arg in
        --json)
            JSON_OUTPUT=true
            ;;
        *)
            if [[ $arg =~ ^[0-9]+$ ]]; then
                PR_NUMBER=$arg
            fi
            ;;
    esac
done

# PR番号が指定されていない場合、現在のブランチから自動検出
if [ -z "$PR_NUMBER" ]; then
    CURRENT_BRANCH=$(git branch --show-current)
    PR_NUMBER=$(gh pr list --head "$CURRENT_BRANCH" --repo "$REPO" --json number -q '.[0].number' 2>/dev/null)

    if [ -z "$PR_NUMBER" ]; then
        echo "エラー: 現在のブランチ ($CURRENT_BRANCH) に対応するPRが見つかりません" >&2
        exit 1
    fi
fi

# GraphQL クエリ（対話を含む全コメント取得）
QUERY='
query($owner: String!, $repo: String!, $prNumber: Int!) {
  repository(owner: $owner, name: $repo) {
    pullRequest(number: $prNumber) {
      number
      title
      reviewThreads(first: 100) {
        edges {
          node {
            isResolved
            path
            line
            startLine
            diffSide
            comments(first: 50) {
              edges {
                node {
                  body
                  author { login }
                  createdAt
                }
              }
            }
          }
        }
      }
    }
  }
}
'

# GraphQL API 実行
RESULT=$(gh api graphql \
    -f owner="$OWNER" \
    -f repo="$REPO_NAME" \
    -F prNumber="$PR_NUMBER" \
    -f query="$QUERY" 2>/dev/null)

if [ $? -ne 0 ]; then
    echo "エラー: PR #$PR_NUMBER の情報取得に失敗しました" >&2
    exit 1
fi

# レスポンスの検証
if ! echo "$RESULT" | jq -e '.data.repository.pullRequest' >/dev/null 2>&1; then
    echo "エラー: APIレスポンスが不正です" >&2
    echo "デバッグ情報: $RESULT" >&2
    exit 1
fi

# JSON出力モード
if [ "$JSON_OUTPUT" = true ]; then
    # 未解決コメントを配列として出力（結果がない場合は空配列）
    UNRESOLVED_JSON=$(echo "$RESULT" | jq '[.data.repository.pullRequest.reviewThreads.edges[] | select(.node.isResolved == false)]' 2>/dev/null)
    if [ -z "$UNRESOLVED_JSON" ] || [ "$UNRESOLVED_JSON" = "null" ]; then
        echo "[]"
    else
        echo "$UNRESOLVED_JSON"
    fi
    exit 0
fi

# 整形出力モード
PR_TITLE=$(echo "$RESULT" | jq -r '.data.repository.pullRequest.title // "不明"')
UNRESOLVED=$(echo "$RESULT" | jq '[.data.repository.pullRequest.reviewThreads.edges[] | select(.node.isResolved == false)]')
if [ -z "$UNRESOLVED" ] || ! echo "$UNRESOLVED" | jq -e '.' >/dev/null 2>&1; then
    echo "エラー: 未解決コメントの解析に失敗しました" >&2
    exit 1
fi
COUNT=$(echo "$UNRESOLVED" | jq 'length')

echo "=== PR #$PR_NUMBER 未解決コメント ==="
echo "タイトル: $PR_TITLE"
echo ""

if [ "$COUNT" -eq 0 ]; then
    echo "未解決のコメントはありません"
    exit 0
fi

# 全コメント（対話を含む）を表示
echo "$UNRESOLVED" | jq -r '.[] |
    .node as $thread |
    "📁 \($thread.path // "?")" +
    (if $thread.startLine and $thread.startLine != $thread.line then
        " (L\($thread.startLine)-L\($thread.line // "?"))"
    else
        " (L\($thread.line // "?"))"
    end) +
    "\n" +
    ([$thread.comments.edges[] |
        .node as $comment |
        "  [\($comment.author.login // "unknown") \(($comment.createdAt // "")[0:10])]\n" +
        "  \($comment.body | gsub("\r\n"; "\n") | split("\n") | map("    " + .) | join("\n"))\n"
    ] | join("\n")) +
    "---\n"'

echo "合計: ${COUNT}件の未解決スレッド"
