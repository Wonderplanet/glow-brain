# Claude Code ガイド相談レポート

**日付**: 2026年01月14日
**トピック**: commandsファイルfrontmatterのallowed-tools設定

## 相談内容

- `allowed-tools`の役割と目的
- `--dangerously-skip-permissions`モード使用時の`allowed-tools`の扱い
- `allowed-tools`を指定する場合のメリット
- `allowed-tools`を指定しない場合のデメリット
- ベストプラクティスと推奨される使い方

## 重要な結論・学び

### 1. allowed-toolsの役割と目的

**allowed-tools**は、commandsファイル（またはSkills）のfrontmatterで指定される、そのコマンド実行時に許可するツールのリストです。

> 出典: [Slash Commands - Claude Code Documentation](https://code.claude.com/docs/en/slash-commands.md)

**設定例**:
```markdown
---
allowed-tools: Bash(git add:*), Bash(git status:*), Bash(git commit:*)
description: Create a git commit
---
```

**ベストプラクティス**:
- コマンドが使用するツールを明示的に指定する
- 最小権限の原則に従い、必要なツールのみに限定する

**注意点**:
- Bashツールを使う場合、`allowed-tools`の指定は必須

---

### 2. --dangerously-skip-permissionsとallowed-toolsの関係

`--dangerously-skip-permissions`と`allowed-tools`は**独立した概念**です。

#### --dangerously-skip-permissionsの役割

> 出典: [CLI Reference - Claude Code Documentation](https://code.claude.com/docs/en/cli-reference.md)

```
--dangerously-skip-permissions: Skip permission prompts (use with caution)
```

これはCLIフラグで、**全てのツール使用時にパーミッション確認をスキップ**します。

#### Permission Modesとの関連

> 出典: [IAM - Claude Code Documentation](https://code.claude.com/docs/en/iam.md)

| Mode | Description |
|------|-------------|
| `bypassPermissions` | Skips all permission prompts (requires safe environment) |

`--dangerously-skip-permissions`は、`bypassPermissions`モードに相当します。

**重要**: `--dangerously-skip-permissions`モード使用時でも、`allowed-tools`は以下の理由で意味を持ちます：

1. **セッション継続時の一貫性** - 権限モードが変わる場合への対応
2. **ドキュメント用途** - コマンドの意図を明示
3. **モード変更時の保険** - 後で権限モードを変更した際に機能

---

### 3. allowed-toolsを指定する場合のメリット

#### メリット1: セキュリティの明示化と文書化

特定のツールのみに限定することで、**そのコマンドが何をするのか**が明確になります。

> 出典: [Skills - Claude Code Documentation](https://code.claude.com/docs/en/skills.md)

引用：
> When this Skill is active, Claude can only use the specified tools (Read, Grep, Glob) without needing to ask for permission. This is useful for:
> - Read-only Skills that shouldn't modify files
> - Skills with limited scope: for example, only data analysis, no file writing
> - Security-sensitive workflows where you want to restrict capabilities

**コード例**:
```markdown
---
allowed-tools: Read, Grep, Glob
description: Analyze codebase (read-only)
---

# Codebase Analysis

This command analyzes the codebase without making any modifications.
```

#### メリット2: 権限モード変更時の保護

将来、`--dangerously-skip-permissions`をやめて通常モードに切り替える場合、`allowed-tools`があれば、設定していたコマンドは意図したツールのみで実行される保証になります。

**移行例**:

現在の使用方法：
```bash
claude --dangerously-skip-permissions
```

将来の設定（settings.json）：
```json
{
  "defaultMode": "dontAsk",
  "permissions": {
    "allow": [
      "Bash(git add:*)",
      "Bash(git commit:*)"
    ]
  }
}
```

#### メリット3: チーム環境での安全性向上

複数人で共有するプロジェクトコマンド（`.claude/commands/`）の場合、`allowed-tools`を指定することで：

- **無意識のツール使用を防止** - コマンド実行者が気づかないうちに危険なツールが使われない
- **インテント明示** - 「このコマンドはファイル読取のみ」という意図が明確

---

### 4. allowed-toolsを指定しない場合のデメリット

#### デメリット1: 権限モード切り替え時の脆弱性

`--dangerously-skip-permissions` → 通常モードへの切り替えで、コマンドが**予期しないツール使用で権限確認ダイアログ**が出るようになります。

**例**（allowed-toolsなし）:
```markdown
---
description: Optimize code
---

# Code Optimization

Optimize the code for better performance.
```

この場合、将来的に権限確認が入ると、実行の度に許可ダイアログが表示されます。

#### デメリット2: チーム間の信頼性低下

`.claude/commands/`で共有するコマンドの場合、`allowed-tools`なしだと：

- チームメンバーが「このコマンドが何をするのか」不明確
- コマンド実行時に予期しないツール使用の可能性
- セキュリティレビューが困難

#### デメリット3: 保守性の低下

時間経過後にコマンドを見返すとき、**どのツールを使うつもりだったのか判断困難**になります。

---

### 5. ベストプラクティスと推奨される使い方

#### 推奨事項1: 権限モードに関わらずallowed-toolsを指定する

`--dangerously-skip-permissions`を使っていても、**コマンドが使用するツールを明示的に指定する**ことは重要です。

> 出典: [Slash Commands - Claude Code Documentation](https://code.claude.com/docs/en/slash-commands.md)

引用：
> You *must* include `allowed-tools` with the `Bash` tool, but you can choose the specific bash commands to allow.

#### 推奨事項2: 最小権限の原則（Principle of Least Privilege）

コマンドで必要なツールのみに限定します：

```markdown
---
allowed-tools: Bash(git add:*), Bash(git status:*), Bash(git commit:*)
description: Create a git commit
---
```

**不要なツール**（例：`Edit`でファイル編集）を含めない。

#### 推奨事項3: 読取専用コマンドは明示的に制限

データ分析や調査用コマンドには：

```markdown
---
allowed-tools: Read, Grep, Glob, Bash(duckdb:*)
description: Analyze CSV data with SQL
---
```

`Edit`や`Bash(git push:*)`など修正系ツールを**明示的に除外**。

#### 推奨事項4: プロジェクトコマンドは必須

`.claude/commands/`にチェックインするコマンド（チーム共有）では`allowed-tools`は**必須**：

```markdown
---
allowed-tools: Bash(npm test:*), Bash(npm run lint:*)
description: Run tests and linting
---

# Test & Lint

Run all tests and linting checks.
```

#### 推奨事項5: 権限モードの移行計画を立てる

現在：
```bash
claude --dangerously-skip-permissions
```

将来（settings.jsonで権限モード設定）：
```json
{
  "defaultMode": "dontAsk",
  "permissions": {
    "allow": [
      "Bash(git add:*)",
      "Bash(git commit:*)"
    ]
  }
}
```

このとき、`allowed-tools`があれば**既存のコマンドは自動的に適応**します。

---

## まとめ表

| 側面 | --dangerously-skip-permissions使用時 | 通常モードへの移行時 |
|------|--------------------------------------|-------------------|
| **allowed-toolsなし** | 問題なし | 権限確認ダイアログが出現→UX低下 |
| **allowed-toolsあり** | セキュリティ意図が明確 | 指定ツールのみで動作→継続性確保 |

---

## 結論

**`--dangerously-skip-permissions`モード使用中であっても、`allowed-tools`を指定することで、セキュリティ、保守性、将来への対応力が向上します。チーム環境では特に重要です。**

## 参照ドキュメント

各トピックで参照した公式ドキュメントのURL一覧：

- [Slash Commands - Claude Code Documentation](https://code.claude.com/docs/en/slash-commands.md)
- [Skills - Claude Code Documentation](https://code.claude.com/docs/en/skills.md)
- [IAM - Claude Code Documentation](https://code.claude.com/docs/en/iam.md)
- [CLI Reference - Claude Code Documentation](https://code.claude.com/docs/en/cli-reference.md)

## 次のアクション

1. 既存のコマンドファイルに`allowed-tools`を追加
2. プロジェクト共有コマンド（`.claude/commands/`）の`allowed-tools`を見直し
3. 将来の権限モード移行計画を検討

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*
