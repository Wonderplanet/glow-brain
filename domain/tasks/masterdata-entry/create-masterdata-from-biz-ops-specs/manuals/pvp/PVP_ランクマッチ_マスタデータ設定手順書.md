# PVP（ランクマッチ） マスタデータ設定手順書

## 概要

PVP（ランクマッチ）のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

PVPのマスタデータは、以下の2テーブル構成で作成します。

**PVP基本情報**:
- **MstPvp** - PVP基本設定（ランキング最低ランク、挑戦回数、初期BP等）
- **MstPvpI18n** - PVPルール説明（多言語対応）

**関連設定**（別途設定）:
- **MstInGame** - インゲーム設定（BGM、背景、ページ等）
- **MstInGameI18n** - リザルトTips
- **MstInGameSpecialRule** - 期間限定特殊ルール
- **MstInGameSpecialRuleUnitStatus** - 全キャラHP/ATK補正
- **MstPage** - ページ設定
- **MstKomaLine** - コマライン配置（突風コマ等）
- **MstAutoPlayerSequence** - PVP用オートプレイ設定

**重要**: MstPvpとMstPvpI18nは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

ランクマッチ開催仕様書から以下の情報を抽出します。

**必要情報**:
- PVP ID（例: 2026004、2026005）
- シーズン名（例: 地獄楽1ランクマ、地獄楽2ランクマ）
- 開催期間
- 参加最低ランク（例: Bronze）
- 1日の挑戦上限（例: フリー10回、チケット10回）
- チケットコスト（例: 1枚）
- インゲームID（例: pvp_jig_01、pvp_jig_02）
- 初期BP（例: 1000）
- ルール説明文
  - 基本情報（ステージ構成、リーダーP等）
  - コマ効果情報（毒コマ、突風コマ等）
  - 特別ルール情報（初期リーダーP、体力補正等）
- リリースキー（例: 202601010）

### 2. MstPvp シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,release_key,ranking_min_pvp_rank_class,max_daily_challenge_count,max_daily_item_challenge_count,item_challenge_cost_amount,mst_in_game_id,initial_battle_point
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | PVPの一意識別子。命名規則: 年月日形式の数値（例: `2026004`） |
| **release_key** | リリースキー。例: `202601010` |
| **ranking_min_pvp_rank_class** | ランキング参加最低ランククラス。下記の「ranking_min_pvp_rank_class設定一覧」を参照 |
| **max_daily_challenge_count** | 1日の最大チャレンジ回数（フリー）。例: `10` |
| **max_daily_item_challenge_count** | 1日の最大アイテムチャレンジ回数（チケット）。例: `10` |
| **item_challenge_cost_amount** | アイテムチャレンジのコスト数量。例: `1`（ランクマッチチケット1枚） |
| **mst_in_game_id** | インゲームID。MstInGame.idと対応。命名規則: `pvp_{series_id}_{連番2桁}` |
| **initial_battle_point** | バトル開始時の初期バトルポイント（リーダーP）。例: `1000` |

#### 2.3 ranking_min_pvp_rank_class設定一覧

PVPで使用可能なranking_min_pvp_rank_classは以下の通りです。**大文字小文字を正確に一致**させてください。

| ranking_min_pvp_rank_class | 説明 | 特徴 |
|----------|------|------|
| **Bronze** | ブロンズ | 初心者ランク、参加最低ランク |
| **Silver** | シルバー | 中級者ランク |
| **Gold** | ゴールド | 上級者ランク |
| **Platinum** | プラチナ | 最上級ランク |

**頻繁に使用されるranking_min_pvp_rank_class**:
- Bronze（最も一般的、参加障壁なし）

#### 2.4 ID採番ルール

PVP IDは、以下の形式で採番します。

```
YYYYNNN
```

**パラメータ**:
- `YYYY`: 年（例: 2026）
- `NNN`: シーズン連番（例: 004、005）

**採番例**:
```
2026004   (2026年シーズン4: 地獄楽1ランクマ)
2026005   (2026年シーズン5: 地獄楽2ランクマ)
```

**mst_in_game_idの採番**:
```
pvp_{series_id}_{連番2桁}
```

**採番例**:
```
pvp_jig_01   (地獄楽1ランクマ)
pvp_jig_02   (地獄楽2ランクマ)
```

#### 2.5 作成例

```
ENABLE,id,release_key,ranking_min_pvp_rank_class,max_daily_challenge_count,max_daily_item_challenge_count,item_challenge_cost_amount,mst_in_game_id,initial_battle_point
e,2026004,202601010,Bronze,10,10,1,pvp_jig_01,1000
e,2026005,202601010,Bronze,10,10,1,pvp_jig_02,1000
```

#### 2.6 設定のポイント

- **max_daily_challenge_count**: フリーチャレンジ回数。通常は10回程度
- **max_daily_item_challenge_count**: チケットチャレンジ回数。通常は10回程度
- **item_challenge_cost_amount**: チケットコスト。通常は1枚
- **initial_battle_point**: 初期BP。特別ルールで設定（例: 通常0→特別ルール1000）
- **ranking_min_pvp_rank_class**: 参加最低ランク。通常はBronze（誰でも参加可能）

### 3. MstPvpI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,id,release_key,mst_pvp_id,language,name,description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。MstPvp.idと同じ値を使用 |
| **release_key** | リリースキー。MstPvpと同じ値 |
| **mst_pvp_id** | PVP ID。MstPvp.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **name** | PVP名（通常は空欄） |
| **description** | ルール説明文。改行は`\n`で表現 |

#### 3.3 description設定のポイント

ルール説明文は、以下の構造で作成します。

```
【基本情報】
（ステージ構成、リーダーP等の基本ルール）

【コマ効果情報】
（登場するコマ効果と対策）

【特別ルール情報】
（特別ルールの説明）
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

#### 3.4 作成例

```
ENABLE,id,release_key,mst_pvp_id,language,name,description
e,2026004,202601010,2026004,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n毒コマが登場するぞ!\n特性で毒ダメージ軽減を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
e,2026005,202601010,2026005,ja,,"【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n突風コマが登場するぞ!\n特性で突風コマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP!"
```

## 関連設定（別途作成が必要）

### 4. MstInGame設定（PVP用）

PVP用のインゲーム設定を作成します。詳細は「クエスト・ステージ手順書」を参照してください。

**主要設定項目**:
- **id**: `pvp_{series_id}_{連番2桁}`（例: pvp_jig_01）
- **mst_auto_player_sequence_id**: PVP用オートプレイシーケンスID
- **bgm_asset_key**: BGMアセットキー（例: SSE_SBG_003_007）
- **background_asset_key**: 背景アセットキー（例: jig_00001）
- **mst_page_id**: ページID（例: pvp_jig_01）
- **enemy_placement_id**: 敵配置ID（例: pvp_jig_01）

### 5. MstInGameSpecialRule設定（特別ルール）

期間限定の特別ルールを設定します。

**主要設定項目**:
- **mst_in_game_id**: インゲームID（例: pvp_jig_02）
- **rule_type**: ルールタイプ（例: InitialBattlePoint、AllUnitHpRate）
- **rule_value**: ルール値（例: 1000、300）

**作成例**:
```
ENABLE,id,mst_in_game_id,rule_type,rule_value
e,pvp_jig_02_rule_01,pvp_jig_02,InitialBattlePoint,1000
e,pvp_jig_02_rule_02,pvp_jig_02,AllUnitHpRate,300
```

### 6. MstPage / MstKomaLine設定（ステージ構成）

PVP用のステージ構成（3段ステージ、突風コマ配置等）を設定します。詳細は「クエスト・ステージ手順書」を参照してください。

**主要設定項目**:
- **MstPage.id**: `pvp_{series_id}_{連番2桁}`（例: pvp_jig_01）
- **MstKomaLine**: コマ配置、コマ効果（毒コマ、突風コマ等）

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstPvp.id: 年月日形式の数値（YYYYNNN）
  - MstPvp.mst_in_game_id: `pvp_{series_id}_{連番2桁}`
  - MstPvpI18n.id: MstPvp.idと同じ値
  - MstPvpI18n.mst_pvp_id: MstPvp.idと対応

- [ ] **リレーションの整合性**
  - `MstPvp.mst_in_game_id` が `MstInGame.id` に存在する
  - `MstPvpI18n.mst_pvp_id` が `MstPvp.id` に存在する

- [ ] **enum値の正確性**
  - ranking_min_pvp_rank_class: Bronze、Silver、Gold、Platinum
  - 大文字小文字が正確に一致している

- [ ] **数値の妥当性**
  - max_daily_challenge_count、max_daily_item_challenge_count、item_challenge_cost_amount、initial_battle_pointが正の整数である
  - 挑戦回数が適切な範囲内（通常10回程度）

- [ ] **description設定の完全性**
  - 【基本情報】【コマ効果情報】【特別ルール情報】の3セクションが含まれている
  - 改行が`\n`で正しく表現されている

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - 他言語（en、zh-CN、zh-TW）も設定されている

- [ ] **ルール説明文の品質**
  - 誤字脱字がない
  - プレイヤーが理解しやすい表現である

- [ ] **関連設定の整合性**
  - MstInGame、MstPage、MstKomaLineが正しく設定されている
  - 特別ルール設定がMstInGameSpecialRuleに反映されている

## 出力フォーマット

最終的な出力は以下の2シート構成で行います。

### MstPvp シート

| ENABLE | id | release_key | ranking_min_pvp_rank_class | max_daily_challenge_count | max_daily_item_challenge_count | item_challenge_cost_amount | mst_in_game_id | initial_battle_point |
|--------|----|-----------|-----------------------------|---------------------------|--------------------------------|----------------------------|----------------|---------------------|
| e | 2026004 | 202601010 | Bronze | 10 | 10 | 1 | pvp_jig_01 | 1000 |
| e | 2026005 | 202601010 | Bronze | 10 | 10 | 1 | pvp_jig_02 | 1000 |

### MstPvpI18n シート

| ENABLE | id | release_key | mst_pvp_id | language | name | description |
|--------|----|-----------|-----------|----|------|------------|
| e | 2026004 | 202601010 | 2026004 | ja | | 【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n毒コマが登場するぞ!\n特性で毒ダメージ軽減を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP! |
| e | 2026005 | 202601010 | 2026005 | ja | | 【基本情報】\n3段のステージで戦うぞ!\n相手との距離があるので、リーダーPのバランスを考えてパーティを編成しよう!\n\n【コマ効果情報】\n突風コマが登場するぞ!\n特性で突風コマ無効化を持っているキャラを編成しよう!\n\n【特別ルール情報】\nリーダーPが1,000溜まった状態でバトルが開始されるぞ!\nさらに、全キャラの体力のステータス値が3倍UP! |

## 重要なポイント

- **2テーブル構成**: PVPは基本設定とI18nの2テーブルで管理
- **関連設定の多さ**: MstInGame、MstPage、MstKomaLine、MstInGameSpecialRule等の関連設定が必要
- **特別ルールの設定**: 初期BP、全キャラHP/ATK補正等の特別ルールはMstInGameSpecialRuleで設定
- **ルール説明文の構造**: 【基本情報】【コマ効果情報】【特別ルール情報】の3セクション構成
- **挑戦回数の管理**: フリーチャレンジとアイテムチャレンジの2種類の挑戦回数を管理
- **ID採番の特殊性**: 年月日形式の数値（YYYYNNN）を使用
- **外部キー整合性の徹底**: mst_in_game_idが正しくMstInGameに存在することを確認
