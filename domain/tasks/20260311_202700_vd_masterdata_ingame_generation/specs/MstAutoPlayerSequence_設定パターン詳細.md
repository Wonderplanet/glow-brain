# MstAutoPlayerSequence 設定パターン詳細

> 参照元: `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md`
> C#実装: `glow-client/Assets/GLOW/.../InGame/Domain/Battle/AutoPlayer/`
> glow-schema: `glow-schema/Schema/Stage.yml`

---

## 1. 概要

MstAutoPlayerSequence はバトル中の**敵出現ルール**を定義する。
1つのインゲーム（MstInGame）に対して複数行が紐づく。

```
MstInGame.id（= sequence_set_id）
  └─ MstAutoPlayerSequence（複数行）
        ├─ 行1: 開始時にボスを1体出す
        ├─ 行2: 2500ms後に雑魚を3体出す
        └─ ...
```

### 3層構造

```
sequence_set_id     = インゲームID（バトル1つ分）
  └─ sequence_group_id  = フェーズ（空=デフォルト、"w1", "w2", ...）
        └─ sequence_element_id = 個々のアクション（連番整数 or "groupchange_N"）
```

---

## 2. CSVカラム一覧（必須度・型・デフォルト）

| カラム名 | 型 | NULL | デフォルト | VD固定値 | 備考 |
|---------|-----|------|-----------|---------|-----|
| `ENABLE` | string | × | - | `e` | `e`固定 |
| `id` | string | × | - | `{block_id}_{sequence_element_id}` | レコードID |
| `sequence_set_id` | string | × | - | `{block_id}` | MstInGame.idと一致 |
| `sequence_group_id` | string | - | `""` | `""` | VDは空=デフォルトグループ |
| `sequence_element_id` | string | × | - | 連番（`1`,`2`...） | グループ切替は`groupchange_N` |
| `priority_sequence_element_id` | string | ✓ | - | `__NULL__` | 通常未使用 |
| `condition_type` | enum | × | `None` | → §3参照 | 発火条件 |
| `condition_value` | string | × | - | → §3参照 | 条件の値 |
| `action_type` | enum | × | `None` | `SummonEnemy` | 実行アクション |
| `action_value` | string | △ | `""` | `MstEnemyStageParameter.id` | SummonEnemy時は必須 |
| `action_value2` | string | - | `""` | `""` | 現状未使用 |
| `summon_count` | int | △ | `0` | 召喚数 | SummonEnemy時は必須（99=実質無限） |
| `summon_interval` | int | - | `0` | `0` | 0=同時召喚、正値=順次召喚(ms) |
| `summon_animation_type` | enum | - | `None` | `None` | 召喚演出 |
| `summon_position` | float | - | `0` | `0`（通常）/ `1.7`（砦付近） | 召喚X位置 |
| `move_start_condition_type` | enum | - | `None` | `None`（通常）/ `Damage`（ボス） | 移動開始条件 |
| `move_start_condition_value` | long | - | `0` | `0`（通常）/ `1`（ボス） | 移動開始条件値 |
| `move_stop_condition_type` | enum | - | `None` | `None` | 移動停止条件 |
| `move_stop_condition_value` | long | - | `0` | `0` | 移動停止条件値 |
| `move_restart_condition_type` | enum | - | `None` | `None` | 移動再開条件 |
| `move_restart_condition_value` | long | - | `0` | `0` | 移動再開条件値 |
| `move_loop_count` | int | - | `0` | `0` | 移動ループ回数 |
| `is_summon_unit_outpost_damage_invalidation` | bool | - | `0` | `0` | 1=砦ダメージ無効 |
| `last_boss_trigger` | bool | - | `0` | `0` | ラスボストリガー |
| `aura_type` | enum | - | `Default` | `Default`（雑魚）/ `Boss`（ボス） | オーラ演出 |
| `death_type` | enum | - | - | `Normal` | 死亡演出 |
| `enemy_hp_coef` | float | × | `0` | 設計書による | HP倍率（MstInGame全体倍率に乗算） |
| `enemy_attack_coef` | float | × | `0` | 設計書による | 攻撃力倍率 |
| `enemy_speed_coef` | float | × | `0` | 設計書による | 移動速度倍率 |
| `override_drop_battle_point` | int | ✓ | - | `__NULL__` | バトルポイント上書き（空=MstEnemyStageParameterの値） |
| `defeated_score` | int | - | `0` | `0` | レイド用スコア（VDは0） |
| `action_delay` | int | - | `0` | `0` | 発火遅延（ms） |
| `deactivation_condition_type` | enum | - | `None` | `None` | このシーケンスを無効化する条件 |
| `deactivation_condition_value` | string | - | `""` | `""` | 無効化条件値 |
| `release_key` | bigint | × | `1` | `202604010` | リリースキー |

---

## 3. condition_type 別の設定パターン

### 3-1. `InitialSummon`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `InitialSummon` | バトル開始時に発火 |
| `condition_value` | `0` または `1` | どちらでも動作（慣例で`0`） |

**使用タイミング**: ボスを最初から出したいとき・最初の雑魚を即出現させたいとき

**注意**: 同グループ内の複数行が同時発火する（並列監視）

---

### 3-2. `ElapsedTime`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `ElapsedTime` | バトル開始から経過時間で発火 |
| `condition_value` | 経過時間 ÷ 100（整数） | 単位は**100ms**。250 = 2500ms後 |

**使用例**:
```
condition_value: 150  → バトル開始から15秒後に発火
condition_value: 250  → バトル開始から25秒後に発火
condition_value: 500  → バトル開始から50秒後に発火
```

**注意**: バトル開始からの絶対時間。グループが切り替わっても時間はリセットされない。
グループ切り替え後の経過時間を使いたい場合は `ElapsedTimeSinceSequenceGroupActivated` を使う。

---

### 3-3. `ElapsedTimeSinceSequenceGroupActivated`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `ElapsedTimeSinceSequenceGroupActivated` | グループ切り替え後からの経過時間 |
| `condition_value` | 経過時間 ÷ 100（整数） | 単位は**100ms**。150 = 1500ms後 |

**使用タイミング**: グループ（フェーズ）切り替えが発生するコンテンツ（レイド・チャレンジ等）で、次のフェーズ内の時間を制御したいとき。

**VDでの使用**: VDはグループ切り替えを使わないため通常不使用。

---

### 3-4. `FriendUnitDead`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `FriendUnitDead` | 指定シーケンス要素で召喚したキャラが倒されたとき発火 |
| `condition_value` | `sequence_element_id`の文字列値 | **その要素で召喚したキャラが1体でも倒されたとき**発火 |

> **⚠️ 注意: 「累計撃破数」ではない**
>
> C#実装（`EnemyUnitDeadCommonConditionModel`）の判定ロジック:
> ```csharp
> context.DeadUnits.Any(unit =>
>     unit.BattleSide == BattleSide.Enemy &&
>     unit.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId)
> ```
> `condition_value` は `sequence_element_id` の**文字列**として解釈される（`int.Parse` は行われない）。
> 「その sequence_element_id で召喚されたユニットが1体でもDeadUnitsに存在する」ときに発火する。

**正しい設定パターン**:
```
_1: InitialSummon → 雑魚3体召喚（このユニットに sequence_element_id="1" が付与される）
_2: ElapsedTime=250 → 雑魚2体召喚（sequence_element_id="2"）

_3: FriendUnitDead, condition_value="1" → _1（sequence_element_id=1）で召喚した3体のうち
                                          1体でも倒されたとき発火（✅ 正しい）

_3: FriendUnitDead, condition_value="3" → _3 自身で召喚するキャラを参照 → 循環参照 ❌ 永遠に発火しない
```

**使用例（本番データパターン）**:
```
seq_elem_id=1: InitialSummon → 雑魚3体召喚
seq_elem_id=2: ElapsedTime   → 雑魚5体召喚
seq_elem_id=4: FriendUnitDead, condition_value=1 → seq_elem_id=1 のキャラが1体倒れたとき発火
seq_elem_id=6: FriendUnitDead, condition_value=2 → seq_elem_id=2 のキャラが1体倒れたとき発火
```

---

### 3-5. `OutpostDamage`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `OutpostDamage` | 敵砦へのダメージ累計 |
| `condition_value` | ダメージ量（整数） | 累計Nダメージ以上受けたとき発火 |

**使用タイミング**: 砦にダメージが入ったタイミングで無限出現等を発動させたいとき。

**VDでの使用例**:
```
condition_type: OutpostDamage, condition_value: 1  → 砦に1ダメージ入ったら発火（実質砦攻撃開始時）
```

---

### 3-6. `OutpostHpPercentage`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `OutpostHpPercentage` | 敵砦HPが指定%以下になったとき |
| `condition_value` | 0〜100（整数） | 砦HPがN%以下になったとき発火 |

---

### 3-7. `EnterTargetKomaIndex`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `EnterTargetKomaIndex` | 指定コマにプレイヤーユニットが入ったとき |
| `condition_value` | コマインデックス（整数、0始まり） | 何番目のコマかを指定 |

---

### 3-8. `DarknessKomaCleared`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `DarknessKomaCleared` | 暗黒コマがクリアされたとき |
| `condition_value` | コマ番号（整数） | どのコマかを指定 |

---

### 3-9. `FriendUnitTransform`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `FriendUnitTransform` | 対象オートプレイヤー側のユニットが変身したとき |
| `condition_value` | `sequence_element_id` | 変身したユニットを出現させたシーケンス要素のID |

**注意**: C#実装によると `condition_value` は `AutoPlayerSequenceElementId`（`sequence_element_id` の値）を受け取る。

---

### 3-10. `FriendUnitSummoned`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `FriendUnitSummoned` | 対象オートプレイヤー側のユニットが召喚されたとき |
| `condition_value` | `sequence_element_id` | 召喚されたユニットを定義したシーケンス要素のID |

---

### 3-11. `SequenceElementActivated`

| カラム | 設定値 | 理由 |
|--------|--------|------|
| `condition_type` | `SequenceElementActivated` | 指定のシーケンス要素が発火したとき |
| `condition_value` | `sequence_element_id` | 発火を監視したい要素のID |

---

## 4. action_type 別の設定パターン

### 4-1. `SummonEnemy`（最頻用途）

```
action_type:    SummonEnemy
action_value:   MstEnemyStageParameter.id  ← 必須
action_value2:  ""                          （未使用）
summon_count:   1〜（必須。99以上で実質無限）
summon_interval: 0（同時）or 正値（順次、ms）
```

**依存カラム**:
- `action_value` に `MstEnemyStageParameter.id` を設定する（必須）
- `summon_count` を 1 以上にする（必須）
- `summon_interval` が 0 なら `summon_count` 体を同時召喚
- `summon_interval > 0` なら `summon_count` 体を N ms 間隔で順次召喚
- `aura_type` を適切に設定（ボスは `Boss`、雑魚は `Default`）
- `death_type` を設定（通常は `Normal`）
- `enemy_hp_coef` / `enemy_attack_coef` / `enemy_speed_coef` を設定（必須）
- ボスを砦付近に配置する場合: `summon_position=1.7`, `move_start_condition_type=Damage`, `move_start_condition_value=1`

---

### 4-2. `SwitchSequenceGroup`（グループ切り替え）

```
id:                    {set_id}_groupchange_N
sequence_element_id:   groupchange_N
condition_type:        FriendUnitDead（または ElapsedTime）
condition_value:       累計撃破数（または経過時間）
action_type:           SwitchSequenceGroup
action_value:          w1（切り替え先グループID）
action_value2:         ""
summon_count:          ""（空にする）
```

**重要な制約**:
- `sequence_element_id` は `groupchange_N` 形式にする（慣例・必須）
- `summon_count` は空にする
- `action_value` に切り替え先グループID（例: `w1`, `w2`）を入れる

---

### 4-3. `SummonPlayerCharacter`

```
action_type:   SummonPlayerCharacter
action_value:  MstUnit.id
summon_count:  1〜
```

**使用タイミング**: チュートリアルやデモ演出でプレイヤーキャラを自動召喚したいとき。

---

### 4-4. `SummonPlayerSpecialCharacter`

```
action_type:   SummonPlayerSpecialCharacter
action_value:  （特殊キャラのID）
```

---

### 4-5. `PlayerSpecialAttack`

```
action_type:   PlayerSpecialAttack
action_value:  ""
```

プレイヤーの必殺技を自動で発動させる。

---

### 4-6. `SummonGimmickObject`

```
action_type:   SummonGimmickObject
action_value:  MstInGameGimmickObject.id（必須）
```

---

### 4-7. `TransformGimmickObjectToEnemy`

```
action_type:   TransformGimmickObjectToEnemy
action_value:  （変換元ギミックオブジェクトのID）
```

---

### 4-8. `OpponentRush`（PvP専用）

```
action_type:   OpponentRush
action_value:  ""
```

対戦相手のラッシュを発動する。PvP専用。

---

## 5. move系カラム設定パターン

### 5-1. ボスを砦付近に待機させてダメージを受けるまで動かないパターン

```
summon_position:            1.7    ← 砦の横
move_start_condition_type:  Damage
move_start_condition_value: 1      ← 1ダメージ受けたら移動開始
move_stop_condition_type:   None
move_stop_condition_value:  0
move_restart_condition_type: None
move_restart_condition_value: 0
move_loop_count:             0
```

**効果**: 召喚時は砦の横にいて、プレイヤーに攻撃されるまで動かない演出。

---

### 5-2. 通常の雑魚（すぐ動く）パターン

```
summon_position:            0      ← デフォルト位置（右端）
move_start_condition_type:  None
move_start_condition_value: 0
move_stop_condition_type:   None
move_stop_condition_value:  0
move_restart_condition_type: None
move_restart_condition_value: 0
move_loop_count:             0
```

---

### 5-3. move_start_condition_type 全パターン

| 値 | condition_value | 説明 |
|----|-----------------|------|
| `None` | `0` | 召喚と同時に移動開始（デフォルト） |
| `ElapsedTime` | ms（整数） | 召喚後N ms経過したら移動開始 |
| `Damage` | ダメージ量 | Nダメージを受けたら移動開始 |
| `FoeEnterSameKoma` | `0` | 敵（プレイヤー側）が同じコマに入ったら移動開始 |
| `EnterTargetKoma` | コマインデックス | 指定コマに到達したら移動開始 |
| `DeadFriendUnitCount` | 累計撃破数 | 仲間がN体倒されたら移動開始 |
| `OnFieldPlayerCharacterCount` | プレイヤーキャラ数 | フィールド上のプレイヤーキャラがN体以上になったら移動開始 |

### 5-4. move_stop_condition_type 全パターン

| 値 | condition_value | 説明 |
|----|-----------------|------|
| `None` | `0` | 止まらない（常に移動） |
| `ElapsedTime` | ms（整数） | 移動開始後N ms後に停止 |
| `TargetPosition` | 座標（float） | 指定位置に到達したら停止 |
| `PassedKomaCount` | コマ数（整数） | Nコマ通過したら停止 |

### 5-5. move_restart_condition_type

`move_stop_condition_type` でいったん停止した後、再び動き始める条件。
使える値は `MoveStartConditionType` と同じ（`None`, `ElapsedTime`, `Damage`, `FoeEnterSameKoma`, `EnterTargetKoma`, `DeadFriendUnitCount`, `OnFieldPlayerCharacterCount`）。

---

## 6. aura_type / death_type 設定

### aura_type

| 値 | 用途 |
|----|------|
| `Default` | 通常の雑魚・通常ボス |
| `Boss` | VDボスキャラ・イベントボスクラス |
| `AdventBoss1` | 降臨バトル wave1 のボス |
| `AdventBoss2` | 降臨バトル wave2〜3 のボス |
| `AdventBoss3` | 降臨バトル 最終wave のボス |

**VDでの選択**:
- `c_` プレフィックスの敵（フレンドユニット出自） → `Boss`
- `e_` プレフィックスの敵 → `Default`

### death_type

| 値 | 用途 |
|----|------|
| `Normal` | 通常の死亡演出（大半のケース） |
| `Escape` | 逃走演出（敵が逃げていく演出） |

---

## 7. summon_position 設定

詳細: `specs/summon_position_設定仕様.md` を参照

| 値 | 意味 |
|----|------|
| `0` | デフォルト位置（フィールド右端付近） |
| `1.7` | 砦の横（ボス待機位置） |

**注意**: `summon_position` に値を入れる場合は float 型で入力する。

---

## 8. enemy_hp_coef / enemy_attack_coef / enemy_speed_coef

### 最終パラメータ計算式

```
最終HP = MstEnemyStageParameter.hp
         × MstInGame.normal_enemy_hp_coef（全体倍率）
         × MstAutoPlayerSequence.enemy_hp_coef（個別倍率）

最終攻撃力 = MstEnemyStageParameter.attack
             × MstInGame.normal_enemy_attack_coef
             × MstAutoPlayerSequence.enemy_attack_coef

最終速度 = MstEnemyStageParameter.speed
           × MstAutoPlayerSequence.enemy_speed_coef
```

### VDでの設定例

| パターン | hp_coef | attack_coef | speed_coef |
|---------|---------|-------------|------------|
| 序盤の雑魚 | 1.0 | 1.0 | 1.0 |
| 中盤の雑魚 | 2.0 | 1.5 | 1.0 |
| ボス（Normalブロック） | 5.0〜15.0 | 2.0〜5.0 | 1.0 |
| ボス（Bossブロック） | 30.0〜100.0 | 5.0〜20.0 | 0.8〜1.0 |
| 無限ループ雑魚（終盤） | 2.0〜5.0 | 2.0 | 1.0 |

---

## 9. VD固有の設定パターン

### 9-1. VDで固定の値

| カラム | VD固定値 |
|--------|---------|
| `ENABLE` | `e` |
| `sequence_group_id` | `""` （空）|
| `priority_sequence_element_id` | `__NULL__` |
| `action_type` | `SummonEnemy` （大半）|
| `action_value2` | `""` |
| `summon_animation_type` | `None` |
| `is_summon_unit_outpost_damage_invalidation` | `0` |
| `last_boss_trigger` | `0` |
| `move_stop_condition_type` | `None` |
| `move_stop_condition_value` | `0` |
| `move_restart_condition_type` | `None` |
| `move_restart_condition_value` | `0` |
| `move_loop_count` | `0` |
| `override_drop_battle_point` | `__NULL__` |
| `defeated_score` | `0` |
| `action_delay` | `0` |
| `deactivation_condition_type` | `None` |
| `deactivation_condition_value` | `""` |
| `release_key` | `202604010` |

### 9-2. VD Normalブロックの典型構成（5〜10行）

```
行1: condition_type=InitialSummon  → 雑魚を出す（3体程度）
行2: condition_type=ElapsedTime(150〜250)  → 追加雑魚
行3: condition_type=FriendUnitDead(3〜5)  → 撃破で追加雑魚
行4: condition_type=FriendUnitDead(N+3)  → さらに追加
...
最終行: condition_type=OutpostDamage(1) or FriendUnitDead(大きい数)
        summon_count=99  → 無限出現ループ
```

### 9-3. VD Bossブロックの典型構成（5〜10行）

```
行1: condition_type=InitialSummon
     action_value=c_xxx（ボス）
     summon_count=1
     summon_position=1.7
     move_start_condition_type=Damage
     move_start_condition_value=1
     aura_type=Boss

行2: condition_type=ElapsedTime(100〜250)  → 雑魚サポート出現
行3: condition_type=FriendUnitDead(2〜5)  → 追加雑魚
行4: condition_type=FriendUnitDead(N+3)  → さらに追加
...
最終行: condition_type=OutpostDamage(1)
        summon_count=99  → 無限ループ雑魚
```

---

## 10. IDの命名規則

### id（レコードID）

```
通常行:       {block_id}_{sequence_element_id}
              例: vd_kai_normal_00001_1

グループ切替: {block_id}_groupchange_N
              例: vd_kai_challenge_00001_groupchange_1
```

### action_value（MstEnemyStageParameter.id）

```
雑魚（e_プレフィックス）:   e_{作品ID}_{キャラID}_vd_{ユニット種別}_{色}
                           例: e_kai_00001_vd_Normal_Yellow

ボス（c_プレフィックス）:   c_{作品ID}_{キャラID}_vd_{ユニット種別}_{色}
                           例: c_kai_00201_vd_Boss_Green
```

---

## 11. 注意事項まとめ

| 番号 | 注意事項 |
|------|---------|
| ① | `sequence_set_id` は必ず対応する `MstInGame.id` と一致させる（違うと敵が一切出ない） |
| ② | `FriendUnitDead` はグループをまたいで累計カウント。リセットされない |
| ③ | 同じ `condition_value` を複数行に書くと全行が同時発火する（意図的な多体同時召喚に利用可） |
| ④ | `summon_count=99` は実質無限召喚（終盤の無限ループに使う） |
| ⑤ | `summon_interval=0` で同時召喚、正値（ms）で順次召喚 |
| ⑥ | ボス待機パターンは必ず `summon_position=1.7` + `move_start_condition_type=Damage` + `move_start_condition_value=1` のセットで設定 |
| ⑦ | `enemy_hp_coef` / `enemy_attack_coef` / `enemy_speed_coef` は MstInGame の全体倍率に**乗算**される（1.0が等倍） |
| ⑧ | `SwitchSequenceGroup` 行の `summon_count` は空（`""`）にする |
| ⑨ | `ElapsedTime` の `condition_value` は **100ms単位**の整数（1000ms = 10ではなく1000ms = 1000ではなく**100ms単位で250 = 2500ms**） |
| ⑩ | VDはグループ切り替えを使わないため `sequence_group_id` は空にする |

---

## 12. action_typeと必須カラムの依存関係早見表

| action_type | action_value | summon_count | summon_interval | その他注意点 |
|-------------|-------------|--------------|-----------------|------------|
| `SummonEnemy` | MstEnemyStageParameter.id（必須） | 必須（1〜） | 任意 | aura_type, death_type, enemy_*_coef必須 |
| `SwitchSequenceGroup` | 切り替え先group_id（必須） | 空にする | - | sequence_element_id は `groupchange_N` 形式 |
| `SummonPlayerCharacter` | MstUnit.id（必須） | 必須 | 任意 | - |
| `SummonPlayerSpecialCharacter` | 特殊キャラID | 任意 | 任意 | - |
| `PlayerSpecialAttack` | `""` | - | - | - |
| `SummonGimmickObject` | MstInGameGimmickObject.id（必須） | 任意 | 任意 | - |
| `TransformGimmickObjectToEnemy` | ギミックオブジェクトID | - | - | - |
| `OpponentRush` | `""` | - | - | PvP専用 |
