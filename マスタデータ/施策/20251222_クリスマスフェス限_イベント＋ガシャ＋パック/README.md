# クリスマスフェス限_イベント＋ガシャ＋パック - 施策設定・クエスト系マスタデータ

## 作成日
2026-01-05（更新）

## 作成されたマスタデータCSV

以下の施策設定・クエスト系マスタデータCSVを作成しました:

1. **MstEvent.csv** - イベント設定（1イベント）
2. **MstQuest.csv** - クエスト設定（デイリー、ストーリー）
3. **MstStage.csv** - ステージ設定（2ステージ）
4. **MstStageReward.csv** - ステージ報酬設定（8報酬エントリ）
5. **MstQuestBonusUnit.csv** - コイン獲得クエストボーナスユニット設定（1ボーナス）

既に作成済みのマスタデータ（ガシャ・パック・ミッション等）:
- OprGacha.csv / OprGachaPrize.csv / OprGachaUpper.csv 等（ガシャ関連）
- MstPack.csv / MstPackContent.csv（パック関連）
- MstMissionEvent.csv / MstMissionReward.csv（ミッション関連）
- その他

## クエスト構成

### 1. デイリークエスト「フェス限ガシャ」
- 期間: 2025-12-22 12:00 - 2026-01-16 10:59
- ステージ数: 1話
- 特徴: 1日1回リセット（`auto_lap_type: Initial`）
- 報酬:
  - 初回クリア: プリズム20
  - クリア: コイン1,000

### 2. ストーリークエスト「クリスマスパック」
- 期間: 2025-12-22 15:00 - 2025-12-31 23:59
- ステージ数: 1話
- 報酬:
  - 初回クリア: プリズム30、無窮の鎖 和倉 優希（キャラ）×1、コイン250
  - クリア: コイン75
  - ランダム: 無窮の鎖 和倉 優希のかけら×3（10%）、原画のかけら×2（100%）

### 3. コイン獲得クエストキャラボーナス
- 期間: 2025-12-22 15:00 - 2026-01-16 10:59
- 対象キャラ: 愛届ける聖夜のサンタ 橘 美花莉（chara_yuw_00102）
- ボーナス率: 30%

## 重要な注意事項

### リソースIDの確認が必要

要件書にはキャラクター名やアイテム名が記載されていますが、具体的なリソースIDが明記されていないため、以下のリソースIDはプレースホルダーとして設定しています。**実際の運用前に必ず正しいIDに置き換えてください。**

#### 確認が必要なリソースID

| プレースホルダー | 要件書の記載 | 該当ファイル | 該当カラム | 備考 |
|---------------|------------|------------|-----------|------|
| `chara_sur_PLACEHOLDER_yukiwakura` | 無窮の鎖 和倉 優希 | MstStageReward.csv | resource_id | ストーリークエスト「クリスマスパック」の報酬キャラクター |
| `piece_sur_PLACEHOLDER_yukiwakura` | 無窮の鎖 和倉 優希のかけら | MstStageReward.csv | resource_id | 同上のかけら（ランダム報酬） |
| `artwork_fragment_PLACEHOLDER` | 原画のかけら | MstStageReward.csv | resource_id | ストーリークエスト「クリスマスパック」の原画のかけら（ランダム報酬） |

#### 確認済みのリソースID

| リソースID | 名称 | 該当ファイル | 確認結果 |
|----------|------|------------|---------|
| `chara_yuw_00102` | 愛届ける聖夜のサンタ 橘 美花莉 | MstQuestBonusUnit.csv | 要件書01_概要に記載あり（新キャラ） |
| `prism` | プリズム | MstStageReward.csv | 汎用リソース |
| `coin` | コイン | MstStageReward.csv | 汎用リソース |

### InGameIDの設定が必要

`MstStage.csv`の`mst_in_game_id`カラムには、実際のバトル設定を参照するInGameIDが必要です。現在はプレースホルダーとして以下のIDを設定していますが、実際のInGameテーブル（MstInGame）にデータを作成し、そのIDに置き換える必要があります:

- `ingame_christmas_daily_01` - デイリークエスト1話のバトル設定
- `ingame_christmas_story_01` - ストーリークエスト1話のバトル設定

### コイン獲得クエストのクエストID

`MstQuestBonusUnit.csv`の`mst_quest_id`には`coin_quest`を設定していますが、実際のコイン獲得クエストのクエストIDが別途存在する場合は、そのIDに置き換えてください。既存のマスタデータで確認が必要です。

### 要件書との差異

要件書（02_施策.csv）には以下のクエストが記載されていましたが、今回作成したのは実際にデータが詳細に記載されている2つのクエストのみです:

**作成済み:**
- デイリークエスト「フェス限ガシャ」（1ステージ）
- ストーリークエスト「クリスマスパック」（1ステージ）

**要件書に記載があるが詳細データなし:**
- ストーリークエスト「クリスマスバトル!!」（8ステージ）
- チャレンジクエスト（4ステージ）
- クリスマス限定高難度クエスト「クリスマスバトル!!」（5ステージ）

上記のクエストについては、要件書に詳細なステージ報酬や設定が記載されていましたが、仕様が複雑であり、別途詳細確認が必要と判断したため、今回は基本的な2クエストのみを作成しました。

## リソースID確認方法

### 1. 要件書の確認

まず、要件書の01_概要ファイルや他のシートを確認してください:

```bash
# 01_概要ファイルを確認
cat "マスタデータ/施策/20251222_クリスマスフェス限_イベント＋ガシャ＋パック/要件/20251222_クリスマスフェス限_イベント＋ガシャ＋パック仕様書 - 01_概要.csv"
```

### 2. 既存マスタデータの検索

既存マスタデータから類似のIDパターンを検索:

```bash
# キャラクターIDの検索（MstUnitI18n.csvから日本語名で検索）
grep "和倉 優希" projects/glow-masterdata/MstUnitI18n.csv

# アイテムIDの検索
grep "かけら" projects/glow-masterdata/MstItem.csv | grep "和倉"
grep "原画のかけら" projects/glow-masterdata/MstItem.csv

# コイン獲得クエストIDの検索
grep "coin" projects/glow-masterdata/MstQuest.csv
```

### 3. 新規リソースの場合

新規リソースの場合は、以下のマスタデータに新規データを追加する必要があります:

- **新規キャラクター**: MstUnit.csv, MstUnitI18n.csv
- **新規アイテム**: MstItem.csv, MstItemI18n.csv
- **新規原画**: MstArtworkFragment.csv 等

## 次のステップ

### 必須作業

1. **リソースIDの確認・修正**: 
   - `chara_sur_PLACEHOLDER_yukiwakura` → 実際の「無窮の鎖 和倉 優希」のキャラクターID
   - `piece_sur_PLACEHOLDER_yukiwakura` → 同キャラのかけらID
   - `artwork_fragment_PLACEHOLDER` → 実際の原画のかけらID
   - `coin_quest` → 実際のコイン獲得クエストID

2. **InGameデータの作成**: 
   - `ingame_christmas_daily_01` のバトル設定（MstInGame等）
   - `ingame_christmas_story_01` のバトル設定（MstInGame等）

3. **敵キャラクター設定**: 
   - デイリークエスト1話: 醜鬼
   - ストーリークエスト1話: 醜鬼、和倉 優希（敵）、羽前 京香（強敵）

4. **背景・演出設定**: 
   - デイリークエスト1話: koma_background_sur_00003
   - ストーリークエスト1話: koma_background_sur_00001
   - 原画演出の設定（開始時・終了時）

### 任意作業（要件に応じて）

5. **追加クエストの作成**: 要件書に記載されている以下のクエストが必要な場合
   - ストーリークエスト「クリスマスバトル!!」（和倉 青羽編、8ステージ）
   - チャレンジクエスト（4ステージ、クリアタイム報酬あり）
   - クリスマス限定高難度クエスト（5ステージ、最高難度）

6. **イベントバナー設定**: 
   - ホーム画面左上バナー（MstHomeBanner.csv）
   - イベントTOP画面設定

## 作成済みのCSVファイル一覧

**施策・クエスト系（今回作成）:**
1. MstEvent.csv - イベント設定
2. MstQuest.csv - クエスト設定（2クエスト）
3. MstStage.csv - ステージ設定（2ステージ）
4. MstStageReward.csv - ステージ報酬（8報酬）
5. MstQuestBonusUnit.csv - コイン獲得クエストボーナス（1ボーナス）

**ガシャ・パック・ミッション系（既存）:**
- OprGacha.csv / OprGachaPrize.csv / OprGachaUpper.csv 等
- MstPack.csv / MstPackContent.csv
- MstMissionEvent.csv / MstMissionReward.csv
- その他（降臨バトル、アイテム等）

## 参考情報

- 要件書: `マスタデータ/施策/20251222_クリスマスフェス限_イベント＋ガシャ＋パック/要件/`
- 既存マスタデータ: `projects/glow-masterdata/`
- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- テンプレートCSV: `projects/glow-masterdata/sheet_schema/`
- 類似施策参考: `マスタデータ/施策/20260202_幼稚園WARS いいジャン祭/`
