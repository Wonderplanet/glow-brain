# criterion_type設定ルール

## 概要

`criterion_type`は、ミッションの達成条件のタイプを指定するカラムです。
このドキュメントでは、設定可能な値とその使用方法を説明します。

## データソース

criterion_typeの定義は以下のファイルに記載されています。

- `マスタデータ/docs/mission/ミッショントリガー一覧.csv`

## 基本ルール

### 1. 定義済みの値のみ設定可能

criterion_typeには、ミッショントリガー一覧.csvの`criterion_type`列に定義されている値のみ設定できます。
未定義の値を設定すると、データ投入エラーまたはゲーム内エラーが発生します。

### 2. ミッションタイプごとの制限

各criterion_typeには「対象ミッションタイプ」が定義されています。

- **全て対象**: すべてのミッションタイプで使用可能
- **特定タイプのみ**: 指定されたミッションタイプでのみ使用可能

例:
- `AdventBattleChallengeCount`は「全て対象」→ すべてのミッションタイプで使用可能
- `MissionBonusPoint`は「初心者/デイリー/ウィークリー」のみ → 期間限定ミッションでは使用不可

### 3. criterion_valueとcriterion_countの設定

各criterion_typeには、以下の2つのパラメータが関連します。

| パラメータ | 説明 | 例 |
|-----------|------|-----|
| **criterion_value** | 条件の指定値（X） | ステージID、アイテムIDなど |
| **criterion_count** | 条件の回数（Y） | 挑戦回数、獲得数など |

ミッショントリガー一覧.csvの説明を参考に、適切な値を設定してください。

## 降臨バトルミッション用のcriterion_type

降臨バトルミッションでは、以下のcriterion_typeを使用します。

### AdventBattleChallengeCount

**説明**: 降臨バトルにX回挑戦する

**使用条件**: 達成条件

**対象ミッションタイプ**: 全て対象

**パラメータ設定**:
- `criterion_value`: NULL（未指定）
- `criterion_count`: 挑戦してほしい回数

**設定例**:
```csv
criterion_type,criterion_value,criterion_count
AdventBattleChallengeCount,,5
```

**意味**: 降臨バトルに5回挑戦する

---

### AdventBattleTotalScore

**説明**: 降臨バトルの累計スコアがX達成

**使用条件**: 達成条件

**対象ミッションタイプ**: 全て対象

**パラメータ設定**:
- `criterion_value`: NULL（未指定）
- `criterion_count`: 獲得してほしい累計スコア

**設定例**:
```csv
criterion_type,criterion_value,criterion_count
AdventBattleTotalScore,,100000
```

**意味**: 降臨バトルに複数回挑戦した際の累計スコアが100,000に達する

**補足**: 複数回挑戦した場合、すべてのスコアの合計が判定対象となります。

---

### AdventBattleScore

**説明**: 降臨バトルのハイスコアがX達成

**使用条件**: 達成条件

**対象ミッションタイプ**: 全て対象

**パラメータ設定**:
- `criterion_value`: NULL（未指定）
- `criterion_count`: 獲得してほしいハイスコア

**設定例**:
```csv
criterion_type,criterion_value,criterion_count
AdventBattleScore,,50000
```

**意味**: 降臨バトルのハイスコアが50,000に達する

**補足**: 複数回挑戦した場合、その中で最大のスコアが判定対象となります。

## 全criterion_typeリスト（参考）

以下は、ミッショントリガー一覧.csvに定義されているすべてのcriterion_typeのリストです。

### アイテム関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| SpecificItemCollect | 指定アイテムをX個集める | アイテムID | 集めて欲しい個数 |

### インゲーム関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| DefeatEnemyCount | インゲームで敵をY体撃破 | NULL | 撃破数 |
| DefeatBossEnemyCount | インゲームで強敵をY体撃破 | NULL | 撃破数 |
| SpecificSeriesEnemyDiscoveryCount | 指定作品Xの敵キャラをY体発見 | 作品ID | 発見数 |
| EnemyDiscoveryCount | 敵キャラをY体発見 | NULL | 発見数 |
| SpecificEnemyDiscoveryCount | 敵キャラXをY体発見 | エネミーID | 発見数 |

### ガシャ関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| SpecificGachaDrawCount | 指定ガシャXをY回引く | ガシャID | 引く回数 |
| GachaDrawCount | 通算でガシャをY回引く | NULL | 引く回数 |

### ゲート関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| OutpostEnhanceCount | ゲートをX回以上強化 | NULL | 強化回数 |
| SpecificOutpostEnhanceLevel | 指定ゲート強化項目がLvYに到達 | 強化項目ID | 到達レベル |

### システム関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| IaaCount | 広告視聴をY回する | NULL | 視聴回数 |
| FollowCompleted | 公式Xをフォローする | NULL | 1（固定） |
| AccountCompleted | アカウント連携を行う | NULL | 1（固定） |
| ReviewCompleted | ストアレビューを記載 | NULL | 1（固定） |
| AccessWeb | 指定のWEBサイトにアクセス | URL | 1（固定） |

### ステージ/クエスト関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| SpecificQuestClear | 指定クエストを初クリア | クエストID | 1（固定） |
| SpecificStageClearCount | 指定ステージXをY回クリア | ステージID | クリア回数 |
| SpecificStageChallengeCount | 指定ステージXにY回挑戦 | ステージID | 挑戦回数 |
| QuestClearCount | 通算クエストクリア回数がY回に到達 | NULL | クリア回数 |
| StageClearCount | 通算ステージクリア回数がY回に到達 | NULL | クリア回数 |
| SpecificUnitStageClearCount | 指定ユニット編成で指定ステージをY回クリア | ユニットID.ステージID | クリア回数 |
| SpecificUnitStageChallengeCount | 指定ユニット編成で指定ステージにY回挑戦 | ユニットID.ステージID | 挑戦回数 |

### ミッション関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| MissionClearCount | ミッションをY個クリア | NULL | クリア数 |
| SpecificMissionClearCount | 指定ミッショングループXの内でY個クリア | group_key | クリア数 |
| MissionBonusPoint | ミッションボーナスポイントをY個集める | NULL | 獲得ポイント |

### ユーザー関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| UserLevel | プレイヤーレベルがYに到達 | NULL | 到達レベル |
| CoinCollect | コインをY個集める | NULL | 集める量 |
| CoinUsedCount | コインをX枚使用 | NULL | 使用量 |

### ユニット関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| UnitLevelUpCount | ユニットのレベルアップをY回 | NULL | レベルアップ回数 |
| UnitLevel | 全ユニットの内でいずれかがLv.Yに到達 | NULL | 到達レベル |
| SpecificUnitLevel | 指定ユニットがLv.Yに到達 | ユニットID | 到達レベル |
| SpecificUnitRankUpCount | 指定ユニットのランクアップ回数がY回以上 | ユニットID | ランクアップ回数 |
| SpecificUnitGradeUpCount | 指定ユニットのグレードアップ回数がY回以上 | ユニットID | グレードアップ回数 |
| SpecificSeriesUnitAcquiredCount | 特定作品XのユニットをY体獲得 | 作品ID | 獲得種類数 |
| UnitAcquiredCount | ユニットをY体入手 | NULL | 入手体数 |
| SpecificUnitAcquiredCount | 指定ユニットXをY体獲得 | ユニットID | 獲得体数 |

### ログイン関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| LoginCount | 通算ログインがY日に到達 | NULL | ログイン日数 |
| LoginContinueCount | 連続ログインがY日目に到達 | NULL | 連続日数 |

### 探索関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| IdleIncentiveQuickCount | クイック探索をY回する | NULL | 実行回数 |
| IdleIncentiveCount | 探索をY回する | NULL | 実行回数 |

### 図鑑関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| SpecificSeriesEmblemAcquiredCount | 指定作品XのエンブレムをY個獲得 | 作品ID | 獲得種類数 |
| EmblemAcquiredCount | エンブレムをY個獲得 | NULL | 獲得種類数 |
| SpecificEmblemAcquiredCount | 指定エンブレムXをYつ獲得 | エンブレムID | 獲得個数 |
| SpecificSeriesArtworkCompletedCount | 指定作品Xの原画をYつ完成 | 作品ID | 完成数 |
| ArtworkCompletedCount | 原画をYつ完成 | NULL | 完成数 |
| SpecificArtworkCompletedCount | 指定原画Xを1つ完成 | 原画ID | 1（固定） |

### 降臨バトル関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| AdventBattleChallengeCount | 降臨バトルをX回挑戦 | NULL | 挑戦回数 |
| AdventBattleTotalScore | 降臨バトルの累計スコアがX達成 | NULL | 累計スコア |
| AdventBattleScore | 降臨バトルのハイスコアがX達成 | NULL | ハイスコア |

### 決闘関連

| criterion_type | 説明 | criterion_value | criterion_count |
|---------------|------|-----------------|-----------------|
| PvpChallengeCount | 決闘にY回挑戦 | NULL | 挑戦回数 |
| PvpWinCount | 決闘にY回勝利 | NULL | 勝利回数 |

## 使用上の注意

### criterion_valueの特殊な設定方法

一部のcriterion_typeでは、criterion_valueに特殊な形式の値を設定します。

#### SpecificUnitStageClearCount / SpecificUnitStageChallengeCount

**形式**: `ユニットID.ステージID`

**例**: `unit1.stage2`

**説明**: 2つのIDを「.(ドット)」で連結した文字列を設定します。

### 挑戦系ミッションの注意点

以下のミッションは「挑戦を押したら無条件でクリアとするミッション」です。
厳密なチェックは行わず、`mission/clear_on_call` APIで達成できます。

- `FollowCompleted`
- `AccountCompleted`
- `ReviewCompleted`
- `AccessWeb`

### クエストクリアの判定タイミング

`SpecificQuestClear`では、クエストに含まれるすべてのステージを1回以上クリアした時点で達成となります。

例: クエストAにステージ1,2,3がある場合
- ステージ1,2をクリア済み
- ステージ3を初クリア
- → この時点でクエストA初クリアと判定

### カウント系の仕様

#### 累積カウント

以下のcriterion_typeは、期間中の累積でカウントします。

- `DefeatEnemyCount`（撃破した数の合計値）
- `DefeatBossEnemyCount`（撃破した数の合計値）
- `StageClearCount`（同一ステージを複数回クリアした場合も加算）

#### 種類カウント

以下のcriterion_typeは、種類数でカウントします（重複カウントなし）。

- `SpecificSeriesUnitAcquiredCount`（獲得済ユニットを再度獲得してもカウントしない）
- `EnemyDiscoveryCount`（発見済エネミーを再度発見してもカウントしない）
- `EmblemAcquiredCount`（獲得済エンブレムを再度獲得してもカウントしない）

#### 体数カウント

以下のcriterion_typeは、体数でカウントします（重複カウントあり）。

- `UnitAcquiredCount`（同じユニットを2体入手した場合は+2）
- `SpecificUnitAcquiredCount`（獲得済みでもカウントする）

## データ検証チェックリスト

criterion_type設定時は、以下を確認してください。

- [ ] ミッショントリガー一覧.csvに定義されている値である
- [ ] 対象ミッションタイプが「全て対象」または該当するミッションタイプである
- [ ] criterion_valueの形式が正しい（NULLの場合は空欄）
- [ ] criterion_countが正の整数である
- [ ] 特殊な形式（ドット連結など）が正しく設定されている

## トラブルシューティング

### エラー: Invalid criterion_type

**原因**: ミッショントリガー一覧.csvに定義されていない値を設定している

**対処**:
1. ミッショントリガー一覧.csvを確認
2. 正しいcriterion_typeに修正
3. タイプミスがないか確認（大文字小文字も一致させる）

### エラー: criterion_valueが不正

**原因**: criterion_valueに不正なIDや形式を設定している

**対処**:
1. 該当するマスターテーブルでIDの存在を確認
2. ドット連結が必要な場合は形式を確認
3. NULLの場合は空欄にする

## 参考資料

- `マスタデータ/docs/mission/ミッショントリガー一覧.csv` - 全criterion_typeの定義
- 各マスターテーブルのスキーマ - criterion_valueで参照するIDの定義

## 更新履歴

- 2026-01-17: 初版作成
