# Claude Code スキル一覧

glow-serverプロジェクトで利用可能なClaude Codeスキルの概要です。

## スキル作成ガイド

新しいスキルを作成する際は [HOW_TO_CREATE_SKILLS.md](HOW_TO_CREATE_SKILLS.md) を参照してください。

## スキル一覧

### Admin開発

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **admin-athena-query** | ログページのAthena対応 | 新規ログテーブルの履歴ページ実装時に使用。AthenaQueryTraitでの過去ログ参照、Athenaテーブル定義SQL生成 |
| **admin-browser-tester** | admin実装後の動作確認 | ブラウザで実際の動作確認を自動化。CRUD機能、バリデーション、表示項目変更などをテスト |
| **admin-page-navigator** | 特定ページへの遷移 | FilamentのURL構造を理解し、必要なクエリパラメータを特定してURLで直接遷移 |
| **admin-reward-display** | 報酬情報の表示実装 | テーブルやフォームで報酬情報を扱う実装。RewardInfoGetTraitを使ってN+1問題を回避 |
| **admin-test-data-finder** | テストデータ探索 | usr/logDBから条件に応じたプレイヤーデータを検索し、テストに最適なusr_user_idを特定 |

### API開発（基本）

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **api-endpoint-implementation** | 新規APIエンドポイント追加 | glow-schema確認からルーティング定義、Controller・ResultData実装、テストまでの全体フローを提供 |
| **api-schema-reference** | API仕様確認 | glow-schemaのYAML定義からリクエストパラメータやレスポンス構造を確認 |
| **api-request-validation** | リクエストバリデーション実装 | YAML定義からLaravelバリデーションルールへの変換、Controller実装 |
| **api-response** | レスポンス実装 | ResponseFactoryにメソッド追加。日時データのISO8601変換、YAMLとの一致を保証 |
| **api-sequence-diagram-generator** | シーケンス図生成 | APIエンドポイントの詳細なシーケンス図（Mermaid形式）を生成。ルーティングからレスポンスまでの処理フローを可視化 |
| **domain-layer** | Domain層実装 | クリーンアーキテクチャに基づいたEntity、Repository、Service、UseCaseの実装 |
| **api-reward-send-service** | 報酬送付実装 | RewardDelegator経由でRewardSendServiceを使った報酬配布、新規リソース追加、RewardSendPolicy/UnreceivedRewardReasonの調整方法を包括的に提供 |

### API開発（テスト）

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **api-test-implementation** | テスト実装 | PHPUnitテスト（Unit/Feature/Scenario）作成。Factory/Mockeryを使ったテストデータ準備 |
| **api-test-runner** | テスト実行・修正 | テストを実行し、失敗パターンを分析して自動修正。全テストが通る状態にする |
| **usr-model-manager-user-id-check** | UsrModelManagerエラー対応 | user id checkエラーの原因特定とRepository/UseCase/Service層での修正 |

### BNE外部決済システム

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **bne-external-payment-platform** | プラットフォーム連携 | Apple StoreKit/Google Play Billingから国コード・通貨コード取得 |
| **bne-external-payment-purchase** | 購入処理・検証 | アイテム付与、年齢制限、国チェック、同時購入制御、購入履歴管理 |
| **bne-external-payment-webhook** | ウェブフック処理 | Xsolla WebStoreからのウェブフック受信、署名検証、ユーザ情報取得 |

### DB・マイグレーション

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **database-query** | DB操作 | glow-server-local-db MCPでテーブル構造確認、データ検索、CRUD操作 |
| **migration** | マイグレーション実装 | 複数DB（mst/mng/usr/log/sys/admin）のマイグレーション作成、実行、ロールバック |

### コード品質・コマンド実行

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **sail-check-fixer** | コード品質チェック | `sail check`実行。phpcs/phpcbf、phpstan、deptrac、testの全エラーを解消 |
| **sail-execution** | sailコマンド実行 | sail-wpコマンドを正しく実行。`cd api`などのディレクトリ移動ミスを防止 |

### その他の機能実装

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **schema-pr-implementer** | glow-schema PR反映 | glow-schemaのYAML変更をglow-serverに反映。マイグレーション、Entity/Model更新 |
| **ecs-config-synchronizer** | ECS設定同期 | 環境変数やDockerfile変更をcodebuild配下のECS設定ファイルに反映 |

### オーケストレーション・自動化

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **sdd-orchestrator** | SDD設計フロー自動化 | 新機能のSDD作成において8段階を自動実行。PDF仕様書からAPI設計書まで一貫して生成 |
| **sdd-orchestrator-v2** | SDD v2設計フロー | SDDの3段階統合版。要件調査→仕様確認→API設計を効率化 |
| **skill-flow-orchestrator** | スキル/サブエージェント連携 | 複数スキルを効率的に連携させるフロー実行エンジン。依存関係分析、並列実行最適化 |

### スキル・サブエージェント作成

| スキル名 | 使用場面 | 説明 |
|---------|---------|------|
| **create-skill** | 新スキル作成 | Progressive Disclosureパターンに従った新規スキル作成。スコープ評価、分割提案を含む |
| **create-subagent** | 新サブエージェント作成 | 専門分野、トリガー条件、モデル選択を定義した新規サブエージェント生成 |

## スキルの使い方

### 自動起動

Claude Codeは、ユーザーのリクエスト内容を解析して、適切なスキルを自動的に認識・起動します。

**例：**
- 「マイグレーションを作成して」→ `migration` スキルが自動起動
- 「APIレスポンスを実装して」→ `api-response` スキルが自動起動
- 「ブラウザでadmin画面をテストして」→ `admin-browser-tester` スキルが自動起動

### 手動起動

必要に応じて、スキル名を明示的に指定することもできます：

```
skill-name を使って〇〇を実装して
```

## 関連ドキュメント

- **各スキルの詳細**: 各スキルディレクトリ内の `skill.md` を参照
- **スキル作成ガイド**: [HOW_TO_CREATE_SKILLS.md](HOW_TO_CREATE_SKILLS.md)
