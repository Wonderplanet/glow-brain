# 報酬設定 マスタデータ サンプル出力

## 概要

このファイルは、報酬設定スキル (masterdata-from-bizops-reward) を使用した際の実際の出力例を示します。

## サンプル1: イベントログインボーナス報酬

### 入力パラメータ

```
release_key: 202601010
reward_table: MstMissionReward
event_id: event_jig_00001
event_name: 地獄楽 いいジャン祭
```

### 出力

#### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_463 | 202601010 | event_jig_00001_daily_bonus_01 | Item | ticket_glo_00003 | 2 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス 1日目 |
| e | mission_reward_464 | 202601010 | event_jig_00001_daily_bonus_02 | Coin | | 5000 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス 2日目 |
| e | mission_reward_465 | 202601010 | event_jig_00001_daily_bonus_03 | FreeDiamond | | 40 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス 3日目 |
| e | mission_reward_466 | 202601010 | event_jig_00001_daily_bonus_04 | Item | memory_chara_jig_00601 | 100 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス 4日目 |
| e | mission_reward_467 | 202601010 | event_jig_00001_daily_bonus_05 | Item | ticket_glo_00003 | 3 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス 5日目 |

#### 推測値レポート

##### MstMissionReward.id
- **値**: mission_reward_463
- **理由**: 設計書に連番の開始番号の記載がなかったため、既存の最大IDから連番を推測
- **確認事項**: 既存のMstMissionRewardで使用されているIDと重複していないことを確認してください

##### MstMissionReward.sort_order
- **値**: 1
- **理由**: 設計書に表示順序の記載がなかったため、デフォルト値`1`を設定
- **確認事項**: 同一グループ内で複数の報酬がある場合、表示順序を調整してください

## サンプル2: イベントミッション報酬

### 入力パラメータ

```
release_key: 202601010
reward_table: MstMissionReward
event_id: event_jig_00001
series_id: jig
```

### 出力

#### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_480 | 202601010 | jig_00001_event_reward_01 | Item | memory_chara_jig_00701 | 200 | 1 | jigいいジャン祭_ミッション1 |
| e | mission_reward_481 | 202601010 | jig_00001_event_reward_02 | Coin | | 10000 | 1 | jigいいジャン祭_ミッション2 |
| e | mission_reward_482 | 202601010 | jig_00001_event_reward_03 | FreeDiamond | | 50 | 1 | jigいいジャン祭_ミッション3 |
| e | mission_reward_483 | 202601010 | jig_00001_event_reward_04 | Item | ticket_glo_00003 | 1 | 1 | jigいいジャン祭_ミッション4 |

## サンプル3: クエスト初回クリア報酬

### 入力パラメータ

```
release_key: 202601010
reward_table: MstQuestFirstTimeClearReward
quest_id: quest_jig_00001
```

### 出力

#### MstQuestFirstTimeClearReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order |
|--------|----|------------|---------|--------------|------------|----------------|-----------|
| e | quest_first_time_clear_reward_1 | 202601010 | quest_jig_00001_first_clear | FreeDiamond | | 10 | 1 |
| e | quest_first_time_clear_reward_2 | 202601010 | quest_jig_00001_first_clear | Coin | | 1000 | 2 |

## サンプル4: 複数のリソースタイプを含む報酬

### 入力パラメータ

```
release_key: 202601010
reward_table: MstMissionReward
group_id: jig_00001_limited_term_1
```

### 出力

#### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_490 | 202601010 | jig_00001_limited_term_1 | Unit | chara_jig_00601 | 1 | 1 | 期間限定ミッション キャラ報酬 |
| e | mission_reward_491 | 202601010 | jig_00001_limited_term_1 | Item | memory_chara_jig_00601 | 50 | 2 | 期間限定ミッション アイテム報酬 |
| e | mission_reward_492 | 202601010 | jig_00001_limited_term_1 | Emblem | emblem_jig_00001 | 1 | 3 | 期間限定ミッション エンブレム報酬 |
| e | mission_reward_493 | 202601010 | jig_00001_limited_term_1 | FreeDiamond | | 100 | 4 | 期間限定ミッション ダイヤ報酬 |

#### 推測値レポート

##### MstMissionReward.sort_order
- **値**: 1, 2, 3, 4
- **理由**: 設計書に表示順序の記載がなかったため、報酬の重要度順にsort_orderを設定
- **確認事項**: 表示順序が意図通りであることを確認してください

## 重要なポイント

### resource_type別の設定

1. **Item**: resource_idに`MstItem.id`を設定
2. **Unit**: resource_idに`MstUnit.id`を設定
3. **Emblem**: resource_idに`MstEmblem.id`を設定
4. **Artwork**: resource_idに`MstArtwork.id`を設定
5. **Coin/FreeDiamond/PaidDiamond/Stamina/Experience**: resource_idは空欄

### group_id命名規則

- イベントログインボーナス: `{event_id}_daily_bonus_{連番2桁}`
- イベント報酬: `{series_id}_{event_連番5桁}_event_reward_{連番2桁}`
- 期間限定ミッション: `{series_id}_{event_連番5桁}_limited_term_{連番}`
- クエスト初回クリア報酬: `{quest_id}_first_clear`

### 推測値レポートの重要性

設計書に記載がない値を推測で決定した場合、必ず推測値レポートに記載してください。これにより、データインポートエラーや本番不具合のリスクを低減できます。
