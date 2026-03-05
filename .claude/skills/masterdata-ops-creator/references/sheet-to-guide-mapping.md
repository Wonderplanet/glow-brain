# スプシシート → 手順書 → テーブル マッピング表

スプシCSVファイルと、参照すべき手順書、生成するテーブルの対応関係を整理したリファレンス。

---

## マッピング全体像

| 設計書ファイルパターン | 機能 | 参照手順書 | 生成テーブル |
|-------------------|------|----------|------------|
| `01_概要.csv`, `02_施策.csv` | イベント基本情報 | `01_event.md` | MstSeries, MstEvent, MstEventI18n, MstEventBonusUnit 等 |
| `@*設計書.csv`, `06_ガシャ基本仕様.csv` | ガチャ | `03_gacha.md` | OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n |
| `03_降臨バトル.csv` | 降臨バトル | `06_advent-battle.md` | MstEmblem, MstAdventBattle, MstAdventBattleRewardGroup, MstAdventBattleReward, MstAdventBattleClearReward, MstAdventBattleRank |
| `04_ミッション.csv`, `05_報酬一覧.csv` | ミッション | `05_mission.md` | MstMissionReward, MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionLimitedTerm |
| `07_*.csv`, `08_*.csv` | ショップ・パック | `08_shop-pack.md` | MstStoreProduct, MstPack, MstPackContent, OprProduct, OprProductI18n |
| `*交換所*.csv` | 交換所 | `10_exchange.md` | MstExchange, MstExchangeI18n, MstExchangeLineup, MstExchangeCost, MstExchangeReward |
| バナー関連の記載 | UIバナー | `11_ui-banner.md` | MstHomeBanner, MstMangaAnimation, MstSpeechBalloonI18n |

---

## 機能別詳細マッピング

### 01_event: イベント基本情報

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/01_event.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `01_概要.csv` | イベント名・期間・シリーズ情報・ボーナスユニット | MstSeries, MstSeriesI18n, MstEvent, MstEventI18n |
| `02_施策.csv` | イベント種別・表示設定 | MstEventBonusUnit, MstEventDisplayUnit |
| `01_概要.csv`（ボーナスユニット欄） | ボーナスユニット一覧 | MstEventBonusUnit, MstEventDisplayUnit, MstEventDisplayUnitI18n |

**必須確認項目:**
- イベント期間（start_at / end_at）
- シリーズ名（既存 or 新規）
- ボーナスユニット一覧

---

### 03_gacha: ガチャ実装

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/03_gacha.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `@*ガシャ_設計書.csv` | ガチャID・名称・種別・開催期間・景品リスト | OprGacha, OprGachaI18n, OprGachaPrize |
| `06_ガシャ基本仕様.csv` | 消費リソース（石・チケット）・天井設定・確率 | OprGachaUseResource, OprGachaUpper |
| `@*設計書.csv` | ガチャ画面表示ユニット | OprGachaDisplayUnitI18n |

**必須確認項目:**
- gacha_type（Pickup / Festival / PaidOnly / Ticket / Medal）
- 開催期間
- 消費リソース種別と消費数
- 天井設定（上限回数・天井報酬）
- 景品リスト（ユニットID・レアリティ・排出確率）

**ガチャ種別ごとの設定パターン:**

| gacha_type | 特徴 | OprGachaUpper 設定 |
|-----------|------|------------------|
| Pickup | ピックアップ通常ガシャ | 天井あり（200回等） |
| Festival | フェス限定ガシャ | 天井あり |
| PaidOnly | 有料限定ガシャ | 天井なしも可 |
| Ticket | チケットガシャ | 上限回数あり |
| Medal | メダル交換ガシャ | 固定交換数 |

---

### 06_advent-battle: 降臨バトル

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/06_advent-battle.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `03_降臨バトル.csv` | 降臨バトル名称・期間・難易度・エンブレム | MstEmblem, MstEmblemI18n, MstAdventBattle, MstAdventBattleI18n |
| `03_降臨バトル.csv`（報酬欄） | 撃破報酬・クリア報酬・ランク報酬 | MstAdventBattleRewardGroup, MstAdventBattleReward, MstAdventBattleClearReward, MstAdventBattleRank |

**必須確認項目:**
- 降臨バトル名称（日本語・英語）
- 開催期間
- エンブレム設定（新規 or 既存）
- 難易度ランク数と各ランクの報酬

---

### 05_mission: イベントミッション

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/05_mission.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `04_ミッション.csv` | ミッション条件・解放順序・報酬グループ | MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency |
| `05_報酬一覧.csv` | 報酬グループ詳細（アイテム・数量） | MstMissionReward |
| `04_ミッション.csv`（期間限定欄） | 期間限定ミッション | MstMissionLimitedTerm, MstMissionLimitedTermI18n |
| `04_ミッション.csv`（デイリー欄） | デイリーボーナス設定 | MstMissionEventDailyBonusSchedule, MstMissionEventDailyBonus |

**必須確認項目:**
- ミッション一覧（条件・達成数・報酬）
- 解放順序（前提ミッションID）
- 期間限定ミッションの有無と期間

---

### 08_shop-pack: ショップ・パック販売

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/08_shop-pack.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `07_ショップ_要件書.csv` | ショップ全体要件・販売期間 | OprProduct, OprProductI18n |
| `07_*設計書.csv` | パック内容（アイテム・数量・価格） | MstPack, MstPackI18n, MstPackContent |
| `08_*.csv` | ストア商品ID（iOS/Android） | MstStoreProduct, MstStoreProductI18n |

**必須確認項目:**
- パック種別（通常販売 / 初回限定 / セット販売）
- 販売期間
- 価格（円）とストア商品ID
- 内容物（アイテム種別・数量・おまけ）

---

### 10_exchange: 交換所（任意）

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/10_exchange.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `*交換所*.csv` または `01_概要.csv` の交換所欄 | 交換所名称・有効期間・ラインナップ | MstExchange, MstExchangeI18n, MstExchangeLineup |
| 同上（コスト・報酬欄） | 交換コスト・報酬 | MstExchangeCost, MstExchangeReward |

**含まれる判定:**
- `01_概要.csv` または `02_施策.csv` に「交換所」という記載がある
- フォルダに `*交換所*.csv` が存在する

---

### 11_ui-banner: UIバナー

**参照手順書**: `domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/11_ui-banner.md`

| 設計書シート | 参照する情報 | 生成テーブル |
|------------|-----------|------------|
| `01_概要.csv`（バナー欄） | ホームバナー画像・表示期間・遷移先 | MstHomeBanner |
| 漫画アニメーション設定 | 漫画アニメーションID・表示設定 | MstMangaAnimation |
| キャラクター吹き出しテキスト | 吹き出しテキスト（多言語） | MstSpeechBalloonI18n |

**バナーは必ずほぼすべてのリリースで存在する。**
`01_概要.csv` のバナー欄から情報を取得する。

---

## 機能が含まれるかの判定チェックリスト

リリースに含まれる機能を判定するための確認リスト:

```
□ イベント基本情報  → 01_概要.csv または 02_施策.csv が存在するか
□ ガチャ           → @*.csv または 06_ガシャ基本仕様.csv が存在するか
□ 降臨バトル       → 03_降臨バトル.csv が存在するか
□ ミッション       → 04_ミッション.csv が存在するか
□ ショップ・パック  → 07_*.csv または 08_*.csv が存在するか
□ 交換所           → *交換所*.csv が存在するか、または概要CSVに「交換所」の記載があるか
□ UIバナー         → 01_概要.csv に「バナー」の記載があるか（ほぼ必ず存在）
```

---

## 手順書のパス一覧

```
domain/tasks/20260305_111309_ops-design-sheet-improvement/outputs/setup-guides/
├── README.md               # インデックス・依存関係図
├── 01_event.md             # イベント基本情報
├── 02_unit.md              # キャラクター・ユニット（スコープ外）
├── 03_gacha.md             # ガチャ実装
├── 04_quest-stage.md       # クエスト・ステージ（スコープ外）
├── 05_mission.md           # ミッション
├── 06_advent-battle.md     # 降臨バトル
├── 07_artwork.md           # アートワーク（スコープ外）
├── 08_shop-pack.md         # ショップ・パック
├── 09_ingame-battle.md     # インゲーム（masterdata-ingame-creator に委譲）
├── 10_exchange.md          # 交換所（任意）
└── 11_ui-banner.md         # UIバナー
```

**スコープ外手順書（`masterdata-ops-creator` では参照しない）:**
- `02_unit.md` — 新規ユニット追加（別途手動）
- `04_quest-stage.md` — クエスト・ステージ設計（別途手動）
- `07_artwork.md` — アートワーク（別途手動）
- `09_ingame-battle.md` — インゲーム設定（`masterdata-ingame-creator` が担当）
