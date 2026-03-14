# 新人魔法少女 桜木 カナ（chara_mag_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_mag_00001
> mst_series_id: mag
> 作品名: 株式会社マジルミエ

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_mag_00001` |
| mst_series_id | `mag` |
| 作品名 | 株式会社マジルミエ |
| asset_key | `chara_mag_00001` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

normalクエストのNormal難易度に限定すると、桜木 カナは2ステージ（`normal_mag_00006`・`normal_glo4_00002`）でボス（Boss / Attack / Blue）として登場する。どちらのステージでもフレンドユニットが一定数倒されたタイミング（`FriendUnitDead`）で召喚されるラストボス的な位置づけを持つ。

HPは 320,000〜350,000 と高い耐久力を持ち、攻撃力は 800〜1,200 の範囲に分布する。`as4`（アシスト4段階目相当）バリエーションでは HP が上昇し攻撃間隔も長くなる一方、攻撃力は低下するという特徴がある。変身設定はなく、アビリティも未設定である。

コマ効果は両ステージともすべて None であり、コマ構成による追加ギミックは設けられていない。

---

## 3. ステージ別使用実態

### normal_mag_00006（メインクエスト Normal）

#### このステージでの役割

フレンドユニットが1体倒れると出現するラストボス。通常バリエーション（`c_mag_00001_general_Boss_Blue`）が採用されており、高HPと接近攻撃で持続的な圧力をかける。同タイミングでつらら系ザコ（`e_mag_00101_general2`）が大量召喚されるため、ボス単体の処理に集中できない構造になっている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_mag_00001_general_Boss_Blue` | Boss | Attack | Blue | 320,000 | 1,200 | 45 | 0.4 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_mag_00001_general_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**c_mag_00001_general_Boss_Blue**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 89 |
| Special | 0 | なし | なし | 117 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|---------------------|-----------------|--------|-------------|---------|----------------|-------------|
| c_mag_00001_general_Boss_Blue_Appearance_00001 | 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 0.0 | None |
| c_mag_00001_general_Boss_Blue_Normal_00000 | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0 | None |
| c_mag_00001_general_Boss_Blue_Special_00002 | 1〜5 | Direct | 0.5 | 100 | Foe | Damage | Normal | 50.0 | None |
| c_mag_00001_general_Boss_Blue_Special_00002 | 6 | Direct | 0.5 | 100 | Foe | Damage | KnockBack1 | 50.0 | None |
| c_mag_00001_general_Boss_Blue_Special_00002 | 7 | Direct | 0.5 | 1 | Self | None | Normal | 0.0 | AttackPowerUp（永続・効果50） |

登場時（Appearance）は自身を中心とした広範囲ノックバック（ForcedKnockBack5）を発動。通常攻撃は近距離1体Damage。スペシャル攻撃は全体に5連続ヒットの後、ノックバック付きの追撃を行い、最後に自身の攻撃力を永続強化（AttackPowerUp）する。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 1
sequence_element_id: 2
action_type: SummonEnemy
action_value: c_mag_00001_general_Boss_Blue
summon_position: （デフォルト）
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: （なし）
```

フレンドユニットが1体倒れた時点で即座に召喚される。同条件で `e_mag_00101_general2` のザコ群が大量に湧くため、ボス出現と同時に戦場が一気に混雑する。

#### コマ効果

コマ効果なし（全コマ効果が None）。

---

### normal_glo4_00002（メインクエスト Normal）

#### このステージでの役割

フレンドユニットが3体倒れると登場する終盤ボス。`as4`（強化版）バリエーション（`c_mag_00001_general_as4_Boss_Blue`）が採用されており、HPが350,000と通常版より高いが攻撃力は800に抑えられている。経過時間トリガーで呼び込まれた複数キャラとの同時対処が求められる難度設計。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_mag_00001_general_as4_Boss_Blue` | Boss | Attack | Blue | 350,000 | 800 | 45 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_mag_00001_general_as4_Boss_Blue` | なし | None | なし | なし |

#### 攻撃パターン

**c_mag_00001_general_as4_Boss_Blue**

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 89 |
| Special | 0 | なし | なし | 117 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|---------------------|-----------------|--------|-------------|---------|----------------|-------------|
| c_mag_00001_general_as4_Boss_Blue_Appearance_00001 | 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 0.0 | None |
| c_mag_00001_general_as4_Boss_Blue_Normal_00000 | 1 | Direct | 0.6 | 1 | Foe | Damage | Normal | 100.0 | None |
| c_mag_00001_general_as4_Boss_Blue_Special_00002 | 1〜4 | Direct | 0.7 | 100 | Foe | Damage | Normal | 50.0 | None |
| c_mag_00001_general_as4_Boss_Blue_Special_00002 | 5〜6 | Direct | 0.7 | 100 | Foe | Damage | Normal | 100.0 | None |

通常版と比較して攻撃レンジが若干広くなっており（Normal: 0.5→0.6、Special: 0.5→0.7）、スペシャル攻撃後半2発が強化されている（power_parameter 50→100）。一方、攻撃力強化の自己バフ（AttackPowerUp）は本バリエーションには存在せず、自己強化なしのシンプルな全体攻撃構成になっている。

#### シーケンス設定

```
condition_type: FriendUnitDead
condition_value: 3
sequence_element_id: 11
action_type: SummonEnemy
action_value: c_mag_00001_general_as4_Boss_Blue
summon_position: （デフォルト）
summon_count: 1
enemy_hp_coef: 1
sequence_group_id: （なし）
```

フレンドユニットが3体倒れた段階で召喚される。同タイミングで `c_mag_00101_general_as4_Normal_Blue` も召喚されるため、ボスが同時に複数体出現する。sequence_element_id が 11 と後半に位置し、ステージの最終局面での登場となる。

#### コマ効果

コマ効果なし（全コマ効果が None）。
