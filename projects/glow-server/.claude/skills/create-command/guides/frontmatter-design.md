# YAMLフロントマター設計

## 利用可能なフィールド

| フィールド | 目的 | 必須 |
|-----------|------|------|
| `description` | コマンドの簡潔な説明。自動補完で表示 | 推奨 |
| `allowed-tools` | 使用可能なツールのリスト | オプション |
| `model` | 特定のモデルを指定 | オプション |
| `argument-hint` | 引数形式のヒント | オプション |
| `disable-model-invocation` | 自動実行を禁止 | オプション |

## description

### 良い例
```yaml
description: Create a git commit with a message. Use when committing changes.
```

### 悪い例
```yaml
description: For commits  # 曖昧すぎる
```

### ポイント
- 何ができるか明記
- いつ使うか示す
- 具体的なキーワードを含める

## allowed-tools

### 構文
```yaml
allowed-tools: ToolName, ToolName(pattern:*), Read, Write
```

### 例
```yaml
# Git操作のみ許可
allowed-tools: Bash(git add:*), Bash(git status:*), Bash(git commit:*)

# 読み取り専用
allowed-tools: Read, Grep, Glob

# 特定のBashコマンドのみ
allowed-tools: Bash(ls:*), Bash(find:*)
```

### 最小権限の原則
必要最小限のツールのみを許可する

## model

```yaml
model: sonnet                      # 一般的なタスク
model: haiku                       # 高速・低コスト
model: opus                        # 複雑なタスク
model: claude-3-5-haiku-20241022   # 正式なモデルID
```

## argument-hint

```yaml
argument-hint: [message]                        # 単一引数
argument-hint: [pr-number] [priority]           # 複数引数
argument-hint: add [tagId] | remove [tagId]     # 選択肢を表示
```

## disable-model-invocation

```yaml
disable-model-invocation: true  # SlashCommandツールによる自動実行を禁止
```

破壊的な操作を行うコマンドに推奨
