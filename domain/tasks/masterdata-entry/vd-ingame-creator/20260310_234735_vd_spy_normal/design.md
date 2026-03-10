# 限界チャレンジ（VD）インゲームマスタデータ設計書

## 基本情報

| 項目 | 値 |
|------|-----|
| 生成日時 | 2026-03-10 23:47:35 |
| 作品ID | spy（SPY×FAMILY） |
| ブロック種別 | normal |
| 参照元（EnemyStageParameter） | ファントムマスター版 MstEnemyStageParameter.csv |

---

## 生成するインゲームID

| テーブル | ID |
|---------|-----|
| MstInGame.id | `vd_spy_normal_00001` |
| MstAutoPlayerSequence.sequence_set_id | `vd_spy_normal_00001` |
| MstPage.id | `vd_spy_normal_00001` |
| MstEnemyOutpost.id | `vd_spy_normal_00001` |

---

## MstEnemyStageParameter 敵パラメータ設計

※ファントムマスター版 MstEnemyStageParameter.csv に既存エントリあり（**新規生成なし・参照のみ**）

| ID | 役割 | mst_enemy_character_id | character_unit_kind | color | HP | 攻撃力 | move_speed | damage_knock_back_count | drop_battle_point |
|----|------|----------------------|--------------------|----|-----|-------|-----------|------------------------|------------------|
| `e_spy_00001_vd_Normal_Blue` | 雑魚A（密輸組織の残党） | enemy_spy_00001 | Normal | Blue | 10,000 | 50 | 34 | 2 | 300 |
| `e_glo_00001_vd_Normal_Colorless` | 雑魚B（ファントム） | enemy_glo_00001 | Normal | Colorless | 5,000 | 100 | 34 | 3 | 150 |

---

## MstAutoPlayerSequence シーケンス設計（normalブロック）

- 合計行数: **6行**（要件: 3〜8行 ✓）
- フェーズ切り替え: **なし**（SwitchSequenceGroup 不使用）
- sequence_group_id: **空**（デフォルトグループのみ）
- aura_type: **すべて Default**
- 合計出現体数: **18体**（要件: 最低15体以上 ✓）

| # | condition_type | condition_value | action_type | action_value（EnemyStageParameter ID） | summon_count | aura_type |
|---|----------------|-----------------|-------------|--------------------------------------|-------------|-----------|
| 1 | ElapsedTime | 250 | SummonEnemy | `e_spy_00001_vd_Normal_Blue` | 3 | Default |
| 2 | ElapsedTime | 1500 | SummonEnemy | `e_glo_00001_vd_Normal_Colorless` | 3 | Default |
| 3 | ElapsedTime | 3000 | SummonEnemy | `e_spy_00001_vd_Normal_Blue` | 3 | Default |
| 4 | ElapsedTime | 5000 | SummonEnemy | `e_glo_00001_vd_Normal_Colorless` | 3 | Default |
| 5 | ElapsedTime | 7000 | SummonEnemy | `e_spy_00001_vd_Normal_Blue` | 3 | Default |
| 6 | ElapsedTime | 9000 | SummonEnemy | `e_glo_00001_vd_Normal_Colorless` | 3 | Default |

**FK確認**: action_value に設定している全IDがファントムマスター版 MstEnemyStageParameter.csv に存在することを確認済み ✓

---

## MstKomaLine 構成

- **フロア数: 3固定**（row=1, 2, 3）
- 各フロアの height: 0.33 / 0.33 / 0.34（合計 1.00）
- 行パターン: **フロアごとに12パターンからランダム独立抽選**
- `koma_effect_type`: `None`（固定）
- `koma1_effect_target_side`: `All`（エフェクトなしでも設定必須）
- ID命名: `{mst_page_id}_{row番号}` 例: `vd_spy_normal_00001_1`

### 各フロアのランダム抽選結果

| フロア | row | height | 抽選パターン | コマ数 | koma1_width | koma2_width | koma3_width | koma4_width | koma_line_layout_asset_key |
|--------|-----|--------|-------------|-------|------------|------------|------------|------------|--------------------------|
| 1 | 1 | 0.33 | パターン3「左ちょい長2コマ」 | 2 | 0.40 | 0.60 | - | - | 3 |
| 2 | 2 | 0.33 | パターン9「中央広い」 | 3 | 0.25 | 0.50 | 0.25 | - | 9 |
| 3 | 3 | 0.34 | パターン12「4等分」 | 4 | 0.25 | 0.25 | 0.25 | 0.25 | 12 |

各フロアのコマ幅合計:
- フロア1: 0.40 + 0.60 = **1.00 ✓**
- フロア2: 0.25 + 0.50 + 0.25 = **1.00 ✓**
- フロア3: 0.25 + 0.25 + 0.25 + 0.25 = **1.00 ✓**

---

## MstEnemyOutpost 設計

| 項目 | 値 |
|------|-----|
| id | `vd_spy_normal_00001` |
| hp | **100**（固定・変更不可） |
| is_damage_invalidation | 空（ダメージ有効） |

---

## MstPage 設計

| 項目 | 値 |
|------|-----|
| id | `vd_spy_normal_00001` |

---

## MstInGame 設計

| 項目 | 値 |
|------|-----|
| id | `vd_spy_normal_00001` |
| content_type | `Dungeon` |
| stage_type | `vd_normal` |
| bgm_asset_key | `SSE_SBG_003_010` |
| boss_mst_enemy_stage_parameter_id | 空（ボスなし） |

---

## 参照した既存データ

- MstEnemyStageParameter（ファントムマスター版）:
  - `e_spy_00001_vd_Normal_Blue`（release_key: 202509010）
  - `e_glo_00001_vd_Normal_Colorless`（release_key: 202509010）
- 既存VDインゲームデータ: 新規コンテンツのため参照なし

---

## 不確定事項・要確認事項

- `koma{N}_asset_key` の具体的な値が未確定（VD向けアセット命名規則の確認が必要）
