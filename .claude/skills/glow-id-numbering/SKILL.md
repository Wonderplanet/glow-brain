---
name: glow-id-numbering
description: GLOWゲームプロジェクトのID採番・検証スキル。キャラ、クエスト、アイテム、BGMなどのID生成ルール参照、ID検証、次の空きID提案を提供。マスターデータ作成、ID命名規則確認、新規作品追加時のID設計、ID重複チェックで使用。
---

# GLOW ID採番・検証スキル

GLOWゲームプロジェクトにおける各種リソースのID採番ルールを参照し、ID検証や次のID提案を行うスキル。

## 主な機能

1. **ID生成ルールの参照** - カテゴリー別の命名規則とフォーマット確認
2. **ID検証** - 既存IDのフォーマット、桁数、接頭語の正確性チェック
3. **次のID提案** - 既存IDを調査し、使用可能な次の番号を自動提案
4. **作品IDマッピング** - 作品名から3文字コードへの変換

## 参照ファイル

### id_rules.md - ID採番ルール詳細

カテゴリー別のID命名規則を収録:

```bash
Read references/id_rules.md
```

**主要カテゴリー:**
- 作品、キャラ、敵キャラ
- アバターアイコン、キャラアイコン
- クエスト、アイテム、図鑑
- エンブレム、背景、コンテンツ
- BGM、ゲート

**各ルールに含まれる情報:**
- フォーマット例
- 最大桁数
- 接頭語
- 番号割り当てルール

### work_ids.md - 作品IDマッピング

作品名と3文字作品IDの対応表:

```bash
Read references/work_ids.md
```

**収録情報:**
- 全作品の作品ID（spy, dan, aka等）
- 連載曜日
- display_order

## スクリプト

### validate_id.py - ID検証

指定されたIDが正しい命名規則に従っているかを検証:

```bash
python scripts/validate_id.py <ID> [category]
```

**例:**
```bash
# 自動カテゴリー検出
python scripts/validate_id.py "chara_spy_00001"

# カテゴリー指定
python scripts/validate_id.py "quest_main_normal_dan_00001" "クエスト"

# 不正なID（難易度欠落）
python scripts/validate_id.py "quest_main_dan_00001"
# ❌ 検証失敗: フォーマット不正
```

**検証項目:**
- 接頭語の正確性
- 作品IDの形式（3文字小文字英字）
- 番号のゼロ埋め
- 桁数制限
- アンダースコア配置

### find_next_id.py - 次のID提案

既存IDを調査し、使用可能な次の番号を提案:

```bash
python scripts/find_next_id.py <base_dir> <prefix> <work_id>
```

**例:**
```bash
# SPY×FAMILYの次のキャラID
python scripts/find_next_id.py "マスタデータ/GLOW_ID 管理" chara spy

# ダンダダンのメインクエスト（ノーマル）
python scripts/find_next_id.py "マスタデータ/GLOW_ID 管理" quest_main_normal dan

# 汎用背景
python scripts/find_next_id.py "マスタデータ/GLOW_ID 管理" background glo
```

**出力情報:**
- 既存ID数
- 最新ID
- 提案される次のID
- 空き番号（あれば）

## 使用ワークフロー

### 新規キャラのID採番

1. 作品IDを確認:
   ```bash
   Read references/work_ids.md
   # SPY×FAMILY → spy
   ```

2. キャラIDルールを参照:
   ```bash
   Read references/id_rules.md
   # フォーマット: chara_{作品ID}_{5桁番号}
   ```

3. 次のIDを提案:
   ```bash
   python scripts/find_next_id.py "マスタデータ/GLOW_ID 管理" chara spy
   # 💡 提案ID: chara_spy_00502
   ```

4. 生成したIDを検証:
   ```bash
   python scripts/validate_id.py "chara_spy_00502"
   # ✅ 検証成功
   ```

### クエストIDの生成

1. クエストIDルールを確認:
   ```bash
   Read references/id_rules.md
   # フォーマット: quest_{カテゴリー}_{難易度}_{作品ID}_{5桁番号}
   ```

2. 次のIDを提案（例: ダンダダンのメインクエスト・ノーマル難易度）:
   ```bash
   python scripts/find_next_id.py "マスタデータ/GLOW_ID 管理" quest_main_normal dan
   ```

3. 検証:
   ```bash
   python scripts/validate_id.py "quest_main_normal_dan_00015"
   ```

### BGM IDの生成

1. BGMルールを確認:
   ```bash
   Read references/id_rules.md
   # フォーマット: SBG_{画面ID3桁}_{連番3桁}
   # 例: SBG_011_001（タイトル画面BGM）
   ```

2. 画面IDを確認してID生成:
   ```
   タイトル画面（011）の2曲目 → SBG_011_002
   ```

3. 検証:
   ```bash
   python scripts/validate_id.py "SBG_011_002"
   ```

## ID命名規則クイックリファレンス

| カテゴリー | フォーマット | 例 |
|-----------|------------|-----|
| 作品 | `{3文字}` | `spy`, `dan` |
| キャラ | `chara_{作品ID}_{5桁}` | `chara_spy_00001` |
| 敵キャラ | `enemy_{作品ID}_{5桁}` | `enemy_spy_00001` |
| クエスト | `quest_{種別}_{難易度}_{作品ID}_{5桁}` | `quest_main_normal_dan_00001` |
| アイテム | `{接頭語}_{作品ID}_{5桁}` | `prism_glo_00001` |
| 背景 | `background_{作品ID}_{5桁}` | `background_spy_00001` |
| BGM | `SBG_{画面ID3桁}_{連番3桁}` | `SBG_011_001` |

## 注意事項

- **作品IDは必ず小文字3文字**（spy ○, SPY ✗）
- **番号は必ずゼロ埋め**（00001 ○, 1 ✗）
- **汎用リソースは作品ID = `glo`**
- **削除予定ファイルは使用禁止**
- **同名キャラ（別バージョン）は連番で別ID取得**

## トラブルシューティング

### スクリプトが実行できない

実行権限を付与:
```bash
chmod +x scripts/validate_id.py
chmod +x scripts/find_next_id.py
```

### マスタデータディレクトリが見つからない

パスを確認:
```bash
ls "マスタデータ/GLOW_ID 管理"
```

プロジェクトルートから相対パスで指定:
```bash
python scripts/find_next_id.py "./マスタデータ/GLOW_ID 管理" chara spy
```

### ID検証が失敗する

id_rules.mdでフォーマットを確認:
```bash
Read references/id_rules.md
```

エラーメッセージに期待されるフォーマットが表示されるので、それに従って修正。

## データソース

このスキルは以下のファイルを参照:
- `マスタデータ/GLOW_ID 管理/ID割り振りルール.html`（コアルール）
- `マスタデータ/GLOW_ID 管理/作品.html`（作品IDマッピング）
- `マスタデータ/GLOW_ID 管理/*.html`（各カテゴリー管理表）

最新情報はこれらのファイルで確認してください。
