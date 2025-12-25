---
description: 要件に基づいてGLOWマスタデータを生成
argument-hint: '要件の説明'
agent: 'agent'
---

# GLOW マスタデータ生成プロンプト

このプロンプトは、ユーザーが指定した要件に基づいて、GLOWプロジェクトのマスタデータ（CSV形式）を生成します。glow-server、glow-client、glow-masterdataの3つのリポジトリを調査し、既存のマスタデータ構造に準拠したデータを作成します。

## ⚠️ タスク完遂の原則

**このタスクは、要件に含まれる全てのマスタデータを最後まで作り切ることが目的です。**

### 絶対に守るべきルール

1. **途中で止めない**: 一部のデータだけ作成して報告書を書いて終了するのは禁止
2. **完了条件**: REPORTの「未作成のマスタデータ」セクションが**ゼロ**になるまで作業を継続
3. **不確実性への対処**:
   - 仕様が不明確でも、既存データのパターンから推測して実装を続ける
   - 立ち止まって確認を求めるのではなく、最も合理的なアプローチを推測して継続
4. **段階的完了の禁止**: 「コアデータだけ先に作る」という段階的アプローチは取らない
5. **全体実装**: 要件に含まれる全てのマスタデータを1回のタスクで完成させる

### タスク完了の定義

以下の条件を**全て**満たすまで作業を継続してください：
- ✅ 要件に記載された全てのマスタデータが生成されている
- ✅ REPORTの「未作成のマスタデータ」セクションが存在しない、または空である
- ✅ 全てのCSVファイルが適切な形式で保存されている
- ✅ データ整合性チェックが全て完了している

## コンテキスト

### 対象プロジェクト
- **glow-server**: Laravel/PHP サーバーコード
- **glow-client**: Unity/C# クライアントコード（軽量化版）
- **glow-masterdata**: マスタデータCSVファイル群

### マスタデータの命名規則
- **Mst系**: 静的マスタデータ（例: `MstUnit`, `MstAdventBattle`, `MstAbility`）
- **Opr系**: 運用系マスタデータ（例: `OprGacha`, `OprCampaign`）

### CSV形式の特徴
- 1行目: `ENABLE,カラム1,カラム2,...`
- 2行目以降: `e,値1,値2,...`（eはENABLE状態を示す）
- NULL値: `__NULL__` で表現
- 日時形式: `YYYY-MM-DD HH:MM:SS`

## タスク

**重要**: 以下のステップは全て実施し、要件の全てのマスタデータが完成するまで作業を継続してください。途中で報告書を書いて終了するのではなく、完全実装を目指してください。

以下のステップに従って、マスタデータを生成してください：

### 1. 要件の理解と分析

ユーザーから提供された要件を分析し、以下を明確にします：

- **データの目的**: 何のためのマスタデータか（例: 新ガチャ、新イベント、新キャラクター）
- **対象となるマスタデータ**: どのモデル（Mst/Opr）を使用するか（**全て洗い出す**）
- **データ量**: 何件のレコードが必要か
- **関連データ**: 他のマスタデータとの関連性（**漏れなくリストアップ**）

⚠️ **注意**: この段階で必要な全てのマスタデータをリストアップしてください。後で「未作成」として残すのは避けてください。

### 2. 既存データ構造の調査

**必須**: 必ず以下のファイルを調査してください：

#### CSVテンプレートファイルの取得（最優先）

**重要**: CSV作成時は必ず対応するテンプレートファイルをコピーして使用してください：

```bash
# テンプレートファイルをコピー
cp projects/glow-masterdata/sheet_schema/[ModelName].csv マスタデータ/施策/[要件を要約した日本語]/[ModelName].csv
```

テンプレートファイルの構造：
- 1行目: `memo`（説明用）
- 2行目: `TABLE,モデル名,モデル名,...`（各カラムが所属するテーブル）
- 3行目: `ENABLE,カラム1,カラム2,...`（実際のヘッダー）

**テンプレートファイルのヘッダー（3行目）に完全に従ってデータを作成してください。**

#### 既存マスタデータの参照
```bash
# 既存データの内容を確認（データパターンの参考に）
cat projects/glow-masterdata/[ModelName].csv | head -10
```

以下の情報を抽出：
- データ型と形式
- 必須カラムとオプショナルカラム
- デフォルト値やパターン

#### サーバー側のテーブル定義（スキーマJSON）

**重要**: テーブル名は以下のルールで変換されます：
- モデル名（PascalCase）→ テーブル名（snake_case + 複数形）
- 例: `OprGacha` → `opr_gachas`
- 例: `MstUnit` → `mst_units`
- 例: `MstAdventBattle` → `mst_advent_battles`

**スキーマファイルの参照方法**:

マスタテーブル：
```bash
# マスタテーブルの全テーブル一覧
jq '.databases.mst.tables | keys' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定テーブルのスキーマ全体を取得
jq '.databases.mst.tables.opr_gachas' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定テーブルのカラム一覧のみ取得
jq '.databases.mst.tables.opr_gachas.columns | keys' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定カラムの詳細情報（型、NULL可否、デフォルト値、コメント）
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

ユーザーテーブル：
```bash
# ユーザーテーブルの全テーブル一覧
jq '.databases.usr.tables | keys' projects/glow-server/api/database/schema/exports/user_tables_schema.json

# 特定テーブルのスキーマ取得
jq '.databases.usr.tables.usr_users' projects/glow-server/api/database/schema/exports/user_tables_schema.json
```

**スキーマJSONの構造**:
```json
{
  "databases": {
    "mst": {
      "tables": {
        "テーブル名": {
          "comment": "テーブル説明",
          "columns": {
            "カラム名": {
              "type": "データ型 (例: varchar(255), enum('A','B'), int unsigned)",
              "nullable": true/false,
              "default": "デフォルト値",
              "comment": "カラム説明"
            }
          },
          "indexes": {
            "PRIMARY": {...},
            "インデックス名": {...}
          }
        }
      }
    }
  }
}
```

スキーマから以下を確認：
- カラム名とデータ型（ENUM型の選択肢も含む）
- PRIMARY KEY、UNIQUE制約
- NOT NULL制約、DEFAULT値
- COMMENT（カラムの説明）
- インデックス定義

#### クライアント側のデータモデル（参考）
```bash
# クライアント側のモデル定義を検索
grep -r "class [ModelName]" projects/glow-client/Assets/GLOW/Scripts/
```

### 3. データの設計

調査結果に基づき、以下を設計します：

- **データスキーマ**: カラム定義と型
- **データ内容**: 要件を満たす具体的な値
- **整合性**: 既存データとの整合性（IDの重複回避、外部キー制約など）
- **命名規則**: asset_keyやidの命名パターンに従う

⚠️ **注意**:
- 不明確な仕様があっても、既存データのパターンから推測して設計を進めてください
- 「仕様が不明なので作成しない」という判断は避け、合理的な推測で実装してください
- 全てのマスタデータに対して設計を完了させてください

### 4. CSVファイルの生成

設計に基づいて**全ての**CSVファイルを生成します：

```csv
ENABLE,id,column1,column2,...
e,value1,value2,value3,...
e,value1,value2,value3,...
```

**重要な注意事項**:
- IDは既存データと重複しないこと
- `__NULL__`を適切に使用
- 日時カラムは`YYYY-MM-DD HH:MM:SS`形式
- release_keyは最新の日付（例: 202509010）を使用

### 5. ファイルの保存

生成したCSVファイルを以下のパスに保存：

```
マスタデータ/施策/[要件を要約した日本語]/[ModelName].csv
```

**パス構成**:
- `マスタデータ/施策`: 固定パス
- `[要件を要約した日本語]`: 要件を表す簡潔な日本語フォルダ名（例: `新春ガチャ`, `イベント第1弾`）
- `[ModelName].csv`: クライアント定義のデータモデル名（例: `OprGacha.csv`, `MstUnit.csv`）

### 6. DDLスキーマとの整合性チェックと自動修正

**必須**: 全てのCSVファイルを生成した後、DDLファイルと照合して整合性をチェックし、必要に応じて自動修正してください。

#### 6-1. スキーマJSONファイルの参照

各マスタデータに対応するテーブル定義をスキーマJSONファイルから取得：

```bash
# モデル名→テーブル名変換規則:
# - PascalCase → snake_case
# - 複数形化（sまたはesを追加）
# 例: OprGacha → opr_gachas
# 例: MstUnit → mst_units
# 例: MstAdventBattle → mst_advent_battles

# スキーマJSONからテーブル定義を取得（マスタテーブルの場合）
jq '.databases.mst.tables.[table_name]' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 例：OprGachaの場合
jq '.databases.mst.tables.opr_gachas' projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 6-2. カラムの存在確認

生成したCSVのヘッダー行（テンプレートファイルの3行目）とスキーマJSONのカラム定義を比較：

**チェック項目**:
1. **CSVにあってスキーマJSONにないカラム**
   - ❌ エラー: スキーマJSONに存在しないカラムは削除が必要
   - 該当カラムをCSVから削除して再保存
   - ⚠️ 注意：テンプレートファイルを使用している場合は通常発生しません

2. **スキーマJSONにあってCSVにないカラム**
   - NOT NULL制約があるカラム: CSVに追加してデフォルト値を設定
   - NULL許可のカラム: `__NULL__`で追加（任意）
   - ⚠️ 注意：テンプレートファイルを使用している場合は通常発生しません

3. **カラムの順序**
   - 順序は問わないが、`ENABLE`は常に1列目
   - `id`は2列目を推奨

#### 6-3. データ型の検証

各カラムの値がスキーマJSONのデータ型に適合しているか確認：

**チェック項目**:
1. **ENUM型**
   ```json
   // スキーマJSON例
   {
     "type": "enum('active','inactive','pending')",
     "nullable": false
   }
   ```
   - CSVの値が許可されたENUM値のみか確認
   - 不正な値があれば修正または削除

2. **INT/BIGINT型**
   - 数値のみが入っているか確認
   - 文字列が混入している場合は修正

3. **DATETIME型**
   - `YYYY-MM-DD HH:MM:SS`形式か確認
   - 形式が異なる場合は修正

4. **VARCHAR/TEXT型**
   - 最大文字数制限を超えていないか確認

#### 6-4. 制約の検証

**PRIMARY KEY制約**:
- `id`カラムの値が一意か確認
- 重複がある場合はエラーとして報告

**UNIQUE制約**:
- UNIQUE制約のあるカラムの値が一意か確認
- 重複がある場合は修正

**NOT NULL制約**:
- NOT NULL制約のあるカラムに`__NULL__`や空文字が入っていないか確認
- 違反がある場合はデフォルト値で埋める

#### 6-5. 自動修正の実施

チェックで検出された問題を自動修正：

1. **不正なカラムの削除**
   - スキーマJSONに存在しないカラムをCSVから削除
   - 削除したカラム名を記録

2. **不足カラムの追加**
   - NOT NULL制約のあるカラムを追加
   - 適切なデフォルト値を設定

3. **データ型の修正**
   - ENUM値を許可された値に修正
   - 日時形式を正規化

4. **CSVの再保存**
   - 修正後のCSVを同じパスに上書き保存

#### 6-6. 修正ログの記録

修正内容を以下の形式で記録（後でREPORTに含める）：

```markdown
## スキーマ検証と修正

### [ModelName1].csv
- ✅ スキーマチェック完了: 問題なし

### [ModelName2].csv
- ⚠️ 修正内容:
  - 削除したカラム: `old_column` (スキーマJSONに存在しないため)
  - 追加したカラム: `new_required_field` (NOT NULL制約のため、デフォルト値: 0)
  - データ型修正: `status`カラムの不正値 "active_new" → "active"

### [ModelName3].csv
- ❌ エラー:
  - PRIMARY KEY重複: id=12345 が2件存在
  - 対処: 2件目のidを12346に修正
```

### 7. 生成レポートの作成

**全てのマスタデータを生成した後に**、データ生成結果をMarkdown形式でレポートします：

```
マスタデータ/施策/[要件を要約した日本語]/REPORT.md
```

⚠️ **重要**: レポートを書く前に、要件に含まれる全てのマスタデータが生成されていることを確認してください。

レポートには以下を含めてください：

#### レポートテンプレート

```markdown
# マスタデータ生成レポート

## 要件概要
[ユーザーが指定した要件の要約]

## 生成データ一覧

### [ModelName1].csv
- **レコード数**: X件
- **主要カラム**: column1, column2, ...
- **データ概要**: [簡潔な説明]

### [ModelName2].csv
- **レコード数**: X件
- **主要カラム**: column1, column2, ...
- **データ概要**: [簡潔な説明]

## データ設計の詳細

### ID範囲
- [ModelName1]: [開始ID] ~ [終了ID]
- [ModelName2]: [開始ID] ~ [終了ID]

### 命名規則
- IDパターン: [説明]
- asset_keyパターン: [説明]

### 参照した既存データ
- [既存ファイル名1]: [参照目的]
- [既存ファイル名2]: [参照目的]

## スキーマ検証と修正

[ステップ6で実施したスキーマ検証の結果をここに記載]

### [ModelName1].csv
- ✅ スキーマチェック完了: 問題なし

### [ModelName2].csv
- ⚠️ 修正内容:
  - [修正内容を具体的に記載]

## データ整合性チェック

- [x] **スキーマJSONとの整合性を確認**
- [x] **CSVテンプレートファイルのヘッダーに完全に従っている**
- [x] IDの重複がないことを確認
- [x] 必須カラムがすべて埋まっている
- [x] 日時形式が正しい
- [x] 外部キー制約を満たしている
- [x] 命名規則に準拠している
- [x] ENUM型の値が許可された値のみであることを確認
- [x] データ型が正しいことを確認
- [x] **要件に含まれる全てのマスタデータが生成されている**

## 備考
[その他の重要な情報や注意事項]
```

⚠️ **レポートに関する重要な注意**:
- レポートに「未作成のマスタデータ」セクションを作成してはいけません
- 全てのマスタデータが完成してからレポートを書いてください
- もし途中で不明点がある場合は、既存データのパターンから推測して実装を続けてください

## ベストプラクティス

### タスク完遂の徹底 🎯
- **完全実装**: 要件に含まれる全てのマスタデータを1回のタスクで完成させる
- **途中終了の禁止**: 一部だけ作成して報告書を書いて終了しない
- **不確実性への対処**: 不明点があっても既存パターンから推測して実装を続ける
- **段階的完了の回避**: 「まずコアデータだけ」という段階的アプローチは取らない
- **完了条件の遵守**: REPORTに「未作成」が残らないようにする

### データ品質
- **DDLスキーマ準拠**: サーバー側のテーブル定義と完全に一致させる
- **整合性**: 既存データと矛盾しない
- **完全性**: 必須カラムはすべて埋める
- **妥当性**: 値の範囲やフォーマットが正しい
- **一貫性**: 命名規則やパターンを統一
- **制約遵守**: PRIMARY KEY、UNIQUE、NOT NULL制約を満たす

### CSVテンプレートの活用 ✨
- **最優先ルール**: CSVファイルは必ずテンプレートファイルをコピーして作成する
- **ヘッダー厳守**: テンプレートファイルの3行目（ヘッダー行）に完全に従う
- **テンプレートの構造理解**: 1行目（memo）、2行目（TABLE指定）、3行目（ヘッダー）を把握する
- **カラム追加禁止**: テンプレートにないカラムを独自に追加しない

### スキーマ検証の徹底 ⚠️
- **必須ステップ**: CSV生成後、必ずスキーマJSONファイルと照合する
- **自動修正**: 不正なカラムは自動削除、不足カラムは自動追加
- **データ型チェック**: ENUM、INT、DATETIME型の値を厳密に検証
- **制約チェック**: PRIMARY KEY重複、UNIQUE違反、NOT NULL違反を検出
- **修正ログ**: 全ての修正内容をREPORTに記録

### 調査の徹底
- **CSVテンプレートファイルを最優先で参照**: `projects/glow-masterdata/sheet_schema/`
- 必ず既存のマスタデータを参照
- **スキーマJSONファイルを必ず参照**:
  - マスタ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
  - ユーザー: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`
- **jqコマンドで効率的に参照**: テーブル一覧、カラム詳細、型情報を素早く取得
- 関連するマスタデータも調査（外部キー参照など）
- I18nファイルの存在も確認

### ファイル命名
- フォルダ名は簡潔で分かりやすく（20文字以内推奨）
- モデル名は正確に（大文字小文字を含む）
- ファイル名に特殊文字を使わない

### ドキュメント化
- 生成レポートは詳細に記載
- データ設計の意図を明記
- **重要**: レポートは全データ生成完了後に作成する（途中で書かない）

## 出力形式

### CSVファイル

```csv
ENABLE,id,column1,column2,column3
e,unique_id_001,value1,value2,value3
e,unique_id_002,value1,value2,value3
```

### ディレクトリ構造

```
マスタデータ/
└── 施策/
    └── [要件を要約した日本語]/
        ├── 施策ファイル構成.md
        ├── REPORT.md
        ├── [ModelName1].csv
        └── [ModelName2].csv
```

## 注意事項

### タスク完遂に関する重要事項 ⚠️
- **絶対にやってはいけないこと**:
  - ❌ 一部のマスタデータだけ作成して報告書を書いて終了する
  - ❌ 「未作成のマスタデータ」セクションをREPORTに追加する
  - ❌ 不明点があるからといって作業を途中で止める
  - ❌ 「まずコアデータだけ」という段階的アプローチ
- **必ずやるべきこと**:
  - ✅ 要件に含まれる全てのマスタデータを完成させる
  - ✅ 不明点は既存データから推測して実装を続ける
  - ✅ REPORTを書く前に全データが揃っていることを確認する
  - ✅ データ整合性チェックを全て完了させる

### 既存データとの整合性
- **ID重複**: 既存データのIDと重複しないよう、必ず最大IDを確認
- **外部キー**: 参照先のマスタデータが存在することを確認
- **日付範囲**: `start_at`と`end_at`は重複や矛盾がないように設定

### CSVテンプレートの活用 ⚠️
- **テンプレートの使用必須**: CSV作成時は必ずテンプレートファイルをコピーして使用
- **ヘッダー改変禁止**: テンプレートファイルのヘッダー（3行目）を勝手に変更しない
- **カラム順序厳守**: テンプレートのカラム順序を保持する
- **不要カラムの削除禁止**: テンプレートにあるカラムを独断で削除しない

### スキーマJSONとの整合性 ⚠️
- **カラム検証必須**: CSV生成後、必ずスキーマJSONファイルと照合すること
- **不正カラムの削除**: スキーマJSONに存在しないカラムは問答無用で削除
- **必須カラムの追加**: NOT NULL制約のあるカラムが不足している場合は追加
- **データ型の厳守**: ENUM、INT、DATETIME型の値を厳格にチェック
- **制約違反の修正**: PRIMARY KEY重複、UNIQUE違反は自動修正
- **テーブル名変換**: PascalCase（例: OprGacha）→ snake_case + 複数形（例: opr_gachas）

### GLOWプロジェクト特有の規則
- **release_key**: リリース日を示すキー（例: 202509010）
- **ENABLE列**: 常に`e`（無効な場合は`d`だが、通常は生成しない）
- **__NULL__**: NULLを表す特殊文字列
- **I18n対応**: 多言語対応が必要な場合は、対応するI18nファイルも生成

### 参照専用リポジトリ
- **重要**: このリポジトリ（glow-brain）は参照専用です
- `projects/`以下のファイルは直接編集しないこと
- 生成したデータは`docs/`以下に保存すること
- 実際の適用は本来のリポジトリで行うこと

## 関連ファイル

- **CSVテンプレートファイル（最優先）**: `projects/glow-masterdata/sheet_schema/*.csv`
- 既存マスタデータ: `projects/glow-masterdata/*.csv`
- **スキーマJSONファイル**:
  - マスタテーブル: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
  - ユーザーテーブル: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`
- クライアントモデル: `projects/glow-client/Assets/GLOW/Scripts/`
- バージョン設定: `config/versions.json`

## 例: 新ガチャのマスタデータ生成

### 要件
「新春限定ガチャを追加したい。期間は2026年1月1日〜1月31日。10連ガチャで1回確定報酬あり。」

### 生成されるファイル
```
マスタデータ/施策/新春限定ガチャ/
├── REPORT.md
├── OprGacha.csv
├── OprGachaI18n.csv
└── MstGachaPrizeGroup.csv (必要に応じて)
```

### OprGacha.csv
```csv
ENABLE,id,gacha_type,upper_group,enable_ad_play,enable_add_ad_play_upper,ad_play_interval_time,multi_draw_count,multi_fixed_prize_count,daily_play_limit_count,total_play_limit_count,daily_ad_limit_count,total_ad_limit_count,prize_group_id,fixed_prize_group_id,appearance_condition,unlock_condition_type,unlock_duration_hours,start_at,end_at,display_information_id,dev-qa_display_information_id,display_gacha_caution_id,gacha_priority,release_key
e,NewYear_2026_001,Premium,NewYear_2026,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,NewYear_2026_001,fixd_NewYear_2026_001,Always,None,__NULL__,"2026-01-01 00:00:00","2026-01-31 23:59:59",,,new_year_caution_001,80,202512010
```

---

## 最後に：タスク完遂の確認

このプロンプトを完了する前に、以下を必ず確認してください：

1. ✅ 要件に記載された全てのマスタデータが生成されているか？
2. ✅ **全てのCSVファイルがテンプレートファイルからコピーして作成されているか？**
3. ✅ 全てのCSVファイルが適切なパスに保存されているか？
4. ✅ **スキーマJSONとの整合性チェックが完了しているか？**
5. ✅ **スキーマJSONに存在しないカラムが削除されているか？**
6. ✅ **必須カラムが全て含まれているか？**
7. ✅ **データ型とENUM値が正しいか？**
8. ✅ REPORTに「未作成のマスタデータ」セクションが存在しないか？
9. ✅ REPORTに「スキーマ検証と修正」セクションが含まれているか？
10. ✅ データ整合性チェックが全て完了しているか？
11. ✅ 全てのファイルが正しい形式で保存されているか？

**これら全てがYESになるまで、作業を継続してください。**

---

このプロンプトを使用することで、GLOWプロジェクトの既存構造に準拠した高品質なマスタデータを**完全に**生成できます。

**重要**: このプロンプトは「一部だけ作成」ではなく、「全て完成させる」ことが目的です。途中で止めずに最後まで実装してください。
