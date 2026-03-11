# vd_tak_normal_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

「タコピーの原罪」の世界観を反映したノーマルブロックです。tak作品固有の雑魚敵は存在しないため、全3波すべてにファントム（Colorless属性・攻撃ロール）を配置します。ファントムは限界チャレンジ共通の雑魚敵であり、act_combo_cycle=1 の単純な攻撃パターンを持つ敵です。3波構成（0.25秒後・1.5秒後・3.0秒後）で合計18体が登場し、プレイヤーへの適度なプレッシャーと十分なバトルポイント獲得機会を確保しています。フロア係数 1.00 を基準に設計され、HP 5,000・攻撃力 100 のファントムが波状に押し寄せる単純明快な難易度構成です。フェーズ切り替えはなく、SwitchSequenceGroup は使用しません。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール・tak固有キャラなし |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202509010)
> `e_glo_00001_vd_Normal_Colorless` は release_key=202509010 で既存登録済み。今回バッチ（release_key=202604010）では新規追加不要。既存IDをそのまま参照する。

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

各行独立ランダム抽選（12パターンから）の結果:

```mermaid
block-beta
  columns 4
  A["row=1 / koma1\n幅=0.25\neffect: None"]:1 B["row=1 / koma2\n幅=0.50\neffect: None"]:2 C["row=1 / koma3\n幅=0.25\neffect: None"]:1
  columns 5
  D["row=2 / koma1\n幅=0.60\neffect: None"]:3 E["row=2 / koma2\n幅=0.40\neffect: None"]:2
  columns 4
  F["row=3 / koma1\n幅=0.25\neffect: None"]:1 G["row=3 / koma2\n幅=0.25\neffect: None"]:1 H["row=3 / koma3\n幅=0.25\neffect: None"]:1 I["row=3 / koma4\n幅=0.25\neffect: None"]:1
```

| row | height | 選択パターン | コマ数 | 各幅 | 幅合計 |
|-----|--------|------------|-------|------|--------|
| 1 | 0.33 | パターン9「中央広い」 | 3コマ | 0.25, 0.50, 0.25 | 1.0 |
| 2 | 0.33 | パターン2「右ちょい長2コマ」 | 2コマ | 0.60, 0.40 | 1.0 |
| 3 | 0.34 | パターン12「4等分」 | 4コマ | 0.25, 0.25, 0.25, 0.25 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> W1["ElapsedTime 250ms\nファントム ×6\nDefault"]
  W1 --> W2["ElapsedTime 1500ms\nファントム ×6\nDefault"]
  W2 --> W3["ElapsedTime 3000ms\nファントム ×6\nDefault"]
  W3 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数 |
|------|-------------|---|---|---------|
| 1 | ElapsedTime 250ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 6 | 6 |
| 2 | ElapsedTime 1500ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 6 | 12 |
| 3 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 6 | 18 |

合計: **18体**（要件「最低15体以上」を満たす）

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波 | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|---|---|---------|---------|------|----------|----------|-------|
| 1 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 2 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 3 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | （空） | VDの背景切り替えはゲームロジック側で管理 |
| フロア0以上 | koma_background_vd_00001 | クライアント側でフロア係数に応じて切り替え |
| フロア20以上 | koma_background_vd_00003 | 同上 |
| フロア40以上 | koma_background_vd_00005 | 同上 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | SSE_SBG_003_010 | ノーマルブロック用BGM |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | 全敵キャラ（ノーマルブロックはボスなし、全行Default） |

---

### 敵キャラ召喚アニメーション

全キャラ `SummonEnemy` アクションによるElapsedTime時間差召喚。InitialSummonは使用しない（normalブロックはボスなし）。ファントムは `speed=34` の標準移動速度キャラであり、3波に分かれて前線へ進行する演出となる。tak作品固有キャラはなく、全波ともファントムのみで構成されるシンプルな召喚演出。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照（新規追加不要） | e_glo_00001_vd_Normal_Colorless は release_key=202509010 で登録済み |
| MstEnemyOutpost | 新規生成 | id=vd_tak_normal_00001、HP=100固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_tak_normal_00001 |
| MstKomaLine | 新規生成 | 3行固定（row1-3）、各行独立ランダム抽選 |
| MstAutoPlayerSequence | 新規生成 | sequence_set_id=vd_tak_normal_00001、3要素（計18体） |
| MstInGame | 新規生成 | id=vd_tak_normal_00001、stage_type=vd_normal、ボスなし |

---

## マスタデータ設計詳細

### MstEnemyStageParameter

> 既存登録済み（release_key=202509010）。今回バッチ（202604010）での追加不要。

| ENABLE | release_key | id | mst_enemy_character_id | character_unit_kind | role_type | color | sort_order | hp | damage_knock_back_count | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|--------|-------------|---|------------------------|---------------------|-----------|-------|------------|---|------------------------|------------|---------------|-------------|-------------------|------------------|
| e | 202509010 | e_glo_00001_vd_Normal_Colorless | enemy_glo_00001 | Normal | Attack | Colorless | 1001 | 5000 | 3 | 34 | 0.22 | 100 | 1 | 150 |

### MstEnemyOutpost

| ENABLE | release_key | id | hp | is_damage_invalidation |
|--------|-------------|---|---|----------------------|
| e | 202604010 | vd_tak_normal_00001 | 100 | （空） |

### MstPage

| ENABLE | release_key | id |
|--------|-------------|---|
| e | 202604010 | vd_tak_normal_00001 |

### MstKomaLine

| ENABLE | release_key | id | mst_page_id | row | height | koma1_width | koma2_width | koma3_width | koma4_width | koma1_effect_type | koma2_effect_type | koma3_effect_type | koma4_effect_type | koma1_effect_target_side | koma2_effect_target_side | koma3_effect_target_side | koma4_effect_target_side |
|--------|-------------|---|------------|-----|--------|------------|------------|------------|------------|------------------|------------------|------------------|------------------|------------------------|------------------------|------------------------|------------------------|
| e | 202604010 | vd_tak_normal_00001_row1 | vd_tak_normal_00001 | 1 | 0.33 | 0.25 | 0.50 | 0.25 | | None | None | None | | All | All | All | |
| e | 202604010 | vd_tak_normal_00001_row2 | vd_tak_normal_00001 | 2 | 0.33 | 0.60 | 0.40 | | | None | None | | | All | All | | |
| e | 202604010 | vd_tak_normal_00001_row3 | vd_tak_normal_00001 | 3 | 0.34 | 0.25 | 0.25 | 0.25 | 0.25 | None | None | None | None | All | All | All | All |

### MstAutoPlayerSequence

| ENABLE | release_key | id | sequence_set_id | sequence_group_id | sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | summon_position | move_start_condition_type | move_start_condition_value | aura_type | enemy_hp_coef | enemy_attack_coef | enemy_speed_coef | koma_effect_type |
|--------|-------------|---|----------------|------------------|--------------------|--------------|-----------------|-----------|--------------|-----------|-----------------|-----------------|--------------------------|--------------------------|-----------|--------------|-----------------|--------------------|-----------------|
| e | 202604010 | vd_tak_normal_00001_1 | vd_tak_normal_00001 | （空） | 1 | ElapsedTime | 250 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 6 | 0 | | | | Default | 1 | 1 | 1 | None |
| e | 202604010 | vd_tak_normal_00001_2 | vd_tak_normal_00001 | （空） | 2 | ElapsedTime | 1500 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 6 | 0 | | | | Default | 1 | 1 | 1 | None |
| e | 202604010 | vd_tak_normal_00001_3 | vd_tak_normal_00001 | （空） | 3 | ElapsedTime | 3000 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 6 | 0 | | | | Default | 1 | 1 | 1 | None |

### MstInGame

| ENABLE | release_key | id | mst_auto_player_sequence_set_id | bgm_asset_key | mst_page_id | mst_enemy_outpost_id | boss_mst_enemy_stage_parameter_id | normal_enemy_hp_coef | normal_enemy_attack_coef | normal_enemy_speed_coef | normal_enemy_roulette_point | rare_enemy_roulette_point | boss_enemy_roulette_point | boss_enemy_hp_coef | boss_enemy_attack_coef | boss_enemy_speed_coef |
|--------|-------------|---|--------------------------------|--------------|------------|---------------------|----------------------------------|---------------------|------------------------|-----------------------|---------------------------|--------------------------|--------------------------|------------------|----------------------|---------------------|
| e | 202604010 | vd_tak_normal_00001 | vd_tak_normal_00001 | SSE_SBG_003_010 | vd_tak_normal_00001 | vd_tak_normal_00001 | （空） | 1.0 | 1.0 | 1 | 5 | 50 | 20 | 1.0 | 1.0 | 1 |
