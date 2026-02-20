# MstAutoPlayerSequence 詳細説明

> 参照リリースキー: 202602015
> CSVパス: `domain/raw-data/masterdata/released/{リリースキー}/tables/MstAutoPlayerSequence.csv`

---

## 概要

MstAutoPlayerSequence は**バトル中に敵をいつ・どこに・どのくらい出現させるか**を制御するシーケンステーブル。
1つのインゲーム（バトル）に対して、複数行のシーケンスが紐づく。

```
MstInGame.id
  └─ MstAutoPlayerSequence（sequence_set_id = MstInGame.id）
        ├─ 行1: 開始時にボスを1体出す
        ├─ 行2: 500ms後に雑魚を5体出す
        ├─ 行3: 3000ms後に別の雑魚を11体出す
        └─ ...
```

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| `ENABLE` | string | ○ | `e` = 有効 |
| `id` | string | ○ | レコードID。`{sequence_set_id}_{sequence_element_id}` が基本形 |
| `sequence_set_id` | string | ○ | **インゲームIDと一致させる**。このIDでMstInGameと紐づく |
| `sequence_group_id` | string | - | グループID。空=デフォルトグループ。フェーズ切り替え時に使う |
| `sequence_element_id` | string | ○ | グループ内の要素番号（連番）。グループ切り替え行は `groupchange_N` 形式 |
| `priority_sequence_element_id` | string | - | 優先シーケンス要素ID（未使用が多い） |
| `condition_type` | enum | ○ | **このシーケンスが発火する条件** → 詳細は後述 |
| `condition_value` | string | ○ | 条件の値（ElapsedTimeなら100ms単位の数値など） |
| `action_type` | enum | ○ | **発火時のアクション** → 詳細は後述 |
| `action_value` | string | △ | アクションの対象ID（SummonEnemyなら MstEnemyStageParameter.id） |
| `action_value2` | string | - | アクションの追加値（現状未使用が多い） |
| `summon_count` | int | △ | 召喚数（SummonEnemy時に指定） |
| `summon_interval` | int | - | 複数召喚時の間隔（ms）。`0` または空=同時召喚 |
| `summon_animation_type` | enum | - | 召喚演出。`None` / `Fall0` / `Fall` / `Fall4` |
| `summon_position` | float | - | 召喚X位置（0〜1+α）。空=デフォルト位置。`1.7`=砦付近 |
| `move_start_condition_type` | enum | - | 敵の**移動開始**条件 → 詳細は後述 |
| `move_start_condition_value` | long | - | 移動開始条件の値 |
| `move_stop_condition_type` | enum | - | 敵の**移動停止**条件 |
| `move_stop_condition_value` | long | - | 移動停止条件の値 |
| `move_restart_condition_type` | enum | - | 移動**再開**条件 |
| `move_restart_condition_value` | long | - | 移動再開条件の値 |
| `move_loop_count` | int | - | 移動ループ回数 |
| `is_summon_unit_outpost_damage_invalidation` | bool | - | `1` = 召喚ユニットがプレイヤー砦にダメージを与えない |
| `last_boss_trigger` | string | - | （詳細未確認） |
| `aura_type` | enum | - | ユニット周囲のオーラ演出 → 詳細は後述 |
| `death_type` | enum | - | 死亡時演出。`Normal`（通常）/ `Escape`（逃走） |
| `enemy_hp_coef` | float | ○ | 敵HP倍率（MstInGameの全体倍率に**乗算**） |
| `enemy_attack_coef` | float | ○ | 敵攻撃力倍率 |
| `enemy_speed_coef` | float | ○ | 敵移動速度倍率 |
| `override_drop_battle_point` | int | - | バトルポイント上書き。空=MstEnemyStageParameterの値を使用 |
| `defeated_score` | int | - | 撃破スコア（レイド用）。通常ステージは `0` |
| `action_delay` | int | - | アクション発火の遅延（ms） |
| `deactivation_condition_type` | enum | - | このシーケンスを無効化する条件 |
| `deactivation_condition_value` | string | - | 無効化条件の値 |
| `release_key` | string | ○ | リリースキー |

---

## 3層の構造

シーケンスは **set → group → element** の3層で構成される。

```
sequence_set_id        = インゲームID単位（バトル1つ分のシーケンス全体）
  └─ sequence_group_id = フェーズ単位（空=デフォルト, "w1", "w2", ...）
        └─ sequence_element_id = 個々のアクション（連番）
```

### IDの例（`raid_you1_00001` の場合）

| id | sequence_set_id | sequence_group_id | sequence_element_id |
|----|-----------------|-------------------|---------------------|
| `raid_you1_00001_1` | `raid_you1_00001` | `` (空) | `1` |
| `raid_you1_00001_groupchange_1` | `raid_you1_00001` | `` (空) | `groupchange_1` |
| `raid_you1_00001_5` | `raid_you1_00001` | `w1` | `5` |
| `raid_you1_00001_groupchange_2` | `raid_you1_00001` | `w1` | `groupchange_2` |
| `raid_you1_00001_9` | `raid_you1_00001` | `w2` | `9` |

---

## condition_type（発火条件）

シーケンスがいつ発火するかを決める。**同じグループの複数要素は並列に監視される**（どれかが条件を満たせば発火）。

| condition_type | condition_value の意味 | 説明 |
|---------------|----------------------|------|
| `InitialSummon` | `0` または `1` | バトル開始時に即発火。ボスや最初の敵の召喚に使う |
| `ElapsedTime` | 経過時間（100ms単位） | バトル開始からN×100ms後に発火。`250`=2500ms後 |
| `ElapsedTimeSinceSequenceGroupActivated` | 経過時間（100ms単位） | **グループ切り替え後**からの経過時間。グループ内でよく使う |
| `FriendUnitDead` | 累計撃破数 | 敵の累計撃破数がN体になったとき発火。グループをまたいで累計カウント |
| `OutpostDamage` | ダメージ量 | 敵砦がN以上のダメージを受けたとき |
| `OutpostHpPercentage` | HP% (0〜100) | 敵砦のHPがN%以下になったとき |
| `EnterTargetKomaIndex` | コマインデックス | 指定コマに敵が到達したとき |
| `DarknessKomaCleared` | - | 暗黒コマがクリアされたとき |
| `FriendUnitTransform` | - | 味方ユニットが変身したとき |
| `FriendUnitSummoned` | - | 味方ユニットが召喚されたとき |
| `SequenceElementActivated` | element_id | 指定のシーケンス要素が発火したとき |

> **FriendUnitDeadの注意点**: 累計カウントで判定するため、同じ条件値を複数行に書いても全部発火する。
> 例：elem=4（FriendUnitDead=1）とelem=5（FriendUnitDead=1）は、1体目が死んだとき**両方とも同時発火**する。

---

## action_type（アクション）

条件が満たされたときに何をするか。

| action_type | action_value | summon_count | 説明 |
|-------------|-------------|--------------|------|
| `SummonEnemy` | `MstEnemyStageParameter.id` | 必要 | 指定パラメータの敵を召喚。最も頻繁に使う |
| `SwitchSequenceGroup` | 切り替え先 `sequence_group_id` | 不要 | シーケンスグループを切り替える（フェーズ移行） |
| `SummonPlayerCharacter` | `MstUnit.id` | 必要 | プレイヤーキャラを自動召喚 |
| `SummonPlayerSpecialCharacter` | - | - | プレイヤー特殊キャラを自動召喚 |
| `PlayerSpecialAttack` | - | - | プレイヤーの必殺技を発動させる |
| `SummonGimmickObject` | `MstInGameGimmickObject.id` | - | ギミックオブジェクトを召喚 |
| `TransformGimmickObjectToEnemy` | - | - | ギミックオブジェクトを敵に変換 |
| `OpponentRush` | - | - | 対戦相手のラッシュ（PvP用） |

> **SwitchSequenceGroupの行**: `sequence_element_id` を `groupchange_N` 形式にするのが慣例。`summon_count` は空にする。

---

## aura_type（オーラ演出）

敵が出現するときの光輪演出。ボスの格をビジュアルで表す。

| aura_type | 用途 |
|-----------|------|
| `Default` | 通常。雑魚敵や通常のボス |
| `Boss` | イベントボスクラス |
| `AdventBoss1` | 降臨バトル wave1のボス |
| `AdventBoss2` | 降臨バトル wave2〜3のボス |
| `AdventBoss3` | 降臨バトル 最終waveのボス（最も強い演出） |

---

## move系カラム（敵の移動制御）

召喚した敵がすぐ動くのではなく、特定条件が満たされてから動き始めさせる設定。

### move_start_condition_type

| 値 | move_start_condition_value | 説明 |
|----|--------------------------|------|
| `None` | - | デフォルト。召喚と同時に移動開始 |
| `ElapsedTime` | ms | 召喚後N ms経過したら移動開始 |
| `Damage` | ダメージ量 | Nダメージを受けたら移動開始。ボスが「ダメージを受けるまで動かない」演出に使う |
| `FoeEnterSameKoma` | - | 敵（プレイヤー側ユニット）が同じコマに入ったら移動開始 |
| `EnterTargetKoma` | コマインデックス | 指定コマに到達したら移動開始 |
| `DeadFriendUnitCount` | 累計撃破数 | 仲間がN体倒されたら移動開始 |

### move_stop_condition_type

| 値 | move_stop_condition_value | 説明 |
|----|--------------------------|------|
| `None` | - | 止まらない（常に移動） |
| `ElapsedTime` | ms | N ms後に停止 |
| `TargetPosition` | - | 指定位置に到達したら停止 |
| `PassedKomaCount` | コマ数 | Nコマ通過したら停止 |

---

## 実データ例

### パターン1: シンプル（雑魚のみ・1行）

`event_you1_1day_00001`（デイリークエスト）

```
id:                    event_you1_1day_00001_1
sequence_set_id:       event_you1_1day_00001
sequence_group_id:     （空）
sequence_element_id:   1
condition_type:        ElapsedTime
condition_value:       250           ← 2500ms後
action_type:           SummonEnemy
action_value:          c_you_00001_you1_1d1c_Normal_Colorless
summon_count:          1
summon_interval:       0
summon_position:       （空）
move_start_condition_type: None
aura_type:             Default
death_type:            Normal
enemy_hp_coef:         1
enemy_attack_coef:     0.3           ← 弱い（デイリーなので）
enemy_speed_coef:      1
override_drop_battle_point: （空）
defeated_score:        0
```

→ 開始から2.5秒後に雑魚を1体出すだけ。デイリーミッション等の簡単なステージ。

---

### パターン2: ボスあり（3行）

`event_you1_charaget01_00001`（イベントクエスト ステージ1）

```
行1: ボスを開始時に砦付近に配置（動かない）
  condition_type:        InitialSummon
  condition_value:       0
  action_type:           SummonEnemy
  action_value:          c_you_00201_you1_charaget01_Boss_Yellow  ← ボス
  summon_count:          1
  summon_position:       1.7    ← 砦付近
  move_start_condition_type: Damage
  move_start_condition_value: 1  ← ダメージを受けたら動き始める
  aura_type:             Default
  enemy_hp_coef:         1.5
  enemy_attack_coef:     3
  override_drop_battle_point: 200

行2: 500ms後に雑魚5体を一定間隔で出す
  condition_type:        ElapsedTime
  condition_value:       500
  action_type:           SummonEnemy
  action_value:          e_you_00001_you1_charaget01_Normal_Colorless  ← 雑魚
  summon_count:          5
  summon_interval:       1500  ← 1500msごとに1体ずつ
  enemy_hp_coef:         5
  enemy_attack_coef:     2
  override_drop_battle_point: 70

行3: 3000ms後に別の雑魚11体
  condition_type:        ElapsedTime
  condition_value:       3000
  action_type:           SummonEnemy
  action_value:          e_you_00101_you1_charaget01_Normal_Yellow  ← 別の雑魚
  summon_count:          11
  summon_interval:       3000
  enemy_hp_coef:         5
  enemy_attack_coef:     2
  override_drop_battle_point: 70
```

→ ボスが砦付近で待機し、雑魚が時間差で押し寄せる典型パターン。

---

### パターン3: グループ切り替え（チャレンジ・4行）

`event_you1_challenge_00001`（チャレンジステージ）

```
行1: ボスを開始時に出す（ElapsedTimeで移動開始）
  condition_type:        InitialSummon
  action_value:          c_you_00201_you1_challenge_Boss_Green
  move_start_condition_type: ElapsedTime
  move_start_condition_value: 500   ← 0.5秒後に移動開始
  enemy_hp_coef:         11.9
  enemy_attack_coef:     1.2

行2: 400ms後に雑魚1体
  condition_type:        ElapsedTime / condition_value: 400
  summon_count:          1

行3: 1500ms後に雑魚6体
  condition_type:        ElapsedTime / condition_value: 1500
  summon_count:          6

行4: 味方1体死亡でグループ切り替え  ← SwitchSequenceGroup
  sequence_element_id:   groupchange_1
  condition_type:        FriendUnitDead
  condition_value:       1
  action_type:           SwitchSequenceGroup
  action_value:          w1             ← グループ"w1"に切り替え
  summon_count:          （空）
```

→ 敵が1体倒されると次のフェーズ（グループ w1）に移行する構造。

---

### パターン4: マルチウェーブ（レイド・50行・グループループ）

`raid_you1_00001`（レイドバトル）

グループ構造：デフォルト → w1 → w2 → w3 → w4 → w5 → w6 → w1（ループ）

```
【デフォルトグループ】（バトル開始〜最初の3体撃破まで）
  elem=1: InitialSummon  → 雑魚1体（score=30）
  elem=2: InitialSummon  → 雑魚1体（score=30）
  elem=3: InitialSummon  → 別雑魚1体（score=30）
  elem=4: ElapsedTime(500) → 別雑魚3体（score=10）
  elem=groupchange_1: FriendUnitDead(3) → SwitchSequenceGroup(w1)
                                         ↑ 累計3体撃破でw1へ

【w1グループ】（3体撃破〜6体撃破まで）
  elem=5:  ElapsedTimeSinceGroupActivated(0)   → 通常ボス1体（score=50, aura=Default）
  elem=6:  ElapsedTimeSinceGroupActivated(150) → ボス1体（score=75, aura=AdventBoss1）
  elem=7:  ElapsedTimeSinceGroupActivated(0)   → 雑魚2体
  elem=8:  ElapsedTimeSinceGroupActivated(750) → 雑魚3体
  elem=groupchange_2: FriendUnitDead(6) → SwitchSequenceGroup(w2)

【w2グループ】（6体撃破〜9体撃破まで）
  ...

【w5グループ】（最終ボスフェーズ）
  elem=26: ElapsedTimeSinceGroupActivated(0) → 大ボス1体（score=500, aura=AdventBoss3）
  ...

【w6グループ】
  ...
  elem=groupchange_7: FriendUnitDead(35) → SwitchSequenceGroup(w1) ← w1に戻る（ループ！）
```

→ レイドは永続するバトルのため、w6 が終わると w1 に戻るループ構造になっている。

---

## 1インゲームを作るために必要な行数の目安

| ステージ種別 | 行数目安 | 特徴 |
|------------|---------|------|
| デイリーミッション | 1〜3行 | シンプル。ElapsedTimeで雑魚を出すだけ |
| イベントクエスト（序盤） | 3〜6行 | ボス1体 + 時間差雑魚 |
| イベントクエスト（後半） | 6〜16行 | ボス複数 + FriendUnitDeadによる段階的出現 |
| チャレンジ / サベージ | 4〜22行 | FriendUnitDeadでSwitchSequenceGroupを組み合わせ |
| レイド | 30〜50行 | グループが多段階。ループ構造あり |

---

## IDの命名規則

### `id`（レコードID）

```
{sequence_set_id}_{sequence_element_id}

通常行:        event_you1_charaget01_00001_1
グループ切替:  event_you1_challenge_00001_groupchange_1
```

### `action_value`（MstEnemyStageParameter.id）

```
{キャラ種別プレフィックス}_{mst_enemy_character_id}_{インゲームID}_{character_unit_kind}_{color}

通常ボス: c_you_00201_you1_charaget01_Boss_Yellow
          ↑c=キャラ個別ID(chara_you_00201を短縮)
雑魚:     e_you_00001_you1_charaget01_Normal_Colorless
          ↑e=敵
降臨ボス: e_you_00201_you1_advent_Boss_Green
```

---

## 設定時のポイントと注意事項

### ポイント1: sequence_set_id は必ず MstInGame.id と一致させる

MstInGame.mst_auto_player_sequence_set_id で参照される。IDが違うと敵が一切出現しない。

### ポイント2: コマ効果の倍率は MstInGame の倍率と乗算される

```
最終HP = MstEnemyStageParameter.hp
       × MstInGame.normal_enemy_hp_coef  （全体倍率）
       × MstAutoPlayerSequence.enemy_hp_coef（個別倍率）
```

### ポイント3: FriendUnitDead は累計カウント・グループをまたいで加算

- グループが切り替わっても撃破数はリセットされない
- レイドの `FriendUnitDead(6)` は「累計6体目が倒れたとき」を意味する
- 同じ condition_value を複数行に書くと全行が同時発火する（意図的な多体同時召喚に利用）

### ポイント4: defeated_score は通常ステージでは 0

- `0` → スコア非表示（通常クエスト）
- 正の整数 → レイドバトルのスコア加算

### ポイント5: summon_interval の挙動

- `0` または空 → 指定数を**同時**に召喚
- 正の整数（ms）→ 1体召喚後、N ms 後に次の1体を召喚（順番に出す）

### ポイント6: グループ切り替え行の書き方

```
id:                    {set_id}_groupchange_N
sequence_element_id:   groupchange_N   ← "groupchange_" + 連番
condition_type:        FriendUnitDead  （または ElapsedTime など）
action_type:           SwitchSequenceGroup
action_value:          w1              ← 切り替え先グループID
summon_count:          （空にする）
```

### ポイント7: ボスを砦付近に配置する場合

```
summon_position:           1.7   ← 1.0が通常の右端。1.7は砦の横
move_start_condition_type: Damage
move_start_condition_value: 1    ← 1ダメージ受けたら動き始める
```

こうすることで「最初は砦に近づいた状態でダメージを受けるまで静止しているボス」を表現できる。
