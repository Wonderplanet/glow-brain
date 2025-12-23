# glow-brain

GLOW プロジェクトのコードを参照するための開発環境です。

## これは何？

GLOWの3つのプロジェクト（サーバー、マスターデータ、クライアント）のコードを、バージョンを指定して簡単に参照できるようにするツールです。

**できること:**
- ✅ バージョンを指定してコードを取得
- ✅ 別のバージョンに簡単に切り替え
- ✅ 最新のコードに更新
- ✅ 軽量化されたクライアントコード（約100MB）

## 重要な注意事項

このプロジェクトは**完全に参照専用**です。Gitフックにより、以下の操作は自動的にブロックされます：

**❌ 禁止されている操作:**
- コミット（`git commit`）
- プッシュ（`git push`）
- マージ（`git merge`）
- リベース（`git rebase`）

**✅ 許可されている操作:**
- コードの閲覧・検索・参照
- バージョンの切り替え（`./scripts/setup.sh <version>`）
- 最新コードへの更新（`git pull`、`./scripts/setup.sh`）

**コードを変更したい場合:**
本来のリポジトリを直接クローンして作業してください：
```bash
# glow-serverの例
git clone git@github.com:Wonderplanet/glow-server.git
```

## クイックスタート

### 1. 必要なツールをインストール

macOSの場合、ターミナルで以下を実行：

```bash
brew install jq
```

### 2. コードを取得

```bash
./scripts/setup.sh
```

これで完了です！`projects/` フォルダ内に以下が作成されます：
- `glow-server/` - サーバーコード
- `glow-masterdata/` - マスターデータ
- `glow-client/` - クライアントコード（軽量版）

### 3. コードを見る

好きなエディタで `projects/` 内のコードを参照できます：

```bash
# VS Codeで開く場合
code projects/glow-server

# Finderで開く場合
open projects
```

## よく使うコマンド

### バージョンを切り替える

```bash
# v1.4.1に切り替え
./scripts/setup.sh 1.4.1

# v1.5.0に切り替え
./scripts/setup.sh 1.5.0
```

### 最新のコードに更新

```bash
./scripts/setup.sh
```

### 現在のバージョンを確認

```bash
cat config/versions.json | grep current_version
```

## トラブルシューティング

### 「この操作は禁止されています」と表示される

このリポジトリは参照専用のため、`git commit`、`git push`、`git merge`などの
変更操作はできません。

**コードを変更したい場合:**
本来のリポジトリを直接クローンして作業してください：
```bash
# glow-serverの例
git clone git@github.com:Wonderplanet/glow-server.git

# glow-clientの例
git clone git@github.com:Wonderplanet/glow-client.git

# glow-masterdataの例
git clone git@github.com:Wonderplanet/glow-masterdata.git
```

**間違えて変更してしまった場合:**
以下のコマンドで元に戻せます：
```bash
# 例：glow-serverを元に戻す
cd projects/glow-server
git reset --hard HEAD
git clean -fd
cd ../..
```

### 「jq コマンドが見つかりません」と表示される

以下を実行してください：
```bash
brew install jq
```

### 「未コミットの変更があります」と表示される

コードを変更してしまった可能性があります。以下で元に戻せます：

```bash
# 例：glow-serverを元に戻す
cd projects/glow-server
git reset --hard HEAD
git clean -fd
cd ../..
```

### うまく動かない時

コードを削除して、最初からやり直せます：

```bash
# すべて削除
rm -rf projects

# 再セットアップ
./scripts/setup.sh
```

## 詳しい使い方

詳細は `scripts/README.md` を参照してください。

## バージョン一覧

利用可能なバージョン：
- **1.4.1** (デフォルト)
- **1.5.0**

新しいバージョンは `config/versions.json` で管理されています。

## プロジェクト構成

```
glow-brain/
├── README.md              # このファイル
├── config/
│   └── versions.json      # バージョン設定
├── scripts/
│   ├── setup.sh           # セットアップスクリプト
│   └── README.md          # 詳細な使い方
└── projects/              # ここにコードが入ります
    ├── glow-server/
    ├── glow-masterdata/
    └── glow-client/
```

## サポート

問題が発生した場合は、開発チームにお問い合わせください。
