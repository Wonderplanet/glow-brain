# リリースキー 202512020 データ投入レポート

## 概要

- **リリースキー**: 202512020
- **抽出日時**: 2026-01-10
- **総テーブル数**: 93テーブル
- **総行数**: 2,901行
- **カテゴリ別内訳**:
  - Mst（固定マスタデータ）: 59テーブル、2,102行
  - Opr（運営施策データ）: 10テーブル、323行
  - I18n（多言語対応）: 24テーブル、476行

## データ投入サマリー

このリリースでは、**【推しの子】コラボイベント「いいジャン祭」と2026年正月キャンペーン**に関する大規模なマスタデータが投入されました。新規キャラクター7体、複数のガチャ、降臨バトル、イベントクエスト、報酬システムなど、イベント運営に必要な全要素が含まれています。

## 主要な機能追加

### 1. 【推しの子】コラボイベント「いいジャン祭」
- **イベント期間**: 2026-01-01 00:00:00 〜 2026-02-02 10:59:59
- 【推しの子】作品とのコラボレーションによる大型イベント
- 新規キャラクター、クエスト、ガチャ、降臨バトルなど包括的なコンテンツ

### 2. お正月キャンペーン
- **キャンペーン期間**: 2026-01-01 00:00:00 〜 2026-01-05 23:59:59
- 正月限定のキャンペーン施策
- デイリークエストやガチャなど期間限定コンテンツ

### 3. 降臨バトル「ファーストライブ」
- **開催期間**: 2026-01-09 15:00:00 〜 2026-01-13 14:59:59
- スコアチャレンジ型の競技イベント
- ランキング報酬とエンブレム報酬を実装

## 機能別データ詳細

### キャラクター（MstUnit）

投入された新規キャラクター: **7体**

| ID | 日本語名 | レアリティ | ロール | 属性 |
|---|---|---|---|---|
| chara_osh_00001 | B小町不動のセンター アイ | UR | Technical | Red |
| chara_osh_00101 | 復讐を誓う片星 星野 アクア | UR | Special | Colorless |
| chara_osh_00201 | 星野 ルビー | SSR | Attack | Red |
| chara_osh_00301 | MEMちょ | SSR | Support | Yellow |
| chara_osh_00401 | 有馬 かな | SSR | Technical | Green |
| chara_osh_00501 | 黒川 あかね | SSR | Technical | Blue |
| chara_osh_00601 | ぴえヨン | SR | Defense | Green |

**特徴**:
- UR2体、SSR4体、SR1体という豪華なラインナップ
- 【推しの子】の主要キャラクターを網羅
- 各キャラクターは固有のアビリティとロールを持つ

### イベント（MstEvent）

投入されたイベント: **2件**

1. **event_osh_00001（【推しの子】 いいジャン祭）**
   - 期間: 2026-01-01 〜 2026-02-02
   - シリーズ: osh（推しの子）
   - メインコラボイベント

2. **event_glo_00001（お正月キャンペーン）**
   - 期間: 2026-01-01 〜 2026-01-05
   - シリーズ: glo（GLOW）
   - 正月限定キャンペーン

### ガチャ（OprGacha）

投入されたガチャ: **6種類**

1. **Fest_osh_001（正月DXフェスガシャ）**
   - タイプ: Festival
   - 期間: 2026-01-01 〜 2026-02-02
   - 説明: 「B小町不動のセンター アイ」の出現率UP中!
   - 特典: SSR以上1体確定、ピックアップURキャラ1体確定

2. **Pickup_osh_001（【推しの子】 いいジャン祭ピックアップガシャ）**
   - タイプ: Pickup
   - 期間: 2026-01-01 〜 2026-02-02
   - 説明: 新URキャラ1体と新SSRキャラ4体の出現率UP中!
   - 特典: SR以上1体確定、ピックアップURキャラ1体確定

3. **UR_newyear_001（2026年正月記念！UR1体確定ガシャ）**
   - タイプ: PaidOnly
   - 期間: 2026-01-01 〜 2026-02-02
   - 説明: 有償プリズム1,500個で引ける！URキャラ1体確定！
   - 回数制限: 10回まで

4. **UR_newyear_Ticket_001（2026年正月記念！UR1体確定ガシャ（チケット版））**
   - タイプ: Ticket
   - 期間: 2026-01-01 〜 2038-01-01
   - チケット使用版

5. **gasho_001（賀正ガシャ2026）**
   - タイプ: Medal
   - 期間: 2026-01-01 〜 2038-01-01
   - 賀正ガシャチケットで引けるガシャ

6. **SSRticket_osh_001（【推しの子】SSR確定チケットガシャ）**
   - タイプ: Ticket
   - 期間: 2026-01-01 〜 2038-01-01
   - 【推しの子】作品のSSRキャラのみ出現

**ガチャ賞品（OprGachaPrize）**: 247行 - 各ガチャの排出テーブル定義

### 降臨バトル（MstAdventBattle）

投入された降臨バトル: **1件**

- **quest_raid_osh1_00001（ファーストライブ）**
  - イベント: event_osh_00001（【推しの子】 いいジャン祭）
  - タイプ: ScoreChallenge
  - 期間: 2026-01-09 15:00:00 〜 2026-01-13 14:59:59
  - 挑戦回数: 1日3回（広告で+2回）
  - 説明: ボスを倒して高スコア獲得!!

**関連データ**:
- MstAdventBattleRank: 16段階のランク報酬
- MstAdventBattleReward: 119件の報酬定義
- MstAdventBattleRewardGroup: 54グループの報酬グループ
- MstAdventBattleClearReward: 5件のクリア報酬

### クエスト（MstQuest）

投入されたクエスト: **9件**

**メインクエスト（3件）**:
- quest_main_osh_normal_17（【推しの子】 - Normal）
- quest_main_osh_hard_17（【推しの子】 - Hard）
- quest_main_osh_veryhard_17（【推しの子】 - Extra）

**イベントクエスト（6件）**:
1. quest_event_osh1_charaget01（芸能界へ! - 収集クエスト）
2. quest_event_osh1_challenge01（推しの子になってやる - チャレンジクエスト）
3. quest_event_osh1_charaget02（ぴえヨンのブートクエスト - 強化クエスト）
4. quest_event_osh1_savage（芸能界には才能が集まる - 高難易度クエスト）
5. quest_event_osh1_1day（ファンと推し合戦! - デイリークエスト）
6. quest_event_glo1_1day（開運!ジャンブル運試し - デイリークエスト）

**関連データ**:
- MstStage: 24ステージ
- MstStageReward: 60件の報酬
- MstStageEventReward: 68件のイベント報酬
- MstStageClearTimeReward: 48件のクリアタイム報酬

### アイテム（MstItem）

投入されたアイテム: **12件**

**キャラクター素材（8件）**:
- memory_chara_osh_00601（ぴえヨンのメモリー - ランクアップ素材）
- piece_osh_00001 〜 piece_osh_00601（各キャラのかけら - グレードアップ素材）

**ガチャチケット（3件）**:
- ticket_glo_00207（『補てん』2026年正月記念！UR1体確定ガシャチケット）
- ticket_osh_10000（【推しの子】SSR確定ガシャチケット）
- ticket_glo_10001（賀正ガシャチケット Ver.2026）

**イベントアイテム（1件）**:
- item_glo_00001（いいジャン祭メダル【赤】 - 交換所用）

### エンブレム（MstEmblem）

投入されたエンブレム: **19件**

**シリーズエンブレム（1件）**:
- emblem_normal_osh_00001（【推しの子】 - メインクエストクリア証明）

**イベントエンブレム（10件）**:
- emblem_event_osh_00001（嘘吐き - 復讐を誓う星）
- emblem_event_osh_00002（B小町 - ロゴ）
- emblem_event_osh_00003 〜 00009（各キャラ推しエンブレム）
- emblem_event_osh_00010（アイのサイン）

**降臨バトルランキングエンブレム（6件）**:
- emblem_adventbattle_osh_season01_00001 〜 00006（最強で無敵のアイドル - 1位 〜 301-1,000位）

**GLOWイベントエンブレム（2件）**:
- emblem_event_glo_00001（祝！2026 - 正月記念）
- emblem_event_glo_00002（開運 - 強運の証）

### アートワーク（MstArtwork）

投入されたアートワーク: **5件**

**通常アートワーク（3件）**:
- artwork_osh_0001（激バズ！本能のヲタ芸！）
- artwork_osh_0002（やっと言えた）
- artwork_osh_0003（いってきます）

**イベントアートワーク（2件）**:
- artwork_event_osh_0001（絶対ママみたいになるんだ！）
- artwork_event_osh_0002（覆面筋トレ系ユーチューバー）

**関連データ**:
- MstArtworkFragment: 80個のフラグメント（各アートワークを16ピースに分割）
- MstArtworkFragmentPosition: 80個の配置情報

### ミッション（MstMissionEvent）

投入されたイベントミッション: **56件**

**関連データ**:
- MstMissionEventDependency: 44件の依存関係定義
- MstMissionReward: 78件の報酬
- MstMissionEventDailyBonus: 15件のデイリーボーナス
- MstMissionLimitedTerm: 4件の期間限定ミッション
- MstMissionAchievement: 3件の実績ミッション

### 交換所（MstExchange）

投入された交換所: **1件**

**関連データ**:
- MstExchangeLineup: 26件の交換ラインナップ
- MstExchangeCost: 26件のコスト定義
- MstExchangeReward: 26件の報酬定義

### パック（MstPack）

投入されたパック: **6件**

1. event_item_pack_7（【お一人様1回まで購入可】いいジャン祭 開催記念パック）
2. event_item_pack_8（【お一人様1回まで購入可】お正月 DXお得パック梅）
3. event_item_pack_9（【お一人様2回まで購入可】お正月 DXお得パック竹）
4. event_item_pack_10（【お一人様1回まで購入可】お正月 DXお得パック松）
5. event_item_pack_11（【お一人様1回まで購入可】お正月 超DX ジャンブルパック）
6. monthly_item_pack_2（【お一人様1回まで購入可】お得強化パック）

**関連データ**:
- MstPackContent: 36件のパック内容定義

### バトルシステム（MstInGame/MstAttack）

**インゲーム設定（MstInGame）**: 26件
- バトル中の各種ゲーム設定

**攻撃データ（MstAttack）**: 165件
- キャラクターの攻撃モーション定義

**攻撃エレメント（MstAttackElement）**: 241件
- 攻撃の属性・エフェクト定義

**AutoPlayer（MstAutoPlayerSequence）**: 354件
- オート戦闘のAI行動パターン定義

### 敵キャラクター

**敵キャラクター（MstEnemyCharacter）**: 7体
- イベント専用の敵キャラクター

**敵拠点（MstEnemyOutpost）**: 25件
- ステージ上の敵拠点配置

**敵パラメータ（MstEnemyStageParameter）**: 64件
- ステージごとの敵の強さ設定

### PvP（MstPvp）

投入されたPvP: **2件**
- PvP対戦モードの基本設定

### その他のシステム

**ホームバナー（MstHomeBanner）**: 5件
- イベント告知バナー

**放置報酬（MstIdleIncentive）**:
- MstIdleIncentiveItem: 15件
- MstIdleIncentiveReward: 3件

**ショップパス（MstShopPass）**: 1件
- MstShopPassReward: 2件

**コマライン（MstKomaLine）**: 77件
- ステージのコマ配置定義

**マンガアニメーション（MstMangaAnimation）**: 20件
- 演出用アニメーション定義

**ストア商品（MstStoreProduct）**: 13件
- ストアで販売される商品

**シリーズ（MstSeries）**: 1件
- osh（【推しの子】シリーズ）の基本情報

## 多言語対応（I18n）

合計24テーブルで476行の多言語データが投入されました。主に日本語（ja）の名称、説明文が含まれています。

**主要なI18nテーブル**:
- MstEventI18n（2件）
- MstUnitI18n（7件）
- MstQuestI18n（9件）
- MstItemI18n（12件）
- MstEmblemI18n（19件）
- MstAttackI18n（165件）
- MstArtworkI18n・MstArtworkFragmentI18n（85件）
- MstEventDisplayUnitI18n（12件）
- MstInGameI18n（26件）
- OprGachaI18n・OprGachaDisplayUnitI18n（24件）
- その他（115件）

## データ規模TOP10テーブル

| テーブル名 | 行数 | カテゴリ | 主な内容 |
|-----------|------|---------|---------|
| MstAutoPlayerSequence | 354 | Mst | オート戦闘AI定義 |
| OprGachaPrize | 247 | Opr | ガチャ排出テーブル |
| MstAttackElement | 241 | Mst | 攻撃属性・エフェクト |
| MstAttack | 165 | Mst | 攻撃モーション |
| MstAttackI18n | 165 | I18n | 攻撃の多言語名 |
| MstAdventBattleReward | 119 | Mst | 降臨バトル報酬 |
| MstArtworkFragment | 80 | Mst | アートワークピース |
| MstArtworkFragmentI18n | 80 | I18n | アートワークピース名 |
| MstArtworkFragmentPosition | 80 | Mst | ピース配置情報 |
| MstMissionReward | 78 | Mst | ミッション報酬 |

## まとめ

リリースキー202512020は、**【推しの子】コラボイベント「いいジャン祭」と2026年正月キャンペーン**を実現するための大規模なデータ投入です。

**規模感**:
- 93テーブル、2,901行という大規模投入
- 新規キャラクター7体を含む包括的なコンテンツ
- イベント、ガチャ、降臨バトル、ミッション、報酬など全要素を網羅

**特徴**:
- 【推しの子】という人気IPとのコラボレーション
- UR2体を含む豪華なキャラクターラインナップ
- 複数種類のガチャによる収益化施策
- 降臨バトルによるランキング競技要素
- 豊富なミッションと報酬による長期プレイ促進
- アートワーク収集要素による収集欲の刺激

**投入期間**:
- メインイベント: 約1ヶ月（2026-01-01 〜 2026-02-02）
- 正月キャンペーン: 5日間（2026-01-01 〜 2026-01-05）
- 降臨バトル: 4日間（2026-01-09 〜 2026-01-13）

このリリースは、年始の重要な施策として、新規ユーザー獲得と既存ユーザーの継続プレイの両方を狙った戦略的なデータ投入と言えます。
