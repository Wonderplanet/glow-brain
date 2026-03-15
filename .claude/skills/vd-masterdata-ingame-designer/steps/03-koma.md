# Step 03: コマ設計

VDインゲーム設計書（design.md）の **`### コマ設計`** セクションを生成・更新する手順。

- **担当セクション**: `## レベルデザイン > ### コマ設計`
- 生成物: Mermaid block-beta 図 + 行別テーブル

---

## ルール（VD固定）

| ブロック種別 | 行数 | 各行のコマ数 |
|------------|------|------------|
| normal | **3行固定**（row=1,2,3） | 各行独立で1〜4コマをランダム選択 |
| boss | **1行固定**（row=1） | 1〜4コマ |

---

## Step 0: 準備・ドキュメント読み込み

以下を読み込む。

**参照ファイル（必須）**:
- `.claude/skills/vd-masterdata-ingame-designer/references/koma-background-offset.md` — 推奨back_ground_offset値
- `domain/knowledge/masterdata/table-docs/MstKomaLine.md` — テーブル定義

**コマアセットキーの取得（DuckDBクエリ）**:

作品IDに合った `koma1_asset_key` と `koma1_back_ground_offset` を以下のDuckDBクエリで取得する:

```sql
SELECT DISTINCT koma1_asset_key, koma1_back_ground_offset
FROM read_csv_auto('projects/glow-masterdata/MstKomaLine.csv')
WHERE id LIKE '%_{作品ID}_%'
LIMIT 5;
```

## Step 1: コマレイアウト設計

#### コマ数・幅パターンの選択

以下のパターン対応表から各行のパターンを選択する:

| パターン番号 | コマ数 | 幅パターン（koma1〜4） | 説明 |
|-----------|------|-------------------|------|
| 1 | 1 | 1.0 | 1コマフル幅 |
| 2 | 2 | 0.6, 0.4 | 左広い |
| 3 | 2 | 0.4, 0.6 | 右広い |
| 4 | 2 | 0.75, 0.25 | 左がかなり広い |
| 5 | 2 | 0.25, 0.75 | 右がかなり広い |
| 6 | 2 | 0.5, 0.5 | 2等分（完全均等）|
| 7 | 3 | 0.33, 0.34, 0.33 | 3等分 |
| 8 | 3 | 0.5, 0.25, 0.25 | 左広い・右2等分 |
| 9 | 3 | 0.25, 0.5, 0.25 | 中央広い |
| 10 | 3 | 0.25, 0.25, 0.5 | 右広い・左2等分 |
| 11 | 3 | 0.4, 0.2, 0.4 | 左右広い・中央狭い |
| 12 | 4 | 0.25, 0.25, 0.25, 0.25 | 4等分（完全均等）|

**選択の多様性確保**:
- normalブロック（3行）: 3行それぞれ異なるパターンを選択することを推奨
- 対抗キャラ能力に合わせたコマ効果（`koma1_effect_type`）を1〜2行に設定

#### コマ効果の選択

対抗キャラ能力（引数）に応じて以下のいずれかを選択:
- `PoisonDamageCut` → `koma_effect_type=Poison`
- `BurnDamageCut` → `koma_effect_type=Burn`
- `SlipDamageKomaBlock` → `koma_effect_type=SlipDamage`
- `AttackPowerDownKomaBlock` → `koma_effect_type=AttackPowerDown`
- `GustKomaBlock` → `koma_effect_type=Gust`

対抗キャラ能力が渡されない場合は `None`（効果なし）とする。

#### コマアセットキーの決定

Step 0 の DuckDB クエリで取得した `koma1_asset_key` を使用する。
`koma-background-offset.md` を参照して `koma1_back_ground_offset` の推奨値を確認する。

## Step 2: 設計セクション生成

以下のフォーマットでMarkdownを生成する。

````markdown
### コマ設計

```mermaid
block-beta
  columns {N}
  A["row=1 / koma1<br/>幅={width}<br/>effect: {effect}"]:{span} B["row=1 / koma2<br/>幅={width}<br/>effect: None"]:{span}
  ...（各行）
```
※ columns は1つのみ。各行のスパン合計 = {N} になること。

| row | height | 選択パターン | コマ数 | 各幅 | koma1_asset_key | koma1_effect_type | 幅合計 |
|-----|--------|------------|-------|------|----------------|------------------|--------|
| 1 | 0.33 | パターン{N} | {コマ数} | {各幅} | {asset_key} | {effect} | 1.0 |
| 2 | 0.33 | パターン{N} | {コマ数} | {各幅} | {asset_key} | None | 1.0 |
| 3 | 0.34 | パターン{N} | {コマ数} | {各幅} | {asset_key} | None | 1.0 |
````

**height の設定**:
- normalブロック（3行）: row=1 → `0.33`、row=2 → `0.33`、row=3 → `0.34`
- bossブロック（1行）: row=1 → `1.0`

**Mermaid block-beta図の作成ルール**:
- `columns N` は1つだけ宣言する（複数不可）
- N は全行のコマ幅をスパン整数で表現できる値（通常は 4）
- 各ブロックのスパン = `N × コマ幅`（幅0.25→`:1`、幅0.50→`:2`、幅0.33→`:1`等）
- 各行のスパン合計が N になることを確認する

## Step 3: 確認・更新

`--batch` フラグがない場合:
```
コマ設計を生成しました。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

承認後（または `--batch` 時）、design.md の該当セクションを更新する。

---

## ガードレール

1. **行数はブロック種別で固定**: normal=3行、boss=1行（変更不可）
2. **`koma1_asset_key` は DuckDB クエリで取得**: `projects/glow-masterdata/MstKomaLine.csv` から作品ID別に取得した値のみ使用
3. **`koma1_back_ground_offset` は koma-background-offset.md を参照**: 推奨値を使用
4. **Mermaid columns は1つのみ宣言**: 複数の `columns` 宣言は行崩れの原因
5. **各行のスパン合計が N になること**: Mermaid図の整合性を確認

---

## リファレンス

- `projects/glow-masterdata/MstKomaLine.csv` — DuckDBクエリでコマアセットキーを取得（`WHERE id LIKE '%_{作品ID}_%'`）
- `.claude/skills/vd-masterdata-ingame-designer/references/koma-background-offset.md` — 推奨back_ground_offset値
- `domain/knowledge/masterdata/table-docs/MstKomaLine.md` — テーブル定義
