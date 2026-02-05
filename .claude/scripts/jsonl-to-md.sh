#!/bin/bash
# JSONL → Markdown 変換スクリプト
# 使用方法: ./jsonl-to-md.sh <input.jsonl> <output.md>

set -e

# タイムゾーン設定（環境変数SESSION_TIMEZONEで変更可能、デフォルトはJST）
TZ_SETTING="${SESSION_TIMEZONE:-Asia/Tokyo}"

INPUT_FILE="$1"
OUTPUT_FILE="$2"

# 引数チェック
if [ -z "$INPUT_FILE" ] || [ -z "$OUTPUT_FILE" ]; then
  echo "使用方法: $0 <input.jsonl> <output.md>" >&2
  exit 1
fi

if [ ! -f "$INPUT_FILE" ]; then
  echo "エラー: 入力ファイルが見つかりません: $INPUT_FILE" >&2
  exit 1
fi

# 事前集計: jqが利用可能な場合のみ
SUMMARY_TABLE=""
if command -v jq >/dev/null 2>&1; then
  COUNT_USER=$(jq -r 'select(.type == "user") | .type' "$INPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')
  COUNT_ASSISTANT=$(jq -r 'select(.type == "assistant") | .type' "$INPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')
  COUNT_PROGRESS=$(jq -r 'select(.type == "progress") | .type' "$INPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')
  COUNT_SYSTEM=$(jq -r 'select(.type == "system") | .type' "$INPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')
  COUNT_FILE_HISTORY=$(jq -r 'select(.type == "file-history-snapshot") | .type' "$INPUT_FILE" 2>/dev/null | wc -l | tr -d ' ')

  SUMMARY_TABLE="| セクション | 件数 |
|------------|------|
| 👤 User | ${COUNT_USER} |
| 🤖 Assistant | ${COUNT_ASSISTANT} |
| 📊 Progress | ${COUNT_PROGRESS} |
| ⚙️ System | ${COUNT_SYSTEM} |
| 📁 File History | ${COUNT_FILE_HISTORY} |
"
fi

# ヘッダー出力（サマリ付き、jqがなければサマリなし）
cat > "$OUTPUT_FILE" << EOF
# セッションログ

${SUMMARY_TABLE}---

EOF

# JSONLを1行ずつ処理
LINE_NUM=0
while IFS= read -r line; do
  LINE_NUM=$((LINE_NUM + 1))

  # 空行をスキップ
  [ -z "$line" ] && continue

  # JSONとして不正な行をスキップ
  if ! echo "$line" | jq -e . >/dev/null 2>&1; then
    continue
  fi

  # type フィールドを取得
  TYPE=$(echo "$line" | jq -r '.type // empty')

  # タイムスタンプをUTCから指定タイムゾーンに変換
  TIMESTAMP=$(echo "$line" | jq -r '.timestamp // empty')
  if [ -n "$TIMESTAMP" ]; then
    # ISO8601形式のタイムスタンプを指定タイムゾーンに変換
    ISO_TS=$(echo "$TIMESTAMP" | sed 's/\..*//')  # 小数点以下を削除

    if [[ "$OSTYPE" == "darwin"* ]]; then
      # macOS: エポック秒経由で変換
      EPOCH=$(date -j -u -f "%Y-%m-%dT%H:%M:%S" "$ISO_TS" "+%s" 2>/dev/null)
      if [ -n "$EPOCH" ]; then
        TIMESTAMP=$(TZ="$TZ_SETTING" date -r "$EPOCH" "+%Y-%m-%d %H:%M:%S" 2>/dev/null || echo "$ISO_TS" | sed 's/T/ /')
      else
        TIMESTAMP=$(echo "$ISO_TS" | sed 's/T/ ')
      fi
    else
      # Linux: -d でISO8601をパース（Zを付加してUTCであることを明示）
      TIMESTAMP=$(TZ="$TZ_SETTING" date -d "${ISO_TS}Z" "+%Y-%m-%d %H:%M:%S" 2>/dev/null || echo "$ISO_TS" | sed 's/T/ /')
    fi
  fi

  # タイムスタンプ表示用
  TS_DISPLAY=""
  [ -n "$TIMESTAMP" ] && TS_DISPLAY=" [$TIMESTAMP]"

  case "$TYPE" in
    user)
      # ユーザーメッセージの処理
      CONTENT_TYPE=$(echo "$line" | jq -r '.message.content | type')

      # ツール実行結果かどうかを判別（content配列内にtool_resultが含まれるか）
      IS_TOOL_RESULT="no"
      if [ "$CONTENT_TYPE" = "array" ]; then
        HAS_TOOL_RESULT=$(echo "$line" | jq -r '[.message.content[].type] | index("tool_result") // empty')
        if [ -n "$HAS_TOOL_RESULT" ]; then
          IS_TOOL_RESULT="yes"
        fi
      fi

      if [ "$IS_TOOL_RESULT" = "yes" ]; then
        # ツール実行結果として出力
        echo "" >> "$OUTPUT_FILE"
        echo "## 🔧 Tool Result${TS_DISPLAY}" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
      else
        # 通常のユーザー入力として出力
        echo "" >> "$OUTPUT_FILE"
        echo "## 👤 User${TS_DISPLAY}" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
      fi

      # message.contentの処理（string または array）
      if [ "$CONTENT_TYPE" = "string" ]; then
        # string型の場合
        CONTENT=$(echo "$line" | jq -r '.message.content')
        LINE_COUNT=$(echo "$CONTENT" | wc -l | tr -d ' ')

        if [ "$LINE_COUNT" -ge 10 ]; then
          # 10行以上の場合は折りたたむ
          FIRST_LINE=$(echo "$CONTENT" | head -1)
          # 120文字で切り詰め
          if [ ${#FIRST_LINE} -gt 120 ]; then
            SUMMARY="${FIRST_LINE:0:120}..."
          else
            SUMMARY="$FIRST_LINE"
          fi

          echo "<details>" >> "$OUTPUT_FILE"
          echo "<summary>${SUMMARY}</summary>" >> "$OUTPUT_FILE"
          echo "" >> "$OUTPUT_FILE"
          echo "$CONTENT" >> "$OUTPUT_FILE"
          echo "" >> "$OUTPUT_FILE"
          echo "</details>" >> "$OUTPUT_FILE"
        else
          # 10行未満はそのまま表示
          echo "$CONTENT" >> "$OUTPUT_FILE"
        fi
        echo "" >> "$OUTPUT_FILE"
      elif [ "$CONTENT_TYPE" = "array" ]; then
        # array型の場合、各要素を処理
        CONTENT_LENGTH=$(echo "$line" | jq '.message.content | length')

        for ((i=0; i<CONTENT_LENGTH; i++)); do
          ELEM_TYPE=$(echo "$line" | jq -r ".message.content[$i].type // empty")

          case "$ELEM_TYPE" in
            text)
              TEXT_CONTENT=$(echo "$line" | jq -r ".message.content[$i].text")
              TEXT_LINE_COUNT=$(echo "$TEXT_CONTENT" | wc -l | tr -d ' ')

              if [ "$TEXT_LINE_COUNT" -ge 10 ]; then
                # 10行以上の場合は折りたたむ
                FIRST_LINE=$(echo "$TEXT_CONTENT" | head -1)
                # 120文字で切り詰め
                if [ ${#FIRST_LINE} -gt 120 ]; then
                  SUMMARY="${FIRST_LINE:0:120}..."
                else
                  SUMMARY="$FIRST_LINE"
                fi

                echo "<details>" >> "$OUTPUT_FILE"
                echo "<summary>${SUMMARY}</summary>" >> "$OUTPUT_FILE"
                echo "" >> "$OUTPUT_FILE"
                echo "$TEXT_CONTENT" >> "$OUTPUT_FILE"
                echo "" >> "$OUTPUT_FILE"
                echo "</details>" >> "$OUTPUT_FILE"
              else
                # 10行未満はそのまま表示
                echo "$TEXT_CONTENT" >> "$OUTPUT_FILE"
              fi
              echo "" >> "$OUTPUT_FILE"
              ;;
            tool_result)
              # ツール結果（user message内）
              TOOL_USE_ID=$(echo "$line" | jq -r ".message.content[$i].tool_use_id // empty")
              IS_ERROR=$(echo "$line" | jq -r ".message.content[$i].is_error // false")

              echo "" >> "$OUTPUT_FILE"
              echo "### 🔧 Tool Result" >> "$OUTPUT_FILE"
              [ -n "$TOOL_USE_ID" ] && echo "Tool Use ID: \`$TOOL_USE_ID\`" >> "$OUTPUT_FILE"
              [ "$IS_ERROR" = "true" ] && echo "**Error**: true" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo '```' >> "$OUTPUT_FILE"
              echo "$line" | jq -r ".message.content[$i].content // empty" >> "$OUTPUT_FILE"
              echo '```' >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
            *)
              # 未知のcontent type → 生JSON出力
              echo "" >> "$OUTPUT_FILE"
              echo "### Unknown Content Type: $ELEM_TYPE" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo '```json' >> "$OUTPUT_FILE"
              echo "$line" | jq ".message.content[$i]" >> "$OUTPUT_FILE"
              echo '```' >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
          esac
        done
      fi

      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;

    assistant)
      # アシスタントメッセージの処理
      echo "" >> "$OUTPUT_FILE"
      echo "## 🤖 Assistant${TS_DISPLAY}" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"

      # message.contentの処理（string または array）
      CONTENT_TYPE=$(echo "$line" | jq -r '.message.content | type')

      if [ "$CONTENT_TYPE" = "string" ]; then
        # string型の場合
        echo "$line" | jq -r '.message.content' >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
      elif [ "$CONTENT_TYPE" = "array" ]; then
        # array型の場合、各要素を処理
        CONTENT_LENGTH=$(echo "$line" | jq '.message.content | length')

        for ((i=0; i<CONTENT_LENGTH; i++)); do
          ELEM_TYPE=$(echo "$line" | jq -r ".message.content[$i].type // empty")

          case "$ELEM_TYPE" in
            thinking)
              echo "" >> "$OUTPUT_FILE"
              echo "### 💭 Thinking" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo "$line" | jq -r ".message.content[$i].thinking" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
            text)
              echo "" >> "$OUTPUT_FILE"
              echo "### 💬 Response" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo "$line" | jq -r ".message.content[$i].text" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
            tool_use)
              TOOL_NAME=$(echo "$line" | jq -r ".message.content[$i].name // empty")
              TOOL_ID=$(echo "$line" | jq -r ".message.content[$i].id // empty")

              echo "" >> "$OUTPUT_FILE"
              echo "### 🛠️ Tool Use: $TOOL_NAME" >> "$OUTPUT_FILE"
              [ -n "$TOOL_ID" ] && echo "Tool ID: \`$TOOL_ID\`" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo '```json' >> "$OUTPUT_FILE"
              echo "$line" | jq ".message.content[$i].input" >> "$OUTPUT_FILE"
              echo '```' >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
            *)
              # 未知のcontent type → 生JSON出力
              echo "" >> "$OUTPUT_FILE"
              echo "### Unknown Content Type: $ELEM_TYPE" >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              echo '```json' >> "$OUTPUT_FILE"
              echo "$line" | jq ".message.content[$i]" >> "$OUTPUT_FILE"
              echo '```' >> "$OUTPUT_FILE"
              echo "" >> "$OUTPUT_FILE"
              ;;
          esac
        done
      fi

      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;

    progress)
      # 進捗情報の処理
      echo "" >> "$OUTPUT_FILE"
      echo "## 📊 Progress${TS_DISPLAY}" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"

      # data内の主要フィールドを抽出
      PROGRESS_TYPE=$(echo "$line" | jq -r '.data.type // empty')

      if [ -n "$PROGRESS_TYPE" ]; then
        echo "Type: \`$PROGRESS_TYPE\`" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"

        # progress typeごとの詳細情報
        case "$PROGRESS_TYPE" in
          hook_progress)
            COMMAND=$(echo "$line" | jq -r '.data.command // empty')
            HOOK_EVENT=$(echo "$line" | jq -r '.data.hookEvent // empty')
            [ -n "$COMMAND" ] && echo "Command: \`$COMMAND\`" >> "$OUTPUT_FILE"
            [ -n "$HOOK_EVENT" ] && echo "Hook Event: \`$HOOK_EVENT\`" >> "$OUTPUT_FILE"
            ;;
          agent_progress)
            AGENT_ID=$(echo "$line" | jq -r '.data.agentId // empty')
            PROMPT=$(echo "$line" | jq -r '.data.prompt // empty')
            [ -n "$AGENT_ID" ] && echo "Agent ID: \`$AGENT_ID\`" >> "$OUTPUT_FILE"
            if [ -n "$PROMPT" ] && [ ${#PROMPT} -le 100 ]; then
              echo "Prompt: \`$PROMPT\`" >> "$OUTPUT_FILE"
            fi
            ;;
        esac

        echo "" >> "$OUTPUT_FILE"
      fi

      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;

    system)
      # システムメッセージの処理
      echo "" >> "$OUTPUT_FILE"
      echo "## ⚙️ System${TS_DISPLAY}" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"

      # subtype フィールドを抽出
      SUBTYPE=$(echo "$line" | jq -r '.subtype // empty')

      if [ -n "$SUBTYPE" ]; then
        echo "Subtype: \`$SUBTYPE\`" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"

        # subtype ごとの詳細情報
        case "$SUBTYPE" in
          stop_hook_summary)
            HOOK_COUNT=$(echo "$line" | jq -r '.hookCount // 0')
            echo "Hook Count: $HOOK_COUNT" >> "$OUTPUT_FILE"
            echo "" >> "$OUTPUT_FILE"

            # hook_infosを一覧表示
            HOOK_INFOS=$(echo "$line" | jq -r '.hookInfos[]?.command // empty' 2>/dev/null)
            if [ -n "$HOOK_INFOS" ]; then
              echo "Hooks:" >> "$OUTPUT_FILE"
              echo "$HOOK_INFOS" | while read -r hook_cmd; do
                [ -n "$hook_cmd" ] && echo "- \`$hook_cmd\`" >> "$OUTPUT_FILE"
              done
              echo "" >> "$OUTPUT_FILE"
            fi
            ;;
        esac
      fi

      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;

    file-history-snapshot)
      # ファイル履歴スナップショットの処理
      echo "" >> "$OUTPUT_FILE"
      echo "## 📁 File History Snapshot${TS_DISPLAY}" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"

      # ファイル数を表示
      FILE_COUNT=$(echo "$line" | jq '.files | length // 0')
      echo "Files: $FILE_COUNT" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"

      # ファイル一覧
      if [ "$FILE_COUNT" -gt 0 ]; then
        echo "### Files" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
        echo "$line" | jq -r '.files[] | "- `\(.path)` (\(.lines) lines)"' >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
      fi

      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;

    *)
      # 未知のtype → 生JSON出力（情報欠損を防ぐ）
      TYPE_DISPLAY="${TYPE:-unknown}"
      echo "" >> "$OUTPUT_FILE"
      echo "## ${TYPE_DISPLAY}${TS_DISPLAY}" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      echo '```json' >> "$OUTPUT_FILE"
      echo "$line" | jq '.' >> "$OUTPUT_FILE"
      echo '```' >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      echo "---" >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      ;;
  esac

done < "$INPUT_FILE"

echo "変換完了: $OUTPUT_FILE (処理行数: $LINE_NUM)"
