#!/bin/bash
# JSONL → Markdown 変換スクリプト
# 使用方法: ./jsonl-to-md.sh <input.jsonl> <output.md>

set -e

# タイムゾーン設定（環境変数SESSION_TIMEZONEで変更可能、デフォルトはJST）
TZ_SETTING="${SESSION_TIMEZONE:-Asia/Tokyo}"

# 折りたたみ閾値設定
FOLD_THRESHOLD_USER=10      # Userセクション用（既存動作維持）
FOLD_THRESHOLD_OTHER=300    # その他セクション用

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

# ================================================================================
# 共通関数
# ================================================================================

# タイプから設定を取得（POSIX互換）
# 戻り値: "ラベル|背景色"
get_section_config() {
  local type="$1"
  case "$type" in
    user)                  echo "👤 User|#e3f2fd" ;;
    assistant)             echo "🤖 Assistant|#e8f5e9" ;;
    progress)              echo "📊 Progress|#fff8e1" ;;
    system)                echo "⚙️ System|#f3e5f5" ;;
    file-history-snapshot) echo "📁 File History Snapshot|#fff3e0" ;;
    tool_result)           echo "🔧 Tool Result|#f5f5f5" ;;
    *)                     echo "${type}|#ffebee" ;;
  esac
}

# セクション開始
start_section() {
  local type="$1"
  local timestamp="$2"
  local outfile="$3"
  local config=$(get_section_config "$type")
  local label="${config%|*}"
  local bgcolor="${config#*|}"

  echo "" >> "$outfile"
  echo "<div style=\"background-color: ${bgcolor}; padding: 16px; margin: 8px 0; border-radius: 8px; color: #212121;\">" >> "$outfile"
  echo "" >> "$outfile"
  echo "## ${label}${timestamp}" >> "$outfile"
  echo "" >> "$outfile"
}

# セクション終了
end_section() {
  local outfile="$1"
  echo "---" >> "$outfile"
  echo "" >> "$outfile"
  echo "</div>" >> "$outfile"
  echo "" >> "$outfile"
}

# 折りたたみ出力関数
# 引数: $1=コンテンツ, $2=閾値, $3=出力ファイル
output_with_fold() {
  local content="$1"
  local threshold="$2"
  local outfile="$3"
  local line_count=$(echo "$content" | wc -l | tr -d ' ')

  if [ "$line_count" -ge "$threshold" ]; then
    # 折りたたみ出力
    local first_line=$(echo "$content" | head -1)
    local summary="${first_line:0:120}"
    [ ${#first_line} -gt 120 ] && summary="${summary}..."

    echo "<details>" >> "$outfile"
    echo "<summary>${summary}</summary>" >> "$outfile"
    echo "" >> "$outfile"
    echo "$content" >> "$outfile"
    echo "" >> "$outfile"
    echo "</details>" >> "$outfile"
  else
    echo "$content" >> "$outfile"
  fi
  echo "" >> "$outfile"
}

# ================================================================================
# コンテンツ処理関数
# ================================================================================

# Userコンテンツ処理
process_user_content() {
  local line="$1"
  local outfile="$2"
  local content_type=$(echo "$line" | jq -r '.message.content | type')

  if [ "$content_type" = "string" ]; then
    # string型の場合
    local content=$(echo "$line" | jq -r '.message.content')
    output_with_fold "$content" "$FOLD_THRESHOLD_USER" "$outfile"
  elif [ "$content_type" = "array" ]; then
    # array型の場合、各要素を処理
    local content_length=$(echo "$line" | jq '.message.content | length')

    for ((i=0; i<content_length; i++)); do
      local elem_type=$(echo "$line" | jq -r ".message.content[$i].type // empty")

      case "$elem_type" in
        text)
          local text_content=$(echo "$line" | jq -r ".message.content[$i].text")
          output_with_fold "$text_content" "$FOLD_THRESHOLD_USER" "$outfile"
          ;;
        tool_result)
          # ツール結果（user message内）
          local tool_use_id=$(echo "$line" | jq -r ".message.content[$i].tool_use_id // empty")
          local is_error=$(echo "$line" | jq -r ".message.content[$i].is_error // false")
          local tool_result_content=$(echo "$line" | jq -r ".message.content[$i].content // empty")
          local tool_result_lines=$(echo "$tool_result_content" | wc -l | tr -d ' ')

          echo "" >> "$outfile"
          echo "### 🔧 Tool Result" >> "$outfile"
          [ -n "$tool_use_id" ] && echo "Tool Use ID: \`$tool_use_id\`" >> "$outfile"
          [ "$is_error" = "true" ] && echo "**Error**: true" >> "$outfile"
          echo "" >> "$outfile"

          # 閾値以上なら折りたたむ
          if [ "$tool_result_lines" -ge "$FOLD_THRESHOLD_OTHER" ]; then
            echo "<details>" >> "$outfile"
            echo "<summary>Tool Result (${tool_result_lines} lines)</summary>" >> "$outfile"
            echo "" >> "$outfile"
            echo '```' >> "$outfile"
            echo "$tool_result_content" >> "$outfile"
            echo '```' >> "$outfile"
            echo "" >> "$outfile"
            echo "</details>" >> "$outfile"
          else
            echo '```' >> "$outfile"
            echo "$tool_result_content" >> "$outfile"
            echo '```' >> "$outfile"
          fi
          echo "" >> "$outfile"
          ;;
        *)
          # 未知のcontent type → 生JSON出力
          echo "" >> "$outfile"
          echo "### Unknown Content Type: $elem_type" >> "$outfile"
          echo "" >> "$outfile"
          echo '```json' >> "$outfile"
          echo "$line" | jq ".message.content[$i]" >> "$outfile"
          echo '```' >> "$outfile"
          echo "" >> "$outfile"
          ;;
      esac
    done
  fi
}

# Assistantコンテンツ処理
process_assistant_content() {
  local line="$1"
  local outfile="$2"
  local content_type=$(echo "$line" | jq -r '.message.content | type')

  if [ "$content_type" = "string" ]; then
    # string型の場合
    echo "$line" | jq -r '.message.content' >> "$outfile"
    echo "" >> "$outfile"
  elif [ "$content_type" = "array" ]; then
    # array型の場合、各要素を処理
    local content_length=$(echo "$line" | jq '.message.content | length')

    for ((i=0; i<content_length; i++)); do
      local elem_type=$(echo "$line" | jq -r ".message.content[$i].type // empty")

      case "$elem_type" in
        thinking)
          echo "" >> "$outfile"
          echo "### 💭 Thinking" >> "$outfile"
          echo "" >> "$outfile"
          local thinking_content=$(echo "$line" | jq -r ".message.content[$i].thinking")
          output_with_fold "$thinking_content" "$FOLD_THRESHOLD_OTHER" "$outfile"
          ;;
        text)
          echo "" >> "$outfile"
          echo "### 💬 Response" >> "$outfile"
          echo "" >> "$outfile"
          local text_content=$(echo "$line" | jq -r ".message.content[$i].text")
          output_with_fold "$text_content" "$FOLD_THRESHOLD_OTHER" "$outfile"
          ;;
        tool_use)
          local tool_name=$(echo "$line" | jq -r ".message.content[$i].name // empty")
          local tool_id=$(echo "$line" | jq -r ".message.content[$i].id // empty")
          local tool_input_json=$(echo "$line" | jq ".message.content[$i].input")
          local tool_input_lines=$(echo "$tool_input_json" | wc -l | tr -d ' ')

          echo "" >> "$outfile"
          echo "### 🛠️ Tool Use: $tool_name" >> "$outfile"
          [ -n "$tool_id" ] && echo "Tool ID: \`$tool_id\`" >> "$outfile"
          echo "" >> "$outfile"

          # JSON部分が閾値以上なら折りたたむ
          if [ "$tool_input_lines" -ge "$FOLD_THRESHOLD_OTHER" ]; then
            echo "<details>" >> "$outfile"
            echo "<summary>Tool Input (${tool_input_lines} lines)</summary>" >> "$outfile"
            echo "" >> "$outfile"
            echo '```json' >> "$outfile"
            echo "$tool_input_json" >> "$outfile"
            echo '```' >> "$outfile"
            echo "" >> "$outfile"
            echo "</details>" >> "$outfile"
          else
            echo '```json' >> "$outfile"
            echo "$tool_input_json" >> "$outfile"
            echo '```' >> "$outfile"
          fi
          echo "" >> "$outfile"
          ;;
        *)
          # 未知のcontent type → 生JSON出力
          echo "" >> "$outfile"
          echo "### Unknown Content Type: $elem_type" >> "$outfile"
          echo "" >> "$outfile"
          echo '```json' >> "$outfile"
          echo "$line" | jq ".message.content[$i]" >> "$outfile"
          echo '```' >> "$outfile"
          echo "" >> "$outfile"
          ;;
      esac
    done
  fi
}

# Progressコンテンツ処理
process_progress_content() {
  local line="$1"
  local outfile="$2"
  local progress_type=$(echo "$line" | jq -r '.data.type // empty')

  if [ -n "$progress_type" ]; then
    echo "Type: \`$progress_type\`" >> "$outfile"
    echo "" >> "$outfile"

    # progress typeごとの詳細情報
    case "$progress_type" in
      hook_progress)
        local command=$(echo "$line" | jq -r '.data.command // empty')
        local hook_event=$(echo "$line" | jq -r '.data.hookEvent // empty')
        [ -n "$command" ] && echo "Command: \`$command\`" >> "$outfile"
        [ -n "$hook_event" ] && echo "Hook Event: \`$hook_event\`" >> "$outfile"
        ;;
      agent_progress)
        local agent_id=$(echo "$line" | jq -r '.data.agentId // empty')
        local prompt=$(echo "$line" | jq -r '.data.prompt // empty')
        [ -n "$agent_id" ] && echo "Agent ID: \`$agent_id\`" >> "$outfile"
        if [ -n "$prompt" ] && [ ${#prompt} -le 100 ]; then
          echo "Prompt: \`$prompt\`" >> "$outfile"
        fi
        ;;
    esac

    echo "" >> "$outfile"
  fi
}

# Systemコンテンツ処理
process_system_content() {
  local line="$1"
  local outfile="$2"
  local subtype=$(echo "$line" | jq -r '.subtype // empty')

  if [ -n "$subtype" ]; then
    echo "Subtype: \`$subtype\`" >> "$outfile"
    echo "" >> "$outfile"

    # subtype ごとの詳細情報
    case "$subtype" in
      stop_hook_summary)
        local hook_count=$(echo "$line" | jq -r '.hookCount // 0')
        echo "Hook Count: $hook_count" >> "$outfile"
        echo "" >> "$outfile"

        # hook_infosを一覧表示
        local hook_infos=$(echo "$line" | jq -r '.hookInfos[]?.command // empty' 2>/dev/null)
        if [ -n "$hook_infos" ]; then
          echo "Hooks:" >> "$outfile"
          echo "$hook_infos" | while read -r hook_cmd; do
            [ -n "$hook_cmd" ] && echo "- \`$hook_cmd\`" >> "$outfile"
          done
          echo "" >> "$outfile"
        fi
        ;;
    esac
  fi
}

# File History Snapshotコンテンツ処理
process_file_history_content() {
  local line="$1"
  local outfile="$2"
  local file_count=$(echo "$line" | jq '.files | length // 0')

  echo "Files: $file_count" >> "$outfile"
  echo "" >> "$outfile"

  # ファイル一覧
  if [ "$file_count" -gt 0 ]; then
    echo "### Files" >> "$outfile"
    echo "" >> "$outfile"

    # ファイル数が多い場合は折りたたむ
    if [ "$file_count" -ge 100 ]; then
      echo "<details>" >> "$outfile"
      echo "<summary>${file_count} files (click to expand)</summary>" >> "$outfile"
      echo "" >> "$outfile"
      echo "$line" | jq -r '.files[] | "- `\(.path)` (\(.lines) lines)"' >> "$outfile"
      echo "" >> "$outfile"
      echo "</details>" >> "$outfile"
    else
      echo "$line" | jq -r '.files[] | "- `\(.path)` (\(.lines) lines)"' >> "$outfile"
    fi
    echo "" >> "$outfile"
  fi
}

# ================================================================================
# メイン処理
# ================================================================================

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

  # セクションタイプの決定（userの場合はtool_result判定）
  SECTION_TYPE="$TYPE"
  if [ "$TYPE" = "user" ]; then
    # ツール実行結果かどうかを判別
    CONTENT_TYPE=$(echo "$line" | jq -r '.message.content | type')
    if [ "$CONTENT_TYPE" = "array" ]; then
      HAS_TOOL_RESULT=$(echo "$line" | jq -r '[.message.content[].type] | index("tool_result") // empty')
      [ -n "$HAS_TOOL_RESULT" ] && SECTION_TYPE="tool_result"
    fi
  fi

  # タイプ別処理
  case "$TYPE" in
    user)
      start_section "$SECTION_TYPE" "$TS_DISPLAY" "$OUTPUT_FILE"
      process_user_content "$line" "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;

    assistant)
      start_section "assistant" "$TS_DISPLAY" "$OUTPUT_FILE"
      process_assistant_content "$line" "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;

    progress)
      start_section "progress" "$TS_DISPLAY" "$OUTPUT_FILE"
      process_progress_content "$line" "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;

    system)
      start_section "system" "$TS_DISPLAY" "$OUTPUT_FILE"
      process_system_content "$line" "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;

    file-history-snapshot)
      start_section "file-history-snapshot" "$TS_DISPLAY" "$OUTPUT_FILE"
      process_file_history_content "$line" "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;

    *)
      # 未知のtype → 生JSON出力
      TYPE_DISPLAY="${TYPE:-unknown}"
      start_section "$TYPE_DISPLAY" "$TS_DISPLAY" "$OUTPUT_FILE"
      echo '```json' >> "$OUTPUT_FILE"
      echo "$line" | jq '.' >> "$OUTPUT_FILE"
      echo '```' >> "$OUTPUT_FILE"
      echo "" >> "$OUTPUT_FILE"
      end_section "$OUTPUT_FILE"
      ;;
  esac

done < "$INPUT_FILE"

echo "変換完了: $OUTPUT_FILE (処理行数: $LINE_NUM)"
