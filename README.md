# glow-brain

**GLOWゲームプロジェクト全体の開発支援のための統合コンテキストリポジトリ**

glow-brainは、GLOWプロジェクトに関する質問、相談、設計などの包括的な支援を行うためのリポジトリです。主に、AI（GitHub CopilotやClaude Code）での作業時に、充実したコンテキストを用意して、GLOWの全メンバーの日々の作業効率アップや、新しい発見をすることを目的としています。

---

## 特徴

- **4つのリポジトリを1箇所に集約** - server/client/masterdata/schemaのコードベースを1箇所に集約し、横断的な理解と開発を可能にします
- **バージョンごとのブランチ管理** - 異なるバージョンのコードベースを並行管理できます
- **AI支援に最適化** - GitHub CopilotやClaude Codeで使いやすいよう設計されています
- **カスタムスキルとエージェント** - マスタデータ作成・検証などの専用ツールを提供します

---

## プロジェクト構成

```
glow-brain/
├── projects/              # GLOWプロジェクトの4リポジトリを集約
│   ├── glow-server/      # サーバー実装（API、DB）
│   ├── glow-client/      # クライアント実装（Unity/C#）
│   ├── glow-masterdata/  # マスタデータ（CSV）
│   └── glow-schema/      # API通信・マスタデータ配信スキーマ定義
├── マスタデータ/           # 運営仕様書と生成データ
├── .github/
│   ├── agents/          # copilot カスタムエージェント
│   ├── prompts/         # copilot プロンプトファイル
├── .claude/
│   ├── skills/          # Agent Skills
├── scripts/              # セットアップスクリプト
├── docs/                 # プロジェクトドキュメント
└── config/               # 設定ファイル
    └── versions.json    # バージョン管理設定
```

### projects/ - 4つのリポジトリ

| リポジトリ | 役割 | 主要技術 | 配置内容 |
|-----------|------|---------|---------|
| **glow-server** | バックエンドAPI、DB定義 | TypeScript, PostgreSQL | 完全なリポジトリ内容 |
| **glow-client** | ゲームクライアント | Unity, C# | スクリプトのみ（軽量化版） |
| **glow-masterdata** | マスタデータ管理 | CSV | 完全なリポジトリ内容 |
| **glow-schema** | API通信・マスタデータ配信スキーマ定義 | JSON, Ruby | 完全なリポジトリ内容 |

**重要な注意点**:
- これらは**参照専用**です（`.git`ディレクトリは削除済み）
- Git submoduleではなく、通常のディレクトリとして管理
- 実際の変更は各リポジトリで行う
- `config/versions.json`で管理されたバージョン・ブランチ状態

---

## バージョン管理の仕組み

glow-brainでは、**バージョンごとに専用のブランチ**を作成し、異なるバージョンのコードベースを並行管理できます。

### 仕組み

1. `config/versions.json`で各バージョンのブランチを定義
2. バージョンごとに専用のGitブランチを作成
3. ブランチを切り替えることで、そのバージョンの開発関連ファイルに瞬時に切り替え可能

### メリット

- **バージョン間の切り替えが高速** - `git checkout`で瞬時に切り替え
- **バージョンごとの独立した作業環境** - 異なるバージョンの開発を並行して進められる
- **コンテキストの整合性** - そのバージョンに対応したserver/client/masterdataのコードが常に揃っている

### config/versions.json の例

```json
{
  "current_version": "v1.5.0-devld",
  "versions": {
    "v1.5.0-devld": {
      "glow-server": "develop/v1.5.0",
      "glow-client": "release/v1.5.0",
      "glow-masterdata": "release/dev-ld"
    },
    "v1.5.1-devld": {
      "glow-server": "develop/v1.5.1",
      "glow-client": "release/v1.5.0",
      "glow-masterdata": "release/dev-ld"
    }
  }
}
```

---

## セットアップ

### 前提条件

- Git
- jq (`brew install jq`)
- GitHubへのSSHアクセス権限

### 初回セットアップ

```bash
# 1. リポジトリをクローン
git clone git@github.com:Wonderplanet/glow-brain.git
cd glow-brain

# 2. setup.sh を実行（current_versionをセットアップ）
./scripts/setup.sh

# または、特定のバージョンをセットアップ
./scripts/setup.sh v1.5.0-devld
```

### バージョン環境ブランチの作成（管理者向け）

全バージョンの環境ブランチを一括で作成できます:

※ github action workflow で 定期実行するように登録済みです
(.github/workflows/create-version-branches.yml)

```bash
# 全バージョンのブランチを作成（確認あり）
./scripts/create-version-branches.sh

# 確認をスキップして自動実行
./scripts/create-version-branches.sh --yes
```

このスクリプトは以下を実行します:
1. `config/versions.json`に定義された全バージョンを取得
2. 各バージョンごとに専用ブランチを作成
3. そのバージョンに対応したserver/client/masterdataをprojects/配下に配置
4. リモートにpush

---

## 使い方

### バージョンの切り替え

```bash
# ブランチを切り替えるだけでバージョンが切り替わる
git checkout v1.5.0-devld
git checkout v1.5.1-devld
```

### 最新の状態に更新

```bash
# 現在のバージョンを最新に更新
./scripts/setup.sh

# 特定のバージョンを最新に更新
./scripts/setup.sh v1.5.0-devld
```

### コードベースの調査

4つのリポジトリを横断的に調査できます:

```bash
# 例: 特定のマスタデータテーブルがどこで使われているか調査
grep -r "mst_events" projects/

# 例: サーバーAPIとクライアント実装の対応関係を確認
# （Claude CodeやGitHub Copilotでの質問が便利です）
```

---

## 主要なワークフロー

### マスタデータ作成

運営仕様書からマスタデータCSVを作成するワークフローが整備されています。

詳細は以下を参照:
- `.ai-context/prompts/運営仕様書からマスタデータ作成の手順書.md`
- `.claude/skills/masterdata-csv-validator/SKILL.md`
- `.github/agents/pln-masterdata-creator.agent.md`

### カスタムスキル

このリポジトリには、GLOW開発を支援するカスタムスキルが用意されています:

- **masterdata-explorer** - マスタデータのスキーマ調査とSQL分析
- **masterdata-csv-validator** - マスタデータCSVの検証
- **plugin-marketplace-creator** - プラグインマーケットプレイス作成
