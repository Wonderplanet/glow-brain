---
name: vd-masterdata-ingame-design-orchestrator
description: VDインゲームマスタデータ生成の全フローを統合制御するオーケストレーションスキル。5専門スキルを順次呼び出してdesign.mdを生成し、CSV生成→design.json生成→xlsx生成まで完走します。「VDブロック全自動生成」「VDインゲーム一括生成」「orchestrator」「VD全フロー」などのキーワードで使用します。
---

# VDインゲームデザインオーケストレーションスキル

## 概要

VDインゲームマスタデータの**全生成フローを統合制御**するオーケストレーションスキル。

5つの専門スキルを順次呼び出して `design.md` を構築し、その後 CSV・design.json・xlsx 生成まで一気通貫で実行する。

**キャラ選定はスキル内では行わない。** どのキャラを使うかはユーザーが引数として指示する。

---

## 引数

| 引数 | 必須 | 説明 | 例 |
|------|------|------|-----|
| `作品ID` | ✓ | シリーズ略称 | `kai` / `dan` / `spy` 等 |
| `ブロック種別` | ✓ | `normal` または `boss` | `normal` |
| `キャラリスト` | ✓ | キャラID・色属性・体数のリスト | `e_kai_00101_vd_Normal_Yellow x3, c_kai_00301_vd_Normal_Green x1` |
| `[ボスキャラID]` | bossのみ | ボスブロックのボスキャラID | `e_kai_00501_vd_Boss_Colorless` |
| `[連番]` | 任意 | 開始番号（デフォルト: `00001`） | `00002` |
| `[--step]` | 任意 | 実行フェーズ指定 | `all`（デフォルト）/ `design` / `csv` / `json` / `xlsx` |
| `[--batch]` | 任意 | ヒアリング・確認ループをスキップ | |

---

## 実行フロー

```
Phase 1: ヒアリング（--batch で省略可）
  └─ 作品ID・ブロック種別・敵構成・キャラリストを確認

Phase 2: design.md 生成（--step=design or all）
  ├─ Step 2-1: vd-masterdata-ingame-enemy-stats-designer    → 敵ステータスセクション生成
  ├─ Step 2-2: vd-masterdata-ingame-enemy-action-designer   → 行動パターンセクション生成
  ├─ Step 2-3: vd-masterdata-ingame-koma-designer           → コマ設計セクション生成
  ├─ Step 2-4: vd-masterdata-ingame-sequence-designer       → シーケンス設計セクション生成
  └─ Step 2-5: vd-masterdata-ingame-presentation-designer   → 演出セクション生成
  → design.md として統合・保存

Phase 3: CSV 生成（--step=csv or all）
  └─ vd-masterdata-ingame-data-creator（既存スキル）を呼び出し

Phase 4: design.json 生成（--step=json or all）
  └─ vd-masterdata-ingame-design-json-creator（既存スキル）を呼び出し

Phase 5: xlsx 生成（--step=xlsx or all）
  └─ vd-masterdata-ingame-xlsx-creator（既存スキル）を呼び出し
```

---

## 詳細ワークフロー

### Phase 1: ヒアリング（`--batch` で省略）

以下の情報が揃っているかを確認する。不足があればユーザーに確認する。

| 確認項目 | 内容 |
|---------|------|
| 作品ID | シリーズ略称（`kai` / `dan` / `spy` 等）。未指定なら確認する |
| ブロック種別 | `boss` または `normal` のどちらか |
| ブロックID | `vd_{作品ID}_{ブロック種別}_{連番}` の形式で決定する |
| キャラリスト | 各キャラのID・色属性・体数。**ユーザーが指定する**（スキルが独自に選定しない） |
| ボスキャラID | bossブロックのみ：ボスキャラID・色属性 |

**ブロックIDの決定**:
```
vd_{作品ID}_{ブロック種別}_{連番5桁}
例: vd_kai_normal_00001
```
既存フォルダが存在する場合は次の連番を使用。

---

### Phase 2: design.md 生成

出力先:
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/{ブロックID}/design.md
```

**VD固有の固定値（全ステップで遵守）**:

| 項目 | bossブロック | normalブロック |
|------|------------|--------------|
| `MstEnemyOutpost.hp` | `100`（固定） | `100`（固定） |
| `MstKomaLine` 行数 | `1行`（固定） | `3行固定` |
| フェーズ切り替え | 禁止 | 禁止 |
| BGM | `SSE_SBG_003_004` | `SSE_SBG_003_010` |
| `mst_defense_target_id` | `__NULL__` | `__NULL__` |
| `mst_auto_player_sequence_id` | `""`（空文字） | `""`（空文字） |
| `boss_bgm_asset_key` | `""`（空文字） | `""`（空文字） |
| 全coefカラム×6 | `1.0` | `1.0` |

#### Step 2-1: 敵ステータス設計

`vd-masterdata-ingame-enemy-stats-designer` スキルの手順に従い、以下を実行:

1. `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` を読み込む
2. `vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv` を参照して既存IDを確認
3. 引数のキャラIDごとに `base_hp` / `base_atk` / `base_spd` / `knockback` / `combo` / `drop_bp` を設計
4. メインクエスト実績 (`specs/メインクエスト_Normal難易度_エネミー/`) を参照（該当ファイルがあれば）
5. 敵キャラ選定テーブル・ステータステーブルを生成

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-2: 行動パターン設計

`vd-masterdata-ingame-enemy-action-designer` スキルの手順に従い、以下を実行:

1. `domain/knowledge/masterdata/table-docs/MstAttack.md` / `MstAttackElement.md` を読み込む
2. 各キャラの攻撃種別・ダメージ種別・効果・対象を設計
3. 対抗キャラがある場合は軽減ダメージ種別と連動させる
4. 行動パターンテーブルを生成

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-3: コマ設計

`vd-masterdata-ingame-koma-designer` スキルの手順に従い、以下を実行:

1. `.claude/skills/vd-masterdata-ingame-design-creator/references/series-koma-assets.csv` を読み込む
2. `.claude/skills/vd-masterdata-ingame-design-creator/references/koma-background-offset.md` を読み込む
3. `.claude/skills/vd-masterdata-ingame-design-creator/references/vd-column-defaults.md` を読み込む
4. ブロック種別に応じた行数でコマレイアウトを設計（normal=3行、boss=1行）
5. 作品IDに合った `koma1_asset_key` を設定
6. Mermaid block-beta 図 + 行別テーブルを生成

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-4: シーケンス設計

`vd-masterdata-ingame-sequence-designer` スキルの手順に従い、以下を実行:

1. `.claude/skills/vd-masterdata-ingame-design-creator/references/MstAutoPlayerSequence_具体例集.md` を読み込む
2. `.claude/skills/vd-masterdata-ingame-design-creator/references/MstAutoPlayerSequence_設計パターン集.md` を読み込む
3. `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` を読み込む
4. VD禁止ルール（InitialSummon / ElapsedTime / SwitchSequenceGroup 禁止）を厳守
5. c_キャラチェーンルールを適用
6. normalブロックは15体以上になるよう設計
7. Mermaid flowchart 図 + elemテーブルを生成

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-5: 演出設計

`vd-masterdata-ingame-presentation-designer` スキルの手順に従い、以下を実行:

1. ブロック種別に応じた `bgm_asset_key` を設定（boss=`SSE_SBG_003_004`、normal=`SSE_SBG_003_010`）
2. 作品ID・ブロック種別に応じた `loop_background_asset_key` を設定
3. aura_type を各キャラの役割（雑魚=`Default`、ボス=`Boss`）に応じて設定
4. 演出セクションを生成

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### design.md 統合・保存

全5ステップ完了後、`design-format.md` のフォーマットに従って全セクションを統合し、design.md として保存する:

**フォーマット参照**: `.claude/skills/vd-masterdata-ingame-design-creator/references/design-format.md`

```
# {ブロックID} インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: {release_key}

## インゲーム要件テキスト
{Step 2-1〜2-5の設計内容を散文でまとめる}

## レベルデザイン
### 勝利条件
### 敵キャラ設計     ← Step 2-1, 2-2の出力
### コマ設計         ← Step 2-3の出力
### 敵キャラシーケンス設計  ← Step 2-4の出力

## 演出              ← Step 2-5の出力
```

---

### Phase 3: CSV 生成

`vd-masterdata-ingame-data-creator` スキルの手順に従い実行:

1. `design.md` を読み込み、データを抽出
2. SQLite DB を構築（schema.sql で作成）
3. データを INSERT
4. CSV をエクスポート（6テーブル: MstEnemyStageParameter・MstEnemyOutpost・MstPage・MstKomaLine・MstAutoPlayerSequence・MstInGame）

**`--batch` なし**: CSV 生成完了後、生成サマリーを表示してユーザーに確認

---

### Phase 4: design.json 生成

`vd-masterdata-ingame-design-json-creator` スキルの手順に従い実行:

1. `design.md` と `generated/*.csv` を読み込む
2. JSON を構築して `design.json` に保存

---

### Phase 5: xlsx 生成

`vd-masterdata-ingame-xlsx-creator` スキルの手順に従い実行:

```bash
python .claude/skills/vd-masterdata-ingame-xlsx-creator/scripts/create_all_xlsx.py \
  --task-dir "domain/tasks/20260311_202700_vd_masterdata_ingame_generation"
```

---

## --step 指定による部分実行

| --step | 実行フェーズ |
|--------|-----------|
| `all`（デフォルト） | Phase 1〜5 全て |
| `design` | Phase 2 のみ（design.md 生成） |
| `csv` | Phase 3 のみ（CSV 生成） |
| `json` | Phase 4 のみ（design.json 生成） |
| `xlsx` | Phase 5 のみ（xlsx 生成） |

**部分実行時の前提確認**:
- `--step=csv`: 対象ブロックの `design.md` が存在することを確認
- `--step=json`: 対象ブロックの `design.md` と `generated/*.csv` が存在することを確認
- `--step=xlsx`: 全ブロックの `design.json` が存在することを確認

---

## 呼び出し例

```
# フル生成（全フェーズ）
/vd-masterdata-ingame-design-orchestrator 作品ID=kai ブロック種別=normal キャラリスト=e_kai_00101_vd_Normal_Yellow,c_kai_00301_vd_Normal_Green

# ボスブロック
/vd-masterdata-ingame-design-orchestrator 作品ID=kai ブロック種別=boss ボスキャラID=e_kai_00501_vd_Boss_Colorless

# バッチモード（確認ループなし）
/vd-masterdata-ingame-design-orchestrator 作品ID=dan ブロック種別=normal キャラリスト=e_dan_00101_vd_Normal_Red,e_dan_00201_vd_Normal_Blue --batch

# design.mdのみ生成
/vd-masterdata-ingame-design-orchestrator 作品ID=spy ブロック種別=normal キャラリスト=e_spy_00101_vd_Normal_Yellow --step=design

# CSVのみ生成（design.mdは既存）
/vd-masterdata-ingame-design-orchestrator 作品ID=kai ブロック種別=normal --step=csv
```

---

## ガードレール

1. **キャラ選定はユーザーが行う**: スキルは渡されたキャラIDを前提とし、独自にキャラを追加・変更しない
2. **VD固定値を全フェーズで遵守**: `InitialSummon`/`ElapsedTime`/`SwitchSequenceGroup` 禁止、c_キャラチェーンルール等
3. **各Stepの承認を得てから次へ**: `--batch` フラグがない場合、各専門スキルの成果物をユーザーが承認するまで次のステップに進まない
4. **design.mdを媒介としてフェーズ間でコンテキストを引き継ぐ**: 各専門スキルの出力は design.md の該当セクションに随時書き込む
5. **部分実行時は前提ファイルを確認**: `--step=csv` 時は design.md の存在を確認してから実行

---

## スキル間のデータ受け渡し

```
[引数] → Phase 2（専門スキル×5）
           ↓ design.md に各セクションを順次追記
Phase 2 → [design.md] → Phase 3（data-creator）
                         ↓ generated/*.csv
                         → Phase 4（design-json-creator）
                           ↓ design.json
                           → Phase 5（xlsx-creator）
                             ↓ vd_all/vd_all.xlsx
```

---

## 参照スキル一覧

| スキル | フェーズ | 役割 |
|--------|---------|------|
| `vd-masterdata-ingame-enemy-stats-designer` | Phase 2-1 | 敵ステータス設計 |
| `vd-masterdata-ingame-enemy-action-designer` | Phase 2-2 | 行動パターン設計 |
| `vd-masterdata-ingame-koma-designer` | Phase 2-3 | コマ設計 |
| `vd-masterdata-ingame-sequence-designer` | Phase 2-4 | シーケンス設計 |
| `vd-masterdata-ingame-presentation-designer` | Phase 2-5 | 演出設計 |
| `vd-masterdata-ingame-data-creator` | Phase 3 | CSV生成 |
| `vd-masterdata-ingame-design-json-creator` | Phase 4 | JSON生成 |
| `vd-masterdata-ingame-xlsx-creator` | Phase 5 | xlsx生成 |
