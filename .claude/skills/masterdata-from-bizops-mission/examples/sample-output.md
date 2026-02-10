# ミッション マスタデータ サンプル出力

このファイルは、ミッションマスタデータ作成スキルの出力例を示します。

## 前提条件

- **リリースキー**: 202601010
- **シリーズID**: jig (地獄楽)
- **イベントID**: event_jig_00001
- **イベント連番**: 00001
- **ミッション数**: 43
- **has_daily_bonus**: 1 (ログインボーナスあり)
- **has_limited_term_mission**: 1 (期間限定ミッションあり)

## 出力シート一覧

以下の8シートが出力されます:

1. MstMissionEvent
2. MstMissionEventI18n
3. MstMissionEventDependency
4. MstMissionReward
5. MstMissionEventDailyBonus
6. MstMissionEventDailyBonusSchedule
7. MstMissionLimitedTerm
8. MstMissionLimitedTermI18n

---

## 1. MstMissionEvent シート

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | unlock_criterion_value | unlock_criterion_count | group_key | mst_mission_reward_group_id | sort_order | destination_scene |
|--------|----|-----------|--------------|-----------------|-----------------|-----------------|-----------------------|------------------------|------------------------|-----------|----------------------------|-----------|-------------------|
| e | event_jig_00001_1 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 2 | __NULL__ | | 0 | | jig_00001_event_reward_01 | 1 | UnitList |
| e | event_jig_00001_2 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 3 | __NULL__ | | 0 | | jig_00001_event_reward_02 | 2 | UnitList |
| e | event_jig_00001_23 | 202601010 | event_jig_00001 | SpecificQuestClear | quest_event_jig1_charaget01 | 1 | __NULL__ | | 0 | | jig_00001_event_reward_23 | 23 | Event |
| e | event_jig_00001_27 | 202601010 | event_jig_00001 | DefeatEnemyCount | | 10 | __NULL__ | | 0 | | jig_00001_event_reward_27 | 27 | Event |

**重要な注意点**:
- **group_key**: 常に空欄(イベントミッションでは使用しない)
- **unlock_criterion_type**: 常に`__NULL__`(依存関係はMstMissionEventDependencyで管理)

---

## 2. MstMissionEventI18n シート

| ENABLE | release_key | id | mst_mission_event_id | language | description |
|--------|-------------|----|--------------------|----------|------------|
| e | 202601010 | event_jig_00001_1_ja | event_jig_00001_1 | ja | "メイ をグレード2まで強化しよう" |
| e | 202601010 | event_jig_00001_2_ja | event_jig_00001_2 | ja | "メイ をグレード3まで強化しよう" |
| e | 202601010 | event_jig_00001_23_ja | event_jig_00001_23 | ja | "ストーリークエスト「必ず生きて帰る」をクリアしよう" |
| e | 202601010 | event_jig_00001_27_ja | event_jig_00001_27 | ja | "敵を10体撃破しよう" |

---

## 3. MstMissionEventDependency シート

| ENABLE | id | release_key | group_id | mst_mission_event_id | unlock_order | 備考 |
|--------|----|-----------|---------|--------------------|--------------|-----|
| e | 151 | 202601010 | event_jig_00001_1 | event_jig_00001_1 | 1 | |
| e | 152 | 202601010 | event_jig_00001_1 | event_jig_00001_2 | 2 | |
| e | 153 | 202601010 | event_jig_00001_1 | event_jig_00001_3 | 3 | |
| e | 154 | 202601010 | event_jig_00001_1 | event_jig_00001_4 | 4 | |
| e | 155 | 202601010 | event_jig_00001_5 | event_jig_00001_5 | 1 | |
| e | 156 | 202601010 | event_jig_00001_5 | event_jig_00001_6 | 2 | |

**説明**:
- `event_jig_00001_1`グループ: グレードアップミッション(1→2→3→4の順に解放)
- `event_jig_00001_5`グループ: レベルアップミッション(5→6の順に解放)

**重要な注意点**:
- **group_id**: 依存関係グループの**最初のミッションID**と同じ値
- **unlock_order**: 1から連番で設定
- 依存関係が不要なミッションは、このテーブルに含めない

---

## 4. MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|-----------|---------|--------------|-----------|-----------------|-----------|----|
| e | mission_reward_480 | 202601010 | jig_00001_event_reward_01 | Item | memory_chara_jig_00701 | 200 | 1 | jigいいジャン祭_ミッション |
| e | mission_reward_481 | 202601010 | jig_00001_event_reward_02 | Item | memory_chara_jig_00701 | 300 | 1 | jigいいジャン祭_ミッション |
| e | mission_reward_490 | 202601010 | jig_00001_event_reward_11 | FreeDiamond | | 50 | 1 | jigいいジャン祭_ミッション |
| e | mission_reward_502 | 202601010 | jig_00001_event_reward_23 | Coin | | 12500 | 1 | jigいいジャン祭_ミッション |

**重要な注意点**:
- **group_id**: MstMissionEvent.mst_mission_reward_group_idと同じ値
- **resource_type**: `Coin`、`FreeDiamond`、`Item`、`Emblem`、`Unit`のいずれか
- **resource_id**: `Coin`、`FreeDiamond`の場合は空欄、それ以外は対応するリソースID

---

## 5. MstMissionEventDailyBonus シート

| ENABLE | id | release_key | mst_mission_event_daily_bonus_schedule_id | login_day | mst_mission_reward_group_id |
|--------|----|-------------|-----------------------------------------|----------|----------------------------|
| e | event_jig_00001_daily_bonus_01 | 202601010 | event_jig_00001_daily_bonus_schedule | 1 | event_jig_00001_daily_bonus_01 |
| e | event_jig_00001_daily_bonus_02 | 202601010 | event_jig_00001_daily_bonus_schedule | 2 | event_jig_00001_daily_bonus_02 |
| e | event_jig_00001_daily_bonus_17 | 202601010 | event_jig_00001_daily_bonus_schedule | 17 | event_jig_00001_daily_bonus_17 |

**重要な注意点**:
- **id**: `{mst_event_id}_daily_bonus_{login_day:02d}`(2桁ゼロパディング)
- **login_day**: 1から順番に採番
- **mst_mission_reward_group_id**: `{作品ID}_{イベント連番5桁}_daily_bonus_{login_day:02d}`

**対応する報酬をMstMissionRewardに追加**:
```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_XXX,202601010,event_jig_00001_daily_bonus_01,FreeDiamond,,100,1,ログインボーナス1日目
```

---

## 6. MstMissionEventDailyBonusSchedule シート

| ENABLE | id | release_key | mst_event_id | start_at | end_at |
|--------|----|-----------|--------------|---------|----|
| e | event_jig_00001_daily_bonus_schedule | 202601010 | event_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 03:59:00 |

**重要な注意点**:
- **id**: `{mst_event_id}_daily_bonus_schedule`
- **start_at、end_at**: イベント期間内に設定

---

## 7. MstMissionLimitedTerm シート

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | mst_mission_reward_group_id | sort_order | start_at | end_at | destination_scene |
|--------|----|-----------|--------------|-----------------|-----------------|-----------------|-----------------------------|-----------|----------|--------|-------------------|
| e | jig_00001_limited_term_1 | 202601010 | event_jig_00001 | AdventBattleChallengeCount | quest_raid_jig1_00001 | 5 | jig_00001_limited_term_1 | 1 | 2026-01-23 15:00:00 | 2026-01-29 14:59:00 | Event |
| e | jig_00001_limited_term_2 | 202601010 | event_jig_00001 | AdventBattleChallengeCount | quest_raid_jig1_00001 | 10 | jig_00001_limited_term_2 | 2 | 2026-01-23 15:00:00 | 2026-01-29 14:59:00 | Event |

**重要な注意点**:
- **id**: `{作品ID}_{イベント連番5桁}_limited_term_{連番}`
- **criterion_type**: MstMissionEventと同じcriterion_typeを使用
- **start_at、end_at**: イベント期間内に設定

**対応する報酬をMstMissionRewardに追加**:
```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_XXX,202601010,jig_00001_limited_term_1,FreeDiamond,,100,1,降臨バトル期間限定
```

---

## 8. MstMissionLimitedTermI18n シート

| ENABLE | release_key | id | mst_mission_limited_term_id | language | description |
|--------|-------------|----|--------------------------|-----------|-----------|
| e | 202601010 | jig_00001_limited_term_1_ja | jig_00001_limited_term_1 | ja | "降臨バトル「まるで 悪夢を見ているようだ」に5回挑戦しよう！" |
| e | 202601010 | jig_00001_limited_term_2_ja | jig_00001_limited_term_2 | ja | "降臨バトル「まるで 悪夢を見ているようだ」に10回挑戦しよう！" |

---

## 推測値レポート

### MstMissionEvent.destination_scene
- **値**: UnitList(event_jig_00001_1)、Event(event_jig_00001_23、event_jig_00001_27)
- **理由**: criterion_typeから推測。手順書の「criterion_type設定一覧」表に基づき設定
- **確認事項**: ユーザーが遷移する画面が適切か確認してください

### MstMissionReward.resource_id
- **値**: memory_chara_jig_00701
- **理由**: 設計書にアイテムIDの記載がなく、キャラクターIDから推測
- **確認事項**: 正しいアイテムIDか、MstItemテーブルに存在するか確認してください

### MstMissionEventDailyBonus.mst_mission_reward_group_id
- **値**: event_jig_00001_daily_bonus_01、event_jig_00001_daily_bonus_02...
- **理由**: ログインボーナス報酬グループIDを標準ルールに従って自動生成
- **確認事項**: 対応する報酬がMstMissionRewardに存在するか確認してください

### MstMissionLimitedTerm.start_at/end_at
- **値**: 2026-01-23 15:00:00 / 2026-01-29 14:59:00
- **理由**: 設計書に期間限定ミッション開催期間の記載がなく、イベント期間から推測
- **確認事項**: 降臨バトル等の開催期間が正しいか確認してください

---

## 注意事項

### criterion_typeとdestination_sceneの対応

**重要**: criterion_typeとdestination_sceneは強い相関関係があります。手順書の「criterion_type設定一覧」表を参照してください。

**イベントミッションで頻繁に使用されるcriterion_type**:
- SpecificUnitGradeUpCount → destination_scene: UnitList
- SpecificQuestClear → destination_scene: Event
- DefeatEnemyCount → destination_scene: Event
- SpecificStageClearCount → destination_scene: Event

### 依存関係の設定

依存関係は、**順序解放が必要なミッショングループのみ**に設定します。全てのミッションに設定する必要はありません。

**依存関係が必要なミッション**:
- グレードアップミッション
- レベルアップミッション
- 敵撃破数ミッション

**依存関係が不要なミッション**:
- 単発の特定クエストクリアミッション
- 独立したガシャミッション

### has_daily_bonus=0の場合

ログインボーナスがない場合、以下のシートは出力されません:
- MstMissionEventDailyBonus
- MstMissionEventDailyBonusSchedule

### has_limited_term_mission=0の場合

期間限定ミッションがない場合、以下のシートは出力されません:
- MstMissionLimitedTerm
- MstMissionLimitedTermI18n

---

## 参考

詳細な設定ルールとenum値一覧は [references/manual.md](../references/manual.md) を参照してください。
