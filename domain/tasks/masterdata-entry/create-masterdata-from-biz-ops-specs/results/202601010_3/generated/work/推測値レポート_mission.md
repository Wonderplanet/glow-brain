# 推測値レポート - ミッションマスタデータ

## 概要
地獄楽 いいジャン祭 特別ミッションのマスタデータ生成において、運営仕様書に明記されていない値を推測で決定しました。以下の項目を必ず確認してください。

## 推測値一覧

### 1. MstMissionEvent.criterion_value (クエストID)

**ミッション23-26: クエストID**
- **event_jig_00001_23**: `quest_event_jig1_charaget01`
- **event_jig_00001_24**: `quest_event_jig1_charaget02`
- **event_jig_00001_25**: `quest_event_jig1_challenge01`
- **event_jig_00001_26**: `quest_event_jig1_savage`

**理由**: 02_施策.csvの「画像」列に記載されたクエスト画像IDから推測しました。
**確認事項**: 
- 正しいクエストIDか確認してください
- MstQuestテーブルに該当するIDが存在するか確認してください

---

### 2. MstMissionReward.resource_id (アイテムID)

#### カラーメモリーID
- **memory_chara_jig_00701**: メイのカラーメモリー
- **memory_chara_jig_00601**: 民谷 巌鉄斎のカラーメモリー

**理由**: キャラクターID (chara_jig_00701, chara_jig_00601) から、カラーメモリーIDの命名規則を推測しました。
**確認事項**: 
- MstItemテーブルに該当するIDが存在するか確認してください
- 正しいアイテムIDの命名規則か確認してください

#### キャラクターのかけらID
- **piece_jig_00701**: メイのかけら
- **piece_jig_00601**: 民谷 巌鉄斎のかけら
- **piece_jig_00001**: がらんの画眉丸のかけら

**理由**: キャラクターID から、かけらIDの命名規則 `piece_{series}_{number}` を推測しました。
**確認事項**: 
- MstItemテーブルに該当するIDが存在するか確認してください
- がらんの画眉丸のキャラクターIDが `chara_jig_00001` であることを確認してください

#### メモリーフラグメントID
- **memory_fragment_1**: メモリーフラグメント・初級
- **memory_fragment_2**: メモリーフラグメント・中級
- **memory_fragment_3**: メモリーフラグメント・上級

**理由**: 標準的な命名規則を推測しました。
**確認事項**: 
- MstItemテーブルに該当するIDが存在するか確認してください
- レアリティ順に1→2→3が正しいか確認してください

#### ガシャチケットID
- **ticket_pickup_gacha**: ピックアップガシャチケット
- **ticket_special_gacha**: スペシャルガシャチケット

**理由**: 標準的なガシャチケットの命名規則を推測しました。
**確認事項**: 
- MstItemテーブルに該当するIDが存在するか確認してください

#### カラーメモリーID (ログインボーナス用)
- **memory_color_green**: カラーメモリー・グリーン
- **memory_color_red**: カラーメモリー・レッド

**理由**: 標準的なカラーメモリーの命名規則を推測しました。
**確認事項**: 
- MstItemテーブルに該当するIDが存在するか確認してください

---

### 3. MstMissionEvent.destination_scene

**全ミッション共通**
- **ユニット育成系ミッション (1-22)**: `UnitList`
- **クエスト系ミッション (23-26)**: `Event`
- **敵撃破ミッション (27-43)**: `Event`

**理由**: マニュアルの「criterion_type設定一覧」表の「destination_scene候補」列に基づき、以下のルールで設定しました:
- `SpecificUnitGradeUpCount` → `UnitList`
- `SpecificUnitLevel` → `UnitList`
- `SpecificQuestClear` → `Event` (イベントミッションの場合)
- `DefeatEnemyCount` → `Event` (イベントミッションの場合)

**確認事項**: 
- ユーザーが遷移する画面が適切か確認してください
- イベントTOP画面への遷移が意図通りか確認してください

---

### 4. MstMissionReward.id (報酬連番)

**開始番号**: `mission_reward_480`

**理由**: 既存のマスタデータとの連番の重複を避けるため、480から開始しました。
**確認事項**: 
- 既存のMstMissionRewardテーブルの最大IDを確認してください
- 連番が重複していないか確認してください

---

### 5. MstMissionEventDependency.id (依存関係連番)

**開始番号**: `151`

**理由**: 既存のマスタデータとの連番の重複を避けるため、151から開始しました。
**確認事項**: 
- 既存のMstMissionEventDependencyテーブルの最大IDを確認してください
- 連番が重複していないか確認してください

---

### 6. ログインボーナス期間

**MstMissionEventDailyBonusSchedule**
- **start_at**: `2026-01-16 15:00:00`
- **end_at**: `2026-02-02 03:59:00`

**理由**: 02_施策.csvの「地獄楽 いいジャン祭 特別ログインボーナス」の開催期間から設定しました。
**確認事項**: 
- イベント期間と一致しているか確認してください
- 終了日時が正しいか確認してください (2/2 3:59)

---

## 手順書通りに作成した項目 (推測値ではない)

以下の項目は手順書の命名規則に従って作成したため、推測値レポートの対象外です:

1. **MstMissionEvent.id**: `event_{作品ID}_{イベント連番5桁}_{ミッション連番}`
2. **MstMissionEvent.mst_mission_reward_group_id**: `{作品ID}_{イベント連番5桁}_event_reward_{報酬段階}`
3. **MstMissionEventDependency.group_id**: 依存関係グループの最初のミッションID
4. **MstMissionEventI18n.id**: `{mst_mission_event_id}_{language}`
5. **MstMissionEventDailyBonus.id**: `{mst_event_id}_daily_bonus_{login_day:02d}`
6. **MstMissionEventDailyBonusSchedule.id**: `{mst_event_id}_daily_bonus_schedule`

---

## データインポート前の最終確認

### 必須チェック項目
- [ ] すべての推測したアイテムIDがMstItemテーブルに存在するか
- [ ] すべての推測したクエストIDがMstQuestテーブルに存在するか
- [ ] すべてのIDが一意であるか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか
- [ ] 開催期間が正しいか

### 推奨チェック項目
- [ ] destination_sceneの遷移先が適切か
- [ ] 報酬内容が仕様書と一致しているか
- [ ] 依存関係の順序が正しいか
- [ ] ログインボーナスの日数が正しいか (17日間)

---

## 生成完了したテーブル

1. ✅ MstMissionEvent (43ミッション)
2. ✅ MstMissionEventI18n (43件の日本語説明文)
3. ✅ MstMissionEventDependency (39件の依存関係)
4. ✅ MstMissionReward (43グループのミッション報酬 + 17日のログインボーナス報酬)
5. ✅ MstMissionEventDailyBonus (17日間)
6. ✅ MstMissionEventDailyBonusSchedule (1スケジュール)
7. ⚠️  MstMissionLimitedTerm (該当なし - 期間限定ミッションの記載なし)
8. ⚠️  MstMissionLimitedTermI18n (該当なし)

**注**: 期間限定ミッション (MstMissionLimitedTerm) は、04_ミッション.csvに明確な記載がなかったため作成していません。降臨バトル等の期間限定ミッションが必要な場合は、別途追加が必要です。
