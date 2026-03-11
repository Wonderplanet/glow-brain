# VDインゲームCSV 空欄カラム修正

対象ブロック: `{ブロックID}`（例: `vd_gom_normal_00001`）
対象ファイル: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/{ブロックID}/generated/`

下記の調査結果に従い、生成CSVの空欄カラムを修正してください。

参照ファイル：
- `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/knowledge/vd_ingame_csv_空欄カラム調査結果.md`
- `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/knowledge/MstKomaLine_series_top_koma.csv`

---

## 修正内容

### MstAutoPlayerSequence.csv

| カラム | 設定値 |
|--------|--------|
| `defeated_score` | `0`（全行） |

※その他の必須カラム（`summon_animation_type`, `move_start/stop/restart_condition_type`, `death_type`, `deactivation_condition_type`）はすでに設定済みのはずだが、空欄なら調査結果の値を設定すること。

### MstKomaLine.csv

1. `MstKomaLine_series_top_koma.csv` からブロックのシリーズID（例: `aya`, `gom`）に対応する行を取得
2. 以下のカラムを全行に設定する

| カラム | 取得元 |
|--------|--------|
| `koma1_asset_key` | `koma_asset_key` 列の値 |
| `koma1_back_ground_offset` | `back_ground_offset` 列の値 |
