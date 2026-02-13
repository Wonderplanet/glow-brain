---
name: masterdata-id-numbering
description: GLOWマスタデータのID採番ルール検索・生成・検証スキル。ID割り振りルール.csvを参照し、マスタデータ作成時の正確なID生成をサポートします。以下の場合に使用:(1) マスタデータのID採番ルールを確認、(2) 新しいIDを生成、(3) IDが採番ルールに準拠しているか検証、(4) カテゴリー別の採番パターンを調査。「ID採番」「採番ルール」「ID生成」「マスタデータID」「numbering」「ID割り振り」「ID検証」などのキーワードで使用します。
allowed-tools: Read, Grep, Glob, Bash
---

# GLOWマスタデータ ID採番ルール支援スキル

GLOWプロジェクトのマスタデータID採番ルールを検索・提示・生成・検証する統合スキルです。

## データソース

**主要データソース**:
```
domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv
```

このCSVファイルが採番ルールの唯一の真実の情報源（Single Source of Truth）です。

## 採番ルールの特徴

- **作品コード**: 3文字の小文字（例: `spy`, `dan`, `aka`）
- **連番管理**: 作品ごとに5桁連番（00001〜99999）
- **100番単位**: キャラは100番単位で区切り管理
- **敵キャラ**: `enemy_` 接頭語を使用
- **汎用ID**: 作品IDに `glo` を使用

### 主なカテゴリー

| カテゴリー | パターン例 | 説明 |
|-----------|-----------|------|
| キャラ | `chara_spy_00001` | 接頭語+作品ID+5桁連番 |
| クエスト | `quest_main_normal_dan_00001` | 接頭語+難易度+カテゴリー+作品ID+連番 |
| アイテム | `prism_glo_00001` | カテゴリー接頭語+作品ID+連番 |
| エンブレム | `emblem_normal_spy_00001` | 接頭語+カテゴリー+作品ID+連番 |
| BGM | `SBG_011_001` | 管理番号+サウンド数 |

## 利用可能なスクリプト

### 1. カテゴリー一覧表示

```bash
cd .claude/skills/masterdata-id-numbering/scripts
./list_categories.sh
```

全カテゴリーの一覧を表示します。

### 2. 採番ルール検索

```bash
./search_numbering_rule.sh <カテゴリー名>
```

**例**:
```bash
./search_numbering_rule.sh キャラ
./search_numbering_rule.sh クエスト
```

指定したカテゴリーの詳細な採番ルールを表示します。

### 3. ID生成

```bash
python3 generate_id.py <カテゴリー> <作品ID> <連番> [オプション]
```

**例**:
```bash
# キャラID生成
python3 generate_id.py キャラ spy 1
# 出力: chara_spy_00001

# クエストID生成（難易度とカテゴリー指定）
python3 generate_id.py クエスト dan 1 --difficulty=normal --quest_category=main
# 出力: quest_main_normal_dan_00001

# BGM ID生成
python3 generate_id.py BGM - - --mgmt_number=011 --sound_number=001
# 出力: SBG_011_001
```

**オプションパラメータ**:
- `--difficulty`: クエストの難易度
- `--quest_category`: クエストカテゴリー
- `--emblem_category`: エンブレムカテゴリー
- `--content_category`: コンテンツカテゴリー
- `--icon_type`: キャラアイコンタイプ（picon/eicon）
- `--chara_type`: キャラタイプ（chara/enemy）
- `--mgmt_number`: BGM管理番号
- `--sound_number`: サウンド数

### 4. ID検証

```bash
python3 validate_id.py <ID> [--category=<カテゴリー>] [--csv-path=<CSVパス>]
```

**例**:
```bash
# 基本的な検証
python3 validate_id.py chara_spy_00001

# カテゴリーを指定して検証
python3 validate_id.py quest_main_normal_dan_00001 --category=クエスト

# 既存CSVとの重複チェック
python3 validate_id.py chara_spy_00001 --csv-path=projects/glow-masterdata/MstHero.csv
```

**検証内容**:
- 採番ルールへの準拠確認
- パターンマッチング
- 既存マスタデータCSVとの重複チェック

## ユースケース

### ケース1: 新しいキャラクターIDを生成

```bash
cd .claude/skills/masterdata-id-numbering/scripts

# 1. キャラクターの採番ルールを確認
./search_numbering_rule.sh キャラ

# 2. IDを生成（spy作品の1番目のキャラ）
python3 generate_id.py キャラ spy 1

# 3. 生成したIDを検証
python3 validate_id.py chara_spy_00001
```

### ケース2: 採番ルールの調査

```bash
# 全カテゴリーを一覧表示
./list_categories.sh

# 特定カテゴリーの詳細ルールを確認
./search_numbering_rule.sh クエスト
```

### ケース3: IDの検証と重複チェック

```bash
# IDが採番ルールに準拠しているか検証
python3 validate_id.py chara_spy_00001

# 既存マスタデータとの重複をチェック
python3 validate_id.py chara_spy_00001 --csv-path=projects/glow-masterdata/MstHero.csv
```

## 設計原則

### データドリブン設計

すべてのスクリプトは `ID割り振りルール.csv` を動的に解析します。新しいカテゴリーが追加されても、スクリプト修正は不要です。

**新カテゴリー追加時の対応**:
1. `ID割り振りルール.csv` に新しいカテゴリーを追加
2. スクリプトは自動的に新カテゴリーを認識
3. 即座に検索・生成・検証が可能

### 拡張性

- ルールロジックはハードコーディング禁止
- CSVパーサーは柔軟に設計（列順変更に対応）
- 新しいパターンも自動解析

## 注意事項

### DBスキーマとCSVの命名規則の違い

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |
| **ID値** | snake_case | `chara_spy_00001` |

### プレフィックス

- `mst_*` / `Mst*` - 固定マスタデータ
- `opr_*` / `Opr*` - 運営施策・期間限定データ

### 敵キャラの扱い

敵専用キャラは `enemy_` 接頭語を使用:
- プレイアブル: `chara_spy_00001`
- 敵専用: `enemy_spy_00001`

### 汎用IDの作品コード

作品に依存しないアイテムやリソースは作品コードに `glo` を使用:
- `prism_glo_00001` - 汎用プリズム
- `background_glo_00001` - 汎用背景

## 既存スキルとの統合

このスキルは以下のスキルと併用できます:

- `masterdata-from-bizops-*`: 運営仕様書からマスタデータCSV作成時にID生成を支援
- `masterdata-csv-validator`: 作成したCSVのID検証
- `masterdata-explorer`: マスタデータのスキーマ調査時に採番ルールを参照

## トラブルシューティング

### エラー: カテゴリーが見つかりません

```bash
# カテゴリー一覧を確認
./list_categories.sh

# 正確なカテゴリー名を使用
./search_numbering_rule.sh キャラ  # 正しい
./search_numbering_rule.sh chara   # 誤り
```

### エラー: ID割り振りルール.csvが見つかりません

データソースのパスを確認してください:
```
domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv
```

### 生成したIDが検証エラー

- カテゴリー名が正しいか確認
- 必要なオプションパラメータが渡されているか確認
- `search_numbering_rule.sh` で採番ルールの詳細を確認
