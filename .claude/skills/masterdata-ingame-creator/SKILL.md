---
name: masterdata-ingame-creator
description: インゲーム設計テキストからインゲーム関連マスタデータCSVを作成するスキル。ユーザーが提供した設計思想（敵構成・ステージ種別・難易度イメージ）を分析し、MstEnemyStageParameter・MstAutoPlayerSequence・MstInGame等の必須テーブルを含む全CSVを段階的に生成します。「インゲームCSV作成」「インゲームマスタ作成」「バトルステージ作成」「敵出現設定」「新規ステージ設計」「インゲーム設計」「限界チャレンジ」「dungeonブロック作成」などのキーワードで使用します。
---

# インゲームマスタデータ作成スキル

## 概要

インゲーム（バトル画面）を構成するマスタデータCSVを、設計思想テキストから正確に生成します。
テーブル間の依存関係を考慮した正しい順序で生成し、列ヘッダーは実際のCSVファイルから直接読み取って完全準拠します。

---

## 出力先

```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ秒まで}_{英語要約}/design.md        ← Phase 1 で生成
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ秒まで}_{英語要約}/generated/*.csv  ← Phase 2 で生成
```

**例:**
```
domain/tasks/masterdata-entry/masterdata-ingame-creator/20260220_153042_event_kai1_battle/
  ├── design.md                      ← 設計書（Phase 1 成果物）
  ├── {INGAME_ID}.md                 ← 詳細解説ドキュメント（Phase 2 成果物）
  └── generated/                     ← CSV群（Phase 2 成果物）
      ├── MstEnemyStageParameter.csv
      ├── MstEnemyOutpost.csv
      ├── MstPage.csv
      ├── MstKomaLine.csv
      ├── MstAutoPlayerSequence.csv
      └── MstInGame.csv
```

---

## 生成対象テーブル

| テーブル | 必須度 | 説明 |
|---------|--------|------|
| `MstEnemyStageParameter.csv` | **必須** | 敵ユニットのステータス・挙動パラメータ |
| `MstEnemyOutpost.csv` | **必須** | 敵砦のHP・アセット設定 |
| `MstPage.csv` | **必須** | バトルフィールドのページID定義 |
| `MstKomaLine.csv` | **必須** | コマライン（コマ配置・コマ効果）設定 |
| `MstAutoPlayerSequence.csv` | **必須** | 敵出現シーケンス設定 |
| `MstInGame.csv` | **必須** | インゲーム全体設定（バトルフィールド参照） |
| `MstInGameI18n.csv` | オプション | インゲームの多言語テキスト（説明文・ティップス） |
| `MstInGameSpecialRule.csv` | オプション | 特別ルール（コンティニュー禁止等） |

---

## 9ステップワークフロー（2フェーズ構成）

> **重要**: Phase 1（設計フェーズ）でユーザーの承認を得てから、Phase 2（生成フェーズ）に進む。
> 設計書の承認なしにCSVを生成してはならない。

---

## Phase 1: 設計フェーズ

### Step 0: 設計思想の分析・不足情報の質問（1回のみ）

ユーザーの入力テキストを分析し、以下の情報が揃っているか確認する。
**不足している場合は、まとめて1回だけ質問する。2回に分けない。**

確認する7項目（[interview-questions.md](references/interview-questions.md) 参照）:

1. **コンテンツ種別** — event/normal/hard/veryhard/raid/dungeon/pvp/tutorial 等（種別ごとの設定パターンは [ingame-content-type-patterns.md](references/ingame-content-type-patterns.md) を参照）
   - **dungeon（限界チャレンジ）の場合**: ブロック種別（boss/normal）を確認し、MstEnemyOutpost HPとコマ行数は固定値を使用する（boss=HP:1,000/コマ1行、normal=HP:100/コマ3行）
2. **ステージ種別** — event/challenge/savage/raid/dungeon_boss/dungeon_normal/normal/hard/veryhard
3. **インゲームID** — ユーザーが指定しているか（なければ命名規則に従い提案）
4. **使用する敵キャラ** — `mst_enemy_character_id`（既存IDを確認が必要）
5. **ボスの有無** — ボスキャラID、ボスの色属性
6. **コマ効果** — 指定がなければ `None`（エフェクトなし）
7. **特別ルール** — チャレンジ/サベージの場合は SpeedAttack/NoContinue等

設計確認サマリーを作成し、ユーザーに承認を求めてからStep 1に進む。

---

### Step 1: 既存データの参照（DuckDB）

同種ステージの既存データを参照し、値の範囲・パターンを把握する。

```bash
# 同種ステージの参照例
duckdb -c "SELECT * FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE id LIKE '{種別}_%' LIMIT 5;"
```

詳細クエリは [duckdb-reference-queries.md](references/duckdb-reference-queries.md) を参照。

---

### Step 2: 設計書MD生成

DuckDBで参照した既存データとStep 0のヒアリング内容を基に、**設計書**（`design.md`）を生成してタイムスタンプ付きディレクトリに保存する。

**出力先:**
```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ}_{英語要約}/design.md
```

**設計書の内容フォーマット:**

```markdown
# インゲームマスタデータ設計書

## 基本情報
- 生成日時: {タイムスタンプ}
- コンテンツ種別: {event/dungeon/normal 等}
- ステージ種別: {event/challenge/dungeon_boss 等}

## インゲームID命名案
- インゲームID: `{提案ID}`
- 命名根拠: {命名規則に基づく根拠}

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | {N}件 | ボス{n}体、雑魚{n}体 |
| MstEnemyOutpost | 1件 | HP: {値} |
| MstPage | 1件 | |
| MstKomaLine | {N}件 | {N}行構成 |
| MstAutoPlayerSequence | {N}件 | {N}ウェーブ構成 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `content_type`: {値}
- `stage_type`: {値}
- `hp_coef`: {値}（根拠: {参照した既存データとの比較}）
- `attack_coef`: {値}
- その他重要パラメータ: {値}

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード |
|--------|------|----------------------|-----|-------|--------|
| {id} | ボス | {id} | {値} | {値} | {値} |
| {id} | 雑魚A | {id} | {値} | {値} | {値} |

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: {N}
- 初期配置（InitialSummon）: {キャラ概要}
- ウェーブ1: {出現タイミング・キャラ概要}
- ...

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: {N}行
- コマ効果: {None / 設定値}

## 参照した既存データ
- 参照ステージID: {id}（{参照クエリの結果サマリー}）

## 不確定事項・要確認事項
- {あれば記載。なければ「なし」}
```

---

### Step 3: ユーザー確認・承認

設計書を提示した後、以下を明示してユーザーに確認を求める。

```
設計書を生成しました（design.md）。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。CSV生成（Phase 2）に進みます。
修正がある場合は具体的にご指示ください。設計書を更新して再度ご確認いただきます。
```

- 修正依頼があれば設計書を更新して再確認する
- **ユーザーの承認が得られるまで Phase 2 に進んではならない**

---

## Phase 2: 生成フェーズ（ユーザー承認後に実行）

---

### Step 4: 生成テーブルと順序の確認

テーブル依存関係に従った生成順序を確認する（[table-creation-order.md](references/table-creation-order.md) 参照）。

**基本生成順序:**
1. MstEnemyStageParameter（他テーブルが参照する基礎データ）
2. MstEnemyOutpost（MstInGameが参照）
3. MstPage（MstInGameが参照）
4. MstKomaLine（MstPageに属する）
5. MstAutoPlayerSequence（MstEnemyStageParameterのIDを参照）
6. MstInGame（上記すべてを参照）
7. （オプション）MstInGameSpecialRule / MstInGameI18n

---

### Step 5: CSV生成（列ヘッダー厳守）

**必須手順**: 各テーブルを生成する前に、sheet_schemaのCSVファイルの3行目（ENABLE行）を読み取り、列順を厳守する。

```python
# 各テーブル生成前に実行
# Read tool で projects/glow-masterdata/sheet_schema/{テーブル名}.csv の3行目（ENABLE行）を確認する
# ※ 3行ヘッダー形式: 行1=memo, 行2=TABLE,テーブル名, 行3=ENABLE,カラム名,...
```

生成のポイント:
- 各テーブルの設定値は [stage-type-patterns.md](references/stage-type-patterns.md) を参照
- MstAutoPlayerSequenceの設計は [sequence-design-guide.md](references/sequence-design-guide.md) を参照
- MstEnemyStageParameterの値設定は [enemy-parameter-guide.md](references/enemy-parameter-guide.md) を参照
- MstKomaLine/MstPageの設計は [koma-layout-guide.md](references/koma-layout-guide.md) を参照

---

### Step 6: インゲームデータ総合検証（masterdata-ingame-verifier）

生成した全CSVを masterdata-ingame-verifier スキルで総合検証する。
以下の5フェーズをカバーする:

- **フェーズA: フォーマット検証** — 列順・型・enum値（sheet_schemaベースで確認）
- **フェーズB: ID整合性チェック** — 4テーブルのID連鎖・FK参照の確認
- **フェーズC: ゲームプレイ品質チェック** — コマ幅合計・シーケンス合理性・ステージ種別固有ルール
- **フェーズD: バランス比較** — 既存データの分布との比較（±5倍範囲外はWARNING）
- **フェーズE: アセットキー形式チェック** — 必須アセットキーの空欄確認

**結果の扱い:**
- CRITICAL エラー → 必ず修正してから次のステップへ進む
- WARNING → 内容を確認し、意図的であればそのまま進んでよい

---

### Step 7: ファイル保存とサマリー出力

全CSVをタイムスタンプ付きディレクトリに保存し、以下のサマリーを出力する:

```markdown
## 生成サマリー

### 生成ファイル一覧
- MstEnemyStageParameter.csv: {行数}行（{ボス数}ボス + {雑魚数}雑魚）
- MstEnemyOutpost.csv: 1行（id: {id}, HP: {hp}）
- MstPage.csv: 1行（id: {id}）
- MstKomaLine.csv: {行数}行
- MstAutoPlayerSequence.csv: {行数}行
- MstInGame.csv: 1行（id: {id}）

### 使用するID
- インゲームID: {id}
- 敵砦ID: {id}
- ページID: {id}

### 次のステップ
1. 詳細解説ドキュメントを生成する（Step 8）
2. MstQuest（新規クエストの場合）を作成する
3. projects/glow-masterdata/ に配置してDB投入する
```

---

### Step 8: 詳細解説ドキュメント生成（masterdata-ingame-detail-explainer）

生成したCSVを元に、インゲームの詳細解説ドキュメントを生成する。

**保存先**: タスクフォルダ直下（`domain/knowledge/` ではなく `{タスクフォルダ}/` 直下）

```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ}_{英語要約}/{INGAME_ID}.md
```

**実行手順:**

1. `masterdata-ingame-detail-explainer` スキルを呼び出す
2. 生成されたドキュメントの保存先が `domain/knowledge/masterdata/in-game/guides/` になっている場合は、タスクフォルダ直下に移動する

**ドキュメントに含まれる8セクション:**
1. 概要（インゲームID・BGM・背景・敵構成等の一覧表）
2. 関連テーブル設定（MstInGame / MstEnemyOutpost / MstPage+MstKomaLine）
3. 使用する敵パラメータ一覧（カラム解説 + 全パラメータ表 + 特性解説）
4. グループ構造の全体フロー（Mermaidフローチャート）
5. 全N行の詳細データ（グループ単位）
6. グループ切り替えまとめ
7. スコア体系
8. この設定から読み取れる設計パターン

---

## ガードレール（必ず守ること）

### 1. 列ヘッダーの順番厳守

各テーブルのCSVを生成する前に、**必ずRead toolでsheet_schemaのCSVファイルの3行目（ENABLE行）を読み取り**、その順番に従う。

```
# NG: 記憶に基づいて列順を決める
# NG: projects/glow-masterdata/*.csv（実データ）の1行目から列順を読む（sheet_schemaと差異がある場合があるため）
# OK: Read で projects/glow-masterdata/sheet_schema/MstInGame.csv の3行目（ENABLE行）を確認してから生成
```

### 2. IDの一貫性

```
MstInGame.id
= MstAutoPlayerSequence.sequence_set_id
= MstPage.id  （MstInGame.mst_page_idの参照先）
= MstEnemyOutpost.id  （MstInGame.mst_enemy_outpost_idの参照先）
```

### 3. FK参照の存在確認

`MstAutoPlayerSequence.action_value`（SummonEnemy時）に設定するIDが、同バッチ内の`MstEnemyStageParameter`に存在することを確認する。

### 4. ENABLE列

全テーブルで `e` を設定する。

### 5. 倍率の乗算

`MstInGame.*_coef × MstAutoPlayerSequence.*_coef` が最終倍率になることを意識してパラメータを設定する。

### 6. ユーザー質問は1回だけ

不足情報はまとめて一度の質問で確認する。確認した設計で問題ないかを承認してもらってから生成を開始する。

### 7. 設計書の承認なしにCSVを生成してはならない

Phase 2（CSV生成）は、必ずStep 3でユーザーの明示的な承認を得てから実行する。
設計書（design.md）を生成せずに直接CSVを生成することは禁止。

### 8. ボスの二重設定

ボスは以下の2箇所で設定することが多い:
- `MstInGame.boss_mst_enemy_stage_parameter_id` — ボスパラメータIDの参照
- `MstAutoPlayerSequence`の行 — InitialSummon + summon_position=1.7 でボスを砦付近に配置

---

## 主要な参照先

| パス | 用途 |
|-----|------|
| `domain/tasks/masterdata-entry/in-game-tables/インゲームマスタデータ設定方法.md` | 全テーブルの設定値・命名規則の一次情報 |
| `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` | シーケンス設計の詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` | 敵パラメータ詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstPage.md` | ページ設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstKomaLine.md` | コマライン設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` | 敵砦設計詳細 |
| `domain/knowledge/masterdata/in-game/guides/` | 実データに基づく解説例 |
| `projects/glow-masterdata/*.csv` | ヘッダー順の確認・既存データ参照（DuckDB） |

---

## リファレンス一覧

- [interview-questions.md](references/interview-questions.md) — Step 0 の質問フロー
- [ingame-content-type-patterns.md](references/ingame-content-type-patterns.md) — インゲームコンテンツ種別ごとの設定パターン
- [id-naming-rules.md](references/id-naming-rules.md) — ID命名規則の詳細
- [table-creation-order.md](references/table-creation-order.md) — テーブル生成順序と依存関係
- [stage-type-patterns.md](references/stage-type-patterns.md) — ステージ種別ごとの設定パターン
- [sequence-design-guide.md](references/sequence-design-guide.md) — MstAutoPlayerSequence設計ガイド
- [enemy-parameter-guide.md](references/enemy-parameter-guide.md) — MstEnemyStageParameter設計ガイド
- [koma-layout-guide.md](references/koma-layout-guide.md) — MstPage/MstKomaLine設計ガイド
- [duckdb-reference-queries.md](references/duckdb-reference-queries.md) — 類似データ参照用DuckDBクエリ集
