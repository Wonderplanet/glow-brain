# 醜鬼（enemy_sur_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_sur_00101
> mst_series_id: sur
> 作品名: 魔都精兵のスレイブ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_sur_00101` |
| mst_series_id | `sur` |
| 作品名 | 魔都精兵のスレイブ |
| asset_key | `enemy_sur_00101` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

醜鬼はメインクエスト Normal（魔都精兵のスレイブシリーズ）の全5ステージで使用される汎用敵キャラクターである。Normalユニットとして3色（Colorless・Blue・Green）＋Bossバリエーション1種が用意されており、ステージの難易度が上がるにつれてBossバリエーションが登場する構成となっている。

Normal難易度のHPレンジは Colorless（3,000）< Blue（15,000）< Green（22,000）と色によって大きく差があり、Colorless は盾役（Defense）として前衛に立ちつつHP消耗を吸収する役割を担う。Blue・Green は攻撃役（Attack）として継続召喚圧をかけてくる。Boss バリエーションは HP 400,000・攻撃力 850 と大幅に強化されており、登場自体が1体であっても強烈な圧力源となる。

変身設定は全バリエーションで「なし（None）」であり、シンプルなユニット構成。アビリティも設定なし。

コマ効果は normal_sur_00001 のみ「AttackPowerDown（Player側）」が1コマに設定されており、他ステージはほぼコマ効果なし。コマ効果による難易度調整は最小限で、シーケンス設計で難易度を制御している。

出現シーケンスは全ステージ共通で ElapsedTime（時間経過）が主体で、FriendUnitDead（味方死亡トリガー）との組み合わせにより圧力を倍増させる設計が特徴的。後半ステージ（normal_sur_00004・00005）では OutpostDamage や summon_count=99 による無限召喚でゴール突破を狙ってくる。

---

## 3. ステージ別使用実態

### normal_sur_00001（メインクエスト Normal）

#### このステージでの役割

序盤ステージ。Colorless（Defense）が開幕から盾役として召喚され、その後 Blue・Green の Attack ユニットが段階的に投入される。FriendUnitDead トリガーにより撃破するほど追加敵が湧く仕組みで、プレイヤーへの継続プレッシャーを学習させる設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sur_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 3,000 | 100 | 35 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Blue` | Normal | Attack | Blue | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Green` | Normal | Attack | Green | 22,000 | 300 | 50 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sur_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |

**MstAttackElement（Normal攻撃）**

| mst_attack_id | attack_type | range_end_parameter | damage_type | hit_type | power_parameter | effect_type |
|---------------|-------------|---------------------|-------------|----------|-----------------|-------------|
| `e_sur_00101_general_Normal_Colorless_Normal_00000` | Direct | 0.3 | Damage | Normal | 100.0% | None |
| `e_sur_00101_general_Normal_Blue_Normal_00000` | Direct | 0.3 | Damage | Normal | 100.0% | None |
| `e_sur_00101_general_Normal_Green_Normal_00000` | Direct | 0.3 | Damage | Normal | 100.0% | None |

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 200
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 200 / enemy_hp_coef: 1

[elem 2] condition_type: ElapsedTime / condition_value: 1000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 4 / summon_interval: 50 / enemy_hp_coef: 1

[elem 3] condition_type: ElapsedTime / condition_value: 1300
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 0 / enemy_hp_coef: 1

[elem 4] condition_type: FriendUnitDead / condition_value: 3
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 50 / enemy_hp_coef: 1

[elem 5] condition_type: ElapsedTime / condition_value: 1500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 6] condition_type: FriendUnitDead / condition_value: 5
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 7] condition_type: ElapsedTime / condition_value: 2500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 5 / summon_interval: 250 / enemy_hp_coef: 1

[elem 8] condition_type: ElapsedTime / condition_value: 3200
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 500 / enemy_hp_coef: 1

[elem 9] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 500 / enemy_hp_coef: 1

[elem 10] condition_type: ElapsedTime / condition_value: 4500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 11] condition_type: ElapsedTime / condition_value: 4900
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 700 / enemy_hp_coef: 1

[elem 12] condition_type: ElapsedTime / condition_value: 5800
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 13] condition_type: ElapsedTime / condition_value: 6600
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 750 / enemy_hp_coef: 1

[elem 14] condition_type: ElapsedTime / condition_value: 7000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 15] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 750 / enemy_hp_coef: 1
```

序盤は Colorless で盾を張り、徐々に Blue・Green を投入していく典型的な波状攻撃パターン。アウトポスト被ダメ時に Green 無限召喚でゴール突破を狙う設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| AttackPowerDown | 1 | Player |

> 1つのコマラインにのみ AttackPowerDown が設定されており、プレイヤー側の攻撃力を低下させる効果。

---

### normal_sur_00002（メインクエスト Normal）

#### このステージでの役割

中盤ステージ。Colorless が開幕直後から大量召喚され、Green が後半の主力として大量ウェーブを形成する。FriendUnitDead と ElapsedTime の両方を組み合わせて隙間なく敵を送り込む設計で、継続的なリソース消耗を狙う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sur_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 3,000 | 100 | 35 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Blue` | Normal | Attack | Blue | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Green` | Normal | Attack | Green | 22,000 | 300 | 50 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sur_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 5 / summon_interval: 250 / enemy_hp_coef: 1

[elem 2] condition_type: ElapsedTime / condition_value: 800
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 100 / enemy_hp_coef: 1

[elem 3] condition_type: ElapsedTime / condition_value: 1300
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 4] condition_type: FriendUnitDead / condition_value: 3
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 4 / summon_interval: 50 / enemy_hp_coef: 1

[elem 5] condition_type: FriendUnitDead / condition_value: 3
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 350 / enemy_hp_coef: 1

[elem 6] condition_type: ElapsedTime / condition_value: 1700
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 7] condition_type: FriendUnitDead / condition_value: 6
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 50 / enemy_hp_coef: 1

[elem 8] condition_type: ElapsedTime / condition_value: 2500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 9] condition_type: ElapsedTime / condition_value: 3000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 10] condition_type: FriendUnitDead / condition_value: 9
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 11] condition_type: FriendUnitDead / condition_value: 8
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 12] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 13] condition_type: FriendUnitDead / condition_value: 12
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 14] condition_type: ElapsedTime / condition_value: 5600
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 15] condition_type: FriendUnitDead / condition_value: 14
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 50 / enemy_hp_coef: 1

[elem 16] condition_type: ElapsedTime / condition_value: 6100
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 17] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1
```

FriendUnitDead のトリガー値が細かく設定されており（3、6、8、9、12、14）、撃破のたびに Green が補充される。アウトポスト被ダメ時にも Green の無限召喚が発動する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |

> コマ効果の設定なし（全コマ None）。

---

### normal_sur_00003（メインクエスト Normal）

#### このステージでの役割

中盤ステージ。Blue が開幕主力となり、Colorless が防衛役として交互に召喚される。後半から Green が大量投入され、アウトポスト被ダメ時には99体無限召喚が2系統発動する厳しい設計。一部コマに summon_position が指定されており、特定位置への出現パターンが含まれる。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sur_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 3,000 | 100 | 35 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Blue` | Normal | Attack | Blue | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Green` | Normal | Attack | Green | 22,000 | 300 | 50 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sur_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 150
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 300 / enemy_hp_coef: 1

[elem 2] condition_type: ElapsedTime / condition_value: 1000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 3] condition_type: ElapsedTime / condition_value: 1500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 50 / enemy_hp_coef: 1

[elem 4] condition_type: FriendUnitDead / condition_value: 3
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 5] condition_type: FriendUnitDead / condition_value: 3
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 6] condition_type: ElapsedTime / condition_value: 2800
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 7] condition_type: ElapsedTime / condition_value: 2700
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 8] condition_type: FriendUnitDead / condition_value: 6
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 100 / enemy_hp_coef: 1

[elem 9] condition_type: FriendUnitDead / condition_value: 7
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 100 / enemy_hp_coef: 1

[elem 10] condition_type: ElapsedTime / condition_value: 3200
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: 1.3 / summon_count: 3 / summon_interval: 100 / enemy_hp_coef: 1

[elem 11] condition_type: ElapsedTime / condition_value: 3600
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 500 / enemy_hp_coef: 1

[elem 12] condition_type: ElapsedTime / condition_value: 3000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 600 / enemy_hp_coef: 1

[elem 13] condition_type: ElapsedTime / condition_value: 4500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: 1.3 / summon_count: 3 / summon_interval: 100 / enemy_hp_coef: 1

[elem 14] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 800 / enemy_hp_coef: 1

[elem 15] condition_type: ElapsedTime / condition_value: 5500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 16] condition_type: ElapsedTime / condition_value: 2500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 10 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 17] condition_type: ElapsedTime / condition_value: 5700
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: 1.3 / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 18] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 750 / enemy_hp_coef: 1

[elem 19] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1
```

summon_position=1.3 の指定が Colorless と Blue の一部に使用されており、特定コマ位置への強制出現が設計されている。アウトポスト被ダメ時の Green 無限召喚が2系統で同時発動する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |

> コマ効果の設定なし（全コマ None）。

---

### normal_sur_00004（メインクエスト Normal）

#### このステージでの役割

ボス初登場ステージ。Colorless・Blue が序盤から複数回召喚されて防衛線を張り、8体撃破を条件に Boss Green（HP 400,000）が登場するFriendUnitDeadトリガー設計。アウトポスト被ダメ時には summon_count=99 かつ enemy_hp_coef=2.8〜2.9 の強化 Green が無限召喚される厳しい後半仕様。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sur_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 3,000 | 100 | 35 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Blue` | Normal | Attack | Blue | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Green` | Normal | Attack | Green | 22,000 | 300 | 50 | 0.2 | 1 |
| `e_sur_00101_general_Boss_Green` | Boss | Attack | Green | 400,000 | 850 | 45 | 0.2 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sur_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Green` | なし | None | なし | なし |
| `e_sur_00101_general_Boss_Green` | なし | None | なし | なし |

#### 攻撃パターン

**Normalユニット（Colorless・Blue・Green）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 40 |

**Bossユニット（Boss_Green）**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 40 |

**Bossの攻撃Element詳細**

| mst_attack_id | attack_type | hit_type | damage_type | power_parameter | 備考 |
|---------------|-------------|----------|-------------|-----------------|------|
| `e_sur_00101_general_Boss_Green_Appearance_00001` | Direct | ForcedKnockBack5 | None | 0.0% | 登場時広範囲ノックバック（range_end: 50.0） |
| `e_sur_00101_general_Boss_Green_Normal_00000` | Direct | Normal | Damage | 100.0% | 通常攻撃 |
| `e_sur_00101_general_Boss_Green_Normal_00000` | Direct | Stun | Damage | 10.0% | スタン付与攻撃（2段目） |

#### シーケンス設定

```
[elem 1] condition_type: ElapsedTime / condition_value: 200
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 2] condition_type: ElapsedTime / condition_value: 250
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 3] condition_type: ElapsedTime / condition_value: 700
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 3 / summon_interval: 50 / enemy_hp_coef: 1

[elem 4] condition_type: ElapsedTime / condition_value: 1000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 100 / enemy_hp_coef: 1

[elem 5] condition_type: ElapsedTime / condition_value: 1500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 6] condition_type: ElapsedTime / condition_value: 1800
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 7] condition_type: ElapsedTime / condition_value: 2500
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 8] condition_type: ElapsedTime / condition_value: 2550
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 9] condition_type: FriendUnitDead / condition_value: 8
  action_type: SummonEnemy / action_value: e_sur_00101_general_Boss_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 10] condition_type: ElapsedTime / condition_value: 3300
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 800 / enemy_hp_coef: 1

[elem 11] condition_type: ElapsedTime / condition_value: 4000
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 12] condition_type: ElapsedTime / condition_value: 4200
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 13] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 2.9 / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 14] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 2.8 / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1
```

8体撃破（FriendUnitDead=8）で Boss Green が1体登場する。アウトポスト被ダメ後の Green は enemy_hp_coef=2.8〜2.9 で強化されており、HP換算で約61,600〜63,800 相当になる。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |

> コマ効果の設定なし（全コマ None）。

---

### normal_sur_00005（メインクエスト Normal）

#### このステージでの役割

最終ステージ。InitialSummon（コマ初期配置）でフィールド内の特定位置に Colorless・Blue が複数体配置されており、ステージ開始時点から敵が盤面に存在する特殊設計。その後 ElapsedTime でさらに追加召喚され、FriendUnitDead による Green 大量追加、アウトポスト被ダメで enemy_hp_coef=3.8〜3.9（実質HP 83,600〜85,800相当）の超強化 Green 無限召喚が発動する最高難度設定。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_sur_00101_general_Normal_Colorless` | Normal | Defense | Colorless | 3,000 | 100 | 35 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Blue` | Normal | Attack | Blue | 15,000 | 200 | 40 | 0.2 | 1 |
| `e_sur_00101_general_Normal_Green` | Normal | Attack | Green | 22,000 | 300 | 50 | 0.2 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_sur_00101_general_Normal_Colorless` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Blue` | なし | None | なし | なし |
| `e_sur_00101_general_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |
| Normal | 0 | なし | なし | 40 |

#### シーケンス設定

```
[elem 1] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: 0.8 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 2] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: 1.9 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 3] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: 1.8 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 4] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: 2.9 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 5] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: 2.8 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 6] condition_type: InitialSummon / condition_value: 2
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: 2.85 / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 7] condition_type: ElapsedTime / condition_value: 100
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Colorless
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 700 / enemy_hp_coef: 1

[elem 9] condition_type: FriendUnitDead / condition_value: 8
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Blue
  summon_position: （デフォルト） / summon_count: 99 / summon_interval: 500 / enemy_hp_coef: 1

[elem 10] condition_type: FriendUnitDead / condition_value: 8
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: （デフォルト） / summon_count: 1 / summon_interval: 0 / enemy_hp_coef: 1

[elem 12] condition_type: FriendUnitDead / condition_value: 10
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 3.8 / summon_count: 2 / summon_interval: 50 / enemy_hp_coef: 1

[elem 13] condition_type: FriendUnitDead / condition_value: 10
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 3.9 / summon_count: 99 / summon_interval: 800 / enemy_hp_coef: 1

[elem 14] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 3.8 / summon_count: 99 / summon_interval: 1200 / enemy_hp_coef: 1

[elem 15] condition_type: OutpostDamage / condition_value: 1
  action_type: SummonEnemy / action_value: e_sur_00101_general_Normal_Green
  summon_position: 3.9 / summon_count: 99 / summon_interval: 1500 / enemy_hp_coef: 1
```

InitialSummon でコマ上の6か所に Colorless×2・Blue×4 を初期配置する唯一のステージ。enemy_hp_coef=3.8〜3.9 の超強化 Green が OutpostDamage と FriendUnitDead 10体の両方から発動し、最終ステージに相応しい高い難易度となっている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| なし | - | - |

> コマ効果の設定なし（全コマ None）。
