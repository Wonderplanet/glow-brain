# CodeRabbit設定レビュー・調整コマンド

CodeRabbitの設定ファイル（`.coderabbit.yaml`）をレビュー・調整するコマンドです。

---

## 使用方法

```text
/general:review-coderabbit-config [対象ファイルパス]
```

- 対象ファイルパスを省略した場合、リポジトリルートの`.coderabbit.yaml`を対象とします

---

## 実行内容

引数: $ARGUMENTS

**このコマンドは `coderabbit-config-reviewer` サブエージェントを呼び出します。**

---

## 実行指示

### Task toolで以下のサブエージェントを呼び出す：

```
Task tool パラメータ:
- subagent_type: "coderabbit-config-reviewer"
- description: "CodeRabbit設定レビュー"
- prompt: |
    CodeRabbitの設定ファイルをレビュー・調整してください。

    対象ファイル: $ARGUMENTS（省略時は .coderabbit.yaml）

    ## レビュー観点

    1. **構文・スキーマ検証**
       - YAMLシンタックスエラーがないか
       - スキーマ定義に準拠しているか（https://coderabbit.ai/integrations/schema.v2.json）

    2. **ツール統合確認**
       - PHPStan、PHPMD、Gitleaks等が適切に設定されているか
       - glow-serverプロジェクトに必要なツールが有効か

    3. **パスベースレビュー指示**
       - api/**/*.php、admin/**/*.php等のパス指示が効果的か
       - プロジェクト構造に合った指示になっているか

    4. **ベストプラクティス準拠**
       - 公式ドキュメント（https://docs.coderabbit.ai/reference/configuration）の推奨設定に準拠しているか

    ## 期待する成果物

    - 現状の問題点と改善提案のレポート
    - 必要に応じて設定ファイルの更新
```

---

## 完了条件

サブエージェントから以下の報告を受けること：

1. 設定ファイルのレビュー結果
2. 発見された問題点（あれば）
3. 改善提案または実施した修正内容

---

## サブエージェント呼び出しの具体例

```javascript
Task({
  subagent_type: "coderabbit-config-reviewer",
  description: "CodeRabbit設定レビュー",
  prompt: `CodeRabbitの設定ファイルをレビュー・調整してください。

    対象ファイル: .coderabbit.yaml

    既存のcoderabbit関連ドキュメントは参照せず、公式ドキュメントから最新情報を取得して
    レビュー・調整を実施してください。

    参考リンク:
    - 設定リファレンス: https://docs.coderabbit.ai/reference/configuration
    - YAMLテンプレート: https://docs.coderabbit.ai/reference/yaml-template
    - スキーマ定義: https://coderabbit.ai/integrations/schema.v2.json`
})
```

---

## 注意事項

### 基本原則

- **公式ドキュメント優先**: 既存のプロジェクトドキュメントではなく、常に公式ドキュメントから最新情報を取得
- **逐次情報取得**: 必要な情報はWebFetchで公式サイトから取得
- **スキーマバリデーション**: 設定ファイルには必ずスキーマ参照を含める

### 禁止事項

- 既存のcoderabbit関連のプロジェクトドキュメントを参照すること
- 古い設定例やサンプルをそのまま適用すること
- 公式ドキュメントを確認せずに設定を変更すること

---

## 関連リソース

### 公式ドキュメント
- [Configuration Reference](https://docs.coderabbit.ai/reference/configuration)
- [YAML Template](https://docs.coderabbit.ai/reference/yaml-template)
- [Schema Definition](https://coderabbit.ai/integrations/schema.v2.json)
- [Official Documentation](https://docs.coderabbit.ai/)

### サブエージェント
- `.claude/agents/coderabbit-config-reviewer.md`
