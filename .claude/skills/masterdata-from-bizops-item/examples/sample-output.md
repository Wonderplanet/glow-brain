# アイテム マスタデータ サンプル出力

このファイルは、アイテムスキルの出力例を示します。

## 入力例

```
アイテムの運営仕様書からマスタデータを作成してください。

添付ファイル:
- ヒーロー設計書_地獄楽_いいジャン祭.xlsx
- クエスト設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- item_type: CharacterFragment,RankUpMaterial
- start_date: 2026-01-16 15:00:00
- end_date: 2037-12-31 23:59:59
```

## 出力例

### 1. MstItem シート

| ENABLE | id | type | group_type | rarity | asset_key | effect_value | sort_order | start_date | end_date | release_key | item_type | destination_opr_product_id |
|--------|----|----|----------|--------|----------|------------|-----------|----------|---------|------------|----------|--------------------------|
| e | memory_chara_jig_00601 | RankUpMaterial | Etc | SR | memory_chara_jig_00601 | chara_jig_00601 | 1013 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | RankUpMaterial | |
| e | memory_chara_jig_00701 | RankUpMaterial | Etc | SR | memory_chara_jig_00701 | chara_jig_00701 | 1014 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | RankUpMaterial | |
| e | piece_jig_00401 | CharacterFragment | Etc | UR | piece_jig_00401 | | 1 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | CharacterFragment | |
| e | piece_jig_00501 | CharacterFragment | Etc | SSR | piece_jig_00501 | | 1 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | CharacterFragment | |
| e | piece_jig_00601 | CharacterFragment | Etc | SR | piece_jig_00601 | | 1 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | CharacterFragment | |
| e | piece_jig_00701 | CharacterFragment | Etc | SR | piece_jig_00701 | | 1 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | CharacterFragment | |

### 2. MstItemI18n シート

| ENABLE | release_key | id | mst_item_id | language | name | description |
|--------|------------|----|------------|---------|------|-----------|
| e | 202601010 | memory_chara_jig_00601_ja | memory_chara_jig_00601 | ja | 民谷 巌鉄斎のメモリー | 民谷 巌鉄斎のLv.上限開放に使用するアイテム |
| e | 202601010 | memory_chara_jig_00701_ja | memory_chara_jig_00701 | ja | メイのメモリー | メイのLv.上限開放に使用するアイテム |
| e | 202601010 | piece_jig_00401_ja | piece_jig_00401 | ja | 賊王 亜左 弔兵衛のかけら | 賊王 亜左 弔兵衛のグレードアップに使用するアイテム |
| e | 202601010 | piece_jig_00501_ja | piece_jig_00501 | ja | 山田浅ェ門 桐馬のかけら | 山田浅ェ門 桐馬のグレードアップに使用するアイテム |
| e | 202601010 | piece_jig_00601_ja | piece_jig_00601 | ja | 民谷 巌鉄斎のかけら | 民谷 巌鉄斎のグレードアップに使用するアイテム |
| e | 202601010 | piece_jig_00701_ja | piece_jig_00701 | ja | メイのかけら | メイのグレードアップに使用するアイテム |

## 推測値レポート

### MstItem.effect_value
- **値**: chara_jig_00601, chara_jig_00701
- **理由**: 設計書にキャラメモリーの効果値(対応するキャラID)の記載がなかったため、アイテムIDから推測して設定
- **確認事項**: 正しいキャラIDを確認し、必要に応じて差し替えてください

### MstItemI18n.description
- **値**: 民谷 巌鉄斎のLv.上限開放に使用するアイテム(他のアイテムも同様)
- **理由**: 設計書にアイテム説明文の記載がなかったため、標準的な説明文を推測して設定
- **確認事項**: 正しいアイテム説明文を確認し、必要に応じて差し替えてください

### MstItem.sort_order
- **値**: 1013, 1014, 1(他のアイテムも同様)
- **理由**: 設計書に表示順序の記載がなかったため、標準的な値を推測して設定
- **確認事項**: 必要に応じて表示順序を調整してください
