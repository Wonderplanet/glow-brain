# vd_sum_normal_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

ファントム（`e_glo_00001_vd_Normal_Colorless`：Colorless/Attack・HP5,000）が開幕250msに5体で先行登場し、影（`e_sum_00001_vd_Normal_Red`：Red/Defense・HP350,000）が1,500ms・2,500msと中盤に向けて押し寄せてくる構成。5体撃破で影が補充され、10体撃破すると網代 慎平（`c_sum_00001_vd_Boss_Red`：Red/Technical・HP245,000）がBossオーラつきで砦付近に出現。その後は影の撃破が続くと再度網代 慎平が補充登場し、最終的に影の無限補充と網代 慎平の繰り返し登場が重なる終盤圧力ラッシュへと移行する。合計確定出現数は20体以上で「最低15体以上」の要件を満たす。

コマは3行固定（各行独立抽選）。row1=パターン12（4等分4コマ：0.25×4）、row2=パターン8（左広い3コマ：0.50/0.25/0.25）、row3=パターン6（2等分2コマ：0.50/0.50）。コマアセット: `sum_00001`（back_ground_offset: 0.6）。

UR対抗キャラ「影のウシオ 小舟 潮」（`chara_sum_00101`）への対抗として、Red属性の影と網代 慎平が主軸となり、Red属性対策コマを活かした攻略を求める設計となっている。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| `enemy_glo_00001` | ファントム | 雑魚（共通） | Colorless/Attack・序盤テンポ役 |
| `enemy_sum_00001` | 影 | 雑魚 | `vd_all` CSVに `e_sum_00001_vd_Normal_Red` として定義済み。Red/Defense |
| `chara_sum_00001` | 網代 慎平 | 強化ボス（c_キャラ） | `vd_all` CSVに `c_sum_00001_vd_Boss_Red` として定義済み。Red/Technical |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照（`vd_all/data/MstEnemyStageParameter.csv`より）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| `e_glo_00001_vd_Normal_Colorless` | ファントム | Normal | Attack | Colorless | 5000 | 100 | 34 | 0.22 | 3 | 1 | 150 |
| `e_sum_00001_vd_Normal_Red` | 影 | Normal | Defense | Red | 350000 | 600 | 40 | 0.2 | 1 | 1 | 10 |
| `c_sum_00001_vd_Boss_Red` | 網代 慎平 | Boss | Technical | Red | 245000 | 500 | 45 | 0.11 | 2 | 5 | 10 |

---

### コマ設計

```mermaid
block-beta
  columns 4
  A["row=1 / koma1\n幅=0.25\neffect: None"]:1 B["row=1 / koma2\n幅=0.25\neffect: None"]:1 C["row=1 / koma3\n幅=0.25\neffect: None"]:1 D["row=1 / koma4\n幅=0.25\neffect: None"]:1
  E["row=2 / koma1\n幅=0.50\neffect: None"]:2 F["row=2 / koma2\n幅=0.25\neffect: None"]:1 G["row=2 / koma3\n幅=0.25\neffect: None"]:1
  H["row=3 / koma1\n幅=0.50\neffect: None"]:2 I["row=3 / koma2\n幅=0.50\neffect: None"]:2
```

※ columns は1つのみ（4）。各行のスパン合計 = 4。

| row | height | 選択パターン | コマ数 | 各幅 | 幅合計 |
|-----|--------|------------|-------|------|--------|
| 1 | 0.33 | パターン12「4等分」 | 4 | 0.25, 0.25, 0.25, 0.25 | 1.0 |
| 2 | 0.33 | パターン8「左広い」 | 3 | 0.50, 0.25, 0.25 | 1.0 |
| 3 | 0.34 | パターン6「2等分」 | 2 | 0.50, 0.50 | 1.0 |

---

### 敵キャラシーケンス設計

> **c_キャラ同時出現ルール（プランナー確認済み）**: c_キャラ（`c_` プレフィックス）が複数体登場する場合、
> 初回のみ `ElapsedTime`、2体目以降は `FriendUnitDead`（前の c_キャラの sequence_element_id を
> condition_value に指定）でチェーンすること。また c_キャラの `summon_count` は必ず `1` とすること。`e_glo_*` は対象外。

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start([開始]) --> E1[ElapsedTime: 250ms\nファントム×5\nelem=1]
  E1 --> E2[ElapsedTime: 1500ms\n影×3\nelem=2]
  E2 --> E3[ElapsedTime: 2500ms\n影×3\nelem=3]
  E3 --> E4[ElapsedTime: 4000ms\n影×3\nelem=4]
  E4 --> E5[FriendUnitDead: 5体\n影×3\nelem=5]
  E5 --> E6[FriendUnitDead: 10体\n網代慎平×1\nBossオーラ\nelem=6]
  E6 --> E7[FriendUnitDead: 10体\n影×3\nelem=7]
  E7 --> E8[FriendUnitDead: 15体\n影×99\n無限補充\nelem=8]
  E8 --> E9[FriendUnitDead: 15体\n網代慎平×1\nBossオーラ\nelem=9]
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数/召喚位置 |
|------|-------------|---|---|-----------------|
| 1 | ElapsedTime=250 | ファントム (e_glo_00001_vd_Normal_Colorless) | 5（interval=0） | 累計5体 |
| 2 | ElapsedTime=1500 | 影 (e_sum_00001_vd_Normal_Red) | 3（interval=500） | 累計8体 |
| 3 | ElapsedTime=2500 | 影 (e_sum_00001_vd_Normal_Red) | 3（interval=500） | 累計11体 |
| 4 | ElapsedTime=4000 | 影 (e_sum_00001_vd_Normal_Red) | 3（interval=500） | 累計14体 |
| 5 | FriendUnitDead=5 | 影 (e_sum_00001_vd_Normal_Red) | 3（interval=500） | +3体追加 |
| 6 | FriendUnitDead=10 | 網代 慎平 (c_sum_00001_vd_Boss_Red) | 1（summon_count=1） | position=1.7 / Bossオーラ |
| 7 | FriendUnitDead=10 | 影 (e_sum_00001_vd_Normal_Red) | 3（interval=500） | 同タイミング elem=6と並列 |
| 8 | FriendUnitDead=15 | 影 (e_sum_00001_vd_Normal_Red) | 99（interval=750） | 無限補充スタート |
| 9 | FriendUnitDead=15 | 網代 慎平 (c_sum_00001_vd_Boss_Red) | 1（summon_count=1） | Bossオーラ / elem=8と並列 |

**設計のポイント**:
- `ElapsedTime` の4波（elem1〜4）で開幕〜中盤を形成。ファントム5体が先行し、影が500ms interval で3体ずつ登場する
- `FriendUnitDead=5` で中盤補充（elem5）。倒すほど続く「影の波」演出
- `FriendUnitDead=10` で網代 慎平がBossオーラつきで砦付近（position=1.7）に登場（elem6）、同タイミングで影3体も追加（elem7）
- `FriendUnitDead=15` で終盤：影の無限補充（elem8、interval=750ms）と網代 慎平の再登場（elem9）が同時に発動
- 合計確定出現数（elem1〜7）: ファントム5体 + 影3+3+3+3+3=15体 + 網代慎平1体 = 21体（「最低15体以上」の制約クリア）
- c_キャラ（網代 慎平）は複数エントリ（elem6・elem9）あるが、それぞれ独立した FriendUnitDead トリガーで発火するため同時出現なし。各 summon_count=1 で1体ずつ確実に出現する

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波/フェーズ | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|-----------|---|---------|---------|------|----------|----------|-------|
| 開幕（elem1） | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 中盤 ElapsedTime（elem2〜4） | 影 | 350,000 | 1.0 | 350,000 | 600 | 1.0 | 600 |
| FriendUnitDead=5（elem5） | 影 | 350,000 | 1.0 | 350,000 | 600 | 1.0 | 600 |
| FriendUnitDead=10（elem6） | 網代 慎平 | 245,000 | 1.0 | 245,000 | 500 | 1.0 | 500 |
| FriendUnitDead=10（elem7） | 影 | 350,000 | 1.0 | 350,000 | 600 | 1.0 | 600 |
| 無限補充（elem8） | 影 | 350,000 | 1.0 | 350,000 | 600 | 1.0 | 600 |
| FriendUnitDead=15（elem9） | 網代 慎平 | 245,000 | 1.0 | 245,000 | 500 | 1.0 | 500 |

MstInGame の `normal_enemy_hp_coef = 1.0`、`normal_enemy_attack_coef = 1.0`。

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| MstInGame.loop_background_asset_key | （空） | VD normalは背景省略 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | `SSE_SBG_003_010` | normalブロック固定値 |
| boss_bgm_asset_key | （空） | normalブロックではボスBGM切り替えなし |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | ファントム（elem=1）、影（全elem） |
| Boss | 網代 慎平（elem=6, 9） |

---

### 敵キャラ召喚アニメーション

`summon_animation_type` は全行 `None`（VD固定値）。

網代 慎平（elem=6）は `position=1.7` に配置し、`move_start_condition_type=None`（召喚と同時に移動開始）。FriendUnitDead=10体撃破後にBossオーラで出現し、プレイヤーに緊張感を与える。FriendUnitDead=15体到達後（elem=9）は再度網代 慎平が補充登場し、影の無限補充と同時に最終圧力をかける。

---

## テーブルデータサマリ

### MstInGame

| カラム | 値 |
|-------|---|
| id | `vd_sum_normal_00001` |
| release_key | `202604010` |
| content_type | `Dungeon` |
| stage_type | `vd_normal` |
| mst_page_id | `vd_sum_normal_00001` |
| mst_enemy_outpost_id | `vd_sum_normal_00001` |
| boss_mst_enemy_stage_parameter_id | （空） |
| mst_auto_player_sequence_id | `vd_sum_normal_00001` |
| mst_auto_player_sequence_set_id | `vd_sum_normal_00001` |
| bgm_asset_key | `SSE_SBG_003_010` |
| boss_bgm_asset_key | （空） |
| loop_background_asset_key | （空） |
| normal_enemy_hp_coef | `1.0` |
| normal_enemy_attack_coef | `1.0` |
| normal_enemy_speed_coef | `1.0` |
| boss_enemy_hp_coef | `1.0` |
| boss_enemy_attack_coef | `1.0` |
| boss_enemy_speed_coef | `1.0` |

### MstPage

| カラム | 値 |
|-------|---|
| id | `vd_sum_normal_00001` |
| release_key | `202604010` |

### MstEnemyOutpost

| カラム | 値 |
|-------|---|
| id | `vd_sum_normal_00001` |
| hp | `100` |
| is_damage_invalidation | （空） |
| outpost_asset_key | （空） |
| artwork_asset_key | （要確認） |
| release_key | `202604010` |

### MstKomaLine（3行）

| id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles |
|----|------------|-----|--------|--------------------------|----------------|-------------|------------------------|-------------------|------------------------|------------------------|------------------------|--------------------------|--------------------------|
| `vd_sum_normal_00001_1` | `vd_sum_normal_00001` | 1 | 0.33 | 12 | `sum_00001` | 0.25 | 0.6 | None | 0 | 0 | All | All | All |
| `vd_sum_normal_00001_2` | `vd_sum_normal_00001` | 2 | 0.33 | 8 | `sum_00001` | 0.50 | 0.6 | None | 0 | 0 | All | All | All |
| `vd_sum_normal_00001_3` | `vd_sum_normal_00001` | 3 | 0.34 | 6 | `sum_00001` | 0.50 | 0.6 | None | 0 | 0 | All | All | All |

行1: koma2〜4_asset_key=`sum_00001`, 各width=0.25, 各effect_type=None
行2: koma2_asset_key=`sum_00001`, koma2_width=0.25, koma2_effect_type=None / koma3_asset_key=`sum_00001`, koma3_width=0.25, koma3_effect_type=None
行3: koma2_asset_key=`sum_00001`, koma2_width=0.50, koma2_effect_type=None

### MstAutoPlayerSequence（9行）

| id | sequence_set_id | sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | summon_position | aura_type | death_type | enemy_hp_coef | enemy_attack_coef | enemy_speed_coef | defeated_score | summon_animation_type | move_start_condition_type | move_stop_condition_type | move_restart_condition_type |
|----|----------------|--------------------|----|----|----|----|----|----|----|----|----|----|----|----|----|----|----|----|----|
| `vd_sum_normal_00001_1` | `vd_sum_normal_00001` | 1 | ElapsedTime | 250 | SummonEnemy | `e_glo_00001_vd_Normal_Colorless` | 5 | 0 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_2` | `vd_sum_normal_00001` | 2 | ElapsedTime | 1500 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 3 | 500 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_3` | `vd_sum_normal_00001` | 3 | ElapsedTime | 2500 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 3 | 500 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_4` | `vd_sum_normal_00001` | 4 | ElapsedTime | 4000 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 3 | 500 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_5` | `vd_sum_normal_00001` | 5 | FriendUnitDead | 5 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 3 | 500 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_6` | `vd_sum_normal_00001` | 6 | FriendUnitDead | 10 | SummonEnemy | `c_sum_00001_vd_Boss_Red` | 1 | 0 | 1.7 | Boss | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_7` | `vd_sum_normal_00001` | 7 | FriendUnitDead | 10 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 3 | 500 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_8` | `vd_sum_normal_00001` | 8 | FriendUnitDead | 15 | SummonEnemy | `e_sum_00001_vd_Normal_Red` | 99 | 750 | | Default | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |
| `vd_sum_normal_00001_9` | `vd_sum_normal_00001` | 9 | FriendUnitDead | 15 | SummonEnemy | `c_sum_00001_vd_Boss_Red` | 1 | 0 | | Boss | Normal | 1.0 | 1.0 | 1.0 | 0 | None | None | None | None |

---

## ID一覧

| テーブル | カラム | 値 |
|---------|--------|-----|
| MstInGame | id | vd_sum_normal_00001 |
| MstAutoPlayerSequence | sequence_set_id | vd_sum_normal_00001 |
| MstPage | id | vd_sum_normal_00001 |
| MstEnemyOutpost | id | vd_sum_normal_00001 |
| MstKomaLine | id（row1） | vd_sum_normal_00001_1 |
| MstKomaLine | id（row2） | vd_sum_normal_00001_2 |
| MstKomaLine | id（row3） | vd_sum_normal_00001_3 |
| MstAutoPlayerSequence | id（elem1） | vd_sum_normal_00001_1 |
| MstAutoPlayerSequence | id（elem2） | vd_sum_normal_00001_2 |
| MstAutoPlayerSequence | id（elem3） | vd_sum_normal_00001_3 |
| MstAutoPlayerSequence | id（elem4） | vd_sum_normal_00001_4 |
| MstAutoPlayerSequence | id（elem5） | vd_sum_normal_00001_5 |
| MstAutoPlayerSequence | id（elem6） | vd_sum_normal_00001_6 |
| MstAutoPlayerSequence | id（elem7） | vd_sum_normal_00001_7 |
| MstAutoPlayerSequence | id（elem8） | vd_sum_normal_00001_8 |
| MstAutoPlayerSequence | id（elem9） | vd_sum_normal_00001_9 |
