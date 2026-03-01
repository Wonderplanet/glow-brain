# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01 13:15:08
- コンテンツ種別: dungeon（限界チャレンジ）
- ブロック種別: normal（通常ブロック）
- シリーズ: SPY×FAMILY（spy）

---

## インゲームID命名案

- **インゲームID**: `dungeon_spy_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` の規則に従いシリーズ略称 `spy` を使用

同一IDを共有するテーブル:

| テーブル | カラム | 値 |
|---------|--------|-----|
| MstInGame | id | `dungeon_spy_normal_00001` |
| MstAutoPlayerSequence | sequence_set_id | `dungeon_spy_normal_00001` |
| MstPage | id | `dungeon_spy_normal_00001` |
| MstEnemyOutpost | id | `dungeon_spy_normal_00001` |

---

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚A（enemy_spy_00001）、雑魚B（enemy_spy_00101） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | dungeon_normal固定3行 |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成（雑魚のみ） |
| MstInGame | 1件 | ボスなし |

---

## MstEnemyStageParameter 敵パラメータ設計

### 雑魚敵の選定根拠

`domain/knowledge/masterdata/in-game/作品別雑魚敵使用状況調査.md` より：

| 順位 | mst_enemy_character_id | 使用回数 | 採用 |
|------|----------------------|---------|----|
| 1位 | enemy_spy_00001 | 116 | ✅ 雑魚A |
| 2位 | enemy_spy_00101 | 29 | ✅ 雑魚B |

SPY×FAMILYは「1体主力型」だが、2体を採用することでdungeon_normalブロックに変化をもたせる。

### パラメータ設計

| 項目 | 雑魚A | 雑魚B | 備考 |
|------|------|------|------|
| id | `e_spy_00001_spy_dungeon_Normal_Colorless` | `e_spy_00101_spy_dungeon_Normal_Colorless` | |
| mst_enemy_character_id | `enemy_spy_00001` | `enemy_spy_00101` | |
| character_unit_kind | `Normal` | `Normal` | 雑魚敵 |
| hp | `1000` | `1000` | ユーザー指定 |
| attack_power | `5000` | `5000` | ユーザー指定（※要確認） |
| move_speed | `45` | `42` | 「少し速い」= 普通(35-50)の上限寄り |
| well_distance | `0.35` | `0.25` | 既存spy敵の値を参考 |
| role_type | `Attack` | `Attack` | 既存spy敵と同様 |
| drop_battle_point | `100` | `100` | 通常雑魚の目安 |
| mst_unit_ability_id1 | （空） | （空） | 必殺ワザなし |
| attack_combo_cycle | `1` | `1` | シンプルな攻撃 |
| damage_knock_back_count | `0` | `0` | 雑魚ノックバックなし |

> **⚠️ attack_power について要確認**
>
> ユーザー指定の `5000` は既存データの最大値（ガイド上限: ボス3,800）を超える非常に高い値です。
> 既存SPY×FAMILY normal ステージの雑魚は attack_power=50（シーケンス倍率×2で実効値100）です。
> dungeon専用の高難易度設定として意図的に5000を指定したのか確認してください。
> - 意図的なら: このまま 5000 で生成します
> - 別の意図なら: 「最終攻撃力5000を実現するには、attack_power=500 × シーケンス倍率coef=10」のような設計も可能です

---

## MstInGame 主要パラメータ設計

| カラム | 値 | 備考 |
|--------|-----|------|
| id | `dungeon_spy_normal_00001` | |
| mst_auto_player_sequence_id | `dungeon_spy_normal_00001` | |
| mst_auto_player_sequence_set_id | `dungeon_spy_normal_00001` | |
| mst_page_id | `dungeon_spy_normal_00001` | |
| mst_enemy_outpost_id | `dungeon_spy_normal_00001` | |
| boss_mst_enemy_stage_parameter_id | （空） | dungeon_normalはボスなし |
| boss_count | `0` | ボスなし |
| normal_enemy_hp_coef | `1` | 全体倍率1（新規コンテンツ） |
| normal_enemy_attack_coef | `1` | 全体倍率1 |
| normal_enemy_speed_coef | `1` | 全体倍率1 |
| boss_enemy_hp_coef | `1` | |
| boss_enemy_attack_coef | `1` | |
| boss_enemy_speed_coef | `1` | |
| bgm_asset_key | `SSE_SBG_003_002` | 参照: normal_spy系と同様 |
| boss_bgm_asset_key | （空） | ボスなし |
| loop_background_asset_key | `spy_00005` | 参照: normal_spy_00001 と同様 |
| player_outpost_asset_key | （空） | 設定なし |
| mst_defense_target_id | （空） | |
| release_key | `999999999` | 開発テスト用（後で正式リリースキーに変更） |

---

## MstEnemyOutpost 設計

| カラム | 値 | 備考 |
|--------|-----|------|
| id | `dungeon_spy_normal_00001` | |
| hp | `100` | dungeon_normal 固定値 |
| is_damage_invalidation | （空） | ダメージ有効 |
| outpost_asset_key | （空） | |
| artwork_asset_key | `spy_0001` | 参照: normal_spy_00001と同様 |

---

## MstPage 設計

| カラム | 値 |
|--------|-----|
| id | `dungeon_spy_normal_00001` |
| release_key | `999999999` |

---

## MstKomaLine 構成設計

dungeon_normal は **3行固定**。コマエフェクトなし（None）で構成。

| id | row | height | layout_asset_key | koma1 | koma1_width | koma2 | koma2_width |
|----|-----|--------|-----------------|-------|-------------|-------|-------------|
| `dungeon_spy_normal_00001_1` | 1 | 0.55 | 6 | spy_00005 | 0.5 | spy_00005 | 0.5 |
| `dungeon_spy_normal_00001_2` | 2 | 0.55 | 3 | spy_00005 | 0.4 | spy_00005 | 0.6 |
| `dungeon_spy_normal_00001_3` | 3 | 0.55 | 1 | spy_00005 | 1.0 | （空） | （空） |

- 参照: `normal_spy_00001` のコマレイアウトパターンを踏襲
- エフェクト: すべて `None`（コマ効果なし）

---

## MstAutoPlayerSequence ウェーブ構成設計

dungeon_normal の動作仕様: フロア毎に配置された敵が、味方の進行に反応して起動する受動的構成。

**5行構成**（雑魚のみ、ボスなし）:

| 行 | id | condition_type | condition_value | action_value | summon_count | hp_coef | atk_coef | spd_coef |
|-----|-----|---------------|----------------|-------------|-------------|---------|---------|---------|
| 1 | `dungeon_spy_normal_00001_1` | ElapsedTime | 500 | e_spy_00001_spy_dungeon_Normal_Colorless | 3 | 1 | 1 | 1 |
| 2 | `dungeon_spy_normal_00001_2` | ElapsedTime | 2000 | e_spy_00101_spy_dungeon_Normal_Colorless | 2 | 1 | 1 | 1 |
| 3 | `dungeon_spy_normal_00001_3` | FriendUnitDead | 2 | e_spy_00001_spy_dungeon_Normal_Colorless | 2 | 1 | 1 | 1 |
| 4 | `dungeon_spy_normal_00001_4` | ElapsedTime | 5000 | e_spy_00101_spy_dungeon_Normal_Colorless | 2 | 1 | 1 | 1 |
| 5 | `dungeon_spy_normal_00001_5` | FriendUnitDead | 5 | e_spy_00001_spy_dungeon_Normal_Colorless | 1 | 1 | 1 | 1 |

- 全行: aura_type=`Default`、death_type=`Normal`、move_start_condition_type=`None`
- 合計召喚数: enemy_spy_00001が6体、enemy_spy_00101が4体（計10体）

---

## 参照した既存データ

- 参照ステージID: `normal_spy_00001` 〜 `normal_spy_00004`
  - BGMアセットキー: `SSE_SBG_003_002`
  - 背景アセットキー: `spy_00005`（または `spy_00002`）
  - コマアセットキー: `spy_00005`（layout_key=6: 0.5/0.5）、layout_key=3（0.4/0.6）
  - MstEnemyStageParameter: 既存スパイ雑魚 HP=1000, move_speed=31-34
- dungeon既存データ: なし（新規コンテンツのため）

---

## 不確定事項・要確認事項

1. **attack_power = 5000 の意図確認（必須）**
   - 既存SPY×FAMILY雑魚の100倍の攻撃力になります
   - dungeon専用の高難易度設定として意図的か確認が必要

2. **release_key**
   - 暫定 `999999999`（開発テスト用）を使用。正式なリリースキーが決まり次第更新してください

3. **コマアセットキー**
   - `spy_00005` を使用（normal_spy_00001と同じアセット）。dungeon専用アセットが別途用意される場合は変更が必要
