---
name: masterdata-from-bizops-pvp
description: PVP(ランクマッチ)の運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 2個(MstPvp, MstPvpI18n)。ランクマッチの基本設定とルール説明文を精度高く作成します。
---

# PVP(ランクマッチ) マスタデータ作成スキル

## 概要

PVP(ランクマッチ)の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の2テーブルを自動生成:

**PVP基本情報**:
- **MstPvp** - PVP基本設定(ランキング最低ランク、挑戦回数、初期BP等)
- **MstPvpI18n** - PVPルール説明(多言語対応)

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **mst_pvp_id** | PVP ID | `2026004` |
| **pvp_name** | シーズン名 | `地獄楽1ランクマ` |
| **mst_in_game_id** | インゲームID | `pvp_jig_01` |
| **ranking_min_pvp_rank_class** | 参加最低ランク | `Bronze`(Bronze/Silver/Gold/Platinum) |
| **max_daily_challenge_count** | フリーチャレンジ回数 | `10` |
| **max_daily_item_challenge_count** | チケットチャレンジ回数 | `10` |
| **item_challenge_cost_amount** | チケットコスト | `1` |
| **initial_battle_point** | 初期BP | `1000` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
PVP(ランクマッチ)の運営仕様書からマスタデータを作成してください。

添付ファイル:
- ランクマッチ開催仕様書_地獄楽1.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_pvp_id: 2026004
- pvp_name: 地獄楽1ランクマ
- mst_in_game_id: pvp_jig_01
- ranking_min_pvp_rank_class: Bronze
- max_daily_challenge_count: 10
- max_daily_item_challenge_count: 10
- item_challenge_cost_amount: 1
- initial_battle_point: 1000
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- PVP ID(例: 2026004、2026005)
- シーズン名(例: 地獄楽1ランクマ、地獄楽2ランクマ)
- 開催期間
- 参加最低ランク(例: Bronze)
- 1日の挑戦上限(例: フリー10回、チケット10回)
- チケットコスト(例: 1枚)
- インゲームID(例: pvp_jig_01、pvp_jig_02)
- 初期BP(例: 1000)
- ルール説明文(基本情報、コマ効果情報、特別ルール情報)

**任意情報**:
- 挑戦回数(記載がない場合はデフォルト値10回)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstPvp** - PVP基本設定
2. **MstPvpI18n** - PVPルール説明(多言語対応)

#### ID採番ルール

PVPのIDは以下の形式で採番します:

```
MstPvp.id: YYYYNNN (年+シーズン連番)
MstPvp.mst_in_game_id: pvp_{series_id}_{連番2桁}
MstPvpI18n.id: {mst_pvp_id} (MstPvp.idと同じ値)
```

**例**:
```
2026004 (2026年シーズン4)
pvp_jig_01 (地獄楽1ランクマ)
2026004 (日本語I18n)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(ranking_min_pvp_rank_class)
- [ ] 挑戦回数が妥当か(通常10回程度)
- [ ] descriptionに3セクション(基本情報、コマ効果情報、特別ルール情報)が含まれているか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstPvp.max_daily_challenge_count`: フリーチャレンジ回数(推測値)
- `MstPvp.max_daily_item_challenge_count`: チケットチャレンジ回数(推測値)
- `MstPvp.initial_battle_point`: 初期BP(特別ルールから推測)
- `MstPvpI18n.description`: ルール説明文(仕様書から抽出・整形)

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の2シートを作成:
  1. MstPvp
  2. MstPvpI18n

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstPvp.max_daily_challenge_count
- 値: 10(推測値)
- 理由: 設計書に挑戦回数の記載がなかったため、標準的な値を設定
- 確認事項: 運営側で適切な挑戦回数を確認してください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstPvp シート

| ENABLE | id | release_key | ranking_min_pvp_rank_class | max_daily_challenge_count | max_daily_item_challenge_count | item_challenge_cost_amount | mst_in_game_id | initial_battle_point |
|--------|----|-----------|-----------------------------|---------------------------|--------------------------------|----------------------------|----------------|---------------------|
| e | 2026004 | 202601010 | Bronze | 10 | 10 | 1 | pvp_jig_01 | 1000 |
| e | 2026005 | 202601010 | Bronze | 10 | 10 | 1 | pvp_jig_02 | 1000 |

### MstPvpI18n シート

| ENABLE | id | release_key | mst_pvp_id | language | name | description |
|--------|----|-----------|-----------|----|------|------------|
| e | 2026004 | 202601010 | 2026004 | ja | | 【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n毒コマが登場するぞ!\n特性で毒ダメージ軽減を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP! |

### 推測値レポート

#### MstPvp.max_daily_challenge_count
- **値**: 10(推測値)
- **理由**: 設計書に挑戦回数の記載がなかったため、標準的な値を設定
- **確認事項**: 運営側で適切な挑戦回数を確認してください

## 注意事項

### ID採番の特殊性について

PVP IDは、他のテーブルと異なり**年月日形式の数値(YYYYNNN)**を使用します:

```
YYYY: 年(例: 2026)
NNN: シーズン連番(例: 004、005)
```

### ルール説明文の構造について

MstPvpI18n.descriptionは、以下の3セクション構成で作成してください:

```
【基本情報】
(ステージ構成、リーダーP等の基本ルール)

【コマ効果情報】
(登場するコマ効果と対策)

【特別ルール情報】
(特別ルールの説明)
```

**作成例**:
```
【基本情報】
3段のステージで戦うぞ!
相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!

【コマ効果情報】
突風コマが登場するぞ!
特性で突風コマ無効化を持っているキャラを編成しよう!

【特別ルール情報】
リーダーPが1,000溜まった状態でバトルが開始されるぞ!
さらに、全キャラの体力のステータス値が3倍UP!
```

**注意点**:
- 改行は`\n`で表現する
- 【基本情報】【コマ効果情報】【特別ルール情報】の3セクションを必ず含める
- プレイヤーが理解しやすい表現を心がける

### 挑戦回数設定について

以下の2種類の挑戦回数を設定してください:

1. **max_daily_challenge_count**(フリーチャレンジ):
   - 通常: `10`回
   - リソースコストなし

2. **max_daily_item_challenge_count**(チケットチャレンジ):
   - 通常: `10`回
   - item_challenge_cost_amount: `1`(ランクマッチチケット1枚)

### 関連設定について

PVP設定には、以下の関連設定が必要です(別途作成):

**MstInGame**:
- id: pvp_{series_id}_{連番2桁}
- BGM、背景、ページID、敵配置ID等

**MstInGameSpecialRule**(特別ルールがある場合):
- 初期BP設定(InitialBattlePoint: 1000)
- 全キャラHP補正(AllUnitHpRate: 300)
- 全キャラATK補正(AllUnitAtkRate: 300)

**MstPage / MstKomaLine**:
- ステージ構成(3段ステージ等)
- コマ効果(毒コマ、突風コマ等)

**注意**: これらの関連設定の詳細は、「クエスト・ステージ手順書」および「インゲーム手順書」を参照してください。

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstPvp.mst_in_game_id` → `MstInGame.id`
- `MstPvpI18n.mst_pvp_id` → `MstPvp.id`

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: ID採番がわからない

**原因**: 年月日形式の数値(YYYYNNN)を理解していない

**対処法**:
```
YYYY: 年(例: 2026)
NNN: シーズン連番(例: 004、005)

例:
2026004 (2026年シーズン4)
2026005 (2026年シーズン5)
```

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid ranking_min_pvp_rank_class: bronze (expected: Bronze)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値: Bronze、Silver、Gold、Platinum
3. 頻出エラー: `bronze` → `Bronze`

### Q3: ルール説明文の構造がわからない

**対処法**:
- 【基本情報】【コマ効果情報】【特別ルール情報】の3セクションを必ず含める
- 改行は`\n`で表現する
- プレイヤーが理解しやすい表現を心がける

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
