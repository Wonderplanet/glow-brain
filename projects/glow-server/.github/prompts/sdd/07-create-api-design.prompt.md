---
mode: 'agent'
tools: ['codebase']
description: 'サーバーAPI要件書を分析し、各APIの詳細仕様（リクエスト・レスポンス・エラー）を設計します'
---

# SDD サーバーAPI設計書作成

サーバーAPI要件書を分析し、実装に必要な具体的なAPI設計を行った「サーバーAPI設計書」を作成します。

## 使用方法

機能名を入力してください: ${input:featureName:機能名を入力（例: スタミナブースト）}

## 処理ステップ

1. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/07_サーバーAPI設計書作成_テンプレート.md` を読み込む

2. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を「${input:featureName}」に置換

3. **プロンプトの実行**
   - 置換後の内容に従ってサーバーAPI設計書を作成
   - 各APIのリクエスト・レスポンス仕様を設計
   - エラーハンドリングを設計

## 前提条件

- `docs/sdd/features/${input:featureName}/05_サーバーAPI要件書.md` が存在すること
- `docs/sdd/templates/API設計書.md` テンプレートファイルが存在すること

## 出力

- `docs/sdd/features/${input:featureName}/07_サーバーAPI設計書.md` にAPI設計書が出力されます

## 備考

このステップは設計フェーズの一部であり、Stage 6, 8と**並列実行可能**です。
