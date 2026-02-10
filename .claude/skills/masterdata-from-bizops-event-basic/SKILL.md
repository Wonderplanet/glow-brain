---
name: masterdata-from-bizops-event-basic
description: イベント基本設定の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 3個(MstEvent, MstEventI18n, MstHomeBanner)。イベント開催期間、バナー設定等のマスタデータを精度高く作成します。
---

# イベント基本設定 マスタデータ作成スキル

## 概要

イベント基本設定の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の3テーブルを自動生成:

**イベント基本情報**:
- **MstEvent** - イベントの基本情報(ID、シリーズ、開催期間等)
- **MstEventI18n** - イベント名・吹き出しテキスト(多言語対応)

**バナー設定**:
- **MstHomeBanner** - ホーム画面のバナー設定

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **event_id** | イベントID | `event_jig_00001` |
| **event_name** | イベント名 | `地獄楽いいジャン祭` |
| **start_at** | 開催開始日時 | `2026-01-16 15:00:00` |
| **end_at** | 開催終了日時 | `2026-02-16 10:59:59` |
| **is_displayed_series_logo** | 作品ロゴ表示有無 | `1`(表示) または `0`(非表示) |
| **is_displayed_jump_plus** | 作品を読むボタン表示有無 | `1`(表示) または `0`(非表示) |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
イベント基本設定の運営仕様書からマスタデータを作成してください。

添付ファイル:
- イベント設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- event_id: event_jig_00001
- event_name: 地獄楽いいジャン祭
- start_at: 2026-01-16 15:00:00
- end_at: 2026-02-16 10:59:59
- is_displayed_series_logo: 1
- is_displayed_jump_plus: 1
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- イベント名称(例: 「地獄楽いいジャン祭」)
- イベントID(例: `event_jig_00001`)
- シリーズID(例: `jig`)
- 開催期間(開始日時・終了日時)
- 作品ロゴ表示有無
- 作品を読むボタン表示有無
- リリースキー(例: `202601010`)

**任意情報**:
- 吹き出し内テキスト(記載がない場合は推測)
- バナー情報(遷移先、表示期間、表示順序)(記載がない場合は推測)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstEvent** - イベントの基本設定
2. **MstEventI18n** - イベント名・吹き出しテキスト(多言語対応)
3. **MstHomeBanner** - ホーム画面のバナー設定

#### ID採番ルール

イベント基本設定のIDは以下の形式で採番します:

**MstEvent.id**:
```
event_{series_id}_{連番5桁}
```

**MstEventI18n.id**:
```
{event_id}_{language}
```

**MstHomeBanner.id**:
```
数値の連番
```

**例**:
```
event_jig_00001 (地獄楽 イベント1)
event_jig_00001_ja (地獄楽 イベント1 日本語)
23 (ホームバナー23番)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(destination、language等)
- [ ] 開催期間が妥当か(start_at < end_at)
- [ ] バナー表示期間がイベント開催期間内に収まっているか
- [ ] MstHomeBanner.destination_pathが指定先のテーブルに存在するか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstEventI18n.balloon`: 吹き出し内テキスト(推測値)
- `MstHomeBanner.id`: バナーID(連番採番の開始番号を推測)
- `MstHomeBanner.asset_key`: バナーアセットキー(推測値)
- `MstHomeBanner.sort_order`: バナー表示順序(推測値)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の3シートを作成:
  1. MstEvent
  2. MstEventI18n
  3. MstHomeBanner

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### {テーブル名}.{カラム名}
- 値: {設定した値}
- 理由: {推測した根拠}
- 確認事項: {ユーザーが確認すべき内容}
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstEvent シート

| ENABLE | id | mst_series_id | is_displayed_series_logo | is_displayed_jump_plus | start_at | end_at | asset_key | release_key |
|--------|----|--------------|-----------------------|----------------------|----------|--------|----------|-------------|
| e | event_jig_00001 | jig | 1 | 1 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | event_jig_00001 | 202601010 |

### MstEventI18n シート

| ENABLE | release_key | id | mst_event_id | language | name | balloon |
|--------|------------|----|--------------|----|------|---------|
| e | 202601010 | event_jig_00001_ja | event_jig_00001 | ja | 地獄楽いいジャン祭 | 地獄楽いいジャン祭\n開催中! |

### MstHomeBanner シート

| ENABLE | id | destination | destination_path | asset_key | start_at | end_at | sort_order | release_key |
|--------|----|------------|----------------|----------|----------|--------|-----------|-------------|
| e | 23 | Event | event_jig_00001 | hometop_event_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 7 | 202601010 |
| e | 24 | Gacha | Pickup_jig_001 | hometop_gacha_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 6 | 202601010 |

### 推測値レポート

#### MstEventI18n.balloon
- **値**: 地獄楽いいジャン祭\n開催中!
- **理由**: 設計書に吹き出しテキストの記載がなかったため、イベント名を基に訴求力のあるテキストを推測
- **確認事項**: 吹き出しテキストが適切であることを確認し、必要に応じて調整してください

#### MstHomeBanner.id
- **値**: 23
- **理由**: 設計書にバナーIDの記載がなかったため、既存の最大IDから連番を推測
- **確認事項**: 既存のMstHomeBannerで使用されているIDと重複していないことを確認してください

#### MstHomeBanner.sort_order
- **値**: 7
- **理由**: 設計書に表示順序の記載がなかったため、標準的な値を設定
- **確認事項**: 他のバナーとの表示順序を確認し、必要に応じて調整してください

## 注意事項

### 開催期間について

開催期間は、以下の形式で設定してください:

```
start_at: YYYY-MM-DD HH:MM:SS
end_at:   YYYY-MM-DD HH:MM:SS
```

**注意点**:
- 日時指定は必ずダブルクォートで囲む
- start_atがend_atより前であることを確認
- イベント全体の開催期間を正確に設定(個別コンテンツの開催期間とは異なる場合がある)

### バナー設定について

MstHomeBannerは、ホーム画面に表示されるバナーを設定します。

**destination設定一覧**:

| destination | 説明 | destination_pathの指定内容 | 使用例 |
|------------|------|--------------------------|--------|
| **Event** | イベント画面 | イベントID | `event_jig_00001` |
| **Gacha** | ガチャ画面 | ガチャID | `Pickup_jig_001` |
| **CreditShop** | 有償ショップ | 空文字または商品ID | |
| **BasicShop** | 通常ショップ | 空文字または商品ID | |
| **Pack** | パック購入画面 | パックID | |
| **Pass** | パス購入画面 | パスID | |
| **BeginnerMission** | 初心者ミッション | 空文字 | |
| **AdventBattle** | 降臨バトル | 降臨バトルID | |
| **Pvp** | ランクマッチ | PVP ID | |
| **Web** | 外部Webページ | URL | |
| **None** | 遷移なし | 空文字 | |

**頻繁に使用されるdestination**:
- Event(イベント告知バナー)
- Gacha(ガチャ告知バナー)
- AdventBattle(降臨バトル告知バナー)

### バナーアセットキー命名規則

バナーのアセットキーは、以下の命名規則を推奨します。

```
hometop_{type}_{series_id}_{連番5桁}
```

**パラメータ**:
- `type`: バナーのタイプ
  - `event`: イベントバナー
  - `gacha`: ガチャバナー
  - `advent`: 降臨バトルバナー
- `series_id`: シリーズID(jig、osh、kai等)
- `連番5桁`: 連番(00001から)

**命名例**:
```
hometop_event_jig_00001   (地獄楽イベントバナー)
hometop_gacha_jig_00001   (地獄楽ガチャAバナー)
hometop_gacha_jig_00002   (地獄楽ガチャBバナー)
```

### バナー表示順序について

MstHomeBanner.sort_orderは、ホーム画面でのバナー表示順序を示します。

**ルール**:
- 数値が小さいほど上位に表示される
- 重要度の高いバナーには小さい数値を設定
- 同時に複数バナーを表示する場合、sort_orderで優先順位を調整

**例**:
```
イベントバナー: sort_order=7
ガチャAバナー: sort_order=6 (イベントバナーより優先)
ガチャBバナー: sort_order=6 (ガチャAと同優先度)
```

### 吹き出しテキストについて

MstEventI18n.balloonは、ホーム画面の吹き出し内に表示されるテキストです。

**ポイント**:
- 改行する場合は`\n`を使用
- ダブルクォートで囲むことで改行やカンマを含めることができる
- 簡潔かつ訴求力のあるテキストを設定

**例**:
```
"地獄楽いいジャン祭\n開催中!"
```

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstEventI18n.mst_event_id` → `MstEvent.id`
- `MstHomeBanner.destination_path (destination=Event)` → `MstEvent.id`
- `MstHomeBanner.destination_path (destination=Gacha)` → `OprGacha.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: enum値のエラーが発生する

**エラー**:
```
Invalid destination: event (expected: Event)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `event` → `Event`, `gacha` → `Gacha`

### Q2: バナー表示期間が不正

**原因**: バナー表示期間がイベント開催期間外に設定されている

**対処法**:
- MstHomeBannerのstart_atとend_atを、MstEventのstart_atとend_atの範囲内に設定

### Q3: バナーIDが重複している

**原因**: 既存のMstHomeBannerで使用されているIDと重複している

**対処法**:
- 既存のMstHomeBannerで使用されているIDを確認し、重複しないIDを採番

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
