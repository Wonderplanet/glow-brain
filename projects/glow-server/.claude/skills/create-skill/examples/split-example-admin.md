# Admin開発の分割例

Admin機能開発を技術領域別に2つのスキルに分割した実例を紹介します。

## 分割前の課題

### 元の構想: 統合スキル「admin-development」

最初は「Admin機能開発」でFilament全般をカバーしようとしました。

**想定される内容:**
- データベース設計
- Eloquent モデル実装
- Repository パターン
- リレーション設定
- N+1 問題対策
- Filament Resource 実装
- Table カラム定義
- Form フィールド定義
- Action 実装
- バリデーション

**問題点:**

1. **技術スタックが大きく異なる**
   - バックエンド: Laravel/Eloquent/Repository
   - フロントエンド: Filament/Blade/Livewire
   - 異なる知識が必要

2. **実装パターンが混在**
   - データ取得のパターン（Eloquent Query、Repository）
   - UI表示のパターン（Table、Form、Action）
   - 混在すると分かりにくい

3. **使用タイミングが異なる**
   - データ層: DB設計が決まった時点
   - UI層: データ取得が完了した時点
   - 常に同時に実装するわけではない

4. **参照ファイルが多すぎる**
   - データ層: 5-6個
   - UI層: 7-8個
   - 合計12-14個の参照ファイルが必要

## 分割案の検討

### スコープ評価

**判断基準の確認:**

- ✅ 技術スタックが異なる → **Laravel（バックエンド） vs Filament（フロントエンド）**
- ✅ 実装パターンが5つ以上 → **Eloquent/Repository/Table/Form/Actionなど8パターン**
- ✅ 使用タイミングが異なる → **データ層の実装 → UI層の実装**
- ✅ 参照ファイルが8個以上 → **想定12-14個**
- ✅ SKILL.mdが100行を超えそう → **想定150行以上**

→ **分割が必要**

### 分割軸の決定

**検討した軸:**

1. **技術領域別分割** ← 採用
   - データ取得・処理（バックエンド） vs UI構築（フロントエンド）
   - 各領域で技術スタックが異なる
   - 使用タイミングが明確

2. フェーズ別分割
   - 設計 → 実装 → テスト
   - AdminはUI層とデータ層が密接に関連するため不適

3. 機能別分割
   - CRUD → 一覧表示 → 詳細表示
   - 機能ごとに両方の層が必要なため重複が多い

**採用理由:**
- バックエンドとフロントエンドの責務が明確に分離
- 各領域の専門家が独立してスキルを改善可能
- 使用するタイミングで適切なスキルを選択できる

## 分割後の構成

### 1. admin-data-provider スキル（データ取得・処理）

**ファイル構成:**

```
.claude/skills/admin-data-provider/
├── SKILL.md (38行)
├── guides/
│   ├── eloquent-basics.md (140行)
│   ├── repository-pattern.md (150行)
│   └── n-plus-one-prevention.md (130行)
├── patterns/
│   └── query-optimization.md (145行)
└── examples/
    ├── simple-query.md (90行)
    └── complex-query.md (125行)
```

**SKILL.md (抜粋):**

```yaml
---
name: Providing Data for Admin Pages
description: Adminページのデータ取得・処理を実装する際に使用。Repository、Query、N+1対策を行い、効率的なデータ取得を実現する。
---

# Providing Data for Admin Pages

Admin画面のデータ取得と処理をサポートします。

## Instructions

### 1. Eloquent の基本を確認

モデル、リレーション、クエリビルダーの使い方を確認します。
参照: **[Eloquent基礎](guides/eloquent-basics.md)**

### 2. Repository パターンの実装

データアクセス層を Repository パターンで実装します。
参照: **[Repositoryパターン](guides/repository-pattern.md)**

### 3. N+1 問題の対策

Eager Loadingやクエリ最適化でN+1問題を回避します。
参照リスト:
- **[N+1問題対策](guides/n-plus-one-prevention.md)**
- **[クエリ最適化](patterns/query-optimization.md)**

### 4. 実装例の参照

実際のコードで実装パターンを確認します。
参照リスト:
- **[シンプルなクエリ](examples/simple-query.md)**
- **[複雑なクエリ](examples/complex-query.md)**

## 参照ドキュメント

### ガイド
- **[Eloquent基礎](guides/eloquent-basics.md)** - モデル、リレーション、クエリビルダー
- **[Repositoryパターン](guides/repository-pattern.md)** - Repository実装方法
- **[N+1問題対策](guides/n-plus-one-prevention.md)** - Eager Loadingとクエリ最適化

### パターン
- **[クエリ最適化](patterns/query-optimization.md)** - パフォーマンス向上のパターン

### 実装例
- **[シンプルなクエリ](examples/simple-query.md)** - 基本的なデータ取得例
- **[複雑なクエリ](examples/complex-query.md)** - JOINやサブクエリを使った例

## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[admin-ui-builder](../admin-ui-builder/SKILL.md)** - 取得したデータを画面に表示

**典型的な使用フロー:**
1. **`admin-data-provider`** でデータ取得を実装（このスキル）
2. `admin-ui-builder` でFilament UIを実装
```

**規模:**
- SKILL.md: 38行
- 参照ファイル: 5個（合計780行）
- 総行数: 約818行

### 2. admin-ui-builder スキル（UI構築）

**ファイル構成:**

```
.claude/skills/admin-ui-builder/
├── SKILL.md (42行)
├── guides/
│   ├── filament-basics.md (150行)
│   └── resource-structure.md (130行)
├── patterns/
│   ├── table-pattern.md (180行)
│   ├── form-pattern.md (190行)
│   └── action-pattern.md (160行)
└── examples/
    ├── simple-resource.md (110行)
    └── complex-resource.md (145行)
```

**SKILL.md (抜粋):**

```yaml
---
name: Building Admin UI Components
description: Filament UI コンポーネントを実装する際に使用。テーブル、フォーム、アクションの実装を行い、管理画面を構築する。
---

# Building Admin UI Components

Filament UIコンポーネントの実装をサポートします。

## Instructions

### 1. Filament の基本を確認

Resource、Table、Formの基本構造を確認します。
参照リスト:
- **[Filament基礎](guides/filament-basics.md)**
- **[Resource構造](guides/resource-structure.md)**

### 2. Table の実装

一覧表示のテーブルを実装します。
参照: **[Tableパターン](patterns/table-pattern.md)**

### 3. Form の実装

作成・編集フォームを実装します。
参照: **[Formパターン](patterns/form-pattern.md)**

### 4. Action の実装

ボタンやカスタムアクションを実装します。
参照: **[Actionパターン](patterns/action-pattern.md)**

### 5. 実装例の参照

実際のResourceで実装パターンを確認します。
参照リスト:
- **[シンプルなResource](examples/simple-resource.md)**
- **[複雑なResource](examples/complex-resource.md)**

## 参照ドキュメント

### ガイド
- **[Filament基礎](guides/filament-basics.md)** - Filamentの基本概念
- **[Resource構造](guides/resource-structure.md)** - Resourceの構造と役割

### パターン
- **[Tableパターン](patterns/table-pattern.md)** - テーブル実装パターン
- **[Formパターン](patterns/form-pattern.md)** - フォーム実装パターン
- **[Actionパターン](patterns/action-pattern.md)** - アクション実装パターン

### 実装例
- **[シンプルなResource](examples/simple-resource.md)** - 基本的なCRUD Resource例
- **[複雑なResource](examples/complex-resource.md)** - カスタムアクション含む例

## 関連スキル

このスキルは以下のスキルと連携して動作します：

- **[admin-data-provider](../admin-data-provider/SKILL.md)** - データ取得・処理の実装

**典型的な使用フロー:**
1. `admin-data-provider` でデータ取得を実装
2. **`admin-ui-builder`** でFilament UIを実装（このスキル）
```

**規模:**
- SKILL.md: 42行
- 参照ファイル: 7個（合計1065行）
- 総行数: 約1107行

## 分割の効果

### Before（統合スキル）

```
admin-development スキル
├── SKILL.md (150行) ← 長すぎる
├── 参照ファイル (12-14個) ← 多すぎる
└── 総行数: 約2000行 ← 大きすぎる

問題点:
- バックエンドとフロントエンドが混在
- 技術スタックが異なるのに同じスキル
- 実装パターンが混在して分かりづらい
- 使用タイミングが不明確
```

### After（分割スキル）

```
admin-data-provider スキル
├── SKILL.md (38行)
├── 参照ファイル (5個)
└── 総行数: 約818行
└── 対象: Laravel/Eloquent/Repository

admin-ui-builder スキル
├── SKILL.md (42行)
├── 参照ファイル (7個)
└── 総行数: 約1107行
└── 対象: Filament/Blade/Livewire

利点:
- バックエンドとフロントエンドが明確に分離
- 各領域の専門家が独立してスキルを改善可能
- 使用するタイミングで適切なスキルを選択できる
- 技術スタック別にドキュメントが整理されている
```

### トークン消費の比較

**統合スキル:**
- 常に全体（約2000行）を読み込む
- 推定トークン: 約4000トークン

**分割スキル:**
- データ層のみ: admin-data-provider（約818行）
- UI層のみ: admin-ui-builder（約1107行）
- 推定トークン: 約1600-2200トークン（必要なスキルのみ）

→ **最大45%のトークン削減**

## 運用での成功例

### 実際の使用フロー

**ケース1: 新規Admin画面追加**

```
ステップ1: データ層の実装
ユーザー: 「Productsのデータ取得を実装してください」
Claude: admin-data-provider スキルを使用
→ Product Model、ProductRepository実装

ステップ2: UI層の実装
ユーザー: 「Products管理画面を作成してください」
Claude: admin-ui-builder スキルを使用
→ ProductResource、Table、Form実装
```

**ケース2: 既存画面の改修**

```
ケース2-1: データ層のみの改修
ユーザー: 「N+1問題を解決してください」
Claude: admin-data-provider スキルのみ使用
→ Eager Loading追加

ケース2-2: UI層のみの改修
ユーザー: 「テーブルにカラムを追加してください」
Claude: admin-ui-builder スキルのみ使用
→ Table定義に新しいカラム追加
```

### ユーザーフィードバック

> 「以前はバックエンドとフロントエンドが混在していて、どこを参照すれば良いか分からなかった。分割後は必要な領域だけを見れば良いので効率的」

> 「データ取得だけ改善したい時に admin-data-provider だけ使えるので、関係ないFilamentの情報が出てこない」

> 「バックエンド担当とフロントエンド担当で分かれている場合、各自が必要なスキルだけ参照できるので便利」

## 技術領域別分割の応用例

### 他の適用可能なケース

**1. フルスタック開発**
```
backend-api スキル
├── DB設計
├── ビジネスロジック
└── API実装

frontend-web スキル
├── コンポーネント実装
├── 状態管理
└── API連携
```

**2. モバイル + サーバー開発**
```
server-implementation スキル
├── サーバーサイド実装
├── API設計
└── データベース

mobile-client スキル
├── モバイルUI実装
├── API連携
└── ローカルストレージ
```

**3. インフラ + アプリ開発**
```
infra-setup スキル
├── Docker設定
├── CI/CD設定
└── デプロイ自動化

app-development スキル
├── アプリケーション実装
├── テスト
└── ドキュメント
```

## まとめ

Admin開発を技術領域別に2つのスキルに分割した結果：

**成功要因:**
- バックエンドとフロントエンドの明確な分離
- 各技術スタックに特化した内容
- 使用タイミングが明確（データ層→UI層）
- トークン消費を最大45%削減
- 専門家による独立した保守が可能

**適用可能な他のケース:**
- フルスタック開発（Backend + Frontend）
- モバイル + サーバー開発
- インフラ + アプリ開発
- データベース + ビジネスロジック

この分割パターンは、技術スタックが大きく異なる開発タスクに広く適用できます。
