# Claude Code 機能ガイド for glow-server

このドキュメントは、glow-serverプロジェクトでClaude Codeを効果的に活用するための初学者向けガイドです。

## 目次

1. [Claude Codeの基礎知識](#claude-codeの基礎知識)
2. [ディレクトリ構成](#ディレクトリ構成)
3. [使い方ガイド](#使い方ガイド)
4. [機能一覧](#機能一覧)
5. [よくある使用シナリオ](#よくある使用シナリオ)
6. [拡張方法](#拡張方法)
7. [ハンズオンチュートリアル](#ハンズオンチュートリアル)

---

## Claude Codeの基礎知識

### Claude Codeとは

Claude CodeはAnthropicが提供するCLIベースのAIアシスタントで、ソフトウェア開発タスクを支援します。glow-serverプロジェクトでは、以下の3つの拡張機能を活用してカスタマイズしています：

| 機能 | 説明 | 呼び出し方 |
|------|------|-----------|
| **スキル (Skills)** | 特定タスクの詳細なガイダンスを提供 | 自動で読み込まれる（または手動で参照） |
| **コマンド (Commands)** | 定型処理を実行するスラッシュコマンド | `/project:コマンド名` |
| **エージェント (Agents)** | 専門分野に特化したサブAIワーカー | 自動で起動（または `Task` ツールで明示的に起動） |

### 基本的な対話の流れ

```
ユーザー → 「テストを実行して失敗していたら修正して」
    ↓
Claude Code → 関連スキルを参照 → 適切なコマンド実行 → 必要に応じてエージェント起動
    ↓
結果報告
```

---

## ディレクトリ構成

```
.claude/
├── README.md              ← このファイル
├── api.md                 ← API開発用のCLAUDE.MD（コマンド、アーキテクチャ情報）
├── admin.md               ← admin開発用のCLAUDE.MD（Filament、Laravel情報）
├── skills/                ← スキル定義（詳細なガイダンス）
│   ├── HOW_TO_CREATE_SKILLS.md  ← スキル作成ガイド
│   ├── migration/         ← DBマイグレーション関連
│   ├── api-test-implementation/ ← テスト実装関連
│   ├── sail-check-fixer/  ← コード品質チェック関連
│   └── ...（他多数）
├── commands/              ← スラッシュコマンド定義
│   ├── api/              ← API開発用コマンド
│   ├── sdd/              ← SDD（Spec-Driven Development）コマンド
│   └── general/          ← 汎用コマンド
├── agents/               ← サブエージェント定義
│   ├── api/              ← API開発用エージェント
│   ├── admin/            ← admin開発用エージェント
│   └── general/          ← 汎用エージェント
└── tmp/                  ← 一時ファイル
```

---

## 使い方ガイド

### 1. スキル (Skills) の使い方

スキルは**特定のタスクを実行する際のベストプラクティス**を提供します。

#### 自動読み込み

Claude Codeは会話の内容から関連するスキルを自動的に読み込みます。例えば：

```
ユーザー: 「マイグレーションを作成して」
→ migration スキルが自動で読み込まれる
```

#### 手動で参照する場合

特定のスキルを明示的に使いたい場合：

```
ユーザー: 「sail-check-fixerスキルを使ってコード品質をチェックして」
```

#### 主要スキル一覧

| スキル名 | 用途 |
|---------|------|
| `migration` | DBマイグレーション実装 |
| `api-test-implementation` | PHPUnitテスト実装 |
| `api-test-runner` | テスト実行と失敗修正 |
| `sail-check-fixer` | コード品質チェック（phpcs/phpstan/deptrac） |
| `sail-execution` | sailコマンドの正しい実行方法 |
| `api-response` | APIレスポンス実装 |
| `domain-layer` | ドメインレイヤー実装 |
| `database-query` | MCPを使ったDB操作 |

### 2. コマンド (Commands) の使い方

コマンドは**スラッシュ記法で実行する定型処理**です。

#### 実行方法

```
/project:api-test
/project:api-test UserTest|LoginTest
```

#### 主要コマンド一覧

| コマンド | 説明 |
|---------|------|
| `/project:api-test` | PHPUnitテスト実行＆失敗修正 |
| `/project:api-fix-sail-check-errors` | sail checkエラーを全て修正 |
| `/sdd:00-sdd-run-full-flow` | SDD設計フロー全体を実行 |
| `/general:create-skill` | 新しいスキルを作成 |
| `/general:create-subagent` | 新しいエージェントを作成 |

#### SDDコマンド群

Spec-Driven Development（仕様駆動開発）用のコマンド群です：

```
/sdd:00-sdd-run-full-flow          ← 全フロー実行（これを使うことが多い）
/sdd:01-extract-server-requirements ← サーバー要件抽出
/sdd:02-investigate-code-requirements ← コード調査
...
```

### 3. エージェント (Agents) の使い方

エージェントは**特定分野に特化したサブAI**です。複雑なタスクを自律的に処理します。

#### 自動起動

Claude Codeはタスクに応じて適切なエージェントを自動で起動します：

```
ユーザー: 「phpstanエラーを全て修正して」
→ api-phpstan-fixer エージェントが自動起動
```

#### 主要エージェント一覧

| エージェント | 用途 |
|-------------|------|
| `api-phpstan-fixer` | PHPStan静的解析エラーの修正 |
| `api-phpcs-phpcbf-fixer` | コーディング規約違反の修正 |
| `api-deptrac-fixer` | アーキテクチャ違反の修正 |
| `admin-browser-tester` | admin画面のブラウザテスト |

---

## 機能一覧

### API開発用スキル

| スキル | 説明 | 使用タイミング |
|--------|------|---------------|
| `api-endpoint-implementation` | 新規APIエンドポイント実装 | 新しいAPIを作成する時 |
| `api-request-validation` | リクエストバリデーション実装 | リクエストパラメータの検証実装時 |
| `api-response` | レスポンス実装 | APIレスポンスを作成する時 |
| `api-schema-reference` | glow-schemaのYAML参照 | API仕様を確認する時 |
| `api-test-implementation` | テスト実装 | PHPUnitテストを書く時 |
| `api-test-runner` | テスト実行・修正 | テストを実行して失敗を修正する時 |
| `domain-layer` | ドメインレイヤー実装 | UseCase/Service/Repository等の実装時 |

### admin開発用スキル

| スキル | 説明 | 使用タイミング |
|--------|------|---------------|
| `admin-browser-tester` | ブラウザ動作確認 | admin実装後の動作テスト時 |
| `admin-page-navigator` | ページ遷移支援 | admin画面のURL特定時 |
| `admin-reward-display` | 報酬情報表示実装 | 報酬関連のテーブル・フォーム実装時 |
| `admin-test-data-finder` | テストデータ検索 | テスト用のプレイヤーデータを探す時 |

### インフラ・DB関連スキル

| スキル | 説明 | 使用タイミング |
|--------|------|---------------|
| `migration` | DBマイグレーション | テーブル作成・変更時 |
| `database-query` | DB操作（MCP使用） | テーブル構造確認・データ検索時 |
| `ecs-config-synchronizer` | ECS設定同期 | 環境変数・Dockerfile変更時 |
| `sail-execution` | sailコマンド実行 | Dockerコンテナ上でコマンド実行時 |
| `sail-check-fixer` | コード品質チェック | PR作成前の品質チェック時 |

### 課金・報酬関連スキル

| スキル | 説明 | 使用タイミング |
|--------|------|---------------|
| `bne-external-payment-platform` | 外部決済プラットフォーム連携 | Apple/Google決済実装時 |
| `bne-external-payment-purchase` | 購入処理・検証 | 購入フロー実装時 |
| `bne-external-payment-webhook` | Webhook処理 | Xsollaウェブフック実装時 |
| `reward-send-service` | 報酬送付機能 | 報酬配布機能実装時 |

### glow-schema関連スキル

| スキル | 説明 | 使用タイミング |
|--------|------|---------------|
| `schema-pr-implementer` | スキーマPR反映 | glow-schemaの変更をglow-serverに反映時 |

### SDD（Spec-Driven Development）関連

| スキル/コマンド | 説明 |
|---------------|------|
| `sdd-orchestrator` | SDDフロー全体のオーケストレーション |
| `/sdd:*` コマンド群 | 各SDDステップを個別に実行 |

---

## よくある使用シナリオ

### シナリオ1: 新規API実装

```
1. 「新しいAPIを実装したい」と伝える
2. glow-schemaのYAML定義を確認（api-schema-referenceスキル）
3. ドメインレイヤーを実装（domain-layerスキル）
4. Controller・レスポンスを実装（api-endpoint-implementation, api-responseスキル）
5. テストを実装（api-test-implementationスキル）
6. コード品質チェック（sail-check-fixerスキル）
```

**簡単な指示例:**
```
「/api/stage/endのエンドポイントを実装して」
「glow-schemaのPR #123の変更を反映して」
```

### シナリオ2: テスト実装・実行

```
1. /project:api-test を実行
2. 失敗したテストがあれば自動修正される
3. 全テストがパスするまで繰り返し
```

**簡単な指示例:**
```
/project:api-test
/project:api-test StageEndTest
「StageEndControllerのテストを書いて」
```

### シナリオ3: コード品質チェック

```
1. /project:api-fix-sail-check-errors を実行
2. phpcs/phpstan/deptracの全エラーが修正される
```

または個別にエージェントを使用:
```
「phpstanエラーを修正して」 → api-phpstan-fixerエージェントが起動
「phpcsエラーを修正して」 → api-phpcs-phpcbf-fixerエージェントが起動
```

### シナリオ4: DBマイグレーション

```
「mst_itemsテーブルにnew_columnカラムを追加するマイグレーションを作成して」
→ migrationスキルが自動読み込み
→ 正しい命名規則・実装パターンでマイグレーションファイル作成
```

### シナリオ5: admin画面の実装・テスト

```
1. admin機能を実装
2. 「admin画面の動作確認をして」と伝える
3. admin-browser-testerエージェントがブラウザで自動テスト
```

### シナリオ6: SDD（仕様駆動開発）フロー

新機能の設計から実装設計書作成まで：
```
/sdd:00-sdd-run-full-flow [仕様書PDFパス]
```

---

## 拡張方法

### 新しいスキルを作成する

```
/general:create-skill [スキル名] [スキルの説明]
```

または、`skills/HOW_TO_CREATE_SKILLS.md` を参照して手動で作成。

**スキル作成のポイント:**
- `SKILL.md` は500行以下に抑える
- 詳細は参照ファイルに分割（Progressive Disclosure）
- 実際のコードを例として掲載
- チェックリストを含める

### 新しいコマンドを作成する

```
/general:create-custom-command [コマンド名] [コマンドの説明]
```

**コマンドファイルの配置:**
- API関連: `.claude/commands/api/`
- admin関連: `.claude/commands/admin/`
- 汎用: `.claude/commands/general/`

### 新しいエージェントを作成する

```
/general:create-subagent [エージェント名] [エージェントの説明]
```

**エージェントファイルの配置:**
- API関連: `.claude/agents/api/`
- admin関連: `.claude/agents/admin/`
- 汎用: `.claude/agents/general/`

---

## 開発環境の基本コマンド

glow-serverプロジェクトでは、以下のコマンドをDockerコンテナ上で実行します：

```bash
# 基本コマンド（リポジトリルートから実行）
./tools/bin/sail-wp up -d           # コンテナ起動
./tools/bin/sail-wp down            # コンテナ停止

# API側コマンド
./tools/bin/sail-wp test            # テスト実行
./tools/bin/sail-wp check           # 品質チェック（phpcs/phpstan/deptrac）
./tools/bin/sail-wp artisan migrate # マイグレーション実行

# admin側コマンド
./tools/bin/sail-wp admin test      # adminテスト実行
./tools/bin/sail-wp admin check     # admin品質チェック
./tools/bin/sail-wp admin artisan migrate  # adminマイグレーション
```

**重要**: Claude Codeに指示する際は、`sail` または `sail-wp` と伝えるだけでOK。`cd api` などのディレクトリ移動は不要です。

---

## 困ったときは

### Claude Codeが期待通り動かない場合

1. **スキルを明示的に指定**: 「migration スキルを使って...」
2. **具体的な指示**: 「sail phpstanを実行してエラーを全て修正して」
3. **コマンドを使用**: `/project:api-test` など定型処理はコマンドで

### 新しい機能を追加したい場合

1. 既存のスキル/コマンド/エージェントを参考に
2. `HOW_TO_CREATE_SKILLS.md` を読む
3. `/general:create-*` コマンドを使用

### デバッグ

- DB構造確認: `database-query` スキルを使用
- テストデータ検索: `admin-test-data-finder` スキルを使用
- admin動作確認: `admin-browser-tester` エージェントを使用

---

## ハンズオンチュートリアル

実際に手を動かしながら学べるチュートリアルを用意しています。初めてClaude Codeを使う方は、ぜひこちらから始めてください。

### 基礎編

| チュートリアル | 内容 | 所要時間 |
|--------------|------|---------|
| [03. MCP設定](./tutorials/03-mcp-setup-hands-on.md) | MCPサーバーの設定方法、DB操作・ブラウザ操作の基本 | 25分 |

### API開発編

| チュートリアル | 内容 | 所要時間 |
|--------------|------|---------|
| [01. テスト実行・自動修正](./tutorials/01-api-test-hands-on.md) | `/project:api-test` コマンドでテストを実行し、失敗を自動修正する方法 | 15分 |
| [02. コード品質チェック](./tutorials/02-sail-check-fixer-hands-on.md) | phpcs/phpstan/deptracのエラーを一括で修正する方法 | 20分 |

### 推奨学習順序

1. **03. MCP設定** - Claude Codeの拡張機能（DB/ブラウザ操作）を理解
2. **01. テスト実行・自動修正** - 最も頻繁に使う基本操作
3. **02. コード品質チェック** - PR作成前の必須作業

---

## 参考リンク

- [Claude Code公式ドキュメント](https://docs.anthropic.com/claude-code)
- [スキル作成ガイド](./skills/HOW_TO_CREATE_SKILLS.md)
- [API開発ガイド](./api.md)
- [admin開発ガイド](./admin.md)

---

このドキュメントを活用して、glow-serverの開発を効率的に進めてください。
