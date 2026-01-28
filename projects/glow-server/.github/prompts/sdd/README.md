# SDD（Spec-Driven Development）Copilot Prompts

GitHub Copilotでゲーム体験仕様書からサーバーAPI実装設計までの要件定義・設計プロセスを支援するプロンプトファイル群です。

## 概要

このディレクトリには、Claude Code用の `.claude/commands/sdd/` と同等の機能をGitHub Copilotで実行するためのプロンプトファイルが含まれています。

## 使用方法

### VS Code / JetBrains IDEs

Copilot Chatで以下のようにスラッシュコマンドとして実行できます：

```
/00-run-full-flow
```

実行後、機能名の入力を求められます。

### 個別ステップの実行

| コマンド | 説明 |
|---------|------|
| `/00-run-full-flow` | 全8段階を一括実行 |
| `/01-extract-server-requirements` | サーバー要件抽出 |
| `/02-investigate-code-requirements` | コード調査追記 |
| `/03-review-server-spec` | サーバー仕様レビュー |
| `/04-confirm-game-experience-spec` | ゲーム体験仕様確認結果まとめ |
| `/05-finalize-server-requirements` | サーバーAPI要件書まとめ |
| `/06-overview-api-design` | API実装全体概要設計 |
| `/07-create-api-design` | サーバーAPI設計書作成 |
| `/08-design-api-implementation` | サーバーAPI機能要件実装設計 |

## フロー図

```
                     【要件定義フェーズ（順次実行）】
PDF → 01 → 02 → 03 → [人間確認] → 04 → 05
      ↓    ↓    ↓                 ↓    ↓
    抽出  調査  レビュー          確認  統合

                                  ↓

            【設計フェーズ（並列実行可能）】
                            ┌─────┼─────┐
                            ↓     ↓     ↓
                           06    07    08
                            ↓     ↓     ↓
                       概要設計  API設計  実装設計
```

## 前提条件

- `docs/sdd/features/{機能名}/ゲーム体験仕様書.pdf` が配置されていること
- `docs/sdd/prompts/` に各ステージのテンプレートファイルが存在すること

## Claude Code版との違い

| 項目 | Claude Code版 | Copilot版 |
|------|--------------|-----------|
| 配置場所 | `.claude/commands/sdd/` | `.github/prompts/sdd/` |
| ファイル拡張子 | `.md` | `.prompt.md` |
| 実行方法 | `/sdd:xxx` | `/xxx` |
| 入力方法 | 引数として渡す | インタラクティブ入力 |

## 関連ドキュメント

- **Claude Code版**: `.claude/commands/sdd/README.md`
- **プロンプトテンプレート**: `docs/sdd/prompts/`
- **出力先**: `docs/sdd/features/{機能名}/`
