---
mode: 'agent'
tools: ['codebase']
description: 'ゲーム体験仕様書（PDF）から、サーバー側で考慮すべき要件を抽出します'
---

# SDD サーバー要件抽出

ゲーム体験仕様書（PDF）から、サーバー側で考慮すべき要件を抽出します。

## 使用方法

機能名を入力してください: ${input:featureName:機能名を入力（例: スタミナブースト）}

## 処理ステップ

1. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/01_サーバー要件抽出_テンプレート.md` を読み込む

2. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を「${input:featureName}」に置換

3. **プロンプトの実行**
   - 置換後の内容に従ってサーバー要件を抽出
   - `docs/sdd/features/${input:featureName}/ゲーム体験仕様書.pdf` を分析

## 前提条件

- `docs/sdd/features/${input:featureName}/` ディレクトリが存在すること
- `docs/sdd/features/${input:featureName}/ゲーム体験仕様書.pdf` が配置されていること

## 出力

- `docs/sdd/features/${input:featureName}/01_サーバー要件抽出.md` にサーバー要件が出力されます
