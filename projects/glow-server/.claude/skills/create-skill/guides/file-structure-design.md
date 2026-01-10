# ファイル構成設計ガイド

Progressive Disclosureパターンに従った、スキルのファイル構成設計について説明します。

## 基本構成

```
.claude/skills/{skill-name}/
├── SKILL.md (40-100行推奨)
│   └── 概要、手順、参照リスト
├── common-rules.md (必要に応じて)
│   └── 全体共通のルール
├── guides/
│   └── {topic}.md (複数可)
│       └── トピック別の詳細ガイド
├── patterns/
│   └── {pattern-name}.md (複数可)
│       └── パターン別の実装ガイド
└── examples/
    └── {use-case}.md (複数可)
        └── ユースケース別の実装例
```

## ディレクトリの役割と使い分け

### guides/ - コンセプト・ツール解説

**目的:** 「何ができるか」「どう動くか」の理解

**内容:**
- ツールの説明
- API仕様
- コンセプト解説
- アーキテクチャ概要

**ファイル例:**
- `guides/factory.md` - Factoryの使い方と機能
- `guides/mockery.md` - Mockeryの使い方
- `guides/commands.md` - 利用可能なコマンド一覧

**特徴:**
- 説明的・教育的な内容
- 「〜とは」「〜ガイド」「〜の使い方」といったタイトル
- コード例よりも概念の説明が中心

### patterns/ - 実装パターン・方法論

**目的:** 「どのように実装するか」の手順と判断基準

**内容:**
- 実装手順（ステップバイステップ）
- 使い分けの判断基準
- アーキテクチャパターン
- ベストプラクティス

**ファイル例:**
- `patterns/paginated-tables.md` - ページネーションテーブルの実装方法
- `patterns/detail-page.md` - 詳細ページの実装方法
- `patterns/form-validation.md` - フォームバリデーションの実装パターン

**特徴:**
- 抽象的で汎用性が高い
- 「〜の方法」「〜のパターン」「〜の実装」といったタイトル
- 手順とチェックリストが含まれる

### examples/ - 具体的な実装例

**目的:** 「実際のコード」を提示、そのまま真似できる

**内容:**
- 完全なコードスニペット
- 実ファイルからの引用（ファイルパスを明記）
- コマンド実行例
- 実際のプロジェクトでの使用例

**ファイル例:**
- `examples/new-table.md` - 新規テーブル作成の実例
- `examples/mst-mng.md` - mst/mngデータベースでの実装例
- `examples/column-changes.md` - カラム変更の実装例

**特徴:**
- 具体的で実用的
- 「〜の例」「〜のサンプル」「〜での実装」といったタイトル
- ファイルパスを明記（例: `api/database/migrations/xxx.php`）
- コピー&ペースト可能なコード

## 使い分けの判断基準

```
「〜の方法」「〜のパターン」→ patterns/
「〜の例」「〜のサンプル」「〜での実装」→ examples/
「〜とは」「〜ガイド」「〜の使い方」→ guides/
```

## ファイル分割の判断基準

### SKILL.mdの長さ

- **推奨:** 30-50行
- **最大:** 100行以下
- **役割:** ほぼ目次として機能させる
- **詳細な説明や例は全て参照ファイルに記載**

### 参照ファイルの長さ

- **目安:** 200行程度
- **最大:** 300行まで（それ以上は分割を検討）
- **100行超のファイルには目次を追加**

### ファイル分割のタイミング

**関連性が高い内容は1ファイルにまとめる:**
```
✅ patterns/form-implementation.md (250行)
   - フォーム作成
   - バリデーション
   - エラーハンドリング
```

**パターンが大きく異なる場合は分割:**
```
✅ patterns/form-creation.md (120行)
✅ patterns/form-validation.md (130行)
```

### サブディレクトリの使用

**3つ以上の類似ファイルがある場合はサブディレクトリにまとめる:**

```
❌ 良くない例（フラット構造）:
examples-new-table.md
examples-column-changes.md
examples-table-deletion.md
examples-complex.md

✅ 良い例（サブディレクトリ）:
examples/
├── new-table.md
├── column-changes.md
├── table-deletion.md
└── complex.md
```

## スケール別の構成例

### 小規模スキル（ファイル数が5個以下）

```
.claude/skills/api-response/
├── SKILL.md
├── common-rules.md
├── response-factory-guide.md
├── response-data-factory-guide.md
└── examples.md
```

→ **ディレクトリ分割は不要**

### 中規模スキル（類似ファイルが3-5個）

```
.claude/skills/admin-reward-display/
├── SKILL.md
├── architecture.md
└── patterns/
    ├── paginated-tables.md
    ├── detail-page-tables.md
    └── form-input.md
```

→ **1つのディレクトリ（patterns/）のみ使用**

### 大規模スキル（多様なファイル種別）

```
.claude/skills/migration/
├── SKILL.md
├── common-rules.md
├── naming-conventions.md
├── reference.md
├── guides/
│   └── commands.md
└── examples/
    ├── mst-mng.md
    ├── usr-log-sys.md
    └── admin.md
```

→ **複数ディレクトリ（guides/, examples/）を使用**

### 最大規模スキル

```
.claude/skills/schema-pr-implementer/
├── SKILL.md
├── workflow.md
├── guides/
│   └── pattern-guide.md
└── examples/
    ├── new-table.md
    ├── column-changes.md
    ├── table-deletion.md
    └── complex.md
```

→ **guides/, examples/ の両方を使用**

## ファイル配置のルール

### 相対パス参照

**SKILL.mdからサブディレクトリへ:**
```markdown
参照: **[ファイル名](examples/xxx.md)**
```

**サブディレクトリ内のファイル間:**
```markdown
参照: **[ファイル名](../guides/xxx.md)**
```

### 命名規則

**ディレクトリ名:**
- 複数形を使用（guides/, patterns/, examples/）

**ファイル名:**
- ケバブケース（kebab-case）を使用
- 例: `new-table.md`, `column-changes.md`, `paginated-tables.md`

## 実装のポイント

### Progressive Disclosure（段階的情報開示）

**レベル1（SKILL.md）:**
- 何をするスキルか
- どの順序で進めるか
- どこを参照すべきか

**レベル2（参照ファイル）:**
- 具体的なルール
- 実装パターン
- よくある間違い

### 実例の扱い

- 既存コードを積極的に活用
- ファイルパス（例: `api/database/migrations/xxx.php`）を明記
- コード全体を掲載（一部省略しない）
- 架空の例より実際のコード

### チェックリストの設計

- 具体的で実行可能な項目
- Yes/Noで判断できる
- 重要な項目には★マークや「重要」を明記

## 検証チェックリスト

ファイル構成設計後、以下を確認してください：

- [ ] SKILL.mdは30-50行（最大100行以下）か
- [ ] SKILL.mdが目次として機能し、詳細は参照ファイルに記載されているか
- [ ] 各ファイルの役割が明確か（guides/patterns/examples/の使い分け）
- [ ] 参照は1レベル深までか
- [ ] 100行超のファイルに目次があるか
- [ ] 3つ以上の類似ファイルがサブディレクトリにまとめられているか
- [ ] ファイル名がケバブケースか
- [ ] ディレクトリ名が複数形か
