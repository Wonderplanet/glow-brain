# テスト・品質管理系エージェントパターン

このドキュメントでは、テスト実行、コード品質チェック、エラー修正に関連するサブエージェントの実装パターンを説明します。

## パターン分類

### 1. 静的解析エラー修正エージェント

PHPStanなどの静的解析ツールのエラーを自動修正。

**推奨設定:**
- **model**: `sonnet`
- **color**: `blue`

**description テンプレート:**
```yaml
description: |
  {ツール名}静的解析エラーを検出・修正するサブエージェント。{実行コマンド}を実行し、{エラー種別1}・{エラー種別2}・{エラー種別3}等の全ての{ツール名}エラーを解消する。設定変更によるエラー無視は禁止。{トリガー条件}時に使用。

  Examples:
  <example>Context: {具体的な状況} user: '{ユーザー要求}' assistant: '{エージェント起動メッセージ}' <commentary>{起動理由}</commentary></example>
```

**実装例:**
```markdown
---
name: api-phpstan-fixer
description: PHPStan静的解析エラーを検出・修正するサブエージェント。sail phpstanを実行し、型エラー・未定義変数・メソッド呼び出しエラー等の全ての静的解析エラーを解消する。設定変更によるエラー無視は禁止。sail checkコマンドでphpstanエラーが出た時や、型安全性を確保したい時に使用。Examples: <example>Context: sail checkでphpstanエラーが発生 user: 'phpstanエラーを全て修正して' assistant: 'api-phpstan-fixerエージェントを使用して静的解析エラーを解消します' <commentary>phpstanエラーの解消が必要なため、このエージェントを使用</commentary></example>
model: sonnet
color: blue
---
```

### 2. コーディング規約修正エージェント

phpcs/phpcbfなどのコーディングスタイルチェッカーのエラーを自動修正。

**推奨設定:**
- **model**: `haiku` または `sonnet`
- **color**: `blue`

**description テンプレート:**
```yaml
description: |
  {言語}コーディング規約違反を自動検出・修正するサブエージェント。{検出コマンド}→{修正コマンド}の順で実行し、全ての{規約名}エラーを解消する。{トリガー条件}時に使用。
```

**実装例:**
```markdown
---
name: api-phpcs-phpcbf-fixer
description: PHPコーディング規約違反を自動検出・修正するサブエージェント。sail phpcs→sail phpcbfの順で実行し、全てのコーディングスタイルエラーを解消する。sail checkコマンドでphpcs/phpcbfエラーが出た時や、コード整形が必要な時に使用。
model: haiku
color: blue
---
```

### 3. アーキテクチャ検証エージェント

Deptracなどのアーキテクチャルールチェッカーの違反を修正。

**推奨設定:**
- **model**: `sonnet`
- **color**: `blue`

**description テンプレート:**
```yaml
description: |
  {ツール名}アーキテクチャ違反を検出・修正するサブエージェント。{実行コマンド}を実行し、レイヤー間の不正な依存関係やアーキテクチャ違反を全て解消する。設定変更によるエラー無視は禁止。{トリガー条件}時に使用。
```

### 4. テスト実行・修正エージェント

ユニットテスト、機能テストの実行とエラー修正。

**推奨設定:**
- **model**: `sonnet`
- **color**: `blue`

**description テンプレート:**
```yaml
description: |
  {テストフレームワーク}テストを実行して失敗しているものがあれば自動修正する専門エージェント。{実行コマンド}でテストを実行し、{エラー種別1}、{エラー種別2}、{エラー種別3}などの失敗パターンを分析して自動的にコードを修正し、全テストが通る状態にする。{トリガー条件}時に使用。
```

**実装例:**
```markdown
---
name: api-test-runner
description: API開発時にPHPUnitテストを実行して失敗しているものがあれば自動修正する必要がある時に使用。sail phpunitコマンドでテストを実行し、アサーション失敗、例外エラー、DB関連エラー、モック期待値不一致などの失敗パターンを分析して自動的にコードを修正し、全テストが通る状態にする。
model: sonnet
color: blue
---
```

## 実装フロー

### 典型的な品質チェックフロー

```
1. コーディング規約チェック (phpcs/phpcbf)
   ↓
2. 静的解析 (phpstan)
   ↓
3. アーキテクチャ検証 (deptrac)
   ↓
4. ユニットテスト実行 (phpunit)
   ↓
5. 統合テスト実行
```

### エージェント起動の優先順位

1. **コーディング規約修正** - 最も単純、自動修正可能
2. **静的解析エラー修正** - 型安全性の確保
3. **アーキテクチャ違反修正** - 設計原則の遵守
4. **テスト修正** - 機能の正確性保証

## glow-server 固有の考慮事項

### sail check コマンド

glow-serverでは `sail check` コマンドで全ての品質チェックを実行：

```bash
sail check
# 実行内容:
# - phpcs (コーディング規約)
# - phpcbf (自動修正)
# - phpstan (静的解析)
# - deptrac (アーキテクチャ)
# - phpunit (テスト)
```

エージェントはこのコマンド実行後のエラーに対応します。

### api/admin 両対応

品質チェックエージェントはapi/adminの両ディレクトリに対応する必要があります：

```markdown
## 実行対象

- **api**: `sail check` → `sail phpstan` / `sail test`
- **admin**: `sail admin check` → `sail admin phpstan` / `sail admin test`
```

### エラー無視の禁止

**重要**: 品質チェックエージェントは設定ファイルを変更してエラーを無視することは禁止：

```markdown
## 基本原則

- ❌ phpstan.neon で `ignoreErrors` を追加しない
- ❌ .phpcs.xml で `exclude-pattern` を追加しない
- ❌ deptrac.yaml でルールを緩和しない
- ✅ コードを修正してエラーを根本的に解消する
```

## 命名規則

### エージェント名

- **パターン**: `{対象}-{ツール名}-fixer`
- **例**:
  - `api-phpstan-fixer`
  - `api-phpcs-phpcbf-fixer`
  - `api-deptrac-fixer`
  - `api-test-runner`

### カラー選択

テスト・品質管理系エージェントは基本的に **`blue`** を使用。

## エージェント作成チェックリスト

テスト・品質管理系エージェントを作成する際の確認項目：

- [ ] 実行コマンドが明記されている（sail check、sail phpstan等）
- [ ] エラー種別が具体的に列挙されている
- [ ] 「設定変更によるエラー無視は禁止」が明記されている
- [ ] トリガー条件が明確（「sail checkでエラーが出た時」等）
- [ ] api/adminどちらに対応するか明確
- [ ] Examples セクションで具体的な使用例を提供
- [ ] エラー修正の方針（根本的解決）が説明されている

## 実装テンプレート

### 完全なエージェント例

```markdown
---
name: api-eslint-fixer
description: JavaScript/TypeScriptのESLintエラーを検出・修正するサブエージェント。npm run lintを実行し、構文エラー・未使用変数・インポート順序違反等の全てのESLintエラーを解消する。設定変更によるエラー無視は禁止。コード品質チェック時やPR作成前に使用。Examples: <example>Context: npm run lintでエラーが発生 user: 'ESLintエラーを全て修正して' assistant: 'api-eslint-fixerエージェントを使用してコード品質エラーを解消します' <commentary>ESLintエラーの解消が必要なため、このエージェントを使用</commentary></example>
model: sonnet
color: blue
---

# ESLint Fixer

## 役割と責任

JavaScriptとTypeScriptコードのESLintエラーを自動的に検出・修正します。

## 基本原則

- 全てのESLintエラーを根本的に修正
- .eslintrc の設定変更によるエラー無視は禁止
- 自動修正可能なエラーは `--fix` オプションで修正
- 手動修正が必要なエラーはコードを適切に変更

## 標準作業フロー

### 1. エラー検出

\`\`\`bash
npm run lint
\`\`\`

### 2. 自動修正

\`\`\`bash
npm run lint -- --fix
\`\`\`

### 3. 残存エラーの手動修正

自動修正できなかったエラーを解析し、コードを修正

### 4. 再検証

\`\`\`bash
npm run lint
\`\`\`

全てのエラーが解消されるまで繰り返し

## エラー種別と対処方法

### 構文エラー
- コードの構文を修正

### 未使用変数
- 変数を削除、または使用箇所を追加

### インポート順序違反
- import文を正しい順序に並び替え

### 型エラー
- 適切な型注釈を追加

## 品質保証基準

- ESLintエラーが0件になること
- コードの機能が変更されていないこと
- 既存テストが全て通ること
```

## 関連スキル

テスト・品質管理系エージェントが参照すべき既存スキル：

- **[sail-execution](../../sail-execution/)** - sailコマンドの正しい実行方法
- **[sail-check-fixer](../../sail-check-fixer/)** - sail check全体の統合修正
- **[api-test-implementation](../../api-test-implementation/)** - テスト実装パターン
