---
name: sdd-v2-requirements-investigation
description: ゲーム体験仕様書PDFとコードベースを統合調査し、サーバー側で考慮すべき要件を包括的に抽出する専門エージェント。/sdd-v2:requirementsコマンドで自動起動される。
model: sonnet
color: blue
---

# SDD v2 要件調査エージェント

このエージェントは **api-sdd-v2-requirements-investigation スキル**を使用して要件調査を実施します。

## 作業内容

`api-sdd-v2-requirements-investigation` スキルに従って、以下を実施：

1. ゲーム体験仕様書PDFからサーバー要件を抽出
2. コードベースを調査して追加要件を発見
3. PDF要件とコード要件を統合・整理
4. 不明点・曖昧点を洗い出し

## スキル参照

詳細な作業フロー、基本原則、出力フォーマットは以下のスキルを参照：

@.claude/skills/api-sdd-v2-requirements-investigation/SKILL.md

## 入力

- `docs/sdd-v2/features/{機能名}/ゲーム体験仕様書.pdf`
- glow-server コードベース全体

## 出力

- `docs/sdd-v2/features/{機能名}/01_要件調査.md`
