---
name: masterdata-ingame-creator
description: インゲーム設計テキストからインゲーム関連マスタデータCSVを作成するスキル。ユーザーが提供した設計思想（敵構成・ステージ種別・難易度イメージ）を分析し、MstEnemyStageParameter・MstAutoPlayerSequence・MstInGame等の必須テーブルを含む全CSVを段階的に生成します。「インゲームCSV作成」「インゲームマスタ作成」「バトルステージ作成」「敵出現設定」「新規ステージ設計」「インゲーム設計」などのキーワードで使用します。
---

# インゲームマスタデータ作成スキル

## 概要

インゲーム（バトル画面）を構成するマスタデータCSVを、設計思想テキストから正確に生成します。
テーブル間の依存関係を考慮した正しい順序で生成し、列ヘッダーは実際のCSVファイルから直接読み取って完全準拠します。

---

## 出力先

```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ秒まで}_{英語要約}/generated/*.csv
```

**例:**
```
domain/tasks/masterdata-entry/masterdata-ingame-creator/20260220_153042_event_kai1_battle/generated/
  ├── MstEnemyStageParameter.csv
  ├── MstEnemyOutpost.csv
  ├── MstPage.csv
  ├── MstKomaLine.csv
  ├── MstAutoPlayerSequence.csv
  ├── MstInGame.csv
  ├── MstStage.csv
  ├── MstStageEventSetting.csv
  └── MstStageEventReward.csv
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
| `MstStage.csv` | **必須** | ステージ設定（コスト・EXP・コイン等） |
| `MstStageEventSetting.csv` | **必須** | ステージのイベント設定（期間・クリア回数） |
| `MstStageEventReward.csv` | **必須** | ステージのドロップ報酬設定 |
| `MstInGameI18n.csv` | オプション | インゲームの多言語テキスト（説明文・ティップス） |
| `MstStageClearTimeReward.csv` | オプション | クリアタイム報酬（challenge/savage系のみ） |
| `MstInGameSpecialRule.csv` | オプション | 特別ルール（コンティニュー禁止等） |

---

## 7ステップワークフロー

### Step 0: 設計思想の分析・不足情報の質問（1回のみ）

ユーザーの入力テキストを分析し、以下の情報が揃っているか確認する。
**不足している場合は、まとめて1回だけ質問する。2回に分けない。**

確認する6項目（[interview-questions.md](references/interview-questions.md) 参照）:

1. **ステージ種別** — event/challenge/savage/raid/daily/normal/hard/veryhard
2. **インゲームID** — ユーザーが指定しているか（なければ命名規則に従い提案）
3. **使用する敵キャラ** — `mst_enemy_character_id`（既存IDを確認が必要）
4. **ボスの有無** — ボスキャラID、ボスの色属性
5. **コマ効果** — 指定がなければ `None`（エフェクトなし）
6. **特別ルール** — チャレンジ/サベージの場合は SpeedAttack/NoContinue等

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

### Step 2: 生成テーブルと順序の確認

テーブル依存関係に従った生成順序を確認する（[table-creation-order.md](references/table-creation-order.md) 参照）。

**基本生成順序:**
1. MstEnemyStageParameter（他テーブルが参照する基礎データ）
2. MstEnemyOutpost（MstInGameが参照）
3. MstPage（MstInGameが参照）
4. MstKomaLine（MstPageに属する）
5. MstAutoPlayerSequence（MstEnemyStageParameterのIDを参照）
6. MstInGame（上記すべてを参照）
7. MstStage（MstInGameを参照）
8. MstStageEventSetting（MstStageを参照）
9. MstStageEventReward（MstStageを参照）
10. （オプション）MstStageClearTimeReward / MstInGameSpecialRule / MstInGameI18n

---

### Step 3: CSV生成（列ヘッダー厳守）

**必須手順**: 各テーブルを生成する前に、実際のCSVファイルの1行目を読み取り、列順を厳守する。

```python
# 各テーブル生成前に実行
# Read tool で projects/glow-masterdata/{テーブル名}.csv の1行目を確認する
```

生成のポイント:
- 各テーブルの設定値は [stage-type-patterns.md](references/stage-type-patterns.md) を参照
- MstAutoPlayerSequenceの設計は [sequence-design-guide.md](references/sequence-design-guide.md) を参照
- MstEnemyStageParameterの値設定は [enemy-parameter-guide.md](references/enemy-parameter-guide.md) を参照
- MstKomaLine/MstPageの設計は [koma-layout-guide.md](references/koma-layout-guide.md) を参照

---

### Step 4: CSVバリデーション（masterdata-csv-validator）

生成した全CSVを検証する。

```bash
for csv in domain/tasks/masterdata-entry/masterdata-ingame-creator/{フォルダ}/generated/*.csv; do
  python .claude/skills/masterdata-csv-validator/scripts/validate_all.py --csv "$csv"
done
```

エラーがあれば修正してから次のステップへ進む。

---

### Step 5: ID整合性チェック（4テーブルのID連鎖確認）

以下のID連鎖が正しいことを確認する:

```
MstInGame.id
  = MstAutoPlayerSequence.sequence_set_id
  = MstPage.id（MstInGame.mst_page_idが参照）
  = MstEnemyOutpost.id（MstInGame.mst_enemy_outpost_idが参照）
  = MstStage.mst_in_game_id
```

**FK参照チェック:**
- `MstAutoPlayerSequence.action_value`（action_type=SummonEnemy時）→ `MstEnemyStageParameter.id` が存在するか
- `MstInGame.boss_mst_enemy_stage_parameter_id` → `MstEnemyStageParameter.id` が存在するか

---

### Step 6: ファイル保存とサマリー出力

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
- MstStage.csv: {行数}行
- MstStageEventSetting.csv: {行数}行
- MstStageEventReward.csv: {行数}行

### 使用するID
- インゲームID: {id}
- 敵砦ID: {id}
- ページID: {id}

### 次のステップ
1. MstQuest（新規クエストの場合）を作成する
2. projects/glow-masterdata/ に配置してDB投入する
```

---

## ガードレール（必ず守ること）

### 1. 列ヘッダーの順番厳守

各テーブルのCSVを生成する前に、**必ずRead toolで実際のCSVファイルの1行目を読み取り**、その順番に従う。

```
# NG: 記憶に基づいて列順を決める
# OK: Read で projects/glow-masterdata/MstInGame.csv の先頭を確認してから生成
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

### 7. ボスの二重設定

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
- [id-naming-rules.md](references/id-naming-rules.md) — ID命名規則の詳細
- [table-creation-order.md](references/table-creation-order.md) — テーブル生成順序と依存関係
- [stage-type-patterns.md](references/stage-type-patterns.md) — ステージ種別ごとの設定パターン
- [sequence-design-guide.md](references/sequence-design-guide.md) — MstAutoPlayerSequence設計ガイド
- [enemy-parameter-guide.md](references/enemy-parameter-guide.md) — MstEnemyStageParameter設計ガイド
- [koma-layout-guide.md](references/koma-layout-guide.md) — MstPage/MstKomaLine設計ガイド
- [duckdb-reference-queries.md](references/duckdb-reference-queries.md) — 類似データ参照用DuckDBクエリ集
