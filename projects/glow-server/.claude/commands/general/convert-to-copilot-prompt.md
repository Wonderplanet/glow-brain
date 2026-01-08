---
description: Claude Codeのカスタムコマンドを GitHub Copilot用プロンプトファイルに変換する。
argument-hint: "{変換対象のパス}"
---

# Claude Code コマンドを GitHub Copilot プロンプトに変換

Claude Codeのカスタムスラッシュコマンド（`.claude/commands/`配下の`.md`ファイル）を、GitHub Copilot用のプロンプトファイル（`.github/prompts/`配下の`.prompt.md`ファイル）に変換します。

## 使用方法

```
/general:convert-to-copilot-prompt {変換対象のパス}
```

例:
- `/general:convert-to-copilot-prompt .claude/commands/sdd/` - ディレクトリ内の全ファイルを変換
- `/general:convert-to-copilot-prompt .claude/commands/api/db-backup.md` - 単一ファイルを変換

## 実行内容

引数: $ARGUMENTS

### 処理ステップ

1. **入力の解析**
   - 引数からパスを取得
   - ディレクトリか単一ファイルかを判定
   - 対象の`.md`ファイル一覧を取得（README.mdは除外）

2. **変換先ディレクトリの準備**
   - `.claude/commands/{path}/` → `.github/prompts/{path}/` へのマッピング
   - 変換先ディレクトリが存在しない場合は作成

3. **各ファイルの変換**

   #### 3.1 Claude Codeコマンドの解析
   - ファイル内容を読み込み
   - 以下の要素を抽出:
     - タイトル（H1見出し）
     - 説明文（最初の段落）
     - 使用方法（引数パターン `$ARGUMENTS` の有無）
     - 処理ステップ
     - 前提条件
     - 出力

   #### 3.2 Copilotプロンプト形式への変換

   **YAMLフロントマターの生成:**
   ```yaml
   ---
   mode: 'agent'
   tools: ['codebase']
   description: '{説明文}'
   ---
   ```

   **引数の変換:**
   - `$ARGUMENTS` → `${input:variableName:説明}`
   - `{機能名}` → `${input:featureName:機能名を入力}`
   - 第N引数パターン → 対応するinput変数

   **構造の変換:**
   - 使用方法セクションをインタラクティブ入力形式に変更
   - 処理ステップから引数参照を`${input:xxx}`形式に置換
   - 前提条件・出力セクションも同様に変換

4. **ファイル書き出し**
   - 変換結果を`.prompt.md`拡張子で保存
   - ファイル名: 元のファイル名から`.md`を`.prompt.md`に変更

5. **READMEの生成**
   - 変換したコマンド一覧を含むREADME.mdを生成
   - 使用方法と元のClaude Codeコマンドとの対応表を含める

## 変換ルール詳細

### YAMLフロントマター

| Claude Code | Copilot |
|-------------|---------|
| (なし) | `mode: 'agent'` |
| (なし) | `tools: ['codebase']` |
| H1見出し直後の説明 | `description: '...'` |

### 引数の変換

| Claude Code形式 | Copilot形式 |
|-----------------|-------------|
| `$ARGUMENTS` | `${input:args:引数を入力}` |
| `{機能名}` | `${input:featureName:機能名を入力}` |
| `{ファイルパス}` | `${input:filePath:ファイルパスを入力}` |
| 第1引数 | `${input:param1:第1引数}` |

### コマンド名の変換

| Claude Code | Copilot |
|-------------|---------|
| `/sdd:extract-server-requirements` | `/01-extract-server-requirements` |
| `/api:create-endpoint` | `/create-endpoint` |

## 出力例

### 入力（Claude Code）

```markdown
# DBバックアップ作成

データベースのバックアップを作成します。

## 使用方法

/api:db-backup {対象DB}

例: `/api:db-backup mst`

## 実行内容

引数: $ARGUMENTS
```

### 出力（Copilot）

```markdown
---
mode: 'agent'
tools: ['codebase', 'terminalCommand']
description: 'データベースのバックアップを作成します'
---

# DBバックアップ作成

データベースのバックアップを作成します。

## 使用方法

対象DBを入力してください: ${input:targetDb:対象DB（例: mst）}
```

## 注意事項

- **README.mdは変換対象外**: 各ディレクトリのREADME.mdはスキップされます
- **tools配列の推測**: コマンド内容を解析して適切なtoolsを設定
  - DB操作 → `['codebase', 'terminalCommand']`
  - ファイル操作のみ → `['codebase']`
  - テスト実行 → `['codebase', 'terminalCommand']`
- **既存ファイルの上書き**: 同名ファイルが存在する場合は上書き確認を行う
- **元ファイルは変更しない**: Claude Codeコマンドファイルは読み取り専用として扱う

## 変換後の確認

変換完了後、以下を確認してください:

1. `.github/prompts/{path}/` に`.prompt.md`ファイルが生成されていること
2. YAMLフロントマターが正しい形式であること
3. `${input:xxx}`形式の変数が正しく設定されていること
4. VS CodeのCopilot Chatで`/コマンド名`として認識されること
