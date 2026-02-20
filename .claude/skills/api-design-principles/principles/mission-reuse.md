# ミッション実装のコスト意識

## 原則

**新規でミッションを実装するコストは高すぎるので、既存を無理のない形で流用することをまず第一に考える。**

## 背景

ミッション機能の新規実装は、以下の理由から非常に高コストです:

### 実装コスト
- **サーバー側**: 達成条件判定ロジック、進捗管理、報酬付与、DB設計
- **クライアント側**: UI実装、演出、画面遷移、通知
- **マスタデータ**: テーブル設計、CSV作成、バリデーション

### 保守コスト
- 新しいバグの混入リスク
- 既存ミッションとの整合性確保
- 運営オペレーションの複雑化
- ドキュメント・テストの追加

### 運用コスト
- CS対応の増加（新しい不具合パターン）
- 監視・ログ解析の対象増加
- 施策設計時の選択肢増加による意思決定コスト

## ミッション分類の整理

GLOWにおけるミッションは、以下のように分類されます:

### 1. デイリーミッション (`MstMissionDaily`)
- **表示画面**: デイリーミッション画面
- **特徴**: 毎日リセット、固定の達成条件
- **識別データ**: なし（画面で一意に識別）
- **マスタテーブル**: `mst_mission_dailies`

### 2. ウィークリーミッション (`MstMissionWeekly`)
- **表示画面**: ウィークリーミッション画面
- **特徴**: 毎週リセット、固定の達成条件
- **識別データ**: なし（画面で一意に識別）
- **マスタテーブル**: `mst_mission_weeklies`

### 3. 達成ミッション (`MstMissionAchievement`)
- **表示画面**: 達成ミッション画面（常設）
- **特徴**: リセットなし、永続的、順次解放可能
- **識別データ**: なし（画面で一意に識別）
- **マスタテーブル**: `mst_mission_achievements`

### 4. ビギナーミッション (`MstMissionBeginner`)
- **表示画面**: ビギナーミッション画面
- **特徴**: 初心者向け、日数で順次解放
- **識別データ**: `unlockDay`（開始日からの経過日数）
- **マスタテーブル**: `mst_mission_beginners`

### 5. イベントミッション (`MstMissionEvent`)
- **表示画面**: イベント画面内
- **特徴**: イベントに紐づく、期間指定
- **識別データ**: **`mstEventId`（どのイベントに紐づくか）**
- **追加識別**: `eventCategory`（AdventBattle等）
- **マスタテーブル**: `mst_mission_events`

### 6. イベントデイリーミッション (`MstMissionEventDaily`)
- **表示画面**: イベント画面内
- **特徴**: イベント期間中、毎日リセット
- **識別データ**: **`mstEventId`（どのイベントに紐づくか）**
- **マスタテーブル**: `mst_mission_event_dailies`

### 7. 期間限定ミッション (`MstMissionLimitedTerm`)
- **表示画面**: 機能ごとに異なる（例: 降臨バトル画面）
- **特徴**: 期間指定、機能に紐づく、進行状況を`progressGroupKey`で管理
- **識別データ**: **`missionCategory`（AdventBattle等）+ `progressGroupKey`**
- **マスタテーブル**: `mst_mission_limited_terms`

## 流用戦略

上記の分類を踏まえ、以下の戦略で既存ミッションを流用します:

### 戦略1: イベントミッション・期間限定ミッションの流用を最優先

#### 1-A: イベントミッション (`MstMissionEvent`) の活用

イベントミッションは**`mstEventId`で機能を識別**できるため、新しいイベント機能でも流用しやすい設計です。

**構造**:
```
mst_mission_events
├─ id (ミッションID)
├─ mstEventId (どのイベントに紐づくか)
├─ criterionType (達成条件の種類)
├─ criterionValue (達成条件の値)
├─ criterionCount (達成条件の回数)
├─ eventCategory (AdventBattle等の機能種別)
└─ mstMissionRewardGroupId (報酬グループID)
```

**新機能での流用例**:
- 新イベント「降臨バトル」を実装する場合
- マスタデータ: `MstMissionEvent`（既存）
- 識別データ: `mstEventId`（降臨バトルのイベントID）
- 機能種別: `eventCategory: AdventBattle`
- 表示画面: 降臨バトル画面内
- **サーバーロジック**: イベントミッションの既存実装を流用

#### 1-B: 期間限定ミッション (`MstMissionLimitedTerm`) の活用

期間限定ミッションは**`missionCategory`で機能種別を識別**し、`progressGroupKey`で進行状況を管理します。

**構造**:
```
mst_mission_limited_terms
├─ id (ミッションID)
├─ progressGroupKey (進行状況グループキー)
├─ missionCategory (AdventBattle等の機能種別)
├─ criterionType (達成条件の種類)
├─ startAt / endAt (期間)
└─ mstMissionRewardGroupId (報酬グループID)
```

**使い分け**:
- **イベントミッション**: イベント全体に紐づくミッション（イベント単位で管理）
- **期間限定ミッション**: より細かい粒度の期間管理が必要な場合（降臨バトル個別等）

### 戦略2: 既存ミッション種別で対応可能か検討

新しい要求が、既存の7種類のミッションで実現できないか必ず検討します。

**判断基準**:
- リセット周期は既存種別（Daily/Weekly/なし）と一致するか？
- 達成条件の種類は`MissionCriterionType`（90種類以上）で対応可能か？
- 表示場所は既存画面で問題ないか？
- イベントに紐づく場合は`MstMissionEvent`を検討
- 特定機能に紐づく期間限定ミッションは`MstMissionLimitedTerm`を検討

### 戦略3: マスタデータでの差別化を優先

新しい要求の大半は、**マスタデータのパラメータで制御**することで実現できます。

**悪い例**: 新規ミッション種別を追加
```typescript
// MissionTypeに新しい種別を追加
enum MissionType {
  Achievement,      // 既存
  Daily,            // 既存
  Weekly,           // 既存
  Beginner,         // 既存
  Event,            // 既存
  EventDaily,       // 既存
  LimitedTerm,      // 既存
  NewFeature,       // ❌ 新規追加（不要）
}
```

**良い例1**: イベントミッションとして実装
```csv
# MstMissionEvent.csv - 降臨バトルミッション
id,mstEventId,criterionType,criterionValue,criterionCount,eventCategory,...
mission_001,advent_battle_001,AdventBattleChallengeCount,,3,AdventBattle,...
mission_002,advent_battle_001,AdventBattleTotalScore,,10000,AdventBattle,...
```

**良い例2**: 期間限定ミッションとして実装
```csv
# MstMissionLimitedTerm.csv
id,progressGroupKey,missionCategory,criterionType,startAt,endAt,...
mission_001,tower_battle_01,AdventBattle,StageClearCount,2026-02-01,2026-02-28,...
```

### 戦略4: 新規実装が必要な場合の判断

以下の条件を**すべて満たす場合のみ**、新規ミッション実装を検討します:

- [ ] 既存の7種類のミッション種別では達成条件が表現できない
- [ ] 90種類以上の`MissionCriterionType`では対応不可能
- [ ] `MstMissionEvent`または`MstMissionLimitedTerm`の流用では設計が歪む
- [ ] マスタデータの拡張（新しい`eventCategory`や`missionCategory`）では対応不可能
- [ ] 将来的に他の機能でも再利用できる汎用性がある
- [ ] 実装・保守コストを正当化できるビジネス価値がある

**重要**: 新しい達成条件を追加したい場合、まず`MissionCriterionType`のenumに追加できないか検討してください。

## 具体例

### 例1: 新イベント「降臨バトル」のミッション

**要求**:
> 降臨バトル専用のミッション機能。バトル挑戦回数やスコアで達成判定。

**検討プロセス**:
1. **既存種別で対応可能か？**: YES
   - イベントミッション（`MstMissionEvent`）で実装可能
   - 達成条件: `AdventBattleChallengeCount`、`AdventBattleTotalScore`等が既に存在
2. **期間限定ミッションも選択肢**:
   - `MstMissionLimitedTerm`でも実装可能
   - `missionCategory: AdventBattle`で識別
3. **マスタデータで差別化**:
   - `mstEventId`で降臨バトルイベントに紐づけ
   - `eventCategory: AdventBattle`で機能種別を明示

**結論**: `MstMissionEvent`を使用。新規テーブル不要。

---

### 例2: 「累計ログインミッション」の追加要求

**要求**:
> 累計ログイン日数に応じた報酬付与ミッション。

**検討プロセス**:
1. **既存種別で対応可能か？**: YES
   - 達成ミッション（`MstMissionAchievement`）として実装可能
   - 達成条件: 既存の`LoginCount`で対応可能
2. **マスタデータで差別化**: `MstMissionAchievement`に新しいミッションを追加

**結論**: `MstMissionAchievement`を流用。新規実装不要。

---

### 例3: 「デイリーボーナス」との混同

**要求**:
> デイリーログインボーナスを実装したい。

**検討プロセス**:
1. **ミッションで実装すべきか？**: YES、ただし専用種別が存在
   - `MstMissionDailyBonus`が既に存在
   - ログイン日数に応じた報酬を段階的に付与
2. **既存のデイリーボーナス機能で対応**: YES

**結論**: `MstMissionDailyBonus`を使用。新規実装不要。

## チェックリスト

新しいミッション要求を受けたら、以下を確認してください:

- [ ] 既存の7種類のミッション種別を調査した
  - `MstMissionAchievement`, `MstMissionDaily`, `MstMissionWeekly`, `MstMissionBeginner`
  - `MstMissionEvent`, `MstMissionEventDaily`, `MstMissionLimitedTerm`
- [ ] 90種類以上の`MissionCriterionType`で達成条件が表現可能か確認した
- [ ] イベントに紐づく場合、`MstMissionEvent`または`MstMissionEventDaily`を検討した
- [ ] 期間限定・機能別の場合、`MstMissionLimitedTerm`の流用を検討した
- [ ] マスタデータでの差別化（`eventCategory`や`missionCategory`の追加）を検討した
- [ ] 新規実装が必要な理由を明確に説明できる
- [ ] 他機能（デイリーボーナス等）での実現可能性を検討した

## 関連原則

- [データベース設計の慎重性](database-design.md) - 安易なテーブル追加を避ける
- [仕様書の批判的検討](specification-review.md) - 仕様書の要求を鵜呑みにしない

## 補足: 実際のミッション設計パターン

### パターン1: イベントミッション (`MstMissionEvent`)

イベントに紐づくミッションの良い設計例です:

```
mst_mission_events
├─ id (ミッションID)
├─ mstEventId (どのイベントに紐づくか)
├─ criterionType (達成条件の種類: MissionCriterionType)
├─ criterionValue (達成条件の値)
├─ criterionCount (達成条件の回数)
├─ eventCategory (AdventBattle等の機能種別)
├─ mstMissionRewardGroupId (報酬グループID)
└─ destinationScene (遷移先シーン)
```

**この設計の優れた点**:
- イベントごとに独立したミッションを定義できる
- `eventCategory`で機能種別を柔軟に識別
- サーバーロジックは汎用的（イベントIDで絞り込むだけ）
- 新しいイベントでも同じ構造を使える

### パターン2: 期間限定ミッション (`MstMissionLimitedTerm`)

より細かい期間管理が必要な場合の設計例です:

```
mst_mission_limited_terms
├─ id (ミッションID)
├─ progressGroupKey (進行状況グループキー)
├─ missionCategory (AdventBattle等の機能種別)
├─ criterionType (達成条件の種類: MissionCriterionType)
├─ criterionValue (達成条件の値)
├─ criterionCount (達成条件の回数)
├─ startAt / endAt (期間)
└─ mstMissionRewardGroupId (報酬グループID)
```

**この設計の優れた点**:
- イベントより細かい粒度での期間管理
- `progressGroupKey`で進行状況を柔軟にグルーピング
- `missionCategory`で機能種別を識別
- 他の機能でも同じパターンを適用できる

### 使い分けの指針

| 要件 | 推奨テーブル | 理由 |
|------|------------|------|
| イベント単位で管理 | `MstMissionEvent` | イベントIDで自然に管理できる |
| 個別の期間設定が必要 | `MstMissionLimitedTerm` | `startAt/endAt`で柔軟に期間設定 |
| 毎日リセット | `MstMissionEventDaily` | イベント期間中の日次ミッション |
