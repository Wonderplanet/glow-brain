# ネクストアクション

## 次にやること

### Phase 1: フォーマット調査（即実施可能）

1. **マスタデータCSVのヘッダー仕様を確認する**
   - `projects/glow-masterdata/{Table}.csv` の1行目構造を複数テーブルで確認
   - `ENABLE` 列の役割・必須性を確認
   - ファイル名とテーブル名の対応ルールを確認

2. **sheet_schema CSVのヘッダー仕様を確認する**
   - `projects/glow-masterdata/sheet_schema/{Table}.csv` の1-3行目を複数確認
   - 行1（memo行）の構造パターン
   - 行2（TABLE行）: マスタデータCSVのカラムとの対応、I18nテーブルの追加ルール
   - 行3（ENABLE行）: マスタデータCSVのカラムとの差分（I18n列の追加など）

3. **変換ロジックを設計する**
   - マスタデータCSV → sheet_schema CSV の変換アルゴリズムを整理
   - I18n列（`xxx.ja`等）はsheet_schema参照から補完する方針を確認

### Phase 2: スキル実装

4. **スキルディレクトリを作成する**
   ```
   .claude/skills/masterdata-csv-to-sheet-schema-converter/
   ├── SKILL.md          # スキルドキュメント
   └── scripts/
       └── convert.py   # 変換スクリプト
   ```

5. **Pythonスクリプトを実装する**
   - 機能①: 入力CSVヘッダー検証（vs `projects/glow-masterdata/` の1行目）
   - 機能②: 入力CSVヘッダー修正（列順の修正、欠損列の補完）
   - 機能③: sheet_schema CSV生成（行1-3の生成ロジック）
   - 機能④: 出力sheet_schema CSVヘッダー検証（vs `projects/glow-masterdata/sheet_schema/` の1-3行目）
   - 機能⑤: 出力sheet_schema CSVヘッダー修正

6. **SKILL.md を作成する**
   - 使い方・オプション・ワークフローを記述

### Phase 3: 動作検証

7. **実際のマスタCSVで動作テストをする**
   - 正常系: 正しいヘッダーのCSVを変換
   - 異常系: ヘッダーが不正なCSVを検出・修正

## 長期的なアクション

- `masterdata-csv-to-xlsx` スキルと連携した一気通貫フローの構築
- `create-subagent` や他のスキルから自動呼び出しできるように整備

## 注意事項

- sheet_schema CSVの行2（TABLE行）はI18nテーブルの扱いを要調査
- 変換は `projects/glow-masterdata/sheet_schema/` が存在するテーブルのみ対象
- `masterdata-csv-to-xlsx` スキルの `scripts/convert_to_xlsx.py` も参考にすること
