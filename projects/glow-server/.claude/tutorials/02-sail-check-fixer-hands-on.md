# ハンズオン: コード品質チェック (`sail-check-fixer`)

このハンズオンでは、Claude Codeを使ってPR作成前のコード品質チェック（phpcs/phpstan/deptrac）を実行し、エラーを自動修正する方法を学びます。

## このハンズオンで学べること

- `sail check` で検出される3種類のエラーの理解
- `/project:api-fix-sail-check-errors` コマンドの使い方
- 個別エージェントを使った部分修正
- PR作成前の品質チェックフロー

## 所要時間

約20分

---

## 事前知識: 3つの品質チェックツール

glow-serverでは、以下の3つのツールでコード品質をチェックしています：

| ツール | 役割 | チェック内容 |
|--------|------|-------------|
| **phpcs/phpcbf** | コーディング規約 | PSR-12準拠、インデント、空白、命名規則など |
| **phpstan** | 静的解析 | 型エラー、未定義変数、null安全性など |
| **deptrac** | アーキテクチャ | レイヤー間の依存関係違反 |

これらは `sail check` コマンドで一括実行されます。

---

## 事前準備

### 1. Dockerコンテナが起動していることを確認

```bash
./tools/bin/sail-wp ps
```

### 2. 現在のコード品質状態を確認

手動で確認したい場合：
```bash
./tools/bin/sail-wp check
```

---

## ハンズオン手順

### Step 1: 全品質チェックを一括実行・修正

最もシンプルな使い方です。Claude Codeに以下のように入力してください：

```
/project:api-fix-sail-check-errors
```

**何が起こるか：**
1. `sail check` が実行される
2. 3つのツール（phpcs, phpstan, deptrac）のエラーが検出される
3. 検出されたエラーを順番に自動修正
4. 再度 `sail check` を実行して確認
5. 全エラーが解消されるまで繰り返し

**期待される最終出力：**
```
phpcs: OK
phpstan: OK (0 errors)
deptrac: OK (0 violations)
```

### Step 2: 自然言語での指示

コマンドを使わずに、自然言語で指示することもできます：

```
「sail checkを実行してエラーを全部修正して」
「コード品質チェックして問題があれば直して」
「PR作成前のチェックをして」
```

---

## 個別ツールの使い方

### phpcs/phpcbf だけを修正したい場合

```
「phpcsエラーを修正して」
```

Claude Codeは `api-phpcs-phpcbf-fixer` エージェントを起動し、コーディング規約違反のみを修正します。

**よくあるphpcsエラー例：**
```
ERROR: Line exceeds 120 characters
ERROR: Expected 1 space after comma
ERROR: Missing doc comment for function
```

### phpstan だけを修正したい場合

```
「phpstanエラーを修正して」
```

Claude Codeは `api-phpstan-fixer` エージェントを起動し、型エラーのみを修正します。

**よくあるphpstanエラー例：**
```
Property has no type specified
Method should return int but returns string
Cannot call method on possibly null value
```

### deptrac だけを修正したい場合

```
「deptracエラーを修正して」
```

Claude Codeは `api-deptrac-fixer` エージェントを起動し、アーキテクチャ違反のみを修正します。

**よくあるdeptracエラー例：**
```
Controller depends on Repository (should use UseCase)
Domain A directly accesses Domain B (should use Delegator)
```

---

## 実践シナリオ

### シナリオA: 新しいAPIを実装した後

新しいコントローラーやUseCaseを追加した後：

```
「実装したコードの品質チェックをして、問題があれば修正して」
```

**Claude Codeの動作：**
1. `sail check` を実行
2. 新規ファイルで発生しているエラーを検出
3. 型アノテーション追加、コーディング規約修正などを実施
4. 再チェックして確認

### シナリオB: PRレビューで指摘を受けた場合

レビューで「型アノテーションが不足している」と指摘された場合：

```
「phpstanエラーをチェックして修正して」
```

### シナリオC: CIで品質チェックが失敗した場合

GitHub ActionsなどのCIで品質チェックが失敗した場合：

```
「CIでsail checkが失敗しているので修正して」
```

---

## 各エラーの理解と修正例

### 1. phpcsエラーの修正例

**エラー:**
```
Missing @return tag in function comment
```

**修正前:**
```php
/**
 * Get user by ID.
 */
public function getUser(int $id): ?User
```

**修正後:**
```php
/**
 * Get user by ID.
 *
 * @return User|null
 */
public function getUser(int $id): ?User
```

### 2. phpstanエラーの修正例

**エラー:**
```
Cannot call method getName() on User|null
```

**修正前:**
```php
$user = $this->repository->find($id);
return $user->getName();
```

**修正後:**
```php
$user = $this->repository->find($id);
if ($user === null) {
    throw new UserNotFoundException("User not found: {$id}");
}
return $user->getName();
```

### 3. deptracエラーの修正例

**エラー:**
```
StageController depends on StageRepository (Violation)
```

**修正前:**
```php
class StageController
{
    public function __construct(
        private StageRepository $repository  // 直接依存はNG
    ) {}
}
```

**修正後:**
```php
class StageController
{
    public function __construct(
        private StageEndUseCase $useCase  // UseCase経由でアクセス
    ) {}
}
```

---

## よくある質問と対処法

### Q1: 修正してもエラーが消えない

**対処法：**
```
「残っているエラーの詳細を教えて」
「このエラーの原因を調査して」
```

複雑なケースでは、設計の見直しが必要な場合もあります。

### Q2: 自動修正の内容を確認したい

**対処法：**
```
「何を修正したか一覧で教えて」
「変更したファイルを見せて」
```

### Q3: 特定のファイルだけチェックしたい

**対処法：**
```
「StageEndController.phpの品質チェックをして」
「app/Domain/Stage/配下のファイルをチェックして」
```

### Q4: エラーの意味がわからない

**対処法：**
```
「このphpstanエラーの意味を説明して」
「なぜこのdeptracエラーが出るのか教えて」
```

---

## 禁止事項（重要）

Claude Codeは以下の方法でエラーを「無視」することは**絶対にしません**：

| 禁止事項 | 理由 |
|---------|------|
| phpstan.neonでエラーレベルを下げる | 根本解決にならない |
| `@phpstan-ignore-next-line` を追加 | エラーを隠蔽しているだけ |
| deptrac.yamlで例外を追加 | アーキテクチャが崩れる |
| phpcs.xmlでルールを除外 | コーディング規約の統一性が損なわれる |

**全てのエラーは適切なコード修正で解決します。**

---

## PR作成前の推奨フロー

1. **実装完了**
   ```
   「○○機能の実装が完了しました」
   ```

2. **テスト実行**
   ```
   /project:api-test
   ```

3. **品質チェック**
   ```
   /project:api-fix-sail-check-errors
   ```

4. **最終確認**
   ```
   「sail checkとテストが全部通ることを確認して」
   ```

5. **PR作成**
   ```
   「PRを作成して」
   ```

---

## 関連するスキル

| スキル | 用途 |
|--------|------|
| `sail-check-fixer` | 品質チェック全体の詳細ガイド |
| `sail-execution` | sailコマンドの正しい実行方法 |

詳細なガイドは以下を参照：
- [sail-check-fixer スキル](../skills/sail-check-fixer/SKILL.md)

---

## まとめ

| 目的 | コマンド/指示 |
|------|--------------|
| 全品質チェック＆修正 | `/project:api-fix-sail-check-errors` |
| phpcsのみ修正 | 「phpcsエラーを修正して」 |
| phpstanのみ修正 | 「phpstanエラーを修正して」 |
| deptracのみ修正 | 「deptracエラーを修正して」 |
| 自然言語での指示 | 「sail checkして問題を修正して」 |

---

## 次のステップ

- [ハンズオン: テスト実行・自動修正](./01-api-test-hands-on.md) - テスト実行の詳細を学ぶ
- [sail-check-fixer スキル](../skills/sail-check-fixer/SKILL.md) - 各ツールの詳細なガイド
- [アーキテクチャドキュメント](../../docs/01_project/architecture/) - deptracエラー理解のための設計知識
