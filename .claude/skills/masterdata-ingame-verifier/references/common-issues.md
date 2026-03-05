# よくある問題と修正提案

インゲームマスタデータCSV検証で頻出する問題のパターンと修正方法。

---

## CRITICAL 問題

### [ID整合性] FK参照切れ

**症状**:
```json
{
  "check": "sequence_action_value_fk",
  "status": "FAIL",
  "details": [
    {"row": 5, "action_value": "enemy_param_00999", "found": false}
  ]
}
```

**原因**:
- MstAutoPlayerSequence の action_value に存在しないMstEnemyStageParameter.idを設定した
- IDのタイポ・コピー時の誤り
- MstEnemyStageParameterに当該レコードを作成し忘れた

**修正方法**:
1. `duckdb -c "SELECT id FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)"` で実在するIDを確認
2. MstAutoPlayerSequence の該当行の action_value を正しいIDに修正
   または
3. MstEnemyStageParameter に不足しているレコードを追加

---

### [コマ配置] 幅合計が1.0でない

**症状**: line_number=2 の total_width = 0.85

**原因**:
- コマのwidthの合計計算ミス
- コマ1つ分の width 設定漏れ
- 小数点の精度問題（0.333 × 3 = 0.999 など）

**修正方法**:
```
dungeon_normal（3行）の標準的なコマ幅パターン例:
  - コマ1列の場合: width = 1.0
  - コマ2列（均等）: width = 0.5, 0.5
  - コマ3列（均等）: width = 0.334, 0.333, 0.333（合計1.000）
```

最後のコマのwidthを調整して合計が1.000になるように修正:
```
例: 0.4, 0.4 → 合計0.8 → 最後のコマを 0.2 に変更
```

---

### [ステージ種別] dungeon_boss の MstEnemyOutpost.hp ≠ 1,000

**症状**: MstEnemyOutpost.hp = 50,000（dungeon_bossなのに固定値でない）

**原因**: ステージ種別の仕様を誤解して一般的なhpを設定した

**修正方法**:
MstEnemyOutpost.csv の `hp` カラムを `1000` に変更する

| ステージ種別 | 正しいhp |
|-----------|---------|
| dungeon_boss | **1000** |
| dungeon_normal | **100** |

---

### [ステージ種別] dungeon_normal のコマ行数が3行でない

**症状**: COUNT(DISTINCT line_number) = 1（dungeon_normalなのに1行しかない）

**原因**: dungeon_bossのデータをテンプレートとしてコピーして、行数を修正し忘れた

**修正方法**:
MstKomaLine.csv に行数が1行のみなら、不足している2行分を追加する。
各行の `line_number`（1, 2, 3）と対応するコマを設定し、
各行のwidthの合計が1.0になるよう調整する。

---

## WARNING 問題

### [パラメータ] attack_power の異常値（5,000 vs 基準値 50〜2,380）

**症状**:
```
id: enemy_param_001, attack_power: 5000
基準値（Atk/Tech, Normal）: AVG ≒ 300
乖離率: 約16.7倍
```

**確認事項**:
- 意図的な強敵設定（ボスの取り巻き、特殊役割）か？
- MstInGame.normal_enemy_attack_coef などの倍率係数との組み合わせで調整しているか？
- エネミーステータスシートのどの行を参考にしたか？

**もし意図的でない場合の修正提案**:
```
同種（dungeon_normal, Atk/Tech, Normal）の基準値（例: 200〜500）に変更
または
attack_powerを下げて、MstAutoPlayerSequence の enemy_attack_coef で倍率調整
```

**注**: 5,000のような極端な値が実際に発生した事例があるため、CRITICAL扱いではなくWARNINGとして必ず確認を求める。

---

### [ボス設定] ボスがInitialSummonで召喚されていない

**症状**:
```
MstInGame.boss_mst_enemy_stage_parameter_id = "enemy_param_boss_001"
MstAutoPlayerSequence に InitialSummon + action_value="enemy_param_boss_001" が存在しない
```

**確認事項**:
- ボスを後から登場させる設計（ElapsedTime 条件）が意図的か？
- 初期配置が不要なステージ種別か？

**修正提案**（初期配置が必要な場合）:
MstAutoPlayerSequenceに以下の行を追加:
```
sort_order: 1
action_type: SummonEnemy
action_value: {boss_mst_enemy_stage_parameter_id}
condition_type: InitialSummon
condition_value: （空欄）
enemy_hp_coef: 1
enemy_attack_coef: 1
enemy_speed_coef: 1
aura_type: Boss
```

---

### [シーケンス] ElapsedTimeが時系列で逆転している

**症状**:
```
sort_order=5, condition_value=10000 (100秒)
sort_order=6, condition_value=8000  (80秒) ← 逆転
```

**原因**: 行の並び順を修正したが、condition_valueを更新し忘れた

**修正方法**: condition_valueを sort_order に対して単調増加するよう並べ直す

---

### [アセットキー] bgm_asset_key が空欄

**症状**: MstInGame.bgm_asset_key = ""

**確認事項**: BGMをなしで実装する設計か、設定忘れか？

**修正提案**: 既存データから同種ステージのBGMキーを参照して設定
```sql
SELECT DISTINCT bgm_asset_key, COUNT(*) AS cnt
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE)
WHERE bgm_asset_key != ''
GROUP BY bgm_asset_key
ORDER BY cnt DESC
LIMIT 10;
```

---

### [バランス] 既存データと大きく乖離（±5倍超）

**症状**:
```
gen_hp: 150,000（生成値）
existing_hp_avg: 8,000（既存平均）
hp_ratio: 18.75（18倍以上）
```

**確認事項**:
- このキャラはボス（character_unit_kind=Boss）相当か？
- 特別なコンテンツ（高難度、レイド等）向けの設計か？
- ステージレベル（recommended_level）が一般的な水準と異なるか？

**意図的でない場合の修正提案**:
エネミーステータスシートの推奨PTLvに対応する基準値を参照して設定する

---

## フォーマット問題（masterdata-csv-validatorより）

### [列順不一致] カラムの並び順がテンプレートと異なる

**修正方法**: テンプレートCSV（`projects/glow-masterdata/sheet_schema/{テーブル名}.csv`）の列順に合わせてCSVを再作成

### [enum違反] 定義外のenum値

**修正方法**: DBスキーマの許可されたenum値リストを確認して修正
```bash
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json | \
  jq '.mst_enemy_stage_parameters[] | select(.name == "role_type")'
```
