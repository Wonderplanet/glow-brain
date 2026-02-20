#!/bin/bash
# ID採番ルールCSVのパス設定

# プロジェクトルートからの相対パス
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../../.." && pwd)"
ID_RULE_CSV="${PROJECT_ROOT}/domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv"

# CSVファイルの存在確認
if [ ! -f "$ID_RULE_CSV" ]; then
    echo "エラー: ID割り振りルール.csvが見つかりません" >&2
    echo "パス: $ID_RULE_CSV" >&2
    exit 1
fi
