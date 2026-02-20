# MstAutoPlayerSequence 設計ガイド

`MstAutoPlayerSequence` の設計に必要な情報をまとめたガイド。
詳細仕様は `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` を参照。

---

## condition_type 全種別早見表

| condition_type | condition_value | 説明 | よく使う場面 |
|---------------|----------------|------|------------|
| `InitialSummon` | `0` または `1` | バトル開始時即座に発火 | ボス初期配置、最初の雑魚 |
| `ElapsedTime` | 経過時間（100ms単位） | バトル開始からN×100ms後 | 時間差で雑魚を出す |
| `ElapsedTimeSinceSequenceGroupActivated` | 経過時間（100ms単位） | グループ切り替え後からの経過時間 | グループ内での段階出現 |
| `FriendUnitDead` | 累計撃破数 | 敵の累計撃破数がN体になった時 | グループ切り替えトリガー |
| `OutpostDamage` | ダメージ量 | 敵砦がN以上のダメージを受けた時 | 砦削り量に応じた出現 |
| `OutpostHpPercentage` | HP%（0〜100） | 敵砦のHPがN%以下になった時 | 砦HP残量に応じた出現 |
| `EnterTargetKomaIndex` | コマインデックス | 指定コマに敵が到達した時 | 地形に応じた出現 |
| `DarknessKomaCleared` | - | 暗黒コマがクリアされた時 | 暗黒コマクリア後 |
| `FriendUnitTransform` | - | 味方ユニットが変身した時 | 変身トリガー |
| `FriendUnitSummoned` | - | 味方ユニットが召喚された時 | 召喚トリガー |
| `SequenceElementActivated` | element_id | 指定シーケンス要素が発火した時 | 連鎖トリガー |

---

## action_type 全種別と必要カラム

| action_type | action_value | summon_count | 説明 |
|-------------|-------------|--------------|------|
| `SummonEnemy` | MstEnemyStageParameter.id | **必須** | 敵を召喚（最も頻繁に使う） |
| `SwitchSequenceGroup` | 切り替え先の sequence_group_id | 空にする | グループ切り替え（フェーズ移行） |
| `SummonPlayerCharacter` | MstUnit.id | **必須** | プレイヤーキャラを自動召喚 |
| `SummonPlayerSpecialCharacter` | - | - | プレイヤー特殊キャラを自動召喚 |
| `PlayerSpecialAttack` | - | - | プレイヤーの必殺技を発動 |
| `SummonGimmickObject` | MstInGameGimmickObject.id | - | ギミックオブジェクトを召喚 |
| `TransformGimmickObjectToEnemy` | - | - | ギミックを敵に変換 |
| `OpponentRush` | - | - | 対戦相手のラッシュ（PvP用） |

---

## グループ切り替え行の書き方テンプレート

```
id:                    {set_id}_groupchange_{N}
sequence_set_id:       {set_id}
sequence_group_id:     {現在のグループID or 空}
sequence_element_id:   groupchange_{N}       ← 慣例: "groupchange_" + 連番
condition_type:        FriendUnitDead
condition_value:       {累計撃破数}
action_type:           SwitchSequenceGroup
action_value:          {切り替え先グループID}  ← 例: "w1"
summon_count:          （空にする）
enemy_hp_coef:         1                     ← 倍率は設定が必要な場合もある
enemy_attack_coef:     1
enemy_speed_coef:      1
```

---

## summon_position の代表値

| 値 | 位置 |
|-----|------|
| 空（未設定） | デフォルト位置（右端付近） |
| `1.0` | フィールド右端 |
| `1.7` | 砦付近（ボスの初期配置によく使う） |
| `0.5` | フィールド中央付近 |
| `0.0` | フィールド左端付近 |

---

## aura_type の選択基準

| aura_type | 使用場面 |
|-----------|---------|
| `Default` | 雑魚敵、通常のボス（character_unit_kind=Boss以外） |
| `Boss` | イベントボスクラス（character_unit_kind=Boss） |
| `AdventBoss1` | 降臨バトル wave1のボス |
| `AdventBoss2` | 降臨バトル wave2〜3のボス |
| `AdventBoss3` | 降臨バトル 最終waveのボス（最も強い演出） |

---

## ウェーブ設計4パターン

### パターン1: シンプル（雑魚のみ・1〜3行）

デイリークエストや簡易ステージ向け:

```
行1: ElapsedTime(250) → SummonEnemy(雑魚A) × 3体
行2: ElapsedTime(1000) → SummonEnemy(雑魚A) × 3体
行3: ElapsedTime(3000) → SummonEnemy(雑魚B) × 2体
```

### パターン2: ボスあり（3〜6行）

イベントクエスト序盤の典型パターン:

```
行1: InitialSummon → SummonEnemy(ボス) × 1
     summon_position=1.7（砦付近）
     move_start_condition_type=Damage, move_start_condition_value=1

行2: ElapsedTime(500) → SummonEnemy(雑魚A) × 5
     summon_interval=1500

行3: ElapsedTime(3000) → SummonEnemy(雑魚B) × 11
     summon_interval=3000
```

### パターン3: グループ切り替え（チャレンジ向け・4〜22行）

FriendUnitDeadでフェーズが進む構造:

```
【デフォルトグループ】
行1: InitialSummon → ボス × 1（move_start: ElapsedTime(500)）
行2: ElapsedTime(400) → 雑魚A × 1
行3: ElapsedTime(1500) → 雑魚A × 6
行G: groupchange_1: FriendUnitDead(1) → SwitchSequenceGroup(w1)

【w1グループ】
行4: ElapsedTimeSinceGroupActivated(0) → 強い雑魚B × 3
行5: ElapsedTimeSinceGroupActivated(300) → ボス2 × 1
行G: groupchange_2: FriendUnitDead(5) → SwitchSequenceGroup(w2)
```

### パターン4: マルチウェーブループ（レイド・30〜50行）

永続バトル向け。wNが終わるとw1に戻るループ:

```
【デフォルトグループ】
InitialSummon × 3行（最初の雑魚を出す）
groupchange_1: FriendUnitDead(3) → SwitchSequenceGroup(w1)

【w1】
グループ活性化後に敵を出す + w2への切り替えトリガー

【w2】〜【w5】
段階的に強い敵が出る

【wN: ループ終了グループ】
...
groupchange_X: FriendUnitDead(累計N) → SwitchSequenceGroup(w1) ← w1に戻る！
```

---

## move系カラムの設定パターン

### ボスが砦付近で待機するパターン（よく使う）

```
summon_position:              1.7    ← 砦付近
move_start_condition_type:    Damage
move_start_condition_value:   1      ← 1ダメージ受けたら移動開始
move_stop_condition_type:     None
```

### 敵が最初は動かず、時間後に移動するパターン

```
summon_position:              （空）
move_start_condition_type:    ElapsedTime
move_start_condition_value:   500    ← 0.5秒後に移動開始
move_stop_condition_type:     None
```

### 通常（召喚と同時に移動）

```
summon_position:              （空）
move_start_condition_type:    None   ← デフォルト：召喚と同時に移動
move_stop_condition_type:     None
```

---

## デフォルト値（空欄でよいカラム）

以下は通常の用途では空欄でよい:

| カラム | 理由 |
|--------|------|
| `sequence_group_id` | 空=デフォルトグループ |
| `priority_sequence_element_id` | 優先シーケンス（未使用が多い） |
| `action_value2` | 現状未使用が多い |
| `summon_animation_type` | デフォルトでOK（`None`） |
| `last_boss_trigger` | 詳細未確認、通常空 |
| `move_stop_condition_type` | `None`（止まらない）が多い |
| `move_restart_condition_type` | 未使用が多い |
| `move_loop_count` | 未使用が多い |
| `is_summon_unit_outpost_damage_invalidation` | `0`（通常）が多い |
| `override_drop_battle_point` | 空=MstEnemyStageParameterの値を使用 |
| `defeated_score` | `0`（通常ステージ）。レイドは設定が必要 |
| `action_delay` | 0（遅延なし）が多い |
| `deactivation_condition_type` | `None` が多い |
