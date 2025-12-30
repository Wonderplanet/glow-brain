# Masterdata Marketplace - 使用ガイド

## インストール方法

### このプロジェクトで使用する場合

**ステップ1**: `.claude/settings.json`を作成（既に存在する場合はスキップ）

プロジェクトルートに`.claude/settings.json`がない場合は作成してください。

**ステップ2**: `.claude/settings.json`に以下を追加

既存の設定がある場合は、`extraKnownMarketplaces`と`enabledPlugins`セクションをマージしてください。

```json
{
  "extraKnownMarketplaces": {
    "masterdata": {
      "source": {
        "source": "directory",
        "path": "./.claude/marketplaces/masterdata"
      }
    }
  },
  "enabledPlugins": {
    "masterdata@masterdata": true
  }
}
```

**ステップ3**: Claude Codeを再起動

設定を反映するために、Claude Codeセッションを再起動してください。

### 他のプロジェクトで使用する場合

```bash
# Marketplaceを追加
/plugin marketplace add /path/to/glow-brain/.claude/marketplaces/masterdata

# プラグインを有効化
/plugin install masterdata@masterdata
```

または、GitHubリポジトリとして公開する場合：

```bash
/plugin marketplace add your-org/masterdata-marketplace
/plugin install masterdata@masterdata
```

## 利用可能な機能

### 1. マスタデータ調査スキル

**スキル名**: `masterdata-explorer`

**使い方**:
```
masterdata-explorerスキルを使って、mst_eventsテーブルの構造を教えてください
```

**機能**:
- DBスキーマの確認（jqコマンドでJSON解析）
- 既存CSVファイルの参照
- テーブル名・カラム名の検索
- enum値の確認
- NULL許可カラムの特定

### 2. 施策マスタデータ作成コマンド

**コマンド名**: `/masterdata:create-master-data`

**使い方**:
```
/masterdata:create-master-data 20260202_幼稚園WARS いいジャン祭
```

**機能**:
- 施策仕様書を元にマスタデータCSVを作成
- 既存リソースの確認と検証
- コンテンツタイプ別のグルーピング処理
- 並列処理によるCSV生成

### 3. マスタデータ自動生成コマンド

**コマンド名**: `/masterdata:masterdata-generator`

**使い方**:
```
/masterdata:masterdata-generator 20260202_幼稚園WARS いいジャン祭
```

**機能**:
- 施策仕様書(XLSX)からの自動変換
- 複数マスタデータの並列生成
- テンプレート適合性チェック
- AI修正による自動補正

**処理フロー**:
1. ファイル特定と事前検証
2. XLSX→CSV変換
3. 生成対象シートの特定
4. マスタデータ生成（並列処理）
5. テンプレート適合性チェック
6. 完了レポート

### 4. リレーション図生成コマンド

**コマンド名**: `/masterdata:masterdata-relation`

**使い方**:
```
/masterdata:masterdata-relation quest
```

**機能**:
- 機能別のテーブル関連性分析
- Mermaid ERD図の自動生成
- コードベース調査（Laravel/Unity）
- リレーション詳細ドキュメント作成

**出力例**:
- `docs/機能一覧/マスタデータリレーション_quest.md`
- テーブル一覧表
- Mermaid ERD図
- リレーション詳細説明

## ディレクトリ構造

```
.claude/marketplaces/masterdata/
├── .claude-plugin/
│   └── marketplace.json          # Marketplace設定
├── plugins/
│   └── masterdata/
│       ├── .claude-plugin/
│       │   └── plugin.json       # Plugin設定
│       ├── skills/
│       │   └── masterdata-explorer.md
│       ├── commands/
│       │   ├── create-master-data.md
│       │   ├── masterdata-generator.md
│       │   └── masterdata-relation.md
│       ├── references/
│       │   └── schema-reference.md
│       └── scripts/
│           └── search_schema.sh
├── README.md
├── USAGE.md                      # このファイル
└── .gitignore
```

## 設定ファイルの詳細

### marketplace.json

```json
{
  "name": "masterdata",
  "owner": {
    "name": "Wonderplanet GLOW"
  },
  "metadata": {
    "description": "GLOWプロジェクトのマスタデータ管理ツールセット",
    "version": "1.0.0",
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "masterdata",
      "source": "./masterdata",
      "strict": true
    }
  ]
}
```

### plugin.json（概要）

- **name**: masterdata
- **version**: 1.0.0
- **skills**: 1個（masterdata-explorer）
- **commands**: 3個
- **license**: MIT
- **category**: productivity

## トラブルシューティング

### プラグインが認識されない

```bash
# Marketplace検証
cd .claude/marketplaces/glow-masterdata
claude plugin validate .

# プラグイン一覧確認
/plugin list
```

### スキルが使えない

`.claude/settings.json`の`enabledPlugins`を確認してください：

```json
{
  "enabledPlugins": {
    "masterdata@masterdata": true
  }
}
```

### コマンドが見つからない

プラグインが正しくインストールされているか確認：

```bash
/plugin info masterdata@masterdata
```

## 既存コンポーネントとの互換性

このMarketplaceは、以下の既存コンポーネントをコピーして作成されています：

- `.claude/skills/masterdata-explorer/` → 元の場所に残っています
- `.claude/commands/masterdata/` → 元の場所に残っています

**互換性**: 既存のコンポーネントはそのまま動作します。Marketplace版を優先的に使用したい場合は、settings.jsonでプラグインを有効化してください。

## 次のステップ

1. **Gitリポジトリとして管理**:
   ```bash
   cd .claude/marketplaces/glow-masterdata
   git init
   git add .
   git commit -m "Initial commit: GLOW Masterdata Marketplace"
   ```

2. **GitHubで公開**（オプション）:
   ```bash
   git remote add origin git@github.com:your-org/masterdata-marketplace.git
   git push -u origin main
   ```

3. **チームで共有**:
   - リポジトリURLを共有
   - チームメンバーは`/plugin marketplace add`で追加

## サポート

質問や問題がある場合は、Wonderplanet GLOWの開発チームにお問い合わせください。
