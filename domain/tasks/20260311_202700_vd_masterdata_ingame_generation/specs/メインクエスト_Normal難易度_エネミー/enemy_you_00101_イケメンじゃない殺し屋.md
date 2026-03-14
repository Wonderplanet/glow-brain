# イケメンじゃない殺し屋（enemy_you_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_you_00101
> mst_series_id: you
> 作品名: 幼稚園WARS

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_you_00101` |
| mst_series_id | `you` |
| 作品名 | 幼稚園WARS |
| asset_key | `enemy_you_00101` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

> **注意**: 本ドキュメントは「normalクエストのNormal難易度のみ」フィルタを適用した結果を記載しています。**`enemy_you_00101`（イケメンじゃない殺し屋）はnormalクエストのNormal難易度には出現しません。**
>
> 全コンテンツでの使用実績は以下の通りです:
> - イベント（`event_you1_charaget01_*`, `event_you1_charaget02_*`, `event_you1_savage_*`）: 計14ステージ
> - 降臨バトル（`raid_you1_00001`）: 1ステージ

フィルタ対象（normalクエストのNormal難易度）でのデータが存在しないため、ステージ別使用実態セクションは記載なし。

---

## 3. ステータスバリエーション（参考: 全パラメータ一覧）

> フィルタ「normalクエストのNormal難易度のみ」では該当パラメータが存在しないため、以下は参考情報として全パラメータを記載します。フィルタ適用環境での使用実績はありません。

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_you_00101_you1_advent_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 100 | 30 | 0.6 | 5 |
| `e_you_00101_you1_advent_Normal_Green` | Normal | Attack | Green | 1,000 | 100 | 30 | 0.6 | 3 |
| `e_you_00101_you1_charaget01_Normal_Yellow` | Normal | Attack | Yellow | 1,000 | 100 | 30 | 0.4 | 2 |
| `e_you_00101_you1_charaget02_Normal_Red` | Normal | Attack | Red | 1,000 | 100 | 30 | 0.6 | 2 |
| `e_you_00101_you1_savage01_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 30 | 0.43 | 2 |
| `e_you_00101_you1_savage01_Normal_Green` | Normal | Attack | Green | 10,000 | 100 | 30 | 0.43 | 2 |

**アビリティ・変身設定（全パラメータ）**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `e_you_00101_you1_advent_Boss_Colorless` | なし | None | なし | なし |
| `e_you_00101_you1_advent_Normal_Green` | なし | None | なし | なし |
| `e_you_00101_you1_charaget01_Normal_Yellow` | なし | None | なし | なし |
| `e_you_00101_you1_charaget02_Normal_Red` | なし | None | なし | なし |
| `e_you_00101_you1_savage01_Normal_Colorless` | なし | None | なし | なし |
| `e_you_00101_you1_savage01_Normal_Green` | なし | None | なし | なし |

---

## 4. 攻撃パターン（参考: 全パラメータ分）

| mst_unit_id | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| `e_you_00101_you1_advent_Boss_Colorless` | Appearance | 0 | なし | なし | 50 | 0 | 0 |
| `e_you_00101_you1_advent_Boss_Colorless` | Normal | 0 | なし | なし | 70 | 28 | 20 |
| `e_you_00101_you1_advent_Normal_Green` | Normal | 0 | なし | なし | 70 | 28 | 20 |
| `e_you_00101_you1_charaget01_Normal_Yellow` | Normal | 0 | なし | なし | 70 | 28 | 20 |
| `e_you_00101_you1_charaget02_Normal_Red` | Normal | 0 | なし | なし | 64 | 28 | 70 |
| `e_you_00101_you1_savage01_Normal_Colorless` | Normal | 0 | なし | なし | 70 | 28 | 20 |
| `e_you_00101_you1_savage01_Normal_Green` | Normal | 0 | なし | なし | 70 | 28 | 20 |

**MstAttackElement 詳細**

| mst_attack_id | sort_order | attack_type | range_end_parameter | max_target_count | target | damage_type | hit_type | power_parameter | effect_type |
|--------------|-----------|------------|-------------------|-----------------|--------|------------|---------|----------------|------------|
| `e_you_00101_you1_advent_Boss_Colorless_Appearance_00001` | 1 | Direct | 50.0 | 100 | Foe | None | ForcedKnockBack5 | 100.0% | None |
| `e_you_00101_you1_advent_Boss_Colorless_Normal_00000` | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0% | None |
| `e_you_00101_you1_advent_Boss_Colorless_Normal_00000` | 2 | Direct | 0.5 | 1 | Foe | None | KnockBack2 | 0.0% | None |
| `e_you_00101_you1_advent_Normal_Green_Normal_00000` | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0% | None |
| `e_you_00101_you1_charaget01_Normal_Yellow_Normal_00000` | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0% | None |
| `e_you_00101_you1_charaget02_Normal_Red_Normal_00000` | 1 | Direct | 0.62 | 1 | Foe | Damage | Normal | 100.0% | None |
| `e_you_00101_you1_savage01_Normal_Colorless_Normal_00000` | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0% | None |
| `e_you_00101_you1_savage01_Normal_Green_Normal_00000` | 1 | Direct | 0.5 | 1 | Foe | Damage | Normal | 100.0% | None |

---

## 5. ステージ別使用実態

**フィルタ「normalクエストのNormal難易度のみ」の対象ステージでの出現実績はありません。**

参考として、全コンテンツでの使用実績を以下に示します。

| コンテンツ種別 | インゲームID | 使用パラメータID |
|-------------|------------|--------------|
| イベント | `event_you1_charaget01_00001` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget01_00002` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget01_00003` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget01_00004` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget01_00005` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget01_00006` | `e_you_00101_you1_charaget01_Normal_Yellow` |
| イベント | `event_you1_charaget02_00002` | `e_you_00101_you1_charaget02_Normal_Red` |
| イベント | `event_you1_charaget02_00003` | `e_you_00101_you1_charaget02_Normal_Red` |
| イベント | `event_you1_charaget02_00004` | `e_you_00101_you1_charaget02_Normal_Red` |
| イベント | `event_you1_charaget02_00005` | `e_you_00101_you1_charaget02_Normal_Red` |
| イベント | `event_you1_charaget02_00006` | `e_you_00101_you1_charaget02_Normal_Red` |
| イベント | `event_you1_savage_00001` | `e_you_00101_you1_savage01_Normal_Green`, `e_you_00101_you1_savage01_Normal_Colorless` |
| イベント | `event_you1_savage_00002` | `e_you_00101_you1_savage01_Normal_Colorless`, `e_you_00101_you1_savage01_Normal_Green` |
| イベント | `event_you1_savage_00003` | `e_you_00101_you1_savage01_Normal_Green`, `e_you_00101_you1_savage01_Normal_Colorless` |
| 降臨バトル | `raid_you1_00001` | `e_you_00101_you1_advent_Boss_Colorless`, `e_you_00101_you1_advent_Normal_Green` |
