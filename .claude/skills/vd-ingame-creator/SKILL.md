---
name: vd-ingame-creator
description: 限界チャレンジ（VD）専用インゲームマスタデータCSVを作成するスキル。ユーザーが指定した作品・ブロック種別（boss/normal）からMstEnemyStageParameter・MstAutoPlayerSequence・MstInGame等の必須テーブルを段階的に生成します。「限界チャレンジ作成」「VDインゲーム作成」「VDマスタ生成」「VDブロック作成」「vd_boss」「vd_normal」「限界チャレンジCSV」「VDインゲームCSV」などのキーワードで使用します。
---

# 限界チャレンジ（VD）インゲームマスタデータ作成スキル

## 概要

限界チャレンジ（VD）のインゲームマスタデータCSVを、**作品IDとブロック種別（boss / normal のどちらか1つ）**を指定して1ブロック分を生成します。
テーブル間の依存関係を考慮した正しい順序で生成し、列ヘッダーはsheet_schemaのCSVから直接読み取って完全準拠します。

---

## 出力先

```
domain/tasks/masterdata-entry/vd-ingame-creator/{タイムスタンプ秒まで}_{英語要約}/design.md        ← Phase 1
domain/tasks/masterdata-entry/vd-ingame-creator/{タイムスタンプ秒まで}_{英語要約}/generated/*.csv  ← Phase 2
```

**例:**
```
domain/tasks/masterdata-entry/vd-ingame-creator/20260413_120000_vd_kai_boss/
  ├── design.md
  └── generated/
      ├── MstEnemyStageParameter.csv
      ├── MstEnemyOutpost.csv
      ├── MstPage.csv
      ├── MstKomaLine.csv
      ├── MstAutoPlayerSequence.csv
      └── MstInGame.csv
```

---

## VD固有の固定値（変更不可）

| 項目 | bossブロック | normalブロック |
|------|------------|--------------|
| `MstInGame.content_type` | `Dungeon` | `Dungeon` |
| `MstInGame.stage_type` | `vd_boss` | `vd_normal` |
| `MstEnemyOutpost.hp` | **1,000**（固定） | **100**（固定） |
| `MstKomaLine` 行数 | **1行**（固定） | **3行固定**（各行ごとにコマ数1〜4でランダム独立抽選） |
| `MstEnemyOutpost.is_damage_invalidation` | 空 | 空 |
| フェーズ切り替え | **禁止**（SwitchSequenceGroup使用不可） | **禁止** |
| BGM | `SSE_SBG_003_004` | `SSE_SBG_003_010` |

### IDプレフィックス

| ブロック種別 | MstInGame.id パターン | 例 |
|------------|---------------------|-----|
| boss | `vd_{作品ID}_boss_{連番5桁}` | `vd_kai_boss_00001` |
| normal | `vd_{作品ID}_normal_{連番5桁}` | `vd_kai_normal_00001` |

> **注**: 限界チャレンジIDプレフィックスは `vd_` を使用する（既存の `dungeon_` ではない）

### MstEnemyStageParameter.id 短縮形

```
{作品ID}_vd
```
- ボス例: `c_kai_00201_kai_vd_Boss_Red`
- 雑魚例: `e_kai_00001_kai_vd_Normal_Colorless`

---

## 生成対象テーブル

| テーブル | 必須度 |
|---------|--------|
| `MstEnemyStageParameter.csv` | **必須** |
| `MstEnemyOutpost.csv` | **必須** |
| `MstPage.csv` | **必須** |
| `MstKomaLine.csv` | **必須** |
| `MstAutoPlayerSequence.csv` | **必須** |
| `MstInGame.csv` | **必須** |

---

## 8ステップワークフロー（2フェーズ構成）

> **重要**: Phase 1（設計フェーズ）でユーザー承認を得てから Phase 2（生成フェーズ）に進む。

---

## Phase 1: 設計フェーズ

### Step 0: 情報確認（1回のみ）

以下が揃っているか確認し、不足は **まとめて1回だけ質問する**。

| 確認項目 | 内容 |
|---------|------|
| 作品ID | シリーズ略称（`kai` / `dan` / `spy` 等）。未指定なら確認する |
| ブロック種別 | `boss` または `normal` のどちらか。未指定なら確認する |
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

DuckDB参照結果とヒアリング内容を基に `design.md` を生成してタイムスタンプ付きディレクトリに保存する。

**設計書フォーマット:**

```markdown
# 限界チャレンジ（VD）インゲームマスタデータ設計書

## 基本情報
- 生成日時: {タイムスタンプ}
- 作品ID: {作品ID}
- ブロック種別: {boss/normal}

## 生成するインゲームID
- ID: `vd_{作品ID}_{ブロック種別}_{連番}`
- ブロック種別: {boss / normal}
- ボスキャラ: {キャラID}（bossブロックのみ）
- 雑魚キャラ: {雑魚A, 雑魚B, ...}

## MstEnemyStageParameter 敵パラメータ設計
| ID | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|-----|------|----------------------|----|-------|--------|
| {id} | ボス | {chara_id} | {値} | {値} | {値} |
| {id} | 雑魚A | {enemy_id} | {値} | {値} | {値} |

## MstAutoPlayerSequence シーケンス設計（{ブロック種別}ブロック）
- 行1: ...
- 行2: ...

## MstKomaLine 構成
- bossブロック: 1行固定（row=1, height=1.0, koma1_width=1.0）
- normalブロック: **3行固定**（row=1〜3、各行ごとに12パターンからランダム独立抽選）
  - 12パターンの定義: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/コマ設計_行パターン.csv`

| row | height | コマ数 | 各幅 |
|-----|--------|-------|-----|
| 1 | 0.33 | {ランダム1〜4} | {各幅合計=1.0、コマ設計_行パターン.csvのコマ幅を使用} |
| 2 | 0.33 | {ランダム1〜4} | {各幅合計=1.0、コマ設計_行パターン.csvのコマ幅を使用} |
| 3 | 0.34 | {ランダム1〜4} | {各幅合計=1.0、コマ設計_行パターン.csvのコマ幅を使用} |

## 参照した既存データ
- 参照ID: {id}（DuckDBクエリ結果サマリー）

## 不確定事項・要確認事項
- {あれば記載。なければ「なし」}
```

---

### Step 3: ユーザー確認・承認

```
設計書を生成しました（design.md）。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。CSV生成（Phase 2）に進みます。
修正がある場合は具体的にご指示ください。
```

**ユーザー承認なしにPhase 2へ進んではならない。**

---

## Phase 2: 生成フェーズ（ユーザー承認後に実行）

### Step 4: 生成順序の確認

1. MstEnemyStageParameter
2. MstEnemyOutpost
3. MstPage
4. MstKomaLine
5. MstAutoPlayerSequence
6. MstInGame

---

### Step 5: CSV生成（列ヘッダー厳守）

各テーブルを生成する前に **必ずRead toolでsheet_schemaのCSVファイルの3行目（ENABLE行）を読み取り**、列順を厳守する。

```
# OK: Read で projects/glow-masterdata/sheet_schema/MstInGame.csv の3行目を確認してから生成
# NG: 記憶に基づいて列順を決める
```

**ブロック種別ごとのシーケンスパターンは [vd-sequence-patterns.md](references/vd-sequence-patterns.md) を参照。**
**フロア係数・エナジー・背景設定は [vd-floor-parameters.md](references/vd-floor-parameters.md) を参照。**

---

### Step 6: 検証（masterdata-ingame-verifier）

生成した全CSVを `masterdata-ingame-verifier` スキルで総合検証する。

- **CRITICAL エラー** → 必ず修正してから次へ
- **WARNING** → 確認し、意図的ならそのまま進んでよい

---

### Step 7: ファイル保存とサマリー出力

```markdown
## 生成サマリー

### 生成ファイル一覧
- MstEnemyStageParameter.csv: {行数}行
- MstEnemyOutpost.csv: 1行
- MstPage.csv: 1行
- MstKomaLine.csv: {行数}行
- MstAutoPlayerSequence.csv: {行数}行
- MstInGame.csv: 1行

### 生成したインゲームID
- {生成したIDを列挙}

### 次のステップ
1. 詳細解説ドキュメントを生成する（Step 8）
2. projects/glow-masterdata/ に配置してDB投入する
```

---

### Step 8: 詳細解説ドキュメント生成（masterdata-ingame-detail-explainer）

生成したCSVを元に詳細解説ドキュメントを生成する。

**保存先**: タスクフォルダ直下

```
domain/tasks/masterdata-entry/vd-ingame-creator/{タイムスタンプ}_{英語要約}/{INGAME_ID}.md
```

---

## ガードレール（必ず守ること）

1. **IDプレフィックスは `vd_`**: 既存の `dungeon_` は使用しない
2. **列ヘッダーはsheet_schemaの3行目から読み取る**: 記憶で列順を決めない
3. **IDの一貫性**: `MstInGame.id = MstAutoPlayerSequence.sequence_set_id = MstPage.id = MstEnemyOutpost.id`
4. **FK参照の存在確認**: `MstAutoPlayerSequence.action_value`（SummonEnemy時）が同バッチ内の`MstEnemyStageParameter`に存在することを確認
5. **ENABLE列**: 全テーブルで `e` を設定
6. **コマ幅合計は必ず1.0**: 行あたりの全komaXX_widthの合計が1.0になること
7. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない
8. **アウトポストHP固定**: boss=1,000固定、normal=100固定（変更不可）
9. **ユーザー質問は1回**: 不足情報はまとめて1度の質問で確認する
10. **設計書承認前にCSV生成禁止**: Phase 2はStep 3の承認後のみ実行する
11. **ボスの二重設定**: `MstInGame.boss_mst_enemy_stage_parameter_id` + `MstAutoPlayerSequence`のInitialSummonで設定
12. **koma1_effect_target_side**: コマ効果なしでも `All` を設定する
13. **コマ効果は設定しない**: `koma_effect_type` は `None` 固定
14. **normalブロックのMstKomaLineは3行固定**: row=1〜3 の3エントリを生成する。各行ごとに `コマ設計_行パターン.csv`（12パターン）から独立してランダム抽選し、コマ幅はCSVの値をそのまま使用すること（1行にまとめてはならない）

---

## リファレンス一覧

- [vd-character-list.md](references/vd-character-list.md) — 作品別登場キャラ一覧（作品ID・ボスキャラ・雑魚キャラ・UR対抗キャラ）
- [vd-floor-parameters.md](references/vd-floor-parameters.md) — フロア係数・エナジー設計・背景・BGM設定
- [vd-sequence-patterns.md](references/vd-sequence-patterns.md) — boss/normalのシーケンスパターン詳細
- [vd-id-naming.md](references/vd-id-naming.md) — VD専用ID命名規則
- [duckdb-vd-queries.md](references/duckdb-vd-queries.md) — VD用DuckDBクエリ集

## 主要な参照先

| パス | 用途 |
|-----|------|
| `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` | シーケンス設計の詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` | 敵パラメータ詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstPage.md` | ページ設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstKomaLine.md` | コマライン設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` | 敵砦設計詳細 |
| `projects/glow-masterdata/sheet_schema/` | 列ヘッダー順の確認 |
| `projects/glow-masterdata/*.csv` | 既存データ参照（DuckDB） |
| `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/マスタデータ要件_共通.md` | VD共通要件（敵ステータス・ID採番・エナジー設計等） |
| `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/マスタデータ要件_ボスブロック.md` | bossブロック詳細要件 |
| `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/マスタデータ要件_通常ブロック.md` | normalブロック詳細要件（3フロア・行パターン等） |
| `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/コマ設計_行パターン.csv` | MstKomaLineの12行パターン定義（コマ数・各幅の具体値） |
