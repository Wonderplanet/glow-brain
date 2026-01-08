# api-fix-sail-check-errors

sail checkで検出される全てのコード品質エラーを自動解消するコマンドです。

---

## 🚨 重要：実行指示（MUST）

**このコマンドを実行する際は、必ず以下の手順でTask toolを使用してサブエージェントを順次呼び出してください。**

サブエージェントを使わずに直接処理することは禁止です。各フェーズで指定されたサブエージェントを**Task tool**で呼び出してください。

---

## 実行手順

### Phase 1: コーディング規約違反の修正（phpcs/phpcbf）

**Task toolで以下のサブエージェントを呼び出す：**

```
Task tool パラメータ:
- subagent_type: "api-phpcs-phpcbf-fixer"
- description: "phpcs/phpcbfエラー修正"
- prompt: "sail phpcs/phpcbfで検出されるコーディング規約違反を全て修正してください。設定ファイルの変更やignoreコメントの使用は禁止です。"
```

**完了条件**: サブエージェントから「phpcsエラー0件」の報告を受けること

---

### Phase 2: 静的解析エラーの修正（phpstan）

**Phase 1完了後、Task toolで以下のサブエージェントを呼び出す：**

```
Task tool パラメータ:
- subagent_type: "api-phpstan-fixer"
- description: "phpstanエラー修正"
- prompt: "sail phpstanで検出される静的解析エラーを全て修正してください。設定ファイルの変更やignoreコメントの使用は禁止です。"
```

**完了条件**: サブエージェントから「phpstanエラー0件」の報告を受けること

---

### Phase 3: アーキテクチャ違反の修正（deptrac）

**Phase 2完了後、Task toolで以下のサブエージェントを呼び出す：**

```
Task tool パラメータ:
- subagent_type: "api-deptrac-fixer"
- description: "deptracエラー修正"
- prompt: "sail deptracで検出されるアーキテクチャ違反を全て修正してください。設定ファイルの変更やignoreコメントの使用は禁止です。"
```

**完了条件**: サブエージェントから「deptracエラー0件」の報告を受けること

---

### Phase 4: テストエラーの修正（phpunit）

**Phase 3完了後、Task toolで以下のサブエージェントを呼び出す：**

```
Task tool パラメータ:
- subagent_type: "api-test-fixer"
- description: "テストエラー修正"
- prompt: "sail phpunitで検出されるテストエラーを全て修正してください。全てのテストが成功するまで修正を続けてください。"
```

**完了条件**: サブエージェントから「全テスト成功」の報告を受けること

---

### Phase 5: 最終確認

**全てのサブエージェントが完了した後、自分で以下を実行：**

```bash
sail check
```

**期待結果**: 全てのチェック（phpcs、phpstan、deptrac、test）がエラー0件で成功

エラーが残っている場合は、該当するフェーズのサブエージェントを再度呼び出す。

---

## サブエージェント呼び出しの具体例

```javascript
// Phase 1: phpcs/phpcbf
Task({
  subagent_type: "api-phpcs-phpcbf-fixer",
  description: "phpcs/phpcbfエラー修正",
  prompt: "sail phpcs/phpcbfで検出されるコーディング規約違反を全て修正してください。..."
})

// Phase 2: phpstan（Phase 1完了後）
Task({
  subagent_type: "api-phpstan-fixer",
  description: "phpstanエラー修正",
  prompt: "sail phpstanで検出される静的解析エラーを全て修正してください。..."
})

// Phase 3: deptrac（Phase 2完了後）
Task({
  subagent_type: "api-deptrac-fixer",
  description: "deptracエラー修正",
  prompt: "sail deptracで検出されるアーキテクチャ違反を全て修正してください。..."
})

// Phase 4: test（Phase 3完了後）
Task({
  subagent_type: "api-test-fixer",
  description: "テストエラー修正",
  prompt: "sail testで検出されるテストエラーを全て修正してください。..."
})
```

---

## 禁止事項

- ❌ サブエージェントを使わずに直接処理すること
- ❌ 設定ファイル（phpcs.xml、phpstan.neon、deptrac.yaml）の変更
- ❌ ignoreコメント・アノテーションの使用
- ❌ エラーが残っている状態で完了とすること

---

## 各サブエージェントの役割

### api-phpcs-phpcbf-fixer
- sail phpcs を実行してコーディング規約違反を検出
- sail phpcbf を実行して自動修正可能なエラーを修正
- 残存するエラーを手動で修正
- 全てのphpcsエラーが解消されるまで繰り返し

### api-phpstan-fixer
- sail phpstan を実行して型エラー・未定義エラーを検出
- 型アノテーション追加、nullチェック追加などでエラーを修正
- 全てのphpstanエラーが解消されるまで繰り返し

### api-deptrac-fixer
- sail deptrac を実行してレイヤー間依存関係違反を検出
- インターフェース導入、依存性注入などでアーキテクチャ違反を修正
- 全てのdeptracエラーが解消されるまで繰り返し

### api-test-fixer
- sail test を実行してテスト失敗を検出
- テストコードまたは実装コードを適切に修正
- 全てのテストが成功するまで繰り返し

---

## 注意事項

### 実行前の確認事項

1. **Dockerコンテナが起動していること**
   ```bash
   docker compose ps
   ```

2. **作業ブランチで実行すること**
   mainブランチやdevelopブランチで直接実行しないこと

### 実行後の推奨事項

1. **変更内容のレビュー**
   ```bash
   git diff
   ```

2. **コミット**
   ```bash
   git add .
   git commit -m "Fix all sail check errors"
   ```

---

## トラブルシューティング

### Q: 一部のエラーだけ解消したい

A: 該当するサブエージェントのみをTask toolで呼び出してください：
- phpcs/phpcbfのみ: `subagent_type: "api-phpcs-phpcbf-fixer"`
- phpstanのみ: `subagent_type: "api-phpstan-fixer"`
- deptracのみ: `subagent_type: "api-deptrac-fixer"`
- testのみ: `subagent_type: "api-test-fixer"`

### Q: サブエージェントが途中で停止した

A: エラーメッセージを確認し、問題を解決後に該当フェーズのサブエージェントを再度呼び出してください。
