---
name: masterdata-from-bizops-mission
description: ミッションの運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 8個(MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionReward, MstMissionEventDailyBonus, MstMissionEventDailyBonusSchedule, MstMissionLimitedTerm, MstMissionLimitedTermI18n)。イベントミッションのマスタデータを精度高く作成します。
---

# ミッション マスタデータ作成スキル

## 概要

イベントミッションの運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の8テーブルを自動生成:

**ミッション基本情報**:
- **MstMissionEvent** - イベントミッションの基本情報(達成条件、報酬等)
- **MstMissionEventI18n** - ミッション説明文(多言語対応)
- **MstMissionEventDependency** - ミッション間の解放順序の依存関係
- **MstMissionReward** - ミッション報酬

**ログインボーナス**(条件付き):
- **MstMissionEventDailyBonus** - イベント期間中のログインボーナス
- **MstMissionEventDailyBonusSchedule** - ログインボーナスの開催スケジュール

**期間限定ミッション**(条件付き):
- **MstMissionLimitedTerm** - 期間限定ミッション(降臨バトル等)
- **MstMissionLimitedTermI18n** - 期間限定ミッション説明文(多言語対応)

**重要**: MstMissionEventDailyBonus系はログインボーナスがある場合のみ、MstMissionLimitedTerm系は期間限定ミッションがある場合のみ作成します。

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **mst_event_id** | イベントID | `event_jig_00001` |
| **event_number** | イベント連番(5桁) | `00001` |
| **mission_count** | ミッション数 | `43` |
| **has_daily_bonus** | ログインボーナスフラグ | `0`(なし)/`1`(あり) |
| **has_limited_term_mission** | 期間限定ミッションフラグ | `0`(なし)/`1`(あり) |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
ミッションの運営仕様書からマスタデータを作成してください。

添付ファイル:
- イベントミッション設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_event_id: event_jig_00001
- event_number: 00001
- mission_count: 43
- has_daily_bonus: 1
- has_limited_term_mission: 1
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- ミッションの達成条件(何を何回実行するか)
- ミッションの報酬内容
- イベントID(mst_event_id)
- ミッションの表示順序
- ミッション説明文(日本語)
- ミッション間の依存関係(順序解放の有無)

**任意情報**:
- ログインボーナスの期間と報酬内容(記載がない場合は作成不要)
- 期間限定ミッションの有無(記載がない場合は作成不要)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstMissionEvent** - イベントミッション基本情報
2. **MstMissionEventI18n** - ミッション説明文
3. **MstMissionEventDependency** - ミッション間の依存関係(順序解放が必要なグループのみ)
4. **MstMissionReward** - ミッション報酬
5. **MstMissionEventDailyBonus** - ログインボーナス(has_daily_bonus=1の場合のみ)
6. **MstMissionEventDailyBonusSchedule** - ログインボーナススケジュール(has_daily_bonus=1の場合のみ)
7. **MstMissionLimitedTerm** - 期間限定ミッション(has_limited_term_mission=1の場合のみ)
8. **MstMissionLimitedTermI18n** - 期間限定ミッション説明文(has_limited_term_mission=1の場合のみ)

#### ID採番ルール

ミッションのIDは以下の形式で採番します:

```
MstMissionEvent.id: event_{作品ID}_{イベント連番5桁}_{ミッション連番}
MstMissionEvent.mst_mission_reward_group_id: {作品ID}_{イベント連番5桁}_event_reward_{報酬段階}
MstMissionEventDependency.group_id: event_{作品ID}_{イベント連番5桁}_{ミッション連番} (グループの最初のミッションID)
MstMissionReward.id: mission_reward_{連番}
MstMissionEventDailyBonus.id: {mst_event_id}_daily_bonus_{login_day:02d}
MstMissionLimitedTerm.id: {作品ID}_{イベント連番5桁}_limited_term_{連番}
```

**例**:
```
event_jig_00001_1 (地獄楽 イベント1 ミッション1)
jig_00001_event_reward_01 (地獄楽 イベント1 報酬段階1)
event_jig_00001_1 (依存関係グループの最初のミッションID)
mission_reward_480 (報酬連番480)
event_jig_00001_daily_bonus_01 (ログインボーナス1日目)
jig_00001_limited_term_1 (期間限定ミッション1)
```

**重要な注意点**:
- **報酬段階のゼロパディング**: 通常は2桁ゼロパディング(01、02、03...)、例外として推しの子(osh)のみゼロパディングなし(1、2、3...)

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] criterion_typeとdestination_sceneが正しく対応しているか
- [ ] enum値が正確に一致しているか(criterion_type、resource_type、destination_scene等)
- [ ] group_keyが常に空欄であるか(イベントミッションでは使用しない)
- [ ] unlock_criterion_typeが常に`__NULL__`であるか
- [ ] 依存関係グループのgroup_idが最初のミッションIDと一致しているか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstMissionEvent.destination_scene`: criterion_typeから推測したdestination_scene
- `MstMissionReward.resource_id`: アイテムIDの推測
- `MstMissionEventDailyBonus.mst_mission_reward_group_id`: ログインボーナス報酬グループID
- `MstMissionLimitedTerm.start_at/end_at`: 期間限定ミッション開催期間

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の8シート(条件付きを含む)を作成:
  1. MstMissionEvent
  2. MstMissionEventI18n
  3. MstMissionEventDependency(順序解放が必要なグループのみ)
  4. MstMissionReward
  5. MstMissionEventDailyBonus(has_daily_bonus=1の場合のみ)
  6. MstMissionEventDailyBonusSchedule(has_daily_bonus=1の場合のみ)
  7. MstMissionLimitedTerm(has_limited_term_mission=1の場合のみ)
  8. MstMissionLimitedTermI18n(has_limited_term_mission=1の場合のみ)

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstMissionEvent.destination_scene
- 値: Event
- 理由: criterion_type(SpecificUnitGradeUpCount)からdestination_sceneを推測
- 確認事項: ユーザーが遷移する画面が適切か確認してください

### MstMissionReward.resource_id
- 値: memory_chara_jig_00701
- 理由: 設計書にアイテムIDの記載がなく、キャラクターIDから推測
- 確認事項: 正しいアイテムIDか、MstItemテーブルに存在するか確認してください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstMissionEvent シート

| ENABLE | id | release_key | mst_event_id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | unlock_criterion_value | unlock_criterion_count | group_key | mst_mission_reward_group_id | sort_order | destination_scene |
|--------|----|-----------|--------------|-----------------|-----------------|-----------------|-----------------------|------------------------|------------------------|-----------|----------------------------|-----------|-------------------|
| e | event_jig_00001_1 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 2 | __NULL__ | | 0 | | jig_00001_event_reward_01 | 1 | UnitList |
| e | event_jig_00001_2 | 202601010 | event_jig_00001 | SpecificUnitGradeUpCount | chara_jig_00701 | 3 | __NULL__ | | 0 | | jig_00001_event_reward_02 | 2 | UnitList |

### MstMissionEventI18n シート

| ENABLE | release_key | id | mst_mission_event_id | language | description |
|--------|-------------|----|--------------------|----------|------------|
| e | 202601010 | event_jig_00001_1_ja | event_jig_00001_1 | ja | "メイ をグレード2まで強化しよう" |
| e | 202601010 | event_jig_00001_2_ja | event_jig_00001_2 | ja | "メイ をグレード3まで強化しよう" |

### MstMissionEventDependency シート

| ENABLE | id | release_key | group_id | mst_mission_event_id | unlock_order | 備考 |
|--------|----|-----------|---------|--------------------|--------------|-----|
| e | 151 | 202601010 | event_jig_00001_1 | event_jig_00001_1 | 1 | |
| e | 152 | 202601010 | event_jig_00001_1 | event_jig_00001_2 | 2 | |

### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|-----------|---------|--------------|-----------|-----------------|-----------|----|
| e | mission_reward_480 | 202601010 | jig_00001_event_reward_01 | Item | memory_chara_jig_00701 | 200 | 1 | jigいいジャン祭_ミッション |
| e | mission_reward_490 | 202601010 | jig_00001_event_reward_11 | FreeDiamond | | 50 | 1 | jigいいジャン祭_ミッション |

### 推測値レポート

#### MstMissionEvent.destination_scene
- **値**: UnitList
- **理由**: criterion_type(SpecificUnitGradeUpCount)から推測。手順書のcriterion_type一覧表に基づき設定
- **確認事項**: ユーザーが遷移する画面が適切か確認してください

#### MstMissionReward.resource_id
- **値**: memory_chara_jig_00701
- **理由**: 設計書にアイテムIDの記載がなく、キャラクターIDから推測
- **確認事項**: 正しいアイテムIDか、MstItemテーブルに存在するか確認してください

## 注意事項

### criterion_typeとdestination_sceneの対応について

**重要な原則**:
- criterion_typeとdestination_sceneは強い相関関係があります
- 83%のcriterion_typeは1つのdestination_sceneに固定されています
- 手順書の「criterion_type設定一覧」表の「destination_scene候補」列を必ず参照してください

**イベントミッションでの推奨設定**:
- イベント関連のミッションは基本的に`Event`を設定
- ガシャ引き回数ミッションは`Gacha`
- ユニット育成系ミッションは`UnitList`

### 依存関係の設定について

**重要**: 依存関係は、**順序解放が必要なミッショングループのみ**に設定します。全てのミッションに設定する必要はありません。

**依存関係が必要なミッションの例**:
- グレードアップミッション(グレード2→3→4→5)
- レベルアップミッション(Lv.20→30→40→50→60→70→80)
- 敵撃破数ミッション(10体→20体→30体→...→1000体)

**依存関係が不要なミッションの例**:
- 単発の特定クエストクリアミッション
- 独立したガシャミッション
- アカウント連携、SNSフォロー等の1回限りのミッション

### group_keyの設定について

**重要**: イベントミッションでは`group_key`は常に空欄です。依存関係はMstMissionEventDependencyで管理します。

### mst_mission_reward_group_idの採番について

**報酬段階のゼロパディングルール**:
- **通常**: 2桁ゼロパディング(01, 02, 03...28)
- **例外(osh: 推しの子のみ)**: ゼロパディングなし(1, 2, 3...53)

**採番例**:
```
jig_00001_event_reward_01   (地獄楽 イベント1 報酬段階1)
osh_00001_event_reward_1    (推しの子 イベント1 報酬段階1)
```

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstMissionEvent.mst_mission_reward_group_id` = `MstMissionReward.group_id`
- `MstMissionEventDependency.mst_mission_event_id` がMstMissionEvent.idに存在する
- `MstMissionEventI18n.mst_mission_event_id` がMstMissionEvent.idに存在する
- `MstMissionEventDailyBonus.mst_mission_event_daily_bonus_schedule_id` がMstMissionEventDailyBonusSchedule.idに存在する
- `MstMissionLimitedTermI18n.mst_mission_limited_term_id` がMstMissionLimitedTerm.idに存在する

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: criterion_valueが必要なcriterion_typeと不要なcriterion_typeの見分け方は?

**対処法**:
手順書の「criterion_type設定一覧」表の「criterion_value」列を参照してください。
- 「空欄(`__NULL__`)」と記載されている場合: criterion_valueは不要
- 「アイテムID」「ガシャID」等と記載されている場合: criterion_valueが必要

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid criterion_type: specificquestclear (expected: SpecificQuestClear)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `specificquestclear` → `SpecificQuestClear`

### Q3: 依存関係のgroup_idの設定が分からない

**原因**: group_idは依存関係グループの最初のミッションIDと同じ値を使用する

**対処法**:
1. 依存関係を持つミッションのグループを特定する
2. そのグループの**最初のミッションID**をgroup_idとして使用する
3. 同じgroup_id内のミッションに、unlock_order=1から順番に番号を振る

**例**:
```
グレードアップミッション(event_jig_00001_1→2→3→4)の場合:
group_id: event_jig_00001_1 (最初のミッションID)
```

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
