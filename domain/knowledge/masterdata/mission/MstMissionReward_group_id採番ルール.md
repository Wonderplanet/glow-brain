# MstMissionReward.csv - group_id 採番ルール（完全版）

## 概要

MstMissionReward.csvの`group_id`は、ミッション報酬のグループを識別するためのIDです。
このドキュメントは、全ミッションテーブルとの対応関係を網羅的に調査した上で作成されています。

**調査対象テーブル（9種）:**
- MstMissionDailyBonus
- MstMissionDaily
- MstMissionWeekly
- MstMissionAchievement
- MstMissionBeginner
- MstMissionEvent
- MstMissionLimitedTerm
- MstMissionEventDailyBonus
- MstMissionEventDaily

**調査日:** 2026-01-17
**データソース:** `projects/glow-masterdata/MstMissionReward.csv`

---

## 採番パターンの全体構造

group_idは大きく3つのカテゴリに分類されます：

1. **固定ミッション系** - バージョン番号を含む
2. **イベント系** - 作品コードとイベント連番を含む
3. **GLOW全体イベント系** - `glo`プレフィックス

---

## 1. 固定ミッション系（バージョン番号あり）

固定ミッション系のgroup_idには、**仕様バージョンを示す中間番号**が含まれます。

### バージョン番号の意味

中間番号（`_1_`, `_2_`）は、**同じミッションタイプ内での仕様バージョン**を表します。

**重要な対応関係:**
- ミッションテーブルの`id`に含まれる番号 = group_idの中間番号
- 例: `daily_bonus_1` → `daily_bonus_reward_1_1`（両方とも`1`）
- 例: `daily_2_1` → `daily_reward_2_1`（両方とも`2`）

**現在のバージョン分布:**
- **バージョン1**: デイリーボーナスのみ
- **バージョン2**: デイリー、ウィークリー、アチーブメント、初心者ミッション

**用途:**
- 仕様変更時に新バージョンとして別管理
- 旧データを残しつつ新仕様に移行可能
- データ移行期間中の並行運用をサポート

---

### 1.1 デイリーボーナス（バージョン1）

```
daily_bonus_reward_1_{連番}
```

**パラメータ:**
- `1`: バージョン番号
- `連番`: 1～7（ゼロパディングなし）

**使用元テーブル:** `MstMissionDailyBonus`

**対応関係:**
```
MstMissionDailyBonus.id       → MstMissionReward.group_id
daily_bonus_1                  → daily_bonus_reward_1_1
daily_bonus_2                  → daily_bonus_reward_1_2
...
daily_bonus_7                  → daily_bonus_reward_1_7
```

**実例:**
| group_id | 報酬内容（例） | 出現回数 |
|----------|--------------|---------|
| daily_bonus_reward_1_1 | FreeDiamond (20) | 1 |
| daily_bonus_reward_1_2 | Coin (2000) | 1 |
| daily_bonus_reward_1_7 | FreeDiamond (50), Item×2 | 3 |

---

### 1.2 デイリーミッション（バージョン2）

```
daily_reward_2_{連番}
```

**パラメータ:**
- `2`: バージョン番号
- `連番`: 1～5（ゼロパディングなし）

**使用元テーブル:** `MstMissionDaily`（ボーナスポイント達成報酬のみ）

**対応関係:**
```
MstMissionDaily.id            → MstMissionReward.group_id
daily_2_1                      → （報酬なし、group_key: Daily1）
daily_bonus_point_2_1          → daily_reward_2_1
daily_bonus_point_2_2          → daily_reward_2_2
...
daily_bonus_point_2_5          → daily_reward_2_5
```

**重要な特徴:**
- 通常ミッション（`daily_2_*`）: `mst_mission_reward_group_id`は空、`group_key`で管理
- ボーナスポイント達成ミッション（`daily_bonus_point_2_*`）: `group_id`で報酬を指定

**実例:**
| group_id | 報酬内容（例） | 出現回数 |
|----------|--------------|---------|
| daily_reward_2_1 | FreeDiamond (10) | 1 |
| daily_reward_2_5 | FreeDiamond (30) | 1 |

---

### 1.3 ウィークリーミッション（バージョン2）

```
weekly_reward_2_{連番}
```

**パラメータ:**
- `2`: バージョン番号
- `連番`: 1～5（ゼロパディングなし）

**使用元テーブル:** `MstMissionWeekly`（ボーナスポイント達成報酬のみ）

**対応関係:**
```
MstMissionWeekly.id           → MstMissionReward.group_id
weekly_2_1                     → （報酬なし、group_key: Weekly2）
weekly_bonus_point_2_1         → weekly_reward_2_1
weekly_bonus_point_2_2         → weekly_reward_2_2
...
weekly_bonus_point_2_5         → weekly_reward_2_5
```

**構造:** デイリーミッションと同様の構造

**実例:**
| group_id | 報酬内容（例） | 出現回数 |
|----------|--------------|---------|
| weekly_reward_2_1 | FreeDiamond (20) | 1 |
| weekly_reward_2_5 | FreeDiamond (50) | 1 |

---

### 1.4 アチーブメント（バージョン2）

```
achievement_2_{連番}
```

**パラメータ:**
- `2`: バージョン番号
- `連番`: 1～103（ゼロパディングなし）

**使用元テーブル:** `MstMissionAchievement`

**対応関係:**
```
MstMissionAchievement.id      → MstMissionReward.group_id
achievement_2_1                → achievement_2_1
achievement_2_2                → achievement_2_2
...
achievement_2_103              → achievement_2_103
```

**重要な特徴:**
- ミッションIDとgroup_idが**完全一致**
- 各アチーブメントに1つのgroup_idが対応

**実例:**
| group_id | 報酬内容（例） | 出現回数 |
|----------|--------------|---------|
| achievement_2_1 | FreeDiamond (100) | 1 |
| achievement_2_2 | FreeDiamond (1500) | 1 |
| achievement_2_103 | （データ存在） | 1 |

---

### 1.5 初心者ミッション（バージョン2）

初心者ミッションには**2種類のgroup_idパターン**があります。

#### 1.5.1 通常報酬（共有型）

```
mission_reward_beginner_2
```

**パラメータ:**
- `2`: バージョン番号
- 連番なし（複数ミッションで共有）

**使用元テーブル:** `MstMissionBeginner`

**対応関係:**
```
MstMissionBeginner.id         → MstMissionReward.group_id
beginner2_1_1                  → mission_reward_beginner_2
beginner2_1_2                  → mission_reward_beginner_2
beginner2_2_1                  → mission_reward_beginner_2
...（複数ミッションが同じgroup_idを共有）
```

**重要な特徴:**
- 複数の初心者ミッションが**同一のgroup_id**を共有
- 各グループ（Beginner1, Beginner2...）内のミッションで共通の報酬プール

#### 1.5.2 ボーナス報酬（個別型）

```
mission_reward_beginner_bonus_2_{連番}
```

**パラメータ:**
- `2`: バージョン番号
- `連番`: 1～8（ゼロパディングなし）

**使用元テーブル:** `MstMissionBeginner`（ボーナス報酬）

**実例:**
| group_id | 報酬内容（例） | 出現回数 |
|----------|--------------|---------|
| mission_reward_beginner_bonus_2_1 | FreeDiamond (200) | 1 |
| mission_reward_beginner_bonus_2_2 | FreeDiamond (150), Coin (50000), Item | 3 |
| mission_reward_beginner_bonus_2_8 | FreeDiamond (1000), Item | 2 |

---

## 2. イベント系

イベント系のgroup_idは、**作品コード**と**イベント連番**を含みます。

---

### 2.1 イベント累計報酬

```
{作品コード}_{イベント連番5桁}_event_reward_{報酬段階}
```

**パラメータ:**
- `作品コード`: 3文字の作品識別子（後述）
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `報酬段階`: 報酬のステップ番号

**報酬段階のゼロパディング:**
- **通常**: 2桁ゼロパディング（01, 02, 03...24）
- **例外（osh: 推しの子のみ）**: ゼロパディングなし（1, 2, 3...53）

**使用元テーブル:** `MstMissionEvent`

**対応関係:**
```
MstMissionEvent.id            → MstMissionReward.group_id
event_kai_00001_1              → kai_00001_event_reward_01
event_kai_00001_2              → kai_00001_event_reward_02
event_osh_00001_1              → osh_00001_event_reward_1（例外）
event_osh_00001_53             → osh_00001_event_reward_53（例外）
```

**実例:**
| 作品 | group_id | 報酬段階の範囲 |
|------|----------|---------------|
| kai（怪獣8号） | kai_00001_event_reward_01～28 | 01～28（2桁） |
| spy（SPY×FAMILY） | spy_00001_event_reward_01～24 | 01～24（2桁） |
| osh（推しの子） | osh_00001_event_reward_1～53 | 1～53（例外） |

---

### 2.2 イベント期間限定ミッション

```
{作品コード}_{イベント連番5桁}_limited_term_{段階}
```

**パラメータ:**
- `作品コード`: 3文字の作品識別子
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `段階`: 1, 2, 3, 4（ゼロパディングなし）

**使用元テーブル:** `MstMissionLimitedTerm`

**対応関係:**
```
MstMissionLimitedTerm.id      → MstMissionReward.group_id
limited_term_1                 → kai_00001_limited_term_1
limited_term_2                 → kai_00001_limited_term_2
limited_term_17                → kai_00002_limited_term_1
...
```

**重要な特徴:**
- ミッションIDは連番管理（limited_term_1, limited_term_2...）
- group_idで作品とイベントを識別

**実例:**
| 作品 | group_id | 段階の範囲 |
|------|----------|-----------|
| kai（怪獣8号）00001 | kai_00001_limited_term_1～4 | 1～4 |
| kai（怪獣8号）00002 | kai_00002_limited_term_1～4 | 1～4 |
| spy（SPY×FAMILY） | spy_00001_limited_term_1～4 | 1～4 |

---

### 2.3 イベントデイリーボーナス

```
event_{作品コード}_{イベント連番5桁}_daily_bonus_{日数}
```

**パラメータ:**
- `作品コード`: 3文字の作品識別子
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `日数`: 1から開始（ゼロパディングなし）

**使用元テーブル:** `MstMissionEventDailyBonus`

**対応関係:**
```
MstMissionEventDailyBonus.id  → MstMissionReward.group_id
event_kai_00001_daily_bonus_1  → event_kai_00001_daily_bonus_1
event_kai_00001_daily_bonus_2  → event_kai_00001_daily_bonus_2
...
```

**重要な特徴:**
- ミッションIDとgroup_idが**完全一致**

**実例:**
| 作品 | group_id | 日数の範囲 |
|------|----------|-----------|
| kai（怪獣8号） | event_kai_00001_daily_bonus_1～12 | 1～12 |
| spy（SPY×FAMILY） | event_spy_00001_daily_bonus_1～16 | 1～16 |
| sur（チェンソーマン） | event_sur_00001_daily_bonus_1～24 | 1～24 |

---

## 3. GLOW全体イベント

```
glo_{イベント連番5桁}_event_reward_{2桁連番}
```

**パラメータ:**
- `glo`: GLOW全体を示す特別な作品コード
- `イベント連番5桁`: イベントごとに採番（00001からゼロパディング）
- `2桁連番`: 報酬段階（01から、ゼロパディング）

**使用元テーブル:** `MstMissionEvent`

**実例:**
| group_id | 報酬段階の範囲 |
|----------|---------------|
| glo_00001_event_reward_01～03 | 01～03 |

---

## 作品コード一覧

| 作品コード | 作品名 | イベント連番（確認済み） | 備考 |
|-----------|--------|---------------------|------|
| kai | 怪獣8号 | 00001, 00002 | 複数回イベント開催 |
| dan | ダンダダン | 00001 | - |
| jig | 地獄楽 | 00001 | - |
| kim | 着せ恋 | 00001 | - |
| mag | マッシュル | 00001 | - |
| osh | 推しの子 | 00001 | ゼロパディング例外あり |
| spy | SPY×FAMILY | 00001 | - |
| sur | チェンソーマン | 00001 | - |
| you | 幼女戦記 | 00001 | - |
| yuw | 憂国のモリアーティ | 00001 | - |
| glo | GLOW全体 | 00001 | 特別な作品コード |

---

## 命名規則のまとめ

### 基本ルール

1. **すべて小文字 + アンダースコア（snake_case）**
2. **固定ミッション系**: `{種類}_{バージョン}_{連番}` の形式
3. **イベント系**: `{作品コード}_{イベント連番}_{種類}_{段階}` の形式

### ゼロパディングルール

| 要素 | ゼロパディング | 例 |
|------|-------------|-----|
| イベント連番（5桁） | **あり** | 00001, 00002 |
| イベント報酬段階（通常） | **あり（2桁）** | 01, 02...24 |
| イベント報酬段階（osh例外） | **なし** | 1, 2...53 |
| 期間限定ミッション段階 | **なし** | 1, 2, 3, 4 |
| イベントデイリー日数 | **なし** | 1, 2...24 |
| 固定ミッション連番 | **なし** | 1, 2...103 |

### バージョン番号のルール

- **意味**: 同じミッションタイプ内での仕様バージョン
- **対応関係**: ミッションテーブルのIDの番号 = group_idの中間番号
- **現在の分布**:
  - バージョン1: デイリーボーナス
  - バージョン2: デイリー、ウィークリー、アチーブメント、初心者

---

## ミッションテーブルとgroup_idの対応表

| ミッションテーブル | group_idパターン | 対応関係 |
|------------------|----------------|---------|
| MstMissionDailyBonus | `daily_bonus_reward_1_{連番}` | id末尾 = group_id末尾 |
| MstMissionDaily | `daily_reward_2_{連番}` | ボーナスポイント時のみ |
| MstMissionWeekly | `weekly_reward_2_{連番}` | ボーナスポイント時のみ |
| MstMissionAchievement | `achievement_2_{連番}` | **完全一致** |
| MstMissionBeginner | `mission_reward_beginner_2`<br>`mission_reward_beginner_bonus_2_{連番}` | 共有型 or 個別型 |
| MstMissionEvent | `{作品}_{イベント}_event_reward_{段階}` | id末尾 = group_id末尾 |
| MstMissionLimitedTerm | `{作品}_{イベント}_limited_term_{段階}` | group_idで作品識別 |
| MstMissionEventDailyBonus | `event_{作品}_{イベント}_daily_bonus_{日数}` | **完全一致** |

---

## 新規追加時のガイドライン

### 固定ミッション系

1. 既存のバージョン番号を確認（`1` or `2`）
2. ミッションIDの番号とgroup_idの中間番号を一致させる
3. 連番は1から開始（ゼロパディングなし）

### イベント系

1. 作品コード（3文字）を確認・決定
2. イベント連番（5桁、ゼロパディング）を採番
   - 初回: 00001
   - 2回目以降: 00002, 00003...
3. 報酬段階の連番ルールを選択:
   - **通常**: 2桁ゼロパディング（01, 02...）
   - **osh例外の踏襲**: ゼロパディングなし（1, 2...）
     - ⚠️ 新規作品は通常ルールを推奨

### 複数報酬の扱い

同一group_idに複数の報酬アイテムを紐付ける場合:
- MstMissionReward.csvで複数行作成
- すべて同じgroup_idを指定
- sort_orderで表示順を制御

**例:**
```csv
id,group_id,resource_type,resource_amount,sort_order
mission_reward_48,mission_reward_beginner_bonus_2_2,FreeDiamond,150,1
mission_reward_49,mission_reward_beginner_bonus_2_2,Coin,50000,1
mission_reward_50,mission_reward_beginner_bonus_2_2,Item,1,1
```

---

## 注意事項

### 例外パターン

1. **osh（推しの子）のゼロパディング例外**
   - 報酬段階が1桁の場合もゼロパディングなし
   - 既存データとの整合性を優先する場合は踏襲
   - 新規作品では通常ルール（2桁ゼロパディング）を推奨

2. **共有型group_id（初心者ミッション）**
   - `mission_reward_beginner_2`は複数ミッションで共有
   - 新規追加時は既存データとの整合性を確認

### データ整合性チェック

新規追加時は以下を確認してください：

1. ミッションテーブルのIDとgroup_idの対応関係
2. バージョン番号の一貫性
3. ゼロパディングルールの適用
4. 作品コードの一意性
5. イベント連番の重複チェック

---

## 参照情報

**調査方法:**
- masterdata-explorerスキルを使用
- DuckDBでMstMissionReward.csvを分析
- 全ミッションテーブル（9種）との対応関係を調査

**関連ドキュメント:**
- `.claude/skills/masterdata-explorer/SKILL.md`
- `マスタデータ/docs/マスターテーブル一覧調査方法.md`

**DBスキーマ:**
- `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
