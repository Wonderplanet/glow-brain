# コマンドの配置ルール

## プロジェクトレベル vs ユーザーレベル

### プロジェクトレベル（`.claude/commands/`）

**特徴:**
- リポジトリに保存、Gitで管理
- チーム全体で共有
- `/help`で "(project)" と表示

**適したケース:**
- プロジェクト固有のワークフロー
- チーム共通の手順
- コードベースに依存するタスク

**例:**
```
.claude/commands/
├── api/
│   └── generate-endpoint.md    # API生成
├── admin/
│   └── add-resource.md         # 管理画面リソース追加
└── general/
    └── create-skill.md         # スキル作成
```

### ユーザーレベル（`~/.claude/commands/`）

**特徴:**
- ホームディレクトリに保存
- 全プロジェクトで利用可能
- `/help`で "(user)" と表示

**適したケース:**
- 個人的なワークフロー
- 複数プロジェクト共通のタスク
- 個人の生産性ツール

**例:**
```
~/.claude/commands/
├── security-review.md    # セキュリティレビュー
├── explain.md            # コード説明
└── optimize.md           # 最適化
```

## 名前空間の設計

### サブディレクトリによる分類

```
.claude/commands/
├── api/           # API開発関連
├── admin/         # 管理画面関連
├── general/       # 汎用コマンド
├── sdd/           # SDD関連
└── master-data/   # マスタデータ関連
```

### 命名規則

- kebab-case: `create-migration.md`
- 動詞で始める: `generate-`, `create-`, `fix-`, `review-`
- 具体的な名前: `fix-phpstan-errors.md` > `fix-errors.md`

## 優先順位

同名のコマンドが存在する場合:

1. プロジェクトレベル（`.claude/commands/`）
2. ユーザーレベル（`~/.claude/commands/`）

**プロジェクトコマンドが常に優先される**

## 本プロジェクトでの推奨

### generalディレクトリ
プロジェクト横断で使える汎用コマンド
- `/general:create-skill`
- `/general:create-subagent`
- `/general:create-command` ← 今回作成

### 機能別ディレクトリ
特定機能に特化したコマンド
- `/api:generate-endpoint`
- `/admin:add-resource`
- `/sdd:extract-requirements`
