# SDD（Spec-Driven Development）コマンド

ゲーム体験仕様書からサーバーAPI実装までの要件定義・設計プロセスを支援するコマンド群です。

## 概要

SDDプロセスは8つのステップで構成され、要件定義フェーズ（Stage 1-5）と設計フェーズ（Stage 6-8）に分かれています。

**要件定義フェーズ（Stage 1-5）**
- 順次実行（各ステップの出力が次のステップの入力になる）
- ゲーム体験仕様書からサーバー要件を抽出・詳細化・確定

**設計フェーズ（Stage 6-8）**
- 並列実行可能（3つのステップを同時実行）
- 60%高速化（従来の順次実行比）
- 完全なサーバーAPI要件書を基に、異なる観点から設計を行う

## クイックスタート

### 全フロー一括実行（推奨）

```bash
/sdd:run-full-flow {機能名}
```

このコマンドで8つのステップ全てを自動実行します。
- ⏱️ **所要時間**: 約25-30分（人間確認を除く）
- ⚡ **高速化**: 設計フェーズ（Stage 6-8）の並列実行により60%高速化
- ⚠️ **注意**: Stage 4でプランナーへの確認結果の提供が必要

### 実行前チェックリスト

- [ ] ゲーム体験仕様書PDFを `docs/sdd/features/{機能名}/` に配置済み
- [ ] glow-schemaリポジトリがクローン済み（`../glow-schema`）
- [ ] Stage 4でプランナー確認を実施できる準備がある

### 個別実行

各ステップを個別に実行することも可能です。詳細は「プロンプトの順番と意味」を参照。

## プロンプトの順番と意味

### 要件定義フェーズ（01-05）

#### 01. サーバー要件抽出
- 📄 **INPUT**: `docs/sdd/features/{機能名}/ゲーム体験仕様書.pdf`
- 🎯 **目的**: 仕様書からサーバー関与が必要な項目を抽出
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/01_サーバー要件抽出.md`
- 💻 **コマンド**: `/sdd:extract-server-requirements {機能名}`

#### 02. コード調査追記
- 📄 **INPUT**: 01_サーバー要件抽出.md + コードベース
- 🎯 **目的**: 仕様書に書かれていない暗黙の前提・制約を洗い出し
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/02_サーバー要件_コード調査追記.md`
- 💻 **コマンド**: `/sdd:investigate-code-requirements {機能名}`

#### 03. サーバー仕様レビュー
- 📄 **INPUT**: 01_サーバー要件抽出.md + 02_サーバー要件_コード調査追記.md
- 🎯 **目的**: 仕様を詳細化し、曖昧さ・不明点を評価
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/03_サーバー仕様レビュー.md`（プランナー確認項目リスト付き）
- 💻 **コマンド**: `/sdd:review-server-spec {機能名}`

#### 04. ゲーム体験仕様確認結果まとめ
- 📄 **INPUT**: 03_サーバー仕様レビュー.md + 人間の確認結果
- 🎯 **目的**: プランナー確認結果を整理し、不明点の解決状況を評価
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/04_ゲーム体験仕様確認結果まとめ.md`
- 💻 **コマンド**: `/sdd:confirm-game-experience-spec {機能名}`
- ⚠️ **注意**: コマンド実行後、AIが確認結果の提供を依頼するので、その時に提供する

#### 05. サーバーAPI要件書まとめ
- 📄 **INPUT**: 上記4つ全て
- 🎯 **目的**: 全ての情報を統合し、完全で一貫性のある要件書を作成
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/05_サーバーAPI要件書.md`
- 💻 **コマンド**: `/sdd:finalize-server-requirements {機能名}`

---

### 設計フェーズ（06-08）

#### 06. API実装全体概要設計
- 📄 **INPUT**: 05_サーバーAPI要件書.md
- 🎯 **目的**: どのAPIエンドポイントが関わるか全体像を把握（新規 vs 既存改修）
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/06_API実装全体概要設計.md`
- 💻 **コマンド**: `/sdd:overview-api-design {機能名}`

#### 07. サーバーAPI設計書作成
- 📄 **INPUT**: 05_サーバーAPI要件書.md
- 🎯 **目的**: 各APIの詳細仕様を設計（リクエスト・レスポンス・エラー）
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/07_サーバーAPI設計書.md`
- 💻 **コマンド**: `/sdd:create-api-design {機能名}`

#### 08. サーバーAPI機能要件実装設計
- 📄 **INPUT**: 05_サーバーAPI要件書.md
- 🎯 **目的**: 実装の詳細設計（DB設計・ドメイン設計・クリーンアーキテクチャ）
- 📤 **OUTPUT**: `docs/sdd/features/{機能名}/08_サーバーAPI機能要件実装設計.md`
- 💻 **コマンド**: `/sdd:design-api-implementation {機能名}`

---

## フロー図

```
                     【要件定義フェーズ（順次実行）】
PDF → 01 → 02 → 03 → [人間確認] → 04 → 05
      ↓    ↓    ↓                 ↓    ↓
    抽出  調査  レビュー          確認  統合

                                  ↓

            【設計フェーズ（並列実行・60%高速化）】
                            ┌─────┼─────┐
                            ↓     ↓     ↓
                           06    07    08
                            ↓     ↓     ↓
                       概要設計  API設計  実装設計
```

**要件定義フェーズ（Stage 1-5）**: 「何を作るか」を確定（順次実行）
**設計フェーズ（Stage 6-8）**: 「どう作るか」を設計（並列実行可能）

---

## 使用例

### 方法1: 全フロー一括実行（推奨）

#### 1. 準備
```bash
# ディレクトリ作成
mkdir -p "docs/sdd/features/スタミナブースト"

# ゲーム体験仕様書.pdfを配置
# docs/sdd/features/スタミナブースト/ゲーム体験仕様書.pdf
```

#### 2. 全フロー実行
```bash
/sdd:run-full-flow スタミナブースト
```

このコマンドで以下が自動実行されます：

**要件定義フェーズ（順次実行）**
- Stage 1: サーバー要件抽出
- Stage 2: コード調査追記
- Stage 3: サーバー仕様レビュー
- ⚠️ **一時停止**: プランナーへの確認結果を提供
- Stage 4: ゲーム体験仕様確認結果まとめ
- Stage 5: サーバーAPI要件書まとめ

**設計フェーズ（並列実行・60%高速化）**
- Stage 6, 7, 8を同時実行:
  - Stage 6: API実装全体概要設計
  - Stage 7: サーバーAPI設計書作成
  - Stage 8: サーバーAPI機能要件実装設計

---

### 方法2: 個別ステップ実行

#### 1. 準備
```bash
# ディレクトリ作成
mkdir -p "docs/sdd/features/スタミナブースト"

# ゲーム体験仕様書.pdfを配置
# docs/sdd/features/スタミナブースト/ゲーム体験仕様書.pdf
```

#### 2. 要件定義フェーズ（順次実行）
```bash
/sdd:extract-server-requirements スタミナブースト
/sdd:investigate-code-requirements スタミナブースト
/sdd:review-server-spec スタミナブースト

# プランナーに確認（人間が実施）
# サーバー仕様レビュー.md の「4. プランナーへ確認が必要な項目まとめ」を参照

/sdd:confirm-game-experience-spec スタミナブースト
# → 確認結果を提供

/sdd:finalize-server-requirements スタミナブースト
```

#### 3. 設計フェーズ（並行実行可能）
```bash
/sdd:overview-api-design スタミナブースト
/sdd:create-api-design スタミナブースト
/sdd:design-api-implementation スタミナブースト
```

#### 4. 生成されるファイル
```
docs/sdd/features/スタミナブースト/
├── ゲーム体験仕様書.pdf                         # 入力（手動配置）
├── 01_サーバー要件抽出.md                       # Stage 1の出力
├── 02_サーバー要件_コード調査追記.md            # Stage 2の出力
├── 03_サーバー仕様レビュー.md                   # Stage 3の出力
├── 04_ゲーム体験仕様確認結果まとめ.md           # Stage 4の出力
├── 05_サーバーAPI要件書.md                      # Stage 5の出力（要件定義完了）
├── 06_API実装全体概要設計.md                    # Stage 6の出力
├── 07_サーバーAPI設計書.md                      # Stage 7の出力
└── 08_サーバーAPI機能要件実装設計.md            # Stage 8の出力（設計完了）
```

---

## アーキテクチャ

### コマンドファイル
- **場所**: `.claude/commands/sdd/00-sdd-run-full-flow.md`（オーケストレーター）、`01-*.md` 〜 `08-*.md`（個別ステップ）
- **役割**: スラッシュコマンドの定義
- **トリガー**: `/sdd:xxx` として実行

### プロンプトテンプレート
- **場所**: `docs/sdd/prompts/01_*_テンプレート.md` 〜 `08_*_テンプレート.md`
- **役割**: 各ステップの詳細な作業手順、出力フォーマットを定義
- **使用方法**: コマンド実行時に `{FEATURE_NAME}` が機能名に置換される

### サブエージェント
- **場所**: `.claude/agents/api/sdd/01-*.md` 〜 `08-*.md`
- **役割**: 各コマンドに対応する専門エージェント
- **トリガー**: `/sdd:xxx` コマンド実行時に自動起動
- **構成**: プロンプトテンプレートを参照し、役割・基本原則・簡潔なステップ概要を定義

### スキル
- **場所**: `.claude/skills/sdd-orchestrator/`
- **役割**: 全8段階のオーケストレーション、並列実行制御、エラーハンドリング
- **トリガー**: `/sdd:run-full-flow` コマンド実行時に自動起動
- **詳細ドキュメント**:
  - `SKILL.md` - スキル概要と使用方法
  - `stage-details.md` - 各Stageの詳細な責務・入出力仕様
  - `orchestration-patterns.md` - 実行フロー、並列実行戦略、トークン最適化
  - `examples.md` - 具体的な使用例（スタミナブースト等）
  - `troubleshooting.md` - エラーハンドリング、トラブルシューティング

---

## 各フェーズの目的

### 要件定義フェーズの目的
1. **仕様書から抽出**（01）: 公式な要件を取得
2. **コードから補完**（02）: 暗黙の前提を明示化
3. **レビューで詳細化**（03）: 曖昧さを洗い出し
4. **確認で確定**（04）: 不明点を解消
5. **統合で完成**（05）: 完全な要件書を作成

### 設計フェーズの目的
6. **全体概要**（06）: APIエンドポイントの鳥瞰図
7. **API詳細**（07）: リクエスト・レスポンス仕様
8. **実装設計**（08）: DB・ドメイン層の設計

---

## 重要な原則

### 要件定義フェーズ
- **実装非依存**: 「何を実現するか」のみ、「どう実装するか」には言及しない
- **中立性**: 実装方式を断定しない
- **トレーサビリティ**: 各要件の出典を明記
- **完全性**: 曖昧さを排除

### 設計フェーズ
- **具体性**: 実装に必要な詳細まで設計
- **一貫性**: 既存実装との整合性を保つ
- **glow-server準拠**: クリーンアーキテクチャに従う

---

## コマンド一覧

### オーケストレーターコマンド
- `/sdd:run-full-flow {機能名}` - 全8ステップを一括実行（推奨）

### 個別実行コマンド
- `/sdd:extract-server-requirements {機能名}` - 01. サーバー要件抽出
- `/sdd:investigate-code-requirements {機能名}` - 02. コード調査追記
- `/sdd:review-server-spec {機能名}` - 03. サーバー仕様レビュー
- `/sdd:confirm-game-experience-spec {機能名}` - 04. ゲーム体験仕様確認結果まとめ
- `/sdd:finalize-server-requirements {機能名}` - 05. サーバーAPI要件書まとめ
- `/sdd:overview-api-design {機能名}` - 06. API実装全体概要設計
- `/sdd:create-api-design {機能名}` - 07. サーバーAPI設計書作成
- `/sdd:design-api-implementation {機能名}` - 08. サーバーAPI機能要件実装設計

---

## トラブルシューティング

### コマンドが動作しない
- `.claude/commands/sdd/00-sdd-run-full-flow.md` および `01-*.md` 〜 `08-*.md` が存在するか確認
- 機能名に特殊文字が含まれていないか確認（日本語は使用可能）
- スラッシュコマンドの形式が正しいか確認（`/sdd:run-full-flow {機能名}`）

### プロンプトテンプレートが見つからない
- `docs/sdd/prompts/01_*_テンプレート.md` 〜 `08_*_テンプレート.md` が存在するか確認
- ファイル名の接頭辞番号（01-08）が正しいか確認

### サブエージェントが起動しない
- `.claude/agents/api/sdd/01-*.md` 〜 `08-*.md` が存在するか確認
- YAML frontmatter の `name` が正しいか確認（`sdd-extract-server-requirements` など）

### Stage 4で人間確認が求められる
- これは正常な動作です。プランナーへの確認結果を準備し、エージェントに提供してください
- 確認結果のフォーマットについては `03_サーバー仕様レビュー.md` を参照

### 並列実行でエラーが発生する
- Stage 6-8の並列実行中にエラーが発生した場合、個別に再実行可能です
- 詳細は `.claude/skills/sdd-orchestrator/troubleshooting.md` を参照

### glow-schemaリポジトリが見つからない
- `../glow-schema` ディレクトリにglow-schemaリポジトリをクローンしてください
- または、glow-serverと同じ親ディレクトリにglow-schemaリポジトリを配置してください

---

## 関連ドキュメント

### 実装ファイル
- **コマンド**: `.claude/commands/sdd/` - スラッシュコマンド定義
- **プロンプトテンプレート**: `docs/sdd/prompts/` - 各ステップの詳細手順
- **サブエージェント**: `.claude/agents/api/sdd/` - 専門エージェント定義
- **スキル**: `.claude/skills/sdd-orchestrator/` - オーケストレーションロジック

### 生成ドキュメント
- **出力先**: `docs/sdd/features/{機能名}/`
- **ファイル形式**:
  - `01_サーバー要件抽出.md` 〜 `08_サーバーAPI機能要件実装設計.md`
  - `ゲーム体験仕様書.pdf`（入力）

### 詳細ガイド
- **スキル詳細**: `.claude/skills/sdd-orchestrator/SKILL.md`
- **Stage詳細**: `.claude/skills/sdd-orchestrator/stage-details.md`
- **実行フロー**: `.claude/skills/sdd-orchestrator/orchestration-patterns.md`
- **使用例**: `.claude/skills/sdd-orchestrator/examples.md`
- **トラブルシューティング**: `.claude/skills/sdd-orchestrator/troubleshooting.md`
