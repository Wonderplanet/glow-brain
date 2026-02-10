---
name: masterdata-from-bizops-artwork
description: 原画の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 5個(MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition)。原画とその欠片16個分のマスタデータを精度高く作成します。
---

# 原画 マスタデータ作成スキル

## 概要

原画の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の5テーブルを自動生成:

**原画基本情報**:
- **MstArtwork** - 原画の基本情報(レアリティ、拠点追加HP等)
- **MstArtworkI18n** - 原画名・説明文(多言語対応)

**原画の欠片情報**:
- **MstArtworkFragment** - 原画の欠片情報(ドロップ率、レアリティ、アセット番号)
- **MstArtworkFragmentI18n** - 原画の欠片名(多言語対応)
- **MstArtworkFragmentPosition** - 原画の欠片の配置位置(1~16)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai) | `jig` |
| **artwork_id** | 原画ID | `artwork_event_jig_0001` |
| **artwork_category** | 原画カテゴリ | `event`(イベント原画)、`series`(シリーズ原画) |
| **artwork_title** | 原画タイトル | `必ず生きて帰る`(最大40文字) |
| **artwork_description** | 原画説明文 | `死罪となった「がらんの画眉丸」は...`(最大255文字) |
| **rarity** | レアリティ | `SSR`(N/R/SR/SSR/UR) |
| **outpost_additional_hp** | 拠点追加HP | `100`(通常100) |
| **drop_design** | 欠片ドロップ設計 | どのステージでどの欠片がドロップするか |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
原画の運営仕様書からマスタデータを作成してください。

添付ファイル:
- 原画設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- artwork_id: artwork_event_jig_0001
- artwork_category: event
- artwork_title: 必ず生きて帰る
- artwork_description: 死罪となった「がらんの画眉丸」は、死を目前に妻の言葉を思い出す。「人の心は、そんなに簡単に死なないわ」。里に残した妻がかけてくれた言葉だ。妻との「普通の暮らし」を手に入れるため、画眉丸は謎多き島・神仙郷へ向かう。そして心に強く誓う「必ず生きて帰る」と。
- rarity: SSR
- outpost_additional_hp: 100
- drop_design: ストーリークエスト「必ず生きて帰る」の6ステージに分散
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- 原画ID(例: `artwork_event_jig_0001`)
- 原画タイトル(最大40文字)
- 原画説明文(最大255文字)
- シリーズID(例: `jig`)
- レアリティ(N、R、SR、SSR、UR)
- 拠点追加HP(通常100)
- 欠片の数(通常16個)
- 欠片のドロップ設計

**任意情報**:
- アセットキー(記載がない場合は原画IDから推測)
- 表示順序(記載がない場合は連番)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstArtwork** - 原画の基本設定
2. **MstArtworkI18n** - 原画名・説明文(多言語対応)
3. **MstArtworkFragment** - 原画の欠片情報(16個)
4. **MstArtworkFragmentI18n** - 原画の欠片名(16個×言語数)
5. **MstArtworkFragmentPosition** - 原画の欠片の配置位置(16個)

#### ID採番ルール

原画のIDは以下の形式で採番します:

```
MstArtwork.id: artwork_{category}_{series_id}_{連番4桁}
MstArtworkI18n.id: {mst_artwork_id}_{language}
MstArtworkFragment.id: artwork_fragment_{category}_{series_id}_{連番5桁}
MstArtworkFragmentI18n.id: {mst_artwork_fragment_id}_{language}
MstArtworkFragmentPosition.id: {mst_artwork_fragment_id}
```

**例**:
```
artwork_event_jig_0001 (地獄楽イベント原画1)
artwork_event_jig_0001_ja (日本語I18n)
artwork_fragment_event_jig_00001 (原画1の欠片1)
artwork_fragment_event_jig_00001_ja (欠片1の日本語I18n)
```

**欠片ID連番ルール**:
- 1つ目の原画: 00001~00016(欠片1~16)
- 2つ目の原画: 00101~00116(欠片1~16)
- 3つ目の原画: 00201~00216(欠片1~16)

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(rarity、language等)
- [ ] 1つの原画につき16個の欠片が存在するか
- [ ] asset_numとpositionは1~16の値がすべて重複なく存在するか
- [ ] 欠片の3テーブル(MstArtworkFragment、MstArtworkFragmentI18n、MstArtworkFragmentPosition)で同じ欠片IDが存在するか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstArtwork.asset_key`: アセットキー(記載がない場合は原画IDから推測)
- `MstArtwork.sort_order`: 表示順序(記載がない場合は連番)
- `MstArtworkFragment.drop_group_id`: ドロップグループID(記載がない場合は標準パターンで推測)
- `MstArtworkFragment.asset_num`: アセット番号の順序(記載がない場合はランダムで推測)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の5シートを作成:
  1. MstArtwork
  2. MstArtworkI18n
  3. MstArtworkFragment(16個)
  4. MstArtworkFragmentI18n(16個×言語数)
  5. MstArtworkFragmentPosition(16個)

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstArtwork.asset_key
- 値: event_jig_0001
- 理由: 設計書にアセットキーの記載がなかったため、原画IDから推測
- 確認事項: アセット担当者と連携して正しいアセットキーに差し替えてください

### MstArtworkFragment.drop_group_id
- 値: event_jig_a_0001～event_jig_a_0006
- 理由: 設計書にドロップグループIDの記載がなかったため、標準パターン(6ステージに分散)を適用
- 確認事項: ステージ設計と照らし合わせて正しいドロップグループIDに差し替えてください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstArtwork シート

| ENABLE | id | mst_series_id | outpost_additional_hp | asset_key | sort_order | rarity | release_key |
|--------|----|--------------|-----------------------|-----------|-----------|--------|-------------|
| e | artwork_event_jig_0001 | jig | 100 | event_jig_0001 | 01 | SSR | 202601010 |
| e | artwork_event_jig_0002 | jig | 100 | event_jig_0002 | 02 | SSR | 202601010 |

### MstArtworkI18n シート

| ENABLE | release_key | id | mst_artwork_id | language | name | description |
|--------|-------------|----|---------------|----------|------|-------------|
| e | 202601010 | artwork_event_jig_0001_ja | artwork_event_jig_0001 | ja | 必ず生きて帰る | 死罪となった「がらんの画眉丸」は、死を目前に妻の言葉を思い出す。「人の心は、そんなに簡単に死なないわ」。里に残した妻がかけてくれた言葉だ。妻との「普通の暮らし」を手に入れるため、画眉丸は謎多き島・神仙郷へ向かう。そして心に強く誓う「必ず生きて帰る」と。 |
| e | 202601010 | artwork_event_jig_0002_ja | artwork_event_jig_0002 | ja | 兄は弟の道標だ！！ | 立場こそ死罪人と首切り役人だが、亜左 弔兵衛と山田浅ェ門 桐馬は、紛れもなく兄弟である。二人は、壮絶な幼少期を生き抜いてきた。親を失い路頭に迷う日々に、泣きじゃくる弟へ兄は言った。「何が正しいかわからねぇなら、オレだけを信じろ！」その言葉が示すように、二人の絆は成長した今も昔も変わらない。兄は弟を導き、弟は兄を信じ続けている。 |

### MstArtworkFragment シート(一部)

| ENABLE | release_key | id | mst_artwork_id | drop_group_id | drop_percentage | rarity | asset_num |
|--------|-------------|----|--------------|--------------|-----------------|----|-----------|
| e | 202601010 | artwork_fragment_event_jig_00001 | artwork_event_jig_0001 | event_jig_a_0001 | 100 | SSR | 7 |
| e | 202601010 | artwork_fragment_event_jig_00002 | artwork_event_jig_0001 | event_jig_a_0001 | 100 | SSR | 5 |
| e | 202601010 | artwork_fragment_event_jig_00003 | artwork_event_jig_0001 | event_jig_a_0002 | 100 | SSR | 16 |
| e | 202601010 | artwork_fragment_event_jig_00004 | artwork_event_jig_0001 | event_jig_a_0002 | 100 | SSR | 11 |
| ...(全16個) | | | | | | | |

### MstArtworkFragmentI18n シート(一部)

| ENABLE | release_key | id | mst_artwork_fragment_id | language | name |
|--------|-------------|----|-----------------------|----------|------|
| e | 202601010 | artwork_fragment_event_jig_00001_ja | artwork_fragment_event_jig_00001 | ja | 原画のかけら7 |
| e | 202601010 | artwork_fragment_event_jig_00002_ja | artwork_fragment_event_jig_00002 | ja | 原画のかけら5 |
| ...(全16個) | | | | | |

### MstArtworkFragmentPosition シート(一部)

| ENABLE | release_key | id | mst_artwork_fragment_id | position |
|--------|-------------|----|-----------------------|----------|
| e | 202601010 | artwork_fragment_event_jig_00001 | artwork_fragment_event_jig_00001 | 7 |
| e | 202601010 | artwork_fragment_event_jig_00002 | artwork_fragment_event_jig_00002 | 5 |
| ...(全16個) | | | | |

### 推測値レポート

#### MstArtwork.asset_key
- **値**: event_jig_0001
- **理由**: 設計書にアセットキーの記載がなかったため、原画IDから推測
- **確認事項**: アセット担当者と連携して正しいアセットキーに差し替えてください

#### MstArtworkFragment.drop_group_id
- **値**: event_jig_a_0001～event_jig_a_0006
- **理由**: 設計書にドロップグループIDの記載がなかったため、標準パターン(6ステージに分散)を適用
- **確認事項**: ステージ設計と照らし合わせて正しいドロップグループIDに差し替えてください

## 注意事項

### 欠片の数について

1つの原画につき必ず16個の欠片を作成してください。原画は4×4グリッド(16個のマス)で構成されます。

```
 1  |  2  |  3  |  4
----+-----+-----+-----
 5  |  6  |  7  |  8
----+-----+-----+-----
 9  | 10  | 11  | 12
----+-----+-----+-----
13  | 14  | 15  | 16
```

### asset_numとpositionの関係

MstArtworkFragment.asset_numとMstArtworkFragmentPosition.positionは通常同じ値を設定します。

### ドロップ設計について

通常、16個の欠片を6つのステージに分散配置します:

```
ステージ1 (drop_group_id: event_jig_a_0001): 欠片2個(例: asset_num 7, 5)
ステージ2 (drop_group_id: event_jig_a_0002): 欠片2個(例: asset_num 16, 11)
ステージ3 (drop_group_id: event_jig_a_0003): 欠片3個(例: asset_num 2, 9, 13)
ステージ4 (drop_group_id: event_jig_a_0004): 欠片3個(例: asset_num 8, 4, 3)
ステージ5 (drop_group_id: event_jig_a_0005): 欠片3個(例: asset_num 10, 14, 6)
ステージ6 (drop_group_id: event_jig_a_0006): 欠片3個(例: asset_num 12, 1, 15)
```

同じ`drop_group_id`を持つ欠片は同じステージで同時にドロップします。

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstArtworkI18n.mst_artwork_id` → `MstArtwork.id`
- `MstArtworkFragment.mst_artwork_id` → `MstArtwork.id`
- `MstArtworkFragmentI18n.mst_artwork_fragment_id` → `MstArtworkFragment.id`
- `MstArtworkFragmentPosition.mst_artwork_fragment_id` → `MstArtworkFragment.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: 欠片が16個に満たない場合

**原因**: 欠片の作成漏れ

**対処法**:
1. 原画1つにつき必ず16個の欠片を作成してください
2. asset_numは1~16のすべての数字を1回ずつ使用します

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid rarity: ssr (expected: SSR)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `ssr` → `SSR`, `ur` → `UR`

### Q3: ドロップグループIDがわからない場合

**対処法**:
運営仕様書に記載がない場合は、以下の命名規則を使用してください:

```
{event_id}_a_{ステージ連番4桁}

例:
event_jig_a_0001  (ステージ1)
event_jig_a_0002  (ステージ2)
event_jig_a_0003  (ステージ3)
```

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
