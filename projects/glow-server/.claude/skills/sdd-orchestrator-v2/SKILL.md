---
name: sdd-orchestrator-v2
description: |
  PDF仕様書からAPI設計書を12～15分で効率的に生成する、効率化された4段階のSDD v2オーケストレーター。v1の8段階を4段階に統合して簡素化：(1) 要件調査（PDF抽出 + コード調査）、(2) 仕様確認（レビュー + プランナー確認、人間確認必須）、(3) API設計書作成（概要 + 詳細 + DB + 実装設計）、(4) API設計書レビュー（品質検証）。以下の場合に使用：(1) 新機能（ガチャ、クエスト、スタミナブースト、交換所等）の設計書を効率的に作成、(2) v1より高速な設計プロセス、(3) ユーザーが「SDD v2」「v2で設計書」「新機能設計v2」「API設計書作成v2」または明示的にv2ワークフローに言及した時。
---

# SDD v2 設計フローオーケストレーター

## 概要

新機能のSDD(Spec-Driven Development)作成において、4つの段階を自動実行するオーケストレータースキルです。

### 4段階フロー

```
【要件定義フェーズ】
Stage 1: 要件調査 (PDF抽出 + コード調査を統合)
    ↓
Stage 2: 仕様確認 (レビュー + プランナー確認を統合)
    ↓ ⚠️ 人間確認必須
【設計フェーズ】
Stage 3: API設計書作成 (概要 + 詳細 + DB + 実装設計を統合)
    ↓
Stage 3-2: API設計書レビュー (チェックリストに基づく品質検証)
```

## Instructions

### 基本的な使用方法

1. **フルフロー実行**
   ```bash
   /sdd-v2:run-full {機能名}
   ```

2. **実行される内容**
   - Stage 1を実行（要件調査：PDF抽出 + コード調査）
   - Stage 2を実行（仕様確認：レビュー + 確認項目リスト作成）
   - ⚠️ **Stage 2で一時停止**（プランナー確認待ち）
   - Stage 2を再実行（確認結果統合）
   - Stage 3を実行（API設計書作成）
   - Stage 3-2を実行（API設計書レビュー：チェックリストに基づく品質検証）

3. **所要時間**
   - 約12～15分（Stage 2の人間確認時間を除く）

### 段階別実行（途中から再実行する場合）

各段階は個別に実行可能です：

**スキル直接起動:**

```
api-sdd-v2-requirements-investigation   # Stage 1: 要件調査
api-sdd-v2-spec-confirmation           # Stage 2: 仕様確認
api-sdd-v2-api-design                  # Stage 3: API設計書作成
api-sdd-v2-api-design-review           # Stage 3-2: API設計書レビュー
```

**コマンド経由:**

```bash
/sdd-v2:requirements {機能名}   # Stage 1: 要件調査
/sdd-v2:spec-confirm {機能名}   # Stage 2: 仕様確認
/sdd-v2:api-design {機能名}     # Stage 3: API設計書作成
/sdd-v2:api-review {機能名}     # Stage 3-2: API設計書レビュー
```

## 詳細ドキュメント

各段階の詳細、オーケストレーション戦略、エラーハンドリング、使用例については以下を参照：

- **[stage-details.md](stage-details.md)** - 各Stage（1-3）の詳細な責務・入出力仕様
- **[orchestration-patterns.md](orchestration-patterns.md)** - 実行フロー、人間確認ポイント、最適化
- **[examples.md](examples.md)** - 具体的な使用例（交換所等）
- **[troubleshooting.md](troubleshooting.md)** - エラーハンドリング、トラブルシューティング

## 重要な注意事項

- ⚠️ **Stage 2では必ず人間確認が必要です** - プランナーへの確認結果を準備してください
- ⚠️ 全段階の実行には12～15分かかります（Stage 2の人間確認時間を除く）
- ⚠️ 各段階の出力ファイルは `docs/sdd-v2/features/{機能名}/` に保存されます

## 出力ファイル一覧

| Stage | 出力ファイル |
|-------|-------------|
| 1 | `docs/sdd-v2/features/{機能名}/01_要件調査.md` |
| 2 | `docs/sdd-v2/features/{機能名}/02_仕様確認.md` |
| 2 | `docs/sdd-v2/features/{機能名}/02_2_プランナー確認結果.md` |
| 3 | `docs/sdd-v2/features/{機能名}/03_API設計書.md` |
| 3-2 | `docs/sdd-v2/features/{機能名}/03_API設計書.md` (レビュー後更新) |

## チェックリスト

実行前に以下を確認：

- [ ] ゲーム体験仕様書PDFが `docs/sdd-v2/features/{機能名}/` に配置済み
- [ ] glow-schemaリポジトリがクローン済み（`../glow-schema`）
- [ ] Stage 2でプランナー確認を実施できる準備がある

## 参考資料

各段階のプロンプトテンプレート：

- [01_要件調査_テンプレート.md](../../../docs/sdd-v2/prompts/01_要件調査_テンプレート.md)
- [02_仕様確認_テンプレート.md](../../../docs/sdd-v2/prompts/02_仕様確認_テンプレート.md)
- [03_API設計書_テンプレート.md](../../../docs/sdd-v2/prompts/03_API設計書_テンプレート.md)
