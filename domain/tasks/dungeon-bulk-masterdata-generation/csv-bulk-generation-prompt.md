# dungeon マスタデータ 一括CSV生成 — subagent委譲プロンプト集

`masterdata-ingame-creator` スキルを使い、インゲーム要件テキスト1件ごとにsubagentを起動してCSVを並列生成するためのプロンプト集。

---

## 前提

- インゲーム要件テキストは全30ブロック分が `outputs/` 配下に生成済み
- 1ブロック（1インゲームID）= 1サブエージェントで並列処理する
- **インゲーム要件テキストは設計・承認済みとみなし**、サブエージェントはユーザー確認を待たずPhase 2（CSV生成）まで自律的に完了させる

---

## 出力先ルール（重要）

スキルのデフォルト出力先（`domain/tasks/masterdata-entry/masterdata-ingame-creator/`）**ではなく**、以下に保存すること：

```
domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/normal/generated/
  ├── MstEnemyStageParameter.csv
  ├── MstEnemyOutpost.csv
  ├── MstPage.csv
  ├── MstKomaLine.csv
  ├── MstAutoPlayerSequence.csv
  └── MstInGame.csv

domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/boss/generated/
  ├── （同上）
```

---

## 対象ブロック一覧（全30件）

| # | series_id | ブロック | インゲームID | インプットファイル | 備考 |
|---|-----------|---------|------------|-----------------|-----|
| 1 | chi | normal | dungeon_chi_normal_00001 | outputs/chi/normal/dungeon_chi_normal_00001.md | |
| 2 | chi | boss | dungeon_chi_boss_00001 | outputs/chi/boss/dungeon_chi_boss_00001.md | |
| 3 | dan | normal | dungeon_dan_normal_00001 | outputs/dan/normal/dungeon_dan_normal_00001.md | |
| 4 | dan | boss | dungeon_dan_boss_00001 | outputs/dan/boss/dungeon_dan_boss_00001.md | |
| 5 | gom | normal | dungeon_gom_normal_00001 | outputs/gom/normal/dungeon_gom_normal_00001.md | |
| 6 | gom | boss | dungeon_gom_boss_00001 | outputs/gom/boss/dungeon_gom_boss_00001.md | |
| 7 | hut | normal | dungeon_hut_normal_00001 | outputs/hut/normal/dungeon_hut_normal_00001.md | |
| 8 | hut | boss | dungeon_hut_boss_00001 | outputs/hut/boss/dungeon_hut_boss_00001.md | |
| 9 | jig | normal | dungeon_jig_normal_00001 | outputs/jig/normal/dungeon_jig_normal_00001.md | |
| 10 | jig | boss | dungeon_jig_boss_00001 | outputs/jig/boss/dungeon_jig_boss_00001.md | |
| 11 | kai | normal | dungeon_kai_normal_00001 | outputs/kai/normal/dungeon_kai_normal_00001.md | |
| 12 | kai | boss | dungeon_kai_boss_00001 | outputs/kai/boss/dungeon_kai_boss_00001.md | |
| 13 | kim | normal | dungeon_kim_normal_00001 | outputs/kim/normal/dungeon_kim_normal_00001.md | |
| 14 | kim | boss | dungeon_kim_boss_00001 | outputs/kim/boss/dungeon_kim_boss_00001.md | |
| 15 | mag | normal | dungeon_mag_normal_00001 | outputs/mag/normal/dungeon_mag_normal_00001.md | |
| 16 | mag | boss | dungeon_mag_boss_00001 | outputs/mag/boss/dungeon_mag_boss_00001.md | |
| 17 | osh | normal | dungeon_osh_normal_00001 | outputs/osh/normal/dungeon_osh_normal_00001.md | |
| 18 | osh | boss | dungeon_osh_boss_00001 | outputs/osh/boss/dungeon_osh_boss_00001.md | |
| 19 | spy | normal | dungeon_spy_normal_00001 | outputs/spy/normal/dungeon_spy_normal_00001.md | |
| 20 | spy | boss | dungeon_spy_boss_00001 | outputs/spy/boss/dungeon_spy_boss_00001.md | |
| 21 | sum | normal | dungeon_sum_normal_00001 | outputs/sum/normal/dungeon_sum_normal_00001.md | |
| 22 | sum | boss | dungeon_sum_boss_00001 | outputs/sum/boss/dungeon_sum_boss_00001.md | |
| 23 | sur | normal | dungeon_sur_normal_00001 | outputs/sur/normal/dungeon_sur_normal_00001.md | |
| 24 | sur | boss | dungeon_sur_boss_00001 | outputs/sur/boss/dungeon_sur_boss_00001.md | |
| 25 | tak | normal | dungeon_tak_normal_00001 | outputs/tak/normal/dungeon_tak_normal_00001.md | |
| 26 | tak | boss | dungeon_tak_boss_00001 | outputs/tak/boss/dungeon_tak_boss_00001.md | |
| 27 | you | normal | dungeon_you_normal_00001 | outputs/you/normal/dungeon_you_normal_00001.md | |
| 28 | you | boss | dungeon_you_boss_00001 | outputs/you/boss/dungeon_you_boss_00001.md | |
| 29 | yuw | normal | dungeon_yuw_normal_00001 | outputs/yuw/normal/dungeon_yuw_normal_00001.md | |
| 30 | yuw | boss | dungeon_yuw_boss_00001 | outputs/yuw/boss/dungeon_yuw_boss_00001.md | |

---

## subagentプロンプトテンプレート

以下を各ブロックの情報に合わせて `{変数}` を差し替えて使用する。

---

```
以下のインゲーム要件テキストをインプットとして、masterdata-ingame-creator スキルのワークフローに従い、CSVを生成・保存してください。

## 実行の前提

- インゲーム要件テキストはすでに設計・承認済みのため、設計書（design.md）生成後はユーザー確認を待たず、自律的にPhase 2（CSV生成）まで完了してください
- 禁止: domain/tasks/masterdata-entry/ 以下への保存（デフォルト出力先は使わない）
- 禁止: domain/tasks/masterdata-entry/ 以下のファイルを見ること。一才見てはいけません。見るの禁止です。

## インプット（インゲーム要件テキスト）

以下のファイルを読み込んでください：

domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/{block_type}/dungeon_{series_id}_{block_type}_00001.md

## スキル参照先

以下のファイルを参照してスキルのワークフローを実行してください：

- スキル定義: .claude/skills/masterdata-ingame-creator/SKILL.md
- ステージ種別パターン: .claude/skills/masterdata-ingame-creator/references/stage-type-patterns.md
- シーケンス設計ガイド: .claude/skills/masterdata-ingame-creator/references/sequence-design-guide.md
- 敵パラメータガイド: .claude/skills/masterdata-ingame-creator/references/enemy-parameter-guide.md
- コマレイアウトガイド: .claude/skills/masterdata-ingame-creator/references/koma-layout-guide.md
- IDルール: .claude/skills/masterdata-ingame-creator/references/id-naming-rules.md
- テーブル生成順: .claude/skills/masterdata-ingame-creator/references/table-creation-order.md
- DuckDBクエリ集: .claude/skills/masterdata-ingame-creator/references/duckdb-reference-queries.md

## 出力先（デフォルトから変更）

以下のパスに保存してください（ディレクトリがなければ作成）：

domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/{block_type}/generated/
  ├── MstEnemyStageParameter.csv
  ├── MstEnemyOutpost.csv
  ├── MstPage.csv
  ├── MstKomaLine.csv
  ├── MstAutoPlayerSequence.csv
  └── MstInGame.csv

設計書（design.md）の保存先：
domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/{block_type}/design.md

## dungeon仕様の固定値（必ず守ること）

- normal ブロック: MstEnemyOutpost HP = 100, コマ行数 = 3行固定
- boss ブロック: MstEnemyOutpost HP = 1,000, コマ行数 = 1行固定, ボス撃破までゲートダメージ無効

## 完了条件

全CSVと design.md を保存したら、以下を報告してください：
- 保存したファイル一覧
- 各CSVの行数サマリー
- 使用したインゲームID・敵砦ID
```

---

## 実行時のポイント

### 並列起動数の目安

- **1バッチ: 最大10〜15作品** を並列起動（15以上は一度に起動せず2バッチに分ける）
- `run_in_background: true` を必ず指定する

### 確認コマンド（完了後）

```bash
# 生成済みCSVの確認
ls domain/tasks/dungeon-bulk-masterdata-generation/outputs/*/normal/generated/
ls domain/tasks/dungeon-bulk-masterdata-generation/outputs/*/boss/generated/
```

### 検証（全件完了後）

```
/masterdata-ingame-verifier
```

全作品分が揃ったら `/masterdata-csv-to-xlsx` でXLSX出力。
