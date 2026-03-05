# 202601010（地獄楽 いいジャン祭）運営仕様書 ↔ マスタデータ マッピング調査

**対象リリースキー**: `202601010`
**施策名**: 地獄楽 いいジャン祭
**仕様書フォルダ**: `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書/`
**マスタデータフォルダ**: `domain/raw-data/masterdata/released/202601010/tables/`
**調査日**: 2026-03-05

---

## A. シート → マスタデータ マッピング表

| 仕様書シート | 生成されたマスタデータテーブル | 備考 |
|-------------|-------------------------------|------|
| `02_施策.csv` | MstEvent, MstEventI18n | 施策全体のイベント定義（event_jig_00001）。ログインボーナスのスケジュールも含む |
| `02_施策.csv` | MstMissionEventDailyBonus, MstMissionEventDailyBonusSchedule | ◆ログインボーナスセクション（日ごとの報酬・スケジュール） |
| `02_施策.csv` | MstMissionReward | ログインボーナス報酬の実体（17件）を定義 |
| `03_降臨バトル.csv` | MstAdventBattle, MstAdventBattleI18n | 降臨バトル「まるで 悪夢を見ているようだ」の基本定義。ID: `quest_raid_jig1_00001` |
| `03_降臨バトル.csv` | MstAdventBattleRank, MstAdventBattleReward, MstAdventBattleRewardGroup | ランク報酬・クリア報酬の詳細テーブル |
| `03_降臨バトル.csv` | MstAdventBattleClearReward | クリア報酬（ランダム報酬含む） |
| `03_降臨バトル.csv` | MstEventBonusUnit | ボーナス対象キャラと倍率（降臨バトル用ボーナスグループ: `raid_jig1_00001`） |
| `03_降臨バトル.csv` | MstQuestEventBonusSchedule | 降臨バトル期間中のボーナス適用スケジュール |
| `03_降臨バトル.csv` | MstEmblem, MstEmblemI18n | 降臨バトルランキング報酬エンブレム6種（1位・2位・3位・4-50位・51-300位・301-1000位）＋イベントエンブレム1種（神仙郷） |
| `04_ミッション.csv` | MstMissionEvent, MstMissionEventI18n | いいジャン祭特別ミッション43件（メイ強化・巌鉄斎強化・クエストクリア・敵撃破等） |
| `04_ミッション.csv` | MstMissionEventDependency | ミッション解放順序の定義 |
| `04_ミッション.csv` | MstMissionLimitedTerm, MstMissionLimitedTermI18n | 降臨バトル期間限定ミッション4件（5回・10回・20回・30回挑戦） |
| `04_ミッション.csv` | MstMissionReward | ミッション報酬実体（64件のうちログインボーナス以外の部分） |
| `02_施策.csv`（クエスト関連） | MstQuest, MstQuestI18n | 各クエスト定義: ストーリー2本・チャレンジ1本・高難易度1本・デイリー1本 |
| `02_施策.csv`（クエスト関連） | MstStage, MstStageI18n | 各クエストのステージ定義（20ステージ） |
| `02_施策.csv`（クエスト関連） | MstStageEventReward | ステージのイベント報酬（初回クリア・ランダムドロップ等）70件 |
| `02_施策.csv`（クエスト関連） | MstStageEventSetting | ステージごとのリセット設定・開催期間（20件） |
| `02_施策.csv`（クエスト関連） | MstStageClearTimeReward | チャレンジクエストのタイムアタック報酬（クリアタイム別報酬） |
| `02_施策.csv`（クエスト関連） | MstQuestBonusUnit | コイン獲得クエストのキャラボーナス設定 |
| `02_施策.csv` | MstEventDisplayUnit, MstEventDisplayUnitI18n | クエストTOP画面の表示キャラとセリフ（7件） |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGacha | ガシャA基本設定（Pickup_jig_001）：開催期間・天井・チケット設定等 |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGachaI18n | ガシャA名称・説明文（Pickup_jig_001_ja） |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGachaDisplayUnitI18n | ガシャA表示キャラ（賊王 亜左 弔兵衛・山田浅ェ門 桐馬）の訴求文言 |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGachaPrize | ガシャAの排出キャラ一覧と排出重み（ピックアップ設定含む） |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGachaUpper | ガシャA天井設定（100連） |
| `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv` | OprGachaUseResource | ガシャA使用リソース（プリズム150個/1500個・チケット） |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGacha | ガシャB基本設定（Pickup_jig_002） |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGachaI18n | ガシャB名称・説明文 |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGachaDisplayUnitI18n | ガシャB表示キャラ（がらんの画眉丸・山田浅ェ門 桐馬）の訴求文言 |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGachaPrize | ガシャBの排出キャラ一覧 |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGachaUpper | ガシャB天井設定 |
| `地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | OprGachaUseResource | ガシャB使用リソース |
| `07_いいジャン祭パック_設計書.csv` | MstPack, MstPackI18n | いいジャン祭パック（event_item_pack_12）の定義・名称 |
| `07_いいジャン祭パック_設計書.csv` | MstPackContent | パック内容物（メモリーフラグメント初・中・上級・ピックアップガシャチケット） |
| `07_いいジャン祭パック_設計書.csv` | MstStoreProduct, MstStoreProductI18n | iOS/Android製品ID・価格設定 |
| `07_いいジャン祭パック_設計書.csv` | OprProduct, OprProductI18n | パック販売期間・表示優先度 |
| `07_ショップ_要件書.csv`（差し込みプリズム部分） | OprProduct, OprProductI18n | お得プリズム販売設定（OprProduct id: 64, 65） |
| `07_差し込み用お得プリズム_設計書.csv` | MstStoreProduct, MstStoreProductI18n | 差し込み用お得プリズムの製品ID・価格 |
| `07_差し込み用お得プリズム_設計書.csv` | OprProduct, OprProductI18n | 差し込み用お得プリズムの販売期間設定 |
| `バナー一覧.csv` | MstHomeBanner | ホームバナー設定（イベント・ガシャA・ガシャB：計3件） |
| `アセット一覧.csv` / `地獄楽 クリエイティブ依頼.csv` | MstItem, MstItemI18n | キャラのかけら・メモリーアイテム定義（piece_jig_00401/00501/00601/00701、memory_chara_jig_00601/00701） |
| `アセット一覧.csv` | MstEmblem, MstEmblemI18n | エンブレムのアセットキー定義（降臨バトル6種＋イベント1種） |

---

## B. マスタデータ → 仕様書シート 逆引き表

| マスタデータテーブル | 対応する仕様書シート | 備考 |
|---------------------|---------------------|------|
| MstAbility, MstAbilityI18n | （仕様書に明示なし） | キャラ固有の特性（HP条件攻撃UP・被ダメカット）。ガシャ設計書のキャラ説明から間接的に導出 |
| MstAdventBattle, MstAdventBattleI18n | `03_降臨バトル.csv` | 降臨バトル基本定義 |
| MstAdventBattleClearReward | `03_降臨バトル.csv` | ▼報酬関連・◆クリア報酬セクション |
| MstAdventBattleRank, MstAdventBattleReward, MstAdventBattleRewardGroup | `03_降臨バトル.csv` | ▼報酬関連・◆ランキング報酬セクション |
| MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition | `02_施策.csv`（ストーリークエスト定義部分） | ストーリークエスト「必ず生きて帰る」「兄は弟の道標だ！！」の原画アートワーク定義。仕様書の明示的なシートは存在しない |
| MstAttack, MstAttackElement, MstAttackI18n | （仕様書に明示なし） | キャラの通常・必殺攻撃パラメータ。ガシャ設計書・キャラ説明から間接的に定義 |
| MstAutoPlayerSequence | `02_施策.csv`（各クエスト定義） | AI（オートプレイ）の敵配置シーケンス（185件）。仕様書の専用シートなし |
| MstEmblem, MstEmblemI18n | `03_降臨バトル.csv`、`地獄楽 クリエイティブ依頼.csv` | 降臨バトルエンブレム＋イベントエンブレム（神仙郷） |
| MstEnemyCharacter, MstEnemyCharacterI18n | （仕様書に明示なし） | 敵キャラ定義。仕様書の専用シートなし |
| MstEnemyOutpost, MstEnemyStageParameter | （仕様書に明示なし） | 敵拠点・ステージ出現パラメータ。仕様書の専用シートなし |
| MstEvent, MstEventI18n | `02_施策.csv`（▼全体概要セクション） | イベント全体定義（event_jig_00001） |
| MstEventBonusUnit | `03_降臨バトル.csv`（▼ボーナスセクション） | 降臨バトルボーナス対象キャラ |
| MstEventDisplayUnit, MstEventDisplayUnitI18n | `03_降臨バトル.csv`（クエストTOP表示キャラ設定）、`02_施策.csv` | 各クエストTOP画面の表示キャラとセリフ |
| MstHomeBanner | `バナー一覧.csv` | ホームバナー定義 |
| MstInGame, MstInGameI18n | `02_施策.csv`、`03_降臨バトル.csv` | インゲームパラメータ（BGM・ページ・拠点等）。仕様書の専用シートなし |
| MstInGameSpecialRule, MstInGameSpecialRuleUnitStatus | `02_施策.csv`（チャレンジクエスト部分）、PVP部分 | チャレンジのスピードアタック・コンティニュー禁止ルール等 |
| MstItem, MstItemI18n | `アセット一覧.csv`、`地獄楽 クリエイティブ依頼.csv` | キャラのかけら・メモリーアイテム |
| MstKomaLine | （仕様書に明示なし） | コマ配置定義（PVPステージ含む）。仕様書の専用シートなし |
| MstMangaAnimation | `02_施策.csv`（ストーリークエスト定義） | ストーリー演出アニメーション（マンガ風コマ送り）。仕様書の専用シートなし |
| MstMissionEvent, MstMissionEventI18n | `04_ミッション.csv` | いいジャン祭特別ミッション |
| MstMissionEventDailyBonus, MstMissionEventDailyBonusSchedule | `02_施策.csv`（◆ログインボーナスセクション） | ログインボーナス |
| MstMissionEventDependency | `04_ミッション.csv` | ミッション解放順序 |
| MstMissionLimitedTerm, MstMissionLimitedTermI18n | `04_ミッション.csv`（降臨バトル期間限定ミッション） | 降臨バトル期間限定ミッション |
| MstMissionReward | `02_施策.csv`（ログインボーナス報酬）、`04_ミッション.csv`（ミッション報酬）、`03_降臨バトル.csv`（降臨バトルミッション報酬） | 報酬実体（64件）は複数シートに定義が分散 |
| MstPack, MstPackI18n, MstPackContent | `07_いいジャン祭パック_設計書.csv` | いいジャン祭パック |
| MstPage | （仕様書に明示なし） | インゲームページ定義。各ステージに対応 |
| MstPvp, MstPvpI18n | （仕様書に明示なし） | PVPステージ2本（pvp_jig_01, pvp_jig_02）。仕様書の専用シートなし（地獄楽施策として初定義） |
| MstQuest, MstQuestI18n | `02_施策.csv`（▼全体概要の各クエスト行） | クエスト定義 |
| MstQuestBonusUnit | `02_施策.csv`（【コイン獲得クエスト】コイン獲得クエスト キャラボーナス） | コイン獲得クエストのキャラボーナス |
| MstQuestEventBonusSchedule | `03_降臨バトル.csv`（開催期間） | 降臨バトルのボーナス有効期間 |
| MstSpecialAttackI18n | （仕様書に明示なし） | キャラ必殺技名称。ガシャ設計書のキャラ訴求から間接的に定義 |
| MstSpecialRoleLevelUpAttackElement | `04_ミッション.csv`（メイのグレードアップミッション定義） | メイ（DropSR）のグレードアップごとの必殺技強化パラメータ |
| MstSpeechBalloonI18n | （仕様書に明示なし） | キャラの吹き出しセリフ。仕様書の専用シートなし |
| MstStage, MstStageI18n | `02_施策.csv`（各クエスト詳細） | ステージ定義（20ステージ） |
| MstStageClearTimeReward | `02_施策.csv`（チャレンジクエスト・高難易度クエスト定義） | タイムアタック報酬（チャレンジクエスト系） |
| MstStageEndCondition | `03_降臨バトル.csv`（時間制限90秒設定）、PVP設定 | ステージ終了条件 |
| MstStageEventReward | `02_施策.csv`（各クエストの報酬）、`05_報酬一覧.csv` | ステージ報酬（70件） |
| MstStageEventSetting | `02_施策.csv`（各クエストの開催期間） | ステージ開催設定（20件） |
| MstStoreProduct, MstStoreProductI18n | `07_いいジャン祭パック_設計書.csv`、`07_差し込み用お得プリズム_設計書.csv` | 製品ID・価格設定 |
| MstUnit, MstUnitI18n | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | 新キャラ4体（賊王 亜左 弔兵衛UR・山田浅ェ門 桐馬SSR・民谷 巌鉄斎SR・メイSR） |
| MstUnitAbility | （仕様書に明示なし） | キャラ固有能力パラメータ |
| MstUnitSpecificRankUp | `04_ミッション.csv`（メイ・巌鉄斎のグレードアップミッション） | DropSRキャラ（メイ・民谷 巌鉄斎）のグレードアップ設定 |
| OprGacha, OprGachaI18n | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv` | ガシャ基本設定（A: Pickup_jig_001、B: Pickup_jig_002） |
| OprGachaDisplayUnitI18n | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv`（ガシャバナー用訴求文言セクション） | キャラ訴求文言 |
| OprGachaPrize | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv`（ガシャマスター設定部分） | ガシャ排出キャラ・排出重み（150件：A/B合計） |
| OprGachaUpper | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv`（天井設定100回） | 天井設定 |
| OprGachaUseResource | `地獄楽 いいジャン祭ピックアップガシャA_設計書.csv`、`地獄楽 いいジャン祭ピックアップガシャB_設計書.csv`（プリズム・チケット設定） | 使用リソース |
| OprProduct, OprProductI18n | `07_いいジャン祭パック_設計書.csv`、`07_差し込み用お得プリズム_設計書.csv`（販売期間設定） | 製品販売設定 |

---

## C. 参照用シート一覧（マスタデータ生成なし）

以下のシートは、マスタデータの直接生成に使用されず、設計の参考・確認・運営作業管理のために存在する。

| シート名 | 用途 | 理由 |
|---------|------|------|
| `00_ロードマップ転記用.csv` | 施策一覧と仕様書リンクのまとめ | ロードマップ用の転記シート。マスタデータ定義情報なし |
| `01_概要.csv` | 施策全体の企画概要・コンセプト説明 | 施策の背景・コンセプト・主要コンテンツの説明のみ。具体的なデータ定義なし |
| `05_報酬一覧.csv` | 施策全体の報酬合計数の集計・バランス確認 | 合計数の計算シート。実際の報酬定義は `02_施策.csv`・`04_ミッション.csv` で行う |
| `06_ガシャ基本仕様.csv` | ガシャの確率・金額・ラインナップ一覧 | ガシャの共通仕様・参照用レート表。実データは各ガシャ設計書に記載 |
| `06_ガシャ目次.csv` | ガシャ設計書URLの一覧 | 目次・ナビゲーション用シート |
| `06_ピックアップガシャA_注意事項.csv` | ガシャA用アプリ内注意事項文言 | アプリに表示するテキスト文言のみ（OprGachaI18nの `display_gacha_caution_id` で参照される文言だが、CSVには直接定義されていない） |
| `06_ピックアップガシャB_注意事項.csv` | ガシャB用アプリ内注意事項文言 | 同上 |
| `07_ショップ目次.csv` | ショップ関連シートのURL一覧 | 目次・ナビゲーション用シート。商品定義は `いいジャン祭パック_設計書` 等に記載 |
| `07_ショップ_要件書.csv` | ショップタブ商品の全体要件まとめ | ショップ機能の要件定義・過去施策との比較。プリズム恒常販売等は別リリースキーで管理 |
| `アセット一覧.csv` | グラフィックアセットの仕様・命名規則まとめ | イラスト・UI班向けの制作依頼管理シート。アセット名はマスタデータの `asset_key` に対応するが、アセット自体はABで管理 |
| `バナー一覧.csv` | バナー画像の制作管理リスト | デザイン班向けのバナー制作・アップロード管理。ホームバナーのアセットキーはMstHomeBannerに反映 |
| `バナー作成依頼.csv` | バナー制作依頼の全体管理シート | デザイン班への依頼管理（通常・いいジャン祭・記念など施策をまたいだ一覧） |
| `企画仕様書_目次.csv` | 仕様書全タブのURL一覧 | ナビゲーション用目次シート |
| `告知スケジュールNEO.csv` | お知らせ・SNS告知スケジュール管理 | 告知タイミング・バナー管理。マスタデータ（MstHomeBanner等）の `start_at` と連動するが、シート自体はデザイン管理用 |
| `クリエイティブ一覧.csv` | グラフィック素材の制作依頼・進捗管理 | デザイン班向けの制作管理シート。アセットキー（item_icon, emblem_icon等）の命名確認に使用 |
| `地獄楽 クリエイティブ依頼.csv` | 地獄楽施策固有のクリエイティブ依頼まとめ | デザイン班への依頼管理（カラーメモリー・エンブレム・降臨バトル背景等）。アセット命名の参考 |
| `memo用リソース計算用シート.csv` | スタミナ・周回数・カラーメモリー配布量の計算メモ | 報酬バランス設計の計算補助シート。実データは `04_ミッション.csv` 等に反映済み |
| `かけらなどの計算 のコピー.csv` | キャラのかけら獲得数・グレードアップ必要数の計算シート | バランス設計の計算補助シート。MstUnitSpecificRankUp の設計根拠として使用 |
| `ストーリー8話→6話になった報酬設計.csv` | ストーリー話数変更に伴う報酬設計の変更履歴 | 過去バージョンとの比較・変更経緯メモ。最終的な報酬定義は `02_施策.csv` に反映済み |
| `景品単価の簡便な算定方法.csv` | 各アイテムの景品単価計算方法（景品表示法対応） | 法務・法令遵守用の計算参照シート。マスタデータには直接反映されない |

---

## D. 気づき・注意点

### D-1. 仕様書に記載があるがマスタデータ対応シートが不明なもの

- **PVP（対人戦）定義**: 仕様書に専用シートが存在しないが、MstPvp / MstPvpI18n / MstKomaLine / MstInGame / MstPage / MstStageEndCondition に地獄楽施策のPVPステージが2本（pvp_jig_01, pvp_jig_02）定義されている。どの仕様書から生成されたか不明。
- **ガシャ注意事項文言**: `06_ピックアップガシャA_注意事項.csv` と `06_ピックアップガシャB_注意事項.csv` のテキストは、OprGachaの `display_gacha_caution_id`（UUID形式）で参照されている。このUUIDが示す先のデータ（おそらくCMSや別システム管理）はCSVには存在しない。

### D-2. マスタデータにあるが仕様書の対応シートが不明なもの

- **MstAutoPlayerSequence（185件）**: 敵AIのシーケンス定義。仕様書に専用シートがなく、ステージ仕様から間接的に生成される。
- **MstEnemyCharacter / MstEnemyCharacterI18n / MstEnemyOutpost / MstEnemyStageParameter**: 敵キャラ・拠点の定義。仕様書に専用シートがない。
- **MstKomaLine（58件）**: コマ（マス目）の配置と効果定義。仕様書に専用シートがない。
- **MstMangaAnimation（24件）**: ストーリー演出マンガアニメーション定義。仕様書に専用シートがない。
- **MstSpeechBalloonI18n（8件）**: キャラの吹き出しセリフ（召喚時・必殺技チャージ時）。仕様書に専用シートがない。
- **MstArtwork / MstArtworkFragment / MstArtworkFragmentI18n / MstArtworkFragmentPosition**: ストーリークエストクリアで獲得できる原画アートワーク定義。仕様書に専用シートがない。
- **MstAbility / MstAbilityI18n / MstUnitAbility**: キャラの特性（アビリティ）定義。仕様書に専用シートがなく、ガシャ設計書のキャラ説明から間接的に設計される。

### D-3. 1つのシートが複数テーブルにまたがるケース

- **`02_施策.csv`** は最も複雑で、以下の複数テーブルの情報を1シートに集約している:
  - MstEvent / MstEventI18n（イベント定義）
  - MstQuest / MstQuestI18n（各クエスト定義）
  - MstStage / MstStageI18n / MstStageEventReward / MstStageEventSetting（ステージ定義・報酬）
  - MstMissionEventDailyBonus / MstMissionEventDailyBonusSchedule（ログインボーナス）
  - MstQuestBonusUnit（コイン獲得クエストのキャラボーナス）
  - MstEventDisplayUnit / MstEventDisplayUnitI18n（クエスト表示キャラ）

- **`03_降臨バトル.csv`** も複数テーブルを内包:
  - MstAdventBattle / MstAdventBattleI18n / MstAdventBattleClearReward / MstAdventBattleRank / MstAdventBattleReward / MstAdventBattleRewardGroup
  - MstEventBonusUnit（ボーナス対象キャラ）
  - MstQuestEventBonusSchedule（ボーナス有効期間）
  - MstEmblem / MstEmblemI18n（降臨バトルランキング報酬エンブレム）
  - MstStageClearTimeReward（一部）

- **`04_ミッション.csv`** が関係するテーブル:
  - MstMissionEvent / MstMissionEventI18n / MstMissionEventDependency
  - MstMissionLimitedTerm / MstMissionLimitedTermI18n
  - MstMissionReward（ミッション報酬部分）
  - MstUnitSpecificRankUp（メイ・巌鉄斎のグレードアップ設定は実質このシートから読み取れる）

### D-4. 複数シートが1つのテーブルに集約されているケース

- **OprProduct / OprProductI18n**: いいジャン祭パック（`07_いいジャン祭パック_設計書.csv`）と差し込み用お得プリズム（`07_差し込み用お得プリズム_設計書.csv`）の両方が集約されている。
- **MstStoreProduct / MstStoreProductI18n**: 同様に複数のパック設計書から集約。
- **MstMissionReward（64件）**: `02_施策.csv`（ログインボーナス17件）、`04_ミッション.csv`（ミッション報酬）、`03_降臨バトル.csv`（降臨バトル期間限定ミッション報酬）の3シートの報酬定義が1テーブルに集約されている。
- **MstHomeBanner**: イベントバナー・ガシャAバナー・ガシャBバナーの3件が集約。それぞれ `バナー一覧.csv`（イベント・ガシャ）と各ガシャ設計書（ホームバナーID）に定義されている。

### D-5. ID命名規則の観察

- 施策識別子として `jig_` プレフィックスが全テーブルに一貫して使用されている
- 降臨バトル固有では `raid_jig1_` プレフィックスが使用されている
- ピックアップガシャは `Pickup_jig_001`（A）/ `Pickup_jig_002`（B）

### D-6. 月次パック（monthly_item_pack_3）について

- MstPack・MstPackI18n に `monthly_item_pack_3`（お得強化パック）が1件含まれているが、対応する設計書シートが見当たらない。`07_ショップ目次.csv` の「価格調整・設定」セクションに「お得強化パックのスケジュールを変更して再販売」という記述があり、既存パックのスケジュール変更のみのため、専用設計書シートが作成されなかった可能性がある。

---

## E. テーブル別レコード数サマリ（release_key=202601010）

| テーブル | レコード数 | 説明 |
|---------|-----------|------|
| MstAbility | 2 | キャラ特性定義 |
| MstAbilityI18n | 2 | 特性名称 |
| MstAdventBattle | 1 | 降臨バトル定義 |
| MstAdventBattleClearReward | 5 | クリア報酬 |
| MstAdventBattleI18n | 1 | 降臨バトル名称 |
| MstAdventBattleRank | 16 | ランクボーダー |
| MstAdventBattleReward | 120 | ランキング報酬実体 |
| MstAdventBattleRewardGroup | 55 | ランキング報酬グループ |
| MstArtwork | 2 | 原画アートワーク |
| MstArtworkFragment | 32 | 原画のかけら |
| MstArtworkFragmentI18n | 32 | 原画のかけら名称 |
| MstArtworkFragmentPosition | 32 | 原画配置位置 |
| MstArtworkI18n | 2 | 原画タイトル・説明 |
| MstAttack | 117 | 攻撃定義 |
| MstAttackElement | 152 | 攻撃要素 |
| MstAttackI18n | 117 | 攻撃説明文 |
| MstAutoPlayerSequence | 185 | AI敵シーケンス |
| MstEmblem | 7 | エンブレム定義 |
| MstEmblemI18n | 7 | エンブレム名称・説明 |
| MstEnemyCharacter | 5 | 敵キャラ定義 |
| MstEnemyCharacterI18n | 5 | 敵キャラ名称 |
| MstEnemyOutpost | 21 | 敵拠点定義 |
| MstEnemyStageParameter | 50 | 敵ステージパラメータ |
| MstEvent | 1 | イベント定義 |
| MstEventBonusUnit | 7 | ボーナスキャラ |
| MstEventDisplayUnit | 7 | クエスト表示キャラ |
| MstEventDisplayUnitI18n | 7 | クエスト表示キャラセリフ |
| MstEventI18n | 1 | イベント名称 |
| MstHomeBanner | 3 | ホームバナー |
| MstInGame | 23 | インゲーム設定 |
| MstInGameI18n | 23 | インゲーム説明 |
| MstInGameSpecialRule | 22 | インゲーム特別ルール |
| MstInGameSpecialRuleUnitStatus | 1 | PVP特別ルールステータス |
| MstItem | 6 | アイテム定義（かけら・メモリー） |
| MstItemI18n | 6 | アイテム名称 |
| MstKomaLine | 58 | コマ配置定義 |
| MstMangaAnimation | 24 | マンガアニメーション |
| MstMissionEvent | 43 | イベントミッション |
| MstMissionEventDailyBonus | 17 | ログインボーナス |
| MstMissionEventDailyBonusSchedule | 1 | ログインボーナススケジュール |
| MstMissionEventDependency | 39 | ミッション解放順序 |
| MstMissionEventI18n | 43 | ミッション説明文 |
| MstMissionLimitedTerm | 4 | 降臨バトル期間限定ミッション |
| MstMissionLimitedTermI18n | 4 | 期間限定ミッション説明文 |
| MstMissionReward | 64 | 報酬実体（全ミッション） |
| MstPack | 2 | パック定義 |
| MstPackContent | 7 | パック内容物 |
| MstPackI18n | 2 | パック名称 |
| MstPage | 23 | インゲームページ |
| MstPvp | 2 | PVP設定 |
| MstPvpI18n | 2 | PVP説明文 |
| MstQuest | 5 | クエスト定義 |
| MstQuestBonusUnit | 8 | コイン獲得クエストボーナス |
| MstQuestEventBonusSchedule | 1 | 降臨バトルボーナス期間 |
| MstQuestI18n | 5 | クエスト名称 |
| MstSpecialAttackI18n | 4 | 必殺技名称 |
| MstSpecialRoleLevelUpAttackElement | 5 | DropSRグレードアップ攻撃強化 |
| MstSpeechBalloonI18n | 8 | キャラ吹き出しセリフ |
| MstStage | 20 | ステージ定義 |
| MstStageClearTimeReward | 21 | タイムアタック報酬 |
| MstStageEndCondition | 3 | ステージ終了条件 |
| MstStageEventReward | 70 | ステージイベント報酬 |
| MstStageEventSetting | 20 | ステージ開催設定 |
| MstStageI18n | 20 | ステージ名称 |
| MstStoreProduct | 7 | ストア製品定義 |
| MstStoreProductI18n | 7 | ストア製品価格 |
| MstUnit | 4 | ユニット定義（新キャラ4体） |
| MstUnitAbility | 4 | ユニット特性パラメータ |
| MstUnitI18n | 4 | ユニット名称・説明 |
| MstUnitSpecificRankUp | 12 | DropSRグレードアップ設定 |
| OprGacha | 2 | ガシャ基本設定（A・B） |
| OprGachaDisplayUnitI18n | 4 | ガシャ表示キャラ訴求文言 |
| OprGachaI18n | 2 | ガシャ名称・説明 |
| OprGachaPrize | 150 | ガシャ排出テーブル |
| OprGachaUpper | 2 | ガシャ天井設定 |
| OprGachaUseResource | 6 | ガシャ使用リソース |
| OprProduct | 7 | 製品販売設定 |
| OprProductI18n | 7 | 製品表示設定 |
