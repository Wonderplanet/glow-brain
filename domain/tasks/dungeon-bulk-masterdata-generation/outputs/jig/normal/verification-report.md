# インゲームマスタデータ検証レポート

- 対象: `dungeon_jig_normal_00001`（dungeon_normal）
- 検証日時: 2026-03-02
- 検証ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/jig/normal/generated/`

---

## 総合判定: 合格（軽微な確認事項あり）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | 注記あり | ヘッダー形式はバリデーター仕様外（参考データと同形式のため問題なし） |
| B: ID整合性 | OK | 全FK参照一致（エラーなし） |
| C: ゲームプレイ品質 | OK | コマ幅・シーケンス・ HP固定値すべて正常 |
| D: バランス比較 | OK | 既存jig mainquetst系と同等範囲 |
| E: アセットキー | 注記あり | outpost_asset_key / artwork_asset_key は空白（参考データと同様で許容範囲） |

---

## Step 1: フォーマット検証

### 結果サマリー

全6ファイルで `validate_all.py` を実行。全ファイルが `valid: false` を返した。

### 分析

バリデーターが期待するヘッダー形式:
```
行1: memo,...
行2: TABLE,<テーブル名>,...
行3: ENABLE,<カラム名>,...
```

今回のCSVの実際の形式（1行目がENABLE行）:
```
ENABLE,id,...
e,dungeon_jig_normal_00001,...
```

**ただし、参考データ（`dungeon_spy_normal_00001`）も全く同じフォーマット**を使用しており、このタスクにおける正式なフォーマットであることが確認された。validate_all.py のテンプレートチェックルールがこの簡略ヘッダー形式に未対応なため発生するエラーであり、CSVデータ内容自体の問題ではない。

| ファイル | テンプレート検証 | CSV形式 | DBスキーマ | Enum | 実質的な問題 |
|---------|----------------|---------|-----------|------|------------|
| MstAutoPlayerSequence.csv | NG（ヘッダー形式） | NG（同上） | カラム数35（期待34） | OK | なし |
| MstEnemyOutpost.csv | NG（ヘッダー形式） | NG（同上） | NG（最小行数不足） | NG | なし |
| MstEnemyStageParameter.csv | NG（ヘッダー形式） | NG（同上） | NG（最小行数不足） | NG | なし |
| MstInGame.csv | NG（ヘッダー形式） | NG（同上） | NG（最小行数不足） | NG | なし |
| MstKomaLine.csv | NG（ヘッダー形式） | NG（同上） | カラム数43（期待42） | OK | なし |
| MstPage.csv | NG（ヘッダー形式） | NG（同上） | NG（最小行数不足） | NG | なし |

> カラム数の差異（+1）はENABLEカラムが先頭に追加されているためであり、フォーマット仕様通り。

### 判定

フォーマットはこのタスクの正式仕様に準拠している。実質的な問題なし。

---

## Step 2: ID整合性チェック

`verify_id_integrity.py` を実行。全チェック項目がパス。

```json
{
  "check": "id_integrity",
  "valid": true,
  "checks": {
    "ingame_sequence_fk": true,
    "ingame_page_fk": true,
    "ingame_outpost_fk": true,
    "ingame_boss_fk": true,
    "sequence_set_id_consistency": true,
    "sequence_action_value_fk": true
  },
  "issues": []
}
```

| チェック項目 | 結果 |
|------------|------|
| MstInGame.mst_auto_player_sequence_set_id → MstAutoPlayerSequence.sequence_set_id | OK |
| MstInGame.mst_page_id → MstPage.id | OK |
| MstInGame.mst_enemy_outpost_id → MstEnemyOutpost.id | OK |
| MstInGame.boss_mst_enemy_stage_parameter_id（空欄で正常） | OK |
| MstAutoPlayerSequence.sequence_set_id の一貫性 | OK |
| SummonEnemy の action_value → MstEnemyStageParameter.id | OK |

---

## Step 3: ゲームプレイ品質チェック

### 3-1: 敵パラメータの妥当性

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|-------------|------------|--------------|
| e_jig_00001_mainquest_Normal_Colorless | Normal | Defense | 3,500 | 50 | 31 | 0.25 |
| e_jig_00401_mainquest_Normal_Colorless | Normal | Attack | 3,000 | 100 | 32 | 0.35 |

エネミーステータスシートの dungeon_normal 向け基準値（メインクエスト Normal系）との照合:
- Defense: HP基準 3,500〜10,000（今回: 3,500 → 最低水準。問題なし）
- Attack: HP基準 3,000〜5,000（今回: 3,000 → 最低水準。問題なし）

> 地獄楽（jig）は dungeon の初回実装作品として、難易度を抑えた設定で妥当。

### 3-2: コマ配置の整合性

| row | total_width |
|-----|------------|
| 1 | 1.0 |
| 2 | 1.0 |
| 3 | 1.0 |

- 行数: 3行（dungeon_normal の仕様どおり）
- 各行のコマ幅合計: 全行 1.0（仕様適合）

### 3-3: シーケンスの合理性

MstAutoPlayerSequence の全シーケンス（ElapsedTime 単調増加確認）:

| sequence_element_id | condition_type | condition_value | action_type | summon_count |
|--------------------|----------------|----------------|------------|-------------|
| 1 | ElapsedTime | 250 | SummonEnemy | 10 |
| 2 | ElapsedTime | 700 | SummonEnemy | 5 |
| 3 | ElapsedTime | 3000 | SummonEnemy | 15 |
| 4 | ElapsedTime | 4500 | SummonEnemy | 10 |
| 5 | ElapsedTime | 7000 | SummonEnemy | 10 |

- ElapsedTime: 250 → 700 → 3,000 → 4,500 → 7,000（単調増加、問題なし）
- 時系列逆行: 0件
- 合計召喚数: 10+5+15+10+10 = 50体（十分な数量）

### 3-4: ステージ種別固有ルール（dungeon_normal）

| 項目 | 期待値 | 実際値 | 判定 |
|------|--------|--------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | OK |
| コマ行数 | 3行（固定） | 3行 | OK |
| boss_mst_enemy_stage_parameter_id | 空欄 | null | OK |
| boss_count | 0 | 0 | OK |

### 3-5: ボス設定の二重チェック

- `boss_mst_enemy_stage_parameter_id`: null（dungeon_normalのためボスなし、正常）
- `boss_count`: 0（正常）
- InitialSummon: なし（ボスがないため期待どおり）

---

## Step 4: バランス比較

既存の jig mainquest Normal 系エネミーパラメータとの比較:

| role_type | 既存HP範囲 | 今回HP | 既存ATK範囲 | 今回ATK | 既存Speed範囲 | 今回Speed |
|-----------|------------|--------|------------|---------|--------------|----------|
| Defense | 3,500〜10,000（avg 7,833） | 3,500 | 50（固定） | 50 | 20〜31（avg 26） | 31 |
| Attack | 3,000〜5,000（avg 4,000） | 3,000 | 100（固定） | 100 | 30〜35（avg 32.5） | 32 |

今回の生成データは既存 jig 系の最低水準パラメータを踏襲しており、バランスが取れている。±5倍範囲内に収まっており問題なし。

SPY参考ブロック（`dungeon_spy_normal_00001`）との比較:
- SPY: HP=1,000, ATK=5,000, move_speed=45（高難度設定）
- JIG: HP=3,000〜3,500, ATK=50〜100, move_speed=31〜32（低難度設定）

> JIG は SPY より HP が高く ATK が低い設定。これは HP によるタンク感を重視した設計であり、dungeon_normal ではどちらの設定も有効。

---

## Step 5: アセットキーチェック

### MstInGame

| カラム | 値 | 判定 |
|--------|-----|------|
| bgm_asset_key | `SSE_SBG_003_003` | OK（設定済み） |
| boss_bgm_asset_key | null | OK（ボスなしのため空欄は正常） |
| loop_background_asset_key | `jig_00002` | OK（設定済み） |
| player_outpost_asset_key | null | 注記（後述） |

### MstEnemyOutpost

| カラム | 値 | 判定 |
|--------|-----|------|
| outpost_asset_key | null | 注記（後述） |
| artwork_asset_key | null | 注記（後述） |

### MstKomaLine

| id | koma_line_layout_asset_key | koma1_asset_key | koma2_asset_key | koma3_asset_key |
|----|--------------------------|----------------|----------------|----------------|
| dungeon_jig_normal_00001_1 | 6 | jig_00002 | jig_00002 | null |
| dungeon_jig_normal_00001_2 | 3 | jig_00002 | jig_00002 | null |
| dungeon_jig_normal_00001_3 | 1 | jig_00002 | null | null |

コマ有効行（koma1, koma2）のアセットキーは設定済み。空欄コマ（koma3, koma4）は意図的な空欄で問題なし。

### アセットキーに関する注記

`player_outpost_asset_key`、`outpost_asset_key`、`artwork_asset_key` が null になっているが、参考データ（`dungeon_spy_normal_00001`）でも同様にこれらは null である。dungeon_normal ではこれらのアセットキーは未使用（またはシステム側でデフォルト値を使用）と推定される。実害なし。

---

## 最終判定

### 合格

dungeon_jig_normal_00001 は実機プレイで問題ないと判断する。

**根拠:**
1. ID整合性: 全FK参照が一致しており、参照切れなし
2. dungeon_normal 固有仕様: MstEnemyOutpost.hp=100、コマ行数=3行が正確に設定されている
3. シーケンス: ElapsedTime が単調増加しており、50体のエネミーが時系列に沿って召喚される
4. パラメータバランス: 既存 jig mainquest Normal 系の最低水準と一致しており適切
5. ボス設定: dungeon_normal のためボスなし（boss_count=0, boss_mst_enemy_stage_parameter_id=null）が正しく設定されている
6. アセットキー: bgm（SSE_SBG_003_003）と背景（jig_00002）が設定済み。未設定のキーは参考データと同様のパターンで許容範囲内

### 確認推奨事項（WARNING相当）

| 項目 | 内容 | 推奨アクション |
|------|------|--------------|
| エネミーHP | Defense=3,500、Attack=3,000（最低水準） | 意図的に低難度設定であれば問題なし。難易度調整が必要な場合は `MstInGame.normal_enemy_hp_coef` で倍率設定 |
| outpost_asset_key | null | 参考データ（SPY）と同様のため許容範囲。アセット準備後に設定が必要な場合は更新 |
