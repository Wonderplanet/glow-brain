# 好本 静（chara_kim_00301）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_kim_00301
> mst_series_id: kim
> 作品名: 君のことが大大大大大好きな100人の彼女

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_kim_00301` |
| mst_series_id | `kim` |
| 作品名 | 君のことが大大大大大好きな100人の彼女 |
| asset_key | `chara_kim_00301` |
| is_phantomized | `1` |

**キャラクター説明:**
愛城 恋太郎の彼女の1人。高校1年生。読書が大好きでお淑やかな図書委員。会話が苦手なため、愛読書の台詞を引用して話す。愛城 恋太郎からもらったスマホのテキスト読み上げアプリによってさらにコミュニケーションがスムーズになった。

---

## 2. キャラクター特徴まとめ

好本 静は「君のことが大大大大大好きな100人の彼女」作品のcキャラ（フレンドユニット）として実装されている。**normalクエストのNormal難易度での使用実績はない**（本ドキュメント作成時点でフィルタ対象データなし）。

使用コンテンツは**イベント**（9ステージ）と**降臨バトル**（1ステージ）に限定される。

ステータスの傾向として、Normalユニットは HP 1,000 / 攻撃力 100 の軽量設定、Bossユニットは難易度に応じて HP 10,000〜100,000 / 攻撃力 100〜300 と幅広いレンジがある。role_type はすべて **Support**（支援型）で、攻撃だけでなく味方へのバフ・回復スキルが特徴的。

変身設定（transformationConditionType）は全パラメータで `None` であり、変身なし。アビリティ（mst_unit_ability_id1）も全パラメータ未設定。

コマ効果は全ステージ合計で **Gust** が最多（9回）、次いで **AttackPowerDown**（5回）が使われており、プレイヤー側への妨害系効果が目立つ。

---

## 3. コンテンツフィルタ適用結果

**フィルタ: normalクエストのNormal難易度のみ**

`normal_` プレフィックスのインゲームIDにおいて `chara_kim_00301` の使用実績は**0件**です。

以下「ステージ別使用実態」には、参考情報として全コンテンツのデータを記載します。

---

## 4. ステージ別使用実態（参考: 全コンテンツ）

### event_kim1_challenge_00003（イベント）

#### このステージでの役割

好本 静（Boss / Yellow）がイベントのチャレンジステージで主役ボスとして初期配置される。ステージ開幕と拠点へのダメージ到達時に合計2回出現し、サポート型ボスとして自陣フレンドを強化する役割を担う。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_challenge_Boss_Yellow` | Boss | Support | Yellow | 50,000 | 300 | 40 | 0.18 | 2 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_challenge_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Appearance | 1 | Direct | None | ForcedKnockBack5 | Percentage | 100.0 | Foe / All | None | 0 | - |
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | Heal | Normal | MaxHpPercentage | 20.0 | Friend / All | None | 0 | - |

> Special攻撃は味方全体のHP20%回復（MaxHpPercentage）。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00301_kim1_challenge_Boss_Yellow
summon_position: 0.85
summon_count: 1
enemy_hp_coef: 5
sequence_group_id: （なし）

sequence_element_id: 6
condition_type: OutpostDamage
condition_value: 1
action_type: SummonEnemy
action_value: c_kim_00301_kim1_challenge_Boss_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 7
sequence_group_id: （なし）
```

開幕（InitialSummon）に位置 0.85 で1体出現し、拠点への初ダメージ到達時（OutpostDamage=1）に追加1体出現する2段構成。HP倍率は5〜7と高く、タフなボスとして機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（None のみ） | - | - |

---

### event_kim1_charaget01_00002（イベント）

#### このステージでの役割

好本 静（Boss / Colorless）がキャラゲットイベント第1弾ステージ2に登場。経過時間でボス単体が1体召喚され、時間差で仲間が死亡した際にさらに追加される構成。低い enemy_hp_coef（2）で召喚されており、序盤の軽量ボスとして配置されている。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget01_Boss_Colorless` | Boss | Support | Colorless | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget01_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Appearance | 1 | Direct | None | ForcedKnockBack5 | Percentage | 100.0 | Foe / All | None | 0 | - |
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | None | Normal | Percentage | 0.0 | Foe / All | None | 0 | - |
| Special | 2 | Direct | None | Normal | Percentage | 0.0 | Friend / All | AttackPowerUp | 250 | 20 |

> Special攻撃のアタック要素はダメージ0（ダミー攻撃）で、実質は2番目の味方全体攻撃力+20%バフ（250フレーム持続）が主目的。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: ElapsedTime
condition_value: 1500
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget01_Boss_Colorless
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 2
sequence_group_id: （なし）
```

経過時間1500単位後に1体出現。hp_coef=2と軽量でステージ序盤の練習ボスとして機能する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（None のみ） | - | - |

---

### event_kim1_charaget01_00004（イベント）

#### このステージでの役割

好本 静（Boss / Colorless）がキャラゲットイベント第1弾ステージ4に登場。HP99%以下（OutpostHpPercentage=99）というほぼ即時トリガーで、他のボスキャラ（花園 羽香里・院田 唐音）と同時に召喚される。3体ボスが一斉に出現するイベント後半の高難度ステージを構成する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget01_Boss_Colorless` | Boss | Support | Colorless | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget01_Boss_Colorless` | なし | None | なし | なし |

#### 攻撃パターン

（event_kim1_charaget01_00002 と同一パラメータIDのため同内容）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細**

event_kim1_charaget01_00002 と同一。Special攻撃は味方全体攻撃力+20%バフ（250フレーム持続）。

#### シーケンス設定

```
sequence_element_id: 3
condition_type: OutpostHpPercentage
condition_value: 99
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget01_Boss_Colorless
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 7
sequence_group_id: （なし）
```

拠点HPが99%以下になった瞬間（ほぼ即時）に1体出現。hp_coef=7と高く、ステージ4のボス役として機能する。花園 羽香里（hp_coef=10）・院田 唐音（hp_coef=9）と組み合わせた3体同時召喚。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（None のみ） | - | - |

---

### event_kim1_charaget02_00003（イベント）

#### このステージでの役割

好本 静（Boss / Red）がキャラゲットイベント第2弾ステージ3で開幕ボスとして出現。InitialSummonで前線（位置1.8）に即座に配置され、大量のファントムを引き連れた形式のステージで、主役ボスとして攻撃力バフを提供する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | Boss | Support | Red | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Appearance | 1 | Direct | None | ForcedKnockBack5 | Percentage | 100.0 | Foe / All | None | 0 | - |
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | None | Normal | Percentage | 0.0 | Foe / All | None | 0 | - |
| Special | 2 | Direct | None | Normal | Percentage | 0.0 | Friend / All | AttackPowerUp | 250 | 20 |

> Special攻撃は味方全体攻撃力+20%バフ（250フレーム持続）。Colorlessパラメータと同内容。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget02_Boss_Red
summon_position: 1.8
summon_count: 1
enemy_hp_coef: 6
sequence_group_id: （なし）
```

開幕即座に前線位置1.8に単体出現。hp_coef=6。以降は大量のファントム（最大60体規模）が波状に押し寄せる構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 1 | Player |

---

### event_kim1_charaget02_00004（イベント）

#### このステージでの役割

好本 静（Boss / Red）がキャラゲットイベント第2弾ステージ4で開幕ボスとして配置。前ステージと同様の構成だが、EnterTargetKomaIndex（指定コマインデックス到達）トリガーでファントムの追加召喚が加わり、コマ進行と連動した難易度設計になっている。

#### 使用パラメータ

（event_kim1_charaget02_00003 と同一パラメータID）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | Boss | Support | Red | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

event_kim1_charaget02_00003 と同一。

#### シーケンス設定

```
sequence_element_id: 1
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget02_Boss_Red
summon_position: 1.8
summon_count: 1
enemy_hp_coef: 6
sequence_group_id: （なし）
```

開幕に位置1.8で出現。コマインデックス2到達時に追加ファントムが4体×2ウェーブ召喚される仕掛けあり。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 2 | Player |

---

### event_kim1_charaget02_00005（イベント）

#### このステージでの役割

好本 静（Boss / Red）がキャラゲットイベント第2弾ステージ5に登場。別のボスキャラ（`c_kim_00001_kim1_charaget02_Boss_Red`）の登場後1000単位遅れてInitialSummonで配置される。先行ボスと組み合わさることで2ボス並立のステージを形成する。

#### 使用パラメータ

（event_kim1_charaget02_00003 と同一パラメータID）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | Boss | Support | Red | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

event_kim1_charaget02_00003 と同一。

#### シーケンス設定

```
sequence_element_id: 2
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget02_Boss_Red
summon_position: 2.6
summon_count: 1
enemy_hp_coef: 7
sequence_group_id: （なし）
```

InitialSummonで位置2.6（深め）に出現。別ボスが先にElapsedTimeで位置なし召喚されているためやや後方ポジション。hp_coef=7で耐久度はやや高め。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（None のみ） | - | - |

---

### event_kim1_charaget02_00006（イベント）

#### このステージでの役割

好本 静（Boss / Red）がキャラゲットイベント第2弾ステージ6に登場。別ボスと同様のInitialSummon（位置2.6）配置で、コマインデックス3到達時に大量ファントム（3体×2列）が援軍として出現するステージ終盤の構成。

#### 使用パラメータ

（event_kim1_charaget02_00003 と同一パラメータID）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | Boss | Support | Red | 10,000 | 100 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_charaget02_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

event_kim1_charaget02_00003 と同一。

#### シーケンス設定

```
sequence_element_id: 2
condition_type: InitialSummon
condition_value: 0
action_type: SummonEnemy
action_value: c_kim_00301_kim1_charaget02_Boss_Red
summon_position: 2.6
summon_count: 1
enemy_hp_coef: 8
sequence_group_id: （なし）
```

InitialSummonで位置2.6に出現。hp_coef=8とシリーズ最高耐久度。コマインデックス3到達で追加ファントム（3体×2）と連動する構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 2 | Player |

---

### event_kim1_savage_00002（イベント）

#### このステージでの役割

好本 静（Boss / Yellow）がサベージイベント第2弾に登場。ElapsedTimeで1200〜2500単位の間に花園 羽香里・院田 唐音と時間差で出現する3連ボス形式の最後の一体。大量のファントム（60体規模が複数ウェーブ）が継続して押し寄せる中で時間差登場する高難度構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_savage02_Boss_Yellow` | Boss | Support | Yellow | 100,000 | 300 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_savage02_Boss_Yellow` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

> Appearanceなし（登場エフェクトなし）。

**MstAttackElement詳細**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | Heal | Normal | MaxHpPercentage | 20.0 | Friend / All | None | 0 | - |

> Special攻撃は味方全体のHP20%回復（MaxHpPercentage）。チャレンジ版と同内容。

#### シーケンス設定

```
sequence_element_id: 9
condition_type: ElapsedTime
condition_value: 2500
action_type: SummonEnemy
action_value: c_kim_00301_kim1_savage02_Boss_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 4
sequence_group_id: （なし）
```

3体の中で最後に登場（花園:1200、院田:2000、好本:2500）。hp_coef=4と最も低い設定で、3体登場後の中盤処理を担う位置づけ。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| なし（None のみ） | - | - |

---

### event_kim1_savage_00003（イベント）

#### このステージでの役割

好本 静（Boss / Red）がサベージイベント第3弾に登場。ウェーブ切り替え（SwitchSequenceGroup）方式でグループw1内のFriendUnitDead=6トリガーにより出現。花園 羽香里・院田 唐音の撃破後に召喚される3番目のボスで、グループw1の最終ボスとして機能する。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_savage03_Boss_Red` | Boss | Support | Red | 100,000 | 300 | 32 | 0.3 | 3 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_savage03_Boss_Red` | なし | None | なし | なし |

#### 攻撃パターン

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | Heal | Normal | MaxHpPercentage | 20.0 | Friend / All | None | 0 | - |

> savage02と同内容。Special攻撃は味方全体のHP20%回復。

#### シーケンス設定

```
sequence_element_id: 9
condition_type: FriendUnitDead
condition_value: 6
action_type: SummonEnemy
action_value: c_kim_00301_kim1_savage03_Boss_Red
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 3
sequence_group_id: w1
```

グループw1において味方6体撃破で出現（花園:elem7、院田:elem8、好本:elem9）。hp_coef=3と3体の中で最も低い。グループ変更後さらに別のボス（`c_kim_00001`）が再登場する複雑な構成。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| AttackPowerDown | 5 | Player |

---

### raid_kim1_00001（降臨バトル）

#### このステージでの役割

好本 静が降臨バトルに2種のパラメータIDで登場する。Normal版（`c_kim_00301_kim1_advent_Normal_Yellow`）はウェーブ4でサポートボスの援軍として3回繰り返し出現する耐久ユニット、Boss版（`c_kim_00301_kim1_advent_Boss_Yellow`）はウェーブ1内でElapsedTimeSinceSequenceGroupActivated=200で1回出現するボスユニット。降臨バトル全体は5ウェーブ（w1〜w5）の多段構成。

#### 使用パラメータ

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00301_kim1_advent_Boss_Yellow` | Boss | Support | Yellow | 10,000 | 100 | 32 | 0.3 | 1 |
| `c_kim_00301_kim1_advent_Normal_Yellow` | Normal | Support | Yellow | 1,000 | 100 | 32 | 0.3 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_kim_00301_kim1_advent_Boss_Yellow` | なし | None | なし | なし |
| `c_kim_00301_kim1_advent_Normal_Yellow` | なし | None | なし | なし |

#### 攻撃パターン（Boss版）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細（Boss版）**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Appearance | 1 | Direct | None | ForcedKnockBack5 | Percentage | 100.0 | Foe / All | None | 0 | - |
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | None | Normal | Percentage | 0.0 | Foe / All | None | 0 | - |
| Special | 2 | Direct | None | Normal | MaxHpPercentage | 10.0 | Friend / All | AttackPowerUp | 300 | 100 |
| Special | 3 | Direct | None | Normal | Percentage | 100.0 | Friend / All | DamageCut | 300 | 25 |

> Boss版Specialは味方全体HP10%回復 + 攻撃力100%アップ（300フレーム）+ ダメージカット25%（300フレーム）の強力な3点バフ。

#### 攻撃パターン（Normal版）

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Normal | 0 | なし | なし | 88 |
| Special | 0 | なし | なし | 180 |

**MstAttackElement詳細（Normal版）**

| attack_kind | sort_order | attack_type | damage_type | hit_type | power_parameter_type | power_parameter | target | effect_type | effective_duration | effect_value |
|------------|-----------|------------|------------|---------|--------------------|--------------------|-------|------------|-------------------|-------------|
| Normal | 1 | Direct | Damage | Normal | Percentage | 100.0 | Foe / All | None | 0 | - |
| Special | 1 | Direct | None | Normal | Percentage | 0.0 | Foe / All | None | 0 | - |
| Special | 2 | Direct | Heal | Normal | MaxHpPercentage | 5.0 | Friend / All | AttackPowerUp | 150 | 50 |

> Normal版Specialは味方全体HP5%回復 + 攻撃力50%アップ（150フレーム）のライト版サポート。

#### シーケンス設定（Boss版: ウェーブ1）

```
sequence_element_id: 6
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 200
action_type: SummonEnemy
action_value: c_kim_00301_kim1_advent_Boss_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 8
sequence_group_id: w1
```

ウェーブ1アクティブ化から200単位後に1体出現。hp_coef=8。ウェーブ1はFriendUnitDead=4でウェーブ2へ移行。

#### シーケンス設定（Normal版: ウェーブ4）

```
sequence_element_id: 28
condition_type: ElapsedTimeSinceSequenceGroupActivated
condition_value: 500
action_type: SummonEnemy
action_value: c_kim_00301_kim1_advent_Normal_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 35
sequence_group_id: w4

sequence_element_id: 29
condition_type: FriendUnitDead
condition_value: 28
action_type: SummonEnemy
action_value: c_kim_00301_kim1_advent_Normal_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 35
sequence_group_id: w4

sequence_element_id: 30
condition_type: FriendUnitDead
condition_value: 29
action_type: SummonEnemy
action_value: c_kim_00301_kim1_advent_Normal_Yellow
summon_position: （なし）
summon_count: 1
enemy_hp_coef: 35
sequence_group_id: w4
```

ウェーブ4で最大3体が連続出現（初回ElapsedTime + 死亡リレー2回）。hp_coef=35と非常に高く、降臨バトル終盤のスタミナ勝負を演出する。

#### コマ効果

| コマ効果種別 | 使用回数 | 対象サイド |
|-----------|---------|-----------|
| Gust | 1 | Player |
| Poison | 1 | Player |
| SlipDamage | 1 | Player |

---

## 5. 全ステージ横断: コマ効果ランキング（参考）

| 順位 | コマ効果種別 | 総使用回数 |
|-----|-----------|---------|
| 1 | Gust | 9 |
| 2 | AttackPowerDown | 5 |
| 3 | Poison | 1 |
| 3 | SlipDamage | 1 |

> 集計対象: chara_kim_00301が登場する全インゲームのMstKomaLine（koma1〜4）。None は除外。

---

## 6. パラメータID一覧（全バリエーション）

| パラメータID | kind | role | color | HP | 攻撃力 | 主な使用コンテンツ |
|------------|------|------|-------|-----|--------|-----------------|
| `c_kim_00301_kim1_advent_Boss_Yellow` | Boss | Support | Yellow | 10,000 | 100 | 降臨バトル |
| `c_kim_00301_kim1_advent_Normal_Yellow` | Normal | Support | Yellow | 1,000 | 100 | 降臨バトル |
| `c_kim_00301_kim1_challenge_Boss_Yellow` | Boss | Support | Yellow | 50,000 | 300 | イベント（チャレンジ） |
| `c_kim_00301_kim1_charaget01_Boss_Colorless` | Boss | Support | Colorless | 10,000 | 100 | イベント（キャラゲット1） |
| `c_kim_00301_kim1_charaget02_Boss_Red` | Boss | Support | Red | 10,000 | 100 | イベント（キャラゲット2） |
| `c_kim_00301_kim1_savage02_Boss_Yellow` | Boss | Support | Yellow | 100,000 | 300 | イベント（サベージ2） |
| `c_kim_00301_kim1_savage03_Boss_Red` | Boss | Support | Red | 100,000 | 300 | イベント（サベージ3） |
