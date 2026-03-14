# MstAutoPlayerSequence — condition_type 設定パターン解説

**分析日**: 2026-03-14
**分析対象**: 全インゲームデータ（MstAutoPlayerSequence.csv、有効レコードのみ）
**除外**: `InitialSummon`、`ElapsedTime`

---

## condition_type 一覧と件数

| condition_type | 件数 | 主なコンテンツ |
|---|---|---|
| FriendUnitDead | 1,463 | 全コンテンツ |
| ElapsedTimeSinceSequenceGroupActivated | 603 | Savage系、BootGet02系 |
| EnterTargetKomaIndex | 229 | Challenge系、Savage系 |
| OutpostHpPercentage | 155 | CharaGet系、Challenge系 |
| OutpostDamage | 136 | Challenge系、Savage系 |
| DarknessKomaCleared | 41 | メインクエスト（dan作品） |
| FriendUnitTransform | 20 | メインクエスト（dan作品） |
| FriendUnitSummoned | 9 | Savage系 |

---

## 1. FriendUnitDead

### 概要

**「フレンドユニットの累計撃破数がcondition_valueに達した時点で発動」**。
最も広く使われるcondition_type（全体の約57%）。バトル開始前から有効で、撃破数が蓄積されるたびに対応するレコードの発動条件が充足される。

### condition_value の意味

`condition_value` = 「このレコードが発動する、フレンドユニットの累計撃破数」

| condition_value | 意味 |
|---|---|
| 0 | バトル開始時点で即時発動（0体撃破）|
| 1 | 1体目が撃破された瞬間に発動 |
| N | N体目が撃破された瞬間に発動 |
| 1500 | 累計1500体撃破（大規模Savageコンテンツ向け） |

### 具体例

**例1: 1体撃破で次のボスを召喚（基本パターン）**

```
sequence_set_id: normal_glo3_00001
condition_type:  FriendUnitDead
condition_value: 1
action_type:     SummonEnemy
action_value:    c_sur_00301_general_as_Normal_Yellow
enemy_hp_coef:   0.9
enemy_attack_coef: 1.0
summon_count:    1
```

解説: 1体のフレンドユニットが撃破されたら、次のボスを召喚。「1体倒すと次が来る」直線的な進行。

---

**例2: 複数体撃破で段階的に次のウェーブへ切り替え（SwitchSequenceGroup）**

```
sequence_set_id:    event_jig1_savage_00001
sequence_group_id:  （空）
condition_type:     FriendUnitDead
condition_value:    2
action_type:        SwitchSequenceGroup
action_value:       w1
action_delay:       50
```

解説: 2体撃破でグループ `w1` へ移行。撃破が進むたびに `w1→w2→w3→w4` と段階的にウェーブを切り替える設計（Savage系で多用）。

---

**例3: 段階的に蓄積されるボス召喚**

```
sequence_set_id:  event_aya1_charaget02_00004
condition_type:   FriendUnitDead
condition_value:  1
action_type:      SummonEnemy
action_value:     c_aya_00101_aya1_charaget02_Boss_Green
enemy_hp_coef:    15
enemy_attack_coef: 2.5
summon_count:     1

---

condition_value:  2
action_value:     c_aya_00001_aya1_charaget02_Normal_Green
enemy_hp_coef:    8
enemy_attack_coef: 5.0
```

解説: `condition_value=1` でボスAを召喚、`condition_value=2` でノーマル敵を追加召喚する多段構成。

---

**例4: condition_value=0 — バトル開始直後に即時発動**

```
sequence_set_id:  event_yuw1_savage_00001
condition_type:   FriendUnitDead
condition_value:  0
action_type:      SummonEnemy
action_value:     e_glo_00001_savageyuwburn_Normal_Red
enemy_hp_coef:    0.45
enemy_attack_coef: 2.6
summon_count:     40
summon_interval:  800
```

解説: 撃破数0（＝バトル開始時）で即発動。大量の雑魚敵を一斉に出す設計。condition_value=0 は稀なパターン（1件のみ確認）。

---

**例5: 大量撃破カウンターで強化敵が出現（高難易度のエスカレート）**

```
sequence_set_id:  normal_glo3_00001
condition_type:   FriendUnitDead
condition_value:  4
action_type:      SummonEnemy
action_value:     e_glo_00001_general_rik_vh_Normal_Yellow
enemy_hp_coef:    0.1
enemy_attack_coef: 0.2
summon_count:     99
summon_interval:  1200
```

解説: 4体撃破後、大量の雑魚敵が無限湧きに近い状態で出現。プレッシャーを高める演出。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,hard_glo3_00001_2,hard_glo3_00001,,2,,FriendUnitDead,1,SummonEnemy,c_sur_00301_general_as_Normal_Yellow,,1,0,Fall,2.8,None,,None,,None,,,,,Boss,Normal,2.5,1.5,1,300,0,250,None,,202509010
```

---

## 2. ElapsedTimeSinceSequenceGroupActivated

### 概要

**「sequence_group がアクティブになってからの経過時間（ミリ秒）がcondition_valueに達したら発動」**。
`sequence_group_id` が設定されているレコード専用のcondition_type。Savage（討伐）系コンテンツでウェーブ内の時間経過による出現制御に使う。

### condition_value の意味

`condition_value` = 「そのグループがアクティブになってからの経過ミリ秒数」
`0` は「グループ切り替え直後に即時発動」。

| 値 | 実時間換算 |
|---|---|
| 0 | グループ開始直後 |
| 300 | 0.3秒後 |
| 500 | 0.5秒後 |
| 1000 | 1.0秒後 |
| 2000 | 2.0秒後 |

### 具体例

**例1: グループ切り替え直後に即時召喚（condition_value=0）**

```
sequence_set_id:    event_jig1_savage_00001
sequence_group_id:  w1
condition_type:     ElapsedTimeSinceSequenceGroupActivated
condition_value:    0
action_type:        SummonEnemy
action_value:       c_jig_00401_jig1_savage_Normal_Colorless
enemy_hp_coef:      80
enemy_attack_coef:  7
summon_count:       1
```

解説: `w1` グループが始まった瞬間に固定ボスを召喚。ウェーブの「顔」となる敵を先出しする典型パターン。

---

**例2: グループ開始後0.3秒・0.5秒・1.5秒で段階的に追加召喚**

```
# 0.3秒後
sequence_group_id:  w1
condition_type:     ElapsedTimeSinceSequenceGroupActivated
condition_value:    300
action_value:       e_jig_00001_jig1_savage_Normal_Yellow
summon_count:       3
summon_interval:    750

# 0.5秒後
condition_value:    500
action_value:       e_jig_00001_jig1_savage_Normal_Colorless
summon_count:       3
summon_interval:    750

# 1.5秒後
condition_value:    1500
action_value:       e_jig_00001_jig1_savage_Normal_Yellow
summon_count:       3
summon_interval:    1200
```

解説: グループ開始後に時間を分散して複数の敵を段階召喚。プレイヤーに適度なインターバルを与えながら敵密度を上げる。

---

**例3: グループの最後で次グループへ切り替え**

```
sequence_set_id:    event_jig1_savage_00001
sequence_group_id:  w4
condition_type:     ElapsedTimeSinceSequenceGroupActivated
condition_value:    2000
action_type:        SwitchSequenceGroup
action_value:       w1
```

解説: `w4` グループが2秒続いたら `w1` に戻る（ループ）。この場合 `FriendUnitDead` による `SwitchSequenceGroup` と組み合わせて「撃破数 or タイムアウト」どちらかで次ウェーブへ進む設計になっている。

---

**例4: 時間差で複数のボスキャラを押し寄せる**

```
sequence_group_id:  w1
condition_value:    1000
action_value:       c_kim_00101_kim1_savage01_Boss_Blue
enemy_hp_coef:      6
summon_count:       1

condition_value:    900
action_value:       c_kim_00201_kim1_savage01_Boss_Blue
enemy_hp_coef:      7
summon_count:       1
```

解説: 0.9秒後・1.0秒後に異なるボスを同時期に出現させる。少しタイミングをずらすことで圧力を演出。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_jig1_savage_00001_7,event_jig1_savage_00001,w1,5,,ElapsedTimeSinceSequenceGroupActivated,0,SummonEnemy,c_jig_00401_jig1_savage_Normal_Colorless,,1,0,None,,None,,None,,None,,,,,,Default,Normal,80,7,1,100,0,,None,,202601010
```

---

## 3. EnterTargetKomaIndex

### 概要

**「プレイヤーのコマがターゲットコマインデックス（コマ配置列）に到達・通過したとき発動」**。
condition_value はコマのインデックス番号（0〜7）を指す。CharaGet02（挑戦状）や特定のSavageで使用。

### condition_value の意味

`condition_value` = 「発動するコマ配置インデックス番号（0始まり）」

| condition_value | 使用件数 | 用途感 |
|---|---|---|
| 0 | 26 | 先頭コマ到達 |
| 1 | 29 | 1つ目コマ |
| 2 | 39 | 中間コマ |
| 3 | 65 | 最多。中盤コマ |
| 4 | 26 | 後半コマ |
| 5 | 32 | 後半コマ |
| 6 | 4 | 最終コマ付近 |
| 7 | 8 | 最終コマ付近 |

### 具体例

**例1: 序盤コマ到達でボス先行召喚（condition_value=0）**

```
sequence_set_id:  event_dan1_charaget01_00002
condition_type:   EnterTargetKomaIndex
condition_value:  0
action_type:      SummonEnemy
action_value:     c_dan_00101_bbaget_Boss_Blue
enemy_hp_coef:    1.5
enemy_attack_coef: 2.0
summon_count:     1
```

解説: 最初のコマ（インデックス0）に到達したらすぐボスを召喚。バトル開始直後にボスが現れる即戦闘スタートを演出。

---

**例2: 中盤コマ（インデックス3）で複数の敵が一斉に出現**

```
sequence_set_id:  event_aya1_charaget02_00006
condition_type:   EnterTargetKomaIndex
condition_value:  3
action_type:      SummonEnemy
action_value:     c_aya_00101_aya1_charaget02_Normal_Green
enemy_hp_coef:    15
enemy_attack_coef: 3.0
summon_position:  2.7
summon_count:     1

condition_value:  3
action_value:     c_aya_00201_aya1_charaget02_Normal_Green
enemy_hp_coef:    11
summon_position:  2.6

condition_value:  3
action_value:     c_aya_00301_aya1_charaget02_Normal_Green
enemy_hp_coef:    10
summon_position:  2.5
```

解説: 同じ `condition_value=3` に複数レコードを設定し、1コマで3体を一斉召喚。InboundCharaGet02の典型的な「中盤集中攻撃」パターン。

---

**例3: コマ5到達でボスを強化してリスポーン**

```
sequence_set_id:  event_dan1_charaget02_00005
condition_type:   EnterTargetKomaIndex
condition_value:  3
action_type:      SummonEnemy
action_value:     e_dan_00301_airaget_Boss_Colorless
enemy_hp_coef:    1.75
enemy_attack_coef: 1.4
summon_count:     1
```

解説: コマインデックス3到達で通常より強化された敵を出現。「このコマラインを超えると敵が強くなる」演出。

---

**例4: 後半コマ（インデックス6）で最強ボスが落下**

```
sequence_set_id:  event_aya1_charaget02_00006
condition_type:   EnterTargetKomaIndex
condition_value:  6
action_type:      SummonEnemy
action_value:     e_aya_00001_aya1_charaget02_Boss_Green
summon_animation_type: （Fall等）
summon_position:  2.5
enemy_hp_coef:    12
enemy_attack_coef: 6.0
summon_count:     1
```

解説: 後半の難しいコマに到達したときに最強ボスを召喚。コマが奥に進むほど敵が強くなる緊張感を演出。

---

**例5: Savage系でコマ到達に連動してSummon**

```
sequence_set_id:  event_dan1_savage_00001
condition_type:   EnterTargetKomaIndex
condition_value:  2
action_type:      SummonEnemy
action_value:     e_glo_00001_savage_Normal_Red
enemy_hp_coef:    2.5
enemy_attack_coef: 2.1
summon_count:     10
summon_interval:  400
```

解説: Savage系でもコマインデックス到達トリガーを使う例。コマ位置2を超えると雑魚10体が出現し始める。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_aya1_charaget02_00006_1,event_aya1_charaget02_00006,,1,,EnterTargetKomaIndex,6,SummonEnemy,e_aya_00001_aya1_charaget02_Boss_Green,,1,1000,None,2.5,None,,None,,None,,,,,,Default,Normal,12,6,1,200,0,,None,,202604010
```

---

## 4. OutpostHpPercentage

### 概要

**「アウトポスト（拠点）のHPが condition_value % 以下になったとき発動」**。
大多数が `condition_value=99`（= 「最初のダメージを受けた瞬間」を意味する実質即時発動）。CharaGet系でバトル開始時の最初のボス召喚トリガーとして多用される。

### condition_value の意味

`condition_value` = 「アウトポストのHP残量パーセント（この値以下になったとき発動）」

| condition_value | 件数 | 用途 |
|---|---|---|
| 99 | 97 | 実質バトル開始直後（最初のダメージで発動） |
| 80 | 6 | HP80%以下になったら |
| 70 | 7 | HP70%以下になったら |
| 60 | 7 | HP60%以下になったら |
| 50 | 23 | HP半分以下（中間フェーズ切り替え） |
| 40 | 6 | HP40%以下（終盤フェーズ） |
| 30 | 5 | HP30%以下（危機フェーズ） |
| 10 | 1 | HP10%以下（ラストスパート） |

### 具体例

**例1: condition_value=99（実質開始直後ボス召喚）**

```
sequence_set_id:  event_aya1_charaget01_00003
condition_type:   OutpostHpPercentage
condition_value:  99
action_type:      SummonEnemy
action_value:     e_aya_00001_aya1_charaget01_Boss_Green
enemy_hp_coef:    8
enemy_attack_coef: 4.5
summon_count:     1
```

解説: 最初にアウトポストがダメージを受けた瞬間にボスを召喚。`InitialSummon` とほぼ同等の効果だが、「ダメージを受けた後」という条件が明確。

---

**例2: 複数の敵をHP99%トリガーで一斉召喚**

```
sequence_set_id:  event_dan1_challenge01_00001
condition_type:   OutpostHpPercentage
condition_value:  99
action_type:      SummonEnemy
action_value:     e_dan_00001_dan1challenge_Normal_Blue
summon_count:     5
summon_interval:  500
enemy_hp_coef:    1.3
enemy_attack_coef: 3.2

---

condition_value:  99
action_value:     e_dan_00201_dan1challenge_Boss_Blue
summon_count:     1
summon_interval:  0
enemy_hp_coef:    13
enemy_attack_coef: 5.5
```

解説: 同一条件で雑魚複数 + ボス1体をまとめて召喚する。初期波状攻撃演出。

---

**例3: HP50%で中間フェーズに突入**

```
sequence_set_id:  veryhard_chi_00001
condition_type:   OutpostHpPercentage
condition_value:  50
action_type:      SummonEnemy
action_value:     e_chi_00101_general_chi_vh_big_Normal_Yellow
aura_type:        Boss
enemy_hp_coef:    1.0
enemy_attack_coef: 1.0
summon_count:     1
```

解説: アウトポストのHPが半分になったことをトリガーに、特殊な強化ボスが召喚される。「ゲームの折り返し地点での逆転演出」。

---

**例4: HP70%でサポート敵が援軍として出現**

```
sequence_set_id:  event_jig1_savage_00001
sequence_group_id: w5
condition_type:   OutpostHpPercentage
condition_value:  70
action_type:      SummonEnemy
action_value:     e_jig_00001_jig1_savage_Normal_Yellow
enemy_hp_coef:    150
enemy_attack_coef: 6
summon_count:     3
summon_interval:  50
```

解説: Savage最終フェーズでHP70%以下になると超強化された雑魚が群れで出現。HP管理が重要になる設計。

---

**例5: HP50%でSwitchSequenceGroup（フェーズ切り替え）**

```
sequence_set_id:  event_xxx_challenge
condition_type:   OutpostHpPercentage
condition_value:  50
action_type:      SwitchSequenceGroup
action_value:     phase2
```

解説: HPがボーダーを超えたらグループを丸ごと切り替える。後半は別セットの敵パターンを使う本格2フェーズ設計（3件確認）。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_aya1_charaget01_00003_1,event_aya1_charaget01_00003,,1,,OutpostHpPercentage,99,SummonEnemy,e_aya_00001_aya1_charaget01_Boss_Green,,1,0,None,,None,,None,,None,,,,,,Default,Normal,8,4.5,1,200,0,,None,,202604010
```

---

## 5. OutpostDamage

### 概要

**「アウトポストへの累計ダメージがcondition_valueに達したとき発動」**。
条件値 `1` が圧倒的多数（= 「最初にダメージを受けた瞬間」= OutpostHpPercentage:99 とほぼ同等の効果）。Challenge系でのボス初期召喚に多用される。

### condition_value の意味

`condition_value` = 「アウトポストへの累計ダメージ量（ゲーム内数値）」

| condition_value | 件数 | 解釈 |
|---|---|---|
| 1 | 123 | 最初のダメージで即時発動 |
| 1000 | 1 | 累計1000ダメージ到達 |
| 2000 | 1 | 累計2000ダメージ到達 |
| 5000 | 9 | 累計5000ダメージ到達（段階的フェーズ）|
| 7000 | 2 | 累計7000ダメージ到達（最終フェーズ）|

### 具体例

**例1: ダメージ1（実質最初）でボスを召喚**

```
sequence_set_id:  event_jig1_challenge01_00002
condition_type:   OutpostDamage
condition_value:  1
action_type:      SummonEnemy
action_value:     c_jig_00201_jig1_challenge_Boss_Green
aura_type:        Boss
enemy_hp_coef:    3.3
enemy_attack_coef: 6.4
summon_count:     1
```

解説: OutpostHpPercentage:99 と同様に「バトル開始直後にボスを召喚」する設計。Challengeコンテンツでは OutpostDamage:1 がよく使われる。

---

**例2: ダメージ1で複数のボスを同時召喚**

```
sequence_set_id:  event_jig1_challenge01_00002
condition_type:   OutpostDamage
condition_value:  1
action_value:     c_jig_00201_jig1_challenge_Boss_Green  → ボスA
condition_value:  1
action_value:     c_jig_00301_jig1_challenge_Boss_Green  → ボスB
condition_value:  1
action_value:     e_jig_00401_jig1_challenge_Normal_Colorless → 雑魚
```

解説: 同じダメージ条件に複数レコードを設定し、初戦から複数の強敵が登場する初期構成。

---

**例3: ダメージ累計5000でフェーズ2突入（段階型）**

```
sequence_set_id:  event_you1_challenge_00002
condition_type:   OutpostDamage
condition_value:  5000
action_type:      SummonEnemy
action_value:     e_glo_00001_you1_challenge_Normal_Green
enemy_hp_coef:    27
enemy_attack_coef: 1.0
summon_count:     1
（同じ条件で5レコード）
```

解説: 累計5000ダメージに到達したタイミングで同一敵が5体連続召喚。「ダメージを与え続けると第2フェーズが始まる」仕様。

---

**例4: Savage最終フェーズでダメージ蓄積型の大量ボス召喚**

```
sequence_set_id:  event_f05anniv_savage_00002
condition_type:   OutpostDamage
condition_value:  1
action_type:      SummonEnemy
action_value:     c_sur_00901_glo2_savage01_Boss_Colorless
enemy_hp_coef:    70
enemy_attack_coef: 5.2
summon_count:     1

---

condition_type:   OutpostDamage
condition_value:  1
action_value:     e_sur_00101_glo2_savage01_Normal_Red
enemy_hp_coef:    20
enemy_attack_coef: 4.0
summon_count:     3
summon_interval:  1300

condition_type:   OutpostDamage
condition_value:  1
action_value:     e_sur_00101_glo2_savage01_Normal_Colorless
enemy_hp_coef:    20
summon_count:     3
summon_interval:  900
```

解説: OutpostDamage:1 で超強化ボス1体 + 雑魚6体を段階召喚。他の condition_type（FriendUnitSummoned など）とも組み合わせた複合設計。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_jig1_challenge01_00002_1,event_jig1_challenge01_00002,,1,,OutpostDamage,1,SummonEnemy,c_jig_00201_jig1_challenge_Boss_Green,,1,,None,,None,,None,,None,,,,,,Boss,Normal,3.3,6.4,1,,0,,None,,202601010
```

---

## 6. DarknessKomaCleared

### 概要

**「暗黒コマを condition_value 枚クリアしたとき発動」**。
暗黒コマはフィールド上に配置される特殊な妨害コマ。これを消去する枚数をトリガーとする。メインクエスト（特にDan作品系）のNormal/Hard/VeryHardで使用。

### condition_value の意味

`condition_value` = 「暗黒コマのクリア枚数（累計）」

| condition_value | 件数 |
|---|---|
| 1 | 7 |
| 2 | 14 |
| 3 | 12 |
| 4 | 8 |

### 具体例

**例1: 暗黒コマ2枚クリアでボスを召喚**

```
sequence_set_id:  normal_dan_00001
condition_type:   DarknessKomaCleared
condition_value:  2
action_type:      SummonEnemy
action_value:     e_dan_00201_general_n_Boss_Colorless
aura_type:        Default
enemy_hp_coef:    0.55
enemy_attack_coef: 0.3
summon_count:     1
```

解説: 暗黒コマを2枚消去するとボスが出現。プレイヤーの「暗黒コマへの対処」というアクションに連動した演出。

---

**例2: 段階的にクリア枚数が増えるにつれて強い敵が出現**

```
# 2枚クリアで弱め
condition_value:  2
action_value:     e_dan_00001_general_h_Normal_Red
enemy_hp_coef:    4.5
enemy_attack_coef: 4.0

# 3枚クリアで強め
condition_value:  3
action_value:     e_dan_00001_general_h_Normal_Red
enemy_hp_coef:    4.5
enemy_attack_coef: 4.0（complex追加）
```

解説: 暗黒コマをクリアするたびに次々と敵が召喚される。消去ペースが上がるほど召喚数も増える。

---

**例3: 暗黒コマ4枚クリアで強化ボス登場**

```
sequence_set_id:  hard_dan_00006
condition_type:   DarknessKomaCleared
condition_value:  4
action_type:      SummonEnemy
action_value:     e_dan_00001_general_h_trans_Normal_Red
enemy_hp_coef:    2.5
enemy_attack_coef: 8.5
summon_count:     1
```

解説: 4枚という比較的多い枚数をクリアしてはじめて変身モードの強敵が出現。中盤フェーズへの移行演出。

---

**例4: SwitchSequenceGroupとの組み合わせ**

```
sequence_set_id:  hard_dan_00001
condition_type:   DarknessKomaCleared
condition_value:  2
action_type:      SwitchSequenceGroup
action_value:     group1
```

解説: 暗黒コマ2枚クリアをトリガーにシーケンスグループを切り替える。グループ切り替えで次フェーズの敵パターンに移行する設計。

---

**例5: Savage系での暗黒コマクリアと組み合わせ**

```
sequence_set_id:  event_spy1_savage_00002
condition_type:   DarknessKomaCleared
condition_value:  2
action_type:      SummonEnemy
action_value:     e_spy_00101_spy1challenge_Normal_Red
enemy_hp_coef:    30
enemy_attack_coef: 5
summon_count:     1
```

解説: Savageコンテンツでも暗黒コマのクリアが敵召喚のトリガーになる場合がある。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,normal_dan_00001_1,normal_dan_00001,,1,,DarknessKomaCleared,2,SummonEnemy,e_dan_00201_general_n_Boss_Colorless,,1,0,None,,None,,None,,None,,,,,,Default,Normal,0.55,0.3,1,,0,,None,,202509010
```

---

## 7. FriendUnitTransform

### 概要

**「フレンドユニットの変身回数が condition_value に達したとき発動」**。
全20件で `condition_value=1` のみ（1回変身したら発動）。Dan作品のメインクエストに特化しており、「ダグが変身した瞬間に反応する」仕様。

### condition_value の意味

| condition_value | 意味 |
|---|---|
| 1 | フレンドユニットが初めて変身したとき発動（全20件で固定値） |

### 具体例

**例1: 変身トリガーで増援を召喚**

```
sequence_set_id:  hard_dan_00004
condition_type:   FriendUnitTransform
condition_value:  1
action_type:      SummonEnemy
action_value:     e_dan_00101_general_h_Normal_Colorless
enemy_hp_coef:    2.2
enemy_attack_coef: 7.0
summon_count:     3
summon_interval:  100
```

解説: フレンドユニットが変身した瞬間に雑魚が3体で出現。「変身＝強くなったプレイヤーへの敵の対応」という演出。

---

**例2: 変身後に間隔を空けた複数ウェーブ召喚**

```
sequence_set_id:  hard_dan_00004
condition_type:   FriendUnitTransform
condition_value:  1
action_type:      SummonEnemy
action_value:     e_dan_00101_general_h_Normal_Colorless
summon_count:     3
summon_interval:  100
action_delay:     100

--- （同条件で）

summon_count:     3
summon_interval:  200
action_delay:     500

--- （同条件で）

summon_count:     3
summon_interval:  500
action_delay:     150
```

解説: 同一変身トリガーで `action_delay` を変えた複数レコードを設定。時間差で雑魚が波状に出現する演出。

---

**例3: 変身でSwitchSequenceGroup（フェーズ移行）**

```
sequence_set_id:  hard_dan_00003
condition_type:   FriendUnitTransform
condition_value:  1
action_type:      SwitchSequenceGroup
action_value:     group1
```

解説: 変身を契機にシーケンスグループを切り替える。Dan作品の「変身後フェーズ」に専用の敵出現パターンを持たせる設計。

---

**例4: VeryHardでの変身対応（大量召喚 + 複数波）**

```
sequence_set_id:  veryhard_dan_00002
condition_type:   FriendUnitTransform
condition_value:  1
action_type:      SummonEnemy
action_value:     e_dan_00101_general_vh_Normal_Blue
enemy_hp_coef:    3.0
enemy_attack_coef: 9.0
summon_count:     3
summon_interval:  150

--- (同条件)
summon_count:     50
summon_interval:  50

--- (同条件)
summon_count:     30
summon_interval:  50
```

解説: VeryHardでは変身後に最初の波3体 → 連続50体 → 連続30体という急激な数的優位を作る高難易度設計。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,hard_dan_00004_5,hard_dan_00004,,3,,FriendUnitTransform,1,SummonEnemy,e_dan_00101_general_h_Normal_Colorless,,3,100,None,,None,,None,,None,,,,,,Default,Normal,2.2,7,1,50,0,100,None,,202509010
```

---

## 8. FriendUnitSummoned

### 概要

**「フレンドユニットの召喚数（累計）が condition_value に達したとき発動」**。
件数は9件と少ない。Savage系の特定コンテンツ（`event_f05anniv_savage`、`event_l05anniv_challenge01`）や `event_you1_charaget02` など、特殊な召喚メカニクスを持つステージで使用。

### condition_value の意味

| condition_value | 解釈 |
|---|---|
| 2 | 累計2体のフレンドユニットが召喚されたとき |
| 3 | 累計3体のフレンドユニットが召喚されたとき |
| 8 | 累計8体のフレンドユニットが召喚されたとき |
| w1_4 | グループw1の4番目に召喚されたフレンドユニット（特殊な参照形式） |

### 具体例

**例1: 2体目のフレンドユニット召喚で反応（最初の具体例）**

```
sequence_set_id:  event_f05anniv_savage_00002
condition_type:   FriendUnitSummoned
condition_value:  2
action_type:      SummonEnemy
action_value:     e_sur_00101_glo2_savage01_Normal_Red
enemy_hp_coef:    55
enemy_attack_coef: 2.0
summon_count:     2
summon_interval:  200
```

解説: プレイヤーが2体目のフレンドユニットを召喚した瞬間に対応する敵が出現。召喚数と敵出現を連動させることで「召喚するたびに敵が増える」緊張感を演出。

---

**例2: 3体召喚で援軍を呼び込む**

```
sequence_set_id:  event_l05anniv_challenge01_00003
condition_type:   FriendUnitSummoned
condition_value:  3
action_type:      SummonEnemy
action_value:     e_glo_00001_l05anniv_challenge01_Normal_Colorless
enemy_hp_coef:    18
enemy_attack_coef: 5.3
summon_count:     4
summon_interval:  10
```

解説: フレンドユニット3体が召喚された時点で4体の援軍が一斉出現。召喚コストを支払う代償として敵が増加する「リスク＆リワード」構造。

---

**例3: 8体召喚でさらに大きな波が来る**

```
sequence_set_id:  event_f05anniv_savage_00003
condition_type:   FriendUnitSummoned
condition_value:  8
action_type:      SummonEnemy
action_value:     e_sur_00101_glo2_savage01_Normal_Red
enemy_hp_coef:    41
enemy_attack_coef: 4.1
summon_count:     10
summon_interval:  1700

--- (同条件)
summon_count:     10
summon_interval:  800
```

解説: 累計8体という大量の召喚が溜まると、強化された敵が20体近く出現する終盤フェーズ設計。大規模Savageで召喚ごとに蓄積するシステム。

---

**例4: グループ番号を参照するw1_4（特殊パターン）**

```
sequence_set_id:  event_you1_charaget02_00006
sequence_group_id: w1
condition_type:   FriendUnitSummoned
condition_value:  w1_4
action_type:      SummonEnemy
action_value:     e_you_00001_you1_charaget02_Normal_Red
enemy_hp_coef:    26
enemy_attack_coef: 2.26
summon_count:     4
summon_interval:  2400

--- (同条件で複数)
action_value:     e_you_00101_you1_charaget02_Normal_Red
summon_count:     99
summon_interval:  1600
```

解説: `w1_4` という特殊な参照形式でw1グループの4番目の召喚ユニットに連動。グループ内の召喚順序に依存した高度な設計（このパターンは1ステージのみで確認）。

---

### 1レコード 完全設定例

```csv
ENABLE,id,sequence_set_id,sequence_group_id,sequence_element_id,priority_sequence_element_id,condition_type,condition_value,action_type,action_value,action_value2,summon_count,summon_interval,summon_animation_type,summon_position,move_start_condition_type,move_start_condition_value,move_stop_condition_type,move_stop_condition_value,move_restart_condition_type,move_restart_condition_value,move_loop_count,is_summon_unit_outpost_damage_invalidation,last_boss_trigger,aura_type,death_type,enemy_hp_coef,enemy_attack_coef,enemy_speed_coef,override_drop_battle_point,defeated_score,action_delay,deactivation_condition_type,deactivation_condition_value,release_key
e,event_f05anniv_savage_00002_8,event_f05anniv_savage_00002,,8,,FriendUnitSummoned,2,SummonEnemy,e_sur_00101_glo2_savage01_Normal_Red,,2,200,None,,None,,None,,None,,,,,,Default,Normal,55,2,1.2,,0,,None,,202603020
```

---

## 設計パターン まとめ

### condition_type の用途別使い分け

| 目的 | 推奨 condition_type |
|---|---|
| バトル開始直後に出現（実質即時） | `OutpostHpPercentage: 99` または `OutpostDamage: 1` |
| 敵を倒すたびに次の敵が出現する直線進行 | `FriendUnitDead: 1, 2, 3, ...` |
| wave（フェーズ）の切り替え | `FriendUnitDead + SwitchSequenceGroup` |
| wave内での時間経過による敵出現制御 | `ElapsedTimeSinceSequenceGroupActivated` |
| コマ進行度に連動した出現 | `EnterTargetKomaIndex` |
| HP残量に応じたフェーズ変化 | `OutpostHpPercentage: 50, 30, 10...` |
| 暗黒コマ演出との連動 | `DarknessKomaCleared` |
| 変身キャラ専用の連動演出 | `FriendUnitTransform` |
| 味方召喚数に連動した出現制御 | `FriendUnitSummoned` |

### 複合パターン（複数のcondition_typeを同一ステージで使う）

- **CharaGet02**: `OutpostHpPercentage:99`（開始ボス）+ `EnterTargetKomaIndex`（コマ進行ボス）+ `FriendUnitDead`（撃破数進行）
- **Savage（討伐）**: `FriendUnitDead + SwitchSequenceGroup`（ウェーブ切替）+ `ElapsedTimeSinceSequenceGroupActivated`（wave内時系列）+ `OutpostHpPercentage or OutpostDamage`（HP連動）
- **メインクエスト（dan）**: `DarknessKomaCleared` + `FriendUnitTransform` + `FriendUnitDead`
