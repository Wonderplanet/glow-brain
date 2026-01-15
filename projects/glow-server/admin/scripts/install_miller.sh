#!/bin/bash

# miller インストールスクリプト
# Millerデータ処理ツールをインストールする

set -euo pipefail

# ログ出力用
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $*" >&2
}

# millerがすでにインストールされているかチェック
if command -v mlr &> /dev/null; then
    log "miller は既にインストールされています"
    mlr --version
    exit 0
fi

log "miller のインストールを開始します"

# 一時ディレクトリを作成
TEMP_DIR=$(mktemp -d)
cd "$TEMP_DIR"

# アーキテクチャを判定
ARCH=$(uname -m)
if [[ "$ARCH" == "x86_64" ]]; then
    ARCH_SUFFIX="linux-amd64"
elif [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
    ARCH_SUFFIX="linux-arm64"
else
    log "エラー: サポートされていないアーキテクチャ: $ARCH"
    exit 1
fi

# Millerのバージョンを設定
MLR_VERSION="${MLR_VERSION:-6.15.0}"
MLR_FILE="miller-${MLR_VERSION}-${ARCH_SUFFIX}.tar.gz"
DOWNLOAD_URL="https://github.com/johnkerl/miller/releases/download/v${MLR_VERSION}/${MLR_FILE}"

log "Miller をダウンロード中: $DOWNLOAD_URL"
curl -fsSL -o /tmp/mlr.tgz "$DOWNLOAD_URL" || {
    log "エラー: ダウンロードに失敗しました"
    exit 1
}

# millerを展開
log "miller を展開中..."
tar -xzf /tmp/mlr.tgz -C /usr/local/bin || {
    log "エラー: 展開に失敗しました"
    exit 1
}

# mlrバイナリを適切な場所に移動
EXTRACT_DIR="/usr/local/bin/miller-${MLR_VERSION}-${ARCH_SUFFIX}"
if [[ -f "${EXTRACT_DIR}/mlr" ]]; then
    log "miller を /usr/local/bin にインストール中..."
    mv "${EXTRACT_DIR}/mlr" /usr/local/bin/
    chmod +x /usr/local/bin/mlr
    
    # 展開ディレクトリを削除
    rm -rf "$EXTRACT_DIR"
else
    log "エラー: mlrバイナリが見つかりません"
    exit 1
fi

# 一時ファイルを削除
rm -f /tmp/mlr.tgz
cd /
rm -rf "$TEMP_DIR"

# インストール確認
if command -v mlr &> /dev/null; then
    log "miller のインストールが完了しました"
    mlr --version
else
    log "エラー: miller のインストールに失敗しました"
    exit 1
fi