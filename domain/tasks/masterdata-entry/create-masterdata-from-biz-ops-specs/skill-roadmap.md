# マスタデータ作成スキル開発ロードマップ

## 概要

既存の手順書をClaude Codeスキル形式に変換し、運営仕様書からマスタデータCSVを自動作成できる仕組みを構築します。

### 背景

タスク#2で策定された分割計画を反映し、元の12グループから以下の2つを分割して**14グループ**になりました:
- 「原画・エンブレム」 → 「原画」+「エンブレム」
- 「アイテム・報酬」 → 「アイテム」+「報酬」

本ロードマップでは、これら14グループに対応する14個のスキルを段階的に開発していきます。

### 開発方針

- **段階的な展開**: 優先度の高いグループから順次スキル化
- **品質重視**: タスク#3（ガチャスキル）の評価結果を反映
- **効率的な開発**: テンプレート化により開発速度を向上

---

## スキル一覧表

全14個のスキルの概要を以下に示します。

| No | スキル名 | 対象テーブル数 | 優先度 | 作成フェーズ | 主要な対象テーブル | 概要 |
|----|---------|--------------|--------|------------|----------------|------|
| 1 | masterdata-from-bizops-**gacha** | 6 | 高 | Phase 1 | OprGacha, OprGachaPrize, OprGachaUpper等 | ガチャ設定の作成。最も使用頻度が高い |
| 2 | masterdata-from-bizops-**hero** | 13 | 高 | Phase 1 | MstUnit, MstAbility, MstAttack等 | ヒーロー（ユニット）の作成。新規キャラ追加で頻繁に使用 |
| 3 | masterdata-from-bizops-**mission** | 8 | 高 | Phase 1 | MstMissionEvent, MstMissionReward等 | ミッション設定の作成。イベントごとに使用 |
| 4 | masterdata-from-bizops-**quest-stage** | 10 | 中 | Phase 2 | MstQuest, MstStage, MstStageEventReward等 | クエスト・ステージの作成。イベントごとに使用 |
| 5 | masterdata-from-bizops-**item** | 2 | 中 | Phase 2 | MstItem, MstItemI18n | アイテムの作成。新規アイテム追加時に使用（分割後） |
| 6 | masterdata-from-bizops-**reward** | 17 | 中 | Phase 2 | MstMissionReward, MstStageReward, MstAdventBattleReward等 | **報酬設定用汎用スキル**。全報酬テーブルの共通ルールをカバー。各機能スキルと併用（分割後） |
| 7 | masterdata-from-bizops-**event-basic** | 3 | 中 | Phase 2 | MstEvent, MstEventBonusUnit等 | イベント基本設定の作成。イベントごとに使用 |
| 8 | masterdata-from-bizops-**advent-battle** | 7 | 低 | Phase 3 | MstAdventBattle, MstAdventBattleReward等 | 降臨バトルの作成。特定イベントのみ |
| 9 | masterdata-from-bizops-**pvp** | 2 | 低 | Phase 3 | MstPvp, MstPvpI18n | PVP（ランクマッチ）の作成。シーズンごと |
| 10 | masterdata-from-bizops-**shop-pack** | 7 | 低 | Phase 3 | MstStoreProduct, OprProduct, MstPack等 | ショップ・パックの作成。施策ごと |
| 11 | masterdata-from-bizops-**artwork** | 5 | 低 | Phase 3 | MstArtwork, MstArtworkFragment等 | 原画の作成。新規コンテンツ追加時（分割後） |
| 12 | masterdata-from-bizops-**emblem** | 2 | 低 | Phase 3 | MstEmblem, MstEmblemI18n | エンブレムの作成。新規コンテンツ追加時（分割後） |
| 13 | masterdata-from-bizops-**enemy-autoplayer** | 5 | 低 | Phase 3 | MstEnemyCharacter, MstAutoPlayerSequence等 | 敵・自動行動の作成。新規敵キャラ追加時 |
| 14 | masterdata-from-bizops-**ingame** | 7 | 低 | Phase 3 | MstInGame, MstMangaAnimation等 | インゲーム（マンガアニメーション含む）の作成 |

**合計**: 14スキル、95テーブル（100%カバレッジ）
- 元の79テーブル + 報酬テーブル16個追加（MstMissionRewardを除く）

---

## フェーズ別開発計画

### Phase 1: 高頻度グループ（優先度高）

**対象**: 3スキル、27テーブル

| スキル名 | 対象テーブル数 | 見積もり工数（参考値） |
|---------|--------------|---------------------|
| masterdata-from-bizops-gacha | 6 | **完了** ✅ |
| masterdata-from-bizops-hero | 13 | 2～3日 |
| masterdata-from-bizops-mission | 8 | 1.5～2日 |

**特徴**:
- **最も使用頻度が高い機能**
- ガチャは運営施策の中心（毎月複数回実施）
- ヒーローは新規キャラ追加時に必須
- ミッションはイベントごとに作成

**開発上の注意点**:
- ガチャスキル（タスク#3）の評価結果を他のスキルにフィードバック
- 特にヒーローは13テーブルと多いため、段階的な検証が必要
- ミッションは報酬との連携が重要

**Phase 1完了後の効果**:
- 日常的に発生するマスタデータ作成タスクの大半を自動化
- 運営施策の実施スピード向上

---

### Phase 2: 中頻度グループ（優先度中）

**対象**: 4スキル、32テーブル

| スキル名 | 対象テーブル数 | 見積もり工数（参考値） |
|---------|--------------|---------------------|
| masterdata-from-bizops-quest-stage | 10 | 2～2.5日 |
| masterdata-from-bizops-item | 2 | 0.5～1日 |
| masterdata-from-bizops-reward | 17 | 2～3日 |
| masterdata-from-bizops-event-basic | 3 | 1～1.5日 |

**特徴**:
- **定期的に使用する機能**
- クエスト・ステージはイベントごとに作成
- アイテム・報酬は分割後の独立スキル（メンテナンス性向上）
- **報酬スキルは汎用スキル**として、全報酬テーブル（17個）の共通ルールをカバー
- イベント基本設定は全イベントで必須

**開発上の注意点**:
- **報酬スキルの汎用性**: 報酬設定用汎用スキルとして開発
  - 全17個の報酬テーブルの共通ルールをまとめる
  - 各機能ごとの固有設定を拡張要素として記載
  - 各機能スキル（ガチャ、ヒーロー、ミッション等）と併用して使用
- **アイテム・報酬の連携**: 報酬スキルはアイテムスキルで作成したIDを参照
  - アイテムスキル → 報酬スキルの順序で実行（単独使用時）
  - 各機能スキルと併用時は、報酬設定を高精度に実行
- クエスト・ステージは10テーブルと多いため、段階的な検証が必要

**Phase 2完了後の効果**:
- イベント開催時のマスタデータ作成がほぼ自動化
- 運営施策の準備期間を大幅短縮

---

### Phase 3: 低頻度グループ（優先度低）

**対象**: 7スキル、36テーブル

| スキル名 | 対象テーブル数 | 見積もり工数（参考値） |
|---------|--------------|---------------------|
| masterdata-from-bizops-advent-battle | 7 | 1.5～2日 |
| masterdata-from-bizops-pvp | 2 | 0.5～1日 |
| masterdata-from-bizops-shop-pack | 7 | 1.5～2日 |
| masterdata-from-bizops-artwork | 5 | 1～1.5日 |
| masterdata-from-bizops-emblem | 2 | 0.5～1日 |
| masterdata-from-bizops-enemy-autoplayer | 5 | 1～1.5日 |
| masterdata-from-bizops-ingame | 7 | 1.5～2日 |

**特徴**:
- **特定のイベントや施策で使用**
- 降臨バトル、PVP、インゲーム等は不定期
- 原画・エンブレムは分割後の独立スキル（拡張性向上）

**開発上の注意点**:
- **原画・エンブレムの独立性**: 完全に独立したスキルとして開発
  - 将来的な類似リソース追加に対応しやすい構成
  - 各スキルの責務を明確化
- 降臨バトルはエンブレムとの連携が必要（ランキング報酬）

**Phase 3完了後の効果**:
- **全95テーブルを100%カバー**
- あらゆる運営施策に対応可能

---

## 各スキルの詳細説明

### 1. masterdata-from-bizops-gacha（Phase 1）✅

**対象テーブル**: 6個
- OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaChallenge

**概要**:
ガチャ設定の運営仕様書からマスタデータを作成します。ガチャの基本設定、排出アイテム、上限設定、使用リソース、チャレンジ設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_series_id: シリーズID
- opr_gacha_id: ガチャID

**主要な出力**:
- ガチャ基本設定（開催期間、ガチャタイプ、優先度等）
- 排出アイテム（キャラメモリー、RankUpMaterial等）
- 上限設定（天井、ステップアップ等）
- 使用リソース（FreeDiamond、PaidDiamond、Ticket等）

**推測値の例**:
- OprGacha.display_information_id（Strapi管理UUID）
- OprGacha.gacha_priority（表示優先度）
- OprGachaPrize.weight（排出重み）

**状態**: タスク#3で開発完了 ✅

---

### 2. masterdata-from-bizops-hero（Phase 1）

**対象テーブル**: 13個
- MstUnit, MstUnitI18n, MstUnitAbility, MstAbility, MstAbilityI18n, MstAttack, MstAttackI18n, MstAttackElement, MstAttackNormalSubjectStatus, MstAttackSpecialSubjectStatus, MstAttackNormalObjectStatus, MstAttackSpecialObjectStatus, MstUnitVoice

**概要**:
ヒーロー（ユニット）の運営仕様書からマスタデータを作成します。ユニットの基本設定、アビリティ、攻撃設定、ステータス効果、ボイス設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_series_id: シリーズID
- mst_unit_id: ユニットID
- character_name: キャラクター名

**主要な出力**:
- ユニット基本設定（コスト、レアリティ、HP、ATK等）
- アビリティ設定（スキル名、効果、発動条件等）
- 攻撃設定（通常攻撃、必殺技、攻撃属性等）
- ステータス効果（バフ、デバフ等）
- ボイス設定（召喚、勝利、敗北等）

**推測値の例**:
- MstUnit.summon_cost（レアリティから推測）
- MstUnit.special_attack_initial_cool_time（設計書に記載なし）
- MstAttack.action_frames（設計書に記載なし）
- MstUnitAbility.ability_parameter1/2/3（設計書に記載なし）

**開発上の注意点**:
- 13テーブルと最も多いため、段階的な検証が必要
- アビリティと攻撃の設定が複雑
- ステータス効果の整合性チェックが重要

**見積もり工数**: 2～3日

---

### 3. masterdata-from-bizops-mission（Phase 1）

**対象テーブル**: 8個
- MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionEventGroup, MstMissionEventGroupI18n, MstMissionReward, MstMissionRewardGroup, MstMissionRewardGroupI18n

**概要**:
ミッション設定の運営仕様書からマスタデータを作成します。ミッションイベント、依存関係、グループ設定、報酬設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_event_id: イベントID

**主要な出力**:
- ミッションイベント（タイプ、条件、達成数等）
- ミッション依存関係（前提ミッション等）
- ミッショングループ（パネルミッション等）
- ミッション報酬（アイテム、コイン等）

**推測値の例**:
- MstMissionEvent.event_point（イベントポイント）
- MstMissionReward.resource_id（アイテムIDの推測）

**開発上の注意点**:
- 報酬との連携が重要（MstMissionReward）
- ミッション依存関係の整合性チェック

**見積もり工数**: 1.5～2日

---

### 4. masterdata-from-bizops-quest-stage（Phase 2）

**対象テーブル**: 10個
- MstQuest, MstQuestI18n, MstQuestClearReward, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageEnemyLayer, MstStageEnemyLayerEnemy, MstStageReward

**概要**:
クエスト・ステージの運営仕様書からマスタデータを作成します。クエスト設定、クリア報酬、ステージ設定、敵配置、イベント報酬を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_event_id: イベントID
- quest_count: クエスト数

**主要な出力**:
- クエスト設定（難易度、消費スタミナ等）
- クエストクリア報酬
- ステージ設定（敵配置、Wave数等）
- ステージイベント報酬（ドロップアイテム等）
- 敵レイヤー・敵配置

**推測値の例**:
- MstQuest.stamina_cost（スタミナコスト）
- MstStage.stage_number（ステージ番号の自動採番）
- MstStageEnemyLayerEnemy.enemy_id（敵キャラIDの推測）

**開発上の注意点**:
- 10テーブルと多いため、段階的な検証が必要
- 敵配置とWave数の整合性チェック

**見積もり工数**: 2～2.5日

---

### 5. masterdata-from-bizops-item（Phase 2）

**対象テーブル**: 2個（分割後）
- MstItem, MstItemI18n

**概要**:
アイテムの運営仕様書からマスタデータを作成します。アイテムの基本設定、多言語対応を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- item_type: アイテムタイプ（CharacterFragment、RankUpMaterial、Ticket等）

**主要な出力**:
- アイテム基本設定（ID、タイプ、レアリティ、効果値等）
- アイテム名・説明文（多言語）

**推測値の例**:
- MstItem.effect_value（キャラメモリーの場合はキャラID）
- MstItem.start_at/end_at（開始・終了日時）

**開発上の注意点**:
- type=RankUpMaterialの場合、effect_valueにキャラIDを設定
- type=CharacterFragmentの場合、effect_valueは空欄
- 報酬スキルから参照されるため、IDの採番ルールを厳守

**見積もり工数**: 0.5～1日

---

### 6. masterdata-from-bizops-reward（Phase 2）**汎用スキル**

**対象テーブル**: 17個（全報酬テーブル）
- MstMissionReward（ミッション報酬）
- MstStageReward（ステージ報酬）
- MstStageEventReward（ステージイベント報酬）
- MstAdventBattleReward（降臨バトル報酬）
- MstPvpReward（PVP報酬）
- MstExchangeReward（交換報酬）
- MstShopPassReward（ショップパス報酬）
- MstDailyBonusReward（デイリーボーナス報酬）
- MstIdleIncentiveReward（放置報酬）
- MstUnitEncyclopediaReward（ユニット図鑑報酬）
- MstStageClearTimeReward（ステージクリア時間報酬）
- MstStageEnhanceRewardParam（ステージ強化報酬パラメータ）
- MstEventDisplayReward（イベント表示報酬）
- MstAdventBattleClearReward（降臨バトルクリア報酬）
- MstAdventBattleRewardGroup（降臨バトル報酬グループ）
- MstPvpRewardGroup（PVP報酬グループ）
- MstStageRewardGroup（ステージ報酬グループ）

**位置付け**: **報酬設定用汎用スキル**

**概要**:
報酬設定の共通ルールをまとめた汎用スキルです。全17個の報酬テーブルに対応し、各機能ごとの固有設定も拡張要素として記載します。各機能スキル（ガチャ、ヒーロー、ミッション等）と併用して、報酬設定を高精度に実行できます。

**主要な入力パラメータ**:
- release_key: リリースキー
- reward_type: 報酬タイプ（Mission、Stage、AdventBattle、Pvp等）
- reward_group_id: 報酬グループID

**主要な出力**:
- 報酬設定（リソースタイプ、リソースID、数量等）
- 報酬グループの定義

**推測値の例**:
- 報酬テーブル.resource_id（アイテムID、ユニットID、エンブレムIDの参照）
- 報酬テーブル.weight（ランダム排出の重み）

**開発上の注意点**:
- **汎用スキルとしての設計**: 全17個の報酬テーブルの共通ルールをまとめる
- **resource_type別の参照先**:
  - `resource_type=Item` → `MstItem.id` を参照（アイテム報酬）
  - `resource_type=Unit` → `MstUnit.id` を参照（ユニット報酬）
  - `resource_type=Emblem` → `MstEmblem.id` を参照（エンブレム報酬）
  - `resource_type=Coin/FreeDiamond/PaidDiamond` → 参照なし（通貨報酬）
- **各機能ごとの固有設定**: 拡張要素として記載（ミッション報酬、ステージ報酬等の固有ルール）
- **各機能スキルとの併用**: ガチャ、ヒーロー、ミッション等の各機能スキルをメインに使いつつ、本スキルを併用して報酬設定を高精度に実行
- 手順書にアイテム、ユニット、エンブレムへの依存関係を明記

**依存関係**:
```
報酬テーブル.resource_id → MstItem.id (resource_type=Item の場合)
報酬テーブル.resource_id → MstUnit.id (resource_type=Unit の場合)
報酬テーブル.resource_id → MstEmblem.id (resource_type=Emblem の場合)
報酬テーブル.resource_id → 空欄 (resource_type=Coin/FreeDiamond/PaidDiamond の場合)
```

**使用方法**:
1. **単独使用**: 報酬設定のみを行う場合
2. **併用**: 各機能ごとのマスタデータ作成スキルと併用して、報酬設定を高精度に実行

**見積もり工数**: 2～3日

---

### 7. masterdata-from-bizops-event-basic（Phase 2）

**対象テーブル**: 3個
- MstEvent, MstEventI18n, MstEventBonusUnit

**概要**:
イベント基本設定の運営仕様書からマスタデータを作成します。イベントの基本情報、ボーナスユニット設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_event_id: イベントID
- event_name: イベント名

**主要な出力**:
- イベント基本設定（開催期間、イベントタイプ等）
- イベント名・説明文（多言語）
- ボーナスユニット設定（ボーナス倍率等）

**推測値の例**:
- MstEvent.event_type（イベントタイプの推測）
- MstEventBonusUnit.bonus_rate（ボーナス倍率）

**開発上の注意点**:
- 全イベントで必須の設定
- 他のイベント関連スキル（ミッション、クエスト等）との連携

**見積もり工数**: 1～1.5日

---

### 8. masterdata-from-bizops-advent-battle（Phase 3）

**対象テーブル**: 7個
- MstAdventBattle, MstAdventBattleI18n, MstAdventBattleRank, MstAdventBattleRankI18n, MstAdventBattleReward, MstAdventBattleRewardGroup, MstAdventBattleRewardGroupI18n

**概要**:
降臨バトルの運営仕様書からマスタデータを作成します。降臨バトル設定、ランク設定、報酬設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_series_id: シリーズID
- mst_advent_battle_id: 降臨バトルID

**主要な出力**:
- 降臨バトル基本設定（開催期間、難易度等）
- ランク設定（ランク条件、ランク報酬等）
- 報酬設定（ランキング報酬、エンブレム等）

**推測値の例**:
- MstAdventBattleRank.rank_threshold（ランク条件の推測）
- MstAdventBattleReward.resource_id（エンブレムIDの参照）

**開発上の注意点**:
- エンブレムスキルとの連携（ランキング報酬）
- ランク条件の整合性チェック

**見積もり工数**: 1.5～2日

---

### 9. masterdata-from-bizops-pvp（Phase 3）

**対象テーブル**: 2個
- MstPvp, MstPvpI18n

**概要**:
PVP（ランクマッチ）の運営仕様書からマスタデータを作成します。PVPの基本設定、多言語対応を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_pvp_id: PVPID
- season_name: シーズン名

**主要な出力**:
- PVP基本設定（シーズン期間、ランク設定等）
- PVP名・説明文（多言語）

**推測値の例**:
- MstPvp.rank_threshold（ランク条件）

**開発上の注意点**:
- シーズンごとに作成
- ランク条件の整合性チェック

**見積もり工数**: 0.5～1日

---

### 10. masterdata-from-bizops-shop-pack（Phase 3）

**対象テーブル**: 7個
- MstStoreProduct, MstStoreProductI18n, MstStoreProductGroup, MstStoreProductGroupI18n, OprProduct, MstPack, MstPackI18n, MstPackContent

**概要**:
ショップ・パックの運営仕様書からマスタデータを作成します。ストア商品、商品グループ、パック設定、パック内容を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- product_type: 商品タイプ（Pack、Subscription等）

**主要な出力**:
- ストア商品設定（価格、商品タイプ等）
- 商品グループ設定
- パック設定（パック内容、購入制限等）
- パック内容（リソースタイプ、数量等）

**推測値の例**:
- MstStoreProduct.price（価格）
- MstPackContent.resource_id（アイテムIDの参照）

**開発上の注意点**:
- 施策ごとに作成
- 購入制限の整合性チェック

**見積もり工数**: 1.5～2日

---

### 11. masterdata-from-bizops-artwork（Phase 3）

**対象テーブル**: 5個（分割後）
- MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition

**概要**:
原画の運営仕様書からマスタデータを作成します。原画の基本設定、欠片設定、欠片配置を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_series_id: シリーズID
- mst_artwork_id: 原画ID

**主要な出力**:
- 原画基本設定（レアリティ、拠点追加HP等）
- 原画名・説明文（多言語）
- 原画の欠片設定（16個の欠片、4×4グリッド）
- 欠片のドロップグループ設定
- 欠片の配置位置設定（1～16）

**推測値の例**:
- MstArtwork.base_hp（拠点追加HP）
- MstArtworkFragment.drop_rate（ドロップ率）

**開発上の注意点**:
- 1つの原画につき必ず16個の欠片を作成
- 3つの欠片テーブル（Fragment、FragmentI18n、FragmentPosition）は連動して作成
- 原画5テーブルは強く結合しているため、1つのスキルにまとめる

**見積もり工数**: 1～1.5日

---

### 12. masterdata-from-bizops-emblem（Phase 3）

**対象テーブル**: 2個（分割後）
- MstEmblem, MstEmblemI18n

**概要**:
エンブレムの運営仕様書からマスタデータを作成します。エンブレムの基本設定、多言語対応を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_series_id: シリーズID
- emblem_type: エンブレムタイプ（Event、Series）

**主要な出力**:
- エンブレム基本設定（タイプ、シリーズID、アセットキー等）
- エンブレム名・説明文（多言語）

**推測値の例**:
- MstEmblem.asset_key（アセットキー）

**開発上の注意点**:
- 降臨バトルエンブレムはランキング順位に応じて複数作成（通常6個）
- エンブレムタイプの設定を正確に行う
- 完全に独立したリソース

**見積もり工数**: 0.5～1日

---

### 13. masterdata-from-bizops-enemy-autoplayer（Phase 3）

**対象テーブル**: 5個
- MstEnemyCharacter, MstEnemyStageParameter, MstAutoPlayerSequence, MstAutoPlayerSequenceI18n, MstAutoPlayerEvent

**概要**:
敵・自動行動の運営仕様書からマスタデータを作成します。敵キャラクター、ステージパラメータ、自動行動シーケンス、自動行動イベントを自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_enemy_character_id: 敵キャラID

**主要な出力**:
- 敵キャラクター設定（HP、ATK、スキル等）
- ステージパラメータ（難易度別パラメータ等）
- 自動行動シーケンス（行動パターン等）
- 自動行動イベント（トリガー、アクション等）

**推測値の例**:
- MstEnemyCharacter.hp/atk（ステータス値）
- MstAutoPlayerSequence.sequence_order（行動順序）

**開発上の注意点**:
- 新規敵キャラ追加時に使用
- 自動行動パターンの整合性チェック

**見積もり工数**: 1～1.5日

---

### 14. masterdata-from-bizops-ingame（Phase 3）

**対象テーブル**: 7個
- MstInGame, MstInGameI18n, MstPage, MstPageI18n, MstKoma, MstKomaLine, MstMangaAnimation

**概要**:
インゲーム（マンガアニメーション含む）の運営仕様書からマスタデータを作成します。インゲーム設定、ページ設定、コマ設定、マンガアニメーション設定を自動生成します。

**主要な入力パラメータ**:
- release_key: リリースキー
- mst_in_game_id: インゲームID
- page_count: ページ数

**主要な出力**:
- インゲーム基本設定
- ページ設定（ページ順序、内容等）
- コマ設定（コマ配置、台詞等）
- コマライン設定（台詞テキスト等）
- マンガアニメーション設定

**推測値の例**:
- MstPage.page_order（ページ順序）
- MstKoma.koma_order（コマ順序）

**開発上の注意点**:
- 新規マンガアニメーション追加時に使用
- ページ・コマの順序整合性チェック

**見積もり工数**: 1.5～2日

---

## 見積もり工数のサマリ

| フェーズ | スキル数 | テーブル数 | 見積もり工数（参考値） |
|---------|---------|-----------|---------------------|
| Phase 1 | 3 | 27 | 3.5～5日（ガチャ完了済み） |
| Phase 2 | 4 | 16 | 4.5～6日 |
| Phase 3 | 7 | 36 | 8～11.5日 |
| **合計** | **14** | **79** | **16～22.5日** |

**注意**:
- 上記は1名のエンジニアが全スキルを開発する場合の参考値
- 複数名での並行開発により期間短縮が可能
- タスク#3（ガチャスキル）の評価結果により工数が変動する可能性あり

---

## 開発ロードマップの実行計画

### 前提条件

1. **ガチャスキル（タスク#3）の完成と評価**
   - Phase 1の先行スキルとして開発完了
   - 評価結果を他のスキルにフィードバック

2. **スキルテンプレートの整備**
   - タスク#1で策定された設計アーキテクチャに基づく
   - SKILL.mdのテンプレート作成
   - references/manual.mdの移植手順確立

3. **分割計画の反映**
   - タスク#2で策定された分割計画を適用
   - 原画・エンブレム → 原画 + エンブレム
   - アイテム・報酬 → アイテム + 報酬

### Phase 1の実行計画（優先度高）

**目標**: 最も使用頻度が高い3スキルを完成させる

**スキル**:
1. masterdata-from-bizops-gacha ✅（完了）
2. masterdata-from-bizops-hero
3. masterdata-from-bizops-mission

**実行手順**:
1. ガチャスキルの評価結果を収集
2. 評価結果をヒーロー・ミッションスキルの開発にフィードバック
3. ヒーローススキルの開発（2～3日）
   - 13テーブルと最も多いため、段階的な検証を実施
   - アビリティと攻撃の設定が複雑なため、入念なテスト
4. ミッションスキルの開発（1.5～2日）
   - 報酬との連携を重点的にテスト
5. Phase 1の統合テスト
   - 実際の運営仕様書で3スキルを実行
   - 品質確認とフィードバック収集

**Phase 1完了時の成果**:
- 日常的に発生するマスタデータ作成タスクの大半を自動化
- スキル開発のベストプラクティス確立

### Phase 2の実行計画（優先度中）

**目標**: イベント関連の4スキルを完成させる

**スキル**:
1. masterdata-from-bizops-quest-stage
2. masterdata-from-bizops-item
3. masterdata-from-bizops-reward
4. masterdata-from-bizops-event-basic

**実行手順**:
1. Phase 1の評価結果を反映
2. クエスト・ステージスキルの開発（2～2.5日）
   - 10テーブルと多いため、段階的な検証を実施
3. アイテムスキルの開発（0.5～1日）
   - 分割後の独立スキルとして開発
4. 報酬スキルの開発（0.5～1日）
   - アイテムスキルとの連携を重点的にテスト
5. イベント基本設定スキルの開発（1～1.5日）
6. Phase 2の統合テスト
   - アイテム・報酬の連携確認
   - イベント関連スキルの統合テスト

**Phase 2完了時の成果**:
- イベント開催時のマスタデータ作成がほぼ自動化
- 分割後のスキル（アイテム・報酬）のメンテナンス性向上

### Phase 3の実行計画（優先度低）

**目標**: 残り7スキルを完成させ、全79テーブルを100%カバー

**スキル**:
1. masterdata-from-bizops-advent-battle
2. masterdata-from-bizops-pvp
3. masterdata-from-bizops-shop-pack
4. masterdata-from-bizops-artwork
5. masterdata-from-bizops-emblem
6. masterdata-from-bizops-enemy-autoplayer
7. masterdata-from-bizops-ingame

**実行手順**:
1. Phase 1・2の評価結果を反映
2. 各スキルを順次開発
   - 降臨バトル（1.5～2日）
   - PVP（0.5～1日）
   - ショップ・パック（1.5～2日）
   - 原画（1～1.5日）
   - エンブレム（0.5～1日）
   - 敵・自動行動（1～1.5日）
   - インゲーム（1.5～2日）
3. Phase 3の統合テスト
   - 原画・エンブレムの独立性確認
   - 降臨バトルとエンブレムの連携確認
   - 全79テーブルの網羅性確認

**Phase 3完了時の成果**:
- **全79テーブルを100%カバー**
- あらゆる運営施策に対応可能
- マスタデータ作成の完全自動化

---

## スキル開発のベストプラクティス

### 1. スキルテンプレートの活用

タスク#1で策定されたスキル設計アーキテクチャに基づき、以下のテンプレートを活用します:

```
.claude/skills/masterdata-from-bizops-{機能名}/
├── SKILL.md                     # スキルファイル（統合版）
├── references/
│   └── manual.md                # 詳細手順書（既存の手順書を移植）
└── examples/
    └── sample-output.md         # サンプル出力
```

### 2. 既存手順書の最大限活用

既存の `{機能名}_マスタデータ設定手順書.md` を `references/manual.md` にほぼそのまま移植します。これにより:
- 開発工数を削減
- 既存の詳細ルールを保持
- Claude Codeが参照する際の精度向上

### 3. 推測値レポートの徹底

設計書に明記されていない値を推測で決定した場合、必ずユーザーに報告します:
- データ品質リスクの明確化
- ユーザーが確認・修正すべき箇所を明示
- 高品質なマスタデータ作成を保証

### 4. 段階的な検証

特にテーブル数が多いスキル（ヒーロー13テーブル、クエスト・ステージ10テーブル等）は、段階的な検証を実施します:
- 各テーブルごとの単体テスト
- テーブル間の整合性テスト
- 実際の運営仕様書での統合テスト

### 5. 既存スキルとの連携

作成したマスタデータは、既存の `masterdata-csv-validator` スキルで検証します:

```bash
# マスタデータ作成
/gacha-masterdata gacha_spec.xlsx --release-key 202601010 --series-id jig

# 検証
/masterdata-csv-validator OprGacha.csv
```

この2ステップのワークフローで、高品質なマスタデータを確実に作成できます。

---

## 期待される効果

### 1. 開発速度の向上

**現状**:
- 手動でのマスタデータ作成: 1イベントあたり数日～1週間
- 設計書の見落とし、採番ミス等のリスクが高い

**スキル導入後**:
- 自動作成: 1イベントあたり数時間
- 設計書の見落とし、採番ミスのリスクが大幅に低減
- **開発速度: 5～10倍向上**

### 2. データ品質の向上

**推測値レポートによる品質管理**:
- 推測値を明示的に報告
- ユーザーが確認・修正すべき箇所を明確化
- データ整合性チェックの自動化

**期待される効果**:
- データの正確性向上
- 手戻りの削減
- 運営施策の品質向上

### 3. 属人化の解消

**現状**:
- マスタデータ作成の属人化
- 新規メンバーの学習コストが高い

**スキル導入後**:
- スキルによる標準化
- 誰でも同じ品質でマスタデータを作成可能
- 新規メンバーの学習コストを大幅に削減

### 4. 運営施策の実施スピード向上

**Phase 1完了後**:
- ガチャ、ヒーロー、ミッションの自動化
- 日常的な運営施策の準備期間を大幅短縮

**Phase 2完了後**:
- イベント関連の自動化
- イベント開催時の準備期間を大幅短縮

**Phase 3完了後**:
- 全機能の自動化
- あらゆる運営施策に即座に対応可能

---

## まとめ

### 本ロードマップの特徴

1. **14スキルで全79テーブルを100%カバー**
   - 分割計画を反映し、元の12グループから14グループに拡張
   - 原画・エンブレム、アイテム・報酬を独立スキル化

2. **段階的な展開**
   - Phase 1（高頻度）: 3スキル、27テーブル
   - Phase 2（中頻度）: 4スキル、16テーブル
   - Phase 3（低頻度）: 7スキル、36テーブル

3. **品質重視**
   - ガチャスキル（タスク#3）の評価結果を反映
   - 推測値レポートによる品質管理
   - 段階的な検証による高品質保証

4. **効率的な開発**
   - スキルテンプレートの活用
   - 既存手順書の最大限活用
   - 複数名での並行開発が可能

### 次のステップ

1. **タスク#3（ガチャスキル）の評価**
   - 評価結果を収集
   - 他のスキルへのフィードバック内容を整理

2. **Phase 1の開発開始**
   - ヒーローススキルの開発
   - ミッションスキルの開発

3. **継続的な改善**
   - 各フェーズの評価結果を次フェーズに反映
   - スキルテンプレートの継続的な改善
   - ベストプラクティスの共有

---

**作成日**: 2026-02-10
**作成者**: skill-roadmap-planner (Claude Code Agent Team)
**関連タスク**: タスク#4
