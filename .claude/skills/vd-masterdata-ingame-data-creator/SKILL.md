---
name: vd-masterdata-ingame-data-creator
description: VDインゲーム設計書（design.md）からSQLiteを経由してCSVを生成するスキル。CHECK制約でenum値を検証し、エクスポート時に列順序を固定することで高品質なCSVを保証します。「VDインゲームCSV生成」「design.mdからCSV作成」「VDマスタデータ生成」「SQLite経由CSV」などのキーワードで使用します。
---

# VDインゲームCSV生成スキル（SQLite経由）

## 概要

`vd-masterdata-ingame-design-creator`スキルで承認した **design.md** を読み込み、
SQLite の CHECK 制約で値を検証しながら、列順序を保証した CSV を生成するスキル。

- **インプット**: design.md のパス
- **アウトプット**: design.md と同じフォルダの `generated/` 配下に6テーブルのCSV

---

## 出力先

```
{design.mdと同じフォルダ}/
├── sqlite/
│   ├── .gitignore      ← *.db, *.db-wal, *.db-shm を除外
│   └── ingame.db       ← git管理外（再生成可能なため）
└── generated/
    ├── MstEnemyStageParameter.csv
    ├── MstEnemyOutpost.csv
    ├── MstPage.csv
    ├── MstKomaLine.csv
    ├── MstAutoPlayerSequence.csv
    └── MstInGame.csv
```

**具体例**:
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_kai_normal_00001/
├── design.md
├── sqlite/
│   ├── .gitignore
│   └── ingame.db
└── generated/
    └── *.csv（6ファイル）
```

---

## 5ステップワークフロー

### Step 0: 対象確認

以下を確認する。

| 確認項目 | 内容 |
|---------|------|
| design.mdのパス | 未指定なら直近の `vd-ingame-design-creator/{ブロックID}/design.md` を確認 |
| ブロックID | design.mdのH1タイトルから取得（例: `vd_kai_normal_00001`） |
| MstEnemyStageParameter参照CSV | 固定パス（後述）を使用 |

**MstEnemyStageParameter 参照CSVパス（固定）**:
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv
```

変数として定義:
```bash
BLOCK_ID="vd_kai_normal_00001"   # design.mdのH1から取得
WORK_DIR="domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/${BLOCK_ID}"
SQLITE_DB="${WORK_DIR}/sqlite/ingame.db"
ENEMY_CSV="domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv"
```

---

### Step 1: design.md 読み込みとデータ抽出

Read tool で design.md を読み込み、以下のデータを抽出する。

| 設計書セクション | 抽出データ | 対象テーブル |
|---------------|---------|-----------|
| 敵キャラステータス表 | 使用するMstEnemyStageParameter ID一覧 | MstEnemyStageParameter（フィルタ用） |
| コマ設計表 | row/height/コマ数/各koma幅/koma_asset_key/back_ground_offset/effect | MstKomaLine |
| シーケンス設計表 | elem/条件タイミング/敵ID/数/aura_type | MstAutoPlayerSequence |
| 演出 > BGM | bgm_asset_key値 | MstInGame |
| ID一覧 | 全テーブルのID | 各テーブル共通 |

**空欄になりがちなカラムのデフォルト値**: [vd-column-defaults.md](../../vd-masterdata-ingame-design-creator/references/vd-column-defaults.md) を参照する。

---

### Step 2: SQLite DB 構築

```bash
mkdir -p "${WORK_DIR}/sqlite"

# .gitignore 作成（DBファイルをgit管理外に）
cat > "${WORK_DIR}/sqlite/.gitignore" << 'EOF'
*.db
*.db-wal
*.db-shm
EOF

# スキーマ作成
sqlite3 "$SQLITE_DB" < .claude/skills/vd-masterdata-ingame-data-creator/scripts/schema.sql
```

---

### Step 3: INSERT（Claudeが生成・実行）

Step 1 で抽出したデータを基に、Claude が以下のテーブルへ INSERT 文を生成して実行する。
**MstEnemyStageParameter は INSERT しない**（既存CSV からインポート）。

```bash
sqlite3 "$SQLITE_DB" <<'SQL'
BEGIN;

-- MstEnemyOutpost
INSERT INTO mst_enemy_outposts (id, hp, is_damage_invalidation, outpost_asset_key, artwork_asset_key, release_key)
VALUES ('{ブロックID}', {hp}, NULL, NULL, NULL, {release_key});

-- MstPage
INSERT INTO mst_pages (id, release_key)
VALUES ('{ブロックID}', {release_key});

-- MstKomaLine（row数分）
INSERT INTO mst_koma_lines (id, mst_page_id, row, height, koma_line_layout_asset_key, ...)
VALUES (...);

-- MstAutoPlayerSequence（elem数分）
INSERT INTO mst_auto_player_sequences (id, sequence_set_id, sequence_element_id, condition_type, condition_value, action_type, action_value, summon_count, summon_interval, summon_animation_type, aura_type, death_type, enemy_hp_coef, enemy_attack_coef, enemy_speed_coef, defeated_score, deactivation_condition_type, release_key, ...)
VALUES (...);

-- MstInGame
INSERT INTO mst_in_games (id, mst_auto_player_sequence_id, mst_auto_player_sequence_set_id, bgm_asset_key, mst_page_id, mst_enemy_outpost_id, boss_mst_enemy_stage_parameter_id, boss_count, normal_enemy_hp_coef, normal_enemy_attack_coef, normal_enemy_speed_coef, boss_enemy_hp_coef, boss_enemy_attack_coef, boss_enemy_speed_coef, release_key, ...)
VALUES (...);

-- MstInGameI18n（result_tips/descriptionがある場合のみ）
-- INSERT INTO mst_in_games_i18n (id, mst_in_game_id, language, result_tips, description)
-- VALUES ('{ブロックID}_ja', '{ブロックID}', 'ja', '{result_tips}', '{description}');

COMMIT;
SQL
```

**CHECK 制約エラーが出た場合**: エラー内容を確認して値を修正し、再 INSERT。

#### INSERT時の主要な制約値

| カラム | 有効な値 |
|--------|---------|
| `condition_type` | `None` / `ElapsedTime` / `OutpostDamage` / `OutpostHpPercentage` / `InitialSummon` / `EnterTargetKomaIndex` / `DarknessKomaCleared` / `FriendUnitDead` / `FriendUnitTransform` / `FriendUnitSummoned` / `SequenceElementActivated` / `ElapsedTimeSinceSequenceGroupActivated` |
| `action_type` | `None` / `SummonEnemy` / `SummonPlayerCharacter` / `SwitchSequenceGroup` / `PlayerSpecialAttack` / `SummonPlayerSpecialCharacter` / `SummonGimmickObject` / `TransformGimmickObjectToEnemy` |
| `summon_animation_type` | `None` / `Fall0` / `Fall` / `Fall4` |
| `aura_type` | `Default` / `Boss` / `AdventBoss1` / `AdventBoss2` / `AdventBoss3` |
| `death_type` | `Normal` / `Escape` |
| `deactivation_condition_type` | `condition_type` と同じ値セット |
| `koma[1-4]_effect_type` | `None` / `AttackPowerUp` / `AttackPowerDown` / `MoveSpeedUp` / `SlipDamage` / `Gust` / `Poison` / `Darkness` / `Burn` / `Stun` / `Freeze` / `Weakening` |
| `koma[1-4]_effect_target_side` | `All` / `Player` / `Enemy` |
| `move_start/restart_condition_type` | `None` / `ElapsedTime` / `FoeEnterSameKoma` / `EnterTargetKoma` / `Damage` / `DeadFriendUnitCount` |
| `move_stop_condition_type` | `None` / `ElapsedTime` / `TargetPosition` / `PassedKomaCount` |

---

### Step 4: CSV エクスポート

```bash
python3 .claude/skills/vd-masterdata-ingame-data-creator/scripts/export_csv.py \
  --db "$SQLITE_DB" \
  --enemy-csv "$ENEMY_CSV" \
  --enemy-ids "{設計書に記載のID1},{ID2}" \
  --out "${WORK_DIR}/generated"
```

`--enemy-ids` には design.md の「敵キャラステータス表」から抽出した
MstEnemyStageParameter ID をカンマ区切りで指定する。

---

### Step 5: サマリー出力

生成完了後、以下の形式でサマリーを表示する。

```
## 生成サマリー

### 生成ファイル一覧
- MstEnemyStageParameter.csv: {行数}行（設計書参照IDのみ）
- MstEnemyOutpost.csv: 1行
- MstPage.csv: 1行
- MstKomaLine.csv: {行数}行
- MstAutoPlayerSequence.csv: {行数}行
- MstInGame.csv: 1行

### 生成したインゲームID
- {ブロックID}

### 出力先
- {WORK_DIR}/generated/
```

---

## ガードレール（必ず守ること）

1. **IDプレフィックスは `vd_`**: 全テーブルのIDは `vd_` で始まる
2. **ENABLE カラムは常に `e`**: エクスポート時に付加（テーブルには含めない）
3. **列順は sheet_schema に厳密に準拠**: `export_csv.py` が保証する
4. **CHECK 制約エラーは即座に修正**: エラーが出たら INSERT 値を確認して再実行
5. **MstEnemyStageParameter は既存 CSV のみ使用**: 新規 INSERT 禁止
6. **INSERT はトランザクション単位**: `BEGIN; ... COMMIT;` で囲む
7. **vd-column-defaults.md 参照**: 空欄になりがちなカラムのデフォルト値を使用する
8. **boss ブロックは InitialSummon で SummonEnemy**: ボスの召喚は `InitialSummon` + `summon_position=1.7`
9. **VD では SwitchSequenceGroup 禁止**: `action_type` に `SwitchSequenceGroup` は使わない
10. **normalブロックの MstKomaLine は 3行固定**: row=1,2,3 の3エントリを生成する

---

## リファレンス一覧

- [schema.sql](scripts/schema.sql) — SQLite スキーマ定義（7テーブル）
- [export_csv.py](scripts/export_csv.py) — CSV エクスポートスクリプト
- [vd-column-defaults.md](../../vd-masterdata-ingame-design-creator/references/vd-column-defaults.md) — デフォルト値定義
- [vd-masterdata-ingame-design-creator SKILL.md](../../vd-masterdata-ingame-design-creator/SKILL.md) — 前工程のスキル
