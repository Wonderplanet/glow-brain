---
name: admin-page-navigator
description: glow-server管理ツール(Filament)の特定ページへ直接URLで遷移するスキル。以下の場合に使用:管理画面への遷移、特定ページを開く、直接URLでアクセス、Filamentページの確認、admin URLの構築。FilamentのURL構造を理解し、必要なパラメータを特定して、Resourceページ(一覧/詳細/編集)、カスタムページ(ダッシュボード)、リレーションマネージャーページのURLを構築。chrome-devtools MCPまたはブラウザでの遷移テストに対応。「管理画面に遷移」「特定ページを開く」「URLでアクセス」「Filamentページを確認」「admin URLを構築」などのリクエストで起動。
---

# Admin Page Navigation

glow-server管理ツール(admin)の特定ページへ直接URLで遷移する方法を支援します。

## Instructions

### 1. URL構造の理解

Filamentのルーティング構造を理解:
参照: **[url-structure.md](url-structure.md)**

### 2. 遷移先URLの特定

要件に応じて以下を参照:

- **Resourceページ** → **[resource-urls.md](examples/resource-urls.md)**
- **カスタムページ** → **[custom-page-urls.md](examples/custom-page-urls.md)**

### 3. URLの構築と遷移

1. 必要なパラメータ(userId, unitId等)を特定
2. URLを構築
3. chrome-devtools MCPで遷移テスト、またはブラウザで直接開く

## 参照ドキュメント

- **[url-structure.md](url-structure.md)** - FilamentのURL構造とルーティング
- **[examples/resource-urls.md](examples/resource-urls.md)** - Resourceページへの遷移
- **[examples/custom-page-urls.md](examples/custom-page-urls.md)** - カスタムページへの遷移
