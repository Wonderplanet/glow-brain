# 星野 ルビー（chara_osh_00201）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_osh_00201
> mst_series_id: osh
> 作品名: 【推しの子】

> **フィルタ適用注記**: 本ドキュメントは「normalクエストのNormal難易度のみ」のフィルタで生成を依頼されましたが、`chara_osh_00201`（星野 ルビー）はnormalクエスト（`normal_%`）のインゲームで使用実績がありません。イベント（6ステージ）および降臨バトル（1ステージ）のみに登場するため、全コンテンツのデータを対象に記載しています。

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_osh_00201` |
| mst_series_id | `osh` |
| 作品名 | 【推しの子】 |
| asset_key | `chara_osh_00201` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

星野 ルビーはイベント・降臨バトル専用のフレンドユニット（`c_` プレフィックス）として実装されており、normalクエストへの登場実績はない。コンテンツ種別はイベント6ステージ・降臨バトル1ステージの合計7ステージに登場する。

ステータス面では、コンテンツ用途や役割によって大きく異なる。Normalユニットとして使われる場合はHP 1,000〜50,000、Bossユニットとして使われる場合はHP 10,000〜50,000が設定されており、高難易度イベントやサベージイベントでは攻撃力300の強力な設定が採用される。移動速度は30〜37と幅があり、索敵距離は全バリエーションで0.22（一部0.3）と近接寄りの設定となっている。

role_typeは全バリエーションでAttack固定。変身設定は全パラメータでNone（変身なし）。アビリティ設定もなし。

シーケンス配置パターンとしては、ElapsedTime（経過時間）トリガーでの単体召喚が多く、降臨バトルではElapsedTimeSinceSequenceGroupActivatedを使ったウェーブ進行型の複雑な構成でも使われる。FriendUnitDead（フレンドユニット撃破）をトリガーとした登場パターンも多く、前の敵を倒した後に出現する連続戦タイプの設計が多い。

コマ効果は全登場ステージを通じてSlipDamage（スリップダメージ）が最も多く（10回）、Burn（炎上）・Poison（毒）も降臨バトルステージで1回ずつ使用されている。効果対象サイドはPlayerが基本となっており、プレイヤー側へのデバフを主眼に置いたステージ設計となっている。

---

## 3. ステージ別使用実態

### event_l05anniv_charaget01_00002（イベント）

#### このステージでの役割

周年記念イベント「キャラゲット」系コンテンツの2ステージ目。Normalユニット（Blue・HP 1,000）として配置され、ElapsedTimeトリガーで1体のみ出現するサブ的な役割。主役のBossキャラ（c_osh_00001系）と共に登場する脇役ポジション。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_l05anniv_charaget01_Normal_Blue` | Normal | Attack | Blue | 1,000 | 100 | 35 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_l05anniv_charaget01_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 250 | 50 | なし |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1250
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_osh_00201_l05anniv_charaget01_Normal_Blue
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 20
sequence_group_id: （空欄）
```

ElapsedTime 1250（約21秒）という比較的遅いタイミングで1体のみ登場。序盤のメインの敵が展開した後に追加で出現するサポート的な配置。

#### コマ効果

対象ステージでのコマ効果に該当するKomaLine行はなし（Noneのみ）。

---

### event_l05anniv_charaget01_00003（イベント）

#### このステージでの役割

周年記念イベント「キャラゲット」系コンテンツの3ステージ目。BossとNormal両方のユニットとして登場する。Bossとして開幕後1000フレームで出現し、さらにFriendUnitDead（フレンドユニット撃破数2体）条件でもNormalとして追加出現するという二段構えの配置。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_l05anniv_charaget01_Boss_Blue` | Boss | Attack | Blue | 10,000 | 100 | 35 | 0.22 | 2 |
| `c_osh_00201_l05anniv_charaget01_Normal_Blue` | Normal | Attack | Blue | 1,000 | 100 | 35 | 0.22 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_l05anniv_charaget01_Boss_Blue` | なし | None | なし | なし |
| `c_osh_00201_l05anniv_charaget01_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン（Boss）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Appearance攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 250 | 50 | なし |

#### シーケンス設定

```
（Boss登場）
condition_type: ElapsedTime
condition_value: 1000
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_osh_00201_l05anniv_charaget01_Boss_Blue
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 8
sequence_group_id: （空欄）

（Normal追加登場）
condition_type: FriendUnitDead
condition_value: 2
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_osh_00201_l05anniv_charaget01_Normal_Blue
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 25
sequence_group_id: （空欄）
```

ElapsedTime 1000（約17秒）でBossが登場し、さらに2体撃破後にNormalが追加出現する二段構え。enemy_hp_coefが25と高めに設定されたNormalが後半に登場するため、終盤まで戦いが続く設計。

#### コマ効果

対象ステージでのコマ効果に該当するKomaLine行はなし（Noneのみ）。

---

### event_osh1_challenge01_00002（イベント）

#### このステージでの役割

osh1イベントのチャレンジ2ステージ目。Bossユニット（Red・HP 50,000・攻撃力300）として高難易度コンテンツの主力として配置。FriendUnitDead 1体撃破をトリガーに登場するため、序盤の敵を突破した先に待ち受けるメインボス的な位置づけ。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_challenge_Boss_Red` | Boss | Attack | Red | 50,000 | 300 | 30 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_challenge_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Appearance攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 500 | 50 | なし |

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 4
action_type: SummonEnemy
action_value: c_osh_00201_challenge_Boss_Red
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 4.7
sequence_group_id: （空欄）
```

序盤のコモン敵（e_glo_00002_charaget_repeat系）1体を倒した後に登場するボス。enemy_hp_coef 4.7というHPブースト付きで出現するため、実質HP 235,000相当の耐久力となる非常に手強いボスとして機能する。

#### コマ効果

対象ステージでのコマ効果に該当するKomaLine行はなし（Noneのみ）。

---

### event_osh1_charaget01_00001（イベント）

#### このステージでの役割

osh1イベントのキャラゲット1ステージ目。Bossユニット（Colorless・HP 10,000・攻撃力300）として、ElapsedTime 1000フレームで登場する。コモン敵が初期召喚される中、後から追いかけるように出現するボスとして機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_charaget_repeat_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 300 | 30 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_charaget_repeat_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Appearance攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 500 | 50 | なし |

#### シーケンス設定

```
condition_type: ElapsedTime
condition_value: 1000
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_osh_00201_charaget_repeat_Boss_Colorless
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3.1
sequence_group_id: （空欄）
```

ElapsedTime 1000フレーム（約17秒）で登場するボス。c_osh_00401_charaget_repeat_Boss_Colorless（1100フレームに登場）と前後して出現し、2体のBossが時間差で来る波状攻撃型の設計。enemy_hp_coef 3.1付与で実質HP 31,000相当。

#### コマ効果

対象ステージでのコマ効果に該当するKomaLine行はなし（Noneのみ）。

---

### event_osh1_savage_00002（イベント）

#### このステージでの役割

osh1サベージイベントの2ステージ目。Normalユニット（Blue・HP 50,000・攻撃力300）として、FriendUnitDead撃破条件のウェーブが切り替わった後のシーケンスグループ `w1` の中盤に登場する。同じグループ内でc_osh_00301、c_osh_00401とともに3体の強力なキャラが時間差で出現し、プレイヤーを圧倒するサベージ設計の中核を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_osh1savage02_Normal_Blue` | Normal | Attack | Blue | 50,000 | 300 | 35 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_osh1savage02_Normal_Blue` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 500 | 50 | なし |

#### シーケンス設定

```
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 300
sequence_element_id: 7
action_type: SummonEnemy
action_value: c_osh_00201_osh1savage02_Normal_Blue
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 10
sequence_group_id: w1
```

FriendUnitDead 1体で `w1` グループへ切り替わり、グループアクティブ後300フレームで登場。enemy_hp_coef 10付与で実質HP 500,000という非常に高耐久なサベージ仕様。グループ内でc_osh_00301（700フレーム）・c_osh_00401（1100フレーム）と連続して追加登場する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 10 | Player |

（2行のKomaLineデータ: 1行は koma1+koma2=SlipDamage/Player、1行は koma1=SlipDamage/Player）

---

### event_osh1_savage_00003（イベント）

#### このステージでの役割

osh1サベージイベントの3ステージ目。FriendUnitDead 26体（シーケンスelement 26の撃破）を条件に登場するNormalユニット（Green・HP 50,000・攻撃力300）。3ステージ中最終盤に登場するキャラの一角で、c_osh_00401（22体撃破後）、c_osh_00301（24体撃破後）に続く段階的な強敵ラッシュの第3弾として配置されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_osh1savage03_Normal_Green` | Normal | Attack | Green | 50,000 | 300 | 35 | 0.22 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_osh1savage03_Normal_Green` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Normal攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.23 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.23 | 1 | Self | None | Normal | 100.0% | AttackPowerUp | 500 | 50 | なし |

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 26
sequence_element_id: 26
action_type: SummonEnemy
action_value: c_osh_00201_osh1savage03_Normal_Green
summon_position: （空欄）
summon_count: 1
summon_interval: 0
enemy_hp_coef: 12
sequence_group_id: （空欄）
```

FriendUnitDead 26体という条件は、シーケンスの後半で積み上げた多数の敵を倒した後に出現することを意味し、enemy_hp_coef 12付与で実質HP 600,000のサベージ仕様。同一の撃破トリガー26でコモン敵の大量追加召喚も発動するため、ルビーを倒す難易度が大幅に高まる設計。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| SlipDamage | 8 | Player |

（2行のKomaLineデータ: 1行は koma2+koma3=SlipDamage/Player、1行は koma1+koma2+koma3+koma4=SlipDamage/Player）

---

### raid_osh1_00001（降臨バトル）

#### このステージでの役割

osh1降臨バトルの1ステージ目。Normal（初期召喚）とBoss（ウェーブ`w3`）の両方のユニットとして使用される。Normalとして初期召喚（position: 2.7・HP 3倍）で最初から配置されており、ゲーム開始直後から脅威として機能する。Bossとしてはウェーブ `w3`（ElapsedTimeSinceSequenceGroupActivated 0）で出現し、HP 15倍という強力な降臨ボスとして待ち構える。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00201_osh1_advent_Normal_Red` | Normal | Attack | Red | 1,000 | 100 | 37 | 0.22 | 1 |
| `c_osh_00201_osh1_advent_Boss_Red` | Boss | Attack | Red | 10,000 | 100 | 31 | 0.22 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00201_osh1_advent_Normal_Red` | なし | None | なし | なし |
| `c_osh_00201_osh1_advent_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン（Normal）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Normal攻撃 - Normal用）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.3 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃 - Normal用）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.3 | 1 | Foe | None | Normal | 0.0% | None | 0 | 0 | なし |
| 2 | Direct | 0.0 | 1 | Self | None | Normal | 0.0% | AttackPowerUp | 500 | 100 | なし |

#### 攻撃パターン（Boss）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 69 |
| Special | 0 | なし | なし | 150 |

**MstAttackElement詳細（Appearance攻撃 - Boss用）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |

**MstAttackElement詳細（Normal攻撃 - Boss用）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|
| 1 | Direct | 0.3 | 1 | Foe | Damage | Normal | 50.0% | None |
| 2 | Direct | 0.3 | 1 | Foe | Damage | Normal | 50.0% | None |

**MstAttackElement詳細（Special攻撃 - Boss用）**

| sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type | effective_duration | effect_parameter | effect_value |
|-----------|------------|--------------------|-----------------|---------|-----------|---------|-----------------|-----------|--------------------|-----------------|-------------|
| 1 | Direct | 0.3 | 1 | Foe | None | Normal | 0.0% | None | 0 | 0 | なし |
| 2 | Direct | 0.0 | 1 | Self | None | Normal | 0.0% | AttackPowerUp | 500 | 200 | なし |

#### シーケンス設定

```
（Normal - 初期召喚）
condition_type: InitialSummon
condition_value: 0
sequence_element_id: 3
action_type: SummonEnemy
action_value: c_osh_00201_osh1_advent_Normal_Red
summon_position: 2.7
summon_count: 1
summon_interval: 0
enemy_hp_coef: 3
sequence_group_id: （空欄）

（Normal - ウェーブw5再登場）
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 250
sequence_element_id: 38
action_type: SummonEnemy
action_value: c_osh_00201_osh1_advent_Normal_Red
summon_position: 2.7
summon_count: 1
summon_interval: 0
enemy_hp_coef: 350
sequence_group_id: w5

（Boss - ウェーブw3で登場）
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 0
sequence_element_id: 21
action_type: SummonEnemy
action_value: c_osh_00201_osh1_advent_Boss_Red
summon_position: 2.5
summon_count: 1
summon_interval: 0
enemy_hp_coef: 15
sequence_group_id: w3
```

初期召喚でNormal（HP倍率3）として登場した後、ウェーブw3でBoss（HP倍率15）として再登場する2段階型の出演構成。さらにウェーブw5でNormal（HP倍率350）として再々登場し、降臨バトルのボリュームある長期戦設計を支えている。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド(effect_target_side) |
|-----------|---------|------------------------------|
| Burn | 1 | Player |
| Poison | 1 | Player |
| SlipDamage | 1 | Player |

（KomaLine 1行: koma1=Burn/Player、koma2=Poison/Player、koma3=SlipDamage/Player）

---
