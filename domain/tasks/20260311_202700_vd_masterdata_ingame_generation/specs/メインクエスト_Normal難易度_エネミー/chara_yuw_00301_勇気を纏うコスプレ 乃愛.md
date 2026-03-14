# 勇気を纏うコスプレ 乃愛（chara_yuw_00301）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_yuw_00301
> mst_series_id: yuw
> 作品名: 2.5次元の誘惑

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_yuw_00301` |
| mst_series_id | `yuw` |
| 作品名 | 2.5次元の誘惑 |
| asset_key | `chara_yuw_00301` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

コンテンツフィルタ（normalクエストのNormal難易度のみ）を適用した結果、このキャラクター（chara_yuw_00301 / 勇気を纏うコスプレ 乃愛）は**メインクエスト Normal難易度（`normal_`プレフィックスのステージ）では使用されていません**。

全コンテンツでの使用実績を参考情報として記載します。

**全コンテンツでの使用実績（参考）**:

| コンテンツ種別 | ステージ数 |
|------------|---------|
| イベント | 6 |
| 降臨バトル | 1 |
| メインクエスト Normal | 0 |

パラメータバリエーションは全5件（Normal種 2件・Boss種 3件）存在し、イベントおよび降臨バトル専用のキャラクターとして設計されています。HP は 10,000〜50,000 の幅を持ち、role_type は一貫して Technical です。

---

## 3. ステージ別使用実態

> **フィルタ適用結果**: normalクエストのNormal難易度（`normal_`プレフィックス）には該当ステージが存在しません。
>
> このキャラクターはメインクエスト Normal難易度では使用されていないため、ステージ別使用実態の記載はありません。

---

## 付録: 全パラメータ一覧（参考情報・フィルタ対象外）

> 以下は normalクエストのNormal難易度フィルタ対象外のデータです。VD設計の参考情報として記載します。

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_yuw_00301_challenge_Normal_Green` | Normal | Technical | Green | 50,000 | 300 | 29 | 0.26 | 3 |
| `c_yuw_00301_okumuraget_Normal_Colorless` | Normal | Technical | Colorless | 50,000 | 300 | 29 | 0.23 | 3 |
| `c_yuw_00301_challengeburn_Boss_Red` | Boss | Technical | Red | 50,000 | 300 | 29 | 0.26 | 3 |
| `c_yuw_00301_okumuraget_Boss_Blue` | Boss | Technical | Blue | 50,000 | 300 | 29 | 0.23 | 3 |
| `c_yuw_00301raid_00001_Boss_Red` | Boss | Technical | Red | 10,000 | 100 | 29 | 0.26 | 3 |

**アビリティ・変身設定（全パラメータ共通）**:
- アビリティ (mst_unit_ability_id1): なし
- 変身条件 (transformationConditionType): None
- 変身なし

### 攻撃パターン詳細（参考）

#### Normal種（c_yuw_00301_challenge_Normal_Green / c_yuw_00301_okumuraget_Normal_Colorless）

**Normal攻撃**:

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 79 | 0 | 50〜90 |

- attack_element (sort_order=1): Direct / range 0〜0.24 / 対象: Foe / ダメージ50% / エフェクトなし
- attack_element (sort_order=2): Direct / range 0〜0.24 / 対象: Foe / ダメージ50% / エフェクトなし
- 2ヒット構成の近距離ダイレクト攻撃

**Special攻撃**:

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Special | 0 | なし | なし | 175 | 160 | 0 |

- `c_yuw_00301_challenge_Normal_Green_Special_00001`: Direct / range 0〜0.24 / 対象: Friend (全色・全役割) / ダメージ0% / **攻撃力アップ (AttackPowerUp)**: 効果数-1・持続1,000フレーム・値30
- `c_yuw_00301_okumuraget_Normal_Colorless_Special_00001`: Direct / range 0〜0.24 / 対象: Self / ダメージ0% / **攻撃力アップ (AttackPowerUp)**: 効果数-1・持続1,000フレーム・値30

> challengeバリアントのSpecialは味方全体に攻撃力バフ、okumuraghetバリアントはセルフバフ

#### Boss種（c_yuw_00301_challengeburn_Boss_Red / c_yuw_00301_okumuraget_Boss_Blue）

**Appearance（登場時）**:
- Direct / range 0〜50.0 / 対象: Foe (最大100体) / ForcedKnockBack5 / ダメージなし
- 登場時に広範囲ノックバックを発動

**Normal攻撃**:

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Normal | 0 | なし | なし | 79 | 0 | 50〜90 |

- `c_yuw_00301_challengeburn_Boss_Red_Normal_00000`:
  - element1: Direct / range 0〜0.27 / 対象: Foe / ダメージ50% / **Burn**: 効果数-1・持続300フレーム・値3,000
  - element2: Direct / range 0〜0.27 / 対象: Foe / ダメージ50% / エフェクトなし
- `c_yuw_00301_okumuraget_Boss_Blue_Normal_00000`:
  - element1: Direct / range 0〜0.24 / 対象: Foe / ダメージ50% / エフェクトなし
  - element2: Direct / range 0〜0.24 / 対象: Foe / ダメージ50% / エフェクトなし

> challengeburnはBurn付与（炎上デバフ）を持つ、okumuraghetはシンプルな2ヒット構成

**Special攻撃**:

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|------------|-----------|--------------|------------------|--------------|-------------|---------------------|
| Special | 0 | なし | なし | 175 | 160 | 0 |

- Direct / range 0〜0.24 / 対象: Self / ダメージ0% / **攻撃力アップ (AttackPowerUp)**: 効果数-1・持続1,000フレーム・値30
- ボス種のSpecialはセルフバフのみ
