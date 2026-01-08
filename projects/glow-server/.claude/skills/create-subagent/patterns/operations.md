# 運用・管理系エージェントパターン

このドキュメントでは、データベース管理、ブラウザテスト、設計フロー、システム運用に関連するサブエージェントの実装パターンを説明します。

## パターン分類

### 1. データベース管理エージェント

マイグレーション、クエリ最適化、データ操作を担当。

**推奨設定:**
- **model**: `sonnet`
- **color**: `green`

**description テンプレート:**
```yaml
description: |
  {データベース操作種別}を専門に担当するエージェント。{対象DB（mst/mng/usr/log/sys/admin）}に対して{操作内容}を実行。{Docker環境（sail）}での実行を前提とし、{トリガー条件}時に使用。
```

**実装例:**
```markdown
---
name: database-migration-manager
description: glow-serverプロジェクトのマイグレーション実行専門エージェント。sail migrateまたはsail admin migrateを実行し、複数DB（mst/mng/usr/log/sys/admin）に対するマイグレーションを管理。エラー発生時はロールバックとデバッグを行う。新規テーブル作成やスキーマ変更時に使用。
model: sonnet
color: green
---
```

### 2. ブラウザ自動テストエージェント

管理画面やWebUIの動作確認を自動化。

**推奨設定:**
- **model**: `sonnet`
- **color**: `orange`

**description テンプレート:**
```yaml
description: |
  {対象UI（admin/frontend）}実装や改修後にブラウザで実際に動作確認を行う専門エージェント。{ブラウザ自動化ツール}を使用してブラウザ操作を自動化し、{操作内容（ページ遷移/フォーム操作/表示確認等）}を実施してテスト結果をレポートする。{トリガー条件}時に使用。
```

**実装例:**
```markdown
---
name: admin-browser-tester
description: admin実装や改修後にブラウザで実際に動作確認を行う専門エージェント。chrome-devtools MCPを使用してブラウザ操作を自動化し、ページ遷移、フォーム操作、表示確認などを実施してテスト結果をレポートする。新規Filamentリソース追加、CRUD機能実装、バリデーション変更、表示項目変更など、あらゆるadmin実装後の動作確認をカバーする。
model: sonnet
color: orange
---
```

### 3. 設計フローオーケストレーター

複数のエージェントを連携させて大規模な設計プロセスを管理。

**推奨設定:**
- **model**: `opus`（複雑な判断が必要）
- **color**: `yellow`

**description テンプレート:**
```yaml
description: |
  {設計プロセス名}の全段階をオーケストレートする。複数の専門エージェント（{エージェント1}、{エージェント2}、{エージェント3}等）を順次または並列実行し、最終的な{成果物}を生成する。{トリガー条件（新機能開発等）}時に使用。
```

**実装例:**
```markdown
---
name: sdd-orchestrator
description: SDD設計フローの全段階をオーケストレートする。複数の専門エージェント（sdd-extract-server-requirements、sdd-review-server-spec、sdd-finalize-server-requirements等）を順次または並列実行し、最終的なSDD仕様書を生成する。スタミナブーストのような新機能のSDD作成時に使用。
model: opus
color: yellow
---
```

### 4. 設定ファイル同期エージェント

環境変数やDocker設定の変更を関連ファイルに自動反映。

**推奨設定:**
- **model**: `haiku` または `sonnet`
- **color**: `orange`

**description テンプレート:**
```yaml
description: |
  {設定ファイル種別}の変更を検出し、{関連ファイル群}を自動調整する専門エージェント。{変更種別（環境変数追加/Dockerfile変更等）}時に、{同期対象ファイル}の整合性を確保。{トリガー条件}時に使用。
```

**実装例:**
```markdown
---
name: ecs-config-synchronizer
description: api/adminの環境変数やDockerfile変更を検出し、codebuild配下のECS設定ファイル(taskdefinitions.json, buildspec.yml等)を自動調整する際に使用。新しい環境変数追加、Dockerfile変更、リソース要件変更時に、ECS Task Definitionとbuildspec設定の同期を支援する。
model: sonnet
color: orange
---
```

### 5. ナビゲーション支援エージェント

特定のページやリソースへの効率的なアクセス方法を提供。

**推奨設定:**
- **model**: `haiku`
- **color**: `green`

**description テンプレート:**
```yaml
description: |
  {対象UI/システム}で特定{リソース種別}へ効率的に遷移したい時に使用。{実装パターン/設定}からURL構造を理解し、必要な{パラメータ}を特定してURLで直接遷移する方法を提供する。
```

## 実装フロー

### オーケストレーターエージェントの典型フロー

```
1. 要件収集
   ↓
2. サブエージェントA起動（並列可能なら並列実行）
   ↓
3. サブエージェントB起動
   ↓
4. 結果統合
   ↓
5. 最終成果物生成
```

### ブラウザテストエージェントの典型フロー

```
1. ブラウザ起動
   ↓
2. 対象ページへ遷移
   ↓
3. 操作実行（フォーム入力、ボタンクリック等）
   ↓
4. 結果検証（表示内容、エラーメッセージ等）
   ↓
5. テストレポート生成
```

## glow-server 固有の考慮事項

### 複数データベース対応

glow-serverは複数のデータベース接続を持つため、明記が必要：

```markdown
## データベース接続

- **MySQL系**: mst、mng、admin（mysqlコンテナ）
- **TiDB系**: usr、log、sys（tidbコンテナ）

マイグレーションやクエリ実行時は適切な接続先を選択。
```

### MCP（Model Context Protocol）活用

ブラウザテストやデータベース操作にはMCPサーバーを活用：

```markdown
## 使用MCP

- **chrome-devtools**: ブラウザ自動操作
- **glow-server-local-db**: ローカルDB直接操作
```

### Docker環境前提

```markdown
## 実行環境

全ての操作はDocker環境で実行：

\`\`\`bash
# コンテナ起動確認
docker compose ps

# sailコマンド経由で実行
sail artisan {command}
sail admin {command}
\`\`\`
```

## 命名規則

### エージェント名

- **データベース系**: `database-{機能}`
  - 例: `database-migration-manager`, `database-query-optimizer`

- **ブラウザテスト系**: `{対象}-browser-tester`
  - 例: `admin-browser-tester`, `frontend-e2e-tester`

- **設計フロー系**: `{設計手法}-{役割}`
  - 例: `sdd-orchestrator`, `api-design-coordinator`

- **設定同期系**: `{対象}-config-synchronizer`
  - 例: `ecs-config-synchronizer`, `env-sync-manager`

### カラー選択基準

| 機能 | カラー | 理由 |
|------|--------|------|
| データベース管理 | `green` | リソース・データ管理 |
| ブラウザテスト | `orange` | 運用・検証 |
| 設計フロー | `yellow` | ドキュメント・設計 |
| 設定同期 | `orange` | 運用・インフラ |

## エージェント作成チェックリスト

運用・管理系エージェントを作成する際の確認項目：

- [ ] 使用するMCPサーバーが明記されている（該当する場合）
- [ ] Docker環境での実行方法が説明されている
- [ ] 複数DB対応の場合、接続先が明確
- [ ] api/adminどちらに対応するか明確
- [ ] エラーハンドリングとロールバック方針が定義されている
- [ ] オーケストレーターの場合、サブエージェントの起動順序が明確
- [ ] ブラウザテストの場合、検証項目が具体的
- [ ] 既存の類似エージェントとの役割分担が明確

## 実装テンプレート

### データベース管理エージェント例

```markdown
---
name: database-migration-manager
description: glow-serverプロジェクトのマイグレーション実行専門エージェント。sail migrateまたはsail admin migrateを実行し、複数DB（mst/mng/usr/log/sys/admin）に対するマイグレーションを管理。エラー発生時はロールバックとデバッグを行う。新規テーブル作成やスキーマ変更時に使用。
model: sonnet
color: green
---

# Database Migration Manager

## 役割と責任

glow-serverプロジェクトのデータベースマイグレーション実行を専門に担当します。

## 基本原則

- 全てのコマンドはDocker環境（sail）で実行
- エラー発生時は自動的にロールバック
- マイグレーション前に必ずバックアップ確認
- 複数DB接続を正しく管理

## 標準作業フロー

### 1. 事前確認

\`\`\`bash
# Docker環境の起動確認
docker compose ps

# 既存マイグレーション状態確認
sail artisan migrate:status
\`\`\`

### 2. マイグレーション実行

\`\`\`bash
# APIディレクトリ
sail migrate

# Adminディレクトリ
sail admin migrate
\`\`\`

### 3. エラーハンドリング

エラー発生時：
1. エラーログを解析
2. 該当マイグレーションファイルを確認
3. 必要に応じてロールバック
4. 修正後に再実行

### 4. 検証

\`\`\`bash
# マイグレーション完了確認
sail artisan migrate:status

# テーブル構造確認（必要に応じて）
\`\`\`

## データベース接続

- **MySQL系**: mst、mng、admin（mysqlコンテナ）
- **TiDB系**: usr、log、sys（tidbコンテナ）

マイグレーションファイルの配置場所に応じて適切な接続先が自動選択されます。

## 品質保証基準

- マイグレーション実行前にテスト環境で確認
- ロールバック可能な状態を常に維持
- 本番環境では必ずバックアップ取得後に実行
- マイグレーション後は関連テストを実行
```

### ブラウザテストエージェント例

```markdown
---
name: admin-browser-tester
description: admin実装や改修後にブラウザで実際に動作確認を行う専門エージェント。chrome-devtools MCPを使用してブラウザ操作を自動化し、ページ遷移、フォーム操作、表示確認などを実施してテスト結果をレポートする。新規Filamentリソース追加、CRUD機能実装、バリデーション変更、表示項目変更など、あらゆるadmin実装後の動作確認をカバーする。
model: sonnet
color: orange
---

# Admin Browser Tester

## 役割と責任

admin管理画面の実装・改修後、ブラウザで実際の動作を自動確認します。

## 基本原則

- chrome-devtools MCPを使用したブラウザ自動操作
- 実際のユーザー操作フローをシミュレート
- 画面表示、バリデーション、データ保存を総合的に検証
- テスト結果は詳細にレポート

## 使用MCP

- **chrome-devtools**: ブラウザの起動、操作、検証

## 標準作業フロー

### 1. ブラウザ起動と認証

\`\`\`
1. ブラウザ起動
2. admin画面（http://localhost:{NGINX_ADMIN_PORT}/admin）へアクセス
3. ログイン処理
\`\`\`

### 2. 対象ページへ遷移

\`\`\`
1. ナビゲーションメニューから対象リソースを選択
2. 一覧ページの表示確認
\`\`\`

### 3. CRUD操作実行

\`\`\`
1. 新規作成（Create）
   - 「新規作成」ボタンクリック
   - フォーム入力
   - バリデーション確認
   - 保存処理

2. 一覧表示（Read）
   - データが正しく表示されているか確認
   - フィルター、ソート機能確認

3. 編集（Update）
   - レコード選択
   - 編集フォーム入力
   - 更新処理確認

4. 削除（Delete）
   - 削除ボタンクリック
   - 確認ダイアログ
   - 削除処理確認
\`\`\`

### 4. 結果検証

- 期待される表示内容と実際の表示を比較
- エラーメッセージの妥当性確認
- データの保存状態を確認

### 5. テストレポート生成

- 実行した操作内容
- 検証結果（成功/失敗）
- スクリーンショット（必要に応じて）
- 発見された問題点

## テスト対象

- Filamentリソースの新規追加
- フォームバリデーション変更
- 表示項目の追加・変更
- カスタムアクション
- リレーション表示

## 品質保証基準

- 全てのCRUD操作が正常に動作すること
- バリデーションが正しく機能すること
- ユーザー体験が損なわれていないこと
```

## 関連スキル

運用・管理系エージェントが参照すべき既存スキル：

- **[migration](../../migration/)** - マイグレーション実装
- **[database-query](../../database-query/)** - データベース操作
- **[admin-browser-tester](../../admin-browser-tester/)** - ブラウザテスト実装（既存の場合）
- **[sail-execution](../../sail-execution/)** - sailコマンドの正しい実行
