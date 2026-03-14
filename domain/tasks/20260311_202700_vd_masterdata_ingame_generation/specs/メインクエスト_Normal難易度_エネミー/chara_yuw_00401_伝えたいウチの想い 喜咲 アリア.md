# 伝えたいウチの想い 喜咲 アリア（chara_yuw_00401）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_yuw_00401
> mst_series_id: yuw
> 作品名: 2.5次元の誘惑

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_yuw_00401` |
| mst_series_id | `yuw` |
| 作品名 | 2.5次元の誘惑 |
| asset_key | `chara_yuw_00401` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

**normalクエストのNormal難易度における使用実績: なし**

`chara_yuw_00401`（伝えたいウチの想い 喜咲 アリア）は、normalクエスト（`normal_` プレフィックス）のNormal難易度ステージには一切登場しない。現時点でのインゲーム使用実績はイベント（`event_yuw1_challenge01_00004`、`event_yuw1_charaget02_00004〜00008`）と降臨バトル（`raid_yuw1_00001`）のみである。

このキャラクターはフレンドユニット系（cキャラ）として実装されており、全パラメータが `c_yuw_00401_` プレフィックスで始まる。ステータスはDefenseロールで固定され、HPは10,000〜50,000、攻撃力は100〜300のレンジ。イベントや降臨バトルでのBoss/Normal両kindで使用されており、変身設定は全バリエーションで「なし」。

---

## 3. ステージ別使用実態（参考：normalクエスト外の実績）

> **注意**: 対象フィルタ「normalクエストのNormal難易度のみ」に合致するステージは存在しない。
> 以下はフィルタ外の参考情報として記載する。

### 全パラメータバリエーション一覧（参考）

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|----|--------|---------|---------|--------------|
| `c_yuw_00401_challenge_Boss_Green` | Boss | Defense | Green | 50,000 | 300 | 30 | 0.17 | 2 |
| `c_yuw_00401_okumuraget_Boss_Blue` | Boss | Defense | Blue | 50,000 | 300 | 30 | 0.17 | 2 |
| `c_yuw_00401raid_00001_Boss_Red` | Boss | Defense | Red | 10,000 | 100 | 30 | 0.17 | 2 |
| `c_yuw_00401_challenge_Normal_Green` | Normal | Defense | Green | 50,000 | 300 | 30 | 0.17 | 2 |
| `c_yuw_00401_okumuraget_Normal_Blue` | Normal | Defense | Blue | 50,000 | 300 | 30 | 0.17 | 2 |

**アビリティ・変身設定（全バリエーション共通）**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| 全パラメータ共通 | なし | None | なし | なし |

### 攻撃パターン（参考）

attack_kindは Normal・Special・Appearance の3種が基本構成。killerColorsなし（All対象）。

| mst_unit_id | attack_kind | unit_grade | killer_colors | action_frames | 攻撃Elementの主な特徴 |
|------------|------------|-----------|--------------|--------------|----------------------|
| c_yuw_00401_challenge_Normal_Green | Normal | 0 | なし | 59 | Direct/Distance 0.18/Damage/Percentage 100% |
| c_yuw_00401_challenge_Normal_Green | Special | 0 | なし | 134 | Direct/Distance 0.18/Damage/Percentage 200% |
| c_yuw_00401_challenge_Boss_Green | Normal | 0 | なし | 59 | Direct/Distance 0.18/Damage/Percentage 100% |
| c_yuw_00401_challenge_Boss_Green | Special | 0 | なし | 134 | Direct/Distance 0.18/Damage/Percentage 300% |
| c_yuw_00401_challenge_Boss_Green | Appearance | 0 | なし | 50 | Direct/Distance 50/None/ForcedKnockBack5 |
| c_yuw_00401_okumuraget_Normal_Blue | Normal | 0 | なし | 59 | Direct/Distance 0.18/Damage/Percentage 100% |
| c_yuw_00401_okumuraget_Normal_Blue | Special | 0 | なし | 134 | Direct/Distance 0.18/Damage/Percentage 300% |
| c_yuw_00401_okumuraget_Boss_Blue | Normal | 0 | なし | 59 | Direct/Distance 0.18/Damage/Percentage 100% |
| c_yuw_00401_okumuraget_Boss_Blue | Special | 0 | なし | 134 | Direct/Distance 0.18/Damage/Percentage 300% |
| c_yuw_00401_okumuraget_Boss_Blue | Appearance | 0 | なし | 50 | Direct/Distance 50/None/ForcedKnockBack5 |
| c_yuw_00401raid_00001_Boss_Red | Normal | 0 | なし | 59 | Direct/Distance 0.18/Damage/Percentage 100% |
| c_yuw_00401raid_00001_Boss_Red | Special | 0 | なし | 134 | Direct/Distance 0.18/Damage/Percentage 300% |
| c_yuw_00401raid_00001_Boss_Red | Appearance | 0 | なし | 50 | Direct/Distance 50/None/ForcedKnockBack5 |

> Normalパラメータ（Normal kind）にはAppearanceアタックがなく、BossパラメータにはAppearanceアタックが設定される。

### インゲーム使用実績サマリー（参考）

| コンテンツ種別 | ステージ数 |
|-------------|----------|
| イベント | 5 |
| 降臨バトル | 1 |
| メインクエスト Normal | 0 |

---

## まとめ

`chara_yuw_00401`（伝えたいウチの想い 喜咲 アリア）は**normalクエストのNormal難易度では未使用**のキャラクターである。VDインゲームマスタデータ生成においてこのキャラを参照・流用する場合は、イベントや降臨バトルでの実績パラメータを参考に新規パラメータを設計する必要がある。
