# クリスマスフェス限_イベント＋ガシャ＋パック - 施策設定・クエスト系マスタデータ

## 作成日
2025-12-29

## 作成されたマスタデータCSV

以下の施策設定・クエスト系マスタデータCSVを作成しました:

1. **MstEvent.csv** - イベント設定
2. **MstQuest.csv** - クエスト設定（デイリー、ストーリー×2、チャレンジ、高難度）
3. **MstStage.csv** - ステージ設定（全26ステージ）
4. **MstStageReward.csv** - ステージ報酬設定
5. **MstStageClearTimeReward.csv** - クリアタイム報酬設定
6. **MstQuestBonusUnit.csv** - コイン獲得クエストボーナスユニット設定

## クエスト構成

### 1. デイリークエスト「フェス限ガシャ」
- 期間: 2025-12-22 12:00 - 2026-01-16 10:59
- ステージ数: 1話
- 特徴: 1日1回リセット

### 2. ストーリークエスト「クリスマスパック」
- 期間: 2025-12-22 15:00 - 2025-12-31 23:59
- ステージ数: 8話
- 報酬: 無窮の鎖 和倉 優希関連

### 3. ストーリークエスト「クリスマスバトル!!」
- 期間: 2025-12-22 15:00 - 2025-12-31 23:59
- ステージ数: 8話
- 報酬: 和倉 青羽関連

### 4. チャレンジクエスト
- 期間: 2025-12-22 15:00 - 2025-12-31 23:59
- ステージ数: 4話
- 特徴: クリアタイム報酬あり

### 5. クリスマス限定高難度クエスト「クリスマスバトル!!」
- 期間: 2025-12-22 15:00 - 2025-12-31 23:59
- ステージ数: 5話（1話、2話、3話、高難度1話、高難度2話）
- 特徴: クリアタイム報酬あり、高難度設定

## 重要な注意事項

### リソースIDの確認が必要

要件書にはキャラクター名やアイテム名が記載されていますが、具体的なリソースIDが明記されていないため、以下のリソースIDはプレースホルダーとして設定しています。**実際の運用前に必ず正しいIDに置き換えてください。**

#### 確認が必要なリソースID

| プレースホルダー | 要件書の記載 | 該当テーブル | 備考 |
|---------------|------------|------------|------|
| `chara_sur_XXXXX` | 無窮の鎖 和倉 優希 | MstStageReward | ストーリークエスト「クリスマスパック」の報酬キャラクター |
| `piece_sur_XXXXX` | 無窮の鎖 和倉 優希のかけら | MstStageReward | 同上のかけら |
| `memory_sur_XXXXX` | 無窮の鎖 和倉 優希のメモリー | MstStageReward | 同上のメモリー |
| `artwork_fragment_XXXXX` | 原画のかけら | MstStageReward | ストーリークエスト「クリスマスパック」の原画のかけら |
| `chara_sur_YYYYY` | 和倉 青羽 | MstStageReward | ストーリークエスト「クリスマスバトル!!」の報酬キャラクター |
| `piece_sur_YYYYY` | 和倉 青羽のかけら | MstStageReward | 同上のかけら |
| `colormemory_sur_YYYYY` | 和倉 青羽のカラーメモリー | MstStageReward | 同上のカラーメモリー |
| `artwork_fragment_YYYYY` | 原画のかけら | MstStageReward | ストーリークエスト「クリスマスバトル!!」の原画のかけら |
| `piece_sur_ZZZZZ` | 東 八千穂のかけら | MstStageReward | チャレンジクエスト2話の報酬 |
| `chara_sur_ZZZZZ` | 誇り高き魔都の剣姫 羽前 京香 | MstStageReward | チャレンジクエスト3話の報酬 |
| `chara_sur_ZZZZY` | 空間を操る六番組組長 出雲 天花 | MstStageReward | チャレンジクエスト4話の報酬 |
| `emblem_sur_AAAAA` | エンブレム「イコラ」 | MstStageReward | 高難度クエスト1話の報酬 |
| `emblem_sur_AAAAB` | エンブレム「ラスタロッテ」 | MstStageReward | 高難度クエスト2話の報酬 |

#### 確認済みのリソースID

| リソースID | 名称 | 該当テーブル | 確認結果 |
|----------|------|------------|---------|
| `chara_yuw_00102` | 愛届ける聖夜のサンタ 橘 美花莉 | MstQuestBonusUnit | 既存マスタデータに存在 |
| `item_memoryfragment_beginner` | メモリーフラグメント・初級 | MstStageReward | 汎用アイテム（推測） |
| `item_memoryfragment_intermediate` | メモリーフラグメント・中級 | MstStageReward | 汎用アイテム（推測） |
| `item_memoryfragment_advanced` | メモリーフラグメント・上級 | MstStageReward | 汎用アイテム（推測） |
| `ticket_pickup_gacha` | ピックアップガシャチケット | MstStageReward | 汎用アイテム（推測） |
| `ticket_special_gacha` | スペシャルガシャチケット | MstStageReward | 汎用アイテム（推測） |

### InGameIDの設定が必要

`MstStage.csv`の`mst_in_game_id`カラムには、実際のバトル設定を参照するInGameIDが必要です。現在はプレースホルダーとして`ingame_sur_christmas_XXXX_XX`形式で設定していますが、実際のInGameテーブル（MstInGame）にデータを作成し、そのIDに置き換える必要があります。

### コイン獲得クエストのクエストID

`MstQuestBonusUnit.csv`の`mst_quest_id`には`coin_quest`を設定していますが、実際のコイン獲得クエストのクエストIDが別途存在する場合は、そのIDに置き換えてください。

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
# キャラクターIDの検索
grep "和倉\|橘\|羽前" projects/glow-masterdata/MstUnitI18n.csv

# アイテムIDの検索
grep "かけら\|メモリー\|エンブレム" projects/glow-masterdata/MstItem.csv
```

### 3. 新規リソースの場合

新規リソースの場合は、キャラクター・アイテムマスタデータ（MstUnit.csv、MstItem.csv等）に新規データを追加する必要があります。

## 次のステップ

1. **リソースIDの確認・修正**: 上記のプレースホルダーを実際のIDに置き換える
2. **InGameデータの作成**: バトル設定（MstInGame等）を作成し、IDを`MstStage.csv`に反映
3. **敵キャラクター設定**: 要件書に記載されている敵キャラクター（醜鬼、強敵キャラ等）の設定
4. **背景・演出設定**: 要件書に記載されている背景ID、原画演出の設定
5. **特別ルール設定**: チャレンジクエストや高難度クエストの特別ルール設定（MstInGameSpecialRule等）
6. **ミッション設定**: 要件書の04_ミッションに基づくミッションマスタデータの作成

## 参考情報

- 要件書: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/マスタデータ/施策/20251222_クリスマスフェス限_イベント＋ガシャ＋パック/要件/`
- 既存マスタデータ: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-masterdata/`
- DBスキーマ: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- テンプレートCSV: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-masterdata/sheet_schema/`
