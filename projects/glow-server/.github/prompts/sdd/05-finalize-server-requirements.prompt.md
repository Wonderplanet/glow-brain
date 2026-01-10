---
mode: 'agent'
tools: ['codebase']
description: 'これまでの全ドキュメントを統合し、完全なサーバーAPI要件書を作成します'
---

# SDD サーバーAPI要件書まとめ

これまでのステップで作成された全てのドキュメントを統合し、サーバーAPI側で実現すべき要件を完全な形でまとめた「サーバーAPI要件書」を作成します。

## 使用方法

機能名を入力してください: ${input:featureName:機能名を入力（例: スタミナブースト）}

## 処理ステップ

1. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/05_サーバーAPI要件書まとめ_テンプレート.md` を読み込む

2. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を「${input:featureName}」に置換

3. **プロンプトの実行**
   - 置換後の内容に従ってサーバーAPI要件書を作成

## 前提条件

以下の4つのドキュメントが全て存在すること:

1. `docs/sdd/features/${input:featureName}/01_サーバー要件抽出.md`
2. `docs/sdd/features/${input:featureName}/02_サーバー要件_コード調査追記.md`
3. `docs/sdd/features/${input:featureName}/03_サーバー仕様レビュー.md`
4. `docs/sdd/features/${input:featureName}/04_ゲーム体験仕様確認結果まとめ.md`

## 出力

- `docs/sdd/features/${input:featureName}/05_サーバーAPI要件書.md` にサーバーAPI要件書が出力されます
- このドキュメントで**要件定義フェーズが完了**します
