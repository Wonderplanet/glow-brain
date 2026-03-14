# シロガネ（enemy_aya_00001）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: enemy_aya_00001
> mst_series_id: aya
> 作品名: あやかしトライアングル

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `enemy_aya_00001` |
| mst_series_id | `aya` |
| 作品名 | あやかしトライアングル |
| asset_key | `enemy_aya_00001` |
| is_phantomized | `0` |

---

## 2. キャラクター特徴まとめ

シロガネ（enemy_aya_00001）は「あやかしトライアングル」作品のエネミーキャラクターで、**イベントコンテンツ専用**のキャラクターです。

コンテンツフィルタ「normalクエストのNormal難易度のみ」を適用した結果、**該当するステージは存在しません**（メインクエストのNormal難易度では未使用）。全コンテンツではイベント（`event_aya1_*`）の10ステージで使用されています。

ステータス傾向（全バリエーション参考）：
- **役割**: 全バリエーションで `role_type = Attack`（攻撃特化型）
- **unit_kind**: Normal（雑魚敵）と Boss（ボス級）の2種類が存在
- **HP**: Normal種は 1,000、Boss種は 5,000〜10,000 と幅広い
- **攻撃力**: 10〜100 とバリエーションにより差異
- **変身設定**: 全バリエーションで変身なし（`transformationConditionType = None`）
- **アビリティ**: 全バリエーションでアビリティなし

---

## 3. ステージ別使用実態

> **コンテンツフィルタ適用結果**: normalクエストのNormal難易度（`normal_` で始まるステージ）での使用実績は **0件** です。
>
> enemy_aya_00001 は現時点でメインクエストのNormal難易度には登場しておらず、イベントコンテンツ（`event_aya1_*`）でのみ使用されています。

該当なし

---

## 付録: 全パラメータバリエーション一覧（参考）

フィルタ外のデータですが、パラメータ全体の把握のため参考情報として記載します。

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `e_aya_00001_aya1_1d1c_Boss_Colorless` | Boss | Attack | Colorless | 5,000 | 10 | 50 | 0.35 | 3 |
| `e_aya_00001_aya1_challenge01_Boss_Green` | Boss | Attack | Green | 10,000 | 100 | 34 | 0.3 | 2 |
| `e_aya_00001_aya1_charaget01_Boss_Green` | Boss | Attack | Colorless | 10,000 | 100 | 50 | 0.35 | 3 |
| `e_aya_00001_aya1_charaget02_Boss_Green` | Boss | Attack | Green | 10,000 | 100 | 50 | 0.35 | 2 |
| `e_aya_00001_aya1_challenge01_Normal_Green` | Normal | Attack | Green | 1,000 | 100 | 34 | 0.3 | 2 |
| `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | Attack | Colorless | 1,000 | 100 | 50 | 0.35 | 3 |

**アビリティ・変身設定（参考）**

| パラメータID | アビリティ | 変身条件 | 変身条件値 | 変身後パラメータID |
|------------|-----------|---------|----------|----------------|
| `e_aya_00001_aya1_1d1c_Boss_Colorless` | なし | None | なし | なし |
| `e_aya_00001_aya1_challenge01_Boss_Green` | なし | None | なし | なし |
| `e_aya_00001_aya1_charaget01_Boss_Green` | なし | None | なし | なし |
| `e_aya_00001_aya1_charaget02_Boss_Green` | なし | None | なし | なし |
| `e_aya_00001_aya1_challenge01_Normal_Green` | なし | None | なし | なし |
| `e_aya_00001_aya1_charaget01_Normal_Green` | なし | None | なし | なし |

**攻撃パターン（参考）**

| パラメータID | attack_kind | unit_grade | killer_colors | action_frames | 攻撃射程(range_end) | max_target_count |
|------------|-----------|-----------|--------------|--------------|-------------------|----------------|
| `e_aya_00001_aya1_1d1c_Boss_Colorless` | Appearance | 0 | なし | 50 | 50.0 | 100 |
| `e_aya_00001_aya1_1d1c_Boss_Colorless` | Normal | 0 | なし | 80 | 0.37 | 100 |
| `e_aya_00001_aya1_challenge01_Boss_Green` | Appearance | 0 | なし | 50 | 50.0 | 100 |
| `e_aya_00001_aya1_challenge01_Boss_Green` | Normal | 0 | なし | 80 | 0.37 | 1 |
| `e_aya_00001_aya1_challenge01_Normal_Green` | Normal | 0 | なし | 80 | 0.37 | 1 |
| `e_aya_00001_aya1_charaget01_Boss_Green` | Appearance | 0 | なし | 50 | 50.0 | 100 |
| `e_aya_00001_aya1_charaget01_Boss_Green` | Normal | 0 | なし | 80 | 0.37 | 1 |
| `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | 0 | なし | 80 | 0.37 | 1 |
| `e_aya_00001_aya1_charaget02_Boss_Green` | Appearance | 0 | なし | 50 | 50.0 | 100 |
| `e_aya_00001_aya1_charaget02_Boss_Green` | Normal | 0 | なし | 80 | 0.37 | 1 |

> Killer属性・特効: 全パラメータでなし（空文字）。攻撃タイプはすべて Direct（近接）。ダメージタイプは Normal攻撃が Damage、Appearance攻撃が None（ForcedKnockBack5）。

**全コンテンツ使用実績（参考）**

| コンテンツ種別 | ステージ数 |
|-------------|----------|
| イベント | 10 |
| メインクエスト Normal | 0 |

使用ステージ一覧（イベント、参考）：

| ステージID | 使用パラメータID | kind | color |
|-----------|---------------|------|-------|
| `event_aya1_1day_00001` | `e_aya_00001_aya1_1d1c_Boss_Colorless` | Boss | Colorless |
| `event_aya1_challenge_00001` | `e_aya_00001_aya1_challenge01_Normal_Green` | Normal | Green |
| `event_aya1_challenge_00001` | `e_aya_00001_aya1_challenge01_Boss_Green` | Boss | Green |
| `event_aya1_challenge_00003` | `e_aya_00001_aya1_challenge01_Normal_Green` | Normal | Green |
| `event_aya1_charaget01_00001` | `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | Colorless |
| `event_aya1_charaget01_00002` | `e_aya_00001_aya1_charaget01_Boss_Green` | Boss | Colorless |
| `event_aya1_charaget01_00002` | `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | Colorless |
| `event_aya1_charaget01_00003` | `e_aya_00001_aya1_charaget01_Boss_Green` | Boss | Colorless |
| `event_aya1_charaget01_00003` | `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | Colorless |
| `event_aya1_charaget01_00004` | `e_aya_00001_aya1_charaget01_Boss_Green` | Boss | Colorless |
| `event_aya1_charaget01_00004` | `e_aya_00001_aya1_charaget01_Normal_Green` | Normal | Colorless |
| `event_aya1_charaget02_00001` | `e_aya_00001_aya1_charaget02_Boss_Green` | Boss | Green |
| `event_aya1_charaget02_00003` | `e_aya_00001_aya1_charaget02_Boss_Green` | Boss | Green |
| `event_aya1_charaget02_00006` | `e_aya_00001_aya1_charaget02_Boss_Green` | Boss | Green |
