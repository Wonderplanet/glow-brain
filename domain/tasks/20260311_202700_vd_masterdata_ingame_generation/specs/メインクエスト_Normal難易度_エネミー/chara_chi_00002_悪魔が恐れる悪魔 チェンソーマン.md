# 悪魔が恐れる悪魔 チェンソーマン（chara_chi_00002）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_chi_00002
> mst_series_id: chi
> 作品名: チェンソーマン

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_chi_00002` |
| mst_series_id | `chi` |
| 作品名 | チェンソーマン |
| asset_key | `chara_chi_00002` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

チェンソーマン（chara_chi_00002）は、メインクエストNormal難易度において2種のパラメータで運用されている。`normal_chi_00006` では本作品クエストのフィナーレを飾るボスユニット（Boss/Technical/Yellow）として登場し、HP 400,000・攻撃力 450 と序盤〜中盤のボスとして相応の耐久力を持つ。`normal_glo3_00001` では他作品（チェンソーマン×グローナ）クロスクエストのメインキャラとして通常敵（Normal/Technical/Yellow）で配置され、HP 100,000・攻撃力 300 とやや控えめなステータスに設定されている。

両パラメータとも role_type は Technical で固定、color は Yellow 統一。アビリティ・変身設定は一切なし。攻撃パターンは通常攻撃（Normal）とスペシャル（Special）を持ち、スペシャルには複数ヒット＋広範囲ドレイン＆ノックバックが含まれるのが特徴。召喚位置（summon_position）はいずれも空欄（デフォルト位置）で統一されている。コマ効果は両ステージとも全コマ None であり、コマ効果を使ったギミック演出は設定されていない。

---

## 3. ステージ別使用実態

### normal_chi_00006（メインクエスト Normal）

#### このステージでの役割

チェンソーマン本作品ストーリーのメインクエスト終盤に登場するボスユニット。「仲間キャラが2体倒れた後に初めて出現する」という条件付き召喚設計になっており、プレイヤーが序盤の苦境を乗り越えた後に解放される大型の敵として機能している。HP 400,000 で他の通常敵と比べ高い耐久力を持ち、ステージの難易度上昇をけん引する存在。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_chi_00002_general_Boss_Yellow` | Boss | Technical | Yellow | 400,000 | 450 | 50 | 0.15 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_chi_00002_general_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**c_chi_00002_general_Boss_Yellow**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 54 |
| Special | 0 | なし | なし | 187 |

**MstAttackElement 詳細（Normal攻撃）**

| sort_order | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter |
|-----------|------------|-----------|-----------------|-------------|---------|----------------|
| 1 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |

**MstAttackElement 詳細（Special攻撃）**

| sort_order | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter |
|-----------|------------|-----------|-----------------|-------------|---------|----------------|
| 1 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 2 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 3 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 4 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 5 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 6 | Direct | Distance 0.35 | 100 | Damage | Drain | 200% |
| 7 | Direct | Distance 0.35 | 100 | None | KnockBack1 | 0% |

> Appearance は HP 満タン時に発動する出現アクション（ForcedKnockBack5 で広範囲ノックバック）。Special は5連続単体攻撃のあと、広範囲 200% ドレイン攻撃＋全体ノックバックを行う強力な技構成。

#### シーケンス設定

```
sequence_set_id: normal_chi_00006
sequence_element_id: 3
condition_type: FriendUnitDead
condition_value: 2
action_type: SummonEnemy
action_value: c_chi_00002_general_Boss_Yellow
summon_position: （デフォルト）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 1
sequence_group_id: （なし）
```

フレンドユニットが2体倒れた時点でチェンソーマンが1体召喚される。シーケンス要素1（250フレーム経過で余獣1体）→ 要素2（フレンド1体死亡で別キャラ1体）→ 要素3（フレンド2体死亡でチェンソーマン登場）という段階的な登場設計。

#### コマ効果

コマ効果なし（全コマ `None`）

---

### normal_glo3_00001（メインクエスト Normal）

#### このステージでの役割

チェンソーマン×グローナ（glo3）クロスクエストにおいて、ステージ開幕から登場するメインキャラの通常敵として配置される。HP 100,000・攻撃力 300 と Normal 種としてはやや強めの設定で、250フレーム（約4秒）待機後に即召喚される先導役。このステージではさらに後続として他キャラクター3体が連鎖的に召喚される構成になっており、チェンソーマンはその先頭を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_chi_00002_general_as_Normal_Yellow` | Normal | Technical | Yellow | 100,000 | 300 | 35 | 0.11 | 5 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_chi_00002_general_as_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

**c_chi_00002_general_as_Normal_Yellow**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 54 |
| Special | 0 | なし | なし | 133 |

**MstAttackElement 詳細（Normal攻撃）**

| sort_order | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter |
|-----------|------------|-----------|-----------------|-------------|---------|----------------|
| 1 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |

**MstAttackElement 詳細（Special攻撃）**

| sort_order | attack_type | range_end | max_target_count | damage_type | hit_type | power_parameter |
|-----------|------------|-----------|-----------------|-------------|---------|----------------|
| 1 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 2 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 3 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 4 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 5 | Direct | Distance 0.2 | 1 | Damage | Normal | 100% |
| 6 | Direct | Distance 0.5 | 100 | Damage | Drain | 200% |
| 7 | Direct | Distance 0.5 | 100 | Damage | KnockBack1 | 0% |

> Special はボス版と同様に5連続単体攻撃＋広範囲ドレイン＆ノックバック構成。ボス版より range_end が大きく（0.5 vs 0.35）広範囲化されており、通常敵ながら範囲スペシャルの脅威度は高い。なお as（アニバーサリー）パラメータのため Appearance 攻撃は存在しない。

#### シーケンス設定

```
sequence_set_id: normal_glo3_00001
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 400
action_type: SummonEnemy
action_value: c_chi_00002_general_as_Normal_Yellow
summon_position: （デフォルト）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 0.9
sequence_group_id: （なし）
```

ステージ開始から400フレーム（約6.7秒）経過後に1体召喚。enemy_hp_coef=0.9 が設定されており、パラメータ定義値の90%のHPで出現する（実際のHP: 90,000）。その後フレンドユニットが1体倒れるたびに別キャラが連鎖召喚される形式。

#### コマ効果

コマ効果なし（全コマ `None`）
