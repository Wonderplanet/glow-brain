---
name: Implementing Laravel Migrations
description: glow-serverプロジェクトの複数DB（mst/mng/usr/log/sys/admin）に対するマイグレーション実装。テーブル作成・変更、マイグレーション実行、ロールバック時に使用。apiとadminの両ディレクトリに対応し、各DB接続パターンをサポート。
---

# Implementing Laravel Migrations

## Instructions

マイグレーション実装は以下の順序で進めてください：

1. **共通ルールを確認** → [common-rules.md](common-rules.md) でtimestampTz、カラム順序などの必須ルールを確認
2. **テーブル名からDB接続を判断** → テーブル接頭辞（mst_/mng_/usr_/log_/sys_/adm_）からDB接続とファイル配置を決定
3. **命名規則を確認** → [naming-conventions.md](naming-conventions.md) でテーブル命名ルールを確認
4. **実装例を参照** → 該当するDB接続パターンの実装例を確認（examples-*.md）
5. **コマンド実行** → [commands.md](commands.md) で正しいコマンド実行方法を確認
6. **最終チェック** → [reference.md](reference.md) でチェックポイントを確認

### 参照ドキュメント

- **[common-rules.md](common-rules.md)** - 全DB共通の必須ルール（timestampTz、カラム順序、コメント等）
- **[naming-conventions.md](naming-conventions.md)** - テーブル命名規則の詳細
- **[commands.md](commands.md)** - sailコマンドの実行方法
- **[examples-mst-mng.md](examples-mst-mng.md)** - mst/mng接続の実装例（$connectionプロパティ必要）
- **[examples-usr-log-sys.md](examples-usr-log-sys.md)** - usr/log/sys接続の実装例（TiDB）
- **[examples-admin.md](examples-admin.md)** - admin接続の実装例
- **[reference.md](reference.md)** - チェックポイントとベストプラクティス

## Examples

具体的な実装例は、各DB接続パターンごとのドキュメントを参照してください：

- **mst/mng接続**: [examples-mst-mng.md](examples-mst-mng.md)
- **usr/log/sys接続**: [examples-usr-log-sys.md](examples-usr-log-sys.md)
- **admin接続**: [examples-admin.md](examples-admin.md)
