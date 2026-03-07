# dev-qa2環境 データ反映失敗調査レポート

## 発生日時
2025-12-05 13:07:30 (JST)

## 事象
dev-qa2環境へのデータ反映処理中にエラーが発生し、失敗した。
[slack](https://wonderplanet-glow.slack.com/archives/C069N3UL80H/p1764907865353939?thread_ts=1764904648.658579&cid=C069N3UL80H)

## エラー内容
```
SQLSTATE[HY000] [2002] Connection refused (Connection: mng, SQL: select * from `mng_master_releases` where `enabled` = 1 and `target_release_version_id` is not null order by `release_key` desc limit 1)
```

mngデータベースへの接続が拒否された。

## 原因
**MySQLコンテナがOOM（Out Of Memory）でkillされた。**

### 根拠
```bash
docker events --since "2025-12-05T04:00:00" --until "2025-12-05T05:00:00"
```
```
2025-12-05T04:07:25 container oom 2d43da7d9171...
2025-12-05T04:07:26 container die ... exitCode=137
```

- `container oom`: OOM Killerによりコンテナがkillされた
- `exitCode=137`: OOMによる強制終了を示すコード

### 時系列（JST）
| 時刻 | イベント |
|------|----------|
| 13:06:45 | import done（mstデータのインポート完了） |
| 13:06:49 | MngCacheRepository削除（mng接続OK） |
| 13:07:07 | S3アセットコピー完了 |
| **13:07:25** | **MySQLコンテナOOM発生** |
| 13:07:26 | MySQLコンテナ再起動開始 |
| **13:07:30** | **Connection refused エラー発生** |
| 13:08:06 | MySQL再起動完了 |

インポート処理の負荷によりメモリ使用量が増加し、MySQLコンテナがOOMでkillされた。その再起動中に接続しようとしてエラーになった。

## 環境情報

### EC2インスタンス
- **インスタンスタイプ**: t4g.medium
- **vCPU**: 2
- **メモリ**: 4GB

### メモリ使用状況（調査時点）
```bash
free -h
```
```
               total        used        free      shared  buff/cache   available
Mem:           3.8Gi       2.7Gi       242Mi       403Mi       853Mi       505Mi
Swap:             0B          0B          0B
```

### 稼働中のDockerコンテナ
```bash
docker ps
```
| コンテナ | イメージ | 用途 |
|---------|----------|------|
| glow-server-mysql-1 | mysql:8.0 | データベース |
| glow-server-php-1 | glow-server-php | API |
| glow-server-php-admin-1 | glow-server-php-admin | 管理画面 |
| glow-server-nginx-1 | glow-server-nginx | Webサーバー |
| glow-server-nginx-admin-1 | glow-server-nginx-admin | 管理画面用Webサーバー |
| glow-server-redis-1 | redis:6.2 | キャッシュ |

## 問題点
1. **メモリ不足**: 4GBのメモリで6つのコンテナ（特にMySQL、PHP×2）を運用するのは厳しい
2. **スワップなし**: メモリ不足時のバッファがない
3. **MySQLのメモリ制限なし**: MySQLが必要に応じてメモリを使い切る可能性がある

## 対策

### 短期対策（即時対応可能）

#### 1. スワップの有効化
```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# 永続化（再起動後も有効にする）
echo '/swapfile swap swap defaults 0 0' | sudo tee -a /etc/fstab
```

#### 2. MySQLのメモリ制限（docker-compose.yml）
```yaml
mysql:
  image: mysql:8.0
  mem_limit: 1g
```

### 中長期対策（推奨）

#### 1. EC2インスタンスタイプのアップグレード

| タイプ | メモリ | 月額目安（東京リージョン） |
|--------|--------|---------------------------|
| t4g.medium（現在） | 4GB | 約$24 |
| **t4g.large（推奨）** | **8GB** | **約$49** |
| t4g.xlarge | 16GB | 約$97 |

**t4g.large（8GB）への変更を推奨**。月額コスト増は約$25だが、OOMの心配がなくなる。

#### 2. MySQLの設定最適化（my.cnf）
```ini
innodb_buffer_pool_size = 256M
```

## 結論
データ反映処理自体は正常に動作していたが、処理負荷によるメモリ消費増加でMySQLコンテナがOOMでkillされた。

**推奨対応**:
1. **即時**: スワップを有効化して再度データ反映を実行
2. **恒久**: t4g.large（8GB）へのインスタンスタイプ変更

## 調査コマンド参考

```bash
# EC2の稼働時間確認
uptime

# メモリ使用状況
free -h

# Dockerコンテナ一覧
docker ps

# コンテナの状態詳細（OOMKilledフラグ確認）
docker inspect <container_name> | grep -A 10 "State"

# Dockerイベントログ（特定時間帯）
docker events --since "2025-12-05T04:00:00" --until "2025-12-05T05:00:00"
```
