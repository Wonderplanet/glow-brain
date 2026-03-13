---
name: masterdata-ingame-analyzer
description: GLOWインゲームマスタデータの分析・レポート生成スキル。指定したコンテンツ（メインクエスト・イベント・VD・降臨バトル等）や難易度を対象に、`projects/glow-masterdata/` のCSVをDuckDBで分析し、結果をMDファイルにまとめます。「インゲーム分析」「マスタデータ分析」「敵設定分析」「ステージ分析」「バランス分析」「インゲーム調査」などのキーワードで使用します。引数には分析したいコンテンツ・難易度・観点と、オプションで出力先パスを指定します。
---

# GLOWインゲームマスタデータ分析スキル

引数で指定された内容に基づきインゲームマスタデータを分析し、MDレポートとして保存します。

**対象テーブル**: MstInGame / MstAutoPlayerSequence / MstEnemyStageParameter / MstEnemyOutpost / MstPage / MstKomaLine / MstAttack / MstAttackElement

**分析元データ**: `projects/glow-masterdata/*.csv`（過去リリース済みデータ）

---

## 引数

### 書式

```
{分析対象} [{分析観点}] [出力先: {パス}]
```

### 引数一覧

| 引数 | 必須 | 説明 | 例 |
|------|------|------|----|
| **分析対象** | ✅ | コンテンツ種別・難易度・キャラ等を自由記述 | `VDのNormalブロック`, `メインクエストHard難易度`, `降臨バトル` |
| **分析観点** | ❌ | 何を分析するかを指定。省略時はデータ全体の傾向を分析 | `敵HP設計の傾向`, `出現パターンの構造`, `コマエフェクト使用状況` |
| **出力先** | ❌ | MDファイルの保存先パス。省略時はカレントディレクトリに `ingame_analysis_{日付}.md` | `出力先: specs/analysis.md` |

### 呼び出し例

```
# 最小構成（分析対象のみ）
VDのNormalブロックを分析して

# 観点を指定
メインクエストHard難易度の敵HP・攻撃力倍率の傾向を分析して

# 特定キャラに絞る
降臨バトルのkaiキャラ関連ステージを敵出現パターン視点で分析して

# 出力先を指定
VDブロック全体のバランス分析 出力先: specs/vd_balance_analysis.md

# 複数観点
メインクエストNormal難易度について、コマエフェクトの種類と使用頻度・敵ステータス分布を分析して 出力先: specs/normal_ingame_analysis.md
```

---

## 分析ワークフロー

### Step 1: 引数を解釈して分析範囲を特定

引数から以下を読み取る:
- **分析対象コンテンツ**: 例 "メインクエストのNormal難易度", "限界チャレンジVD", "降臨バトル"
- **分析の観点・目的**: 例 "敵HP設計の傾向", "敵出現パターンの構造", "コマエフェクト使用状況"
- **出力先パス** (オプション): 指定がなければカレントディレクトリに `ingame_analysis_{日付}.md` として保存

コンテンツ別の絞り込み方法は [references/content-filter-guide.md](references/content-filter-guide.md) を参照。

### Step 2: masterdata-explorerを使いデータを絞り込む

masterdata-explorerスキルのDuckDBパターンを使い、対象データを抽出する。

```bash
# カラム名確認（必ずクエリ前に実行）
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_in_games
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_auto_player_sequences

# DuckDB起動
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# 例: VDブロックのMstInGameを抽出
SELECT *
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%' AND ENABLE = 'e'
LIMIT 20;
```

### Step 3: 分析クエリを実行

目的に応じて複数のクエリを組み合わせて分析する。テーブルリレーションと重要カラムは [references/table-reference.md](references/table-reference.md) を参照。

**よく使う分析パターン**:

```sql
-- パターンA: ステータス倍率の統計
SELECT
    ROUND(AVG(normal_enemy_hp_coef), 2) as avg_hp_coef,
    ROUND(AVG(normal_enemy_attack_coef), 2) as avg_atk_coef,
    MIN(normal_enemy_hp_coef) as min_hp,
    MAX(normal_enemy_hp_coef) as max_hp
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%' AND ENABLE = 'e';

-- パターンB: シーケンス行数の分布
SELECT
    sequence_set_id,
    COUNT(*) as row_count,
    COUNT(CASE WHEN action_type = 'SummonEnemy' THEN 1 END) as summon_rows,
    COUNT(CASE WHEN action_type = 'SwitchSequenceGroup' THEN 1 END) as group_changes
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'vd_%' AND ENABLE = 'e'
GROUP BY sequence_set_id
ORDER BY row_count DESC;

-- パターンC: 敵HPの分布（MstEnemyStageParameter × シーケンス倍率）
SELECT
    s.sequence_set_id,
    p.character_unit_kind,
    p.hp as base_hp,
    CAST(s.enemy_hp_coef AS FLOAT) as seq_coef,
    ROUND(p.hp * CAST(s.enemy_hp_coef AS FLOAT), 0) as final_hp
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') p
    ON s.action_value = p.id
WHERE s.action_type = 'SummonEnemy'
    AND s.sequence_set_id LIKE 'vd_%'
    AND s.ENABLE = 'e' AND p.ENABLE = 'e'
ORDER BY final_hp DESC;
```

### Step 4: MDレポートを作成して保存

分析結果を以下の構成でMDファイルに出力する:

```markdown
# {コンテンツ名} インゲームマスタデータ分析レポート

**分析日**: {日付}
**分析対象**: {テーブル一覧}
**絞り込み条件**: {WHERE句の内容}

---

## 1. 分析サマリー

- 対象レコード数: {件数}
- 分析の主要な発見: {3〜5点のポイント}

## 2. {観点1}の分析

### 統計情報
| 指標 | 値 |
|------|-----|
| ... | ... |

### 考察
...

## 3. {観点2}の分析
...

## 4. 特筆すべきパターン・発見

- **パターンA**: ...
- **パターンB**: ...

## 5. 次のアクションへの示唆

- ...
```

---

## コンテンツ別の絞り込み早見表

| コンテンツ | 絞り込みキー | 主要テーブル |
|-----------|------------|------------|
| メインクエストNormal | `MstInGame.id LIKE 'normal_%'` | MstInGame, MstAutoPlayerSequence |
| メインクエストHard | `MstInGame.id LIKE 'hard_%'` | MstInGame, MstAutoPlayerSequence |
| 限界チャレンジ(VD)Normal | `MstInGame.id LIKE 'vd_%normal%'` | MstInGame, MstAutoPlayerSequence, MstEnemyStageParameter |
| 限界チャレンジ(VD)Boss | `MstInGame.id LIKE 'vd_%boss%'` | MstInGame, MstAutoPlayerSequence, MstEnemyStageParameter |
| イベントチャレンジ | `MstInGame.id LIKE 'event_%challenge%'` | MstInGame, MstAutoPlayerSequence |
| 降臨バトル | `MstInGame.id LIKE 'raid_%'` | MstInGame, MstAutoPlayerSequence |
| 降臨バトルボスパラメータ | `MstEnemyStageParameter.character_unit_kind = 'AdventBattleBoss'` | MstEnemyStageParameter, MstAutoPlayerSequence |
| ランクマッチ（PvP） | `MstInGame.id LIKE 'pvp_%'` | MstInGame |

詳細なクエリ例は [references/content-filter-guide.md](references/content-filter-guide.md) を参照。

---

## テーブルリレーション（概要）

詳細は [references/table-reference.md](references/table-reference.md) を参照。

```
MstInGame（バトルステージ）
  ├─ mst_page_id          → MstPage.id
  │                             └─(1:N) MstKomaLine.mst_page_id
  ├─ mst_enemy_outpost_id → MstEnemyOutpost.id
  ├─ boss_mst_enemy_stage_parameter_id → MstEnemyStageParameter.id
  └─ mst_auto_player_sequence_set_id   → MstAutoPlayerSequence.sequence_set_id
                                              └─ action_value → MstEnemyStageParameter.id（SummonEnemy時）

MstEnemyStageParameter.id
  └─ 暗黙リレーション: MstAttack.mst_unit_id（クライアントで結合）
       └─(1:N) MstAttackElement.mst_attack_id
```

---

## 注意事項

- **CSVパス**: `projects/glow-masterdata/` 直下のファイルが分析対象
- **ENABLE フィルタ**: 必ず `WHERE ENABLE = 'e'` で有効データのみ絞り込む
- **nullstr='__NULL__'**: DuckDB読み込み時は常に `nullstr='__NULL__'` を指定する
- **カラム名確認**: クエリ前に必ず `search_schema.sh columns` でカラム名を確認する
- **MstAttack連携**: `MstEnemyStageParameter.id = MstAttack.mst_unit_id`（DB外部キーなし、クライアント結合）
- **VD敵にMstAttackなし**: `_vd_` を含むIDに対応するMstAttackレコードは存在しない（設計上）
