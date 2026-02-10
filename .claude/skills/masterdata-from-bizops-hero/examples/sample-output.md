# ヒーロー マスタデータ サンプル出力

このファイルは、ヒーローマスタデータ作成スキルの出力例を示します。

## 前提条件

- **リリースキー**: 202601010
- **シリーズID**: jig (地獄楽)
- **キャラクター**: 賊王 亜左 弔兵衛
- **ユニットID**: chara_jig_00401
- **レアリティ**: UR
- **ユニットラベル**: PremiumUR (ガチャ排出)
- **has_specific_rank_up**: 0 (イベント配布キャラではない)
- **is_enemy_character**: 0 (敵としては登場しない)

## 出力シート一覧

以下の11シートが出力されます(has_specific_rank_up=0、is_enemy_character=0のため、MstUnitSpecificRankUp、MstEnemyCharacter系は出力されません):

1. MstUnit
2. MstUnitI18n
3. MstUnitAbility
4. MstAbility
5. MstAbilityI18n
6. MstAttack
7. MstAttackElement
8. MstAttackI18n
9. MstSpecialAttackI18n
10. MstSpeechBalloonI18n

---

## 1. MstUnit シート

| ENABLE | id | fragment_mst_item_id | role_type | color | attack_range_type | unit_label | has_specific_rank_up | mst_series_id | asset_key | rarity | sort_order | summon_cost | summon_cool_time | special_attack_initial_cool_time | special_attack_cool_time | min_hp | max_hp | damage_knock_back_count | move_speed | well_distance | min_attack_power | max_attack_power | mst_unit_ability_id1 | ability_unlock_rank1 | mst_unit_ability_id2 | ability_unlock_rank2 | mst_unit_ability_id3 | ability_unlock_rank3 | is_encyclopedia_special_attack_position_right | release_key |
|--------|----|--------------------|----------|-------|------------------|-----------|---------------------|--------------|----------|--------|-----------|------------|----------------|------------------------------|-------------------------|--------|--------|------------------------|-----------|--------------|----------------|----------------|---------------------|---------------------|---------------------|---------------------|---------------------|---------------------|---------------------------------------------|-------------|
| e | chara_jig_00401 | piece_jig_00401 | Technical | Colorless | Short | PremiumUR | 0 | jig | chara_jig_00401 | UR | 1 | 1000 | 770 | 655 | 1140 | 2100 | 21000 | 3 | 30 | 0.31 | 2500 | 25000 | ability_jig_00401_01 | 0 | ability_jig_00401_02 | 4 | | 0 | 0 | 202601010 |

---

## 2. MstUnitI18n シート

| ENABLE | id | mst_unit_id | locale | unit_name | flavor_text | release_key |
|--------|----|-----------|----|-----------|------------|-------------|
| e | chara_jig_00401_i18n_ja | chara_jig_00401 | ja | 【賊王】亜左 弔兵衛 | 幕府の重罪人たちが収容された孤島「獄門島」の主。1000人以上の盗賊を束ねる、神出鬼没の「賊王」。 | 202601010 |

---

## 3. MstUnitAbility シート

| ENABLE | id | mst_unit_id | mst_ability_id | ability_parameter1 | ability_parameter2 | ability_parameter3 | release_key |
|--------|----|-----------|--------------|--------------------|--------------------|--------------------|-------------|
| e | ability_jig_00401_01 | chara_jig_00401 | ability_attack_power_up_by_hp_percentage_less | 50 | 30 | | 202601010 |
| e | ability_jig_00401_02 | chara_jig_00401 | ability_damage_cut_by_hp_percentage_over | 30 | 50 | | 202601010 |

**説明**:
- アビリティ1: HP30%以下の時、攻撃力50%UP
- アビリティ2: HP50%以上の時、ダメージ30%カット

---

## 4. MstAbility シート

| ENABLE | id | ability_type | asset_key | release_key |
|--------|----|-----------|---------|---------||
| e | ability_attack_power_up_by_hp_percentage_less | ability_attack_power_up_by_hp_percentage_less | | 202601010 |
| e | ability_damage_cut_by_hp_percentage_over | ability_damage_cut_by_hp_percentage_over | | 202601010 |

---

## 5. MstAbilityI18n シート

| ENABLE | id | mst_ability_id | locale | ability_name | ability_description | release_key |
|--------|----|--------------|----|-------------|---------------------|-------------|
| e | ability_attack_power_up_by_hp_percentage_less_i18n_ja | ability_attack_power_up_by_hp_percentage_less | ja | 低体力攻撃UP | 体力{1}%以下時に攻撃を{0}%UP | 202601010 |
| e | ability_damage_cut_by_hp_percentage_over_i18n_ja | ability_damage_cut_by_hp_percentage_over | ja | 高体力ダメージカット | 体力{1}%以上時に被ダメージを{0}%カット | 202601010 |

**説明**:
- `{0}`, `{1}` はMstUnitAbilityのparameter1、parameter2で置換されます

---

## 6. MstAttack シート

| ENABLE | id | mst_unit_id | grade | attack_kind | action_frames | release_key |
|--------|----|-----------|----|-----------|--------------|-------------|
| e | attack_jig_00401_N_0 | chara_jig_00401 | 0 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_1 | chara_jig_00401 | 1 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_2 | chara_jig_00401 | 2 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_3 | chara_jig_00401 | 3 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_4 | chara_jig_00401 | 4 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_5 | chara_jig_00401 | 5 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_6 | chara_jig_00401 | 6 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_7 | chara_jig_00401 | 7 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_8 | chara_jig_00401 | 8 | Normal | 60 | 202601010 |
| e | attack_jig_00401_N_9 | chara_jig_00401 | 9 | Normal | 60 | 202601010 |
| e | attack_jig_00401_S_0 | chara_jig_00401 | 0 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_1 | chara_jig_00401 | 1 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_2 | chara_jig_00401 | 2 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_3 | chara_jig_00401 | 3 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_4 | chara_jig_00401 | 4 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_5 | chara_jig_00401 | 5 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_6 | chara_jig_00401 | 6 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_7 | chara_jig_00401 | 7 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_8 | chara_jig_00401 | 8 | Special | 120 | 202601010 |
| e | attack_jig_00401_S_9 | chara_jig_00401 | 9 | Special | 120 | 202601010 |

**重要**: 通常攻撃(Normal)とSpecial(必殺技)、それぞれグレード0～9の10レコード、合計20レコードが必要です。

---

## 7. MstAttackElement シート

**注意**: MstAttackElementは152カラムの非常に複雑なテーブルです。ここでは主要カラムのみ表示します。

| ENABLE | id | mst_attack_id | element_order | attack_type | damage_type | power_parameter | effect_type | ... (他148カラム) |
|--------|----|--------------|--------------|-------------|-------------|-----------------|-------------|------------------|
| e | attack_jig_00401_N_0_element_0 | attack_jig_00401_N_0 | 0 | Slash | Physical | 100 | Damage | ... |
| e | attack_jig_00401_S_0_element_0 | attack_jig_00401_S_0 | 0 | Slash | Physical | 150 | Damage | ... |

**重要**: 各MstAttackレコード(20レコード)に対して、MstAttackElementを作成します。

---

## 8. MstAttackI18n シート

| ENABLE | id | mst_attack_id | locale | attack_description | release_key |
|--------|----|--------------|----|-------------------|-------------|
| e | attack_jig_00401_N_0_i18n_ja | attack_jig_00401_N_0 | ja | | 202601010 |
| e | attack_jig_00401_S_0_i18n_ja | attack_jig_00401_S_0 | ja | | 202601010 |

**注意**: attack_descriptionは通常空欄です。

---

## 9. MstSpecialAttackI18n シート

| ENABLE | id | mst_unit_id | locale | special_attack_name | release_key |
|--------|----|-----------|----|---------------------|-------------|
| e | chara_jig_00401_special_attack_i18n_ja | chara_jig_00401 | ja | 盗賊王の剣戟 | 202601010 |

---

## 10. MstSpeechBalloonI18n シート

| ENABLE | id | mst_unit_id | locale | speech_balloon_text | release_key |
|--------|----|-----------|----|---------------------|-------------|
| e | chara_jig_00401_speech_balloon_i18n_ja | chara_jig_00401 | ja | 行くぜ！ | 202601010 |

---

## 推測値レポート

### MstUnit.summon_cost
- **値**: 1000
- **理由**: 設計書にコスト値の記載がなく、レアリティUR標準値を設定
- **確認事項**: レアリティに応じた標準値か、他のURキャラと比較して確認してください

### MstUnit.summon_cool_time
- **値**: 770
- **理由**: 設計書にクールタイム値の記載がなく、レアリティUR標準値を設定
- **確認事項**: レアリティに応じた標準値か、他のURキャラと比較して確認してください

### MstAttack.action_frames
- **値**: 60(Normal)、120(Special)
- **理由**: 設計書にアクションフレーム数の記載がなく、標準値を設定
- **確認事項**: キャラクターのアニメーション速度に応じて調整が必要か確認してください

### MstSpecialAttackI18n.special_attack_name
- **値**: 盗賊王の剣戟
- **理由**: 設計書に必殺技名の記載がなく、キャラクター特性から推測
- **確認事項**: キャラクター原作に沿った必殺技名か確認してください

### MstSpeechBalloonI18n.speech_balloon_text
- **値**: 行くぜ！
- **理由**: 設計書に吹き出しセリフの記載がなく、キャラクター特性から推測
- **確認事項**: キャラクター原作に沿ったセリフか確認してください

---

## 注意事項

### グレード0～9の設定

- **MstAttack**: 通常攻撃(Normal)とSpecial(必殺技)、それぞれグレード0～9の10レコード
- **合計**: 20レコード

### has_specific_rank_up=1の場合

イベント配布キャラの場合、以下のシートが追加されます:
- **MstUnitSpecificRankUp**: rank 1～5の5レコード

### is_enemy_character=1の場合

ヒーローが敵としても登場する場合、以下のシートが追加されます:
- **MstEnemyCharacter**: 敵キャラクター基本情報
- **MstEnemyCharacterI18n**: 敵キャラクター名

---

## 参考

詳細な設定ルールとenum値一覧は [references/manual.md](../references/manual.md) を参照してください。
