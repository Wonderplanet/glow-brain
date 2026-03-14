# ラーメン（enemy_gom_00701）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_gom_00701
> mst_series_id: gom
> 作品名: "姫様"拷問"の時間です"

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_gom_00701` |
| mst_series_id | `gom` |
| 作品名 | "姫様"拷問"の時間です" |
| asset_key | `enemy_gom_00701` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

ラーメンはメインクエストNormal難易度において**Boss種別（character_unit_kind=Boss）のAttackロール**として使用される中ボスキャラクターです。フィルタ対象ステージ（normal_glo1_00001、normal_gom_00005、normal_gom_00006）では全てBoss種別で登場し、HP 10,000・攻撃力 50 と控えめなボス水準のステータスを持ちます。

カラーバリエーションはColorlessとYellowの2種が存在し、"姫様"拷問"の時間です"作品内のステージ（normal_gom_*）ではYellow固定、"姫様"拷問"の時間です"以外のステージ（normal_glo1_00001）ではColorlessが使用されます。変身設定はなく、アビリティも設定されていません。

出現トリガーはElapsedTime（経過時間）またはFriendUnitDead（味方ユニット死亡数）で使用されており、ステージの局面転換・フェーズ移行のタイミングで1体だけ召喚されるパターンが中心です。コマ効果はnormal_gom_00006でSlipDamage（Playerサイド）が使われており、プレイヤー側への継続ダメージを与えるステージ設計が見られます。

---

## 3. ステージ別使用実態

### normal_glo1_00001（メインクエスト Normal）

#### このステージでの役割

味方ユニット2体死亡という条件（FriendUnitDead=2）のタイミングでColorless版が1体召喚される。プレイヤー側がある程度消耗した後のフォロー攻撃として登場し、続けて複数の雑魚ユニット（e_gom_00801、e_gom_00901）ウェーブを引き起こすフェーズ転換ボスとして機能している。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00701_general_n_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00701_general_n_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 50 |

**MstAttackElement（Normal攻撃）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|------------|----------|-----------|--------|------------|---------|----------------|------------|
| 1 | Direct | Distance:0 | Distance:0.27 | 1 | Foe / All | Damage | Normal | Percentage:100.0 | None |

**MstAttackElement（Appearance）**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | effect_type |
|-----------|------------|------------|----------|-----------|--------|------------|---------|------------|
| 1 | Direct | Distance:0 | Distance:50.0 | 100 | Foe / Character | None | ForcedKnockBack5 | None |

> 登場時に範囲50の広範囲ノックバック（ForcedKnockBack5）を全敵キャラクターに与え、通常攻撃は近接1体ダメージ（射程0.27）のシンプルな構成。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 3
action_type: SummonEnemy
action_value: e_gom_00701_general_n_Boss_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 4
sequence_group_id: （空）
```

味方ユニット2体死亡をトリガーに1体だけ召喚される。enemy_hp_coef=4によりHPは基準値の4倍（40,000相当）に強化されており、消耗したプレイヤーに対して強力な圧力をかける設計となっている。

#### コマ効果

コマ効果なし（Noneのみ）

---

### normal_gom_00005（メインクエスト Normal）

#### このステージでの役割

ElapsedTime=0で開幕直後にColorless版が召喚され、ウェーブ全体のフロントボスとして機能する。さらにシーケンスグループ切り替え後（group1・group2）も各グループ先頭でBossColorlessが再召喚されるため、フェーズが変わるたびに再登場する繰り返しボスとして設計されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00701_general_n_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00701_general_n_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 50 |

> 攻撃パターンはnormal_glo1_00001と同一。登場時ForcedKnockBack5（広範囲ノックバック）＋通常は近接1体ダメージ。

#### シーケンス設定（開幕ウェーブ）

```
condition_type: ElapsedTime
condition_value: 0
sequence_element_id: 1
action_type: SummonEnemy
action_value: e_gom_00701_general_n_Boss_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: （空）
```

#### シーケンス設定（group1フェーズ）

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
sequence_element_id: 4
action_type: SummonEnemy
action_value: e_gom_00701_general_n_Boss_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: group1
```

#### シーケンス設定（group2フェーズ）

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
sequence_element_id: 8
action_type: SummonEnemy
action_value: e_gom_00701_general_n_Boss_Colorless
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 2
sequence_group_id: group2
```

開幕・group1・group2それぞれのフェーズ開始直後（condition_value=0）に1体ずつ召喚される。enemy_hp_coef=2で各フェーズで同一強度（HP20,000相当）を維持しており、フェーズ管理のアンカー的なボスとして機能している。

#### コマ効果

コマ効果なし（Noneのみ）

---

### normal_gom_00006（メインクエスト Normal）

#### このステージでの役割

ElapsedTime=500（約5秒後）でYellow版が1体召喚される。ステージ序盤から友軍（c_gom_00101_general_n_Boss_Yellow）と共にYellow配色で展開される構成の中で、序盤の中継ボスとして登場する。後半にはgroup1フェーズで大ボス（c_gom_00001）や強力なユニット群が登場する多段階構成のステージ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_gom_00701_general_n_Boss_Yellow` | Boss | Attack | Yellow | 10,000 | 50 | 34 | 0.11 | （空） |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_gom_00701_general_n_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 50 |

> 攻撃パターンはColorless版と同一（登場時ForcedKnockBack5＋通常は近接1体ダメージ）。

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 500
sequence_element_id: 4
action_type: SummonEnemy
action_value: e_gom_00701_general_n_Boss_Yellow
summon_position: （空）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1.5
sequence_group_id: （空）
```

ElapsedTime=500で1体召喚。enemy_hp_coef=1.5でHP15,000相当と、他ステージと比べて控えめな強化設定。序盤～中盤のつなぎボスとして配置されており、プレイヤーに重すぎない負荷を与えるバランス調整が見られる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 2 | Player |

> SlipDamageがPlayerサイドに設定されており、プレイヤーに継続ダメージ（スリップダメージ）を与えるステージ設計。ラーメンというキャラクター名から食事をモチーフにした継続ダメージ演出と考えられる。
