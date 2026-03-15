# VD共通要件

VD（限界チャレンジ）インゲームブロック全般に適用される共通要件。

---

## 難易度基準

### 目標難易度

**メインクエスト normalクエスト Normal難易度相当**

### 調査方法

`masterdata-ingame-analyzer` スキルを使って対象キャラのメインクエスト実績ステータスを調査する。

```
/masterdata-ingame-analyzer 対象={mst_enemy_character_id} コンテンツ=メインクエスト 難易度=Normal
```

---

## 固定値

| テーブル | カラム | 値 |
|---------|-------|---|
| MstInGame | content_type | `Dungeon` |
| MstInGame | stage_type（normalブロック） | `vd_normal` |
| MstInGame | stage_type（bossブロック） | `vd_boss` |
| MstInGame | mst_defense_target_id | `__NULL__` |
| MstInGame | mst_auto_player_sequence_id | `""`（空文字） |
| MstInGame | boss_bgm_asset_key | `""`（空文字） |
| MstInGame | BGM（normalブロック） | `SSE_SBG_003_010` |
| MstInGame | BGM（bossブロック） | `SSE_SBG_003_004` |
| MstEnemyOutpost | hp | `100` |
| MstEnemyStageParameter | 全coefカラム×6 | `1.0` |
| MstAutoPlayerSequence | summon_animation_type | `None` |

---

## 構造固定値

| 項目 | normalブロック | bossブロック |
|------|--------------|------------|
| MstKomaLine 行数 | **3行固定** | **1行固定** |
| フェーズ切り替え | 禁止 | 禁止 |

---

## 禁止ルール

| 禁止項目 | 理由 |
|---------|------|
| `InitialSummon` の使用 | VDでは使用禁止 |
| `ElapsedTime` の使用 | VDでは使用禁止 |
| `SwitchSequenceGroup` の使用 | フェーズ切り替え禁止 |
| c_キャラの同時2体以上出現 | フィールドに同時に複数体出現不可 |
| c_キャラの `summon_count` ≥ 2 | 1体ずつ召喚必須 |

### c_キャラチェーンルール

c_キャラ（`c_` プレフィックス）が複数体登場する場合:
1. 最初の c_キャラ: 任意の condition_type で召喚
2. 2体目以降: 必ず `FriendUnitDead`（前の c_キャラの `sequence_element_id` を condition_value に指定）でチェーン
3. c_キャラの全エントリは `summon_count = 1`
4. `e_glo_*` はこの制約の対象外

---

## MstEnemyStageParameter

- **全て新規設計**: VDのMstEnemyStageParameterは各ブロックで新規作成。既存VDデータは参照しない
- ID プレフィックス: `e_`（通常敵）または `c_`（フレンドユニット）
