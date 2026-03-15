---
name: vd-masterdata-ingame-designer
description: VDインゲームマスタデータ生成の統合スキル。design.md生成（5ステップ）→CSV生成→design.json生成→xlsx生成まで一気通貫で実行。--step引数で個別ステップのみの実行も可能。「VDブロック生成」「VD設計書作成」「VDインゲーム設計」「VD全自動生成」「vd-masterdata-ingame-designer」などのキーワードで使用します。
---

# VDインゲームデザイナー統合スキル

## 概要

VDインゲームマスタデータ生成の**全フローを統合制御**するスキル。

- **`--step` 引数なし（デフォルト）**: 全フェーズを実行（design.md生成 → CSV → JSON → xlsx）
- **`--step=<ステップ名>`**: 個別ステップのみ実行

**キャラ選定はスキル内では行わない。** どのキャラを使うかはユーザーが引数として指示する。

---

## 引数

| 引数 | 必須 | 説明 | 例 |
|------|------|------|-----|
| `作品ID` | ✓ | シリーズ略称 | `kai` / `dan` / `spy` 等 |
| `ブロック種別` | ✓ | `normal` または `boss` | `normal` |
| `キャラリスト` | ✓（全体実行時） | キャラID・色属性・体数のリスト | `e_kai_00101_vd_Normal_Yellow x3, c_kai_00301_vd_Normal_Green x1` |
| `[ボスキャラID]` | bossのみ | ボスブロックのボスキャラID | `e_kai_00501_vd_Boss_Colorless` |
| `[連番]` | 任意 | 開始番号（デフォルト: `00001`） | `00002` |
| `[--step]` | 任意 | 実行フェーズ指定（下記参照） | |
| `[--batch]` | 任意 | ヒアリング・確認ループをスキップ | |

### --step オプション

| --step | 実行内容 |
|--------|---------|
| 未指定（デフォルト） | Phase 1〜5 全て |
| `all` | Phase 1〜5 全て |
| `enemy-stats` | Phase 2 の敵ステータス設計のみ（design.md の該当セクション更新） |
| `enemy-action` | Phase 2 の行動パターン設計のみ |
| `koma` | Phase 2 のコマ設計のみ |
| `sequence` | Phase 2 のシーケンス設計のみ |
| `presentation` | Phase 2 の演出設計のみ |
| `design` | Phase 2 全体（design.md 生成） |
| `csv` | Phase 3 のみ（CSV 生成） |
| `json` | Phase 4 のみ（design.json 生成） |
| `xlsx` | Phase 5 のみ（xlsx 生成） |

---

## 実行フロー

```
Phase 1: ヒアリング（--batch で省略可）
  └─ 作品ID・ブロック種別・敵構成・キャラリストを確認

Phase 2: design.md 生成（--step=design or all or 個別ステップ）
  ├─ enemy-stats:    steps/01-enemy-stats.md の手順を Read して実行 → 敵ステータスセクション生成
  ├─ enemy-action:   steps/02-enemy-action.md の手順を Read して実行 → 行動パターンセクション生成
  ├─ koma:           steps/03-koma.md の手順を Read して実行 → コマ設計セクション生成
  ├─ sequence:       steps/04-sequence.md の手順を Read して実行 → シーケンス設計セクション生成
  └─ presentation:   steps/05-presentation.md の手順を Read して実行 → 演出セクション生成
  → design.md として統合・保存（references/design-format.md に基づく）

Phase 3: CSV 生成（--step=csv or all）
  └─ vd-masterdata-ingame-data-creator スキルの手順で実行

Phase 4: design.json 生成（--step=json or all）
  └─ vd-masterdata-ingame-design-json-creator スキルの手順で実行

Phase 5: xlsx 生成（--step=xlsx or all）
  └─ vd-masterdata-ingame-xlsx-creator スキルの手順で実行
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

**フォーマット参照**: `.claude/skills/vd-masterdata-ingame-designer/references/design-format.md`

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

#### Step 2-1: 敵ステータス設計（enemy-stats）

Read tool で `.claude/skills/vd-masterdata-ingame-designer/steps/01-enemy-stats.md` を読み込み、手順に従って実行する。

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-2: 行動パターン設計（enemy-action）

Read tool で `.claude/skills/vd-masterdata-ingame-designer/steps/02-enemy-action.md` を読み込み、手順に従って実行する。

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-3: コマ設計（koma）

Read tool で `.claude/skills/vd-masterdata-ingame-designer/steps/03-koma.md` を読み込み、手順に従って実行する。

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-4: シーケンス設計（sequence）

Read tool で `.claude/skills/vd-masterdata-ingame-designer/steps/04-sequence.md` を読み込み、手順に従って実行する。

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### Step 2-5: 演出設計（presentation）

Read tool で `.claude/skills/vd-masterdata-ingame-designer/steps/05-presentation.md` を読み込み、手順に従って実行する。

**`--batch` なし**: ユーザーに確認を求める → 承認後に次へ進む

#### design.md 統合・保存

全5ステップ完了後、`design-format.md` のフォーマットに従って全セクションを統合し、design.md として保存する:

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

## 個別ステップ実行（--step=<ステップ名>）

個別ステップ指定時は、対象 design.md の**該当セクションのみを更新**する。

**前提確認**:
- `--step=enemy-stats` / `enemy-action` / `koma` / `sequence` / `presentation`: ブロックIDが確定していること（design.md が存在しなければ新規作成）
- `--step=csv`: 対象ブロックの `design.md` が存在することを確認
- `--step=json`: 対象ブロックの `design.md` と `generated/*.csv` が存在することを確認
- `--step=xlsx`: 全ブロックの `design.json` が存在することを確認

---

## 呼び出し例

```
# フル生成（全フェーズ）
/vd-masterdata-ingame-designer 作品ID=kai ブロック種別=normal キャラリスト=e_kai_00101_vd_Normal_Yellow,c_kai_00301_vd_Normal_Green

# ボスブロック
/vd-masterdata-ingame-designer 作品ID=kai ブロック種別=boss ボスキャラID=e_kai_00501_vd_Boss_Colorless

# コマ設計だけ実行（design.mdの該当セクションのみ更新）
/vd-masterdata-ingame-designer 作品ID=kai ブロック種別=normal --step=koma

# シーケンス設計だけ実行
/vd-masterdata-ingame-designer 作品ID=kai ブロック種別=normal --step=sequence

# バッチ（確認ループなし）
/vd-masterdata-ingame-designer 作品ID=dan ブロック種別=normal キャラリスト=e_dan_00101_vd_Normal_Red,e_dan_00201_vd_Normal_Blue --batch

# design.mdのみ生成
/vd-masterdata-ingame-designer 作品ID=spy ブロック種別=normal キャラリスト=e_spy_00101_vd_Normal_Yellow --step=design

# CSVのみ生成（design.mdは既存）
/vd-masterdata-ingame-designer 作品ID=kai ブロック種別=normal --step=csv
```

---

## ガードレール

1. **キャラ選定はユーザーが行う**: スキルは渡されたキャラIDを前提とし、独自にキャラを追加・変更しない
2. **VD固定値を全フェーズで遵守**: `InitialSummon`/`ElapsedTime`/`SwitchSequenceGroup` 禁止、c_キャラチェーンルール等
3. **各Stepの承認を得てから次へ**: `--batch` フラグがない場合、各ステップの成果物をユーザーが承認するまで次のステップに進まない
4. **design.mdを媒介としてフェーズ間でコンテキストを引き継ぐ**: 各ステップの出力は design.md の該当セクションに随時書き込む
5. **部分実行時は前提ファイルを確認**: `--step=csv` 時は design.md の存在を確認してから実行

---

## スキル間のデータ受け渡し

```
[引数] → Phase 2（steps/01〜05 を順次 Read して実行）
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
| `vd-masterdata-ingame-data-creator` | Phase 3 | CSV生成 |
| `vd-masterdata-ingame-design-json-creator` | Phase 4 | JSON生成 |
| `vd-masterdata-ingame-xlsx-creator` | Phase 5 | xlsx生成 |

---

## リファレンス

- `.claude/skills/vd-masterdata-ingame-designer/references/design-format.md` — design.md フォーマットテンプレート
- `.claude/skills/vd-masterdata-ingame-designer/references/series-koma-assets.csv` — 作品別コマアセットキー
- `.claude/skills/vd-masterdata-ingame-designer/references/koma-background-offset.md` — 推奨back_ground_offset値
- `.claude/skills/vd-masterdata-ingame-designer/references/vd-column-defaults.md` — カラムデフォルト値定義
- `.claude/skills/vd-masterdata-ingame-designer/references/MstAutoPlayerSequence_具体例集.md` — 過去実例集
- `.claude/skills/vd-masterdata-ingame-designer/references/MstAutoPlayerSequence_設計パターン集.md` — 設計パターン集
