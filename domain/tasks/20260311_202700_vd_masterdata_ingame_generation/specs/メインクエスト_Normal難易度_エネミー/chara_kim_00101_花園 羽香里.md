# 花園 羽香里（chara_kim_00101）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_kim_00101
> mst_series_id: kim
> 作品名: 君のことが大大大大大好きな100人の彼女

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_kim_00101` |
| mst_series_id | `kim` |
| 作品名 | 君のことが大大大大大好きな100人の彼女 |
| asset_key | `chara_kim_00101` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

花園 羽香里は「君のことが大大大大大好きな100人の彼女」シリーズのキャラクターで、**メインクエストNormal難易度（`normal_`プレフィックス）では一切登場しない**。

全コンテンツを通じると、イベント（event_kim1系）と降臨バトル（raid_kim1_00001）のみに登場する。is_phantomizedが1のため幻影化キャラクターに分類される（`c_`プレフィックスのパラメータIDを使用）。

- **コンテンツ分布**: イベント専用キャラクター（12ステージ）+ 降臨バトル（1ステージ）に限定
- **HP・攻撃力のレンジ**: Normal種は HP 1,000〜10,000 / 攻撃力 100〜300、Boss種は HP 10,000〜100,000 / 攻撃力 100〜500 と幅広い
- **character_unit_kindの傾向**: Normal（雑魚）とBoss（ボス）の両方に登場
- **role_typeの傾向**: Attack中心。Boss種1体のみ Defense（savage02）
- **変身設定**: 全パラメータで変身なし（`transformationConditionType=None`）
- **アビリティ**: 全パラメータでアビリティ未設定
- **攻撃パターン**: 直接攻撃（Direct）のみ。Normalはシンプルな単体攻撃＋多段スペシャル。Bossは出現時ノックバック（ForcedKnockBack5）+ 多段スペシャル（一部AttackPowerUpやDamageCut等の自己バフ付き）

---

## 3. メインクエストNormal難易度での使用実績

**対象コンテンツ（メインクエストNormal難易度、`normal_`プレフィックス）への登場なし。**

`chara_kim_00101`（花園 羽香里）は、現時点でリリース済みのメインクエストNormal難易度ステージには一切登場しない。全使用実績はイベントおよび降臨バトルに限られる。

---

## 4. 参考: 全コンテンツでの登場ステージ一覧

> ※ コンテンツフィルタ（normalクエストNormal難易度）の対象外データ。参考情報として記載。

| インゲームID | コンテンツ種別 | 使用パラメータID | kind | role | color |
|------------|------------|----------------|------|------|-------|
| `event_kim1_1day_00001` | イベント | `c_kim_00101_kim1_1d1c_Normal_Colorless` | Normal | Attack | Colorless |
| `event_kim1_challenge_00001` | イベント | `c_kim_00101_kim1_challenge_Boss_Green` | Boss | Attack | Green |
| `event_kim1_challenge_00004` | イベント | `c_kim_00101_kim1_challenge_Normal_Green` | Normal | Attack | Green |
| `event_kim1_charaget01_00001` | イベント | `c_kim_00101_kim1_charaget01_Boss_Colorless` | Boss | Attack | Colorless |
| `event_kim1_charaget01_00002` | イベント | `c_kim_00101_kim1_charaget01_Normal_Colorless` | Normal | Attack | Colorless |
| `event_kim1_charaget01_00003` | イベント | `c_kim_00101_kim1_charaget01_Boss_Colorless` | Boss | Attack | Colorless |
| `event_kim1_charaget01_00004` | イベント | `c_kim_00101_kim1_charaget01_Boss_Colorless` | Boss | Attack | Colorless |
| `event_kim1_charaget02_00001` | イベント | `c_kim_00101_kim1_charaget02_Boss_Red` | Boss | Attack | Red |
| `event_kim1_charaget02_00002` | イベント | `c_kim_00101_kim1_charaget02_Boss_Red` | Boss | Attack | Red |
| `event_kim1_savage_00001` | イベント | `c_kim_00101_kim1_savage01_Boss_Blue` | Boss | Attack | Blue |
| `event_kim1_savage_00002` | イベント | `c_kim_00101_kim1_savage02_Boss_Yellow` | Boss | Defense | Yellow |
| `event_kim1_savage_00003` | イベント | `c_kim_00101_kim1_savage03_Boss_Red` | Boss | Attack | Red |
| `raid_kim1_00001` | 降臨バトル | `c_kim_00101_kim1_advent_Normal_Red` | Normal | Attack | Red |
| `raid_kim1_00001` | 降臨バトル | `c_kim_00101_kim1_advent_Boss_Red` | Boss | Attack | Red |

---

## 5. 参考: 全パラメータ一覧

> ※ コンテンツフィルタ（normalクエストNormal難易度）の対象外データ。参考情報として記載。

### Normal種

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00101_kim1_1d1c_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 35 | 0.21 | 1 |
| `c_kim_00101_kim1_advent_Normal_Red` | Normal | Attack | Red | 1,000 | 100 | 35 | 0.24 | 1 |
| `c_kim_00101_kim1_challenge_Normal_Green` | Normal | Attack | Green | 10,000 | 300 | 35 | 0.23 | 2 |
| `c_kim_00101_kim1_charaget01_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 30 | 0.23 | 2 |

### Boss種

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_kim_00101_kim1_advent_Boss_Red` | Boss | Attack | Red | 10,000 | 100 | 35 | 0.24 | 1 |
| `c_kim_00101_kim1_challenge_Boss_Green` | Boss | Attack | Green | 50,000 | 300 | 35 | 0.23 | 2 |
| `c_kim_00101_kim1_charaget01_Boss_Colorless` | Boss | Attack | Colorless | 10,000 | 100 | 30 | 0.23 | 2 |
| `c_kim_00101_kim1_charaget02_Boss_Red` | Boss | Attack | Red | 10,000 | 100 | 30 | 0.23 | 2 |
| `c_kim_00101_kim1_savage01_Boss_Blue` | Boss | Attack | Blue | 100,000 | 500 | 30 | 0.27 | 2 |
| `c_kim_00101_kim1_savage02_Boss_Yellow` | Boss | Defense | Yellow | 100,000 | 500 | 30 | 0.17 | 2 |
| `c_kim_00101_kim1_savage03_Boss_Red` | Boss | Attack | Red | 100,000 | 500 | 30 | 0.23 | 2 |

**アビリティ・変身設定（全パラメータ共通）**

| 項目 | 値 |
|------|-----|
| アビリティ(mst_unit_ability_id1) | なし |
| 変身条件(transformationConditionType) | None |
| 変身条件値 | なし |
| 変身後パラメータID | なし |

---

## 6. 参考: 攻撃パターン（全パラメータ）

> ※ コンテンツフィルタ（normalクエストNormal難易度）の対象外データ。参考情報として記載。

### Normal種の攻撃パターン

#### Normal攻撃（単体近接）

| パラメータID | attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|------------|-----------|--------------|------------------|--------------|------------|-------------------|
| `c_kim_00101_kim1_1d1c_Normal_Colorless` | Normal | 0 | なし | なし | 62 | 13 | 50 |
| `c_kim_00101_kim1_advent_Normal_Red` | Normal | 0 | なし | なし | 62 | 13 | 50 |
| `c_kim_00101_kim1_challenge_Normal_Green` | Normal | 0 | なし | なし | 62 | 13 | 60 |
| `c_kim_00101_kim1_charaget01_Normal_Colorless` | Normal | 0 | なし | なし | 62 | 13 | 50 |

全Normal種のNormal攻撃はDirect/Damage/Percentage 100%の単体攻撃。攻撃範囲はパラメータにより0.21〜0.28。

#### スペシャル攻撃（多段全体攻撃）

全Normal種で共通して5段スペシャル攻撃を持つ。各段はDirect/Damage/Percentageで対全体を攻撃。

- `kim1_1d1c_Normal_Colorless` / `kim1_charaget01_Normal_Colorless`: 各段 60%、射程0.38
- `kim1_advent_Normal_Red`: 各段 5%/5%/10%/30%/50%（段階的に倍率増加）+ 自己AttackPowerUp（+50%、持続500フレーム）
- `kim1_challenge_Normal_Green`: 各段 60%、射程0.30

### Boss種の攻撃パターン

#### 出現時攻撃（Appearance）

- savage03以外の全Boss種に出現時ノックバック攻撃あり
- attack_kind: Appearance、effect: ForcedKnockBack5、射程50.0（全画面）、対全体

| パラメータID | Appearance有無 |
|------------|--------------|
| `c_kim_00101_kim1_advent_Boss_Red` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_challenge_Boss_Green` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_charaget01_Boss_Colorless` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_charaget02_Boss_Red` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_savage01_Boss_Blue` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_savage02_Boss_Yellow` | あり（ForcedKnockBack5） |
| `c_kim_00101_kim1_savage03_Boss_Red` | なし |

#### スペシャル攻撃の特徴

| パラメータID | スペシャル特徴 |
|------------|-------------|
| `c_kim_00101_kim1_advent_Boss_Red` | 5段階段的増加（5%/5%/10%/30%/50%）+ 自己AttackPowerUp（+200%、持続250フレーム） |
| `c_kim_00101_kim1_challenge_Boss_Green` | 4段 60%+最終段Stun（スタン付き） |
| `c_kim_00101_kim1_charaget01_Boss_Colorless` | 5段 60% |
| `c_kim_00101_kim1_charaget02_Boss_Red` | 5段 60%（1段目のみ射程0.47、他0.38） |
| `c_kim_00101_kim1_savage01_Boss_Blue` | 5段 60%＋自己AttackPowerUp（+10%、持続500フレーム） |
| `c_kim_00101_kim1_savage02_Boss_Yellow` | 5段 30%（Defense種）＋自己DamageCut（30%、持続1000フレーム） |
| `c_kim_00101_kim1_savage03_Boss_Red` | 5段 60% |
