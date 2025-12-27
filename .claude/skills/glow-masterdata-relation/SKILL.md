---
name: glow-masterdata-relation
description: GLOWプロジェクトのマスターデータテーブル（mst_*, opr_*）のリレーション構造を調査し、mermaid記法を使った視覚的なER図とドキュメントを自動生成します。マスターデータ、テーブル構造、リレーション、ER図、スキーマ、データベース設計のドキュメント化で使用します。
---

# GLOW Masterdata Relation

GLOWプロジェクトのマスターデータテーブルのリレーション構造を可視化し、ドキュメントを自動生成するスキル。

## 使用するタイミング

以下のようなリクエストに対して自動的に起動します：

- "クエスト・ステージのリレーションドキュメントを作成して"
- "ガチャシステムのテーブル構造を調査して"
- "ユニット関連のマスターテーブルのER図が欲しい"
- "マスターデータのリレーション情報をまとめて"

## クイックスタート

### 基本的な使い方

1. **ユーザーから領域キーワードを取得**
   - 例: "クエスト・ステージ・インゲーム"
   - 例: "ガチャ"
   - 例: "ユニット、アビリティ"

2. **スクリプトを実行**

```bash
python3 .claude/skills/glow-masterdata-relation/scripts/generate_relation_doc.py "quest,stage,in_game"
```

3. **出力ファイルを指定する場合**

```bash
python3 .claude/skills/glow-masterdata-relation/scripts/generate_relation_doc.py \
  "quest,stage,in_game" \
  マスタデータ/docs/クエスト・ステージ・インゲーム_リレーション.md
```

### スクリプトの引数

- **第1引数（必須）**: カンマ区切りのキーワード
  - テーブル名の一部にマッチする文字列
  - 例: `"quest,stage"` → `mst_quests`, `mst_stages`, `mst_quest_bonus_units` など
  - 大文字小文字は区別しない

- **第2引数（オプション）**: 出力ファイルパス
  - 指定しない場合は標準出力に表示
  - 推奨パス: `マスタデータ/docs/[領域名]_マスターテーブルリレーション.md`

## ワークフロー

### Step 1: 領域の特定

ユーザーのリクエストから、調査対象の領域を特定します。

**よくある領域とキーワード:**

| 領域 | キーワード例 |
|------|------------|
| クエスト・ステージ | `quest,stage,in_game` |
| ガチャ | `gacha` |
| ユニット | `unit,ability` |
| ミッション | `mission` |
| アイテム | `item` |
| 降臨バトル | `advent` |
| イベント | `event` |
| 交換所 | `exchange` |
| 図鑑 | `artwork,encyclopedia` |

領域が不明瞭な場合は、ユーザーに確認してください。

### Step 2: スクリプト実行

`generate_relation_doc.py` を実行してドキュメントを生成します。

スクリプトは以下を自動実行します：
1. DBスキーマJSON（`projects/glow-server/api/database/schema/exports/master_tables_schema.json`）を読み込み
2. キーワードに一致するテーブルを検索
3. 外部キー相当のカラムを検出（`mst_*_id`, `opr_*_id` パターン）
4. mermaid ER図を生成
5. テーブル一覧とリレーションサマリーを生成
6. マークダウンファイルとして出力

### Step 3: ドキュメントの拡張（オプション）

生成されたドキュメントは基本的なER図とテーブル一覧のみ含みます。
必要に応じて以下を追加してください：

1. **詳細なフローチャート**
   - 機能ごとの詳細なmermaidフロー図
   - 例: クエスト→ステージ→インゲーム→敵の流れ

2. **特殊なリレーションパターンの説明**
   - resource_type + resource_id パターン
   - group_id パターン
   - 係数（coefficient）パターン
   - 依存関係パターン

3. **設計思想の記述**
   - 報酬システムの汎用設計
   - イベント期間管理
   - 多言語対応（i18n）

詳細は `references/glow-schema-patterns.md` を参照してください。

### Step 4: ファイル保存と報告

生成されたドキュメントを `マスタデータ/docs/` ディレクトリに保存し、ユーザーに報告します。

**ファイル名の推奨形式:**
- `[領域名]_マスターテーブルリレーション.md`
- 例: `クエスト・ステージ・インゲーム_マスターテーブルリレーション.md`
- 例: `ガチャシステム_マスターテーブルリレーション.md`

## リファレンス

### GLOWスキーマパターン

GLOWプロジェクト固有のテーブル命名規則、リレーションパターン、特殊な設計パターンについては、以下のリファレンスを参照：

[references/glow-schema-patterns.md](references/glow-schema-patterns.md)

このファイルには以下の情報が含まれます：
- テーブル命名規則（mst_*, opr_*, *_i18n）
- 外部キー検出パターン
- 特殊なリレーションパターン（resource_type, group_id, coefficient, dependencies, i18n）
- 機能領域ごとの主要テーブル群
- DBスキーマJSONの構造とjqコマンド例

ドキュメントに詳細な説明を追加する際は、このリファレンスを読み込んでください。

## 制限事項

- **mst_*, opr_* テーブルのみ対象**: ユーザーテーブル（usr_*）やログテーブル（log_*）は対象外
- **外部キー制約なし**: GLOWプロジェクトではアプリケーション層で整合性を管理しているため、DB上の外部キー制約は存在しません
- **命名規則ベースの検出**: `*_id` パターンで外部キーを検出するため、命名規則に従わないカラムは検出されません
- **複雑なリレーションは手動追加**: resource_type/resource_id などの動的リレーションは、mermaid図への自動追加が困難なため手動で追記してください

## トラブルシューティング

### テーブルが見つからない

**問題**: "キーワードに一致するテーブルが見つかりませんでした"

**解決策**:
1. キーワードのスペルを確認
2. 単数形/複数形を試す（`quest` → `quests`）
3. より広いキーワードを試す（`gacha_prize` → `gacha`）
4. スキーマファイルで直接検索:
   ```bash
   jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

### スキーマファイルが見つからない

**問題**: "Schema file not found"

**解決策**:
- カレントディレクトリがプロジェクトルート（glow-brain）であることを確認
- スキーマファイルのパスを確認: `ls projects/glow-server/api/database/schema/exports/`

### ER図が複雑すぎる

**問題**: テーブル数が多すぎてER図が見にくい

**解決策**:
1. キーワードをより具体的に絞る
2. 領域を分割して複数のドキュメントを作成
3. 全体像のER図は主要テーブルのみに絞り、詳細は個別のフロー図で補完

## 実行例

### 例1: クエスト・ステージ・インゲーム

```bash
python3 .claude/skills/glow-masterdata-relation/scripts/generate_relation_doc.py \
  "quest,stage,in_game" \
  マスタデータ/docs/クエスト・ステージ・インゲーム_マスターテーブルリレーション.md
```

### 例2: ガチャシステム

```bash
python3 .claude/skills/glow-masterdata-relation/scripts/generate_relation_doc.py \
  "gacha" \
  マスタデータ/docs/ガチャシステム_マスターテーブルリレーション.md
```

### 例3: 標準出力に表示（ファイル保存なし）

```bash
python3 .claude/skills/glow-masterdata-relation/scripts/generate_relation_doc.py "unit,ability"
```
