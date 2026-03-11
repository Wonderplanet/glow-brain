---
name: vd-masterdata-ingame-design-creator
description: VDインゲーム設計書（design.md）の作成と調整に特化したスキル。作品ID・ブロック種別・敵構成をヒアリングし、DuckDBで既存データを参照して設計書MDを生成、ユーザーが承認するまで修正ループを繰り返します。「VD設計書作成」「VDインゲーム設計」「VD設計調整」「design.md作成」「VDブロック設計」などのキーワードで使用します。
---

# VDインゲーム設計書作成スキル（design.md 専用）

## 概要

限界チャレンジ（VD）の**インゲーム設計書（design.md）の作成と調整**に特化したスキル。

- **このスキルが行うこと**: design.md の生成・修正ループ
- **このスキルが行わないこと**: CSV生成（CSV生成は `vd-ingame-creator` スキルを使用）

---

## 出力先

```
domain/tasks/masterdata-entry/vd-ingame-design-creator/{ブロックID}/design.md
```

**例:**
```
domain/tasks/masterdata-entry/vd-ingame-design-creator/vd_kai_boss_00001/design.md
domain/tasks/masterdata-entry/vd-ingame-design-creator/vd_kai_normal_00001/design.md
```

---

## VD固有の固定値（変更不可）

| 項目 | bossブロック | normalブロック |
|------|------------|--------------|
| `MstInGame.content_type` | `Dungeon` | `Dungeon` |
| `MstInGame.stage_type` | `vd_boss` | `vd_normal` |
| `MstEnemyOutpost.hp` | **1,000**（固定） | **100**（固定） |
| `MstKomaLine` 行数 | **1行**（固定） | **3行固定**（各行ごとにコマ数1〜4でランダム独立抽選） |
| フェーズ切り替え | **禁止**（SwitchSequenceGroup使用不可） | **禁止** |
| BGM | `SSE_SBG_003_004` | `SSE_SBG_003_010` |

### IDプレフィックス

| ブロック種別 | MstInGame.id パターン | 例 |
|------------|---------------------|-----|
| boss | `vd_{作品ID}_boss_{連番5桁}` | `vd_kai_boss_00001` |
| normal | `vd_{作品ID}_normal_{連番5桁}` | `vd_kai_normal_00001` |

> **注**: 限界チャレンジIDプレフィックスは `vd_` を使用する（既存の `dungeon_` ではない）

---

## 4ステップワークフロー

### Step 0: 情報確認（1回のみ）

以下が揃っているか確認し、不足は**まとめて1回だけ質問する**。

| 確認項目 | 内容 |
|---------|------|
| 作品ID | シリーズ略称（`kai` / `dan` / `spy` 等）。未指定なら確認する |
| ブロック種別 | `boss` または `normal` のどちらか |
| ボスキャラ | bossブロックのみ：ボスキャラID・色属性 |
| 雑魚キャラ | 雑魚キャラID・色属性・体数（ElapsedTime区切りごと） |
| 連番 | 開始番号（通常は `00001`） |

作品別の登場キャラは [vd-character-list.md](references/vd-character-list.md) を参照。

---

### Step 1: 既存VDデータの参照（DuckDB）

```bash
# 既存VDデータの参照
duckdb -c "SELECT id, bgm_asset_key, boss_bgm_asset_key, content_type, stage_type, boss_mst_enemy_stage_parameter_id FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE id LIKE 'vd_%' LIMIT 10;"

# 既存VDシーケンス確認
duckdb -c "SELECT sequence_set_id, condition_type, condition_value, action_type, action_value, summon_count, aura_type, enemy_hp_coef, enemy_attack_coef FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE sequence_set_id LIKE 'vd_%' LIMIT 20;"
```

詳細クエリは [duckdb-vd-queries.md](references/duckdb-vd-queries.md) を参照。

---

### Step 2: 設計書MD生成

DuckDB参照結果とヒアリング内容を基に `design.md` を生成して出力先ディレクトリに保存する。

**設計書フォーマット**: [design-format.md](references/design-format.md) を参照。

**コマ設計時の参照先**:
- `koma1_asset_key` の決定: [series-koma-assets.csv](references/series-koma-assets.csv) を参照して作品IDに合った値を設定する
- `koma1_back_ground_offset` の決定: [koma-background-offset.md](references/koma-background-offset.md) を参照して推奨値を設定する

**空欄になりがちなカラムのデフォルト値**: [vd-column-defaults.md](references/vd-column-defaults.md) を参照する。

---

### Step 3: ユーザー確認・修正ループ

```
設計書を生成しました（design.md）。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

**ユーザーが「OK」または「承認」と言うまで修正ループを繰り返す。**

**Step 3 完了時**: design.md のパスを案内して終了する。CSV生成は行わない。

---

## ガードレール（必ず守ること）

1. **IDプレフィックスは `vd_`**: 既存の `dungeon_` は使用しない
2. **アウトポストHP固定**: boss=1,000固定、normal=100固定（変更不可）
3. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない
4. **ユーザー質問は1回**: 不足情報はまとめて1度の質問で確認する
5. **承認前に完了しない**: ユーザーが「OK」と言うまで修正ループを続ける
6. **コマアセットキーは series-koma-assets.csv を参照**: 作品IDに合った `koma1_asset_key` を設定する
7. **koma1_back_ground_offset は koma-background-offset.md を参照**: 推奨仮値を設定する
8. **空欄カラムのデフォルト値は vd-column-defaults.md を参照**: 設定漏れを防ぐ
9. **CSV生成は行わない**: このスキルは design.md の作成・修正専用
10. **ボスの二重設定**: `MstInGame.boss_mst_enemy_stage_parameter_id` + `MstAutoPlayerSequence`のInitialSummonで設定することを設計書に明記する
11. **normalブロックのMstKomaLineは3行固定**: row=1〜3 の3エントリを設計する

---

## リファレンス一覧

- [design-format.md](references/design-format.md) — design.md フォーマットテンプレート定義
- [vd-character-list.md](references/vd-character-list.md) — 作品別登場キャラ一覧
- [duckdb-vd-queries.md](references/duckdb-vd-queries.md) — VD用DuckDBクエリ集
- [vd-column-defaults.md](references/vd-column-defaults.md) — デザインフェーズで設定が必要なカラムのデフォルト値
- [series-koma-assets.csv](references/series-koma-assets.csv) — 作品別コマアセットキー・back_ground_offset対応表
- [koma-background-offset.md](references/koma-background-offset.md) — コマアセットキー別推奨back_ground_offset値
