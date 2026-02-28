# MstPage / MstKomaLine 照合用正解データ

このファイルは `projects/glow-masterdata/MstPage.csv` / `MstKomaLine.csv` から
照合対象シートのデータを抽出したものです。
XLSXを Google Sheets で開き、EB〜FR 列の表示値と以下を突合してください。

**対象外**: 通常ブロック・ボスブロックはpage_idが未入力のためCSV照合不可。

---

## 【ストーリー】必ず生きて帰る

### 1話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00001`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00001` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00001_1` | `event_jig1_charaget01_00001` | `1` | `0.55` | `6` | `jig_00001` | `0.5` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.5` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_charaget01_00001_2` | `event_jig1_charaget01_00001` | `2` | `0.55` | `1` | `jig_00001` | `1` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 2話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00002`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00002` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00002_1` | `event_jig1_charaget01_00002` | `1` | `0.55` | `5` | `jig_00002` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.75` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_charaget01_00002_2` | `event_jig1_charaget01_00002` | `2` | `0.55` | `4` | `jig_00002` | `0.75` | `0` | `Gust` | `1500` | `0.5` | `Player` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 3話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00003`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00003` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00003_1` | `event_jig1_charaget01_00003` | `1` | `0.55` | `1` | `jig_00002` | `1` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_charaget01_00003_2` | `event_jig1_charaget01_00003` | `2` | `0.55` | `6` | `jig_00002` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 4話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00004`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00004` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00004_1` | `event_jig1_charaget01_00004` | `1` | `0.55` | `1` | `jig_00001` | `1` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_charaget01_00004_2` | `event_jig1_charaget01_00004` | `2` | `0.55` | `6` | `jig_00001` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.5` | `-0.6` | `Gust` | `1000` | `0.2` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_charaget01_00004_3` | `event_jig1_charaget01_00004` | `3` | `0.55` | `8` | `jig_00001` | `0.5` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 5話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00005`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00005` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00005_1` | `event_jig1_charaget01_00005` | `1` | `1` | `1` | `jig_00003` | `1` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 6話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_charaget01_00006`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_charaget01_00006` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_charaget01_00006_1` | `event_jig1_charaget01_00006` | `1` | `0.55` | `4` | `jig_00003` | `0.75` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_charaget01_00006_2` | `event_jig1_charaget01_00006` | `2` | `0.55` | `6` | `jig_00003` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.5` | `-0.6` | `Poison` | `200` | `5` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_charaget01_00006_3` | `event_jig1_charaget01_00006` | `3` | `0.55` | `12` | `jig_00003` | `0.25` | `0.3` | `Gust` | `500` | `0.2` | `Player` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `202601010` |

---

## 【チャレンジ】死罪人と首切り役人設計

### 1話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_challenge01_00001`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_challenge01_00001` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_challenge01_00001_1` | `event_jig1_challenge01_00001` | `1` | `0.55` | `3` | `jig_00003` | `0.4` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.6` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_challenge01_00001_2` | `event_jig1_challenge01_00001` | `2` | `0.55` | `4` | `jig_00003` | `0.75` | `0.2` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 2話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `event_jig1_challenge01_00002`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_challenge01_00002` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_challenge01_00002_1` | `event_jig1_challenge01_00002` | `1` | `0.55` | `8` | `jig_00003` | `0.5` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_challenge01_00002_2` | `event_jig1_challenge01_00002` | `2` | `0.55` | `10` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `Poison` | `200` | `5` | `Player` | `All` | `All` | `jig_00003` | `0.5` | `0.3` | `Poison` | `200` | `8` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 3話 （GROUP_B: er=13, rr=13, kr_start=32）
**page_id**: `event_jig1_challenge01_00003`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_challenge01_00003` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_challenge01_00003_1` | `event_jig1_challenge01_00003` | `1` | `0.55` | `5` | `jig_00003` | `0.25` | `0.7` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.75` | `0.7` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_challenge01_00003_2` | `event_jig1_challenge01_00003` | `2` | `0.55` | `7` | `jig_00003` | `0.33` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.34` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.33` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_challenge01_00003_3` | `event_jig1_challenge01_00003` | `3` | `0.55` | `10` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.5` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 4話 （GROUP_B: er=13, rr=13, kr_start=32）
**page_id**: `event_jig1_challenge01_00004`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_challenge01_00004` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_challenge01_00004_1` | `event_jig1_challenge01_00004` | `1` | `0.55` | `3` | `jig_00003` | `0.4` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.6` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_challenge01_00004_2` | `event_jig1_challenge01_00004` | `2` | `0.55` | `9` | `jig_00003` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.5` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_challenge01_00004_3` | `event_jig1_challenge01_00004` | `3` | `0.55` | `5` | `jig_00003` | `0.25` | `0.7` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00003` | `0.75` | `0.7` | `Burn` | `300` | `1000` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

## 【降臨バトル】まるで悪夢を見ているようだ

### 1話 （GROUP_A: er=13, rr=13, kr_start=31）
**page_id**: `raid_jig1_00001`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `raid_jig1_00001` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `raid_jig1_00001_1` | `raid_jig1_00001` | `1` | `0.55` | `3` | `jig_00001` | `0.4` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.6` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `raid_jig1_00001_2` | `raid_jig1_00001` | `2` | `0.55` | `1` | `jig_00001` | `1` | `-0.4` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `raid_jig1_00001_3` | `raid_jig1_00001` | `3` | `0.55` | `3` | `jig_00001` | `0.4` | `0.7` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00001` | `0.6` | `0.7` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

## 【高難度】手負いの獣は恐ろしいぞ

### 1話 （GROUP_B: er=13, rr=13, kr_start=32）
**page_id**: `event_jig1_savage_00001`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_savage_00001` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_savage_00001_1` | `event_jig1_savage_00001` | `1` | `0.55` | `1` | `jig_00002` | `1` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_savage_00001_2` | `event_jig1_savage_00001` | `2` | `0.55` | `5` | `jig_00002` | `0.25` | `0.7` | `Poison` | `4500` | `3` | `Player` | `All` | `All` | `jig_00002` | `0.75` | `0.7` | `Gust` | `1000` | `0.5` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_savage_00001_3` | `event_jig1_savage_00001` | `3` | `0.55` | `1` | `jig_00002` | `1` | `0.3` | `Gust` | `1000` | `0.5` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行34 | `e` | `event_jig1_savage_00001_4` | `event_jig1_savage_00001` | `4` | `0.55` | `12` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `202601010` |

---

### 2話 （GROUP_B: er=13, rr=13, kr_start=32）
**page_id**: `event_jig1_savage_00002`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_savage_00002` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_savage_00002_1` | `event_jig1_savage_00002` | `1` | `0.55` | `6` | `jig_00002` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_savage_00002_2` | `event_jig1_savage_00002` | `2` | `0.55` | `9` | `jig_00002` | `0.25` | `-1` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.5` | `-1` | `Gust` | `1200` | `0.5` | `Player` | `All` | `All` | `jig_00002` | `0.25` | `-1` | `Gust` | `1500` | `0.3` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |

---

### 3話 （GROUP_B: er=13, rr=13, kr_start=32）
**page_id**: `event_jig1_savage_00003`

#### MstPage（行29: EB〜ED）
| 列 | フィールド | 期待値 |
|-----|-----------|--------|
| EB29 | ENABLE | `e` |
| EC29 | id | `event_jig1_savage_00003` |
| ED29 | release_key | `202601010` |

#### MstKomaLine（行31〜35: EB〜FR）
| row | ENABLE | id | mst_page_id | row | height | koma_line_layout_asset_key | koma1_asset_key | koma1_width | koma1_back_ground_offset | koma1_effect_type | koma1_effect_parameter1 | koma1_effect_parameter2 | koma1_effect_target_side | koma1_effect_target_colors | koma1_effect_target_roles | koma2_asset_key | koma2_width | koma2_back_ground_offset | koma2_effect_type | koma2_effect_parameter1 | koma2_effect_parameter2 | koma2_effect_target_side | koma2_effect_target_colors | koma2_effect_target_roles | koma3_asset_key | koma3_width | koma3_back_ground_offset | koma3_effect_type | koma3_effect_parameter1 | koma3_effect_parameter2 | koma3_effect_target_side | koma3_effect_target_colors | koma3_effect_target_roles | koma4_asset_key | koma4_width | koma4_back_ground_offset | koma4_effect_type | koma4_effect_parameter1 | koma4_effect_parameter2 | koma4_effect_target_side | koma4_effect_target_colors | koma4_effect_target_roles | release_key |
|-----|------|----|-----------|----|------|--------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|---------------|-----------|------------------------|-----------------|-----------------------|-----------------------|------------------------|--------------------------|-------------------------|-----------|
| 行31 | `e` | `event_jig1_savage_00003_1` | `event_jig1_savage_00003` | `1` | `0.55` | `1` | `jig_00002` | `1` | `0.3` | `None` | `0` | `0` | `All` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行32 | `e` | `event_jig1_savage_00003_2` | `event_jig1_savage_00003` | `2` | `0.55` | `6` | `jig_00002` | `0.5` | `-0.6` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.5` | `-0.6` | `Burn` | `600` | `1500` | `Player` | `All` | `All` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `` | `` | `__NULL__` | `None` | `` | `` | `__NULL__` | `` | `` | `202601010` |
| 行33 | `e` | `event_jig1_savage_00003_3` | `event_jig1_savage_00003` | `3` | `0.55` | `12` | `jig_00002` | `0.25` | `0` | `Burn` | `600` | `1500` | `Player` | `All` | `All` | `jig_00002` | `0.25` | `0` | `Gust` | `500` | `0.2` | `Player` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `jig_00002` | `0.25` | `0` | `None` | `0` | `0` | `All` | `All` | `All` | `202601010` |

---
