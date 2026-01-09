---
name: admin-reward-display
description: glow-server管理ツールでRewardInfoGetTraitを使用したN+1問題を防ぐ報酬情報表示の実装スキル。以下の場合に使用:報酬情報の表示、RewardInfoGetTraitの使用、報酬テーブルの実装、N+1問題の回避。3つの表示パターン(一覧ページ用ページネーションテーブル、詳細ページテーブル、報酬選択UI用フォーム入力)に対応し、EagerロードによるN+1クエリ防止とマスタデータ結合を実現。「報酬情報を表示」「RewardInfoGetTraitを使用」「報酬テーブルを実装」「報酬を表示」「報酬のN+1を回避」などのリクエストで起動。
---

# Admin Reward Display Implementation

glow-server管理ツール(admin)における報酬情報の表示・入力機能を正確に実装します。

## Instructions

### 1. 要件確認とアーキテクチャ理解

1. ユーザーの実装要件を確認
2. **[architecture.md](architecture.md)** でアーキテクチャを理解

### 2. 実装パターンの選択

要件に応じて以下のいずれかを選択し、**正確に実装**:

- **一覧ページ** → **[pattern-paginated-tables.md](pattern-paginated-tables.md)**
- **詳細ページ** → **[pattern-detail-page-tables.md](pattern-detail-page-tables.md)**

### 3. 実装と検証

1. 選択したパターンのドキュメントに従って実装
2. 実装完了チェックリストで全項目を確認
3. ブラウザでの動作確認とN+1問題の確認

## 参照ドキュメント

- **[architecture.md](architecture.md)** - アーキテクチャと設計思想
- **[pattern-paginated-tables.md](pattern-paginated-tables.md)** - ページネーションテーブル
- **[pattern-detail-page-tables.md](pattern-detail-page-tables.md)** - 詳細ページ内テーブル
