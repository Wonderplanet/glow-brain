# 降臨バトルミッション ID採番ルール

## 概要

このドキュメントは、降臨バトルミッションのマスタデータ作成時に使用するID採番ルールをまとめたものです。
過去データの分析結果（2025年9月〜2026年2月の既存データ）に基づいて、詳細なルールと具体例を記載しています。

---

## 分析対象データ

**分析期間**: 2025年9月〜2026年2月（11イベント分、44ミッション）

**データソース**:
- `projects/glow-masterdata/MstMissionLimitedTerm.csv`
- `projects/glow-masterdata/MstMissionReward.csv`

---

## MstMissionLimitedTerm のID採番ルール

### 1. id（ミッションID）

#### 命名規則

```
limited_term_{連番}
```

#### 採番ルール

- **プレフィックス**: `limited_term_` (固定)
- **連番**: 1から始まる整数（全体で一意）
- **採番方法**: 既存の最大値 + 1

#### 現在の採番範囲

- **最小値**: `limited_term_1`
- **最大値**: `limited_term_44`
- **総数**: 44ミッション

#### 新規作成時の採番方法

1. 既存データの最大連番を確認
2. 最大値 + 1 から連番を開始
3. リリースごとに連続した番号を割り当て

**例**: 最大値が `limited_term_44` の場合、次は `limited_term_45` から開始

#### リリースキーごとのID範囲（参考）

| release_key | ID範囲 | ミッション数 |
|-------------|--------|------------|
| 202509010 | limited_term_1 〜 limited_term_4 | 4 |
| 202510010 | limited_term_5 〜 limited_term_8 | 4 |
| 202510020 | limited_term_9 〜 limited_term_12 | 4 |
| 202511010 | limited_term_13 〜 limited_term_20 | 8 |
| 202511020 | limited_term_21 〜 limited_term_24 | 4 |
| 202512010 | limited_term_25 〜 limited_term_28 | 4 |
| 202601010 | limited_term_29 〜 limited_term_32 | 4 |
| 202512020 | limited_term_33 〜 limited_term_36 | 4 |
| 202602010 | limited_term_37 〜 limited_term_40 | 4 |
| 202602020 | limited_term_41 〜 limited_term_44 | 4 |

**注意**: 必ずしもリリース日順にID が割り当てられるわけではない（例: 202512020は202601010より後にIDが割り当てられている）

#### 作成例

```csv
e,limited_term_45,202603010,group12,AdventBattleChallengeCount,,5,AdventBattle,new_event_limited_term_1,1,AdventBattle,2026-03-01 15:00:00,2026-03-05 14:59:59,新イベントに5回挑戦しよう！
e,limited_term_46,202603010,group12,AdventBattleChallengeCount,,10,AdventBattle,new_event_limited_term_2,2,AdventBattle,2026-03-01 15:00:00,2026-03-05 14:59:59,新イベントに10回挑戦しよう！
```

---

### 2. progress_group_key（進捗グループキー）

#### 命名規則

```
group{連番}
```

#### 採番ルール

- **プレフィックス**: `group` (固定)
- **連番**: 1から始まる整数
- **スコープ**: イベントごとに一意（同一イベント内の全ミッションは同じグループキーを共有）
- **採番方法**: 既存の最大値 + 1

#### 現在の採番範囲

- **最小値**: `group1`
- **最大値**: `group11`
- **総数**: 11グループ

#### グループごとのミッション数（全て4ミッション）

| progress_group_key | ミッション数 | sort_order範囲 |
|-------------------|------------|---------------|
| group1 | 4 | 1〜4 |
| group2 | 4 | 1〜4 |
| group3 | 4 | 1〜4 |
| group4 | 4 | 1〜4 |
| group5 | 4 | 1〜4 |
| group6 | 4 | 1〜4 |
| group7 | 4 | 1〜4 |
| group8 | 4 | 1〜4 |
| group9 | 4 | 1〜4 |
| group10 | 4 | 1〜4 |
| group11 | 4 | 1〜4 |

**パターン**: 全てのイベントで4ミッション構成が標準

#### 新規作成時の採番方法

1. 既存データの最大グループ番号を確認
2. 最大値 + 1 を新イベントのグループキーとする
3. 同一イベント内の全ミッションに同じグループキーを設定

**例**: 最大値が `group11` の場合、次のイベントは `group12`

#### 作成例

```csv
# 新イベント（group12）の4ミッション
e,limited_term_45,202603010,group12,...
e,limited_term_46,202603010,group12,...
e,limited_term_47,202603010,group12,...
e,limited_term_48,202603010,group12,...
```

---

### 3. mst_mission_reward_group_id（報酬グループID）

#### 命名規則

```
{イベントプレフィックス}_limited_term_{連番}
```

#### 採番ルール

- **イベントプレフィックス**: イベント固有の識別子（3文字）+ `_00001_`
- **サフィックス**: `limited_term_{連番}`
- **連番**: イベント内で1から始まる整数（ミッションの順序に対応）
- **スコープ**: イベントごとに一意

#### イベントプレフィックスの命名パターン

イベント名の頭文字や略称を使用（3文字 + `_00001_`）

**既存のプレフィックス一覧**:

| イベントプレフィックス | ミッション数 | 備考 |
|--------------------|------------|------|
| `kai_00001_` | 4 | |
| `spy_00001_` | 4 | |
| `dan_00001_` | 4 | |
| `mag_00001_` | 4 | |
| `kai_00002_` | 4 | 同一キャラの2回目イベント |
| `yuw_00001_` | 4 | |
| `sur_00001_` | 4 | |
| `jig_00001_` | 4 | |
| `osh_00001_` | 4 | |
| `you_00001_` | 4 | |
| `kim_00001_` | 4 | |

**パターン**:
- 基本形: `{3文字略称}_00001_`
- 同一キャラで複数イベントがある場合: `{3文字略称}_00002_`, `{3文字略称}_00003_`, ...
- 例: `kai_00001_`, `kai_00002_` （海が2回登場）

#### 新規作成時の採番方法

1. イベント名から3文字の略称を決定
   - 例: 「ファーストライブ」→ `fir_`（first）
   - 例: 「星空ステージ」→ `sta_`（stage）
2. 同一キャラ/テーマの過去イベントをチェック
   - 初回: `{略称}_00001_`
   - 2回目: `{略称}_00002_`
3. イベント内の各ミッションに連番を付与
   - `{プレフィックス}limited_term_1`
   - `{プレフィックス}limited_term_2`
   - `{プレフィックス}limited_term_3`
   - `{プレフィックス}limited_term_4`

#### 作成例

**新イベント「ファーストライブ」（初回）の場合**:

```csv
e,limited_term_45,202603010,group12,AdventBattleChallengeCount,,5,AdventBattle,fir_00001_limited_term_1,1,AdventBattle,...
e,limited_term_46,202603010,group12,AdventBattleChallengeCount,,10,AdventBattle,fir_00001_limited_term_2,2,AdventBattle,...
e,limited_term_47,202603010,group12,AdventBattleChallengeCount,,20,AdventBattle,fir_00001_limited_term_3,3,AdventBattle,...
e,limited_term_48,202603010,group12,AdventBattleChallengeCount,,25,AdventBattle,fir_00001_limited_term_4,4,AdventBattle,...
```

**「海」の3回目イベントの場合**:

```csv
e,limited_term_49,202604010,group13,AdventBattleChallengeCount,,5,AdventBattle,kai_00003_limited_term_1,1,AdventBattle,...
e,limited_term_50,202604010,group13,AdventBattleChallengeCount,,10,AdventBattle,kai_00003_limited_term_2,2,AdventBattle,...
```

---

## MstMissionReward のID採番ルール

### 1. id（報酬ID）

#### 命名規則

```
mission_reward_{連番}
```

#### 採番ルール

- **プレフィックス**: `mission_reward_` (固定)
- **連番**: 1から始まる整数（全体で一意、全ミッションタイプ共通）
- **採番方法**: 既存の最大値 + 1

#### 現在の採番範囲

- **最小値**: `mission_reward_1`
- **最大値**: `mission_reward_705`
- **総数**: 705報酬レコード

**注意**: この連番は降臨バトルミッション以外のミッション報酬も含む全体の連番

#### 新規作成時の採番方法

1. MstMissionReward.csv の全レコードから最大連番を確認
2. 最大値 + 1 から連番を開始
3. ミッション順に連続した番号を割り当て

**例**: 最大値が `mission_reward_705` の場合、次は `mission_reward_706` から開始

#### 降臨バトルミッションの報酬パターン

**標準パターン**: 1ミッションあたり1報酬

| ミッション順序 | 報酬ID例 | 報酬タイプ（典型例） |
|------------|---------|-------------------|
| 1番目 | mission_reward_527 | Coin（2000） |
| 2番目 | mission_reward_528 | FreeDiamond（20） |
| 3番目 | mission_reward_529 | Coin（3000） |
| 4番目 | mission_reward_530 | FreeDiamond（30） |

**パターン**: Coin → FreeDiamond を交互に繰り返すことが多い

#### 作成例

```csv
# 4ミッション分の報酬（mission_reward_706〜709）
e,mission_reward_706,202603010,fir_00001_limited_term_1,Coin,,2000,1,新イベントに5回挑戦しよう！
e,mission_reward_707,202603010,fir_00001_limited_term_2,FreeDiamond,,20,1,新イベントに10回挑戦しよう！
e,mission_reward_708,202603010,fir_00001_limited_term_3,Coin,,3000,1,新イベントに20回挑戦しよう！
e,mission_reward_709,202603010,fir_00001_limited_term_4,FreeDiamond,,30,1,新イベントに25回挑戦しよう！
```

---

### 2. group_id（報酬グループID）

#### 命名規則

```
{MstMissionLimitedTerm.mst_mission_reward_group_id と同じ値}
```

#### 採番ルール

- **必須条件**: MstMissionLimitedTerm.mst_mission_reward_group_id と完全一致
- **リレーション**: 1対1の対応関係
- **命名形式**: `{イベントプレフィックス}_limited_term_{連番}`

#### 整合性チェック

MstMissionReward の group_id は、必ず対応する MstMissionLimitedTerm レコードの mst_mission_reward_group_id と一致している必要があります。

**チェック方法**:

```sql
-- MstMissionLimitedTerm に存在しない group_id がないか確認
SELECT r.group_id
FROM MstMissionReward r
LEFT JOIN MstMissionLimitedTerm m ON r.group_id = m.mst_mission_reward_group_id
WHERE m.id IS NULL;
```

結果が空であればOK（不整合なし）

#### 作成例

**MstMissionLimitedTerm**:
```csv
e,limited_term_45,202603010,group12,AdventBattleChallengeCount,,5,AdventBattle,fir_00001_limited_term_1,1,AdventBattle,...
```

**MstMissionReward**（対応する group_id）:
```csv
e,mission_reward_706,202603010,fir_00001_limited_term_1,Coin,,2000,1,新イベントに5回挑戦しよう！
```

`fir_00001_limited_term_1` が両テーブルで一致

---

## ID採番の実践フロー

### ステップ1: 既存データの最大値を確認

```bash
# MstMissionLimitedTerm の最大ID
duckdb -c "
SELECT MAX(CAST(REPLACE(id, 'limited_term_', '') AS INTEGER)) as max_id
FROM read_csv('projects/glow-masterdata/MstMissionLimitedTerm.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e';
"

# progress_group_key の最大番号
duckdb -c "
SELECT MAX(CAST(REPLACE(progress_group_key, 'group', '') AS INTEGER)) as max_group
FROM read_csv('projects/glow-masterdata/MstMissionLimitedTerm.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e';
"

# MstMissionReward の最大ID
duckdb -c "
SELECT MAX(CAST(REPLACE(id, 'mission_reward_', '') AS INTEGER)) as max_id
FROM read_csv('projects/glow-masterdata/MstMissionReward.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e';
"
```

### ステップ2: イベントプレフィックスの決定

1. イベント名から3文字略称を考案
2. 既存のプレフィックス一覧をチェック
3. 同一キャラ/テーマの回数を確認（`_00001_`, `_00002_`, ...）

```bash
# 既存のイベントプレフィックス一覧
duckdb -c "
SELECT DISTINCT SUBSTR(mst_mission_reward_group_id, 1, 10) as event_prefix
FROM read_csv('projects/glow-masterdata/MstMissionLimitedTerm.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
ORDER BY event_prefix;
"
```

### ステップ3: IDの割り当て

**MstMissionLimitedTerm（4ミッションの場合）**:

| 項目 | 値の例 |
|------|-------|
| id | limited_term_45, limited_term_46, limited_term_47, limited_term_48 |
| progress_group_key | group12（全ミッション同じ） |
| mst_mission_reward_group_id | fir_00001_limited_term_1, fir_00001_limited_term_2, fir_00001_limited_term_3, fir_00001_limited_term_4 |
| sort_order | 1, 2, 3, 4 |

**MstMissionReward（4報酬の場合）**:

| 項目 | 値の例 |
|------|-------|
| id | mission_reward_706, mission_reward_707, mission_reward_708, mission_reward_709 |
| group_id | fir_00001_limited_term_1, fir_00001_limited_term_2, fir_00001_limited_term_3, fir_00001_limited_term_4 |
| sort_order | 1, 1, 1, 1（各ミッション内で1報酬のみの場合） |

### ステップ4: データ整合性の確認

- [ ] id の一意性（重複なし）
- [ ] 連番の連続性（飛び番号がないか）
- [ ] MstMissionLimitedTerm.mst_mission_reward_group_id と MstMissionReward.group_id の対応
- [ ] progress_group_key が全ミッションで統一されているか
- [ ] sort_order が1から順に設定されているか

---

## 命名の注意点

### イベントプレフィックスの選定基準

1. **3文字略称**: イベント名やキャラ名から3文字を選ぶ
2. **既存との重複回避**: 既存のプレフィックスと重複しないか確認
3. **直感的な命名**: 後から見てイベントが推測できる略称にする
4. **一貫性**: 同一キャラの場合は同じ略称を使い、連番を変える（例: `kai_00001_`, `kai_00002_`）

### 良い例

- `osh_00001_` - 「推しの子」イベント
- `spy_00001_` - 「スパイファミリー」イベント
- `kai_00001_`, `kai_00002_` - 「海」キャラの1回目、2回目

### 避けるべき例

- `abc_00001_` - 意味不明な略称
- `event1_00001_` - 汎用的すぎる命名
- `kai_1_` - フォーマット不統一（`_00001_` 形式に統一）

---

## トラブルシューティング

### エラー1: ID重複エラー

**症状**: データ投入時に「id already exists」エラー

**原因**: 既存データと同じIDを使用している

**対処**:
1. 最新のマスタデータから最大IDを再確認
2. 連番を修正

### エラー2: リレーション不整合

**症状**: ミッション達成時に報酬が付与されない

**原因**: `MstMissionLimitedTerm.mst_mission_reward_group_id` と `MstMissionReward.group_id` が一致しない

**対処**:
```sql
-- 不整合レコードを検出
SELECT m.id, m.mst_mission_reward_group_id
FROM MstMissionLimitedTerm m
LEFT JOIN MstMissionReward r ON m.mst_mission_reward_group_id = r.group_id
WHERE r.id IS NULL;
```

結果に表示されたレコードの group_id を修正

### エラー3: progress_group_key が統一されていない

**症状**: ミッション進捗が正しく表示されない

**原因**: 同一イベント内のミッションで progress_group_key が異なる

**対処**: 同一イベントの全ミッションに同じ progress_group_key を設定

---

## まとめ

### 重要なポイント

1. **id（ミッションID）**: 全体で一意、連続した連番を使用
2. **progress_group_key**: イベントごとに一意、同一イベント内で統一
3. **mst_mission_reward_group_id**: イベントプレフィックス + `_limited_term_{連番}`
4. **mission_reward_id**: 全体で一意、連続した連番を使用
5. **group_id**: MstMissionLimitedTerm.mst_mission_reward_group_id と完全一致

### チェックリスト

- [ ] 最大ID値を確認してから採番開始
- [ ] イベントプレフィックスが既存と重複していないか確認
- [ ] progress_group_key が同一イベント内で統一されているか
- [ ] mst_mission_reward_group_id と group_id が正しく対応しているか
- [ ] sort_order が1から順に設定されているか
- [ ] 全てのIDが一意であるか

---

## 参考クエリ

### 既存データの確認

```bash
# 最新のイベントプレフィックスを確認
duckdb -c "
SELECT DISTINCT SUBSTR(mst_mission_reward_group_id, 1, 10) as event_prefix
FROM read_csv('projects/glow-masterdata/MstMissionLimitedTerm.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
ORDER BY event_prefix;
"

# 最新のprogress_group_keyを確認
duckdb -c "
SELECT DISTINCT progress_group_key
FROM read_csv('projects/glow-masterdata/MstMissionLimitedTerm.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
ORDER BY CAST(REPLACE(progress_group_key, 'group', '') AS INTEGER) DESC
LIMIT 5;
"
```

---

## 更新履歴

- 2026-01-17: 初版作成（既存データ44ミッション、705報酬レコードを分析）
