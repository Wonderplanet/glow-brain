# GLOW マスタデータ スキル群

このディレクトリには、GLOWプロジェクトのマスタデータ生成に関連する5つのスキルが含まれています。

## スキル一覧

### 層1: 基盤スキル（独立実行可能）

#### 1. `masterdata-schema-inspector`
**役割**: スキーマ情報の調査・提示（読み取り専用）

- モデル名からテーブル定義、ENUM選択肢、制約情報を抽出
- CSVテンプレートファイルと既存マスタデータの参照
- **トリガー**: マスタデータのスキーマ、テーブル定義を確認、CSV形式を調査
- **スクリプト**:
  - `scripts/convert_model_to_table.sh`: モデル名→テーブル名変換
  - `scripts/extract_schema.sh`: スキーマJSON抽出

**使用例**:
```
Skill(skill: "masterdata-schema-inspector", args: "OprGacha")
```

---

#### 2. `masterdata-validator`
**役割**: 生成済みCSVファイルの検証と自動修正

- CSVヘッダーとスキーマJSONの照合
- ENUM、NOT NULL、PRIMARY KEY制約のチェック
- データ型検証と自動修正
- **トリガー**: マスタデータの検証、スキーマとの整合性、CSVファイルをチェック
- **スクリプト**:
  - `scripts/validate_csv.sh`: 6段階のCSV検証ロジック

**使用例**:
```
Skill(skill: "masterdata-validator", args: "マスタデータ/施策/新春ガチャ/OprGacha.csv OprGacha")
```

---

### 層2: ワークフロースキル

#### 3. `masterdata-requirement-analyzer`
**役割**: 要件フォルダを分析してドキュメント生成

- 要件フォルダのファイル一覧を取得
- ファイル構造と関係性を分析
- `要件ファイル構成.md` を生成
- **トリガー**: 要件ファイル構成、要件を分析

**使用例**:
```
Skill(skill: "masterdata-requirement-analyzer", args: "マスタデータ/施策/新春ガチャ")
```

---

#### 4. `masterdata-generator`
**役割**: 要件に基づくマスタデータ生成

- 要件の分析とマスタデータリストアップ
- `masterdata-schema-inspector` でスキーマ調査
- テンプレートファイルをコピーしてCSV生成
- `masterdata-validator` でスキーマ検証
- REPORT.md生成
- **トリガー**: マスタデータを生成、GLOWマスタデータ作成、要件からマスタデータ
- **依存**: `masterdata-schema-inspector`, `masterdata-validator`

**使用例**:
```
Skill(skill: "masterdata-generator", args: "新春限定ガチャを追加。期間は2026年1月1日〜1月31日。10連ガチャで1回確定報酬あり。")
```

---

### 層3: 統合ワークフロースキル

#### 5. `masterdata-full-workflow`
**役割**: フル実行ワークフロー

- `masterdata-requirement-analyzer` を実行
- `masterdata-generator` を実行
- 全成果物の確認
- **トリガー**: マスタデータフル実行、施策のマスタデータを作成
- **依存**: `masterdata-requirement-analyzer`, `masterdata-generator`

**使用例**:
```
Skill(skill: "masterdata-full-workflow", args: "マスタデータ/施策/新春ガチャ")
```

---

## スキル構成図

```
┌─────────────────────────────────────────┐
│ masterdata-full-workflow (統合)     │
│ - 要件分析→データ生成→検証を一括実行    │
└──────────┬──────────────────┬───────────┘
           ↓                  ↓
   ┌───────────────────┐   ┌──────────────────┐
   │ requirement-      │   │ masterdata-      │
   │ analyzer          │   │ generator        │
   │ (要件分析)        │   │ (データ生成)     │
   └───────────────────┘   └────┬─────────────┘
                                ↓
                   ┌────────────┴─────────────┐
                   ↓                          ↓
           ┌────────────────┐      ┌─────────────────┐
           │ schema-        │      │ validator       │
           │ inspector      │      │ (検証・修正)    │
           │ (スキーマ調査) │      │                 │
           └────────────────┘      └─────────────────┘
```

## 使用シナリオ

### シナリオ1: フル実行（推奨）

施策ディレクトリに要件フォルダを準備済みの場合:

```
Skill(skill: "masterdata-full-workflow", args: "マスタデータ/施策/新春ガチャ")
```

**出力**:
- `要件ファイル構成.md`
- `REPORT.md`
- 全てのマスタデータCSV

---

### シナリオ2: 個別実行

#### 2-1. スキーマ調査のみ

```
Skill(skill: "masterdata-schema-inspector", args: "OprGacha")
```

#### 2-2. データ生成のみ

```
Skill(skill: "masterdata-generator", args: "新春限定ガチャを追加...")
```

#### 2-3. 検証のみ

```
Skill(skill: "masterdata-validator", args: "マスタデータ/施策/新春ガチャ/OprGacha.csv OprGacha")
```

---

## スクリプトの直接実行

### モデル名→テーブル名変換

```bash
bash .claude/skills/masterdata-schema-inspector/scripts/convert_model_to_table.sh OprGacha
# 出力: opr_gachas
```

### スキーマJSON抽出

```bash
bash .claude/skills/masterdata-schema-inspector/scripts/extract_schema.sh opr_gachas
# 出力: JSON形式のテーブル定義
```

### CSV検証

```bash
bash .claude/skills/masterdata-validator/scripts/validate_csv.sh \
    マスタデータ/施策/新春ガチャ/OprGacha.csv \
    OprGacha
# 出力: JSON形式の検証結果
```

---

## 既存プロンプトとの関係

これらのスキルは、既存の `.github/prompts/マスタデータ-*.prompt.md` ファイルと並行運用できます。

| 既存プロンプト | 対応スキル |
|---------------|----------|
| マスタデータ-0.フル実行 | `masterdata-full-workflow` |
| マスタデータ-1.要件ファイル構成生成 | `masterdata-requirement-analyzer` |
| マスタデータ-2.データ生成 | `masterdata-generator` |

既存プロンプトは削除されず、段階的に移行できます。

---

## 重要な設計原則

### テンプレートファイルの厳守
- CSV作成時は必ず `projects/glow-masterdata/sheet_schema/[ModelName].csv` をコピー
- テンプレートの3行目（ヘッダー）を改変しない

### スキーマ検証の徹底
- CSV生成後、必ず`masterdata-validator`で検証
- ENUM型、NOT NULL制約、PRIMARY KEY制約を厳密にチェック

### タスク完遂駆動
- `masterdata-generator` は途中終了せず、全マスタデータを完成させる
- 「未作成のマスタデータ」セクションを作成しない
- 不明点は既存データから推測して実装継続

### 参照専用リポジトリの尊重
- `projects/` 配下は参照のみ
- 生成データは `マスタデータ/施策/[施策名]/` に配置

---

## トラブルシューティング

### スキルが自動検出されない

- description に適切なトリガーキーワードが含まれているか確認
- スキル名が小文字とハイフンのみで構成されているか確認

### スクリプトが実行できない

```bash
# 実行権限を付与
chmod +x .claude/skills/*/scripts/*.sh
```

### 依存スキルが見つからない

スキルの依存関係を確認:
- `masterdata-generator` → `schema-inspector`, `validator`
- `masterdata-full-workflow` → `requirement-analyzer`, `generator`

---

## 今後の拡張予定

- [ ] I18n対応マスタデータの自動生成
- [ ] マスタデータ間の整合性チェック強化
- [ ] 既存データとの差分表示機能
- [ ] ロールバック機能

---

## 関連ファイル

- **スキーマJSON（マスタ）**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- **スキーマJSON（ユーザー）**: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`
- **CSVテンプレート**: `projects/glow-masterdata/sheet_schema/*.csv`
- **既存マスタデータ**: `projects/glow-masterdata/*.csv`

---

**最終更新**: 2025-12-26
**作成者**: Claude Code (Sonnet 4.5)
