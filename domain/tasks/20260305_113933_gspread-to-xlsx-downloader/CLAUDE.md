# CLAUDE.md - gspread-to-xlsx-downloader

このファイルはClaude Codeがこのタスクフォルダで作業する際の引き継ぎコンテキストです。
新しい会話を開始した際は、まずこのファイルを読んでタスクの状態を把握してください。

---

## タスク概要

**タスク名**: gspread-to-xlsx-downloader
**作成日**: 2026-03-05 11:39
**フォルダ**: `domain/tasks/20260305_113933_gspread-to-xlsx-downloader`

サービスアカウント認証を使い、Google Spreadsheet（URL/ID指定）をXLSXとしてダウンロード・保存するPythonスクリプトを `domain/tools/` に作成する。

---

## 背景・目的

### 背景・経緯
- スプシのデータをXLSXとして保存・管理したい
- 毎回ブラウザからエクスポートする手動作業を自動化したい

### 解決したい問題
- ブラウザからの手動エクスポートが煩雑（ファイル → ダウンロード → 形式選択の繰り返し）
- 複数のGoogle Spreadsheetを一括取得する手段がない

### 期待する成果
- URL/IDを渡すだけでXLSXを取得できる
- Googleドライブのフォルダ構成を保って `domain/raw-data/google-drive/spread-sheet/` 以下に保存できる
- AIワークフロー（masterdata-csv-to-xlsxスキル等）に組み込める

### ネクストアクション候補
既存の運営フローでは：
1. 人がgspread上の運営設計書に設計を手動入力
2. さらにマスタデータ投入シートへ手動入力
3. マスタデータ作成 → 実機確認 → QA

このスクリプトを作成後、既存gspreadの構成・内容を分析し、
マスタデータ投入シートへの入力やマスタデータ作成部分をAI生成に置き換えるための
**抜本的な運営フロー改善の材料**にする。

### 解決アイデア
- Google Drive APIを使ってPythonスクリプトで実装
- サービスアカウントのcredential.jsonで認証
- スプシURLまたはIDを複数指定可能なCLIとして実装
- 保存先: `domain/raw-data/google-drive/spread-sheet/` 以下にGoogleドライブのフォルダ構成を再現

---

## フォルダ構造

```
domain/tasks/20260305_113933_gspread-to-xlsx-downloader/
├── README.md              # タスク概要・目的・成果定義
├── next-actions.md        # 次にやること（チェックリスト形式）
├── CLAUDE.md              # このファイル（Claudeへの引き継ぎ）
（タスクの内容に応じて追加）
├── inputs/                # 入力データ・要件
├── outputs/               # 成果物
├── analysis/              # 分析結果
└── scripts/               # 実行スクリプト
```

**実装先**: `domain/tools/gspread_to_xlsx.py`
**保存先**: `domain/raw-data/google-drive/spread-sheet/`

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
2. **成果物はこのフォルダに集約する** - 作業結果は `domain/tasks/20260305_113933_gspread-to-xlsx-downloader/` 配下に保存
3. **next-actions.mdを常に最新に保つ** - 作業が進んだら完了チェックと次アクション更新
4. **rawフォルダのファイルは編集禁止** - `raw/` 配下は直接編集せず必ずコピーして使用
5. **README.mdに作業ログを追記する** - 主要な変更・決定事項を「作業ログ」セクションに記録

---

## 引き継ぎ情報

（作業を進めるにつれて、重要な決定・発見・注意点をここに追記してください）
