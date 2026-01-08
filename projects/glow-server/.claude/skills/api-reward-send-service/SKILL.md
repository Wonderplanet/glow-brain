---
name: api-reward-send-service
description: |
  glow-server APIの報酬配布システム実装をRewardDelegatorとRewardSendServiceを使って行う。以下の場合に使用: (1) ユーザーへの報酬配布 (ガチャ、ミッション、イベント等)、(2) 報酬追加・送付・取得フローの実装 (addReward/addRewards, sendRewards, getSentRewards)、(3) 新しい報酬リソースタイプの追加 (アイテム、キャラクター、通貨、スタミナ等)、(4) エラーハンドリング用のRewardSendPolicyカスタマイズ、(5) UnreceivedRewardReasonの調整、(6) 特定の報酬タイプ向けRewardSendServiceパターンの実装、(7) 報酬機能のテスト実装。報酬システムのアーキテクチャと実装パターンの包括的なガイダンスを提供。 (project)
---

# API Reward Send Service の使い方

glow-server APIでRewardDelegatorとRewardSendServiceを使った報酬送付機能の包括的な実装ガイド。

## Instructions

### 1. アーキテクチャを理解する

RewardDelegator、RewardManager、RewardSendServiceの関係性と設計思想を理解する。

参照: **[architecture.md](architecture.md)**

### 2. 報酬を追加して送付する

基本的な報酬送付フローを実装する：
1. RewardDelegatorに報酬を追加（`addReward` / `addRewards`）
2. 報酬を送付（`sendRewards`）
3. 送付済み報酬を取得（`getSentRewards`）

参照: **[examples/basic-usage.md](examples/basic-usage.md)**

### 3. エラーハンドリングとポリシーを設定する

RewardSendPolicyを使ってリソース上限超過時の動作を制御する。

参照:
- **[guides/reward-send-policy.md](guides/reward-send-policy.md)** - ポリシーの使い方とカスタマイズ
- **[examples/error-handling.md](examples/error-handling.md)** - エラーハンドリング実装例

### 4. 新しい配布リソースを追加する

新しい報酬タイプやリソースを追加する際の手順を確認する。

参照: **[guides/add-new-resource.md](guides/add-new-resource.md)**

### 5. UnreceivedRewardReasonを調整する

報酬が受け取れなかった理由の種類と調整方法を理解する。

参照: **[guides/unreceived-reward-reason.md](guides/unreceived-reward-reason.md)**

### 6. 各報酬タイプのSendServiceを理解する

既存の報酬タイプのSendService実装パターンと、新規追加方法を確認する。

参照: **[guides/reward-types.md](guides/reward-types.md)**

### 7. テストを実装する

RewardSendServiceを使った機能のテストを実装する。

参照: **[examples/testing.md](examples/testing.md)**

## 参照ドキュメント

### アーキテクチャ
- **[architecture.md](architecture.md)** - システムアーキテクチャと設計思想

### ガイド
- **[guides/reward-send-policy.md](guides/reward-send-policy.md)** - RewardSendPolicyの使い方とカスタマイズ
- **[guides/reward-types.md](guides/reward-types.md)** - 各報酬タイプとSendServiceの実装
- **[guides/add-new-resource.md](guides/add-new-resource.md)** - 新しい配布リソースの追加方法
- **[guides/unreceived-reward-reason.md](guides/unreceived-reward-reason.md)** - UnreceivedRewardReasonの調整方法

### 実装例
- **[examples/basic-usage.md](examples/basic-usage.md)** - 基本的な使い方の実例
- **[examples/error-handling.md](examples/error-handling.md)** - エラーハンドリングの実例
- **[examples/testing.md](examples/testing.md)** - テスト実装の実例
