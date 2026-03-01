# インゲームマスタデータ設計書 - dungeon_spy_boss_00001

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ブロック種別: boss（ボスブロック）
- シリーズ: SPY×FAMILY（spy）

---

## インゲームID命名案

- **インゲームID**: `dungeon_spy_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の規則に従いシリーズ略称 `spy` を使用

同一IDを共有するテーブル:

| テーブル | カラム | 値 |
|---------|--------|-----|
| MstInGame | id | `dungeon_spy_boss_00001` |
| MstAutoPlayerSequence | sequence_set_id | `dungeon_spy_boss_00001` |
| MstPage | id | `dungeon_spy_boss_00001` |
| MstEnemyOutpost | id | `dungeon_spy_boss_00001` |

---

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 1件 | ボスのみ（ロイド）|
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon_boss固定値）|
| MstPage | 1件 | |
| MstKomaLine | 1件 | dungeon_boss固定1行 |
| MstAutoPlayerSequence | 3件 | ボス1体 + 護衛雑魚ウェーブ2波 |
| MstInGame | 1件 | ボスあり |

---

## MstEnemyStageParameter 敵パラメータ設計

### ボスキャラ選定根拠

`<黄昏> ロイド`（chara_spy_00101）をボスとして起用。
- ロイドはSPY×FAMILYの主人公であり、「強敵オーラ」付きの dungeon ボスとして設定
- MstEnemyCharacter.id = `chara_spy_00101`（name: `<黄昏> ロイド`）
- MstEnemyStageParameter.id には `c_` プレフィックスを使用（プレイヤーキャラが敵として登場）

### パラメータ設計

| 項目 | 値 | 根拠・備考 |
|------|----|---------|
| id | `c_spy_00101_spy_dungeon_boss_Boss_Colorless` | c_{キャラ}_{コンテキスト}_{unit_kind}_{color} 形式 |
| mst_enemy_character_id | `chara_spy_00101` | ロイドのキャラID |
| character_unit_kind | `Boss` | ボスとして登場 |
| role_type | `Attack` | 既存ロイドボスと同様 |
| color | `Colorless` | カラーレス（汎用ボス） |
| sort_order | `1` | |
| hp | `50000` | ユーザー指定（最終値 = hp × coef=1）|
| attack_power | `15000` | ユーザー指定（最終値 = attack × coef=1）|
| move_speed | `55` | 「少し速い」= 速い寄り（目安: 速い=50-80） |
| well_distance | `0.35` | 普通〜広め。ボスが遠くから攻撃できる設定 |
| damage_knock_back_count | `3` | ボス向け（目安: ボスは2-5回）|
| attack_combo_cycle | `7` | ボスらしい演出（既存ロイドの最大値）|
| mst_unit_ability_id1 | （空） | SPY×FAMILY専用の敵アビリティIDなし（※下記注記参照） |
| drop_battle_point | `500` | ボスの目安（300-1000）|
| mstTransformationEnemyStageParameterId | （空） | 変身なし |
| transformationConditionType | `None` | |
| transformationConditionValue | （空） | |

> **⚠️ 必殺ワザ使用あり について**
>
> MstEnemyStageParameter では `mst_unit_ability_id1` が必殺ワザのアビリティIDに相当しますが、
> SPY×FAMILY（spy）専用の敵用アビリティは現在 MstAbility に存在しません。
> 既存のロイドボス（c_spy_00101_general_Boss_Colorless 等）もすべて mst_unit_ability_id1=空 です。
>
> **対応方針**: `attack_combo_cycle=7`（最大コンボ数）を設定することで、ロイドのボス攻撃アニメーション（必殺ワザを含む連続攻撃）を引き出します。
> 将来的に spy 専用の敵アビリティが追加される場合は、このカラムに設定します。

---

## MstInGame 主要パラメータ設計

| カラム | 値 | 備考 |
|--------|-----|------|
| id | `dungeon_spy_boss_00001` | |
| mst_auto_player_sequence_id | `dungeon_spy_boss_00001` | |
| mst_auto_player_sequence_set_id | `dungeon_spy_boss_00001` | |
| mst_page_id | `dungeon_spy_boss_00001` | |
| mst_enemy_outpost_id | `dungeon_spy_boss_00001` | |
| boss_mst_enemy_stage_parameter_id | `c_spy_00101_spy_dungeon_boss_Boss_Colorless` | ロイドのパラメータID |
| boss_count | `1` | ボス1体 |
| normal_enemy_hp_coef | `1` | 全体倍率1（新規コンテンツ。直接値で調整） |
| normal_enemy_attack_coef | `1` | 全体倍率1 |
| normal_enemy_speed_coef | `1` | 全体倍率1 |
| boss_enemy_hp_coef | `1` | 全体倍率1 |
| boss_enemy_attack_coef | `1` | 全体倍率1 |
| boss_enemy_speed_coef | `1` | 全体倍率1 |
| bgm_asset_key | `SSE_SBG_003_002` | 参照: dungeon_spy_normal と同様 |
| boss_bgm_asset_key | （空） | ボスBGMなし（通常BGMで統一）|
| loop_background_asset_key | `spy_00005` | 参照: dungeon_spy_normal と同様 |
| player_outpost_asset_key | （空） | |
| mst_defense_target_id | （空） | |
| release_key | `999999999` | 開発テスト用（後で正式リリースキーに変更）|

---

## MstEnemyOutpost 設計

| カラム | 値 | 備考 |
|--------|-----|------|
| id | `dungeon_spy_boss_00001` | |
| hp | `1000` | dungeon_boss 固定値 |
| is_damage_invalidation | `1` | ボス撃破まで敵ゲートはダメージ無効（dungeon_boss仕様）|
| outpost_asset_key | （空） | |
| artwork_asset_key | `spy_0001` | normalブロックと同じアセット |
| release_key | `999999999` | |

---

## MstPage 設計

| カラム | 値 |
|--------|-----|
| id | `dungeon_spy_boss_00001` |
| release_key | `999999999` |

---

## MstKomaLine 構成設計

dungeon_boss は **1行固定**。コマエフェクトなし（None）で構成。

| id | row | height | layout_asset_key | koma1 | koma1_width |
|----|-----|--------|-----------------|-------|-------------|
| `dungeon_spy_boss_00001_1` | 1 | 0.55 | 1 | spy_00005 | 1.0（全幅）|

- 参照: normalブロックの行3（1コマ全幅、layout_key=1）と同様
- エフェクト: `None`（コマ効果なし）

---

## MstAutoPlayerSequence ウェーブ構成設計

dungeon_boss の動作仕様: ボスが敵ゲート前に召喚され、ダメージを受けたら進軍開始。護衛の雑魚が時間差で出現。

**3行構成**（ボス1体 + 護衛雑魚2波）:

| 行 | id | condition_type | condition_value | action_value | summon_count | aura_type | summon_position | move_start_condition |
|-----|----|----|----|----|----|----|----|----|
| 1 | `dungeon_spy_boss_00001_1` | InitialSummon | 0 | `c_spy_00101_spy_dungeon_boss_Boss_Colorless` | 1 | `Boss` | 1.7 | Damage / 1 |
| 2 | `dungeon_spy_boss_00001_2` | ElapsedTime | 3000 | `e_spy_00001_spy_dungeon_Normal_Colorless` | 2 | `Default` | （空） | None |
| 3 | `dungeon_spy_boss_00001_3` | ElapsedTime | 6000 | `e_spy_00101_spy_dungeon_Normal_Colorless` | 1 | `Default` | （空） | None |

- **行1（ボス）**: InitialSummon で砦付近（1.7）に配置。ダメージを1受けたら進軍開始
- **行2（護衛A）**: 3秒後に enemy_spy_00001 × 2体が出現
- **行3（護衛B）**: 6秒後に enemy_spy_00101 × 1体が追加
- 全行: death_type=`Normal`、enemy_hp_coef=1、enemy_attack_coef=1、enemy_speed_coef=1

> **参照する雑魚パラメータ**:
> - `e_spy_00001_spy_dungeon_Normal_Colorless`（normalブロックで作成済み）
> - `e_spy_00101_spy_dungeon_Normal_Colorless`（normalブロックで作成済み）
> これらは dungeon_spy_normal_00001 で既に定義されたパラメータを流用します。

---

## 参照した既存データ

- 参照ステージID: `dungeon_spy_normal_00001`（normalブロック参考フォルダ）
  - BGMアセットキー: `SSE_SBG_003_002`
  - 背景アセットキー: `spy_00005`
  - コマアセットキー: `spy_00005`
  - 雑魚: `e_spy_00001_spy_dungeon_Normal_Colorless`、`e_spy_00101_spy_dungeon_Normal_Colorless`
- 参照キャラ: `c_spy_00101_*`（既存ロイドボス群、hp=10000-25000, attack=50-400, move_speed=30-31）

---

## 不確定事項・要確認事項

1. **is_damage_invalidation = 1 の確認（重要）**
   - dungeon_bossの仕様「ボス撃破まで敵ゲートはダメージ無効」に基づき `1` を設定しています
   - 実際のゲームロジックで管理されている場合は `（空）` に変更が必要です

2. **mst_unit_ability_id1 について**
   - spy専用敵アビリティが存在しないため空です
   - 将来的に専用アビリティが追加される場合は要更新

3. **release_key**
   - 暫定 `999999999`（開発テスト用）。正式なリリースキーが決まり次第更新してください

4. **boss_bgm_asset_key**
   - ボス専用BGMが用意される場合は要設定。現在は通常BGMで統一

5. **護衛雑魚はnormalブロック定義の流用**
   - `e_spy_00001_spy_dungeon_Normal_Colorless`、`e_spy_00101_spy_dungeon_Normal_Colorless` は
     dungeon_spy_normal_00001 で定義されたパラメータを共用します
   - ボスブロックのMstEnemyStageParameterにはボスパラメータのみを含めます
