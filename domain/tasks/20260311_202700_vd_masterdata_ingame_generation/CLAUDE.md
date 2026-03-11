# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## タスク概要

限界チャレンジ（VD）インゲームマスタデータCSVを生成するタスクフォルダです。

- **ブロック種別**: Normal / Boss
- **主要成果物**: MstEnemyStageParameter・MstAutoPlayerSequence・MstInGame等のCSV
- **リリースキー**: 202604010

---

## アクセス禁止領域

以下のフォルダへの参照・編集・読み込みは一切禁止です:

- `domain/tasks/` 以下の**本タスクフォルダ以外**のタスクフォルダ全て
  - （例: `20260310_115400_vd_ingame_masterdata_generation/`, `dungeon-bulk-masterdata-generation/`, `masterdata-entry/`）
- `domain/raw-data/` 以下全て

---

## フォルダ構造

```
20260311_202700_vd_masterdata_ingame_generation/
├── specs/                          # 仕様書・テンプレート
│   └── 限界チャレンジ(VD)_アウトゲーム関連_ブロック基礎設計テンプレート.xlsx
└── vd-ingame-design-creator/       # ブロック設計・CSV生成作業領域
    ├── vd_all/
    │   └── data/
    │       └── MstEnemyStageParameter.csv  # 全VD作品の敵パラメータマスター一覧
    └── {block_id}/                 # ブロックごとのフォルダ（例: vd_kai_normal_00001）
        ├── design.md               # インゲームデータ詳細解説（人間が読める設計書）
        ├── design.json             # design.mdのJSON構造化版（xlsx生成等で使用）
        ├── generated/              # 生成されたCSVファイル群
        │   ├── MstAutoPlayerSequence.csv
        │   ├── MstEnemyOutpost.csv
        │   ├── MstEnemyStageParameter.csv
        │   ├── MstInGame.csv
        │   ├── MstKomaLine.csv
        │   └── MstPage.csv
        └── sqlite/
            └── ingame.db           # CSV生成に使用したSQLiteデータベース
```

### ブロックID命名規則

`vd_{作品ID}_{ブロック種別}_{連番4桁}`

- 作品ID例: `kai`（怪獣8号）
- ブロック種別: `normal` または `boss`
- 例: `vd_kai_normal_00001`, `vd_kai_boss_00001`

---

## 生成CSVテーブル一覧

各ブロックで生成するCSVと主要なID命名規則:

| テーブル | ID命名パターン | 備考 |
|---------|-------------|------|
| MstInGame | `{block_id}` | content_type=Dungeon, stage_type=vd_normal or vd_boss |
| MstPage | `{block_id}` | |
| MstEnemyOutpost | `{block_id}` | HP=100固定 |
| MstKomaLine | `{block_id}_{行番号}` | 例: vd_kai_normal_00001_1, _2, _3 |
| MstAutoPlayerSequence | `{block_id}_{elem番号}` | 例: vd_kai_normal_00001_1 〜 _5 |
| MstEnemyStageParameter | `e_{キャラID}_vd_{ユニット種別}_{色}` | 例: e_kai_00101_vd_Normal_Yellow |

---

## vd_all/data/ の役割

`vd_all/data/MstEnemyStageParameter.csv` は **全VD作品の敵パラメータを集約したマスターリスト**です。新規ブロックを作成する際、この一覧から既存の敵パラメータIDを参照できます。新規の敵パラメータは個別ブロックの `generated/MstEnemyStageParameter.csv` に追加します。

---

## 使用スキル

本タスクでは以下のスキルを使用します:

- **`vd-masterdata-ingame-design-creator`**: design.md 設計書の作成・調整
- **`vd-masterdata-ingame-data-creator`**: design.md から SQLite 経由で CSV 生成
- **`vd-masterdata-ingame-design-json-creator`**: design.md と generated CSV から design.json 生成
- **`vd-block-design-xlsx`**: design.md と CSV から ブロック基礎設計 xlsx 生成
