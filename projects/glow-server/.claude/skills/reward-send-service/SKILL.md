---
name: "Using Reward Send Service"
description: glow-serverで報酬送付機能を実装する際に使用。RewardDelegator経由でRewardSendServiceを使って報酬を配布する方法、RewardSendPolicyによるエラーハンドリング戦略、各報酬タイプのSendService実装パターン、テスト実装方法を提供。
---

# Reward Send Service の使い方

glow-serverでRewardDelegatorとRewardSendServiceを使った報酬送付機能の実装ガイド。

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
- **[guides/reward-send-policy.md](guides/reward-send-policy.md)** - ポリシーの使い方
- **[examples/error-handling.md](examples/error-handling.md)** - エラーハンドリング実装例

### 4. 各報酬タイプのSendServiceを理解する

必要に応じて新しい報酬タイプのSendServiceを実装する。

参照: **[guides/reward-types.md](guides/reward-types.md)**

### 5. テストを実装する

RewardSendServiceを使った機能のテストを実装する。

参照: **[examples/testing.md](examples/testing.md)**

## 参照ドキュメント

- **[architecture.md](architecture.md)** - システムアーキテクチャと設計思想
- **[guides/reward-send-policy.md](guides/reward-send-policy.md)** - RewardSendPolicyの使い方
- **[guides/reward-types.md](guides/reward-types.md)** - 各報酬タイプとSendServiceの実装
- **[examples/basic-usage.md](examples/basic-usage.md)** - 基本的な使い方の実例
- **[examples/error-handling.md](examples/error-handling.md)** - エラーハンドリングの実例
- **[examples/testing.md](examples/testing.md)** - テスト実装の実例
