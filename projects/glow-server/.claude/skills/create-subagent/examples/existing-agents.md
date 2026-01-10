# 既存エージェントの実例

このドキュメントでは、glow-serverプロジェクトで実際に使用されている既存サブエージェントの実例を紹介します。新しいエージェントを作成する際の参考にしてください。

## API開発系エージェント

### api-phpstan-fixer

**カテゴリ**: テスト・品質管理
**model**: sonnet
**color**: purple

```markdown
---
name: api-phpstan-fixer
description: PHPStan静的解析エラーを検出・修正するサブエージェント。sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する。設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用。Examples: <example>Context: sail checkでphpstanエラーが発生 user: 'phpstanエラーを全て修正して' assistant: 'api-phpstan-fixerエージェントを使用して静的解析エラーを解消します' <commentary>phpstanエラーの解消が必要なため、このエージェントを使用</commentary></example>
model: sonnet
color: purple
---
```

**学べるポイント:**
- ✅ トリガー条件が明確（「sail checkでphpstanエラーが出た時」）
- ✅ 制約事項を明記（「設定変更によるエラー無視は禁止」）
- ✅ エラー種別を具体的に列挙（型エラー・未定義変数・メソッド呼び出しエラー）
- ✅ Examples セクションで具体的な使用例を提供

### api-phpcs-phpcbf-fixer

**カテゴリ**: テスト・品質管理
**model**: haiku
**color**: purple

```markdown
---
name: api-phpcs-phpcbf-fixer
description: PHPコーディング規約違反を自動検出・修正するサブエージェント。sail phpcs→sail phpcbfの順で実行し、全てのコーディングスタイルエラーを解消する。sail checkコマンドでphpcs/phpcbfエラーが出た時や、コード整形が必要な時に使用。Examples: <example>Context: sail checkでphpcsエラーが発生 user: 'phpcsエラーを全て修正して' assistant: 'api-phpcs-phpcbf-fixerエージェントを使用してコーディング規約違反を解消します' <commentary>phpcs/phpcbfエラーの解消が必要なため、このエージェントを使用</commentary></example>
model: haiku
color: purple
---
```

**学べるポイント:**
- ✅ 軽量タスクには `haiku` モデルを使用
- ✅ 実行順序を明記（phpcs→phpcbf）
- ✅ 自動修正可能な作業はhaikuで十分

### api-deptrac-fixer

**カテゴリ**: テスト・品質管理
**model**: sonnet
**color**: purple

```markdown
---
name: api-deptrac-fixer
description: Deptracアーキテクチャ違反を検出・修正するサブエージェント。sail deptracを実行し、レイヤー間の不正な依存関係やアーキテクチャ違反を全て解消する。設定変更によるエラー無視は禁止。sail checkコマンドでdeptracエラーが出た時や、アーキテクチャの整合性を確保したい時に使用。Examples: <example>Context: sail checkでdeptracエラーが発生 user: 'deptracエラーを全て修正して' assistant: 'api-deptrac-fixerエージェントを使用してアーキテクチャ違反を解消します' <commentary>deptracエラーの解消が必要なため、このエージェントを使用</commentary></example>
model: sonnet
color: purple
---
```

**学べるポイント:**
- ✅ アーキテクチャ検証の重要性を強調
- ✅ 設定変更によるエラー無視を禁止
- ✅ レイヤー間の依存関係という概念を明記

### api-sequence-diagram-generator

**カテゴリ**: ドキュメント・可視化
**model**: sonnet
**color**: yellow

```markdown
---
name: api-sequence-diagram-generator
description: APIエンドポイントの詳細なシーケンス図（Mermaid形式）を生成するエージェント。ルーティングからレスポンスまでのコードフローを解析し、包括的なドキュメントを作成。Examples: <example>Context: ユーザーがステージ終了APIのシーケンス図を作成したい user: '/api/stage/endのシーケンス図を作成して' assistant: 'api-sequence-diagram-generatorエージェントを使用して、ステージ終了APIの詳細なシーケンス図を生成します' <commentary>ユーザーがAPIエンドポイントのシーケンス図生成を要求しているため、このエージェントを使用</commentary></example>
model: sonnet
color: yellow
---
```

**学べるポイント:**
- ✅ ドキュメント生成には `yellow` カラーを使用
- ✅ 具体的なAPI例（/api/stage/end）を提示
- ✅ 出力形式を明記（Mermaid形式）

## Admin・管理ツール系エージェント

### admin-browser-tester

**カテゴリ**: 運用・テスト
**model**: sonnet
**color**: orange

```markdown
---
name: admin-browser-tester
description: admin実装や改修後にブラウザで実際に動作確認を行う専門エージェント。chrome-devtools MCPを使用してブラウザ操作を自動化し、ページ遷移、フォーム操作、表示確認などを実施してテスト結果をレポートする。新規Filamentリソース追加、CRUD機能実装、バリデーション変更、表示項目変更など、あらゆるadmin実装後の動作確認をカバーする。Examples: <example>Context: ユーザーが新しいFilamentリソース（Productsリソース）を追加した user: 'Products管理画面を追加したので動作確認してください' assistant: 'admin-browser-testerエージェントを使用してProducts管理画面の動作確認を実施します'</example>
model: sonnet
color: orange
---
```

**学べるポイント:**
- ✅ 使用MCPを明記（chrome-devtools）
- ✅ 対象とする操作を具体的に列挙（ページ遷移、フォーム操作、表示確認）
- ✅ 運用系エージェントは `orange` カラー
- ✅ Filament固有の用語（リソース）を使用

## SDD設計フロー系エージェント

### sdd-orchestrator

**カテゴリ**: 設計フロー
**model**: opus
**color**: yellow

```markdown
---
name: sdd-orchestrator
description: SDD設計フローの全段階をオーケストレートする。複数の専門エージェント（sdd-extract-server-requirements、sdd-review-server-spec、sdd-finalize-server-requirements等）を順次または並列実行し、最終的なSDD仕様書を生成する。スタミナブーストのような新機能のSDD作成時に使用。
model: opus
color: yellow
---
```

**学べるポイント:**
- ✅ 複雑なオーケストレーションには `opus` モデルを使用
- ✅ サブエージェント名を具体的に列挙
- ✅ 実行方法を明記（順次または並列）
- ✅ 具体的な使用例（スタミナブースト）を提示

### sdd-extract-server-requirements

**カテゴリ**: 設計フロー
**model**: sonnet
**color**: yellow

```markdown
---
name: sdd-extract-server-requirements
description: ゲーム体験仕様書PDFからサーバー側で考慮すべき要件を抽出する専門エージェント。/sdd:extract-server-requirementsコマンドで自動起動される。
model: sonnet
color: yellow
---
```

**学べるポイント:**
- ✅ スラッシュコマンドとの連携を明記
- ✅ 入力形式（PDF）と出力内容（サーバー要件）を明確化
- ✅ 設計プロセスの一部として位置づけ

## 実装パターン別の比較

### 軽量タスク vs 重量タスク

| エージェント | model | 理由 |
|-------------|-------|------|
| api-phpcs-phpcbf-fixer | haiku | コーディングスタイル修正は自動化可能で軽量 |
| api-phpstan-fixer | sonnet | 型エラー修正には判断力が必要 |
| sdd-orchestrator | opus | 複数エージェントの統括には高度な判断が必要 |

### 色の使い分け

| カラー | 用途 | 例 |
|--------|------|-----|
| purple | API・コア機能開発 | api-phpstan-fixer, api-deptrac-fixer |
| blue | テスト・品質管理 | （該当なし、purpleと統合） |
| yellow | ドキュメント・設計 | api-sequence-diagram-generator, sdd-orchestrator |
| orange | 運用・管理 | admin-browser-tester |
| green | データベース・リソース | （該当なし） |

## description のベストプラクティス実例

### ✅ 良い例（api-phpstan-fixer）

```yaml
description: PHPStan静的解析エラーを検出・修正するサブエージェント。sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する。設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用。
```

**理由:**
1. **機能が明確**: PHPStan静的解析エラーの検出・修正
2. **実行内容が具体的**: sail phpstanを実行
3. **エラー種別を列挙**: 型エラー・未定義変数・メソッド呼び出しエラー
4. **制約を明記**: 設定変更によるエラー無視は禁止
5. **トリガー条件が明確**: sail checkでエラーが出た時、型安全性確保時

### ❌ 悪い例（改善が必要）

```yaml
description: コードの品質を改善するエージェント
```

**問題点:**
1. 何をするか不明確
2. トリガー条件がない
3. 具体的な動作内容がない
4. 制約事項がない

## Examples セクションの活用

### 標準フォーマット

```yaml
Examples: <example>Context: {状況説明} user: '{ユーザーの要求}' assistant: '{エージェント起動メッセージ}' <commentary>{エージェント起動理由}</commentary></example>
```

### 実例（api-phpstan-fixer）

```yaml
Examples: <example>Context: sail checkでphpstanエラーが発生 user: 'phpstanエラーを全て修正して' assistant: 'api-phpstan-fixerエージェントを使用して静的解析エラーを解消します' <commentary>phpstanエラーの解消が必要なため、このエージェントを使用</commentary></example>
```

### 複数例を含める場合

```yaml
Examples: <example>Context: 新規API実装後のテスト user: '新しいAPIのテストを実行して' assistant: 'api-test-runnerエージェントでテストを実行します'</example> <example>Context: PR作成前の品質チェック user: 'テストが通るか確認して' assistant: 'api-test-runnerエージェントで全テストを実行し、失敗があれば修正します'</example>
```

## glow-server プロジェクト固有の慣習

### sail コマンドの記載

```yaml
# ✅ 正しい
description: sail phpstanを実行し...

# ❌ 間違い
description: phpstanを実行し...
```

### ディレクトリの明記

```yaml
# ✅ 正しい（api/admin両対応の場合）
description: ...sail migrateまたはsail admin migrateを実行し...

# ✅ 正しい（api専用の場合）
description: ...APIディレクトリのマイグレーションを実行し...
```

### データベース接続の明記

```yaml
# ✅ 正しい
description: ...複数DB（mst/mng/usr/log/sys/admin）に対する...
```

## チェックリスト

既存エージェントから学んだベストプラクティスのチェックリスト：

- [ ] description が機能とトリガー条件を明確に説明している
- [ ] エラー種別や操作内容が具体的に列挙されている
- [ ] 制約事項が明記されている（必要な場合）
- [ ] Examples セクションで具体的な使用例を提供している
- [ ] sail コマンドを正しく記載している
- [ ] api/admin どちらに対応するか明確
- [ ] 使用MCPが明記されている（該当する場合）
- [ ] 適切なmodelが選択されている（haiku/sonnet/opus）
- [ ] 適切なcolorが選択されている
- [ ] 既存エージェントと役割が重複していない

## 関連リソース

- **[subagent-structure.md](../subagent-structure.md)** - 基本構造と必須フィールド
- **[patterns/api-development.md](../patterns/api-development.md)** - API開発系パターン
- **[patterns/testing.md](../patterns/testing.md)** - テスト・品質管理系パターン
- **[patterns/operations.md](../patterns/operations.md)** - 運用・管理系パターン
