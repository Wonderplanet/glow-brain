# MstIdleIncentive 詳細説明

> CSVパス: `projects/glow-masterdata/MstIdleIncentive.csv`

---

## 概要

`MstIdleIncentive` は**探索（放置報酬）機能の基本設定テーブル**。放置後に報酬を受け取れるようになるまでの時間・報酬増加間隔・最大放置時間・クイック獲得コスト・広告視聴設定などをグローバルに管理する。現在は1レコードのみ（`idle_incentive_1`）が有効。

### ゲームプレイへの影響

- **initial_reward_receive_minutes**: プレイヤーが放置を開始してから、最初に報酬を受け取れるようになるまでの待機時間（分）。
- **reward_increase_interval_minutes**: この分数ごとに報酬量が増加する。段階的に報酬が積み上がる仕組みを実現する。
- **max_idle_hours**: 報酬が積み上がる最大放置時間（時間）。これを超えて放置しても報酬は増えない。
- **required_quick_receive_diamond_amount**: プリズム（一次通貨）を消費してクイック獲得する場合の必要プリズム量。
- **max_daily_diamond_quick_receive_amount**: 1日にプリズムで何回クイック獲得できるかの上限。
- **max_daily_ad_quick_receive_amount**: 1日に広告視聴で何回クイック獲得できるかの上限。
- **ad_interval_seconds**: 広告視聴クイック獲得のクールダウン時間（秒）。
- **quick_idle_minutes**: クイック獲得を実行したときに換算される実質放置時間（分）。

### 関連テーブルとの構造図

```
MstIdleIncentive（探索基本設定）
  └─ 参照元: クライアント（探索機能全体に適用）

MstIdleIncentiveReward（ステージ進捗別の報酬設定）
  └─ mst_idle_incentive_id のような直接参照はなく、
     サーバー側ロジックで組み合わせて使用
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID）。現在 `idle_incentive_1` のみ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `asset_key` | varchar(255) | 不可 | - | 探索機能に関連するアセットキー |
| `initial_reward_receive_minutes` | int unsigned | 不可 | - | 最初に報酬を受け取れるまでの放置時間（分） |
| `reward_increase_interval_minutes` | int unsigned | 不可 | - | 報酬が増加する時間間隔（分） |
| `max_idle_hours` | int unsigned | 不可 | - | 最大放置時間（時間）。上限を超えても報酬は増えない |
| `required_quick_receive_diamond_amount` | int unsigned | 不可 | 0 | クイック獲得1回に必要なプリズム量 |
| `max_daily_diamond_quick_receive_amount` | int unsigned | 不可 | 0 | 1日のプリズムによるクイック獲得上限回数 |
| `max_daily_ad_quick_receive_amount` | int unsigned | 不可 | - | 1日の広告視聴によるクイック獲得上限回数 |
| `ad_interval_seconds` | int unsigned | 不可 | - | 広告視聴クイック獲得のクールダウン時間（秒） |
| `quick_idle_minutes` | int unsigned | 不可 | - | クイック獲得で換算される実質放置時間（分） |

---

## 他テーブルとの連携

| 連携先テーブル | 連携方法 | 説明 |
|-------------|---------|------|
| `mst_idle_incentive_rewards` | サーバー側ロジックで参照 | ステージ進捗別の具体的な報酬量 |
| `mst_idle_incentive_items` | サーバー側ロジックで参照 | 探索で獲得できるアイテム一覧 |

---

## 実データ例

**パターン1: 現行の探索基本設定（唯一のレコード）**
```
ENABLE: e
id: idle_incentive_1
release_key: 202509010
asset_key: NULL
initial_reward_receive_minutes: 10
reward_increase_interval_minutes: 10
max_idle_hours: 24
required_quick_receive_diamond_amount: 30
max_daily_diamond_quick_receive_amount: 3
max_daily_ad_quick_receive_amount: 1
ad_interval_seconds: 60
quick_idle_minutes: 120
```
- 10分放置後に最初の報酬が受け取れる
- その後10分ごとに報酬が積み上がる
- 最大24時間（上限）
- プリズム30個で最大3回/日のクイック獲得可能
- 広告視聴は1日1回、60秒のクールダウンあり
- クイック獲得すると120分放置相当の報酬を即時受け取れる

---

## 設定時のポイント

1. **レコードは原則1件**: 現在の設計では全プレイヤーに共通の探索設定として1件のみ運用している。複数レコードの切り替え機能はクライアント・サーバーに実装されていない可能性がある。変更時は既存レコードを更新する。
2. **initial と interval の整合性**: `initial_reward_receive_minutes` と `reward_increase_interval_minutes` が同じ値になっていることが多い。最初の報酬タイミングと増加間隔を一致させると仕様が明快になる。
3. **quick_idle_minutes の設計**: クイック獲得で換算される時間（`quick_idle_minutes`）は `max_idle_hours * 60`（最大放置時間）以下に設定する。最大値を超えると意味がない。
4. **ad_interval_seconds の最低値**: 広告SDKの制限や体験上、あまり短すぎると連続視聴になる。実機で動作確認を行い適切な値を設定する。
5. **プリズム消費回数の上限**: `max_daily_diamond_quick_receive_amount` を変更するとプリズム消費の機会が変わり、収益に影響する。変更時はビジネス観点でのレビューが必要。
6. **asset_key**: 現状NULL設定で運用されている。将来的な機能拡張用の予備カラムの可能性がある。
