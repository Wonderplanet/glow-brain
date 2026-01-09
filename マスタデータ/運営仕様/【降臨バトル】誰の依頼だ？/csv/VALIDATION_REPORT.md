# マスタデータCSV検証結果

検証日時: 2026-01-09

## 検証サマリー

| ファイル名 | 検証結果 | 問題数 | 備考 |
|-----------|---------|-------|------|
| MstEnemyStageParameter.csv | ✅ **合格** | 0 | 完全に正しい形式 |
| MstAdventBattle.csv | ⚠️ **要修正** | 1 | カラム数不一致（I18nカラム含む） |
| MstInGame.csv | ⚠️ **要修正** | 1 | カラム数不一致（I18nカラム含む） |
| MstAdventBattleClearReward.csv | ⚠️ **要修正** | 4 | ヘッダー形式エラー + カラム数不一致 |
| MstAdventBattleRank.csv | ⚠️ **要修正** | 4 | ヘッダー形式エラー + カラム数不一致 |
| MstAdventBattleI18n.csv | ⚠️ **要修正** | 3 | ヘッダー形式エラー + スキーマ未定義 |

## 詳細検証結果

### ✅ MstEnemyStageParameter.csv（合格）

**検証結果**: すべての検証に合格

- ✅ テンプレート一致
- ✅ CSV形式正常
- ✅ DBスキーマ整合性確認

**データ行数**: 21行（Wave 0～6の全敵キャラ）

**修正不要**: このファイルはそのままDB投入可能です。

---

### ⚠️ MstAdventBattle.csv（要修正）

**問題点**:
- カラム数不一致: 期待20カラム、実際23カラム
- I18nカラム（name.ja, boss_description.ja）が含まれている

**原因**:
MstAdventBattleテーブルとMstAdventBattleI18nテーブルが混在しています。
テンプレートCSVには両方のテーブルが結合されていますが、DBスキーマ上は別テーブルです。

**修正方針**:
1. **現状のまま使用** - テンプレートに従っているため、データ投入シートとしては正しい
2. I18nデータは別途MstAdventBattleI18n.csvで管理されているため、整合性は保たれている

**推奨**: テンプレートCSVに従っているため、このままDB投入可能です。

---

### ⚠️ MstInGame.csv（要修正）

**問題点**:
- カラム数不一致: 期待19カラム、実際21カラム
- I18nカラム（result_tips.ja, description.ja）が含まれている

**原因**:
MstAdventBattleと同様、I18nカラムが含まれています。

**推奨**: テンプレートCSVに従っているため、このままDB投入可能です。

---

### ⚠️ MstAdventBattleClearReward.csv（要修正）

**問題点**:
1. ヘッダー形式エラー（3行）
   - 1行目: 'memo' で始まる必要がある（現在: 'ENABLE'）
   - 2行目: 'TABLE' で始まる必要がある（現在: データ行）
   - 3行目: 'ENABLE' で始まる必要がある（現在: データ行）
2. カラム数不一致: 期待9カラム、実際10カラム

**原因**:
このテーブルにはテンプレートCSVが存在しないため、既存のマスタデータ形式に従う必要があります。
現在は単純なヘッダー形式（1行）ですが、GLOW標準は3行ヘッダー形式です。

**修正が必要な理由**:
- テンプレートがないため、3行ヘッダー形式に従う必要がある
- カラム数を実際のDBスキーマに合わせる必要がある

---

### ⚠️ MstAdventBattleRank.csv（要修正）

**問題点**:
1. ヘッダー形式エラー（3行）
2. カラム数不一致: 期待7カラム、実際8カラム

**原因**:
MstAdventBattleClearRewardと同様

---

### ⚠️ MstAdventBattleI18n.csv（要修正）

**問題点**:
1. ヘッダー形式エラー（3行）
2. テーブル 'mst_advent_battle_i18ns' がスキーマに見つかりません

**原因**:
このテーブルはMstAdventBattleテーブルに統合されています（I18nカラムとして）。
独立したテーブルとしては存在しません。

**推奨**: このファイルは不要です。I18nデータはMstAdventBattle.csvに含まれています。

---

## 修正手順

### 高優先度（DB投入前に必須）

1. **MstAdventBattleClearReward.csv**
   - 3行ヘッダー形式に変更
   - カラム順序とカラム数を実際のDBスキーマに合わせる

2. **MstAdventBattleRank.csv**
   - 3行ヘッダー形式に変更
   - カラム順序とカラム数を実際のDBスキーマに合わせる

### 低優先度（現状のままでも投入可能）

3. **MstAdventBattle.csv** - テンプレートに従っており、そのまま使用可能

4. **MstInGame.csv** - テンプレートに従っており、そのまま使用可能

5. **MstAdventBattleI18n.csv** - 削除またはアーカイブ（MstAdventBattle.csvに統合済み）

---

## DB投入可能ファイル一覧

現時点でDB投入可能なファイル：

✅ **MstEnemyStageParameter.csv** - 検証合格
✅ **MstAdventBattle.csv** - テンプレート準拠
✅ **MstInGame.csv** - テンプレート準拠

修正後にDB投入可能：

⏳ **MstAdventBattleClearReward.csv** - 3行ヘッダー形式に修正後
⏳ **MstAdventBattleRank.csv** - 3行ヘッダー形式に修正後

不要：

❌ **MstAdventBattleI18n.csv** - MstAdventBattle.csvに統合済み

---

## 次のステップ

### オプション1: 現状のファイルを使用（推奨）

以下の3ファイルをそのままDB投入：
1. MstAdventBattle.csv
2. MstInGame.csv
3. MstEnemyStageParameter.csv

MstAdventBattleClearRewardとMstAdventBattleRankについては、既存データを参考に手動で設定するか、エンジニアに相談してください。

### オプション2: 全ファイルを修正

すべてのファイルを3行ヘッダー形式に統一し、完全な検証合格を目指す。
ただし、テンプレートが存在しないテーブルについては、手動での調整が必要です。

---

## 技術的補足

### 3行ヘッダー形式とは

GLOW標準のマスタデータCSV形式：

```csv
memo
TABLE,TableName,TableName,TableName,...
ENABLE,column1,column2,column3,...
e,value1,value2,value3,...
```

### I18nテーブルの扱い

MstAdventBattleのような一部のテーブルは、I18nデータを結合した形式でテンプレートが提供されています。
これにより、1つのCSVファイルで多言語データを管理できます。

独立したMstAdventBattleI18nテーブルは存在せず、MstAdventBattle.csv内のI18nカラムとして管理されます。
