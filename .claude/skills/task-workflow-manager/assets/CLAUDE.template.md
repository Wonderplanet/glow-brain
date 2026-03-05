# CLAUDE.md - {TASK_NAME}

このファイルはClaude Codeがこのタスクフォルダで作業する際の引き継ぎコンテキストです。
新しい会話を開始した際は、まずこのファイルを読んでタスクの状態を把握してください。

---

## タスク概要

**タスク名**: {TASK_NAME}
**作成日**: {TIMESTAMP}
**フォルダ**: `{TASK_FOLDER_PATH}`

{BRIEF_SUMMARY}

---

## 背景・目的

### 背景・経緯
{BACKGROUND}

### 解決したい問題
{PROBLEM}

### 期待する成果
{EXPECTED_OUTCOME}

### ネクストアクション候補
{NEXT_ACTION_IDEAS}

### 解決アイデア
{SOLUTION_IDEAS}

---

## フォルダ構造

```
{TASK_FOLDER_PATH}/
├── README.md              # タスク概要・目的・成果定義
├── next-actions.md        # 次にやること（チェックリスト形式）
├── CLAUDE.md              # このファイル（Claudeへの引き継ぎ）
（タスクの内容に応じて追加）
├── inputs/                # 入力データ・要件
├── outputs/               # 成果物
├── analysis/              # 分析結果
├── raw/                   # 生データ置き場（編集禁止）
└── scripts/               # 実行スクリプト
```

---

## GLOWプロジェクトの主要リソース

| リソース | パス |
|---------|------|
| DBスキーマ（テーブル定義・カラム型・enum値） | `projects/glow-server/api/database/schema/exports/master_tables_schema.json` |
| マスタデータCSV | `projects/glow-masterdata/*.csv` |
| サーバー実装（TypeScript） | `projects/glow-server/` |
| クライアント実装（Unity/C#） | `projects/glow-client/` |
| APIスキーマ定義 | `projects/glow-schema/` |

### テーブル命名規則

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| DBスキーマ | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| CSVファイル | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

---

## 作業ルール

1. **このフォルダを起点として作業する** - 新しい会話ではまずこのCLAUDE.mdを読む
2. **成果物はこのフォルダに集約する** - 作業結果は `{TASK_FOLDER_PATH}/` 配下に保存
3. **next-actions.mdを常に最新に保つ** - 作業が進んだら完了チェックと次アクション更新
4. **rawフォルダのファイルは編集禁止** - `raw/` 配下は直接編集せず必ずコピーして使用
5. **README.mdに作業ログを追記する** - 主要な変更・決定事項を「作業ログ」セクションに記録

---

## 引き継ぎ情報

（作業を進めるにつれて、重要な決定・発見・注意点をここに追記してください）
