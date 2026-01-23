#!/bin/bash

# stdin から JSON を読み込み、cwd を抽出
input=$(cat)
cwd=$(echo "$input" | jq -r '.cwd // empty')

# cwd が取得できない場合は終了
if [ -z "$cwd" ]; then
    exit 0
fi

# git リポジトリでない場合は終了
if ! git -C "$cwd" rev-parse --git-dir > /dev/null 2>&1; then
    exit 0
fi

# 未ステージの変更ファイルを取得
modified_files=$(git -C "$cwd" diff --name-only 2>/dev/null)

# 変更がある場合のみ通知
if [ -n "$modified_files" ]; then
    echo "IMPORTANT: The following files have been manually edited since your last changes:"
    echo "$modified_files" | while read -r file; do
        echo "- $file"
    done
    echo ""
    echo "Please read these files to understand the manual adjustments before proceeding."
fi

exit 0
