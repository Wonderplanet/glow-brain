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

# 3. オプション設定（環境変数SAVE_JSONL=trueの場合のみJSONLファイルを保存）
SAVE_JSONL="${SAVE_JSONL:-false}"

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

exit 0
