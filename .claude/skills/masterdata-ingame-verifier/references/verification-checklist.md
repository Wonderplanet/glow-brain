# 検証チェックリスト（フェーズ別全項目）

インゲームマスタデータCSVの検証時に確認する全チェック項目。
各フェーズをすべてクリアすると「確実に実機プレイで問題ありません」と判定できる。

---

## Phase A: フォーマット（masterdata-csv-validator 委譲）

`validate_all.py` を各CSVに対して実行し、全件 `valid: true` を確認する。

| # | チェック項目 | 判定基準 | 違反時 |
|---|------------|---------|-------|
| A-1 | 列名・列順がテンプレートCSVと一致するか | テンプレート完全一致 | CRITICAL |
| A-2 | CSV形式が正しいか（改行・ダブルクォート） | 形式エラーなし | CRITICAL |
| A-3 | NULL不可カラムに値が設定されているか | 全必須カラムに値あり | CRITICAL |
| A-4 | enum値がスキーマ定義と一致するか | 定義済みenum値のみ | CRITICAL |
| A-5 | 型がスキーマ定義と一致するか（int/varchar等） | 型一致 | CRITICAL |

---

## Phase B: ID整合性

`verify_id_integrity.py` を実行して全FK参照を確認する。

| # | チェック項目 | FK方向 | 違反時 |
|---|------------|-------|-------|
| B-1 | MstInGame.mst_auto_player_sequence_set_id → MstAutoPlayerSequence.sequence_set_id（全行） | ingame → sequence | CRITICAL |
| B-2 | MstInGame.mst_page_id → MstPage.id | ingame → page | CRITICAL |
| B-3 | MstInGame.mst_enemy_outpost_id → MstEnemyOutpost.id | ingame → outpost | CRITICAL |
| B-4 | MstInGame.boss_mst_enemy_stage_parameter_id → MstEnemyStageParameter.id（空欄は許可） | ingame → parameter | CRITICAL |
| B-5 | 全MstAutoPlayerSequenceのsequence_set_idが同一値か | sequence内部整合性 | WARNING |
| B-6 | action_type=SummonEnemy の action_value → MstEnemyStageParameter.id（全行） | sequence → parameter | CRITICAL |
| B-7 | MstEnemyStageParameter.mst_enemy_character_id → projects/glow-masterdata/MstEnemyCharacter.csv（全行） | parameter → character | WARNING（新キャラの場合あり） |

---

## Phase C: ゲームプレイ品質

### C-1: 敵パラメータの妥当性

| # | チェック項目 | 基準 | 違反時 |
|---|------------|------|-------|
| C-1-1 | hp（Normal）が通常範囲内か | 100 〜 500,000 | WARNING |
| C-1-2 | hp（Boss）が通常範囲内か | 50,000 〜 3,000,000 | WARNING |
| C-1-3 | attack_powerが通常範囲内か | 21 〜 3,800 | WARNING（>3,800）/ CRITICAL（>10,000） |
| C-1-4 | move_speedが通常範囲内か | 5 〜 100 | WARNING |
| C-1-5 | well_distanceが通常範囲内か | 0.05 〜 2.0 | WARNING |

### C-2: コマ配置の整合性

| # | チェック項目 | 基準 | 違反時 |
|---|------------|------|-------|
| C-2-1 | 各行のコマ幅合計が1.0か | ROUND(SUM(komaX_width),3) = 1.0 | CRITICAL |
| C-2-2 | ~~height合計が1.0か~~ | ~~削除~~ | ~~削除~~ |
| C-2-3 | dungeon_boss のコマ行数が1行か（設計仕様） | COUNT(DISTINCT row) = 1 | WARNING |
| C-2-4 | dungeon_normal のコマ行数が3行か（設計仕様） | COUNT(DISTINCT row) = 3 | WARNING |

> **備考**: height合計に固定制限はなし。既存データ分析で 1.1（2行コマ最多）、1.65（3行コマ最多）など多様な値が確認された。dungeon固有ルール（C-2-3/C-2-4）は既存データにdungeon系が存在しないため設計仕様として扱う。

### C-3: シーケンスの合理性

| # | チェック項目 | 基準 | 違反時 |
|---|------------|------|-------|
| C-3-1 | ~~召喚数が合理的か~~ | ~~削除~~ | ~~削除~~ |
| C-3-2 | ElapsedTime条件が時系列で単調増加か | 逆転がない | WARNING |

> **備考**: 召喚数に固定の制限はなし。既存データ分析でSummonEnemy行数が1〜53（summon_count列を考慮した実召喚数は1〜1895）と確認された。

### C-4: ステージ種別固有ルール

| # | チェック項目 | 種別 | 基準 | 違反時 |
|---|------------|------|------|-------|
| C-4-1 | MstEnemyOutpost.hp が固定値か | dungeon_boss | hp = 1,000 | CRITICAL |
| C-4-2 | MstEnemyOutpost.hp が固定値か | dungeon_normal | hp = 100 | CRITICAL |
| C-4-3 | SpeedAttackルールが存在するか | event_challenge | MstInGameSpecialRule に SpeedAttack | WARNING |
| C-4-4 | is_damage_invalidation が1か | raid | is_damage_invalidation = 1 | WARNING |

### C-5: ボス設定の二重チェック

| # | チェック項目 | 基準 | 違反時 |
|---|------------|------|-------|
| C-5-1 | ボスIDが設定されている場合、InitialSummonで召喚されているか | ボスID ≠ 空 → InitialSummon行にaction_value=ボスID | WARNING |
| C-5-2 | boss_count が設定されている場合、召喚数と整合するか | boss_count ≒ SummonEnemy行でボスIDが登場する回数 | WARNING |

---

## Phase D: バランス比較

| # | チェック項目 | 基準 | 違反時 |
|---|------------|------|-------|
| D-1 | hp が既存データの ±5倍範囲内か | 0.2倍 〜 5.0倍 | WARNING |
| D-2 | attack_power が既存データの ±5倍範囲内か | 0.2倍 〜 5.0倍 | WARNING |
| D-3 | 同種ステージが存在しない場合、近似種別と比較したか | NOTE記録 | NOTE |

---

## Phase E: アセットキー形式

| # | チェック項目 | 対象テーブル.カラム | 違反時 |
|---|------------|-----------------|-------|
| E-1 | bgm_asset_key が空欄でないか | MstInGame.bgm_asset_key | WARNING |
| E-2 | boss_bgm_asset_key が空欄でないか（ボスあり時） | MstInGame.boss_bgm_asset_key | WARNING |
| E-3 | artwork_asset_key が空欄でないか | MstEnemyOutpost.artwork_asset_key | WARNING |
| E-4 | koma_asset_key が空欄でないか | MstKomaLine.koma_asset_key（全行） | WARNING |
| E-5 | loop_background_asset_key が空欄でないか | MstPage.loop_background_asset_key | WARNING |

---

## 判定サマリー

| 違反種別 | 意味 | 対応 |
|---------|------|------|
| **CRITICAL** | 実機でクラッシュ・データ破損・ゲームプレイ不能 | 即修正が必要 |
| **WARNING** | ゲームプレイに影響しうる可能性があるが意図的かもしれない | 確認・検討を要請 |
| **NOTE** | 情報として記録するが判定には影響しない | 参考情報として提示 |

すべてのCRITICALが解消され、WARNINGが意図的であると確認されたら **合格** とする。
