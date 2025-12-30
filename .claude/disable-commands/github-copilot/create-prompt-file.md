---
description: GitHub Copilot用プロンプトファイルを作成
allowed-tools: Bash(ls:*), Bash(mkdir:*), Bash(cat:*)
---

# GitHub Copilotプロンプトファイル作成

GitHub Copilot Chatでスラッシュコマンドとして使用できるプロンプトファイルを、公式のベストプラクティスに沿った形式で作成します。

## 公式ドキュメント

このコマンドは以下の公式ドキュメントに基づいています：

- [Your first prompt file - GitHub Docs](https://docs.github.com/en/copilot/tutorials/customization-library/prompt-files/your-first-prompt-file)
- [Use prompt files in VS Code](https://code.visualstudio.com/docs/copilot/customization/prompt-files)
- [5 tips for writing better custom instructions for Copilot](https://github.blog/ai-and-ml/github-copilot/5-tips-for-writing-better-custom-instructions-for-copilot/)
- [GitHub Awesome Copilot - Community Repository](https://github.com/github/awesome-copilot)

**注意**: プロンプトファイルは現在パブリックプレビュー段階で、VS Code、Visual Studio、JetBrains IDEsでのみ利用可能です。

## タスク

このコマンドは対話形式で実行されます。以下のステップに従って、高品質なプロンプトファイルを生成してください。

### 1. 要件の収集

まず、AskUserQuestionツールを使って、プロンプトファイルの詳細を収集してください。

以下の質問を一度に提示してください：

```json
{
  "questions": [
    {
      "question": "このプロンプトは何を実行するためのものですか？",
      "header": "目的",
      "multiSelect": false,
      "options": [
        {
          "label": "コード生成",
          "description": "新しいコードを生成"
        },
        {
          "label": "コードレビュー",
          "description": "既存コードをレビュー"
        },
        {
          "label": "リファクタリング",
          "description": "コードの改善"
        },
        {
          "label": "ドキュメント生成",
          "description": "コメントやドキュメントを作成"
        },
        {
          "label": "テスト生成",
          "description": "ユニットテストやE2Eテストを作成"
        }
      ]
    },
    {
      "question": "どの言語やフレームワークを対象としますか？",
      "header": "対象",
      "multiSelect": false,
      "options": [
        {
          "label": "汎用",
          "description": "言語非依存"
        },
        {
          "label": "TypeScript/JavaScript",
          "description": "Web開発全般"
        },
        {
          "label": "Python",
          "description": "Python関連"
        },
        {
          "label": "PHP/Laravel",
          "description": "Laravel開発"
        },
        {
          "label": "C#/Unity",
          "description": "Unity開発"
        }
      ]
    },
    {
      "question": "プロンプトが処理する範囲は？",
      "header": "範囲",
      "multiSelect": false,
      "options": [
        {
          "label": "現在のファイル",
          "description": "アクティブなファイルのみ"
        },
        {
          "label": "選択範囲",
          "description": "ユーザーが選択したコード"
        },
        {
          "label": "プロジェクト全体",
          "description": "リポジトリ全体を対象"
        },
        {
          "label": "カスタム",
          "description": "特定のディレクトリやパターン"
        }
      ]
    },
    {
      "question": "期待される出力形式は？",
      "header": "出力",
      "multiSelect": false,
      "options": [
        {
          "label": "コードブロック",
          "description": "新しいコードを生成"
        },
        {
          "label": "Markdown",
          "description": "ドキュメントやレポート"
        },
        {
          "label": "提案リスト",
          "description": "複数の改善案"
        },
        {
          "label": "差分表示",
          "description": "変更前後の比較"
        }
      ]
    }
  ]
}
```

### 2. プロンプトファイルの生成

収集した情報に基づいて、以下の構造でプロンプトファイルを作成してください：

#### ファイル構造

プロンプトファイルは、YAMLフロントマターとMarkdown本文で構成されます。

##### フロントマター（必須/推奨フィールド）

```yaml
---
description: [簡潔な説明]
name: [チャットで表示される名前（オプション）]
argument-hint: [引数のヒント（引数がある場合）]
agent: 'agent'  # ask, edit, agent, またはカスタムエージェント
model: 'GPT-4o'  # 使用するモデル（オプション）
tools: ['githubRepo', 'search/codebase']  # 利用可能なツール（オプション）
---
```

**フィールドの説明**:
- `description`: プロンプトの簡潔な説明（必須）
- `name`: チャット内で `/` の後に表示される名前
- `argument-hint`: ユーザー向けの引数ヒント（例: `[feature-name] [priority]`）
- `agent`: 実行エージェント（'agent'が推奨）
- `model`: 使用する言語モデル
- `tools`: 利用可能なツールのリスト

##### 本文構造

```markdown
# [プロンプトのタイトル]

[プロンプトの目的と概要の説明]

## コンテキスト

[対象言語、フレームワーク、処理範囲などの説明]

## タスク

[構造化された明確な指示]

1. **[ステップ1]**
   - [具体的な手順]
   - [期待される動作]

2. **[ステップ2]**
   - [具体的な手順]
   - [期待される動作]

## ベストプラクティス

[適用すべきベストプラクティスやガイドライン]

- [ベストプラクティス1]
- [ベストプラクティス2]

## 出力形式

[期待される出力の形式と構造]

## 注意事項

[重要な制約や考慮事項]
```

##### 動的入力の活用

ユーザーからの入力を受け取る場合、以下の変数を使用できます：

- `${input:variableName}` - ユーザーに入力を求める
- `${file}` - 現在のファイル
- `${selection}` - 選択範囲
- `${workspaceFolder}` - ワークスペースフォルダ

例:
```
このコードを分析してください: ${input:code:Paste your code here}
```

### 3. ファイル名の決定

収集した情報に基づいて、適切なファイル名を決定してください。

**命名パターン**：
- コード生成: `generate-[feature].prompt.md`
- レビュー: `review-[aspect].prompt.md`
- リファクタリング: `refactor-[target].prompt.md`
- ドキュメント: `document-[type].prompt.md`
- テスト: `test-[scope].prompt.md`

**重要**: ファイル名は小文字とハイフンのみを使用してください（例: `create-readme.prompt.md`, `review-security.prompt.md`）

### 4. ディレクトリの確認と作成

プロンプトファイルは `.github/prompts/` ディレクトリに配置します。

1. まず、Bashツールで `.github/prompts` ディレクトリが存在するか確認してください：
   ```bash
   ls -la .github/prompts 2>/dev/null || echo "ディレクトリが存在しません"
   ```

2. ディレクトリが存在しない場合は、作成してください：
   ```bash
   mkdir -p .github/prompts
   ```

### 5. プロンプトファイルの作成

Writeツールを使用して、`.github/prompts/[filename].prompt.md` にプロンプトファイルを作成してください。

**作成時の注意**：
- フロントマターのYAML構文が正しいか確認
- コードブロック内のバッククォートが正しくエスケープされているか確認
- 動的変数（`${input:variableName}`など）の構文が正しいか確認

### 6. 確認と使用方法の提示

ファイル作成後、以下の情報をユーザーに提供してください：

1. **作成されたファイルのパス**
   ```
   .github/prompts/[filename].prompt.md
   ```

2. **GitHub Copilot Chatでの使用方法**
   - VS Codeの場合: Copilot Chatを開いて `/[prompt-name]` と入力
   - 例: `/generate-react-component`

3. **プロンプトの確認方法**
   ```bash
   cat .github/prompts/[filename].prompt.md
   ```

4. **カスタマイズ方法**
   - フロントマターのフィールドを調整（`model`, `tools`など）
   - 動的入力変数を追加して柔軟性を向上
   - ベストプラクティスセクションをプロジェクト固有の内容に更新

## GitHub Copilot プロンプトのベストプラクティス

生成するプロンプトは、以下の公式ベストプラクティスに従ってください：

### 構造

- **明確なタイトル**: プロンプトの目的が一目でわかる
- **詳細な説明**: コンテキストと期待される動作を明記
- **構造化された指示**: ステップバイステップで明確に
- **具体的な出力形式**: 期待される結果を明示

### 内容

- **具体的であること**: 曖昧な表現を避け、具体的な指示を書く
- **コンテキストを提供**: 必要な背景情報を含める
- **制約を明示**: 従うべきルールや制限を明確に
- **例を含める**: 可能な限り具体例を示す

### 品質

- **再現性**: 同じ入力で同じ結果が得られる
- **拡張性**: 類似タスクに応用可能
- **保守性**: 理解しやすく、更新しやすい

## 出力例

### コード生成プロンプトの例

```markdown
---
description: React Componentを生成
agent: 'agent'
model: 'GPT-4o'
tools: ['githubRepo', 'search/codebase']
---

# React TypeScript Component Generator

TypeScript + React + Tailwind CSSを使用した、モダンなReactコンポーネントを生成します。

## コンテキスト

- **言語**: TypeScript
- **フレームワーク**: React 18+
- **スタイリング**: Tailwind CSS
- **範囲**: 単一コンポーネント

## タスク

以下の要件に従って、Reactコンポーネントを生成してください：

1. **型定義**
   - Props interfaceを定義
   - 必要に応じてState型を定義
   - 厳密な型付けを行う

2. **コンポーネント実装**
   - 関数コンポーネントとして実装
   - 適切なReact Hooksを使用
   - アクセシビリティを考慮

3. **スタイリング**
   - Tailwind CSSユーティリティクラスを使用
   - レスポンシブデザインを考慮
   - ダークモード対応

## ベストプラクティス

- Named exportを使用
- PropTypesではなくTypeScriptの型を使用
- useCallback/useMemoで適切にメモ化
- エラーハンドリングを実装
- 適切なaria-*属性を追加

## 出力形式

```typescript
// ComponentName.tsx
import { FC } from 'react';

interface ComponentNameProps {
  // Props definition
}

export const ComponentName: FC<ComponentNameProps> = ({ ... }) => {
  // Component implementation

  return (
    <div className="...">
      {/* JSX */}
    </div>
  );
};
```

## 注意事項

- コンポーネント名はPascalCaseで
- ファイル名もPascalCaseで統一
- 不要なコメントは含めない
- ESLintルールに準拠
```

### コードレビュープロンプトの例

```markdown
---
description: セキュリティ観点でコードレビュー
agent: 'agent'
---

# Security Code Review

選択されたコードをセキュリティの観点から包括的にレビューします。

## コンテキスト

- **範囲**: 選択範囲
- **言語**: 全言語対応
- **焦点**: セキュリティ脆弱性の検出

## タスク

以下の観点で選択されたコードを分析してください：

1. **入力検証**
   - ユーザー入力が適切に検証されているか
   - サニタイズ処理が実装されているか
   - 型チェックが適切か

2. **認証・認可**
   - 認証メカニズムは適切か
   - 権限チェックは十分か
   - セッション管理は安全か

3. **データ保護**
   - SQLインジェクションのリスクはないか
   - XSS脆弱性はないか
   - CSRF対策は実装されているか
   - 機密データが露出していないか

4. **暗号化**
   - パスワードは適切にハッシュ化されているか
   - 通信は暗号化されているか
   - 暗号化アルゴリズムは安全か

5. **エラーハンドリング**
   - エラーメッセージに機密情報が含まれていないか
   - 適切なログ記録がされているか
   - エラーが適切に処理されているか

## ベストプラクティス

OWASP Top 10に基づいた分析を行う：
- A01: Broken Access Control
- A02: Cryptographic Failures
- A03: Injection
- A04: Insecure Design
- A05: Security Misconfiguration
- A06: Vulnerable and Outdated Components
- A07: Identification and Authentication Failures
- A08: Software and Data Integrity Failures
- A09: Security Logging and Monitoring Failures
- A10: Server-Side Request Forgery

## 出力形式

各発見事項について以下の形式で報告：

### [脆弱性の種類]

**リスクレベル**: 🔴 高 / 🟡 中 / 🟢 低

**場所**:
- ファイル: [filename]
- 行: [line number]

**説明**:
[問題の詳細な説明]

**影響**:
[この脆弱性が悪用された場合の影響]

**修正提案**:
```[language]
// 修正後のコード
```

**参考**:
[関連するOWASPやCWEへのリンク]

## 注意事項

- False positiveを避けるため、コンテキストを考慮
- フレームワークの組み込みセキュリティ機能を認識
- リスクレベルは実際の影響度に基づいて判断
- 修正提案は実装可能で具体的に
```

---

上記のベストプラクティスとテンプレートに従って、ユーザーの要件に最適なGitHub Copilotプロンプトファイルを作成してください。
