# 密輸組織の残党（enemy_spy_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_spy_00001
> mst_series_id: spy
> 対象: メインクエスト Normal難易度（5ステージ）

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_spy_00001` |
| mst_series_id | `spy` |
| asset_key | `enemy_spy_00001` |
| is_phantomized | `0` |
| 日本語名 | 密輸組織の残党 |
| 説明 | \<黄昏\>が襲撃した密輸組織の残党。奪われた美術品を取り戻すため、仕込んだ発信機を元に追跡したが返り討ちにあう。 |

---

## 2. キャラクター特徴まとめ

### ステータス・攻撃の傾向

5ステージ全てで `general_n` 系パラメータのみが使用されており、HP 1,000・攻撃力 50・移動速度 34 の固定ステータス（変身・アビリティ・キラーなし）が基本。攻撃は近接単体のシンプルな直接攻撃のみ。ステータス自体は低く設定しておき、**enemy_hp_coef で場ごとに耐久力を調整する**設計思想が見られる（1.5〜7.0 の幅）。

### シーケンス・配置の傾向

| パターン | 採用ステージ | 特徴 |
|--------|-----------|------|
| 経過時間による大量追加召喚 | 全5ステージ | ElapsedTime でほぼ必ず中盤〜後半に追加波が来る |
| 初期配置（InitialSummon） | normal_spy_00003, 00005 | 位置指定で開幕から前線に展開 |
| FriendUnitDeadによるエスカレーション | normal_spy_00003, 00004, 00006 | フレンドが削れるほど召喚数・グループが増加する |
| Bossへのエスカレーション | normal_spy_00003 のみ | FriendUnitDead 2体でBossが出現、登場時ノックバック演出あり |

### 役割の傾向

- **単体ステージ（00004）**: このキャラ1種のみで構成、物量エスカレーション型の純粋な数押し
- **複合ステージ（glo1_00002, 00005）**: 他キャラの補助的な追加波として機能
- **主役ステージ（00003, 00006）**: このキャラが主力として大量出現、ステージの難易度の中核を担う

### コマ効果の傾向

| ステージ | コマ効果 | 方向性 |
|--------|---------|------|
| normal_glo1_00002 | AttackPowerDown(Player) | プレイヤーの火力を落とす |
| normal_spy_00003 | AttackPowerUp(Player) | プレイヤーをバフして戦いやすくする |
| normal_spy_00004 | なし | 純粋な物量勝負 |
| normal_spy_00005 | なし | 純粋な物量勝負 |
| normal_spy_00006 | AttackPowerUp(Player) | プレイヤーをバフして戦いやすくする |

AttackPowerUp（プレイヤーへのバフ）とAttackPowerDown（プレイヤーへのデバフ）が混在しており、ステージ難易度に応じて使い分けられている。高難度・大量召喚ステージにはバフ、複合ステージにはデバフという傾向が見られる。

---

## 3. ステージ別使用実態

### normal_glo1_00002

**このステージでの役割**

SPY×FAMILY以外のキャラ（姫様"拷問"の時間です系）と混在する複合構成ステージの中で、ElapsedTime 650フレーム後に3体を1,000フレーム間隔で追加召喚する役割。enemy_hp_coef=4.5 と高めで設定されており、HP 1,000 の通常より耐久力が高い状態で登場する。コマ効果はPlayerへの AttackPowerDown が設定されており、プレイヤーの火力を落とすステージ構成。

**使用パラメータ**

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 |
|------------|------|------|-------|-----|--------|---------|---------|
| `e_spy_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.4 |

**攻撃パターン**

| attack_kind | action_frames | attack_delay | next_attack_interval | attack_type | 射程 | damage_type | effect_type |
|------------|--------------|-------------|---------------------|------------|------|------------|------------|
| Normal | 50 | 9 | 50 | Direct | 0.41 | Damage | None |

**シーケンス設定（このキャラ分のみ）**

| seq_id | condition_type | condition_value | summon_count | summon_interval | enemy_hp_coef | sequence_group_id |
|--------|--------------|----------------|-------------|----------------|--------------|------------------|
| 3 | ElapsedTime | 650 | 3 | 1,000 | 4.5 | (なし) |

**コマ効果**

| コマ | effect_type | effect_target_side |
|-----|------------|-------------------|
| koma2 | AttackPowerDown | Player |
| koma1 | AttackPowerDown | Player |

---

### normal_spy_00003

**このステージでの役割**

SPY×FAMILYで最初に本格的にこのキャラが主役となるステージ。初期にNormalユニット1体を位置1.6に配置し、500フレーム後から10体を1,250フレーム間隔で大量召喚する主力構成。フレンドユニット2体撃破をトリガーにBossが1体出現するエスカレーション演出があり、中盤の山場を演出している。コマ効果はPlayerへのAttackPowerUpが設定されており、プレイヤーに有利なバフが入る。

**使用パラメータ**

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 |
|------------|------|------|-------|-----|--------|---------|---------|
| `e_spy_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.4 |
| `e_spy_00001_general_n_Boss_Blue` | Boss | Attack | Blue | 10,000 | 50 | 34 | 0.4 |

> BossユニットはAppearance攻撃（ForcedKnockBack5・範囲50）を持ち、登場時に全プレイヤーキャラをノックバックさせる演出がある。

**攻撃パターン**

| パラメータID | attack_kind | action_frames | attack_delay | next_attack_interval | 射程 | damage_type | effect_type |
|------------|------------|--------------|-------------|---------------------|------|------------|------------|
| general_n_Normal_Colorless | Normal | 50 | 9 | 50 | 0.41 | Damage | None |
| general_n_Boss_Blue | Appearance | 50 | 0 | 0 | 50.0 | None(ノックバック) | None |
| general_n_Boss_Blue | Normal | 50 | 9 | 40 | 0.41 | Damage | None |

**シーケンス設定（このキャラ分のみ）**

| seq_id | condition_type | condition_value | action_value（パラメータID末尾） | summon_position | summon_count | summon_interval | enemy_hp_coef |
|--------|--------------|----------------|-------------------------------|----------------|-------------|----------------|--------------|
| 1 | ElapsedTime | 500 | …Normal_Colorless | (ランダム) | 10 | 1,250 | 1.5 |
| 2 | InitialSummon | 1 | …Normal_Colorless | 1.6 | 1 | 0 | 1.5 |
| 3 | FriendUnitDead | 2 | …Boss_Blue | (ランダム) | 1 | 0 | 1.5 |

**コマ効果**

| コマ | effect_type | effect_target_side |
|-----|------------|-------------------|
| koma1 | AttackPowerUp | Player |
| koma2 | AttackPowerUp | Player |

---

### normal_spy_00004

**このステージでの役割**

このキャラ1種のみで構成された集中特化ステージ。フレンドユニットを撃破するたびにグループが切り替わり、召喚数が 1→2→1→2→3→99 とエスカレートする。最終フェーズ（group5）では summon_count=99・summon_interval=1,000 の大量波状攻撃となり、フレンドユニットが全滅に近づくほど圧倒的な物量で圧力をかける設計。コマ効果は設定なし。

**使用パラメータ**

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 |
|------------|------|------|-------|-----|--------|---------|---------|
| `e_spy_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.4 |

**攻撃パターン**

| attack_kind | action_frames | attack_delay | next_attack_interval | attack_type | 射程 | damage_type | effect_type |
|------------|--------------|-------------|---------------------|------------|------|------------|------------|
| Normal | 50 | 9 | 50 | Direct | 0.41 | Damage | None |

**シーケンス設定（このキャラ分のみ）**

| seq_id | condition_type | condition_value | summon_count | summon_interval | enemy_hp_coef | sequence_group_id |
|--------|--------------|----------------|-------------|----------------|--------------|------------------|
| 1 | ElapsedTime | 0 | 1 | 0 | 1.5 | (なし) |
| 2 | ElapsedTimeSinceSequenceGroupActivated | 100 | 2 | 50 | 1.5 | group1 |
| 3 | ElapsedTimeSinceSequenceGroupActivated | 100 | 1 | 50 | 1.5 | group2 |
| 4 | ElapsedTimeSinceSequenceGroupActivated | 100 | 2 | 50 | 1.5 | group3 |
| 5 | ElapsedTimeSinceSequenceGroupActivated | 100 | 3 | 100 | 1.5 | group4 |
| 6 | ElapsedTimeSinceSequenceGroupActivated | 500 | 99 | 1,000 | 1.5 | group5 |

> グループ切り替え条件（SwitchSequenceGroup）: FriendUnitDead 1→group1 / 2→group2 / 3→group3 / 4→group4 / 5→group5

**コマ効果**

なし（全コマ None）

---

### normal_spy_00005

**このステージでの役割**

ステージ開始と同時に3か所（位置 0.9 / 1.3 / 1.6）へ1体ずつ分散配置し、500フレーム後に15体を750フレーム間隔で追加召喚する2段構成。他のフレンドキャラ（c_spy_00401・c_spy_00201）と並行して進むステージで、このキャラは開幕の圧力と中盤の物量を担う。コマ効果は設定なし。

**使用パラメータ**

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 |
|------------|------|------|-------|-----|--------|---------|---------|
| `e_spy_00001_general_n_Normal_Colorless` | Normal | Attack | Colorless | 1,000 | 50 | 34 | 0.4 |

**攻撃パターン**

| attack_kind | action_frames | attack_delay | next_attack_interval | attack_type | 射程 | damage_type | effect_type |
|------------|--------------|-------------|---------------------|------------|------|------------|------------|
| Normal | 50 | 9 | 50 | Direct | 0.41 | Damage | None |

**シーケンス設定（このキャラ分のみ）**

| seq_id | condition_type | condition_value | summon_position | summon_count | summon_interval | enemy_hp_coef |
|--------|--------------|----------------|----------------|-------------|----------------|--------------|
| 3 | InitialSummon | 0 | 0.9 | 1 | 0 | 1.5 |
| 4 | InitialSummon | 0 | 1.6 | 1 | 0 | 1.5 |
| 5 | InitialSummon | 0 | 1.3 | 1 | 0 | 1.5 |
| 6 | ElapsedTime | 500 | (ランダム) | 15 | 750 | 1.5 |

**コマ効果**

なし（全コマ None）

---

### normal_spy_00006

**このステージでの役割**

Defenseロール（Blue）に変わる唯一のステージ。enemy_hp_coef=7 と全5ステージ中で最も高い倍率が設定されており、HP 1,000 × 7 = 実質 7,000 相当の耐久力を持つ。フレンドユニット撃破ごとにグループが切り替わり、各フェーズで継続的に3〜4体ずつ追加召喚される持久戦設計。コマ効果はPlayerへのAttackPowerUpが設定されており、プレイヤーに有利な状態で戦う。

**使用パラメータ**

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 |
|------------|------|------|-------|-----|--------|---------|---------|
| `e_spy_00001_general_n_Normal_Blue` | Normal | Defense | Blue | 1,000 | 50 | 34 | 0.4 |

**攻撃パターン**

| attack_kind | action_frames | attack_delay | next_attack_interval | attack_type | 射程 | damage_type | effect_type |
|------------|--------------|-------------|---------------------|------------|------|------------|------------|
| Normal | 50 | 9 | 50 | Direct | 0.41 | Damage | None |

**シーケンス設定（このキャラ分のみ）**

| seq_id | condition_type | condition_value | summon_count | summon_interval | enemy_hp_coef | sequence_group_id |
|--------|--------------|----------------|-------------|----------------|--------------|------------------|
| 2 | ElapsedTime | 150 | 3 | 750 | 7 | (なし) |
| 4 | ElapsedTimeSinceSequenceGroupActivated | 0 | 1 | 50 | 7 | group1 |
| 7 | ElapsedTimeSinceSequenceGroupActivated | 300 | 2 | 500 | 7 | group2 |
| 8 | ElapsedTimeSinceSequenceGroupActivated | 1,500 | 3 | 250 | 7 | group2 |
| 11 | ElapsedTime | 250 | 3 | 750 | 7 | (なし) |
| 12 | ElapsedTimeSinceSequenceGroupActivated | 500 | 4 | 500 | 7 | group2 |

> グループ切り替え条件（SwitchSequenceGroup）: FriendUnitDead 2→group1 / 3→group2

**コマ効果**

| コマ | effect_type | effect_target_side |
|-----|------------|-------------------|
| koma1 | AttackPowerUp | Player |
| koma2 | AttackPowerUp | Player |
