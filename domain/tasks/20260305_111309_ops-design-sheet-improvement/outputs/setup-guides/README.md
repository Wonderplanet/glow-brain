# マスタデータ設定手順書 インデックス

## 概要

リリースキー単位でマスタデータを投入する際の機能別手順書一覧。各手順書はテーブル設定順序・カラム設定ガイド・DuckDB クエリ・検証方法をまとめている。

---

## 手順書一覧

| ファイル | 機能 | 主要テーブル数 | フェーズ |
|---------|------|-------------|---------|
| [01_event.md](01_event.md) | イベント基本情報 | 7 テーブル | Phase 1 |
| [02_unit.md](02_unit.md) | キャラクター・ユニット追加 | 12 テーブル | Phase 1 |
| [03_gacha.md](03_gacha.md) | ガチャ実装 | 6 テーブル | Phase 1 |
| [04_quest-stage.md](04_quest-stage.md) | イベントクエスト・ステージ設定 | 10 テーブル | Phase 2 |
| [05_mission.md](05_mission.md) | イベントミッション | 8 テーブル | Phase 2 |
| [06_advent-battle.md](06_advent-battle.md) | 降臨バトル | 8 テーブル | Phase 2 |
| [07_artwork.md](07_artwork.md) | アートワーク・フラグメント | 6 テーブル | Phase 3 |
| [08_shop-pack.md](08_shop-pack.md) | パック・商品販売（ショップ） | 7 テーブル | Phase 3 |
| [09_ingame-battle.md](09_ingame-battle.md) | インゲーム・バトル設定 | 9 テーブル（スキル推奨） | Phase 4 |
| [10_exchange.md](10_exchange.md) | 交換所 | 5 テーブル（任意） | Phase 4 |
| [11_ui-banner.md](11_ui-banner.md) | UI・表示（ホームバナー等） | 3 テーブル | Phase 4 |

---

## 依存関係図

機能間の依存関係（→ は「先に設定が必要」を示す）

```
[MstSeries]（01_event）
    └─→ [MstEvent]（01_event）
              ├─→ [MstEventBonusUnit]（01_event）
              │         └─→ [MstAdventBattle]（06_advent-battle）
              │                   └─→ [MstMissionLimitedTerm]（05_mission）
              ├─→ [MstQuest]（04_quest-stage）
              │         └─→ [MstMissionEvent]（05_mission）
              └─→ [MstExchange]（10_exchange）

[MstUnit]（02_unit）
    ├─→ [OprGachaPrize]（03_gacha）
    ├─→ [MstEventBonusUnit]（01_event）
    └─→ [OprGachaDisplayUnitI18n]（03_gacha）

[MstInGame]（09_ingame-battle）
    ├─→ [MstStage]（04_quest-stage）
    └─→ [MstAdventBattle]（06_advent-battle）

[MstStage]（04_quest-stage）
    ├─→ [MstStageEventReward]（04_quest-stage）
    ├─→ [MstArtworkFragment.drop_group_id]（07_artwork）
    └─→ [MstMangaAnimation]（11_ui-banner）

[MstArtwork]（07_artwork）
    └─→ [MstArtworkFragment]（07_artwork）
              └─→ [MstArtworkFragmentPosition]（07_artwork）
```

**推奨設定順序（全機能がある場合）:**
1. MstSeries → MstEvent（01）
2. MstItem/MstUnit（02）
3. MstInGame 系（09 スキル実行）
4. MstQuest → MstStage（04）
5. OprGacha 系（03）
6. MstAdventBattle 系（06）
7. MstMissionEvent 系（05）
8. MstArtwork 系（07）
9. MstPack/OprProduct 系（08）
10. MstExchange 系（10、任意）
11. MstHomeBanner・MstMangaAnimation（11）

---

## 機能別テーブル詳細一覧

### 01_event: イベント基本情報

| テーブル | 役割 |
|---------|------|
| MstSeries | シリーズ定義（初回のみ） |
| MstSeriesI18n | シリーズ多言語名 |
| MstEvent | イベント本体 |
| MstEventI18n | イベント多言語名・吹き出し |
| MstEventBonusUnit | イベントボーナスユニット |
| MstEventDisplayUnit | クエスト別表示ユニット |
| MstEventDisplayUnitI18n | 表示ユニット多言語情報 |

### 02_unit: キャラクター・ユニット追加

| テーブル | 役割 |
|---------|------|
| MstItem | ユニットかけらアイテム |
| MstItemI18n | アイテム多言語名 |
| MstAbility | アビリティ種別（既存再利用可） |
| MstAbilityI18n | アビリティ多言語名 |
| MstUnitAbility | ユニット固有アビリティ |
| MstAttack | 通常攻撃・必殺ワザ性能 |
| MstAttackI18n | 通常攻撃多言語名 |
| MstSpecialAttackI18n | 必殺ワザ多言語名 |
| MstAttackElement | 攻撃属性 |
| MstUnit | ユニット本体 |
| MstUnitI18n | ユニット多言語名・説明 |
| MstSpeechBalloonI18n | ゲーム内吹き出しテキスト |

### 03_gacha: ガチャ実装

| テーブル | 役割 | gacha_type 例 |
|---------|------|-------------|
| OprGacha | ガチャ本体 | Pickup/Festival/PaidOnly/Ticket/Medal |
| OprGachaI18n | ガチャ多言語名・説明 | — |
| OprGachaPrize | 景品リスト | — |
| OprGachaUpper | 天井・上限設定 | — |
| OprGachaUseResource | 消費リソース | — |
| OprGachaDisplayUnitI18n | ガチャ画面表示ユニット説明 | — |

### 04_quest-stage: イベントクエスト・ステージ設定

| テーブル | 役割 |
|---------|------|
| MstQuest | クエスト定義 |
| MstQuestI18n | クエスト多言語名 |
| MstQuestBonusUnit | クエスト別ボーナスユニット |
| MstQuestEventBonusSchedule | イベントボーナス有効スケジュール |
| MstStage | ステージ定義 |
| MstStageI18n | ステージ多言語名 |
| MstStageEventReward | ステージドロップ報酬 |
| MstStageClearTimeReward | タイムアタック報酬 |
| MstStageEventSetting | ステージイベント設定 |
| MstStageEndCondition | ステージ終了条件（降臨系） |

### 05_mission: イベントミッション

| テーブル | 役割 |
|---------|------|
| MstMissionReward | 報酬グループ定義 |
| MstMissionEvent | イベントミッション本体 |
| MstMissionEventI18n | ミッション多言語説明 |
| MstMissionEventDependency | ミッション解放順序 |
| MstMissionEventDailyBonusSchedule | デイリーボーナス有効期間 |
| MstMissionEventDailyBonus | デイリーボーナス各日報酬 |
| MstMissionLimitedTerm | 期間限定ミッション |
| MstMissionLimitedTermI18n | 期間限定ミッション多言語説明 |

### 06_advent-battle: 降臨バトル

| テーブル | 役割 |
|---------|------|
| MstEmblem | エンブレム定義 |
| MstEmblemI18n | エンブレム多言語名 |
| MstAdventBattle | 降臨バトル本体 |
| MstAdventBattleI18n | 降臨バトル多言語名 |
| MstAdventBattleRewardGroup | 報酬グループ定義 |
| MstAdventBattleReward | 報酬詳細 |
| MstAdventBattleClearReward | クリア報酬 |
| MstAdventBattleRank | ランク定義 |

### 07_artwork: アートワーク・フラグメント

| テーブル | 役割 |
|---------|------|
| MstArtwork | アートワーク定義 |
| MstArtworkI18n | アートワーク多言語名・説明 |
| MstArtworkFragment | フラグメント定義（通常 16個/枚） |
| MstArtworkFragmentI18n | フラグメント多言語名 |
| MstArtworkFragmentPosition | フラグメント位置設定 |
| MstArtworkAcquisitionRoute | アートワーク入手ルート |

### 08_shop-pack: パック・商品販売

| テーブル | 役割 |
|---------|------|
| MstStoreProduct | ストア商品 ID（iOS/Android）定義 |
| MstStoreProductI18n | ストア商品多言語名 |
| MstPack | パック内容定義 |
| MstPackI18n | パック多言語名 |
| MstPackContent | パック内容物 |
| OprProduct | 販売情報（期間・優先度） |
| OprProductI18n | 販売情報多言語 |

### 09_ingame-battle: インゲーム・バトル設定（スキル推奨）

| テーブル | 役割 | 詳細ドキュメント |
|---------|------|---------------|
| MstInGame | インゲームステージ定義 | `table-docs/MstInGame.md` |
| MstInGameI18n | インゲーム多言語名 | — |
| MstAutoPlayerSequence | AI 敵出現シーケンス | `table-docs/MstAutoPlayerSequence.md` |
| MstEnemyStageParameter | 敵パラメータ定義 | `table-docs/MstEnemyStageParameter.md` |
| MstEnemyOutpost | 前哨戦定義 | `table-docs/MstEnemyOutpost.md` |
| MstEnemyCharacter | 敵キャラクター定義 | — |
| MstEnemyCharacterI18n | 敵キャラクター多言語名 | — |
| MstPage | ステージページ構成 | `table-docs/MstPage.md` |
| MstKomaLine | コマライン設定 | `table-docs/MstKomaLine.md` |

### 10_exchange: 交換所（任意）

| テーブル | 役割 |
|---------|------|
| MstExchange | 交換所定義 |
| MstExchangeI18n | 交換所多言語名 |
| MstExchangeLineup | 交換ラインナップ |
| MstExchangeCost | 交換コスト |
| MstExchangeReward | 交換報酬 |

### 11_ui-banner: UI・表示

| テーブル | 役割 |
|---------|------|
| MstHomeBanner | ホーム画面バナー |
| MstMangaAnimation | 漫画アニメーション |
| MstSpeechBalloonI18n | インゲーム吹き出しテキスト |

---

## 利用可能スキル対応表

| スキル名 | 対応機能 | 主な用途 |
|---------|---------|--------|
| `masterdata-explorer` | 全機能 | DBスキーマ確認・DuckDB クエリ実行 |
| `masterdata-csv-validator` | 全機能 | CSV 検証・整合性チェック |
| `masterdata-ingame-creator` | 09_ingame-battle | インゲームデータ自動生成 |
| `masterdata-ingame-verifier` | 09_ingame-battle | インゲームデータ品質検証 |
| `masterdata-id-numbering` | 全機能（ID採番） | ID 採番ルール確認・生成 |
| `masterdata-releasekey-reporter` | 全機能 | リリースキー別データ抽出 |
| `masterdata-csv-to-xlsx` | 全機能 | CSV → XLSX 変換 |

---

## 参照リソース

| リソース | パス |
|---------|------|
| DBスキーマ | `projects/glow-server/api/database/schema/exports/master_tables_schema.json` |
| 過去リリース（標準） | `domain/raw-data/masterdata/released/202602015/` |
| 過去リリース（交換所あり） | `domain/raw-data/masterdata/released/202512020/` |
| インゲームテーブル詳細 | `domain/knowledge/masterdata/table-docs/` |
| インゲーム実装例 34 件 | `domain/knowledge/masterdata/in-game/guides/` |
| ID 採番ルール | `domain/knowledge/masterdata/ID割り振りルール.csv` |
