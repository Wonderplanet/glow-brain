# 黒川あかね（chara_osh_00501）詳細解説

> 作成日: 2026-03-14
> mst_enemy_character_id: chara_osh_00501
> mst_series_id: osh
> 作品名: 【推しの子】

---

## 1. キャラクター基本情報

| カラム | 値 |
|--------|-----|
| id | `chara_osh_00501` |
| mst_series_id | `osh` |
| 作品名 | 【推しの子】 |
| asset_key | `chara_osh_00501` |
| is_phantomized | `1` |

---

## 2. キャラクター特徴まとめ

**本ドキュメントはコンテンツフィルタ「normalクエストのNormal難易度のみ」を適用して生成しています。**

黒川あかねはイベントコンテンツ（`event_`プレフィックス）でのみ使用されており、normalクエスト（メインクエストNormal、`normal_`プレフィックス）では使用実績がありません。そのため、フィルタ適用後の対象ステージは0件となります。

参考として、既存パラメータの概要を示します。2種類のBossパラメータが存在し、どちらもTechnical / Boss / Yellow の組み合わせです。HPの幅は10,000〜100,000と大きく差があり、イベントコンテンツごとに強さが大きく調整されています。

---

## 3. ステージ別使用実態

**フィルタ（normalクエストのNormal難易度のみ）に該当するステージは存在しません。**

黒川あかねが登場するステージは以下のとおりですが、いずれもイベントコンテンツであり、対象フィルタ外です：

| インゲームID | コンテンツ種別 | 使用パラメータID |
|------------|-------------|----------------|
| `event_osh1_charaget01_00003` | イベント | `c_osh_00501_charaget_repeat_Boss_Yellow` |
| `event_osh1_savage_00001` | イベント | `c_osh_00501_osh1savage01_Boss_Yellow` |

---

## 参考: 全パラメータ一覧（フィルタ対象外）

> 以下はフィルタ対象外のパラメータ情報です。VD設計等の参考用として記載しています。

| パラメータID | kind | role | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック数 |
|------------|------|------|-------|-----|--------|---------|---------|--------------|
| `c_osh_00501_charaget_repeat_Boss_Yellow` | Boss | Technical | Yellow | 10,000 | 300 | 30 | 0.26 | 3 |
| `c_osh_00501_osh1savage01_Boss_Yellow` | Boss | Technical | Yellow | 100,000 | 300 | 30 | 0.26 | 1 |

**アビリティ・変身設定**

| パラメータID | アビリティ(mst_unit_ability_id1) | 変身条件(transformationConditionType) | 変身条件値 | 変身後パラメータID |
|------------|--------------------------------|--------------------------------------|----------|----------------|
| `c_osh_00501_charaget_repeat_Boss_Yellow` | なし | None | なし | なし |
| `c_osh_00501_osh1savage01_Boss_Yellow` | なし | None | なし | なし |

**攻撃パターン**

両パラメータ共通の攻撃構成：

| attack_kind | unit_grade | killer_colors | killer_percentage | action_frames |
|------------|-----------|--------------|------------------|--------------|
| Appearance | 0 | なし | なし | 50 |
| Normal | 0 | なし | なし | 53 |
| Special | 0 | なし | なし | 134 |

攻撃エレメント詳細：

| attack_kind | attack_type | range_end_parameter | max_target_count | damage_type | hit_type | power_parameter |
|------------|------------|-------------------|-----------------|------------|---------|----------------|
| Appearance | Direct | 50.0 | 100 | None | ForcedKnockBack5 | 100% |
| Normal | Direct | 0.27 | 1 | Damage | Normal | 100% |
| Special | Direct | 0.37 | 100 | Damage | Stun | 100% |

- **Normal攻撃**: 近接1体ターゲットのダメージ攻撃
- **Special攻撃**: 範囲0.37内の最大100体にスタン付与ダメージ攻撃
- **Appearance**: 登場時に50.0範囲内の敵を強制ノックバック（ForcedKnockBack5）

---

## メモ

- normalクエストのNormal難易度での使用実績なし
- VD設計にこのキャラを新規採用する場合、既存パラメータ（`c_osh_00501_*`）を参考にして新規MstEnemyStageParameterを作成する必要があります
- Special攻撃が広範囲スタンであるため、ボス・強敵枠として設計されています
