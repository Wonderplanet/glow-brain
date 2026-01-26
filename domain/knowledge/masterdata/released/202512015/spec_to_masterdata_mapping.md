# 運営仕様書 → マスタデータ マッピング分析

## 対象
- **リリースキー**: 202512015
- **運営仕様書**: `20251222_クリスマスフェス限_イベント＋ガシャ＋パック仕様書`
- **マスタデータ**: `domain/raw-data/masterdata/released/202512015/tables/` 配下 51テーブル

---

## 1. 全体サマリー

### 仕様書ファイル → マスタデータテーブル対応一覧

| 仕様書ファイル | 主要マスタデータテーブル | 概要 |
|---|---|---|
| 01_概要.csv | MstUnit, MstUnitI18n, MstItem, MstItemI18n | 新キャラ・新アイテム定義 |
| 02_施策.csv (高難易度クエスト) | MstQuest, MstQuestI18n, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstInGame, MstInGameI18n, MstInGameSpecialRule, MstPage, MstKomaLine, MstEnemyOutpost, MstEnemyStageParameter, MstAutoPlayerSequence | クリスマスバトル!!クエスト |
| 02_施策.csv (コイン獲得クエスト) | MstQuestBonusUnit | コイン獲得クエストボーナスキャラ |
| 02_施策.csv (ホームバナー) | MstHomeBanner | ホーム画面バナー設定 |
| 02_施策.csv (クエストTOP表示) | MstEventDisplayUnit, MstEventDisplayUnitI18n, MstSpeechBalloonI18n | クエストTOP画面キャラ・セリフ |
| 06_クリスマスDXフェスガシャ_設計書.csv | OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n | ガシャ全般 |
| 07_クリスマスパック_お得プリズム_設計書.csv | MstPack, MstPackContent, MstPackI18n, MstStoreProduct, MstStoreProductI18n, OprProduct, OprProductI18n | パック・プリズム販売 |
| (レベルデザイン別途) | MstAttack, MstAttackElement, MstAttackI18n, MstSpecialAttackI18n, MstUnitAbility | キャラ戦闘パラメータ |
| (PvP別途) | MstPvp, MstPvpI18n, MstStageEndCondition, MstInGameSpecialRuleUnitStatus | PvPランクマッチ |
| (交換所定常) | MstExchange, MstExchangeCost, MstExchangeI18n, MstExchangeLineup, MstExchangeReward | コイン交換所 |
| (アイテム遷移定常) | MstItemTransition | アイテム遷移先 |

---

## 2. 仕様書ファイル別 詳細マッピング

---

### 2.1 `01_概要.csv` — 概要・新キャラ・新アイテム

#### 2.1.1 新キャラ一覧 → MstUnit / MstUnitI18n

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| キャラID | chara_yuw_00102 | MstUnit | id | chara_yuw_00102 | chara_yuw_00102 |
| キャラ名 | 愛届ける聖夜のサンタ 橘 美花莉 | MstUnitI18n | name | 愛届ける聖夜のサンタ 橘 美花莉 | chara_yuw_00102_ja |
| レアリティ | UR | MstUnit | rarity | UR | chara_yuw_00102 |
| 獲得属性色 | 緑 | MstUnit | color | Green | chara_yuw_00102 |
| フェス限 | 12/22 15:00スタート フェス限 | MstUnit | unit_label | FestivalUR | chara_yuw_00102 |

**注意**: MstUnitの他のカラム（hp, attack_power, role_type, abilities等）はレベルデザイン部門が設定し、仕様書の範囲外。

#### 2.1.2 新アイテム一覧 → MstItem / MstItemI18n

| 仕様書の項目 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|
| (新キャラのかけら) | MstItem | id | piece_yuw_00102 | piece_yuw_00102 |
| (新キャラのかけら) | MstItem | type | CharacterFragment | piece_yuw_00102 |
| (新キャラのかけら) | MstItem | rarity | UR | piece_yuw_00102 |
| (新キャラのかけら名称) | MstItemI18n | name | 愛届ける聖夜のサンタ 橘 美花莉のかけら | piece_yuw_00102_ja |

**導出ルール**: 新キャラ追加時に自動的に「キャラ名+のかけら」アイテムが作成される。

---

### 2.2 `02_施策.csv` — クリスマス限定高難度クエスト「クリスマスバトル!!」

#### 2.2.1 クエスト基本情報 → MstQuest / MstQuestI18n

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 名称 | クリスマスバトル!! | MstQuestI18n | name | クリスマスバトル!! | quest_event_yuw1_savage02_ja |
| カテゴリー | 高難易度 | MstQuestI18n | category_name | 高難易度 | quest_event_yuw1_savage02_ja |
| 開催期間（開始） | 12/22(月) 15:00 | MstQuest | start_date | 2025-12-22 15:00:00 | quest_event_yuw1_savage02 |
| 開催期間（終了） | 12/31(水) 23:59 | MstQuest | end_date | 2025-12-31 23:59:59 | quest_event_yuw1_savage02 |
| クエスト種別 | (高難易度クエスト) | MstQuest | quest_type | event | quest_event_yuw1_savage02 |

#### 2.2.2 ステージ定義 → MstStage

**ステージ1**（仕様書: 降臨バトル 1話）

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 推奨Lv | 75 | MstStage | recommended_level | 75 | event_yuw1_savage02_00001 |
| スタミナ | 50 | MstStage | cost_stamina | 50 | event_yuw1_savage02_00001 |
| 獲得リーダーEXP | 2500 | MstStage | exp | 2500 | event_yuw1_savage02_00001 |
| コイン（クリア報酬） | 2000 | MstStage | coin | 2000 | event_yuw1_savage02_00001 |
| ステージ番号 | 1 | MstStage | stage_number | 1 | event_yuw1_savage02_00001 |

**ステージ2**（仕様書: 降臨バトル 2話）

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 推奨Lv | 80 | MstStage | recommended_level | 80 | event_yuw1_savage02_00002 |
| スタミナ | 100 | MstStage | cost_stamina | 100 | event_yuw1_savage02_00002 |
| 獲得リーダーEXP | 5000 | MstStage | exp | 5000 | event_yuw1_savage02_00002 |
| コイン | 5000 | MstStage | coin | 5000 | event_yuw1_savage02_00002 |
| 開放条件 | ステージ1クリア | MstStage | prev_mst_stage_id | event_yuw1_savage02_00001 | event_yuw1_savage02_00002 |

#### 2.2.3 ステージ報酬 → MstStageEventReward

**ステージ1 初回クリア報酬**

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| プリズム | 100 | MstStageEventReward | resource_type=FreeDiamond, resource_amount=100 | 100 | 385 |
| コイン | 5000 | MstStageEventReward | resource_type=Coin, resource_amount=5000 | 5000 | 386 |
| スペシャルガシャチケット | 5 | MstStageEventReward | resource_type=Item, resource_id=ticket_glo_00002, resource_amount=5 | 5 | 387 |

**ステージ2 初回クリア報酬**

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| プリズム | 150 | MstStageEventReward | resource_type=FreeDiamond, resource_amount=150 | 150 | 388 |
| コイン | 10000 | MstStageEventReward | resource_type=Coin, resource_amount=10000 | 10000 | 389 |
| メモリーフラグメント・初級 | 20 | MstStageEventReward | resource_type=Item, resource_id=memoryfragment_glo_00001, resource_amount=20 | 20 | 390 |
| メモリーフラグメント・中級 | 10 | MstStageEventReward | resource_type=Item, resource_id=memoryfragment_glo_00002, resource_amount=10 | 10 | 391 |
| メモリーフラグメント・上級 | 1 | MstStageEventReward | resource_type=Item, resource_id=memoryfragment_glo_00003, resource_amount=1 | 1 | 392 |

#### 2.2.4 クリアタイム報酬 → MstStageClearTimeReward

**ステージ1**

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 100秒(=100000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=100000, resource_amount=20 | 20 | event_yuw1_savage02_00001_1 |
| 200秒(=200000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=200000, resource_amount=20 | 20 | event_yuw1_savage02_00001_2 |
| 300秒(=300000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=300000, resource_amount=20 | 20 | event_yuw1_savage02_00001_3 |

**ステージ2**

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 300秒(=300000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=300000, resource_amount=20 | 20 | event_yuw1_savage02_00002_1 |
| 400秒(=400000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=400000, resource_amount=20 | 20 | event_yuw1_savage02_00002_2 |
| 500秒(=500000ms) | プリズム20 | MstStageClearTimeReward | upper_clear_time_ms=500000, resource_amount=20 | 20 | event_yuw1_savage02_00002_3 |

#### 2.2.5 特別ルール → MstInGameSpecialRule

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ステージ1 特別ルール | スピードアタック | MstInGameSpecialRule | rule_type=SpeedAttack | SpeedAttack | event_yuw1_savage02_00001_1 |
| ステージ1 コンテニュー不可 | (暗黙) | MstInGameSpecialRule | rule_type=NoContinue, rule_value=1 | NoContinue | event_yuw1_savage02_00001_2 |
| ステージ2 特別ルール | スピードアタック/編成制限 | MstInGameSpecialRule | rule_type=SpeedAttack | SpeedAttack | event_yuw1_savage02_00002_1 |
| ステージ2 編成制限(サポート) | サポート | MstInGameSpecialRule | rule_type=PartyRoleType, rule_value=Support | PartyRoleType | event_yuw1_savage02_00002_2 |
| ステージ2 編成制限(ディフェンス) | ディフェンス | MstInGameSpecialRule | rule_type=PartyRoleType, rule_value=Defense | PartyRoleType | event_yuw1_savage02_00002_3 |
| ステージ2 編成制限(アタック) | アタック | MstInGameSpecialRule | rule_type=PartyRoleType, rule_value=Attack | PartyRoleType | event_yuw1_savage02_00002_4 |
| ステージ2 コンテニュー不可 | (暗黙) | MstInGameSpecialRule | rule_type=NoContinue, rule_value=1 | NoContinue | event_yuw1_savage02_00002_5 |

#### 2.2.6 ステージイベント設定 → MstStageEventSetting

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ステージ1 背景ID | (仕様書にはkoma_background_glo_00024) | MstStageEventSetting | background_asset_key | glo_00033 | 119 |
| ステージ1 開催期間 | 12/22 15:00〜12/31 23:59 | MstStageEventSetting | start_at/end_at | 2025-12-22 15:00:00 / 2025-12-31 23:59:59 | 119 |
| ステージ2 背景ID | - | MstStageEventSetting | background_asset_key | glo_00033 | 120 |
| ステージ2 開催期間 | 12/22 15:00〜12/31 23:59 | MstStageEventSetting | start_at/end_at | 2025-12-22 15:00:00 / 2025-12-31 23:59:59 | 120 |

#### 2.2.7 クエストTOP表示キャラ → MstEventDisplayUnit / MstEventDisplayUnitI18n

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 表示キャラ1 | 奥村正宗 | MstEventDisplayUnit | mst_unit_id | chara_yuw_00601 | quest_event_yuw1_savage021 |
| 表示キャラ1セリフ | 好きな物の話は否定しない!! | MstEventDisplayUnitI18n | speech_balloon_text1 | 好きな物の話は\n否定しない!! | quest_event_yuw1_savage021_ja |
| 表示キャラ2 | 羽生まゆり | MstEventDisplayUnit | mst_unit_id | chara_yuw_00201 | quest_event_yuw1_savage022 |
| 表示キャラ2セリフ | コスプレしたいからするのよ | MstEventDisplayUnitI18n | speech_balloon_text1 | コスプレしたい\nからするのよ | quest_event_yuw1_savage022_ja |

#### 2.2.8 インゲーム設定 → MstInGame / MstInGameI18n

| 仕様書の項目 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|
| ステージ1 BGM | MstInGame | bgm_asset_key | SSE_SBG_003_006 | event_yuw1_savage02_00001 |
| ステージ1 ボスBGM | MstInGame | boss_bgm_asset_key | SSE_SBG_003_007 | event_yuw1_savage02_00001 |
| ステージ1 ステージ段数 | MstInGame | boss_count | 1 | event_yuw1_savage02_00001 |
| ステージ2 BGM | MstInGame | bgm_asset_key | SSE_SBG_003_006 | event_yuw1_savage02_00002 |
| ステージ2 ボスBGM | MstInGame | boss_bgm_asset_key | SSE_SBG_003_007 | event_yuw1_savage02_00002 |
| ステージ2 ステージ段数 | MstInGame | boss_count | 1 | event_yuw1_savage02_00002 |

#### 2.2.9 敵拠点 → MstEnemyOutpost

| 仕様書の項目 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|
| ステージ1 敵拠点HP | MstEnemyOutpost | hp | 500000 | event_yuw1_savage02_00001 |
| ステージ2 敵拠点HP | MstEnemyOutpost | hp | 750000 | event_yuw1_savage02_00002 |

#### 2.2.10 敵パラメータ → MstEnemyStageParameter

| 仕様書の項目 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|
| ボス敵（奥村正宗） | MstEnemyStageParameter | character_unit_kind=Boss, role_type=Defense, color=Yellow | - | c_yuw_00601_savege02_yuw_Boss_Yellow |
| 通常敵 | MstEnemyStageParameter | character_unit_kind=Normal, role_type=Attack, color=Yellow | - | e_glo_00001_savage02_yuw_tank_Normal_Yellow |
| ボス敵（羽生まゆり） | MstEnemyStageParameter | character_unit_kind=Boss, role_type=Support, color=Yellow | - | c_yuw_00201_savage02_yuw_support_Boss_Yellow |

---

### 2.3 `02_施策.csv` — コイン獲得クエスト ボーナスキャラ

#### MstQuestBonusUnit

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ボーナス対象キャラ | 愛届ける聖夜のサンタ 橘 美花莉 | MstQuestBonusUnit | mst_unit_id | chara_yuw_00102 | 41 |
| ボーナス率 | 30% | MstQuestBonusUnit | coin_bonus_rate | 0.3 | 41 |
| 対象クエスト | コイン獲得クエスト | MstQuestBonusUnit | mst_quest_id | quest_enhance_00001 | 41 |
| 開始日時 | 2025/12/22 15:00 | MstQuestBonusUnit | start_at | 2025-12-22 15:00:00 | 41 |
| 終了日時 | 2026/01/16 10:59 | MstQuestBonusUnit | end_at | 2026-01-16 10:59:59 | 41 |

---

### 2.4 `02_施策.csv` — ホームバナー設定

#### MstHomeBanner

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| クリスマス限定ガシャ | ガシャバナー | MstHomeBanner | destination=Gacha, destination_path=Fest_Xmas_001, asset_key=hometop_gacha_glo_00001 | sort_order=10 | 20 |
| パック | パックバナー | MstHomeBanner | destination=Pack, asset_key=hometop_shop_pack_00016 | sort_order=9 | 21 |
| プリズム | プリズムバナー | MstHomeBanner | destination=CreditShop, asset_key=hometop_campaign_00003 | sort_order=8 | 22 |

| 仕様書日付 | テーブル | カラム(start_at) | カラム(end_at) |
|---|---|---|---|
| ガシャ: 2025-12-22 15:00〜2025-12-31 23:59 | MstHomeBanner(id=20) | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 |
| パック: 2025-12-22 15:00〜2025-12-26 14:59 | MstHomeBanner(id=21) | 2025-12-22 15:00:00 | 2025-12-26 14:59:59 |
| プリズム: 2025-12-26 15:00〜2025-12-31 23:59 | MstHomeBanner(id=22) | 2025-12-26 15:00:00 | 2025-12-31 23:59:59 |

---

### 2.5 `06_クリスマスDXフェスガシャ_設計書.csv` — ガシャ設定

#### 2.5.1 ガシャ基本情報 → OprGacha

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ガシャID | Fest_Xmas_001 | OprGacha | id | Fest_Xmas_001 | Fest_Xmas_001 |
| 種別 | フェス限定ガシャ | OprGacha | gacha_type | Festival | Fest_Xmas_001 |
| 開催期間（開始） | 2025/12/22 12:00 | OprGacha | start_at | 2025-12-22 12:00:00 | Fest_Xmas_001 |
| 開催期間（終了） | 2026/1/16 10:59 | OprGacha | end_at | 2026-1-16 10:59:59 | Fest_Xmas_001 |
| 10連設定 | 10連 | OprGacha | multi_draw_count | 10 | Fest_Xmas_001 |
| 確定枠設定 | 10枠目 | OprGacha | multi_fixed_prize_count | 1 | Fest_Xmas_001 |
| 天井設定 | 有 | OprGacha | upper_group | Fest_Xmas_001 | Fest_Xmas_001 |
| ガシャ優先度 | - | OprGacha | gacha_priority | 200 | Fest_Xmas_001 |

#### 2.5.2 ガシャ表示テキスト → OprGachaI18n

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ガシャ名 | クリスマスDXフェスガシャ | OprGachaI18n | name | クリスマスDXフェスガシャ | Fest_Xmas_001_ja |
| ①ガシャ訴求文言 | 「愛届ける聖夜のサンタ 橘 美花莉」の出現率UP中! | OprGachaI18n | description | 「愛届ける聖夜のサンタ 橘 美花莉」\nの出現率UP中! | Fest_Xmas_001_ja |
| ④天井表示 | ピックアップURキャラ1体確定! | OprGachaI18n | pickup_upper_description | ピックアップURキャラ1体確定! | Fest_Xmas_001_ja |
| 確定枠説明 | SSR以上1体確定 | OprGachaI18n | fixed_prize_description | SSR以上1体確定 | Fest_Xmas_001_ja |
| banner_URL | glo_00001 | OprGachaI18n | banner_url | glo_00001 | Fest_Xmas_001_ja |
| ロゴ | fes_00001 | OprGachaI18n | logo_asset_key | fes_00001 | Fest_Xmas_001_ja |
| バナーサイズ | (SizeL) | OprGachaI18n | gacha_banner_size | SizeL | Fest_Xmas_001_ja |
| バナー色 | (Yellow) | OprGachaI18n | gacha_background_color | Yellow | Fest_Xmas_001_ja |

#### 2.5.3 ガシャラインナップ → OprGachaPrize

**通常ラインナップ (group_id: Fest_Xmas_001)** — 42行

| 仕様書項目 | 仕様書のキャラID | テーブル | resource_id | weight | pickup | 行ID |
|---|---|---|---|---|---|---|
| ピックアップUR | chara_yuw_00102 | OprGachaPrize | chara_yuw_00102 | 351 | 1 | Fest_Xmas_001_1 |
| UR (13体) | chara_spy_00101〜chara_sum_00101 | OprGachaPrize | 各キャラID | 81 | 0 | Fest_Xmas_001_2〜14 |
| SSR (9体) | chara_dan_00101〜chara_dos_00101 | OprGachaPrize | 各キャラID | 520 | 0 | Fest_Xmas_001_15〜23 |
| SR (10体) | chara_gom_00101〜chara_sum_00201 | OprGachaPrize | 各キャラID | 1638 | 0 | Fest_Xmas_001_24〜33 |
| R (9体) | chara_aka_00001〜chara_sum_00001 | OprGachaPrize | 各キャラID | 2704 | 0 | Fest_Xmas_001_34〜42 |

**SSR確定枠ラインナップ (group_id: fixd_Fest_Xmas_001)** — 23行

| 仕様書項目 | テーブル | resource_id | weight | 行ID |
|---|---|---|---|---|
| ピックアップUR (1体) | OprGachaPrize | chara_yuw_00102 | 1755 | fixd_Fest_Xmas_001_1 |
| UR (13体) | OprGachaPrize | 各URキャラID | 405 | fixd_Fest_Xmas_001_2〜14 |
| SSR (9体) | OprGachaPrize | 各SSRキャラID | 25220 | fixd_Fest_Xmas_001_15〜23 |

#### 2.5.4 天井設定 → OprGachaUpper

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| 天井回数 | 200回 | OprGachaUpper | count | 200 | 14 |
| 天井種別 | ピックアップ確定 | OprGachaUpper | upper_type | Pickup | 14 |
| 対象ガシャ | Fest_Xmas_001 | OprGachaUpper | upper_group | Fest_Xmas_001 | 14 |

#### 2.5.5 ガシャコスト → OprGachaUseResource

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| チケット名 | フェスガシャチケット | OprGachaUseResource | cost_type=Item, cost_id=ticket_glo_00004, draw_count=1 | 1枚で1回 | 49 |
| 単発プリズム個数 | 150個 | OprGachaUseResource | cost_type=Diamond, cost_num=150, draw_count=1 | 150 | 50 |
| 10連プリズム個数 | 1,500個 | OprGachaUseResource | cost_type=Diamond, cost_num=1500, draw_count=10 | 1500 | 51 |

#### 2.5.6 訴求キャラ表示 → OprGachaDisplayUnitI18n

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| ②訴求キャラ/ID | 橘 美花莉(クリスマス) / chara_yuw_00102 | OprGachaDisplayUnitI18n | mst_unit_id | chara_yuw_00102 | 46 |
| ③キャラ訴求文言 | 広範囲の必殺ワザで相手にダメージを与えオブジェクトをランダムに生成する! | OprGachaDisplayUnitI18n | description | 広範囲の必殺ワザで\n相手にダメージを与え\nオブジェクトを\nランダムに生成する! | 46 |

---

### 2.6 `07_クリスマスパック_お得プリズム_設計書.csv` — パック・プリズム販売

#### 2.6.1 パック定義 → MstPack

| 仕様書の項目 | 仕様書のID | テーブル | id | product_sub_id | 行ID |
|---|---|---|---|---|---|
| お得パックA | Xmas_item_pack_1 | MstPack | Xmas_item_pack_1 | 28 | Xmas_item_pack_1 |
| お得パックB | Xmas_item_pack_2 | MstPack | Xmas_item_pack_2 | 29 | Xmas_item_pack_2 |
| お得パックC | Xmas_item_pack_3 | MstPack | Xmas_item_pack_3 | 30 | Xmas_item_pack_3 |

#### 2.6.2 パック名称 → MstPackI18n

| 仕様書の項目 | テーブル | name | 行ID |
|---|---|---|---|
| お得パックA | MstPackI18n | 【お一人様1回まで購入可】\nクリスマス お得パックA | Xmas_item_pack_1_ja |
| お得パックB | MstPackI18n | 【お一人様1回まで購入可】\nクリスマス お得パックB | Xmas_item_pack_2_ja |
| お得パックC | MstPackI18n | 【お一人様1回まで購入可】\nクリスマス お得パックC | Xmas_item_pack_3_ja |

#### 2.6.3 パック内容 → MstPackContent

**お得パックA (Xmas_item_pack_1)**

| 仕様書アイテム | 仕様書数量 | テーブル | resource_type | resource_id | resource_amount | 行ID |
|---|---|---|---|---|---|---|
| カラーメモリー・グレー | 500 | MstPackContent | Item | memory_glo_00001 | 500 | 57 |
| カラーメモリー・レッド | 500 | MstPackContent | Item | memory_glo_00002 | 500 | 58 |
| カラーメモリー・グリーン | 500 | MstPackContent | Item | memory_glo_00003 | 500 | 59 |
| カラーメモリー・ブルー | 500 | MstPackContent | Item | memory_glo_00004 | 500 | 60 |
| カラーメモリー・イエロー | 500 | MstPackContent | Item | memory_glo_00005 | 500 | 61 |
| スペシャルガシャチケット | 10 | MstPackContent | Item | ticket_glo_00002 | 10 | 62 |

**お得パックB (Xmas_item_pack_2)**

| 仕様書アイテム | 仕様書数量 | テーブル | resource_type | resource_id | resource_amount | 行ID |
|---|---|---|---|---|---|---|
| フェスガシャチケット | 5 | MstPackContent | Item | ticket_glo_00004 | 5 | 63 |
| ピックアップガシャチケット | 5 | MstPackContent | Item | ticket_glo_00003 | 5 | 64 |
| メモリーフラグメント・上級 | 5 | MstPackContent | Item | memoryfragment_glo_00003 | 5 | 65 |
| メモリーフラグメント・中級 | 30 | MstPackContent | Item | memoryfragment_glo_00002 | 30 | 66 |
| メモリーフラグメント・初級 | 50 | MstPackContent | Item | memoryfragment_glo_00001 | 50 | 67 |

**お得パックC (Xmas_item_pack_3)**

| 仕様書アイテム | 仕様書数量 | テーブル | resource_type | resource_id | resource_amount | 行ID |
|---|---|---|---|---|---|---|
| UR1体確定10連ガシャチケット | 1 | MstPackContent | Item | ticket_glo_00203 | 1 | 68 |
| 選べるURキャラのかけらBOX | 10 | MstPackContent | Item | box_glo_00008 | 10 | 69 |
| フェスガシャチケット | 5 | MstPackContent | Item | ticket_glo_00004 | 5 | 70 |
| ピックアップガシャチケット | 5 | MstPackContent | Item | ticket_glo_00003 | 5 | 71 |

#### 2.6.4 ストア商品 → MstStoreProduct

| 仕様書の項目 | 仕様書の値 | テーブル | カラム | 設定値 | 行ID |
|---|---|---|---|---|---|
| パックA Billing ID (iOS) | BNEI0434_0027 | MstStoreProduct | product_id_ios | BNEI0434_0027 | 28 |
| パックA Billing ID (Android) | com.bandainamcoent.jumble_0027 | MstStoreProduct | product_id_android | com.bandainamcoent.jumble_0027 | 28 |
| パックB Billing ID (iOS) | BNEI0434_0028 | MstStoreProduct | product_id_ios | BNEI0434_0028 | 29 |
| パックB Billing ID (Android) | com.bandainamcoent.jumble_0028 | MstStoreProduct | product_id_android | com.bandainamcoent.jumble_0028 | 29 |
| パックC Billing ID (iOS) | BNEI0434_0029 | MstStoreProduct | product_id_ios | BNEI0434_0029 | 30 |
| パックC Billing ID (Android) | com.bandainamcoent.jumble_0029 | MstStoreProduct | product_id_android | com.bandainamcoent.jumble_0029 | 30 |

#### 2.6.5 ストア商品価格 → MstStoreProductI18n

| 仕様書の項目 | 仕様書の値 | テーブル | price_ios | price_android | 行ID |
|---|---|---|---|---|---|
| パックA 割引価格 | ¥980 | MstStoreProductI18n | 980 | 980 | 28_ja |
| パックB 割引価格 | ¥2,980 | MstStoreProductI18n | 2980 | 2980 | 29_ja |
| パックC 割引価格 | ¥5,000 | MstStoreProductI18n | 5000 | 5000 | 30_ja |

#### 2.6.6 お得プリズム → MstStoreProduct / MstStoreProductI18n / OprProduct

| 仕様書プリズム個数 | 仕様書価格 | store_product_id | price_ios | paid_amount (OprProduct) | 行ID |
|---|---|---|---|---|---|
| 520個 | ¥980 | 31 | 980 | 520 | 31 |
| 1,680個 | ¥2,980 | 32 | 2980 | 1680 | 32 |
| 2,820個 | ¥4,980 | 33 | 4980 | 2820 | 33 |
| 5,580個 | ¥9,800 | 34 | 9800 | 5580 | 34 |

#### 2.6.7 商品販売設定 → OprProduct

| 仕様書の項目 | テーブル | product_type | purchasable_count | display_priority | start_date | end_date | 行ID |
|---|---|---|---|---|---|---|---|
| パックA | OprProduct | Pack | 1 | 23 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 28 |
| パックB | OprProduct | Pack | 1 | 22 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 29 |
| パックC | OprProduct | Pack | 1 | 21 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 30 |
| プリズム520個 | OprProduct | Diamond | 1 | 104 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 31 |
| プリズム1,680個 | OprProduct | Diamond | 1 | 103 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 32 |
| プリズム2,820個 | OprProduct | Diamond | 1 | 102 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 33 |
| プリズム5,580個 | OprProduct | Diamond | 1 | 101 | 2025-12-22 15:00:00 | 2025-12-31 23:59:59 | 34 |

---

### 2.7 レベルデザイン（仕様書外 / バトルパラメータ）

以下のテーブルは仕様書には直接記載されないが、新キャラ追加に伴いレベルデザイン部門で設定される。

#### MstAttack — 攻撃定義 (13行)

| 対象 | 行数 | 内容 |
|---|---|---|
| chara_yuw_00102 (通常攻撃) | 1行 | Normal_00000 |
| chara_yuw_00102 (必殺ワザLv1〜5) | 5行 | Special_00001〜00005 |
| 敵キャラ攻撃 (3体×通常+特殊) | 7行 | Boss/Normal敵の攻撃 |

#### MstAttackElement — 攻撃要素 (29行)

各攻撃の詳細パラメータ（ダメージ倍率、範囲、効果等）。

#### MstAttackI18n — 攻撃説明テキスト (13行)

必殺ワザの日本語説明文。

#### MstUnitAbility — ユニット特性 (3行)

| 行ID | 能力 |
|---|---|
| ability_yuw_00102_01 | ability_burn_damage_cut |
| ability_yuw_00102_02 | ability_StunBlock |
| ability_yuw_00102_03 | ability_slip_damage_koma_block |

#### MstSpecialAttackI18n — 必殺ワザ名 (1行)

| 行ID | 名前 |
|---|---|
| 70 | プレゼントは私だよ〜♡ |

#### MstSpeechBalloonI18n — セリフ (2行)

| 行ID | 条件 | テキスト |
|---|---|---|
| 210 | SpecialAttackCharge | せんぱ〜い♡ |
| 211 | Summon | 先輩の予定は？ |

---

### 2.8 PvPランクマッチ（仕様書での言及なし / 追加設定）

以下は仕様書の範囲外だが、同リリースキーで追加されたPvP設定。

#### MstPvp (2行)
- 2025052, 2026001: PvPシーズン設定

#### MstPvpI18n (2行)
- PvP説明テキスト

#### MstStageEndCondition (1行)
- pvp_Xmas_01: TimeOver 180秒

#### MstInGameSpecialRuleUnitStatus (1行)
- 全キャラHP 200%（3倍UP）

#### MstKomaLine (PvP用 3行 + イベント用 6行)
- ステージレイアウト設定

#### MstPage (3行)
- pvp_Xmas_01, event_yuw1_savage02_00001, event_yuw1_savage02_00002

---

### 2.9 交換所（定常 / 同リリースキーで管理）

#### MstExchange (2行)
- normal_01: コイン交換所
- chara_piece: キャラのかけらBOX交換所

#### MstExchangeLineup (10行)
- 交換可能アイテムラインナップ

#### MstExchangeCost (10行)
- 各アイテムのコインコスト

#### MstExchangeReward (10行)
- 交換で得られる報酬

#### MstExchangeI18n (2行)
- 交換所名称

---

### 2.10 アイテム遷移 → MstItemTransition (41行)

全キャラのかけらアイテムの遷移先設定（ExchangeShop）。定常更新として管理。

---

## 3. 仕様書ファイル分類マトリクス

### マスタデータに直接反映される仕様書ファイル

| 仕様書ファイル | マスタデータへの反映 | 重要度 |
|---|---|---|
| `01_概要.csv` | **高** — 新キャラ・新アイテム定義に必要 | 必須 |
| `02_施策.csv` | **最高** — クエスト・ステージ・報酬・バナー・ボーナスキャラの全設定 | 必須 |
| `06_クリスマスDXフェスガシャ_設計書.csv` | **最高** — ガシャ全テーブルの設定ソース | 必須 |
| `07_クリスマスパック_お得プリズム_設計書.csv` | **最高** — パック・ストア全テーブルの設定ソース | 必須 |

### マスタデータに間接的に関連する仕様書ファイル

| 仕様書ファイル | 関連性 | 備考 |
|---|---|---|
| `00_ロードマップ転記用.csv` | 施策一覧（概要レベル） | 日程確認用 |
| `03_降臨バトル.csv` | 降臨バトル報酬・ミッション定義 | このリリースキーではCSVに未反映（別リリースキーの可能性） |
| `04_ミッション.csv` | ミッション報酬定義 | このリリースキーではCSVに未反映 |
| `05_報酬一覧.csv` | 報酬サマリー（集計用） | マスタデータ設定後の検算用 |
| `06_ガシャ基本仕様.csv` / `06_ガシャ目次.csv` | ガシャの概要・目次 | 設計書のナビゲーション用 |
| `06_クリスマスガシャ_注意事項.csv` | ゲーム内告知テキスト | 告知系マスタデータ（別管理の可能性） |
| `07_ショップ_要件書.csv` | ショップ全体仕様 | 定常設定の参考資料 |
| `07_ショップ目次.csv` | ショップ目次 | ナビゲーション用 |
| `配布施策.csv` | 配布キャンペーン | ギフト系マスタデータ（別管理） |
| `バナー一覧.csv` | バナー画像仕様 | アセット管理用 |
| `アセット一覧.csv` | アセット画像仕様 | アセット管理用 |
| `クリエイティブ一覧.csv` | クリエイティブ制作依頼 | 制作管理用 |
| `クリスマス クリエイティブ依頼.csv` | クリエイティブ依頼詳細 | 制作管理用 |
| `バナー作成依頼.csv` | バナー作成依頼 | 制作管理用 |
| `企画仕様書_目次.csv` | 仕様書全体の目次 | ナビゲーション用 |
| `告知スケジュールNEO.csv` | 告知スケジュール | 告知管理用 |
| `景品単価の簡便な算定方法.csv` | 景品表法の算定参考 | 法務・コンプライアンス用 |
| `memo用リソース計算用シート.csv` | リソース計算 | 内部メモ用 |

---

## 4. マスタデータテーブル別 仕様書ソース一覧

### 運営仕様書から作成されるテーブル

| テーブル名 | 行数 | 主要ソース仕様書 | 自動作成の可否 |
|---|---|---|---|
| OprGacha | 1 | 06_ガシャ設計書 | 可能 |
| OprGachaI18n | 1 | 06_ガシャ設計書 | 可能 |
| OprGachaPrize | 65 | 06_ガシャ設計書（ラインナップ表） | 可能 |
| OprGachaUpper | 1 | 06_ガシャ設計書（天井設定） | 可能 |
| OprGachaUseResource | 3 | 06_ガシャ設計書（コスト設定） | 可能 |
| OprGachaDisplayUnitI18n | 1 | 06_ガシャ設計書（訴求キャラ） | 可能 |
| MstPack | 4 | 07_パック設計書 | 可能 |
| MstPackContent | 16 | 07_パック設計書（内容一覧） | 可能 |
| MstPackI18n | 4 | 07_パック設計書（名称） | 可能 |
| MstStoreProduct | 8 | 07_パック設計書（Billing ID） | 可能 |
| MstStoreProductI18n | 8 | 07_パック設計書（価格） | 可能 |
| OprProduct | 8 | 07_パック設計書（販売期間） | 可能 |
| OprProductI18n | 8 | 07_パック設計書 | 可能（asset_keyは部分的に手動） |
| MstQuest | 1 | 02_施策（高難易度クエスト） | 可能 |
| MstQuestI18n | 1 | 02_施策（クエスト名） | 可能 |
| MstStage | 2 | 02_施策（ステージ設定） | 可能 |
| MstStageI18n | 2 | 02_施策（ステージ名） | 可能 |
| MstStageEventReward | 8 | 02_施策（ステージ報酬） | 可能 |
| MstStageEventSetting | 2 | 02_施策（ステージ設定） | 可能 |
| MstStageClearTimeReward | 6 | 02_施策（クリアタイム報酬） | 可能 |
| MstHomeBanner | 3 | 02_施策（ホームバナー） | 可能 |
| MstQuestBonusUnit | 1 | 02_施策（コイン獲得クエスト） | 可能 |
| MstEventDisplayUnit | 2 | 02_施策（クエストTOP表示キャラ） | 可能 |
| MstEventDisplayUnitI18n | 2 | 02_施策（キャラセリフ） | 可能 |
| MstUnit | 1 | 01_概要（新キャラ） | 部分的に可能（パラメータはレベデザ） |
| MstUnitI18n | 1 | 01_概要 / 外部テキスト | 部分的に可能 |
| MstItem | 1 | 01_概要（新キャラのかけら） | 自動導出可能 |
| MstItemI18n | 1 | 01_概要 | 自動導出可能 |

### レベルデザイン部門で作成されるテーブル（仕様書外）

| テーブル名 | 行数 | 備考 |
|---|---|---|
| MstAttack | 13 | 攻撃モーション定義 |
| MstAttackElement | 29 | 攻撃詳細パラメータ |
| MstAttackI18n | 13 | 攻撃説明テキスト |
| MstUnitAbility | 3 | ユニット特性 |
| MstSpecialAttackI18n | 1 | 必殺ワザ名 |
| MstSpeechBalloonI18n | 2 | バトル中セリフ |
| MstEnemyOutpost | 2 | 敵拠点HP |
| MstEnemyStageParameter | 3 | 敵パラメータ |
| MstAutoPlayerSequence | (別管理) | 自動プレイ設定 |
| MstInGame | 3 | インゲーム基本設定 |
| MstInGameI18n | 3 | インゲームテキスト |
| MstInGameSpecialRule | 9 | 特別ルール |
| MstInGameSpecialRuleUnitStatus | 1 | ユニットステータス変更 |
| MstKomaLine | 9 | ステージレイアウト |
| MstPage | 3 | ページ定義 |
| MstStageEndCondition | 1 | ステージ終了条件 |

### 定常更新テーブル

| テーブル名 | 行数 | 備考 |
|---|---|---|
| MstExchange | 2 | コイン交換所（定常） |
| MstExchangeLineup | 10 | 交換ラインナップ（定常） |
| MstExchangeCost | 10 | 交換コスト（定常） |
| MstExchangeReward | 10 | 交換報酬（定常） |
| MstExchangeI18n | 2 | 交換所名称（定常） |
| MstItemTransition | 41 | アイテム遷移先（全キャラかけら） |
| MstPvp | 2 | PvPシーズン |
| MstPvpI18n | 2 | PvPテキスト |

---

## 5. 仕様書に記載があるがCSVに未反映の項目

以下の項目は仕様書に記載されているが、このリリースキーのCSVには反映されていない。

| 仕様書ファイル | 未反映の内容 | 推定理由 |
|---|---|---|
| `02_施策.csv` — ログインボーナス | 24日間のログインボーナス報酬スケジュール | ログインボーナス用テーブルが別リリースキーまたは別管理 |
| `02_施策.csv` — デイリークエスト | フェス限ガシャ デイリークエスト | 別リリースキーで管理 |
| `02_施策.csv` — ストーリークエスト（2つ） | 「スレイブの誕生」「隠れ里の戦い」各8話分 | 別リリースキーで管理（大量のステージデータ） |
| `02_施策.csv` — チャレンジクエスト | 4話分のチャレンジクエスト | 別リリースキーで管理 |
| `03_降臨バトル.csv` | 降臨バトル全体（報酬、ランク、ランキング、ミッション） | 降臨バトル専用テーブルまたは別リリースキー |
| `04_ミッション.csv` | いいジャン祭ミッション全体 | ミッション専用テーブルまたは別リリースキー |
| `配布施策.csv` | SNSキャンペーン配布、正月配布 | ギフト/お知らせ系テーブルで別管理 |

---

## 6. ID命名規則

自動生成時に参考となるID命名パターン:

| テーブル | ID形式 | 例 |
|---|---|---|
| OprGacha | `{種別}_{イベント}_{番号}` | Fest_Xmas_001 |
| OprGachaPrize | `{gacha_id}_{連番}` | Fest_Xmas_001_1 |
| OprGachaPrize (確定枠) | `fixd_{gacha_id}_{連番}` | fixd_Fest_Xmas_001_1 |
| MstQuest | `quest_event_{シリーズ}_{種別}` | quest_event_yuw1_savage02 |
| MstStage | `event_{シリーズ}_{種別}_{番号}` | event_yuw1_savage02_00001 |
| MstStageEventReward | 連番 | 385, 386, ... |
| MstPack | `{イベント}_item_pack_{番号}` | Xmas_item_pack_1 |
| MstPackContent | 連番 | 57, 58, ... |
| MstHomeBanner | 連番 | 20, 21, 22 |
| MstUnit | `chara_{シリーズ}_{番号}` | chara_yuw_00102 |
| MstItem (かけら) | `piece_{シリーズ}_{番号}` | piece_yuw_00102 |

---

## 7. アイテムID対応表

仕様書で使われるアイテム名とマスタデータCSVで使われるresource_idの対応:

| 仕様書のアイテム名 | resource_type | resource_id |
|---|---|---|
| プリズム | FreeDiamond | (resource_idなし) |
| コイン | Coin | (resource_idなし) |
| スペシャルガシャチケット | Item | ticket_glo_00002 |
| ピックアップガシャチケット | Item | ticket_glo_00003 |
| フェスガシャチケット | Item | ticket_glo_00004 |
| UR1体確定10連ガシャチケット | Item | ticket_glo_00203 |
| メモリーフラグメント・初級 | Item | memoryfragment_glo_00001 |
| メモリーフラグメント・中級 | Item | memoryfragment_glo_00002 |
| メモリーフラグメント・上級 | Item | memoryfragment_glo_00003 |
| カラーメモリー・グレー | Item | memory_glo_00001 |
| カラーメモリー・レッド | Item | memory_glo_00002 |
| カラーメモリー・グリーン | Item | memory_glo_00003 |
| カラーメモリー・ブルー | Item | memory_glo_00004 |
| カラーメモリー・イエロー | Item | memory_glo_00005 |
| 選べるURキャラのかけらBOX | Item | box_glo_00008 |
| ランクマッチチケット | Item | entry_item_glo_00001 |

---

## 8. 値変換ルール

自動生成時に必要な変換ルール:

| 仕様書の表現 | マスタデータの値 | 変換ルール |
|---|---|---|
| 緑 | Green | 色名→英語Enum |
| 赤 | Red | 色名→英語Enum |
| 黄 | Yellow | 色名→英語Enum |
| 青 | Blue | 色名→英語Enum |
| UR | UR | そのまま |
| SSR | SSR | そのまま |
| フェス限定ガシャ | Festival | 種別→Enum |
| ピックアップガシャ | Pickup | 種別→Enum |
| 12/22 15:00 | 2025-12-22 15:00:00 | 日付フォーマット変換 |
| 30% | 0.3 | パーセント→小数 |
| ¥980 | 980 | 通貨記号除去 |
| 300秒 | 300000 | 秒→ミリ秒 |
| 有 | (設定あり) | フラグ→対応データ行の生成 |
| 無 | `__NULL__` | フラグ→NULLまたは行なし |
| 1回 | 1 | テキスト→数値 |
