# マスタデータCSV検証結果

検証日時: 2026-01-09
最終更新: 2026-01-09（修正完了）

## ✅ 最終検証サマリー

| ファイル名 | 検証結果 | 状態 | 備考 |
|-----------|---------|------|------|
| MstEnemyStageParameter.csv | ✅ **合格** | DB投入可能 | 21レコード、テンプレート準拠 |
| MstAdventBattle.csv | ✅ **合格** | DB投入可能 | テンプレート準拠（I18nカラム含む） |
| MstInGame.csv | ✅ **合格** | DB投入可能 | テンプレート準拠（I18nカラム含む） |
| MstAdventBattleClearReward.csv | ✅ **合格** | DB投入可能 | 5レコード、既存形式に準拠 |
| MstAdventBattleRank.csv | ✅ **合格** | DB投入可能 | 4レコード、既存形式に準拠 |
| ~~MstAdventBattleI18n.csv~~ | ❌ **削除済** | 不要 | MstAdventBattle.csvに統合済み |

**全ファイルDB投入可能**: 5ファイル（合計32レコード）

---

## 詳細検証結果

### ✅ MstEnemyStageParameter.csv

**検証結果**: すべての検証に合格

- ✅ テンプレート一致
- ✅ CSV形式正常
- ✅ DBスキーマ整合性確認

**データ行数**: 21行（Wave 0～6の全敵キャラ）

**形式**: 3行ヘッダー形式（テンプレート準拠）

---

### ✅ MstAdventBattle.csv

**検証結果**: テンプレート準拠

**特徴**:
- I18nカラム（name.ja, boss_description.ja）を含む
- テンプレートCSVに従った結合形式
- 1ファイルで多言語データを管理

**形式**: 3行ヘッダー形式（テンプレート準拠）

**推奨**: このままDB投入可能

---

### ✅ MstInGame.csv

**検証結果**: テンプレート準拠

**特徴**:
- I18nカラム（result_tips.ja, description.ja）を含む
- テンプレートCSVに従った結合形式

**形式**: 3行ヘッダー形式（テンプレート準拠）

**推奨**: このままDB投入可能

---

### ✅ MstAdventBattleClearReward.csv

**検証結果**: 既存形式に準拠

**形式**: 1行ヘッダー形式（テンプレート未定義のため、既存データの形式に従う）

**カラム**: 
```
ENABLE,id,mst_advent_battle_id,reward_category,resource_type,resource_id,resource_amount,percentage,sort_order,release_key
```

**データ行数**: 5行（カラーメモリー5種類）

**検証内容**:
- ✅ カラム順序が既存データと一致
- ✅ カラム数が正しい（10カラム = ENABLE + 9カラム）
- ✅ データ型が正しい

**推奨**: このままDB投入可能

---

### ✅ MstAdventBattleRank.csv

**検証結果**: 既存形式に準拠

**形式**: 1行ヘッダー形式（テンプレート未定義のため、既存データの形式に従う）

**カラム**:
```
ENABLE,id,mst_advent_battle_id,rank_type,rank_level,required_lower_score,asset_key,release_key
```

**データ行数**: 4行（Bronze 4段階）

**検証内容**:
- ✅ カラム順序が既存データと一致
- ✅ カラム数が正しい（8カラム = ENABLE + 7カラム）
- ✅ データ型が正しい

**推奨**: このままDB投入可能

---

### ❌ MstAdventBattleI18n.csv（削除済）

**状態**: ファイル削除

**理由**:
- このテーブルは独立して存在しない
- I18nデータはMstAdventBattle.csv内のI18nカラムとして管理される
- 重複データを避けるため削除

---

## 検証の詳細説明

### ヘッダー形式について

GLOWマスタデータには2つの形式があります：

**1. 3行ヘッダー形式**（テンプレートが存在する場合）
```csv
memo
TABLE,TableName,TableName,...
ENABLE,column1,column2,...
e,value1,value2,...
```

**2. 1行ヘッダー形式**（テンプレートが存在しない場合）
```csv
ENABLE,column1,column2,...
e,value1,value2,...
```

本プロジェクトの各ファイル：
- MstAdventBattle.csv: 3行ヘッダー（テンプレート有）
- MstInGame.csv: 3行ヘッダー（テンプレート有）
- MstEnemyStageParameter.csv: 3行ヘッダー（テンプレート有）
- MstAdventBattleClearReward.csv: 1行ヘッダー（テンプレート無）
- MstAdventBattleRank.csv: 1行ヘッダー（テンプレート無）

### カラム数について

DBスキーマのカラム数とCSVのカラム数が1つ異なるのは正常です：
- CSVファイルには制御用の `ENABLE` カラムが含まれる
- DBスキーマには `ENABLE` カラムは含まれない（データ投入時に処理される）

例：
- DBスキーマ: 9カラム
- CSVファイル: 10カラム（ENABLE + 9カラム）

### I18nカラムについて

一部のテーブルは、I18nデータを同一CSVに含む設計になっています：

**MstAdventBattle.csv**:
- 本体カラム: id, mst_event_id, mst_in_game_id, ...
- I18nカラム: name.ja, boss_description.ja

この設計により、1ファイルで多言語データを管理できます。

---

## DB投入手順

### ステップ1: ファイル確認

以下の5ファイルを準備：
1. MstAdventBattle.csv
2. MstInGame.csv
3. MstEnemyStageParameter.csv
4. MstAdventBattleClearReward.csv
5. MstAdventBattleRank.csv

### ステップ2: DB投入

推奨投入順序：
1. MstAdventBattle.csv（降臨バトル本体）
2. MstInGame.csv（ゲーム内設定）
3. MstEnemyStageParameter.csv（敵キャラ）
4. MstAdventBattleClearReward.csv（クリア報酬）
5. MstAdventBattleRank.csv（ランク報酬）

### ステップ3: 動作確認

テスト環境で以下を確認：
- [ ] 降臨バトルが表示される
- [ ] BGMが正しく再生される
- [ ] 敵キャラが正しく出現する（Wave 0～6）
- [ ] クリア報酬が取得できる
- [ ] ランク報酬が正しく表示される
- [ ] 多言語テキストが正しく表示される

---

## 技術的補足

### 既存データとの比較

作成したCSVファイルは、以下の既存降臨バトルを参考にしています：
- quest_raid_kai_00001（怪獣退治の時間）
- quest_raid_spy1_00001（SPY×FAMILY）
- quest_raid_dan1_00001（ダンダダン）

カラム順序、データ型、命名規則はすべて既存データに準拠しています。

### 前提条件

以下のマスタデータが事前に登録されている必要があります：

**必須**:
- MstEvent（event_you_00001）: イベント基本情報
- MstEnemyCharacter（c_you_00001, c_you_00101, c_you_00201, c_you_00301）: 敵キャラクター定義
- MstItem（memory_glo_00001～00005）: カラーメモリーアイテム

**オプション**:
- MstUnitAbility（ability_you_00001_01, ability_you_00201_01, ability_you_00101_01）: アビリティ定義（敵が特殊能力を持つ場合）

### リリースキー

すべてのCSVファイルで release_key が `202602010` に統一されています。
リリース時期が変更になった場合は、すべてのファイルで一括変更してください。

---

## まとめ

✅ **全5ファイルがDB投入可能**

検証の結果、すべてのCSVファイルが正しい形式で作成されており、DB投入可能な状態です。

- テンプレートがあるファイル: 3行ヘッダー形式を使用
- テンプレートがないファイル: 1行ヘッダー形式を使用（既存データに準拠）
- カラム順序: すべて既存データと一致
- データ型: すべて正しい
- 命名規則: すべて既存ルールに準拠

エンジニアに5ファイルを渡して、DB投入を依頼できます。
