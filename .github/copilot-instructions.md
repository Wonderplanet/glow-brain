すべての返答は常に日本語で行ってください。

# glow-brain AI エージェント向けガイド

## このプロジェクトとは？

**glow-brain** は、GLOWゲームプロジェクトの複数バージョンを参照するための**読み取り専用ワークスペース**です。3つの独立したリポジトリ（glow-server、glow-masterdata、glow-client）をバージョン管理されたチェックアウトで統合し、コードを変更することなくバージョン間のコード比較を可能にします。

## 重要：これは読み取り専用リポジトリです

**`projects/` ディレクトリ内のコードを変更しないでください。**

- すべての `git commit`、`git push`、`git merge`、`git rebase` 操作は **Git フックでブロック**されます（[scripts/hooks/](../scripts/hooks/)）
- セットアップスクリプトは更新前に未コミット変更を検出します
- 変更は元のリポジトリで行う必要があります：
  - `git@github.com:Wonderplanet/glow-server.git`
  - `git@github.com:Wonderplanet/glow-client.git`
  - `git@github.com:Wonderplanet/glow-masterdata.git`

## アーキテクチャ概要

### リポジトリ構造
```
glow-brain/
├── config/versions.json       # バージョンとブランチのマッピング
├── scripts/
│   ├── setup.sh               # メイン統合スクリプト
│   └── hooks/                 # 変更防止用Gitフック
└── projects/                  # 管理対象リポジトリ（編集禁止）
    ├── glow-server/           # サーバーコード（完全クローン）
    ├── glow-masterdata/       # マスターデータCSV（完全クローン）
    └── glow-client/           # Unityクライアント（sparse checkout）
```

### バージョン管理モデル

[config/versions.json](../config/versions.json) のバージョン設定：
- セマンティックバージョン（`1.4.1`、`1.5.0`）をリポジトリごとの特定ブランチ名にマッピング
- `current_version` でワークスペースの状態を追跡
- 各リポジトリは異なるブランチ命名規則を使用可能（例：`develop/v1.4.1` vs `release/v1.4.1`）

### クライアントリポジトリの最適化

**glow-client は積極的なサイズ削減を実施**：
- Sparse checkout で `Assets/GLOW/Scripts` と `Assets/Framework/Scripts` のみに限定
- Git LFS をスキップ（`GIT_LFS_SKIP_SMUDGE=1`）
- `--depth 1` と `--filter=blob:none` でシャロークローン
- 結果として完全版に比べて約100MBに削減

## 重要なワークフロー

### セットアップ・バージョン切り替え

```bash
# デフォルトバージョンでセットアップ（config/versions.json から）
./scripts/setup.sh

# 特定バージョンに切り替え
./scripts/setup.sh 1.5.0
```

**実行される処理：**
1. `jq` の前提条件をチェック（macOS: `brew install jq`）
2. `config/versions.json` にバージョンが存在するか検証
3. 各リポジトリに対して：
   - 存在しない場合はクローン（クライアント専用最適化を適用）
   - 存在する場合は対象ブランチに更新（分岐している場合は強制リセット）
   - 保護用Gitフックをインストール
4. 設定ファイルの `current_version` を更新

### 誤った変更からのリカバリ

```bash
cd projects/glow-server
git reset --hard HEAD
git clean -fd
```

### 新しいバージョンの追加

[config/versions.json](../config/versions.json) を編集：
```json
{
  "versions": {
    "1.6.0": {
      "glow-server": "develop/v1.6.0",
      "glow-client": "release/v1.6.0",
      "glow-masterdata": "release/dev-ld"
    }
  }
}
```

## プロジェクト固有の規約

### Bashスクリプトパターン

[scripts/setup.sh](../scripts/setup.sh) はプロジェクトのシェル規約を示します：
- 厳格なエラー処理のため `set -euo pipefail` を使用
- カラーコード付きログ関数（`info`、`success`、`error`、`warning`）
- パス用の `readonly` 変数（`PROJECT_ROOT`、`CONFIG_FILE`）
- nullチェック付きの防御的な `jq` 使用

### Force-Push の処理

更新ロジック（[setup.sh:205-228](../scripts/setup.sh#L205-L228)）は上流の force-push を処理します：
```bash
if ! git merge --ff-only "origin/${target_branch}" 2>/dev/null; then
    warning "履歴が分岐しています。リモートに強制的に合わせます..."
    git reset --hard "origin/${target_branch}"
fi
```
これは読み取り専用モードでの意図的な動作で、常にリモート状態に合わせます。

### Gitフック保護

[scripts/hooks/](../scripts/hooks/) のフックは多層的な保護を提供：
- `pre-commit`: 日本語エラーメッセージでコミットをブロック
- `pre-push`: プッシュを防止
- `pre-merge-commit`: マージ操作を停止

## 一般的な操作（推奨・非推奨）

### ✅ 推奨
- 任意のエディタ/IDEで `projects/` 内のコードを閲覧
- バージョン間でコードの変遷を検索
- バージョン間で実装を比較
- 最新コードへの更新：`./scripts/setup.sh`

### ❌ 非推奨
- `projects/` サブディレクトリ内でのブランチ作成
- 変更のコミット（フックがブロックしますが、トリガーを避ける）
- 管理対象リポジトリへの新しいファイルの追加
- 標準的なgitワークフローが動作すると想定すること（これは通常のワークスペースではありません）

## 理解すべき重要なファイル

- [config/versions.json](../config/versions.json): バージョンマッピングの唯一の信頼できる情報源
- [scripts/setup.sh](../scripts/setup.sh): すべての統合ロジック（397行）
- [README.md](../README.md): 日本語のユーザー向けドキュメント
- [scripts/hooks/pre-commit](../scripts/hooks/pre-commit): 保護メカニズムの例

## トラブルシューティング

**"jq コマンドが見つかりません"**
- インストール：`brew install jq`（macOS）

**"未コミットの変更があります"**
- リセット：`cd projects/<repo> && git reset --hard HEAD && git clean -fd`

**間違ったバージョンがチェックアウトされている**
- 再実行：`./scripts/setup.sh <version>`（冪等な操作）

**クライアントリポジトリが大きすぎる**
- LFSアセットが取得された場合の予期される動作
- 削除して再セットアップ：`rm -rf projects/glow-client && ./scripts/setup.sh`

すべての返答は常に日本語で行ってください。