---
mode: 'agent'
tools: ['codebase']
description: '仕様書ベースの要件とコード調査ベースの要件を統合し、サーバー実装観点で仕様の詳細化・曖昧さの評価を行います'
---

# SDD サーバー仕様レビュー

仕様書ベースの要件とコード調査ベースの要件を統合し、サーバー実装観点で仕様の詳細化・曖昧さの評価を行います。

## 使用方法

機能名を入力してください: ${input:featureName:機能名を入力（例: スタミナブースト）}

## 処理ステップ

1. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/03_サーバー仕様レビュー_テンプレート.md` を読み込む

2. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を「${input:featureName}」に置換

3. **プロンプトの実行**
   - 置換後の内容に従って仕様レビューを実施
   - 曖昧さ・不明点の洗い出しとプランナー確認項目の抽出

## 前提条件

- `docs/sdd/features/${input:featureName}/01_サーバー要件抽出.md` が存在すること
- `docs/sdd/features/${input:featureName}/02_サーバー要件_コード調査追記.md` が存在すること
  - 先に `/01-extract-server-requirements` と `/02-investigate-code-requirements` を実行済みであること

## 出力

- `docs/sdd/features/${input:featureName}/03_サーバー仕様レビュー.md` にサーバー実装観点の仕様レビューレポートが出力されます
- レポートにはプランナーへの確認項目リストが含まれます
