---
name: sdd-orchestrator
description: SDD(Spec-Driven Development)設計フローの全8段階を自動オーケストレート。ゲーム体験仕様書PDF、既存コード調査、プランナー確認、要件定義、API設計の各段階で専門サブエージェントを順次・並列実行し、最終的なサーバーAPI実装設計書を生成する。スタミナブースト、ガチャ、クエストなどの新機能SDD作成時に使用。
---

# SDD設計フローオーケストレーター

## 概要

新機能のSDD(Spec-Driven Development)作成において、8つの段階を自動実行するオーケストレータースキルです。

### 2つのフェーズ

**要件定義フェーズ（Stage 1-5）**
```
Stage 1: サーバー要件抽出
Stage 2: コード調査追記
Stage 3: サーバー仕様レビュー
Stage 4: ゲーム体験仕様確認結果まとめ（⚠️ 人間確認必須）
Stage 5: サーバーAPI要件書まとめ
```

**設計フェーズ（Stage 6-8）**
```
Stage 6: API実装全体概要設計（並列実行可能）
Stage 7: サーバーAPI設計書作成（並列実行可能）
Stage 8: サーバーAPI機能要件実装設計（並列実行可能）
```

## Instructions

### 基本的な使用方法

1. **フルフロー実行**
   ```bash
   /sdd:run-full-flow {機能名}
   ```

2. **実行される内容**
   - Stage 1-3を順次実行（要件抽出→コード調査→レビュー）
   - ⚠️ **Stage 4で一時停止**（プランナー確認待ち）
   - Stage 5を実行（要件書まとめ）
   - Stage 6-8を並列実行（API設計）

3. **所要時間**
   - 約25～30分（Stage 4の人間確認時間を除く）
   - 設計フェーズの並列実行により60%高速化

### 段階別実行（途中から再実行する場合）

各段階は個別に実行可能です：

```bash
# 要件定義フェーズ
/sdd:extract-server-requirements {機能名}        # Stage 1
/sdd:investigate-code-requirements {機能名}      # Stage 2
/sdd:review-server-spec {機能名}                 # Stage 3
/sdd:confirm-game-experience-spec {機能名}       # Stage 4
/sdd:finalize-server-requirements {機能名}       # Stage 5

# 設計フェーズ
/sdd:overview-api-design {機能名}                # Stage 6
/sdd:create-api-design {機能名}                  # Stage 7
/sdd:design-api-implementation {機能名}          # Stage 8
```

## 詳細ドキュメント

各段階の詳細、オーケストレーション戦略、エラーハンドリング、使用例については以下を参照：

- **[stage-details.md](stage-details.md)** - 各Stage（1-8）の詳細な責務・入出力仕様
- **[orchestration-patterns.md](orchestration-patterns.md)** - 実行フロー、並列実行戦略、トークン最適化
- **[examples.md](examples.md)** - 具体的な使用例（スタミナブースト等）
- **[troubleshooting.md](troubleshooting.md)** - エラーハンドリング、トラブルシューティング

## 重要な注意事項

- ⚠️ **Stage 4では必ず人間確認が必要です** - プランナーへの確認結果を準備してください
- ⚠️ 全段階の実行には25～30分かかります（Stage 4の人間確認時間を除く）
- ⚠️ 設計フェーズ（Stage 6-8）は並列実行により高速化されます
- ⚠️ 各段階の出力ファイルは `docs/sdd/features/{機能名}/` に保存されます

## 出力ファイル一覧

| Stage | 出力ファイル |
|-------|-------------|
| 1 | `docs/sdd/features/{機能名}/サーバー要件抽出.md` |
| 2 | `docs/sdd/features/{機能名}/サーバー要件_コード調査追記.md` |
| 3 | `docs/sdd/features/{機能名}/サーバー仕様レビュー.md` |
| 4 | `docs/sdd/features/{機能名}/ゲーム体験仕様確認結果まとめ.md` |
| 5 | `docs/sdd/features/{機能名}/サーバーAPI要件書.md` |
| 6 | `docs/sdd/features/{機能名}/API実装全体概要設計.md` |
| 7 | `docs/sdd/features/{機能名}/サーバーAPI設計書.md` |
| 8 | `docs/sdd/features/{機能名}/サーバーAPI機能要件実装設計.md` |

## チェックリスト

実行前に以下を確認：

- [ ] ゲーム体験仕様書PDFが利用可能
- [ ] glow-schemaリポジトリがクローン済み（`../glow-schema`）
- [ ] Stage 4でプランナー確認を実施できる準備がある

## 参考資料

各段階の詳細な実装ガイド：

**要件定義フェーズ：**
- [01_サーバー要件抽出_テンプレート.md](../../docs/sdd/prompts/01_サーバー要件抽出_テンプレート.md)
- [02_コード調査追記_テンプレート.md](../../docs/sdd/prompts/02_コード調査追記_テンプレート.md)
- [03_サーバー仕様レビュー_テンプレート.md](../../docs/sdd/prompts/03_サーバー仕様レビュー_テンプレート.md)
- [04_ゲーム体験仕様確認結果まとめ_テンプレート.md](../../docs/sdd/prompts/04_ゲーム体験仕様確認結果まとめ_テンプレート.md)
- [05_サーバーAPI要件書まとめ_テンプレート.md](../../docs/sdd/prompts/05_サーバーAPI要件書まとめ_テンプレート.md)

**設計フェーズ：**
- [06_API実装全体概要設計_テンプレート.md](../../docs/sdd/prompts/06_API実装全体概要設計_テンプレート.md)
- [07_サーバーAPI設計書作成_テンプレート.md](../../docs/sdd/prompts/07_サーバーAPI設計書作成_テンプレート.md)
- [08_サーバーAPI機能要件実装設計_テンプレート.md](../../docs/sdd/prompts/08_サーバーAPI機能要件実装設計_テンプレート.md)
