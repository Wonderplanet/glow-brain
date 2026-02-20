# エンブレムマスタデータ生成 - 推測値レポート

## 生成日時
2026-02-11

## 対象リリースキー
202601010

## 生成対象テーブル
- MstEmblem.csv
- MstEmblemI18n.csv

## データソース
- 運営仕様書: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭(施策)/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書/05_報酬一覧.csv` (11行目: 高難度報酬として「神仙郷のエンブレム」1個)
- 過去データ: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables/MstEmblem.csv`
- DBスキーマ: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/projects/glow-server/api/database/schema/exports/master_tables_schema.json`

## 生成されたエンブレム

### MstEmblem
| フィールド | 値 | 確定/推測 | 根拠 |
|-----------|-----|----------|------|
| id | emblem_event_jig_00001 | 推測 | 過去のイベントエンブレムの命名規則に従い、`emblem_{イベントタイプ}_{シリーズID}_{連番}`形式を適用。地獄楽のシリーズIDは`jig`、初めてのイベントエンブレムなので連番は00001 |
| emblemType | Event | 推測 | 報酬として配布されるエンブレムで、通常エンブレム(emblem_normal_jig_00001)とは異なる特別なエンブレムと判断。過去の他シリーズのイベントエンブレムパターンと一致 |
| mstSeriesId | jig | 確定 | 地獄楽のシリーズIDは過去データから`jig`と確認済み |
| assetKey | event_jig_00001 | 推測 | IDと同じ形式でasset_keyを生成。過去データで一貫したパターンを確認 |
| release_key | 202601010 | 確定 | タスクで指定されたリリースキー |

### MstEmblemI18n
| フィールド | 値 | 確定/推測 | 根拠 |
|-----------|-----|----------|------|
| id | emblem_event_jig_00001_ja | 推測 | `{mst_emblem_id}_{language}`形式に従う |
| mst_emblem_id | emblem_event_jig_00001 | 推測 | MstEmblemのidに対応 |
| language | ja | 確定 | 日本語のみサポート |
| name | 神仙郷のエンブレム | 確定 | 報酬一覧(5行目、11行目の列22)に記載 |
| description | 極楽浄土とも呼ばれる不老不死の島「神仙郷」のエンブレム | 推測 | 地獄楽の物語設定に基づいて作成。神仙郷は作品の中心的な舞台で、不老不死の秘薬がある島とされている |
| release_key | 202601010 | 確定 | タスクで指定されたリリースキー |

## 推測値の詳細説明

### 1. ID命名規則
**推測内容**: `emblem_event_jig_00001`

**根拠**:
- 過去の地獄楽エンブレム: `emblem_normal_jig_00001` (通常エンブレム)
- 他シリーズのイベントエンブレム例:
  - `emblem_event_kai_00001` (怪獣8号)
  - `emblem_event_spy_00001` (SPY×FAMILY)
  - `emblem_event_dan_00001` (ダンダダン)
- 命名パターン: `emblem_{type}_{series}_{number}`

### 2. エンブレムタイプ
**推測内容**: `Event`

**根拠**:
- 高難度クリア報酬として配布される特別なエンブレム
- 通常エンブレム(`emblem_normal_jig_00001`)とは別に存在
- 他シリーズでも同様にイベント報酬エンブレムは`Event`タイプ

### 3. 説明文(description)
**推測内容**: 「極楽浄土とも呼ばれる不老不死の島「神仙郷」のエンブレム」

**根拠**:
- 地獄楽の物語における神仙郷の設定:
  - 不老不死の秘薬がある伝説の島
  - 極楽浄土とも呼ばれる
  - 作品の中心的な舞台
- 過去のイベントエンブレムの説明文パターン:
  - 作品世界の設定や要素を説明する形式
  - 簡潔で分かりやすい表現

## 確認が必要な項目

### 高優先度
1. **説明文の内容**:
   - 現在の説明文: 「極楽浄土とも呼ばれる不老不死の島「神仙郷」のエンブレム」
   - より具体的な表現や、作品ファンに響く表現があれば修正が必要
   - 企画意図に合った説明になっているか確認が必要

2. **IDの連番**:
   - 現在: `emblem_event_jig_00001` (初めてのイベントエンブレムとして00001)
   - 地獄楽の過去イベントで他のイベントエンブレムが存在する可能性
   - 存在する場合は連番を調整が必要

### 中優先度
3. **assetKeyの命名**:
   - 現在: `event_jig_00001`
   - アセット制作チームとの命名規則が一致しているか確認
   - 実際のアセットファイル名と対応しているか

### 低優先度
4. **エンブレムタイプ**:
   - 現在: `Event`
   - 今回の高難度報酬が特別な意味を持つ場合、別のカテゴリが必要か

## 生成ファイル

### MstEmblem.csv
```
ENABLE,id,emblemType,mstSeriesId,assetKey,release_key
e,emblem_event_jig_00001,Event,jig,event_jig_00001,202601010
```

### MstEmblemI18n.csv
```
ENABLE,release_key,id,mst_emblem_id,language,name,description
e,202601010,emblem_event_jig_00001_ja,emblem_event_jig_00001,ja,神仙郷のエンブレム,極楽浄土とも呼ばれる不老不死の島「神仙郷」のエンブレム
```

## まとめ

**生成レコード数**:
- MstEmblem: 1レコード
- MstEmblemI18n: 1レコード (日本語のみ)

**推測値の数**: 5項目
- ID (emblem_event_jig_00001)
- emblemType (Event)
- assetKey (event_jig_00001)
- i18n ID (emblem_event_jig_00001_ja)
- description (説明文)

**確定値の数**: 3項目
- mstSeriesId (jig)
- release_key (202601010)
- name (神仙郷のエンブレム)
- language (ja)

**推奨アクション**:
1. 企画担当者に説明文の内容を確認
2. アセット制作チームにassetKeyの命名を確認
3. 過去の地獄楽イベントでエンブレムが存在しないか確認
4. 確認後、問題なければマスタデータとして採用
