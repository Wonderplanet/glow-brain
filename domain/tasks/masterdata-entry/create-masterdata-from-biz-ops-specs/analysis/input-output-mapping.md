# インプット→アウトプットのマッピング分析

## 概要
- **設計書総数**: 15件
- **マスタデータテーブル総数**: 79個
- **データが設定されているテーブル数**: 79個（全テーブルにデータあり）
- **マッピング完了率**: 100%

---

## 設計書別マッピング

### 1. GLOW_ID管理
- **カテゴリ**: システム基盤・ID管理
- **対応テーブル数**: 79個（全テーブルに影響）
- **対応テーブル**:
  - **全テーブル共通**: ID体系の基準となる
  - **特に重要な対応**:
    - MstEvent（event_jig_00001）
    - MstUnit（chara_jig_00401, 00501, 00601, 00701）
    - MstQuest（quest_event_jig1_*）
    - OprGacha（Pickup_jig_001, 002）
    - MstArtwork（artwork_event_jig_0001, 0002）
    - MstEmblem（emblem_event_jig_00001~00007）
    - MstItem（memory_chara_jig_*, piece_jig_*）
- **マッピングパターン**: 多対1（全テーブル→1設計書）
- **特記事項**:
  - プレフィックス「jig」（地獄楽）の統一的な使用
  - 37種類のCSVシートで全IDを一元管理
  - いいジャン祭ID（event_jig_00001）の定義
  - キャラID、アイテムID、クエストID、ガシャバナー、背景、BGM等の全ID定義

---

### 2. GLOW_キャラ名称＆キャラセリフ資料
- **カテゴリ**: 監修・テキスト管理
- **対応テーブル数**: 13個
- **対応テーブル**:
  - MstUnitI18n（キャラ名、フレーバーテキスト）
  - MstSpecialAttackI18n（必殺技名）
  - MstSpeechBalloonI18n（バトル中セリフ）
  - MstEventDisplayUnitI18n（吹き出しセリフ）
  - MstEnemyCharacterI18n（敵キャラ名）
  - MstQuestI18n（クエスト名、フレーバーテキスト）
  - MstStageI18n（ステージ名）
  - MstEventI18n（イベント名、バナーテキスト）
  - MstAdventBattleI18n（降臨バトル名、ボス説明）
  - MstArtworkI18n（原画タイトル、説明）
  - MstArtworkFragmentI18n（原画欠片名）
  - MstEmblemI18n（エンブレム名、説明）
  - MstInGameI18n（リザルトTips）
- **マッピングパターン**: 1対多（1設計書→複数I18nテーブル）
- **特記事項**:
  - 全てのテキスト系カラム（name, description, flavor_text等）の監修資料
  - キャラ名最大16文字、フレーバーテキスト最大196文字（28文字×7行）
  - 監修ステータス管理（未監修/監修通過）

---

### 3. 【1日1回】本能が告げている 危険だと
- **カテゴリ**: イベントクエスト（デイリー）
- **対応テーブル数**: 25個
- **対応テーブル**:
  - **クエスト基本**:
    - MstQuest（quest_event_jig1_1day）
    - MstQuestI18n
  - **ステージ関連**:
    - MstStage（event_jig1_1day_00001~00007: 7ステージ）
    - MstStageI18n
    - MstStageEventReward（初回クリア、ランダム報酬）
    - MstStageEventSetting（Daily reset設定）
    - MstStageClearTimeReward（タイムアタック報酬）
    - MstStageEndCondition（敵全滅条件）
  - **インゲーム設定**:
    - MstInGame（event_jig1_1day_00001~00007）
    - MstInGameI18n（リザルトTips）
  - **ページ・コマライン**:
    - MstPage（event_jig1_1day_00001~00007）
    - MstKomaLine（コマ配置とエフェクト設定）
  - **敵キャラ・オートプレイ**:
    - MstEnemyCharacter（敵バージョン）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter（ステージ別敵パラメータ）
    - MstEnemyOutpost（敵拠点）
    - MstAutoPlayerSequence（敵の自動行動パターン）
  - **演出**:
    - MstMangaAnimation（漫画演出）
  - **特効・ボーナス**:
    - MstQuestBonusUnit（特効キャラ）
- **マッピングパターン**: 1対多（1クエスト設計書→複数テーブル）
- **特記事項**:
  - スタミナ: 1
  - リセット: Daily（1日1回）
  - 開催期間: 2026-01-16 15:00 ~ 2026-02-02 03:59
  - 7ステージ構成
  - 約20種類のCSVシートから生成

---

### 4. 【ストーリー】必ず生きて帰る（画眉丸編）
- **カテゴリ**: イベントクエスト（ストーリー・画眉丸編）
- **対応テーブル数**: 26個
- **対応テーブル**:
  - **クエスト基本**:
    - MstQuest（quest_event_jig1_charaget01）
    - MstQuestI18n
  - **ステージ関連**:
    - MstStage（複数ステージ）
    - MstStageI18n
    - MstStageEventReward（キャラ入手: メイ、メモリー、欠片など）
    - MstStageEventSetting（リセットなし）
    - MstStageClearTimeReward
    - MstStageEndCondition
  - **インゲーム設定**:
    - MstInGame
    - MstInGameI18n
  - **ページ・コマライン**:
    - MstPage
    - MstKomaLine
  - **敵キャラ**:
    - MstEnemyCharacter
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
    - MstEnemyOutpost
    - MstAutoPlayerSequence
  - **原画**:
    - MstArtwork（artwork_event_jig_0001）
    - MstArtworkI18n
    - MstArtworkFragment（16個の欠片）
    - MstArtworkFragmentI18n
    - MstArtworkFragmentPosition
  - **演出**:
    - MstMangaAnimation（開始・終了時の原画演出）
  - **アイテム**:
    - MstItem（memory_chara_jig_00701, piece_jig_00701）
    - MstItemI18n
  - **特効**:
    - MstQuestBonusUnit
- **マッピングパターン**: 1対多（1クエスト設計書→複数テーブル）
- **特記事項**:
  - 開催期間: 2026-01-16 15:00 ~ 2026-02-16 10:59
  - メイを初回報酬で入手可能
  - 原画演出設定（開始時・終了時）
  - 背景ID: koma_background_jig_00001, 00002

---

### 5. 【ストーリー】朱印の者たち（共闘関係編）
- **カテゴリ**: イベントクエスト（ストーリー・共闘関係編）
- **対応テーブル数**: 26個
- **対応テーブル**:
  - **クエスト基本**:
    - MstQuest（quest_event_jig1_charaget02）
    - MstQuestI18n
  - **ステージ関連**:
    - MstStage
    - MstStageI18n
    - MstStageEventReward（キャラ入手: 民谷 巌鉄斎、メモリー、欠片など）
    - MstStageEventSetting
    - MstStageClearTimeReward
    - MstStageEndCondition
  - **インゲーム設定**:
    - MstInGame
    - MstInGameI18n
  - **ページ・コマライン**:
    - MstPage
    - MstKomaLine
  - **敵キャラ**:
    - MstEnemyCharacter
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
    - MstEnemyOutpost
    - MstAutoPlayerSequence
  - **原画**:
    - MstArtwork（artwork_event_jig_0002）
    - MstArtworkI18n
    - MstArtworkFragment（16個の欠片）
    - MstArtworkFragmentI18n
    - MstArtworkFragmentPosition
  - **演出**:
    - MstMangaAnimation
  - **アイテム**:
    - MstItem（memory_chara_jig_00601, piece_jig_00601）
    - MstItemI18n
  - **特効**:
    - MstQuestBonusUnit
- **マッピングパターン**: 1対多（1クエスト設計書→複数テーブル）
- **特記事項**:
  - 開催期間: 2026-01-21 15:00 ~ 2026-02-16 10:59（5日遅れて開始）
  - 民谷 巌鉄斎を初回報酬で入手可能
  - 原画演出設定

---

### 6. 【チャレンジ】死罪人と首切り役人
- **カテゴリ**: イベントクエスト（チャレンジ）
- **対応テーブル数**: 25個
- **対応テーブル**:
  - **クエスト基本**:
    - MstQuest（quest_event_jig1_challenge01）
    - MstQuestI18n
  - **ステージ関連**:
    - MstStage（複数ステージ: event_jig1_challenge01_00001~00005）
    - MstStageI18n
    - MstStageEventReward（カラーメモリー、メモリーフラグメント、コイン）
    - MstStageEventSetting
    - MstStageClearTimeReward
    - MstStageEndCondition
  - **インゲーム設定**:
    - MstInGame
    - MstInGameI18n
  - **ページ・コマライン**:
    - MstPage
    - MstKomaLine
  - **敵キャラ**:
    - MstEnemyCharacter
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
    - MstEnemyOutpost
    - MstAutoPlayerSequence
  - **演出**:
    - MstMangaAnimation
  - **特効**:
    - MstQuestBonusUnit
- **マッピングパターン**: 1対多（1クエスト設計書→複数テーブル）
- **特記事項**:
  - 開催期間: 2026-01-16 15:00 ~ 2026-02-16 10:59
  - 制約条件設定
  - やり込みユーザー向け

---

### 7. 【高難度】手負いの獣は恐ろしいぞ
- **カテゴリ**: イベントクエスト（高難度）
- **対応テーブル数**: 25個
- **対応テーブル**:
  - **クエスト基本**:
    - MstQuest（quest_event_jig1_savage）
    - MstQuestI18n
  - **ステージ関連**:
    - MstStage
    - MstStageI18n
    - MstStageEventReward（高レアリティアイテム）
    - MstStageEventSetting
    - MstStageClearTimeReward
    - MstStageEndCondition
  - **インゲーム設定**:
    - MstInGame
    - MstInGameI18n
  - **ページ・コマライン**:
    - MstPage
    - MstKomaLine
  - **敵キャラ**:
    - MstEnemyCharacter
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter（強敵）
    - MstEnemyOutpost
    - MstAutoPlayerSequence
  - **演出**:
    - MstMangaAnimation
  - **特効**:
    - MstQuestBonusUnit
  - **特別ルール**:
    - MstInGameSpecialRule（高難度用の特殊設定）
- **マッピングパターン**: 1対多（1クエスト設計書→複数テーブル）
- **特記事項**:
  - 開催期間: 2026-01-16 15:00 ~ 2026-02-16 10:59
  - 高難度ステージ設計
  - 強敵エネミー出現

---

### 8. 【降臨バトル】まるで 悪夢を見ているようだ
- **カテゴリ**: イベントクエスト（降臨バトル・ランキング）
- **対応テーブル数**: 27個
- **対応テーブル**:
  - **降臨バトル基本**:
    - MstAdventBattle（quest_raid_jig1_00001）
    - MstAdventBattleI18n
    - MstAdventBattleClearReward（ランダム報酬）
    - MstAdventBattleRank（Bronze/Silver/Gold/Platinum: 各4レベル）
    - MstAdventBattleReward（報酬詳細: 120件）
    - MstAdventBattleRewardGroup（報酬カテゴリ: MaxScore/TotalScore/Rank/RankUp/FirstClear）
  - **インゲーム設定**:
    - MstInGame（raid_jig1_00001）
    - MstInGameI18n
    - MstStageEndCondition（120秒タイムオーバー）
  - **ページ・コマライン**:
    - MstPage
    - MstKomaLine
  - **敵キャラ**:
    - MstEnemyCharacter（ボスエネミー）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter（enemy_jig_00601_boss等）
    - MstEnemyOutpost
    - MstAutoPlayerSequence
  - **特効**:
    - MstEventBonusUnit（特効グループ: raid_jig1_00001）
    - MstQuestEventBonusSchedule（特効スケジュール）
  - **ミッション**:
    - MstMissionLimitedTerm（期間限定ミッション: 降臨バトル5回挑戦など）
    - MstMissionLimitedTermI18n
    - MstMissionReward
- **マッピングパターン**: 1対多（1設計書→複数テーブル）
- **特記事項**:
  - 開催期間: 2026-01-23 15:00 ~ 2026-01-29 14:59（7日間限定）
  - スコアチャレンジ型レイド
  - 初期BP: 500、スコア加算係数: 0.07
  - ランキング報酬設定
  - BGM: SSE_SBG_003_007（高難度BGM）
  - 背景: jig_00001（神仙郷）

---

### 9. ヒーロー基礎設計_chara_jig_00401_賊王 亜左 弔兵衛
- **カテゴリ**: ヒーロー設計（UR・ガチャ）
- **対応テーブル数**: 19個
- **対応テーブル**:
  - **ユニット基本**:
    - MstUnit（chara_jig_00401: UR, Technical, Colorless）
    - MstUnitI18n
  - **アビリティ**:
    - MstAbility（ability_damage_cut_by_hp_percentage_over, ability_attack_power_up_by_hp_percentage_less）
    - MstAbilityI18n
    - MstUnitAbility（ability_jig_00401_01: ダメージカット40%、ability_jig_00401_02: 体力吸収50%）
  - **攻撃アクション**:
    - MstAttack（chara_jig_00401_Normal_*, chara_jig_00401_Special_*: グレード0~9）
    - MstAttackElement（攻撃詳細: ダメージ計算、エフェクト、範囲）
    - MstAttackI18n
    - MstSpecialAttackI18n（必殺技名: 「強イモ弱イモ ゼンブダイジ」）
  - **セリフ**:
    - MstSpeechBalloonI18n（バトル中吹き出しセリフ）
  - **敵バージョン**:
    - MstEnemyCharacter（chara_jig_00401: 敵バージョン）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter（ステージ別敵パラメータ）
  - **アイテム**:
    - MstItem（piece_jig_00401: 欠片）
    - MstItemI18n
  - **ガチャ**:
    - OprGachaPrize（ガチャ排出設定: ピックアップフラグ）
  - **表示**:
    - MstEventDisplayUnit（クエスト画面表示キャラ）
    - MstEventDisplayUnitI18n（吹き出しセリフ）
    - OprGachaDisplayUnitI18n（ガチャ画面表示）
- **マッピングパターン**: 1対多（1キャラ設計書→複数テーブル）
- **特記事項**:
  - キャラID: chara_jig_00401
  - ラベル: PremiumUR
  - ロール: テクニカル
  - 属性: 無（Colorless）
  - 召喚コスト: 1000（高コスト）
  - 基礎HP: 2100、基礎ATK: 2500
  - 特性: HP70%以上で被ダメ40%カット、HP69%以下で攻撃35%UP
  - 必殺ワザ1: 弱体化（被ダメージ1.5倍上昇）
  - 必殺ワザ2: 体力吸収
  - 実装日: 2026-01-16 15:00
  - ガシャA対象

---

### 10. ヒーロー基礎設計_chara_jig_00501_山田浅ェ門 桐馬
- **カテゴリ**: ヒーロー設計（SSR・ガチャ）
- **対応テーブル数**: 19個
- **対応テーブル**:
  - **ユニット基本**:
    - MstUnit（chara_jig_00501: SSR, Support, Green）
    - MstUnitI18n
  - **アビリティ**:
    - MstAbility
    - MstAbilityI18n
    - MstUnitAbility（攻撃UP、ダメージカット系）
  - **攻撃アクション**:
    - MstAttack（chara_jig_00501_Normal_*, chara_jig_00501_Special_*）
    - MstAttackElement
    - MstAttackI18n
    - MstSpecialAttackI18n
  - **セリフ**:
    - MstSpeechBalloonI18n
  - **敵バージョン**:
    - MstEnemyCharacter（chara_jig_00501）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
  - **アイテム**:
    - MstItem（piece_jig_00501: 欠片）
    - MstItemI18n
  - **ガチャ**:
    - OprGachaPrize（ピックアップ）
  - **表示**:
    - MstEventDisplayUnit
    - MstEventDisplayUnitI18n
    - OprGachaDisplayUnitI18n
- **マッピングパターン**: 1対多（1キャラ設計書→複数テーブル）
- **特記事項**:
  - キャラID: chara_jig_00501
  - ラベル: SSR
  - ロール: サポート
  - 実装日: 2026-01-16 15:00
  - ガシャA対象

---

### 11. ヒーロー基礎設計_chara_jig_00601_民谷 巌鉄斎
- **カテゴリ**: ヒーロー設計（SR・イベント配布）
- **対応テーブル数**: 20個
- **対応テーブル**:
  - **ユニット基本**:
    - MstUnit（chara_jig_00601: SR, Defense, Blue）
    - MstUnitI18n
  - **アビリティ**:
    - MstAbility
    - MstAbilityI18n
    - MstUnitAbility（ability_jig_00601_01: スピードUP 50%）
  - **攻撃アクション**:
    - MstAttack（chara_jig_00601_Normal_*, chara_jig_00601_Special_*）
    - MstAttackElement
    - MstAttackI18n
    - MstSpecialAttackI18n
  - **ランクアップ**:
    - MstUnitSpecificRankUp（ランクアップ素材設定）
  - **セリフ**:
    - MstSpeechBalloonI18n
  - **敵バージョン**:
    - MstEnemyCharacter（chara_jig_00601、enemy_jig_00601_boss）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
  - **アイテム**:
    - MstItem（memory_chara_jig_00601, piece_jig_00601）
    - MstItemI18n
  - **表示**:
    - MstEventDisplayUnit
    - MstEventDisplayUnitI18n
- **マッピングパターン**: 1対多（1キャラ設計書→複数テーブル）
- **特記事項**:
  - キャラID: chara_jig_00601
  - ラベル: SR
  - ロール: Defense
  - 入手方法: イベント配布（ストーリークエスト「朱印の者たち」報酬）
  - イベント限定キャラ
  - 最大グレード強化可能

---

### 12. ヒーロー基礎設計_chara_jig_00701_メイ
- **カテゴリ**: ヒーロー設計（SR・イベント配布）
- **対応テーブル数**: 20個
- **対応テーブル**:
  - **ユニット基本**:
    - MstUnit（chara_jig_00701: SR, Special, Colorless）
    - MstUnitI18n
  - **アビリティ**:
    - MstAbility
    - MstAbilityI18n
    - MstUnitAbility（ability_jig_00701_01: HP50%以下時に攻撃40%UP）
  - **攻撃アクション**:
    - MstAttack（chara_jig_00701_Normal_*, chara_jig_00701_Special_*）
    - MstAttackElement
    - MstAttackI18n
    - MstSpecialAttackI18n
    - MstSpecialRoleLevelUpAttackElement（SpecialRoleタイプの成長パラメータ）
  - **ランクアップ**:
    - MstUnitSpecificRankUp
  - **セリフ**:
    - MstSpeechBalloonI18n
  - **敵バージョン**:
    - MstEnemyCharacter（chara_jig_00701）
    - MstEnemyCharacterI18n
    - MstEnemyStageParameter
  - **アイテム**:
    - MstItem（memory_chara_jig_00701, piece_jig_00701）
    - MstItemI18n
  - **表示**:
    - MstEventDisplayUnit
    - MstEventDisplayUnitI18n
- **マッピングパターン**: 1対多（1キャラ設計書→複数テーブル）
- **特記事項**:
  - キャラID: chara_jig_00701
  - ラベル: SR
  - ロール: Special
  - 属性: Colorless
  - 入手方法: イベント配布（ストーリークエスト「必ず生きて帰る」報酬）
  - イベント限定キャラ
  - ステージサムネイルにも登場

---

### 13. GLOW_ランクマッチ開催仕様書
- **カテゴリ**: ランクマッチ（PVP・定常コンテンツ）
- **対応テーブル数**: 14個
- **対応テーブル**:
  - **PVP基本**:
    - MstPvp（2026004, 2026005: 地獄楽1ランクマ、地獄楽2ランクマ）
    - MstPvpI18n（ルール説明: 3段ステージ、突風コマ、特別ルール等）
  - **インゲーム設定**:
    - MstInGame（pvp_jig_01, pvp_jig_02）
    - MstInGameI18n
    - MstInGameSpecialRule（期間限定特殊ルール）
    - MstInGameSpecialRuleUnitStatus（全キャラHP200%UP等）
  - **ページ・コマライン**:
    - MstPage（pvp_jig_01, pvp_jig_02）
    - MstKomaLine（突風コマ配置）
  - **敵キャラ**:
    - MstAutoPlayerSequence（PVP用オートプレイ設定）
  - （注: ランクマッチ報酬やダミーユーザーは設計書に記載があるが、今回のマスタデータCSVには含まれていない可能性）
- **マッピングパターン**: 1対多（1設計書→複数テーブル）
- **特記事項**:
  - 地獄楽2ランクマが今回のいいジャン祭に合わせて開催予定
  - シーズン開催期間: 2026-01-26 ~ （想定）
  - 特別ルール: リーダーP1000スタート、全キャラ体力3倍UP
  - BGM: SSE_SBG_003_007
  - 背景: jig_00001（神仙郷）
  - 参加最低ランク: ブロンズ
  - 1日の挑戦上限: フリー10回、ランクマッチチケット10回

---

### 14. 20260116_地獄楽 いいジャン祭_仕様書（運営仕様書）
- **カテゴリ**: 運営施策・統合仕様書（マスター）
- **対応テーブル数**: 60個以上（ほぼ全テーブルに影響）
- **対応テーブル**:
  - **イベント基本**:
    - MstEvent
    - MstEventI18n
    - MstHomeBanner
  - **ガチャ**:
    - OprGacha（Pickup_jig_001, Pickup_jig_002）
    - OprGachaI18n
    - OprGachaPrize（排出内容とピックアップ）
    - OprGachaUpper（天井: 100回）
    - OprGachaUseResource（コスト: 単発150、10連1500）
    - OprGachaDisplayUnitI18n
  - **ミッション**:
    - MstMissionEvent（43個: キャラ強化、ステージクリア、ガチャ実行等）
    - MstMissionEventI18n
    - MstMissionEventDependency（解放順序）
    - MstMissionReward（ダイヤ、ガチャチケット、メモリー、コイン等）
  - **ログインボーナス**:
    - MstMissionEventDailyBonus（17日間分）
    - MstMissionEventDailyBonusSchedule（2026-01-16 ~ 2026-02-02）
  - **ショップ・パック**:
    - MstStoreProduct（IAP商品ID: iOS/Android/Web）
    - MstStoreProductI18n（価格情報: 3000円など）
    - OprProduct（期間限定商品: いいジャン祭パック等）
    - OprProductI18n
    - MstPack（event_item_pack_12, 13）
    - MstPackI18n
    - MstPackContent（パック内容物）
  - **エンブレム**:
    - MstEmblem（emblem_event_jig_00001~00007）
    - MstEmblemI18n
  - **アイテム**:
    - MstItem（チケット: ticket_glo_00001, ticket_glo_00003等）
    - MstItemI18n
  - **クエスト設定**:
    - MstQuest（5クエストの開催期間、難易度）
    - MstQuestI18n
  - **ステージ報酬**:
    - MstStageEventReward（初回クリア報酬、ランダム報酬）
    - MstStageClearTimeReward
  - **降臨バトル**:
    - MstAdventBattle（開催期間、スコア設定）
    - MstAdventBattleI18n
    - MstAdventBattleReward
    - MstAdventBattleRewardGroup
- **マッピングパターン**: 1対多（1運営仕様書→複数テーブル）、階層的マッピング（運営仕様書→詳細設計書→テーブル）
- **特記事項**:
  - 28シート構成の大規模な統合仕様書
  - イベント全体を統括する「マスター仕様書」
  - 施策名: 地獄楽 いいジャン祭
  - 開催期間: 2026-01-16 15:00 ~ 2026-02-16 10:59
  - メイン課金ポイント: ピックアップガシャA（賊王 亜左 弔兵衛（UR）、山田浅ェ門 桐馬（SSR））
  - 主要コンテンツ: 特別ミッション、イベントキャラ入手（メイ、民谷 巌鉄斎）、降臨バトル、エンブレム
  - ログインボーナス17日間（プリズム合計100個、コイン15000枚等）
  - 全体の報酬バランス、開催スケジュール、収益施策を網羅

---

### 15. （暗黙的インプット）既存マスタデータ・参照データ
- **カテゴリ**: 既存データからの参照・流用
- **対応テーブル数**: 複数
- **対応テーブル**:
  - MstItem（汎用アイテム: memory_glo_00001, memory_glo_00003等）
  - MstItemI18n
  - OprGachaPrize（ガチャラインナップの既存キャラ部分）
- **マッピングパターン**: 既存データ流用
- **特記事項**:
  - 設計書に明示されていないが、システムで共通使用されるアイテム等
  - 例: グロー討伐隊メモリー、サポートメモリー、レアチケット

---

## テーブル別逆引きマッピング

### MstEvent
- **対応設計書**: 運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」、GLOW_ID管理
- **設定項目**: イベントID（event_jig_00001）、開催期間、シリーズID（jig）、アセットキー
- **データソース**: 運営仕様書「01_概要.csv」、ID管理「いいジャン祭ID.csv」

### MstEventI18n
- **対応設計書**: 運営仕様書、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: イベント名「地獄楽いいジャン祭」、バナーテキスト
- **データソース**: 運営仕様書、監修資料

### MstEventBonusUnit
- **対応設計書**: 降臨バトル設計書
- **設定項目**: 特効キャラ7体、ボーナス率20%、グループID（raid_jig1_00001）
- **データソース**: 降臨バトル設計書の特効設定

### MstEventDisplayUnit / MstEventDisplayUnitI18n
- **対応設計書**: 各クエスト設計書、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: クエスト画面表示キャラ、吹き出しセリフ
- **データソース**: クエスト設計書の演出設定、監修資料

### MstUnit
- **対応設計書**:
  - ヒーロー基礎設計_chara_jig_00401（賊王 亜左 弔兵衛）
  - ヒーロー基礎設計_chara_jig_00501（山田浅ェ門 桐馬）
  - ヒーロー基礎設計_chara_jig_00601（民谷 巌鉄斎）
  - ヒーロー基礎設計_chara_jig_00701（メイ）
  - GLOW_ID管理
- **設定項目**: キャラID、ロールタイプ、属性、レアリティ、ステータス、召喚コスト、再召喚時間、移動速度、KB回数等
- **データソース**: 各ヒーロー設計書の「キャラ基礎設計.csv」、「キャラ基礎数値.csv」

### MstUnitI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: キャラ名、説明文（フレーバーテキスト: 最大196文字）
- **データソース**: 監修資料「[プレイアブル]キャラテキスト.csv」

### MstUnitAbility
- **対応設計書**: ヒーロー設計書×4
- **設定項目**: パッシブスキル（アビリティID、パラメータ1/2/3）
- **データソース**: ヒーロー設計書「特性」シート

### MstUnitSpecificRankUp
- **対応設計書**: ヒーロー設計書（chara_jig_00601, chara_jig_00701）
- **設定項目**: ランクアップ素材（必要レベル、必要量、メモリー量等）
- **データソース**: ヒーロー設計書（イベント配布キャラのみ設定）

### MstAbility / MstAbilityI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: アビリティタイプ、説明文テンプレート
- **データソース**: ヒーロー設計書「特性」シート、監修資料

### MstAttack
- **対応設計書**: ヒーロー設計書×4
- **設定項目**: 通常攻撃（Normal）と必殺技（Special）、グレード別差分（0~9）、action_frames、killer_colors等
- **データソース**: ヒーロー設計書「MstAttack.csv」、「必殺ワザ(スペシャル系)_グレード差分.csv」

### MstAttackElement
- **対応設計書**: ヒーロー設計書×4
- **設定項目**: 攻撃詳細（ダメージ計算、エフェクト、範囲、対象、威力パラメータ等）
- **データソース**: ヒーロー設計書の攻撃設定詳細

### MstAttackI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: 攻撃説明文（多くは空欄）
- **データソース**: ヒーロー設計書、監修資料

### MstSpecialAttackI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: 必殺技名（例: 「強イモ弱イモ ゼンブダイジ」）
- **データソース**: 監修資料「キャラセリフ一覧.csv」

### MstSpecialRoleLevelUpAttackElement
- **対応設計書**: ヒーロー設計書（chara_jig_00701: メイ）
- **設定項目**: SpecialRoleタイプの成長パラメータ
- **データソース**: ヒーロー設計書（SpecialRoleキャラのみ）

### MstSpeechBalloonI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: バトル中の吹き出しセリフ（必殺技チャージ時など）
- **データソース**: 監修資料「キャラセリフ一覧.csv」

### MstQuest
- **対応設計書**:
  - イベントクエスト設計×6
  - 運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」
  - GLOW_ID管理
- **設定項目**: クエストID、タイプ、イベントID、ソート順、開始日時、終了日時、クエストグループ、難易度
- **データソース**: 運営仕様書「02_施策.csv」、各クエスト設計書

### MstQuestI18n
- **対応設計書**: イベントクエスト設計×6、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: クエスト名（例: 「必ず生きて帰る」）、カテゴリ名、フレーバーテキスト
- **データソース**: 監修資料「イベントクエスト作品テキスト.csv」

### MstQuestBonusUnit
- **対応設計書**: イベントクエスト設計×6
- **設定項目**: クエスト特効キャラ、コインボーナス率15%
- **データソース**: 各クエスト設計書の特効設定

### MstQuestEventBonusSchedule
- **対応設計書**: 降臨バトル設計書
- **設定項目**: 特効スケジュール（降臨バトル期間: 2026-01-23 ~ 2026-01-29）
- **データソース**: 降臨バトル設計書

### MstStage
- **対応設計書**: イベントクエスト設計×6
- **設定項目**: ステージID、クエストID、インゲームID、ステージ番号、推奨レベル、スタミナコスト、経験値、コイン等
- **データソース**: 各クエスト設計書「ステージ設計▶︎.csv」、運営仕様書の報酬設計

### MstStageI18n
- **対応設計書**: イベントクエスト設計×6、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: ステージ名（例: 「本能が告げている 危険だと」）、カテゴリ名
- **データソース**: 監修資料

### MstStageEventReward
- **対応設計書**: イベントクエスト設計×6、運営仕様書
- **設定項目**: 初回クリア報酬（FirstClear）、ランダム報酬（Random）、報酬カテゴリ、リソースタイプ、リソースID、数量、確率
- **データソース**: 運営仕様書「02_施策.csv」（報酬設計）、各クエスト設計書

### MstStageEventSetting
- **対応設計書**: イベントクエスト設計×6
- **設定項目**: リセットタイプ（Daily/None）、挑戦回数、広告チャレンジ回数、開始日時、終了日時、背景アセットキー
- **データソース**: 各クエスト設計書「1日1回.csv」、「ステージ設計▶︎.csv」

### MstStageClearTimeReward
- **対応設計書**: イベントクエスト設計×6、運営仕様書
- **設定項目**: タイムアタック報酬（例: 140秒以内クリアで無償ダイヤ20個）
- **データソース**: 運営仕様書、各クエスト設計書

### MstStageEndCondition
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書
- **設定項目**: ステージ終了条件（敵全滅、タイムオーバー）
- **データソース**: 各クエスト設計書、降臨バトル設計書（120秒タイムオーバー）

### MstAdventBattle
- **対応設計書**: 降臨バトル設計書、運営仕様書
- **設定項目**: ID（quest_raid_jig1_00001）、イベントID、インゲームID、タイプ（ScoreChallenge）、初期BP（500）、スコア加算係数（0.07）等
- **データソース**: 降臨バトル設計書、運営仕様書「03_降臨バトル.csv」

### MstAdventBattleI18n
- **対応設計書**: 降臨バトル設計書、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: レイド名（「まるで 悪夢を見ているようだ」）、ボス説明
- **データソース**: 監修資料

### MstAdventBattleClearReward
- **対応設計書**: 降臨バトル設計書、運営仕様書
- **設定項目**: クリア時ランダム報酬（メモリー、欠片等）
- **データソース**: 降臨バトル設計書、運営仕様書

### MstAdventBattleRank
- **対応設計書**: 降臨バトル設計書
- **設定項目**: ランク評価（Bronze/Silver/Gold/Platinum 各4レベル）、スコア閾値
- **データソース**: 降臨バトル設計書

### MstAdventBattleReward / MstAdventBattleRewardGroup
- **対応設計書**: 降臨バトル設計書、運営仕様書
- **設定項目**: 報酬カテゴリ（MaxScore/TotalScore/Rank/RankUp/FirstClear）、トリガー条件、報酬詳細
- **データソース**: 降臨バトル設計書、運営仕様書「03_降臨バトル.csv」

### MstInGame
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書、ランクマッチ設計書
- **設定項目**: インゲームID、オートプレイシーケンスID、BGMアセットキー、ボスBGM、背景、ページID、敵配置ID、パラメータ係数等
- **データソース**: 各クエスト設計書「master_config.csv」、降臨バトル設計書、ランクマッチ設計書

### MstInGameI18n
- **対応設計書**: イベントクエスト設計×6、ランクマッチ設計書、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: リザルトTips、説明文（PVPルール説明等）
- **データソース**: 各クエスト設計書「敗北tips.csv」、ランクマッチ設計書、監修資料

### MstInGameSpecialRule
- **対応設計書**: イベントクエスト設計×6（高難度等）、ランクマッチ設計書
- **設定項目**: 期間限定特殊ルール（攻撃速度UP、被ダメージ減少等）
- **データソース**: 各クエスト設計書の特別ルール設定、ランクマッチ設計書

### MstInGameSpecialRuleUnitStatus
- **対応設計書**: ランクマッチ設計書
- **設定項目**: PVP特別ルール（全キャラHP200%UP等）
- **データソース**: ランクマッチ設計書「地獄楽2ランクマ.csv」

### MstPage
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書、ランクマッチ設計書
- **設定項目**: ページID、リリースキー
- **データソース**: 各クエスト設計書

### MstKomaLine
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書、ランクマッチ設計書
- **設定項目**: ページID、行、高さ、コマ1~4の設定（アセット、幅、エフェクト等）
- **データソース**: 各クエスト設計書「マッピング設定シート_new.csv」、「コマ効果.csv」

### MstEnemyCharacter / MstEnemyCharacterI18n
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: 敵キャラID、シリーズID、アセットキー、敵キャラ名、説明
- **データソース**: ヒーロー設計書（敵バージョン）、監修資料「[エネミー]キャラテキスト.csv」

### MstEnemyStageParameter
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書
- **設定項目**: 敵キャラID、キャラユニット種別、ロールタイプ、属性、HP、攻撃力、移動速度、ドロップBP等
- **データソース**: 各クエスト設計書「エネミー出現.csv」

### MstEnemyOutpost
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書
- **設定項目**: 敵拠点ID、HP、ダメージ無効化フラグ、アウトポストアセットキー、アートワークアセットキー
- **データソース**: 各クエスト設計書「ステージ設計▶︎.csv」

### MstAutoPlayerSequence
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書、ランクマッチ設計書
- **設定項目**: シーケンスセットID、シーケンスグループID、条件タイプ、条件値、アクションタイプ、アクション値等
- **データソース**: 各クエスト設計書「MstAutoPlayerSequence.csv」

### MstArtwork / MstArtworkI18n
- **対応設計書**: ストーリークエスト設計（必ず生きて帰る、朱印の者たち）、運営仕様書、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: 原画ID（artwork_event_jig_0001, 0002）、シリーズID、拠点追加HP（+100）、アセットキー、原画タイトル・説明
- **データソース**: ストーリークエスト設計書の原画演出設定、運営仕様書、監修資料

### MstArtworkFragment / MstArtworkFragmentI18n / MstArtworkFragmentPosition
- **対応設計書**: ストーリークエスト設計（必ず生きて帰る、朱印の者たち）
- **設定項目**: 欠片ID、原画ID、ドロップグループID、ドロップ率100%、レアリティ、アセット番号、欠片名、配置位置（1~16）
- **データソース**: ストーリークエスト設計書

### MstMangaAnimation
- **対応設計書**: イベントクエスト設計×6（特にストーリークエスト）
- **設定項目**: ステージID、条件タイプ、条件値、アニメーション開始遅延、速度、一時停止フラグ、スキップ可否、アセットキー
- **データソース**: ストーリークエスト設計書の原画演出設定（開始時・終了時）

### MstMissionEvent / MstMissionEventI18n
- **対応設計書**: 運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」
- **設定項目**: ミッションID、イベントID、基準タイプ、基準値、基準カウント、グループキー、報酬グループID、説明文
- **データソース**: 運営仕様書「04_ミッション.csv」

### MstMissionEventDependency
- **対応設計書**: 運営仕様書
- **設定項目**: グループID、ミッションID、解放順序
- **データソース**: 運営仕様書「04_ミッション.csv」（依存関係設定）

### MstMissionEventDailyBonus / MstMissionEventDailyBonusSchedule
- **対応設計書**: 運営仕様書
- **設定項目**: ログインボーナススケジュールID、ログイン日数、報酬グループID、期間（2026-01-16 ~ 2026-02-02）
- **データソース**: 運営仕様書「02_施策.csv」（ログインボーナス詳細: 17日間分）

### MstMissionLimitedTerm / MstMissionLimitedTermI18n
- **対応設計書**: 運営仕様書、降臨バトル設計書
- **設定項目**: 期間限定ミッション（降臨バトル5回挑戦等）、説明文
- **データソース**: 運営仕様書「04_ミッション.csv」

### MstMissionReward
- **対応設計書**: 運営仕様書
- **設定項目**: グループID、リソースタイプ、リソースID、数量
- **データソース**: 運営仕様書「04_ミッション.csv」、「05_報酬一覧.csv」

### MstItem / MstItemI18n
- **対応設計書**:
  - ヒーロー設計書×4（キャラメモリー、欠片）
  - 運営仕様書（ガチャチケット等）
  - GLOW_ID管理
- **設定項目**: アイテムID、タイプ、グループタイプ、レアリティ、アセットキー、効果値、期間、アイテム名・説明
- **データソース**: ヒーロー設計書、運営仕様書、ID管理シート

### OprGacha / OprGachaI18n
- **対応設計書**: 運営仕様書、GLOW_ID管理
- **設定項目**: ガチャID（Pickup_jig_001, 002）、ガチャタイプ、上限グループ、10連設定、確定枠、期間、優先度、ガチャ名・説明・バナー
- **データソース**: 運営仕様書「地獄楽 いいジャン祭ピックアップガシャA_設計書.csv」、「地獄楽 いいジャン祭ピックアップガシャB_設計書.csv」

### OprGachaPrize
- **対応設計書**: 運営仕様書、ヒーロー設計書×4
- **設定項目**: グループID、リソースタイプ、リソースID、数量、重み、ピックアップフラグ
- **データソース**: 運営仕様書ガチャ設計書（排出内容）

### OprGachaUpper
- **対応設計書**: 運営仕様書
- **設定項目**: 上限グループ、上限タイプ、回数（100回）
- **データソース**: 運営仕様書ガチャ設計書（天井設定）

### OprGachaUseResource
- **対応設計書**: 運営仕様書
- **設定項目**: ガチャID、コストタイプ、コストID、コスト数、抽選回数、コスト優先度
- **データソース**: 運営仕様書ガチャ設計書（単発150、10連1500、チケット設定）

### OprGachaDisplayUnitI18n
- **対応設計書**: 運営仕様書、ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: ガチャID、ユニットID、ソート順、説明文（キャラ訴求文言）
- **データソース**: 運営仕様書ガチャ設計書（キャラ訴求文言①②）

### MstStoreProduct / MstStoreProductI18n
- **対応設計書**: 運営仕様書
- **設定項目**: ストア商品ID、プロダクトID（iOS/Android/Webstore）、価格情報
- **データソース**: 運営仕様書「07_いいジャン祭パック_設計書.csv」

### OprProduct / OprProductI18n
- **対応設計書**: 運営仕様書
- **設定項目**: 商品ID、ストア商品ID、商品タイプ、購入可能回数、有償金額、表示優先度、期間、アセットキー
- **データソース**: 運営仕様書「07_いいジャン祭パック_設計書.csv」

### MstPack / MstPackI18n / MstPackContent
- **対応設計書**: 運営仕様書
- **設定項目**: パックID（event_item_pack_12, 13）、商品サブID、割引率、パックタイプ、取引可能回数、コストタイプ、コスト、アセットキー、パック名、内容物
- **データソース**: 運営仕様書「07_いいジャン祭パック_設計書.csv」

### MstPvp / MstPvpI18n
- **対応設計書**: ランクマッチ設計書
- **設定項目**: PVP ID、ランキング最低ランク、最大デイリーチャレンジ回数、アイテムチャレンジ回数、インゲームID、初期BP、ルール説明
- **データソース**: ランクマッチ設計書「地獄楽2ランクマ.csv」、「01_基本情報.csv」

### MstHomeBanner
- **対応設計書**: 運営仕様書、GLOW_ID管理
- **設定項目**: バナーID、遷移先、遷移先パス、アセットキー、期間、ソート順
- **データソース**: 運営仕様書「バナー一覧.csv」、ID管理シート

### MstEmblem / MstEmblemI18n
- **対応設計書**: 運営仕様書、GLOW_ID管理、GLOW_キャラ名称＆キャラセリフ資料
- **設定項目**: エンブレムID（emblem_event_jig_00001~00007）、エンブレムタイプ、シリーズID、アセットキー、エンブレム名・説明
- **データソース**: 運営仕様書、ID管理シート、監修資料

---

## マッピングパターン分析

### パターン1: 1対1マッピング
- **該当なし**（今回のリリースでは全て複雑なマッピング）

### パターン2: 1対多マッピング（最も一般的）
- **ヒーロー設計書 → 複数テーブル**:
  - 1キャラ設計書 → MstUnit, MstUnitI18n, MstUnitAbility, MstAttack, MstAttackElement, MstAttackI18n, MstSpecialAttackI18n, MstSpeechBalloonI18n, MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstItem（メモリー・欠片）, MstItemI18n, OprGachaPrize, MstEventDisplayUnit, MstEventDisplayUnitI18n, OprGachaDisplayUnitI18n等（約19~20テーブル）
- **クエスト設計書 → 複数テーブル**:
  - 1クエスト設計書 → MstQuest, MstQuestI18n, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstStageEndCondition, MstInGame, MstInGameI18n, MstPage, MstKomaLine, MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstEnemyOutpost, MstAutoPlayerSequence, MstMangaAnimation, MstQuestBonusUnit等（約25~27テーブル）
- **運営仕様書 → 複数テーブル**:
  - 1運営仕様書 → 60個以上のテーブル（ガチャ、ミッション、ログボ、ショップ、パック、エンブレム、アイテム、報酬等）

### パターン3: 多対1マッピング
- **複数設計書 → I18nテーブル**:
  - 15設計書 → MstUnitI18n, MstQuestI18n, MstStageI18n, MstEventI18n等の各種I18nテーブル
  - 全て「GLOW_キャラ名称＆キャラセリフ資料」を経由
- **複数設計書 → MstItem**:
  - ヒーロー設計書×4 → MstItem（各キャラのメモリー・欠片）
  - 運営仕様書 → MstItem（ガチャチケット等）
  - イベント報酬として複数のクエストから参照される
- **全設計書 → GLOW_ID管理**:
  - 15設計書 → 全てID管理シートを参照してID採番

### パターン4: 階層的マッピング
- **運営仕様書（全体像）→ 詳細設計書（詳細パラメータ）→ テーブル**:
  - 運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」
    - → クエスト設計書×6（各クエストの詳細）
      - → MstQuest, MstStage, MstInGame, MstPage, MstKomaLine, MstAutoPlayerSequence等
    - → ヒーロー設計書×4（各キャラの詳細）
      - → MstUnit, MstAttack, MstAttackElement, MstAbility, MstUnitAbility等
    - → 降臨バトル設計書
      - → MstAdventBattle, MstAdventBattleRank, MstAdventBattleReward等
    - → ランクマッチ設計書
      - → MstPvp, MstInGame（PVP用）, MstPage（PVP用）等

---

## 設計書読み取りの推奨順序

### 第1段階: 全体像の把握（必読）
1. **GLOW_ID管理** - ID体系の確認（全テーブルの基準）
2. **運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」** - イベント全体のスケジュール・構成、ガチャ、ミッション、ログボ、ショップ、報酬バランス

### 第2段階: 各機能の詳細設計（機能別並行読み取り可）
3. **ヒーロー設計書×4** - プレイアブルキャラの詳細
   - chara_jig_00401_賊王 亜左 弔兵衛（UR・ガチャ）
   - chara_jig_00501_山田浅ェ門 桐馬（SSR・ガチャ）
   - chara_jig_00601_民谷 巌鉄斎（SR・イベント配布）
   - chara_jig_00701_メイ（SR・イベント配布）
4. **クエスト設計書×6** - ストーリー・デイリー・チャレンジ・降臨等
   - 【ストーリー】必ず生きて帰る（メイ入手）
   - 【ストーリー】朱印の者たち（民谷 巌鉄斎入手）
   - 【1日1回】本能が告げている 危険だと（デイリー）
   - 【チャレンジ】死罪人と首切り役人
   - 【高難度】手負いの獣は恐ろしいぞ
   - 【降臨バトル】まるで 悪夢を見ているようだ（ランキング）
5. **ランクマッチ設計書** - PVP設定

### 第3段階: テキスト・監修（最終確認）
6. **GLOW_キャラ名称＆キャラセリフ資料** - 全テキスト系カラムの監修資料

---

## カバレッジ分析

### 設計書でカバーされているテーブル（79個 - 全テーブル）
全79テーブルが設計書でカバーされています。

#### イベント基本（5個）
- MstEvent
- MstEventI18n
- MstEventBonusUnit
- MstEventDisplayUnit
- MstEventDisplayUnitI18n

#### ユニット関連（7個）
- MstUnit
- MstUnitI18n
- MstUnitAbility
- MstUnitSpecificRankUp
- MstAbility
- MstAbilityI18n
- MstSpeechBalloonI18n

#### 攻撃アクション関連（5個）
- MstAttack
- MstAttackElement
- MstAttackI18n
- MstSpecialAttackI18n
- MstSpecialRoleLevelUpAttackElement

#### クエスト関連（4個）
- MstQuest
- MstQuestI18n
- MstQuestBonusUnit
- MstQuestEventBonusSchedule

#### ステージ関連（6個）
- MstStage
- MstStageI18n
- MstStageEventReward
- MstStageEventSetting
- MstStageClearTimeReward
- MstStageEndCondition

#### 降臨バトル関連（6個）
- MstAdventBattle
- MstAdventBattleI18n
- MstAdventBattleClearReward
- MstAdventBattleRank
- MstAdventBattleReward
- MstAdventBattleRewardGroup

#### インゲーム設定関連（6個）
- MstInGame
- MstInGameI18n
- MstInGameSpecialRule
- MstInGameSpecialRuleUnitStatus
- MstPage
- MstKomaLine

#### 敵キャラ・オートプレイ関連（5個）
- MstEnemyCharacter
- MstEnemyCharacterI18n
- MstEnemyStageParameter
- MstEnemyOutpost
- MstAutoPlayerSequence

#### 原画関連（5個）
- MstArtwork
- MstArtworkI18n
- MstArtworkFragment
- MstArtworkFragmentI18n
- MstArtworkFragmentPosition

#### マンガアニメーション関連（1個）
- MstMangaAnimation

#### ミッション関連（8個）
- MstMissionEvent
- MstMissionEventI18n
- MstMissionEventDependency
- MstMissionEventDailyBonus
- MstMissionEventDailyBonusSchedule
- MstMissionLimitedTerm
- MstMissionLimitedTermI18n
- MstMissionReward

#### アイテム関連（2個）
- MstItem
- MstItemI18n

#### ガチャ関連（6個）
- OprGacha
- OprGachaI18n
- OprGachaPrize
- OprGachaUpper
- OprGachaUseResource
- OprGachaDisplayUnitI18n

#### ストア・商品関連（7個）
- MstStoreProduct
- MstStoreProductI18n
- OprProduct
- OprProductI18n
- MstPack
- MstPackI18n
- MstPackContent

#### PVP関連（2個）
- MstPvp
- MstPvpI18n

#### バナー関連（1個）
- MstHomeBanner

#### エンブレム関連（2個）
- MstEmblem
- MstEmblemI18n

### 設計書に記載がないテーブル（0個）
全てのテーブルが設計書でカバーされています。

### 設計書に記載があるがテーブルに未反映の項目
設計書に記載されているが、今回のマスタデータCSVに含まれていない可能性があるもの:
- ランクマッチの報酬テーブル（設計書「03_ランク報酬.csv」、「04_ランキング報酬.csv」）
- ランクマッチのダミーユーザー（設計書「05_ダミーユーザー一覧.csv」）

**理由推測**:
- ランクマッチ報酬やダミーユーザーは、別のリリースキーで管理されている可能性
- または、運営系の動的データとして別途管理されている可能性

---

## 次のタスクへの示唆

機能別手順書を作成する際の推奨グルーピング:

### グループ1: イベント基本設定
- **対応設計書**: 運営仕様書、GLOW_ID管理
- **対応テーブル**: MstEvent, MstEventI18n, MstHomeBanner
- **参考手順書**: なし（新規作成推奨）

### グループ2: ヒーロー（ユニット）
- **対応設計書**: ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料、GLOW_ID管理
- **対応テーブル**: MstUnit, MstUnitI18n, MstUnitAbility, MstUnitSpecificRankUp, MstAbility, MstAbilityI18n, MstAttack, MstAttackElement, MstAttackI18n, MstSpecialAttackI18n, MstSpecialRoleLevelUpAttackElement, MstSpeechBalloonI18n, MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstItem（メモリー・欠片）, MstItemI18n, MstEventDisplayUnit, MstEventDisplayUnitI18n
- **参考手順書**: なし（新規作成推奨）

### グループ3: クエスト・ステージ
- **対応設計書**: イベントクエスト設計×6、運営仕様書、GLOW_キャラ名称＆キャラセリフ資料、GLOW_ID管理
- **対応テーブル**: MstQuest, MstQuestI18n, MstQuestBonusUnit, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstStageEndCondition, MstInGame, MstInGameI18n, MstPage, MstKomaLine, MstEnemyOutpost, MstAutoPlayerSequence, MstMangaAnimation
- **参考手順書**: なし（新規作成推奨）

### グループ4: 降臨バトル
- **対応設計書**: 降臨バトル設計書、運営仕様書、GLOW_キャラ名称＆キャラセリフ資料
- **対応テーブル**: MstAdventBattle, MstAdventBattleI18n, MstAdventBattleClearReward, MstAdventBattleRank, MstAdventBattleReward, MstAdventBattleRewardGroup, MstEventBonusUnit, MstQuestEventBonusSchedule
- **参考手順書**: なし（新規作成推奨）

### グループ5: ミッション
- **対応設計書**: 運営仕様書
- **対応テーブル**: MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionEventDailyBonus, MstMissionEventDailyBonusSchedule, MstMissionLimitedTerm, MstMissionLimitedTermI18n, MstMissionReward
- **参考手順書**: domain/tasks/masterdata-entry/gemini/mission
  - **活用可能な構造**: mission.md（手順書）、tools/（各種スクリプト）

### グループ6: ガチャ
- **対応設計書**: 運営仕様書、GLOW_ID管理
- **対応テーブル**: OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n
- **参考手順書**: なし（新規作成推奨）

### グループ7: ショップ・パック
- **対応設計書**: 運営仕様書
- **対応テーブル**: MstStoreProduct, MstStoreProductI18n, OprProduct, OprProductI18n, MstPack, MstPackI18n, MstPackContent
- **参考手順書**: なし（新規作成推奨）

### グループ8: アイテム・報酬
- **対応設計書**: ヒーロー設計書×4、運営仕様書、GLOW_ID管理
- **対応テーブル**: MstItem, MstItemI18n
- **参考手順書**: なし（新規作成推奨）

### グループ9: 原画・エンブレム
- **対応設計書**: ストーリークエスト設計、運営仕様書、GLOW_ID管理、GLOW_キャラ名称＆キャラセリフ資料
- **対応テーブル**: MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition, MstEmblem, MstEmblemI18n
- **参考手順書**: なし（新規作成推奨）

### グループ10: PVP（ランクマッチ）
- **対応設計書**: ランクマッチ設計書、GLOW_ID管理
- **対応テーブル**: MstPvp, MstPvpI18n, MstInGame（PVP用）, MstInGameI18n, MstInGameSpecialRule, MstInGameSpecialRuleUnitStatus, MstPage（PVP用）, MstKomaLine
- **参考手順書**: なし（新規作成推奨）

### グループ11: 敵・自動行動
- **対応設計書**: イベントクエスト設計×6、降臨バトル設計書、ヒーロー設計書×4、GLOW_キャラ名称＆キャラセリフ資料
- **対応テーブル**: MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstEnemyOutpost, MstAutoPlayerSequence
- **参考手順書**: なし（新規作成推奨）

---

## 特に注意が必要な設定項目

### 1. 開催期間の整合性
- **イベント全体**: 2026-01-16 15:00:00 ~ 2026-02-16 10:59:59
- **デイリークエスト**: 2026-01-16 15:00:00 ~ 2026-02-02 03:59:00（イベント期間より短い）
- **ストーリー2（朱印の者たち）**: 2026-01-21 15:00:00 ~（5日遅れて開始）
- **降臨バトル**: 2026-01-23 15:00:00 ~ 2026-01-29 14:59:00（7日間限定）
- **ガチャ開始**: 2026-01-16 12:00:00（イベント開始の3時間前）
- **ログボ期間**: 2026-01-16 15:00:00 ~ 2026-02-02 03:59:00

**注意点**: 各コンテンツの開催期間が異なるため、日時の設定ミスに注意

### 2. ID体系の一貫性
- **キャラID**: chara_jig_XXXXX
- **イベントID**: event_jig_00001
- **クエストグループID**: event_jig_00001
- **ガシャID**: Pickup_jig_001
- **バナーID**: gacha_banner_jig_00001、hometop_gacha_jig_00001
- **背景ID**: koma_background_jig_00001

**注意点**: プレフィックス「jig」が全体で統一されているかを確認

### 3. 報酬バランス
- **ストーリークエスト1ステージあたりの報酬量**
- **ログインボーナス17日間の合計報酬**
- **デイリークエスト全日プレイ時の合計報酬**
- **イベント全体での「メイのかけら」「民谷 巌鉄斎のかけら」の獲得量とグレード強化の整合性**

**注意点**: 運営仕様書に記載された報酬設計と各クエスト設計書の報酬が一致しているか確認

### 4. キャラクター性能の複雑さ
- **賊王 亜左 弔兵衛の特性**: HP70%以上で被ダメ40%カット、HP69%以下で攻撃35%UP（体力条件付き）
- **必殺ワザの弱体化・体力吸収のバランス調整**
- **グレード差分の設定**（グレード0~9）

**注意点**: キャラ設計の詳細な条件式とパラメータをマスタデータに正確に反映する必要がある

### 5. 外部ファイル参照
- **原画演出URL**: `251212_地獄楽_ストーリークエスト1原画演出.pdf`
- **クリエイティブ依頼**
- **基礎設計URL**: Google スプレッドシートのURL

**注意点**: マスタデータに含めるべき情報と、外部管理すべき情報の切り分け

### 6. ガシャAとガシャBのラインナップ差異
- **ガシャAからはガシャBでピックアップしている「がらんの画眉丸」をラインナップから抜く**

**注意点**: ラインナップ設定時に除外条件を正しく反映

### 7. ランクマッチの特別ルール
- **リーダーP1000スタート**
- **全キャラ体力3倍UP**
- **突風コマの配置**

**注意点**: 特別ルール設定のマスタデータへの反映方法を検討

---

## まとめ

### 主要な発見

1. **全79テーブルがカバーされている**: 15件の設計書で全マスタデータテーブルが網羅的にカバーされています。

2. **階層的なマッピング構造**:
   - 運営仕様書「20260116_地獄楽 いいジャン祭_仕様書」が全体の「マスター」として機能
   - 各詳細設計書（クエスト、ヒーロー、降臨バトル、ランクマッチ）が詳細パラメータを提供
   - GLOW_ID管理が全体のID体系を統一管理
   - GLOW_キャラ名称＆キャラセリフ資料が全テキスト系カラムを監修

3. **1対多マッピングが主流**:
   - ヒーロー設計書: 1設計書 → 約19~20テーブル
   - クエスト設計書: 1設計書 → 約25~27テーブル
   - 運営仕様書: 1設計書 → 60個以上のテーブル

4. **複雑度の高いテーブル**:
   - MstAttack / MstAttackElement（117攻撃、152要素、グレード差分）
   - MstAutoPlayerSequence（185シーケンス）
   - MstAdventBattleReward / MstAdventBattleRewardGroup（報酬カテゴリ55、報酬120）
   - MstKomaLine（58コマライン）

5. **開催期間の多様性**:
   - 各コンテンツで開催期間が異なる
   - ガシャはイベント開始の3時間前から開始
   - 降臨バトルは7日間限定
   - デイリークエストはイベント全期間より短い

### 次のタスクへの推奨事項

1. **参考手順書の活用**:
   - domain/tasks/masterdata-entry/gemini/mission の構造を他のグループにも適用
   - mission.md（手順書）、tools/（各種スクリプト）の形式を踏襲

2. **グルーピング戦略**:
   - 機能別に11グループに分割（イベント基本、ヒーロー、クエスト、降臨バトル、ミッション、ガチャ、ショップ、アイテム、原画、PVP、敵）
   - 各グループで独立した手順書を作成
   - グループ間の依存関係を明確化

3. **設計書読み取りの順序**:
   - 第1段階: GLOW_ID管理、運営仕様書（全体像）
   - 第2段階: 各詳細設計書（並行読み取り可）
   - 第3段階: GLOW_キャラ名称＆キャラセリフ資料（最終確認）

4. **注意すべきポイント**:
   - 開催期間の整合性チェック
   - ID体系の一貫性チェック
   - 報酬バランスの整合性チェック
   - キャラクター性能の複雑な条件式の正確な反映
   - ガチャラインナップの除外条件
   - ランクマッチの特別ルール設定
