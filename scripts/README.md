# glow-brain-2 セットアップスクリプト

## 概要

glow-brain-2 プロジェクトで、3つのglowリポジトリ（glow-server, glow-masterdata, glow-client）をバージョン管理しながら安全に運用するためのスクリプトです。

## バージョン管理の仕組み

- **バージョンベース管理**: glow-brain-2 は複数のバージョン（1.4.1, 1.5.0など）をサポート
- **ブランチマッピング**: 各バージョンごとに、3つのリポジトリの対応ブランチが決まっている
- **設定ファイル**: `config/versions.json` でバージョンとブランチのマッピングを管理
- **参照専用**: glow-brain-2 は参照専用プロジェクトで、各リポジトリへの変更は禁止

### バージョンとブランチの例

**v1.4.1の場合:**
- glow-server: `develop/v1.4.1`
- glow-client: `release/v1.4.1`
- glow-masterdata: `release/dev-ld`

**v1.5.0の場合:**
- glow-server: `develop/v1.5.0`
- glow-client: `release/v1.5.0`
- glow-masterdata: `release/dev-ld`

## 使用方法

### 初回セットアップ

バージョンを指定してセットアップ:
```bash
./scripts/setup.sh 1.4.1
```

`config/versions.json` の `current_version` を使用してセットアップ:
```bash
./scripts/setup.sh
```

### バージョンの切り替え

別のバージョンに切り替える場合:
```bash
./scripts/setup.sh 1.5.0
```

### 最新への更新

現在のバージョンで最新の変更を取得:
```bash
./scripts/setup.sh
```

## スクリプトの動作

### セットアップ時（リポジトリが存在しない場合）

1. 指定されたバージョンのブランチをクローン
2. glow-client は軽量化クローン（LFSスキップ、shallow clone、sparse checkout）
3. `config/versions.json` の `current_version` を更新

### 更新時（リポジトリが既に存在する場合）

1. 未コミット変更のチェック（変更がある場合はエラー）
2. 必要に応じてブランチを切り替え
3. 最新の変更を取得（fast-forward のみ）
4. `config/versions.json` の `current_version` を更新

## glow-client の軽量化対策

glow-client は容量が大きいため、以下の軽量化対策を実施:

- **LFSスキップ**: `GIT_LFS_SKIP_SMUDGE=1` で LFS ファイルをスキップ
- **Shallow clone**: `--depth 1` で履歴を1つだけ取得
- **Sparse checkout**: スクリプトディレクトリのみチェックアウト
  - `Assets/GLOW/Scripts`
  - `Assets/Framework/Scripts`

これにより、約100MBの軽量版として運用できます。

## 新しいバージョンの追加方法

1. `config/versions.json` を編集:
```json
{
  "current_version": "1.4.1",
  "versions": {
    "1.4.1": { ... },
    "1.5.0": { ... },
    "1.6.0": {
      "glow-server": "develop/v1.6.0",
      "glow-client": "release/v1.6.0",
      "glow-masterdata": "release/dev-ld"
    }
  },
  ...
}
```

2. スクリプトを実行:
```bash
./scripts/setup.sh 1.6.0
```

## トラブルシューティング

### jq コマンドが見つからない

```bash
# macOS
brew install jq

# Ubuntu/Debian
sudo apt-get install jq

# CentOS/RHEL
sudo yum install jq
```

### 未コミット変更エラー

glow-brain-2 は参照専用プロジェクトのため、各リポジトリへの変更は禁止されています。

**対処法:**
```bash
# 変更を確認
cd projects/glow-server
git status

# 変更を破棄する場合
git reset --hard HEAD
git clean -fd

# 変更を一時退避する場合
git stash
```

### ブランチが存在しないエラー

`config/versions.json` に指定されたブランチがリモートリポジトリに存在しない場合、エラーになります。

**対処法:**
1. リモートリポジトリでブランチが作成されているか確認
2. `config/versions.json` のブランチ名が正しいか確認

### 履歴が肥大化した場合（glow-client）

glow-client の履歴が肥大化した場合、再クローンすることで軽量化できます:

```bash
# リポジトリを削除
rm -rf projects/glow-client

# 再クローン
./scripts/setup.sh
```

## 注意事項

- **参照専用**: 各リポジトリへの変更（コミット、ブランチ作成など）は禁止
- **読み取り専用**: コードを参照・分析する目的でのみ使用
- **未コミット変更**: 未コミット変更がある場合、スクリプトはエラーで停止
- **自動更新**: `config/versions.json` の `current_version` は自動で更新される

## ディレクトリ構成

```
glow-brain-2/
├── config/
│   └── versions.json       # バージョン管理（current_version, versions, repositories）
├── scripts/
│   ├── setup.sh            # このスクリプト
│   └── README.md           # このドキュメント
└── projects/               # リポジトリクローン先（git管理外）
    ├── glow-server/
    ├── glow-masterdata/
    └── glow-client/
```

## サポートされているバージョン

`config/versions.json` で定義されているバージョンを確認:
```bash
cat config/versions.json | jq '.versions | keys'
```

現在の `current_version` を確認:
```bash
cat config/versions.json | jq -r '.current_version'
```
