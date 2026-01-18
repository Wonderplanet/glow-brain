# ツールマッチングルール

## マッチング優先順位

スキル/サブエージェントの選定は以下の優先順位で行う：

### 優先度1: 直接マッチング（完全一致）

タスク内容とスキルのdescriptionが完全に一致する場合、最優先で選択。

**判定基準：**
- タスクのキーワード全てがスキルのdescriptionに含まれる
- 用途・対象・工程が完全一致

**例：**
- タスク: "glow-schemaのYAML定義を確認" → `api-schema-reference`（用途完全一致）
- タスク: "mstDBにマイグレーション実行" → `migration`（対象・操作完全一致）

### 優先度2: 工程ベースマッチング

タスクが特定の開発工程に属する場合、工程別推奨ツールから選択。

**判定基準：**
- タスクが「スキーマ確認」「マイグレーション」「ドメイン層実装」等の標準工程に分類できる
- 工程別推奨ツール表（後述）に該当項目がある

### 優先度3: キーワードベースマッチング

タスクキーワードから部分一致でスキルを検索。

**判定基準：**
- タスク内の主要キーワード（API/admin/test/migration等）がスキル名またはdescriptionに含まれる
- ただし、複数マッチした場合は優先度4の複合判定へ

### 優先度4: 複合判定（複数マッチ時）

複数のスキルがマッチした場合、以下の基準で絞り込む：

1. **スコープの狭いスキルを優先**
   - 専門スキル > 汎用スキル
   - 例: `api-endpoint-implementation`（API実装専門） > `domain-layer`（ドメイン層全般）

2. **完全性が高いスキルを優先**
   - 一括処理スキル > 部分処理スキル
   - 例: `api-endpoint-implementation`（全体フロー） > `api-response`（レスポンスのみ）

3. **最終更新日が新しいスキルを優先**
   - 新しいパターンに対応している可能性が高い

### 優先度5: general-purposeサブエージェント（最終手段）

上記のいずれにもマッチしない場合のみ、汎用サブエージェントを使用。

---

## キーワードベースのマッチング

| タスクキーワード | 検索対象 |
|-----------------|---------|
| API, エンドポイント, Controller | `api-*` skills |
| admin, 管理画面, Filament | `admin-*` skills |
| テスト, PHPUnit, test | `*test*` skills/agents |
| マイグレーション, DB, テーブル | `migration`, `database-*` skills |
| エラー, 修正, fix | `*fixer*` agents |
| スキーマ, YAML, glow-schema | `schema-*`, `api-schema-*` skills |
| 報酬, reward | `reward-*` skills |
| PR, レビュー, コメント | `pr-*` skills |
| SDD, 仕様, 設計 | `sdd-*` skills |

## 工程別の推奨ツール（拡張版）

### スキーマ確認

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| glow-schemaのYAML定義を確認 | `api-schema-reference` | YAML構造の理解に特化 |
| リクエストパラメータの型を確認 | `api-schema-reference` | 型定義の解説を提供 |
| レスポンス構造を確認 | `api-schema-reference` | レスポンススキーマの解説 |

### マイグレーション

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| mst/mng/usrテーブル作成 | `migration` | 複数DB対応 |
| カラム追加・変更 | `migration` | スキーマ変更パターン完備 |
| マイグレーション実行 | `migration` | 実行手順を含む |

### ドメイン層実装

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| Entity/Model作成 | `domain-layer` | クリーンアーキテクチャ準拠 |
| Repository実装 | `domain-layer` | 既存パターンの踏襲 |
| Service実装 | `domain-layer` | ビジネスロジック層の実装 |
| Delegator実装 | `domain-layer` | return型ルールを理解 |

### API実装

| タスク例 | 推奨Skill | 併用Skill | 理由 |
|---------|----------|----------|------|
| 新規APIエンドポイント追加 | `api-endpoint-implementation` | `api-test-implementation` | 全体フロー+テスト |
| Controllerのみ作成 | `api-endpoint-implementation` | - | Controller特化 |
| ResponseFactory追加 | `api-response` | - | レスポンス特化 |
| バリデーション追加 | `api-request-validation` | - | バリデーション特化 |

### テスト実装

| タスク例 | 推奨Skill | 推奨Subagent | 理由 |
|---------|----------|-------------|------|
| Unit/Feature/Scenarioテスト作成 | `api-test-implementation` | `api-test-fixer` | 包括的なテスト実装 |
| テスト実行+エラー修正 | - | `api-test-fixer` | エラー修正に特化 |

### 品質チェック

| タスク例 | 推奨Skill | 推奨Subagent | 理由 |
|---------|----------|-------------|------|
| sail check 一括実行 | `sail-check-fixer` | - | phpcs/phpstan/deptrac/test一括 |
| PHPStanエラーのみ修正 | - | `api-phpstan-fixer` | 静的解析特化 |
| phpcs/phpcbfエラーのみ修正 | - | `api-phpcs-phpcbf-fixer` | コーディング規約特化 |
| deptracエラーのみ修正 | - | `api-deptrac-fixer` | アーキテクチャ違反特化 |

### Admin実装

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| Filamentリソース作成 | `admin-*` skills | 管理画面特化 |
| 報酬情報表示 | `admin-reward-display` | 報酬表示パターン完備 |
| Athenaクエリ実装 | `admin-athena-query` | Athena特化 |

### 報酬送付

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| RewardDelegator経由の報酬送付 | `api-reward-send-service` | RewardSendPolicy対応 |
| 各種報酬タイプの実装 | `api-reward-send-service` | 報酬タイプ別パターン |
| 新規リソース追加 | `api-reward-send-service` | リソース追加手順提供 |

### SDD設計

| タスク例 | 推奨Skill | 理由 |
|---------|----------|------|
| 要件調査 | `api-sdd-v2-requirements-investigation` | PDF+コード統合調査 |
| 仕様確認 | `api-sdd-v2-spec-confirmation` | 不明点整理 |
| API設計書作成 | `api-sdd-v2-api-design` | 実装可能な設計書 |
| 設計書レビュー | `api-sdd-v2-api-design-review` | チェックリスト準拠 |

## 確実性を高めるためのマッチング手順

orchestrate-task-plannerサブエージェントは、以下の手順でツールマッチングを実行します：

### Step 1: タスク分析

1. タスク内容から**主要キーワード**を抽出
   - 技術要素（API/admin/test/migration/DB等）
   - 操作（作成/修正/確認/実行等）
   - 対象（Controller/Entity/テーブル/YAML等）

2. タスクを**開発工程**に分類
   - スキーマ確認/マイグレーション/ドメイン層/API層/テスト/品質チェック

3. タスクの**完了条件**を明確化
   - 1ファイル作成？複数ファイル作成？実行のみ？

### Step 2: スキルカタログ検索

1. `.claude/skills/**/SKILL.md`をスキャン
2. 各スキルのfrontmatter（name, description）を抽出
3. descriptionから【用途】【対象】【使用時機】を特定

### Step 3: マッチング実行

1. **直接マッチング**を試行
   - タスクキーワード全てがdescriptionに含まれる？
   - YES → そのスキルを採用、Step 4へ
   - NO → Step 3.2へ

2. **工程ベースマッチング**を試行
   - タスクが標準工程に分類できる？
   - YES → 工程別推奨ツール表から選択、Step 4へ
   - NO → Step 3.3へ

3. **キーワードベースマッチング**を試行
   - タスクキーワードがスキル名/descriptionに含まれる？
   - マッチ0件 → general-purposeを使用
   - マッチ1件 → そのスキルを採用
   - マッチ2件以上 → Step 3.4へ

4. **複合判定**で絞り込み
   - スコープの狭さ、完全性、更新日で比較
   - 最適なスキルを1つ選択

### Step 4: 検証

選択したスキルのdescriptionを再度確認：
- タスクの完了条件を満たすか？
- 必要な全ての操作を含むか？
- 他のスキルとの併用が必要か？

**併用が必要な場合の例：**
- `api-endpoint-implementation`（Controller作成） + `api-test-implementation`（テスト作成）
- `migration`（マイグレーション） + `domain-layer`（Entity/Model作成）

---

## 複数ツール割当のパターン

### パターン1: 実装 + エラー修正

```
TODO: StaminaRecoveryServiceTest 作成
推奨Skill: api-test-implementation
推奨Subagent: api-test-fixer (エラー発生時)
```

### パターン2: 並列実行可能な品質チェック

```
グループ: 品質チェック（順次実行推奨）
- phpcs → api-phpcs-phpcbf-fixer
- phpstan → api-phpstan-fixer
- deptrac → api-deptrac-fixer
```

### パターン3: 複合スキル使用

```
TODO: API実装
推奨Skill:
  1. api-endpoint-implementation (Controller)
  2. api-response (ResponseFactory)
  3. api-request-validation (バリデーション)
```

## システム組み込みSubagents

Taskツールで利用可能：
- `general-purpose`: 複雑なマルチステップタスクの自律的処理
- `Explore`: コードベース探索（quick/medium/very thorough）
- `Plan`: 実装計画の設計
- `claude-code-guide`: Claude Code/Agent SDKの使い方
- `skill-gap-analyzer`: スキルギャップ分析（agents/general配下）
