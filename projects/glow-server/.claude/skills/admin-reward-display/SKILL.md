---
name: "Implementing Admin Reward Display"
description: 管理ツール(admin)のテーブル表示やフォームで報酬情報を扱う実装が必要な時に使用。RewardInfoGetTraitを使った報酬情報の取得と表示を、ページネーションテーブル・詳細ページ内テーブル・フォーム入力の3パターンで正確に実装し、N+1問題を回避する。
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
