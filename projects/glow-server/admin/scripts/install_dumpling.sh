#!/bin/bash

# dumpling インストールスクリプト
# TiDBのdumplingツールをインストールする

set -euo pipefail

# ログ出力用
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" >&2
}

# dumplingがすでにインストールされているかチェック
if command -v dumpling &> /dev/null; then
    log "dumpling は既にインストールされています"
    dumpling --version
    exit 0
fi

log "dumpling のインストールを開始します"

# 一時ディレクトリを作成
TEMP_DIR=$(mktemp -d)
cd "$TEMP_DIR"

# アーキテクチャを判定
ARCH=$(uname -m)
if [[ "$ARCH" == "x86_64" ]]; then
    ARCH_SUFFIX="amd64"
elif [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
    ARCH_SUFFIX="arm64"
else
    log "エラー: サポートされていないアーキテクチャ: $ARCH"
    exit 1
fi

# TiDB Community Toolkitをダウンロード
TOOLKIT_VERSION="${TIDB_VERSION:-v8.5.1}"
TOOLKIT_FILE="tidb-community-toolkit-${TOOLKIT_VERSION}-linux-${ARCH_SUFFIX}.tar.gz"
DOWNLOAD_URL="https://download.pingcap.org/${TOOLKIT_FILE}"

log "TiDB Community Toolkitをダウンロード中: $DOWNLOAD_URL"
wget -q "$DOWNLOAD_URL" || {
    log "エラー: ダウンロードに失敗しました"
    exit 1
}

# ツールキットを展開
log "ツールキットを展開中..."
tar xzf "$TOOLKIT_FILE"
cd "tidb-community-toolkit-${TOOLKIT_VERSION}-linux-${ARCH_SUFFIX}"

# dumplingを展開
DUMPLING_FILE="dumpling-${TOOLKIT_VERSION}-linux-${ARCH_SUFFIX}.tar.gz"
if [[ ! -f "$DUMPLING_FILE" ]]; then
    log "エラー: dumplingファイルが見つかりません: $DUMPLING_FILE"
    exit 1
fi

log "dumplingを展開中..."
tar xzf "$DUMPLING_FILE"

# dumplingバイナリを適切な場所にコピー
INSTALL_DIR="/usr/local/bin"
if [[ -f "dumpling" ]]; then
    log "dumpling を $INSTALL_DIR にインストール中..."
    cp dumpling "$INSTALL_DIR/"
    chmod +x "$INSTALL_DIR/dumpling"
else
    log "エラー: dumplingバイナリが見つかりません"
    exit 1
fi

# 一時ディレクトリを削除
cd /
rm -rf "$TEMP_DIR"

# インストール確認
if command -v dumpling &> /dev/null; then
    log "dumpling のインストールが完了しました"
    dumpling --version
else
    log "エラー: dumpling のインストールに失敗しました"
    exit 1
fi
