---
name: masterdata-from-bizops-quest-stage
description: クエスト・ステージの運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 10個(MstQuest, MstQuestI18n, MstQuestBonusUnit, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstStageEndCondition, MstQuestEventBonusSchedule)。イベントクエスト、デイリークエスト、チャレンジクエスト等のマスタデータを精度高く作成します。
---

# クエスト・ステージ マスタデータ作成スキル

## 概要

クエスト・ステージの運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の10テーブルを自動生成:

**クエスト基本情報**:
- **MstQuest** - クエストの基本情報(タイプ、イベントID、開催期間等)
- **MstQuestI18n** - クエスト名・説明文(多言語対応)
- **MstQuestBonusUnit** - クエスト特効キャラ設定

**ステージ情報**:
- **MstStage** - ステージの基本情報(推奨レベル、スタミナ、報酬等)
- **MstStageI18n** - ステージ名(多言語対応)
- **MstStageEventReward** - ステージ報酬(初回クリア・ランダム報酬)
- **MstStageEventSetting** - ステージイベント設定(リセット、開催期間、背景等)
- **MstStageClearTimeReward** - タイムアタック報酬

**条件付き**:
- **MstStageEndCondition** - ステージ終了条件(降臨バトルやPVP等の特殊クエストの場合のみ)
- **MstQuestEventBonusSchedule** - 特効スケジュール(降臨バトル等の場合のみ)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **mst_event_id** | イベントID | `event_jig_00001` |
| **quest_ids** | クエストID(カンマ区切り) | `quest_event_jig1_charaget01,quest_event_jig1_1day,quest_event_jig1_challenge01` |
| **quest_names** | クエスト名(カンマ区切り) | `必ず生きて帰る,本能が告げている 危険だと,死罪人と首切り役人` |
| **start_date** | 開催開始日時 | `2026-01-16 15:00:00` |
| **end_date** | 開催終了日時 | `2026-02-16 10:59:59` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
クエスト・ステージの運営仕様書からマスタデータを作成してください。

添付ファイル:
- クエスト設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_event_id: event_jig_00001
- quest_ids: quest_event_jig1_charaget01,quest_event_jig1_1day,quest_event_jig1_challenge01
- quest_names: 必ず生きて帰る,本能が告げている 危険だと,死罪人と首切り役人
- start_date: 2026-01-16 15:00:00
- end_date: 2026-02-16 10:59:59
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- クエストの基本情報(クエスト名、タイプ、難易度)
- イベントID(紐付け先のイベント)
- 開催期間(開始日時・終了日時)
- ステージ構成(ステージ数、各ステージの設定)
- 推奨レベル、スタミナコスト、経験値、コイン
- 報酬設定(初回クリア報酬、ランダム報酬、タイムアタック報酬)

**任意情報**:
- 特効キャラ設定(ボーナス率)
  - 運営仕様書の「ボーナスキャラ設定」「コイン獲得ボーナス」セクションから抽出
  - セクションが存在する場合のみMstQuestBonusUnitを作成
  - 各キャラのユニットIDとボーナス率(coin_bonus_rate)を設定
  - 例: chara_jig_00401(20%ボーナス) → coin_bonus_rate=0.2
- 背景・BGM設定(記載がない場合は推測)
- リセット設定(記載がない場合は推測)
- クリア条件(降臨バトルやPVP等の特殊クエストで明記されている場合のみMstStageEndConditionを作成)
- ボーナス期間(降臨バトル等で明記されている場合のみMstQuestEventBonusScheduleを作成)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstQuest** - クエストの基本設定
2. **MstQuestI18n** - クエスト名・説明文(多言語対応)
3. **MstQuestBonusUnit** - クエスト特効キャラ設定(運営仕様書に「ボーナスキャラ設定」「コイン獲得ボーナス」セクションがある場合に作成)
4. **MstStage** - ステージの基本設定
5. **MstStageI18n** - ステージ名(多言語対応)
6. **MstStageEventReward** - ステージ報酬(初回クリア・ランダム報酬)
7. **MstStageEventSetting** - ステージイベント設定(リセット、開催期間、背景等)
8. **MstStageClearTimeReward** - タイムアタック報酬(チャレンジや高難度クエストの場合のみ)
9. **MstStageEndCondition** - ステージ終了条件(降臨バトルやPVP等の特殊クエストで「クリア条件」が明記されている場合のみ)
10. **MstQuestEventBonusSchedule** - 特効スケジュール(降臨バトル等で「ボーナス期間」が明記されている場合のみ)

#### データ依存関係の自動管理

**重要**: 親テーブルを作成した際は、依存する子テーブルも自動的に生成してください。

**依存関係定義** (`config/table_dependencies.json` 参照):
```json
{
  "MstQuest": ["MstQuestI18n"],
  "MstStage": ["MstStageI18n"]
}
```

**自動生成ロジック**:
1. **MstQuest**を作成 → **MstQuestI18n**を自動生成
   - id: `{parent_id}_{language}` (例: `quest_event_jig1_charaget01_ja`)
   - mst_quest_id: `{parent_id}`
   - name、category_name、flavor_textを運営仕様書から抽出

2. **MstStage**を作成 → **MstStageI18n**を自動生成
   - id: `{parent_id}_{language}` (例: `event_jig1_charaget01_00001_ja`)
   - mst_stage_id: `{parent_id}`
   - nameを運営仕様書から抽出

**実装の流れ**:
```
1. MstQuest作成
   ↓ (自動)
2. MstQuestI18n生成

3. MstStage作成
   ↓ (自動)
4. MstStageI18n生成

5. MstStageEventReward作成
6. MstStageEventSetting作成
```

この自動生成により、親テーブル未生成による子テーブル欠落を防止できます。

#### ID採番ルール

**重要**: 新規IDを採番する前に、必ず既存データの最大IDを確認してください。

**既存データからの最大ID取得**:
```
1. マスタデータ/過去データ/{release_key}/{TableName}.csv を確認
2. ID列から数値部分を抽出
3. 最大値を取得
4. 最大値 + 1 から採番開始
```

クエスト・ステージのIDは以下の形式で採番します:

```
MstQuest.id: quest_event_{series_id}{連番}_{クエストタイプ略称}
MstQuestI18n.id: {mst_quest_id}_{language}
MstStage.id: event_{series_id}{連番1桁}_{クエストタイプ略称}{連番2桁}_{連番5桁}
MstStageI18n.id: {mst_stage_id}_{language}
MstStageEventReward.id: {連番}
```

**ステージID命名規則**:
- クエストタイプ略称: `charaget`, `1day`, `challenge`
- ステージ番号: ゼロ埋め5桁
- 同一クエスト内で連番

**MstStageEventReward ID採番**:
- 既存データの最大ID + 1から開始
- リリースキーごとにID範囲を管理
- 例: 202601010のデータは569～

**アセットキーの命名規則**:
- イベントアセットキー: `{series_id}_{連番5桁}` (例: `jig_00001`)
- 背景アセットキー(クエストタイプ別):
  - キャラ入手(`charaget01`): `jig_00003`
  - デイリー(`1day`): `jig_00002`
  - チャレンジ(`challenge01`): `jig_00001`

**例**:
```
quest_event_jig1_charaget01 (地獄楽イベント1 キャラ入手クエスト1)
quest_event_jig1_charaget01_ja (日本語I18n)
event_jig1_charaget01_00001 (キャラ入手クエスト1のステージ1)
event_jig1_charaget01_00001_ja (ステージ1の日本語I18n)
569 (MstStageEventReward - 既存最大568の次)
```

詳細は [references/id_naming_rules.md](references/id_naming_rules.md) を参照してください。

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(quest_type、difficulty、auto_lap_type、reward_category、resource_type、reset_type等)
- [ ] 開催期間が妥当か(start_date < end_date)
- [ ] ステージ解放順序が正しいか(prev_mst_stage_idが適切に設定されている)
- [ ] 報酬設定が妥当か(FirstClear報酬のpercentageが100、Random報酬の確率設定が適切)

### Step 4: パラメータ推測ロジック

設計書に記載がないパラメータは、過去データから学習したパターンに基づいて推測します。

#### 過去データからパターン学習

```typescript
/**
 * 過去データから類似パターンを学習
 * マスタデータ/過去データ/{release_key}/ 配下のCSVから統計的に推測値を算出
 */
interface ParameterPattern {
  costStamina: {
    baseValue: number      // 基準値（例: 5）
    increaseRate: number   // ステージごとの増加率（例: 0.5）
    max: number            // 最大値（例: 20）
  }
  coin: {
    baseValue: number      // 基準値（例: 100）
    increaseRate: number   // ステージごとの増加率（例: 50）
    max: number            // 最大値（例: 1000）
  }
  recommendedLevel: {
    baseValue: number      // 基準値（例: 10）
    increaseRate: number   // ステージごとの増加率（例: 5）
    max: number            // 最大値（例: 100）
  }
  exp: {
    baseValue: number      // 基準値（例: 50）
    increaseRate: number   // ステージごとの増加率（例: 25）
    max: number            // 最大値（例: 500）
  }
}

function learnParameterPatterns(pastData: any[]): ParameterPattern {
  // 過去データから基準値・増加率・最大値を統計的に算出
  return {
    costStamina: { baseValue: 5, increaseRate: 0.5, max: 20 },
    coin: { baseValue: 100, increaseRate: 50, max: 1000 },
    recommendedLevel: { baseValue: 10, increaseRate: 5, max: 100 },
    exp: { baseValue: 50, increaseRate: 25, max: 500 }
  }
}

/**
 * 学習したパターンから推測値を生成
 */
function estimateParameter(
  parameterName: string,
  stageNumber: number,
  pattern: ParameterPattern
): number {
  const config = pattern[parameterName]
  const estimated = config.baseValue + (stageNumber - 1) * config.increaseRate
  return Math.min(estimated, config.max)
}
```

#### アセットキー推測ルール

詳細は [references/asset_key_rules.md](references/asset_key_rules.md) を参照してください。

**基本ルール**:
1. **イベントIDからプレフィックス抽出**: `event_jig_00001` → `jig_`
2. **クエストタイプ別アセットキー**:
   - `charaget01` → `jig_00003`
   - `1day` → `jig_00001`
   - `challenge` → `jig_00002`

### Step 5: 推測値レポート(詳細化)

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstStage.cost_stamina`: スタミナコスト(推測値)
- `MstStage.coin`: コイン獲得量(推測値)
- `MstStage.exp`: 経験値(推測値)
- `MstStage.recommended_level`: 推奨レベル(推測値)
- `MstStage.mst_artwork_fragment_drop_group_id`: 原画欠片ドロップグループID(推測値)
- `MstStageEventSetting.background_asset_key`: 背景アセットキー(推測値)
- `MstQuestBonusUnit.coin_bonus_rate`: 特効ボーナス率(推測値)
- `MstStageEventReward.percentage`: ランダム報酬の獲得確率(推測値)

#### 推測値レポート形式（拡張版）

```typescript
interface InferenceReport {
  field: string                 // フィールド名
  value: any                    // 推測値
  confidence: "High" | "Medium" | "Low"  // 信頼度スコア
  reasoning: string             // 推測根拠
  source: "past_data_pattern" | "specification" | "default_value" | "manual_input_required"  // データソース
}
```

**レポート例**:
```markdown
## 推測値レポート

### MstStage.cost_stamina
- **値**: 5 → 8 → 10 → 12 → 15
- **信頼度**: High
- **推測根拠**: 過去データ(release_key=202512010)のパターン学習結果に基づき、ステージ1から5まで段階的に増加
- **データソース**: past_data_pattern
- **確認事項**: イベント難易度に応じて調整が必要か確認してください

### MstStageEventSetting.background_asset_key
- **値**: jig_00003
- **信頼度**: Medium
- **推測根拠**: アセットキー生成ルールに基づき、クエストタイプ「charaget01」から推測
- **データソース**: asset_key_rules
- **確認事項**: 実際の背景アセットキーを確認し、必要に応じて差し替えてください
```

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の10シートを作成:
  1. MstQuest
  2. MstQuestI18n
  3. MstQuestBonusUnit(特効キャラがある場合のみ)
  4. MstStage
  5. MstStageI18n
  6. MstStageEventReward
  7. MstStageEventSetting
  8. MstStageClearTimeReward(タイムアタック報酬がある場合のみ)
  9. MstStageEndCondition(特殊クエストの場合のみ)
  10. MstQuestEventBonusSchedule(特殊クエストの場合のみ)

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstStage.mst_artwork_fragment_drop_group_id
- 値: event_jig_a_0001
- 理由: 設計書に原画欠片ドロップグループIDの記載がなかったため、イベントIDから推測して設定
- 確認事項: 正しい原画欠片ドロップグループIDを確認し、必要に応じて差し替えてください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstQuest シート

| ENABLE | id | quest_type | mst_event_id | sort_order | asset_key | start_date | end_date | quest_group | difficulty | release_key |
|--------|----|-----------|--------------|-----------|---------|-----------|---------|-----------|-----------|---------||
| e | quest_event_jig1_charaget01 | event | event_jig_00001 | 1 | jig1_charaget01 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | event_jig1_charaget_mei | Normal | 202601010 |

### MstQuestI18n シート

| ENABLE | release_key | id | mst_quest_id | language | name | category_name | flavor_text |
|--------|-------------|----|--------------|---------|----|--------------|------------|
| e | 202601010 | quest_event_jig1_charaget01_ja | quest_event_jig1_charaget01 | ja | 必ず生きて帰る | ストーリー | |

### MstStage シート

| ENABLE | id | mst_quest_id | mst_in_game_id | stage_number | recommended_level | cost_stamina | exp | coin | prev_mst_stage_id | mst_stage_tips_group_id | auto_lap_type | max_auto_lap_count | sort_order | asset_key | mst_stage_limit_status_id | release_key | mst_artwork_fragment_drop_group_id | start_at | end_at |
|--------|----|--------------|--------------|-----------|--------------|-----------|----|----|-----------------|-----------------------|--------------|------------------|-----------|---------|-----------------------|-----------|---------------------------------|---------|---------||
| e | event_jig1_charaget01_00001 | quest_event_jig1_charaget01 | event_jig1_charaget01_00001 | 1 | 10 | 5 | 50 | 100 | | 1 | AfterClear | 5 | 1 | event_jig1_00001 | | 202601010 | event_jig_a_0001 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 |

### MstStageEventReward シート

| ENABLE | id | mst_stage_id | reward_category | resource_type | resource_id | resource_amount | percentage | sort_order | release_key |
|--------|----|--------------|--------------|--------------|-----------|--------------|-----------|-----------|---------||
| e | 569 | event_jig1_charaget01_00001 | FirstClear | Unit | chara_jig_00701 | 1 | 100 | 1 | 202601010 |
| e | 570 | event_jig1_charaget01_00001 | FirstClear | FreeDiamond | prism_glo_00001 | 40 | 100 | 2 | 202601010 |

### 推測値レポート

#### MstStage.mst_artwork_fragment_drop_group_id
- **値**: event_jig_a_0001
- **理由**: 設計書に原画欠片ドロップグループIDの記載がなかったため、イベントIDから推測して設定
- **確認事項**: 正しい原画欠片ドロップグループIDを確認し、必要に応じて差し替えてください

#### MstStageEventSetting.background_asset_key
- **値**: jig_00003
- **理由**: 設計書に背景アセットキーの記載がなかったため、シリーズIDから推測して設定
- **確認事項**: 正しい背景アセットキーを確認し、必要に応じて差し替えてください

## 注意事項

### 特効キャラ設定について(MstQuestBonusUnit)

**作成条件**:
- 運営仕様書に「ボーナスキャラ設定」「コイン獲得ボーナス」セクションが存在する場合のみ作成
- セクションが存在しない場合はMstQuestBonusUnitシートを作成しない

**設定項目**:
- **mst_unit_id**: 特効キャラのユニットID(例: chara_jig_00401)
- **coin_bonus_rate**: コインボーナス率(例: 0.15=15%UP、0.2=20%UP)
- **start_at / end_at**: 特効期間(通常はクエスト開催期間と同じ)

**ボーナス率のガイドライン**:
- **20%**: 最高ボーナス(新規実装URキャラ等)
- **15%**: 高ボーナス(イベント主役キャラ)
- **10%**: 中ボーナス(イベント関連キャラ)
- **5%**: 低ボーナス(シリーズキャラ)

### クエストタイプについて

MstQuestのquest_typeは以下のいずれかを設定してください:

| quest_type | 説明 | 使用例 |
|----------|------|--------|
| **event** | イベントクエスト | ストーリークエスト、デイリークエスト、チャレンジクエスト等 |
| **raid** | レイドクエスト | 降臨バトル等(別テーブルMstAdventBattleと連携) |
| **story** | ストーリークエスト | メインストーリー |
| **enhance** | 強化クエスト | 素材集めクエスト |

**重要**: 大文字小文字を正確に一致させてください。

### ステージ周回設定について

MstStageのauto_lap_typeは以下のいずれかを設定してください:

- **AfterClear**: クリア後周回可能(ストーリークエスト等)
- **__NULL__**: 周回不可(デイリークエスト、チャレンジクエスト等)

### リセット設定について

MstStageEventSettingのreset_typeは以下のいずれかを設定してください:

- **Daily**: 毎日リセット(デイリークエスト)
- **__NULL__**: リセットなし(ストーリークエスト、チャレンジクエスト等)

**注意**: reset_type=Dailyの場合、clearable_countを必ず設定してください(通常は1)。

### 報酬設定について

MstStageEventRewardのreward_categoryは以下のいずれかを設定してください:

- **FirstClear**: 初回クリア報酬(percentageは必ず100)
- **Random**: ランダム報酬(percentageは確率を設定)

**重要**: FirstClear報酬のpercentageは必ず100に設定してください。Random報酬のpercentageの合計が100を超えても良い(複数報酬同時獲得可能)。

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstQuest.mst_event_id` → `MstEvent.id`
- `MstQuestI18n.mst_quest_id` → `MstQuest.id`
- `MstQuestBonusUnit.mst_quest_id` → `MstQuest.id`
- `MstQuestBonusUnit.mst_unit_id` → `MstUnit.id`
- `MstStage.mst_quest_id` → `MstQuest.id`
- `MstStage.prev_mst_stage_id` → `MstStage.id`(または空欄)
- `MstStageI18n.mst_stage_id` → `MstStage.id`
- `MstStageEventReward.mst_stage_id` → `MstStage.id`
- `MstStageEventSetting.mst_stage_id` → `MstStage.id`
- `MstStageClearTimeReward.mst_stage_id` → `MstStage.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: ステージ解放順序が正しく設定されない

**原因**: prev_mst_stage_idが正しく設定されていない

**対処法**:
1. 最初のステージはprev_mst_stage_idを空欄に設定
2. 2番目以降のステージは、前のステージのIDを設定
3. ステージが順番に解放されるように設定

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid quest_type: Event (expected: event)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `Event` → `event`, `NORMAL` → `Normal`

### Q3: リセット設定が正しく動作しない

**原因**: reset_type=Dailyの場合にclearable_countが設定されていない

**対処法**:
- reset_type=Daily: clearable_countを必ず設定(通常は1)
- reset_type=__NULL__: clearable_countは空欄

### Q4: MstStageEndConditionの作成判断がわからない

**原因**: 通常のイベントクエストでは不要なテーブルを作成してしまう

**対処法**:
- **作成対象**: 降臨バトル、PVP、特殊なクリア条件があるクエストのみ
- **作成しない**: ストーリークエスト、デイリークエスト、チャレンジクエスト等の通常クエスト
- **判断基準**: 運営仕様書に「クリア条件」「ステージ終了条件」が明記されている場合のみ作成

### Q5: MstQuestEventBonusScheduleの作成判断がわからない

**原因**: 通常のイベントクエストでは不要なテーブルを作成してしまう

**対処法**:
- **作成対象**: 降臨バトル等で特効期間が複数段階ある場合のみ
- **作成しない**: 通常のクエスト(特効期間がクエスト開催期間と同じ場合)
- **判断基準**: 運営仕様書に「ボーナス期間」「特効スケジュール」が明記されている場合のみ作成
- **設定項目**: mst_quest_id、start_at、end_at、event_bonus_group_id

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
