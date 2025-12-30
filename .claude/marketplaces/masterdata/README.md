# Masterdata Plugin Marketplace

GLOWプロジェクトのマスタデータ管理ツールセットです。

## 概要

このMarketplaceには、GLOWプロジェクトのマスタデータを効率的に管理するための以下のツールが含まれています:

- **マスタデータ調査**: DBスキーマとCSVファイルの構造確認
- **CSV生成**: 施策仕様書からのマスタデータCSV自動生成
- **リレーション分析**: テーブル間の関係性を可視化

## 含まれるプラグイン

### masterdata

マスタデータ管理の統合ツールセット

**スキル:**
- `masterdata-explorer`: DBスキーマとCSVファイルの調査・理解

**コマンド:**
- `/masterdata:create-master-data`: 施策マスタデータCSVを作成
- `/masterdata:masterdata-generator`: 仕様書からCSVを自動生成
- `/masterdata:masterdata-relation`: リレーション図を生成

## インストール方法

### プロジェクト内で使用する場合

このMarketplaceは既にプロジェクトに含まれています。

### 他のプロジェクトで使用する場合

```bash
# Marketplaceを追加
/plugin marketplace add /path/to/masterdata

# プラグインを有効化
/plugin install masterdata@masterdata
```

または、`.claude/settings.json`に追加:

```json
{
  "extraKnownMarketplaces": {
    "masterdata": {
      "source": {
        "source": "path",
        "path": "/path/to/masterdata"
      }
    }
  },
  "enabledPlugins": {
    "masterdata@masterdata": true
  }
}
```

## 使用例

### マスタデータの調査

```
masterdata-explorerスキルを使って、mst_eventsテーブルの構造を教えてください
```

### 施策マスタデータの作成

```
/masterdata:create-master-data 20260202_幼稚園WARS いいジャン祭
```

### リレーション図の生成

```
/masterdata:masterdata-relation quest
```

## 要件

- `jq`: JSON処理に必要
- Python 3.x: 一部の自動生成機能に必要
  - pandas, openpyxl: XLSX変換に必要

## ライセンス

MIT

## メンテナー

Wonderplanet GLOW
