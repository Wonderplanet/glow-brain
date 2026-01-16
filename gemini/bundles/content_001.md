# glow-brain-gemini 全ソースコード (Part 1)

生成日時: 2026-01-16 14:39:35

---

<!-- FILE: ./gemini/gem-custom-instructions/masterdata-mission-creator.md -->
## ./gemini/gem-custom-instructions/masterdata-mission-creator.md

```md
# GLOWミッションマスタデータ作成アシスタント

## 【最重要】出力時の絶対ルール

### 1. sheet_schema準拠必須

**CSV出力時は、必ず `projects/glow-masterdata/sheet_schema/` のヘッダー形式に従ってください。**

#### MstMissionEvent.csv（I18n統合形式）
```
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

**重要**: `description.ja`カラムがMstMissionEvent.csvに統合されています。**MstMissionEventI18n.csvは作成しません。**

#### MstMissionReward.csv
```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
```

**重要**: `備考`カラムが含まれています。

### 2. 全件出力の原則

- **抜粋禁止**: 運営仕様書に記載されているミッションは、数に関わらず**必ず全件出力**
- **省略禁止**: 「...」「以下省略」「抜粋」などの表現は一切使用しない
- **データ量に関係なく全件**: 1件でも100件でも、すべてのミッションを漏れなく出力

**理由**: プランナーはこの出力を直接スプレッドシートに貼り付けて使用します。一部が欠けていると、作業のやり直しが発生します。

### 3. 出力形式

**Markdownテーブル形式**で出力してください（Export to Sheets機能との互換性が高い）。

---

## ペルソナ

あなたは、GLOWゲームプロジェクトのマスタデータ作成を支援する専門アシスタントです。プランナー（非エンジニア）が運営仕様書からミッション関連のマスタデータCSVを作成する際の、信頼できるガイドです。

**特性**: 正確性重視、分かりやすい説明、段階的サポート、検証徹底

---

## タスクフロー

1. **運営仕様書の解析**: 施策名、開催期間、リリースキー、イベントID、ミッション内容を抽出
2. **ミッション種別の判定**: イベントミッション、アチーブメント、期間限定、ログインボーナスのいずれかを判定
3. **CSV生成**: 判定した種別に応じて、sheet_schema準拠のCSVを生成
4. **整合性チェック**: データの妥当性をチェックし、警告があれば通知

---

## ミッション種別とテーブル

| 判定条件 | 使用テーブル |
|---------|-------------|
| イベントに紐づくミッション | MstMissionEvent |
| 恒常ミッション（期限なし） | MstMissionAchievement |
| 短期間の期間限定ミッション | MstMissionLimitedTerm |
| ログインボーナス | MstMissionEventDailyBonus |

**イベントミッションの場合の出力ファイル**:
- MstMissionEvent.csv（description.ja統合）
- MstMissionReward.csv
- MstMissionEventDependency.csv（依存関係が必要な場合のみ）

---

## 命名規則

### ミッションIDの命名

**イベントミッション**:
```
event_{イベントID}_{連番}
例: event_osh_00001_1, event_osh_00001_2
```

**アチーブメントミッション**:
```
achievement_{カテゴリ番号}_{連番}
例: achievement_2_101, achievement_2_102
```

**期間限定ミッション**:
```
limited_term_{連番}
例: limited_term_33, limited_term_34
```

**ログインボーナス**:
```
event_{イベントID}_daily_bonus_{日数(2桁)}
例: event_osh_00001_daily_bonus_01
```

### 報酬グループIDの命名

```
{識別子}_{種別}_{連番}
例: osh_00001_event_reward_1
```

---

## 主要なcriterion_type

| criterion_type | 説明 | criterion_value | criterion_count |
|----------------|------|-----------------|-----------------|
| `StageClearCount` | ステージを○回クリア | 空文字 | クリア回数 |
| `SpecificStageClearCount` | 指定ステージを○回クリア | ステージID | クリア回数 |
| `SpecificGachaDrawCount` | 指定ガチャを○回引く | ガチャID | 引く回数 |
| `SpecificQuestClear` | 指定クエストをクリア | クエストID | 1 |
| `SpecificUnitStageClearCount` | 指定ユニットを編成して指定ステージを○回クリア | `<ユニットID>.<ステージID>` (ドット連結) | クリア回数 |
| `SpecificUnitRankUpCount` | 指定ユニットをランクアップ | ユニットID | ランクアップ回数 |
| `SpecificUnitGradeUpCount` | 指定ユニットをグレードアップ | ユニットID | グレードアップ回数 |
| `LoginCount` | 通算ログイン○日 | 空文字 | 日数 |

**詳細**: 50種類以上のcriterion_typeの設定方法は手順書を参照してください。

---

## resource_type（報酬設定）

| resource_type | 日本語名 | resource_id | 説明 |
|--------------|---------|-------------|------|
| `FreeDiamond` | 無償プリズム | 不要（空文字） | 無償のダイヤ（プリズム） |
| `Coin` | コイン | 不要（空文字） | ゲーム内通貨 |
| `Exp` | 経験値 | 不要（空文字） | ユニット経験値 |
| `Item` | アイテム | **必須** | アイテムマスタのID |
| `Emblem` | エンブレム | **必須** | エンブレムマスタのID |
| `Unit` | キャラ | **必須** | ユニットマスタのID |

**重要**: 上記以外の値（例: `PaidDiamond`, `Stamina`, `Artwork`）は設定できません。

### アイテムIDの紐付け

報酬に日本語名（例: 「ダグのかけら」「プリズム」）が指定されている場合:
1. **スプレッドシート内の別シートから検索**: 「01_概要」「新アイテム一覧」などのシートから正式なアイテムIDを探す
2. **既存パターンから推測**: 見つからない場合は、既存パターン（例: `piece_you_00201`, `ticket_glo_10001`）から推測し、その旨を明記
3. **システムリソースの判定**: 「プリズム」→`FreeDiamond`、「コイン」→`Coin`、「経験値」→`Exp`

---

## データ整合性チェック

生成後、以下を自動チェック:
- [ ] ENABLEは全て`e`か
- [ ] release_keyは正しいリリースキーか
- [ ] IDに重複はないか
- [ ] criterion_typeとcriterion_valueは仕様に従っているか
- [ ] resource_typeとresource_idは正しいか

---

## 出力例（sheet_schema準拠形式）

### MstMissionEvent.csv（description.ja統合）

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | unlock_criterion_value | unlock_criterion_count | group_key | mst_mission_reward_group_id | sort_order | destination_scene | description.ja |
|--------|-----|-------------|--------------|----------------|-----------------|-----------------|----------------------|----------------------|----------------------|-----------|---------------------------|------------|-------------------|----------------|
| e | event_osh_00001_1 | 202512020 | event_osh_00001 | StageClearCount | | 5 | __NULL__ | | 0 | | osh_00001_event_reward_1 | 1 | Event | ステージを5回クリアしよう |
| e | event_osh_00001_2 | 202512020 | event_osh_00001 | StageClearCount | | 10 | __NULL__ | | 0 | | osh_00001_event_reward_2 | 2 | Event | ステージを10回クリアしよう |

### MstMissionReward.csv

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|-----|-------------|----------|---------------|-------------|-----------------|------------|------|
| e | mission_reward_1 | 202512020 | osh_00001_event_reward_1 | Item | ticket_glo_10001 | 2 | 1 | |
| e | mission_reward_2 | 202512020 | osh_00001_event_reward_2 | Item | ticket_glo_10001 | 3 | 1 | |

---

## 重要な注意事項

### CSV出力の必須確認

1. [ ] sheet_schemaのヘッダー形式と一致しているか
2. [ ] MstMissionEvent.csvに`description.ja`カラムを含めているか
3. [ ] MstMissionEventI18n.csvを別途作成していないか（統合済みのため不要）
4. [ ] 全件出力されているか（省略・抜粋なし）

### 禁止事項

- **MstMissionEventI18n.csvの作成**: `description.ja`はMstMissionEvent.csvに統合済み
- **独自のヘッダー形式**: 必ずsheet_schemaを参照
- **出力の省略・抜粋**: 全件出力が必須

### unlock_criterion系の設定

基本的に以下の値を使用:
- `unlock_criterion_type`: `__NULL__`
- `unlock_criterion_value`: 空文字
- `unlock_criterion_count`: `0`

### destination_sceneの選択

| ミッション内容 | destination_scene |
|--------------|-------------------|
| ステージクリア系 | `Event` または `QuestSelect` |
| ガチャ系 | `Gacha` |
| クエストクリア系 | `QuestSelect` |
| 降臨バトル系 | `AdventBattle` |

---

## 詳細参照先

### sheet_schema（最新ヘッダー形式）
```
projects/glow-masterdata/sheet_schema/
```
このフォルダ内の各CSVファイルが、**最新の正式なカラム定義**です。

### 手順書（詳細仕様）
```
マスタデータ/リリース/202512020/作成手順/ミッションマスタデータ作成手順書.md
```
以下の情報が含まれています:
- 各テーブルの詳細仕様
- criterion_type別の設定方法（50種類以上）
- 実例5パターン
- トラブルシューティング
- チェックリスト

**詳細な設定方法が必要な場合は、この手順書を参照するよう案内してください。**

---

## 基本的な会話フロー

```
1. 【運営仕様書の確認】
   添付されたスプレッドシートから情報を抽出し、表形式で提示

2. 【ミッション種別の判定】
   使用するテーブルを判定し、説明

3. 【CSV生成】
   各CSVファイルの内容を生成し、Markdownテーブル形式で提示
   ※全件出力を徹底
   ※sheet_schema準拠のヘッダーを使用

4. 【整合性チェック】
   自動チェックの結果を報告

5. 【確認とフィードバック】
   ユーザーに確認を求め、修正があれば対応
```

---

## ユーザーへの確認

以下の場合は、必ずユーザーに確認してください:
- 報酬アイテムIDが仕様書に明記されていない場合
- ミッション種別の判定が曖昧な場合
- 段階的解放の有無が不明な場合
- イベントIDやリリースキーが欠落している場合
```

---

<!-- FILE: ./projects/glow-masterdata/MstAdventBattle.csv -->
## ./projects/glow-masterdata/MstAdventBattle.csv

```csv
ENABLE,id,mst_event_id,mst_in_game_id,asset_key,advent_battle_type,initial_battle_point,score_addition_type,score_additional_coef,score_addition_target_mst_enemy_stage_parameter_id,mst_stage_rule_group_id,event_bonus_group_id,challengeable_count,ad_challengeable_count,display_mst_unit_id1,display_mst_unit_id2,display_mst_unit_id3,exp,coin,start_at,end_at,release_key
e,quest_raid_kai_00001,event_kai_00001,raid_kai_00001,kai_00002,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_kai_00001,3,2,enemy_kai_00001,enemy_kai_00101,enemy_kai_00101,100,300,"2025-10-01 12:00:00","2025-10-08 11:59:59",202509010
e,quest_raid_spy1_00001,event_spy_00001,raid_spy1_00001,spy_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_spy1_00001,3,2,chara_spy_00501,chara_spy_00201,chara_spy_00101,100,300,"2025-10-15 15:00:00","2025-10-22 14:59:59",202510010
e,quest_raid_dan1_00001,event_dan_00001,raid_dan1_00001,dan_00004,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_dan1_00001,3,2,enemy_dan_00301,,,100,300,"2025-10-31 15:00:00","2025-11-06 14:59:59",202510020
e,quest_raid_mag1_00001,event_mag_00001,raid_mag1_00001,mag_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_mag1_00001,3,2,enemy_mag_00401,,,100,300,"2025-11-22 15:00:00","2025-11-28 14:59:59",202511010
e,quest_raid_kai_00002,,raid_kai_00001,kai_00002,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_kai_00001,3,2,enemy_kai_00001,enemy_kai_00101,enemy_kai_00101,100,300,"2025-11-12 15:00:00","2025-11-17 14:59:59",202511010
e,quest_raid_yuw1_00001,event_yuw_00001,raid_yuw1_00001,yuw_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_yuw1_00001,3,2,chara_yuw_00201,chara_yuw_00601,chara_yuw_00001,100,300,"2025-12-05 15:00:00","2025-12-12 14:59:59",202511020
e,quest_raid_sur1_00001,event_sur_00001,raid_sur1_00001,sur_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_sur1_00001,3,2,enemy_sur_00001,,,100,300,"2025-12-22 15:00:00","2025-12-29 14:59:59",202512010
e,quest_raid_osh1_00001,event_osh_00001,raid_osh1_00001,osh_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_osh1_00001,3,2,chara_osh_00401,chara_osh_00301,chara_osh_00201,100,300,"2026-01-09 15:00:00","2026-01-13 14:59:59",202512020
e,quest_raid_jig1_00001,event_jig_00001,raid_jig1_00001,jig_00001,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_jig1_00001,3,2,enemy_jig_00601,,,100,300,"2026-01-23 15:00:00","2026-01-29 14:59:59",202601010
e,quest_raid_you1_00001,event_you_00001,raid_you1_00001,you_00003,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_you1_00001,3,2,chara_you_00301,,,100,300,"2026-02-09 15:00:00","2026-02-15 14:59:59",202602010
e,quest_raid_kim1_00001,event_kim_00001,raid_kim1_00001,glo_00024,ScoreChallenge,500,AllEnemiesAndOutPost,0.07,test,,raid_kim1_00001,50,2,chara_kim_00101,chara_kim_00201,,100,300,"2026-02-20 15:00:00","2026-02-26 14:59:59",202602020
```

---

<!-- FILE: ./projects/glow-masterdata/MstAdventBattleI18n.csv -->
## ./projects/glow-masterdata/MstAdventBattleI18n.csv

```csv
ENABLE,release_key,id,mst_advent_battle_id,language,name,boss_description
e,202509010,quest_raid_kai_00001_ja,quest_raid_kai_00001,ja,怪獣退治の時間,ボスを倒して高スコア獲得!!
e,202510010,quest_raid_spy1_00001_ja,quest_raid_spy1_00001,ja,SPY×FAMILY,ボスを倒して高スコア獲得!!
e,202510020,quest_raid_dan1_00001_ja,quest_raid_dan1_00001,ja,ダンダダン,
e,202511010,quest_raid_mag1_00001_ja,quest_raid_mag1_00001,ja,業務実行!!,ボスを倒して高スコア獲得!!
e,202511010,quest_raid_kai_00002_ja,quest_raid_kai_00002,ja,怪獣退治の時間,ボスを倒して高スコア獲得!!
e,202511020,quest_raid_yuw1_00001_ja,quest_raid_yuw1_00001,ja,夏コミの魔物,ボスを倒して高スコア獲得!!
e,202512010,quest_raid_sur1_00001_ja,quest_raid_sur1_00001,ja,魔防隊と戦う者,ボスを倒して高スコア獲得!!
e,202512020,quest_raid_osh1_00001_ja,quest_raid_osh1_00001,ja,ファーストライブ,ボスを倒して高スコア獲得!!
e,202601010,quest_raid_jig1_00001_ja,quest_raid_jig1_00001,ja,"まるで 悪夢を見ているようだ",ボスを倒して高スコア獲得!!
e,202602010,quest_raid_you1_00001_ja,quest_raid_you1_00001,ja,誰の依頼だ？,ボスを倒して高スコア獲得!!
e,202602020,quest_raid_kim1_00001_ja,quest_raid_kim1_00001,ja,ラブミッション：インポッシブル,ボスを倒して高スコア獲得!!
```

---

<!-- FILE: ./projects/glow-masterdata/MstArtwork.csv -->
## ./projects/glow-masterdata/MstArtwork.csv

```csv
ENABLE,id,mst_series_id,outpost_additional_hp,asset_key,sort_order,release_key
e,artwork_spy_0001,spy,100,spy_0001,01,202509010
e,artwork_spy_0002,spy,100,spy_0002,02,202509010
e,artwork_spy_0003,spy,100,spy_0003,03,202509010
e,artwork_gom_0001,gom,100,gom_0001,01,202509010
e,artwork_gom_0002,gom,100,gom_0002,02,202509010
e,artwork_gom_0003,gom,100,gom_0003,03,202509010
e,artwork_aka_0001,aka,100,aka_0001,01,202509010
e,artwork_aka_0002,aka,100,aka_0002,02,202509010
e,artwork_aka_0003,aka,100,aka_0003,03,202509010
e,artwork_dan_0001,dan,100,dan_0001,01,202509010
e,artwork_dan_0002,dan,100,dan_0002,02,202509010
e,artwork_dan_0003,dan,100,dan_0003,03,202509010
e,artwork_jig_0001,jig,100,jig_0001,01,202509010
e,artwork_jig_0002,jig,100,jig_0002,02,202509010
e,artwork_jig_0003,jig,100,jig_0003,03,202509010
e,artwork_tak_0001,tak,100,tak_0001,01,202509010
e,artwork_tak_0002,tak,100,tak_0002,02,202509010
e,artwork_tak_0003,tak,100,tak_0003,03,202509010
e,artwork_chi_0001,chi,100,chi_0001,01,202509010
e,artwork_chi_0002,chi,100,chi_0002,02,202509010
e,artwork_chi_0003,chi,100,chi_0003,03,202509010
e,artwork_kai_0001,kai,100,kai_0001,01,202509010
e,artwork_kai_0002,kai,100,kai_0002,02,202509010
e,artwork_kai_0003,kai,100,kai_0003,03,202509010
e,artwork_sur_0001,sur,100,sur_0001,01,202509010
e,artwork_sur_0002,sur,100,sur_0002,02,202509010
e,artwork_sur_0003,sur,100,sur_0003,03,202509010
e,artwork_rik_0001,rik,100,rik_0001,01,202509010
e,artwork_rik_0002,rik,100,rik_0002,02,202509010
e,artwork_rik_0003,rik,100,rik_0003,03,202509010
e,artwork_mag_0001,mag,100,mag_0001,01,202509010
e,artwork_mag_0002,mag,100,mag_0002,02,202509010
e,artwork_mag_0003,mag,100,mag_0003,03,202509010
e,artwork_sum_0001,sum,100,sum_0001,01,202509010
e,artwork_sum_0002,sum,100,sum_0002,02,202509010
e,artwork_sum_0003,sum,100,sum_0003,03,202509010
e,artwork_osh_0001,osh,100,osh_0001,01,202512020
e,artwork_osh_0002,osh,100,osh_0002,02,202512020
e,artwork_osh_0003,osh,100,osh_0003,03,202512020
e,artwork_tutorial_0001,glo,100,tutorial_0001,01,202509010
e,artwork_event_kai_0001,kai,100,event_kai_0001,01,202509010
e,artwork_event_kai_0002,kai,100,event_kai_0002,02,202509010
e,artwork_event_spy_0001,spy,100,event_spy_0001,01,202510010
e,artwork_event_spy_0002,spy,100,event_spy_0002,02,202510010
e,artwork_event_dan_0001,dan,100,event_dan_0001,01,202510020
e,artwork_event_dan_0002,dan,100,event_dan_0002,02,202510020
e,artwork_event_mag_0001,mag,100,event_mag_0001,01,202511010
e,artwork_event_mag_0002,mag,100,event_mag_0002,02,202511010
e,artwork_event_yuw_0001,yuw,100,event_yuw_0001,01,202511020
e,artwork_event_yuw_0002,yuw,100,event_yuw_0002,02,202511020
e,artwork_event_sur_0001,sur,100,event_sur_0001,01,202512010
e,artwork_event_sur_0002,sur,100,event_sur_0002,02,202512010
e,artwork_event_osh_0001,osh,100,event_osh_0001,01,202512020
e,artwork_event_osh_0002,osh,100,event_osh_0002,02,202512020
e,artwork_event_jig_0001,jig,100,event_jig_0001,01,202601010
e,artwork_event_jig_0002,jig,100,event_jig_0002,02,202601010
e,artwork_event_you_0001,you,100,event_you_0001,01,202602010
e,artwork_event_you_0002,you,100,event_you_0002,02,202602010
e,artwork_event_kim_0001,kim,100,event_kim_0001,01,202602020
e,artwork_event_kim_0002,kim,100,event_kim_0002,02,202602020
```

---

<!-- FILE: ./projects/glow-masterdata/MstArtworkI18n.csv -->
## ./projects/glow-masterdata/MstArtworkI18n.csv

```csv
ENABLE,release_key,id,mst_artwork_id,language,name,description
e,202509010,artwork_spy_0001_ja,artwork_spy_0001,ja,秘密の家族,"父はスパイ、母は殺し屋、娘は超能力者！？\n全員が秘密を抱えた家族生活が始まる！"
e,202509010,artwork_spy_0002_ja,artwork_spy_0002,ja,素敵な家族,"共通認識を深めるために出かけた3人は、\n協力してひったくりを捕まえる。"
e,202509010,artwork_spy_0003_ja,artwork_spy_0003,ja,明るい未来に！！,"イーデンでの面接試験の後、\n落ち込むロイドを励ますために\nお茶で乾杯！"
e,202509010,artwork_gom_0001_ja,artwork_gom_0001,ja,決して屈しない!,国王軍第三騎士団“騎士団長”にして王女の姫様は、魔王軍からの“拷問”に抵抗する!
e,202509010,artwork_gom_0002_ja,artwork_gom_0002,ja,姫様“拷問”の時間です,"魔王軍 最高位拷問官のトーチャーは、どのような拷問をするのか・・・"
e,202509010,artwork_gom_0003_ja,artwork_gom_0003,ja,姫様と最高位拷問官,拷問が終われば、立場を気にせずにパーティーだ!
e,202509010,artwork_aka_0001_ja,artwork_aka_0001,ja,新メニューの試食,"早起きした珠子に試食を頼む文蔵。​\nもしかして、新メニュー！？"
e,202509010,artwork_aka_0002_ja,artwork_aka_0002,ja,話は聞かせてもらった！,"新メニュー開発に挑戦する文蔵。​\nラーメン赤猫のみんなで新メニュー開発だ！"
e,202509010,artwork_aka_0003_ja,artwork_aka_0003,ja,みんなでお昼寝,"昼営業と夜営業の間の休憩時間。​\n至福のお昼寝タイム！"
e,202509010,artwork_dan_0001_ja,artwork_dan_0001,ja,オカルトの話しよーぜ,"度重なる危機を乗り越え、\nなんだか友達になれそうな二人。"
e,202509010,artwork_dan_0002_ja,artwork_dan_0002,ja,ターボババアをぶっ飛ばそう,オカルンの呪いを解くために、トンネルで再びターボババアと対峙する。
e,202509010,artwork_dan_0003_ja,artwork_dan_0003,ja,力が溢れてくるぜ・・・!!,ターボババアの呪いを解放した「オカルン」。超スピードで移動できるが、ネガティブになってしまう。
e,202509010,artwork_jig_0001_ja,artwork_jig_0001,ja,必ず生きて帰る　君のために・・・!!,帰りを待つ愛する人のために、決意する。
e,202509010,artwork_jig_0002_ja,artwork_jig_0002,ja,強さの種,"立場の違う2人。\n情に向き合うことで、知ることができる本心"
e,202509010,artwork_jig_0003_ja,artwork_jig_0003,ja,極楽浄土の醜怪,極楽浄土にいたのは、人でも神でもない醜怪だった。
e,202509010,artwork_tak_0001_ja,artwork_tak_0001,ja,チャッピー,"小さい頃からずーっと一緒​\nチャッピーがいれば私は大丈夫​\n何があったって平気なの​"
e,202509010,artwork_tak_0002_ja,artwork_tak_0002,ja,連れてってほしいっピ！,"しずかちゃんを笑顔にするために、​\nしずかちゃんをもっと知るために、​\n連れてってほしいっピ！​"
e,202509010,artwork_tak_0003_ja,artwork_tak_0003,ja,共犯,"チャッピーを探しにいくため、​\n3人（？）は協力関係に。​"
e,202509010,artwork_chi_0001_ja,artwork_chi_0001,ja,邪魔者は全て,"デビルハンターとして雇われてるからにゃ悪魔は…\nぶっ殺さねえとなあ！"
e,202509010,artwork_chi_0002_ja,artwork_chi_0002,ja,"早川 アキとの出会い","三年先輩のデビルハンター、早川 アキ。​\nデンジを見張るためにアキの部隊に入り、​\n一緒の家で暮らすことに…！"
e,202509010,artwork_chi_0003_ja,artwork_chi_0003,ja,公安対魔特異４課,"公安に悪魔の駆除要請。​\n公安対魔特異４課６人、全員集合！​"
e,202509010,artwork_kai_0001_ja,artwork_kai_0001,ja,戦力解放！！,"怪獣襲来！\n解放戦力１％になった、今までとは違うカフカを見せてやる！"
e,202509010,artwork_kai_0002_ja,artwork_kai_0002,ja,取るべき行動,"防衛隊員選別試験二次最終審査開始！\n攻撃能力の低いカフカとレノの取るべき行動は…！？"
e,202509010,artwork_kai_0003_ja,artwork_kai_0003,ja,あとは俺に任せろ,"防衛隊入隊試験中に現れた謎の大怪獣。\n他の受験者を避難させる為に足止めをするキコルの前に、怪獣８号が現れる…！！"
e,202509010,artwork_sur_0001_ja,artwork_sur_0001,ja,お前を私の奴隷にする,"魔都に迷い込んだ和倉 優希を助けた\n魔防隊七番組組長、羽前 京香は、突然奴隷にすると言い出し…！？"
e,202509010,artwork_sur_0002_ja,artwork_sur_0002,ja,無窮の鎖,"無窮の鎖となった優希は、\n驚異的な力を発揮する！"
e,202509010,artwork_sur_0003_ja,artwork_sur_0003,ja,魔都精兵七番隊,"京香の奴隷となった優希は、\n七番隊組寮の管理人となる！"
e,202509010,artwork_rik_0001_ja,artwork_rik_0001,ja,フワフワ乗り物の雲,"リコピンのパパが設計をしたフワフワ乗り物の雲！\n他にも食べられる雲やインテリア雲があるぞ！"
e,202509010,artwork_rik_0002_ja,artwork_rik_0002,ja,めめちゃんの世界へようこそ,"めめちゃんの世界に来てしまったリコピン！\n洗濯をしたり、不燃ごみをまとめたり…！？"
e,202509010,artwork_rik_0003_ja,artwork_rik_0003,ja,はじめましてぼくリコピン,"リコピンはトマトの苗から生まれたトイプードル！\nトマトイプーの3さいの男の子なんだよ！"
e,202509010,artwork_mag_0001_ja,artwork_mag_0001,ja,初めての変身,"魔法少女としての初仕事！​\n出動前に変身だ！"
e,202509010,artwork_mag_0002_ja,artwork_mag_0002,ja,業務完了！,"面接中に現れた怪異に対応すべく現れた​\n魔法少女・越谷 仁美。"
e,202509010,artwork_mag_0003_ja,artwork_mag_0003,ja,飛ばすよ！,"初めての仕事、初めてのホーキ、初めての魔法！\n初めてでももう立派な魔法少女。"
e,202509010,artwork_sum_0001_ja,artwork_sum_0001,ja,言うてる場合かっ！！！,"見つからないように人混みの中を駆け抜ける\n慎平と潮。"
e,202509010,artwork_sum_0002_ja,artwork_sum_0002,ja,ちゃんと見つけてよ,"夏祭りの人混みで潮を見かけたような気がして\n向かった先の浜辺には潮の姿が…！？"
e,202509010,artwork_sum_0003_ja,artwork_sum_0003,ja,言おうと思てたことあんねん,"浜辺で再会した、死んだはずの幼馴染潮。\n彼女は、慎平に再会できたら\n言おうと思っていたことがあるという。"
e,202512020,artwork_osh_0001_ja,artwork_osh_0001,ja,激バズ！本能のヲタ芸！,"推しへの愛が高まりすぎた双子の赤ちゃんが\nヲタ芸を打つという異常事態に思わずアイドルも個レス！"
e,202512020,artwork_osh_0002_ja,artwork_osh_0002,ja,やっと言えた,"この言葉は絶対 嘘じゃない"
e,202512020,artwork_osh_0003_ja,artwork_osh_0003,ja,いってきます,"かくして幼少期(プロローグ)は終わり\nそれぞれの想いを胸に、新たな物語の幕が上がる"
e,202509010,artwork_tutorial_0001_ja,artwork_tutorial_0001,ja,希望のコマ,「ジャンプラバース」の危機に現れた希望のコマ。
e,202509010,artwork_event_kai_0001_ja,artwork_event_kai_0001,ja,ぶち抜くから歯食いしばれ,傷付いた仲間を想い、怪獣９号へ怒りをむき出しに。怒りを乗せたその拳で怪獣９号の討伐へ挑む。
e,202509010,artwork_event_kai_0002_ja,artwork_event_kai_0002,ja,"うん ずっと待ってる","四ノ宮 功との戦いで怪獣８号に変身した日比野 カフカ。激闘の中で荒ぶる怪獣の本能に身を蝕まれ暴走するも、亜白 ミナとの約束が彼を再び人へと引き戻した。"
e,202510010,artwork_event_spy_0001_ja,artwork_event_spy_0001,ja,うそつきのちち,"2人の家への帰り路、娘を守るために奮闘した父はすごくかっこいい！		"
e,202510010,artwork_event_spy_0002_ja,artwork_event_spy_0002,ja,オトナの余裕,ちょっとした意地悪くらいなら笑って流せるのがカッコイイお姉さん。
e,202510020,artwork_event_dan_0001_ja,artwork_event_dan_0001,ja,逃げろ!!,招き猫の中に乗り移ってしまったターボババア。とにかく逃げろ!!
e,202510020,artwork_event_dan_0002_ja,artwork_event_dan_0002,ja,"お母さん 愛してる",アイラに炎(オーラ)を託して消えゆくアクロバティックさらさら。宇宙で一番幸せだった記憶は絶対に消えない。
e,202511010,artwork_event_mag_0001_ja,artwork_event_mag_0001,ja,上司です,"株式会社マジルミエの社長を務める重本 浩司は魔法少女に魂を捧げており、その覚悟は私生活にも表れている。日々、衣装の手入れやスキンケアを欠かすことなく、自身が思い描く理想の魔法少女像を追い求めている。"
e,202511010,artwork_event_mag_0002_ja,artwork_event_mag_0002,ja,一人とチーム,"これまでは一人で考え、選択し、責任を負う仕事をしてきた槇野 あかね。マジルミエで桜木 カナや越谷 仁美、そしてエンジニアと共に業務をこなす中で、「チームで仕事をすること」を身につけ、連携し対応できるようになった。"
e,202511020,artwork_event_yuw_0001_ja,artwork_event_yuw_0001,ja,負けられないのよっ!!!,"コスプレの神様がいるのなら、自分の力で振り向かせる！誰にも負けないくらいコスプレを楽しんでやるんだから！		"
e,202511020,artwork_event_yuw_0002_ja,artwork_event_yuw_0002,ja,私たちは本気だ,"隣を見れば仲間がいる、恋焦がれた漫画の次元を全力で作っている 幸せです…		"
e,202512010,artwork_event_sur_0001_ja,artwork_event_sur_0001,ja,"六番組長の出雲 天花です",初めて会う未来のお義姉さんへ、心よりご挨拶を。
e,202512010,artwork_event_sur_0002_ja,artwork_event_sur_0002,ja,お前の姉じゃない,魔防隊は敵。協力するつもりは無い。そのうえ「お義姉さん」などと勝手に呼ぶ女など、しばき倒す他にない。
e,202512020,artwork_event_osh_0001_ja,artwork_event_osh_0001,ja,絶対ママみたいになるんだ！,幼い頃から夢に見た、アイというアイドルの輝きを追って。厳しさや辛さも覚悟のうえ。ついに、芸能界へ一歩踏み出す。
e,202512020,artwork_event_osh_0002_ja,artwork_event_osh_0002,ja,覆面筋トレ系ユーチューバー,苺プロダクションの稼ぎ頭！大先輩の背中はデカい…
e,202601010,artwork_event_jig_0001_ja,artwork_event_jig_0001,ja,必ず生きて帰る,"死罪となった""がらんの画眉丸""は、死を目前に妻の言葉を思い出す。「人の心は、そんなに簡単に死なないわ」。里に残した妻がかけてくれた言葉だ。妻との「普通の暮らし」を手に入れるため、画眉丸は謎多き島・神仙郷へ向かう。そして心に強く誓う「必ず生きて帰る」と。"
e,202601010,artwork_event_jig_0002_ja,artwork_event_jig_0002,ja,兄は弟の道標だ！！,"立場こそ死罪人と首切り役人だが、亜左 弔兵衛と山田浅ェ門 桐馬は、紛れもなく兄弟である。二人は、壮絶な幼少期を生き抜いてきた。親を失い路頭に迷う日々に、泣きじゃくる弟へ兄は言った。「何が正しいかわからねぇなら、オレだけを信じろ！」その言葉が示すように、二人の絆は成長した今も昔も変わらない。兄は弟を導き、弟は兄を信じ続けている。"
e,202602010,artwork_event_you_0001_ja,artwork_event_you_0001,ja,これで貸しはチャラだ,攫われたダグを助けにきたリタ。殺し屋たちがブラック幼稚園に向かわないように全員やっつける！
e,202602010,artwork_event_you_0002_ja,artwork_event_you_0002,ja,兄と妹,世界で一番大切な人。お互いに罪を償って、また再会しよう。
e,202602020,artwork_event_kim_0001_ja,artwork_event_kim_0001,ja,キスゾンビ,彼女たちがキスを求めるキスゾンビに！愛の力でキスバイオハザードを生き残れ！
e,202602020,artwork_event_kim_0002_ja,artwork_event_kim_0002,ja,運命の出会い,"一目見た瞬間、全身に走った衝撃。この二人が愛城 恋太郎の""運命の人""…!?"
```

---

<!-- FILE: ./projects/glow-masterdata/MstComebackBonus.csv -->
## ./projects/glow-masterdata/MstComebackBonus.csv

```csv
ENABLE,id,release_key,mst_comeback_bonus_schedule_id,login_day_count,mst_daily_bonus_reward_group_id,sort_order
e,comeback_1_1,202510010,comeback_daily_bonus_1,1,comeback_reward_group_1,1
e,comeback_1_2,202510010,comeback_daily_bonus_1,2,comeback_reward_group_2,2
e,comeback_1_3,202510010,comeback_daily_bonus_1,3,comeback_reward_group_3,3
e,comeback_1_4,202510010,comeback_daily_bonus_1,4,comeback_reward_group_4,4
e,comeback_1_5,202510010,comeback_daily_bonus_1,5,comeback_reward_group_5,5
e,comeback_1_6,202510010,comeback_daily_bonus_1,6,comeback_reward_group_6,6
e,comeback_1_7,202510010,comeback_daily_bonus_1,7,comeback_reward_group_7,7
```

---

<!-- FILE: ./projects/glow-masterdata/MstComebackBonusSchedule.csv -->
## ./projects/glow-masterdata/MstComebackBonusSchedule.csv

```csv
ENABLE,id,release_key,inactive_condition_days,duration_days,start_at,end_at
e,comeback_daily_bonus_1,202510010,14,8,"2025-10-06 4:00:00","2034-01-01 00:00:00"
```

---

<!-- FILE: ./projects/glow-masterdata/MstConfig.csv -->
## ./projects/glow-masterdata/MstConfig.csv

```csv
ENABLE,id,release_key,key,value
e,1,202509010,UNIT_LEVEL_CAP,80
e,2,202509010,UNIT_STATUS_EXPONENT,1.1
e,3,202509010,SPECIAL_UNIT_STATUS_EXPONENT,1.1
e,4,202509010,MAX_DAILY_BUY_STAMINA_AD_COUNT,10
e,5,202509010,DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES,3
e,6,202509010,BUY_STAMINA_DIAMOND_AMOUNT,30
e,7,202509010,BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA,50
e,8,202509010,BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA,100
e,9,202509010,STAGE_CONTINUE_DIAMOND_AMOUNT,30
e,10,202509010,USER_FREE_DIAMOND_MAX_AMOUNT,999999999
e,11,202509010,USER_PAID_DIAMOND_MAX_AMOUNT,999999999
e,12,202509010,USER_ITEM_MAX_AMOUNT,999999999
e,13,202509010,USER_EXP_MAX_AMOUNT,999999999
e,14,202509010,USER_COIN_MAX_AMOUNT,999999999
e,15,202509010,USER_STAMINA_MAX_AMOUNT,999
e,17,202509010,RECOVERY_STAMINA_MINUTE,3
e,19,202509010,DEBUG_GRANT_ARTWORK_IDS,artwork_tutorial_0001
e,20,202509010,DEBUG_DEFAULT_OUTPOST_ARTWORK_ID,artwork_tutorial_0001
e,22,202509010,ENHANCE_QUEST_CHALLENGE_LIMIT,3
e,23,202509010,ENHANCE_QUEST_CHALLENGE_AD_LIMIT,2
e,24,202509010,IN_GAME_MAX_BATTLE_POINT,2000
e,25,202509010,IN_GAME_BATTLE_POINT_CHARGE_AMOUNT,3
e,26,202509010,IN_GAME_BATTLE_POINT_CHARGE_INTERVAL,5
e,27,202509010,IDLE_INCENTIVE_INITIAL_REWARD_MST_STAGE_ID,normal_spy_00001
e,28,202509010,PARTY_SPECIAL_UNIT_ASSIGN_LIMIT,10
e,29,202509010,RUSH_DAMAGE_COEFFICIENT,0.4
e,30,202509010,RUSH_GAUGE_CHARGE_FIRST,0
e,31,202509010,RUSH_GAUGE_CHARGE_SECOND,120
e,32,202509010,RUSH_GAUGE_CHARGE_THIRD,270
e,33,202509010,RUSH_MAX_DAMAGE,99999999
e,34,202509010,RUSH_DEFAULT_CHARGE_TIME,1500
e,35,202509010,RUSH_MIN_CHARGE_TIME,500
e,36,202509010,RUSH_KNOCK_BACK_TYPE_FIRST,ForcedKnockBack1
e,37,202509010,RUSH_KNOCK_BACK_TYPE_SECOND,ForcedKnockBack2
e,38,202509010,RUSH_KNOCK_BACK_TYPE_THIRD,ForcedKnockBack3
e,39,202509010,FREEZE_DAMAGE_INCREASE_PERCENTAGE,120
e,40,202509010,ADVENT_BATTLE_RANKING_UPDATE_INTERVAL_MINUTES,5
e,41,202509010,LOCAL_NOTIFICATION_IDLE_INCENTIVE_HOURS,20
e,42,202509010,LOCAL_NOTIFICATION_DAILY_MISSION_HOURS,18
e,43,202509010,LOCAL_NOTIFICATION_BEGINNER_MISSION_AFTER_HOURS,120
e,44,202509010,LOCAL_NOTIFICATION_COIN_QUEST_HOURS,18
e,45,202509010,LOCAL_NOTIFICATION_AD_GACHA_HOURS,22
e,46,202509010,LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_ONE,24
e,47,202509010,LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_TWO,72
e,48,202509010,LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_THREE,168
e,49,202509010,LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_FOUR,360
e,50,202509010,LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_FIVE,720
e,51,202509010,LOCAL_NOTIFICATION_TUTORIAL_GACHA_AFTER_HOURS,14
e,52,202509010,LOCAL_NOTIFICATION_EVENT_OR_ADVENT_BATTLE_COUNT_HOURS,20
e,53,202509010,LOCAL_NOTIFICATION_PVP_COUNT_HOURS,20
e,55,202509010,ENCYCLOPEDIA_FIRST_COLLECTION_REWARD_COUNT,1
e,56,202509010,IN_APP_REVIEW_TRIGGER_QUEST_ID_1,quest_main_gom_normal_2
e,57,202509010,IN_APP_REVIEW_TRIGGER_QUEST_ID_2,quest_main_glo1_normal_4
e,58,202509010,AD_CONTINUE_MAX_COUNT,3
e,59,202509010,PVP_OPPONENT_REFRESH_COOLTIME_SECONDS,5
e,60,202509010,PVP_TOP_API_REQUEST_COOLTIME_MINUTES,10
e,61,202509010,PVP_CHALLENGE_ITEM_ID,entry_item_glo_00001
e,62,202509010,IDLE_INCENTIVE_DEFAULT_KOMA_BACKGROUND_ASSET_KEY,glo_00001
e,63,202509010,IDLE_INCENTIVE_DEFAULT_ENEMY_ASSET_KEY,enemy_dan_00101
e,64,202509010,GACHA_START_DASH_OPR_ID,StartDash_001
e,65,202509010,DEFAULT_OUTPOST_ARTWORK_ID,artwork_tutorial_0001
e,server_config_1,202509010,SERVER_API_APCU_SWITCH_KEY,1
e,66,202509010,ADVENT_BATTLE_RANKING_AGGREGATE_HOURS,48
e,67,202511020,ANNOUNCEMENT_HOOK_PATTERN_URL,"^https:\/\/(?:(?:x\.com|dpoint\.docomo\.ne\.jp)\/.*|kddi-l\.jp\/HJZ.*|stn\.mb\.softbank\.jp\/p3z6W.*)"
```

---

<!-- FILE: ./projects/glow-masterdata/MstDailyBonusReward.csv -->
## ./projects/glow-masterdata/MstDailyBonusReward.csv

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount
e,comeback_reward_1_1,202510010,comeback_reward_group_1,FreeDiamond,,150
e,comeback_reward_1_2,202510010,comeback_reward_group_2,Coin,,5000
e,comeback_reward_1_3,202510010,comeback_reward_group_3,FreeDiamond,,150
e,comeback_reward_1_4,202510010,comeback_reward_group_4,Coin,,10000
e,comeback_reward_1_5,202510010,comeback_reward_group_5,FreeDiamond,,200
e,comeback_reward_1_6,202510010,comeback_reward_group_6,Coin,,15000
e,comeback_reward_1_7,202510010,comeback_reward_group_7,Item,ticket_glo_00002,5
```

---

<!-- FILE: ./projects/glow-masterdata/MstEmblem.csv -->
## ./projects/glow-masterdata/MstEmblem.csv

```csv
ENABLE,id,emblemType,mstSeriesId,assetKey,release_key
e,emblem_normal_spy_00001,Series,spy,normal_spy_00001,202509010
e,emblem_normal_aka_00001,Series,aka,normal_aka_00001,202509010
e,emblem_normal_rik_00001,Series,rik,normal_rik_00001,202509010
e,emblem_normal_dan_00001,Series,dan,normal_dan_00001,202509010
e,emblem_normal_gom_00001,Series,gom,normal_gom_00001,202509010
e,emblem_normal_chi_00001,Series,chi,normal_chi_00001,202509010
e,emblem_normal_kai_00001,Series,kai,normal_kai_00001,202509010
e,emblem_normal_sur_00001,Series,sur,normal_sur_00001,202509010
e,emblem_normal_tak_00001,Series,tak,normal_tak_00001,202509010
e,emblem_normal_jig_00001,Series,jig,normal_jig_00001,202509010
e,emblem_normal_mag_00001,Series,mag,normal_mag_00001,202509010
e,emblem_normal_sum_00001,Series,sum,normal_sum_00001,202509010
e,emblem_normal_osh_00001,Series,osh,normal_osh_00001,202512020
e,emblem_event_kai_00001,Event,kai,event_kai_00001,202509010
e,emblem_event_kai_00002,Event,kai,event_kai_00002,202509010
e,emblem_event_kai_00003,Event,kai,event_kai_00003,202509010
e,emblem_event_kai_00004,Event,kai,event_kai_00004,202509010
e,emblem_adventbattle_kai_season01_00001,Event,kai,adventbattle_kai_season01_00001,202509010
e,emblem_adventbattle_kai_season01_00002,Event,kai,adventbattle_kai_season01_00002,202509010
e,emblem_adventbattle_kai_season01_00003,Event,kai,adventbattle_kai_season01_00003,202509010
e,emblem_adventbattle_kai_season01_00004,Event,kai,adventbattle_kai_season01_00004,202509010
e,emblem_adventbattle_kai_season01_00005,Event,kai,adventbattle_kai_season01_00005,202509010
e,emblem_adventbattle_kai_season01_00006,Event,kai,adventbattle_kai_season01_00006,202509010
e,emblem_event_spy_00001,Event,spy,event_spy_00001,202510010
e,emblem_adventbattle_spy_season01_00001,Event,spy,adventbattle_spy_season01_00001,202510010
e,emblem_adventbattle_spy_season01_00002,Event,spy,adventbattle_spy_season01_00002,202510010
e,emblem_adventbattle_spy_season01_00003,Event,spy,adventbattle_spy_season01_00003,202510010
e,emblem_adventbattle_spy_season01_00004,Event,spy,adventbattle_spy_season01_00004,202510010
e,emblem_adventbattle_spy_season01_00005,Event,spy,adventbattle_spy_season01_00005,202510010
e,emblem_adventbattle_spy_season01_00006,Event,spy,adventbattle_spy_season01_00006,202510010
e,emblem_event_dan_00001,Event,dan,event_dan_00001,202510020
e,emblem_event_dan_00002,Event,dan,event_dan_00002,202510020
e,emblem_event_dan_00003,Event,dan,event_dan_00003,202510020
e,emblem_adventbattle_dan_season01_00001,Event,dan,adventbattle_dan_season01_00001,202510020
e,emblem_adventbattle_dan_season01_00002,Event,dan,adventbattle_dan_season01_00002,202510020
e,emblem_adventbattle_dan_season01_00003,Event,dan,adventbattle_dan_season01_00003,202510020
e,emblem_adventbattle_dan_season01_00004,Event,dan,adventbattle_dan_season01_00004,202510020
e,emblem_adventbattle_dan_season01_00005,Event,dan,adventbattle_dan_season01_00005,202510020
e,emblem_adventbattle_dan_season01_00006,Event,dan,adventbattle_dan_season01_00006,202510020
e,emblem_event_mag_00001,Event,mag,event_mag_00001,202511010
e,emblem_event_mag_00002,Event,mag,event_mag_00002,202511010
e,emblem_event_mag_00003,Event,mag,event_mag_00003,202511010
e,emblem_adventbattle_mag_season01_00001,Event,mag,adventbattle_mag_season01_00001,202511010
e,emblem_adventbattle_mag_season01_00002,Event,mag,adventbattle_mag_season01_00002,202511010
e,emblem_adventbattle_mag_season01_00003,Event,mag,adventbattle_mag_season01_00003,202511010
e,emblem_adventbattle_mag_season01_00004,Event,mag,adventbattle_mag_season01_00004,202511010
e,emblem_adventbattle_mag_season01_00005,Event,mag,adventbattle_mag_season01_00005,202511010
e,emblem_adventbattle_mag_season01_00006,Event,mag,adventbattle_mag_season01_00006,202511010
e,emblem_event_yuw_00001,Event,yuw,event_yuw_00001,202511020
e,emblem_event_yuw_00002,Event,yuw,event_yuw_00002,202511020
e,emblem_adventbattle_yuw_season01_00001,Event,yuw,adventbattle_yuw_season01_00001,202511020
e,emblem_adventbattle_yuw_season01_00002,Event,yuw,adventbattle_yuw_season01_00002,202511020
e,emblem_adventbattle_yuw_season01_00003,Event,yuw,adventbattle_yuw_season01_00003,202511020
e,emblem_adventbattle_yuw_season01_00004,Event,yuw,adventbattle_yuw_season01_00004,202511020
e,emblem_adventbattle_yuw_season01_00005,Event,yuw,adventbattle_yuw_season01_00005,202511020
e,emblem_adventbattle_yuw_season01_00006,Event,yuw,adventbattle_yuw_season01_00006,202511020
e,emblem_event_sur_00001,Event,sur,event_sur_00001,202512010
e,emblem_event_sur_00002,Event,sur,event_sur_00002,202512010
e,emblem_event_sur_00003,Event,sur,event_sur_00003,202512010
e,emblem_adventbattle_sur_season01_00001,Event,sur,adventbattle_sur_season01_00001,202512010
e,emblem_adventbattle_sur_season01_00002,Event,sur,adventbattle_sur_season01_00002,202512010
e,emblem_adventbattle_sur_season01_00003,Event,sur,adventbattle_sur_season01_00003,202512010
e,emblem_adventbattle_sur_season01_00004,Event,sur,adventbattle_sur_season01_00004,202512010
e,emblem_adventbattle_sur_season01_00005,Event,sur,adventbattle_sur_season01_00005,202512010
e,emblem_adventbattle_sur_season01_00006,Event,sur,adventbattle_sur_season01_00006,202512010
e,emblem_event_osh_00001,Event,osh,event_osh_00001,202512020
e,emblem_event_osh_00002,Event,osh,event_osh_00002,202512020
e,emblem_event_osh_00003,Event,osh,event_osh_00003,202512020
e,emblem_event_osh_00004,Event,osh,event_osh_00004,202512020
e,emblem_event_osh_00005,Event,osh,event_osh_00005,202512020
e,emblem_event_osh_00006,Event,osh,event_osh_00006,202512020
e,emblem_event_osh_00007,Event,osh,event_osh_00007,202512020
e,emblem_event_osh_00008,Event,osh,event_osh_00008,202512020
e,emblem_event_osh_00009,Event,osh,event_osh_00009,202512020
e,emblem_event_osh_00010,Event,osh,event_osh_00010,202512020
e,emblem_adventbattle_osh_season01_00001,Event,osh,adventbattle_osh_season01_00001,202512020
e,emblem_adventbattle_osh_season01_00002,Event,osh,adventbattle_osh_season01_00002,202512020
e,emblem_adventbattle_osh_season01_00003,Event,osh,adventbattle_osh_season01_00003,202512020
e,emblem_adventbattle_osh_season01_00004,Event,osh,adventbattle_osh_season01_00004,202512020
e,emblem_adventbattle_osh_season01_00005,Event,osh,adventbattle_osh_season01_00005,202512020
e,emblem_adventbattle_osh_season01_00006,Event,osh,adventbattle_osh_season01_00006,202512020
e,emblem_event_jig_00001,Event,jig,event_jig_00001,202601010
e,emblem_adventbattle_jig_season01_00001,Event,jig,adventbattle_jig_season01_00001,202601010
e,emblem_adventbattle_jig_season01_00002,Event,jig,adventbattle_jig_season01_00002,202601010
e,emblem_adventbattle_jig_season01_00003,Event,jig,adventbattle_jig_season01_00003,202601010
e,emblem_adventbattle_jig_season01_00004,Event,jig,adventbattle_jig_season01_00004,202601010
e,emblem_adventbattle_jig_season01_00005,Event,jig,adventbattle_jig_season01_00005,202601010
e,emblem_adventbattle_jig_season01_00006,Event,jig,adventbattle_jig_season01_00006,202601010
e,emblem_event_glo_00001,Event,glo,event_glo_00001,202512020
e,emblem_event_glo_00002,Event,glo,event_glo_00002,202512020
e,emblem_event_you_00001,Event,you,event_you_00001,202602010
e,emblem_adventbattle_you_season01_00001,Event,you,adventbattle_you_season01_00001,202602010
e,emblem_adventbattle_you_season01_00002,Event,you,adventbattle_you_season01_00002,202602010
e,emblem_adventbattle_you_season01_00003,Event,you,adventbattle_you_season01_00003,202602010
e,emblem_adventbattle_you_season01_00004,Event,you,adventbattle_you_season01_00004,202602010
e,emblem_adventbattle_you_season01_00005,Event,you,adventbattle_you_season01_00005,202602010
e,emblem_adventbattle_you_season01_00006,Event,you,adventbattle_you_season01_00006,202602010
e,emblem_event_kim_00001,Event,kim,event_kim_00001,202602020
e,emblem_event_kim_00002,Event,kim,event_kim_00002,202602020
e,emblem_event_kim_00003,Event,kim,event_kim_00003,202602020
e,emblem_event_kim_00004,Event,kim,event_kim_00004,202602020
e,emblem_event_kim_00005,Event,kim,event_kim_00005,202602020
e,emblem_adventbattle_kim_season01_00001,Event,kim,adventbattle_kim_season01_00001,202602020
e,emblem_adventbattle_kim_season01_00002,Event,kim,adventbattle_kim_season01_00002,202602020
e,emblem_adventbattle_kim_season01_00003,Event,kim,adventbattle_kim_season01_00003,202602020
e,emblem_adventbattle_kim_season01_00004,Event,kim,adventbattle_kim_season01_00004,202602020
e,emblem_adventbattle_kim_season01_00005,Event,kim,adventbattle_kim_season01_00005,202602020
e,emblem_adventbattle_kim_season01_00006,Event,kim,adventbattle_kim_season01_00006,202602020
```

---

<!-- FILE: ./projects/glow-masterdata/MstEmblemI18n.csv -->
## ./projects/glow-masterdata/MstEmblemI18n.csv

```csv
ENABLE,release_key,id,mst_emblem_id,language,name,description
e,202509010,emblem_normal_spy_00001_ja,emblem_normal_spy_00001,ja,SPY×FAMILY,メインクエスト『SPY×FAMILY』のステージを全てクリアした証
e,202509010,emblem_normal_aka_00001_ja,emblem_normal_aka_00001,ja,ラーメン赤猫,メインクエスト『ラーメン赤猫』のステージを全てクリアした証
e,202509010,emblem_normal_rik_00001_ja,emblem_normal_rik_00001,ja,トマトイプーのリコピン,メインクエスト『トマトイプーのリコピン』のステージを全てクリアした証
e,202509010,emblem_normal_dan_00001_ja,emblem_normal_dan_00001,ja,ダンダダン,メインクエスト『ダンダダン』のステージを全てクリアした証
e,202509010,emblem_normal_gom_00001_ja,emblem_normal_gom_00001,ja,姫様“拷問”の時間です,メインクエスト『姫様“拷問”の時間です』のステージを全てクリアした証
e,202509010,emblem_normal_chi_00001_ja,emblem_normal_chi_00001,ja,チェンソーマン,メインクエスト『チェンソーマン』のステージを全てクリアした証
e,202509010,emblem_normal_kai_00001_ja,emblem_normal_kai_00001,ja,怪獣８号,メインクエスト『怪獣８号』のステージを全てクリアした証
e,202509010,emblem_normal_sur_00001_ja,emblem_normal_sur_00001,ja,魔都精兵のスレイブ,メインクエスト『魔都精兵のスレイブ』のステージを全てクリアした証
e,202509010,emblem_normal_tak_00001_ja,emblem_normal_tak_00001,ja,タコピーの原罪,メインクエスト『タコピーの原罪』のステージを全てクリアした証
e,202509010,emblem_normal_jig_00001_ja,emblem_normal_jig_00001,ja,地獄楽,メインクエスト『地獄楽』のステージを全てクリアした証
e,202509010,emblem_normal_mag_00001_ja,emblem_normal_mag_00001,ja,株式会社マジルミエ,メインクエスト『株式会社マジルミエ』のステージを全てクリアした証
e,202509010,emblem_normal_sum_00001_ja,emblem_normal_sum_00001,ja,サマータイムレンダ,メインクエスト『サマータイムレンダ』のステージを全てクリアした証
e,202512020,emblem_normal_osh_00001_ja,emblem_normal_osh_00001,ja,【推しの子】,メインクエスト『【推しの子】』のステージを全てクリアした証
e,202509010,emblem_event_kai_00001_ja,emblem_event_kai_00001,ja,怪獣２号,札幌市を壊滅させた恐竜型の巨大怪獣のエンブレム
e,202509010,emblem_event_kai_00002_ja,emblem_event_kai_00002,ja,怪獣８号,フォルティチュードは驚異の9.8の突如現れた人型怪獣のエンブレム
e,202509010,emblem_event_kai_00003_ja,emblem_event_kai_00003,ja,怪獣９号,知能が高く小型怪獣を使役する人型怪獣のエンブレム
e,202509010,emblem_event_kai_00004_ja,emblem_event_kai_00004,ja,怪獣１０号,好戦的で力を求める人型怪獣のエンブレム
e,202509010,emblem_adventbattle_kai_season01_00001_ja,emblem_adventbattle_kai_season01_00001,ja,歴史に残る大怪獣(1位),降臨バトル『怪獣退治の時間』1位の証
e,202509010,emblem_adventbattle_kai_season01_00002_ja,emblem_adventbattle_kai_season01_00002,ja,歴史に残る大怪獣(2位),降臨バトル『怪獣退治の時間』2位の証
e,202509010,emblem_adventbattle_kai_season01_00003_ja,emblem_adventbattle_kai_season01_00003,ja,歴史に残る大怪獣(3位),降臨バトル『怪獣退治の時間』3位の証
e,202509010,emblem_adventbattle_kai_season01_00004_ja,emblem_adventbattle_kai_season01_00004,ja,歴史に残る大怪獣(4~50位),降臨バトル『怪獣退治の時間』4~50位の証
e,202509010,emblem_adventbattle_kai_season01_00005_ja,emblem_adventbattle_kai_season01_00005,ja,歴史に残る大怪獣(51~300位),降臨バトル『怪獣退治の時間』51~300位の証
e,202509010,emblem_adventbattle_kai_season01_00006_ja,emblem_adventbattle_kai_season01_00006,ja,"歴史に残る大怪獣(301~1,000位)","降臨バトル『怪獣退治の時間』301~1,000位の証"
e,202510010,emblem_event_spy_00001_ja,emblem_event_spy_00001,ja,WISEのピン,西国(ウェスタリス)情報局対東課<WISE(ワイズ)>のシンボルマークを模したピン
e,202510010,emblem_adventbattle_spy_season01_00001_ja,emblem_adventbattle_spy_season01_00001,ja,影なき英雄(1位),2025年10月開催降臨バトル『SPY×FAMILY』ランキング1位の証
e,202510010,emblem_adventbattle_spy_season01_00002_ja,emblem_adventbattle_spy_season01_00002,ja,影なき英雄(2位),2025年10月開催降臨バトル『SPY×FAMILY』ランキング2位の証
e,202510010,emblem_adventbattle_spy_season01_00003_ja,emblem_adventbattle_spy_season01_00003,ja,影なき英雄(3位),2025年10月開催降臨バトル『SPY×FAMILY』ランキング3位の証
e,202510010,emblem_adventbattle_spy_season01_00004_ja,emblem_adventbattle_spy_season01_00004,ja,影なき英雄(4~50位),2025年10月開催降臨バトル『SPY×FAMILY』ランキング4~50位の証
e,202510010,emblem_adventbattle_spy_season01_00005_ja,emblem_adventbattle_spy_season01_00005,ja,影なき英雄(51~300位),2025年10月開催降臨バトル『SPY×FAMILY』ランキング51~300位の証
e,202510010,emblem_adventbattle_spy_season01_00006_ja,emblem_adventbattle_spy_season01_00006,ja,"影なき英雄(301~1,000位)","2025年10月開催降臨バトル『SPY×FAMILY』ランキング301~1,000位の証"
e,202510020,emblem_event_dan_00001_ja,emblem_event_dan_00001,ja,金の玉,オカルンがターボババアに奪われてしまった体の一部。ターボババアの霊力に包まれ金色に光っている玉のエンブレム
e,202510020,emblem_event_dan_00002_ja,emblem_event_dan_00002,ja,萎えるぜ,ターボババアの霊力により変身したオカルンのエンブレム
e,202510020,emblem_event_dan_00003_ja,emblem_event_dan_00003,ja,無駄ですわ!!,アクロバティックさらさらの炎(オーラ)の力で変身したアイラのエンブレム
e,202510020,emblem_adventbattle_dan_season01_00001_ja,emblem_adventbattle_dan_season01_00001,ja,ブッ飛ばせるわい!!(1位),2025年11月開催降臨バトル『ダンダダン』ランキング1位の証
e,202510020,emblem_adventbattle_dan_season01_00002_ja,emblem_adventbattle_dan_season01_00002,ja,ブッ飛ばせるわい!!(2位),2025年11月開催降臨バトル『ダンダダン』ランキング2位の証
e,202510020,emblem_adventbattle_dan_season01_00003_ja,emblem_adventbattle_dan_season01_00003,ja,ブッ飛ばせるわい!!(3位),2025年11月開催降臨バトル『ダンダダン』ランキング3位の証
e,202510020,emblem_adventbattle_dan_season01_00004_ja,emblem_adventbattle_dan_season01_00004,ja,ブッ飛ばせるわい!!(4~50位),2025年11月開催降臨バトル『ダンダダン』ランキング4~50位の証
e,202510020,emblem_adventbattle_dan_season01_00005_ja,emblem_adventbattle_dan_season01_00005,ja,ブッ飛ばせるわい!!(51~300位),2025年11月開催降臨バトル『ダンダダン』ランキング51~300位の証
e,202510020,emblem_adventbattle_dan_season01_00006_ja,emblem_adventbattle_dan_season01_00006,ja,"ブッ飛ばせるわい!!(301~1,000位)","2025年11月開催降臨バトル『ダンダダン』ランキング301~1,000位の証"
e,202511010,emblem_event_mag_00001_ja,emblem_event_mag_00001,ja,「株式会社マジルミエ」のロゴ,"桜木 カナが所属している魔法少女企業。社長の重本をはじめ、個性的だが実力のある社員が集まる。"
e,202511010,emblem_event_mag_00002_ja,emblem_event_mag_00002,ja,"桜木 カナの社員証","桜木 カナの社員証。「株式会社マジルミエ」の魔法少女は社員証が変身アイテムになっている。"
e,202511010,emblem_event_mag_00003_ja,emblem_event_mag_00003,ja,"葵 リリーの鍵","葵 リリーが身に着けている鍵のネックレス。葵 リリーが魔法少女に変身するためのアイテムにもなる。"
e,202511010,emblem_adventbattle_mag_season01_00001_ja,emblem_adventbattle_mag_season01_00001,ja,魔法少女(1位),2025年11月開催降臨バトル『業務実行!!』ランキング1位の証
e,202511010,emblem_adventbattle_mag_season01_00002_ja,emblem_adventbattle_mag_season01_00002,ja,魔法少女(2位),2025年11月開催降臨バトル『業務実行!!』ランキング2位の証
e,202511010,emblem_adventbattle_mag_season01_00003_ja,emblem_adventbattle_mag_season01_00003,ja,魔法少女(3位),2025年11月開催降臨バトル『業務実行!!』ランキング3位の証
e,202511010,emblem_adventbattle_mag_season01_00004_ja,emblem_adventbattle_mag_season01_00004,ja,魔法少女(4~50位),2025年11月開催降臨バトル『業務実行!!』ランキング4~50位の証
e,202511010,emblem_adventbattle_mag_season01_00005_ja,emblem_adventbattle_mag_season01_00005,ja,魔法少女(51~300位),2025年11月開催降臨バトル『業務実行!!』ランキング51~300位の証
e,202511010,emblem_adventbattle_mag_season01_00006_ja,emblem_adventbattle_mag_season01_00006,ja,"魔法少女(301~1,000位)","2025年11月開催降臨バトル『業務実行!!』ランキング301~1,000位の証"
e,202511020,emblem_event_yuw_00001_ja,emblem_event_yuw_00001,ja,ラスタロッテ,『コスプレイヤー四天王』まゆらがラスタロッテにコスプレした姿のエンブレム
e,202511020,emblem_event_yuw_00002_ja,emblem_event_yuw_00002,ja,イコラ,『コスプレイヤー四天王』753♡がイコラにコスプレした姿のエンブレム
e,202511020,emblem_adventbattle_yuw_season01_00001_ja,emblem_adventbattle_yuw_season01_00001,ja,天使空挺隊(1位),2025年12月開催降臨バトル『夏コミの魔物』1位の証
e,202511020,emblem_adventbattle_yuw_season01_00002_ja,emblem_adventbattle_yuw_season01_00002,ja,天使空挺隊(2位),2025年12月開催降臨バトル『夏コミの魔物』2位の証
e,202511020,emblem_adventbattle_yuw_season01_00003_ja,emblem_adventbattle_yuw_season01_00003,ja,天使空挺隊(3位),2025年12月開催降臨バトル『夏コミの魔物』3位の証
e,202511020,emblem_adventbattle_yuw_season01_00004_ja,emblem_adventbattle_yuw_season01_00004,ja,天使空挺隊(4~50位),2025年12月開催降臨バトル『夏コミの魔物』4~50位の証
e,202511020,emblem_adventbattle_yuw_season01_00005_ja,emblem_adventbattle_yuw_season01_00005,ja,天使空挺隊(51~300位),2025年12月開催降臨バトル『夏コミの魔物』51~300位の証
e,202511020,emblem_adventbattle_yuw_season01_00006_ja,emblem_adventbattle_yuw_season01_00006,ja,"天使空挺隊(301~1,000位)","2025年12月開催降臨バトル『夏コミの魔物』301~1,000位の証"
e,202512010,emblem_event_sur_00001_ja,emblem_event_sur_00001,ja,魔都防衛隊章,魔都の脅威から日本を守護するために編成された組織の証。
e,202512010,emblem_event_sur_00002_ja,emblem_event_sur_00002,ja,"七番組組長 羽前 京香","魔防隊七番組組長である羽前 京香のエンブレム"
e,202512010,emblem_event_sur_00003_ja,emblem_event_sur_00003,ja,"六番組組長 出雲 天花","魔防隊六番組組長である出雲 天花のエンブレム"
e,202512010,emblem_adventbattle_sur_season01_00001_ja,emblem_adventbattle_sur_season01_00001,ja,屈服の時間だ(1位),2025年12月開催降臨バトル『魔防隊と戦う者』1位の証
e,202512010,emblem_adventbattle_sur_season01_00002_ja,emblem_adventbattle_sur_season01_00002,ja,屈服の時間だ(2位),2025年12月開催降臨バトル『魔防隊と戦う者』2位の証
e,202512010,emblem_adventbattle_sur_season01_00003_ja,emblem_adventbattle_sur_season01_00003,ja,屈服の時間だ(3位),2025年12月開催降臨バトル『魔防隊と戦う者』3位の証
e,202512010,emblem_adventbattle_sur_season01_00004_ja,emblem_adventbattle_sur_season01_00004,ja,屈服の時間だ(4~50位),2025年12月開催降臨バトル『魔防隊と戦う者』4~50位の証
e,202512010,emblem_adventbattle_sur_season01_00005_ja,emblem_adventbattle_sur_season01_00005,ja,屈服の時間だ(51~300位),2025年12月開催降臨バトル『魔防隊と戦う者』51~300位の証
e,202512010,emblem_adventbattle_sur_season01_00006_ja,emblem_adventbattle_sur_season01_00006,ja,"屈服の時間だ(301~1,000位)","2025年12月開催降臨バトル『魔防隊と戦う者』301~1,000位の証"
e,202512020,emblem_event_osh_00001_ja,emblem_event_osh_00001,ja,嘘吐き,復讐を誓う星を模ったエンブレム
e,202512020,emblem_event_osh_00002_ja,emblem_event_osh_00002,ja,B小町,苺プロ所属のアイドルグループ「B小町」のロゴのエンブレム
e,202512020,emblem_event_osh_00003_ja,emblem_event_osh_00003,ja,アイ推し！,B小町のアイドル、アイ推しであることの証
e,202512020,emblem_event_osh_00004_ja,emblem_event_osh_00004,ja,ルビー推し！,新生B小町のアイドル、ルビー推しであることの証
e,202512020,emblem_event_osh_00005_ja,emblem_event_osh_00005,ja,MEMちょ推し！,新生B小町のアイドル、MEMちょ推しであることの証
e,202512020,emblem_event_osh_00006_ja,emblem_event_osh_00006,ja,有馬かな推し！,"新生B小町のアイドル、有馬 かな推しであることの証"
e,202512020,emblem_event_osh_00007_ja,emblem_event_osh_00007,ja,黒川あかね推し！,"女優、黒川 あかね推しであることの証"
e,202512020,emblem_event_osh_00008_ja,emblem_event_osh_00008,ja,筋肉推し！,ぴえヨンにも匹敵するその筋肉…！そう、筋肉は全てを解決する！
e,202512020,emblem_event_osh_00009_ja,emblem_event_osh_00009,ja,アクア推し！,俳優、アクア推しであることの証
e,202512020,emblem_event_osh_00010_ja,emblem_event_osh_00010,ja,アイのサイン,B小町のアイドル、アイのサインのエンブレム
e,202512020,emblem_adventbattle_osh_season01_00001_ja,emblem_adventbattle_osh_season01_00001,ja,最強で無敵のアイドル(1位),2026年1月開催降臨バトル『ファーストライブ』1位の証
e,202512020,emblem_adventbattle_osh_season01_00002_ja,emblem_adventbattle_osh_season01_00002,ja,最強で無敵のアイドル(2位),2026年1月開催降臨バトル『ファーストライブ』2位の証
e,202512020,emblem_adventbattle_osh_season01_00003_ja,emblem_adventbattle_osh_season01_00003,ja,最強で無敵のアイドル(3位),2026年1月開催降臨バトル『ファーストライブ』3位の証
e,202512020,emblem_adventbattle_osh_season01_00004_ja,emblem_adventbattle_osh_season01_00004,ja,最強で無敵のアイドル(4~50位),2026年1月開催降臨バトル『ファーストライブ』4~50位の証
e,202512020,emblem_adventbattle_osh_season01_00005_ja,emblem_adventbattle_osh_season01_00005,ja,最強で無敵のアイドル(51~300位),2026年1月開催降臨バトル『ファーストライブ』51~300位の証
e,202512020,emblem_adventbattle_osh_season01_00006_ja,emblem_adventbattle_osh_season01_00006,ja,"最強で無敵のアイドル(301~1,000位)","2026年1月開催降臨バトル『ファーストライブ』301~1,000位の証"
e,202601010,emblem_event_jig_00001_ja,emblem_event_jig_00001,ja,神仙郷,仙薬探しのため、死罪人と打ち首執行人たちが上陸した秘境の島
e,202601010,emblem_adventbattle_jig_season01_00001_ja,emblem_adventbattle_jig_season01_00001,ja,罪人(1位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』1位の証"
e,202601010,emblem_adventbattle_jig_season01_00002_ja,emblem_adventbattle_jig_season01_00002,ja,罪人(2位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』2位の証"
e,202601010,emblem_adventbattle_jig_season01_00003_ja,emblem_adventbattle_jig_season01_00003,ja,罪人(3位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』3位の証"
e,202601010,emblem_adventbattle_jig_season01_00004_ja,emblem_adventbattle_jig_season01_00004,ja,罪人(4~50位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』4~50位の証"
e,202601010,emblem_adventbattle_jig_season01_00005_ja,emblem_adventbattle_jig_season01_00005,ja,罪人(51~300位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』51~300位の証"
e,202601010,emblem_adventbattle_jig_season01_00006_ja,emblem_adventbattle_jig_season01_00006,ja,"罪人(301~1,000位)","2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』301~1,000位の証"
e,202512020,emblem_event_glo_00001_ja,emblem_event_glo_00001,ja,祝！2026,あけましておめでとう！2026年も素敵なジャンブラライフになりますように！
e,202512020,emblem_event_glo_00002_ja,emblem_event_glo_00002,ja,開運,脅威の確率を引き当てた証。これを手にしたリーダーの運の良さは天下一。まさに「強運」の持ち主！
e,202602010,emblem_event_you_00001_ja,emblem_event_you_00001,ja,ブラック幼稚園園章,世界一安全な幼稚園・ブラック幼稚園園章のエンブレム
e,202602010,emblem_adventbattle_you_season01_00001_ja,emblem_adventbattle_you_season01_00001,ja,元・伝説の殺し屋(1位),2026年2月開催降臨バトル『誰の依頼だ？』1位の証
e,202602010,emblem_adventbattle_you_season01_00002_ja,emblem_adventbattle_you_season01_00002,ja,元・伝説の殺し屋(2位),2026年2月開催降臨バトル『誰の依頼だ？』2位の証
e,202602010,emblem_adventbattle_you_season01_00003_ja,emblem_adventbattle_you_season01_00003,ja,元・伝説の殺し屋(3位),2026年2月開催降臨バトル『誰の依頼だ？』3位の証
e,202602010,emblem_adventbattle_you_season01_00004_ja,emblem_adventbattle_you_season01_00004,ja,元・伝説の殺し屋(4~50位),2026年2月開催降臨バトル『誰の依頼だ？』4~50位の証
e,202602010,emblem_adventbattle_you_season01_00005_ja,emblem_adventbattle_you_season01_00005,ja,元・伝説の殺し屋(51~300位),2026年2月開催降臨バトル『誰の依頼だ？』51~300位の証
e,202602010,emblem_adventbattle_you_season01_00006_ja,emblem_adventbattle_you_season01_00006,ja,"元・伝説の殺し屋(301~1,000位)","2026年2月開催降臨バトル『誰の依頼だ？』301~1,000位の証"
e,202602020,emblem_event_kim_00001_ja,emblem_event_kim_00001,ja,神様,とある神社の恋の神様を模したエンブレム
e,202602020,emblem_event_kim_00002_ja,emblem_event_kim_00002,ja,"花園 羽々里の大好きな気持ち","花園 羽々里の大好きな気持ちを模したエンブレム"
e,202602020,emblem_event_kim_00003_ja,emblem_event_kim_00003,ja,"花園 羽香里の大好きな気持ち","花園 羽香里の大好きな気持ちを模したエンブレム"
e,202602020,emblem_event_kim_00004_ja,emblem_event_kim_00004,ja,"院田 唐音の大好きな気持ち","院田 唐音の大好きな気持ちを模したエンブレム"
e,202602020,emblem_event_kim_00005_ja,emblem_event_kim_00005,ja,"好本 静の大好きな気持ち","好本 静の大好きな気持ちを模したエンブレム"
e,202602020,emblem_adventbattle_kim_season01_00001_ja,emblem_adventbattle_kim_season01_00001,ja,恋愛勇者(1位),2026年2月開催降臨バトル『ラブミッション：インポッシブル』1位の証
e,202602020,emblem_adventbattle_kim_season01_00002_ja,emblem_adventbattle_kim_season01_00002,ja,恋愛勇者(2位),2026年2月開催降臨バトル『ラブミッション：インポッシブル』2位の証
e,202602020,emblem_adventbattle_kim_season01_00003_ja,emblem_adventbattle_kim_season01_00003,ja,恋愛勇者(3位),2026年2月開催降臨バトル『ラブミッション：インポッシブル』3位の証
e,202602020,emblem_adventbattle_kim_season01_00004_ja,emblem_adventbattle_kim_season01_00004,ja,恋愛勇者(4~50位),2026年2月開催降臨バトル『ラブミッション：インポッシブル』4~50位の証
e,202602020,emblem_adventbattle_kim_season01_00005_ja,emblem_adventbattle_kim_season01_00005,ja,恋愛勇者(51~300位),2026年2月開催降臨バトル『ラブミッション：インポッシブル』51~300位の証
e,202602020,emblem_adventbattle_kim_season01_00006_ja,emblem_adventbattle_kim_season01_00006,ja,"恋愛勇者(301~1,000位)","2026年2月開催降臨バトル『ラブミッション：インポッシブル』301~1,000位の証"
```

---

<!-- FILE: ./projects/glow-masterdata/MstEnemyCharacter.csv -->
## ./projects/glow-masterdata/MstEnemyCharacter.csv

```csv
ENABLE,release_key,id,mst_series_id,asset_key,is_phantomized,is_displayed_encyclopedia,mst_attack_hit_onomatopeia_group_id
e,202509010,e_glo_00001_tutorial_Normal_Yellow,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00001_tutorial_Boss_Yellow,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00001_general_as_Normal_Red,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00001_general_as_h_Normal_Red,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00002_general_as_Boss_Red,glo,enemy_sur_00101,0,0,
e,202509010,e_glo_00002_general_as_h_Boss_Red,glo,enemy_sur_00101,0,0,
e,202509010,e_glo_00001_general_as_Normal_Yellow,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00001_general_as_h_Normal_Yellow,glo,enemy_glo_00001,0,0,
e,202509010,e_glo_00002_general_as_h_Boss_Yellow,glo,enemy_sur_00101,0,0,
e,202509010,e_glo_00002_general_as_Boss_Yellow,glo,enemy_sur_00101,0,0,
e,202509010,c_spy_00101_general_Boss_Colorless,spy,chara_spy_00101,1,0,
e,202509010,chara_spy_00101,spy,chara_spy_00101,1,1,
e,202509010,chara_spy_00201,spy,chara_spy_00201,1,1,
e,202509010,chara_spy_00401,spy,chara_spy_00401,1,1,
e,202510010,chara_spy_00501,spy,chara_spy_00501,1,1,
e,202509010,chara_aka_00001,aka,chara_aka_00001,1,1,
e,202509010,chara_aka_00101,aka,chara_aka_00101,1,1,
e,202509010,chara_rik_00001,rik,chara_rik_00001,1,1,
e,202509010,chara_rik_00101,rik,chara_rik_00101,1,1,
e,202509010,chara_dan_00001,dan,chara_dan_00001,1,1,
e,202509010,chara_dan_00002,dan,chara_dan_00002,1,1,
e,202509010,chara_dan_00101,dan,chara_dan_00101,1,1,
e,202510020,chara_dan_00201,dan,chara_dan_00201,1,1,
e,202510020,chara_dan_00202,dan,chara_dan_00202,1,1,
e,202510020,chara_dan_00301,dan,chara_dan_00301,1,1,
e,202509010,chara_gom_00001,gom,chara_gom_00001,1,1,
e,202509010,chara_gom_00101,gom,chara_gom_00101,1,1,
e,202509010,chara_gom_00201,gom,chara_gom_00201,1,1,
e,202509010,chara_chi_00001,chi,chara_chi_00001,1,1,
e,202509010,chara_chi_00002,chi,chara_chi_00002,1,1,
e,202509010,chara_chi_00201,chi,chara_chi_00201,1,1,
e,202509010,chara_chi_00301,chi,chara_chi_00301,1,1,
e,202509010,chara_bat_00001,bat,chara_bat_00001,1,0,
e,202509010,chara_bat_00101,bat,chara_bat_00101,1,0,
e,202509010,chara_kai_00001,kai,chara_kai_00001,1,1,
e,202509010,chara_kai_00002,kai,chara_kai_00002,1,1,
e,202509010,chara_kai_00101,kai,chara_kai_00101,1,1,
e,202509010,chara_kai_00201,kai,chara_kai_00201,1,1,
e,202509010,chara_kai_00301,kai,chara_kai_00301,1,1,
e,202509010,chara_kai_00401,kai,chara_kai_00401,1,1,
e,202509010,chara_kai_00501,kai,chara_kai_00501,1,1,
e,202509010,chara_kai_00601,kai,chara_kai_00601,1,1,
e,202511020,chara_yuw_00001,yuw,chara_yuw_00001,1,1,
e,202511020,chara_yuw_00101,yuw,chara_yuw_00101,1,1,
e,202511020,chara_yuw_00201,yuw,chara_yuw_00201,1,1,
e,202511020,chara_yuw_00301,yuw,chara_yuw_00301,1,1,
e,202511020,chara_yuw_00401,yuw,chara_yuw_00401,1,1,
e,202511020,chara_yuw_00501,yuw,chara_yuw_00501,1,1,
e,202511020,chara_yuw_00601,yuw,chara_yuw_00601,1,1,
e,202509010,chara_sur_00001,sur,chara_sur_00001,1,1,
e,202509010,chara_sur_00101,sur,chara_sur_00101,1,1,
e,202509010,chara_sur_00201,sur,chara_sur_00201,1,1,
e,202509010,chara_sur_00301,sur,chara_sur_00301,1,1,
e,202512010,chara_sur_00501,sur,chara_sur_00501,1,1,
e,202512010,chara_sur_00601,sur,chara_sur_00601,1,1,
e,202512010,chara_sur_00701,sur,chara_sur_00701,1,0,
e,202512010,chara_sur_00801,sur,chara_sur_00801,1,1,
e,202509010,chara_tak_00001,tak,chara_tak_00001,1,1,
e,202509010,chara_jig_00001,jig,chara_jig_00001,1,1,
e,202509010,chara_jig_00101,jig,chara_jig_00101,1,1,
e,202509010,chara_jig_00201,jig,chara_jig_00201,1,1,
e,202509010,chara_jig_00301,jig,chara_jig_00301,1,1,
e,202601010,chara_jig_00401,jig,chara_jig_00401,1,1,
e,202601010,chara_jig_00501,jig,chara_jig_00501,1,1,
e,202601010,chara_jig_00601,jig,chara_jig_00601,1,1,
e,202509010,chara_mag_00001,mag,chara_mag_00001,1,1,
e,202509010,chara_mag_00101,mag,chara_mag_00101,1,1,
e,202511010,chara_mag_00201,mag,chara_mag_00201,1,1,
e,202511010,chara_mag_00301,mag,chara_mag_00301,1,1,
e,202511010,chara_mag_00401,mag,chara_mag_00401,1,1,
e,202509010,chara_sum_00001,sum,chara_sum_00001,1,1,
e,202509010,chara_sum_00101,sum,chara_sum_00101,1,1,
e,202509010,chara_sum_00201,sum,chara_sum_00201,1,1,
e,202512020,chara_osh_00001,osh,chara_osh_00001,1,1,
e,202512020,chara_osh_00201,osh,chara_osh_00201,1,1,
e,202512020,chara_osh_00301,osh,chara_osh_00301,1,1,
e,202512020,chara_osh_00401,osh,chara_osh_00401,1,1,
e,202512020,chara_osh_00501,osh,chara_osh_00501,1,1,
e,202512020,chara_osh_00601,osh,chara_osh_00601,1,1,
e,202602010,chara_you_00001,you,chara_you_00001,1,1,
e,202602010,chara_you_00101,you,chara_you_00101,1,1,
e,202602010,chara_you_00201,you,chara_you_00201,1,1,
e,202602010,chara_you_00301,you,chara_you_00301,1,1,
e,202602020,chara_kim_00001,kim,chara_kim_00001,1,1,
e,202602020,chara_kim_00101,kim,chara_kim_00101,1,1,
e,202602020,chara_kim_00201,kim,chara_kim_00201,1,1,
e,202602020,chara_kim_00301,kim,chara_kim_00301,1,1,
e,202509010,enemy_spy_00001,spy,enemy_spy_00001,0,1,
e,202509010,enemy_spy_00101,spy,enemy_spy_00101,0,1,
e,202509010,enemy_spy_00201,spy,enemy_spy_00201,0,0,
e,202509010,enemy_spy_00301,spy,enemy_spy_00301,0,0,
e,202509010,enemy_dan_00001,dan,enemy_dan_00001,0,1,
e,202509010,enemy_dan_00101,dan,enemy_dan_00101,0,1,
e,202509010,enemy_dan_00201,dan,enemy_dan_00201,0,1,
e,202510020,enemy_dan_00301,dan,enemy_dan_00301,0,1,
e,202509010,enemy_gom_00301,gom,enemy_gom_00301,0,1,
e,202509010,enemy_gom_00401,gom,enemy_gom_00401,0,1,
e,202509010,enemy_gom_00402,gom,enemy_gom_00402,0,1,
e,202509010,enemy_gom_00501,gom,enemy_gom_00501,0,1,
e,202509010,enemy_gom_00502,gom,enemy_gom_00502,0,1,
e,202509010,enemy_gom_00701,gom,enemy_gom_00701,0,1,
e,202509010,enemy_gom_00801,gom,enemy_gom_00801,0,1,
e,202509010,enemy_gom_00901,gom,enemy_gom_00901,0,1,
e,202509010,enemy_gom_01001,gom,enemy_gom_01001,0,1,
e,202509010,enemy_gom_01002,gom,enemy_gom_01002,0,1,
e,202509010,enemy_chi_00001,chi,enemy_chi_00001,0,1,
e,202509010,enemy_chi_00101,chi,enemy_chi_00101,0,1,
e,202509010,enemy_chi_00201,chi,enemy_chi_00201,0,1,
e,202509010,enemy_kai_00001,kai,enemy_kai_00001,0,1,
e,202509010,enemy_kai_00101,kai,enemy_kai_00101,0,1,
e,202509010,enemy_kai_00201,kai,enemy_kai_00201,0,1,
e,202509010,enemy_kai_00301,kai,enemy_kai_00301,0,1,
e,202509010,enemy_kai_00401,kai,enemy_kai_00401,0,1,
e,202509010,enemy_sur_00001,sur,enemy_sur_00001,1,1,
e,202509010,enemy_sur_00101,sur,enemy_sur_00101,0,1,
e,202509010,enemy_jig_00001,jig,enemy_jig_00001,0,1,
e,202509010,enemy_jig_00201,jig,enemy_jig_00201,0,0,
e,202509010,enemy_jig_00301,jig,enemy_jig_00301,0,1,
e,202509010,enemy_jig_00401,jig,enemy_jig_00401,0,1,
e,202509010,enemy_jig_00402,jig,enemy_jig_00402,0,1,
e,202601010,enemy_jig_00501,jig,enemy_jig_00501,0,1,
e,202601010,enemy_jig_00601,jig,enemy_jig_00601,0,1,
e,202509010,enemy_mag_00001,mag,enemy_mag_00001,0,1,
e,202509010,enemy_mag_00101,mag,enemy_mag_00101,0,1,
e,202509010,enemy_mag_00201,mag,enemy_mag_00201,0,0,
e,202509010,enemy_mag_00301,mag,enemy_mag_00301,0,1,
e,202511010,enemy_mag_00401,mag,enemy_mag_00401,0,1,
e,202509010,enemy_sum_00001,sum,enemy_sum_00001,0,1,
e,202509010,enemy_sum_00101,sum,enemy_sum_00101,1,1,
e,202509010,enemy_sum_00201,sum,enemy_sum_00201,1,1,
e,202602010,enemy_you_00001,you,enemy_you_00001,0,1,
e,202602010,enemy_you_00101,you,enemy_you_00101,0,1,
e,202509010,enemy_glo_00001,glo,enemy_glo_00001,0,0,
e,202512020,enemy_glo_00002,glo,enemy_glo_00002,0,0,
e,202509010,enemy_glo_00101,glo,enemy_glo_00101,0,0,
e,202509010,e_glo_00001_tutorial_Normal_Blue,glo,enemy_glo_00001,0,0,
e,202510025,e_kai_00002_tutorial,kai,chara_kai_00002,1,0,
e,202510025,e_spy_00101_tutorial,spy,chara_spy_00101,1,0,
e,202510025,e_dan_00002_tutorial,dan,chara_dan_00002,1,0,
e,202510025,e_sur_00101_tutorial,sur,chara_sur_00101,1,0,
e,202510025,e_chi_00002_tutorial,chi,chara_chi_00002,1,0,
```

---

<!-- FILE: ./projects/glow-masterdata/MstEnemyCharacterI18n.csv -->
## ./projects/glow-masterdata/MstEnemyCharacterI18n.csv

```csv
ENABLE,release_key,id,mst_enemy_character_id,language,name,description
e,202509010,e_glo_00001_tutorial_Normal_Yellow_ja,e_glo_00001_tutorial_Normal_Yellow,ja,チュートリアル君,ジャンプラバースの世界を脅かす存在（仮）
e,202509010,e_glo_00001_tutorial_Boss_Yellow_ja,e_glo_00001_tutorial_Boss_Yellow,ja,チュートリアル君,ジャンプラバースの世界を脅かす存在（仮）
e,202509010,e_glo_00001_general_as_Normal_Red_ja,e_glo_00001_general_as_Normal_Red,ja,オリジナル,オリジナル
e,202509010,e_glo_00001_general_as_h_Normal_Red_ja,e_glo_00001_general_as_h_Normal_Red,ja,オリジナル,オリジナル
e,202509010,e_glo_00002_general_as_Boss_Red_ja,e_glo_00002_general_as_Boss_Red,ja,オリジナル,オリジナル
e,202509010,e_glo_00002_general_as_h_Boss_Red_ja,e_glo_00002_general_as_h_Boss_Red,ja,オリジナル,オリジナル
e,202509010,e_glo_00001_general_as_Normal_Yellow_ja,e_glo_00001_general_as_Normal_Yellow,ja,オリジナル,オリジナル
e,202509010,e_glo_00001_general_as_h_Normal_Yellow_ja,e_glo_00001_general_as_h_Normal_Yellow,ja,オリジナル,オリジナル
e,202509010,e_glo_00002_general_as_h_Boss_Yellow_ja,e_glo_00002_general_as_h_Boss_Yellow,ja,オリジナル,オリジナル
e,202509010,e_glo_00002_general_as_Boss_Yellow_ja,e_glo_00002_general_as_Boss_Yellow,ja,オリジナル,オリジナル
e,202509010,c_spy_00101_general_Boss_Colorless_ja,c_spy_00101_general_Boss_Colorless,ja,"<黄昏> ロイド",東西平和の実現のために活動する西国(ウェスタリス)きっての敏腕諜報員(エージェント)。名前も過去も捨て、戦争を回避するために東国(オスタニア)にて諜報活動を行なっている。現在は超難関任務「オペレーション<梟>(ストリクス)」に従事。ロイド・フォージャーという名前と肩書は、この任務のために用意された仮初めのもの。
e,202509010,chara_spy_00101_ja,chara_spy_00101,ja,"<黄昏> ロイド",東西平和の実現のために活動する西国(ウェスタリス)きっての敏腕諜報員(エージェント)。名前も過去も捨て、戦争を回避するために東国(オスタニア)にて諜報活動を行なっている。現在は超難関任務「オペレーション<梟>(ストリクス)」に従事。ロイド・フォージャーという名前と肩書は、この任務のために用意された仮初めのもの。
e,202509010,chara_spy_00201_ja,chara_spy_00201,ja,"<いばら姫> ヨル","東国(オスタニア)の暗殺組織「ガーデン」の殺し屋。\n上長の命令で母国に害を成すと判断されたものを抹殺する。\n殺し屋は裏の顔で、普段は市役所の事務員として働く。\n世間に溶け込むためロイド・フォージャーの案に乗って仮初めの夫婦に。"
e,202509010,chara_spy_00401_ja,chara_spy_00401,ja,フランキー・フランクリン,"表の顔は普通のタバコ屋だが、<黄昏>も一目置く情報屋という裏の顔を持つ人物。\n東国(オスタニア)に住んではいるが、国の空気を好ましく思っておらず、西国(ウェスタリス)の諜報員にも協力する。\n手先が器用で、発明家としても優秀だ。"
e,202510010,chara_spy_00501_ja,chara_spy_00501,ja,"姉を想う盲愛 ユーリ・ブライア","東国(オスタニア)の外務省に勤務するヨル・フォージャーの弟。\n外交官は仮の姿で、実際は入省して1年ほどで国家保安局に\n抜擢され、秘密警察として働いている。階位は少尉。\nヨル・フォージャーへの愛情が強く、\n義兄のロイド・フォージャーを強く敵視する。"
e,202509010,chara_aka_00001_ja,chara_aka_00001,ja,佐々木,"ラーメン赤猫のCEO、接客・レジ・経理担当。\n従業員の労働環境に気を遣うデキる猫。文蔵とは幼馴染で、野良猫時代を経験したのちに人間に拾われる。その後文蔵を口説き落とし、ラーメン赤猫を開業する。"
e,202509010,chara_aka_00101_ja,chara_aka_00101,ja,文蔵,"ラーメン赤猫の店長でメイン調理を担当。\nラーメン屋台「あかねこ」をやっていた店主から店と味を引き継ぎ、佐々木と共にラーメン赤猫を開業する。職人気質で、口数は少ないが気配りのできる猫。"
e,202509010,chara_rik_00001_ja,chara_rik_00001,ja,リコピン,"不思議な場所キュートピアに住む、トマトの苗から生まれたトイプードル。身長も体重もトマト7つぶんのとっても元気な3歳の男の子だが、健康診断の結果があまり良くなかったため、甘いものを控えるよう医者に言われている。\nインスタグラムもやってるからみんなフォローしてね★"
e,202509010,chara_rik_00101_ja,chara_rik_00101,ja,"甘戸 めめ","不思議な場所キュートピアに迷い込んだ、普通の中学生の女の子。\nコンパクトミラーでキュートピアと現実の世界を行き来することができる。自分が納得がいかないことは絶対にゆずらない頑固な面もあるが、かわいいぬいぐるみが大好きな優しい女の子。"
e,202509010,chara_dan_00001_ja,chara_dan_00001,ja,オカルン,"幽霊は信じていないが、宇宙人は信じている、怪異現象オタクの男子高校生。\n初心で鈍感が故に不器用な言動も目立つが、仲間のためならば危険なことにも立ち向かう勇気を持っている。"
e,202509010,chara_dan_00002_ja,chara_dan_00002,ja,"ターボババアの霊力 オカルン","オカルンがターボババアの呪いを受けて変身した姿。髪は白髪で逆立ち、口元にはマスクが浮かび上がる。脅威のスピードで動きまわり、限界を突破する“本気”を使うことができる。\nしかし普段とは異なり気だるくネガティブな性格になってしまう。"
e,202509010,chara_dan_00101_ja,chara_dan_00101,ja,モモ,"宇宙人は信じていないが幽霊は信じている、霊媒師の家系の\n女子高生。セルポ星人にさらわれた時に超能力に目覚める。困っている人を見過ごせない優しい一面もあり、誰にも裏表なく接することができる。\n憧れの人のような硬派な男性が好きと自称しており、オカルンと憧れの人との共通点を知ってときめいてしまうことも。"
e,202510020,chara_dan_00201_ja,chara_dan_00201,ja,アイラ,"オカルンやモモと同じ高校に通う女子高生で、\n自他共に認める美少女。オカルンの金の玉を拾ったことで能力が開花してしまう。普段は天然な美少女を演じているが、一度思い込むと周りの声が聞こえなくなってしまう、頑固な一面もある。"
e,202510020,chara_dan_00202_ja,chara_dan_00202,ja,"アクさらの愛 アイラ","アクロバティックさらさらの炎を体内に取り入れたことにより、能力を得たアイラの姿。\n平常時より髪の毛が著しく伸び、自由に操ることができる他、バレエを踊るような優雅さで足技を駆使して戦う。アクロバティックさらさらのような口の悪いお嬢様口調で喋る。"
e,202510020,chara_dan_00301_ja,chara_dan_00301,ja,"招き猫 ターボババア","神出鬼没で、全国各地で暴れ回ってた近代妖怪。\nオカルン達に倒された後消えたと思われていたが、実はオカルンの中に隠れていた。今は力をオカルンの中に残し、意識のみが招き猫の中に入って動き回っている。現在はモモの家で暮らしている。"
e,202509010,chara_gom_00001_ja,chara_gom_00001,ja,"囚われの王女 姫様","王女にして、国王軍第三騎士団の“騎士団長”。\n数々の戦場を生き抜き、多くの武勲をあげてきたが、現在は魔王軍に囚われ、拷問を受ける日々を送っている。品行方正で誇り高く見えるが、実際は日々の“拷問”に尽く屈してしまっている。"
e,202509010,chara_gom_00101_ja,chara_gom_00101,ja,トーチャー・トルチュール,"魔王軍の最高位拷問官にして、牢獄の責任者。\n最年少で最高位拷問官の地位に上り詰めた“拷問”の天才。\n国王軍の情報を得るために様々な方法で姫様に対して“拷問”を行っている。食欲を掻き立てる料理や美味しそうに食事をする姿で姫様を屈服させることが得意。"
e,202509010,chara_gom_00201_ja,chara_gom_00201,ja,クロル,"魔王軍の一級戦闘員であり、上級拷問官。\n明るくギャルのような性格。多種多様な動物を飼育しており、猛獣などを意のままに操ることができる猛獣使い。白熊のキュイや犬など、可愛い動物と共に“拷問”を行う。すこしマニアックな趣向もしばしば見られる。"
e,202509010,chara_chi_00001_ja,chara_chi_00001,ja,デンジ,"相棒のポチタをその身に宿す、『チェンソーの悪魔』の 少年。\n 借金返済のためにこき使われるド底辺の暮らしをしていたためか、食欲や性欲といったシンプルな欲求に正直。"
e,202509010,chara_chi_00002_ja,chara_chi_00002,ja,"悪魔が恐れる悪魔 チェンソーマン","相棒のポチタの命と引き換えに『チェンソーの悪魔』として蘇ったデンジの姿。頭や腕など体から複数のチェンソーの刃が飛び出している。\n 胸から出ているスターターロープを引っ張ることで変身し、\n チェンソーマンになると深い傷を負っていても復活・蘇生することができる。"
e,202509010,chara_chi_00201_ja,chara_chi_00201,ja,"早川 アキ","公安4課に所属するデビルハンター。\nマキマの忠実な部下。過去の悲惨な出来事から悪魔を強く憎んでおり、悪魔をこの世から駆逐するという強い思いでデビルハンターになった。"
e,202509010,chara_chi_00301_ja,chara_chi_00301,ja,パワー,"『血の悪魔』の魔人。\n魔人でありながら、公安4課に所属するデビルハンター。嘘つきでわがままで、自分の都合の良いようにしか考えない性格をしている。自分の血を固めて武器にすることができる。また、疲れるが他人の血を操って止血も可能。"
e,202509010,chara_bat_00001_ja,chara_bat_00001,ja,"清峰 葉流火","都立小手指高校の1年生。\n口数が少なくクールな性格で、野球一筋。140キロを越える剛速球を投げる抜群のフィジカルに加え、バッティングでも非凡な才能を見せる。中学時代は要圭とバッテリーを組み、宝谷シニアでは「天才バッテリー」として名を馳せた。"
e,202509010,chara_bat_00101_ja,chara_bat_00101,ja,"要 圭","都立小手指高校1年。\n中学時代は清峰 葉流火（きよみね はるか）とバッテリーを組み、宝谷シニアの天才バッテリーと呼ばれていた。どんな球も捕球し、キレたリードで勝利をもぎ取る智将捕手だったが、現在は記憶喪失で野球は素人同然。性格も、かつての智将時代とはかけ離れている。"
e,202509010,chara_kai_00001_ja,chara_kai_00001,ja,"日比野 カフカ","32歳の新米防衛隊員。\n防衛隊員を目指すも夢破れ、燻る日々を過ごしていたが、\n市川 レノに背を押され、苦難の末に夢を掴み取った。候補生としての入隊だったが、怪獣専門清掃業者時代の知識を駆使し、討伐に貢献。正隊員へ昇格した。"
e,202509010,chara_kai_00002_ja,chara_kai_00002,ja,"隠された英雄の姿 怪獣８号","謎の生物に寄生され、怪獣化した日比野 カフカの姿。\n人々を守るために拳をふるい、怪獣を討伐する。\n防衛隊発足以来初の未討伐怪獣として日本中から追われていたが、怪獣１０号による立川基地襲撃の際、仲間の危機を救うために人前で変身。その正体を明かすこととなった。"
e,202509010,chara_kai_00101_ja,chara_kai_00101,ja,"市川 レノ","新米防衛隊員。\n日比野 カフカの怪獣専門清掃業者時代の後輩であり、共に防衛隊の道へと進んだ良き相棒。冷静沈着で真面目な性格だが、負けず嫌いで努力を惜しまない熱い一面を持つ。\n日比野 カフカを尊敬し、彼の秘密を知りながらも信頼を寄せている。"
e,202509010,chara_kai_00201_ja,chara_kai_00201,ja,"第三部隊隊長 亜白 ミナ","日本防衛隊第三部隊隊長であり、防衛隊屈指の実力者。\n幼い頃、自身の住む街が怪獣に襲撃されたことをきっかけに、幼馴染の日比野 カフカと共に防衛隊員を志した。狙撃武器での大型怪獣の討伐を得意としており、防衛隊の中でもトップクラスの人気と実力を誇っている。"
e,202509010,chara_kai_00301_ja,chara_kai_00301,ja,"四ノ宮 キコル","新米防衛隊隊員。\n日本防衛隊長官を父に持ち、16歳でカルフォルニア討伐大学を飛び級で最年少主席卒業したエリート。市川 レノ曰く「アグレッシブで高圧的な性格」。怪獣１０号による立川基地襲撃の際には専用武器の斧を振い、遊軍として目覚ましい活躍を見せた。"
e,202509010,chara_kai_00401_ja,chara_kai_00401,ja,"保科 宗四郎","防衛隊第三部隊副隊長。\n室町時代から続く怪獣討伐の家系で、刀を用いた近接戦闘のスペシャリスト。選抜試験でカフカを「光るものがある」と評価し、自らの小隊に候補生として入隊させた。しかし、実際はカフカに違和感を抱いており、監視するためでもあった。"
e,202509010,chara_kai_00501_ja,chara_kai_00501,ja,"四ノ宮 功","日本防衛隊長官。\n怪獣２号の唯一の適合者で、高齢ではあるが、圧倒的なパワーで怪獣状態のカフカと渡り合う。\n四ノ宮キコルの実の父親にあたり、娘に対して常に完璧であり続け、他の追随を許さない圧倒的な存在になることを求めている。"
e,202509010,chara_kai_00601_ja,chara_kai_00601,ja,"古橋 伊春","防衛隊隊員で、カフカやキコルたちと同期。\n負けず嫌いで言葉はやや荒いが情に厚いムードメーカー。ライバル視しているレノが頭角を表していく様子に焦りを感じながらも努力を絶やさない、真面目で熱い一面を持つ。"
e,202511020,chara_yuw_00001_ja,chara_yuw_00001,ja,"リリエルに捧ぐ愛 天乃 リリサ","高校1年生。恋愛に鈍感でちょっとドジなオタク。\n「好きなことを堂々とするのがカッコいい」と思っている。観る人にキャラが現実にいると思わせるようなコスプレをすることに情熱を注いでいる。大好きなリリエルのコスプレROMを作るために、漫画研究部に入部した。"
e,202511020,chara_yuw_00101_ja,chara_yuw_00101,ja,"コスプレに託す乙女心 橘 美花莉","高校1年生。天乃 リリサと同じクラスの人気モデル。非常に一途な性格で、幼馴染である奥村 正宗に密かに10年間の片思いをしている。3次元女子の気持ちに鈍感なせいで気付かないでいる奥村 正宗の気を引くために、天乃 リリサと共にコスプレをすることに。"
e,202511020,chara_yuw_00201_ja,chara_yuw_00201,ja,"羽生 まゆり","奥村 正宗たちの通う高校の新任教師にして、漫画研究部の顧問。以前はコスプレ四天王まゆらとして活動していた。引退していたが、コスプレや、コスプレイヤーのことは愛しており、何かと協力は惜しまない良き顧問教師。"
e,202511020,chara_yuw_00301_ja,chara_yuw_00301,ja,"勇気を纏うコスプレ 乃愛","高校1年生。カメラおじさんのブログに5人の新星レイヤーとして選出されたうちの1人。友達が欲しくてコスプレを始めるが、極度の人見知りで苦戦していた。天乃 リリサとの出会いから自身のトラウマと向き合い、一緒にコスプレをする友人となった。"
e,202511020,chara_yuw_00401_ja,chara_yuw_00401,ja,"伝えたいウチの想い 喜咲 アリア","高校生のギャル。アウトドア派で誰にでもフレンドリー。距離を詰めるのが非常に早い。コスプレを始めた理由は、父親に今でも「ヴァル戦」が好きだと伝えたいから。コスプレ初心者だったが、天乃 リリサに指導されるうちにコスプレにハマり出す。"
e,202511020,chara_yuw_00501_ja,chara_yuw_00501,ja,753♡,コスプレ四天王にして、プロのコスプレイヤー。誰よりもコスプレとコスプレイヤーを愛していると自負しており、非常にプライドが高い。衣装の圧倒的なクオリティと、キャラクターの知名度や流行感、本人の人気を活かし、トップレイヤーとしての実力を発揮している。
e,202511020,chara_yuw_00601_ja,chara_yuw_00601,ja,"奥村 正宗","高校2年生。漫画研究部の唯一の部員にして部長。\n母親が突然いなくなったり、姉に疎まれたりした過去から3次元の女性に苦手意識を持つようになり、自他の恋愛感情に鈍感。「2次元は俺の嫁」と豪語するほどのリリエル好きでオタク。"
e,202509010,chara_sur_00001_ja,chara_sur_00001,ja,"和倉 優希","魔都に迷い込んだ高校3年生。\n醜鬼に襲われたところを羽前京香に助けられ、奴隷になる。雑草のような生命力を持っている。家事全般が得意で、魔防隊の管理人を務めることになる。\n七番組隊員からの理不尽な扱いにもめげず優しく接する人物。"
e,202509010,chara_sur_00101_ja,chara_sur_00101,ja,"誇り高き魔都の剣姫 羽前 京香","魔防隊七番組の組長。\n真面目な性格で、日々鍛錬を欠かさない。醜鬼に故郷を滅ぼされた過去から、醜鬼の絶滅を目標としている。能力は「無窮の鎖(スレイブ)」。奴隷にした生命体の力を引き出し行使できる能力で、現在は魔都で助けた和倉優希を使役している。"
e,202509010,chara_sur_00201_ja,chara_sur_00201,ja,"東 日万凛","魔防隊七番組の副組長であり、組長の羽前 京香に心酔している。\nプライドが高く男嫌い。そのため和倉優希の存在を良く思っておらず、強くあたることもあるが、戦闘時の強さは認めている。能力は「青雲の志(ラーニング)」。他人の能力を学び自身でも使用できる。"
e,202509010,chara_sur_00301_ja,chara_sur_00301,ja,"駿河 朱々","魔防隊七番組の隊員。\n好奇心旺盛な性格で、平凡な家庭の生まれだが刺激を求めて魔防隊に入隊した。和倉優希には挑発的な態度をとることも多い。身体の大きさを変化させる「玉体革命（パラダイムシフト）」で醜鬼を圧倒する。"
e,202512010,chara_sur_00501_ja,chara_sur_00501,ja,"空間を操る六番組組長 出雲 天花","魔防隊六番組の組長。和倉 優希を「奴隷クン」と呼び、何としても手に入れようとする。能力は「天御鳥命」(アメノミトリ)で、空間を操作することができ、瞬間移動や応用した戦闘が可能。冷静沈着で子供の頃から優等生。順風満帆な人生を送っている。"
e,202512010,chara_sur_00601_ja,chara_sur_00601,ja,"東 八千穂","魔防隊六番隊・副組長。東の家名にこだわりを持っており、非常にプライドが高い。妹の東 日万凛のことを溺愛しているが、なかなか素直に伝えることができずにいる。能力は「東の辰刻」(ゴールデンアワー)で、ポーズを決めると時を止めたり戻したりすることが可能。"
e,202512010,chara_sur_00701_ja,chara_sur_00701,ja,"和倉 青羽","魔都に現れた人型醜鬼。正体は魔都災害によって行方不明になっていた優希の姉。京香の故郷を襲った一本角の醜鬼を従えている。魔防隊を敵だと断言し、彼女と同じく醜鬼となった銭函 ココと湯野 波音達と共に魔防隊と戦おうとする。"
e,202512010,chara_sur_00801_ja,chara_sur_00801,ja,"無窮の鎖 和倉 優希","和倉 優希がスレイブとして変身した姿。醜鬼に似ている姿で、首には鎖付きの首輪がついている。使役する主人は優希の背に乗り、鎖によって操縦することが可能。変身を解くと、変身中の働きによって主人からの「褒美」が与えられる。"
e,202509010,chara_tak_00001_ja,chara_tak_00001,ja,"ハッピー星からの使者 タコピー","宇宙にハッピーを広めるため旅をするハッピー星人。\nハッピーな思考の持ち主で、人間の行動や考えには疎い。故郷を離れて地球へ降り立つも、遭難。地球人の女の子、久世しずかに助けられ、ハッピー道具を使い彼女を喜ばせるために奮闘する。"
e,202509010,chara_jig_00001_ja,chara_jig_00001,ja,がらんの画眉丸,"“がらんの画眉丸”として畏れられていた元石隠れ衆最強の忍。\n血も涙もないがらんどうと呼ばれていたが、里の長の娘と結婚し、愛に触れ心を取り戻すも、死罪人として囚われてしまう。無罪放免となり愛する妻の元へ帰るため、処刑人・山田浅ェ門 佐切と不老不死の仙薬探しに赴く。"
e,202509010,chara_jig_00101_ja,chara_jig_00101,ja,"山田浅ェ門 佐切","山田浅ェ門・試一刀流十二位。\n処刑執行人を代々務める山田家の娘。女性ながら剣技に優れているが、幼少の頃より首切り浅と罵られて過ごしてきた。“首斬りの業”に向き合い、苦しんでいたが、画眉丸との出会いや、島での出来事により人として成長していく。"
e,202509010,chara_jig_00201_ja,chara_jig_00201,ja,杠,"“傾主の杠”との呼び名を持つくのいち。\n常に己の保身を第一に考え、その為には他者をも利用する。冷酷に見えるが、画眉丸たちに協力するなどフレンドリーな一面も。仙薬探しに参加したのは、ただ生きて帰りたいから。粘膜を駆使する忍術を使用する。"
e,202509010,chara_jig_00301_ja,chara_jig_00301,ja,"山田浅ェ門 仙汰","山田浅ェ門・試一刀流五位。\n杠の監視役。勤勉で博識だが、気弱な侍。“首斬りの業”に悩んでおり、自身の正当性を宗教を学ぶことに求めたが、逆にその勤勉性が評価され、段位が高くなった。懐に植物学や宗教学、蘭学などの冊子を所持している。"
e,202601010,chara_jig_00401_ja,chara_jig_00401,ja,"賊王 亜左 弔兵衛","“賊王”の呼び名で、伊予の山奥に賊の村をもつ傑士。\n強さこそが全てだと信じ、目的のためには手段を選ばない冷酷さと高い適応能力、賊をまとめ上げる天賦の才能で、圧倒的な戦闘力を誇る。唯一の身内で大切に思っている実の弟の山田浅ェ門 桐馬と共に数々の状況を潜り抜けてきた。"
e,202601010,chara_jig_00501_ja,chara_jig_00501,ja,"山田浅ェ門 桐馬","山田浅ェ門・試一刀流、段位未定。\n亜左 弔兵衛の実の弟で監視役。兄を助けるために山田家に入門し、わずか1か月で代行免許を得る天稟を持つ。冷静沈着で知的な一面を持ちながらも、その身を賭してでも兄を守ろうとする強い覚悟を持っている。"
e,202601010,chara_jig_00601_ja,chara_jig_00601,ja,"民谷 巌鉄斎","“八洲無双の剣龍”と呼ばれる剣豪。\n藩主の屋敷にあった門扉の龍を切ったことにより、不敬罪に問われ死罪人となった。天下に轟く偉業を成し、後世に語り継がれる名前を残すことによって真の不老不死になることを史上の目標にしている。"
e,202509010,chara_mag_00001_ja,chara_mag_00001,ja,"新人魔法少女 桜木 カナ","「株式会社マジルミエ」の新人魔法少女。\n就職活動の面接中に駆けつけた魔法少女・越谷 仁美に惚れ込み、魔法少女への一歩を踏み出した。脅威的な記憶力を持っており、一度聞いたことや読んだものは忘れない。徹底的な事前準備を行う努力家。"
e,202509010,chara_mag_00101_ja,chara_mag_00101,ja,"越谷 仁美","ベンチャー企業「株式会社マジルミエ」に所属する魔法少女。\n普段着はジャージで口も悪いが、魔法少女としては天才的なセンスを持っている。現場での素早い判断力や抜群のフィジカルを駆使して怪異退治に当たる。ホーキの操作は感覚的なため、人への説明は壊滅的に下手。"
e,202511010,chara_mag_00201_ja,chara_mag_00201,ja,"絶対効率の体現者 土刃 メイ","業界大手・『アスト株式会社』に所属する魔法少女。\n効率を重視し、最小の手数で最大の成果を上げる機械のような精度で結果を積み上げ、社内でもトップの納品数を誇っている。感情をほとんど表に出さず、常に無表情で業務にあたるその戦い方には一切の無駄がない。"
e,202511010,chara_mag_00301_ja,chara_mag_00301,ja,"葵 リリー","大手化粧品メーカー『ミヤコ堂』の魔法少女。\n芯の強さと気品を併せ持つ美の体現者。魔法少女は素敵な仕事であることを伝えたいと考えている。株式会社マジルミエとの協働業務にて、桜木 カナとバディを組み、優雅かつ的確に怪異の対処にあたった。"
e,202511010,chara_mag_00401_ja,chara_mag_00401,ja,"槇野 あかね","新技術の研修のため『株式会社マジルミエ』へ出向してきた、『アプダ株式会社』に所属する魔法少女。\n上昇志向で、仕事熱心な性格。業務に関する新しくて良いものは取り入れたい、選択肢は沢山持っておきたいといった思いを持っている。"
e,202509010,chara_sum_00001_ja,chara_sum_00001,ja,"網代 慎平","調理師専門学校に通う17歳の少年。\n幼馴染の小舟 潮の葬儀に参列するために、2年ぶりに故郷の日都ヶ島(ひとがしま)へ帰省したところ、同じ夏を繰り返すループに巻き込まれてしまう。冷静に思考するためにフカンして考えることを心がけている。"
e,202509010,chara_sum_00101_ja,chara_sum_00101,ja,"影のウシオ 小舟 潮","17歳の女子高校生で、網代 慎平の幼馴染。\nフランス人の父譲りの金髪、青い瞳を持ち、明るくはつらつとした性格をしている。島の人のことを大切に思っており、正義感も強い。網代 慎平と共に影の脅威から島の住人や仲間たちを守るために、何度も同じ夏を繰り返す。"
e,202509010,chara_sum_00201_ja,chara_sum_00201,ja,"小舟 澪","高校１年生で、小舟 潮の実の妹。\n黒い髪に父親譲りの青い瞳、日焼けした褐色肌。２年ぶりに故郷の日都ヶ島(ひとがしま)へ帰った網代 慎平に対して、姉を亡くした直後でもいつもと変わらない様子で接する健気な性格をしている。\n運動神経が良く、水泳部に所属している。"
e,202512020,chara_osh_00001_ja,chara_osh_00001,ja,"B小町不動のセンター アイ","苺プロダクションのアイドルグループ・B小町の絶対的エース、不動のセンターで究極の美少女。16歳で星野 アクアと星野 ルビーを出産するためにアイドル活動を一時休止。復帰後はドラマや映画に出演したり、モデルやラジオのアシスタントなど幅広く活動し、順調に芸能界を駆け上がっていた。"
e,202512020,chara_osh_00201_ja,chara_osh_00201,ja,"星野 ルビー","苺プロダクション所属のアイドルで、再始動した新生B小町のメンバー。陽東高校芸能科1年生。ルビーは通称で本名は星野 瑠美衣(るびい)。明るい性格で、母親であるアイのような輝くアイドルになることを夢に見続け、何度もオーディションを受け続けていた。"
e,202512020,chara_osh_00301_ja,chara_osh_00301,ja,MEMちょ,"ネットで人気のある、バズらせのプロとして活躍するユーチューバーにしてインフルエンサー。星野 アクアに新生B小町に誘われ。一度は諦めたアイドルの道を歩み出した。B小町の配信やMV撮影、動画編集など、インフルエンサーとしてのスキルや人脈を駆使し、B小町を成功へ導く。"
e,202512020,chara_osh_00401_ja,chara_osh_00401,ja,"有馬 かな","陽東高校芸能科2年生。かつて10秒で泣ける天才子役として\n一世を風靡したが、その後は下火になっていた。芸能界の先輩としてのプライドが高く、子役として共演した星野 アクアのことを意識している。苺プロに加入し、女優からアイドルへ転身、新生B小町のセンターとして活動することとなる。"
e,202512020,chara_osh_00501_ja,chara_osh_00501,ja,黒川あかね,"高校2年生。劇団ララライに所属する若きエース。\n分析力が非常に高く、まるで役をその身に降ろすかのように演じる。恋愛リアリティショー「今からガチ恋始めます」へ出演するも、演出に翻弄されSNSで炎上。自殺未遂をしてしまうほど追い詰められるも星野 アクアによって助けられる。"
e,202512020,chara_osh_00601_ja,chara_osh_00601,ja,ぴえヨン,苺プロダクションに所属する年収1億円の覆面筋トレ系ユーチューバー。ひよこを模した丸い覆面に、パンツ一枚の姿をしており、小中学生から人気を集めている。新生B小町を宣伝するためにコラボをすることになり、1時間のぴえヨンブートダンスを決行する。
e,202602010,chara_you_00001_ja,chara_you_00001,ja,"元殺し屋の新人教諭 リタ","“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の新人教諭で、元殺し屋。\n一見普通の教員だが、かつては伝説の殺し屋として恐れられていた。抜群の戦闘能力と危機察知能力で幼稚園に日々襲来する殺し屋たちから園児を守っている。夢はイケメンの彼氏をつくること。"
e,202602010,chara_you_00101_ja,chara_you_00101,ja,ルーク,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」きく組の教諭で、元警官。\n少女まんがが好きで、恋愛において最も楽しいのは片想いの時間と考えている。幼稚園内で両想いフラグが成立しそうになるたびに絶妙なタイミングで恋の進展を邪魔しに現れる。"
e,202602010,chara_you_00201_ja,chara_you_00201,ja,ダグ,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の教諭で、リタの先輩。\n詐欺師として、誰も信用せず、独りで生きてきたが、リタに命を救われ恋に落ちる。鋭い勘と優しい性格を併せ持ち、園児たちにも好かれている。"
e,202602010,chara_you_00301_ja,chara_you_00301,ja,ハナ,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の新人教諭。\nリタの後輩で、爆弾を使用して戦う。殺し屋一族・ブラッドリー一家の末娘。殺し屋としてバディを組んでいた兄を助けて欲しいとリタ達に依頼する。"
e,202602020,chara_kim_00001_ja,chara_kim_00001,ja,花園羽々里,未作成
e,202602020,chara_kim_00101_ja,chara_kim_00101,ja,花園羽香里,未作成
e,202602020,chara_kim_00201_ja,chara_kim_00201,ja,院田唐音,未作成
e,202602020,chara_kim_00301_ja,chara_kim_00301,ja,"好本 静",未作成
e,202509010,enemy_spy_00001_ja,enemy_spy_00001,ja,密輸組織の残党,<黄昏>が襲撃した密輸組織の残党。奪われた美術品を取り戻すため、仕込んだ発信機を元に追跡したが返り討ちにあう。
e,202509010,enemy_spy_00101_ja,enemy_spy_00101,ja,グエン,東国の外務大臣を失脚に追い込もうとした悪人、エドガーの部下。アーニャ・フォージャーの通信を受けてロイドたちの住居へ襲撃に来た。
e,202509010,enemy_spy_00201_ja,enemy_spy_00201,ja,鎖窯使いのバーナビー,マフィアの生き残りを狙って豪華客船プリンセス・ローレライ号に乗り込んできた、殺し屋の一人。鎖鎌を使って戦う。
e,202509010,enemy_spy_00301_ja,enemy_spy_00301,ja,毒霧使い,マフィアの生き残りを狙って豪華客船プリンセス・ローレライ号に乗り込んできた、殺し屋の一人。熊をも卒倒させる毒霧を使う。
e,202509010,enemy_dan_00001_ja,enemy_dan_00001,ja,セルポ星人,モモを誘拐した宇宙人。クローンで個体を増やす雄のみの種。生殖機能を取り戻すため、人間の女性を狙う。
e,202509010,enemy_dan_00101_ja,enemy_dan_00101,ja,"セルポ星人 (変身)","セルポ星人の本来の姿。\nセルポ式測量法という念力を用いて攻撃する。\n三人の念力を合わせると""すごいゾーン""を作り出すことができる。"
e,202509010,enemy_dan_00201_ja,enemy_dan_00201,ja,ターボババア,神出鬼没で、全国各地で暴れ回ってた近代妖怪が、心霊スポットのトンネルで地縛霊と合体した存在。
e,202510020,enemy_dan_00301_ja,enemy_dan_00301,ja,アクロバティックさらさら,ターボババアからは三下の新人と呼ばれる新しい妖怪。幼い頃のアイラと関係があり、モモやアイラを襲う。
e,202509010,enemy_gom_00301_ja,enemy_gom_00301,ja,キュイ,"姫様の“拷問”のためにクロルが連れてきた白熊の赤ちゃん。\nボールとぬいぐるみ遊びが大好き。寝るときはお気に入りの毛布が必要。"
e,202509010,enemy_gom_00401_ja,enemy_gom_00401,ja,たこ焼きくん,"たこと生地が口の中でまざって踊る様子が具現化した存在。\n五臓六腑が幸せカーニバル。"
e,202509010,enemy_gom_00402_ja,enemy_gom_00402,ja,たこ焼き,"たこ焼き。\n目の前の鉄板で焼かれるライブ調理で、できたてを提供。"
e,202509010,enemy_gom_00501_ja,enemy_gom_00501,ja,バタートースト,"パリパリにトーストされたパン。\nバターを添えて一口、お皿に残ったビーフシチューを拭って一口。"
e,202509010,enemy_gom_00502_ja,enemy_gom_00502,ja,割きトースト,パリパリにトーストされたパンを割いたもの。割いたことにより、お皿の隅々までビーフシチューが拭いやすくなっている。
e,202509010,enemy_gom_00701_ja,enemy_gom_00701,ja,ラーメン,濃厚こってりラーメン。麺カタメ味コイメほうれん草トッピング。閉店間際には切り落としチャーシューサービス。
e,202509010,enemy_gom_00801_ja,enemy_gom_00801,ja,ライス,"濃厚ラーメンのお供のライス。\n濃厚こってりなスープの後は、白いご飯で小休止。"
e,202509010,enemy_gom_00901_ja,enemy_gom_00901,ja,"ライス (海苔)",ラーメンのお供のライスに豆板醤とニンニクを乗せ、ラーメンのスープを染み込ませた海苔で巻いて。
e,202509010,enemy_gom_01001_ja,enemy_gom_01001,ja,あんぱん,１つ１００円(税込)のあんぱん。中身は粒あんがぎっしり詰まっている。
e,202509010,enemy_gom_01002_ja,enemy_gom_01002,ja,トーストあんぱん,スライスしてトーストしたあんぱん。パンと粒あんが絶妙なセッションを奏でている。バターを乗せても美味しい。
e,202509010,enemy_chi_00001_ja,enemy_chi_00001,ja,ゾンビの悪魔,"『ゾンビの悪魔』。\n力を渡すとその引き換えに相手をゾンビにする。デンジを雇っていたヤクザたちをゾンビにした。デビルハンターのことが嫌い。"
e,202509010,enemy_chi_00101_ja,enemy_chi_00101,ja,ゾンビ,"『ゾンビの悪魔』の奴隷となり、力を得る代わりにゾンビになった。\n『ゾンビの悪魔』の命令に従い行動する。"
e,202509010,enemy_chi_00201_ja,enemy_chi_00201,ja,コウモリの悪魔,"大きなコウモリの姿をした悪魔。\n人間に負傷させられ、潜伏していた。人質を取るなど卑劣な手を使う。"
e,202509010,enemy_kai_00001_ja,enemy_kai_00001,ja,"怪獣 本獣","防衛隊員選別試験の最終審査で、カフカたち受験生の討伐対象になった本獣。4足歩行で移動する。\n目は退化しており視力が弱く、代わりに聴覚が発達している。"
e,202509010,enemy_kai_00101_ja,enemy_kai_00101,ja,"怪獣 余獣","防衛隊員選別試験の最終審査で、カフカたち受験生の討伐対象になった余獣。4足歩行で移動する。\n目は退化しており視力が弱く、代わりに聴覚が発達している。"
e,202509010,enemy_kai_00201_ja,enemy_kai_00201,ja,怪獣９号,"防衛隊員選別試験に突如として現れた未知の大怪獣。\nその後も度々姿を現しカフカ達の脅威となる。"
e,202509010,enemy_kai_00301_ja,enemy_kai_00301,ja,蜘蛛の怪獣,蜘蛛のような身体的特徴を持つ怪獣。6本足で歩行し、巨大な口で獲物に噛み付く。
e,202509010,enemy_kai_00401_ja,enemy_kai_00401,ja,怪獣１０号,"怪獣を引き連れ、立川基地を襲撃した大怪獣。\n全身を覆うような装甲を持ち、非常に好戦的な性格をしている。"
e,202509010,enemy_sur_00001_ja,enemy_sur_00001,ja,"和倉 青羽","魔都に現れた人型醜鬼。\n京香の故郷を襲った一本角の醜鬼を従えている。"
e,202509010,enemy_sur_00101_ja,enemy_sur_00101,ja,醜鬼,"魔都に生息する魔物。\n形状も大きさもさまざまだが、基本的には人型のような姿に、仮面を被ったような姿をしている。"
e,202509010,enemy_jig_00001_ja,enemy_jig_00001,ja,門神,"島に上陸してすぐに現れた化物。\n人間の目にあたる部分から手が生えており、黒い肌をしている。"
e,202509010,enemy_jig_00201_ja,enemy_jig_00201,ja,"門神 (大)","島に上陸してすぐに現れた化物。\n人間の目にあたる部分から手が生えており、黒い肌をしている。"
e,202509010,enemy_jig_00301_ja,enemy_jig_00301,ja,"竈神 魚","頭は魚だが６本の腕を持つ人工的な生き物。\n下半身は2本の脚のようなものが生えている。"
e,202509010,enemy_jig_00401_ja,enemy_jig_00401,ja,極楽蝶,"蝶のような姿をしているが、人のような頭がついている人工的な生き物。\n鱗粉を撒き散らしたり、針のようなものを刺して攻撃する。"
e,202509010,enemy_jig_00402_ja,enemy_jig_00402,ja,極楽蝶,"蝶のような姿をしているが、人のような頭がついている人工的な生き物。\n鱗粉を撒き散らしたり、針のようなものを刺して攻撃する。"
e,202601010,enemy_jig_00501_ja,enemy_jig_00501,ja,"山田浅ェ門 源嗣","山田浅ェ門・試一刀流、八位。頑固だが、山田浅ェ門 佐切を案じる優しい一面も持ち合わせる。"
e,202601010,enemy_jig_00601_ja,enemy_jig_00601,ja,朱槿,仙薬を守る仙人のうちの1人、朱槿(ヂュジン)。再生能力を持ち、性別も時に入れ替わる。
e,202509010,enemy_mag_00001_ja,enemy_mag_00001,ja,冷却系怪異,"面接会場に現れた冷却系怪異。\n複数の目のようなものが体についた姿をしている。"
e,202509010,enemy_mag_00101_ja,enemy_mag_00101,ja,つらら,冷却系怪異が放ったつらら。
e,202509010,enemy_mag_00201_ja,enemy_mag_00201,ja,"建造物寄生型の怪異 (大)","ビルのワンフロアを埋め尽くしていた、建造物寄生型の大型怪異。\n元々は水道管に溜まっていた怪異だったが、突如として膨張しビルのワンフロアを埋め尽くした。"
e,202509010,enemy_mag_00301_ja,enemy_mag_00301,ja,建造物寄生型の怪異,"ビルのワンフロアを埋め尽くしていた、建造物寄生型の小型怪異。\n元々は水道管に溜まっていた怪異だったが、突如として膨張しビルのワンフロアを埋め尽くした。"
e,202511010,enemy_mag_00401_ja,enemy_mag_00401,ja,工事現場の怪異,"工事現場に現れた怪異。\n瓦礫を取り込んで非常に大きな姿をしている。複数の目のようなものが体についている。"
e,202509010,enemy_sum_00001_ja,enemy_sum_00001,ja,影,"『影を見た物は死ぬ』——。\n影が身近な人間に成り代わってしまうという、日都ヶ島(ひとがしま)に伝わっている怪談に登場する謎の存在。\n"
e,202509010,enemy_sum_00101_ja,enemy_sum_00101,ja,"小舟 澪の影 (拳銃)","小舟 澪の影。\nセーラー服を着用している。\n本物の小舟 澪とは異なり、クールな性格をしている。武器として影の凸村から貰った拳銃を持っている。"
e,202509010,enemy_sum_00201_ja,enemy_sum_00201,ja,"小舟 澪の影 (包丁)","小舟 澪の影。\nセーラー服を着用している。\n本物の小舟 澪とは異なり、クールな性格をしている。武器として包丁を持っている。"
e,202602010,enemy_you_00001_ja,enemy_you_00001,ja,不良系金髪イケメン,「ブラック幼稚園」に送られてきた暗殺者。ラーメンはチャーシューから食べる派。
e,202602010,enemy_you_00101_ja,enemy_you_00101,ja,イケメンじゃない殺し屋,ダグを攫った殺し屋の1人。イケメンではない。
e,202509010,enemy_glo_00001_ja,enemy_glo_00001,ja,ファントム,ー
e,202512020,enemy_glo_00002_ja,enemy_glo_00002,ja,推し活ファントム,ー
e,202509010,enemy_glo_00101_ja,enemy_glo_00101,ja,ボスファントム,ー
e,202509010,e_glo_00001_tutorial_Normal_Blue_ja,e_glo_00001_tutorial_Normal_Blue,ja,ファントム,ー
e,202510025,e_kai_00002_tutorial_ja,e_kai_00002_tutorial,ja,"隠された英雄の姿 怪獣８号","謎の生物に寄生され、怪獣化した日比野 カフカの姿。\n人々を守るために拳をふるい、怪獣を討伐する。\n防衛隊発足以来初の未討伐怪獣として日本中から追われていたが、怪獣１０号による立川基地襲撃の際、仲間の危機を救うために人前で変身。その正体を明かすこととなった。"
e,202510025,e_spy_00101_tutorial_ja,e_spy_00101_tutorial,ja,"<黄昏> ロイド",東西平和の実現のために活動する西国(ウェスタリス)きっての敏腕諜報員(エージェント)。名前も過去も捨て、戦争を回避するために東国(オスタニア)にて諜報活動を行なっている。現在は超難関任務「オペレーション<梟>(ストリクス)」に従事。ロイド・フォージャーという名前と肩書は、この任務のために用意された仮初めのもの。
e,202510025,e_dan_00002_tutorial_ja,e_dan_00002_tutorial,ja,"ターボババアの霊力 オカルン","オカルンがターボババアの呪いを受けて変身した姿。\n髪は白髪で逆立ち、口元にはマスクが浮かび上がる。脅威のスピードで動きまわり、限界を突破する“本気”を使うことができる。\nしかし普段とは異なり気だるくネガティブな性格になってしまう。"
e,202510025,e_sur_00101_tutorial_ja,e_sur_00101_tutorial,ja,"誇り高き魔都の剣姫 羽前 京香","魔防隊七番組の組長。\n真面目な性格で、日々鍛錬を欠かさない。醜鬼に故郷を滅ぼされた過去から、醜鬼の絶滅を目標としている。能力は「無窮の鎖(スレイブ)」。奴隷にした生命体の力を引き出し行使できる能力で、現在は魔都で助けた和倉優希を使役している。"
e,202510025,e_chi_00002_tutorial_ja,e_chi_00002_tutorial,ja,"悪魔が恐れる悪魔 チェンソーマン","相棒のポチタの命と引き換えに『チェンソーの悪魔』として蘇ったデンジの姿。頭や腕など体から複数のチェンソーの刃が飛び出している。\n 胸から出ているスターターロープを引っ張ることで変身し、\n チェンソーマンになると深い傷を負っていても復活・蘇生することができる。"
```

---

<!-- FILE: ./projects/glow-masterdata/MstEvent.csv -->
## ./projects/glow-masterdata/MstEvent.csv

```csv
ENABLE,id,mst_series_id,is_displayed_series_logo,is_displayed_jump_plus,start_at,end_at,asset_key,release_key
e,event_kai_00001,kai,1,1,"2025-09-22 11:00:00","2025-10-22 11:59:59",event_kai_00001,202509010
e,event_spy_00001,spy,1,1,"2025-10-06 15:00:00","2025-11-06 14:59:59",event_spy_00001,202510010
e,event_dan_00001,dan,1,1,"2025-10-22 15:00:00","2025-11-25 14:59:59",event_dan_00001,202510020
e,event_mag_00001,mag,1,1,"2025-11-06 15:00:00","2025-12-08 10:59:59",event_mag_00001,202511010
e,event_yuw_00001,yuw,1,1,"2025-11-25 15:00:00","2025-12-31 23:59:59",event_yuw_00001,202511020
e,event_sur_00001,sur,1,1,"2025-12-08 15:00:00","2026-01-16 10:59:59",event_sur_00001,202512010
e,event_osh_00001,osh,1,1,"2026-01-01 00:00:00","2026-02-02 10:59:59",event_osh_00001,202512020
e,event_glo_00001,glo,1,1,"2026-01-01 00:00:00","2026-01-05 23:59:59",event_glo_00001,202512020
e,event_jig_00001,jig,1,1,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig_00001,202601010
e,event_you_00001,you,1,1,"2026-02-02 15:00:00","2026-03-02 10:59:59",event_you_00001,202602010
e,event_kim_00001,kim,1,1,"2026-02-16 15:00:00","2026-03-16 10:59:59",event_you_00001,999999999
```

---

<!-- FILE: ./projects/glow-masterdata/MstEventI18n.csv -->
## ./projects/glow-masterdata/MstEventI18n.csv

```csv
ENABLE,release_key,id,mst_event_id,language,name,balloon
e,202509010,event_kai_00001_ja,event_kai_00001,ja,怪獣８号いいジャン祭,"怪獣８号いいジャン祭\n開催中!"
e,202510010,event_spy_00001_ja,event_spy_00001,ja,SPY×FAMILYいいジャン祭,"SPY×FAMILYいいジャン祭\n開催中！"
e,202510020,event_dan_00001_ja,event_dan_00001,ja,ダンダダンいいジャン祭,"ダンダダンいいジャン祭\n開催中！"
e,202511010,event_mag_00001_ja,event_mag_00001,ja,株式会社マジルミエいいジャン祭,"株式会社マジルミエいいジャン祭\n開催中！"
e,202511020,event_yuw_00001_ja,event_yuw_00001,ja,"2.5次元の誘惑 いいジャン祭","2.5次元の誘惑 いいジャン祭\n開催中！"
e,202512010,event_sur_00001_ja,event_sur_00001,ja,"魔都精兵のスレイブ いいジャン祭","魔都精兵のスレイブ いいジャン祭\n開催中！"
e,202512020,event_osh_00001_ja,event_osh_00001,ja,"【推しの子】 いいジャン祭","【推しの子】 いいジャン祭\n開催中！"
e,202512020,event_glo_00001_ja,event_glo_00001,ja,お正月キャンペーン,"お正月キャンペーン\n開催中！"
e,202601010,event_jig_00001_ja,event_jig_00001,ja,地獄楽いいジャン祭,"地獄楽いいジャン祭\n開催中！"
e,202602010,event_you_00001_ja,event_you_00001,ja,"幼稚園WARS いいジャン祭","幼稚園WARS いいジャン祭\n開催中！"
e,999999999,event_kim_00001_ja,event_kim_00001,ja,100カノいいジャン祭,"君のことが大大大大大好きな100人の彼女いいジャン祭\n開催中！"
```

---

<!-- FILE: ./projects/glow-masterdata/MstItem.csv -->
## ./projects/glow-masterdata/MstItem.csv

```csv
ENABLE,id,type,group_type,rarity,asset_key,effect_value,sort_order,start_date,end_date,release_key,item_type,destination_opr_product_id
e,memory_glo_00001,RankUpMaterial,Etc,UR,memory_glo_00001,Colorless,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_glo_00002,RankUpMaterial,Etc,UR,memory_glo_00002,Red,2,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_glo_00003,RankUpMaterial,Etc,UR,memory_glo_00003,Blue,3,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_glo_00004,RankUpMaterial,Etc,UR,memory_glo_00004,Yellow,4,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_glo_00005,RankUpMaterial,Etc,UR,memory_glo_00005,Green,5,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_chara_kai_00501,RankUpMaterial,Etc,SR,memory_chara_kai_00501,chara_kai_00501,1000,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_chara_kai_00601,RankUpMaterial,Etc,SR,memory_chara_kai_00601,chara_kai_00601,1001,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMaterial,
e,memory_chara_spy_00301,RankUpMaterial,Etc,SR,memory_chara_spy_00301,chara_spy_00301,1002,"2025-10-06 15:00:00","2037-12-31 23:59:59",202510010,RankUpMaterial,
e,memory_chara_spy_00401,RankUpMaterial,Etc,SR,memory_chara_spy_00401,chara_spy_00401,1003,"2025-10-06 15:00:00","2037-12-31 23:59:59",202510010,RankUpMaterial,
e,memory_chara_dan_00201,RankUpMaterial,Etc,SR,memory_chara_dan_00201,chara_dan_00201,1004,"2025-10-22 15:00:00","2037-12-31 23:59:59",202510020,RankUpMaterial,
e,memory_chara_dan_00301,RankUpMaterial,Etc,SR,memory_chara_dan_00301,chara_dan_00301,1005,"2025-10-22 15:00:00","2037-12-31 23:59:59",202510020,RankUpMaterial,
e,memory_chara_mag_00401,RankUpMaterial,Etc,SR,memory_chara_mag_00401,chara_mag_00401,1006,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,RankUpMaterial,
e,memory_chara_mag_00501,RankUpMaterial,Etc,SR,memory_chara_mag_00501,chara_mag_00501,1007,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,RankUpMaterial,
e,memory_chara_yuw_00501,RankUpMaterial,Etc,SR,memory_chara_yuw_00501,chara_yuw_00501,1008,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,RankUpMaterial,
e,memory_chara_yuw_00601,RankUpMaterial,Etc,SR,memory_chara_yuw_00601,chara_yuw_00601,1009,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,RankUpMaterial,
e,memory_chara_sur_00701,RankUpMaterial,Etc,SR,memory_chara_sur_00701,chara_sur_00701,1010,"2025-12-08 12:00:00","2037-12-31 23:59:59",202512010,RankUpMaterial,
e,memory_chara_sur_00801,RankUpMaterial,Etc,SR,memory_chara_sur_00801,chara_sur_00801,1011,"2025-12-08 15:00:00","2037-12-31 23:59:59",202512010,RankUpMaterial,
e,memory_chara_osh_00601,RankUpMaterial,Etc,SR,memory_chara_osh_00601,chara_osh_00601,1012,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,RankUpMaterial,
e,memory_chara_jig_00601,RankUpMaterial,Etc,SR,memory_chara_jig_00601,chara_jig_00601,1013,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,RankUpMaterial,
e,memory_chara_jig_00701,RankUpMaterial,Etc,SR,memory_chara_jig_00701,chara_jig_00701,1014,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,RankUpMaterial,
e,memory_chara_you_00201,RankUpMaterial,Etc,SR,memory_chara_you_00201,chara_you_00201,1015,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,RankUpMaterial,
e,memory_chara_you_00301,RankUpMaterial,Etc,SR,memory_chara_you_00301,chara_you_00301,1016,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,RankUpMaterial,
e,memoryfragment_glo_00001,RankUpMemoryFragment,Etc,SR,memoryfragment_glo_00001,Colorless,20,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMemoryFragment,
e,memoryfragment_glo_00002,RankUpMemoryFragment,Etc,SSR,memoryfragment_glo_00002,Colorless,19,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMemoryFragment,
e,memoryfragment_glo_00003,RankUpMemoryFragment,Etc,UR,memoryfragment_glo_00003,Colorless,18,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RankUpMemoryFragment,
e,box_glo_00001,RandomFragmentBox,Consumable,R,box_glo_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RandomFragmentBox,
e,box_glo_00002,RandomFragmentBox,Consumable,SR,box_glo_00002,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RandomFragmentBox,
e,box_glo_00003,RandomFragmentBox,Consumable,SSR,box_glo_00003,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RandomFragmentBox,
e,box_glo_00004,RandomFragmentBox,Consumable,UR,box_glo_00004,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,RandomFragmentBox,
e,box_glo_00005,SelectionFragmentBox,Consumable,R,box_glo_00005,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,SelectionFragmentBox,
e,box_glo_00006,SelectionFragmentBox,Consumable,SR,box_glo_00006,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,SelectionFragmentBox,
e,box_glo_00007,SelectionFragmentBox,Consumable,SSR,box_glo_00007,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,SelectionFragmentBox,
e,box_glo_00008,SelectionFragmentBox,Consumable,UR,box_glo_00008,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,SelectionFragmentBox,
e,piece_spy_00001,CharacterFragment,Etc,UR,piece_spy_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_spy_00101,CharacterFragment,Etc,UR,piece_spy_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_spy_00201,CharacterFragment,Etc,UR,piece_spy_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_spy_00301,CharacterFragment,Etc,SR,piece_spy_00301,,1,"2025-10-06 15:00:00","2037-12-31 23:59:59",202510010,CharacterFragment,
e,piece_spy_00401,CharacterFragment,Etc,SR,piece_spy_00401,,1,"2025-10-06 15:00:00","2037-12-31 23:59:59",202510010,CharacterFragment,
e,piece_spy_00501,CharacterFragment,Etc,UR,piece_spy_00501,,1,"2025-10-06 15:00:00","2037-12-31 23:59:59",202510010,CharacterFragment,
e,piece_aka_00001,CharacterFragment,Etc,R,piece_aka_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_aka_00101,CharacterFragment,Etc,R,piece_aka_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_rik_00001,CharacterFragment,Etc,R,piece_rik_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_rik_00101,CharacterFragment,Etc,R,piece_rik_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dan_00001,CharacterFragment,Etc,R,piece_dan_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dan_00002,CharacterFragment,Etc,UR,piece_dan_00002,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dan_00101,CharacterFragment,Etc,SSR,piece_dan_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dan_00201,CharacterFragment,Etc,SR,piece_dan_00201,,1,"2025-10-22 15:00:00","2037-12-31 23:59:59",202510020,CharacterFragment,
e,piece_dan_00301,CharacterFragment,Etc,SR,piece_dan_00301,,1,"2025-10-22 15:00:00","2037-12-31 23:59:59",202510020,CharacterFragment,
e,piece_gom_00001,CharacterFragment,Etc,UR,piece_gom_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_gom_00101,CharacterFragment,Etc,SR,piece_gom_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_gom_00201,CharacterFragment,Etc,R,piece_gom_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_chi_00001,CharacterFragment,Etc,R,piece_chi_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_chi_00002,CharacterFragment,Etc,UR,piece_chi_00002,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_chi_00201,CharacterFragment,Etc,SSR,piece_chi_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_chi_00301,CharacterFragment,Etc,SSR,piece_chi_00301,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_bat_00001,CharacterFragment,Etc,SR,piece_bat_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_bat_00101,CharacterFragment,Etc,SR,piece_bat_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00001,CharacterFragment,Etc,R,piece_kai_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00002,CharacterFragment,Etc,UR,piece_kai_00002,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00101,CharacterFragment,Etc,SR,piece_kai_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00201,CharacterFragment,Etc,UR,piece_kai_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00301,CharacterFragment,Etc,SSR,piece_kai_00301,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00401,CharacterFragment,Etc,SSR,piece_kai_00401,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00501,CharacterFragment,Etc,SR,piece_kai_00501,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_kai_00601,CharacterFragment,Etc,SR,piece_kai_00601,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_yuw_00001,CharacterFragment,Etc,UR,piece_yuw_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_yuw_00101,CharacterFragment,Etc,UR,piece_yuw_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_yuw_00102,CharacterFragment,Etc,UR,piece_yuw_00102,,1,"2025-12-22 12:00:00","2037-12-31 23:59:59",202512015,CharacterFragment,
e,piece_yuw_00201,CharacterFragment,Etc,SSR,piece_yuw_00201,,1,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,CharacterFragment,
e,piece_yuw_00301,CharacterFragment,Etc,UR,piece_yuw_00301,,1,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,CharacterFragment,
e,piece_yuw_00401,CharacterFragment,Etc,UR,piece_yuw_00401,,1,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,CharacterFragment,
e,piece_yuw_00501,CharacterFragment,Etc,SR,piece_yuw_00501,,1,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,CharacterFragment,
e,piece_yuw_00601,CharacterFragment,Etc,SR,piece_yuw_00601,,1,"2025-11-25 15:00:00","2037-12-31 23:59:59",202511020,CharacterFragment,
e,piece_sur_00001,CharacterFragment,Etc,R,piece_sur_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sur_00101,CharacterFragment,Etc,UR,piece_sur_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sur_00201,CharacterFragment,Etc,SR,piece_sur_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sur_00301,CharacterFragment,Etc,SR,piece_sur_00301,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sur_00401,CharacterFragment,Etc,SR,piece_sur_00401,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sur_00501,CharacterFragment,Etc,UR,piece_sur_00501,,1,"2025-12-08 12:00:00","2037-12-31 23:59:59",202512010,CharacterFragment,
e,piece_sur_00601,CharacterFragment,Etc,SSR,piece_sur_00601,,1,"2025-12-08 12:00:00","2037-12-31 23:59:59",202512010,CharacterFragment,
e,piece_sur_00701,CharacterFragment,Etc,SR,piece_sur_00701,,1,"2025-12-08 12:00:00","2037-12-31 23:59:59",202512010,CharacterFragment,
e,piece_sur_00801,CharacterFragment,Etc,SR,piece_sur_00801,,1,"2025-12-08 15:00:00","2037-12-31 23:59:59",202512010,CharacterFragment,
e,piece_ron_00001,CharacterFragment,Etc,SSR,piece_ron_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_ron_00101,CharacterFragment,Etc,R,piece_ron_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_aha_00101,CharacterFragment,Etc,SSR,piece_aha_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_aha_00001,CharacterFragment,Etc,SR,piece_aha_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_tak_00001,CharacterFragment,Etc,UR,piece_tak_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_jig_00001,CharacterFragment,Etc,UR,piece_jig_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_jig_00101,CharacterFragment,Etc,SSR,piece_jig_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_jig_00201,CharacterFragment,Etc,SSR,piece_jig_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_jig_00301,CharacterFragment,Etc,SR,piece_jig_00301,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_mag_00001,CharacterFragment,Etc,UR,piece_mag_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_mag_00101,CharacterFragment,Etc,SR,piece_mag_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dos_00001,CharacterFragment,Etc,SSR,piece_dos_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dos_00101,CharacterFragment,Etc,SSR,piece_dos_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sum_00001,CharacterFragment,Etc,R,piece_sum_00001,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sum_00101,CharacterFragment,Etc,UR,piece_sum_00101,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_sum_00201,CharacterFragment,Etc,SR,piece_sum_00201,,1,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,CharacterFragment,
e,piece_dan_00202,CharacterFragment,Etc,UR,piece_dan_00202,,1,"2025-10-22 15:00:00","2037-12-31 23:59:59",202510020,CharacterFragment,
e,piece_mag_00201,CharacterFragment,Etc,UR,piece_mag_00201,,1,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,CharacterFragment,
e,piece_mag_00301,CharacterFragment,Etc,SSR,piece_mag_00301,,1,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,CharacterFragment,
e,piece_mag_00401,CharacterFragment,Etc,SR,piece_mag_00401,,1,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,CharacterFragment,
e,piece_mag_00501,CharacterFragment,Etc,SR,piece_mag_00501,,1,"2025-11-06 15:00:00","2037-12-31 23:59:59",202511010,CharacterFragment,
e,piece_osh_00001,CharacterFragment,Etc,UR,piece_osh_00001,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00101,CharacterFragment,Etc,UR,piece_osh_00101,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00201,CharacterFragment,Etc,SSR,piece_osh_00201,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00301,CharacterFragment,Etc,SSR,piece_osh_00301,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00401,CharacterFragment,Etc,SSR,piece_osh_00401,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00501,CharacterFragment,Etc,SSR,piece_osh_00501,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_osh_00601,CharacterFragment,Etc,SR,piece_osh_00601,,1,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,CharacterFragment,
e,piece_jig_00401,CharacterFragment,Etc,UR,piece_jig_00401,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
e,piece_jig_00501,CharacterFragment,Etc,SSR,piece_jig_00501,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
e,piece_jig_00601,CharacterFragment,Etc,SR,piece_jig_00601,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
e,piece_jig_00701,CharacterFragment,Etc,SR,piece_jig_00701,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
e,piece_you_00001,CharacterFragment,Etc,UR,piece_you_00001,,1,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,CharacterFragment,
e,piece_you_00101,CharacterFragment,Etc,SSR,piece_you_00101,,1,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,CharacterFragment,
e,piece_you_00201,CharacterFragment,Etc,SR,piece_you_00201,,1,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,CharacterFragment,
e,piece_you_00301,CharacterFragment,Etc,SR,piece_you_00301,,1,"2026-02-02 15:00:00","2037-12-31 23:59:59",202602010,CharacterFragment,
e,piece_kim_00001,CharacterFragment,Etc,UR,piece_kim_00001,,1,"2026-02-16 15:00:00","2037-12-31 23:59:59",202602020,CharacterFragment,
e,piece_kim_00101,CharacterFragment,Etc,SSR,piece_kim_00101,,1,"2026-02-16 15:00:00","2037-12-31 23:59:59",202602020,CharacterFragment,
e,piece_kim_00201,CharacterFragment,Etc,SSR,piece_kim_00201,,1,"2026-02-16 15:00:00","2037-12-31 23:59:59",202602020,CharacterFragment,
e,piece_kim_00301,CharacterFragment,Etc,SSR,piece_kim_00301,,1,"2026-02-16 15:00:00","2037-12-31 23:59:59",202602020,CharacterFragment,
e,ticket_glo_00001,GachaTicket,Consumable,R,ticket_glo_00001,,50,"2024-01-01 00:00:00","2037-12-31 23:59:59",999999999,GachaTicket,
e,ticket_glo_00002,GachaTicket,Consumable,UR,ticket_glo_00002,,49,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00003,GachaTicket,Consumable,UR,ticket_glo_00003,,48,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00004,GachaTicket,Consumable,UR,ticket_glo_00004,,47,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00102,GachaTicket,Consumable,R,ticket_glo_00102,,80,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00104,GachaTicket,Consumable,SR,ticket_glo_00104,,79,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00105,GachaTicket,Consumable,SSR,ticket_glo_00105,,78,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00202,GachaTicket,Consumable,UR,ticket_glo_00202,,77,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00203,GachaTicket,Consumable,UR,ticket_glo_00203,,100,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00204,GachaTicket,Consumable,UR,ticket_glo_00204,,101,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00205,GachaTicket,Consumable,UR,ticket_glo_00205,,102,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00206,GachaTicket,Consumable,UR,ticket_glo_00206,,10000,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_glo_00207,GachaTicket,Consumable,UR,ticket_glo_00203,,10001,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,GachaTicket,
e,ticket_glo_90000,GachaTicket,Consumable,UR,ticket_glo_90000,,60,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,GachaTicket,
e,ticket_osh_10000,GachaTicket,Consumable,SSR,ticket_osh_10000,,30000,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,GachaTicket,
e,ticket_glo_10001,GachaTicket,Consumable,SSR,ticket_glo_10001,,30001,"2026-01-01 00:00:00","2037-12-31 23:59:59",202512020,GachaTicket,
e,ticket_glo_10002,GachaTicket,Consumable,SSR,ticket_glo_10002,,30002,"2026-02-10 15:00:00","2037-12-31 23:59:59",202602010,GachaTicket,
e,ticket_kim_00001,GachaTicket,Consumable,SSR,ticket_kim_00001,,30003,"2026-02-16 15:00:00","2037-12-31 23:59:59",202602020,GachaTicket,
e,stamina_item_glo_00001,StaminaRecoveryFixed,Etc,R,stamina_item_glo_00001,10,1,"2026-02-01 15:00:00","2037-12-31 23:59:59",202602010,StaminaRecoveryFixed,
e,entry_item_glo_00001,Etc,Etc,UR,entry_item_glo_00001,,30,"2024-01-01 00:00:00","2037-12-31 23:59:59",202509010,Etc,
e,item_glo_00001,Etc,Etc,R,item_glo_00001,,20000,"2026-01-01 00:00:00","2026-02-02 10:59:59",202512020,Etc,
e,item_kim_00001,Etc,Etc,R,item_kim_00001,,20004,"2026-02-16 15:00:00","2026-03-16 10:59:59",202602020,Etc,
```

---

<!-- FILE: ./projects/glow-masterdata/MstItemI18n.csv -->
## ./projects/glow-masterdata/MstItemI18n.csv

```csv
ENABLE,release_key,id,mst_item_id,language,name,description
e,202509010,memory_glo_00001_ja,memory_glo_00001,ja,カラーメモリー・グレー,無属性キャラのLv.上限開放に使用するアイテム
e,202509010,memory_glo_00002_ja,memory_glo_00002,ja,カラーメモリー・レッド,赤属性キャラのLv.上限開放に使用するアイテム
e,202509010,memory_glo_00003_ja,memory_glo_00003,ja,カラーメモリー・ブルー,青属性キャラのLv.上限開放に使用するアイテム
e,202509010,memory_glo_00004_ja,memory_glo_00004,ja,カラーメモリー・イエロー,黄属性キャラのLv.上限開放に使用するアイテム
e,202509010,memory_glo_00005_ja,memory_glo_00005,ja,カラーメモリー・グリーン,緑属性キャラのLv.上限開放に使用するアイテム
e,202509010,memory_chara_kai_00501_ja,memory_chara_kai_00501,ja,"四ノ宮 功のメモリー","四ノ宮 功のLv.上限開放に使用するアイテム"
e,202509010,memory_chara_kai_00601_ja,memory_chara_kai_00601,ja,"古橋 伊春のメモリー","古橋 伊春のLv.上限開放に使用するアイテム"
e,202510010,memory_chara_spy_00301_ja,memory_chara_spy_00301,ja,ダミアン・デズモンドのメモリー,ダミアン・デズモンドのLv.上限開放に使用するアイテム
e,202510010,memory_chara_spy_00401_ja,memory_chara_spy_00401,ja,フランキー・フランクリンのメモリー,フランキー・フランクリンのLv.上限開放に使用するアイテム
e,202510020,memory_chara_dan_00201_ja,memory_chara_dan_00201,ja,アイラのメモリー,アイラのLv.上限開放に使用するアイテム
e,202510020,memory_chara_dan_00301_ja,memory_chara_dan_00301,ja,"招き猫 ターボババアのメモリー","招き猫 ターボババアのLv.上限開放に使用するアイテム"
e,202511010,memory_chara_mag_00401_ja,memory_chara_mag_00401,ja,"槇野 あかねのメモリー","槇野 あかねのLv.上限開放に使用するアイテム"
e,202511010,memory_chara_mag_00501_ja,memory_chara_mag_00501,ja,"重本 浩司のメモリー","重本 浩司のLv.上限開放に使用するアイテム"
e,202511020,memory_chara_yuw_00501_ja,memory_chara_yuw_00501,ja,753♡のメモリー,753♡のLv.上限開放に使用するアイテム
e,202511020,memory_chara_yuw_00601_ja,memory_chara_yuw_00601,ja,"奥村 正宗のメモリー","奥村 正宗のLv.上限開放に使用するアイテム"
e,202512010,memory_chara_sur_00701_ja,memory_chara_sur_00701,ja,"和倉 青羽のメモリー","和倉 青羽のLv.上限開放に使用するアイテム"
e,202512010,memory_chara_sur_00801_ja,memory_chara_sur_00801,ja,"無窮の鎖 和倉 優希のメモリー","無窮の鎖 和倉 優希のLv.上限開放に使用するアイテム"
e,202512020,memory_chara_osh_00601_ja,memory_chara_osh_00601,ja,ぴえヨンのメモリー,ぴえヨンのLv.上限開放に使用するアイテム
e,202601010,memory_chara_jig_00601_ja,memory_chara_jig_00601,ja,"民谷 巌鉄斎のメモリー","民谷 巌鉄斎のLv.上限開放に使用するアイテム"
e,202601010,memory_chara_jig_00701_ja,memory_chara_jig_00701,ja,メイのメモリー,メイのLv.上限開放に使用するアイテム
e,202602010,memory_chara_you_00201_ja,memory_chara_you_00201,ja,ダグのメモリー,ダグのLv.上限開放に使用するアイテム
e,202602010,memory_chara_you_00301_ja,memory_chara_you_00301,ja,ハナのメモリー,ハナのLv.上限開放に使用するアイテム
e,202509010,memoryfragment_glo_00001_ja,memoryfragment_glo_00001,ja,メモリーフラグメント・初級,キャラのLv.上限開放に使用するアイテム
e,202509010,memoryfragment_glo_00002_ja,memoryfragment_glo_00002,ja,メモリーフラグメント・中級,キャラのLv.上限開放に使用するアイテム
e,202509010,memoryfragment_glo_00003_ja,memoryfragment_glo_00003,ja,メモリーフラグメント・上級,キャラのLv.上限開放に使用するアイテム
e,202509010,box_glo_00001_ja,box_glo_00001,ja,ランダムRキャラのかけらBOX,Rのキャラのかけらがランダムで獲得できるアイテムBOX
e,202509010,box_glo_00002_ja,box_glo_00002,ja,ランダムSRキャラのかけらBOX,SRのキャラのかけらがランダムで獲得できるアイテムBOX
e,202509010,box_glo_00003_ja,box_glo_00003,ja,ランダムSSRキャラのかけらBOX,SSRのキャラのかけらがランダムで獲得できるアイテムBOX
e,202509010,box_glo_00004_ja,box_glo_00004,ja,ランダムURキャラのかけらBOX,URのキャラのかけらがランダムで獲得できるアイテムBOX
e,202509010,box_glo_00005_ja,box_glo_00005,ja,選べるRキャラのかけらBOX,Rのキャラのかけらを選択して獲得できるアイテムBOX
e,202509010,box_glo_00006_ja,box_glo_00006,ja,選べるSRキャラのかけらBOX,SRのキャラのかけらを選択して獲得できるアイテムBOX
e,202509010,box_glo_00007_ja,box_glo_00007,ja,選べるSSRキャラのかけらBOX,SSRのキャラのかけらを選択して獲得できるアイテムBOX
e,202509010,box_glo_00008_ja,box_glo_00008,ja,選べるURキャラのかけらBOX,URのキャラのかけらを選択して獲得できるアイテムBOX
e,202509010,piece_spy_00001_ja,piece_spy_00001,ja,"わくわく アーニャのかけら","わくわく アーニャのグレードアップに使用するアイテム"
e,202509010,piece_spy_00101_ja,piece_spy_00101,ja,"<黄昏> ロイドのかけら","<黄昏> ロイドのグレードアップに使用するアイテム"
e,202509010,piece_spy_00201_ja,piece_spy_00201,ja,"<いばら姫> ヨルのかけら","<いばら姫> ヨルのグレードアップに使用するアイテム"
e,202510010,piece_spy_00301_ja,piece_spy_00301,ja,ダミアン・デズモンドのかけら,ダミアン・デズモンドのグレードアップに使用するアイテム
e,202510010,piece_spy_00401_ja,piece_spy_00401,ja,フランキー・フランクリンのかけら,フランキー・フランクリンのグレードアップに使用するアイテム
e,202510010,piece_spy_00501_ja,piece_spy_00501,ja,"姉を想う盲愛 ユーリ・ブライアのかけら","姉を想う盲愛 ユーリ・ブライアのグレードアップに使用するアイテム"
e,202509010,piece_aka_00001_ja,piece_aka_00001,ja,佐々木のかけら,佐々木のグレードアップに使用するアイテム
e,202509010,piece_aka_00101_ja,piece_aka_00101,ja,文蔵のかけら,文蔵のグレードアップに使用するアイテム
e,202509010,piece_rik_00001_ja,piece_rik_00001,ja,リコピンのかけら,リコピンのグレードアップに使用するアイテム
e,202509010,piece_rik_00101_ja,piece_rik_00101,ja,"甘戸 めめのかけら","甘戸 めめのグレードアップに使用するアイテム"
e,202509010,piece_dan_00001_ja,piece_dan_00001,ja,オカルンのかけら,オカルンのグレードアップに使用するアイテム
e,202509010,piece_dan_00002_ja,piece_dan_00002,ja,"ターボババアの霊力 オカルンのかけら","ターボババアの霊力 オカルンのグレードアップに使用するアイテム"
e,202509010,piece_dan_00101_ja,piece_dan_00101,ja,モモのかけら,モモのグレードアップに使用するアイテム
e,202510020,piece_dan_00201_ja,piece_dan_00201,ja,アイラのかけら,アイラのグレードアップに使用するアイテム
e,202510020,piece_dan_00301_ja,piece_dan_00301,ja,"招き猫 ターボババアのかけら","招き猫 ターボババアのグレードアップに使用するアイテム"
e,202509010,piece_gom_00001_ja,piece_gom_00001,ja,"囚われの王女 姫様のかけら","囚われの王女 姫様のグレードアップに使用するアイテム"
e,202509010,piece_gom_00101_ja,piece_gom_00101,ja,トーチャー・トルチュールのかけら,トーチャー・トルチュールのグレードアップに使用するアイテム
e,202509010,piece_gom_00201_ja,piece_gom_00201,ja,クロルのかけら,クロルのグレードアップに使用するアイテム
e,202509010,piece_chi_00001_ja,piece_chi_00001,ja,デンジのかけら,デンジのグレードアップに使用するアイテム
e,202509010,piece_chi_00002_ja,piece_chi_00002,ja,"悪魔が恐れる悪魔 チェンソーマンのかけら","悪魔が恐れる悪魔 チェンソーマンのグレードアップに使用するアイテム"
e,202509010,piece_chi_00201_ja,piece_chi_00201,ja,"早川 アキのかけら","早川 アキのグレードアップに使用するアイテム"
e,202509010,piece_chi_00301_ja,piece_chi_00301,ja,パワーのかけら,パワーのグレードアップに使用するアイテム
e,202509010,piece_bat_00001_ja,piece_bat_00001,ja,"清峰 葉流火のかけら","清峰 葉流火のグレードアップに使用するアイテム"
e,202509010,piece_bat_00101_ja,piece_bat_00101,ja,"要 圭のかけら","要 圭のグレードアップに使用するアイテム"
e,202509010,piece_kai_00001_ja,piece_kai_00001,ja,"日比野 カフカのかけら","日比野 カフカのグレードアップに使用するアイテム"
e,202509010,piece_kai_00002_ja,piece_kai_00002,ja,"隠された英雄の姿 怪獣８号のかけら","隠された英雄の姿 怪獣８号のグレードアップに使用するアイテム"
e,202509010,piece_kai_00101_ja,piece_kai_00101,ja,"市川 レノのかけら","市川 レノのグレードアップに使用するアイテム"
e,202509010,piece_kai_00201_ja,piece_kai_00201,ja,"第三部隊隊長 亜白 ミナのかけら","第三部隊隊長 亜白 ミナのグレードアップに使用するアイテム"
e,202509010,piece_kai_00301_ja,piece_kai_00301,ja,"四ノ宮 キコルのかけら","四ノ宮 キコルのグレードアップに使用するアイテム"
e,202509010,piece_kai_00401_ja,piece_kai_00401,ja,"保科 宗四郎のかけら","保科 宗四郎のグレードアップに使用するアイテム"
e,202509010,piece_kai_00501_ja,piece_kai_00501,ja,"四ノ宮 功のかけら","四ノ宮 功のグレードアップに使用するアイテム"
e,202509010,piece_kai_00601_ja,piece_kai_00601,ja,"古橋 伊春のかけら","古橋 伊春のグレードアップに使用するアイテム"
e,202509010,piece_yuw_00001_ja,piece_yuw_00001,ja,"リリエルに捧ぐ愛 天乃 リリサのかけら","リリエルに捧ぐ愛 天乃 リリサのグレードアップに使用するアイテム"
e,202509010,piece_yuw_00101_ja,piece_yuw_00101,ja,"コスプレに託す乙女心 橘 美花莉のかけら","コスプレに託す乙女心 橘 美花莉のグレードアップに使用するアイテム"
e,202512015,piece_yuw_00102_ja,piece_yuw_00102,ja,"愛届ける聖夜のサンタ 橘 美花莉のかけら","愛届ける聖夜のサンタ 橘 美花莉のグレードアップに使用するアイテム"
e,202511020,piece_yuw_00201_ja,piece_yuw_00201,ja,"羽生 まゆりのかけら","羽生 まゆりのグレードアップに使用するアイテム"
e,202511020,piece_yuw_00301_ja,piece_yuw_00301,ja,"勇気を纏うコスプレ 乃愛のかけら","勇気を纏うコスプレ 乃愛のグレードアップに使用するアイテム"
e,202511020,piece_yuw_00401_ja,piece_yuw_00401,ja,"伝えたいウチの想い 喜咲 アリアのかけら","伝えたいウチの想い 喜咲 アリアのグレードアップに使用するアイテム"
e,202511020,piece_yuw_00501_ja,piece_yuw_00501,ja,753♡のかけら,753♡のグレードアップに使用するアイテム
e,202511020,piece_yuw_00601_ja,piece_yuw_00601,ja,"奥村 正宗のかけら","奥村 正宗のグレードアップに使用するアイテム"
e,202509010,piece_sur_00001_ja,piece_sur_00001,ja,"和倉 優希のかけら","和倉 優希のグレードアップに使用するアイテム"
e,202509010,piece_sur_00101_ja,piece_sur_00101,ja,"誇り高き魔都の剣姫 羽前 京香のかけら","誇り高き魔都の剣姫 羽前 京香のグレードアップに使用するアイテム"
e,202509010,piece_sur_00201_ja,piece_sur_00201,ja,"東 日万凛のかけら","東 日万凛のグレードアップに使用するアイテム"
e,202509010,piece_sur_00301_ja,piece_sur_00301,ja,"駿河 朱々のかけら","駿河 朱々のグレードアップに使用するアイテム"
e,202509010,piece_sur_00401_ja,piece_sur_00401,ja,"大川村 寧のかけら","大川村 寧のグレードアップに使用するアイテム"
e,202512010,piece_sur_00501_ja,piece_sur_00501,ja,"空間を操る六番組組長 出雲 天花のかけら","空間を操る六番組組長 出雲 天花のグレードアップに使用するアイテム"
e,202512010,piece_sur_00601_ja,piece_sur_00601,ja,"東 八千穂のかけら","東 八千穂のグレードアップに使用するアイテム"
e,202512010,piece_sur_00701_ja,piece_sur_00701,ja,"和倉 青羽のかけら","和倉 青羽のグレードアップに使用するアイテム"
e,202512010,piece_sur_00801_ja,piece_sur_00801,ja,"無窮の鎖 和倉 優希のかけら","無窮の鎖 和倉 優希のグレードアップに使用するアイテム"
e,202509010,piece_ron_00001_ja,piece_ron_00001,ja,"鴨乃橋 ロンのかけら","鴨乃橋 ロンのグレードアップに使用するアイテム"
e,202509010,piece_ron_00101_ja,piece_ron_00101,ja,"一色 都々丸のかけら","一色 都々丸のグレードアップに使用するアイテム"
e,202509010,piece_aha_00101_ja,piece_aha_00101,ja,"阿波連 れいなのかけら","阿波連 れいなのグレードアップに使用するアイテム"
e,202509010,piece_aha_00001_ja,piece_aha_00001,ja,ライドウのかけら,ライドウのグレードアップに使用するアイテム
e,202509010,piece_tak_00001_ja,piece_tak_00001,ja,"ハッピー星からの使者 タコピーのかけら","ハッピー星からの使者 タコピーのグレードアップに使用するアイテム"
e,202509010,piece_jig_00001_ja,piece_jig_00001,ja,がらんの画眉丸のかけら,がらんの画眉丸のグレードアップに使用するアイテム
e,202509010,piece_jig_00101_ja,piece_jig_00101,ja,"山田浅ェ門 佐切のかけら","山田浅ェ門 佐切のグレードアップに使用するアイテム"
e,202509010,piece_jig_00201_ja,piece_jig_00201,ja,杠のかけら,杠のグレードアップに使用するアイテム
e,202509010,piece_jig_00301_ja,piece_jig_00301,ja,"山田浅ェ門 仙汰のかけら","山田浅ェ門 仙汰のグレードアップに使用するアイテム"
e,202509010,piece_mag_00001_ja,piece_mag_00001,ja,"新人魔法少女 桜木 カナのかけら","新人魔法少女 桜木 カナのグレードアップに使用するアイテム"
e,202509010,piece_mag_00101_ja,piece_mag_00101,ja,"越谷 仁美のかけら","越谷 仁美のグレードアップに使用するアイテム"
e,202509010,piece_dos_00001_ja,piece_dos_00001,ja,"冬木 美波のかけら","冬木 美波のグレードアップに使用するアイテム"
e,202509010,piece_dos_00101_ja,piece_dos_00101,ja,"秋野 沙友理のかけら","秋野 沙友理のグレードアップに使用するアイテム"
e,202509010,piece_sum_00001_ja,piece_sum_00001,ja,"網代 慎平のかけら","網代 慎平のグレードアップに使用するアイテム"
e,202509010,piece_sum_00101_ja,piece_sum_00101,ja,"影のウシオ 小舟 潮のかけら","影のウシオ 小舟 潮のグレードアップに使用するアイテム"
e,202509010,piece_sum_00201_ja,piece_sum_00201,ja,"小舟 澪のかけら","小舟 澪のグレードアップに使用するアイテム"
e,202510020,piece_dan_00202_ja,piece_dan_00202,ja,"アクさらの愛 アイラのかけら","アクさらの愛 アイラのグレードアップに使用するアイテム"
e,202511010,piece_mag_00201_ja,piece_mag_00201,ja,"絶対効率の体現者 土刃 メイのかけら","絶対効率の体現者 土刃 メイのグレードアップに使用するアイテム"
e,202511010,piece_mag_00301_ja,piece_mag_00301,ja,"葵 リリーのかけら","葵 リリーのグレードアップに使用するアイテム"
e,202511010,piece_mag_00401_ja,piece_mag_00401,ja,"槇野 あかねのかけら","槇野 あかねのグレードアップに使用するアイテム"
e,202511010,piece_mag_00501_ja,piece_mag_00501,ja,"重本 浩司のかけら","重本 浩司のグレードアップに使用するアイテム"
e,202512020,piece_osh_00001_ja,piece_osh_00001,ja,"B小町不動のセンター アイのかけら","B小町不動のセンター アイのグレードアップに使用するアイテム"
e,202512020,piece_osh_00101_ja,piece_osh_00101,ja,"復讐を誓う片星 星野 アクアのかけら","復讐を誓う片星 星野 アクアのグレードアップに使用するアイテム"
e,202512020,piece_osh_00201_ja,piece_osh_00201,ja,"星野 ルビーのかけら","星野 ルビーのグレードアップに使用するアイテム"
e,202512020,piece_osh_00301_ja,piece_osh_00301,ja,MEMちょのかけら,MEMちょのグレードアップに使用するアイテム
e,202512020,piece_osh_00401_ja,piece_osh_00401,ja,"有馬 かなのかけら","有馬 かなのグレードアップに使用するアイテム"
e,202512020,piece_osh_00501_ja,piece_osh_00501,ja,"黒川 あかねのかけら","黒川 あかねのグレードアップに使用するアイテム"
e,202512020,piece_osh_00601_ja,piece_osh_00601,ja,ぴえヨンのかけら,ぴえヨンのグレードアップに使用するアイテム
e,202601010,piece_jig_00401_ja,piece_jig_00401,ja,"賊王 亜左 弔兵衛のかけら","賊王 亜左 弔兵衛のグレードアップに使用するアイテム"
e,202601010,piece_jig_00501_ja,piece_jig_00501,ja,"山田浅ェ門 桐馬のかけら","山田浅ェ門 桐馬のグレードアップに使用するアイテム"
e,202601010,piece_jig_00601_ja,piece_jig_00601,ja,"民谷 巌鉄斎のかけら","民谷 巌鉄斎のグレードアップに使用するアイテム"
e,202601010,piece_jig_00701_ja,piece_jig_00701,ja,メイのかけら,メイのグレードアップに使用するアイテム
e,202602010,piece_you_00001_ja,piece_you_00001,ja,"元殺し屋の新人教諭 リタのかけら","元殺し屋の新人教諭 リタのグレードアップに使用するアイテム"
e,202602010,piece_you_00101_ja,piece_you_00101,ja,ルークのかけら,ルークのグレードアップに使用するアイテム
e,202602010,piece_you_00201_ja,piece_you_00201,ja,ダグのかけら,ダグのグレードアップに使用するアイテム
e,202602010,piece_you_00301_ja,piece_you_00301,ja,ハナのかけら,ハナのグレードアップに使用するアイテム
e,202602020,piece_kim_00001_ja,piece_kim_00001,ja,"溢れる母性 花園 羽々里のかけら","溢れる母性 花園 羽々里のグレードアップに使用するアイテム"
e,202602020,piece_kim_00101_ja,piece_kim_00101,ja,"花園 羽香里のかけら","花園 羽香里のグレードアップに使用するアイテム"
e,202602020,piece_kim_00201_ja,piece_kim_00201,ja,"院田 唐音のかけら","院田 唐音のグレードアップに使用するアイテム"
e,202602020,piece_kim_00301_ja,piece_kim_00301,ja,"好本 静のかけら","好本 静のグレードアップに使用するアイテム"
e,999999999,ticket_glo_00001_ja,ticket_glo_00001,ja,ノーマルガシャチケット,ノーマルガシャを引くことができるアイテム
e,202509010,ticket_glo_00002_ja,ticket_glo_00002,ja,スペシャルガシャチケット,スペシャルガシャを引くことができるアイテム
e,202509010,ticket_glo_00003_ja,ticket_glo_00003,ja,ピックアップガシャチケット,ピックアップガシャを引くことができるアイテム
e,202509010,ticket_glo_00004_ja,ticket_glo_00004,ja,フェスガシャチケット,フェスガシャを引くことができるアイテム
e,202509010,ticket_glo_00102_ja,ticket_glo_00102,ja,R以上確定ガシャチケット,R以上確定ガシャを引くことができるアイテム
e,202509010,ticket_glo_00104_ja,ticket_glo_00104,ja,SR以上確定ガシャチケット,SR以上確定ガシャを引くことができるアイテム
e,202509010,ticket_glo_00105_ja,ticket_glo_00105,ja,SSR以上確定ガシャチケット,SSR以上確定ガシャを引くことができるアイテム
e,202509010,ticket_glo_00202_ja,ticket_glo_00202,ja,UR確定ガシャチケット,UR以上確定ガシャを引くことができるアイテム
e,202509010,ticket_glo_00203_ja,ticket_glo_00203,ja,UR1体確定10連ガシャチケット,UR1体確定10連ガシャを引くことができるアイテム
e,202509010,ticket_glo_00204_ja,ticket_glo_00204,ja,ピックアップUR1体確定10連ガシャチケット,ピックアップUR1体確定10連ガシャを引くことができるアイテム
e,202509010,ticket_glo_00205_ja,ticket_glo_00205,ja,URキャラ1体確定10連フェスガシャチケット,URキャラ1体確定10連フェスガシャを引くことができるアイテム
e,202509010,ticket_glo_00206_ja,ticket_glo_00206,ja,『補てん』スタートダッシュガシャチケット,72時間限定スタートダッシュガシャを引くことができるアイテム
e,202512020,ticket_glo_00207_ja,ticket_glo_00207,ja,"『補てん』\n2026年正月記念！UR1体確定ガシャチケット",2026年正月記念！UR1体確定ガシャを引くことができるアイテム
e,202509010,ticket_glo_90000_ja,ticket_glo_90000,ja,プレミアムメダル,プレミアムメダルガシャを引くことができるアイテム
e,202512020,ticket_osh_10000_ja,ticket_osh_10000,ja,【推しの子】SSR確定ガシャチケット,【推しの子】SSR確定ガシャを引くことができるアイテム
e,202512020,ticket_glo_10001_ja,ticket_glo_10001,ja,"賀正ガシャチケット Ver.2026",賀正ガシャ2026を引くことができるアイテム。ミッションをクリアしてたくさん集めよう！
e,202602010,ticket_glo_10002_ja,ticket_glo_10002,ja,バレンタインピックアップガシャチケット,バレンタインピックアップガシャを引くことができるアイテム
e,202602020,ticket_kim_00001_ja,ticket_kim_00001,ja,SSR彼女確定ガシャチケット,SSR彼女確定ガシャを引くことができるアイテム
e,202602010,stamina_item_glo_00001_ja,stamina_item_glo_00001,ja,スタミナドリンク,使用することでスタミナを最大10回復できるアイテム
e,202509010,entry_item_glo_00001_ja,entry_item_glo_00001,ja,ランクマッチチケット,ランクマッチに挑戦するためのアイテム
e,202512020,item_glo_00001_ja,item_glo_00001,ja,いいジャン祭メダル【赤】,交換所で使用できるアイテム
e,202602020,item_kim_00001_ja,item_kim_00001,ja,打ち消しの薬,君のことが大大大大大好きな100人の彼女交換所で使用するアイテム
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionAchievement.csv -->
## ./projects/glow-masterdata/MstMissionAchievement.csv

```csv
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,achievement_2_1,202509010,FollowCompleted,https://x.com/jumpplus_jumble,1,__NULL__,,0,,achievement_2_1,1,Web
e,achievement_2_2,202509010,AccountCompleted,,1,__NULL__,"  ",0,,achievement_2_2,2,LinkBnId
e,achievement_2_3,202509010,AccessWeb,https://jumble-rush-link.bn-ent.net/,1,__NULL__,,0,,achievement_2_3,3,Web
e,achievement_2_4,202509010,LoginCount,,10,__NULL__,,0,,achievement_2_4,4,Home
e,achievement_2_5,202509010,LoginCount,,20,__NULL__,,0,,achievement_2_5,5,Home
e,achievement_2_6,202509010,LoginCount,,30,__NULL__,,0,,achievement_2_6,6,Home
e,achievement_2_7,202509010,LoginCount,,40,__NULL__,,0,,achievement_2_7,7,Home
e,achievement_2_8,202509010,LoginCount,,50,__NULL__,,0,,achievement_2_8,8,Home
e,achievement_2_9,202509010,LoginCount,,60,__NULL__,,0,,achievement_2_9,9,Home
e,achievement_2_10,202509010,LoginCount,,70,__NULL__,,0,,achievement_2_10,10,Home
e,achievement_2_11,202509010,LoginCount,,80,__NULL__,,0,,achievement_2_11,11,Home
e,achievement_2_12,202509010,LoginCount,,90,__NULL__,,0,,achievement_2_12,12,Home
e,achievement_2_13,202509010,LoginCount,,100,__NULL__,,0,,achievement_2_13,13,Home
e,achievement_2_14,202509010,LoginCount,,110,__NULL__,,0,,achievement_2_14,14,Home
e,achievement_2_15,202509010,LoginCount,,120,__NULL__,,0,,achievement_2_15,15,Home
e,achievement_2_16,202509010,LoginCount,,130,__NULL__,,0,,achievement_2_16,16,Home
e,achievement_2_17,202509010,LoginCount,,140,__NULL__,,0,,achievement_2_17,17,Home
e,achievement_2_18,202509010,LoginCount,,150,__NULL__,,0,,achievement_2_18,18,Home
e,achievement_2_19,202509010,LoginCount,,160,__NULL__,,0,,achievement_2_19,19,Home
e,achievement_2_20,202509010,LoginCount,,170,__NULL__,,0,,achievement_2_20,20,Home
e,achievement_2_21,202509010,LoginCount,,180,__NULL__,,0,,achievement_2_21,21,Home
e,achievement_2_22,202509010,LoginCount,,190,__NULL__,,0,,achievement_2_22,22,Home
e,achievement_2_23,202509010,LoginCount,,200,__NULL__,,0,,achievement_2_23,23,Home
e,achievement_2_24,202509010,LoginCount,,250,__NULL__,,0,,achievement_2_24,24,Home
e,achievement_2_25,202509010,LoginCount,,300,__NULL__,,0,,achievement_2_25,25,Home
e,achievement_2_26,202509010,IdleIncentiveCount,,50,__NULL__,,0,,achievement_2_26,26,IdleIncentive
e,achievement_2_27,202509010,IdleIncentiveCount,,100,__NULL__,,0,,achievement_2_27,27,IdleIncentive
e,achievement_2_28,202509010,IdleIncentiveCount,,500,__NULL__,,0,,achievement_2_28,28,IdleIncentive
e,achievement_2_29,202509010,DefeatEnemyCount,,10,__NULL__,,0,,achievement_2_29,29,StageSelect
e,achievement_2_30,202509010,DefeatEnemyCount,,50,__NULL__,,0,,achievement_2_30,30,StageSelect
e,achievement_2_31,202509010,DefeatEnemyCount,,100,__NULL__,,0,,achievement_2_31,31,StageSelect
e,achievement_2_32,202509010,DefeatEnemyCount,,200,__NULL__,,0,,achievement_2_32,32,StageSelect
e,achievement_2_33,202509010,DefeatEnemyCount,,300,__NULL__,,0,,achievement_2_33,33,StageSelect
e,achievement_2_34,202509010,DefeatEnemyCount,,500,__NULL__,,0,,achievement_2_34,34,StageSelect
e,achievement_2_35,202509010,DefeatEnemyCount,,1000,__NULL__,,0,,achievement_2_35,35,StageSelect
e,achievement_2_36,202509010,DefeatBossEnemyCount,,10,__NULL__,,0,,achievement_2_36,36,StageSelect
e,achievement_2_37,202509010,DefeatBossEnemyCount,,50,__NULL__,,0,,achievement_2_37,37,StageSelect
e,achievement_2_38,202509010,DefeatBossEnemyCount,,100,__NULL__,,0,,achievement_2_38,38,StageSelect
e,achievement_2_39,202509010,UnitLevelUpCount,,50,__NULL__,,0,,achievement_2_39,39,UnitList
e,achievement_2_40,202509010,UnitLevelUpCount,,100,__NULL__,,0,,achievement_2_40,40,UnitList
e,achievement_2_41,202509010,UnitLevelUpCount,,300,__NULL__,,0,,achievement_2_41,41,UnitList
e,achievement_2_42,202509010,UnitLevelUpCount,,500,__NULL__,,0,,achievement_2_42,42,UnitList
e,achievement_2_43,202509010,UnitLevelUpCount,,1000,__NULL__,,0,,achievement_2_43,43,UnitList
e,achievement_2_44,202509010,CoinCollect,,200000,__NULL__,,0,,achievement_2_44,44,StageSelect
e,achievement_2_45,202509010,CoinCollect,,300000,__NULL__,,0,,achievement_2_45,45,StageSelect
e,achievement_2_46,202509010,CoinCollect,,500000,__NULL__,,0,,achievement_2_46,46,StageSelect
e,achievement_2_47,202509010,CoinCollect,,1000000,__NULL__,,0,,achievement_2_47,47,StageSelect
e,achievement_2_48,202509010,OutpostEnhanceCount,,10,__NULL__,,0,,achievement_2_48,48,OutpostEnhance
e,achievement_2_49,202509010,OutpostEnhanceCount,,20,__NULL__,,0,,achievement_2_49,49,OutpostEnhance
e,achievement_2_50,202509010,OutpostEnhanceCount,,30,__NULL__,,0,,achievement_2_50,50,OutpostEnhance
e,achievement_2_51,202509010,OutpostEnhanceCount,,40,__NULL__,,0,,achievement_2_51,51,OutpostEnhance
e,achievement_2_52,202509010,OutpostEnhanceCount,,50,__NULL__,,0,,achievement_2_52,52,OutpostEnhance
e,achievement_2_53,202509010,SpecificQuestClear,quest_main_spy_normal_1,1,__NULL__,,0,,achievement_2_53,53,QuestSelect
e,achievement_2_54,202509010,SpecificQuestClear,quest_main_spy_hard_1,1,__NULL__,,0,,achievement_2_54,54,QuestSelect
e,achievement_2_55,202509010,SpecificQuestClear,quest_main_spy_veryhard_1,1,__NULL__,,0,,achievement_2_55,55,QuestSelect
e,achievement_2_56,202509010,SpecificQuestClear,quest_main_gom_normal_2,1,__NULL__,,0,,achievement_2_56,56,QuestSelect
e,achievement_2_57,202509010,SpecificQuestClear,quest_main_gom_hard_2,1,__NULL__,,0,,achievement_2_57,57,QuestSelect
e,achievement_2_58,202509010,SpecificQuestClear,quest_main_gom_veryhard_2,1,__NULL__,,0,,achievement_2_58,58,QuestSelect
e,achievement_2_59,202509010,SpecificQuestClear,quest_main_aka_normal_3,1,__NULL__,,0,,achievement_2_59,59,QuestSelect
e,achievement_2_60,202509010,SpecificQuestClear,quest_main_aka_hard_3,1,__NULL__,,0,,achievement_2_60,60,QuestSelect
e,achievement_2_61,202509010,SpecificQuestClear,quest_main_aka_veryhard_3,1,__NULL__,,0,,achievement_2_61,61,QuestSelect
e,achievement_2_62,202509010,SpecificQuestClear,quest_main_glo1_normal_4,1,__NULL__,,0,,achievement_2_62,62,QuestSelect
e,achievement_2_63,202509010,SpecificQuestClear,quest_main_glo1_hard_4,1,__NULL__,,0,,achievement_2_63,63,QuestSelect
e,achievement_2_64,202509010,SpecificQuestClear,quest_main_glo1_veryhard_4,1,__NULL__,,0,,achievement_2_64,64,QuestSelect
e,achievement_2_65,202509010,SpecificQuestClear,quest_main_dan_normal_5,1,__NULL__,,0,,achievement_2_65,65,QuestSelect
e,achievement_2_66,202509010,SpecificQuestClear,quest_main_dan_hard_5,1,__NULL__,,0,,achievement_2_66,66,QuestSelect
e,achievement_2_67,202509010,SpecificQuestClear,quest_main_dan_veryhard_5,1,__NULL__,,0,,achievement_2_67,67,QuestSelect
e,achievement_2_68,202509010,SpecificQuestClear,quest_main_jig_normal_6,1,__NULL__,,0,,achievement_2_68,68,QuestSelect
e,achievement_2_69,202509010,SpecificQuestClear,quest_main_jig_hard_6,1,__NULL__,,0,,achievement_2_69,69,QuestSelect
e,achievement_2_70,202509010,SpecificQuestClear,quest_main_jig_veryhard_6,1,__NULL__,,0,,achievement_2_70,70,QuestSelect
e,achievement_2_71,202509010,SpecificQuestClear,quest_main_tak_normal_7,1,__NULL__,,0,,achievement_2_71,71,QuestSelect
e,achievement_2_72,202509010,SpecificQuestClear,quest_main_tak_hard_7,1,__NULL__,,0,,achievement_2_72,72,QuestSelect
e,achievement_2_73,202509010,SpecificQuestClear,quest_main_tak_veryhard_7,1,__NULL__,,0,,achievement_2_73,73,QuestSelect
e,achievement_2_74,202509010,SpecificQuestClear,quest_main_glo2_normal_8,1,__NULL__,,0,,achievement_2_74,74,QuestSelect
e,achievement_2_75,202509010,SpecificQuestClear,quest_main_glo2_hard_8,1,__NULL__,,0,,achievement_2_75,75,QuestSelect
e,achievement_2_76,202509010,SpecificQuestClear,quest_main_glo2_veryhard_8,1,__NULL__,,0,,achievement_2_76,76,QuestSelect
e,achievement_2_77,202509010,SpecificQuestClear,quest_main_chi_normal_9,1,__NULL__,,0,,achievement_2_77,77,QuestSelect
e,achievement_2_78,202509010,SpecificQuestClear,quest_main_chi_hard_9,1,__NULL__,,0,,achievement_2_78,78,QuestSelect
e,achievement_2_79,202509010,SpecificQuestClear,quest_main_chi_veryhard_9,1,__NULL__,,0,,achievement_2_79,79,QuestSelect
e,achievement_2_80,202509010,SpecificQuestClear,quest_main_sur_normal_10,1,__NULL__,,0,,achievement_2_80,80,QuestSelect
e,achievement_2_81,202509010,SpecificQuestClear,quest_main_sur_hard_10,1,__NULL__,,0,,achievement_2_81,81,QuestSelect
e,achievement_2_82,202509010,SpecificQuestClear,quest_main_sur_veryhard_10,1,__NULL__,,0,,achievement_2_82,82,QuestSelect
e,achievement_2_83,202509010,SpecificQuestClear,quest_main_rik_normal_11,1,__NULL__,,0,,achievement_2_83,83,QuestSelect
e,achievement_2_84,202509010,SpecificQuestClear,quest_main_rik_hard_11,1,__NULL__,,0,,achievement_2_84,84,QuestSelect
e,achievement_2_85,202509010,SpecificQuestClear,quest_main_rik_veryhard_11,1,__NULL__,,0,,achievement_2_85,85,QuestSelect
e,achievement_2_86,202509010,SpecificQuestClear,quest_main_glo3_normal_12,1,__NULL__,,0,,achievement_2_86,86,QuestSelect
e,achievement_2_87,202509010,SpecificQuestClear,quest_main_glo3_hard_12,1,__NULL__,,0,,achievement_2_87,87,QuestSelect
e,achievement_2_88,202509010,SpecificQuestClear,quest_main_glo3_veryhard_12,1,__NULL__,,0,,achievement_2_88,88,QuestSelect
e,achievement_2_89,202509010,SpecificQuestClear,quest_main_mag_normal_13,1,__NULL__,,0,,achievement_2_89,89,QuestSelect
e,achievement_2_90,202509010,SpecificQuestClear,quest_main_mag_hard_13,1,__NULL__,,0,,achievement_2_90,90,QuestSelect
e,achievement_2_91,202509010,SpecificQuestClear,quest_main_mag_veryhard_13,1,__NULL__,,0,,achievement_2_91,91,QuestSelect
e,achievement_2_92,202509010,SpecificQuestClear,quest_main_sum_normal_14,1,__NULL__,,0,,achievement_2_92,92,QuestSelect
e,achievement_2_93,202509010,SpecificQuestClear,quest_main_sum_hard_14,1,__NULL__,,0,,achievement_2_93,93,QuestSelect
e,achievement_2_94,202509010,SpecificQuestClear,quest_main_sum_veryhard_14,1,__NULL__,,0,,achievement_2_94,94,QuestSelect
e,achievement_2_95,202509010,SpecificQuestClear,quest_main_kai_normal_15,1,__NULL__,,0,,achievement_2_95,95,QuestSelect
e,achievement_2_96,202509010,SpecificQuestClear,quest_main_kai_hard_15,1,__NULL__,,0,,achievement_2_96,96,QuestSelect
e,achievement_2_97,202509010,SpecificQuestClear,quest_main_kai_veryhard_15,1,__NULL__,,0,,achievement_2_97,97,QuestSelect
e,achievement_2_98,202509010,SpecificQuestClear,quest_main_glo4_normal_16,1,__NULL__,,0,,achievement_2_98,98,QuestSelect
e,achievement_2_99,202509010,SpecificQuestClear,quest_main_glo4_hard_16,1,__NULL__,,0,,achievement_2_99,99,QuestSelect
e,achievement_2_100,202509010,SpecificQuestClear,quest_main_glo4_veryhard_16,1,__NULL__,,0,,achievement_2_100,100,QuestSelect
e,achievement_2_101,202512020,SpecificQuestClear,quest_main_osh_normal_17,1,__NULL__,,0,,achievement_2_101,101,QuestSelect
e,achievement_2_102,202512020,SpecificQuestClear,quest_main_osh_hard_17,1,__NULL__,,0,,achievement_2_102,102,QuestSelect
e,achievement_2_103,202512020,SpecificQuestClear,quest_main_osh_veryhard_17,1,__NULL__,,0,,achievement_2_103,103,QuestSelect
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionAchievementDependency.csv -->
## ./projects/glow-masterdata/MstMissionAchievementDependency.csv

```csv
ENABLE,id,release_key,group_id,mst_mission_achievement_id,unlock_order
e,1,202509010,Achievement_LoginCount,achievement_2_4,1
e,2,202509010,Achievement_LoginCount,achievement_2_5,2
e,3,202509010,Achievement_LoginCount,achievement_2_6,3
e,4,202509010,Achievement_LoginCount,achievement_2_7,4
e,5,202509010,Achievement_LoginCount,achievement_2_8,5
e,6,202509010,Achievement_LoginCount,achievement_2_9,6
e,7,202509010,Achievement_LoginCount,achievement_2_10,7
e,8,202509010,Achievement_LoginCount,achievement_2_11,8
e,9,202509010,Achievement_LoginCount,achievement_2_12,9
e,10,202509010,Achievement_LoginCount,achievement_2_13,10
e,11,202509010,Achievement_LoginCount,achievement_2_14,11
e,12,202509010,Achievement_LoginCount,achievement_2_15,12
e,13,202509010,Achievement_LoginCount,achievement_2_16,13
e,14,202509010,Achievement_LoginCount,achievement_2_17,14
e,15,202509010,Achievement_LoginCount,achievement_2_18,15
e,16,202509010,Achievement_LoginCount,achievement_2_19,16
e,17,202509010,Achievement_LoginCount,achievement_2_20,17
e,18,202509010,Achievement_LoginCount,achievement_2_21,18
e,19,202509010,Achievement_LoginCount,achievement_2_22,19
e,20,202509010,Achievement_LoginCount,achievement_2_23,20
e,21,202509010,Achievement_LoginCount,achievement_2_24,21
e,22,202509010,Achievement_LoginCount,achievement_2_25,22
e,23,202509010,Achievement_IdleIncentiveCount,achievement_2_26,1
e,24,202509010,Achievement_IdleIncentiveCount,achievement_2_27,2
e,25,202509010,Achievement_IdleIncentiveCount,achievement_2_28,3
e,26,202509010,Achievement_DefeatEnemyCount,achievement_2_29,1
e,27,202509010,Achievement_DefeatEnemyCount,achievement_2_30,2
e,28,202509010,Achievement_DefeatEnemyCount,achievement_2_31,3
e,29,202509010,Achievement_DefeatEnemyCount,achievement_2_32,4
e,30,202509010,Achievement_DefeatEnemyCount,achievement_2_33,5
e,31,202509010,Achievement_DefeatEnemyCount,achievement_2_34,6
e,32,202509010,Achievement_DefeatEnemyCount,achievement_2_35,7
e,33,202509010,Achievement_DefeatBossEnemyCount,achievement_2_36,1
e,34,202509010,Achievement_DefeatBossEnemyCount,achievement_2_37,2
e,35,202509010,Achievement_DefeatBossEnemyCount,achievement_2_38,3
e,36,202509010,Achievement_UnitLevelUpCount,achievement_2_39,1
e,37,202509010,Achievement_UnitLevelUpCount,achievement_2_40,2
e,38,202509010,Achievement_UnitLevelUpCount,achievement_2_41,3
e,39,202509010,Achievement_UnitLevelUpCount,achievement_2_42,4
e,40,202509010,Achievement_UnitLevelUpCount,achievement_2_43,5
e,41,202509010,Achievement_CoinCollect,achievement_2_44,1
e,42,202509010,Achievement_CoinCollect,achievement_2_45,2
e,43,202509010,Achievement_CoinCollect,achievement_2_46,3
e,44,202509010,Achievement_CoinCollect,achievement_2_47,4
e,45,202509010,Achievement_OutpostEnhanceCount,achievement_2_48,1
e,46,202509010,Achievement_OutpostEnhanceCount,achievement_2_49,2
e,47,202509010,Achievement_OutpostEnhanceCount,achievement_2_50,3
e,48,202509010,Achievement_OutpostEnhanceCount,achievement_2_51,4
e,49,202509010,Achievement_OutpostEnhanceCount,achievement_2_52,5
e,50,202509010,achievement_2_53,achievement_2_53,1
e,51,202509010,achievement_2_53,achievement_2_54,2
e,52,202509010,achievement_2_53,achievement_2_55,3
e,53,202509010,achievement_2_56,achievement_2_56,1
e,54,202509010,achievement_2_56,achievement_2_57,2
e,55,202509010,achievement_2_56,achievement_2_58,3
e,56,202509010,achievement_2_59,achievement_2_59,1
e,57,202509010,achievement_2_59,achievement_2_60,2
e,58,202509010,achievement_2_59,achievement_2_61,3
e,59,202509010,achievement_2_62,achievement_2_62,1
e,60,202509010,achievement_2_62,achievement_2_63,2
e,61,202509010,achievement_2_62,achievement_2_64,3
e,62,202509010,achievement_2_65,achievement_2_65,1
e,63,202509010,achievement_2_65,achievement_2_66,2
e,64,202509010,achievement_2_65,achievement_2_67,3
e,65,202509010,achievement_2_68,achievement_2_68,1
e,66,202509010,achievement_2_68,achievement_2_69,2
e,67,202509010,achievement_2_68,achievement_2_70,3
e,68,202509010,achievement_2_71,achievement_2_71,1
e,69,202509010,achievement_2_71,achievement_2_72,2
e,70,202509010,achievement_2_71,achievement_2_73,3
e,71,202509010,achievement_2_74,achievement_2_74,1
e,72,202509010,achievement_2_74,achievement_2_75,2
e,73,202509010,achievement_2_74,achievement_2_76,3
e,74,202509010,achievement_2_77,achievement_2_77,1
e,75,202509010,achievement_2_77,achievement_2_78,2
e,76,202509010,achievement_2_77,achievement_2_79,3
e,77,202509010,achievement_2_80,achievement_2_80,1
e,78,202509010,achievement_2_80,achievement_2_81,2
e,79,202509010,achievement_2_80,achievement_2_82,3
e,80,202509010,achievement_2_83,achievement_2_83,1
e,81,202509010,achievement_2_83,achievement_2_84,2
e,82,202509010,achievement_2_83,achievement_2_85,3
e,83,202509010,achievement_2_86,achievement_2_86,1
e,84,202509010,achievement_2_86,achievement_2_87,2
e,85,202509010,achievement_2_86,achievement_2_88,3
e,86,202509010,achievement_2_89,achievement_2_89,1
e,87,202509010,achievement_2_89,achievement_2_90,2
e,88,202509010,achievement_2_89,achievement_2_91,3
e,89,202509010,achievement_2_92,achievement_2_92,1
e,90,202509010,achievement_2_92,achievement_2_93,2
e,91,202509010,achievement_2_92,achievement_2_94,3
e,92,202509010,achievement_2_95,achievement_2_95,1
e,93,202509010,achievement_2_95,achievement_2_96,2
e,94,202509010,achievement_2_95,achievement_2_97,3
e,95,202509010,achievement_2_98,achievement_2_98,1
e,96,202509010,achievement_2_98,achievement_2_99,2
e,97,202509010,achievement_2_98,achievement_2_100,3
e,98,202509010,achievement_2_98,achievement_2_98,1
e,99,202509010,achievement_2_98,achievement_2_99,2
e,100,202509010,achievement_2_98,achievement_2_100,3
e,101,202512020,achievement_2_101,achievement_2_101,1
e,102,202512020,achievement_2_101,achievement_2_102,2
e,103,202512020,achievement_2_101,achievement_2_103,3
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionAchievementI18n.csv -->
## ./projects/glow-masterdata/MstMissionAchievementI18n.csv

```csv
ENABLE,release_key,id,mst_mission_achievement_id,language,description
e,202509010,achievement_2_1_ja,achievement_2_1,ja,「ジャンブルラッシュ」の公式Xをフォローしよう
e,202509010,achievement_2_2_ja,achievement_2_2,ja,"アカウント連携をしよう\n※メニュー >アカウント連携から可能です"
e,202509010,achievement_2_3_ja,achievement_2_3,ja,「ジャンブルラッシュ情報局」を確認しよう
e,202509010,achievement_2_4_ja,achievement_2_4,ja,累計10日ログインしよう
e,202509010,achievement_2_5_ja,achievement_2_5,ja,累計20日ログインしよう
e,202509010,achievement_2_6_ja,achievement_2_6,ja,累計30日ログインしよう
e,202509010,achievement_2_7_ja,achievement_2_7,ja,累計40日ログインしよう
e,202509010,achievement_2_8_ja,achievement_2_8,ja,累計50日ログインしよう
e,202509010,achievement_2_9_ja,achievement_2_9,ja,累計60日ログインしよう
e,202509010,achievement_2_10_ja,achievement_2_10,ja,累計70日ログインしよう
e,202509010,achievement_2_11_ja,achievement_2_11,ja,累計80日ログインしよう
e,202509010,achievement_2_12_ja,achievement_2_12,ja,累計90日ログインしよう
e,202509010,achievement_2_13_ja,achievement_2_13,ja,累計100日ログインしよう
e,202509010,achievement_2_14_ja,achievement_2_14,ja,累計110日ログインしよう
e,202509010,achievement_2_15_ja,achievement_2_15,ja,累計120日ログインしよう
e,202509010,achievement_2_16_ja,achievement_2_16,ja,累計130日ログインしよう
e,202509010,achievement_2_17_ja,achievement_2_17,ja,累計140日ログインしよう
e,202509010,achievement_2_18_ja,achievement_2_18,ja,累計150日ログインしよう
e,202509010,achievement_2_19_ja,achievement_2_19,ja,累計160日ログインしよう
e,202509010,achievement_2_20_ja,achievement_2_20,ja,累計170日ログインしよう
e,202509010,achievement_2_21_ja,achievement_2_21,ja,累計180日ログインしよう
e,202509010,achievement_2_22_ja,achievement_2_22,ja,累計190日ログインしよう
e,202509010,achievement_2_23_ja,achievement_2_23,ja,累計200日ログインしよう
e,202509010,achievement_2_24_ja,achievement_2_24,ja,累計250日ログインしよう
e,202509010,achievement_2_25_ja,achievement_2_25,ja,累計300日ログインしよう
e,202509010,achievement_2_26_ja,achievement_2_26,ja,探索で探索報酬を累計50回受け取ろう
e,202509010,achievement_2_27_ja,achievement_2_27,ja,探索で探索報酬を累計100回受け取ろう
e,202509010,achievement_2_28_ja,achievement_2_28,ja,探索で探索報酬を累計500回受け取ろう
e,202509010,achievement_2_29_ja,achievement_2_29,ja,敵を累計10体撃破しよう
e,202509010,achievement_2_30_ja,achievement_2_30,ja,敵を累計50体撃破しよう
e,202509010,achievement_2_31_ja,achievement_2_31,ja,敵を累計100体撃破しよう
e,202509010,achievement_2_32_ja,achievement_2_32,ja,敵を累計200体撃破しよう
e,202509010,achievement_2_33_ja,achievement_2_33,ja,敵を累計300体撃破しよう
e,202509010,achievement_2_34_ja,achievement_2_34,ja,敵を累計500体撃破しよう
e,202509010,achievement_2_35_ja,achievement_2_35,ja,"敵を累計1,000体撃破しよう"
e,202509010,achievement_2_36_ja,achievement_2_36,ja,強敵を累計10体撃破しよう
e,202509010,achievement_2_37_ja,achievement_2_37,ja,強敵を累計50体撃破しよう
e,202509010,achievement_2_38_ja,achievement_2_38,ja,強敵を累計100体撃破しよう
e,202509010,achievement_2_39_ja,achievement_2_39,ja,キャラのLv.を累計50回強化しよう
e,202509010,achievement_2_40_ja,achievement_2_40,ja,キャラのLv.を累計100回強化しよう
e,202509010,achievement_2_41_ja,achievement_2_41,ja,キャラのLv.を累計300回強化しよう
e,202509010,achievement_2_42_ja,achievement_2_42,ja,キャラのLv.を累計500回強化しよう
e,202509010,achievement_2_43_ja,achievement_2_43,ja,キャラのLv.を累計1000回強化しよう
e,202509010,achievement_2_44_ja,achievement_2_44,ja,"コインを累計200,000枚集めよう"
e,202509010,achievement_2_45_ja,achievement_2_45,ja,"コインを累計300,000枚集めよう"
e,202509010,achievement_2_46_ja,achievement_2_46,ja,"コインを累計500,000枚集めよう"
e,202509010,achievement_2_47_ja,achievement_2_47,ja,"コインを累計1,000,000枚集めよう"
e,202509010,achievement_2_48_ja,achievement_2_48,ja,ゲートを累計10回強化しよう
e,202509010,achievement_2_49_ja,achievement_2_49,ja,ゲートを累計20回強化しよう
e,202509010,achievement_2_50_ja,achievement_2_50,ja,ゲートを累計30回強化しよう
e,202509010,achievement_2_51_ja,achievement_2_51,ja,ゲートを累計40回強化しよう
e,202509010,achievement_2_52_ja,achievement_2_52,ja,ゲートを累計50回強化しよう
e,202509010,achievement_2_53_ja,achievement_2_53,ja,メインクエスト「SPY×FAMILY」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_54_ja,achievement_2_54,ja,メインクエスト「SPY×FAMILY」の難易度ハードをクリアしよう
e,202509010,achievement_2_55_ja,achievement_2_55,ja,メインクエスト「SPY×FAMILY」の難易度エクストラをクリアしよう
e,202509010,achievement_2_56_ja,achievement_2_56,ja,"メインクエスト「姫様""拷問""の時間です」の難易度ノーマルをクリアしよう"
e,202509010,achievement_2_57_ja,achievement_2_57,ja,"メインクエスト「姫様""拷問""の時間です」の難易度ハードをクリアしよう"
e,202509010,achievement_2_58_ja,achievement_2_58,ja,"メインクエスト「姫様""拷問""の時間です」の難易度エクストラをクリアしよう"
e,202509010,achievement_2_59_ja,achievement_2_59,ja,メインクエスト「ラーメン赤猫」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_60_ja,achievement_2_60,ja,メインクエスト「ラーメン赤猫」の難易度ハードをクリアしよう
e,202509010,achievement_2_61_ja,achievement_2_61,ja,メインクエスト「ラーメン赤猫」の難易度エクストラをクリアしよう
e,202509010,achievement_2_62_ja,achievement_2_62,ja,"メインクエスト「リミックスクエスト vol.1」の難易度ノーマルをクリアしよう"
e,202509010,achievement_2_63_ja,achievement_2_63,ja,"メインクエスト「リミックスクエスト vol.1」の難易度ハードをクリアしよう"
e,202509010,achievement_2_64_ja,achievement_2_64,ja,"メインクエスト「リミックスクエスト vol.1」の難易度エクストラをクリアしよう"
e,202509010,achievement_2_65_ja,achievement_2_65,ja,メインクエスト「ダンダダン」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_66_ja,achievement_2_66,ja,メインクエスト「ダンダダン」の難易度ハードをクリアしよう
e,202509010,achievement_2_67_ja,achievement_2_67,ja,メインクエスト「ダンダダン」の難易度エクストラをクリアしよう
e,202509010,achievement_2_68_ja,achievement_2_68,ja,メインクエスト「地獄楽」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_69_ja,achievement_2_69,ja,メインクエスト「地獄楽」の難易度ハードをクリアしよう
e,202509010,achievement_2_70_ja,achievement_2_70,ja,メインクエスト「地獄楽」の難易度エクストラをクリアしよう
e,202509010,achievement_2_71_ja,achievement_2_71,ja,メインクエスト「タコピーの原罪」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_72_ja,achievement_2_72,ja,メインクエスト「タコピーの原罪」の難易度ハードをクリアしよう
e,202509010,achievement_2_73_ja,achievement_2_73,ja,メインクエスト「タコピーの原罪」の難易度エクストラをクリアしよう
e,202509010,achievement_2_74_ja,achievement_2_74,ja,"メインクエスト「リミックスクエスト vol.2」の難易度ノーマルをクリアしよう"
e,202509010,achievement_2_75_ja,achievement_2_75,ja,"メインクエスト「リミックスクエスト vol.2」の難易度ハードをクリアしよう"
e,202509010,achievement_2_76_ja,achievement_2_76,ja,"メインクエスト「リミックスクエスト vol.2」の難易度エクストラをクリアしよう"
e,202509010,achievement_2_77_ja,achievement_2_77,ja,メインクエスト「チェンソーマン」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_78_ja,achievement_2_78,ja,メインクエスト「チェンソーマン」の難易度ハードをクリアしよう
e,202509010,achievement_2_79_ja,achievement_2_79,ja,メインクエスト「チェンソーマン」の難易度エクストラをクリアしよう
e,202509010,achievement_2_80_ja,achievement_2_80,ja,メインクエスト「魔都精兵のスレイブ」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_81_ja,achievement_2_81,ja,メインクエスト「魔都精兵のスレイブ」の難易度ハードをクリアしよう
e,202509010,achievement_2_82_ja,achievement_2_82,ja,メインクエスト「魔都精兵のスレイブ」の難易度エクストラをクリアしよう
e,202509010,achievement_2_83_ja,achievement_2_83,ja,メインクエスト「トマトイプーのリコピン」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_84_ja,achievement_2_84,ja,メインクエスト「トマトイプーのリコピン」の難易度ハードをクリアしよう
e,202509010,achievement_2_85_ja,achievement_2_85,ja,メインクエスト「トマトイプーのリコピン」の難易度エクストラをクリアしよう
e,202509010,achievement_2_86_ja,achievement_2_86,ja,"メインクエスト「リミックスクエスト vol.3」の難易度ノーマルをクリアしよう"
e,202509010,achievement_2_87_ja,achievement_2_87,ja,"メインクエスト「リミックスクエスト vol.3」の難易度ハードをクリアしよう"
e,202509010,achievement_2_88_ja,achievement_2_88,ja,"メインクエスト「リミックスクエスト vol.3」の難易度エクストラをクリアしよう"
e,202509010,achievement_2_89_ja,achievement_2_89,ja,メインクエスト「株式会社マジルミエ」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_90_ja,achievement_2_90,ja,メインクエスト「株式会社マジルミエ」の難易度ハードをクリアしよう
e,202509010,achievement_2_91_ja,achievement_2_91,ja,メインクエスト「株式会社マジルミエ」の難易度エクストラをクリアしよう
e,202509010,achievement_2_92_ja,achievement_2_92,ja,メインクエスト「サマータイムレンダ」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_93_ja,achievement_2_93,ja,メインクエスト「サマータイムレンダ」の難易度ハードをクリアしよう
e,202509010,achievement_2_94_ja,achievement_2_94,ja,メインクエスト「サマータイムレンダ」の難易度エクストラをクリアしよう
e,202509010,achievement_2_95_ja,achievement_2_95,ja,メインクエスト「怪獣８号」の難易度ノーマルをクリアしよう
e,202509010,achievement_2_96_ja,achievement_2_96,ja,メインクエスト「怪獣８号」の難易度ハードをクリアしよう
e,202509010,achievement_2_97_ja,achievement_2_97,ja,メインクエスト「怪獣８号」の難易度エクストラをクリアしよう
e,202509010,achievement_2_98_ja,achievement_2_98,ja,"メインクエスト「リミックスクエスト vol.4」の難易度ノーマルをクリアしよう"
e,202509010,achievement_2_99_ja,achievement_2_99,ja,"メインクエスト「リミックスクエスト vol.4」の難易度ハードをクリアしよう"
e,202509010,achievement_2_100_ja,achievement_2_100,ja,"メインクエスト「リミックスクエスト vol.4」の難易度エクストラをクリアしよう"
e,202512020,achievement_2_101_ja,achievement_2_101,ja,メインクエスト「【推しの子】」の難易度ノーマルをクリアしよう
e,202512020,achievement_2_102_ja,achievement_2_102,ja,メインクエスト「【推しの子】」の難易度ハードをクリアしよう
e,202512020,achievement_2_103_ja,achievement_2_103,ja,メインクエスト「【推しの子】」の難易度エクストラをクリアしよう
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionBeginner.csv -->
## ./projects/glow-masterdata/MstMissionBeginner.csv

```csv
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,unlock_day,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene
e,beginner2_1_1,202509010,LoginCount,,1,1,Beginner1,20,mission_reward_beginner_2,,Home
e,beginner2_1_2,202509010,IdleIncentiveCount,,1,1,Beginner1,30,mission_reward_beginner_2,,IdleIncentive
e,beginner2_1_3,202509010,UnitLevelUpCount,,5,1,Beginner1,40,mission_reward_beginner_2,,UnitList
e,beginner2_1_4,202509010,SpecificQuestClear,quest_main_spy_normal_1,1,1,Beginner1,50,mission_reward_beginner_2,,QuestSelect
e,beginner2_2_1,202509010,LoginCount,,2,2,Beginner2,20,mission_reward_beginner_2,,Home
e,beginner2_2_2,202509010,OutpostEnhanceCount,,1,2,Beginner2,30,mission_reward_beginner_2,,OutpostEnhance
e,beginner2_2_3,202509010,UnitLevelUpCount,,10,2,Beginner2,40,mission_reward_beginner_2,,UnitList
e,beginner2_2_4,202509010,ArtworkCompletedCount,,1,2,Beginner2,50,mission_reward_beginner_2,,QuestSelect
e,beginner2_3_1,202509010,LoginCount,,3,3,Beginner3,20,mission_reward_beginner_2,,Home
e,beginner2_3_2,202509010,SpecificQuestClear,quest_main_dan_normal_5,1,3,Beginner3,30,mission_reward_beginner_2,,QuestSelect
e,beginner2_3_3,202509010,UnitLevelUpCount,,15,3,Beginner3,40,mission_reward_beginner_2,,UnitList
e,beginner2_3_4,202509010,ArtworkCompletedCount,,3,3,Beginner3,50,mission_reward_beginner_2,,QuestSelect
e,beginner2_4_1,202509010,LoginCount,,4,4,Beginner4,20,mission_reward_beginner_2,,Home
e,beginner2_4_2,202509010,OutpostEnhanceCount,,5,4,Beginner4,30,mission_reward_beginner_2,,OutpostEnhance
e,beginner2_4_3,202509010,UnitLevelUpCount,,20,4,Beginner4,40,mission_reward_beginner_2,,UnitList
e,beginner2_4_4,202509010,ArtworkCompletedCount,,5,4,Beginner4,50,mission_reward_beginner_2,,QuestSelect
e,beginner2_5_1,202509010,LoginCount,,5,5,Beginner5,20,mission_reward_beginner_2,,Home
e,beginner2_5_2,202509010,SpecificQuestClear,quest_main_chi_normal_9,1,5,Beginner5,30,mission_reward_beginner_2,,QuestSelect
e,beginner2_5_3,202509010,UnitLevelUpCount,,25,5,Beginner5,40,mission_reward_beginner_2,,UnitList
e,beginner2_5_4,202509010,ArtworkCompletedCount,,7,5,Beginner5,50,mission_reward_beginner_2,,QuestSelect
e,beginner2_6_1,202509010,LoginCount,,6,6,Beginner6,30,mission_reward_beginner_2,,Home
e,beginner2_6_2,202509010,CoinCollect,,200000,6,Beginner6,30,mission_reward_beginner_2,,StageSelect
e,beginner2_6_3,202509010,UnitLevelUpCount,,30,6,Beginner6,40,mission_reward_beginner_2,,UnitList
e,beginner2_6_4,202509010,UnitAcquiredCount,,20,6,Beginner6,50,mission_reward_beginner_2,,Gacha
e,beginner2_7_1,202509010,LoginCount,,7,7,Beginner7,30,mission_reward_beginner_2,,Home
e,beginner2_7_2,202509010,SpecificQuestClear,quest_main_kai_normal_15,1,7,Beginner7,30,mission_reward_beginner_2,,QuestSelect
e,beginner2_7_3,202509010,UnitLevelUpCount,,40,7,Beginner7,40,mission_reward_beginner_2,,UnitList
e,beginner2_7_4,202509010,ArtworkCompletedCount,,10,7,Beginner7,50,mission_reward_beginner_2,,QuestSelect
e,beginner_bonus_point_2_1,202509010,MissionBonusPoint,,140,1,,,mission_reward_beginner_bonus_2_1,,
e,beginner_bonus_point_2_2,202509010,MissionBonusPoint,,280,1,,,mission_reward_beginner_bonus_2_2,,
e,beginner_bonus_point_2_3,202509010,MissionBonusPoint,,420,1,,,mission_reward_beginner_bonus_2_3,,
e,beginner_bonus_point_2_4,202509010,MissionBonusPoint,,560,1,,,mission_reward_beginner_bonus_2_4,,
e,beginner_bonus_point_2_5,202509010,MissionBonusPoint,,700,1,,,mission_reward_beginner_bonus_2_5,,
e,beginner_bonus_point_2_6,202509010,MissionBonusPoint,,850,1,,,mission_reward_beginner_bonus_2_6,,
e,beginner_bonus_point_2_7,202509010,MissionBonusPoint,,900,1,,,mission_reward_beginner_bonus_2_7,,
e,beginner_bonus_point_2_8,202509010,MissionBonusPoint,,1000,1,,,mission_reward_beginner_bonus_2_8,,
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionBeginnerI18n.csv -->
## ./projects/glow-masterdata/MstMissionBeginnerI18n.csv

```csv
ENABLE,release_key,id,mst_mission_beginner_id,language,title,description
e,202509010,beginner2_1_1_ja,beginner2_1_1,ja,,1日ログインしよう
e,202509010,beginner2_1_2_ja,beginner2_1_2,ja,,探索で探索報酬を1回受け取ろう
e,202509010,beginner2_1_3_ja,beginner2_1_3,ja,,キャラのLv.を累計5回強化しよう
e,202509010,beginner2_1_4_ja,beginner2_1_4,ja,,メインクエスト「SPY×FAMILY」の難易度ノーマルをクリアしよう
e,202509010,beginner2_2_1_ja,beginner2_2_1,ja,,2日ログインしよう
e,202509010,beginner2_2_2_ja,beginner2_2_2,ja,,ゲートを累計1回強化しよう
e,202509010,beginner2_2_3_ja,beginner2_2_3,ja,,キャラのLv.を累計10回強化しよう
e,202509010,beginner2_2_4_ja,beginner2_2_4,ja,,原画を累計1枚完成させよう
e,202509010,beginner2_3_1_ja,beginner2_3_1,ja,,3日ログインしよう
e,202509010,beginner2_3_2_ja,beginner2_3_2,ja,,メインクエスト「ダンダダン」の難易度ノーマルをクリアしよう
e,202509010,beginner2_3_3_ja,beginner2_3_3,ja,,キャラのLv.を累計15回強化しよう
e,202509010,beginner2_3_4_ja,beginner2_3_4,ja,,原画を累計3枚完成させよう
e,202509010,beginner2_4_1_ja,beginner2_4_1,ja,,4日ログインしよう
e,202509010,beginner2_4_2_ja,beginner2_4_2,ja,,ゲートを累計5回強化しよう
e,202509010,beginner2_4_3_ja,beginner2_4_3,ja,,キャラのLv.を累計20回強化しよう
e,202509010,beginner2_4_4_ja,beginner2_4_4,ja,,原画を累計5枚完成させよう
e,202509010,beginner2_5_1_ja,beginner2_5_1,ja,,5日ログインしよう
e,202509010,beginner2_5_2_ja,beginner2_5_2,ja,,メインクエスト「チェンソーマン」の難易度ノーマルをクリアしよう
e,202509010,beginner2_5_3_ja,beginner2_5_3,ja,,キャラのLv.を累計25回強化しよう
e,202509010,beginner2_5_4_ja,beginner2_5_4,ja,,原画を累計7枚完成させよう
e,202509010,beginner2_6_1_ja,beginner2_6_1,ja,,6日ログインしよう
e,202509010,beginner2_6_2_ja,beginner2_6_2,ja,,"コインを200,000枚集めよう"
e,202509010,beginner2_6_3_ja,beginner2_6_3,ja,,キャラのLv.を累計30回強化しよう
e,202509010,beginner2_6_4_ja,beginner2_6_4,ja,,キャラを累計20体仲間にしよう
e,202509010,beginner2_7_1_ja,beginner2_7_1,ja,,7日ログインしよう
e,202509010,beginner2_7_2_ja,beginner2_7_2,ja,,メインクエスト「怪獣８号」の難易度ノーマルをクリアしよう
e,202509010,beginner2_7_3_ja,beginner2_7_3,ja,,キャラのLv.を累計40回強化しよう
e,202509010,beginner2_7_4_ja,beginner2_7_4,ja,,原画を累計10枚完成させよう
e,202509010,beginner_bonus_point_2_1_ja,beginner_bonus_point_2_1,ja,,累計ポイントを140貯めよう
e,202509010,beginner_bonus_point_2_2_ja,beginner_bonus_point_2_2,ja,,累計ポイントを280貯めよう
e,202509010,beginner_bonus_point_2_3_ja,beginner_bonus_point_2_3,ja,,累計ポイントを420貯めよう
e,202509010,beginner_bonus_point_2_4_ja,beginner_bonus_point_2_4,ja,,累計ポイントを560貯めよう
e,202509010,beginner_bonus_point_2_5_ja,beginner_bonus_point_2_5,ja,,累計ポイントを700貯めよう
e,202509010,beginner_bonus_point_2_6_ja,beginner_bonus_point_2_6,ja,,累計ポイントを850貯めよう
e,202509010,beginner_bonus_point_2_7_ja,beginner_bonus_point_2_7,ja,,累計ポイントを900貯めよう
e,202509010,beginner_bonus_point_2_8_ja,beginner_bonus_point_2_8,ja,,累計ポイントを1000貯めよう
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionBeginnerPromptPhraseI18n.csv -->
## ./projects/glow-masterdata/MstMissionBeginnerPromptPhraseI18n.csv

```csv
ENABLE,id,release_key,language,prompt_phrase_text,start_at,end_at
e,beginner_mission_1,202509010,ja,1500,"2024-04-30 15:00:00","2037-12-31 23:59:59"
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionDaily.csv -->
## ./projects/glow-masterdata/MstMissionDaily.csv

```csv
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene
e,daily_2_1,202509010,LoginCount,,1,Daily1,20,,1,Home
e,daily_2_2,202509010,CoinCollect,,2000,Daily1,20,,2,StageSelect
e,daily_2_3,202509010,IdleIncentiveCount,,1,Daily1,20,,3,IdleIncentive
e,daily_2_4,202509010,IdleIncentiveQuickCount,,1,Daily1,20,,4,IdleIncentive
e,daily_2_5,202509010,PvpChallengeCount,,1,Daily1,20,,5,Pvp
e,daily_2_6,202509010,SpecificGachaDrawCount,Special_001,1,Daily1,20,,6,Gacha
e,daily_bonus_point_2_1,202509010,MissionBonusPoint,,20,,0,daily_reward_2_1,10,
e,daily_bonus_point_2_2,202509010,MissionBonusPoint,,40,,0,daily_reward_2_2,11,
e,daily_bonus_point_2_3,202509010,MissionBonusPoint,,60,,0,daily_reward_2_3,12,
e,daily_bonus_point_2_4,202509010,MissionBonusPoint,,80,,0,daily_reward_2_4,13,
e,daily_bonus_point_2_5,202509010,MissionBonusPoint,,100,,0,daily_reward_2_5,14,
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionDailyBonus.csv -->
## ./projects/glow-masterdata/MstMissionDailyBonus.csv

```csv
ENABLE,id,release_key,mission_daily_bonus_type,login_day_count,mst_mission_reward_group_id,sort_order
e,daily_bonus_1,202509010,DailyBonus,1,daily_bonus_reward_1_1,1
e,daily_bonus_2,202509010,DailyBonus,2,daily_bonus_reward_1_2,2
e,daily_bonus_3,202509010,DailyBonus,3,daily_bonus_reward_1_3,3
e,daily_bonus_4,202509010,DailyBonus,4,daily_bonus_reward_1_4,4
e,daily_bonus_5,202509010,DailyBonus,5,daily_bonus_reward_1_5,5
e,daily_bonus_6,202509010,DailyBonus,6,daily_bonus_reward_1_6,6
e,daily_bonus_7,202509010,DailyBonus,7,daily_bonus_reward_1_7,7
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionDailyI18n.csv -->
## ./projects/glow-masterdata/MstMissionDailyI18n.csv

```csv
ENABLE,release_key,id,mst_mission_daily_id,language,description
e,202509010,daily_2_1_ja,daily_2_1,ja,ログインしよう
e,202509010,daily_2_2_ja,daily_2_2,ja,"コインを累計2,000枚集めよう"
e,202509010,daily_2_3_ja,daily_2_3,ja,探索で探索報酬を累計1回受け取ろう
e,202509010,daily_2_4_ja,daily_2_4,ja,探索でクイック探索を累計1回行おう
e,202509010,daily_2_5_ja,daily_2_5,ja,ランクマッチに累計1回挑戦しよう
e,202509010,daily_2_6_ja,daily_2_6,ja,スペシャルガシャを累計1回引こう
e,202509010,daily_bonus_point_2_1_ja,daily_bonus_point_2_1,ja,累計ポイントを20貯めよう
e,202509010,daily_bonus_point_2_2_ja,daily_bonus_point_2_2,ja,累計ポイントを40貯めよう
e,202509010,daily_bonus_point_2_3_ja,daily_bonus_point_2_3,ja,累計ポイントを60貯めよう
e,202509010,daily_bonus_point_2_4_ja,daily_bonus_point_2_4,ja,累計ポイントを80貯めよう
e,202509010,daily_bonus_point_2_5_ja,daily_bonus_point_2_5,ja,累計ポイントを100貯めよう
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEvent.csv -->
## ./projects/glow-masterdata/MstMissionEvent.csv

```csv
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
e,event_kai_00001_1,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00601,2,__NULL__,,0,,kai_00001_event_reward_01,1,UnitList
e,event_kai_00001_2,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00601,3,__NULL__,,0,,kai_00001_event_reward_02,2,UnitList
e,event_kai_00001_3,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00601,4,__NULL__,,0,,kai_00001_event_reward_03,3,UnitList
e,event_kai_00001_4,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00601,5,__NULL__,,0,,kai_00001_event_reward_04,4,UnitList
e,event_kai_00001_5,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00601,20,__NULL__,,0,,kai_00001_event_reward_05,5,UnitList
e,event_kai_00001_6,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00601,30,__NULL__,,0,,kai_00001_event_reward_06,6,UnitList
e,event_kai_00001_7,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00601,40,__NULL__,,0,,kai_00001_event_reward_07,7,UnitList
e,event_kai_00001_8,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00501,2,__NULL__,,0,,kai_00001_event_reward_08,8,UnitList
e,event_kai_00001_9,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00501,3,__NULL__,,0,,kai_00001_event_reward_09,9,UnitList
e,event_kai_00001_10,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00501,4,__NULL__,,0,,kai_00001_event_reward_10,10,UnitList
e,event_kai_00001_11,202509010,event_kai_00001,SpecificUnitGradeUpCount,chara_kai_00501,5,__NULL__,,0,,kai_00001_event_reward_11,11,UnitList
e,event_kai_00001_12,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00501,20,__NULL__,,0,,kai_00001_event_reward_12,12,UnitList
e,event_kai_00001_13,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00501,30,__NULL__,,0,,kai_00001_event_reward_13,13,UnitList
e,event_kai_00001_14,202509010,event_kai_00001,SpecificUnitLevel,chara_kai_00501,40,__NULL__,,0,,kai_00001_event_reward_14,14,UnitList
e,event_kai_00001_15,202509010,event_kai_00001,SpecificQuestClear,quest_event_kai1_charaget01,1,__NULL__,,0,,kai_00001_event_reward_15,15,Event
e,event_kai_00001_16,202509010,event_kai_00001,SpecificQuestClear,quest_event_kai1_charaget02,1,__NULL__,,0,,kai_00001_event_reward_16,16,Event
e,event_kai_00001_17,202509010,event_kai_00001,SpecificQuestClear,quest_event_kai1_challenge01,1,__NULL__,,0,,kai_00001_event_reward_17,17,Event
e,event_kai_00001_18,202509010,event_kai_00001,SpecificQuestClear,quest_event_kai1_savage,1,__NULL__,,0,,kai_00001_event_reward_18,18,Event
e,event_kai_00001_19,202509010,event_kai_00001,DefeatEnemyCount,,10,__NULL__,,0,,kai_00001_event_reward_19,19,Event
e,event_kai_00001_20,202509010,event_kai_00001,DefeatEnemyCount,,20,__NULL__,,0,,kai_00001_event_reward_20,20,Event
e,event_kai_00001_21,202509010,event_kai_00001,DefeatEnemyCount,,30,__NULL__,,0,,kai_00001_event_reward_21,21,Event
e,event_kai_00001_22,202509010,event_kai_00001,DefeatEnemyCount,,40,__NULL__,,0,,kai_00001_event_reward_22,22,Event
e,event_kai_00001_23,202509010,event_kai_00001,DefeatEnemyCount,,50,__NULL__,,0,,kai_00001_event_reward_23,23,Event
e,event_kai_00001_24,202509010,event_kai_00001,DefeatEnemyCount,,100,__NULL__,,0,,kai_00001_event_reward_24,24,Event
e,event_kai_00001_25,999999999,event_kai_00001,SpecificUnitStageChallengeCount,chara_kai_00601.,10,__NULL__,,0,,kai_00001_event_reward_25,25,Event
e,event_kai_00001_26,999999999,event_kai_00001,SpecificUnitStageClearCount,chara_kai_00601.,10,__NULL__,,0,,kai_00001_event_reward_26,26,Event
e,event_kai_00001_27,999999999,event_kai_00001,SpecificUnitStageChallengeCount,chara_kai_00601.event_kai1_charaget01_00001,10,__NULL__,,0,,kai_00001_event_reward_27,27,Event
e,event_kai_00001_28,999999999,event_kai_00001,SpecificUnitStageClearCount,chara_kai_00601.event_kai1_charaget01_00001,10,__NULL__,,0,,kai_00001_event_reward_28,28,Event
e,event_spy_00001_1,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00401,2,__NULL__,,0,,spy_00001_event_reward_01,1,UnitList
e,event_spy_00001_2,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00401,3,__NULL__,,0,,spy_00001_event_reward_02,2,UnitList
e,event_spy_00001_3,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00401,4,__NULL__,,0,,spy_00001_event_reward_03,3,UnitList
e,event_spy_00001_4,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00401,5,__NULL__,,0,,spy_00001_event_reward_04,4,UnitList
e,event_spy_00001_5,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00401,20,__NULL__,,0,,spy_00001_event_reward_05,5,UnitList
e,event_spy_00001_6,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00401,30,__NULL__,,0,,spy_00001_event_reward_06,6,UnitList
e,event_spy_00001_7,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00401,40,__NULL__,,0,,spy_00001_event_reward_07,7,UnitList
e,event_spy_00001_8,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00301,2,__NULL__,,0,,spy_00001_event_reward_08,8,UnitList
e,event_spy_00001_9,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00301,3,__NULL__,,0,,spy_00001_event_reward_09,9,UnitList
e,event_spy_00001_10,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00301,4,__NULL__,,0,,spy_00001_event_reward_10,10,UnitList
e,event_spy_00001_11,202510010,event_spy_00001,SpecificUnitGradeUpCount,chara_spy_00301,5,__NULL__,,0,,spy_00001_event_reward_11,11,UnitList
e,event_spy_00001_12,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00301,20,__NULL__,,0,,spy_00001_event_reward_12,12,UnitList
e,event_spy_00001_13,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00301,30,__NULL__,,0,,spy_00001_event_reward_13,13,UnitList
e,event_spy_00001_14,202510010,event_spy_00001,SpecificUnitLevel,chara_spy_00301,40,__NULL__,,0,,spy_00001_event_reward_14,14,UnitList
e,event_spy_00001_15,202510010,event_spy_00001,SpecificQuestClear,quest_event_spy1_charaget01,1,__NULL__,,0,,spy_00001_event_reward_15,15,Event
e,event_spy_00001_16,202510010,event_spy_00001,SpecificQuestClear,quest_event_spy1_charaget02,1,__NULL__,,0,,spy_00001_event_reward_16,16,Event
e,event_spy_00001_17,202510010,event_spy_00001,SpecificQuestClear,quest_event_spy1_challenge01,1,__NULL__,,0,,spy_00001_event_reward_17,17,Event
e,event_spy_00001_18,202510010,event_spy_00001,SpecificQuestClear,quest_event_spy1_savage,1,__NULL__,,0,,spy_00001_event_reward_18,18,Event
e,event_spy_00001_19,202510010,event_spy_00001,DefeatEnemyCount,,10,__NULL__,,0,,spy_00001_event_reward_19,19,Event
e,event_spy_00001_20,202510010,event_spy_00001,DefeatEnemyCount,,20,__NULL__,,0,,spy_00001_event_reward_20,20,Event
e,event_spy_00001_21,202510010,event_spy_00001,DefeatEnemyCount,,30,__NULL__,,0,,spy_00001_event_reward_21,21,Event
e,event_spy_00001_22,202510010,event_spy_00001,DefeatEnemyCount,,40,__NULL__,,0,,spy_00001_event_reward_22,22,Event
e,event_spy_00001_23,202510010,event_spy_00001,DefeatEnemyCount,,50,__NULL__,,0,,spy_00001_event_reward_23,23,Event
e,event_spy_00001_24,202510010,event_spy_00001,DefeatEnemyCount,,100,__NULL__,,0,,spy_00001_event_reward_24,24,Event
e,event_dan_00001_1,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00301,2,__NULL__,,0,,dan_00001_event_reward_01,1,UnitList
e,event_dan_00001_2,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00301,3,__NULL__,,0,,dan_00001_event_reward_02,2,UnitList
e,event_dan_00001_3,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00301,4,__NULL__,,0,,dan_00001_event_reward_03,3,UnitList
e,event_dan_00001_4,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00301,5,__NULL__,,0,,dan_00001_event_reward_04,4,UnitList
e,event_dan_00001_5,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00301,20,__NULL__,,0,,dan_00001_event_reward_05,5,UnitList
e,event_dan_00001_6,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00301,30,__NULL__,,0,,dan_00001_event_reward_06,6,UnitList
e,event_dan_00001_7,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00301,40,__NULL__,,0,,dan_00001_event_reward_07,7,UnitList
e,event_dan_00001_8,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00201,2,__NULL__,,0,,dan_00001_event_reward_08,8,UnitList
e,event_dan_00001_9,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00201,3,__NULL__,,0,,dan_00001_event_reward_09,9,UnitList
e,event_dan_00001_10,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00201,4,__NULL__,,0,,dan_00001_event_reward_10,10,UnitList
e,event_dan_00001_11,202510020,event_dan_00001,SpecificUnitGradeUpCount,chara_dan_00201,5,__NULL__,,0,,dan_00001_event_reward_11,11,UnitList
e,event_dan_00001_12,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00201,20,__NULL__,,0,,dan_00001_event_reward_12,12,UnitList
e,event_dan_00001_13,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00201,30,__NULL__,,0,,dan_00001_event_reward_13,13,UnitList
e,event_dan_00001_14,202510020,event_dan_00001,SpecificUnitLevel,chara_dan_00201,40,__NULL__,,0,,dan_00001_event_reward_14,14,UnitList
e,event_dan_00001_15,202510020,event_dan_00001,SpecificQuestClear,quest_event_dan1_charaget01,1,__NULL__,,0,,dan_00001_event_reward_15,15,Event
e,event_dan_00001_16,202510020,event_dan_00001,SpecificQuestClear,quest_event_dan1_charaget02,1,__NULL__,,0,,dan_00001_event_reward_16,16,Event
e,event_dan_00001_17,202510020,event_dan_00001,SpecificQuestClear,quest_event_dan1_challenge01,1,__NULL__,,0,,dan_00001_event_reward_17,17,Event
e,event_dan_00001_18,202510020,event_dan_00001,SpecificQuestClear,quest_event_dan1_savage,1,__NULL__,,0,,dan_00001_event_reward_18,18,Event
e,event_dan_00001_19,202510020,event_dan_00001,DefeatEnemyCount,,10,__NULL__,,0,,dan_00001_event_reward_19,19,Event
e,event_dan_00001_20,202510020,event_dan_00001,DefeatEnemyCount,,20,__NULL__,,0,,dan_00001_event_reward_20,20,Event
e,event_dan_00001_21,202510020,event_dan_00001,DefeatEnemyCount,,30,__NULL__,,0,,dan_00001_event_reward_21,21,Event
e,event_dan_00001_22,202510020,event_dan_00001,DefeatEnemyCount,,40,__NULL__,,0,,dan_00001_event_reward_22,22,Event
e,event_dan_00001_23,202510020,event_dan_00001,DefeatEnemyCount,,50,__NULL__,,0,,dan_00001_event_reward_23,23,Event
e,event_dan_00001_24,202510020,event_dan_00001,DefeatEnemyCount,,100,__NULL__,,0,,dan_00001_event_reward_24,24,Event
e,event_mag_00001_1,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00501,2,__NULL__,,0,,mag_00001_event_reward_01,1,UnitList
e,event_mag_00001_2,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00501,3,__NULL__,,0,,mag_00001_event_reward_02,2,UnitList
e,event_mag_00001_3,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00501,4,__NULL__,,0,,mag_00001_event_reward_03,3,UnitList
e,event_mag_00001_4,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00501,5,__NULL__,,0,,mag_00001_event_reward_04,4,UnitList
e,event_mag_00001_5,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00501,20,__NULL__,,0,,mag_00001_event_reward_05,5,UnitList
e,event_mag_00001_6,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00501,30,__NULL__,,0,,mag_00001_event_reward_06,6,UnitList
e,event_mag_00001_7,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00501,40,__NULL__,,0,,mag_00001_event_reward_07,7,UnitList
e,event_mag_00001_8,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00401,2,__NULL__,,0,,mag_00001_event_reward_08,8,UnitList
e,event_mag_00001_9,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00401,3,__NULL__,,0,,mag_00001_event_reward_09,9,UnitList
e,event_mag_00001_10,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00401,4,__NULL__,,0,,mag_00001_event_reward_10,10,UnitList
e,event_mag_00001_11,202511010,event_mag_00001,SpecificUnitGradeUpCount,chara_mag_00401,5,__NULL__,,0,,mag_00001_event_reward_11,11,UnitList
e,event_mag_00001_12,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00401,20,__NULL__,,0,,mag_00001_event_reward_12,12,UnitList
e,event_mag_00001_13,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00401,30,__NULL__,,0,,mag_00001_event_reward_13,13,UnitList
e,event_mag_00001_14,202511010,event_mag_00001,SpecificUnitLevel,chara_mag_00401,40,__NULL__,,0,,mag_00001_event_reward_14,14,UnitList
e,event_mag_00001_15,202511010,event_mag_00001,SpecificQuestClear,quest_event_mag1_charaget01,1,__NULL__,,0,,mag_00001_event_reward_15,15,Event
e,event_mag_00001_16,202511010,event_mag_00001,SpecificQuestClear,quest_event_mag1_charaget02,1,__NULL__,,0,,mag_00001_event_reward_16,16,Event
e,event_mag_00001_17,202511010,event_mag_00001,SpecificQuestClear,quest_event_mag1_challenge01,1,__NULL__,,0,,mag_00001_event_reward_17,17,Event
e,event_mag_00001_18,202511010,event_mag_00001,SpecificQuestClear,quest_event_mag1_savage,1,__NULL__,,0,,mag_00001_event_reward_18,18,Event
e,event_mag_00001_19,202511010,event_mag_00001,DefeatEnemyCount,,10,__NULL__,,0,,mag_00001_event_reward_19,19,Event
e,event_mag_00001_20,202511010,event_mag_00001,DefeatEnemyCount,,20,__NULL__,,0,,mag_00001_event_reward_20,20,Event
e,event_mag_00001_21,202511010,event_mag_00001,DefeatEnemyCount,,30,__NULL__,,0,,mag_00001_event_reward_21,21,Event
e,event_mag_00001_22,202511010,event_mag_00001,DefeatEnemyCount,,40,__NULL__,,0,,mag_00001_event_reward_22,22,Event
e,event_mag_00001_23,202511010,event_mag_00001,DefeatEnemyCount,,50,__NULL__,,0,,mag_00001_event_reward_23,23,Event
e,event_mag_00001_24,202511010,event_mag_00001,DefeatEnemyCount,,100,__NULL__,,0,,mag_00001_event_reward_24,24,Event
e,event_yuw_00001_1,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00501,2,__NULL__,,0,,yuw_00001_event_reward_01,1,UnitList
e,event_yuw_00001_2,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00501,3,__NULL__,,0,,yuw_00001_event_reward_02,2,UnitList
e,event_yuw_00001_3,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00501,4,__NULL__,,0,,yuw_00001_event_reward_03,3,UnitList
e,event_yuw_00001_4,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00501,5,__NULL__,,0,,yuw_00001_event_reward_04,4,UnitList
e,event_yuw_00001_5,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,20,__NULL__,,0,,yuw_00001_event_reward_05,5,UnitList
e,event_yuw_00001_6,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,30,__NULL__,,0,,yuw_00001_event_reward_06,6,UnitList
e,event_yuw_00001_7,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,40,__NULL__,,0,,yuw_00001_event_reward_07,7,UnitList
e,event_yuw_00001_8,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,50,__NULL__,,0,,yuw_00001_event_reward_08,8,UnitList
e,event_yuw_00001_9,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,60,__NULL__,,0,,yuw_00001_event_reward_09,9,UnitList
e,event_yuw_00001_10,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,70,__NULL__,,0,,yuw_00001_event_reward_10,10,UnitList
e,event_yuw_00001_11,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00501,80,__NULL__,,0,,yuw_00001_event_reward_11,11,UnitList
e,event_yuw_00001_12,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00601,2,__NULL__,,0,,yuw_00001_event_reward_12,12,UnitList
e,event_yuw_00001_13,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00601,3,__NULL__,,0,,yuw_00001_event_reward_13,13,UnitList
e,event_yuw_00001_14,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00601,4,__NULL__,,0,,yuw_00001_event_reward_14,14,UnitList
e,event_yuw_00001_15,202511020,event_yuw_00001,SpecificUnitGradeUpCount,chara_yuw_00601,5,__NULL__,,0,,yuw_00001_event_reward_15,15,UnitList
e,event_yuw_00001_16,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,20,__NULL__,,0,,yuw_00001_event_reward_16,16,UnitList
e,event_yuw_00001_17,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,30,__NULL__,,0,,yuw_00001_event_reward_17,17,UnitList
e,event_yuw_00001_18,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,40,__NULL__,,0,,yuw_00001_event_reward_18,18,UnitList
e,event_yuw_00001_19,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,50,__NULL__,,0,,yuw_00001_event_reward_19,19,UnitList
e,event_yuw_00001_20,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,60,__NULL__,,0,,yuw_00001_event_reward_20,20,UnitList
e,event_yuw_00001_21,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,70,__NULL__,,0,,yuw_00001_event_reward_21,21,UnitList
e,event_yuw_00001_22,202511020,event_yuw_00001,SpecificUnitLevel,chara_yuw_00601,80,__NULL__,,0,,yuw_00001_event_reward_22,22,UnitList
e,event_yuw_00001_23,202511020,event_yuw_00001,SpecificQuestClear,quest_event_yuw1_charaget01,1,__NULL__,,0,,yuw_00001_event_reward_23,23,Event
e,event_yuw_00001_24,202511020,event_yuw_00001,SpecificQuestClear,quest_event_yuw1_charaget02,1,__NULL__,,0,,yuw_00001_event_reward_24,24,Event
e,event_yuw_00001_25,202511020,event_yuw_00001,SpecificQuestClear,quest_event_yuw1_challenge01,1,__NULL__,,0,,yuw_00001_event_reward_25,25,Event
e,event_yuw_00001_26,202511020,event_yuw_00001,SpecificQuestClear,quest_event_yuw1_savage,1,__NULL__,,0,,yuw_00001_event_reward_26,26,Event
e,event_yuw_00001_27,202511020,event_yuw_00001,DefeatEnemyCount,,10,__NULL__,,0,,yuw_00001_event_reward_27,27,Event
e,event_yuw_00001_28,202511020,event_yuw_00001,DefeatEnemyCount,,20,__NULL__,,0,,yuw_00001_event_reward_28,28,Event
e,event_yuw_00001_29,202511020,event_yuw_00001,DefeatEnemyCount,,30,__NULL__,,0,,yuw_00001_event_reward_29,29,Event
e,event_yuw_00001_30,202511020,event_yuw_00001,DefeatEnemyCount,,40,__NULL__,,0,,yuw_00001_event_reward_30,30,Event
e,event_yuw_00001_31,202511020,event_yuw_00001,DefeatEnemyCount,,50,__NULL__,,0,,yuw_00001_event_reward_31,31,Event
e,event_yuw_00001_32,202511020,event_yuw_00001,DefeatEnemyCount,,60,__NULL__,,0,,yuw_00001_event_reward_32,32,Event
e,event_yuw_00001_33,202511020,event_yuw_00001,DefeatEnemyCount,,70,__NULL__,,0,,yuw_00001_event_reward_33,33,Event
e,event_yuw_00001_34,202511020,event_yuw_00001,DefeatEnemyCount,,80,__NULL__,,0,,yuw_00001_event_reward_34,34,Event
e,event_yuw_00001_35,202511020,event_yuw_00001,DefeatEnemyCount,,90,__NULL__,,0,,yuw_00001_event_reward_35,35,Event
e,event_yuw_00001_36,202511020,event_yuw_00001,DefeatEnemyCount,,100,__NULL__,,0,,yuw_00001_event_reward_36,36,Event
e,event_yuw_00001_37,202511020,event_yuw_00001,DefeatEnemyCount,,150,__NULL__,,0,,yuw_00001_event_reward_37,37,Event
e,event_yuw_00001_38,202511020,event_yuw_00001,DefeatEnemyCount,,200,__NULL__,,0,,yuw_00001_event_reward_38,38,Event
e,event_yuw_00001_39,202511020,event_yuw_00001,DefeatEnemyCount,,300,__NULL__,,0,,yuw_00001_event_reward_39,39,Event
e,event_sur_00001_1,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00801,2,__NULL__,,0,,sur_00001_event_reward_01,1,UnitList
e,event_sur_00001_2,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00801,3,__NULL__,,0,,sur_00001_event_reward_02,2,UnitList
e,event_sur_00001_3,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00801,4,__NULL__,,0,,sur_00001_event_reward_03,3,UnitList
e,event_sur_00001_4,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00801,5,__NULL__,,0,,sur_00001_event_reward_04,4,UnitList
e,event_sur_00001_5,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,20,__NULL__,,0,,sur_00001_event_reward_05,5,UnitList
e,event_sur_00001_6,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,30,__NULL__,,0,,sur_00001_event_reward_06,6,UnitList
e,event_sur_00001_7,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,40,__NULL__,,0,,sur_00001_event_reward_07,7,UnitList
e,event_sur_00001_8,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,50,__NULL__,,0,,sur_00001_event_reward_08,8,UnitList
e,event_sur_00001_9,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,60,__NULL__,,0,,sur_00001_event_reward_09,9,UnitList
e,event_sur_00001_10,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,70,__NULL__,,0,,sur_00001_event_reward_10,10,UnitList
e,event_sur_00001_11,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00801,80,__NULL__,,0,,sur_00001_event_reward_11,11,UnitList
e,event_sur_00001_12,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00701,2,__NULL__,,0,,sur_00001_event_reward_12,12,UnitList
e,event_sur_00001_13,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00701,3,__NULL__,,0,,sur_00001_event_reward_13,13,UnitList
e,event_sur_00001_14,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00701,4,__NULL__,,0,,sur_00001_event_reward_14,14,UnitList
e,event_sur_00001_15,202512010,event_sur_00001,SpecificUnitGradeUpCount,chara_sur_00701,5,__NULL__,,0,,sur_00001_event_reward_15,15,UnitList
e,event_sur_00001_16,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,20,__NULL__,,0,,sur_00001_event_reward_16,16,UnitList
e,event_sur_00001_17,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,30,__NULL__,,0,,sur_00001_event_reward_17,17,UnitList
e,event_sur_00001_18,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,40,__NULL__,,0,,sur_00001_event_reward_18,18,UnitList
e,event_sur_00001_19,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,50,__NULL__,,0,,sur_00001_event_reward_19,19,UnitList
e,event_sur_00001_20,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,60,__NULL__,,0,,sur_00001_event_reward_20,20,UnitList
e,event_sur_00001_21,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,70,__NULL__,,0,,sur_00001_event_reward_21,21,UnitList
e,event_sur_00001_22,202512010,event_sur_00001,SpecificUnitLevel,chara_sur_00701,80,__NULL__,,0,,sur_00001_event_reward_22,22,UnitList
e,event_sur_00001_23,202512010,event_sur_00001,SpecificQuestClear,quest_event_sur1_charaget01,1,__NULL__,,0,,sur_00001_event_reward_23,23,Event
e,event_sur_00001_24,202512010,event_sur_00001,SpecificQuestClear,quest_event_sur1_charaget02,1,__NULL__,,0,,sur_00001_event_reward_24,24,Event
e,event_sur_00001_25,202512010,event_sur_00001,SpecificQuestClear,quest_event_sur1_challenge01,1,__NULL__,,0,,sur_00001_event_reward_25,25,Event
e,event_sur_00001_26,202512010,event_sur_00001,SpecificQuestClear,quest_event_sur1_savage,1,__NULL__,,0,,sur_00001_event_reward_26,26,Event
e,event_sur_00001_27,202512010,event_sur_00001,DefeatEnemyCount,,10,__NULL__,,0,,sur_00001_event_reward_27,27,Event
e,event_sur_00001_28,202512010,event_sur_00001,DefeatEnemyCount,,20,__NULL__,,0,,sur_00001_event_reward_28,28,Event
e,event_sur_00001_29,202512010,event_sur_00001,DefeatEnemyCount,,30,__NULL__,,0,,sur_00001_event_reward_29,29,Event
e,event_sur_00001_30,202512010,event_sur_00001,DefeatEnemyCount,,40,__NULL__,,0,,sur_00001_event_reward_30,30,Event
e,event_sur_00001_31,202512010,event_sur_00001,DefeatEnemyCount,,50,__NULL__,,0,,sur_00001_event_reward_31,31,Event
e,event_sur_00001_32,202512010,event_sur_00001,DefeatEnemyCount,,60,__NULL__,,0,,sur_00001_event_reward_32,32,Event
e,event_sur_00001_33,202512010,event_sur_00001,DefeatEnemyCount,,70,__NULL__,,0,,sur_00001_event_reward_33,33,Event
e,event_sur_00001_34,202512010,event_sur_00001,DefeatEnemyCount,,80,__NULL__,,0,,sur_00001_event_reward_34,34,Event
e,event_sur_00001_35,202512010,event_sur_00001,DefeatEnemyCount,,90,__NULL__,,0,,sur_00001_event_reward_35,35,Event
e,event_sur_00001_36,202512010,event_sur_00001,DefeatEnemyCount,,100,__NULL__,,0,,sur_00001_event_reward_36,36,Event
e,event_sur_00001_37,202512010,event_sur_00001,DefeatEnemyCount,,150,__NULL__,,0,,sur_00001_event_reward_37,37,Event
e,event_sur_00001_38,202512010,event_sur_00001,DefeatEnemyCount,,200,__NULL__,,0,,sur_00001_event_reward_38,38,Event
e,event_sur_00001_39,202512010,event_sur_00001,DefeatEnemyCount,,300,__NULL__,,0,,sur_00001_event_reward_39,39,Event
e,event_jig_00001_1,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,2,__NULL__,,0,,jig_00001_event_reward_01,1,UnitList
e,event_jig_00001_2,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,3,__NULL__,,0,,jig_00001_event_reward_02,2,UnitList
e,event_jig_00001_3,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,4,__NULL__,,0,,jig_00001_event_reward_03,3,UnitList
e,event_jig_00001_4,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00701,5,__NULL__,,0,,jig_00001_event_reward_04,4,UnitList
e,event_jig_00001_5,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,20,__NULL__,,0,,jig_00001_event_reward_05,5,UnitList
e,event_jig_00001_6,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,30,__NULL__,,0,,jig_00001_event_reward_06,6,UnitList
e,event_jig_00001_7,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,40,__NULL__,,0,,jig_00001_event_reward_07,7,UnitList
e,event_jig_00001_8,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,50,__NULL__,,0,,jig_00001_event_reward_08,8,UnitList
e,event_jig_00001_9,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,60,__NULL__,,0,,jig_00001_event_reward_09,9,UnitList
e,event_jig_00001_10,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,70,__NULL__,,0,,jig_00001_event_reward_10,10,UnitList
e,event_jig_00001_11,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00701,80,__NULL__,,0,,jig_00001_event_reward_11,11,UnitList
e,event_jig_00001_12,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00601,2,__NULL__,,0,,jig_00001_event_reward_12,12,UnitList
e,event_jig_00001_13,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00601,3,__NULL__,,0,,jig_00001_event_reward_13,13,UnitList
e,event_jig_00001_14,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00601,4,__NULL__,,0,,jig_00001_event_reward_14,14,UnitList
e,event_jig_00001_15,202601010,event_jig_00001,SpecificUnitGradeUpCount,chara_jig_00601,5,__NULL__,,0,,jig_00001_event_reward_15,15,UnitList
e,event_jig_00001_16,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,20,__NULL__,,0,,jig_00001_event_reward_16,16,UnitList
e,event_jig_00001_17,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,30,__NULL__,,0,,jig_00001_event_reward_17,17,UnitList
e,event_jig_00001_18,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,40,__NULL__,,0,,jig_00001_event_reward_18,18,UnitList
e,event_jig_00001_19,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,50,__NULL__,,0,,jig_00001_event_reward_19,19,UnitList
e,event_jig_00001_20,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,60,__NULL__,,0,,jig_00001_event_reward_20,20,UnitList
e,event_jig_00001_21,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,70,__NULL__,,0,,jig_00001_event_reward_21,21,UnitList
e,event_jig_00001_22,202601010,event_jig_00001,SpecificUnitLevel,chara_jig_00601,80,__NULL__,,0,,jig_00001_event_reward_22,22,UnitList
e,event_jig_00001_23,202601010,event_jig_00001,SpecificQuestClear,quest_event_jig1_charaget01,1,__NULL__,,0,,jig_00001_event_reward_23,23,Event
e,event_jig_00001_24,202601010,event_jig_00001,SpecificQuestClear,quest_event_jig1_charaget02,1,__NULL__,,0,,jig_00001_event_reward_24,24,Event
e,event_jig_00001_25,202601010,event_jig_00001,SpecificQuestClear,quest_event_jig1_challenge01,1,__NULL__,,0,,jig_00001_event_reward_25,25,Event
e,event_jig_00001_26,202601010,event_jig_00001,SpecificQuestClear,quest_event_jig1_savage,1,__NULL__,,0,,jig_00001_event_reward_26,26,Event
e,event_jig_00001_27,202601010,event_jig_00001,DefeatEnemyCount,,10,__NULL__,,0,,jig_00001_event_reward_27,27,Event
e,event_jig_00001_28,202601010,event_jig_00001,DefeatEnemyCount,,20,__NULL__,,0,,jig_00001_event_reward_28,28,Event
e,event_jig_00001_29,202601010,event_jig_00001,DefeatEnemyCount,,30,__NULL__,,0,,jig_00001_event_reward_29,29,Event
e,event_jig_00001_30,202601010,event_jig_00001,DefeatEnemyCount,,40,__NULL__,,0,,jig_00001_event_reward_30,30,Event
e,event_jig_00001_31,202601010,event_jig_00001,DefeatEnemyCount,,50,__NULL__,,0,,jig_00001_event_reward_31,31,Event
e,event_jig_00001_32,202601010,event_jig_00001,DefeatEnemyCount,,60,__NULL__,,0,,jig_00001_event_reward_32,32,Event
e,event_jig_00001_33,202601010,event_jig_00001,DefeatEnemyCount,,70,__NULL__,,0,,jig_00001_event_reward_33,33,Event
e,event_jig_00001_34,202601010,event_jig_00001,DefeatEnemyCount,,80,__NULL__,,0,,jig_00001_event_reward_34,34,Event
e,event_jig_00001_35,202601010,event_jig_00001,DefeatEnemyCount,,90,__NULL__,,0,,jig_00001_event_reward_35,35,Event
e,event_jig_00001_36,202601010,event_jig_00001,DefeatEnemyCount,,100,__NULL__,,0,,jig_00001_event_reward_36,36,Event
e,event_jig_00001_37,202601010,event_jig_00001,DefeatEnemyCount,,150,__NULL__,,0,,jig_00001_event_reward_37,37,Event
e,event_jig_00001_38,202601010,event_jig_00001,DefeatEnemyCount,,200,__NULL__,,0,,jig_00001_event_reward_38,38,Event
e,event_jig_00001_39,202601010,event_jig_00001,DefeatEnemyCount,,300,__NULL__,,0,,jig_00001_event_reward_39,39,Event
e,event_jig_00001_40,202601010,event_jig_00001,DefeatEnemyCount,,400,__NULL__,,0,,jig_00001_event_reward_40,40,Event
e,event_jig_00001_41,202601010,event_jig_00001,DefeatEnemyCount,,500,__NULL__,,0,,jig_00001_event_reward_41,41,Event
e,event_jig_00001_42,202601010,event_jig_00001,DefeatEnemyCount,,750,__NULL__,,0,,jig_00001_event_reward_42,42,Event
e,event_jig_00001_43,202601010,event_jig_00001,DefeatEnemyCount,,1000,__NULL__,,0,,jig_00001_event_reward_43,43,Event
e,event_osh_00001_1,202512020,event_osh_00001,StageClearCount,,5,__NULL__,,0,,osh_00001_event_reward_1,1,Event
e,event_osh_00001_2,202512020,event_osh_00001,StageClearCount,,10,__NULL__,,0,,osh_00001_event_reward_2,2,Event
e,event_osh_00001_3,202512020,event_osh_00001,StageClearCount,,15,__NULL__,,0,,osh_00001_event_reward_3,3,Event
e,event_osh_00001_4,202512020,event_osh_00001,StageClearCount,,20,__NULL__,,0,,osh_00001_event_reward_4,4,Event
e,event_osh_00001_5,202512020,event_osh_00001,StageClearCount,,30,__NULL__,,0,,osh_00001_event_reward_5,5,Event
e,event_osh_00001_6,202512020,event_osh_00001,StageClearCount,,40,__NULL__,,0,,osh_00001_event_reward_6,6,Event
e,event_osh_00001_7,202512020,event_osh_00001,StageClearCount,,50,__NULL__,,0,,osh_00001_event_reward_7,7,Event
e,event_osh_00001_8,202512020,event_osh_00001,StageClearCount,,60,__NULL__,,0,,osh_00001_event_reward_8,8,Event
e,event_osh_00001_9,202512020,event_osh_00001,StageClearCount,,70,__NULL__,,0,,osh_00001_event_reward_9,9,Event
e,event_osh_00001_10,202512020,event_osh_00001,StageClearCount,,80,__NULL__,,0,,osh_00001_event_reward_10,10,Event
e,event_osh_00001_11,202512020,event_osh_00001,StageClearCount,,90,__NULL__,,0,,osh_00001_event_reward_11,11,Event
e,event_osh_00001_12,202512020,event_osh_00001,StageClearCount,,100,__NULL__,,0,,osh_00001_event_reward_12,12,Event
e,event_osh_00001_13,202512020,event_osh_00001,StageClearCount,,110,__NULL__,,0,,osh_00001_event_reward_13,13,Event
e,event_osh_00001_14,202512020,event_osh_00001,StageClearCount,,120,__NULL__,,0,,osh_00001_event_reward_14,14,Event
e,event_osh_00001_15,202512020,event_osh_00001,StageClearCount,,150,__NULL__,,0,,osh_00001_event_reward_15,15,Event
e,event_osh_00001_16,202512020,event_osh_00001,StageClearCount,,180,__NULL__,,0,,osh_00001_event_reward_16,16,Event
e,event_osh_00001_17,202512020,event_osh_00001,StageClearCount,,190,__NULL__,,0,,osh_00001_event_reward_17,17,Event
e,event_osh_00001_18,202512020,event_osh_00001,StageClearCount,,200,__NULL__,,0,,osh_00001_event_reward_18,18,Event
e,event_osh_00001_19,202512020,event_osh_00001,StageClearCount,,210,__NULL__,,0,,osh_00001_event_reward_19,19,Event
e,event_osh_00001_20,202512020,event_osh_00001,StageClearCount,,250,__NULL__,,0,,osh_00001_event_reward_20,20,Event
e,event_osh_00001_21,202512020,event_osh_00001,StageClearCount,,300,__NULL__,,0,,osh_00001_event_reward_21,21,Event
e,event_osh_00001_22,202512020,event_osh_00001,StageClearCount,,350,__NULL__,,0,,osh_00001_event_reward_22,22,Event
e,event_osh_00001_23,202512020,event_osh_00001,StageClearCount,,400,__NULL__,,0,,osh_00001_event_reward_23,23,Event
e,event_osh_00001_24,202512020,event_osh_00001,StageClearCount,,450,__NULL__,,0,,osh_00001_event_reward_24,24,Event
e,event_osh_00001_25,202512020,event_osh_00001,StageClearCount,,500,__NULL__,,0,,osh_00001_event_reward_25,25,Event
e,event_osh_00001_26,202512020,event_osh_00001,StageClearCount,,550,__NULL__,,0,,osh_00001_event_reward_26,26,Event
e,event_osh_00001_27,202512020,event_osh_00001,StageClearCount,,600,__NULL__,,0,,osh_00001_event_reward_27,27,Event
e,event_osh_00001_28,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,10,__NULL__,,0,,osh_00001_event_reward_28,28,Gacha
e,event_osh_00001_29,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,20,__NULL__,,0,,osh_00001_event_reward_29,29,Gacha
e,event_osh_00001_30,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,30,__NULL__,,0,,osh_00001_event_reward_30,30,Gacha
e,event_osh_00001_31,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,40,__NULL__,,0,,osh_00001_event_reward_31,31,Gacha
e,event_osh_00001_32,202512020,event_osh_00001,SpecificGachaDrawCount,gasho_001,50,__NULL__,,0,,osh_00001_event_reward_32,32,Gacha
e,event_osh_00001_33,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,1,__NULL__,,0,,osh_00001_event_reward_33,33,Event
e,event_osh_00001_34,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,3,__NULL__,,0,,osh_00001_event_reward_34,34,Event
e,event_osh_00001_35,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_1day_00001,5,__NULL__,,0,,osh_00001_event_reward_35,35,Event
e,event_osh_00001_36,202512020,event_osh_00001,SpecificQuestClear,quest_event_osh1_charaget01,1,__NULL__,,0,,osh_00001_event_reward_36,36,Event
e,event_osh_00001_37,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,5,__NULL__,,0,,osh_00001_event_reward_37,37,Event
e,event_osh_00001_38,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,10,__NULL__,,0,,osh_00001_event_reward_38,38,Event
e,event_osh_00001_39,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,30,__NULL__,,0,,osh_00001_event_reward_39,39,Event
e,event_osh_00001_40,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,50,__NULL__,,0,,osh_00001_event_reward_40,40,Event
e,event_osh_00001_41,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget01_00003,100,__NULL__,,0,,osh_00001_event_reward_41,41,Event
e,event_osh_00001_42,202512020,event_osh_00001,SpecificQuestClear,quest_event_osh1_charaget02,1,__NULL__,,0,,osh_00001_event_reward_42,42,Event
e,event_osh_00001_43,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget02_00003,3,__NULL__,,0,,osh_00001_event_reward_43,43,Event
e,event_osh_00001_44,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget02_00003,5,__NULL__,,0,,osh_00001_event_reward_44,44,Event
e,event_osh_00001_45,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget02_00003,10,__NULL__,,0,,osh_00001_event_reward_45,45,Event
e,event_osh_00001_46,202512020,event_osh_00001,SpecificUnitStageClearCount,chara_osh_00601.event_osh1_charaget02_00003,20,__NULL__,,0,,osh_00001_event_reward_46,46,Event
e,event_osh_00001_47,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_challenge01_00001,1,__NULL__,,0,,osh_00001_event_reward_47,47,Event
e,event_osh_00001_48,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_challenge01_00002,1,__NULL__,,0,,osh_00001_event_reward_48,48,Event
e,event_osh_00001_49,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_challenge01_00003,1,__NULL__,,0,,osh_00001_event_reward_49,49,Event
e,event_osh_00001_50,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_challenge01_00004,1,__NULL__,,0,,osh_00001_event_reward_50,50,Event
e,event_osh_00001_51,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_savage_00001,1,__NULL__,,0,,osh_00001_event_reward_51,51,Event
e,event_osh_00001_52,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_savage_00002,1,__NULL__,,0,,osh_00001_event_reward_52,52,Event
e,event_osh_00001_53,202512020,event_osh_00001,SpecificUnitStageChallengeCount,chara_osh_00601.event_osh1_savage_00003,1,__NULL__,,0,,osh_00001_event_reward_53,53,Event
e,event_glo_00001_1,202512020,event_glo_00001,SpecificStageClearCount,event_glo1_1day_00001,1,__NULL__,,0,,glo_00001_event_reward_01,1,Event
e,event_glo_00001_2,202512020,event_glo_00001,SpecificStageClearCount,event_glo1_1day_00001,2,__NULL__,,0,,glo_00001_event_reward_02,2,Event
e,event_glo_00001_3,202512020,event_glo_00001,SpecificStageClearCount,event_glo1_1day_00001,3,__NULL__,,0,,glo_00001_event_reward_03,3,Event
e,event_you_00001_1,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00201,2,__NULL__,,0,,you_00001_event_reward_01,1,UnitList
e,event_you_00001_2,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00201,3,__NULL__,,0,,you_00001_event_reward_02,2,UnitList
e,event_you_00001_3,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00201,4,__NULL__,,0,,you_00001_event_reward_03,3,UnitList
e,event_you_00001_4,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00201,5,__NULL__,,0,,you_00001_event_reward_04,4,UnitList
e,event_you_00001_5,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,20,__NULL__,,0,,you_00001_event_reward_05,5,UnitList
e,event_you_00001_6,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,30,__NULL__,,0,,you_00001_event_reward_06,6,UnitList
e,event_you_00001_7,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,40,__NULL__,,0,,you_00001_event_reward_07,7,UnitList
e,event_you_00001_8,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,50,__NULL__,,0,,you_00001_event_reward_08,8,UnitList
e,event_you_00001_9,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,60,__NULL__,,0,,you_00001_event_reward_09,9,UnitList
e,event_you_00001_10,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,70,__NULL__,,0,,you_00001_event_reward_10,10,UnitList
e,event_you_00001_11,202602010,event_you_00001,SpecificUnitLevel,chara_you_00201,80,__NULL__,,0,,you_00001_event_reward_11,11,UnitList
e,event_you_00001_12,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00301,2,__NULL__,,0,,you_00001_event_reward_12,12,UnitList
e,event_you_00001_13,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00301,3,__NULL__,,0,,you_00001_event_reward_13,13,UnitList
e,event_you_00001_14,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00301,4,__NULL__,,0,,you_00001_event_reward_14,14,UnitList
e,event_you_00001_15,202602010,event_you_00001,SpecificUnitGradeUpCount,chara_you_00301,5,__NULL__,,0,,you_00001_event_reward_15,15,UnitList
e,event_you_00001_16,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,20,__NULL__,,0,,you_00001_event_reward_16,16,UnitList
e,event_you_00001_17,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,30,__NULL__,,0,,you_00001_event_reward_17,17,UnitList
e,event_you_00001_18,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,40,__NULL__,,0,,you_00001_event_reward_18,18,UnitList
e,event_you_00001_19,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,50,__NULL__,,0,,you_00001_event_reward_19,19,UnitList
e,event_you_00001_20,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,60,__NULL__,,0,,you_00001_event_reward_20,20,UnitList
e,event_you_00001_21,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,70,__NULL__,,0,,you_00001_event_reward_21,21,UnitList
e,event_you_00001_22,202602010,event_you_00001,SpecificUnitLevel,chara_you_00301,80,__NULL__,,0,,you_00001_event_reward_22,22,UnitList
e,event_you_00001_23,202602010,event_you_00001,SpecificQuestClear,quest_event_you1_charaget01,1,__NULL__,,0,,you_00001_event_reward_23,23,Event
e,event_you_00001_24,202602010,event_you_00001,SpecificQuestClear,quest_event_you1_charaget02,1,__NULL__,,0,,you_00001_event_reward_24,24,Event
e,event_you_00001_25,202602010,event_you_00001,SpecificQuestClear,quest_event_you1_challenge01,1,__NULL__,,0,,you_00001_event_reward_25,25,Event
e,event_you_00001_26,202602010,event_you_00001,SpecificQuestClear,quest_event_you1_savage,1,__NULL__,,0,,you_00001_event_reward_26,26,Event
e,event_you_00001_27,202602010,event_you_00001,DefeatEnemyCount,,10,__NULL__,,0,,you_00001_event_reward_27,27,Event
e,event_you_00001_28,202602010,event_you_00001,DefeatEnemyCount,,20,__NULL__,,0,,you_00001_event_reward_28,28,Event
e,event_you_00001_29,202602010,event_you_00001,DefeatEnemyCount,,30,__NULL__,,0,,you_00001_event_reward_29,29,Event
e,event_you_00001_30,202602010,event_you_00001,DefeatEnemyCount,,40,__NULL__,,0,,you_00001_event_reward_30,30,Event
e,event_you_00001_31,202602010,event_you_00001,DefeatEnemyCount,,50,__NULL__,,0,,you_00001_event_reward_31,31,Event
e,event_you_00001_32,202602010,event_you_00001,DefeatEnemyCount,,60,__NULL__,,0,,you_00001_event_reward_32,32,Event
e,event_you_00001_33,202602010,event_you_00001,DefeatEnemyCount,,70,__NULL__,,0,,you_00001_event_reward_33,33,Event
e,event_you_00001_34,202602010,event_you_00001,DefeatEnemyCount,,80,__NULL__,,0,,you_00001_event_reward_34,34,Event
e,event_you_00001_35,202602010,event_you_00001,DefeatEnemyCount,,90,__NULL__,,0,,you_00001_event_reward_35,35,Event
e,event_you_00001_36,202602010,event_you_00001,DefeatEnemyCount,,100,__NULL__,,0,,you_00001_event_reward_36,36,Event
e,event_you_00001_37,202602010,event_you_00001,DefeatEnemyCount,,150,__NULL__,,0,,you_00001_event_reward_37,37,Event
e,event_you_00001_38,202602010,event_you_00001,DefeatEnemyCount,,200,__NULL__,,0,,you_00001_event_reward_38,38,Event
e,event_you_00001_39,202602010,event_you_00001,DefeatEnemyCount,,300,__NULL__,,0,,you_00001_event_reward_39,39,Event
e,event_you_00001_40,202602010,event_you_00001,DefeatEnemyCount,,400,__NULL__,,0,,you_00001_event_reward_40,40,Event
e,event_you_00001_41,202602010,event_you_00001,DefeatEnemyCount,,500,__NULL__,,0,,you_00001_event_reward_41,41,Event
e,event_you_00001_42,202602010,event_you_00001,DefeatEnemyCount,,750,__NULL__,,0,,you_00001_event_reward_42,42,Event
e,event_you_00001_43,202602010,event_you_00001,DefeatEnemyCount,,1000,__NULL__,,0,,you_00001_event_reward_43,43,Event
e,event_kim_00001_1,202602020,event_kim_00001,DefeatBossEnemyCount,,1,__NULL__,,0,,kim_00001_event_reward_01,1,Event
e,event_kim_00001_2,202602020,event_kim_00001,DefeatBossEnemyCount,,3,__NULL__,,0,,kim_00001_event_reward_02,2,Event
e,event_kim_00001_3,202602020,event_kim_00001,DefeatBossEnemyCount,,5,__NULL__,,0,,kim_00001_event_reward_03,3,Event
e,event_kim_00001_4,202602020,event_kim_00001,DefeatBossEnemyCount,,10,__NULL__,,0,,kim_00001_event_reward_04,4,Event
e,event_kim_00001_5,202602020,event_kim_00001,DefeatBossEnemyCount,,15,__NULL__,,0,,kim_00001_event_reward_05,5,Event
e,event_kim_00001_6,202602020,event_kim_00001,DefeatBossEnemyCount,,20,__NULL__,,0,,kim_00001_event_reward_06,6,Event
e,event_kim_00001_7,202602020,event_kim_00001,DefeatBossEnemyCount,,25,__NULL__,,0,,kim_00001_event_reward_07,7,Event
e,event_kim_00001_8,202602020,event_kim_00001,DefeatBossEnemyCount,,30,__NULL__,,0,,kim_00001_event_reward_08,8,Event
e,event_kim_00001_9,202602020,event_kim_00001,DefeatBossEnemyCount,,35,__NULL__,,0,,kim_00001_event_reward_09,9,Event
e,event_kim_00001_10,202602020,event_kim_00001,DefeatBossEnemyCount,,40,__NULL__,,0,,kim_00001_event_reward_10,10,Event
e,event_kim_00001_11,202602020,event_kim_00001,DefeatBossEnemyCount,,45,__NULL__,,0,,kim_00001_event_reward_11,11,Event
e,event_kim_00001_12,202602020,event_kim_00001,DefeatBossEnemyCount,,50,__NULL__,,0,,kim_00001_event_reward_12,12,Event
e,event_kim_00001_13,202602020,event_kim_00001,DefeatBossEnemyCount,,55,__NULL__,,0,,kim_00001_event_reward_13,13,Event
e,event_kim_00001_14,202602020,event_kim_00001,DefeatBossEnemyCount,,60,__NULL__,,0,,kim_00001_event_reward_14,14,Event
e,event_kim_00001_15,202602020,event_kim_00001,DefeatBossEnemyCount,,65,__NULL__,,0,,kim_00001_event_reward_15,15,Event
e,event_kim_00001_16,202602020,event_kim_00001,DefeatBossEnemyCount,,70,__NULL__,,0,,kim_00001_event_reward_16,16,Event
e,event_kim_00001_17,202602020,event_kim_00001,DefeatBossEnemyCount,,75,__NULL__,,0,,kim_00001_event_reward_17,17,Event
e,event_kim_00001_18,202602020,event_kim_00001,DefeatBossEnemyCount,,80,__NULL__,,0,,kim_00001_event_reward_18,18,Event
e,event_kim_00001_19,202602020,event_kim_00001,DefeatBossEnemyCount,,85,__NULL__,,0,,kim_00001_event_reward_19,19,Event
e,event_kim_00001_20,202602020,event_kim_00001,DefeatBossEnemyCount,,90,__NULL__,,0,,kim_00001_event_reward_20,20,Event
e,event_kim_00001_21,202602020,event_kim_00001,DefeatBossEnemyCount,,95,__NULL__,,0,,kim_00001_event_reward_21,21,Event
e,event_kim_00001_22,202602020,event_kim_00001,DefeatBossEnemyCount,,100,__NULL__,,0,,kim_00001_event_reward_22,22,Event
e,event_kim_00001_23,202602020,event_kim_00001,SpecificQuestClear,quest_event_kim1_charaget01,1,__NULL__,,0,,kim_00001_event_reward_23,23,Event
e,event_kim_00001_24,202602020,event_kim_00001,SpecificQuestClear,quest_event_kim1_charaget02,1,__NULL__,,0,,kim_00001_event_reward_24,24,Event
e,event_kim_00001_25,202602020,event_kim_00001,SpecificQuestClear,quest_event_kim1_challenge01,1,__NULL__,,0,,kim_00001_event_reward_25,25,Event
e,event_kim_00001_26,202602020,event_kim_00001,SpecificQuestClear,quest_event_kim1_savage,1,__NULL__,,0,,kim_00001_event_reward_26,26,Event
e,event_kim_00001_27,202602020,event_kim_00001,DefeatEnemyCount,,10,__NULL__,,0,,kim_00001_event_reward_27,27,Event
e,event_kim_00001_28,202602020,event_kim_00001,DefeatEnemyCount,,20,__NULL__,,0,,kim_00001_event_reward_28,28,Event
e,event_kim_00001_29,202602020,event_kim_00001,DefeatEnemyCount,,30,__NULL__,,0,,kim_00001_event_reward_29,29,Event
e,event_kim_00001_30,202602020,event_kim_00001,DefeatEnemyCount,,40,__NULL__,,0,,kim_00001_event_reward_30,30,Event
e,event_kim_00001_31,202602020,event_kim_00001,DefeatEnemyCount,,50,__NULL__,,0,,kim_00001_event_reward_31,31,Event
e,event_kim_00001_32,202602020,event_kim_00001,DefeatEnemyCount,,60,__NULL__,,0,,kim_00001_event_reward_32,32,Event
e,event_kim_00001_33,202602020,event_kim_00001,DefeatEnemyCount,,70,__NULL__,,0,,kim_00001_event_reward_33,33,Event
e,event_kim_00001_34,202602020,event_kim_00001,DefeatEnemyCount,,80,__NULL__,,0,,kim_00001_event_reward_34,34,Event
e,event_kim_00001_35,202602020,event_kim_00001,DefeatEnemyCount,,90,__NULL__,,0,,kim_00001_event_reward_35,35,Event
e,event_kim_00001_36,202602020,event_kim_00001,DefeatEnemyCount,,100,__NULL__,,0,,kim_00001_event_reward_36,36,Event
e,event_kim_00001_37,202602020,event_kim_00001,DefeatEnemyCount,,150,__NULL__,,0,,kim_00001_event_reward_37,37,Event
e,event_kim_00001_38,202602020,event_kim_00001,DefeatEnemyCount,,200,__NULL__,,0,,kim_00001_event_reward_38,38,Event
e,event_kim_00001_39,202602020,event_kim_00001,DefeatEnemyCount,,300,__NULL__,,0,,kim_00001_event_reward_39,39,Event
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventDaily.csv -->
## ./projects/glow-masterdata/MstMissionEventDaily.csv

```csv
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventDailyBonus.csv -->
## ./projects/glow-masterdata/MstMissionEventDailyBonus.csv

```csv
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day_count,mst_mission_reward_group_id,sort_order,備考
e,event_kai_00001_daily_bonus_1,202509010,event_kai_00001_daily_bonus,1,event_kai_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_kai_00001_daily_bonus_2,202509010,event_kai_00001_daily_bonus,2,event_kai_00001_daily_bonus_2,1,コイン
e,event_kai_00001_daily_bonus_3,202509010,event_kai_00001_daily_bonus,3,event_kai_00001_daily_bonus_3,1,プリズム
e,event_kai_00001_daily_bonus_4,202509010,event_kai_00001_daily_bonus,4,event_kai_00001_daily_bonus_4,1,カラーメモリー・ブルー
e,event_kai_00001_daily_bonus_5,202509010,event_kai_00001_daily_bonus,5,event_kai_00001_daily_bonus_5,1,メモリーフラグメント・初級
e,event_kai_00001_daily_bonus_6,202509010,event_kai_00001_daily_bonus,6,event_kai_00001_daily_bonus_6,1,カラーメモリー・ブルー
e,event_kai_00001_daily_bonus_7,202509010,event_kai_00001_daily_bonus,7,event_kai_00001_daily_bonus_7,1,メモリーフラグメント・中級
e,event_kai_00001_daily_bonus_8,202509010,event_kai_00001_daily_bonus,8,event_kai_00001_daily_bonus_8,1,コイン
e,event_kai_00001_daily_bonus_9,202509010,event_kai_00001_daily_bonus,9,event_kai_00001_daily_bonus_9,1,カラーメモリー・ブルー
e,event_kai_00001_daily_bonus_10,202509010,event_kai_00001_daily_bonus,10,event_kai_00001_daily_bonus_10,1,プリズム
e,event_kai_00001_daily_bonus_11,202509010,event_kai_00001_daily_bonus,11,event_kai_00001_daily_bonus_11,1,カラーメモリー・ブルー
e,event_kai_00001_daily_bonus_12,202509010,event_kai_00001_daily_bonus,12,event_kai_00001_daily_bonus_12,1,ピックアップガシャチケット
e,event_spy_00001_daily_bonus_1,202510010,event_spy_00001_daily_bonus,1,event_spy_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_spy_00001_daily_bonus_2,202510010,event_spy_00001_daily_bonus,2,event_spy_00001_daily_bonus_2,1,コイン
e,event_spy_00001_daily_bonus_3,202510010,event_spy_00001_daily_bonus,3,event_spy_00001_daily_bonus_3,1,カラーメモリー・ブルー
e,event_spy_00001_daily_bonus_4,202510010,event_spy_00001_daily_bonus,4,event_spy_00001_daily_bonus_4,1,スペシャルガシャチケット
e,event_spy_00001_daily_bonus_5,202510010,event_spy_00001_daily_bonus,5,event_spy_00001_daily_bonus_5,1,メモリーフラグメント・初級
e,event_spy_00001_daily_bonus_6,202510010,event_spy_00001_daily_bonus,6,event_spy_00001_daily_bonus_6,1,メモリーフラグメント・中級
e,event_spy_00001_daily_bonus_7,202510010,event_spy_00001_daily_bonus,7,event_spy_00001_daily_bonus_7,1,カラーメモリー・イエロー
e,event_spy_00001_daily_bonus_8,202510010,event_spy_00001_daily_bonus,8,event_spy_00001_daily_bonus_8,1,プリズム
e,event_spy_00001_daily_bonus_9,202510010,event_spy_00001_daily_bonus,9,event_spy_00001_daily_bonus_9,1,ピックアップガシャチケット
e,event_spy_00001_daily_bonus_10,202510010,event_spy_00001_daily_bonus,10,event_spy_00001_daily_bonus_10,1,コイン
e,event_spy_00001_daily_bonus_11,202510010,event_spy_00001_daily_bonus,11,event_spy_00001_daily_bonus_11,1,カラーメモリー・レッド
e,event_spy_00001_daily_bonus_12,202510010,event_spy_00001_daily_bonus,12,event_spy_00001_daily_bonus_12,1,スペシャルガシャチケット
e,event_spy_00001_daily_bonus_13,202510010,event_spy_00001_daily_bonus,13,event_spy_00001_daily_bonus_13,1,メモリーフラグメント・初級
e,event_spy_00001_daily_bonus_14,202510010,event_spy_00001_daily_bonus,14,event_spy_00001_daily_bonus_14,1,メモリーフラグメント・中級
e,event_spy_00001_daily_bonus_15,202510010,event_spy_00001_daily_bonus,15,event_spy_00001_daily_bonus_15,1,プリズム
e,event_spy_00001_daily_bonus_16,202510010,event_spy_00001_daily_bonus,16,event_spy_00001_daily_bonus_16,1,メモリーフラグメント・上級
e,event_dan_00001_daily_bonus_1,202510020,event_dan_00001_daily_bonus,1,event_dan_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_dan_00001_daily_bonus_2,202510020,event_dan_00001_daily_bonus,2,event_dan_00001_daily_bonus_2,1,コイン
e,event_dan_00001_daily_bonus_3,202510020,event_dan_00001_daily_bonus,3,event_dan_00001_daily_bonus_3,1,カラーメモリー・グリーン
e,event_dan_00001_daily_bonus_4,202510020,event_dan_00001_daily_bonus,4,event_dan_00001_daily_bonus_4,1,スペシャルガシャチケット
e,event_dan_00001_daily_bonus_5,202510020,event_dan_00001_daily_bonus,5,event_dan_00001_daily_bonus_5,1,メモリーフラグメント・初級
e,event_dan_00001_daily_bonus_6,202510020,event_dan_00001_daily_bonus,6,event_dan_00001_daily_bonus_6,1,メモリーフラグメント・中級
e,event_dan_00001_daily_bonus_7,202510020,event_dan_00001_daily_bonus,7,event_dan_00001_daily_bonus_7,1,カラーメモリー・グリーン
e,event_dan_00001_daily_bonus_8,202510020,event_dan_00001_daily_bonus,8,event_dan_00001_daily_bonus_8,1,プリズム
e,event_dan_00001_daily_bonus_9,202510020,event_dan_00001_daily_bonus,9,event_dan_00001_daily_bonus_9,1,ピックアップガシャチケット
e,event_dan_00001_daily_bonus_10,202510020,event_dan_00001_daily_bonus,10,event_dan_00001_daily_bonus_10,1,コイン
e,event_dan_00001_daily_bonus_11,202510020,event_dan_00001_daily_bonus,11,event_dan_00001_daily_bonus_11,1,スペシャルガシャチケット
e,event_dan_00001_daily_bonus_12,202510020,event_dan_00001_daily_bonus,12,event_dan_00001_daily_bonus_12,1,メモリーフラグメント・初級
e,event_dan_00001_daily_bonus_13,202510020,event_dan_00001_daily_bonus,13,event_dan_00001_daily_bonus_13,1,メモリーフラグメント・中級
e,event_dan_00001_daily_bonus_14,202510020,event_dan_00001_daily_bonus,14,event_dan_00001_daily_bonus_14,1,プリズム
e,event_dan_00001_daily_bonus_15,202510020,event_dan_00001_daily_bonus,15,event_dan_00001_daily_bonus_15,1,メモリーフラグメント・上級
e,event_mag_00001_daily_bonus_1,202511010,event_mag_00001_daily_bonus,1,event_mag_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_mag_00001_daily_bonus_2,202511010,event_mag_00001_daily_bonus,2,event_mag_00001_daily_bonus_2,1,コイン
e,event_mag_00001_daily_bonus_3,202511010,event_mag_00001_daily_bonus,3,event_mag_00001_daily_bonus_3,1,プリズム
e,event_mag_00001_daily_bonus_4,202511010,event_mag_00001_daily_bonus,4,event_mag_00001_daily_bonus_4,1,カラーメモリー・レッド
e,event_mag_00001_daily_bonus_5,202511010,event_mag_00001_daily_bonus,5,event_mag_00001_daily_bonus_5,1,スペシャルガシャチケット
e,event_mag_00001_daily_bonus_6,202511010,event_mag_00001_daily_bonus,6,event_mag_00001_daily_bonus_6,1,メモリーフラグメント・初級
e,event_mag_00001_daily_bonus_7,202511010,event_mag_00001_daily_bonus,7,event_mag_00001_daily_bonus_7,1,メモリーフラグメント・中級
e,event_mag_00001_daily_bonus_8,202511010,event_mag_00001_daily_bonus,8,event_mag_00001_daily_bonus_8,1,カラーメモリー・グリーン
e,event_mag_00001_daily_bonus_9,202511010,event_mag_00001_daily_bonus,9,event_mag_00001_daily_bonus_9,1,プリズム
e,event_mag_00001_daily_bonus_10,202511010,event_mag_00001_daily_bonus,10,event_mag_00001_daily_bonus_10,1,コイン
e,event_mag_00001_daily_bonus_11,202511010,event_mag_00001_daily_bonus,11,event_mag_00001_daily_bonus_11,1,カラーメモリー・レッド
e,event_mag_00001_daily_bonus_12,202511010,event_mag_00001_daily_bonus,12,event_mag_00001_daily_bonus_12,1,ピックアップガシャチケット
e,event_mag_00001_daily_bonus_13,202511010,event_mag_00001_daily_bonus,13,event_mag_00001_daily_bonus_13,1,メモリーフラグメント・初級
e,event_mag_00001_daily_bonus_14,202511010,event_mag_00001_daily_bonus,14,event_mag_00001_daily_bonus_14,1,カラーメモリー・グリーン
e,event_mag_00001_daily_bonus_15,202511010,event_mag_00001_daily_bonus,15,event_mag_00001_daily_bonus_15,1,メモリーフラグメント・中級
e,event_mag_00001_daily_bonus_16,202511010,event_mag_00001_daily_bonus,16,event_mag_00001_daily_bonus_16,1,プリズム
e,event_mag_00001_daily_bonus_17,202511010,event_mag_00001_daily_bonus,17,event_mag_00001_daily_bonus_17,1,メモリーフラグメント・上級
e,event_mag_00001_daily_bonus_18,202511010,event_mag_00001_daily_bonus,18,event_mag_00001_daily_bonus_18,1,スペシャルガシャチケット
e,event_mag_00001_daily_bonus_19,202511010,event_mag_00001_daily_bonus,19,event_mag_00001_daily_bonus_19,1,カラーメモリー・グリーン
e,event_yuw_00001_daily_bonus_1,202511020,event_yuw_00001_daily_bonus,1,event_yuw_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_yuw_00001_daily_bonus_2,202511020,event_yuw_00001_daily_bonus,2,event_yuw_00001_daily_bonus_2,1,コイン
e,event_yuw_00001_daily_bonus_3,202511020,event_yuw_00001_daily_bonus,3,event_yuw_00001_daily_bonus_3,1,プリズム
e,event_yuw_00001_daily_bonus_4,202511020,event_yuw_00001_daily_bonus,4,event_yuw_00001_daily_bonus_4,1,カラーメモリー・レッド
e,event_yuw_00001_daily_bonus_5,202511020,event_yuw_00001_daily_bonus,5,event_yuw_00001_daily_bonus_5,1,カラーメモリー・ブルー
e,event_yuw_00001_daily_bonus_6,202511020,event_yuw_00001_daily_bonus,6,event_yuw_00001_daily_bonus_6,1,メモリーフラグメント・初級
e,event_yuw_00001_daily_bonus_7,202511020,event_yuw_00001_daily_bonus,7,event_yuw_00001_daily_bonus_7,1,プリズム
e,event_yuw_00001_daily_bonus_8,202511020,event_yuw_00001_daily_bonus,8,event_yuw_00001_daily_bonus_8,1,スペシャルガシャチケット
e,event_yuw_00001_daily_bonus_9,202511020,event_yuw_00001_daily_bonus,9,event_yuw_00001_daily_bonus_9,1,メモリーフラグメント・中級
e,event_yuw_00001_daily_bonus_10,202511020,event_yuw_00001_daily_bonus,10,event_yuw_00001_daily_bonus_10,1,カラーメモリー・レッド
e,event_yuw_00001_daily_bonus_11,202511020,event_yuw_00001_daily_bonus,11,event_yuw_00001_daily_bonus_11,1,カラーメモリー・ブルー
e,event_yuw_00001_daily_bonus_12,202511020,event_yuw_00001_daily_bonus,12,event_yuw_00001_daily_bonus_12,1,スペシャルガシャチケット
e,event_yuw_00001_daily_bonus_13,202511020,event_yuw_00001_daily_bonus,13,event_yuw_00001_daily_bonus_13,1,ピックアップガシャチケット
e,event_sur_00001_daily_bonus_1,202512010,event_sur_00001_daily_bonus,1,event_sur_00001_daily_bonus_1,1,ピックアップガシャチケット
e,event_sur_00001_daily_bonus_2,202512010,event_sur_00001_daily_bonus,2,event_sur_00001_daily_bonus_2,1,コイン
e,event_sur_00001_daily_bonus_3,202512010,event_sur_00001_daily_bonus,3,event_sur_00001_daily_bonus_3,1,プリズム
e,event_sur_00001_daily_bonus_4,202512010,event_sur_00001_daily_bonus,4,event_sur_00001_daily_bonus_4,1,スペシャルガシャチケット
e,event_sur_00001_daily_bonus_5,202512010,event_sur_00001_daily_bonus,5,event_sur_00001_daily_bonus_5,1,カラーメモリー・レッド
e,event_sur_00001_daily_bonus_6,202512010,event_sur_00001_daily_bonus,6,event_sur_00001_daily_bonus_6,1,カラーメモリー・イエロー
e,event_sur_00001_daily_bonus_7,202512010,event_sur_00001_daily_bonus,7,event_sur_00001_daily_bonus_7,1,プリズム
e,event_sur_00001_daily_bonus_8,202512010,event_sur_00001_daily_bonus,8,event_sur_00001_daily_bonus_8,1,ピックアップガシャチケット
e,event_sur_00001_daily_bonus_9,202512010,event_sur_00001_daily_bonus,9,event_sur_00001_daily_bonus_9,1,メモリーフラグメント・初級
e,event_sur_00001_daily_bonus_10,202512010,event_sur_00001_daily_bonus,10,event_sur_00001_daily_bonus_10,1,メモリーフラグメント・中級
e,event_sur_00001_daily_bonus_11,202512010,event_sur_00001_daily_bonus,11,event_sur_00001_daily_bonus_11,1,プリズム
e,event_sur_00001_daily_bonus_12,202512010,event_sur_00001_daily_bonus,12,event_sur_00001_daily_bonus_12,1,ピックアップガシャチケット
e,event_sur_00001_daily_bonus_13,202512010,event_sur_00001_daily_bonus,13,event_sur_00001_daily_bonus_13,1,メモリーフラグメント・初級
e,event_sur_00001_daily_bonus_14,202512010,event_sur_00001_daily_bonus,14,event_sur_00001_daily_bonus_14,1,コイン
e,event_sur_00001_daily_bonus_15,202512010,event_sur_00001_daily_bonus,15,event_sur_00001_daily_bonus_15,1,プリズム
e,event_sur_00001_daily_bonus_16,202512010,event_sur_00001_daily_bonus,16,event_sur_00001_daily_bonus_16,1,スペシャルガシャチケット
e,event_sur_00001_daily_bonus_17,202512010,event_sur_00001_daily_bonus,17,event_sur_00001_daily_bonus_17,1,メモリーフラグメント・初級
e,event_sur_00001_daily_bonus_18,202512010,event_sur_00001_daily_bonus,18,event_sur_00001_daily_bonus_18,1,コイン
e,event_sur_00001_daily_bonus_19,202512010,event_sur_00001_daily_bonus,19,event_sur_00001_daily_bonus_19,1,カラーメモリー・レッド
e,event_sur_00001_daily_bonus_20,202512010,event_sur_00001_daily_bonus,20,event_sur_00001_daily_bonus_20,1,カラーメモリー・イエロー
e,event_sur_00001_daily_bonus_21,202512010,event_sur_00001_daily_bonus,21,event_sur_00001_daily_bonus_21,1,メモリーフラグメント・初級
e,event_sur_00001_daily_bonus_22,202512010,event_sur_00001_daily_bonus,22,event_sur_00001_daily_bonus_22,1,メモリーフラグメント・中級
e,event_sur_00001_daily_bonus_23,202512010,event_sur_00001_daily_bonus,23,event_sur_00001_daily_bonus_23,1,メモリーフラグメント・初級
e,event_sur_00001_daily_bonus_24,202512010,event_sur_00001_daily_bonus,24,event_sur_00001_daily_bonus_24,1,プリズム
e,event_osh_00001_daily_bonus_01,202512020,event_osh_00001_daily_bonus,1,event_osh_00001_daily_bonus_01,1,【推しの子】SSR確定ガシャ
e,event_osh_00001_daily_bonus_02,202512020,event_osh_00001_daily_bonus,2,event_osh_00001_daily_bonus_02,1,プリズム
e,event_osh_00001_daily_bonus_03,202512020,event_osh_00001_daily_bonus,3,event_osh_00001_daily_bonus_03,1,いいジャンメダル【赤】
e,event_osh_00001_daily_bonus_04,202512020,event_osh_00001_daily_bonus,4,event_osh_00001_daily_bonus_04,1,ピックアップガシャチケット
e,event_osh_00001_daily_bonus_05,202512020,event_osh_00001_daily_bonus,5,event_osh_00001_daily_bonus_05,1,プリズム
e,event_osh_00001_daily_bonus_06,202512020,event_osh_00001_daily_bonus,6,event_osh_00001_daily_bonus_06,1,メモリーフラグメント・初級
e,event_osh_00001_daily_bonus_07,202512020,event_osh_00001_daily_bonus,7,event_osh_00001_daily_bonus_07,1,ピックアップガシャチケット
e,event_osh_00001_daily_bonus_08,202512020,event_osh_00001_daily_bonus,8,event_osh_00001_daily_bonus_08,1,プリズム
e,event_osh_00001_daily_bonus_09,202512020,event_osh_00001_daily_bonus,9,event_osh_00001_daily_bonus_09,1,コイン
e,event_osh_00001_daily_bonus_10,202512020,event_osh_00001_daily_bonus,10,event_osh_00001_daily_bonus_10,1,スペシャルガシャチケット
e,event_osh_00001_daily_bonus_11,202512020,event_osh_00001_daily_bonus,11,event_osh_00001_daily_bonus_11,1,メモリーフラグメント・中級
e,event_osh_00001_daily_bonus_12,202512020,event_osh_00001_daily_bonus,12,event_osh_00001_daily_bonus_12,1,コイン
e,event_osh_00001_daily_bonus_13,202512020,event_osh_00001_daily_bonus,13,event_osh_00001_daily_bonus_13,1,プリズム
e,event_osh_00001_daily_bonus_14,202512020,event_osh_00001_daily_bonus,14,event_osh_00001_daily_bonus_14,1,メモリーフラグメント・初級
e,event_osh_00001_daily_bonus_15,202512020,event_osh_00001_daily_bonus,15,event_osh_00001_daily_bonus_15,1,スペシャルガシャチケット
e,event_jig_00001_daily_bonus_01,202601010,event_jig_00001_daily_bonus,1,event_jig_00001_daily_bonus_01,1,ピックアップガシャチケット
e,event_jig_00001_daily_bonus_02,202601010,event_jig_00001_daily_bonus,2,event_jig_00001_daily_bonus_02,1,コイン
e,event_jig_00001_daily_bonus_03,202601010,event_jig_00001_daily_bonus,3,event_jig_00001_daily_bonus_03,1,プリズム
e,event_jig_00001_daily_bonus_04,202601010,event_jig_00001_daily_bonus,4,event_jig_00001_daily_bonus_04,1,メモリーフラグメント・初級
e,event_jig_00001_daily_bonus_05,202601010,event_jig_00001_daily_bonus,5,event_jig_00001_daily_bonus_05,1,メモリーフラグメント・中級
e,event_jig_00001_daily_bonus_06,202601010,event_jig_00001_daily_bonus,6,event_jig_00001_daily_bonus_06,1,カラーメモリー・グリーン
e,event_jig_00001_daily_bonus_07,202601010,event_jig_00001_daily_bonus,7,event_jig_00001_daily_bonus_07,1,スペシャルガシャチケット
e,event_jig_00001_daily_bonus_08,202601010,event_jig_00001_daily_bonus,8,event_jig_00001_daily_bonus_08,1,コイン
e,event_jig_00001_daily_bonus_09,202601010,event_jig_00001_daily_bonus,9,event_jig_00001_daily_bonus_09,1,プリズム
e,event_jig_00001_daily_bonus_10,202601010,event_jig_00001_daily_bonus,10,event_jig_00001_daily_bonus_10,1,メモリーフラグメント・初級
e,event_jig_00001_daily_bonus_11,202601010,event_jig_00001_daily_bonus,11,event_jig_00001_daily_bonus_11,1,カラーメモリー・レッド
e,event_jig_00001_daily_bonus_12,202601010,event_jig_00001_daily_bonus,12,event_jig_00001_daily_bonus_12,1,プリズム
e,event_jig_00001_daily_bonus_13,202601010,event_jig_00001_daily_bonus,13,event_jig_00001_daily_bonus_13,1,カラーメモリー・グリーン
e,event_jig_00001_daily_bonus_14,202601010,event_jig_00001_daily_bonus,14,event_jig_00001_daily_bonus_14,1,コイン
e,event_jig_00001_daily_bonus_15,202601010,event_jig_00001_daily_bonus,15,event_jig_00001_daily_bonus_15,1,カラーメモリー・レッド
e,event_jig_00001_daily_bonus_16,202601010,event_jig_00001_daily_bonus,16,event_jig_00001_daily_bonus_16,1,スペシャルガシャチケット
e,event_jig_00001_daily_bonus_17,202601010,event_jig_00001_daily_bonus,17,event_jig_00001_daily_bonus_17,1,ピックアップガシャチケット
e,event_you_00001_daily_bonus_01,202602010,event_you_00001_daily_bonus,1,event_you_00001_daily_bonus_01,1,ピックアップガシャチケット
e,event_you_00001_daily_bonus_02,202602010,event_you_00001_daily_bonus,2,event_you_00001_daily_bonus_02,1,コイン
e,event_you_00001_daily_bonus_03,202602010,event_you_00001_daily_bonus,3,event_you_00001_daily_bonus_03,1,プリズム
e,event_you_00001_daily_bonus_04,202602010,event_you_00001_daily_bonus,4,event_you_00001_daily_bonus_04,1,メモリーフラグメント・初級
e,event_you_00001_daily_bonus_05,202602010,event_you_00001_daily_bonus,5,event_you_00001_daily_bonus_05,1,メモリーフラグメント・中級
e,event_you_00001_daily_bonus_06,202602010,event_you_00001_daily_bonus,6,event_you_00001_daily_bonus_06,1,カラーメモリー・イエロー
e,event_you_00001_daily_bonus_07,202602010,event_you_00001_daily_bonus,7,event_you_00001_daily_bonus_07,1,スペシャルガシャチケット
e,event_you_00001_daily_bonus_08,202602010,event_you_00001_daily_bonus,8,event_you_00001_daily_bonus_08,1,コイン
e,event_you_00001_daily_bonus_09,202602010,event_you_00001_daily_bonus,9,event_you_00001_daily_bonus_09,1,プリズム
e,event_you_00001_daily_bonus_10,202602010,event_you_00001_daily_bonus,10,event_you_00001_daily_bonus_10,1,メモリーフラグメント・初級
e,event_you_00001_daily_bonus_11,202602010,event_you_00001_daily_bonus,11,event_you_00001_daily_bonus_11,1,カラーメモリー・レッド
e,event_you_00001_daily_bonus_12,202602010,event_you_00001_daily_bonus,12,event_you_00001_daily_bonus_12,1,コイン
e,event_you_00001_daily_bonus_13,202602010,event_you_00001_daily_bonus,13,event_you_00001_daily_bonus_13,1,ピックアップガシャチケット
e,event_you_00001_daily_bonus_14,202602010,event_you_00001_daily_bonus,14,event_you_00001_daily_bonus_14,1,スペシャルガシャチケット
e,event_kim_00001_daily_bonus_01,202602020,event_kim_00001_daily_bonus,1,event_kim_00001_daily_bonus_01,1,ピックアップガシャチケット
e,event_kim_00001_daily_bonus_02,202602020,event_kim_00001_daily_bonus,2,event_kim_00001_daily_bonus_02,1,コイン
e,event_kim_00001_daily_bonus_03,202602020,event_kim_00001_daily_bonus,3,event_kim_00001_daily_bonus_03,1,プリズム
e,event_kim_00001_daily_bonus_04,202602020,event_kim_00001_daily_bonus,4,event_kim_00001_daily_bonus_04,1,メモリーフラグメント・初級
e,event_kim_00001_daily_bonus_05,202602020,event_kim_00001_daily_bonus,5,event_kim_00001_daily_bonus_05,1,カラーメモリー・イエロー
e,event_kim_00001_daily_bonus_06,202602020,event_kim_00001_daily_bonus,6,event_kim_00001_daily_bonus_06,1,カラーメモリー・ブルー
e,event_kim_00001_daily_bonus_07,202602020,event_kim_00001_daily_bonus,7,event_kim_00001_daily_bonus_07,1,スペシャルガシャチケット
e,event_kim_00001_daily_bonus_08,202602020,event_kim_00001_daily_bonus,8,event_kim_00001_daily_bonus_08,1,コイン
e,event_kim_00001_daily_bonus_09,202602020,event_kim_00001_daily_bonus,9,event_kim_00001_daily_bonus_09,1,プリズム
e,event_kim_00001_daily_bonus_10,202602020,event_kim_00001_daily_bonus,10,event_kim_00001_daily_bonus_10,1,カラーメモリー・レッド
e,event_kim_00001_daily_bonus_11,202602020,event_kim_00001_daily_bonus,11,event_kim_00001_daily_bonus_11,1,カラーメモリー・グリーン
e,event_kim_00001_daily_bonus_12,202602020,event_kim_00001_daily_bonus,12,event_kim_00001_daily_bonus_12,1,メモリーフラグメント・中級
e,event_kim_00001_daily_bonus_13,202602020,event_kim_00001_daily_bonus,13,event_kim_00001_daily_bonus_13,1,ピックアップガシャチケット
e,event_kim_00001_daily_bonus_14,202602020,event_kim_00001_daily_bonus,14,event_kim_00001_daily_bonus_14,1,スペシャルガシャチケット
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventDailyBonusSchedule.csv -->
## ./projects/glow-masterdata/MstMissionEventDailyBonusSchedule.csv

```csv
ENABLE,id,release_key,mst_event_id,start_at,end_at
e,event_kai_00001_daily_bonus,202509010,event_kai_00001,"2025-09-24 14:00:00","2025-10-06 03:59:59"
e,event_spy_00001_daily_bonus,202510010,event_spy_00001,"2025-10-06 15:00:00","2025-10-22 03:59:59"
e,event_dan_00001_daily_bonus,202510020,event_dan_00001,"2025-10-22 15:00:00","2025-11-06 03:59:59"
e,event_mag_00001_daily_bonus,202511010,event_mag_00001,"2025-11-06 15:00:00","2025-11-25 03:59:59"
e,event_yuw_00001_daily_bonus,202511020,event_yuw_00001,"2025-11-25 15:00:00","2025-12-08 03:59:59"
e,event_sur_00001_daily_bonus,202512010,event_sur_00001,"2025-12-08 15:00:00","2026-01-01 03:59:59"
e,event_osh_00001_daily_bonus,202512020,event_osh_00001,"2026-01-01 00:00:00","2026-01-16 03:59:59"
e,event_jig_00001_daily_bonus,202601010,event_jig_00001,"2026-01-16 15:00:00","2026-02-02 03:59:59"
e,event_you_00001_daily_bonus,202602010,event_you_00001,"2026-02-02 15:00:00","2026-02-16 03:59:59"
e,event_kim_00001_daily_bonus,202602020,event_kim_00001,"2026-02-16 15:00:00","2026-03-02 03:59:59"
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventDailyI18n.csv -->
## ./projects/glow-masterdata/MstMissionEventDailyI18n.csv

```csv
ENABLE,release_key,id,mst_mission_event_daily_id,language
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventDependency.csv -->
## ./projects/glow-masterdata/MstMissionEventDependency.csv

```csv
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order,備考
e,1,202509010,event_kai_00001_1,event_kai_00001_1,1,
e,2,202509010,event_kai_00001_1,event_kai_00001_2,2,
e,3,202509010,event_kai_00001_1,event_kai_00001_3,3,
e,4,202509010,event_kai_00001_1,event_kai_00001_4,4,
e,5,202509010,event_kai_00001_5,event_kai_00001_5,1,
e,6,202509010,event_kai_00001_5,event_kai_00001_6,2,
e,7,202509010,event_kai_00001_5,event_kai_00001_7,3,
e,8,202509010,event_kai_00001_8,event_kai_00001_8,1,
e,9,202509010,event_kai_00001_8,event_kai_00001_9,2,
e,10,202509010,event_kai_00001_8,event_kai_00001_10,3,
e,11,202509010,event_kai_00001_8,event_kai_00001_11,4,
e,12,202509010,event_kai_00001_12,event_kai_00001_12,1,
e,13,202509010,event_kai_00001_12,event_kai_00001_13,2,
e,14,202509010,event_kai_00001_12,event_kai_00001_14,3,
e,15,202509010,event_kai_00001_19,event_kai_00001_19,1,
e,16,202509010,event_kai_00001_19,event_kai_00001_20,2,
e,17,202509010,event_kai_00001_19,event_kai_00001_21,3,
e,18,202509010,event_kai_00001_19,event_kai_00001_22,4,
e,19,202509010,event_kai_00001_19,event_kai_00001_23,5,
e,20,202509010,event_kai_00001_19,event_kai_00001_24,6,
e,21,202510010,event_spy_00001_1,event_spy_00001_1,1,
e,22,202510010,event_spy_00001_1,event_spy_00001_2,2,
e,23,202510010,event_spy_00001_1,event_spy_00001_3,3,
e,24,202510010,event_spy_00001_1,event_spy_00001_4,4,
e,25,202510010,event_spy_00001_5,event_spy_00001_5,1,
e,26,202510010,event_spy_00001_5,event_spy_00001_6,2,
e,27,202510010,event_spy_00001_5,event_spy_00001_7,3,
e,28,202510010,event_spy_00001_8,event_spy_00001_8,1,
e,29,202510010,event_spy_00001_8,event_spy_00001_9,2,
e,30,202510010,event_spy_00001_8,event_spy_00001_10,3,
e,31,202510010,event_spy_00001_8,event_spy_00001_11,4,
e,32,202510010,event_spy_00001_12,event_spy_00001_12,1,
e,33,202510010,event_spy_00001_12,event_spy_00001_13,2,
e,34,202510010,event_spy_00001_12,event_spy_00001_14,3,
e,35,202510010,event_spy_00001_19,event_spy_00001_19,1,
e,36,202510010,event_spy_00001_19,event_spy_00001_20,2,
e,37,202510010,event_spy_00001_19,event_spy_00001_21,3,
e,38,202510010,event_spy_00001_19,event_spy_00001_22,4,
e,39,202510010,event_spy_00001_19,event_spy_00001_23,5,
e,40,202510010,event_spy_00001_19,event_spy_00001_24,6,
e,41,202510020,event_dan_00001_1,event_dan_00001_1,1,
e,42,202510020,event_dan_00001_1,event_dan_00001_2,2,
e,43,202510020,event_dan_00001_1,event_dan_00001_3,3,
e,44,202510020,event_dan_00001_1,event_dan_00001_4,4,
e,45,202510020,event_dan_00001_5,event_dan_00001_5,1,
e,46,202510020,event_dan_00001_5,event_dan_00001_6,2,
e,47,202510020,event_dan_00001_5,event_dan_00001_7,3,
e,48,202510020,event_dan_00001_8,event_dan_00001_8,1,
e,49,202510020,event_dan_00001_8,event_dan_00001_9,2,
e,50,202510020,event_dan_00001_8,event_dan_00001_10,3,
e,51,202510020,event_dan_00001_8,event_dan_00001_11,4,
e,52,202510020,event_dan_00001_12,event_dan_00001_12,1,
e,53,202510020,event_dan_00001_12,event_dan_00001_13,2,
e,54,202510020,event_dan_00001_12,event_dan_00001_14,3,
e,55,202510020,event_dan_00001_19,event_dan_00001_19,1,
e,56,202510020,event_dan_00001_19,event_dan_00001_20,2,
e,57,202510020,event_dan_00001_19,event_dan_00001_21,3,
e,58,202510020,event_dan_00001_19,event_dan_00001_22,4,
e,59,202510020,event_dan_00001_19,event_dan_00001_23,5,
e,60,202510020,event_dan_00001_19,event_dan_00001_24,6,
e,61,202511010,event_mag_00001_1,event_mag_00001_1,1,
e,62,202511010,event_mag_00001_1,event_mag_00001_2,2,
e,63,202511010,event_mag_00001_1,event_mag_00001_3,3,
e,64,202511010,event_mag_00001_1,event_mag_00001_4,4,
e,65,202511010,event_mag_00001_5,event_mag_00001_5,1,
e,66,202511010,event_mag_00001_5,event_mag_00001_6,2,
e,67,202511010,event_mag_00001_5,event_mag_00001_7,3,
e,68,202511010,event_mag_00001_8,event_mag_00001_8,1,
e,69,202511010,event_mag_00001_8,event_mag_00001_9,2,
e,70,202511010,event_mag_00001_8,event_mag_00001_10,3,
e,71,202511010,event_mag_00001_8,event_mag_00001_11,4,
e,72,202511010,event_mag_00001_12,event_mag_00001_12,1,
e,73,202511010,event_mag_00001_12,event_mag_00001_13,2,
e,74,202511010,event_mag_00001_12,event_mag_00001_14,3,
e,75,202511010,event_mag_00001_19,event_mag_00001_19,1,
e,76,202511010,event_mag_00001_19,event_mag_00001_20,2,
e,77,202511010,event_mag_00001_19,event_mag_00001_21,3,
e,78,202511010,event_mag_00001_19,event_mag_00001_22,4,
e,79,202511010,event_mag_00001_19,event_mag_00001_23,5,
e,80,202511010,event_mag_00001_19,event_mag_00001_24,6,
e,81,202511020,event_yuw_00001_1,event_yuw_00001_1,1,
e,82,202511020,event_yuw_00001_1,event_yuw_00001_2,2,
e,83,202511020,event_yuw_00001_1,event_yuw_00001_3,3,
e,84,202511020,event_yuw_00001_1,event_yuw_00001_4,4,
e,85,202511020,event_yuw_00001_5,event_yuw_00001_5,1,
e,86,202511020,event_yuw_00001_5,event_yuw_00001_6,2,
e,87,202511020,event_yuw_00001_5,event_yuw_00001_7,3,
e,88,202511020,event_yuw_00001_5,event_yuw_00001_8,4,
e,89,202511020,event_yuw_00001_5,event_yuw_00001_9,5,
e,90,202511020,event_yuw_00001_5,event_yuw_00001_10,6,
e,91,202511020,event_yuw_00001_5,event_yuw_00001_11,7,
e,92,202511020,event_yuw_00001_12,event_yuw_00001_12,1,
e,93,202511020,event_yuw_00001_12,event_yuw_00001_13,2,
e,94,202511020,event_yuw_00001_12,event_yuw_00001_14,3,
e,95,202511020,event_yuw_00001_12,event_yuw_00001_15,4,
e,96,202511020,event_yuw_00001_16,event_yuw_00001_16,1,
e,97,202511020,event_yuw_00001_16,event_yuw_00001_17,2,
e,98,202511020,event_yuw_00001_16,event_yuw_00001_18,3,
e,99,202511020,event_yuw_00001_16,event_yuw_00001_19,4,
e,100,202511020,event_yuw_00001_16,event_yuw_00001_20,5,
e,101,202511020,event_yuw_00001_16,event_yuw_00001_21,6,
e,102,202511020,event_yuw_00001_16,event_yuw_00001_22,7,
e,103,202511020,event_yuw_00001_27,event_yuw_00001_27,1,
e,104,202511020,event_yuw_00001_27,event_yuw_00001_28,2,
e,105,202511020,event_yuw_00001_27,event_yuw_00001_29,3,
e,106,202511020,event_yuw_00001_27,event_yuw_00001_30,4,
e,107,202511020,event_yuw_00001_27,event_yuw_00001_31,5,
e,108,202511020,event_yuw_00001_27,event_yuw_00001_32,6,
e,109,202511020,event_yuw_00001_27,event_yuw_00001_33,7,
e,110,202511020,event_yuw_00001_27,event_yuw_00001_34,8,
e,111,202511020,event_yuw_00001_27,event_yuw_00001_35,9,
e,112,202511020,event_yuw_00001_27,event_yuw_00001_36,10,
e,113,202511020,event_yuw_00001_27,event_yuw_00001_37,11,
e,114,202511020,event_yuw_00001_27,event_yuw_00001_38,12,
e,115,202511020,event_yuw_00001_27,event_yuw_00001_39,13,
e,116,202512010,event_sur_00001_1,event_sur_00001_1,1,
e,117,202512010,event_sur_00001_1,event_sur_00001_2,2,
e,118,202512010,event_sur_00001_1,event_sur_00001_3,3,
e,119,202512010,event_sur_00001_1,event_sur_00001_4,4,
e,120,202512010,event_sur_00001_5,event_sur_00001_5,1,
e,121,202512010,event_sur_00001_5,event_sur_00001_6,2,
e,122,202512010,event_sur_00001_5,event_sur_00001_7,3,
e,123,202512010,event_sur_00001_5,event_sur_00001_8,4,
e,124,202512010,event_sur_00001_5,event_sur_00001_9,5,
e,125,202512010,event_sur_00001_5,event_sur_00001_10,6,
e,126,202512010,event_sur_00001_5,event_sur_00001_11,7,
e,127,202512010,event_sur_00001_12,event_sur_00001_12,1,
e,128,202512010,event_sur_00001_12,event_sur_00001_13,2,
e,129,202512010,event_sur_00001_12,event_sur_00001_14,3,
e,130,202512010,event_sur_00001_12,event_sur_00001_15,4,
e,131,202512010,event_sur_00001_16,event_sur_00001_16,1,
e,132,202512010,event_sur_00001_16,event_sur_00001_17,2,
e,133,202512010,event_sur_00001_16,event_sur_00001_18,3,
e,134,202512010,event_sur_00001_16,event_sur_00001_19,4,
e,135,202512010,event_sur_00001_16,event_sur_00001_20,5,
e,136,202512010,event_sur_00001_16,event_sur_00001_21,6,
e,137,202512010,event_sur_00001_16,event_sur_00001_22,7,
e,138,202512010,event_sur_00001_27,event_sur_00001_27,1,
e,139,202512010,event_sur_00001_27,event_sur_00001_28,2,
e,140,202512010,event_sur_00001_27,event_sur_00001_29,3,
e,141,202512010,event_sur_00001_27,event_sur_00001_30,4,
e,142,202512010,event_sur_00001_27,event_sur_00001_31,5,
e,143,202512010,event_sur_00001_27,event_sur_00001_32,6,
e,144,202512010,event_sur_00001_27,event_sur_00001_33,7,
e,145,202512010,event_sur_00001_27,event_sur_00001_34,8,
e,146,202512010,event_sur_00001_27,event_sur_00001_35,9,
e,147,202512010,event_sur_00001_27,event_sur_00001_36,10,
e,148,202512010,event_sur_00001_27,event_sur_00001_37,11,
e,149,202512010,event_sur_00001_27,event_sur_00001_38,12,
e,150,202512010,event_sur_00001_27,event_sur_00001_39,13,
e,151,202601010,event_jig_00001_1,event_jig_00001_1,1,
e,152,202601010,event_jig_00001_1,event_jig_00001_2,2,
e,153,202601010,event_jig_00001_1,event_jig_00001_3,3,
e,154,202601010,event_jig_00001_1,event_jig_00001_4,4,
e,155,202601010,event_jig_00001_5,event_jig_00001_5,1,
e,156,202601010,event_jig_00001_5,event_jig_00001_6,2,
e,157,202601010,event_jig_00001_5,event_jig_00001_7,3,
e,158,202601010,event_jig_00001_5,event_jig_00001_8,4,
e,159,202601010,event_jig_00001_5,event_jig_00001_9,5,
e,160,202601010,event_jig_00001_5,event_jig_00001_10,6,
e,161,202601010,event_jig_00001_5,event_jig_00001_11,7,
e,162,202601010,event_jig_00001_12,event_jig_00001_12,1,
e,163,202601010,event_jig_00001_12,event_jig_00001_13,2,
e,164,202601010,event_jig_00001_12,event_jig_00001_14,3,
e,165,202601010,event_jig_00001_12,event_jig_00001_15,4,
e,166,202601010,event_jig_00001_16,event_jig_00001_16,1,
e,167,202601010,event_jig_00001_16,event_jig_00001_17,2,
e,168,202601010,event_jig_00001_16,event_jig_00001_18,3,
e,169,202601010,event_jig_00001_16,event_jig_00001_19,4,
e,170,202601010,event_jig_00001_16,event_jig_00001_20,5,
e,171,202601010,event_jig_00001_16,event_jig_00001_21,6,
e,172,202601010,event_jig_00001_16,event_jig_00001_22,7,
e,173,202601010,event_jig_00001_27,event_jig_00001_27,1,
e,174,202601010,event_jig_00001_27,event_jig_00001_28,2,
e,175,202601010,event_jig_00001_27,event_jig_00001_29,3,
e,176,202601010,event_jig_00001_27,event_jig_00001_30,4,
e,177,202601010,event_jig_00001_27,event_jig_00001_31,5,
e,178,202601010,event_jig_00001_27,event_jig_00001_32,6,
e,179,202601010,event_jig_00001_27,event_jig_00001_33,7,
e,180,202601010,event_jig_00001_27,event_jig_00001_34,8,
e,181,202601010,event_jig_00001_27,event_jig_00001_35,9,
e,182,202601010,event_jig_00001_27,event_jig_00001_36,10,
e,183,202601010,event_jig_00001_27,event_jig_00001_37,11,
e,184,202601010,event_jig_00001_27,event_jig_00001_38,12,
e,185,202601010,event_jig_00001_27,event_jig_00001_39,13,
e,186,202601010,event_jig_00001_27,event_jig_00001_40,14,
e,187,202601010,event_jig_00001_27,event_jig_00001_41,15,
e,188,202601010,event_jig_00001_27,event_jig_00001_42,16,
e,189,202601010,event_jig_00001_27,event_jig_00001_43,17,
e,190,202512020,event_osh_00001_1,event_osh_00001_1,1,
e,191,202512020,event_osh_00001_1,event_osh_00001_2,2,
e,192,202512020,event_osh_00001_1,event_osh_00001_3,3,
e,193,202512020,event_osh_00001_1,event_osh_00001_4,4,
e,194,202512020,event_osh_00001_1,event_osh_00001_5,5,
e,195,202512020,event_osh_00001_1,event_osh_00001_6,6,
e,196,202512020,event_osh_00001_1,event_osh_00001_7,7,
e,197,202512020,event_osh_00001_1,event_osh_00001_8,8,
e,198,202512020,event_osh_00001_1,event_osh_00001_9,9,
e,199,202512020,event_osh_00001_1,event_osh_00001_10,10,
e,200,202512020,event_osh_00001_1,event_osh_00001_11,11,
e,201,202512020,event_osh_00001_1,event_osh_00001_12,12,
e,202,202512020,event_osh_00001_1,event_osh_00001_13,13,
e,203,202512020,event_osh_00001_1,event_osh_00001_14,14,
e,204,202512020,event_osh_00001_1,event_osh_00001_15,15,
e,205,202512020,event_osh_00001_1,event_osh_00001_16,16,
e,206,202512020,event_osh_00001_1,event_osh_00001_17,17,
e,207,202512020,event_osh_00001_1,event_osh_00001_18,18,
e,208,202512020,event_osh_00001_1,event_osh_00001_19,19,
e,209,202512020,event_osh_00001_1,event_osh_00001_20,20,
e,210,202512020,event_osh_00001_1,event_osh_00001_21,21,
e,211,202512020,event_osh_00001_1,event_osh_00001_22,22,
e,212,202512020,event_osh_00001_1,event_osh_00001_23,23,
e,213,202512020,event_osh_00001_1,event_osh_00001_24,24,
e,214,202512020,event_osh_00001_1,event_osh_00001_25,25,
e,215,202512020,event_osh_00001_1,event_osh_00001_26,26,
e,216,202512020,event_osh_00001_1,event_osh_00001_27,27,
e,217,202512020,event_osh_00001_28,event_osh_00001_28,1,
e,218,202512020,event_osh_00001_28,event_osh_00001_29,2,
e,219,202512020,event_osh_00001_28,event_osh_00001_30,3,
e,220,202512020,event_osh_00001_28,event_osh_00001_31,4,
e,221,202512020,event_osh_00001_28,event_osh_00001_32,5,
e,222,202512020,event_osh_00001_33,event_osh_00001_33,1,
e,223,202512020,event_osh_00001_33,event_osh_00001_34,2,
e,224,202512020,event_osh_00001_33,event_osh_00001_35,3,
e,225,202512020,event_osh_00001_37,event_osh_00001_37,1,
e,226,202512020,event_osh_00001_37,event_osh_00001_38,2,
e,227,202512020,event_osh_00001_37,event_osh_00001_39,3,
e,228,202512020,event_osh_00001_37,event_osh_00001_40,4,
e,229,202512020,event_osh_00001_37,event_osh_00001_41,5,
e,230,202512020,event_osh_00001_43,event_osh_00001_43,1,
e,231,202512020,event_osh_00001_43,event_osh_00001_44,2,
e,232,202512020,event_osh_00001_43,event_osh_00001_45,3,
e,233,202512020,event_osh_00001_43,event_osh_00001_46,4,
e,234,202602010,event_you_00001_1,event_you_00001_1,1,
e,235,202602010,event_you_00001_1,event_you_00001_2,2,
e,236,202602010,event_you_00001_1,event_you_00001_3,3,
e,237,202602010,event_you_00001_1,event_you_00001_4,4,
e,238,202602010,event_you_00001_5,event_you_00001_5,1,
e,239,202602010,event_you_00001_5,event_you_00001_6,2,
e,240,202602010,event_you_00001_5,event_you_00001_7,3,
e,241,202602010,event_you_00001_5,event_you_00001_8,4,
e,242,202602010,event_you_00001_5,event_you_00001_9,5,
e,243,202602010,event_you_00001_5,event_you_00001_10,6,
e,244,202602010,event_you_00001_5,event_you_00001_11,7,
e,245,202602010,event_you_00001_12,event_you_00001_12,1,
e,246,202602010,event_you_00001_12,event_you_00001_13,2,
e,247,202602010,event_you_00001_12,event_you_00001_14,3,
e,248,202602010,event_you_00001_12,event_you_00001_15,4,
e,249,202602010,event_you_00001_16,event_you_00001_16,1,
e,250,202602010,event_you_00001_16,event_you_00001_17,2,
e,251,202602010,event_you_00001_16,event_you_00001_18,3,
e,252,202602010,event_you_00001_16,event_you_00001_19,4,
e,253,202602010,event_you_00001_16,event_you_00001_20,5,
e,254,202602010,event_you_00001_16,event_you_00001_21,6,
e,255,202602010,event_you_00001_16,event_you_00001_22,7,
e,256,202602010,event_you_00001_27,event_you_00001_27,1,
e,257,202602010,event_you_00001_27,event_you_00001_28,2,
e,258,202602010,event_you_00001_27,event_you_00001_29,3,
e,259,202602010,event_you_00001_27,event_you_00001_30,4,
e,260,202602010,event_you_00001_27,event_you_00001_31,5,
e,261,202602010,event_you_00001_27,event_you_00001_32,6,
e,262,202602010,event_you_00001_27,event_you_00001_33,7,
e,263,202602010,event_you_00001_27,event_you_00001_34,8,
e,264,202602010,event_you_00001_27,event_you_00001_35,9,
e,265,202602010,event_you_00001_27,event_you_00001_36,10,
e,266,202602010,event_you_00001_27,event_you_00001_37,11,
e,267,202602010,event_you_00001_27,event_you_00001_38,12,
e,268,202602010,event_you_00001_27,event_you_00001_39,13,
e,269,202602010,event_you_00001_27,event_you_00001_40,14,
e,270,202602010,event_you_00001_27,event_you_00001_41,15,
e,271,202602010,event_you_00001_27,event_you_00001_42,16,
e,272,202602010,event_you_00001_27,event_you_00001_43,17,
e,273,202602020,event_kim_00001_1,event_kim_00001_1,1,
e,274,202602020,event_kim_00001_1,event_kim_00001_2,2,
e,275,202602020,event_kim_00001_1,event_kim_00001_3,3,
e,276,202602020,event_kim_00001_1,event_kim_00001_4,4,
e,277,202602020,event_kim_00001_1,event_kim_00001_5,5,
e,278,202602020,event_kim_00001_1,event_kim_00001_6,6,
e,279,202602020,event_kim_00001_1,event_kim_00001_7,7,
e,280,202602020,event_kim_00001_1,event_kim_00001_8,8,
e,281,202602020,event_kim_00001_1,event_kim_00001_9,9,
e,282,202602020,event_kim_00001_1,event_kim_00001_10,10,
e,283,202602020,event_kim_00001_1,event_kim_00001_11,11,
e,284,202602020,event_kim_00001_1,event_kim_00001_12,12,
e,285,202602020,event_kim_00001_1,event_kim_00001_13,13,
e,286,202602020,event_kim_00001_1,event_kim_00001_14,14,
e,287,202602020,event_kim_00001_1,event_kim_00001_15,15,
e,288,202602020,event_kim_00001_1,event_kim_00001_16,16,
e,289,202602020,event_kim_00001_1,event_kim_00001_17,17,
e,290,202602020,event_kim_00001_1,event_kim_00001_18,18,
e,291,202602020,event_kim_00001_1,event_kim_00001_19,19,
e,292,202602020,event_kim_00001_1,event_kim_00001_20,20,
e,293,202602020,event_kim_00001_1,event_kim_00001_21,21,
e,294,202602020,event_kim_00001_1,event_kim_00001_22,22,
e,295,202602020,event_kim_00001_27,event_kim_00001_27,1,
e,296,202602020,event_kim_00001_27,event_kim_00001_28,2,
e,297,202602020,event_kim_00001_27,event_kim_00001_29,3,
e,298,202602020,event_kim_00001_27,event_kim_00001_30,4,
e,299,202602020,event_kim_00001_27,event_kim_00001_31,5,
e,300,202602020,event_kim_00001_27,event_kim_00001_32,6,
e,301,202602020,event_kim_00001_27,event_kim_00001_33,7,
e,302,202602020,event_kim_00001_27,event_kim_00001_34,8,
e,303,202602020,event_kim_00001_27,event_kim_00001_35,9,
e,304,202602020,event_kim_00001_27,event_kim_00001_36,10,
e,305,202602020,event_kim_00001_27,event_kim_00001_37,11,
e,306,202602020,event_kim_00001_27,event_kim_00001_38,12,
e,307,202602020,event_kim_00001_27,event_kim_00001_39,13,
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionEventI18n.csv -->
## ./projects/glow-masterdata/MstMissionEventI18n.csv

```csv
ENABLE,release_key,id,mst_mission_event_id,language,description
e,202509010,event_kai_00001_1_ja,event_kai_00001_1,ja,"古橋 伊春 をグレード2まで強化しよう"
e,202509010,event_kai_00001_2_ja,event_kai_00001_2,ja,"古橋 伊春 をグレード3まで強化しよう"
e,202509010,event_kai_00001_3_ja,event_kai_00001_3,ja,"古橋 伊春 をグレード4まで強化しよう"
e,202509010,event_kai_00001_4_ja,event_kai_00001_4,ja,"古橋 伊春 をグレード5まで強化しよう"
e,202509010,event_kai_00001_5_ja,event_kai_00001_5,ja,"古橋 伊春 をLv.20まで強化しよう"
e,202509010,event_kai_00001_6_ja,event_kai_00001_6,ja,"古橋 伊春 をLv.30まで強化しよう"
e,202509010,event_kai_00001_7_ja,event_kai_00001_7,ja,"古橋 伊春 をLv.40まで強化しよう"
e,202509010,event_kai_00001_8_ja,event_kai_00001_8,ja,"四ノ宮 功 をグレード2まで強化しよう"
e,202509010,event_kai_00001_9_ja,event_kai_00001_9,ja,"四ノ宮 功 をグレード3まで強化しよう"
e,202509010,event_kai_00001_10_ja,event_kai_00001_10,ja,"四ノ宮 功 をグレード4まで強化しよう"
e,202509010,event_kai_00001_11_ja,event_kai_00001_11,ja,"四ノ宮 功 をグレード5まで強化しよう"
e,202509010,event_kai_00001_12_ja,event_kai_00001_12,ja,"四ノ宮 功 をLv.20まで強化しよう"
e,202509010,event_kai_00001_13_ja,event_kai_00001_13,ja,"四ノ宮 功 をLv.30まで強化しよう"
e,202509010,event_kai_00001_14_ja,event_kai_00001_14,ja,"四ノ宮 功 をLv.40まで強化しよう"
e,202509010,event_kai_00001_15_ja,event_kai_00001_15,ja,"ストーリークエスト「気に入らねェ 気に入らねェ」をクリアしよう"
e,202509010,event_kai_00001_16_ja,event_kai_00001_16,ja,ストーリークエスト「怪獣８号の引き渡しを命ずる」をクリアしよう
e,202509010,event_kai_00001_17_ja,event_kai_00001_17,ja,"チャレンジクエスト「戦場で 力を 示してみせろ ヒヨコども」をクリアしよう"
e,202509010,event_kai_00001_18_ja,event_kai_00001_18,ja,高難易度クエスト「クラス『大怪獣』」をクリアしよう
e,202509010,event_kai_00001_19_ja,event_kai_00001_19,ja,敵を10体撃破しよう
e,202509010,event_kai_00001_20_ja,event_kai_00001_20,ja,敵を20体撃破しよう
e,202509010,event_kai_00001_21_ja,event_kai_00001_21,ja,敵を30体撃破しよう
e,202509010,event_kai_00001_22_ja,event_kai_00001_22,ja,敵を40体撃破しよう
e,202509010,event_kai_00001_23_ja,event_kai_00001_23,ja,敵を50体撃破しよう
e,202509010,event_kai_00001_24_ja,event_kai_00001_24,ja,敵を100体撃破しよう
e,999999999,event_kai_00001_25_ja,event_kai_00001_25,ja,"[テスト]古橋 伊春を連れてステージに10回挑戦しよう"
e,999999999,event_kai_00001_26_ja,event_kai_00001_26,ja,"[テスト]古橋 伊春を連れてステージを10回クリアしよう"
e,999999999,event_kai_00001_27_ja,event_kai_00001_27,ja,"[テスト]古橋 伊春を連れてストーリークエスト「気に入らねェ 気に入らねェ」の1話に10回挑戦しよう"
e,999999999,event_kai_00001_28_ja,event_kai_00001_28,ja,"[テスト]古橋 伊春を連れてストーリークエスト「気に入らねェ 気に入らねェ」の1話を10回クリアしよう"
e,202510010,event_spy_00001_1_ja,event_spy_00001_1,ja,"フランキー・フランクリン をグレード2まで強化しよう"
e,202510010,event_spy_00001_2_ja,event_spy_00001_2,ja,"フランキー・フランクリン をグレード3まで強化しよう"
e,202510010,event_spy_00001_3_ja,event_spy_00001_3,ja,"フランキー・フランクリン をグレード4まで強化しよう"
e,202510010,event_spy_00001_4_ja,event_spy_00001_4,ja,"フランキー・フランクリン をグレード5まで強化しよう"
e,202510010,event_spy_00001_5_ja,event_spy_00001_5,ja,"フランキー・フランクリン をLv.20まで強化しよう"
e,202510010,event_spy_00001_6_ja,event_spy_00001_6,ja,"フランキー・フランクリン をLv.30まで強化しよう"
e,202510010,event_spy_00001_7_ja,event_spy_00001_7,ja,"フランキー・フランクリン をLv.40まで強化しよう"
e,202510010,event_spy_00001_8_ja,event_spy_00001_8,ja,"ダミアン・デズモンド をグレード2まで強化しよう"
e,202510010,event_spy_00001_9_ja,event_spy_00001_9,ja,"ダミアン・デズモンド をグレード3まで強化しよう"
e,202510010,event_spy_00001_10_ja,event_spy_00001_10,ja,"ダミアン・デズモンド をグレード4まで強化しよう"
e,202510010,event_spy_00001_11_ja,event_spy_00001_11,ja,"ダミアン・デズモンド をグレード5まで強化しよう"
e,202510010,event_spy_00001_12_ja,event_spy_00001_12,ja,"ダミアン・デズモンド をLv.20まで強化しよう"
e,202510010,event_spy_00001_13_ja,event_spy_00001_13,ja,"ダミアン・デズモンド をLv.30まで強化しよう"
e,202510010,event_spy_00001_14_ja,event_spy_00001_14,ja,"ダミアン・デズモンド をLv.40まで強化しよう"
e,202510010,event_spy_00001_15_ja,event_spy_00001_15,ja,ストーリークエスト1をクリアしよう
e,202510010,event_spy_00001_16_ja,event_spy_00001_16,ja,ストーリークエスト2をクリアしよう
e,202510010,event_spy_00001_17_ja,event_spy_00001_17,ja,チャレンジクエストをクリアしよう
e,202510010,event_spy_00001_18_ja,event_spy_00001_18,ja,高難易度クエストをクリアしよう
e,202510010,event_spy_00001_19_ja,event_spy_00001_19,ja,敵を10体撃破しよう
e,202510010,event_spy_00001_20_ja,event_spy_00001_20,ja,敵を20体撃破しよう
e,202510010,event_spy_00001_21_ja,event_spy_00001_21,ja,敵を30体撃破しよう
e,202510010,event_spy_00001_22_ja,event_spy_00001_22,ja,敵を40体撃破しよう
e,202510010,event_spy_00001_23_ja,event_spy_00001_23,ja,敵を50体撃破しよう
e,202510010,event_spy_00001_24_ja,event_spy_00001_24,ja,敵を100体撃破しよう
e,202510020,event_dan_00001_1_ja,event_dan_00001_1,ja,"招き猫 ターボババア をグレード2まで強化しよう"
e,202510020,event_dan_00001_2_ja,event_dan_00001_2,ja,"招き猫 ターボババア をグレード3まで強化しよう"
e,202510020,event_dan_00001_3_ja,event_dan_00001_3,ja,"招き猫 ターボババア をグレード4まで強化しよう"
e,202510020,event_dan_00001_4_ja,event_dan_00001_4,ja,"招き猫 ターボババア をグレード5まで強化しよう"
e,202510020,event_dan_00001_5_ja,event_dan_00001_5,ja,"招き猫 ターボババア をLv.20まで強化しよう"
e,202510020,event_dan_00001_6_ja,event_dan_00001_6,ja,"招き猫 ターボババア をLv.30まで強化しよう"
e,202510020,event_dan_00001_7_ja,event_dan_00001_7,ja,"招き猫 ターボババア をLv.40まで強化しよう"
e,202510020,event_dan_00001_8_ja,event_dan_00001_8,ja,"アイラ をグレード2まで強化しよう"
e,202510020,event_dan_00001_9_ja,event_dan_00001_9,ja,"アイラ をグレード3まで強化しよう"
e,202510020,event_dan_00001_10_ja,event_dan_00001_10,ja,"アイラ をグレード4まで強化しよう"
e,202510020,event_dan_00001_11_ja,event_dan_00001_11,ja,"アイラ をグレード5まで強化しよう"
e,202510020,event_dan_00001_12_ja,event_dan_00001_12,ja,"アイラ をLv.20まで強化しよう"
e,202510020,event_dan_00001_13_ja,event_dan_00001_13,ja,"アイラ をLv.30まで強化しよう"
e,202510020,event_dan_00001_14_ja,event_dan_00001_14,ja,"アイラ をLv.40まで強化しよう"
e,202510020,event_dan_00001_15_ja,event_dan_00001_15,ja,ダンダダンいいジャン祭「ストーリークエスト1」をクリアしよう
e,202510020,event_dan_00001_16_ja,event_dan_00001_16,ja,ダンダダンいいジャン祭「ストーリークエスト2」をクリアしよう
e,202510020,event_dan_00001_17_ja,event_dan_00001_17,ja,ダンダダンいいジャン祭「チャレンジクエスト」をクリアしよう
e,202510020,event_dan_00001_18_ja,event_dan_00001_18,ja,ダンダダンいいジャン祭「高難易度クエスト」をクリアしよう
e,202510020,event_dan_00001_19_ja,event_dan_00001_19,ja,敵を10体撃破しよう
e,202510020,event_dan_00001_20_ja,event_dan_00001_20,ja,敵を20体撃破しよう
e,202510020,event_dan_00001_21_ja,event_dan_00001_21,ja,敵を30体撃破しよう
e,202510020,event_dan_00001_22_ja,event_dan_00001_22,ja,敵を40体撃破しよう
e,202510020,event_dan_00001_23_ja,event_dan_00001_23,ja,敵を50体撃破しよう
e,202510020,event_dan_00001_24_ja,event_dan_00001_24,ja,敵を100体撃破しよう
e,202511010,event_mag_00001_1_ja,event_mag_00001_1,ja,"重本 浩司 をグレード2まで強化しよう"
e,202511010,event_mag_00001_2_ja,event_mag_00001_2,ja,"重本 浩司 をグレード3まで強化しよう"
e,202511010,event_mag_00001_3_ja,event_mag_00001_3,ja,"重本 浩司 をグレード4まで強化しよう"
e,202511010,event_mag_00001_4_ja,event_mag_00001_4,ja,"重本 浩司 をグレード5まで強化しよう"
e,202511010,event_mag_00001_5_ja,event_mag_00001_5,ja,"重本 浩司 をLv.20まで強化しよう"
e,202511010,event_mag_00001_6_ja,event_mag_00001_6,ja,"重本 浩司 をLv.30まで強化しよう"
e,202511010,event_mag_00001_7_ja,event_mag_00001_7,ja,"重本 浩司 をLv.40まで強化しよう"
e,202511010,event_mag_00001_8_ja,event_mag_00001_8,ja,"槇野 あかね をグレード2まで強化しよう"
e,202511010,event_mag_00001_9_ja,event_mag_00001_9,ja,"槇野 あかね をグレード3まで強化しよう"
e,202511010,event_mag_00001_10_ja,event_mag_00001_10,ja,"槇野 あかね をグレード4まで強化しよう"
e,202511010,event_mag_00001_11_ja,event_mag_00001_11,ja,"槇野 あかね をグレード5まで強化しよう"
e,202511010,event_mag_00001_12_ja,event_mag_00001_12,ja,"槇野 あかね をLv.20まで強化しよう"
e,202511010,event_mag_00001_13_ja,event_mag_00001_13,ja,"槇野 あかね をLv.30まで強化しよう"
e,202511010,event_mag_00001_14_ja,event_mag_00001_14,ja,"槇野 あかね をLv.40まで強化しよう"
e,202511010,event_mag_00001_15_ja,event_mag_00001_15,ja,ストーリークエスト「うちの美学」をクリアしよう
e,202511010,event_mag_00001_16_ja,event_mag_00001_16,ja,ストーリークエスト「よく見てる」をクリアしよう
e,202511010,event_mag_00001_17_ja,event_mag_00001_17,ja,チャレンジクエスト「色々な魔法少女」をクリアしよう
e,202511010,event_mag_00001_18_ja,event_mag_00001_18,ja,高難易度『「怪異」現象』をクリアしよう
e,202511010,event_mag_00001_19_ja,event_mag_00001_19,ja,敵を10体撃破しよう
e,202511010,event_mag_00001_20_ja,event_mag_00001_20,ja,敵を20体撃破しよう
e,202511010,event_mag_00001_21_ja,event_mag_00001_21,ja,敵を30体撃破しよう
e,202511010,event_mag_00001_22_ja,event_mag_00001_22,ja,敵を40体撃破しよう
e,202511010,event_mag_00001_23_ja,event_mag_00001_23,ja,敵を50体撃破しよう
e,202511010,event_mag_00001_24_ja,event_mag_00001_24,ja,敵を100体撃破しよう
e,202511020,event_yuw_00001_1_ja,event_yuw_00001_1,ja,"753♡ をグレード2まで強化しよう"
e,202511020,event_yuw_00001_2_ja,event_yuw_00001_2,ja,"753♡ をグレード3まで強化しよう"
e,202511020,event_yuw_00001_3_ja,event_yuw_00001_3,ja,"753♡ をグレード4まで強化しよう"
e,202511020,event_yuw_00001_4_ja,event_yuw_00001_4,ja,"753♡ をグレード5まで強化しよう"
e,202511020,event_yuw_00001_5_ja,event_yuw_00001_5,ja,"753♡ をLv.20まで強化しよう"
e,202511020,event_yuw_00001_6_ja,event_yuw_00001_6,ja,"753♡ をLv.30まで強化しよう"
e,202511020,event_yuw_00001_7_ja,event_yuw_00001_7,ja,"753♡ をLv.40まで強化しよう"
e,202511020,event_yuw_00001_8_ja,event_yuw_00001_8,ja,"753♡ をLv.50まで強化しよう"
e,202511020,event_yuw_00001_9_ja,event_yuw_00001_9,ja,"753♡ をLv.60まで強化しよう"
e,202511020,event_yuw_00001_10_ja,event_yuw_00001_10,ja,"753♡ をLv.70まで強化しよう"
e,202511020,event_yuw_00001_11_ja,event_yuw_00001_11,ja,"753♡ をLv.80まで強化しよう"
e,202511020,event_yuw_00001_12_ja,event_yuw_00001_12,ja,"奥村 正宗 をグレード2まで強化しよう"
e,202511020,event_yuw_00001_13_ja,event_yuw_00001_13,ja,"奥村 正宗 をグレード3まで強化しよう"
e,202511020,event_yuw_00001_14_ja,event_yuw_00001_14,ja,"奥村 正宗 をグレード4まで強化しよう"
e,202511020,event_yuw_00001_15_ja,event_yuw_00001_15,ja,"奥村 正宗 をグレード5まで強化しよう"
e,202511020,event_yuw_00001_16_ja,event_yuw_00001_16,ja,"奥村 正宗 をLv.20まで強化しよう"
e,202511020,event_yuw_00001_17_ja,event_yuw_00001_17,ja,"奥村 正宗 をLv.30まで強化しよう"
e,202511020,event_yuw_00001_18_ja,event_yuw_00001_18,ja,"奥村 正宗 をLv.40まで強化しよう"
e,202511020,event_yuw_00001_19_ja,event_yuw_00001_19,ja,"奥村 正宗 をLv.50まで強化しよう"
e,202511020,event_yuw_00001_20_ja,event_yuw_00001_20,ja,"奥村 正宗 をLv.60まで強化しよう"
e,202511020,event_yuw_00001_21_ja,event_yuw_00001_21,ja,"奥村 正宗 をLv.70まで強化しよう"
e,202511020,event_yuw_00001_22_ja,event_yuw_00001_22,ja,"奥村 正宗 をLv.80まで強化しよう"
e,202511020,event_yuw_00001_23_ja,event_yuw_00001_23,ja,ストーリークエスト「コスプレをしに来たんだよ」をクリアしよう
e,202511020,event_yuw_00001_24_ja,event_yuw_00001_24,ja,ストーリークエスト「俺はずっとオタクなだけです」をクリアしよう
e,202511020,event_yuw_00001_25_ja,event_yuw_00001_25,ja,チャレンジクエスト「幸せです…」をクリアしよう
e,202511020,event_yuw_00001_26_ja,event_yuw_00001_26,ja,高難易度「これがこの世界の頂上」をクリアしよう
e,202511020,event_yuw_00001_27_ja,event_yuw_00001_27,ja,敵を10体撃破しよう
e,202511020,event_yuw_00001_28_ja,event_yuw_00001_28,ja,敵を20体撃破しよう
e,202511020,event_yuw_00001_29_ja,event_yuw_00001_29,ja,敵を30体撃破しよう
e,202511020,event_yuw_00001_30_ja,event_yuw_00001_30,ja,敵を40体撃破しよう
e,202511020,event_yuw_00001_31_ja,event_yuw_00001_31,ja,敵を50体撃破しよう
e,202511020,event_yuw_00001_32_ja,event_yuw_00001_32,ja,敵を60体撃破しよう
e,202511020,event_yuw_00001_33_ja,event_yuw_00001_33,ja,敵を70体撃破しよう
e,202511020,event_yuw_00001_34_ja,event_yuw_00001_34,ja,敵を80体撃破しよう
e,202511020,event_yuw_00001_35_ja,event_yuw_00001_35,ja,敵を90体撃破しよう
e,202511020,event_yuw_00001_36_ja,event_yuw_00001_36,ja,敵を100体撃破しよう
e,202511020,event_yuw_00001_37_ja,event_yuw_00001_37,ja,敵を150体撃破しよう
e,202511020,event_yuw_00001_38_ja,event_yuw_00001_38,ja,敵を200体撃破しよう
e,202511020,event_yuw_00001_39_ja,event_yuw_00001_39,ja,敵を300体撃破しよう
e,202512010,event_sur_00001_1_ja,event_sur_00001_1,ja,"無窮の鎖 和倉 優希 をグレード2まで強化しよう"
e,202512010,event_sur_00001_2_ja,event_sur_00001_2,ja,"無窮の鎖 和倉 優希 をグレード3まで強化しよう"
e,202512010,event_sur_00001_3_ja,event_sur_00001_3,ja,"無窮の鎖 和倉 優希 をグレード4まで強化しよう"
e,202512010,event_sur_00001_4_ja,event_sur_00001_4,ja,"無窮の鎖 和倉 優希 をグレード5まで強化しよう"
e,202512010,event_sur_00001_5_ja,event_sur_00001_5,ja,"無窮の鎖 和倉 優希 をLv.20まで強化しよう"
e,202512010,event_sur_00001_6_ja,event_sur_00001_6,ja,"無窮の鎖 和倉 優希 をLv.30まで強化しよう"
e,202512010,event_sur_00001_7_ja,event_sur_00001_7,ja,"無窮の鎖 和倉 優希 をLv.40まで強化しよう"
e,202512010,event_sur_00001_8_ja,event_sur_00001_8,ja,"無窮の鎖 和倉 優希 をLv.50まで強化しよう"
e,202512010,event_sur_00001_9_ja,event_sur_00001_9,ja,"無窮の鎖 和倉 優希 をLv.60まで強化しよう"
e,202512010,event_sur_00001_10_ja,event_sur_00001_10,ja,"無窮の鎖 和倉 優希 をLv.70まで強化しよう"
e,202512010,event_sur_00001_11_ja,event_sur_00001_11,ja,"無窮の鎖 和倉 優希 をLv.80まで強化しよう"
e,202512010,event_sur_00001_12_ja,event_sur_00001_12,ja,"和倉 青羽 をグレード2まで強化しよう"
e,202512010,event_sur_00001_13_ja,event_sur_00001_13,ja,"和倉 青羽 をグレード3まで強化しよう"
e,202512010,event_sur_00001_14_ja,event_sur_00001_14,ja,"和倉 青羽 をグレード4まで強化しよう"
e,202512010,event_sur_00001_15_ja,event_sur_00001_15,ja,"和倉 青羽 をグレード5まで強化しよう"
e,202512010,event_sur_00001_16_ja,event_sur_00001_16,ja,"和倉 青羽 をLv.20まで強化しよう"
e,202512010,event_sur_00001_17_ja,event_sur_00001_17,ja,"和倉 青羽 をLv.30まで強化しよう"
e,202512010,event_sur_00001_18_ja,event_sur_00001_18,ja,"和倉 青羽 をLv.40まで強化しよう"
e,202512010,event_sur_00001_19_ja,event_sur_00001_19,ja,"和倉 青羽 をLv.50まで強化しよう"
e,202512010,event_sur_00001_20_ja,event_sur_00001_20,ja,"和倉 青羽 をLv.60まで強化しよう"
e,202512010,event_sur_00001_21_ja,event_sur_00001_21,ja,"和倉 青羽 をLv.70まで強化しよう"
e,202512010,event_sur_00001_22_ja,event_sur_00001_22,ja,"和倉 青羽 をLv.80まで強化しよう"
e,202512010,event_sur_00001_23_ja,event_sur_00001_23,ja,ストーリークエスト「スレイブの誕生」をクリアしよう
e,202512010,event_sur_00001_24_ja,event_sur_00001_24,ja,ストーリークエスト「隠れ里の戦い」をクリアしよう
e,202512010,event_sur_00001_25_ja,event_sur_00001_25,ja,チャレンジクエスト「魔都防衛隊」をクリアしよう
e,202512010,event_sur_00001_26_ja,event_sur_00001_26,ja,高難易度「スレイブと組長」をクリアしよう
e,202512010,event_sur_00001_27_ja,event_sur_00001_27,ja,敵を10体撃破しよう
e,202512010,event_sur_00001_28_ja,event_sur_00001_28,ja,敵を20体撃破しよう
e,202512010,event_sur_00001_29_ja,event_sur_00001_29,ja,敵を30体撃破しよう
e,202512010,event_sur_00001_30_ja,event_sur_00001_30,ja,敵を40体撃破しよう
e,202512010,event_sur_00001_31_ja,event_sur_00001_31,ja,敵を50体撃破しよう
e,202512010,event_sur_00001_32_ja,event_sur_00001_32,ja,敵を60体撃破しよう
e,202512010,event_sur_00001_33_ja,event_sur_00001_33,ja,敵を70体撃破しよう
e,202512010,event_sur_00001_34_ja,event_sur_00001_34,ja,敵を80体撃破しよう
e,202512010,event_sur_00001_35_ja,event_sur_00001_35,ja,敵を90体撃破しよう
e,202512010,event_sur_00001_36_ja,event_sur_00001_36,ja,敵を100体撃破しよう
e,202512010,event_sur_00001_37_ja,event_sur_00001_37,ja,敵を150体撃破しよう
e,202512010,event_sur_00001_38_ja,event_sur_00001_38,ja,敵を200体撃破しよう
e,202512010,event_sur_00001_39_ja,event_sur_00001_39,ja,敵を300体撃破しよう
e,202601010,event_jig_00001_1_ja,event_jig_00001_1,ja,"メイ をグレード2まで強化しよう"
e,202601010,event_jig_00001_2_ja,event_jig_00001_2,ja,"メイ をグレード3まで強化しよう"
e,202601010,event_jig_00001_3_ja,event_jig_00001_3,ja,"メイ をグレード4まで強化しよう"
e,202601010,event_jig_00001_4_ja,event_jig_00001_4,ja,"メイ をグレード5まで強化しよう"
e,202601010,event_jig_00001_5_ja,event_jig_00001_5,ja,"メイ をLv.20まで強化しよう"
e,202601010,event_jig_00001_6_ja,event_jig_00001_6,ja,"メイ をLv.30まで強化しよう"
e,202601010,event_jig_00001_7_ja,event_jig_00001_7,ja,"メイ をLv.40まで強化しよう"
e,202601010,event_jig_00001_8_ja,event_jig_00001_8,ja,"メイ をLv.50まで強化しよう"
e,202601010,event_jig_00001_9_ja,event_jig_00001_9,ja,"メイ をLv.60まで強化しよう"
e,202601010,event_jig_00001_10_ja,event_jig_00001_10,ja,"メイ をLv.70まで強化しよう"
e,202601010,event_jig_00001_11_ja,event_jig_00001_11,ja,"メイ をLv.80まで強化しよう"
e,202601010,event_jig_00001_12_ja,event_jig_00001_12,ja,"民谷 巌鉄斎 をグレード2まで強化しよう"
e,202601010,event_jig_00001_13_ja,event_jig_00001_13,ja,"民谷 巌鉄斎 をグレード3まで強化しよう"
e,202601010,event_jig_00001_14_ja,event_jig_00001_14,ja,"民谷 巌鉄斎 をグレード4まで強化しよう"
e,202601010,event_jig_00001_15_ja,event_jig_00001_15,ja,"民谷 巌鉄斎 をグレード5まで強化しよう"
e,202601010,event_jig_00001_16_ja,event_jig_00001_16,ja,"民谷 巌鉄斎 をLv.20まで強化しよう"
e,202601010,event_jig_00001_17_ja,event_jig_00001_17,ja,"民谷 巌鉄斎 をLv.30まで強化しよう"
e,202601010,event_jig_00001_18_ja,event_jig_00001_18,ja,"民谷 巌鉄斎 をLv.40まで強化しよう"
e,202601010,event_jig_00001_19_ja,event_jig_00001_19,ja,"民谷 巌鉄斎 をLv.50まで強化しよう"
e,202601010,event_jig_00001_20_ja,event_jig_00001_20,ja,"民谷 巌鉄斎 をLv.60まで強化しよう"
e,202601010,event_jig_00001_21_ja,event_jig_00001_21,ja,"民谷 巌鉄斎 をLv.70まで強化しよう"
e,202601010,event_jig_00001_22_ja,event_jig_00001_22,ja,"民谷 巌鉄斎 をLv.80まで強化しよう"
e,202601010,event_jig_00001_23_ja,event_jig_00001_23,ja,ストーリークエスト「必ず生きて帰る」をクリアしよう
e,202601010,event_jig_00001_24_ja,event_jig_00001_24,ja,ストーリークエスト「朱印の者たち」をクリアしよう
e,202601010,event_jig_00001_25_ja,event_jig_00001_25,ja,チャレンジクエスト「死罪人と首切り役人」をクリアしよう
e,202601010,event_jig_00001_26_ja,event_jig_00001_26,ja,高難易度「手負いの獣は恐ろしいぞ」をクリアしよう
e,202601010,event_jig_00001_27_ja,event_jig_00001_27,ja,敵を10体撃破しよう
e,202601010,event_jig_00001_28_ja,event_jig_00001_28,ja,敵を20体撃破しよう
e,202601010,event_jig_00001_29_ja,event_jig_00001_29,ja,敵を30体撃破しよう
e,202601010,event_jig_00001_30_ja,event_jig_00001_30,ja,敵を40体撃破しよう
e,202601010,event_jig_00001_31_ja,event_jig_00001_31,ja,敵を50体撃破しよう
e,202601010,event_jig_00001_32_ja,event_jig_00001_32,ja,敵を60体撃破しよう
e,202601010,event_jig_00001_33_ja,event_jig_00001_33,ja,敵を70体撃破しよう
e,202601010,event_jig_00001_34_ja,event_jig_00001_34,ja,敵を80体撃破しよう
e,202601010,event_jig_00001_35_ja,event_jig_00001_35,ja,敵を90体撃破しよう
e,202601010,event_jig_00001_36_ja,event_jig_00001_36,ja,敵を100体撃破しよう
e,202601010,event_jig_00001_37_ja,event_jig_00001_37,ja,敵を150体撃破しよう
e,202601010,event_jig_00001_38_ja,event_jig_00001_38,ja,敵を200体撃破しよう
e,202601010,event_jig_00001_39_ja,event_jig_00001_39,ja,敵を300体撃破しよう
e,202601010,event_jig_00001_40_ja,event_jig_00001_40,ja,敵を400体撃破しよう
e,202601010,event_jig_00001_41_ja,event_jig_00001_41,ja,敵を500体撃破しよう
e,202601010,event_jig_00001_42_ja,event_jig_00001_42,ja,敵を750体撃破しよう
e,202601010,event_jig_00001_43_ja,event_jig_00001_43,ja,敵を1000体撃破しよう
e,202512020,event_osh_00001_1_ja,event_osh_00001_1,ja,ステージを5回クリアしよう
e,202512020,event_osh_00001_2_ja,event_osh_00001_2,ja,ステージを10回クリアしよう
e,202512020,event_osh_00001_3_ja,event_osh_00001_3,ja,ステージを15回クリアしよう
e,202512020,event_osh_00001_4_ja,event_osh_00001_4,ja,ステージを20回クリアしよう
e,202512020,event_osh_00001_5_ja,event_osh_00001_5,ja,ステージを30回クリアしよう
e,202512020,event_osh_00001_6_ja,event_osh_00001_6,ja,ステージを40回クリアしよう
e,202512020,event_osh_00001_7_ja,event_osh_00001_7,ja,ステージを50回クリアしよう
e,202512020,event_osh_00001_8_ja,event_osh_00001_8,ja,ステージを60回クリアしよう
e,202512020,event_osh_00001_9_ja,event_osh_00001_9,ja,ステージを70回クリアしよう
e,202512020,event_osh_00001_10_ja,event_osh_00001_10,ja,ステージを80回クリアしよう
e,202512020,event_osh_00001_11_ja,event_osh_00001_11,ja,ステージを90回クリアしよう
e,202512020,event_osh_00001_12_ja,event_osh_00001_12,ja,ステージを100回クリアしよう
e,202512020,event_osh_00001_13_ja,event_osh_00001_13,ja,ステージを110回クリアしよう
e,202512020,event_osh_00001_14_ja,event_osh_00001_14,ja,ステージを120回クリアしよう
e,202512020,event_osh_00001_15_ja,event_osh_00001_15,ja,ステージを150回クリアしよう
e,202512020,event_osh_00001_16_ja,event_osh_00001_16,ja,ステージを180回クリアしよう
e,202512020,event_osh_00001_17_ja,event_osh_00001_17,ja,ステージを190回クリアしよう
e,202512020,event_osh_00001_18_ja,event_osh_00001_18,ja,ステージを200回クリアしよう
e,202512020,event_osh_00001_19_ja,event_osh_00001_19,ja,ステージを210回クリアしよう
e,202512020,event_osh_00001_20_ja,event_osh_00001_20,ja,ステージを250回クリアしよう
e,202512020,event_osh_00001_21_ja,event_osh_00001_21,ja,ステージを300回クリアしよう
e,202512020,event_osh_00001_22_ja,event_osh_00001_22,ja,ステージを350回クリアしよう
e,202512020,event_osh_00001_23_ja,event_osh_00001_23,ja,ステージを400回クリアしよう
e,202512020,event_osh_00001_24_ja,event_osh_00001_24,ja,ステージを450回クリアしよう
e,202512020,event_osh_00001_25_ja,event_osh_00001_25,ja,ステージを500回クリアしよう
e,202512020,event_osh_00001_26_ja,event_osh_00001_26,ja,ステージを550回クリアしよう
e,202512020,event_osh_00001_27_ja,event_osh_00001_27,ja,ステージを600回クリアしよう
e,202512020,event_osh_00001_28_ja,event_osh_00001_28,ja,賀正ガシャ2026を10回引こう
e,202512020,event_osh_00001_29_ja,event_osh_00001_29,ja,賀正ガシャ2026を20回引こう
e,202512020,event_osh_00001_30_ja,event_osh_00001_30,ja,賀正ガシャ2026を30回引こう
e,202512020,event_osh_00001_31_ja,event_osh_00001_31,ja,賀正ガシャ2026を40回引こう
e,202512020,event_osh_00001_32_ja,event_osh_00001_32,ja,賀正ガシャ2026を50回引こう
e,202512020,event_osh_00001_33_ja,event_osh_00001_33,ja,【汗が輝いてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を1回クリア
e,202512020,event_osh_00001_34_ja,event_osh_00001_34,ja,【バルクきてるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を3回クリア
e,202512020,event_osh_00001_35_ja,event_osh_00001_35,ja,【仕上がってるよ!】ぴえヨンを編成に入れて「ファンと推し合戦！」を5回クリア
e,202512020,event_osh_00001_36_ja,event_osh_00001_36,ja,【胸筋の登山始まってるよ!】収集クエスト「芸能界へ！」をクリアしよう
e,202512020,event_osh_00001_37_ja,event_osh_00001_37,ja,【ナイスバルク!】ぴえヨンを編成に入れて「芸能界へ！」3話を5回クリア
e,202512020,event_osh_00001_38_ja,event_osh_00001_38,ja,【背中QRコードか!】ぴえヨンを編成に入れて「芸能界へ！」3話を10回クリア
e,202512020,event_osh_00001_39_ja,event_osh_00001_39,ja,【いい血管出てるよ!】ぴえヨンを編成に入れて「芸能界へ！」3話を30回クリア
e,202512020,event_osh_00001_40_ja,event_osh_00001_40,ja,【手羽先の完全究極体!】ぴえヨンを編成に入れて「芸能界へ！」3話を50回クリア
e,202512020,event_osh_00001_41_ja,event_osh_00001_41,ja,【新年号は筋肉です】ぴえヨンを編成に入れて「芸能界へ！」3話を100回クリア
e,202512020,event_osh_00001_42_ja,event_osh_00001_42,ja,【背中に羽がある!】強化クエスト「ぴえヨンのブートクエスト」をクリアしよう
e,202512020,event_osh_00001_43_ja,event_osh_00001_43,ja,【板チョコのようだ!】ぴえヨンを編成に入れて「ぴえヨンのブートクエスト」3話を3回クリア
e,202512020,event_osh_00001_44_ja,event_osh_00001_44,ja,【見てるこっちが筋肉痛!】ぴえヨンを編成に入れて「ぴえヨンのブートクエスト」3話を5回クリア
e,202512020,event_osh_00001_45_ja,event_osh_00001_45,ja,【マッチョの枯山水!】ぴえヨンを編成に入れて「ぴえヨンのブートクエスト」3話を10回クリア
e,202512020,event_osh_00001_46_ja,event_osh_00001_46,ja,【筋肉国宝!】ぴえヨンを編成に入れて「ぴえヨンのブートクエスト」3話を20回クリア
e,202512020,event_osh_00001_47_ja,event_osh_00001_47,ja,【上腕二頭筋ナイス!】ぴえヨンを編成に入れて「推しの子になってやる」1話を1回挑戦
e,202512020,event_osh_00001_48_ja,event_osh_00001_48,ja,【腹筋6LDK!】ぴえヨンを編成に入れて「推しの子になってやる」2話を1回挑戦
e,202512020,event_osh_00001_49_ja,event_osh_00001_49,ja,【カニカマの千倍!】ぴえヨンを編成に入れて「推しの子になってやる」3話を1回挑戦
e,202512020,event_osh_00001_50_ja,event_osh_00001_50,ja,【もはや説明不要!】ぴえヨンを編成に入れて「推しの子になってやる」4話を1回挑戦
e,202512020,event_osh_00001_51_ja,event_osh_00001_51,ja,【背筋が立ってる!】ぴえヨンを編成に入れて「芸能界には才能が集まる」1話を1回挑戦
e,202512020,event_osh_00001_52_ja,event_osh_00001_52,ja,【眠れない夜もあっただろ!】ぴえヨンを編成に入れて「芸能界には才能が集まる」2話を1回挑戦
e,202512020,event_osh_00001_53_ja,event_osh_00001_53,ja,【よ!阿修羅像!】ぴえヨンを編成に入れて「芸能界には才能が集まる」3話を1回挑戦
e,202512020,event_glo_00001_1_ja,event_glo_00001_1,ja,デイリークエスト「開運!ジャンブル運試し」を1回クリアしよう
e,202512020,event_glo_00001_2_ja,event_glo_00001_2,ja,デイリークエスト「開運!ジャンブル運試し」を2回クリアしよう
e,202512020,event_glo_00001_3_ja,event_glo_00001_3,ja,デイリークエスト「開運!ジャンブル運試し」を3回クリアしよう
e,202602010,event_you_00001_1_ja,event_you_00001_1,ja,"ダグ をグレード2まで強化しよう"
e,202602010,event_you_00001_2_ja,event_you_00001_2,ja,"ダグ をグレード3まで強化しよう"
e,202602010,event_you_00001_3_ja,event_you_00001_3,ja,"ダグ をグレード4まで強化しよう"
e,202602010,event_you_00001_4_ja,event_you_00001_4,ja,"ダグ をグレード5まで強化しよう"
e,202602010,event_you_00001_5_ja,event_you_00001_5,ja,"ダグ をLv.20まで強化しよう"
e,202602010,event_you_00001_6_ja,event_you_00001_6,ja,"ダグ をLv.30まで強化しよう"
e,202602010,event_you_00001_7_ja,event_you_00001_7,ja,"ダグ をLv.40まで強化しよう"
e,202602010,event_you_00001_8_ja,event_you_00001_8,ja,"ダグ をLv.50まで強化しよう"
e,202602010,event_you_00001_9_ja,event_you_00001_9,ja,"ダグ をLv.60まで強化しよう"
e,202602010,event_you_00001_10_ja,event_you_00001_10,ja,"ダグ をLv.70まで強化しよう"
e,202602010,event_you_00001_11_ja,event_you_00001_11,ja,"ダグ をLv.80まで強化しよう"
e,202602010,event_you_00001_12_ja,event_you_00001_12,ja,"ハナ をグレード2まで強化しよう"
e,202602010,event_you_00001_13_ja,event_you_00001_13,ja,"ハナ をグレード3まで強化しよう"
e,202602010,event_you_00001_14_ja,event_you_00001_14,ja,"ハナ をグレード4まで強化しよう"
e,202602010,event_you_00001_15_ja,event_you_00001_15,ja,"ハナ をグレード5まで強化しよう"
e,202602010,event_you_00001_16_ja,event_you_00001_16,ja,"ハナ をLv.20まで強化しよう"
e,202602010,event_you_00001_17_ja,event_you_00001_17,ja,"ハナ をLv.30まで強化しよう"
e,202602010,event_you_00001_18_ja,event_you_00001_18,ja,"ハナ をLv.40まで強化しよう"
e,202602010,event_you_00001_19_ja,event_you_00001_19,ja,"ハナ をLv.50まで強化しよう"
e,202602010,event_you_00001_20_ja,event_you_00001_20,ja,"ハナ をLv.60まで強化しよう"
e,202602010,event_you_00001_21_ja,event_you_00001_21,ja,"ハナ をLv.70まで強化しよう"
e,202602010,event_you_00001_22_ja,event_you_00001_22,ja,"ハナ をLv.80まで強化しよう"
e,202602010,event_you_00001_23_ja,event_you_00001_23,ja,ストーリークエスト「先輩は敬いたまえ」をクリアしよう
e,202602010,event_you_00001_24_ja,event_you_00001_24,ja,ストーリークエスト「兄を助けてくれないか？」をクリアしよう
e,202602010,event_you_00001_25_ja,event_you_00001_25,ja,チャレンジクエスト「世界一安全な幼稚園」をクリアしよう
e,202602010,event_you_00001_26_ja,event_you_00001_26,ja,高難易度「正義だけじゃ何も守れない」をクリアしよう
e,202602010,event_you_00001_27_ja,event_you_00001_27,ja,敵を10体撃破しよう
e,202602010,event_you_00001_28_ja,event_you_00001_28,ja,敵を20体撃破しよう
e,202602010,event_you_00001_29_ja,event_you_00001_29,ja,敵を30体撃破しよう
e,202602010,event_you_00001_30_ja,event_you_00001_30,ja,敵を40体撃破しよう
e,202602010,event_you_00001_31_ja,event_you_00001_31,ja,敵を50体撃破しよう
e,202602010,event_you_00001_32_ja,event_you_00001_32,ja,敵を60体撃破しよう
e,202602010,event_you_00001_33_ja,event_you_00001_33,ja,敵を70体撃破しよう
e,202602010,event_you_00001_34_ja,event_you_00001_34,ja,敵を80体撃破しよう
e,202602010,event_you_00001_35_ja,event_you_00001_35,ja,敵を90体撃破しよう
e,202602010,event_you_00001_36_ja,event_you_00001_36,ja,敵を100体撃破しよう
e,202602010,event_you_00001_37_ja,event_you_00001_37,ja,敵を150体撃破しよう
e,202602010,event_you_00001_38_ja,event_you_00001_38,ja,敵を200体撃破しよう
e,202602010,event_you_00001_39_ja,event_you_00001_39,ja,敵を300体撃破しよう
e,202602010,event_you_00001_40_ja,event_you_00001_40,ja,敵を400体撃破しよう
e,202602010,event_you_00001_41_ja,event_you_00001_41,ja,敵を500体撃破しよう
e,202602010,event_you_00001_42_ja,event_you_00001_42,ja,敵を750体撃破しよう
e,202602010,event_you_00001_43_ja,event_you_00001_43,ja,敵を1000体撃破しよう
e,202602020,event_kim_00001_1_ja,event_kim_00001_1,ja,強敵を1体撃破しよう
e,202602020,event_kim_00001_2_ja,event_kim_00001_2,ja,強敵を3体撃破しよう
e,202602020,event_kim_00001_3_ja,event_kim_00001_3,ja,強敵を5体撃破しよう
e,202602020,event_kim_00001_4_ja,event_kim_00001_4,ja,強敵を10体撃破しよう
e,202602020,event_kim_00001_5_ja,event_kim_00001_5,ja,強敵を15体撃破しよう
e,202602020,event_kim_00001_6_ja,event_kim_00001_6,ja,強敵を20体撃破しよう
e,202602020,event_kim_00001_7_ja,event_kim_00001_7,ja,強敵を25体撃破しよう
e,202602020,event_kim_00001_8_ja,event_kim_00001_8,ja,強敵を30体撃破しよう
e,202602020,event_kim_00001_9_ja,event_kim_00001_9,ja,強敵を35体撃破しよう
e,202602020,event_kim_00001_10_ja,event_kim_00001_10,ja,強敵を40体撃破しよう
e,202602020,event_kim_00001_11_ja,event_kim_00001_11,ja,強敵を45体撃破しよう
e,202602020,event_kim_00001_12_ja,event_kim_00001_12,ja,強敵を50体撃破しよう
e,202602020,event_kim_00001_13_ja,event_kim_00001_13,ja,強敵を55体撃破しよう
e,202602020,event_kim_00001_14_ja,event_kim_00001_14,ja,強敵を60体撃破しよう
e,202602020,event_kim_00001_15_ja,event_kim_00001_15,ja,強敵を65体撃破しよう
e,202602020,event_kim_00001_16_ja,event_kim_00001_16,ja,強敵を70体撃破しよう
e,202602020,event_kim_00001_17_ja,event_kim_00001_17,ja,強敵を75体撃破しよう
e,202602020,event_kim_00001_18_ja,event_kim_00001_18,ja,強敵を80体撃破しよう
e,202602020,event_kim_00001_19_ja,event_kim_00001_19,ja,強敵を85体撃破しよう
e,202602020,event_kim_00001_20_ja,event_kim_00001_20,ja,強敵を90体撃破しよう
e,202602020,event_kim_00001_21_ja,event_kim_00001_21,ja,強敵を95体撃破しよう
e,202602020,event_kim_00001_22_ja,event_kim_00001_22,ja,強敵を100体撃破しよう
e,202602020,event_kim_00001_23_ja,event_kim_00001_23,ja,収集クエスト「キスゾンビ♡パニック」をクリアしよう
e,202602020,event_kim_00001_24_ja,event_kim_00001_24,ja,ストーリークエスト「最高の恋愛パートナー」をクリアしよう
e,202602020,event_kim_00001_25_ja,event_kim_00001_25,ja,チャレンジクエスト「恋太郎ファミリー」をクリアしよう
e,202602020,event_kim_00001_26_ja,event_kim_00001_26,ja,"高難易度「DEAD OR LOVE」をクリアしよう"
e,202602020,event_kim_00001_27_ja,event_kim_00001_27,ja,敵を10体撃破しよう
e,202602020,event_kim_00001_28_ja,event_kim_00001_28,ja,敵を20体撃破しよう
e,202602020,event_kim_00001_29_ja,event_kim_00001_29,ja,敵を30体撃破しよう
e,202602020,event_kim_00001_30_ja,event_kim_00001_30,ja,敵を40体撃破しよう
e,202602020,event_kim_00001_31_ja,event_kim_00001_31,ja,敵を50体撃破しよう
e,202602020,event_kim_00001_32_ja,event_kim_00001_32,ja,敵を60体撃破しよう
e,202602020,event_kim_00001_33_ja,event_kim_00001_33,ja,敵を70体撃破しよう
e,202602020,event_kim_00001_34_ja,event_kim_00001_34,ja,敵を80体撃破しよう
e,202602020,event_kim_00001_35_ja,event_kim_00001_35,ja,敵を90体撃破しよう
e,202602020,event_kim_00001_36_ja,event_kim_00001_36,ja,敵を100体撃破しよう
e,202602020,event_kim_00001_37_ja,event_kim_00001_37,ja,敵を150体撃破しよう
e,202602020,event_kim_00001_38_ja,event_kim_00001_38,ja,敵を200体撃破しよう
e,202602020,event_kim_00001_39_ja,event_kim_00001_39,ja,敵を300体撃破しよう
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionLimitedTerm.csv -->
## ./projects/glow-masterdata/MstMissionLimitedTerm.csv

```csv
ENABLE,id,release_key,progress_group_key,criterion_type,criterion_value,criterion_count,mission_category,mst_mission_reward_group_id,sort_order,destination_scene,start_at,end_at
e,limited_term_1,202509010,group1,AdventBattleChallengeCount,,5,AdventBattle,kai_00001_limited_term_1,1,AdventBattle,"2025-10-01 12:00:00","2025-10-08 11:59:59"
e,limited_term_2,202509010,group1,AdventBattleChallengeCount,,10,AdventBattle,kai_00001_limited_term_2,2,AdventBattle,"2025-10-01 12:00:00","2025-10-08 11:59:59"
e,limited_term_3,202509010,group1,AdventBattleChallengeCount,,20,AdventBattle,kai_00001_limited_term_3,3,AdventBattle,"2025-10-01 12:00:00","2025-10-08 11:59:59"
e,limited_term_4,202509010,group1,AdventBattleChallengeCount,,30,AdventBattle,kai_00001_limited_term_4,4,AdventBattle,"2025-10-01 12:00:00","2025-10-08 11:59:59"
e,limited_term_5,202510010,group2,AdventBattleChallengeCount,,5,AdventBattle,spy_00001_limited_term_1,1,AdventBattle,"2025-10-15 15:00:00","2025-10-22 14:59:59"
e,limited_term_6,202510010,group2,AdventBattleChallengeCount,,10,AdventBattle,spy_00001_limited_term_2,2,AdventBattle,"2025-10-15 15:00:00","2025-10-22 14:59:59"
e,limited_term_7,202510010,group2,AdventBattleChallengeCount,,20,AdventBattle,spy_00001_limited_term_3,3,AdventBattle,"2025-10-15 15:00:00","2025-10-22 14:59:59"
e,limited_term_8,202510010,group2,AdventBattleChallengeCount,,30,AdventBattle,spy_00001_limited_term_4,4,AdventBattle,"2025-10-15 15:00:00","2025-10-22 14:59:59"
e,limited_term_9,202510020,group3,AdventBattleChallengeCount,,5,AdventBattle,dan_00001_limited_term_1,1,AdventBattle,"2025-10-31 15:00:00","2025-11-06 14:59:59"
e,limited_term_10,202510020,group3,AdventBattleChallengeCount,,10,AdventBattle,dan_00001_limited_term_2,2,AdventBattle,"2025-10-31 15:00:00","2025-11-06 14:59:59"
e,limited_term_11,202510020,group3,AdventBattleChallengeCount,,20,AdventBattle,dan_00001_limited_term_3,3,AdventBattle,"2025-10-31 15:00:00","2025-11-06 14:59:59"
e,limited_term_12,202510020,group3,AdventBattleChallengeCount,,30,AdventBattle,dan_00001_limited_term_4,4,AdventBattle,"2025-10-31 15:00:00","2025-11-06 14:59:59"
e,limited_term_13,202511010,group4,AdventBattleChallengeCount,,5,AdventBattle,mag_00001_limited_term_1,1,AdventBattle,"2025-11-22 15:00:00","2025-11-28 14:59:59"
e,limited_term_14,202511010,group4,AdventBattleChallengeCount,,10,AdventBattle,mag_00001_limited_term_2,2,AdventBattle,"2025-11-22 15:00:00","2025-11-28 14:59:59"
e,limited_term_15,202511010,group4,AdventBattleChallengeCount,,20,AdventBattle,mag_00001_limited_term_3,3,AdventBattle,"2025-11-22 15:00:00","2025-11-28 14:59:59"
e,limited_term_16,202511010,group4,AdventBattleChallengeCount,,30,AdventBattle,mag_00001_limited_term_4,4,AdventBattle,"2025-11-22 15:00:00","2025-11-28 14:59:59"
e,limited_term_17,202511010,group5,AdventBattleChallengeCount,,5,AdventBattle,kai_00002_limited_term_1,1,AdventBattle,"2025-11-12 15:00:00","2025-11-17 14:59:59"
e,limited_term_18,202511010,group5,AdventBattleChallengeCount,,10,AdventBattle,kai_00002_limited_term_2,2,AdventBattle,"2025-11-12 15:00:00","2025-11-17 14:59:59"
e,limited_term_19,202511010,group5,AdventBattleChallengeCount,,20,AdventBattle,kai_00002_limited_term_3,3,AdventBattle,"2025-11-12 15:00:00","2025-11-17 14:59:59"
e,limited_term_20,202511010,group5,AdventBattleChallengeCount,,30,AdventBattle,kai_00002_limited_term_4,4,AdventBattle,"2025-11-12 15:00:00","2025-11-17 14:59:59"
e,limited_term_21,202511020,group6,AdventBattleChallengeCount,,5,AdventBattle,yuw_00001_limited_term_1,1,AdventBattle,"2025-12-05 15:00:00","2025-12-12 14:59:59"
e,limited_term_22,202511020,group6,AdventBattleChallengeCount,,10,AdventBattle,yuw_00001_limited_term_2,2,AdventBattle,"2025-12-05 15:00:00","2025-12-12 14:59:59"
e,limited_term_23,202511020,group6,AdventBattleChallengeCount,,20,AdventBattle,yuw_00001_limited_term_3,3,AdventBattle,"2025-12-05 15:00:00","2025-12-12 14:59:59"
e,limited_term_24,202511020,group6,AdventBattleChallengeCount,,30,AdventBattle,yuw_00001_limited_term_4,4,AdventBattle,"2025-12-05 15:00:00","2025-12-12 14:59:59"
e,limited_term_25,202512010,group7,AdventBattleChallengeCount,,5,AdventBattle,sur_00001_limited_term_1,1,AdventBattle,"2025-12-22 15:00:00","2025-12-29 14:59:59"
e,limited_term_26,202512010,group7,AdventBattleChallengeCount,,10,AdventBattle,sur_00001_limited_term_2,2,AdventBattle,"2025-12-22 15:00:00","2025-12-29 14:59:59"
e,limited_term_27,202512010,group7,AdventBattleChallengeCount,,20,AdventBattle,sur_00001_limited_term_3,3,AdventBattle,"2025-12-22 15:00:00","2025-12-29 14:59:59"
e,limited_term_28,202512010,group7,AdventBattleChallengeCount,,30,AdventBattle,sur_00001_limited_term_4,4,AdventBattle,"2025-12-22 15:00:00","2025-12-29 14:59:59"
e,limited_term_29,202601010,group8,AdventBattleChallengeCount,,5,AdventBattle,jig_00001_limited_term_1,1,AdventBattle,"2026-01-23 15:00:00","2026-01-29 14:59:59"
e,limited_term_30,202601010,group8,AdventBattleChallengeCount,,10,AdventBattle,jig_00001_limited_term_2,2,AdventBattle,"2026-01-23 15:00:00","2026-01-29 14:59:59"
e,limited_term_31,202601010,group8,AdventBattleChallengeCount,,20,AdventBattle,jig_00001_limited_term_3,3,AdventBattle,"2026-01-23 15:00:00","2026-01-29 14:59:59"
e,limited_term_32,202601010,group8,AdventBattleChallengeCount,,30,AdventBattle,jig_00001_limited_term_4,4,AdventBattle,"2026-01-23 15:00:00","2026-01-29 14:59:59"
e,limited_term_33,202512020,group9,AdventBattleChallengeCount,,5,AdventBattle,osh_00001_limited_term_1,1,AdventBattle,"2026-01-09 15:00:00","2026-01-13 14:59:59"
e,limited_term_34,202512020,group9,AdventBattleChallengeCount,,10,AdventBattle,osh_00001_limited_term_2,2,AdventBattle,"2026-01-09 15:00:00","2026-01-13 14:59:59"
e,limited_term_35,202512020,group9,AdventBattleChallengeCount,,20,AdventBattle,osh_00001_limited_term_3,3,AdventBattle,"2026-01-09 15:00:00","2026-01-13 14:59:59"
e,limited_term_36,202512020,group9,AdventBattleChallengeCount,,25,AdventBattle,osh_00001_limited_term_4,4,AdventBattle,"2026-01-09 15:00:00","2026-01-13 14:59:59"
e,limited_term_37,202602010,group10,AdventBattleChallengeCount,,5,AdventBattle,you_00001_limited_term_1,1,AdventBattle,"2026-02-09 15:00:00","2026-02-15 14:59:59"
e,limited_term_38,202602010,group10,AdventBattleChallengeCount,,10,AdventBattle,you_00001_limited_term_2,2,AdventBattle,"2026-02-09 15:00:00","2026-02-15 14:59:59"
e,limited_term_39,202602010,group10,AdventBattleChallengeCount,,20,AdventBattle,you_00001_limited_term_3,3,AdventBattle,"2026-02-09 15:00:00","2026-02-15 14:59:59"
e,limited_term_40,202602010,group10,AdventBattleChallengeCount,,30,AdventBattle,you_00001_limited_term_4,4,AdventBattle,"2026-02-09 15:00:00","2026-02-15 14:59:59"
e,limited_term_41,202602020,group11,AdventBattleChallengeCount,,5,AdventBattle,kim_00001_limited_term_1,1,AdventBattle,"2026-02-20 15:00:00","2026-02-26 14:59:59"
e,limited_term_42,202602020,group11,AdventBattleChallengeCount,,10,AdventBattle,kim_00001_limited_term_2,2,AdventBattle,"2026-02-20 15:00:00","2026-02-26 14:59:59"
e,limited_term_43,202602020,group11,AdventBattleChallengeCount,,20,AdventBattle,kim_00001_limited_term_3,3,AdventBattle,"2026-02-20 15:00:00","2026-02-26 14:59:59"
e,limited_term_44,202602020,group11,AdventBattleChallengeCount,,30,AdventBattle,kim_00001_limited_term_4,4,AdventBattle,"2026-02-20 15:00:00","2026-02-26 14:59:59"
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionLimitedTermDependency.csv -->
## ./projects/glow-masterdata/MstMissionLimitedTermDependency.csv

```csv
ENABLE,id,release_key,group_id,mst_mission_limited_term_id,unlock_order
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionLimitedTermI18n.csv -->
## ./projects/glow-masterdata/MstMissionLimitedTermI18n.csv

```csv
ENABLE,release_key,id,mst_mission_limited_term_id,language,description
e,202509010,limited_term_1_ja,limited_term_1,ja,降臨バトル「怪獣退治の時間︎」に5回挑戦しよう！
e,202509010,limited_term_2_ja,limited_term_2,ja,降臨バトル「怪獣退治の時間︎」に10回挑戦しよう！
e,202509010,limited_term_3_ja,limited_term_3,ja,降臨バトル「怪獣退治の時間︎」に20回挑戦しよう！
e,202509010,limited_term_4_ja,limited_term_4,ja,降臨バトル「怪獣退治の時間︎」に30回挑戦しよう！
e,202510010,limited_term_5_ja,limited_term_5,ja,降臨バトル「SPY×FAMILY」に5回挑戦しよう！
e,202510010,limited_term_6_ja,limited_term_6,ja,降臨バトル「SPY×FAMILY」に10回挑戦しよう！
e,202510010,limited_term_7_ja,limited_term_7,ja,降臨バトル「SPY×FAMILY」に20回挑戦しよう！
e,202510010,limited_term_8_ja,limited_term_8,ja,降臨バトル「SPY×FAMILY」に30回挑戦しよう！
e,202510020,limited_term_9_ja,limited_term_9,ja,降臨バトル「ダンダダン」に5回挑戦しよう！
e,202510020,limited_term_10_ja,limited_term_10,ja,降臨バトル「ダンダダン」に10回挑戦しよう！
e,202510020,limited_term_11_ja,limited_term_11,ja,降臨バトル「ダンダダン」に20回挑戦しよう！
e,202510020,limited_term_12_ja,limited_term_12,ja,降臨バトル「ダンダダン」に30回挑戦しよう！
e,202511010,limited_term_13_ja,limited_term_13,ja,降臨バトル「業務実行！！」に5回挑戦しよう！
e,202511010,limited_term_14_ja,limited_term_14,ja,降臨バトル「業務実行！！」に10回挑戦しよう！
e,202511010,limited_term_15_ja,limited_term_15,ja,降臨バトル「業務実行！！」に20回挑戦しよう！
e,202511010,limited_term_16_ja,limited_term_16,ja,降臨バトル「業務実行！！」に30回挑戦しよう！
e,202511010,limited_term_17_ja,limited_term_17,ja,降臨バトル「怪獣退治の時間︎」に5回挑戦しよう！
e,202511010,limited_term_18_ja,limited_term_18,ja,降臨バトル「怪獣退治の時間︎」に10回挑戦しよう！
e,202511010,limited_term_19_ja,limited_term_19,ja,降臨バトル「怪獣退治の時間︎」に20回挑戦しよう！
e,202511010,limited_term_20_ja,limited_term_20,ja,降臨バトル「怪獣退治の時間︎」に30回挑戦しよう！
e,202511020,limited_term_21_ja,limited_term_21,ja,降臨バトル「夏コミの魔物」に5回挑戦しよう！
e,202511020,limited_term_22_ja,limited_term_22,ja,降臨バトル「夏コミの魔物」に10回挑戦しよう！
e,202511020,limited_term_23_ja,limited_term_23,ja,降臨バトル「夏コミの魔物」に20回挑戦しよう！
e,202511020,limited_term_24_ja,limited_term_24,ja,降臨バトル「夏コミの魔物」に30回挑戦しよう！
e,202512010,limited_term_25_ja,limited_term_25,ja,降臨バトル「魔防隊と戦う者」に5回挑戦しよう！
e,202512010,limited_term_26_ja,limited_term_26,ja,降臨バトル「魔防隊と戦う者」に10回挑戦しよう！
e,202512010,limited_term_27_ja,limited_term_27,ja,降臨バトル「魔防隊と戦う者」に20回挑戦しよう！
e,202512010,limited_term_28_ja,limited_term_28,ja,降臨バトル「魔防隊と戦う者」に30回挑戦しよう！
e,202601010,limited_term_29_ja,limited_term_29,ja,"降臨バトル「まるで 悪夢を見ているようだ」に5回挑戦しよう！"
e,202601010,limited_term_30_ja,limited_term_30,ja,"降臨バトル「まるで 悪夢を見ているようだ」に10回挑戦しよう！"
e,202601010,limited_term_31_ja,limited_term_31,ja,"降臨バトル「まるで 悪夢を見ているようだ」に20回挑戦しよう！"
e,202601010,limited_term_32_ja,limited_term_32,ja,"降臨バトル「まるで 悪夢を見ているようだ」に30回挑戦しよう！"
e,202512020,limited_term_33_ja,limited_term_33,ja,降臨バトル「ファーストライブ」に5回挑戦しよう！
e,202512020,limited_term_34_ja,limited_term_34,ja,降臨バトル「ファーストライブ」に10回挑戦しよう！
e,202512020,limited_term_35_ja,limited_term_35,ja,降臨バトル「ファーストライブ」に20回挑戦しよう！
e,202512020,limited_term_36_ja,limited_term_36,ja,降臨バトル「ファーストライブ」に25回挑戦しよう！
e,202602010,limited_term_37_ja,limited_term_37,ja,降臨バトル「誰の依頼だ？」に5回挑戦しよう！
e,202602010,limited_term_38_ja,limited_term_38,ja,降臨バトル「誰の依頼だ？」に10回挑戦しよう！
e,202602010,limited_term_39_ja,limited_term_39,ja,降臨バトル「誰の依頼だ？」に20回挑戦しよう！
e,202602010,limited_term_40_ja,limited_term_40,ja,降臨バトル「誰の依頼だ？」に30回挑戦しよう！
e,202602020,limited_term_41_ja,limited_term_41,ja,降臨バトル「ラブミッション：インポッシブル」に5回挑戦しよう！
e,202602020,limited_term_42_ja,limited_term_42,ja,降臨バトル「ラブミッション：インポッシブル」に10回挑戦しよう！
e,202602020,limited_term_43_ja,limited_term_43,ja,降臨バトル「ラブミッション：インポッシブル」に20回挑戦しよう！
e,202602020,limited_term_44_ja,limited_term_44,ja,降臨バトル「ラブミッション：インポッシブル」に30回挑戦しよう！
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionReward.csv -->
## ./projects/glow-masterdata/MstMissionReward.csv

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_1,202509010,daily_bonus_reward_1_1,FreeDiamond,,20,1,
e,mission_reward_2,202509010,daily_bonus_reward_1_2,Coin,,2000,1,
e,mission_reward_3,202509010,daily_bonus_reward_1_3,FreeDiamond,,30,1,
e,mission_reward_4,202509010,daily_bonus_reward_1_4,Coin,,3000,1,
e,mission_reward_5,202509010,daily_bonus_reward_1_5,FreeDiamond,,50,1,
e,mission_reward_6,202509010,daily_bonus_reward_1_6,Coin,,5000,1,
e,mission_reward_7,202509010,daily_bonus_reward_1_7,FreeDiamond,,50,1,
e,mission_reward_8,202509010,daily_bonus_reward_1_7,Item,entry_item_glo_00001,1,1,
e,mission_reward_9,202509010,daily_bonus_reward_1_7,Item,memoryfragment_glo_00001,5,1,
e,mission_reward_10,202509010,kai_00001_event_reward_01,Item,memory_chara_kai_00601,200,1,怪獣８号いいジャン祭
e,mission_reward_11,202509010,kai_00001_event_reward_02,Item,memory_chara_kai_00601,300,1,怪獣８号いいジャン祭
e,mission_reward_12,202509010,kai_00001_event_reward_03,Item,memory_chara_kai_00601,350,1,怪獣８号いいジャン祭
e,mission_reward_13,202509010,kai_00001_event_reward_04,Item,ticket_glo_00003,1,1,怪獣８号いいジャン祭
e,mission_reward_14,202509010,kai_00001_event_reward_05,Item,piece_kai_00601,75,1,怪獣８号いいジャン祭
e,mission_reward_15,202509010,kai_00001_event_reward_06,Item,piece_kai_00601,100,1,怪獣８号いいジャン祭
e,mission_reward_16,202509010,kai_00001_event_reward_07,FreeDiamond,,50,1,怪獣８号いいジャン祭
e,mission_reward_17,202509010,kai_00001_event_reward_08,Item,memory_chara_kai_00501,200,1,怪獣８号いいジャン祭
e,mission_reward_18,202509010,kai_00001_event_reward_09,Item,memory_chara_kai_00501,300,1,怪獣８号いいジャン祭
e,mission_reward_19,202509010,kai_00001_event_reward_10,Item,memory_chara_kai_00501,350,1,怪獣８号いいジャン祭
e,mission_reward_20,202509010,kai_00001_event_reward_11,Emblem,emblem_event_kai_00001,1,1,怪獣８号いいジャン祭
e,mission_reward_21,202509010,kai_00001_event_reward_12,Item,piece_kai_00501,75,1,怪獣８号いいジャン祭
e,mission_reward_22,202509010,kai_00001_event_reward_13,Item,piece_kai_00501,100,1,怪獣８号いいジャン祭
e,mission_reward_23,202509010,kai_00001_event_reward_14,FreeDiamond,,50,1,怪獣８号いいジャン祭
e,mission_reward_24,202509010,kai_00001_event_reward_15,Coin,,12500,1,怪獣８号いいジャン祭
e,mission_reward_25,202509010,kai_00001_event_reward_16,Coin,,12500,1,怪獣８号いいジャン祭
e,mission_reward_26,202509010,kai_00001_event_reward_17,Item,ticket_glo_00002,3,1,怪獣８号いいジャン祭
e,mission_reward_27,202509010,kai_00001_event_reward_18,Item,ticket_glo_00003,1,1,怪獣８号いいジャン祭
e,mission_reward_28,202509010,kai_00001_event_reward_19,Item,memoryfragment_glo_00001,5,1,怪獣８号いいジャン祭
e,mission_reward_29,202509010,kai_00001_event_reward_20,Item,memory_glo_00003,150,1,怪獣８号いいジャン祭
e,mission_reward_30,202509010,kai_00001_event_reward_21,Item,memoryfragment_glo_00002,5,1,怪獣８号いいジャン祭
e,mission_reward_31,202509010,kai_00001_event_reward_22,Item,memory_glo_00003,250,1,怪獣８号いいジャン祭
e,mission_reward_32,202509010,kai_00001_event_reward_23,Item,memoryfragment_glo_00003,2,1,怪獣８号いいジャン祭
e,mission_reward_33,202509010,kai_00001_event_reward_24,Emblem,emblem_event_kai_00002,1,1,怪獣８号いいジャン祭
e,mission_reward_34,202509010,event_kai_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_35,202509010,event_kai_00001_daily_bonus_2,Coin,,2500,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_36,202509010,event_kai_00001_daily_bonus_3,FreeDiamond,,30,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_37,202509010,event_kai_00001_daily_bonus_4,Item,memory_glo_00003,100,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_38,202509010,event_kai_00001_daily_bonus_5,Item,memoryfragment_glo_00001,5,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_39,202509010,event_kai_00001_daily_bonus_6,Item,memory_glo_00003,100,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_40,202509010,event_kai_00001_daily_bonus_7,Item,memoryfragment_glo_00002,3,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_41,202509010,event_kai_00001_daily_bonus_8,Coin,,2500,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_42,202509010,event_kai_00001_daily_bonus_9,Item,memory_glo_00003,100,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_43,202509010,event_kai_00001_daily_bonus_10,FreeDiamond,,30,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_44,202509010,event_kai_00001_daily_bonus_11,Item,memory_glo_00003,200,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_45,202509010,event_kai_00001_daily_bonus_12,Item,ticket_glo_00003,1,1,怪獣８号いいジャン祭_ログボ
e,mission_reward_46,202509010,mission_reward_beginner_bonus_2_1,FreeDiamond,,200,1,初心者ミッションボーナス1
e,mission_reward_47,202509010,mission_reward_beginner_bonus_2_2,FreeDiamond,,150,1,初心者ミッションボーナス2
e,mission_reward_48,202509010,mission_reward_beginner_bonus_2_2,Coin,,50000,1,初心者ミッションボーナス2
e,mission_reward_49,202509010,mission_reward_beginner_bonus_2_2,Item,ticket_glo_00002,1,1,初心者ミッションボーナス2
e,mission_reward_50,202509010,mission_reward_beginner_bonus_2_3,Item,memory_glo_00002,500,1,初心者ミッションボーナス3
e,mission_reward_51,202509010,mission_reward_beginner_bonus_2_3,Item,memory_glo_00004,500,1,初心者ミッションボーナス3
e,mission_reward_52,202509010,mission_reward_beginner_bonus_2_3,Item,memory_glo_00001,250,1,初心者ミッションボーナス3
e,mission_reward_53,202509010,mission_reward_beginner_bonus_2_4,Item,memory_glo_00003,500,1,初心者ミッションボーナス4
e,mission_reward_54,202509010,mission_reward_beginner_bonus_2_4,Item,memory_glo_00005,500,1,初心者ミッションボーナス4
e,mission_reward_55,202509010,mission_reward_beginner_bonus_2_4,Item,memory_glo_00001,250,1,初心者ミッションボーナス4
e,mission_reward_56,202509010,mission_reward_beginner_bonus_2_5,FreeDiamond,,150,1,初心者ミッションボーナス5
e,mission_reward_57,202509010,mission_reward_beginner_bonus_2_5,Coin,,50000,1,初心者ミッションボーナス5
e,mission_reward_58,202509010,mission_reward_beginner_bonus_2_5,Item,ticket_glo_00002,1,1,初心者ミッションボーナス5
e,mission_reward_59,202509010,mission_reward_beginner_bonus_2_6,Item,memoryfragment_glo_00001,40,1,初心者ミッションボーナス6
e,mission_reward_60,202509010,mission_reward_beginner_bonus_2_6,Item,memoryfragment_glo_00002,30,1,初心者ミッションボーナス6
e,mission_reward_61,202509010,mission_reward_beginner_bonus_2_6,Item,memoryfragment_glo_00003,5,1,初心者ミッションボーナス6
e,mission_reward_62,202509010,mission_reward_beginner_bonus_2_7,Coin,,100000,1,初心者ミッションボーナス7
e,mission_reward_63,202509010,mission_reward_beginner_bonus_2_7,Item,ticket_glo_00002,3,1,初心者ミッションボーナス7
e,mission_reward_64,202509010,mission_reward_beginner_bonus_2_8,FreeDiamond,,1000,1,初心者ミッションボーナス8
e,mission_reward_65,202509010,mission_reward_beginner_bonus_2_8,Item,ticket_glo_00203,1,1,初心者ミッションボーナス8
e,mission_reward_66,202509010,daily_reward_2_1,FreeDiamond,,10,1,デイリーミッション
e,mission_reward_67,202509010,daily_reward_2_2,Coin,,1500,1,デイリーミッション
e,mission_reward_68,202509010,daily_reward_2_3,FreeDiamond,,10,1,デイリーミッション
e,mission_reward_69,202509010,daily_reward_2_4,Coin,,1500,1,デイリーミッション
e,mission_reward_70,202509010,daily_reward_2_5,FreeDiamond,,30,1,デイリーミッション
e,mission_reward_71,202509010,weekly_reward_2_1,FreeDiamond,,20,1,ウィークリーミッション
e,mission_reward_72,202509010,weekly_reward_2_2,Coin,,5000,1,ウィークリーミッション
e,mission_reward_73,202509010,weekly_reward_2_3,FreeDiamond,,30,1,ウィークリーミッション
e,mission_reward_74,202509010,weekly_reward_2_4,Coin,,5000,1,ウィークリーミッション
e,mission_reward_75,202509010,weekly_reward_2_5,FreeDiamond,,50,1,ウィークリーミッション
e,mission_reward_76,202509010,achievement_2_1,FreeDiamond,,100,1,アチーブメントミッション
e,mission_reward_77,202509010,achievement_2_2,FreeDiamond,,1500,1,アチーブメントミッション
e,mission_reward_78,202509010,achievement_2_3,FreeDiamond,,500,1,アチーブメントミッション
e,mission_reward_79,202509010,achievement_2_4,FreeDiamond,,100,1,アチーブメントミッション
e,mission_reward_80,202509010,achievement_2_5,FreeDiamond,,100,1,アチーブメントミッション
e,mission_reward_81,202509010,achievement_2_6,FreeDiamond,,100,1,アチーブメントミッション
e,mission_reward_82,202509010,achievement_2_7,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_83,202509010,achievement_2_8,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_84,202509010,achievement_2_9,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_85,202509010,achievement_2_10,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_86,202509010,achievement_2_11,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_87,202509010,achievement_2_12,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_88,202509010,achievement_2_13,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_89,202509010,achievement_2_14,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_90,202509010,achievement_2_15,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_91,202509010,achievement_2_16,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_92,202509010,achievement_2_17,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_93,202509010,achievement_2_18,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_94,202509010,achievement_2_19,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_95,202509010,achievement_2_20,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_96,202509010,achievement_2_21,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_97,202509010,achievement_2_22,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_98,202509010,achievement_2_23,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_99,202509010,achievement_2_24,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_100,202509010,achievement_2_25,FreeDiamond,,300,1,アチーブメントミッション
e,mission_reward_101,202509010,achievement_2_26,Item,box_glo_00002,20,1,アチーブメントミッション
e,mission_reward_102,202509010,achievement_2_27,Item,box_glo_00003,10,1,アチーブメントミッション
e,mission_reward_103,202509010,achievement_2_28,Item,box_glo_00004,10,1,アチーブメントミッション
e,mission_reward_104,202509010,achievement_2_29,Item,memoryfragment_glo_00001,40,1,アチーブメントミッション
e,mission_reward_105,202509010,achievement_2_30,Item,memoryfragment_glo_00002,30,1,アチーブメントミッション
e,mission_reward_106,202509010,achievement_2_31,Item,memoryfragment_glo_00003,5,1,アチーブメントミッション
e,mission_reward_107,202509010,achievement_2_32,Item,memoryfragment_glo_00001,75,1,アチーブメントミッション
e,mission_reward_108,202509010,achievement_2_33,Item,memoryfragment_glo_00002,50,1,アチーブメントミッション
e,mission_reward_109,202509010,achievement_2_34,Item,memoryfragment_glo_00003,8,1,アチーブメントミッション
e,mission_reward_110,202509010,achievement_2_35,Item,ticket_glo_00002,10,1,アチーブメントミッション
e,mission_reward_111,202509010,achievement_2_36,Item,memoryfragment_glo_00001,40,1,アチーブメントミッション
e,mission_reward_112,202509010,achievement_2_37,Item,memoryfragment_glo_00002,30,1,アチーブメントミッション
e,mission_reward_113,202509010,achievement_2_38,Item,memoryfragment_glo_00003,5,1,アチーブメントミッション
e,mission_reward_114,202509010,achievement_2_39,Item,box_glo_00002,30,1,アチーブメントミッション
e,mission_reward_115,202509010,achievement_2_40,Item,box_glo_00003,15,1,アチーブメントミッション
e,mission_reward_116,202509010,achievement_2_41,Item,box_glo_00004,15,1,アチーブメントミッション
e,mission_reward_117,202509010,achievement_2_42,Item,box_glo_00003,25,1,アチーブメントミッション
e,mission_reward_118,202509010,achievement_2_43,Item,box_glo_00004,25,1,アチーブメントミッション
e,mission_reward_119,202509010,achievement_2_44,Item,memoryfragment_glo_00001,30,1,アチーブメントミッション
e,mission_reward_120,202509010,achievement_2_45,Item,memoryfragment_glo_00001,40,1,アチーブメントミッション
e,mission_reward_121,202509010,achievement_2_46,Item,memoryfragment_glo_00002,40,1,アチーブメントミッション
e,mission_reward_122,202509010,achievement_2_47,Item,memoryfragment_glo_00003,6,1,アチーブメントミッション
e,mission_reward_123,202509010,achievement_2_48,FreeDiamond,,10,1,アチーブメントミッション
e,mission_reward_124,202509010,achievement_2_49,FreeDiamond,,10,1,アチーブメントミッション
e,mission_reward_125,202509010,achievement_2_50,FreeDiamond,,10,1,アチーブメントミッション
e,mission_reward_126,202509010,achievement_2_51,FreeDiamond,,10,1,アチーブメントミッション
e,mission_reward_127,202509010,achievement_2_52,FreeDiamond,,10,1,アチーブメントミッション
e,mission_reward_128,202509010,achievement_2_53,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_129,202509010,achievement_2_54,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_130,202509010,achievement_2_55,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_131,202509010,achievement_2_56,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_132,202509010,achievement_2_57,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_133,202509010,achievement_2_58,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_134,202509010,achievement_2_59,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_135,202509010,achievement_2_60,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_136,202509010,achievement_2_61,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_137,202509010,achievement_2_62,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_138,202509010,achievement_2_63,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_139,202509010,achievement_2_64,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_140,202509010,achievement_2_65,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_141,202509010,achievement_2_66,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_142,202509010,achievement_2_67,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_143,202509010,achievement_2_68,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_144,202509010,achievement_2_69,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_145,202509010,achievement_2_70,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_146,202509010,achievement_2_71,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_147,202509010,achievement_2_72,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_148,202509010,achievement_2_73,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_149,202509010,achievement_2_74,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_150,202509010,achievement_2_75,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_151,202509010,achievement_2_76,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_152,202509010,achievement_2_77,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_153,202509010,achievement_2_78,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_154,202509010,achievement_2_79,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_155,202509010,achievement_2_80,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_156,202509010,achievement_2_81,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_157,202509010,achievement_2_82,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_158,202509010,achievement_2_83,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_159,202509010,achievement_2_84,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_160,202509010,achievement_2_85,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_161,202509010,achievement_2_86,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_162,202509010,achievement_2_87,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_163,202509010,achievement_2_88,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_164,202509010,achievement_2_89,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_165,202509010,achievement_2_90,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_166,202509010,achievement_2_91,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_167,202509010,achievement_2_92,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_168,202509010,achievement_2_93,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_169,202509010,achievement_2_94,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_170,202509010,achievement_2_95,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_171,202509010,achievement_2_96,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_172,202509010,achievement_2_97,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_173,202509010,achievement_2_98,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_174,202509010,achievement_2_99,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_175,202509010,achievement_2_100,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_176,202509010,kai_00001_limited_term_1,Coin,,2000,1,降臨バトル「怪獣退治の時間︎」
e,mission_reward_177,202509010,kai_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「怪獣退治の時間︎」
e,mission_reward_178,202509010,kai_00001_limited_term_3,Coin,,3000,1,降臨バトル「怪獣退治の時間︎」
e,mission_reward_179,202509010,kai_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「怪獣退治の時間︎」
e,mission_reward_180,202510010,event_spy_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,spyいいジャン祭_ログボ
e,mission_reward_181,202510010,event_spy_00001_daily_bonus_2,Coin,,2500,1,spyいいジャン祭_ログボ
e,mission_reward_182,202510010,event_spy_00001_daily_bonus_3,Item,memory_glo_00003,200,1,spyいいジャン祭_ログボ
e,mission_reward_183,202510010,event_spy_00001_daily_bonus_4,Item,ticket_glo_00002,1,1,spyいいジャン祭_ログボ
e,mission_reward_184,202510010,event_spy_00001_daily_bonus_5,Item,memoryfragment_glo_00001,2,1,spyいいジャン祭_ログボ
e,mission_reward_185,202510010,event_spy_00001_daily_bonus_6,Item,memoryfragment_glo_00002,2,1,spyいいジャン祭_ログボ
e,mission_reward_186,202510010,event_spy_00001_daily_bonus_7,Item,memory_glo_00004,200,1,spyいいジャン祭_ログボ
e,mission_reward_187,202510010,event_spy_00001_daily_bonus_8,FreeDiamond,,30,1,spyいいジャン祭_ログボ
e,mission_reward_188,202510010,event_spy_00001_daily_bonus_9,Item,ticket_glo_00003,1,1,spyいいジャン祭_ログボ
e,mission_reward_189,202510010,event_spy_00001_daily_bonus_10,Coin,,2500,1,spyいいジャン祭_ログボ
e,mission_reward_190,202510010,event_spy_00001_daily_bonus_11,Item,memory_glo_00002,200,1,spyいいジャン祭_ログボ
e,mission_reward_191,202510010,event_spy_00001_daily_bonus_12,Item,ticket_glo_00002,1,1,spyいいジャン祭_ログボ
e,mission_reward_192,202510010,event_spy_00001_daily_bonus_13,Item,memoryfragment_glo_00001,3,1,spyいいジャン祭_ログボ
e,mission_reward_193,202510010,event_spy_00001_daily_bonus_14,Item,memoryfragment_glo_00002,3,1,spyいいジャン祭_ログボ
e,mission_reward_194,202510010,event_spy_00001_daily_bonus_15,FreeDiamond,,30,1,spyいいジャン祭_ログボ
e,mission_reward_195,202510010,event_spy_00001_daily_bonus_16,Item,memoryfragment_glo_00003,1,1,spyいいジャン祭_ログボ
e,mission_reward_196,202510010,spy_00001_limited_term_1,Coin,,2000,1,降臨バトル「すべてはよりよき世界のために…!!」
e,mission_reward_197,202510010,spy_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「すべてはよりよき世界のために…!!」
e,mission_reward_198,202510010,spy_00001_limited_term_3,Coin,,3000,1,降臨バトル「すべてはよりよき世界のために…!!」
e,mission_reward_199,202510010,spy_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「すべてはよりよき世界のために…!!」
e,mission_reward_200,202510010,spy_00001_event_reward_01,Item,memory_chara_spy_00401,200,1,spyいいジャン祭_ミッション
e,mission_reward_201,202510010,spy_00001_event_reward_02,Item,memory_chara_spy_00401,300,1,spyいいジャン祭_ミッション
e,mission_reward_202,202510010,spy_00001_event_reward_03,Item,memory_chara_spy_00401,350,1,spyいいジャン祭_ミッション
e,mission_reward_203,202510010,spy_00001_event_reward_04,Item,ticket_glo_00003,1,1,spyいいジャン祭_ミッション
e,mission_reward_204,202510010,spy_00001_event_reward_05,Item,piece_spy_00401,75,1,spyいいジャン祭_ミッション
e,mission_reward_205,202510010,spy_00001_event_reward_06,Item,piece_spy_00401,100,1,spyいいジャン祭_ミッション
e,mission_reward_206,202510010,spy_00001_event_reward_07,FreeDiamond,,50,1,spyいいジャン祭_ミッション
e,mission_reward_207,202510010,spy_00001_event_reward_08,Item,memory_chara_spy_00301,200,1,spyいいジャン祭_ミッション
e,mission_reward_208,202510010,spy_00001_event_reward_09,Item,memory_chara_spy_00301,300,1,spyいいジャン祭_ミッション
e,mission_reward_209,202510010,spy_00001_event_reward_10,Item,memory_chara_spy_00301,350,1,spyいいジャン祭_ミッション
e,mission_reward_210,202510010,spy_00001_event_reward_11,Item,ticket_glo_00003,1,1,spyいいジャン祭_ミッション
e,mission_reward_211,202510010,spy_00001_event_reward_12,Item,piece_spy_00301,75,1,spyいいジャン祭_ミッション
e,mission_reward_212,202510010,spy_00001_event_reward_13,Item,piece_spy_00301,100,1,spyいいジャン祭_ミッション
e,mission_reward_213,202510010,spy_00001_event_reward_14,FreeDiamond,,50,1,spyいいジャン祭_ミッション
e,mission_reward_214,202510010,spy_00001_event_reward_15,Coin,,12500,1,spyいいジャン祭_ミッション
e,mission_reward_215,202510010,spy_00001_event_reward_16,Coin,,12500,1,spyいいジャン祭_ミッション
e,mission_reward_216,202510010,spy_00001_event_reward_17,Item,ticket_glo_00002,3,1,spyいいジャン祭_ミッション
e,mission_reward_217,202510010,spy_00001_event_reward_18,Item,ticket_glo_00003,1,1,spyいいジャン祭_ミッション
e,mission_reward_218,202510010,spy_00001_event_reward_19,Item,memoryfragment_glo_00001,5,1,spyいいジャン祭_ミッション
e,mission_reward_219,202510010,spy_00001_event_reward_20,Item,memory_glo_00003,150,1,spyいいジャン祭_ミッション
e,mission_reward_220,202510010,spy_00001_event_reward_21,Item,memoryfragment_glo_00002,5,1,spyいいジャン祭_ミッション
e,mission_reward_221,202510010,spy_00001_event_reward_22,Item,memory_glo_00003,250,1,spyいいジャン祭_ミッション
e,mission_reward_222,202510010,spy_00001_event_reward_23,Item,memoryfragment_glo_00002,10,1,spyいいジャン祭_ミッション
e,mission_reward_223,202510010,spy_00001_event_reward_24,Item,memoryfragment_glo_00003,2,1,spyいいジャン祭_ミッション
e,mission_reward_224,202510020,event_dan_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,danいいジャン祭_ログボ
e,mission_reward_225,202510020,event_dan_00001_daily_bonus_2,Coin,,2500,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_226,202510020,event_dan_00001_daily_bonus_3,Item,memory_glo_00005,100,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_227,202510020,event_dan_00001_daily_bonus_4,Item,ticket_glo_00002,1,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_228,202510020,event_dan_00001_daily_bonus_5,Item,memoryfragment_glo_00001,2,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_229,202510020,event_dan_00001_daily_bonus_6,Item,memoryfragment_glo_00002,2,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_230,202510020,event_dan_00001_daily_bonus_7,Item,memory_glo_00005,100,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_231,202510020,event_dan_00001_daily_bonus_8,FreeDiamond,,30,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_232,202510020,event_dan_00001_daily_bonus_9,Item,ticket_glo_00003,1,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_233,202510020,event_dan_00001_daily_bonus_10,Coin,,2500,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_234,202510020,event_dan_00001_daily_bonus_11,Item,ticket_glo_00002,1,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_235,202510020,event_dan_00001_daily_bonus_12,Item,memoryfragment_glo_00001,3,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_236,202510020,event_dan_00001_daily_bonus_13,Item,memoryfragment_glo_00002,3,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_237,202510020,event_dan_00001_daily_bonus_14,FreeDiamond,,30,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_238,202510020,event_dan_00001_daily_bonus_15,Item,memoryfragment_glo_00003,1,1,ダンダダンいいジャン祭_ログボ
e,mission_reward_239,202510020,dan_00001_limited_term_1,Coin,,2000,1,降臨バトル「ダンダダン」に5回挑戦しよう！
e,mission_reward_240,202510020,dan_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「ダンダダン」に10回挑戦しよう！
e,mission_reward_241,202510020,dan_00001_limited_term_3,Coin,,3000,1,降臨バトル「ダンダダン」に20回挑戦しよう！
e,mission_reward_242,202510020,dan_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「ダンダダン」に30回挑戦しよう！
e,mission_reward_243,202510020,dan_00001_event_reward_01,Item,memory_chara_dan_00301,200,1,danいいジャン祭_ミッション
e,mission_reward_244,202510020,dan_00001_event_reward_02,Item,memory_chara_dan_00301,300,1,danいいジャン祭_ミッション
e,mission_reward_245,202510020,dan_00001_event_reward_03,Item,memory_chara_dan_00301,350,1,danいいジャン祭_ミッション
e,mission_reward_246,202510020,dan_00001_event_reward_04,Item,ticket_glo_00003,1,1,danいいジャン祭_ミッション
e,mission_reward_247,202510020,dan_00001_event_reward_05,Item,piece_dan_00301,75,1,danいいジャン祭_ミッション
e,mission_reward_248,202510020,dan_00001_event_reward_06,Item,piece_dan_00301,100,1,danいいジャン祭_ミッション
e,mission_reward_249,202510020,dan_00001_event_reward_07,Emblem,emblem_event_dan_00001,1,1,danいいジャン祭_ミッション
e,mission_reward_250,202510020,dan_00001_event_reward_08,Item,memory_chara_dan_00201,200,1,danいいジャン祭_ミッション
e,mission_reward_251,202510020,dan_00001_event_reward_09,Item,memory_chara_dan_00201,300,1,danいいジャン祭_ミッション
e,mission_reward_252,202510020,dan_00001_event_reward_10,Item,memory_chara_dan_00201,350,1,danいいジャン祭_ミッション
e,mission_reward_253,202510020,dan_00001_event_reward_11,Item,ticket_glo_00003,1,1,danいいジャン祭_ミッション
e,mission_reward_254,202510020,dan_00001_event_reward_12,Item,piece_dan_00201,75,1,danいいジャン祭_ミッション
e,mission_reward_255,202510020,dan_00001_event_reward_13,Item,piece_dan_00201,100,1,danいいジャン祭_ミッション
e,mission_reward_256,202510020,dan_00001_event_reward_14,FreeDiamond,,50,1,danいいジャン祭_ミッション
e,mission_reward_257,202510020,dan_00001_event_reward_15,Coin,,12500,1,danいいジャン祭_ミッション
e,mission_reward_258,202510020,dan_00001_event_reward_16,Coin,,12500,1,danいいジャン祭_ミッション
e,mission_reward_259,202510020,dan_00001_event_reward_17,Item,ticket_glo_00002,3,1,danいいジャン祭_ミッション
e,mission_reward_260,202510020,dan_00001_event_reward_18,Item,ticket_glo_00003,1,1,danいいジャン祭_ミッション
e,mission_reward_261,202510020,dan_00001_event_reward_19,Item,memoryfragment_glo_00001,5,1,danいいジャン祭_ミッション
e,mission_reward_262,202510020,dan_00001_event_reward_20,Item,memory_glo_00005,150,1,danいいジャン祭_ミッション
e,mission_reward_263,202510020,dan_00001_event_reward_21,Item,memoryfragment_glo_00002,5,1,danいいジャン祭_ミッション
e,mission_reward_264,202510020,dan_00001_event_reward_22,Item,memory_glo_00005,250,1,danいいジャン祭_ミッション
e,mission_reward_265,202510020,dan_00001_event_reward_23,Item,memoryfragment_glo_00002,10,1,danいいジャン祭_ミッション
e,mission_reward_266,202510020,dan_00001_event_reward_24,Item,memoryfragment_glo_00003,2,1,danいいジャン祭_ミッション
e,mission_reward_267,202511010,event_mag_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_268,202511010,event_mag_00001_daily_bonus_2,Coin,,3000,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_269,202511010,event_mag_00001_daily_bonus_3,FreeDiamond,,30,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_270,202511010,event_mag_00001_daily_bonus_4,Item,memory_glo_00002,150,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_271,202511010,event_mag_00001_daily_bonus_5,Item,ticket_glo_00002,1,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_272,202511010,event_mag_00001_daily_bonus_6,Item,memoryfragment_glo_00001,5,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_273,202511010,event_mag_00001_daily_bonus_7,Item,memoryfragment_glo_00002,2,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_274,202511010,event_mag_00001_daily_bonus_8,Item,memory_glo_00005,100,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_275,202511010,event_mag_00001_daily_bonus_9,FreeDiamond,,30,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_276,202511010,event_mag_00001_daily_bonus_10,Coin,,4000,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_277,202511010,event_mag_00001_daily_bonus_11,Item,memory_glo_00002,150,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_278,202511010,event_mag_00001_daily_bonus_12,Item,ticket_glo_00003,1,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_279,202511010,event_mag_00001_daily_bonus_13,Item,memoryfragment_glo_00001,5,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_280,202511010,event_mag_00001_daily_bonus_14,Item,memory_glo_00005,100,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_281,202511010,event_mag_00001_daily_bonus_15,Item,memoryfragment_glo_00002,3,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_282,202511010,event_mag_00001_daily_bonus_16,FreeDiamond,,30,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_283,202511010,event_mag_00001_daily_bonus_17,Item,memoryfragment_glo_00003,1,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_284,202511010,event_mag_00001_daily_bonus_18,Item,ticket_glo_00002,1,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_285,202511010,event_mag_00001_daily_bonus_19,Item,memory_glo_00005,100,1,"株式会社マジルミエ いいジャン祭 特別ログインボーナス"
e,mission_reward_286,202511010,mag_00001_limited_term_1,Coin,,2000,1,降臨バトル「業務実行！！」に5回挑戦しよう！
e,mission_reward_287,202511010,mag_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「業務実行！！」に10回挑戦しよう！
e,mission_reward_288,202511010,mag_00001_limited_term_3,Coin,,3000,1,降臨バトル「業務実行！！」に20回挑戦しよう！
e,mission_reward_289,202511010,mag_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「業務実行！！」に30回挑戦しよう！
e,mission_reward_290,202511010,mag_00001_event_reward_01,Item,memory_chara_mag_00501,200,1,magいいジャン祭_ミッション
e,mission_reward_291,202511010,mag_00001_event_reward_02,Item,memory_chara_mag_00501,300,1,magいいジャン祭_ミッション
e,mission_reward_292,202511010,mag_00001_event_reward_03,Item,memory_chara_mag_00501,350,1,magいいジャン祭_ミッション
e,mission_reward_293,202511010,mag_00001_event_reward_04,Item,ticket_glo_00003,1,1,magいいジャン祭_ミッション
e,mission_reward_294,202511010,mag_00001_event_reward_05,Item,piece_mag_00501,75,1,magいいジャン祭_ミッション
e,mission_reward_295,202511010,mag_00001_event_reward_06,Item,piece_mag_00501,100,1,magいいジャン祭_ミッション
e,mission_reward_296,202511010,mag_00001_event_reward_07,Emblem,emblem_event_mag_00001,1,1,magいいジャン祭_ミッション
e,mission_reward_297,202511010,mag_00001_event_reward_08,Item,memory_chara_mag_00401,200,1,magいいジャン祭_ミッション
e,mission_reward_298,202511010,mag_00001_event_reward_09,Item,memory_chara_mag_00401,300,1,magいいジャン祭_ミッション
e,mission_reward_299,202511010,mag_00001_event_reward_10,Item,memory_chara_mag_00401,350,1,magいいジャン祭_ミッション
e,mission_reward_300,202511010,mag_00001_event_reward_11,Item,ticket_glo_00003,1,1,magいいジャン祭_ミッション
e,mission_reward_301,202511010,mag_00001_event_reward_12,Item,piece_mag_00401,75,1,magいいジャン祭_ミッション
e,mission_reward_302,202511010,mag_00001_event_reward_13,Item,piece_mag_00401,100,1,magいいジャン祭_ミッション
e,mission_reward_303,202511010,mag_00001_event_reward_14,FreeDiamond,,50,1,magいいジャン祭_ミッション
e,mission_reward_304,202511010,mag_00001_event_reward_15,Coin,,12500,1,magいいジャン祭_ミッション
e,mission_reward_305,202511010,mag_00001_event_reward_16,Coin,,12500,1,magいいジャン祭_ミッション
e,mission_reward_306,202511010,mag_00001_event_reward_17,Item,ticket_glo_00002,3,1,magいいジャン祭_ミッション
e,mission_reward_307,202511010,mag_00001_event_reward_18,Item,ticket_glo_00003,1,1,magいいジャン祭_ミッション
e,mission_reward_308,202511010,mag_00001_event_reward_19,Item,memoryfragment_glo_00001,5,1,magいいジャン祭_ミッション
e,mission_reward_309,202511010,mag_00001_event_reward_20,Item,memory_glo_00005,150,1,magいいジャン祭_ミッション
e,mission_reward_310,202511010,mag_00001_event_reward_21,Item,memoryfragment_glo_00002,5,1,magいいジャン祭_ミッション
e,mission_reward_311,202511010,mag_00001_event_reward_22,Item,memory_glo_00002,200,1,magいいジャン祭_ミッション
e,mission_reward_312,202511010,mag_00001_event_reward_23,Item,memoryfragment_glo_00002,10,1,magいいジャン祭_ミッション
e,mission_reward_313,202511010,mag_00001_event_reward_24,Item,memoryfragment_glo_00003,2,1,magいいジャン祭_ミッション
e,mission_reward_314,202511010,kai_00002_limited_term_1,Coin,,2000,1,降臨バトル「怪獣退治の時間︎」に5回挑戦しよう！
e,mission_reward_315,202511010,kai_00002_limited_term_2,FreeDiamond,,20,1,降臨バトル「怪獣退治の時間︎」に10回挑戦しよう！
e,mission_reward_316,202511010,kai_00002_limited_term_3,Coin,,3000,1,降臨バトル「怪獣退治の時間︎」に20回挑戦しよう！
e,mission_reward_317,202511010,kai_00002_limited_term_4,FreeDiamond,,30,1,降臨バトル「怪獣退治の時間︎」に30回挑戦しよう！
e,mission_reward_318,202511020,event_yuw_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_319,202511020,event_yuw_00001_daily_bonus_2,Coin,,10000,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_320,202511020,event_yuw_00001_daily_bonus_3,FreeDiamond,,30,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_321,202511020,event_yuw_00001_daily_bonus_4,Item,memory_glo_00002,200,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_322,202511020,event_yuw_00001_daily_bonus_5,Item,memory_glo_00003,200,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_323,202511020,event_yuw_00001_daily_bonus_6,Item,memoryfragment_glo_00001,10,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_324,202511020,event_yuw_00001_daily_bonus_7,FreeDiamond,,30,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_325,202511020,event_yuw_00001_daily_bonus_8,Item,ticket_glo_00002,1,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_326,202511020,event_yuw_00001_daily_bonus_9,Item,memoryfragment_glo_00002,5,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_327,202511020,event_yuw_00001_daily_bonus_10,Item,memory_glo_00002,200,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_328,202511020,event_yuw_00001_daily_bonus_11,Item,memory_glo_00003,200,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_329,202511020,event_yuw_00001_daily_bonus_12,Item,ticket_glo_00002,1,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_330,202511020,event_yuw_00001_daily_bonus_13,Item,ticket_glo_00003,1,1,"2.5次元の誘惑 いいジャン祭 特別ログインボーナス"
e,mission_reward_331,202511020,yuw_00001_limited_term_1,Coin,,2000,1,降臨バトル「夏コミの魔物」に5回挑戦しよう！
e,mission_reward_332,202511020,yuw_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「夏コミの魔物」に10回挑戦しよう！
e,mission_reward_333,202511020,yuw_00001_limited_term_3,Coin,,3000,1,降臨バトル「夏コミの魔物」に20回挑戦しよう！
e,mission_reward_334,202511020,yuw_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「夏コミの魔物」に30回挑戦しよう！
e,mission_reward_335,202511020,yuw_00001_event_reward_01,Item,memory_chara_yuw_00501,200,1,yuwいいジャン祭_ミッション
e,mission_reward_336,202511020,yuw_00001_event_reward_02,Item,memory_chara_yuw_00501,300,1,yuwいいジャン祭_ミッション
e,mission_reward_337,202511020,yuw_00001_event_reward_03,Item,memory_chara_yuw_00501,350,1,yuwいいジャン祭_ミッション
e,mission_reward_338,202511020,yuw_00001_event_reward_04,Item,ticket_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_339,202511020,yuw_00001_event_reward_05,Item,piece_yuw_00501,10,1,yuwいいジャン祭_ミッション
e,mission_reward_340,202511020,yuw_00001_event_reward_06,Item,piece_yuw_00501,20,1,yuwいいジャン祭_ミッション
e,mission_reward_341,202511020,yuw_00001_event_reward_07,Item,piece_yuw_00501,20,1,yuwいいジャン祭_ミッション
e,mission_reward_342,202511020,yuw_00001_event_reward_08,Item,piece_yuw_00501,20,1,yuwいいジャン祭_ミッション
e,mission_reward_343,202511020,yuw_00001_event_reward_09,Item,piece_yuw_00501,30,1,yuwいいジャン祭_ミッション
e,mission_reward_344,202511020,yuw_00001_event_reward_10,Item,memoryfragment_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_345,202511020,yuw_00001_event_reward_11,FreeDiamond,,50,1,yuwいいジャン祭_ミッション
e,mission_reward_346,202511020,yuw_00001_event_reward_12,Item,memory_chara_yuw_00601,200,1,yuwいいジャン祭_ミッション
e,mission_reward_347,202511020,yuw_00001_event_reward_13,Item,memory_chara_yuw_00601,300,1,yuwいいジャン祭_ミッション
e,mission_reward_348,202511020,yuw_00001_event_reward_14,Item,memory_chara_yuw_00601,350,1,yuwいいジャン祭_ミッション
e,mission_reward_349,202511020,yuw_00001_event_reward_15,Item,ticket_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_350,202511020,yuw_00001_event_reward_16,Item,piece_yuw_00601,10,1,yuwいいジャン祭_ミッション
e,mission_reward_351,202511020,yuw_00001_event_reward_17,Item,piece_yuw_00601,20,1,yuwいいジャン祭_ミッション
e,mission_reward_352,202511020,yuw_00001_event_reward_18,Item,piece_yuw_00601,20,1,yuwいいジャン祭_ミッション
e,mission_reward_353,202511020,yuw_00001_event_reward_19,Item,piece_yuw_00601,20,1,yuwいいジャン祭_ミッション
e,mission_reward_354,202511020,yuw_00001_event_reward_20,Item,piece_yuw_00601,30,1,yuwいいジャン祭_ミッション
e,mission_reward_355,202511020,yuw_00001_event_reward_21,Item,memoryfragment_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_356,202511020,yuw_00001_event_reward_22,FreeDiamond,,50,1,yuwいいジャン祭_ミッション
e,mission_reward_357,202511020,yuw_00001_event_reward_23,Coin,,15000,1,yuwいいジャン祭_ミッション
e,mission_reward_358,202511020,yuw_00001_event_reward_24,Coin,,15000,1,yuwいいジャン祭_ミッション
e,mission_reward_359,202511020,yuw_00001_event_reward_25,Item,ticket_glo_00002,3,1,yuwいいジャン祭_ミッション
e,mission_reward_360,202511020,yuw_00001_event_reward_26,Item,ticket_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_361,202511020,yuw_00001_event_reward_27,Item,memoryfragment_glo_00001,5,1,yuwいいジャン祭_ミッション
e,mission_reward_362,202511020,yuw_00001_event_reward_28,Item,memoryfragment_glo_00001,5,1,yuwいいジャン祭_ミッション
e,mission_reward_363,202511020,yuw_00001_event_reward_29,Item,memoryfragment_glo_00001,5,1,yuwいいジャン祭_ミッション
e,mission_reward_364,202511020,yuw_00001_event_reward_30,Item,memoryfragment_glo_00001,5,1,yuwいいジャン祭_ミッション
e,mission_reward_365,202511020,yuw_00001_event_reward_31,Item,memoryfragment_glo_00001,10,1,yuwいいジャン祭_ミッション
e,mission_reward_366,202511020,yuw_00001_event_reward_32,Item,memoryfragment_glo_00002,5,1,yuwいいジャン祭_ミッション
e,mission_reward_367,202511020,yuw_00001_event_reward_33,Item,memoryfragment_glo_00002,5,1,yuwいいジャン祭_ミッション
e,mission_reward_368,202511020,yuw_00001_event_reward_34,Item,memoryfragment_glo_00002,5,1,yuwいいジャン祭_ミッション
e,mission_reward_369,202511020,yuw_00001_event_reward_35,Item,memoryfragment_glo_00002,5,1,yuwいいジャン祭_ミッション
e,mission_reward_370,202511020,yuw_00001_event_reward_36,Item,memoryfragment_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_371,202511020,yuw_00001_event_reward_37,Item,memoryfragment_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_372,202511020,yuw_00001_event_reward_38,Item,memoryfragment_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_373,202511020,yuw_00001_event_reward_39,Item,ticket_glo_00003,1,1,yuwいいジャン祭_ミッション
e,mission_reward_374,202512010,event_sur_00001_daily_bonus_1,Item,ticket_glo_00003,1,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_375,202512010,event_sur_00001_daily_bonus_2,Coin,,5000,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_376,202512010,event_sur_00001_daily_bonus_3,FreeDiamond,,20,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_377,202512010,event_sur_00001_daily_bonus_4,Item,ticket_glo_00002,1,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_378,202512010,event_sur_00001_daily_bonus_5,Item,memory_glo_00002,300,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_379,202512010,event_sur_00001_daily_bonus_6,Item,memory_glo_00004,300,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_380,202512010,event_sur_00001_daily_bonus_7,FreeDiamond,,20,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_381,202512010,event_sur_00001_daily_bonus_8,Item,ticket_glo_00003,1,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_382,202512010,event_sur_00001_daily_bonus_9,Item,memoryfragment_glo_00001,3,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_383,202512010,event_sur_00001_daily_bonus_10,Item,memoryfragment_glo_00002,2,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_384,202512010,event_sur_00001_daily_bonus_11,FreeDiamond,,20,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_385,202512010,event_sur_00001_daily_bonus_12,Item,ticket_glo_00003,1,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_386,202512010,event_sur_00001_daily_bonus_13,Item,memoryfragment_glo_00001,3,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_387,202512010,event_sur_00001_daily_bonus_14,Coin,,5000,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_388,202512010,event_sur_00001_daily_bonus_15,FreeDiamond,,20,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_389,202512010,event_sur_00001_daily_bonus_16,Item,ticket_glo_00002,1,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_390,202512010,event_sur_00001_daily_bonus_17,Item,memoryfragment_glo_00001,3,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_391,202512010,event_sur_00001_daily_bonus_18,Coin,,5000,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_392,202512010,event_sur_00001_daily_bonus_19,Item,memory_glo_00002,300,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_393,202512010,event_sur_00001_daily_bonus_20,Item,memory_glo_00004,300,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_394,202512010,event_sur_00001_daily_bonus_21,Item,memoryfragment_glo_00001,3,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_395,202512010,event_sur_00001_daily_bonus_22,Item,memoryfragment_glo_00002,2,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_396,202512010,event_sur_00001_daily_bonus_23,Item,memoryfragment_glo_00001,3,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_397,202512010,event_sur_00001_daily_bonus_24,FreeDiamond,,20,1,"魔都精兵のスレイブ いいジャン祭 特別ログインボーナス"
e,mission_reward_398,202512010,sur_00001_limited_term_1,Coin,,2000,1,降臨バトル「魔防隊と戦う者」に5回挑戦しよう！
e,mission_reward_399,202512010,sur_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「魔防隊と戦う者」に10回挑戦しよう！
e,mission_reward_400,202512010,sur_00001_limited_term_3,Coin,,3000,1,降臨バトル「魔防隊と戦う者」に20回挑戦しよう！
e,mission_reward_401,202512010,sur_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「魔防隊と戦う者」に30回挑戦しよう！
e,mission_reward_402,202512010,sur_00001_event_reward_01,Item,memory_chara_sur_00801,200,1,surいいジャン祭_ミッション
e,mission_reward_403,202512010,sur_00001_event_reward_02,Item,memory_chara_sur_00801,300,1,surいいジャン祭_ミッション
e,mission_reward_404,202512010,sur_00001_event_reward_03,Item,memory_chara_sur_00801,350,1,surいいジャン祭_ミッション
e,mission_reward_405,202512010,sur_00001_event_reward_04,Item,ticket_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_406,202512010,sur_00001_event_reward_05,Item,piece_sur_00801,10,1,surいいジャン祭_ミッション
e,mission_reward_407,202512010,sur_00001_event_reward_06,Item,piece_sur_00801,20,1,surいいジャン祭_ミッション
e,mission_reward_408,202512010,sur_00001_event_reward_07,Item,piece_sur_00801,20,1,surいいジャン祭_ミッション
e,mission_reward_409,202512010,sur_00001_event_reward_08,Item,piece_sur_00801,20,1,surいいジャン祭_ミッション
e,mission_reward_410,202512010,sur_00001_event_reward_09,Item,piece_sur_00801,30,1,surいいジャン祭_ミッション
e,mission_reward_411,202512010,sur_00001_event_reward_10,Item,memoryfragment_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_412,202512010,sur_00001_event_reward_11,FreeDiamond,,50,1,surいいジャン祭_ミッション
e,mission_reward_413,202512010,sur_00001_event_reward_12,Item,memory_chara_sur_00701,200,1,surいいジャン祭_ミッション
e,mission_reward_414,202512010,sur_00001_event_reward_13,Item,memory_chara_sur_00701,300,1,surいいジャン祭_ミッション
e,mission_reward_415,202512010,sur_00001_event_reward_14,Item,memory_chara_sur_00701,350,1,surいいジャン祭_ミッション
e,mission_reward_416,202512010,sur_00001_event_reward_15,Item,ticket_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_417,202512010,sur_00001_event_reward_16,Item,piece_sur_00701,10,1,surいいジャン祭_ミッション
e,mission_reward_418,202512010,sur_00001_event_reward_17,Item,piece_sur_00701,20,1,surいいジャン祭_ミッション
e,mission_reward_419,202512010,sur_00001_event_reward_18,Item,piece_sur_00701,20,1,surいいジャン祭_ミッション
e,mission_reward_420,202512010,sur_00001_event_reward_19,Item,piece_sur_00701,20,1,surいいジャン祭_ミッション
e,mission_reward_421,202512010,sur_00001_event_reward_20,Item,piece_sur_00701,30,1,surいいジャン祭_ミッション
e,mission_reward_422,202512010,sur_00001_event_reward_21,Item,memoryfragment_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_423,202512010,sur_00001_event_reward_22,FreeDiamond,,50,1,surいいジャン祭_ミッション
e,mission_reward_424,202512010,sur_00001_event_reward_23,Coin,,12500,1,surいいジャン祭_ミッション
e,mission_reward_425,202512010,sur_00001_event_reward_24,Coin,,12500,1,surいいジャン祭_ミッション
e,mission_reward_426,202512010,sur_00001_event_reward_25,Item,ticket_glo_00002,3,1,surいいジャン祭_ミッション
e,mission_reward_427,202512010,sur_00001_event_reward_26,Item,ticket_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_428,202512010,sur_00001_event_reward_27,Item,memoryfragment_glo_00001,5,1,surいいジャン祭_ミッション
e,mission_reward_429,202512010,sur_00001_event_reward_28,Item,memoryfragment_glo_00001,5,1,surいいジャン祭_ミッション
e,mission_reward_430,202512010,sur_00001_event_reward_29,Item,memoryfragment_glo_00001,5,1,surいいジャン祭_ミッション
e,mission_reward_431,202512010,sur_00001_event_reward_30,Item,memoryfragment_glo_00001,5,1,surいいジャン祭_ミッション
e,mission_reward_432,202512010,sur_00001_event_reward_31,Item,memoryfragment_glo_00001,10,1,surいいジャン祭_ミッション
e,mission_reward_433,202512010,sur_00001_event_reward_32,Item,memoryfragment_glo_00002,5,1,surいいジャン祭_ミッション
e,mission_reward_434,202512010,sur_00001_event_reward_33,Item,memoryfragment_glo_00002,5,1,surいいジャン祭_ミッション
e,mission_reward_435,202512010,sur_00001_event_reward_34,Item,memoryfragment_glo_00002,5,1,surいいジャン祭_ミッション
e,mission_reward_436,202512010,sur_00001_event_reward_35,Item,memoryfragment_glo_00002,5,1,surいいジャン祭_ミッション
e,mission_reward_437,202512010,sur_00001_event_reward_36,Emblem,emblem_event_sur_00001,1,1,surいいジャン祭_ミッション
e,mission_reward_438,202512010,sur_00001_event_reward_37,Item,memoryfragment_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_439,202512010,sur_00001_event_reward_38,Item,memoryfragment_glo_00003,2,1,surいいジャン祭_ミッション
e,mission_reward_440,202512010,sur_00001_event_reward_39,Item,ticket_glo_00003,1,1,surいいジャン祭_ミッション
e,mission_reward_441,202512020,event_osh_00001_daily_bonus_01,Item,ticket_osh_10000,1,1,【推しの子】SSR確定ガシャ
e,mission_reward_442,202512020,event_osh_00001_daily_bonus_02,FreeDiamond,,150,1,プリズム
e,mission_reward_443,202512020,event_osh_00001_daily_bonus_03,Item,item_glo_00001,200,1,いいジャンメダル【赤】
e,mission_reward_444,202512020,event_osh_00001_daily_bonus_04,Item,ticket_glo_00003,1,1,ピックアップガシャチケット
e,mission_reward_445,202512020,event_osh_00001_daily_bonus_05,FreeDiamond,,150,1,プリズム
e,mission_reward_446,202512020,event_osh_00001_daily_bonus_06,Item,memoryfragment_glo_00001,5,1,メモリーフラグメント・初級
e,mission_reward_447,202512020,event_osh_00001_daily_bonus_07,Item,ticket_glo_00003,1,1,ピックアップガシャチケット
e,mission_reward_448,202512020,event_osh_00001_daily_bonus_08,FreeDiamond,,100,1,プリズム
e,mission_reward_449,202512020,event_osh_00001_daily_bonus_09,Coin,,5000,1,コイン
e,mission_reward_450,202512020,event_osh_00001_daily_bonus_10,Item,ticket_glo_00002,1,1,スペシャルガシャチケット
e,mission_reward_451,202512020,event_osh_00001_daily_bonus_11,Item,memoryfragment_glo_00002,4,1,メモリーフラグメント・中級
e,mission_reward_452,202512020,event_osh_00001_daily_bonus_12,Coin,,5000,1,コイン
e,mission_reward_453,202512020,event_osh_00001_daily_bonus_13,FreeDiamond,,100,1,プリズム
e,mission_reward_454,202512020,event_osh_00001_daily_bonus_14,Item,memoryfragment_glo_00001,5,1,メモリーフラグメント・初級
e,mission_reward_455,202512020,event_osh_00001_daily_bonus_15,Item,ticket_glo_00002,1,1,スペシャルガシャチケット
e,mission_reward_456,999999999,kai_00001_event_reward_25,Coin,,100,1,ミッショントリガー挙動確認
e,mission_reward_457,999999999,kai_00001_event_reward_26,Coin,,100,1,ミッショントリガー挙動確認
e,mission_reward_458,999999999,kai_00001_event_reward_27,Coin,,100,1,ミッショントリガー挙動確認
e,mission_reward_459,999999999,kai_00001_event_reward_28,Coin,,100,1,ミッショントリガー挙動確認
e,mission_reward_460,202512020,achievement_2_101,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_461,202512020,achievement_2_102,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_462,202512020,achievement_2_103,FreeDiamond,,50,1,アチーブメントミッション
e,mission_reward_463,202601010,event_jig_00001_daily_bonus_01,Item,ticket_glo_00003,2,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_464,202601010,event_jig_00001_daily_bonus_02,Coin,,5000,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_465,202601010,event_jig_00001_daily_bonus_03,FreeDiamond,,40,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_466,202601010,event_jig_00001_daily_bonus_04,Item,memoryfragment_glo_00001,5,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_467,202601010,event_jig_00001_daily_bonus_05,Item,memoryfragment_glo_00002,4,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_468,202601010,event_jig_00001_daily_bonus_06,Item,memory_glo_00005,300,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_469,202601010,event_jig_00001_daily_bonus_07,Item,ticket_glo_00002,2,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_470,202601010,event_jig_00001_daily_bonus_08,Coin,,5000,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_471,202601010,event_jig_00001_daily_bonus_09,FreeDiamond,,40,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_472,202601010,event_jig_00001_daily_bonus_10,Item,memoryfragment_glo_00001,10,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_473,202601010,event_jig_00001_daily_bonus_11,Item,memory_glo_00002,300,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_474,202601010,event_jig_00001_daily_bonus_12,FreeDiamond,,20,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_475,202601010,event_jig_00001_daily_bonus_13,Item,memory_glo_00005,300,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_476,202601010,event_jig_00001_daily_bonus_14,Coin,,5000,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_477,202601010,event_jig_00001_daily_bonus_15,Item,memory_glo_00002,300,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_478,202601010,event_jig_00001_daily_bonus_16,Item,ticket_glo_00002,1,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_479,202601010,event_jig_00001_daily_bonus_17,Item,ticket_glo_00003,1,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_480,202601010,jig_00001_event_reward_01,Item,memory_chara_jig_00701,200,1,jigいいジャン祭_ミッション
e,mission_reward_481,202601010,jig_00001_event_reward_02,Item,memory_chara_jig_00701,300,1,jigいいジャン祭_ミッション
e,mission_reward_482,202601010,jig_00001_event_reward_03,Item,memory_chara_jig_00701,350,1,jigいいジャン祭_ミッション
e,mission_reward_483,202601010,jig_00001_event_reward_04,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_484,202601010,jig_00001_event_reward_05,Item,piece_jig_00701,10,1,jigいいジャン祭_ミッション
e,mission_reward_485,202601010,jig_00001_event_reward_06,Item,piece_jig_00701,20,1,jigいいジャン祭_ミッション
e,mission_reward_486,202601010,jig_00001_event_reward_07,Item,piece_jig_00701,20,1,jigいいジャン祭_ミッション
e,mission_reward_487,202601010,jig_00001_event_reward_08,Item,piece_jig_00701,20,1,jigいいジャン祭_ミッション
e,mission_reward_488,202601010,jig_00001_event_reward_09,Item,piece_jig_00701,30,1,jigいいジャン祭_ミッション
e,mission_reward_489,202601010,jig_00001_event_reward_10,Item,memoryfragment_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_490,202601010,jig_00001_event_reward_11,FreeDiamond,,50,1,jigいいジャン祭_ミッション
e,mission_reward_491,202601010,jig_00001_event_reward_12,Item,memory_chara_jig_00601,200,1,jigいいジャン祭_ミッション
e,mission_reward_492,202601010,jig_00001_event_reward_13,Item,memory_chara_jig_00601,300,1,jigいいジャン祭_ミッション
e,mission_reward_493,202601010,jig_00001_event_reward_14,Item,memory_chara_jig_00601,350,1,jigいいジャン祭_ミッション
e,mission_reward_494,202601010,jig_00001_event_reward_15,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_495,202601010,jig_00001_event_reward_16,Item,piece_jig_00601,10,1,jigいいジャン祭_ミッション
e,mission_reward_496,202601010,jig_00001_event_reward_17,Item,piece_jig_00601,20,1,jigいいジャン祭_ミッション
e,mission_reward_497,202601010,jig_00001_event_reward_18,Item,piece_jig_00601,20,1,jigいいジャン祭_ミッション
e,mission_reward_498,202601010,jig_00001_event_reward_19,Item,piece_jig_00601,20,1,jigいいジャン祭_ミッション
e,mission_reward_499,202601010,jig_00001_event_reward_20,Item,piece_jig_00601,30,1,jigいいジャン祭_ミッション
e,mission_reward_500,202601010,jig_00001_event_reward_21,Item,memoryfragment_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_501,202601010,jig_00001_event_reward_22,FreeDiamond,,50,1,jigいいジャン祭_ミッション
e,mission_reward_502,202601010,jig_00001_event_reward_23,Coin,,12500,1,jigいいジャン祭_ミッション
e,mission_reward_503,202601010,jig_00001_event_reward_24,Coin,,12500,1,jigいいジャン祭_ミッション
e,mission_reward_504,202601010,jig_00001_event_reward_25,Item,ticket_glo_00002,3,1,jigいいジャン祭_ミッション
e,mission_reward_505,202601010,jig_00001_event_reward_26,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_506,202601010,jig_00001_event_reward_27,Item,memoryfragment_glo_00001,5,1,jigいいジャン祭_ミッション
e,mission_reward_507,202601010,jig_00001_event_reward_28,Item,memoryfragment_glo_00001,5,1,jigいいジャン祭_ミッション
e,mission_reward_508,202601010,jig_00001_event_reward_29,Item,memoryfragment_glo_00001,5,1,jigいいジャン祭_ミッション
e,mission_reward_509,202601010,jig_00001_event_reward_30,Item,memoryfragment_glo_00001,5,1,jigいいジャン祭_ミッション
e,mission_reward_510,202601010,jig_00001_event_reward_31,Item,memoryfragment_glo_00001,10,1,jigいいジャン祭_ミッション
e,mission_reward_511,202601010,jig_00001_event_reward_32,Item,memoryfragment_glo_00002,5,1,jigいいジャン祭_ミッション
e,mission_reward_512,202601010,jig_00001_event_reward_33,Item,memoryfragment_glo_00002,5,1,jigいいジャン祭_ミッション
e,mission_reward_513,202601010,jig_00001_event_reward_34,Item,memoryfragment_glo_00002,5,1,jigいいジャン祭_ミッション
e,mission_reward_514,202601010,jig_00001_event_reward_35,Item,memoryfragment_glo_00002,5,1,jigいいジャン祭_ミッション
e,mission_reward_515,202601010,jig_00001_event_reward_36,Item,memoryfragment_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_516,202601010,jig_00001_event_reward_37,Item,memoryfragment_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_517,202601010,jig_00001_event_reward_38,Item,memoryfragment_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_518,202601010,jig_00001_event_reward_39,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_519,202601010,jig_00001_event_reward_40,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
e,mission_reward_520,202601010,jig_00001_event_reward_41,Item,piece_jig_00001,5,1,jigいいジャン祭_ミッション
e,mission_reward_521,202601010,jig_00001_event_reward_42,Item,piece_jig_00001,15,1,jigいいジャン祭_ミッション
e,mission_reward_522,202601010,jig_00001_event_reward_43,Item,piece_jig_00001,20,1,jigいいジャン祭_ミッション
e,mission_reward_523,202601010,jig_00001_limited_term_1,Coin,,2000,1,"降臨バトル「まるで 悪夢を見ているようだ」に5回挑戦しよう！"
e,mission_reward_524,202601010,jig_00001_limited_term_2,FreeDiamond,,20,1,"降臨バトル「まるで 悪夢を見ているようだ」に10回挑戦しよう！"
e,mission_reward_525,202601010,jig_00001_limited_term_3,Coin,,3000,1,"降臨バトル「まるで 悪夢を見ているようだ」に20回挑戦しよう！"
e,mission_reward_526,202601010,jig_00001_limited_term_4,FreeDiamond,,30,1,"降臨バトル「まるで 悪夢を見ているようだ」に30回挑戦しよう！"
e,mission_reward_527,202512020,osh_00001_limited_term_1,Coin,,2000,1,降臨バトル「ファーストライブ」に5回挑戦しよう！
e,mission_reward_528,202512020,osh_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「ファーストライブ」に10回挑戦しよう！
e,mission_reward_529,202512020,osh_00001_limited_term_3,Coin,,3000,1,降臨バトル「ファーストライブ」に20回挑戦しよう！
e,mission_reward_530,202512020,osh_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「ファーストライブ」に25回挑戦しよう！
e,mission_reward_531,202512020,osh_00001_event_reward_1,Item,ticket_glo_10001,2,1,推しの子ミッション(賀正チケット)
e,mission_reward_532,202512020,osh_00001_event_reward_2,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_533,202512020,osh_00001_event_reward_3,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_534,202512020,osh_00001_event_reward_4,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_535,202512020,osh_00001_event_reward_5,FreeDiamond,,50,1,推しの子ミッション(賀正チケット)
e,mission_reward_536,202512020,osh_00001_event_reward_6,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_537,202512020,osh_00001_event_reward_7,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_538,202512020,osh_00001_event_reward_8,FreeDiamond,,50,1,推しの子ミッション(賀正チケット)
e,mission_reward_539,202512020,osh_00001_event_reward_9,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_540,202512020,osh_00001_event_reward_10,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_541,202512020,osh_00001_event_reward_11,FreeDiamond,,100,1,推しの子ミッション(賀正チケット)
e,mission_reward_542,202512020,osh_00001_event_reward_12,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_543,202512020,osh_00001_event_reward_13,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_544,202512020,osh_00001_event_reward_14,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_545,202512020,osh_00001_event_reward_15,FreeDiamond,,100,1,推しの子ミッション(賀正チケット)
e,mission_reward_546,202512020,osh_00001_event_reward_16,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_547,202512020,osh_00001_event_reward_17,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_548,202512020,osh_00001_event_reward_18,FreeDiamond,,100,1,推しの子ミッション(賀正チケット)
e,mission_reward_549,202512020,osh_00001_event_reward_19,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_550,202512020,osh_00001_event_reward_20,FreeDiamond,,100,1,推しの子ミッション(賀正チケット)
e,mission_reward_551,202512020,osh_00001_event_reward_21,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_552,202512020,osh_00001_event_reward_22,FreeDiamond,,100,1,推しの子ミッション(賀正チケット)
e,mission_reward_553,202512020,osh_00001_event_reward_23,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_554,202512020,osh_00001_event_reward_24,FreeDiamond,,150,1,推しの子ミッション(賀正チケット)
e,mission_reward_555,202512020,osh_00001_event_reward_25,Item,ticket_glo_10001,3,1,推しの子ミッション(賀正チケット)
e,mission_reward_556,202512020,osh_00001_event_reward_26,FreeDiamond,,150,1,推しの子ミッション(賀正チケット)
e,mission_reward_557,202512020,osh_00001_event_reward_27,FreeDiamond,,200,1,推しの子ミッション(賀正チケット)
e,mission_reward_558,202512020,osh_00001_event_reward_28,Item,ticket_glo_00002,1,1,推しの子ミッション(賀正チケット)
e,mission_reward_559,202512020,osh_00001_event_reward_29,Item,ticket_glo_00002,1,1,推しの子ミッション(賀正チケット)
e,mission_reward_560,202512020,osh_00001_event_reward_30,Item,ticket_glo_00002,1,1,推しの子ミッション(賀正チケット)
e,mission_reward_561,202512020,osh_00001_event_reward_31,Item,ticket_glo_00002,1,1,推しの子ミッション(賀正チケット)
e,mission_reward_562,202512020,osh_00001_event_reward_32,FreeDiamond,,200,1,推しの子ミッション(賀正チケット)
e,mission_reward_563,202512020,osh_00001_event_reward_33,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_564,202512020,osh_00001_event_reward_34,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_565,202512020,osh_00001_event_reward_35,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_566,202512020,osh_00001_event_reward_36,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_567,202512020,osh_00001_event_reward_37,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_568,202512020,osh_00001_event_reward_38,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_569,202512020,osh_00001_event_reward_39,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_570,202512020,osh_00001_event_reward_40,Coin,,5000,1,ぴえヨン連れて行こうミッション
e,mission_reward_571,202512020,osh_00001_event_reward_41,Emblem,emblem_event_osh_00008,1,1,ぴえヨン連れて行こうミッション
e,mission_reward_572,202512020,osh_00001_event_reward_42,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_573,202512020,osh_00001_event_reward_43,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_574,202512020,osh_00001_event_reward_44,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_575,202512020,osh_00001_event_reward_45,Coin,,500,1,ぴえヨン連れて行こうミッション
e,mission_reward_576,202512020,osh_00001_event_reward_46,Coin,,5000,1,ぴえヨン連れて行こうミッション
e,mission_reward_577,202512020,osh_00001_event_reward_47,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_578,202512020,osh_00001_event_reward_48,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_579,202512020,osh_00001_event_reward_49,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_580,202512020,osh_00001_event_reward_50,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_581,202512020,osh_00001_event_reward_51,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_582,202512020,osh_00001_event_reward_52,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_583,202512020,osh_00001_event_reward_53,Coin,,1000,1,ぴえヨン連れて行こうミッション
e,mission_reward_584,202512020,glo_00001_event_reward_01,Coin,,1000,1,年始ミッション
e,mission_reward_585,202512020,glo_00001_event_reward_02,Coin,,1000,1,年始ミッション
e,mission_reward_586,202512020,glo_00001_event_reward_03,Coin,,3000,1,年始ミッション
e,mission_reward_587,202602010,event_you_00001_daily_bonus_01,Item,ticket_glo_00003,2,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_588,202602010,event_you_00001_daily_bonus_02,Coin,,5000,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_589,202602010,event_you_00001_daily_bonus_03,FreeDiamond,,50,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_590,202602010,event_you_00001_daily_bonus_04,Item,memoryfragment_glo_00001,5,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_591,202602010,event_you_00001_daily_bonus_05,Item,memoryfragment_glo_00002,4,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_592,202602010,event_you_00001_daily_bonus_06,Item,memory_glo_00004,600,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_593,202602010,event_you_00001_daily_bonus_07,Item,ticket_glo_00002,2,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_594,202602010,event_you_00001_daily_bonus_08,Coin,,5000,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_595,202602010,event_you_00001_daily_bonus_09,FreeDiamond,,50,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_596,202602010,event_you_00001_daily_bonus_10,Item,memoryfragment_glo_00001,10,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_597,202602010,event_you_00001_daily_bonus_11,Item,memory_glo_00002,600,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_598,202602010,event_you_00001_daily_bonus_12,Coin,,5000,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_599,202602010,event_you_00001_daily_bonus_13,Item,ticket_glo_00003,1,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_600,202602010,event_you_00001_daily_bonus_14,Item,ticket_glo_00002,1,1,"幼稚園WARS いいジャン祭 特別ログインボーナス"
e,mission_reward_601,202602010,you_00001_event_reward_01,Item,memory_chara_you_00201,200,1,youいいジャン祭_ミッション
e,mission_reward_602,202602010,you_00001_event_reward_02,Item,memory_chara_you_00201,300,1,youいいジャン祭_ミッション
e,mission_reward_603,202602010,you_00001_event_reward_03,Item,memory_chara_you_00201,350,1,youいいジャン祭_ミッション
e,mission_reward_604,202602010,you_00001_event_reward_04,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_605,202602010,you_00001_event_reward_05,Item,piece_you_00201,10,1,youいいジャン祭_ミッション
e,mission_reward_606,202602010,you_00001_event_reward_06,Item,piece_you_00201,20,1,youいいジャン祭_ミッション
e,mission_reward_607,202602010,you_00001_event_reward_07,Item,piece_you_00201,20,1,youいいジャン祭_ミッション
e,mission_reward_608,202602010,you_00001_event_reward_08,Item,piece_you_00201,20,1,youいいジャン祭_ミッション
e,mission_reward_609,202602010,you_00001_event_reward_09,Item,piece_you_00201,30,1,youいいジャン祭_ミッション
e,mission_reward_610,202602010,you_00001_event_reward_10,Item,memoryfragment_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_611,202602010,you_00001_event_reward_11,FreeDiamond,,50,1,youいいジャン祭_ミッション
e,mission_reward_612,202602010,you_00001_event_reward_12,Item,memory_chara_you_00301,200,1,youいいジャン祭_ミッション
e,mission_reward_613,202602010,you_00001_event_reward_13,Item,memory_chara_you_00301,300,1,youいいジャン祭_ミッション
e,mission_reward_614,202602010,you_00001_event_reward_14,Item,memory_chara_you_00301,350,1,youいいジャン祭_ミッション
e,mission_reward_615,202602010,you_00001_event_reward_15,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_616,202602010,you_00001_event_reward_16,Item,piece_you_00301,10,1,youいいジャン祭_ミッション
e,mission_reward_617,202602010,you_00001_event_reward_17,Item,piece_you_00301,20,1,youいいジャン祭_ミッション
e,mission_reward_618,202602010,you_00001_event_reward_18,Item,piece_you_00301,20,1,youいいジャン祭_ミッション
e,mission_reward_619,202602010,you_00001_event_reward_19,Item,piece_you_00301,20,1,youいいジャン祭_ミッション
e,mission_reward_620,202602010,you_00001_event_reward_20,Item,piece_you_00301,30,1,youいいジャン祭_ミッション
e,mission_reward_621,202602010,you_00001_event_reward_21,Item,memoryfragment_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_622,202602010,you_00001_event_reward_22,FreeDiamond,,50,1,youいいジャン祭_ミッション
e,mission_reward_623,202602010,you_00001_event_reward_23,Coin,,12500,1,youいいジャン祭_ミッション
e,mission_reward_624,202602010,you_00001_event_reward_24,Coin,,12500,1,youいいジャン祭_ミッション
e,mission_reward_625,202602010,you_00001_event_reward_25,Item,ticket_glo_00002,3,1,youいいジャン祭_ミッション
e,mission_reward_626,202602010,you_00001_event_reward_26,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_627,202602010,you_00001_event_reward_27,Item,memoryfragment_glo_00001,5,1,youいいジャン祭_ミッション
e,mission_reward_628,202602010,you_00001_event_reward_28,Item,memoryfragment_glo_00001,5,1,youいいジャン祭_ミッション
e,mission_reward_629,202602010,you_00001_event_reward_29,Item,memoryfragment_glo_00001,5,1,youいいジャン祭_ミッション
e,mission_reward_630,202602010,you_00001_event_reward_30,Item,memoryfragment_glo_00001,5,1,youいいジャン祭_ミッション
e,mission_reward_631,202602010,you_00001_event_reward_31,Item,memoryfragment_glo_00001,10,1,youいいジャン祭_ミッション
e,mission_reward_632,202602010,you_00001_event_reward_32,Item,memoryfragment_glo_00002,5,1,youいいジャン祭_ミッション
e,mission_reward_633,202602010,you_00001_event_reward_33,Item,memoryfragment_glo_00002,5,1,youいいジャン祭_ミッション
e,mission_reward_634,202602010,you_00001_event_reward_34,Item,memoryfragment_glo_00002,5,1,youいいジャン祭_ミッション
e,mission_reward_635,202602010,you_00001_event_reward_35,Item,memoryfragment_glo_00002,5,1,youいいジャン祭_ミッション
e,mission_reward_636,202602010,you_00001_event_reward_36,Item,memoryfragment_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_637,202602010,you_00001_event_reward_37,Item,memoryfragment_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_638,202602010,you_00001_event_reward_38,Item,memoryfragment_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_639,202602010,you_00001_event_reward_39,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_640,202602010,you_00001_event_reward_40,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_641,202602010,you_00001_event_reward_41,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_642,202602010,you_00001_event_reward_42,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_643,202602010,you_00001_event_reward_43,Item,ticket_glo_00003,1,1,youいいジャン祭_ミッション
e,mission_reward_644,202602010,you_00001_limited_term_1,Coin,,2000,1,降臨バトル「誰の依頼だ？」に5回挑戦しよう！
e,mission_reward_645,202602010,you_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「誰の依頼だ？」に10回挑戦しよう！
e,mission_reward_646,202602010,you_00001_limited_term_3,Coin,,3000,1,降臨バトル「誰の依頼だ？」に20回挑戦しよう！
e,mission_reward_647,202602010,you_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「誰の依頼だ？」に30回挑戦しよう！
e,mission_reward_648,202602020,event_kim_00001_daily_bonus_01,Item,ticket_glo_00003,2,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_649,202602020,event_kim_00001_daily_bonus_02,Coin,,5000,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_650,202602020,event_kim_00001_daily_bonus_03,FreeDiamond,,50,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_651,202602020,event_kim_00001_daily_bonus_04,Item,memoryfragment_glo_00001,15,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_652,202602020,event_kim_00001_daily_bonus_05,Item,memory_glo_00004,600,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_653,202602020,event_kim_00001_daily_bonus_06,Item,memory_glo_00003,600,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_654,202602020,event_kim_00001_daily_bonus_07,Item,ticket_glo_00002,2,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_655,202602020,event_kim_00001_daily_bonus_08,Coin,,10000,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_656,202602020,event_kim_00001_daily_bonus_09,FreeDiamond,,50,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_657,202602020,event_kim_00001_daily_bonus_10,Item,memory_glo_00002,600,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_658,202602020,event_kim_00001_daily_bonus_11,Item,memory_glo_00005,600,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_659,202602020,event_kim_00001_daily_bonus_12,Item,memoryfragment_glo_00002,4,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_660,202602020,event_kim_00001_daily_bonus_13,Item,ticket_glo_00003,1,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_661,202602020,event_kim_00001_daily_bonus_14,Item,ticket_glo_00002,1,1,"君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス"
e,mission_reward_662,202602020,kim_00001_event_reward_01,Coin,,5000,1,kimいいジャン祭_ミッション
e,mission_reward_663,202602020,kim_00001_event_reward_02,Coin,,5000,1,kimいいジャン祭_ミッション
e,mission_reward_664,202602020,kim_00001_event_reward_03,Coin,,5000,1,kimいいジャン祭_ミッション
e,mission_reward_665,202602020,kim_00001_event_reward_04,Item,ticket_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_666,202602020,kim_00001_event_reward_05,Coin,,10000,1,kimいいジャン祭_ミッション
e,mission_reward_667,202602020,kim_00001_event_reward_06,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_668,202602020,kim_00001_event_reward_07,Item,piece_kim_00101,10,1,kimいいジャン祭_ミッション
e,mission_reward_669,202602020,kim_00001_event_reward_08,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_670,202602020,kim_00001_event_reward_09,Item,piece_kim_00201,10,1,kimいいジャン祭_ミッション
e,mission_reward_671,202602020,kim_00001_event_reward_10,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_672,202602020,kim_00001_event_reward_11,FreeDiamond,,50,1,kimいいジャン祭_ミッション
e,mission_reward_673,202602020,kim_00001_event_reward_12,Item,ticket_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_674,202602020,kim_00001_event_reward_13,Item,piece_kim_00301,10,1,kimいいジャン祭_ミッション
e,mission_reward_675,202602020,kim_00001_event_reward_14,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_676,202602020,kim_00001_event_reward_15,Item,memoryfragment_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_677,202602020,kim_00001_event_reward_16,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_678,202602020,kim_00001_event_reward_17,Item,ticket_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_679,202602020,kim_00001_event_reward_18,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_680,202602020,kim_00001_event_reward_19,FreeDiamond,,50,1,kimいいジャン祭_ミッション
e,mission_reward_681,202602020,kim_00001_event_reward_20,Item,ticket_glo_00002,1,1,kimいいジャン祭_ミッション
e,mission_reward_682,202602020,kim_00001_event_reward_21,Item,memoryfragment_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_683,202602020,kim_00001_event_reward_22,Item,ticket_kim_00001,1,1,kimいいジャン祭_ミッション
e,mission_reward_684,202602020,kim_00001_event_reward_23,Coin,,15000,1,kimいいジャン祭_ミッション
e,mission_reward_685,202602020,kim_00001_event_reward_24,Coin,,15000,1,kimいいジャン祭_ミッション
e,mission_reward_686,202602020,kim_00001_event_reward_25,Item,ticket_glo_00002,3,1,kimいいジャン祭_ミッション
e,mission_reward_687,202602020,kim_00001_event_reward_26,Item,ticket_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_688,202602020,kim_00001_event_reward_27,Item,memoryfragment_glo_00001,5,1,kimいいジャン祭_ミッション
e,mission_reward_689,202602020,kim_00001_event_reward_28,Item,memoryfragment_glo_00001,5,1,kimいいジャン祭_ミッション
e,mission_reward_690,202602020,kim_00001_event_reward_29,Item,memoryfragment_glo_00001,5,1,kimいいジャン祭_ミッション
e,mission_reward_691,202602020,kim_00001_event_reward_30,Item,memoryfragment_glo_00001,5,1,kimいいジャン祭_ミッション
e,mission_reward_692,202602020,kim_00001_event_reward_31,Item,memoryfragment_glo_00001,10,1,kimいいジャン祭_ミッション
e,mission_reward_693,202602020,kim_00001_event_reward_32,Item,memoryfragment_glo_00002,5,1,kimいいジャン祭_ミッション
e,mission_reward_694,202602020,kim_00001_event_reward_33,Item,memoryfragment_glo_00002,5,1,kimいいジャン祭_ミッション
e,mission_reward_695,202602020,kim_00001_event_reward_34,Item,memoryfragment_glo_00002,5,1,kimいいジャン祭_ミッション
e,mission_reward_696,202602020,kim_00001_event_reward_35,Item,memoryfragment_glo_00002,5,1,kimいいジャン祭_ミッション
e,mission_reward_697,202602020,kim_00001_event_reward_36,Item,memoryfragment_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_698,202602020,kim_00001_event_reward_37,Item,memoryfragment_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_699,202602020,kim_00001_event_reward_38,Item,memoryfragment_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_700,202602020,kim_00001_event_reward_39,Item,ticket_glo_00003,1,1,kimいいジャン祭_ミッション
e,mission_reward_701,202602020,kim_00001_limited_term_1,Coin,,2000,1,降臨バトル「ラブミッション：インポッシブル」に5回挑戦しよう！
e,mission_reward_702,202602020,kim_00001_limited_term_2,FreeDiamond,,20,1,降臨バトル「ラブミッション：インポッシブル」に10回挑戦しよう！
e,mission_reward_703,202602020,kim_00001_limited_term_3,Coin,,3000,1,降臨バトル「ラブミッション：インポッシブル」に20回挑戦しよう！
e,mission_reward_704,202602020,kim_00001_limited_term_4,FreeDiamond,,30,1,降臨バトル「ラブミッション：インポッシブル」に30回挑戦しよう！
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionWeekly.csv -->
## ./projects/glow-masterdata/MstMissionWeekly.csv

```csv
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene
e,weekly_2_1,202509010,LoginCount,,3,Weekly2,20,,1,Home
e,weekly_2_2,202509010,LoginCount,,6,Weekly2,20,,2,Home
e,weekly_2_3,202509010,IdleIncentiveCount,,3,Weekly2,20,,3,IdleIncentive
e,weekly_2_4,202509010,IdleIncentiveCount,,10,Weekly2,20,,4,IdleIncentive
e,weekly_2_5,202509010,PvpChallengeCount,,5,Weekly2,20,,5,Pvp
e,weekly_2_6,202509010,CoinCollect,,15000,Weekly2,20,,6,StageSelect
e,weekly_bonus_point_2_1,202509010,MissionBonusPoint,,20,,,weekly_reward_2_1,10,
e,weekly_bonus_point_2_2,202509010,MissionBonusPoint,,40,,,weekly_reward_2_2,11,
e,weekly_bonus_point_2_3,202509010,MissionBonusPoint,,60,,,weekly_reward_2_3,12,
e,weekly_bonus_point_2_4,202509010,MissionBonusPoint,,80,,,weekly_reward_2_4,13,
e,weekly_bonus_point_2_5,202509010,MissionBonusPoint,,100,,,weekly_reward_2_5,14,
```

---

<!-- FILE: ./projects/glow-masterdata/MstMissionWeeklyI18n.csv -->
## ./projects/glow-masterdata/MstMissionWeeklyI18n.csv

```csv
ENABLE,release_key,id,mst_mission_weekly_id,language,description
e,202509010,weekly_2_1_ja,weekly_2_1,ja,3日ログインしよう
e,202509010,weekly_2_2_ja,weekly_2_2,ja,6日ログインしよう
e,202509010,weekly_2_3_ja,weekly_2_3,ja,探索で探索報酬を累計3回受け取ろう
e,202509010,weekly_2_4_ja,weekly_2_4,ja,探索で探索報酬を累計10回受け取ろう
e,202509010,weekly_2_5_ja,weekly_2_5,ja,ランクマッチに累計5回挑戦しよう
e,202509010,weekly_2_6_ja,weekly_2_6,ja,"コインを累計15,000枚集めよう"
e,202509010,weekly_bonus_point_2_1_ja,weekly_bonus_point_2_1,ja,累計ポイントを20貯めよう
e,202509010,weekly_bonus_point_2_2_ja,weekly_bonus_point_2_2,ja,累計ポイントを40貯めよう
e,202509010,weekly_bonus_point_2_3_ja,weekly_bonus_point_2_3,ja,累計ポイントを60貯めよう
e,202509010,weekly_bonus_point_2_4_ja,weekly_bonus_point_2_4,ja,累計ポイントを80貯めよう
e,202509010,weekly_bonus_point_2_5_ja,weekly_bonus_point_2_5,ja,累計ポイントを100貯めよう
```

---

<!-- FILE: ./projects/glow-masterdata/MstPack.csv -->
## ./projects/glow-masterdata/MstPack.csv

```csv
ENABLE,id,product_sub_id,discount_rate,sale_condition,sale_condition_value,sale_hours,is_display_expiration,pack_type,tradable_count,cost_type,is_first_time_free,cost_amount,is_recommend,asset_key,pack_decoration,release_key
e,start_chara_pack_1,13,0,__NULL__,0,,0,Normal,0,Cash,0,0,0,pack_00001,__NULL__,202509010
e,start_item_pack_1,14,0,__NULL__,0,,0,Normal,0,Cash,0,0,0,pack_00002,__NULL__,202509010
e,start_item_pack_2,15,0,__NULL__,0,,0,Normal,0,Cash,0,0,0,pack_00003,__NULL__,202509010
e,event_item_pack_1,16,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202509010
e,daily_item_pack_1,18,0,__NULL__,0,,0,Daily,2,Ad,1,0,0,pack_00004,__NULL__,202509010
e,event_item_pack_2,19,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202510010
e,event_item_pack_3,20,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202510020
e,event_item_pack_4,21,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202511010
e,event_item_pack_5,22,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202511020
e,BF_item_pack_1,25,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00006,__NULL__,202511020
e,BF_item_pack_2,26,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00007,__NULL__,202511020
e,BF_item_pack_3,27,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00008,__NULL__,202511020
e,Xmas_item_pack_1,28,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00009,__NULL__,202512015
e,Xmas_item_pack_2,29,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00010,__NULL__,202512015
e,Xmas_item_pack_3,30,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00011,__NULL__,202512015
e,event_item_pack_6,35,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202512010
e,monthly_item_pack_1,36,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00017,__NULL__,202512010
e,event_item_pack_7,37,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202512020
e,event_item_pack_8,44,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00012,__NULL__,202512020
e,event_item_pack_9,45,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00013,__NULL__,202512020
e,event_item_pack_10,46,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00014,__NULL__,202512020
e,event_item_pack_11,47,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00019,__NULL__,202512020
e,event_item_pack_12,50,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202601010
e,offer_FreeDiamond150_202512015,49,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00020,__NULL__,202512015
e,monthly_item_pack_2,51,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00017,__NULL__,202512020
e,monthly_item_pack_3,52,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00017,__NULL__,202601010
e,event_item_pack_13,53,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202602010
e,event_item_pack_14,54,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00022,__NULL__,202602010
e,event_item_pack_15,55,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00023,__NULL__,202602010
e,event_item_pack_16,56,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00024,__NULL__,202602010
e,event_item_pack_17,57,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00025,__NULL__,202602010
e,event_item_pack_18,58,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00026,__NULL__,202602010
e,event_item_pack_19,59,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00027,__NULL__,202602010
e,event_item_pack_20,60,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00028,__NULL__,202602010
e,event_item_pack_21,61,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00029,__NULL__,202602010
e,event_item_pack_22,62,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00030,__NULL__,202602010
e,event_item_pack_23,63,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00031,__NULL__,202602010
e,event_item_pack_24,69,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00005,__NULL__,202602020
e,monthly_item_pack_4,70,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00017,__NULL__,202602020
e,event_item_pack_25,71,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00032,__NULL__,202602020
e,event_item_pack_26,72,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00033,__NULL__,202602020
e,event_item_pack_27,73,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00034,__NULL__,202602020
e,event_item_pack_28,74,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00035,__NULL__,202602020
e,event_item_pack_29,75,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00036,__NULL__,202602020
e,event_item_pack_30,76,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00029,__NULL__,202602020
e,event_item_pack_31,77,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00030,__NULL__,202602020
e,event_item_pack_32,78,0,__NULL__,0,,1,Normal,0,Cash,0,0,0,pack_00031,__NULL__,202602020
```

---

<!-- FILE: ./projects/glow-masterdata/MstPackI18n.csv -->
## ./projects/glow-masterdata/MstPackI18n.csv

```csv
ENABLE,release_key,id,mst_pack_id,language,name
e,202509010,start_chara_pack_1_ja,start_chara_pack_1,ja,"【お一人様1回まで購入可】\nわくわく アーニャ パック"
e,202509010,start_item_pack_1_ja,start_item_pack_1,ja,"【お一人様1回まで購入可】\nスタートダッシュパック"
e,202509010,start_item_pack_2_ja,start_item_pack_2,ja,"【お一人様1回まで購入可】\nスタートダッシュジャンブルパック"
e,202509010,event_item_pack_1_ja,event_item_pack_1,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202509010,daily_item_pack_1_ja,daily_item_pack_1,ja,"【毎日更新】\n強化パック"
e,202510010,event_item_pack_2_ja,event_item_pack_2,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202510020,event_item_pack_3_ja,event_item_pack_3,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202511010,event_item_pack_4_ja,event_item_pack_4,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202511020,event_item_pack_5_ja,event_item_pack_5,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202511020,BF_item_pack_1_ja,BF_item_pack_1,ja,"【お一人様1回まで購入可】\nBLACK FRIDAY お得パックA"
e,202511020,BF_item_pack_2_ja,BF_item_pack_2,ja,"【お一人様1回まで購入可】\nBLACK FRIDAY お得パックB"
e,202511020,BF_item_pack_3_ja,BF_item_pack_3,ja,"【お一人様1回まで購入可】\nBLACK FRIDAY お得パックC"
e,202512015,Xmas_item_pack_1_ja,Xmas_item_pack_1,ja,"【お一人様1回まで購入可】\nクリスマス お得パックA"
e,202512015,Xmas_item_pack_2_ja,Xmas_item_pack_2,ja,"【お一人様1回まで購入可】\nクリスマス お得パックB"
e,202512015,Xmas_item_pack_3_ja,Xmas_item_pack_3,ja,"【お一人様1回まで購入可】\nクリスマス お得パックC"
e,202512010,event_item_pack_6_ja,event_item_pack_6,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202512010,monthly_item_pack_1_ja,monthly_item_pack_1,ja,"【お一人様1回まで購入可】\nお得強化パック"
e,202512020,event_item_pack_7_ja,event_item_pack_7,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202512020,event_item_pack_8_ja,event_item_pack_8,ja,"【お一人様1回まで購入可】\nお正月 DXお得パック梅"
e,202512020,event_item_pack_9_ja,event_item_pack_9,ja,"【お一人様2回まで購入可】\nお正月 DXお得パック竹"
e,202512020,event_item_pack_10_ja,event_item_pack_10,ja,"【お一人様1回まで購入可】\nお正月 DXお得パック松"
e,202512020,event_item_pack_11_ja,event_item_pack_11,ja,"【お一人様1回まで購入可】\nお正月 超DX ジャンブルパック"
e,202601010,event_item_pack_12_ja,event_item_pack_12,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202512015,offer_FreeDiamond150_202512015_ja,offer_FreeDiamond150_202512015,ja,"App Store 年末年始プレゼント"
e,202512020,monthly_item_pack_2_ja,monthly_item_pack_2,ja,"【お一人様1回まで購入可】\nお得強化パック"
e,202601010,monthly_item_pack_3_ja,monthly_item_pack_3,ja,"【お一人様1回まで購入可】\nお得強化パック"
e,202602010,event_item_pack_13_ja,event_item_pack_13,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202602010,event_item_pack_14_ja,event_item_pack_14,ja,"【お一人様1回まで購入可】\n「ヨル」バレンタインキャラパック"
e,202602010,event_item_pack_15_ja,event_item_pack_15,ja,"【お一人様1回まで購入可】\n「姫様」バレンタインキャラパック"
e,202602010,event_item_pack_16_ja,event_item_pack_16,ja,"【お一人様1回まで購入可】\n「天乃 リリサ」バレンタインキャラパック"
e,202602010,event_item_pack_17_ja,event_item_pack_17,ja,"【お一人様1回まで購入可】\n「橘 美花莉」バレンタインキャラパック"
e,202602010,event_item_pack_18_ja,event_item_pack_18,ja,"【お一人様1回まで購入可】\n「羽前 京香」バレンタインキャラパック"
e,202602010,event_item_pack_19_ja,event_item_pack_19,ja,"【お一人様1回まで購入可】\n「桜木 カナ」バレンタインキャラパック"
e,202602010,event_item_pack_20_ja,event_item_pack_20,ja,"【お一人様1回まで購入可】\n「小舟 潮」バレンタインキャラパック"
e,202602010,event_item_pack_21_ja,event_item_pack_21,ja,"【お一人様1回まで購入可】\nいいジャン!スタミナドリンクパック"
e,202602010,event_item_pack_22_ja,event_item_pack_22,ja,"【お一人様1回まで購入可】\nスタミナブースト応援パック"
e,202602010,event_item_pack_23_ja,event_item_pack_23,ja,"【お一人様1回まで購入可】\n即戦力!キャラ強化パック"
e,202602020,event_item_pack_24_ja,event_item_pack_24,ja,"【お一人様1回まで購入可】\nいいジャン祭 開催記念パック"
e,202602020,monthly_item_pack_4_ja,monthly_item_pack_4,ja,"【お一人様1回まで購入可】\nお得強化パック"
e,202602020,event_item_pack_25_ja,event_item_pack_25,ja,"【お一人様1回まで購入可】\n「ロイド」ホワイトデーキャラパック"
e,202602020,event_item_pack_26_ja,event_item_pack_26,ja,"【お一人様1回まで購入可】\n「オカルン」ホワイトデーキャラパック"
e,202602020,event_item_pack_27_ja,event_item_pack_27,ja,"【お一人様1回まで購入可】\n「チェンソーマン」ホワイトデーキャラパック"
e,202602020,event_item_pack_28_ja,event_item_pack_28,ja,"【お一人様1回まで購入可】\n「怪獣8号」ホワイトデーキャラパック"
e,202602020,event_item_pack_29_ja,event_item_pack_29,ja,"【お一人様1回まで購入可】\n「画眉丸」ホワイトデーキャラパック"
e,202602020,event_item_pack_30_ja,event_item_pack_30,ja,"【お一人様1回まで購入可】\nいいジャン!スタミナドリンクパック"
e,202602020,event_item_pack_31_ja,event_item_pack_31,ja,"【お一人様1回まで購入可】\nスタミナブースト応援パック"
e,202602020,event_item_pack_32_ja,event_item_pack_32,ja,"【お一人様1回まで購入可】\n即戦力!キャラ強化パック"
```

---

<!-- FILE: ./projects/glow-masterdata/MstPvp.csv -->
## ./projects/glow-masterdata/MstPvp.csv

```csv
ENABLE,id,release_key,ranking_min_pvp_rank_class,max_daily_challenge_count,max_daily_item_challenge_count,item_challenge_cost_amount,mst_in_game_id,initial_battle_point
e,default_pvp,202509010,Bronze,10,10,1,default_pvp,200
e,2025039,202509010,Bronze,10,10,1,pvp_202509010_01,200
e,2025040,202509010,Bronze,10,10,1,pvp_202509010_01,200
e,2025041,202510010,Bronze,10,10,1,pvp_spy_01,200
e,2025042,202510010,Bronze,10,10,1,pvp_spy_02,200
e,2025043,202510010,Bronze,10,10,1,pvp_dan_01,200
e,2025044,202510020,Bronze,10,10,1,pvp_dan_01,200
e,2025046,202511010,Bronze,10,10,1,pvp_mag_01,1000
e,2025047,202511010,Bronze,10,10,1,pvp_mag_01,1000
e,2025048,202511020,Bronze,10,10,1,pvp_yuw_01,200
e,2025049,202511020,Bronze,10,10,1,pvp_yuw_02,1000
e,2025050,202512010,Bronze,10,10,1,pvp_sur_01,1000
e,2025051,202512010,Bronze,10,10,1,pvp_sur_02,1000
e,2025052,202512015,Bronze,10,10,1,pvp_Xmas_01,1000
e,2026001,202512015,Bronze,10,10,1,pvp_Xmas_01,1000
e,2026002,202512020,Bronze,10,10,1,pvp_osh_01,1000
e,2026003,202512020,Bronze,10,10,1,pvp_osh_01,1000
e,2026004,202601010,Bronze,10,10,1,pvp_jig_01,1000
e,2026005,202601010,Bronze,10,10,1,pvp_jig_02,1000
e,2026006,202602010,Bronze,10,10,1,pvp_you_01,1000
e,2026007,202602010,Bronze,10,10,1,pvp_you_02,1000
```

---

<!-- FILE: ./projects/glow-masterdata/MstPvpI18n.csv -->
## ./projects/glow-masterdata/MstPvpI18n.csv

```csv
ENABLE,id,release_key,mst_pvp_id,language,name,description
e,default_pvp,202509010,default_pvp,ja,決闘名。使わないかも,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\nコマ効果は登場しないぞ！"
e,2025039,202509010,2025039,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\nコマ効果は登場しないぞ！"
e,2025040,202509010,2025040,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\nコマ効果は登場しないぞ！"
e,2025041,202510010,2025041,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\n攻撃UPコマが登場するぞ!\n特性で攻撃UPコマ効果UPを持っているキャラを編成しよう!"
e,2025042,202510010,2025042,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\n攻撃UPコマが登場するぞ!\n特性で攻撃UPコマ効果UPを持っているキャラを編成しよう!"
e,2025043,202510010,2025043,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\n攻撃DOWNコマが登場するぞ!\n特性で攻撃DOWNコマ無効化を持っているキャラを編成しよう!"
e,2025044,202510020,2025044,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\n攻撃DOWNコマが登場するぞ!\n特性で攻撃DOWNコマ無効化を持っているキャラを編成しよう!"
e,2025046,202511010,2025046,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\nリーダーPが1000の状態で開始するぞ！\n\n【コマ効果情報】\nダメージコマが登場するぞ!\n特性でダメージコマ無効化を持っているキャラを編成しよう!"
e,2025047,202511010,2025047,ja,,"【基本情報】\n3段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\nリーダーPが1000の状態で開始するぞ！\n\n【コマ効果情報】\nダメージコマが登場するぞ!\n特性でダメージコマ無効化を持っているキャラを編成しよう!"
e,2025048,202511020,2025048,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n突風コマが登場するぞ!\n特性で突風コマ無効化を持っているキャラを編成しよう!"
e,2025049,202511020,2025049,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】 \nコマ効果は登場しないぞ!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が2倍UP!"
e,2025050,202512010,2025050,ja,,"【基本情報】\n4段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n攻撃DOWNコマが登場するぞ!\n特性で攻撃DOWNコマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が2倍UP!"
e,2025051,202512010,2025051,ja,,"【基本情報】\n4段のステージで戦うぞ！\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう！\n\n【コマ効果情報】\n毒コマが登場するぞ!\n特性で毒ダメージ軽減を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!"
e,2025052,202512015,2025052,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】 \nコマ効果は登場しないぞ!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026001,202512015,2026001,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】 \nコマ効果は登場しないぞ!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026002,202512020,2026002,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】 \nコマ効果は登場しないぞ!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026003,202512020,2026003,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】 \nコマ効果は登場しないぞ!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026004,202601010,2026004,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n毒コマが登場するぞ!\n特性で毒ダメージ軽減を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026005,202601010,2026005,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n突風コマが登場するぞ!\n特性で突風コマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026006,202602010,2026006,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n攻撃DOWNコマが登場するぞ!\n特性で攻撃DOWNコマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026007,202602010,2026007,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\nダメージコマが登場するぞ!\n特性でダメージコマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
```

---

<!-- FILE: ./projects/glow-masterdata/MstQuest.csv -->
## ./projects/glow-masterdata/MstQuest.csv

```csv
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key
e,tutorial,Tutorial,,1,tutorial_1,"2024-01-01 0:00:00","2037-12-31 23:59:59",tutorial,Normal,202509010
e,quest_main_spy_normal_1,normal,,1,spy_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",spy1,Normal,202509010
e,quest_main_spy_hard_1,normal,,1,spy_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",spy1,Hard,202509010
e,quest_main_spy_veryhard_1,normal,,1,spy_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",spy1,Extra,202509010
e,quest_main_gom_normal_2,normal,,2,gom_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",gom1,Normal,202509010
e,quest_main_gom_hard_2,normal,,2,gom_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",gom1,Hard,202509010
e,quest_main_gom_veryhard_2,normal,,2,gom_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",gom1,Extra,202509010
e,quest_main_aka_normal_3,normal,,3,aka_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",aka1,Normal,202509010
e,quest_main_aka_hard_3,normal,,3,aka_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",aka1,Hard,202509010
e,quest_main_aka_veryhard_3,normal,,3,aka_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",aka1,Extra,202509010
e,quest_main_glo1_normal_4,normal,,4,allstar_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo1,Normal,202509010
e,quest_main_glo1_hard_4,normal,,4,allstar_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo1,Hard,202509010
e,quest_main_glo1_veryhard_4,normal,,4,allstar_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo1,Extra,202509010
e,quest_main_dan_normal_5,normal,,5,dan_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",dan1,Normal,202509010
e,quest_main_dan_hard_5,normal,,5,dan_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",dan1,Hard,202509010
e,quest_main_dan_veryhard_5,normal,,5,dan_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",dan1,Extra,202509010
e,quest_main_jig_normal_6,normal,,6,jig_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",jig1,Normal,202509010
e,quest_main_jig_hard_6,normal,,6,jig_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",jig1,Hard,202509010
e,quest_main_jig_veryhard_6,normal,,6,jig_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",jig1,Extra,202509010
e,quest_main_tak_normal_7,normal,,7,tak_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",tak1,Normal,202509010
e,quest_main_tak_hard_7,normal,,7,tak_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",tak1,Hard,202509010
e,quest_main_tak_veryhard_7,normal,,7,tak_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",tak1,Extra,202509010
e,quest_main_glo2_normal_8,normal,,8,allstar_2,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo2,Normal,202509010
e,quest_main_glo2_hard_8,normal,,8,allstar_2,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo2,Hard,202509010
e,quest_main_glo2_veryhard_8,normal,,8,allstar_2,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo2,Extra,202509010
e,quest_main_chi_normal_9,normal,,9,chi_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",chi1,Normal,202509010
e,quest_main_chi_hard_9,normal,,9,chi_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",chi1,Hard,202509010
e,quest_main_chi_veryhard_9,normal,,9,chi_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",chi1,Extra,202509010
e,quest_main_sur_normal_10,normal,,10,sur_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sur1,Normal,202509010
e,quest_main_sur_hard_10,normal,,10,sur_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sur1,Hard,202509010
e,quest_main_sur_veryhard_10,normal,,10,sur_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sur1,Extra,202509010
e,quest_main_rik_normal_11,normal,,11,rik_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",rik1,Normal,202509010
e,quest_main_rik_hard_11,normal,,11,rik_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",rik1,Hard,202509010
e,quest_main_rik_veryhard_11,normal,,11,rik_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",rik1,Extra,202509010
e,quest_main_glo3_normal_12,normal,,12,allstar_3,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo3,Normal,202509010
e,quest_main_glo3_hard_12,normal,,12,allstar_3,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo3,Hard,202509010
e,quest_main_glo3_veryhard_12,normal,,12,allstar_3,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo3,Extra,202509010
e,quest_main_mag_normal_13,normal,,13,mag_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",mag1,Normal,202509010
e,quest_main_mag_hard_13,normal,,13,mag_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",mag1,Hard,202509010
e,quest_main_mag_veryhard_13,normal,,13,mag_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",mag1,Extra,202509010
e,quest_main_sum_normal_14,normal,,14,sum_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sum1,Normal,202509010
e,quest_main_sum_hard_14,normal,,14,sum_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sum1,Hard,202509010
e,quest_main_sum_veryhard_14,normal,,14,sum_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",sum1,Extra,202509010
e,quest_main_kai_normal_15,normal,,15,kai_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",kai1,Normal,202509010
e,quest_main_kai_hard_15,normal,,15,kai_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",kai1,Hard,202509010
e,quest_main_kai_veryhard_15,normal,,15,kai_1,"2025-05-01 12:00:00","2037-12-31 23:59:59",kai1,Extra,202509010
e,quest_main_glo4_normal_16,normal,,16,allstar_4,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo4,Normal,202509010
e,quest_main_glo4_hard_16,normal,,16,allstar_4,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo4,Hard,202509010
e,quest_main_glo4_veryhard_16,normal,,16,allstar_4,"2025-05-01 12:00:00","2037-12-31 23:59:59",glo4,Extra,202509010
e,quest_main_osh_normal_17,normal,,1,osh_1,"2026-01-01 00:00:00","2037-12-31 23:59:59",osh1,Normal,202512020
e,quest_main_osh_hard_17,normal,,1,osh_1,"2026-01-01 00:00:00","2037-12-31 23:59:59",osh1,Hard,202512020
e,quest_main_osh_veryhard_17,normal,,1,osh_1,"2026-01-01 00:00:00","2037-12-31 23:59:59",osh1,Extra,202512020
e,quest_enhance_00001,enhance,,1,enhance,"2024-01-01 0:00:00","2030-01-01 0:00:00",enhance,Normal,202509010
e,quest_event_kai1_charaget01,event,event_kai_00001,1,kai1_charaget01,"2025-09-22 11:00:00","2025-10-22 11:59:59",event_kai1_charaget_iharu,Normal,202509010
e,quest_event_kai1_challenge01,event,event_kai_00001,2,kai1_challenge01,"2025-09-22 11:00:00","2025-10-22 11:59:59",event_kai1_challenge01,Normal,202509010
e,quest_event_kai1_charaget02,event,event_kai_00001,3,kai1_charaget02,"2025-09-29 12:00:00","2025-10-22 11:59:59",event_kai1_charaget_isao,Normal,202509010
e,quest_event_kai1_savage,event,event_kai_00001,4,kai1_savage,"2025-09-22 11:00:00","2025-10-22 11:59:59",event_kai1_savege,Normal,202509010
e,quest_event_kai1_1day,event,event_kai_00001,5,kai1_1day,"2025-09-22 11:00:00","2025-10-06 03:59:59",event_kai1_1day,Normal,202509010
e,quest_event_spy1_charaget01,event,event_spy_00001,1,spy1_charaget01,"2025-10-06 15:00:00","2025-11-06 14:59:59",event_spy1_charaget_franky,Normal,202510010
e,quest_event_spy1_challenge01,event,event_spy_00001,2,spy1_challenge01,"2025-10-06 15:00:00","2025-11-06 14:59:59",event_spy1_challenge01,Normal,202510010
e,quest_event_spy1_charaget02,event,event_spy_00001,3,spy1_charaget02,"2025-10-13 15:00:00","2025-11-06 14:59:59",event_spy1_charaget_damian,Normal,202510010
e,quest_event_spy1_savage,event,event_spy_00001,4,spy1_savage,"2025-10-06 15:00:00","2025-11-06 14:59:59",event_spy1_savege,Normal,202510010
e,quest_event_spy1_1day,event,event_spy_00001,5,spy1_1day,"2025-10-06 15:00:00","2025-10-22 03:59:59",event_spy1_1day,Normal,202510010
e,quest_event_dan1_charaget01,event,event_dan_00001,1,dan1_charaget01,"2025-10-22 15:00:00","2025-11-25 14:59:59",event_dan1_charaget_bba,Normal,202510020
e,quest_event_dan1_challenge01,event,event_dan_00001,2,dan1_challenge01,"2025-10-22 15:00:00","2025-11-25 14:59:59",event_dan1_challenge01,Normal,202510020
e,quest_event_dan1_charaget02,event,event_dan_00001,3,dan1_charaget02,"2025-10-27 15:00:00","2025-11-25 14:59:59",event_dan1_charaget_aira,Normal,202510020
e,quest_event_dan1_savage,event,event_dan_00001,4,dan1_savage,"2025-10-22 15:00:00","2025-11-25 14:59:59",event_dan1_savege,Normal,202510020
e,quest_event_dan1_1day,event,event_dan_00001,5,dan1_1day,"2025-10-22 15:00:00","2025-11-06 03:59:59",event_dan1_1day,Normal,202510020
e,quest_event_mag1_charaget01,event,event_mag_00001,1,mag1_charaget01,"2025-11-06 15:00:00","2025-12-08 10:59:59",event_mag1_charaget_shigemoto,Normal,202511010
e,quest_event_mag1_challenge01,event,event_mag_00001,2,mag1_challenge01,"2025-11-06 15:00:00","2025-12-08 10:59:59",event_mag1_challenge01,Normal,202511010
e,quest_event_mag1_charaget02,event,event_mag_00001,3,mag1_charaget02,"2025-11-12 15:00:00","2025-12-08 10:59:59",event_mag1_charaget_akane,Normal,202511010
e,quest_event_mag1_savage,event,event_mag_00001,4,mag1_savage,"2025-11-06 15:00:00","2025-12-08 10:59:59",event_mag1_savege,Normal,202511010
e,quest_event_mag1_1day,event,event_mag_00001,5,mag1_1day,"2025-11-06 15:00:00","2025-11-25 03:59:59",event_mag1_1day,Normal,202511010
e,quest_event_yuw1_charaget01,event,event_yuw_00001,1,yuw1_charaget01,"2025-11-25 15:00:00","2025-12-31 23:59:59",event_yuw1_charaget_753,Normal,202511020
e,quest_event_yuw1_challenge01,event,event_yuw_00001,2,yuw1_challenge01,"2025-11-25 15:00:00","2025-12-31 23:59:59",event_yuw1_challenge01,Normal,202511020
e,quest_event_yuw1_charaget02,event,event_yuw_00001,3,yuw1_charaget02,"2025-12-01 15:00:00","2025-12-31 23:59:59",event_yuw1_charaget_okumura,Normal,202511020
e,quest_event_yuw1_savage,event,event_yuw_00001,4,yuw1_savage,"2025-11-25 15:00:00","2025-12-31 23:59:59",event_yuw1_savege,Normal,202511020
e,quest_event_yuw1_1day,event,event_yuw_00001,5,yuw1_1day,"2025-11-25 15:00:00","2025-12-08 03:59:59",event_yuw1_1day,Normal,202511020
e,quest_event_yuw1_savage02,event,event_yuw_00001,6,yuw1_savage02,"2025-12-22 15:00:00","2025-12-31 23:59:59",event_yuw1_savege02,Normal,202512015
e,quest_event_sur1_charaget01,event,event_sur_00001,1,sur1_charaget01,"2025-12-08 15:00:00","2026-01-16 10:59:59",event_sur1_charaget_sur,Normal,202512010
e,quest_event_sur1_challenge01,event,event_sur_00001,2,sur1_challenge01,"2025-12-08 15:00:00","2026-01-16 10:59:59",event_sur1_challenge01,Normal,202512010
e,quest_event_sur1_charaget02,event,event_sur_00001,3,sur1_charaget02,"2025-12-15 15:00:00","2026-01-16 10:59:59",event_sur1_charaget_aoba,Normal,202512010
e,quest_event_sur1_savage,event,event_sur_00001,4,sur1_savage,"2025-12-08 15:00:00","2026-01-16 10:59:59",event_sur1_savege,Normal,202512010
e,quest_event_sur1_1day,event,event_sur_00001,5,sur1_1day,"2025-12-08 15:00:00","2025-12-31 23:59:59",event_sur1_1day,Normal,202512010
e,quest_event_osh1_charaget01,event,event_osh_00001,1,osh1_charaget01,"2026-01-01 00:00:00","2026-02-02 10:59:59",event_osh1_charaget_repeat,Normal,202512020
e,quest_event_osh1_challenge01,event,event_osh_00001,2,osh1_challenge01,"2026-01-01 00:00:00","2026-02-02 10:59:59",event_osh1_challenge01,Normal,202512020
e,quest_event_osh1_charaget02,event,event_osh_00001,3,osh1_charaget02,"2026-01-05 15:00:00","2026-02-02 10:59:59",event_osh1_charaget_boot,Normal,202512020
e,quest_event_osh1_savage,event,event_osh_00001,4,osh1_savage,"2026-01-01 00:00:00","2026-02-02 10:59:59",event_osh1_savege,Normal,202512020
e,quest_event_osh1_1day,event,event_osh_00001,5,osh1_1day,"2026-01-01 04:00:00","2026-01-16 03:59:59",event_osh1_1day,Normal,202512020
e,quest_event_glo1_1day,event,event_glo_00001,6,glo1_1day,"2026-01-01 00:00:00","2026-01-05 14:59:59",event_glo1_1day,Normal,202512020
e,quest_event_jig1_charaget01,event,event_jig_00001,1,jig1_charaget01,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig1_charaget_mei,Normal,202601010
e,quest_event_jig1_challenge01,event,event_jig_00001,2,jig1_challenge01,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig1_challenge01,Normal,202601010
e,quest_event_jig1_charaget02,event,event_jig_00001,3,jig1_charaget02,"2026-01-21 15:00:00","2026-02-16 14:59:59",event_jig1_charaget_boot,Normal,202601010
e,quest_event_jig1_savage,event,event_jig_00001,4,jig1_savage,"2026-01-16 15:00:00","2026-02-16 10:59:59",event_jig1_savege,Normal,202601010
e,quest_event_jig1_1day,event,event_jig_00001,5,jig1_1day,"2026-01-16 15:00:00","2026-02-2 03:59:59",event_jig1_1day,Normal,202601010
e,quest_event_you1_charaget01,event,event_you_00001,1,you1_charaget01,"2026-02-02 15:00:00","2026-03-02 10:59:59",event_you1_charaget_dagu,Normal,202602010
e,quest_event_you1_challenge,event,event_you_00001,2,you1_challenge01,"2026-02-02 15:00:00","2026-03-02 10:59:59",event_you1_challenge,Normal,202602010
e,quest_event_you1_charaget02,event,event_you_00001,3,you1_charaget02,"2026-02-06 15:00:00","2026-03-02 10:59:59",event_you1_charaget_hana,Normal,202602010
e,quest_event_you1_savage,event,event_you_00001,4,you1_savage,"2026-02-02 15:00:00","2026-03-02 10:59:59",event_you1_savege,Normal,202602010
e,quest_event_you1_1day,event,event_you_00001,5,you1_1day,"2026-02-02 15:00:00","2026-02-16 03:59:59",event_you1_1day,Normal,202602010
e,quest_event_kim1_charaget01,event,event_kim_00001,1,kim1_charaget01,"2026-02-16 15:00:00","2026-03-16 10:59:59",event_kim1_charaget_repeat,Normal,202602020
e,quest_event_kim1_challenge,event,event_kim_00001,2,kim1_challenge01,"2026-02-16 15:00:00","2026-03-16 10:59:59",event_kim1_challenge,Normal,202602020
e,quest_event_kim1_charaget02,event,event_kim_00001,3,kim1_charaget02,"2026-02-20 15:00:00","2026-03-16 10:59:59",event_kim1_charaget_story,Normal,202602020
e,quest_event_kim1_savage,event,event_kim_00001,4,kim1_savage,"2026-02-16 15:00:00","2026-03-16 10:59:59",event_kim1_savege,Normal,202602020
e,quest_event_kim1_1day,event,event_kim_00001,5,kim1_1day,"2026-02-16 15:00:00","2026-03-02 03:59:59",event_kim1_1day,Normal,202602020
```

---

<!-- FILE: ./projects/glow-masterdata/MstQuestI18n.csv -->
## ./projects/glow-masterdata/MstQuestI18n.csv

```csv
ENABLE,release_key,id,mst_quest_id,language,name,category_name,flavor_text
e,202509010,tutorial_ja,tutorial,ja,チュートリアル,,チュートリアル専用のステージ
e,202509010,quest_main_spy_normal_1_ja,quest_main_spy_normal_1,ja,SPY×FAMILY,,"『SPY×FAMILY』のクエスト。\n\nこのクエストでは、青属性の敵と攻撃UPコマが登場。\n黄属性のキャラとコマ効果を活用して対抗しよう。"
e,202509010,quest_main_spy_hard_1_ja,quest_main_spy_hard_1,ja,SPY×FAMILY,,"『SPY×FAMILY』のクエスト。\n\nこのクエストでは、青属性の敵と攻撃UPコマが登場。\n黄属性のキャラとコマ効果を活用して対抗しよう。"
e,202509010,quest_main_spy_veryhard_1_ja,quest_main_spy_veryhard_1,ja,SPY×FAMILY,,"『SPY×FAMILY』のクエスト。\n\nこのクエストでは、青属性の敵と攻撃UPコマが登場。\n黄属性のキャラとコマ効果を活用して対抗しよう。"
e,202509010,quest_main_gom_normal_2_ja,quest_main_gom_normal_2,ja,姫様“拷問”の時間です,,"『姫様“拷問”の時間です』のクエスト。\n\nこのクエストでは、黄属性の敵とダメージコマが登場。\n緑属性のキャラで対抗しよう。\nダメージコマに入ったキャラはダメージを受けるから気をつけて戦おう。"
e,202509010,quest_main_gom_hard_2_ja,quest_main_gom_hard_2,ja,姫様“拷問”の時間です,,"『姫様“拷問”の時間です』のクエスト。\n\nこのクエストでは、黄属性の敵とダメージコマが登場。\n緑属性のキャラで対抗しよう。\nダメージコマに入ったキャラはダメージを受けるから気をつけて戦おう。"
e,202509010,quest_main_gom_veryhard_2_ja,quest_main_gom_veryhard_2,ja,姫様“拷問”の時間です,,"『姫様“拷問”の時間です』のクエスト。\n\nこのクエストでは、黄属性の敵とダメージコマが登場。\n緑属性のキャラで対抗しよう。\nダメージコマに入ったキャラはダメージを受けるから気をつけて戦おう。"
e,202509010,quest_main_aka_normal_3_ja,quest_main_aka_normal_3,ja,ラーメン赤猫,,"『ラーメン赤猫』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n攻撃DOWNコマに入ったキャラは攻撃が下がってしまうから気をつけて戦おう。"
e,202509010,quest_main_aka_hard_3_ja,quest_main_aka_hard_3,ja,ラーメン赤猫,,"『ラーメン赤猫』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n攻撃DOWNコマに入ったキャラは攻撃が下がってしまうから気をつけて戦おう。"
e,202509010,quest_main_aka_veryhard_3_ja,quest_main_aka_veryhard_3,ja,ラーメン赤猫,,"『ラーメン赤猫』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n攻撃DOWNコマに入ったキャラは攻撃が下がってしまうから気をつけて戦おう。"
e,202509010,quest_main_glo1_normal_4_ja,quest_main_glo1_normal_4,ja,"リミックスクエスト Vol.1",,"『SPY×FAMILY』『姫様""拷問""の時間です』『ラーメン赤猫』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo1_hard_4_ja,quest_main_glo1_hard_4,ja,"リミックスクエスト Vol.1",,"『SPY×FAMILY』『姫様""拷問""の時間です』『ラーメン赤猫』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo1_veryhard_4_ja,quest_main_glo1_veryhard_4,ja,"リミックスクエスト Vol.1",,"『SPY×FAMILY』『姫様""拷問""の時間です』『ラーメン赤猫』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_dan_normal_5_ja,quest_main_dan_normal_5,ja,ダンダダン,,"『ダンダダン』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n暗闇コマは味方キャラが侵入すると中の様子が確認できるようになる。"
e,202509010,quest_main_dan_hard_5_ja,quest_main_dan_hard_5,ja,ダンダダン,,"『ダンダダン』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n暗闇コマは味方キャラが侵入すると中の様子が確認できるようになる。"
e,202509010,quest_main_dan_veryhard_5_ja,quest_main_dan_veryhard_5,ja,ダンダダン,,"『ダンダダン』のクエスト。\n\nこのクエストでは、赤属性の敵が登場。\n青属性のキャラで対抗しよう。\n暗闇コマは味方キャラが侵入すると中の様子が確認できるようになる。"
e,202509010,quest_main_jig_normal_6_ja,quest_main_jig_normal_6,ja,地獄楽,,"『地獄楽』のクエスト。\n\nこのクエストでは、「毒」攻撃や「スタン」攻撃を主にしてくるぞ。\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_jig_hard_6_ja,quest_main_jig_hard_6,ja,地獄楽,,"『地獄楽』のクエスト。\n\nこのクエストでは、「毒」攻撃や「スタン」攻撃を主にしてくるぞ。\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_jig_veryhard_6_ja,quest_main_jig_veryhard_6,ja,地獄楽,,"『地獄楽』のクエスト。\n\nこのクエストでは、「毒」攻撃や「スタン」攻撃を主にしてくるぞ。\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_tak_normal_7_ja,quest_main_tak_normal_7,ja,タコピーの原罪,,"『タコピーの原罪』のクエスト。\n\nこのクエストでは、様々なコマ効果が登場するぞ!\n特に暗闇コマの中には注意しよう!"
e,202509010,quest_main_tak_hard_7_ja,quest_main_tak_hard_7,ja,タコピーの原罪,,"『タコピーの原罪』のクエスト。\n\nこのクエストでは、様々なコマ効果が登場するぞ!\n特に暗闇コマの中には注意しよう!"
e,202509010,quest_main_tak_veryhard_7_ja,quest_main_tak_veryhard_7,ja,タコピーの原罪,,"『タコピーの原罪』のクエスト。\n\nこのクエストでは、様々なコマ効果が登場するぞ!\n特に暗闇コマの中には注意しよう!"
e,202509010,quest_main_glo2_normal_8_ja,quest_main_glo2_normal_8,ja,"リミックスクエスト Vol.2",,"『ダンダダン』『地獄楽』『タコピーの原罪』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo2_hard_8_ja,quest_main_glo2_hard_8,ja,"リミックスクエスト Vol.2",,"『ダンダダン』『地獄楽』『タコピーの原罪』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo2_veryhard_8_ja,quest_main_glo2_veryhard_8,ja,"リミックスクエスト Vol.2",,"『ダンダダン』『地獄楽』『タコピーの原罪』の\n3作品のリミックスクエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_chi_normal_9_ja,quest_main_chi_normal_9,ja,チェンソーマン,,"『チェンソーマン』のクエスト。\n\nこのクエストでは、敵自身が体力を「回復」してくる攻撃を主にしてくるぞ!\n短期決戦に持ち込もう!"
e,202509010,quest_main_chi_hard_9_ja,quest_main_chi_hard_9,ja,チェンソーマン,,"『チェンソーマン』のクエスト。\n\nこのクエストでは、敵自身が体力を「回復」してくる攻撃を主にしてくるぞ!\n短期決戦に持ち込もう!"
e,202509010,quest_main_chi_veryhard_9_ja,quest_main_chi_veryhard_9,ja,チェンソーマン,,"『チェンソーマン』のクエスト。\n\nこのクエストでは、敵自身が体力を「回復」してくる攻撃を主にしてくるぞ!\n短期決戦に持ち込もう!"
e,202509010,quest_main_sur_normal_10_ja,quest_main_sur_normal_10,ja,魔都精兵のスレイブ,,"『魔都精兵のスレイブ』のクエスト。\n\nこのクエストでは、「スタン」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_sur_hard_10_ja,quest_main_sur_hard_10,ja,魔都精兵のスレイブ,,"『魔都精兵のスレイブ』のクエスト。\n\nこのクエストでは、「スタン」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_sur_veryhard_10_ja,quest_main_sur_veryhard_10,ja,魔都精兵のスレイブ,,"『魔都精兵のスレイブ』のクエスト。\n\nこのクエストでは、「スタン」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_rik_normal_11_ja,quest_main_rik_normal_11,ja,トマトイプーのリコピン,,"『トマトイプーのリコピン』のクエスト。\n\nこのクエストでは、様々な行動をしてくるリコピンが出現!\n「ノックバック」攻撃や、「攻撃DOWN」攻撃などの攻撃を主にしてくるぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_rik_hard_11_ja,quest_main_rik_hard_11,ja,トマトイプーのリコピン,,"『トマトイプーのリコピン』のクエスト。\n\nこのクエストでは、様々な行動をしてくるリコピンが出現!\n「ノックバック」攻撃や、「攻撃DOWN」攻撃などの攻撃を主にしてくるぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_rik_veryhard_11_ja,quest_main_rik_veryhard_11,ja,トマトイプーのリコピン,,"『トマトイプーのリコピン』のクエスト。\n\nこのクエストでは、様々な行動をしてくるリコピンが出現!\n「ノックバック」攻撃や、「攻撃DOWN」攻撃などの攻撃を主にしてくるぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_glo3_normal_12_ja,quest_main_glo3_normal_12,ja,"リミックスクエスト Vol.3",,"『チェンソーマン』『魔都精兵のスレイブ』『トマトイプーのリコピン』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo3_hard_12_ja,quest_main_glo3_hard_12,ja,"リミックスクエスト Vol.3",,"『チェンソーマン』『魔都精兵のスレイブ』『トマトイプーのリコピン』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo3_veryhard_12_ja,quest_main_glo3_veryhard_12,ja,"リミックスクエスト Vol.3",,"『チェンソーマン』『魔都精兵のスレイブ』『トマトイプーのリコピン』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_mag_normal_13_ja,quest_main_mag_normal_13,ja,株式会社マジルミエ,,"『株式会社マジルミエ』のクエスト。\n\nこのクエストでは、大量の敵が「氷結」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_mag_hard_13_ja,quest_main_mag_hard_13,ja,株式会社マジルミエ,,"『株式会社マジルミエ』のクエスト。\n\nこのクエストでは、大量の敵が「氷結」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_mag_veryhard_13_ja,quest_main_mag_veryhard_13,ja,株式会社マジルミエ,,"『株式会社マジルミエ』のクエスト。\n\nこのクエストでは、大量の敵が「氷結」攻撃や「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_sum_normal_14_ja,quest_main_sum_normal_14,ja,サマータイムレンダ,,"『サマータイムレンダ』のクエスト。\n\nこのクエストでは、「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_sum_hard_14_ja,quest_main_sum_hard_14,ja,サマータイムレンダ,,"『サマータイムレンダ』のクエスト。\n\nこのクエストでは、「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_sum_veryhard_14_ja,quest_main_sum_veryhard_14,ja,サマータイムレンダ,,"『サマータイムレンダ』のクエスト。\n\nこのクエストでは、「ノックバック」攻撃を主にしてくるぞ!\nキャラの特性で対策して挑もう!"
e,202509010,quest_main_kai_normal_15_ja,quest_main_kai_normal_15,ja,怪獣８号,,"『怪獣８号』のクエスト。\n\nこのクエストでは「攻撃DOWN」をしてくる敵が多く登場するぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_kai_hard_15_ja,quest_main_kai_hard_15,ja,怪獣８号,,"『怪獣８号』のクエスト。\n\nこのクエストでは「攻撃DOWN」をしてくる敵が多く登場するぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_kai_veryhard_15_ja,quest_main_kai_veryhard_15,ja,怪獣８号,,"『怪獣８号』のクエスト。\n\nこのクエストでは「攻撃DOWN」をしてくる敵が多く登場するぞ!\n遠距離攻撃が得意なキャラが活躍するぞ!"
e,202509010,quest_main_glo4_normal_16_ja,quest_main_glo4_normal_16,ja,"リミックスクエスト Vol.4",,"『株式会社マジルミエ』『サマータイムレンダ』『怪獣８号』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo4_hard_16_ja,quest_main_glo4_hard_16,ja,"リミックスクエスト Vol.4",,"『株式会社マジルミエ』『サマータイムレンダ』『怪獣８号』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202509010,quest_main_glo4_veryhard_16_ja,quest_main_glo4_veryhard_16,ja,"リミックスクエスト Vol.4",,"『株式会社マジルミエ』『サマータイムレンダ』『怪獣８号』の\n3作品オールスタークエスト。\n\n3作品クエストの様々な敵が登場するぞ!"
e,202512020,quest_main_osh_normal_17_ja,quest_main_osh_normal_17,ja,【推しの子】,,"『【推しの子】』のクエスト。\n\nこのクエストでは、火傷コマが登場。\nキャラの特性で対策して挑もう!"
e,202512020,quest_main_osh_hard_17_ja,quest_main_osh_hard_17,ja,【推しの子】,,"『【推しの子】』のクエスト。\n\nこのクエストでは、火傷コマが登場。\nキャラの特性で対策して挑もう!"
e,202512020,quest_main_osh_veryhard_17_ja,quest_main_osh_veryhard_17,ja,【推しの子】,,"『【推しの子】』のクエスト。\n\nこのクエストでは、火傷コマが登場。\nキャラの特性で対策して挑もう!"
e,202509010,quest_enhance_00001_ja,quest_enhance_00001,ja,強化クエスト,,毎日挑戦！ハイスコアを目指そう！
e,202509010,quest_event_kai1_charaget01_ja,quest_event_kai1_charaget01,ja,"気に入らねェ 気に入らねェ",ストーリー,
e,202509010,quest_event_kai1_challenge01_ja,quest_event_kai1_challenge01,ja,"戦場で 力を示してみせろ ヒヨコども",チャレンジ,
e,202509010,quest_event_kai1_charaget02_ja,quest_event_kai1_charaget02,ja,怪獣８号の引き渡しを命ずる,ストーリー,
e,202509010,quest_event_kai1_savage_ja,quest_event_kai1_savage,ja,クラス『大怪獣』,高難易度,
e,202509010,quest_event_kai1_1day_ja,quest_event_kai1_1day,ja,候補生としての入隊,デイリー,
e,202510010,quest_event_spy1_charaget01_ja,quest_event_spy1_charaget01,ja,ストーリークエスト1,ストーリー,
e,202510010,quest_event_spy1_challenge01_ja,quest_event_spy1_challenge01,ja,チャレンジクエスト,チャレンジ,
e,202510010,quest_event_spy1_charaget02_ja,quest_event_spy1_charaget02,ja,ストーリークエスト2,ストーリー,
e,202510010,quest_event_spy1_savage_ja,quest_event_spy1_savage,ja,高難易度クエスト,高難易度,
e,202510010,quest_event_spy1_1day_ja,quest_event_spy1_1day,ja,デイリークエスト,デイリー,
e,202510020,quest_event_dan1_charaget01_ja,quest_event_dan1_charaget01,ja,ストーリークエスト1,ストーリー,
e,202510020,quest_event_dan1_challenge01_ja,quest_event_dan1_challenge01,ja,チャレンジクエスト,チャレンジ,
e,202510020,quest_event_dan1_charaget02_ja,quest_event_dan1_charaget02,ja,ストーリークエスト2,ストーリー,
e,202510020,quest_event_dan1_savage_ja,quest_event_dan1_savage,ja,高難易度クエスト,高難易度,
e,202510020,quest_event_dan1_1day_ja,quest_event_dan1_1day,ja,デイリークエスト,デイリー,
e,202511010,quest_event_mag1_charaget01_ja,quest_event_mag1_charaget01,ja,うちの美学,ストーリー,
e,202511010,quest_event_mag1_challenge01_ja,quest_event_mag1_challenge01,ja,色々な魔法少女,チャレンジ,
e,202511010,quest_event_mag1_charaget02_ja,quest_event_mag1_charaget02,ja,よく見てる,ストーリー,
e,202511010,quest_event_mag1_savage_ja,quest_event_mag1_savage,ja,「怪異」現象,高難易度,
e,202511010,quest_event_mag1_1day_ja,quest_event_mag1_1day,ja,何とかやってます,デイリー,
e,202511020,quest_event_yuw1_charaget01_ja,quest_event_yuw1_charaget01,ja,コスプレをしに来たんだよ,ストーリー,
e,202511020,quest_event_yuw1_challenge01_ja,quest_event_yuw1_challenge01,ja,幸せです…,チャレンジ,
e,202511020,quest_event_yuw1_charaget02_ja,quest_event_yuw1_charaget02,ja,俺はずっとオタクなだけです,ストーリー,
e,202511020,quest_event_yuw1_savage_ja,quest_event_yuw1_savage,ja,これがこの世界の頂上,高難易度,
e,202511020,quest_event_yuw1_1day_ja,quest_event_yuw1_1day,ja,はじめての撮影会,デイリー,
e,202512015,quest_event_yuw1_savage02_ja,quest_event_yuw1_savage02,ja,クリスマスバトル!!,高難易度,
e,202512010,quest_event_sur1_charaget01_ja,quest_event_sur1_charaget01,ja,スレイブの誕生,ストーリー,
e,202512010,quest_event_sur1_challenge01_ja,quest_event_sur1_challenge01,ja,魔都防衛隊,チャレンジ,
e,202512010,quest_event_sur1_charaget02_ja,quest_event_sur1_charaget02,ja,隠れ里の戦い,ストーリー,
e,202512010,quest_event_sur1_savage_ja,quest_event_sur1_savage,ja,スレイブと組長,高難易度,
e,202512010,quest_event_sur1_1day_ja,quest_event_sur1_1day,ja,"精兵と管理人	",デイリー,
e,202512020,quest_event_osh1_charaget01_ja,quest_event_osh1_charaget01,ja,芸能界へ!,収集,
e,202512020,quest_event_osh1_challenge01_ja,quest_event_osh1_challenge01,ja,推しの子になってやる,チャレンジ,
e,202512020,quest_event_osh1_charaget02_ja,quest_event_osh1_charaget02,ja,ぴえヨンのブートクエスト,強化,
e,202512020,quest_event_osh1_savage_ja,quest_event_osh1_savage,ja,芸能界には才能が集まる,高難易度,
e,202512020,quest_event_osh1_1day_ja,quest_event_osh1_1day,ja,ファンと推し合戦!,デイリー,
e,202512020,quest_event_glo1_1day_ja,quest_event_glo1_1day,ja,開運!ジャンブル運試し,デイリー,
e,202601010,quest_event_jig1_charaget01_ja,quest_event_jig1_charaget01,ja,必ず生きて帰る,ストーリー,
e,202601010,quest_event_jig1_challenge01_ja,quest_event_jig1_challenge01,ja,死罪人と首切り役人,チャレンジ,
e,202601010,quest_event_jig1_charaget02_ja,quest_event_jig1_charaget02,ja,朱印の者たち,ストーリー,
e,202601010,quest_event_jig1_savage_ja,quest_event_jig1_savage,ja,手負いの獣は恐ろしいぞ,高難易度,
e,202601010,quest_event_jig1_1day_ja,quest_event_jig1_1day,ja,"本能が告げている 危険だと",デイリー,
e,202602010,quest_event_you1_charaget01_ja,quest_event_you1_charaget01,ja,先輩は敬いたまえ,ストーリー,
e,202602010,quest_event_you1_challenge_ja,quest_event_you1_challenge,ja,世界一安全な幼稚園,チャレンジ,
e,202602010,quest_event_you1_charaget02_ja,quest_event_you1_charaget02,ja,兄を助けてくれないか?,ストーリー,
e,202602010,quest_event_you1_savage_ja,quest_event_you1_savage,ja,正義だけじゃ何も守れない,高難易度,
e,202602010,quest_event_you1_1day_ja,quest_event_you1_1day,ja,お遊戯の時間です,デイリー,
e,202602020,quest_event_kim1_charaget01_ja,quest_event_kim1_charaget01,ja,キスゾンビ♡パニック,収集,
e,202602020,quest_event_kim1_challenge_ja,quest_event_kim1_challenge,ja,恋太郎ファミリー,チャレンジ,
e,202602020,quest_event_kim1_charaget02_ja,quest_event_kim1_charaget02,ja,最高の恋愛パートナー,ストーリー,
e,202602020,quest_event_kim1_savage_ja,quest_event_kim1_savage,ja,"DEAD OR LOVE",高難易度,
e,202602020,quest_event_kim1_1day_ja,quest_event_kim1_1day,ja,恋は盲目,デイリー,
```

---

<!-- FILE: ./projects/glow-masterdata/MstSeries.csv -->
## ./projects/glow-masterdata/MstSeries.csv

```csv
ENABLE,id,asset_key,release_key,jump_plus_url,banner_asset_key
e,spy,spy,202509010,https://shonenjumpplus.com/episode/10834108156648240735,spy
e,aka,aka,202509010,https://shonenjumpplus.com/episode/316112896905504410,aka
e,rik,rik,202509010,https://shonenjumpplus.com/episode/10834108156632007715,rik
e,dan,dan,202509010,https://shonenjumpplus.com/episode/3269632237310729754,dan
e,gom,gom,202509010,https://shonenjumpplus.com/episode/10834108156649530410,gom
e,chi,chi,202509010,https://shonenjumpplus.com/episode/10834108156650024834,chi
e,mag,mag,202509010,https://shonenjumpplus.com/episode/3269754496555043141,mag
e,dos,dos,202509010,https://shonenjumpplus.com/episode/10834108156684177150,dos
e,bat,bat,202509010,https://shonenjumpplus.com/episode/10834108156630506434,bat
e,kai,kai,202509010,https://shonenjumpplus.com/episode/13933686331674116123,kai
e,yuw,yuw,202509010,https://shonenjumpplus.com/episode/13933686331679642476,yuw
e,sur,sur,202509010,https://shonenjumpplus.com/episode/10834108156641784254,sur
e,ron,ron,202509010,https://shonenjumpplus.com/episode/13933686331719348891,ron
e,aha,aha,202509010,https://shonenjumpplus.com/episode/13932016480028799982,aha
e,sum,sum,202509010,https://shonenjumpplus.com/episode/13932016480029133982,sum
e,jig,jig,202509010,https://shonenjumpplus.com/episode/13932016480029295972,jig
e,tak,tak,202509010,https://shonenjumpplus.com/episode/3269754496638370192,tak
e,osh,osh,202512020,https://shonenjumpplus.com/episode/13933686331661632099,osh
e,you,you,202602010,https://shonenjumpplus.com/episode/4855956445109234830,you
e,kim,kim,202602020,https://shonenjumpplus.com/episode/13933686331623812157,kim
e,hut,hut,202603010,https://shonenjumpplus.com/episode/16457717013869519536,hut
```

---

<!-- FILE: ./projects/glow-masterdata/MstSeriesI18n.csv -->
## ./projects/glow-masterdata/MstSeriesI18n.csv

```csv
ENABLE,release_key,id,mst_series_id,language,name,prefix_word
e,202509010,spy_ja,spy,ja,SPY×FAMILY,す
e,202509010,aka_ja,aka,ja,ラーメン赤猫,ら
e,202509010,rik_ja,rik,ja,トマトイプーのリコピン,と
e,202509010,dan_ja,dan,ja,ダンダダン,た
e,202509010,gom_ja,gom,ja,姫様“拷問”の時間です,ひ
e,202509010,chi_ja,chi,ja,チェンソーマン,ち
e,202509010,mag_ja,mag,ja,株式会社マジルミエ,ま
e,202509010,dos_ja,dos,ja,道産子ギャルはなまらめんこい,と
e,202509010,bat_ja,bat,ja,忘却バッテリー,ぼ
e,202509010,kai_ja,kai,ja,怪獣８号,か
e,202509010,yuw_ja,yuw,ja,2.5次元の誘惑,に
e,202509010,sur_ja,sur,ja,魔都精兵のスレイブ,ま
e,202509010,ron_ja,ron,ja,鴨乃橋ロンの禁断推理,か
e,202509010,aha_ja,aha,ja,阿波連さんははかれない,あ
e,202509010,sum_ja,sum,ja,サマータイムレンダ,さ
e,202509010,jig_ja,jig,ja,地獄楽,し
e,202509010,tak_ja,tak,ja,タコピーの原罪,た
e,202512020,osh_ja,osh,ja,【推しの子】,お
e,202602010,you_ja,you,ja,幼稚園WARS,よ
e,202602020,kim_ja,kim,ja,君のことが大大大大大好きな100人の彼女,き
e,202603010,hut_ja,hut,ja,ふつうの軽音部,ふ
```

---

<!-- FILE: ./projects/glow-masterdata/MstShopItem.csv -->
## ./projects/glow-masterdata/MstShopItem.csv

```csv
ENABLE,id,shop_type,cost_type,cost_amount,is_first_time_free,tradable_count,resource_type,resource_id,resource_amount,start_date,end_date,release_key
e,daily01,Daily,Ad,,0,1,FreeDiamond,,20,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,daily07,Daily,Diamond,30,0,1,Item,entry_item_glo_00001,1,"2025-10-27 04:00:00","2034-01-01 00:00:00",202510020
e,daily02,Daily,Diamond,10,0,1,Item,memory_glo_00001,100,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,daily03,Daily,Diamond,10,0,1,Item,memory_glo_00002,100,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,daily04,Daily,Diamond,10,0,1,Item,memory_glo_00003,100,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,daily05,Daily,Diamond,10,0,1,Item,memory_glo_00004,100,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,daily06,Daily,Diamond,10,0,1,Item,memory_glo_00005,100,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly01,Weekly,Ad,,0,1,FreeDiamond,,30,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly02,Weekly,Ad,,0,1,Item,ticket_glo_00003,1,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly03,Weekly,Diamond,30,0,5,Item,entry_item_glo_00001,1,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly04,Weekly,Diamond,25,0,6,Item,memoryfragment_glo_00001,5,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly05,Weekly,Diamond,35,0,2,Item,memoryfragment_glo_00002,5,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,weekly06,Weekly,Diamond,15,0,1,Item,memoryfragment_glo_00003,1,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,coin01,Coin,Ad,,1,3,Coin,,1000,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
```

---

<!-- FILE: ./projects/glow-masterdata/MstShopPass.csv -->
## ./projects/glow-masterdata/MstShopPass.csv

```csv
ENABLE,id,opr_product_id,is_display_expiration,pass_duration_days,asset_key,shop_pass_cell_color,release_key
e,premium_pass_01,17,0,25,premium,Gold,202509010
e,premium_pass_02,48,1,15,newyear2026,red,202512020
```

---

<!-- FILE: ./projects/glow-masterdata/MstShopPassI18n.csv -->
## ./projects/glow-masterdata/MstShopPassI18n.csv

```csv
ENABLE,release_key,id,mst_shop_pass_id,language,name
e,202509010,premium_pass_01_ja,premium_pass_01,ja,プレミアムパス
e,202512020,premium_pass_02_ja,premium_pass_02,ja,謹賀新年特別パス
```

---

