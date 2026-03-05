# gspread-to-xlsx-downloader

## 概要

サービスアカウント認証を使い、Google Spreadsheet（URL/ID指定）をXLSXとしてダウンロード・保存するPythonスクリプトを `domain/tools/` に作成する。

## 背景・経緯

- スプシのデータをXLSXとして保存・管理したい
- 毎回ブラウザからエクスポートする手動作業を自動化したい

## 解決したい問題

- ブラウザからの手動エクスポートが煩雑（ファイル → ダウンロード → 形式選択の繰り返し）
- 複数のGoogle Spreadsheetを一括取得する手段がない

## 期待する成果

- URL/IDを渡すだけでXLSXを取得できる
- Googleドライブのフォルダ構成を保って `domain/raw-data/google-drive/spread-sheet/` 以下に保存できる
- AIワークフロー（masterdata-csv-to-xlsxスキル等）に組み込める

## ネクストアクション候補

既存の運営フローでは：
1. 人がgspread上の運営設計書に設計を手動入力
2. さらにマスタデータ投入シートへ手動入力
3. マスタデータ作成 → 実機確認 → QA

このスクリプトを作成後、既存gspreadの構成・内容を分析し、
マスタデータ投入シートへの入力やマスタデータ作成部分をAI生成に置き換えるための
**抜本的な運営フロー改善の材料**にする。

## 解決アイデア

- Google Drive APIを使ってPythonスクリプトで実装
- サービスアカウントのcredential.jsonで認証
- スプシURLまたはIDを複数指定可能なCLIとして実装
- 保存先: `domain/raw-data/google-drive/spread-sheet/` 以下にGoogleドライブのフォルダ構成を再現

## フォルダ構造

```
20260305_113933_gspread-to-xlsx-downloader/
├── README.md              # このファイル
├── next-actions.md        # ネクストアクション
└── CLAUDE.md              # 次回セッション用の引き継ぎコンテキスト
```

（必要に応じて追加されるフォルダ）
```
├── inputs/                # 入力データ・要件
├── outputs/               # 成果物
├── analysis/              # 分析結果
└── scripts/               # 実行スクリプト
```

## 作業ログ

- 2026-03-05 11:39: タスク作成

## 成果物

（今後追加）

## 関連ドキュメント

（必要に応じて追加）
