---
name: api-mission-system
description: |
  glow-serverのミッションシステム実装を、トリガー追加、進捗更新、テストまでカバーする。以下の場合に使用: (1) 新しいミッショントリガーや達成条件の追加、(2) ミッション進捗更新ロジックの実装、(3) ミッション関連のテスト作成、(4) ミッションシステムのアーキテクチャ理解。全ミッションタイプをサポート: Achievement (永続)、Daily (毎日リセット)、Weekly (毎週リセット)、Beginner (日数で開放)、Event (イベント期間のみ)、EventDaily (イベント期間中の毎日リセット)、LimitedTerm (期間限定)。MissionTrigger、MissionManager、MissionDelegator を使った実装パターン、ベストプラクティス、テストガイダンスを提供。 (project)
---

# Mission System

このスキルは、glow-serverにおけるミッションシステムの実装全般をサポートします。

## このスキルが提供する情報

- ミッションシステムの全体像と仕組み
- 新規ミッショントリガー追加方法
- ミッション関連のテスト実装方法
- ミッション実装のパターンとベストプラクティス

## Instructions

ミッション関連の実装は以下の順序で進めてください：

### 1. 全体像の理解

まず、ミッションシステムの全体像を理解してください：
- [architecture.md](architecture.md) - ミッションシステムのアーキテクチャと仕組み

### 2. 実装タスクに応じた参照

#### 新規ミッショントリガーの追加
新しい達成条件（Criterion）を追加する場合：
- [trigger-implementation.md](trigger-implementation.md) - トリガー実装ガイド

#### ミッション関連のテスト実装
ミッション機能のテストを実装する場合：
- [test-implementation.md](test-implementation.md) - テスト実装ガイド

#### ミッションタイプ別の実装
特定のミッションタイプを実装する場合：
- [mission-types.md](mission-types.md) - ミッションタイプ別ガイド

### 3. ベストプラクティスの確認

実装前後で必ず確認：
- [best-practices.md](best-practices.md) - 実装パターンとベストプラクティス

## 参照ドキュメント一覧

- **[architecture.md](architecture.md)** - ミッションシステムの全体構造と動作フロー
- **[trigger-implementation.md](trigger-implementation.md)** - 新規トリガー追加の手順と実装例
- **[test-implementation.md](test-implementation.md)** - ミッションテストの実装方法
- **[mission-types.md](mission-types.md)** - ミッションタイプ別の特性と実装
- **[best-practices.md](best-practices.md)** - よくあるパターンとアンチパターン

## クイックリファレンス

### よく使うクラス

```php
// トリガー送信
MissionTrigger
MissionManager->addTrigger()
MissionDelegator->addTriggers()

// ミッション更新
MissionUpdateService->updateTriggeredMissions()
MissionUpdateHandleService->handleAllUpdateTriggeredMissions()

// テストサポート
TestMissionTrait
```

### ミッションタイプ

- **Achievement（達成）**: 永続的な実績
- **Daily（デイリー）**: 毎日リセット
- **Weekly（ウィークリー）**: 毎週リセット
- **Beginner（初心者）**: 日数で段階的に開放
- **Event（イベント）**: イベント期間中のみ
- **EventDaily（イベントデイリー）**: イベント期間中の毎日リセット
- **LimitedTerm（期間限定）**: 特定期間のみ

## トラブルシューティング

問題が発生した場合は、該当する参照ドキュメントの「トラブルシューティング」セクションを確認してください。
