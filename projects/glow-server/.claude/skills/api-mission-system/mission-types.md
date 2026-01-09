# ミッションタイプ別ガイド

各ミッションタイプの特性、実装パターン、注意点を解説します。

## 目次

1. [ミッションタイプ一覧](#ミッションタイプ一覧)
2. [Achievement（達成ミッション）](#achievement達成ミッション)
3. [Daily（デイリーミッション）](#dailyデイリーミッション)
4. [Weekly（ウィークリーミッション）](#weeklyウィークリーミッション)
5. [Beginner（初心者ミッション）](#beginner初心者ミッション)
6. [Event（イベントミッション）](#eventイベントミッション)
7. [EventDaily（イベントデイリー）](#eventdailyイベントデイリー)
8. [LimitedTerm（期間限定ミッション）](#limitedterm期間限定ミッション)

## ミッションタイプ一覧

| タイプ | 説明 | リセット | テーブル | 複合 | 依存 |
|--------|------|----------|----------|------|------|
| Achievement | 永続的な実績 | なし | usr_mission_normals | ○ | ○ |
| Daily | デイリーミッション | 毎日 | usr_mission_normals | ○ | × |
| Weekly | ウィークリーミッション | 毎週 | usr_mission_normals | ○ | × |
| Beginner | 初心者ミッション | なし | usr_mission_normals | ○ | ○ |
| Event | イベントミッション | イベント開始時 | usr_mission_events | ○ | ○ |
| EventDaily | イベントデイリー | 毎日 | usr_mission_events | ○ | × |
| LimitedTerm | 期間限定ミッション | 期間開始時 | usr_mission_limited_terms | ○ | ○ |

**凡例**:
- **複合**: 複合ミッション（他のミッションの達成数をカウント）に対応しているか
- **依存**: 依存関係（前のミッション達成で開放）に対応しているか

## Achievement（達成ミッション）

### 概要

永続的な実績ミッション。一度達成したらクリア状態が保持される。

**特徴**:
- リセットなし
- 複合ミッション対応
- 依存関係対応（段階的開放可能）

### マスタテーブル

**テーブル**: `mst_mission_achievements`

**主要カラム**:
```sql
id                          -- ミッションID
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
group_key                   -- 複合ミッション用グループキー
unlock_criterion_type       -- 開放条件タイプ
unlock_criterion_value      -- 開放条件値
unlock_criterion_count      -- 開放必要数
```

### 実装例

```php
// マスタデータ作成
MstMissionAchievement::factory()->create([
    'id' => 'achievement_001',
    'criterion_type' => MissionCriterionType::STAGE_CLEAR_COUNT->value,
    'criterion_value' => null,
    'criterion_count' => 100,
    'mst_mission_reward_group_id' => 'reward_001',
]);
```

### 依存関係の実装

```php
// 依存関係マスタ
MstMissionAchievementDependency::factory()->create([
    'group_id' => 'group_001',
    'mst_mission_achievement_id' => 'achievement_001',
    'unlock_order' => 1,
]);

MstMissionAchievementDependency::factory()->create([
    'group_id' => 'group_001',
    'mst_mission_achievement_id' => 'achievement_002',
    'unlock_order' => 2,  // achievement_001達成後に開放
]);
```

### 注意点

- 達成済みミッションは進捗が更新されない
- 依存関係はチェーン構造で段階的に開放される
- 複合ミッションは他のミッションの初回達成のみカウント

## Daily（デイリーミッション）

### 概要

毎日リセットされるミッション。

**特徴**:
- 毎日4:00（設定値）にリセット
- ボーナスポイント機能
- 複合ミッション対応

### マスタテーブル

**テーブル**: `mst_mission_dailies`

**主要カラム**:
```sql
id                          -- ミッションID
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
bonus_point                 -- ボーナスポイント
group_key                   -- 複合ミッション用グループキー
```

### リセット処理

```php
// MissionUpdateService::resetDailyUsrMissions()
public function resetDailyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
{
    foreach ($usrMissions as $usrMission) {
        if ($usrMission->getMissionType() === MissionType::DAILY->getIntValue()
            && $this->clock->isFirstToday($usrMission->getLatestResetAt())
        ) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

### ボーナスポイント

デイリーミッションのクリア数に応じてボーナス報酬を付与する機能。

**マスタテーブル**: `mst_mission_daily_bonuses`

```sql
mst_mission_daily_bonus_type  -- ボーナスタイプ（Daily/Total）
bonus_point                    -- 必要ポイント
mst_mission_reward_group_id    -- 報酬グループID
```

**実装**:
```php
// デイリーボーナス更新
MissionDailyBonusUpdateService::update(
    $usrUserId,
    MissionDailyBonusType::DAILY,
    $now
);
```

### 注意点

- リセット判定は`latest_reset_at`カラムで行う
- リセット時に進捗、達成状態、報酬受取状態がクリアされる
- ボーナスポイントは別途管理（`usr_mission_daily_bonuses`）

## Weekly（ウィークリーミッション）

### 概要

毎週リセットされるミッション。

**特徴**:
- 毎週月曜日4:00にリセット
- ボーナスポイント機能
- 基本的にDailyと同じ構造

### マスタテーブル

**テーブル**: `mst_mission_weeklies`

**主要カラム**: Dailyと同じ

### リセット処理

```php
// MissionUpdateService::resetWeeklyUsrMissions()
public function resetWeeklyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
{
    foreach ($usrMissions as $usrMission) {
        if ($usrMission->getMissionType() === MissionType::WEEKLY->getIntValue()
            && $this->clock->isFirstWeek($usrMission->getLatestResetAt())
        ) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

### 注意点

- 週の開始は月曜日4:00
- その他はDailyミッションと同じ

## Beginner（初心者ミッション）

### 概要

ミッション機能解放後、日数に応じて段階的に開放されるミッション。

**特徴**:
- 日数で段階的に開放（unlock_day）
- 複合ミッション対応
- 依存関係対応

### マスタテーブル

**テーブル**: `mst_mission_beginners`

**主要カラム**:
```sql
id                          -- ミッションID
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
bonus_point                 -- ボーナスポイント
group_key                   -- 複合ミッション用グループキー
unlock_day                  -- 開放日数（0から）
```

### 開放判定

```php
// MissionBeginnerService::getUnlockedMissions()
// ミッション機能解放日（mission_unlocked_at）からの経過日数で判定
$daysSinceUnlocked = $now->diffInDays($missionUnlockedAt);

// unlock_day <= daysSinceUnlocked のミッションが開放される
```

### 初心者ミッション状態管理

**テーブル**: `usr_mission_statuses`

```sql
usr_user_id
beginner_mission_status     -- 初心者ミッションの状態
mission_unlocked_at         -- ミッション機能解放日時
```

**ステータス**:
```php
enum MissionBeginnerStatus: int
{
    case COMPLETED = 0;         // 完了
    case HAS_LOCKED = 1;        // 未開放あり
    case ALL_UNLOCKED = 2;      // 全開放済み（未完了あり）
}
```

### 実装例

```php
// 0日目（ミッション解放時）
MstMissionBeginner::factory()->create([
    'id' => 'beginner_001',
    'unlock_day' => 0,
    'criterion_type' => MissionCriterionType::TUTORIAL_COMPLETED->value,
    'criterion_count' => 1,
]);

// 1日目
MstMissionBeginner::factory()->create([
    'id' => 'beginner_002',
    'unlock_day' => 1,
    'criterion_type' => MissionCriterionType::LOGIN_COUNT->value,
    'criterion_count' => 2,
]);

// 7日目
MstMissionBeginner::factory()->create([
    'id' => 'beginner_003',
    'unlock_day' => 7,
    'criterion_type' => MissionCriterionType::STAGE_CLEAR_COUNT->value,
    'criterion_count' => 50,
]);
```

### 注意点

- テスト時は`prepareUpdateBeginnerMission()`で初期化必須
- `mission_unlocked_at`が未設定だと進捗更新されない
- 依存関係と日数制限は併用可能（両方満たす必要がある）

## Event（イベントミッション）

### 概要

イベント期間中のみ有効なミッション。

**特徴**:
- イベント期間で有効無効が切り替わる
- イベント開始時にリセット
- 複合ミッション対応
- 依存関係対応

### マスタテーブル

**テーブル**: `mst_mission_events`

**主要カラム**:
```sql
id                          -- ミッションID
mst_event_id                -- イベントID（外部キー）
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
group_key                   -- 複合ミッション用グループキー
unlock_criterion_type       -- 開放条件タイプ
unlock_criterion_value      -- 開放条件値
unlock_criterion_count      -- 開放必要数
```

### イベント管理

**テーブル**: `mst_events`

```sql
id          -- イベントID
start_at    -- 開始日時
end_at      -- 終了日時
```

### 有効判定

```php
// MstEventRepository::getAllActiveEvents()
// 現在時刻がstart_at～end_atの範囲内のイベントを取得
$mstEvents = $this->mstEventRepository->getAllActiveEvents($now);
```

### リセット処理

```php
// MissionUpdateService::resetEventUsrMissions()
public function resetEventUsrMissions(
    Collection $usrMissions,
    Collection $mstMissions,
    CarbonImmutable $now,
): Collection {
    foreach ($usrMissions as $usrMission) {
        $mstMission = $mstMissions->get($usrMission->getMstMissionId());

        // イベント開始時刻より前のデータはリセット
        if ($usrMission->getLatestResetAt() < $mstMission->getStartAt()) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

### 注意点

- イベント期間外はミッション更新されない
- イベント再開時は自動的にリセットされる
- 依存関係はイベントミッション内でのみ有効

## EventDaily（イベントデイリー）

### 概要

イベント期間中、毎日リセットされるミッション。

**特徴**:
- イベント期間中のみ有効
- 毎日4:00にリセット
- ボーナスポイント機能
- 基本的にDailyとEventの組み合わせ

### マスタテーブル

**テーブル**: `mst_mission_event_dailies`

**主要カラム**:
```sql
id                          -- ミッションID
mst_event_id                -- イベントID
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
bonus_point                 -- ボーナスポイント
group_key                   -- 複合ミッション用グループキー
```

### リセット処理

```php
// MissionUpdateService::resetEventDailyUsrMissions()
public function resetEventDailyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
{
    foreach ($usrMissions as $usrMission) {
        if ($usrMission->getMissionType() === MissionType::EVENT_DAILY->getIntValue()
            && $this->clock->isFirstToday($usrMission->getLatestResetAt())
        ) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

### ボーナスポイント

**マスタテーブル**: `mst_mission_event_daily_bonuses`

Dailyボーナスと同じ構造だが、イベント単位で管理。

### 注意点

- イベント期間外は更新されない
- デイリーリセット + イベントリセット両方が適用される
- ボーナスポイントはイベント単位（`usr_mission_event_daily_bonuses`）

## LimitedTerm（期間限定ミッション）

### 概要

特定の期間のみ有効なミッション。イベントミッションと似ているが、より柔軟な期間設定が可能。

**特徴**:
- 期間開始時にリセット
- カテゴリ分類可能
- 複合ミッション対応
- 依存関係対応

### マスタテーブル

**テーブル**: `mst_mission_limited_terms`

**主要カラム**:
```sql
id                          -- ミッションID
category                    -- カテゴリ
start_at                    -- 開始日時
end_at                      -- 終了日時
criterion_type              -- 達成条件タイプ
criterion_value             -- 達成条件値
criterion_count             -- 達成必要数
mst_mission_reward_group_id -- 報酬グループID
group_key                   -- 複合ミッション用グループキー
unlock_criterion_type       -- 開放条件タイプ
unlock_criterion_value      -- 開放条件値
unlock_criterion_count      -- 開放必要数
```

### カテゴリ

```php
enum MissionLimitedTermCategory: int
{
    case GENERAL = 1;       // 汎用
    case CAMPAIGN = 2;      // キャンペーン
    case SEASONAL = 3;      // シーズナル
    // ...
}
```

### 有効判定

```php
// MissionUpdateEntityFactory::createLimitedTermMissionUpdateBundle()
// 現在時刻がstart_at～end_atの範囲内のミッションを取得
$mstMissions = $this->mstMissionLimitedTermRepository->getActiveMissions($now);
```

### リセット処理

```php
// MissionUpdateService::resetLimitedTermUsrMissions()
public function resetLimitedTermUsrMissions(
    Collection $usrMissions,
    Collection $mstMissions,
    CarbonImmutable $now,
): Collection {
    foreach ($usrMissions as $usrMission) {
        $mstMission = $mstMissions->get($usrMission->getMstMissionId());

        // 期間開始時刻より前のデータはリセット
        if ($usrMission->getLatestResetAt() < $mstMission->getStartAt()) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

### 注意点

- 期間外は更新されない
- 期間再設定時は自動的にリセット
- Eventミッションと似ているが、より柔軟な期間設定が可能

## ミッションタイプ選択ガイド

### Achievement vs Event vs LimitedTerm

| 要件 | 推奨タイプ |
|------|-----------|
| 永続的な実績 | Achievement |
| イベント連動 | Event |
| 期間限定（イベント以外） | LimitedTerm |
| キャンペーン | LimitedTerm |

### Daily vs Weekly vs EventDaily

| 要件 | 推奨タイプ |
|------|-----------|
| 毎日リセット | Daily |
| 毎週リセット | Weekly |
| イベント期間中の毎日 | EventDaily |
| ボーナスポイント必要 | Daily/Weekly/EventDaily |

### Beginner vs Achievement

| 要件 | 推奨タイプ |
|------|-----------|
| 初心者向け段階的開放 | Beginner |
| 段階的開放（依存関係） | Achievement |
| 長期的な実績 | Achievement |

## 共通機能

### 複合ミッション

すべてのミッションタイプで使用可能。

**達成条件**:
- `MissionCriterionType::MISSION_CLEAR_COUNT` - 全ミッションのクリア数
- `MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT` - 特定グループのクリア数

**実装**:
```php
// 全ミッションを10個クリア
MstMissionAchievement::factory()->create([
    'criterion_type' => MissionCriterionType::MISSION_CLEAR_COUNT->value,
    'criterion_value' => null,
    'criterion_count' => 10,
]);

// 特定グループのミッションを5個クリア
MstMissionDaily::factory()->create([
    'criterion_type' => MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value,
    'criterion_value' => 'daily_group_a',
    'criterion_count' => 5,
]);

// グループに属するミッション
MstMissionDaily::factory()->create([
    'id' => 'daily_001',
    'group_key' => 'daily_group_a',
    'criterion_type' => MissionCriterionType::STAGE_CLEAR_COUNT->value,
    'criterion_count' => 10,
]);
```

### 開放条件（unlock_criterion）

Achievement、Beginner、Event、LimitedTermで使用可能。

**実装**:
```php
// ユーザーレベル10で開放
MstMissionAchievement::factory()->create([
    'unlock_criterion_type' => MissionCriterionType::USER_LEVEL->value,
    'unlock_criterion_value' => null,
    'unlock_criterion_count' => 10,
]);
```
