# 一般的なユースケース

カスタムスラッシュコマンドのよくある使用パターンです。

## 1. ツール/スキル作成支援

### パターン
- 対応するスキルを呼び出す
- ユーザーとの対話形式

### 例: スキル作成コマンド
```markdown
---
description: 新しいスキルを作成する
---

# スキル作成

create-skillスキルを使用して、新しいスキルを作成します。

まず、基本情報をお聞かせください：

1. スキルの目的
2. 使用状況
3. 対象範囲
```

## 2. コード品質チェック

### パターン
- 複数のチェックを順次実行
- 問題があれば修正

### 例: Lintエラー修正
```markdown
---
description: PHPのLintエラーを検出して修正する
allowed-tools: Bash(sail:*), Read, Edit
---

# Lint Fix

1. sail phpcs を実行
2. エラーがあれば sail phpcbf で自動修正
3. 残ったエラーを手動で修正
```

## 3. ドキュメント生成

### パターン
- コードを解析
- 指定フォーマットで出力

### 例: シーケンス図生成
```markdown
---
description: APIエンドポイントのシーケンス図を生成
argument-hint: [API path]
---

# Sequence Diagram

API: $ARGUMENTS

1. ルーティングからControllerを特定
2. 処理フローを解析
3. Mermaid形式で出力
```

## 4. PR/Issue対応

### パターン
- GitHubコンテキストを取得
- 対応を実行

### 例: PRコメント対応
```markdown
---
description: PRの未解決コメントに対応する
allowed-tools: Bash(gh:*), Read, Edit
---

# PR Comments Fix

1. gh pr view でPR情報取得
2. 未解決コメントを一覧化
3. 順次対応
```

## 5. 環境セットアップ

### パターン
- 前提条件をチェック
- 必要な設定を適用

### 例: 開発環境初期化
```markdown
---
description: 開発環境を初期化する
allowed-tools: Bash(docker:*), Bash(sail:*)
---

# Dev Setup

1. Docker環境を起動
2. マイグレーション実行
3. シードデータ投入
```

## 6. マスタデータ操作

### パターン
- DB構造を確認
- データを生成/更新

### 例: CSV生成
```markdown
---
description: マスタデータをCSV出力
argument-hint: [table-name]
---

# Master Data Export

テーブル: $ARGUMENTS

1. テーブル構造を確認
2. データを取得
3. CSV形式で出力
```

## 7. ワークフロー実行

### パターン
- 複数ステップの処理
- サブエージェントを活用

### 例: SDD実行
```markdown
---
description: SDDフルフローを実行
---

# SDD Full Flow

1. 要件抽出
2. コード調査
3. 仕様レビュー
4. API設計
```

## 選択のガイドライン

| ユースケース | コマンド向き | スキル向き |
|------------|------------|-----------|
| シンプルな操作 | ✓ | |
| 複雑なワークフロー | | ✓ |
| 頻繁に使う | ✓ | |
| ドキュメントが多い | | ✓ |
| ユーザー入力が多い | ✓ | |
| 自動検出が必要 | | ✓ |
