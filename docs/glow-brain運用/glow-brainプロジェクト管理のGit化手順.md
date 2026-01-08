# glow-brainプロジェクト管理のGit化手順

## 概要

glow-brainで管理している3つのプロジェクト（glow-server, glow-client, glow-masterdata）を、参照専用から編集可能なGit管理下に移行する手順。

## 前提条件

- `projects/`配下に3つのリポジトリが既にクローンされている
- 各リポジトリは元々独立したGitリポジトリとしてクローンされている

## 実行手順

### 1. Gitフックと.gitディレクトリの削除

3つ全てのプロジェクトから、保護用フックと.gitディレクトリを削除します。

```bash
# 保護フックを削除
rm -f projects/glow-server/.git/hooks/{pre-commit,pre-push,pre-merge-commit}
rm -f projects/glow-client/.git/hooks/{pre-commit,pre-push,pre-merge-commit}
rm -f projects/glow-masterdata/.git/hooks/{pre-commit,pre-push,pre-merge-commit}

# .gitディレクトリを削除
rm -rf projects/glow-server/.git
rm -rf projects/glow-client/.git
rm -rf projects/glow-masterdata/.git
```

### 2. .gitignoreの修正

`.gitignore`ファイルから`projects/*`の除外設定を削除します。

**変更前**:
```gitignore
# クローンされたリポジトリ（参照専用、git管理外）
projects/*
!projects/.gitkeep
```

**変更後**:
```gitignore
# クローンされたリポジトリ（Git管理対象）
!projects/.gitkeep
```

または、この行ごと削除してもOKです。

### 3. Submodule扱いを解除してGit管理に追加

```bash
# インデックスから削除（submodule扱いを解除）
git rm --cached projects/glow-server projects/glow-client projects/glow-masterdata

# 通常のディレクトリとして再追加
git add projects/glow-server/ projects/glow-client/ projects/glow-masterdata/

# コミット
git commit -m "glow-server, glow-client, glow-masterdataをgit管理対象に追加"
```

### 4. バージョンブランチにプッシュ

```bash
# バージョンごとのブランチにプッシュ
git push origin <バージョンブランチ名>

# 例
git push origin v1.5.0
git push origin v1.4.1
```

## 確認方法

### Git管理状態の確認

```bash
# 追加されたファイルを確認
git status

# Warningが出ないことを確認（submodule扱いになっていない）
git add projects/glow-server/
# → "warning: adding embedded git repository" が出ないこと
```

### リモートでの確認

- GitHubなどでリポジトリを確認
- `projects/`配下のディレクトリが通常のフォルダアイコンで表示されていればOK
- `@`マークやコミットハッシュが表示されている場合はsubmodule扱い（NG）

## 注意事項

### .gitディレクトリ削除の影響

- 元のglow-server/client/masterdataリポジトリとの接続が切れます
- 各プロジェクトの履歴情報は失われます
- `setup.sh`での自動更新機能は使えなくなります

### バージョン管理の運用

- バージョンごとに異なるブランチで管理
- 各バージョンブランチには、そのバージョンのコードがコミットされる
- ブランチ例: `v1.4.1`, `v1.5.0`, `debug`など

### setup.shの扱い

元の`setup.sh`は、リポジトリをクローン・更新するスクリプトでしたが、Git管理化後は以下のように扱いが変わります：

- 保護フックのインストール機能は不要
- 既存環境では`.git`が存在しないため更新処理がエラーになる
- 必要に応じて`setup.sh`を削除または修正

## トラブルシューティング

### Submodule扱いになってしまった場合

```bash
git rm --cached projects/<プロジェクト名>
git add projects/<プロジェクト名>/
git commit -m "Fix: submodule扱いを解除"
```

### 一部のファイルが残ってしまった場合

```bash
# Git管理下から削除
git rm -rf <ディレクトリ名>
git commit -m "Remove <ディレクトリ名>"
```

## 結果

3つ全てのプロジェクト（glow-server, glow-client, glow-masterdata）がglow-brainのGit管理下に入り、バージョンごとのブランチで管理できるようになります。
