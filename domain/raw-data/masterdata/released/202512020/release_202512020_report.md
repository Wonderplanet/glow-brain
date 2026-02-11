# リリースキー 202512020 マスタデータレポート

## 📊 概要

- **リリースキー**: 202512020
- **抽出日時**: 2026-02-11T01:44:24Z
- **総テーブル数**: 93
- **総行数**: 2,901

### カテゴリ別内訳

| カテゴリ | テーブル数 | 行数 |
|---------|-----------|------|
| **Mst** (固定マスタデータ) | 59 | 2,102 |
| **Opr** (運営施策・期間限定) | 10 | 323 |
| **I18n** (多言語対応) | 24 | 476 |

### 最大行数テーブル TOP10

1. **MstAutoPlayerSequence** - 354行（オートプレイヤーシーケンス）
2. **OprGachaPrize** - 247行（ガチャ排出内容）
3. **MstAttackElement** - 241行（攻撃要素）
4. **MstAttack** - 165行（攻撃定義）
5. **MstAttackI18n** - 165行（攻撃多言語）
6. **MstAdventBattleReward** - 119行（降臨バトル報酬）
7. **MstArtworkFragment** - 80行（アートワークフラグメント）
8. **MstArtworkFragmentI18n** - 80行（アートワークフラグメント多言語）
9. **MstArtworkFragmentPosition** - 80行（アートワークフラグメント配置）
10. **MstMissionReward** - 78行（ミッション報酬）

### 過去データ（参考）

対象リリースキーより小さいリリースキー（202512020未満）のデータも抽出されています。

- **過去テーブル数**: 155
- **過去総行数**: 38,228

これにより、今回のリリースで追加されたデータと、それ以前に存在していたデータを比較・分析することが可能です。

---

## 🎯 データ投入サマリー

このリリース（202512020）では、**『【推しの子】』コラボイベント**と**2026年正月キャンペーン**に関連する大規模なデータ投入が実施されました。

### 主要な追加内容

- **新作品『【推しの子】』の実装**
  - 新キャラクター 7体（UR x2、SSR x4、SR x1）
  - メインクエスト（Normal/Hard/Extra）+ イベントクエスト 6種類
  - ステージ 24個

- **正月キャンペーンとガチャ実装**
  - 新ガチャ 6種類（フェス、ピックアップ、有償限定、チケットガチャなど）
  - ガチャ排出内容 247件

- **降臨バトル実装**
  - quest_raid_osh1_00001（ファーストライブ）
  - 報酬・ランキング・クリア報酬の設定

- **イベントミッション・報酬体系**
  - イベントミッション 56件
  - ミッション報酬 78件
  - イベント専用アイテム・エンブレムの追加

---

## 🚀 主要な機能追加

### 1. 【推しの子】作品実装

GLOWプロジェクト初の大型コラボとして、『【推しの子】』作品が実装されました。

#### 新キャラクター（7体）

| ID | 名前 | レアリティ | ロール | 特徴 |
|----|------|-----------|-------|------|
| chara_osh_00001 | B小町不動のセンター アイ | UR | Technical | フェス限定、広範囲必殺ワザ |
| chara_osh_00101 | 復讐を誓う片星 星野 アクア | UR | Special | プレミアム、コマ選択攻撃 |
| chara_osh_00201 | 星野 ルビー | SSR | Attack | 自己バフ型アタッカー |
| chara_osh_00301 | MEMちょ | SSR | Support | ダメージカット支援 |
| chara_osh_00401 | 有馬 かな | SSR | Technical | スタン付与 |
| chara_osh_00501 | 黒川 あかね | SSR | Technical | 攻撃ダウン付与 |
| chara_osh_00601 | ぴえヨン | SR | Defense | 連続攻撃型防御キャラ（ドロップ入手） |

#### クエスト実装（9種類）

**メインクエスト:**
- quest_main_osh_normal_17（【推しの子】 Normal）
- quest_main_osh_hard_17（【推しの子】 Hard）
- quest_main_osh_veryhard_17（【推しの子】 Extra）

**イベントクエスト:**
- quest_event_osh1_charaget01（芸能界へ! - 収集）
- quest_event_osh1_challenge01（推しの子になってやる - チャレンジ）
- quest_event_osh1_charaget02（ぴえヨンのブートクエスト - 強化）
- quest_event_osh1_savage（芸能界には才能が集まる - 高難易度）
- quest_event_osh1_1day（ファンと推し合戦! - デイリー）
- quest_event_glo1_1day（開運!ジャンブル運試し - デイリー）

各クエストには合計 **24個のステージ** が用意され、難易度別に分かれています。

### 2. イベント実装

#### 【推しの子】 いいジャン祭（event_osh_00001）

- **期間**: 2026-01-01 00:00:00 ～ 2026-02-02 10:59:59
- **イベントミッション**: 53件
  - ステージクリア数ミッション（5回～600回）
  - ガチャ回数ミッション（賀正ガシャ10～50回）
  - 特定キャラ使用ミッション（ぴえヨン使用条件）
  - クエスト別ミッション
- **イベント専用アイテム**: item_glo_00001（いいジャン祭メダル【赤】）
- **エンブレム**: 10種類（キャラ推しエンブレム、B小町ロゴなど）

#### お正月キャンペーン（event_glo_00001）

- **期間**: 2026-01-01 00:00:00 ～ 2026-01-05 23:59:59
- **イベントミッション**: 3件（ステージクリア数）
- **エンブレム**: 2種類（祝！2026、開運）

### 3. 降臨バトル実装

#### quest_raid_osh1_00001（ファーストライブ）

- **期間**: 2026-01-09 15:00:00 ～ 2026-01-13 14:59:59
- **バトル形式**: ScoreChallenge（スコアチャレンジ型）
- **挑戦回数**: 通常3回 + 広告視聴2回
- **報酬体系**:
  - **クリア報酬**: 5段階（MstAdventBattleClearReward - 5件）
  - **バトルポイント報酬**: 119件（MstAdventBattleReward）
  - **ランキング報酬**: 16段階（MstAdventBattleRank）
  - **ランキングエンブレム**: 6種類（1位～1,000位）

報酬グループは54グループ（MstAdventBattleRewardGroup）に分割され、詳細な報酬設定がされています。

### 4. ガチャ実装（6種類）

| ID | 名前 | タイプ | 期間 | 特徴 |
|----|------|--------|------|------|
| Fest_osh_001 | 正月DXフェスガシャ | Festival | 2026-01-01～2026-02-02 | アイピックアップ、SSR以上確定 |
| Pickup_osh_001 | 【推しの子】 いいジャン祭ピックアップガシャ | Pickup | 2026-01-01～2026-02-02 | 新UR/SSR出現率UP、SR以上確定 |
| UR_newyear_001 | 2026年正月記念！UR1体確定ガシャ | PaidOnly | 2026-01-01～2026-02-02 | 有償1,500個、UR確定、回数制限10回 |
| UR_newyear_Ticket_001 | 2026年正月記念！UR1体確定ガシャ（チケット） | Ticket | 2026-01-01～2038-01-01 | チケット使用版 |
| gasho_001 | 賀正ガシャ2026 | Medal | 2026-01-01～2038-01-01 | 賀正ガシャチケット使用 |
| SSRticket_osh_001 | 【推しの子】SSR確定チケットガシャ | Ticket | 2026-01-01～2038-01-01 | 【推しの子】作品SSR確定 |

ガチャ排出内容（OprGachaPrize）は **247件** と最大規模で、各ガチャの詳細な排出設定が実装されています。

### 5. アートワーク機能（新規）

アートワークシステムが実装され、フラグメントを集めて完成させる機能が追加されました。

- **MstArtwork**: 5件（アートワーク本体）
- **MstArtworkFragment**: 80件（フラグメント）
- **MstArtworkFragmentPosition**: 80件（フラグメント配置情報）

多言語対応も完備（MstArtworkFragmentI18n - 80件）

### 6. オートプレイヤーシーケンス

**MstAutoPlayerSequence** に **354件** の大規模なシーケンスデータが追加されました。これは今回のリリースで最大行数のテーブルです。

---

## 📁 機能別データ詳細

### キャラクター関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstUnit | 7 | 新キャラクター基本情報 |
| MstUnitI18n | 7 | キャラクター多言語（名前・説明） |
| MstUnitAbility | 8 | キャラクターアビリティ |
| MstUnitSpecificRankUp | 6 | 特定キャラのランクアップ設定 |

### イベント・クエスト関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstEvent | 2 | イベント基本情報 |
| MstEventI18n | 2 | イベント多言語 |
| MstEventBonusUnit | 6 | イベントボーナスキャラ |
| MstEventDisplayUnit | 12 | イベント表示キャラ |
| MstEventDisplayUnitI18n | 12 | イベント表示キャラ多言語 |
| MstQuest | 9 | クエスト基本情報 |
| MstQuestI18n | 9 | クエスト多言語 |
| MstQuestBonusUnit | 7 | クエストボーナスキャラ |
| MstQuestEventBonusSchedule | 1 | クエストイベントボーナススケジュール |

### ステージ関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstStage | 24 | ステージ基本情報 |
| MstStageI18n | 24 | ステージ多言語 |
| MstStageReward | 60 | ステージ報酬 |
| MstStageClearTimeReward | 48 | ステージクリアタイム報酬 |
| MstStageEventReward | 68 | ステージイベント報酬 |
| MstStageEventSetting | 15 | ステージイベント設定 |
| MstStageEndCondition | 2 | ステージ終了条件 |

### ミッション・報酬関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstMissionEvent | 56 | イベントミッション |
| MstMissionEventI18n | 56 | イベントミッション多言語 |
| MstMissionEventDependency | 44 | イベントミッション依存関係 |
| MstMissionEventDailyBonus | 15 | イベントミッションデイリーボーナス |
| MstMissionEventDailyBonusSchedule | 1 | デイリーボーナススケジュール |
| MstMissionLimitedTerm | 4 | 期間限定ミッション |
| MstMissionLimitedTermI18n | 4 | 期間限定ミッション多言語 |
| MstMissionAchievement | 3 | 実績ミッション |
| MstMissionAchievementI18n | 3 | 実績ミッション多言語 |
| MstMissionAchievementDependency | 3 | 実績ミッション依存関係 |
| MstMissionReward | 78 | ミッション報酬 |

### 降臨バトル関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstAdventBattle | 1 | 降臨バトル基本情報 |
| MstAdventBattleI18n | 1 | 降臨バトル多言語 |
| MstAdventBattleReward | 119 | 降臨バトル報酬 |
| MstAdventBattleRewardGroup | 54 | 降臨バトル報酬グループ |
| MstAdventBattleClearReward | 5 | 降臨バトルクリア報酬 |
| MstAdventBattleRank | 16 | 降臨バトルランク |

### ガチャ関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| OprGacha | 6 | ガチャ基本情報 |
| OprGachaI18n | 6 | ガチャ多言語 |
| OprGachaPrize | 247 | ガチャ排出内容（最大） |
| OprGachaUpper | 2 | ガチャ天井設定 |
| OprGachaUseResource | 10 | ガチャ使用リソース |
| OprGachaDisplayUnitI18n | 18 | ガチャ表示キャラ多言語 |

### アイテム関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstItem | 12 | アイテム基本情報 |
| MstItemI18n | 12 | アイテム多言語 |

**追加アイテム:**
- キャラクターかけら（7種類）
- ぴえヨンのメモリー
- ガチャチケット（3種類）
- いいジャン祭メダル【赤】

### エンブレム関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstEmblem | 19 | エンブレム基本情報 |
| MstEmblemI18n | 19 | エンブレム多言語 |

**エンブレム種類:**
- メインクエスト用: 1件（emblem_normal_osh_00001）
- イベント用: 10件（キャラ推し、B小町ロゴなど）
- 降臨バトルランキング用: 6件（1位～1,000位）
- お正月キャンペーン用: 2件（祝！2026、開運）

### 交換所関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstExchange | 1 | 交換所基本情報 |
| MstExchangeI18n | 1 | 交換所多言語 |
| MstExchangeLineup | 26 | 交換所ラインナップ |
| MstExchangeCost | 26 | 交換所コスト |
| MstExchangeReward | 26 | 交換所報酬 |

### バトルシステム関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstAttack | 165 | 攻撃定義 |
| MstAttackI18n | 165 | 攻撃多言語 |
| MstAttackElement | 241 | 攻撃要素（属性・ダメージ等） |
| MstSpecialAttackI18n | 7 | 必殺技多言語 |
| MstSpecialRoleLevelUpAttackElement | 5 | 特殊ロールレベルアップ攻撃要素 |
| MstAutoPlayerSequence | 354 | オートプレイヤーシーケンス（最大） |

### 敵キャラクター関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstEnemyCharacter | 7 | 敵キャラクター基本情報 |
| MstEnemyCharacterI18n | 7 | 敵キャラクター多言語 |
| MstEnemyOutpost | 25 | 敵拠点 |
| MstEnemyStageParameter | 64 | 敵ステージパラメータ |

### インゲーム関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstInGame | 26 | インゲーム設定 |
| MstInGameI18n | 26 | インゲーム多言語 |
| MstInGameSpecialRule | 59 | インゲーム特殊ルール |

### その他の機能

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstHomeBanner | 5 | ホームバナー |
| MstIdleIncentiveItem | 15 | 放置報酬アイテム |
| MstIdleIncentiveReward | 3 | 放置報酬 |
| MstKomaLine | 77 | コマライン設定 |
| MstMangaAnimation | 20 | マンガアニメーション |
| MstPage | 26 | ページ設定 |
| MstPvp | 2 | PvP設定 |
| MstPvpI18n | 2 | PvP多言語 |
| MstSeries | 1 | シリーズ情報（【推しの子】） |
| MstSeriesI18n | 1 | シリーズ多言語 |

### ショップ・課金関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstPack | 6 | パック情報 |
| MstPackI18n | 6 | パック多言語 |
| MstPackContent | 36 | パック内容 |
| MstShopPass | 1 | ショップパス |
| MstShopPassI18n | 1 | ショップパス多言語 |
| MstShopPassReward | 2 | ショップパス報酬 |
| MstStoreProduct | 13 | ストア商品 |
| MstStoreProductI18n | 13 | ストア商品多言語 |
| OprProduct | 13 | 運営商品 |
| OprProductI18n | 13 | 運営商品多言語 |
| OprCampaign | 4 | 運営キャンペーン |
| OprCampaignI18n | 4 | 運営キャンペーン多言語 |

### 発話・UI関連

| テーブル | 行数 | 内容 |
|---------|------|------|
| MstSpeechBalloonI18n | 13 | 吹き出し多言語 |

---

## 🌐 多言語対応（I18n）

このリリースでは、**24テーブル、合計476行** の多言語対応データが追加されました。

### I18nテーブル一覧

| テーブル | 行数 | 対応内容 |
|---------|------|---------|
| MstAdventBattleI18n | 1 | 降臨バトル |
| MstArtworkFragmentI18n | 80 | アートワークフラグメント |
| MstArtworkI18n | 5 | アートワーク |
| MstAttackI18n | 165 | 攻撃 |
| MstEmblemI18n | 19 | エンブレム |
| MstEnemyCharacterI18n | 7 | 敵キャラクター |
| MstEventDisplayUnitI18n | 12 | イベント表示キャラ |
| MstEventI18n | 2 | イベント |
| MstExchangeI18n | 1 | 交換所 |
| MstInGameI18n | 26 | インゲーム |
| MstItemI18n | 12 | アイテム |
| MstMissionAchievementI18n | 3 | 実績ミッション |
| MstMissionEventI18n | 56 | イベントミッション |
| MstMissionLimitedTermI18n | 4 | 期間限定ミッション |
| MstPackI18n | 6 | パック |
| MstPvpI18n | 2 | PvP |
| MstQuestI18n | 9 | クエスト |
| MstSeriesI18n | 1 | シリーズ |
| MstShopPassI18n | 1 | ショップパス |
| MstSpecialAttackI18n | 7 | 必殺技 |
| MstSpeechBalloonI18n | 13 | 吹き出し |
| MstStageI18n | 24 | ステージ |
| MstStoreProductI18n | 13 | ストア商品 |
| MstUnitI18n | 7 | キャラクター |
| **OprGachaDisplayUnitI18n** | 18 | ガチャ表示キャラ（Oprカテゴリ） |
| **OprGachaI18n** | 6 | ガチャ（Oprカテゴリ） |
| **OprProductI18n** | 13 | 運営商品（Oprカテゴリ） |
| **OprCampaignI18n** | 4 | 運営キャンペーン（Oprカテゴリ） |

※ Oprカテゴリにも多言語対応テーブルが含まれています（4テーブル、41行）

---

## 📈 まとめ

### リリースの特徴

1. **大型コラボイベント実装**
   - 『【推しの子】』コラボは、GLOWプロジェクト初の本格的な外部IPコラボ
   - キャラクター、クエスト、イベント、ガチャなど多岐にわたる実装

2. **正月キャンペーン**
   - 2026年正月に合わせたキャンペーンとガチャ実装
   - 期間限定の特別な報酬とエンブレム

3. **新機能の追加**
   - 降臨バトル（ScoreChallenge型）
   - アートワークシステム
   - 大規模なオートプレイヤーシーケンス

4. **充実した報酬体系**
   - イベントミッション 56件
   - ミッション報酬 78件
   - 降臨バトル報酬 119件
   - 多様なエンブレム 19種類

### 規模感

- **総データ量**: 2,901行（93テーブル）
- **最大テーブル**: MstAutoPlayerSequence（354行）
- **多言語対応**: 476行（24テーブル）

このリリースは、GLOWプロジェクトにとって大きな節目となる大規模アップデートです。『【推しの子】』というビッグIPとのコラボレーションにより、新規ユーザーの獲得と既存ユーザーの満足度向上を目指した施策であることが分かります。

---

## 📂 生成ファイル

- **テーブル別CSV**: `domain/raw-data/masterdata/released/202512020/tables/`（93ファイル）
- **過去データCSV**: `domain/raw-data/masterdata/released/202512020/past_tables/`（155ファイル）
- **統計情報**: `domain/raw-data/masterdata/released/202512020/stats/`
  - `summary.json` - 全体統計（過去データ含む）
  - `tables.json` - テーブル別統計
  - `past_tables.json` - 過去データテーブル別統計
- **運営仕様書**: `domain/raw-data/masterdata/released/202512020/specs/`
  - `spreadsheet_list.csv` - 仕様書一覧（手動作成）
  - `specs.csv` - ローカルパス一覧（自動生成、20件）

---

## 📋 運営仕様書一覧

このリリースに関連する運営仕様書が **20件** 特定されました。すべてローカルで見つかっています。

### キャラクター設計（7件）

1. **ヒーロー基礎設計_chara_osh_00001_B小町不動のセンター アイ**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

2. **ヒーロー基礎設計_chara_osh_00101_星野 アクア**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

3. **ヒーロー基礎設計_chara_osh_00201_星野 ルビー**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

4. **ヒーロー基礎設計_chara_osh_00301_MEMちょ**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

5. **ヒーロー基礎設計_chara_osh_00401_有馬 かな**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

6. **ヒーロー基礎設計_chara_osh_00501_黒川 あかね**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

7. **ヒーロー基礎設計_chara_osh_00601_ぴえヨン**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/03_ヒーロー/キャラ設計/マスター/推しの子/`

### クエスト設計（9件）

8. **【17.【推しの子】】メインクエスト基礎設計**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/メインクエスト/`

9. **【収集クエスト】アイドルに憧れて**
   - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

10. **【強化（ストーリー2）】ぴえヨンのブートクエスト**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

11. **【1日1回】ファンと推し合戦!**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

12. **【チャレンジ】推しの子になってやる**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

13. **【デイリー】開運！ジャンブル運試し**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

14. **【高難度】芸能界には才能が集まる**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

15. **【降臨バトル】ファーストライブ**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202512020】【推しの子】いいジャン祭/`

16. **探索基礎設計**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/05_探索/`

### 運営施策（2件）

17. **正月特別号！【推しの子】 いいジャン祭＋メインクエスト_仕様書**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260101_推しの子/`

18. **20251227_年末年始キャンペーン仕様書**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20251226_年末年始/`

### その他（2件）

19. **GLOW_ID 管理**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/`

20. **GLOW_キャラ名称＆キャラセリフ資料**
    - `domain/raw-data/google-drive/spread-sheet/GLOW/011_監修/00_監修/開発監修資料/監修資料/キャラセリフ&名称など/`

**注**: 詳細なパスは `domain/raw-data/masterdata/released/202512020/specs/specs.csv` を参照してください。
