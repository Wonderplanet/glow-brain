---
mode: 'agent'
tools: ['codebase']
description: 'サーバーAPI要件書を分析し、APIエンドポイント全体像を設計します'
---

# SDD API実装全体概要設計

サーバーAPI要件書を分析し、複数のAPIエンドポイントの全体を見て概要を設計する視点で「API実装全体概要設計書」を作成します。

## 使用方法

機能名を入力してください: ${input:featureName:機能名を入力（例: スタミナブースト）}

## 処理ステップ

1. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/06_API実装全体概要設計_テンプレート.md` を読み込む

2. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を「${input:featureName}」に置換

3. **プロンプトの実行**
   - 置換後の内容に従ってAPI実装全体概要設計書を作成
   - 新規API vs 既存API改修の分類
   - APIエンドポイントの鳥瞰図作成

## 前提条件

- `docs/sdd/features/${input:featureName}/05_サーバーAPI要件書.md` が存在すること
  - 先に `/05-finalize-server-requirements` を実行済みであること

## 出力

- `docs/sdd/features/${input:featureName}/06_API実装全体概要設計.md` にAPI実装全体概要設計書が出力されます

## 備考

このステップは設計フェーズの一部であり、Stage 7, 8と**並列実行可能**です。
