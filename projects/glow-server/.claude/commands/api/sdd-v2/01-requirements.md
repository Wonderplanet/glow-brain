---
name: sdd-v2:requirements
description: ゲーム体験仕様書PDFとコードベースを統合調査し、サーバー側で考慮すべき要件を包括的に抽出する。
argument-hint: {機能名}
tools: ["*"]
---

# SDD v2 要件調査

ゲーム体験仕様書（PDF）とコードベースを統合調査し、サーバー側で考慮すべき要件を包括的に抽出します。

## 使用方法

```
/api:sdd-v2:01-requirements {機能名}
```

例: `/api:sdd-v2:01-requirements 交換所`

## 実行内容

引数: $ARGUMENTS

専門エージェント「**sdd-v2-requirements-investigation**」に委譲して実行します。

エージェントは `api-sdd-v2-requirements-investigation` スキルを使用して作業を実施します。

## 前提条件

- `docs/sdd-v2/features/{機能名}/` ディレクトリが存在すること
- `docs/sdd-v2/features/{機能名}/ゲーム体験仕様書.pdf` が配置されていること

## 次のステップ

出力された `01_要件調査.md` を確認後、以下のコマンドで仕様確認を実行:

```
/api:sdd-v2:02-spec-confirm {機能名}
```
