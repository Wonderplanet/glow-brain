---
name: masterdata-full-workflow
description: 施策のマスタデータ生成フルワークフロー。要件分析→コンテンツタイプ別並列生成→統合を一括実行。マスタデータフル実行、施策のマスタデータを作成で使用。
allowed-tools: Skill, Task, Read, Write, Bash
argument-hint: 施策ディレクトリパス
---

# マスタデータ フル実行ワークフロー

このスキルは、施策のマスタデータ生成フルワークフローを実行します。要件ファイル構成の分析→マスタデータ生成までを1回の実行で完遂します。

## 前提条件

事前に要件フォルダを準備してください:

```
マスタデータ/施策/[施策名]/
└── 要件/    ← ユーザーが仕様書ファイルを事前に配置
```

## 入力

施策ディレクトリパス（例: `マスタデータ/施策/新春ガチャ`）

## 出力

- **要件ファイル構成.md**: 要件フォルダの構造分析結果
- **CSVファイル群**: 全てのマスタデータCSV
- **REPORT.md**: データ生成レポート

## タスク

指定された施策ディレクトリに対して、以下の4つのステップを**順番に実行**してください。

### ステップ1: 要件ファイル構成ドキュメントの生成

`masterdata-requirement-analyzer` スキルを実行:

```
Skill(skill: "masterdata-requirement-analyzer", args: "<施策ディレクトリパス>")
```

これにより、`[施策ディレクトリ]/要件ファイル構成.md` が生成されます。

### ステップ2: コンテンツタイプの識別

識別スクリプトを実行してコンテンツタイプを識別:

```
Bash(
  command: "python3 .claude/skills/masterdata-full-workflow/scripts/identify_content_types.py '<施策ディレクトリ>/要件ファイル構成.md' --output json",
  description: "コンテンツタイプを識別"
)
```

出力例: `{"content_types": ["gacha", "battle", "mission"], "count": 3}`

識別されるコンテンツタイプ:
- **gacha**: ガチャ関連
- **battle**: バトル関連
- **mission**: ミッション関連
- **shop**: ショップ/パック関連
- **exchange**: 交換所関連

**注意**: コンテンツタイプが識別できない場合（終了コード1）でも警告して継続してください。

### ステップ3: コンテンツ別subagentの並列起動

**重要**: 識別されたコンテンツタイプごとに、`content-masterdata-generator` subagentを並列起動してください。

**並列実行の方法**: 単一メッセージで複数のTask呼び出しを行います。

例:
```
Task(subagent_type: "content-masterdata-generator",
     description: "ガチャ関連マスタデータ生成",
     prompt: "content_type: gacha\n施策ディレクトリ: <施策ディレクトリパス>")

Task(subagent_type: "content-masterdata-generator",
     description: "バトル関連マスタデータ生成",
     prompt: "content_type: battle\n施策ディレクトリ: <施策ディレクトリパス>")
```

各subagent instanceは部分REPORTを返却します。

### ステップ4: 結果の統合とREPORT生成

各subagent instanceから返却された部分REPORTを統合し、施策ディレクトリ直下に最終的な`REPORT.md`を生成してください:

```
Write(file_path: "<施策ディレクトリ>/REPORT.md", content: "...")
```

REPORTテンプレートを参照: `Read(file_path: ".claude/skills/masterdata-full-workflow/assets/REPORT_template.md")`

**REPORTの構成**:
- 施策概要
- 要件概要
- 生成データ一覧（全instanceの結果を統合）
- スキーマ検証と修正（全instanceの結果を統合）
- データ整合性チェック
- 完了状況

## 重要な注意事項

### タスク完遂の原則 ⚠️

- **全ステップを完全に実行**: ステップ1-4を必ず完遂
- **途中で止めない**: 各subagentは要件に含まれる全マスタデータを最後まで生成
- **完了条件**: 統合REPORT.mdに「未作成のマスタデータ」が残らないようにする

### 完了チェックリスト

#### ステップ1
- ✅ `[施策ディレクトリ]/要件ファイル構成.md` が生成されている

#### ステップ2
- ✅ コンテンツタイプが正しく識別されている

#### ステップ3
- ✅ 識別された全コンテンツタイプに対してsubagentが起動
- ✅ 全CSVファイルがテンプレートからコピーして作成
- ✅ 各subagentから部分REPORTが返却

#### ステップ4
- ✅ `REPORT.md` が施策ディレクトリ直下に生成
- ✅ 全コンテンツタイプの結果がREPORTに統合
- ✅ REPORTに「未作成のマスタデータ」セクションが存在しない
- ✅ REPORTに「スキーマ検証と修正」セクションが含まれる

## エラーハンドリング

- **ステップ1でエラー**: ステップ2には進まない
- **ステップ2でコンテンツタイプが識別できない**: 警告して継続
- **ステップ3で一部のsubagentが失敗**: 他のsubagentは継続実行
- **ステップ4で部分的な失敗**: 統合REPORT.mdに記録

## 詳細ガイド

詳細な情報は以下のファイルを参照してください:

- **ワークフロー詳細**: `Read(file_path: ".claude/skills/masterdata-full-workflow/references/workflow-details.md")`
  - 各ステップの詳細説明
  - ベストプラクティス
  - 依存スキルとsubagent
  - 新しいコンテンツタイプの追加方法

- **トラブルシューティング**: `Read(file_path: ".claude/skills/masterdata-full-workflow/references/troubleshooting.md")`
  - よくある問題と解決方法
  - デバッグ方法
  - パフォーマンス最適化

## 使用例

```
Skill(skill: "masterdata-full-workflow", args: "マスタデータ/施策/新春ガチャ")
```

期待される動作:
1. 要件ファイル構成.mdを生成
2. コンテンツタイプ識別（例: ["gacha"]）
3. content-masterdata-generator subagent起動（ガチャ関連）
4. 統合REPORT.mdを生成

期待される出力:
```
マスタデータ/施策/新春ガチャ/
├── 要件/
├── 要件ファイル構成.md       ← 新規生成
├── REPORT.md                ← 新規生成
├── OprGacha.csv             ← 新規生成
└── MstGachaPrizeGroup.csv   ← 新規生成
```
