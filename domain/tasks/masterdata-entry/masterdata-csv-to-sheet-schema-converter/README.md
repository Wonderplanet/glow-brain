# masterdata-csv-to-sheet-schema-converter

## 概要

テーブル別マスタCSVを検証・修正しながらsheet_schema形式のCSVに変換するスキルを作成する。変換前後それぞれのヘッダー整合性チェックと自動修正を含み、masterdata-csv-to-xlsxスキルとの一気通貫フロー構築を可能にする。

### スキルの役割

```
[入力] 作業中のマスタデータCSV（テーブル別）
  ↓ ① ヘッダーチェック・修正
      参照: projects/glow-masterdata/{Table}.csv の1行目
[検証済みマスタデータCSV]
  ↓ ② sheet_schema形式へ変換
[sheet_schema CSV]
  ↓ ③ ヘッダーチェック・修正
      参照: projects/glow-masterdata/sheet_schema/{Table}.csv の1-3行目
[検証済みsheet_schema CSV]
  ↓ masterdata-csv-to-xlsx スキルへ
```

### CSVフォーマット仕様

**マスタデータCSV（入力）:**
- 1行目のみ守ればよい: `ENABLE,id,col1,col2,...`
- 参照元: `projects/glow-masterdata/{TableName}.csv` の1行目

**sheet_schema CSV（出力）:**
- 1-3行目全て守る必要がある
  - 行1: `memo` (メモ行、残りは空)
  - 行2: `TABLE,{TableName},{TableName},...` (列ごとの所属テーブル名)
  - 行3: `ENABLE,id,col1,...,i18n_col.ja,...` (カラム名。I18n列も含む)
- 参照元: `projects/glow-masterdata/sheet_schema/{TableName}.csv` の1-3行目

## 背景・経緯

新規スキル式フローの構築。テーブルごとのマスタCSVからsheet_schema CSVへの変換を自動化するスキルが存在せず、手動での変換が必要だった。

## 解決したい問題

- 変換ツールがない: テーブル別マスタCSV → sheet_schema CSV の変換を自動でできるスキルが存在しない
- ヘッダー整合性チェックがない: 作業中CSVのヘッダーが正しいか確認する手段がない
- 手動修正コストが高い: 不整合があった場合の修正作業が手動で煩雑

## 期待する成果

以下3つを自動化するスキルの完成:
1. 入力マスタデータCSVのヘッダー検証・修正（参照: glow-masterdata直下）
2. マスタデータCSV → sheet_schema CSV への変換
3. 出力sheet_schema CSVのヘッダー検証・修正（参照: sheet_schema/）

## ネクストアクション候補

masterdata-csv-to-xlsxスキルと連携して一気通貫フローを作る。変換スキル完成後、CSV → sheet_schema変換 → XLSX生成 の完全自動フローを構築する。

## 解決アイデア

sheet_schemaのCSV構造を調査してから実装する。既存のsheet_schema CSVの構造・フォーマットを理解し、変換ロジックを設計・実装する。

## フォルダ構造

```
masterdata-entry/masterdata-csv-to-sheet-schema-converter/
├── README.md              # このファイル
└── next-actions.md        # ネクストアクション
```

（必要に応じて追加されるフォルダ）
```
├── inputs/                # 入力データ・要件
├── outputs/               # 成果物
├── analysis/              # 分析結果
└── scripts/               # 実行スクリプト
```

## 作業ログ

- 2026-02-20 18:30: タスク作成

## 成果物

（今後追加）

## 関連ドキュメント

- `.claude/skills/masterdata-csv-to-xlsx/` - 連携先のXLSX生成スキル
