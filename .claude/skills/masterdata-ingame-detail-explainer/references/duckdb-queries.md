# DuckDBクエリ集（コピペ用）

すべてのクエリは glow-brain リポジトリのルートディレクトリから実行する。

---

## Step 1: MstInGame 基本情報

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id = '{INGAME_ID}';
"
```

---

## Step 2-1: MstEnemyOutpost

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstEnemyOutpost.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id = '{INGAME_ID}';
"
```

---

## Step 2-2: MstInGameI18n（日本語テキスト）

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstInGameI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE mst_in_game_id = '{INGAME_ID}'
  AND language = 'ja';
"
```

---

## Step 2-3: MstPage + MstKomaLine（コマフィールド）

```bash
duckdb -c "
SELECT
  p.id AS page_id,
  p.row AS row_num,
  p.height,
  p.mst_koma_line_layout_id AS layout,
  k.koma_index,
  k.mst_koma_id,
  k.width,
  k.bg_offset,
  k.koma_effect,
  k.koma_effect_param1,
  k.koma_effect_param2,
  k.koma_effect_target
FROM read_csv('projects/glow-masterdata/MstPage.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') p
LEFT JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') k
  ON p.id = k.mst_page_id
WHERE p.id = '{INGAME_ID}'
ORDER BY p.row, k.koma_index;
"
```

---

## Step 2-4: MstAutoPlayerSequence（全行取得）

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
ORDER BY
  CASE WHEN sequence_group_id IS NULL OR sequence_group_id = '' THEN '0_default' ELSE sequence_group_id END,
  sequence_element_id;
"
```

> **グループ順序の注意**: `sequence_group_id` は文字列（'w1', 'w2', ...）のため、辞書順ソートになる。
> 必要に応じて ORDER BY を調整すること。

---

## Step 2-5: MstEnemyStageParameter（使用する敵のみ）

Step 2-4 の結果から `action_type = 'SummonEnemy'` の `action_value` を収集し、IN句に入れる。

```bash
# まずaction_valueの一覧を確認
duckdb -c "
SELECT DISTINCT action_value
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
  AND action_type = 'SummonEnemy';
"

# 次にパラメータを取得（IN句に収集したIDを入れる）
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id IN (
  SELECT DISTINCT action_value
  FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
  WHERE sequence_set_id = '{INGAME_ID}'
    AND action_type = 'SummonEnemy'
)
ORDER BY sort_order;
"
```

---

## Step 2-6: MstEnemyCharacterI18n（敵キャラ日本語名・必須）

MstEnemyStageParameterの`mst_enemy_character_id`からI18n日本語名を一括取得する。
取得結果で `{キャラID: 日本語名}` マッピングを作成し、以降のドキュメント生成で必ず使用する。

```bash
duckdb -c "
SELECT
  esp.id AS stage_param_id,
  esp.mst_enemy_character_id,
  i18n.name AS enemy_name
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') esp
LEFT JOIN read_csv('projects/glow-masterdata/MstEnemyCharacterI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i18n
  ON esp.mst_enemy_character_id = i18n.mst_enemy_character_id
  AND i18n.language = 'ja'
WHERE esp.id IN (
  SELECT DISTINCT action_value
  FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
  WHERE sequence_set_id = '{INGAME_ID}'
    AND action_type = 'SummonEnemy'
);
"
```

---

## Step 2-7: MstAbilityI18n（アビリティ日本語説明・アビリティがある場合は必須）

MstEnemyStageParameterの`mst_unit_ability_id1`からI18n日本語説明を一括取得する。
取得結果で `{アビリティID: 日本語説明}` マッピングを作成し、セクション3のabilityカラムに記載する。

```bash
duckdb -c "
SELECT
  a.id AS ability_id,
  i18n.description AS ability_description
FROM read_csv('projects/glow-masterdata/MstAbility.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') a
LEFT JOIN read_csv('projects/glow-masterdata/MstAbilityI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i18n
  ON a.id = i18n.mst_ability_id
  AND i18n.language = 'ja'
WHERE a.id IN (
  SELECT DISTINCT esp.mst_unit_ability_id1
  FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') esp
  WHERE esp.id IN (
    SELECT DISTINCT action_value
    FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
    WHERE sequence_set_id = '{INGAME_ID}'
      AND action_type = 'SummonEnemy'
  )
  AND esp.mst_unit_ability_id1 IS NOT NULL
  AND esp.mst_unit_ability_id1 != ''
);
"
```

---

## 補助クエリ: グループ構造サマリー

グループ切り替え行だけを抜き出してフロー把握に使う。

```bash
duckdb -c "
SELECT
  sequence_group_id,
  sequence_element_id,
  condition_type,
  condition_value,
  action_type,
  action_value,
  action_delay
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
  AND action_type = 'SwitchSequenceGroup'
ORDER BY
  CASE WHEN sequence_group_id IS NULL OR sequence_group_id = '' THEN '0_default' ELSE sequence_group_id END,
  sequence_element_id;
"
```

---

## 補助クエリ: 特定グループの詳細データ

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
  AND sequence_group_id = '{GROUP_ID}'
ORDER BY sequence_element_id;
"
```

デフォルトグループ（sequence_group_id が空）の場合:

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
  AND (sequence_group_id IS NULL OR sequence_group_id = '')
ORDER BY sequence_element_id;
"
```

---

## エラーパターンと対処

| エラー | 原因 | 対処 |
|-------|------|------|
| `No files found that match the pattern` | CSVファイルが存在しない or パスが誤り | glow-brainルートから実行しているか確認 |
| `Table does not have a column named "X"` | カラム名の推測ミス | `AUTO_DETECT=TRUE` 後の実際カラム名を `DESCRIBE` で確認 |
| 結果が0行 | IDの指定ミス or データ未投入 | 正確なIDを確認してから再実行 |
| ソート順がおかしい | 文字列ソートの影響 | `CASE WHEN` でカスタムソートキーを指定 |

---

## DuckDB設定ファイル

```bash
# 既存の .duckdbrc を使う場合
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

または直接 `-c` オプションでクエリを渡す（推奨）:

```bash
duckdb -c "SELECT ... FROM read_csv(...)"
```
