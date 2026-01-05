# Claude Code ガイド相談レポート

**日付**: 2026年01月05日
**トピック**: Plugin Marketplace の命名規則と設計戦略（完全版）

## 相談の経緯

### 初回の質問
- 会社内でのみ使用する Plugin Marketplace の命名規則について
- プロジェクトごとに作りつつ、プロジェクト間で共有する前提での最適な命名方法
- Marketplace 名とプラグイン名の命名パターン
- 会社名をプレフィックスに含めるべきかどうか
- プラグイン名の衝突を防ぐ方法

### 再検討の背景
初回の相談で「プラグイン名にプロジェクト名をプレフィックスとして付ける」ことを推奨しましたが、以下の疑問が生じました：

- 会社で単一の機能を持つプラグイン（例: code-formatter）を、プロジェクトごとに `glow-code-formatter`, `kanban-code-formatter` のように命名するのは冗長ではないか
- 他プロジェクトで「他プロジェクトの名前を冠したプラグイン」を使うのは不自然ではないか
- プラグイン名を会社内で一意に予約して運用する方が良いのではないか

この疑問を公式ドキュメントから詳細に検証しました。

## 公式ドキュメントからの重要な発見

### 1. 基本的な命名規則

#### Marketplace 名

出典: [Create and distribute a plugin marketplace - Claude Code Docs](https://code.claude.com/docs/en/plugin-marketplaces.md#marketplace-schema)

> Marketplace identifier (kebab-case, no spaces). This is public-facing: users see it when installing plugins (for example, `/plugin install my-tool@your-marketplace`).

**形式**: kebab-case（スペースなし）

**予約語**（使用禁止）:
- `claude-code-marketplace`
- `claude-code-plugins`
- `claude-plugins-official`
- `anthropic-marketplace`
- `anthropic-plugins`
- `agent-skills`
- `life-sciences`

出典: [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md#required-fields)

#### プラグイン名

出典: [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md#plugin-entries)

> Plugin identifier (kebab-case, no spaces). This is public-facing: users see it when installing (for example, `/plugin install my-plugin@marketplace`).

**形式**: kebab-case（スペースなし）

### 2. 複数の Marketplace の同時利用（最重要）

**公式ドキュメントで完全にサポートされています。**

出典: [Settings Documentation](https://code.claude.com/docs/en/settings.md)

```json
{
  "extraKnownMarketplaces": {
    "acme-tools": {
      "source": {
        "source": "github",
        "repo": "acme-corp/claude-plugins"
      }
    },
    "security-plugins": {
      "source": {
        "source": "git",
        "url": "https://git.example.com/security/plugins.git"
      }
    }
  }
}
```

**重要な事実**:
- 会社全体の共通 marketplace とプロジェクト固有の marketplace を併用する設計が可能
- `.claude/settings.json` で複数定義し、チーム全体で共有できる

### 3. プラグイン名のスコープと一意性（最重要）

**最重要な発見: プラグイン名の一意性は「marketplace内」で保証される**

出典: [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)

**重要な事実**:
- 同じプラグイン名が異なる marketplace に存在することは技術的に可能
- `@marketplace名` で明示的に指定すれば、プラグイン名が重複していても区別される
- `code-formatter@wonderplanet-shared` と `code-formatter@other-marketplace` は別のプラグインとして扱われる

出典: [Plugins Documentation](https://code.claude.com/docs/en/plugins.md)

**インストール構文**:
```bash
/plugin install code-formatter@company-tools
/plugin install code-formatter@project-specific
```

### 4. チームプロンプト機能による自動提案

出典: [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)

> When a repository includes `extraKnownMarketplaces`:
> 1. Team members are prompted to install the marketplace when they trust the folder
> 2. Team members are then prompted to install plugins from that marketplace
> 3. Users can skip unwanted marketplaces or plugins (stored in user settings)

**ベストプラクティス**:
- プロジェクトごとに必要な marketplace を `.claude/settings.json` で定義
- チームメンバーがプロジェクトを開いたときに自動的に marketplace のインストールを提案
- 不要なものはスキップ可能

### 5. Enterprise レベルでの Marketplace 制限

出典: [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)

```json
{
  "strictKnownMarketplaces": [
    { "source": "github", "repo": "company/shared-plugins" },
    { "source": "github", "repo": "company/glow-plugins" },
    { "source": "github", "repo": "company/kanban-plugins" }
  ]
}
```

**ベストプラクティス**:
- Enterprise 設定で「会社公認の marketplace のみ」を強制可能
- セキュリティとガバナンスの観点から重要

## 最終推奨設計: 共通 + プロジェクト固有の分離

公式ドキュメントの調査結果から、以下の設計が最適であることが確認できました。

### Marketplace 構成

```
wonderplanet-shared (会社共通プラグイン)
  ├─ code-formatter      # 全プロジェクトで使用可能
  ├─ deployment-tools    # 全プロジェクトで使用可能
  └─ test-runner         # 全プロジェクトで使用可能

wonderplanet-glow (GLOWプロジェクト固有)
  ├─ master-data-validator    # GLOW専用
  └─ game-config-generator    # GLOW専用

wonderplanet-kanban (Kanbanプロジェクト固有)
  ├─ task-manager        # Kanban専用
  └─ sprint-reporter     # Kanban専用
```

### この設計の利点

1. **プラグイン名の冗長性を排除**
   - `glow-code-formatter` ではなく、単に `code-formatter` で良い
   - marketplace で既に区別されているため、プラグイン名にプロジェクト名は不要

2. **他プロジェクトの名前を冠したプラグインを使う不自然さを解消**
   - 共通プラグインは `wonderplanet-shared` から提供
   - Kanban プロジェクトで `glow-xxx` という名前のプラグインを使う必要がない

3. **会社内での一意性を確保**
   - `code-formatter` は `wonderplanet-shared` 内で一意に予約
   - 新しいプロジェクトで同じ名前を使いたい場合は、共通 marketplace を使うか、別の名前を選択

4. **チームメンバーへの自動提案**
   - プロジェクトを開いたときに必要な marketplace とプラグインが自動提案される
   - 手動でのセットアップ作業が不要

5. **Enterprise レベルでの管理**
   - `strictKnownMarketplaces` で会社公認の marketplace のみに制限可能

## 実装例

### GLOWプロジェクトの `.claude/settings.json`

```json
{
  "extraKnownMarketplaces": {
    "wonderplanet-shared": {
      "source": {
        "source": "github",
        "repo": "wonderplanet/claude-plugins-shared"
      }
    },
    "wonderplanet-glow": {
      "source": {
        "source": "github",
        "repo": "wonderplanet/claude-plugins-glow"
      }
    }
  },
  "enabledPlugins": {
    "code-formatter@wonderplanet-shared": true,
    "deployment-tools@wonderplanet-shared": true,
    "master-data-validator@wonderplanet-glow": true,
    "game-config-generator@wonderplanet-glow": true
  }
}
```

### Kanbanプロジェクトの `.claude/settings.json`

```json
{
  "extraKnownMarketplaces": {
    "wonderplanet-shared": {
      "source": {
        "source": "github",
        "repo": "wonderplanet/claude-plugins-shared"
      }
    },
    "wonderplanet-kanban": {
      "source": {
        "source": "github",
        "repo": "wonderplanet/claude-plugins-kanban"
      }
    }
  },
  "enabledPlugins": {
    "code-formatter@wonderplanet-shared": true,
    "deployment-tools@wonderplanet-shared": true,
    "task-manager@wonderplanet-kanban": true,
    "sprint-reporter@wonderplanet-kanban": true
  }
}
```

### Marketplace の marketplace.json 例

#### wonderplanet-shared/marketplace.json

```json
{
  "name": "wonderplanet-shared",
  "owner": {
    "name": "Wonderplanet Development Team",
    "email": "dev@wonderplanet.com"
  },
  "metadata": {
    "description": "Wonderplanet 社内共通プラグイン集"
  },
  "plugins": [
    {
      "name": "code-formatter",
      "source": "./plugins/code-formatter",
      "description": "コードフォーマッター（全プロジェクト共通）",
      "version": "1.0.0"
    },
    {
      "name": "deployment-tools",
      "source": "./plugins/deployment-tools",
      "description": "デプロイメント自動化ツール（全プロジェクト共通）",
      "version": "1.0.0"
    }
  ]
}
```

#### wonderplanet-glow/marketplace.json

```json
{
  "name": "wonderplanet-glow",
  "owner": {
    "name": "GLOW Development Team",
    "email": "glow-dev@wonderplanet.com"
  },
  "metadata": {
    "description": "GLOW プロジェクト専用プラグイン集"
  },
  "plugins": [
    {
      "name": "master-data-validator",
      "source": "./plugins/master-data-validator",
      "description": "マスターデータバリデーター（GLOW専用）",
      "version": "1.0.0"
    },
    {
      "name": "game-config-generator",
      "source": "./plugins/game-config-generator",
      "description": "ゲーム設定ファイル生成ツール（GLOW専用）",
      "version": "1.0.0"
    }
  ]
}
```

## 命名規則まとめ

| 項目 | 推奨パターン | 例 | 理由 |
|------|------------|-----|------|
| **共通 Marketplace 名** | `会社名-shared` | `wonderplanet-shared` | 会社全体で共有するプラグインを配置 |
| **プロジェクト Marketplace 名** | `会社名-プロジェクト名` | `wonderplanet-glow` | プロジェクト固有のプラグインを配置 |
| **共通プラグイン名** | `機能名` | `code-formatter` | シンプルで読みやすい、会社内で一意に予約 |
| **プロジェクト固有プラグイン名** | `機能名` or `プロジェクト名-機能名` | `master-data-validator` or `glow-master-data-validator` | marketplace で既に区別されるため、プロジェクト名は任意 |

## 運用ガイドライン

### プラグイン名の予約ルール

#### 1. 共通プラグイン（`wonderplanet-shared`）
- 会社内で一意の名前を使用
- 他のプロジェクトで同じ機能が必要な場合は、この共通プラグインを使う
- 新しい共通プラグインを追加する際は、既存の名前と衝突しないか確認

#### 2. プロジェクト固有プラグイン
- プロジェクト marketplace 内で一意であれば良い
- 明示的にプロジェクト名を含めたい場合は `glow-xxx` のような命名も可能
- ただし、marketplace で既に区別されるため、通常は不要

### 新しいプロジェクト開始時の手順

1. プロジェクト専用の marketplace を作成（例: `wonderplanet-newproject`）
2. `.claude/settings.json` に共通 marketplace とプロジェクト marketplace を追加
3. 必要なプラグインを `enabledPlugins` で指定
4. チームメンバーに共有

### ユーザーのインストール手順

```bash
# Marketplace を追加（初回のみ）
/plugin marketplace add owner/wonderplanet-shared
/plugin marketplace add owner/wonderplanet-glow

# プラグインをインストール
/plugin install code-formatter@wonderplanet-shared
/plugin install deployment-tools@wonderplanet-shared
/plugin install master-data-validator@wonderplanet-glow
```

ただし、`.claude/settings.json` で `extraKnownMarketplaces` を設定している場合、チームメンバーがプロジェクトを開いたときに自動的に marketplace とプラグインのインストールが提案されます。

### Enterprise 設定（オプション）

会社公認の marketplace のみを許可する場合：

```json
{
  "strictKnownMarketplaces": [
    { "source": "github", "repo": "wonderplanet/claude-plugins-shared" },
    { "source": "github", "repo": "wonderplanet/claude-plugins-glow" },
    { "source": "github", "repo": "wonderplanet/claude-plugins-kanban" }
  ]
}
```

## 未確認事項

### 同じプラグインを複数の Marketplace に登録することについて

ドキュメントには明示的な記載がありませんが、技術的には可能と思われます：

```json
// wonderplanet-shared/marketplace.json
{
  "plugins": [
    {
      "name": "code-formatter",
      "source": { "source": "github", "repo": "wonderplanet/code-formatter" }
    }
  ]
}

// wonderplanet-glow/marketplace.json
{
  "plugins": [
    {
      "name": "code-formatter",
      "source": { "source": "github", "repo": "wonderplanet/code-formatter" }
    }
  ]
}
```

ただし、この運用が推奨されるかどうかは公式ドキュメントに記載がないため、必要に応じて Anthropic サポートへの確認を推奨します。

## 参照ドキュメント

以下の公式ドキュメントを参照し、全てのURLの有効性を確認済みです：

- [Plugin Marketplaces Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)
  - Marketplace Schema
  - Plugin Entries
  - Required Fields
  - Team Prompts
  - Enterprise Settings
- [Plugins Documentation](https://code.claude.com/docs/en/plugins.md)
  - Installation Syntax
- [Settings Documentation](https://code.claude.com/docs/en/settings.md)
  - extraKnownMarketplaces
  - strictKnownMarketplaces

## 次のアクション

### 1. 共通 marketplace の作成
- リポジトリ: `wonderplanet/claude-plugins-shared`
- marketplace.json の作成
- 共通プラグインの移行（既存のプラグインから抽出）

### 2. プロジェクト固有 marketplace の整理
- `wonderplanet-glow` marketplace の作成
- GLOW 専用プラグインの整理

### 3. `.claude/settings.json` の更新
- GLOWプロジェクトでの設定追加
- 他のプロジェクトでも同様に設定
- チームメンバーへの共有

### 4. 運用ルールの文書化
- プラグイン名の予約ルールを社内ドキュメント化
- 新しいプロジェクト開始時のガイドライン作成
- チームメンバー向けの利用マニュアル作成

---

*このレポートは `/claude-code:guide-chat` コマンドにより自動生成されました*
