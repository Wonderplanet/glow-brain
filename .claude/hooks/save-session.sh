#!/bin/bash
# Stopフックから呼び出されるセッション保存スクリプト
set -e

# 1. 入力の取得
SESSION_JSON=$(cat)
SESSION_ID=$(echo "$SESSION_JSON" | jq -r '.session_id // empty')
TRANSCRIPT_PATH=$(echo "$SESSION_JSON" | jq -r '.transcript_path // empty')

# 2. バリデーション
if [ -z "$SESSION_ID" ] || [ -z "$TRANSCRIPT_PATH" ]; then
  exit 0
fi

# 3. オプション設定（デフォルトでJSONLファイルを保存、環境変数で無効化可能）
SAVE_JSONL="${SAVE_JSONL:-true}"

# 4. タイムスタンプの取得
FIRST_TS=""
if [ -f "$TRANSCRIPT_PATH" ]; then
  # 最初のタイムスタンプを持つ行を探してファイル名接頭辞を生成
  while IFS= read -r line; do
    TS=$(echo "$line" | jq -r '.timestamp // empty' 2>/dev/null | sed 's/\..*//')
    if [ -n "$TS" ]; then
      FIRST_TS="$TS"
      break
    fi
  done < "$TRANSCRIPT_PATH"
fi

# タイムスタンプ接頭辞の生成（2026-01-22T15:31:48 → 20260122153148_）
if [ -n "$FIRST_TS" ]; then
  SESSION_PREFIX=$(echo "$FIRST_TS" | sed 's/[-:T]//g')_
else
  SESSION_PREFIX=""
fi

# 5. 保存先ディレクトリの作成（タイムスタンプ付き）
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
SESSIONS_DIR="$PROJECT_ROOT/.claude/sessions"
SESSION_SAVE_DIR="$SESSIONS_DIR/${SESSION_PREFIX}${SESSION_ID}"

mkdir -p "$SESSION_SAVE_DIR"

# 6. JSONLファイルのコピー（SAVE_JSONL=trueの場合のみ）
if [ "$SAVE_JSONL" = "true" ]; then
  # 親セッションファイルのコピー
  if [ -f "$TRANSCRIPT_PATH" ]; then
    cp "$TRANSCRIPT_PATH" "$SESSION_SAVE_DIR/${SESSION_PREFIX}${SESSION_ID}.jsonl"
  fi

  # subagentファイルのコピー（タイムスタンプ接頭辞付き、同じフォルダに配置）
  TRANSCRIPT_DIR=$(dirname "$TRANSCRIPT_PATH")
  SESSION_SUBDIR="$TRANSCRIPT_DIR/$SESSION_ID"
  if [ -d "$SESSION_SUBDIR/subagents" ]; then
    # 各subagent JSONLファイルをタイムスタンプ接頭辞付きで同じフォルダにコピー
    for subagent_jsonl in "$SESSION_SUBDIR/subagents"/*.jsonl; do
      if [ -f "$subagent_jsonl" ]; then
        BASENAME=$(basename "$subagent_jsonl")

        # 最初のタイムスタンプを持つ行を探してファイル名接頭辞を生成
        FIRST_TS=""
        while IFS= read -r line; do
          TS=$(echo "$line" | jq -r '.timestamp // empty' 2>/dev/null | sed 's/\..*//')
          if [ -n "$TS" ]; then
            FIRST_TS="$TS"
            break
          fi
        done < "$subagent_jsonl"

        if [ -n "$FIRST_TS" ]; then
          # 2026-01-22T15:31:48 → 20260122153148_
          SUBAGENT_PREFIX=$(echo "$FIRST_TS" | sed 's/[-:T]//g')_
        else
          SUBAGENT_PREFIX=""
        fi

        cp "$subagent_jsonl" "$SESSION_SAVE_DIR/${SUBAGENT_PREFIX}${BASENAME}"
      fi
    done
  fi
fi

# 7. JSONL → Markdown変換
CONVERTER="$SCRIPT_DIR/jsonl-to-md.sh"
if [ -f "$CONVERTER" ]; then
  if [ "$SAVE_JSONL" = "true" ]; then
    # JSONLが保存されている場合は、保存ディレクトリのJSONLファイルから変換
    for jsonl in "$SESSION_SAVE_DIR"/*.jsonl; do
      if [ -f "$jsonl" ]; then
        "$CONVERTER" "$jsonl" "${jsonl%.jsonl}.md" 2>/dev/null || true
      fi
    done
  else
    # JSONLを保存しない場合は、元のファイルから直接Markdownに変換
    if [ -f "$TRANSCRIPT_PATH" ]; then
      "$CONVERTER" "$TRANSCRIPT_PATH" "$SESSION_SAVE_DIR/${SESSION_PREFIX}${SESSION_ID}.md" 2>/dev/null || true
    fi

    # subagentファイルの変換
    TRANSCRIPT_DIR=$(dirname "$TRANSCRIPT_PATH")
    SESSION_SUBDIR="$TRANSCRIPT_DIR/$SESSION_ID"
    if [ -d "$SESSION_SUBDIR/subagents" ]; then
      for subagent_jsonl in "$SESSION_SUBDIR/subagents"/*.jsonl; do
        if [ -f "$subagent_jsonl" ]; then
          BASENAME=$(basename "$subagent_jsonl")

          # 最初のタイムスタンプを持つ行を探してファイル名接頭辞を生成
          FIRST_TS=""
          while IFS= read -r line; do
            TS=$(echo "$line" | jq -r '.timestamp // empty' 2>/dev/null | sed 's/\..*//')
            if [ -n "$TS" ]; then
              FIRST_TS="$TS"
              break
            fi
          done < "$subagent_jsonl"

          if [ -n "$FIRST_TS" ]; then
            # 2026-01-22T15:31:48 → 20260122153148_
            SUBAGENT_PREFIX=$(echo "$FIRST_TS" | sed 's/[-:T]//g')_
          else
            SUBAGENT_PREFIX=""
          fi

          "$CONVERTER" "$subagent_jsonl" "$SESSION_SAVE_DIR/${SUBAGENT_PREFIX}${BASENAME%.jsonl}.md" 2>/dev/null || true
        fi
      done
    fi
  fi
fi

# 8. Skill使用ログの生成
if [ -f "$TRANSCRIPT_PATH" ]; then
  SKILL_USAGE_LOG="$SESSION_SAVE_DIR/skill-usage.jsonl"

  # JSONLトランスクリプトからSkillツール使用を抽出してskill-usage.jsonlを生成
  jq -r '
    select(.type == "assistant") |
    select(.message.content) |
    .timestamp as $msg_timestamp |
    .message.content[] |
    select(.type == "tool_use" and .name == "Skill") |
    {
      timestamp: $msg_timestamp,
      skill_name: .input.skill,
      tool_use_id: .id,
      session_id: "'$SESSION_ID'"
    } |
    @json
  ' "$TRANSCRIPT_PATH" > "$SKILL_USAGE_LOG" 2>/dev/null || true

  # 空ファイルの場合は削除
  if [ -f "$SKILL_USAGE_LOG" ] && [ ! -s "$SKILL_USAGE_LOG" ]; then
    rm "$SKILL_USAGE_LOG"
  fi
fi

# # 9. フィードバックテンプレート作成
# FEEDBACK_FILE="$SESSION_SAVE_DIR/feedback.json"
# if [[ ! -f "$FEEDBACK_FILE" ]]; then
#   CWD="${CWD:-$(pwd)}"
#   BRANCH=""
#   if [ -d "$CWD/.git" ]; then
#     BRANCH=$(cd "$CWD" && git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "")
#   fi

#   jq -n \
#     --arg session_id "$SESSION_ID" \
#     --arg timestamp "$(date -u +"%Y-%m-%dT%H:%M:%SZ")" \
#     --arg user "$(whoami)" \
#     --arg project "$(basename "$CWD")" \
#     --arg branch "$BRANCH" \
#     '{
#       session_id: $session_id,
#       timestamp: $timestamp,
#       user: $user,
#       rating: null,
#       comment: "(オプション) 良かった点・改善してほしい点・気づいたことなど",
#       metadata: {
#         project: $project,
#         branch: $branch
#       }
#     }' > "$FEEDBACK_FILE"

#   echo "" >&2
#   echo "📝 セッションフィードバックのお願い" >&2
#   echo "   ファイル: $FEEDBACK_FILE" >&2
#   echo "" >&2
#   echo "   このセッションの満足度を教えてください:" >&2
#   echo "   • rating: 1(低い)～5(高い) の数値を入力" >&2
#   echo "   • comment: 良かった点、改善してほしい点など(任意)" >&2
#   echo "" >&2

#   # macOS通知
#   if command -v terminal-notifier &> /dev/null; then
#     # terminal-notifier利用可能: クリックでファイルを開く機能付き
#     terminal-notifier \
#       -title "📝 セッションフィードバックのお願い" \
#       -message "満足度評価: rating (1-5) と comment を入力してください" \
#       -subtitle "クリックしてファイルを開く" \
#       -open "file://$FEEDBACK_FILE" \
#       -group "claude-feedback" \
#       > /dev/null 2>&1 &
#   elif [[ "$OSTYPE" == "darwin"* ]]; then
#     # macOS標準のosascriptで通知（クリックアクションなし）
#     osascript -e "display notification \"満足度評価: rating (1-5) と comment を入力してください\" with title \"📝 セッションフィードバックのお願い\" subtitle \"$FEEDBACK_FILE\"" \
#       > /dev/null 2>&1 &
#   fi
# fi

# # 9. セッションログをリモートにプッシュ（設定で有効な場合）
# PUSH_SCRIPT="$SCRIPT_DIR/push-session-logs.sh"
# if [ -f "$PUSH_SCRIPT" ]; then
#   "$PUSH_SCRIPT" "$SESSION_SAVE_DIR" 2>/dev/null &
# fi

exit 0
