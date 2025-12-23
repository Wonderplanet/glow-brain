# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクトの概要

glow-brainはGLOWプロジェクトのコードを参照するための開発環境です。以下の3つのリポジトリをバージョン指定で管理します：

- **glow-server**: サーバーコード（Laravel/PHP）
- **glow-client**: クライアントコード（Unity/C#）- 軽量化版
- **glow-masterdata**: マスターデータ

## 重要な制約

**このリポジトリは完全に参照専用です。**

- ❌ `git commit`, `git push`, `git merge`, `git rebase`などの変更操作は禁止
- ✅ コードの閲覧、検索、参照のみ許可
- Gitフックにより変更操作は自動的にブロックされます

コードを変更したい場合は、本来のリポジトリを直接クローンしてください：
```bash
git clone git@github.com:Wonderplanet/glow-server.git
git clone git@github.com:Wonderplanet/glow-client.git
git clone git@github.com:Wonderplanet/glow-masterdata.git
```

## バージョン管理

### バージョンの切り替え
```bash
# 特定バージョンに切り替え
./scripts/setup.sh 1.4.1
./scripts/setup.sh 1.5.0

# 最新に更新
./scripts/setup.sh
```

### 現在のバージョンを確認
```bash
cat config/versions.json | grep current_version
```

### バージョン設定

`config/versions.json`でバージョンとブランチの対応を管理：
- `current_version`: デフォルトで使用するバージョン
- `versions`: 各バージョンのブランチ設定
- `repositories`: リポジトリのURL

## セットアップ

### 必要なツール
```bash
brew install jq  # JSON処理に必要
```

### 初期セットアップ
```bash
./scripts/setup.sh
```

これにより`projects/`ディレクトリに3つのリポジトリがクローンされます。

## リポジトリ構造

```
glow-brain/
├── config/
│   └── versions.json       # バージョン設定
├── scripts/
│   ├── setup.sh           # セットアップスクリプト
│   └── hooks/             # Git保護用フック
│       ├── pre-commit
│       ├── pre-push
│       └── pre-merge-commit
└── projects/              # 参照用リポジトリ（Git管理外）
    ├── glow-server/       # Laravelサーバー
    ├── glow-masterdata/   # マスターデータ
    └── glow-client/       # Unity（軽量化版）
```

## 特殊な処理

### glow-clientの軽量化クローン

glow-clientは容量削減のため、sparse checkoutで必要なディレクトリのみを取得：
- `Assets/GLOW/Scripts`
- `Assets/Framework/Scripts`
- Git LFSは無効化（`GIT_LFS_SKIP_SMUDGE=1`）

### リポジトリ更新時の挙動

- 未コミット変更がある場合はエラーで停止（参照専用ポリシー）
- force pushされた場合は自動的にリモートに合わせる（`git reset --hard`）
- fast-forwardマージのみ許可

### Git保護フック

`scripts/hooks/`内のフックが各リポジトリに自動インストールされます：
- `pre-commit`: コミットをブロック
- `pre-push`: プッシュをブロック
- `pre-merge-commit`: マージをブロック

## トラブルシューティング

### 未コミット変更エラー
```bash
cd projects/<repository-name>
git reset --hard HEAD
git clean -fd
```

### 完全リセット
```bash
rm -rf projects
./scripts/setup.sh
```

## 参照用リポジトリの詳細

各`projects/`内のリポジトリには独自のCLAUDE.mdや開発ドキュメントがあります。
特にglow-serverには詳細なAPI開発ガイドやテストパターンが含まれています。
