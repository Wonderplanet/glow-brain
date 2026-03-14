# 怪獣 余獣（enemy_kai_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_kai_00101
> mst_series_id: kai
> 作品名: 怪獣８号

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_kai_00101` |
| mst_series_id | `kai` |
| 作品名 | 怪獣８号 |
| asset_key | `enemy_kai_00101` |
| is_phantomized | `0` |

**キャラクター説明**:
防衛隊員選別試験の最終審査で、カフカたち受験生の討伐対象になった余獣。4足歩行で移動する。目は退化しており視力が弱く、代わりに聴覚が発達している。

---

## 2. キャラクター特徴まとめ

怪獣 余獣はメインクエスト Normal難易度（normal_kai_00003〜00006）において幅広く登場する主力雑魚敵である。カラーバリエーションはColorless・Green・Yellowの3種があり、HPレンジは25,000（Colorless/Defense）から220,000（Yellow/Attack）と大きな幅を持つ。Yellowバリエーションは攻撃力1,000・HPが最も高く、Attack役として後半の強化波として登場する傾向がある。Greenは中程度のHP（67,000）でAttack役、Colorlessは最も低ステータスでDefense役として序盤から継続的に投入される。

変身設定は全バリエーションでなし。アビリティも全て未設定。Yellowバリエーションのみ通常攻撃に加えて攻撃力ダウン効果（AttackPowerDown）付きの第2攻撃エレメントを持ち、対プレイヤー妨害特性がある点が他バリエーションと異なる。コマ効果（koma1〜4 effect_type）は全ステージを通じてNoneのみ使用されており、コマ効果による難易度調整はない。

---

## 3. ステータス一覧（normalクエスト Normal難易度で使用されるバリエーションのみ）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_kai_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 25,000 | 350 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Green` | Normal | Attack | Green | 67,000 | 800 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Yellow` | Normal | Attack | Yellow | 220,000 | 1,000 | 45 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_kai_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_kai_00101_general_Normal_Green` | なし | None | なし | なし |
| `e_kai_00101_general_Normal_Yellow` | なし | None | なし | なし |

---

## 4. 攻撃パターン

### e_kai_00101_general_Normal_Colorless

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 62 | 28 | 75 |

**MstAttackElement**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_param_type | power_param | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|------------|---------|-----------------|------------|------------|
| 1 | Direct | Distance:0 | Distance:0.2 | 1 | Foe/All/All/All | Damage | Normal | Percentage | 100.0 | None |

### e_kai_00101_general_Normal_Green

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 62 | 28 | 75 |

**MstAttackElement**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_param_type | power_param | effect_type |
|-----------|------------|-------------|-----------|-----------|--------|------------|---------|-----------------|------------|------------|
| 1 | Direct | Distance:0 | Distance:0.2 | 1 | Foe/All/All/All | Damage | Normal | Percentage | 100.0 | None |

### e_kai_00101_general_Normal_Yellow

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 62 | 28 | 50 |

**MstAttackElement**

| sort_order | attack_type | range_start | range_end | max_target | target | damage_type | hit_type | power_param_type | power_param | effect_type | effective_count | effective_duration | effect_parameter |
|-----------|------------|-------------|-----------|-----------|--------|------------|---------|-----------------|------------|------------|----------------|-------------------|-----------------|
| 1 | Direct | Distance:0 | Distance:0.3 | 1 | Foe/All/All/All | Damage | Normal | Percentage | 100.0 | None | 0 | 0 | 0 |
| 2 | Direct | Distance:0 | Distance:0.3 | 1 | Foe/All/All/All | Damage | Normal | Percentage | 0.0 | AttackPowerDown | -1 | 500 | 15 |

> Yellow は next_attack_interval が 50（他は 75）と短く、攻撃速度が速い。また攻撃エレメント2番で AttackPowerDown（効果時間500フレーム・効果値15）を付与する妨害攻撃を持つ。

---

## 5. ステージ別使用実態

### normal_kai_00003（メインクエスト Normal）

#### このステージでの役割

3色全てのバリエーションが登場する複合構成ステージ。序盤はColorlessの小隊投入で防衛ラインを構築し、中盤からGreenの波状攻撃へ移行、後半にはYellowが2体登場して攻撃力ダウン妨害を加えるエスカレーション設計になっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_kai_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 25,000 | 350 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Green` | Normal | Attack | Green | 67,000 | 800 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Yellow` | Normal | Attack | Yellow | 220,000 | 1,000 | 45 | 0.2 | 1 |

**アビリティ・変身設定**: 全バリエーションともアビリティなし・変身なし

#### 攻撃パターン

上記「4. 攻撃パターン」を参照。

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 150
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 350 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 2] condition_type: ElapsedTime / condition_value: 1000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 3] condition_type: ElapsedTime / condition_value: 1025
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 4] condition_type: ElapsedTime / condition_value: 2000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 5] condition_type: ElapsedTime / condition_value: 2650
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 2 / summon_interval: 600 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 6] condition_type: FriendUnitDead / condition_value: 4
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 7] condition_type: ElapsedTime / condition_value: 2700
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 2 / summon_interval: 600 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 8] condition_type: ElapsedTime / condition_value: 2800
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 9] condition_type: FriendUnitDead / condition_value: 6
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 10] condition_type: ElapsedTime / condition_value: 3500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 700 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 11] condition_type: ElapsedTime / condition_value: 3600
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 700 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 12] condition_type: FriendUnitDead / condition_value: 9
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 13] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 800 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 14] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 15] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 16] condition_type: ElapsedTime / condition_value: 5500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 17] condition_type: ElapsedTime / condition_value: 6500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 99 / summon_interval: 750 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 18] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 0.9 / sequence_group_id: ""

[elem 19] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 3 / summon_interval: 800 / enemy_hp_coef: 0.9 / sequence_group_id: ""
```

序盤はElapsedTimeによるColorless小隊の時系列投入（elem 1〜3）、中盤からGreenとColorlessの混成波（elem 4〜13）、後半は12体撃破（FriendUnitDead:12）でYellowが出現しつつ継続的なGreen投入（elem 14〜17）でフィニッシュへ。拠点ダメージ（OutpostDamage:1）でもYellow追加投入（elem 18〜19）という圧力強化が入る。enemy_hp_coefはColorlessが1固定・GreenとYellowは0.9。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| （コマ効果なし） | — | — |

> koma1〜4 effect_type は全て None。コマ効果による難易度調整はなし。

---

### normal_kai_00004（メインクエスト Normal）

#### このステージでの役割

normal_kai_00003とほぼ同一のシーケンス構造を持つ難易度ステップアップ版。最大の違いはenemy_hp_coefが全バリエーションで1（normal_kai_00003のGreen・Yellowは0.9）になっており、HP補正が本来値になっている点。またelem 14でボスクラス（e_kai_00001_general_Boss_Yellow）が追加召喚されるため、ボス前の圧力として余獣が機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_kai_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 25,000 | 350 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Green` | Normal | Attack | Green | 67,000 | 800 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Yellow` | Normal | Attack | Yellow | 220,000 | 1,000 | 45 | 0.2 | 1 |

**アビリティ・変身設定**: 全バリエーションともアビリティなし・変身なし

#### 攻撃パターン

上記「4. 攻撃パターン」を参照。

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 150
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 350 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 2] condition_type: ElapsedTime / condition_value: 1000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 3] condition_type: ElapsedTime / condition_value: 1025
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 4] condition_type: ElapsedTime / condition_value: 2000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 5] condition_type: ElapsedTime / condition_value: 2650
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 2 / summon_interval: 600 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 6] condition_type: FriendUnitDead / condition_value: 4
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 7] condition_type: ElapsedTime / condition_value: 2700
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 2 / summon_interval: 600 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 8] condition_type: ElapsedTime / condition_value: 2800
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 9] condition_type: FriendUnitDead / condition_value: 6
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 10] condition_type: ElapsedTime / condition_value: 3500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 700 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 11] condition_type: ElapsedTime / condition_value: 3600
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 700 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 12] condition_type: FriendUnitDead / condition_value: 9
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 13] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 800 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 14] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy
  action_value: e_kai_00001_general_Boss_Yellow（他キャラ）
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 15] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 16] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 17] condition_type: ElapsedTime / condition_value: 5500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 3 / summon_interval: 500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 18] condition_type: ElapsedTime / condition_value: 6500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 99 / summon_interval: 750 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 19] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 20] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Yellow
  summon_count: 3 / summon_interval: 800 / enemy_hp_coef: 1 / sequence_group_id: ""
```

normal_kai_00003と同一の流れだがenemy_hp_coef が全て1となっており難易度が上昇。12体撃破時（FriendUnitDead:12）にボスキャラが同時召喚されるため、Yellow余獣がボス護衛役として機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| （コマ効果なし） | — | — |

> koma1〜4 effect_type は全て None。コマ効果による難易度調整はなし。

---

### normal_kai_00005（メインクエスト Normal）

#### このステージでの役割

ギミックオブジェクト（kai_honju_enemy）を活用したステージで、余獣はColorlessとGreenの2種のみが登場する。序盤はColorlessの継続投入で時間を稼ぎ、中盤以降はGreenが少量ずつ登場する構造。別キャラ（本獣・ボスキャラ）のギミック変換シーケンスが主軸のため、余獣は脇役的な位置づけ。summon_count: 99（無限）によるColorless連続投入が序盤の圧迫手段として機能している。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_kai_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 25,000 | 350 | 45 | 0.11 | 1 |
| `e_kai_00101_general_Normal_Green` | Normal | Attack | Green | 67,000 | 800 | 45 | 0.11 | 1 |

**アビリティ・変身設定**: 全バリエーションともアビリティなし・変身なし

#### 攻撃パターン

上記「4. 攻撃パターン」を参照。

#### シーケンス設定（余獣に関係する行のみ）

```
[elem 1] condition_type: ElapsedTime / condition_value: 300
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 2] condition_type: ElapsedTime / condition_value: 900
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 10 / summon_interval: 700 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 3] condition_type: ElapsedTime / condition_value: 1800
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 4] condition_type: ElapsedTime / condition_value: 2200
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 5] condition_type: ElapsedTime / condition_value: 2900
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 6] condition_type: ElapsedTime / condition_value: 3700
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 7] condition_type: ElapsedTime / condition_value: 2500
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 750 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 8] condition_type: ElapsedTime / condition_value: 4700
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 99 / summon_interval: 1500 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 9] condition_type: ElapsedTime / condition_value: 5200
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 10] condition_type: ElapsedTime / condition_value: 4200
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 1 / summon_interval: 900 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 22] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Green
  summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1 / sequence_group_id: ""
```

ギミック変換シーケンスが並走するため余獣は雑魚の継続投入役として機能。enemy_hp_coefは全て1で補正なし。summon_count: 99による連続投入で持続的な圧力を演出している。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| （コマ効果なし） | — | — |

> koma1〜4 effect_type は全て None。コマ効果による難易度調整はなし。

---

### normal_kai_00006（メインクエスト Normal）

#### このステージでの役割

フレンドユニット（c_kai_00001 等）が初期召喚されるプレイヤー支援型ステージ。余獣はColorlessのみが登場し、序盤の圧力を担う役割を果たす。summon_count: 99（無限）による大量投入が2波あり、フレンドユニットの撃破数増加に伴い別キャラへの移行があるシンプルな設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_kai_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 25,000 | 350 | 45 | 0.11 | 1 |

**アビリティ・変身設定**: アビリティなし・変身なし

#### 攻撃パターン

上記「4. 攻撃パターン」を参照。

#### シーケンス設定（余獣に関係する行のみ）

```
[elem 4] condition_type: ElapsedTime / condition_value: 600
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 600 / enemy_hp_coef: 1 / sequence_group_id: ""

[elem 5] condition_type: ElapsedTime / condition_value: 1100
  action_type: SummonEnemy
  action_value: e_kai_00101_general_Normal_Colorless
  summon_count: 99 / summon_interval: 1100 / enemy_hp_coef: 1 / sequence_group_id: ""
```

elem 1〜3 でフレンドユニット関連召喚（他キャラ）が設定されており、余獣は ElapsedTime で独立して無限投入される。2段階の間隔変化（600→1100フレーム）で徐々に間隔を空けていくデザイン。enemy_hp_coef: 1（補正なし）。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|----------|
| （コマ効果なし） | — | — |

> koma1〜4 effect_type は全て None。コマ効果による難易度調整はなし。
