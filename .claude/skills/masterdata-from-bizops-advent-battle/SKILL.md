---
name: masterdata-from-bizops-advent-battle
description: 降臨バトル(レイドイベント)の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 7個(MstAdventBattle, MstAdventBattleI18n, MstAdventBattleRank, MstAdventBattleClearReward, MstAdventBattleRewardGroup, MstAdventBattleReward, MstEventBonusUnit)。スコアチャレンジ、ボス討伐等の降臨バトルマスタデータを精度高く作成します。
---

# 降臨バトル マスタデータ作成スキル

## 概要

降臨バトル(レイドイベント)の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の7テーブルを自動生成:

**降臨バトル基本情報**:
- **MstAdventBattle** - 降臨バトルの基本情報(ID、開催期間、スコア設定等)
- **MstAdventBattleI18n** - 降臨バトル名・ボス説明(多言語対応)

**ランクシステム**:
- **MstAdventBattleRank** - ランク評価設定(Bronze/Silver/Gold/Master 各4レベル)

**報酬設定**:
- **MstAdventBattleClearReward** - クリア時ランダム報酬
- **MstAdventBattleRewardGroup** - 報酬カテゴリ定義(MaxScore/RaidTotalScore/Rank/Ranking等)
- **MstAdventBattleReward** - 報酬詳細

**特効設定**:
- **MstEventBonusUnit** - 特効キャラ設定(ボーナス率)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **mst_event_id** | イベントID | `event_jig_00001` |
| **advent_battle_id** | 降臨バトルID | `quest_raid_jig1_00001` |
| **advent_battle_name** | 降臨バトル名 | `まるで 悪夢を見ているようだ` |
| **advent_battle_type** | バトルタイプ | `ScoreChallenge`(ScoreChallenge/BossDefeat等) |
| **mst_in_game_id** | インゲームID | `raid_jig1_00001` |
| **start_at** | 開催開始日時 | `2026-01-23 15:00:00` |
| **end_at** | 開催終了日時 | `2026-01-29 14:59:59` |
| **display_mst_unit_id1** | ボス敵キャラID | `enemy_jig_00601` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
降臨バトルの運営仕様書からマスタデータを作成してください。

添付ファイル:
- 降臨バトル設計書_地獄楽_イベント1.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_event_id: event_jig_00001
- advent_battle_id: quest_raid_jig1_00001
- advent_battle_name: まるで 悪夢を見ているようだ
- advent_battle_type: ScoreChallenge
- mst_in_game_id: raid_jig1_00001
- start_at: 2026-01-23 15:00:00
- end_at: 2026-01-29 14:59:59
- display_mst_unit_id1: enemy_jig_00601
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- 降臨バトルの基本情報(バトルID、イベントID、開催期間)
- バトルタイプ(ScoreChallenge、BossDefeat等)
- スコア設定(初期BP、スコア加算係数)
- 挑戦回数設定(通常挑戦、広告挑戦)
- ランク評価基準(Bronze～Master、各レベルのスコア閾値)
- 報酬設定(最高スコア報酬、累計スコア報酬、ランク報酬、ランキング報酬、初回クリア報酬)
- 特効キャラとボーナス率
- ボス敵キャラID
- インゲーム設定(MstInGame.id)

**任意情報**:
- アセットキー(記載がない場合は推測)
- ランクスコア閾値(記載がない場合は標準値を使用)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstAdventBattle** - 降臨バトルの基本設定
2. **MstAdventBattleI18n** - 降臨バトル名・ボス説明(多言語対応)
3. **MstAdventBattleRank** - ランク評価設定(全16レコード)
4. **MstAdventBattleClearReward** - クリア時ランダム報酬
5. **MstAdventBattleRewardGroup** - 報酬カテゴリ定義
6. **MstAdventBattleReward** - 報酬詳細
7. **MstEventBonusUnit** - 特効キャラ設定

#### ID採番ルール

降臨バトルのIDは以下の形式で採番します:

```
MstAdventBattle.id: quest_raid_{series_id}{連番1桁}_{連番5桁}
event_bonus_group_id: raid_{series_id}{連番1桁}_{連番5桁}
MstAdventBattleI18n.id: {mst_advent_battle_id}_{language}
MstAdventBattleRank.id: {mst_advent_battle_id}_rank_{連番2桁}
MstAdventBattleRewardGroup.id: {mst_advent_battle_id変換}_reward_group_{連番5桁}_{連番2桁}
```

**例**:
```
quest_raid_jig1_00001 (地獄楽イベント1の降臨バトル1)
raid_jig1_00001 (特効グループID)
quest_raid_jig1_00001_ja (日本語I18n)
quest_raid_jig1_00001_rank_01 (Bronze レベル1)
quest_raid_jig1_reward_group_00001_01 (報酬グループ1)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(advent_battle_type、score_addition_type、rank_type、reward_category等)
- [ ] 開催期間が妥当か(start_at < end_at)
- [ ] ランク設定が完全か(全16段階)
- [ ] クリア報酬のpercentage合計が100か

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstAdventBattle.asset_key`: アセットキー(推測値)
- `MstAdventBattleRank.required_lower_score`: ランクスコア閾値(標準値使用)
- `MstAdventBattleClearReward.percentage`: クリア報酬確率(均等分配)
- `MstAdventBattleRewardGroup.condition_value`: 報酬獲得スコア閾値(推測値)
- `MstEventBonusUnit.bonus_percentage`: 特効ボーナス率(推測値)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の7シートを作成:
  1. MstAdventBattle
  2. MstAdventBattleI18n
  3. MstAdventBattleRank(全16レコード)
  4. MstAdventBattleClearReward
  5. MstAdventBattleRewardGroup
  6. MstAdventBattleReward
  7. MstEventBonusUnit

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstAdventBattle.asset_key
- 値: jig_00001(推測値)
- 理由: 設計書にアセットキーの記載がなかったため、シリーズIDから推測
- 確認事項: 実際のアセットキーを確認し、正しい値に差し替えてください

### MstAdventBattleRank.required_lower_score
- 値: 標準値を使用(Bronze 1: 1000、Master 4: 2000000)
- 理由: 設計書にスコア閾値の記載がなかったため、標準ガイドラインを適用
- 確認事項: イベント難易度に応じてスコア閾値を調整してください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstAdventBattle シート

| ENABLE | id | mst_event_id | mst_in_game_id | asset_key | advent_battle_type | initial_battle_point | score_addition_type | score_additional_coef | score_addition_target_mst_enemy_stage_parameter_id | mst_stage_rule_group_id | event_bonus_group_id | challengeable_count | ad_challengeable_count | display_mst_unit_id1 | display_mst_unit_id2 | display_mst_unit_id3 | exp | coin | start_at | end_at | release_key |
|--------|----|--------------|-----------------|-----------|--------------------|---------------------|---------------------|-----------------------|----------------------------------------------------|-------------------------|----------------------|---------------------|------------------------|---------------------|---------------------|---------------------|-----|------|-----------|---------| ------------|
| e | quest_raid_jig1_00001 | event_jig_00001 | raid_jig1_00001 | jig_00001 | ScoreChallenge | 500 | AllEnemiesAndOutPost | 0.07 | test | | raid_jig1_00001 | 3 | 2 | enemy_jig_00601 | | | 100 | 300 | 2026-01-23 15:00:00 | 2026-01-29 14:59:59 | 202601010 |

### MstAdventBattleI18n シート

| ENABLE | release_key | id | mst_advent_battle_id | language | name | boss_description |
|--------|-------------|----|----------------------|----------|------|-----------------|
| e | 202601010 | quest_raid_jig1_00001_ja | quest_raid_jig1_00001 | ja | まるで 悪夢を見ているようだ | ボスを倒して高スコア獲得!! |

### MstAdventBattleRank シート

| ENABLE | id | mst_advent_battle_id | rank_type | rank_level | required_lower_score | asset_key | release_key |
|--------|----|----------------------|-----------|------------|----------------------|-----------|-------------|
| e | quest_raid_jig1_00001_rank_01 | quest_raid_jig1_00001 | Bronze | 1 | 1000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_02 | quest_raid_jig1_00001 | Bronze | 2 | 5000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_03 | quest_raid_jig1_00001 | Bronze | 3 | 10000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_04 | quest_raid_jig1_00001 | Bronze | 4 | 15000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_05 | quest_raid_jig1_00001 | Silver | 1 | 30000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_13 | quest_raid_jig1_00001 | Master | 1 | 500000 | | 202601010 |
| e | quest_raid_jig1_00001_rank_16 | quest_raid_jig1_00001 | Master | 4 | 2000000 | | 202601010 |

*(以下、Silver、Gold、Masterの全16レコード)*

### MstEventBonusUnit シート

| ENABLE | id | mst_unit_id | bonus_percentage | event_bonus_group_id | is_pick_up | release_key |
|--------|----| ------------|-----------------|----------------------|-----------|-------------|
| e | 1 | chara_jig_00401 | 20 | raid_jig1_00001 | | 202601010 |
| e | 2 | chara_jig_00001 | 15 | raid_jig1_00001 | | 202601010 |

### 推測値レポート

#### MstAdventBattle.asset_key
- **値**: jig_00001(推測値)
- **理由**: 設計書にアセットキーの記載がなかったため、シリーズIDから推測
- **確認事項**: 実際のアセットキーを確認し、正しい値に差し替えてください

## 注意事項

### ランク設定について

MstAdventBattleRankは、以下の16段階で作成してください:

**標準的なランク構成**:
- Bronze(4レベル) + Silver(4レベル) + Gold(4レベル) + Master(4レベル) = 計16レベル

**スコア閾値のガイドライン**:
- **Bronze**: レベル1: 1,000、レベル4: 15,000
- **Silver**: レベル1: 30,000、レベル4: 100,000
- **Gold**: レベル1: 150,000、レベル4: 300,000
- **Master**: レベル1: 500,000、レベル4: 2,000,000以上

### 報酬設定について

MstAdventBattleRewardGroupとMstAdventBattleRewardは、以下の構造で作成してください:

**報酬カテゴリ**:
- **MaxScore**: 最高スコア報酬(10～15段階)
- **RaidTotalScore**: 累計スコア報酬(15～20段階)
- **Rank**: ランク到達報酬(16段階、全ランク)
- **Ranking**: ランキング報酬(上位順位 + Participation参加賞)

**複数報酬の設定**:
1つの報酬グループに複数の報酬を設定する場合、同じmst_advent_battle_reward_group_idで複数レコードを作成します。

### 特効設定について

MstEventBonusUnitは、以下の方針で作成してください:

**ボーナス率のガイドライン**:
- **20%**: 最高ボーナス(新規実装URキャラ等)
- **15%**: 高ボーナス(イベント主役キャラ)
- **10%**: 中ボーナス(イベント関連キャラ)
- **5%**: 低ボーナス(シリーズキャラ)

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstAdventBattle.mst_event_id` → `MstEvent.id`
- `MstAdventBattle.mst_in_game_id` → `MstInGame.id`
- `MstAdventBattle.event_bonus_group_id` → `MstEventBonusUnit.event_bonus_group_id`
- `MstAdventBattleI18n.mst_advent_battle_id` → `MstAdventBattle.id`
- `MstAdventBattleRank.mst_advent_battle_id` → `MstAdventBattle.id`
- `MstAdventBattleClearReward.mst_advent_battle_id` → `MstAdventBattle.id`
- `MstAdventBattleRewardGroup.mst_advent_battle_id` → `MstAdventBattle.id`
- `MstAdventBattleReward.mst_advent_battle_reward_group_id` → `MstAdventBattleRewardGroup.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: ランクスコア閾値の設定方法がわからない

**対処法**:
1. 標準ガイドラインを参考に設定(Bronze 1: 1,000 ～ Master 4: 2,000,000)
2. イベント難易度に応じて調整
3. 推測値レポートで報告

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid advent_battle_type: scorechallenge (expected: ScoreChallenge)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `scorechallenge` → `ScoreChallenge`, `bronze` → `Bronze`

### Q3: 報酬グループのID形式がわからない

**原因**: `quest_raid_jig1_00001` → `quest_raid_jig1` への変換ルールを理解していない

**対処法**:
```
mst_advent_battle_id: quest_raid_jig1_00001
↓
reward_group_id: quest_raid_jig1_reward_group_00001_01
（末尾の_00001を削除）
```

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
