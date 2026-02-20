# アイテムマスタデータ推測値レポート

## 作成日時
2026-02-11

## 作成対象
地獄楽 いいジャン祭のアイテムマスタデータ

## 入力情報
- **運営仕様書**: `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書/01_概要.csv` (line 65-80)
- **GLOW_ID管理シート**: `domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/アイテム.csv` (line 175-178, 223-224)
- **過去データ**: `domain/raw-data/masterdata/released/202601010/past_tables/MstItem.csv`, `MstItemI18n.csv`
- **リリースキー**: 202601010

## 作成したテーブル

### 1. MstItem.csv
6件のアイテムデータを作成

### 2. MstItemI18n.csv
6件の日本語テキストデータを作成

## 推測値の詳細

### キャラかけら（CharacterFragment型）

| フィールド | 推測値 | 根拠 |
|-----------|--------|------|
| **ENABLE** | `e` | 過去データの標準パターン |
| **type** | `CharacterFragment` | GLOW_ID管理シートで「キャラのかけら」と記載、過去データのpiece系アイテムと同じ型 |
| **group_type** | `Etc` | 過去データのpiece系アイテムすべてが`Etc` |
| **rarity** | UR/SSR/SR | GLOW_ID管理シートの記載に基づく（00401=UR, 00501=SSR, 00601/00701=SR） |
| **asset_key** | アイテムIDと同一 | 過去データのpiece系アイテムで100%一致 |
| **effect_value** | `""` (空文字列) | 過去データのpiece系アイテムすべてが空文字列 |
| **sort_order** | `1` | 過去データのpiece系アイテムすべてが`1` |
| **start_date** | `2026-01-16 15:00:00` | 運営仕様書 line 20の開催開始日時 |
| **end_date** | `2037-12-31 23:59:59` | 過去データの標準的な終了日時（実質無期限） |
| **release_key** | `202601010` | team-leadからの指示 |
| **item_type** | `CharacterFragment` | typeと同じ値（過去データのパターン） |
| **destination_opr_product_id** | `""` (空文字列) | 過去データのpiece系アイテムすべてが空 |

### キャラ専用メモリー（RankUpMaterial型）

| フィールド | 推測値 | 根拠 |
|-----------|--------|------|
| **ENABLE** | `e` | 過去データの標準パターン |
| **type** | `RankUpMaterial` | GLOW_ID管理シートで「キャラ専用カラーメモリー」と記載、過去データのmemory_chara系アイテムと同じ型 |
| **group_type** | `Etc` | 過去データのmemory_chara系アイテムすべてが`Etc` |
| **rarity** | SR | GLOW_ID管理シートの記載に基づく |
| **asset_key** | アイテムIDと同一 | 過去データのmemory_chara系アイテムで100%一致 |
| **effect_value** | キャラID（chara_jig_00601/chara_jig_00701） | 過去データのmemory_chara系アイテムで、対応するキャラIDを設定 |
| **sort_order** | 1013, 1014 | 過去データの最後のmemory_chara系アイテム（1012）の次の連番 |
| **start_date** | `2026-01-16 15:00:00` | 運営仕様書 line 20の開催開始日時 |
| **end_date** | `2037-12-31 23:59:59` | 過去データの標準的な終了日時（実質無期限） |
| **release_key** | `202601010` | team-leadからの指示 |
| **item_type** | `RankUpMaterial` | typeと同じ値（過去データのパターン） |
| **destination_opr_product_id** | `""` (空文字列) | 過去データのmemory_chara系アイテムすべてが空 |

### MstItemI18n.csv

| フィールド | 推測値 | 根拠 |
|-----------|--------|------|
| **ENABLE** | `e` | 過去データの標準パターン |
| **release_key** | `202601010` | team-leadからの指示 |
| **id** | アイテムID + `_ja` | 過去データの命名規則 |
| **mst_item_id** | アイテムID | MstItem.csvのidと一致 |
| **language** | `ja` | 日本語固定 |
| **name** | GLOW_ID管理シートの「アイテム名」をそのまま使用 | line 175-178, 223-224に記載 |
| **description** | キャラかけら: `{キャラ名}のグレードアップに使用するアイテム`<br>メモリー: `{キャラ名}のLv.上限開放に使用するアイテム` | 過去データの定型文パターンを踏襲 |

## 対象外とした項目

### エンブレム（7種類）
- **理由**: MstItemではなく、**MstEmblem**という別テーブルで管理されている
- **対象ID**: emblem_event_jig_00001, emblem_adventbattle_jig_season01_00001～00006
- **対応**: 別タスクで作成が必要

### 原画（2種類）
- **理由**: MstItemではなく、**MstArtworkFragment**という別テーブルで管理されている
- **対象ID**: artwork_event_jig_0001, artwork_event_jig_0002
- **対応**: 別タスクで作成が必要

## リスク評価

### 低リスク
- **ENABLE, type, group_type, rarity, asset_key, effect_value, item_type, destination_opr_product_id**: 過去データで100%一貫したパターンが確認済み
- **name, description**: GLOW_ID管理シートと過去データの定型文から確定

### 中リスク
- **sort_order**: memory_chara系アイテムは連番管理されているが、他のイベントで並行して番号が使用される可能性
- **start_date**: 運営仕様書の日時をそのまま使用したが、実際の配信タイミングは前後する可能性
- **end_date**: 実質無期限として設定したが、イベント期間限定の可能性も考えられる

## 検証推奨事項
1. ✅ MstItem.csvのフォーマットが正しいか
2. ✅ MstItemI18n.csvのフォーマットが正しいか
3. ⚠️ sort_orderの連番が他のイベントと重複していないか
4. ⚠️ エンブレムと原画の別テーブル作成が必要
5. ✅ start_dateがイベント開始日時と一致しているか
