---
name: masterdata-from-bizops-ingame
description: インゲーム(ゲームプレイ設定)の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 7個(MstInGame, MstInGameI18n, MstPage, MstKomaLine, MstInGameSpecialRule, MstInGameSpecialRuleUnitStatus, MstMangaAnimation)。BGM、コマ構成、特別ルール、原画演出等のマスタデータを精度高く作成します。
---

# インゲーム マスタデータ作成スキル

## 概要

インゲーム(ゲームプレイ環境)の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の7テーブルを自動生成:

**インゲーム基本情報**:
- **MstInGame** - インゲームの基本設定(BGM、背景、敵パラメータ係数等、19カラム)
- **MstInGameI18n** - リザルトTips、ステージ説明(多言語対応)

**コマ・ステージ構成**:
- **MstPage** - ページ(ステージ全体)の定義
- **MstKomaLine** - コマライン(コマの行)とコマ効果の詳細設定(41カラム)

**特別ルール**:
- **MstInGameSpecialRule** - 期間限定の特別ルール(スピードアタック、編成制限など)
- **MstInGameSpecialRuleUnitStatus** - 特別ルールでのユニットステータス補正

**演出**:
- **MstMangaAnimation** - ステージ開始・終了時の原画演出

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai) | `jig` |
| **in_game_id** | インゲームID | `event_jig1_1day_00001` |
| **content_type** | コンテンツタイプ | `event`(event/pvp/raid) |
| **bgm_asset_key** | BGMアセットキー | `SSE_SBG_003_001` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
インゲームの運営仕様書からマスタデータを作成してください。

添付ファイル:
- イベントクエスト設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- in_game_id: event_jig1_1day_00001
- content_type: event
- bgm_asset_key: SSE_SBG_003_001
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- インゲームID
- BGM設定(通常BGM、ボスBGM)
- 背景設定(アセットキー)
- コマ構成(段数、各段のコマ数、コマ幅、コマ効果)
- コマ効果(毒、突風、火傷など)の設定
- 敵パラメータ係数(HP倍率、攻撃倍率、スピード倍率)

**任意情報**:
- 特別ルール(スピードアタック、編成制限、ステータス補正など)
- リザルトTips(敗北時表示テキスト)
- ステージ説明(属性情報、ギミック情報)
- 原画演出(開始時・終了時・敵出現時)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstInGame** - インゲームの基本設定(19カラム)
2. **MstInGameI18n** - リザルトTips、ステージ説明
3. **MstPage** - ページの定義
4. **MstKomaLine** - コマラインとコマ効果(41カラム)
5. **MstInGameSpecialRule** - 特別ルール(設計書に記載がある場合のみ)
6. **MstInGameSpecialRuleUnitStatus** - ステータス補正(特別ルールがある場合のみ)
7. **MstMangaAnimation** - 原画演出(設計書に記載がある場合のみ)

#### ID採番ルール

```
MstInGame.id: {content_type}_{series_id}{連番}_{ステージ連番5桁}
MstPage.id: MstInGame.idと同じ値
MstKomaLine.id: {mst_page_id}_{row}
MstInGameSpecialRule.id: {target_id}_{連番}
MstMangaAnimation.id: genga_{series_id}_{クエストタイプ}_{連番2桁}_{条件}
```

**例**:
```
event_jig1_1day_00001 (地獄楽イベント1 デイリークエスト ステージ1)
event_jig1_1day_00001_1 (コマライン row=1)
pvp_jig_01 (地獄楽 PVP設定1)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] コマライン設定の完全性(各ページに少なくとも1つのコマライン、コマ幅の合計が1)
- [ ] enum値が正確に一致しているか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstKomaLine.effect_type`: コマ効果タイプ(記載がない場合は`None`)
- `MstKomaLine.koma_line_layout_asset_key`: レイアウトパターン(記載がない場合は標準パターン)
- `MstInGameSpecialRule.rule_type`: 特別ルールタイプ(記載がない場合は推測)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- 以下の7シート(条件付きを含む)を作成:
  1. MstInGame(19カラム)
  2. MstInGameI18n
  3. MstPage
  4. MstKomaLine(41カラム)
  5. MstInGameSpecialRule(特別ルールがある場合のみ)
  6. MstInGameSpecialRuleUnitStatus(特別ルールがある場合のみ)
  7. MstMangaAnimation(原画演出がある場合のみ)

#### 2. 推測値レポート(必須)

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 注意事項

### MstKomaLineの複雑性

最大41カラムの設定が必要です。主要なカラム:
- koma1～koma4: 各コマの設定(asset_key、width、effect_type、effect_parameter等)
- effect_type: None、Poison、Gust、Burn
- 空のコマは`__NULL__`または空欄で設定

### コマ幅の合計

1行のコマ幅の合計が`1`(100%)になるように設定してください。
例: `0.6 + 0.4 = 1`、`0.33 + 0.34 + 0.33 = 1`

### 特別ルールについて

設計書に記載がある場合のみ作成:
- SpeedAttack: 速攻ルール
- NoContinue: コンティニュー禁止
- PartySeries: パーティ制限
- UnitStatus: ユニットステータス変更

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstInGame.mst_page_id` → `MstPage.id`
- `MstInGame.mst_enemy_outpost_id` → `MstEnemyOutpost.id`
- `MstKomaLine.mst_page_id` → `MstPage.id`
- `MstInGameSpecialRule.target_id` → 該当するステージID・降臨バトルID・PVP ID

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: コマ幅の合計が1にならない

**対処法**:
コマ幅の合計が`1`(100%)になるように調整してください。
例: `0.6 + 0.4 = 1`、`0.25 + 0.25 + 0.25 + 0.25 = 1`

### Q2: enum値のエラーが発生する

**対処法**:
enum値は**大文字小文字を正確に一致**させる。正しいenum値:
- content_type: Stage、AdventBattle、Pvp
- effect_type: None、Poison、Gust、Burn
- effect_target_side: All、Player、Enemy

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます。
