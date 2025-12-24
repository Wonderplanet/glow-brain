---
description: 要件に基づいてGLOWマスタデータを生成
argument-hint: '要件の説明'
agent: 'agent'
---

# GLOW マスタデータ生成プロンプト

このプロンプトは、ユーザーが指定した要件に基づいて、GLOWプロジェクトのマスタデータ（CSV形式）を生成します。glow-server、glow-client、glow-masterdataの3つのリポジトリを調査し、既存のマスタデータ構造に準拠したデータを作成します。

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

以下のステップに従って、マスタデータを生成してください：

### 1. 要件の理解と分析

ユーザーから提供された要件を分析し、以下を明確にします：

- **データの目的**: 何のためのマスタデータか（例: 新ガチャ、新イベント、新キャラクター）
- **対象となるマスタデータ**: どのモデル（Mst/Opr）を使用するか
- **データ量**: 何件のレコードが必要か
- **関連データ**: 他のマスタデータとの関連性

### 2. 既存データ構造の調査

**必須**: 必ず以下のファイルを調査してください：

#### 既存マスタデータの参照
```bash
# 該当するマスタデータファイルを確認
cat projects/glow-masterdata/[ModelName].csv | head -10
```

以下の情報を抽出：
- カラム定義（1行目）
- データ型と形式
- 必須カラムとオプショナルカラム
- デフォルト値やパターン

#### クライアント側のデータモデル
```bash
# クライアント側のモデル定義を検索
grep -r "class [ModelName]" projects/glow-client/Assets/GLOW/Scripts/
```

#### サーバー側のテーブル定義（DDL）

**重要**: テーブル名は以下のルールで変換されます：
- モデル名（PascalCase）→ テーブル名（snake_case + 複数形）
- 例: `OprGacha` → `opr_gachas`
- 例: `MstUnit` → `mst_units`
- 例: `MstAdventBattle` → `mst_advent_battles`

```bash
# サーバー側のテーブルスキーマを確認（テーブル名は小文字+アンダースコア+複数形）
# 例: OprGacha → opr_gachas, MstUnit → mst_units
grep -A 30 "CREATE TABLE \`[table_name]\`" projects/glow-server/api/database/schema/master_tables_ddl.sql
```

DDLから以下を確認：
- カラム名とデータ型（ENUM型の選択肢も含む）
- PRIMARY KEY、UNIQUE制約
- NOT NULL制約、DEFAULT値
- COMMENT（カラムの説明）
- インデックス定義

### 3. データの設計

調査結果に基づき、以下を設計します：

- **データスキーマ**: カラム定義と型
- **データ内容**: 要件を満たす具体的な値
- **整合性**: 既存データとの整合性（IDの重複回避、外部キー制約など）
- **命名規則**: asset_keyやidの命名パターンに従う

### 4. CSVファイルの生成

設計に基づいてCSVファイルを生成します：

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
docs/マスタデータ作成/生成データ/[要件を要約した日本語]/[ModelName].csv
```

**パス構成**:
- `docs/マスタデータ作成/生成データ`: 固定パス
- `[要件を要約した日本語]`: 要件を表す簡潔な日本語フォルダ名（例: `新春ガチャ`, `イベント第1弾`）
- `[ModelName].csv`: クライアント定義のデータモデル名（例: `OprGacha.csv`, `MstUnit.csv`）

### 6. 生成レポートの作成

データ生成結果をMarkdown形式でレポートします：

```
docs/マスタデータ作成/生成データ/[要件を要約した日本語]/REPORT.md
```

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

## データ整合性チェック

- [ ] IDの重複がないことを確認
- [ ] 必須カラムがすべて埋まっている
- [ ] 日時形式が正しい
- [ ] 外部キー制約を満たしている
- [ ] 命名規則に準拠している

## 備考
[その他の重要な情報や注意事項]
```

## ベストプラクティス

### データ品質
- **整合性**: 既存データと矛盾しない
- **完全性**: 必須カラムはすべて埋める
- **妥当性**: 値の範囲やフォーマットが正しい
- **一貫性**: 命名規則やパターンを統一

### 調査の徹底
- 必ず既存のマスタデータを参照
- クライアント・サーバー双方のモデル定義を確認
- 関連するマスタデータも調査（外部キー参照など）
- I18nファイルの存在も確認

### ファイル命名
- フォルダ名は簡潔で分かりやすく（20文字以内推奨）
- モデル名は正確に（大文字小文字を含む）
- ファイル名に特殊文字を使わない

### ドキュメント化
- 生成レポートは詳細に記載
- データ設計の意図を明記

## 出力形式

### CSVファイル

```csv
ENABLE,id,column1,column2,column3
e,unique_id_001,value1,value2,value3
e,unique_id_002,value1,value2,value3
```

### ディレクトリ構造

```
docs/
└── マスタデータ作成/
    └── 生成データ/
        └── [要件を要約した日本語]/
            ├── REPORT.md
            ├── [ModelName1].csv
            └── [ModelName2].csv
```

## 注意事項

### 既存データとの整合性
- **ID重複**: 既存データのIDと重複しないよう、必ず最大IDを確認
- **外部キー**: 参照先のマスタデータが存在することを確認
- **日付範囲**: `start_at`と`end_at`は重複や矛盾がないように設定

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

- 既存マスタデータ: `projects/glow-masterdata/*.csv`
- クライアントモデル: `projects/glow-client/Assets/GLOW/Scripts/`
- サーバーテーブル定義: `projects/glow-server/api/database/schema/master_tables_ddl.sql`
- バージョン設定: `config/versions.json`

## 例: 新ガチャのマスタデータ生成

### 要件
「新春限定ガチャを追加したい。期間は2026年1月1日〜1月31日。10連ガチャで1回確定報酬あり。」

### 生成されるファイル
```
docs/マスタデータ作成/生成データ/新春限定ガチャ/
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

このプロンプトを使用することで、GLOWプロジェクトの既存構造に準拠した高品質なマスタデータを効率的に生成できます。
