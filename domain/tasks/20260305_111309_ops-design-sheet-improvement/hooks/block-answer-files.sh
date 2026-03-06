#!/bin/bash
# 検証モード: 答えファイルへの Bash アクセスをブロック
INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

BLOCKED_PATTERNS=(
  "domain/raw-data/masterdata"
  "projects/glow-masterdata"
)

for pattern in "${BLOCKED_PATTERNS[@]}"; do
  if echo "$COMMAND" | grep -q "$pattern"; then
    echo "❌ 検証モード: 答えファイルへのアクセスはブロックされています（$pattern）" >&2
    exit 2  # exit 2 = ブロック
  fi
done

exit 0
