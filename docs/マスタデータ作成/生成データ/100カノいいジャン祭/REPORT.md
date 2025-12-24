# マスタデータ生成レポート

## 要件概要

**イベント名**: 君のことが大大大大大好きな100人の彼女 いいジャン祭  
**イベントタイプ**: コラボイベント（降臨バトル型）  
**開催期間**: 2026年2月16日 15:00 〜 2026年3月16日 10:59（28日間）  
**シリーズID**: kim（100カノ）

## 生成日時

2024年12月24日

## 生成データ一覧

### MstEvent.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_series_id, start_at, end_at, asset_key
- **データ概要**: イベント基本設定（event_kim_00001）

### MstEventI18n.csv
- **レコード数**: 1件
- **主要カラム**: mst_event_id, language, name, balloon
- **データ概要**: イベント名称の多言語設定（日本語）

### MstEventBonusUnit.csv
- **レコード数**: 4件
- **主要カラム**: mst_unit_id, bonus_percentage, event_bonus_group_id
- **データ概要**: イベントボーナスキャラクター設定
  - 花園 羽々里 (chara_kim_00001): 20%
  - 花園 羽香里 (chara_kim_00101): 10%
  - 院田 唐音 (chara_kim_00201): 10%
  - 好本 静 (chara_kim_00301): 10%

### MstAdventBattle.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_event_id, asset_key, event_bonus_group_id
- **データ概要**: 降臨バトル設定（quest_raid_kim1_00001）
  - バトルタイプ: ScoreChallenge
  - 挑戦回数: 3回/日
  - 広告挑戦: 2回/日

### MstMissionEvent.csv
- **レコード数**: 39件
- **主要カラム**: id, criterion_type, criterion_count, mst_mission_reward_group_id
- **データ概要**: イベントミッション設定
  - 強敵撃破ミッション: 22件（1体〜100体）
  - クエストクリアミッション: 4件
  - 通常敵撃破ミッション: 13件（10体〜300体）

### MstMissionReward.csv
- **レコード数**: 39件
- **主要カラム**: group_id, resource_type, resource_id, resource_amount
- **データ概要**: ミッション報酬設定
  - プリズム合計: 100個
  - コイン合計: 50,000
  - ガチャチケット: ピックアップ×5、スペシャル×9、SSR確定×1
  - キャラかけら: 各キャラ10個ずつ
  - メモリーフラグメント: 初級×30、中級×20、上級×3

### MstExchange.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_event_id, exchange_trade_type, lineup_group_id
- **データ概要**: 100カノ交換所の基本設定

### MstExchangeLineup.csv
- **レコード数**: 10件
- **主要カラム**: group_id, tradable_count, display_order
- **データ概要**: 交換所ラインナップ設定

### MstExchangeCost.csv
- **レコード数**: 10件
- **主要カラム**: mst_exchange_lineup_id, cost_type, cost_amount
- **データ概要**: 交換コスト設定（赤メダル使用）

### MstExchangeReward.csv
- **レコード数**: 10件
- **主要カラム**: mst_exchange_lineup_id, resource_type, resource_id, resource_amount
- **データ概要**: 交換報酬設定
  - 100カノガチャチケット×2
  - プリズム×700（14回×50）
  - イベントSRキャラ（chara_kim_00401）×1
  - キャラかけら、メモリーフラグメント等

## データ設計の詳細

### ID範囲

- **イベントID**: event_kim_00001
- **ミッションID**: event_kim_00001_1 〜 event_kim_00001_39
- **報酬ID**: mission_reward_kim_001 〜 mission_reward_kim_039
- **交換所ID**: event_kim_00001_01
- **交換ラインナップID**: event_kim_00001_01_lineup_00001 〜 00010
- **降臨バトルID**: quest_raid_kim1_00001
- **ボーナスユニットID**: 62 〜 65（連番）

### 命名規則

- **イベントIDパターン**: `event_{series_id}_{連番5桁}`
  - 例: event_kim_00001
- **asset_keyパターン**: `{series_id}_{連番5桁}`
  - 例: kim_00001
- **ミッションIDパターン**: `event_{series_id}_{event_num}_{mission_num}`
  - 例: event_kim_00001_1
- **報酬グループIDパターン**: `{series_id}_{event_num}_event_reward_{連番2桁}`
  - 例: kim_00001_event_reward_01
- **交換所グループIDパターン**: `event_{series_id}_{event_num}_{連番2桁}_lineup`
  - 例: event_kim_00001_01_lineup

### 参照した既存データ

- **MstEvent.csv**: イベント基本設定の構造とパターン参照
- **MstEventBonusUnit.csv**: ボーナスユニット設定のID採番とフォーマット参照
- **MstAdventBattle.csv**: 降臨バトルの設定値と項目参照
- **MstMissionEvent.csv**: ミッション設定のcriterion_type値とパターン参照
- **MstMissionReward.csv**: 報酬設定のresource_type値とパターン参照
- **MstExchange系**: 交換所の設定構造とコスト・報酬の関連参照
- **OprGacha.csv**: ガチャ設定の参考（別途ガチャマスタが必要）

### release_key

- **202602010**: 2026年2月第1リリース
  - イベント開始日（2026-02-16）に基づく

## データ整合性チェック

- [x] IDの重複がないことを確認
  - 既存イベントID（event_jig_00001まで）と重複なし
  - ミッションID、報酬IDは当イベント固有の命名
- [x] 必須カラムがすべて埋まっている
  - ENABLE列、id、release_key等の必須項目は全件設定済み
- [x] 日時形式が正しい
  - `YYYY-MM-DD HH:MM:SS` 形式で統一
- [x] 外部キー制約を満たしている
  - mst_event_id、mst_mission_reward_group_id等の関連は整合
- [x] 命名規則に準拠している
  - 既存パターンに従った命名を実施

## 追加で必要となるマスタデータ

以下のマスタデータは、本イベントを完全に実装するために別途作成が必要です：

### 1. ガチャ関連
- **OprGacha.csv**: ピックアップガチャA/B（Pickup_kim_001, Pickup_kim_002）
- **OprGachaI18n.csv**: ガチャ名称の多言語設定
- **OprGachaPrize.csv**: ガチャ排出設定（ピックアップキャラ含む）
- **OprGachaUpper.csv**: 天井設定（100回で確定）

### 2. キャラクター関連
- **MstUnit.csv**: 新規キャラクター4体の基本設定
  - chara_kim_00001（花園 羽々里・フェス限）
  - chara_kim_00101（花園 羽香里・ピックアップUR）
  - chara_kim_00201（院田 唐音・ピックアップUR）
  - chara_kim_00301（好本 静・ピックアップSSR）
  - chara_kim_00401（交換所報酬SR）

### 3. ステージ関連
- **MstStage.csv**: イベントクエストステージ設定
  - quest_event_kim1_charaget01（彼女ストーリー）
  - quest_event_kim1_collection01（収集クエスト）
  - quest_event_kim1_challenge01（チャレンジ）
  - quest_event_kim1_savage（高難易度）
- **MstStageEventSetting.csv**: イベントステージの期間設定
- **MstInGame.csv**: 降臨バトルのインゲーム設定（raid_kim1_00001）

### 4. ショップパック関連
- **OprProduct.csv**: いいジャン祭パックの商品設定
- **OprProductI18n.csv**: パック名称の多言語設定
- **MstStoreProduct.csv**: ストア商品基本設定

### 5. アイテム関連
- **MstItem.csv**: イベント固有アイテム
  - item_glo_00001（赤メダル）
  - ticket_kim_10000（100カノガチャチケット）
  - その他イベント報酬アイテム

### 6. バナー・UI関連
- **MstBanner.csv**: ホーム画面バナー設定
- **MstEventDisplayUnit.csv**: イベント表示ユニット設定

## 使用方法

### 1. 生成されたCSVファイルの配置

```bash
# glow-masterdataリポジトリへコピー
cp docs/マスタデータ作成/生成データ/100カノいいジャン祭/*.csv \
   path/to/glow-masterdata/
```

### 2. サーバー側でのマスタデータインポート

```bash
# マスタデータをDBにインポート
cd path/to/glow-server/api
php artisan master:import
```

### 3. クライアント側での確認

- Unity Editorで該当イベントが正しく表示されるか確認
- イベントTOP画面、ミッション画面、交換所画面の動作確認

### 4. 動作確認項目

- [ ] イベント期間中にイベントアイコンが表示される
- [ ] 降臨バトルが挑戦可能（1日3回+広告2回）
- [ ] ボーナスキャラクターの倍率が正しく適用される
- [ ] ミッションが達成可能で報酬が獲得できる
- [ ] 交換所で赤メダルを使用してアイテム交換ができる
- [ ] ガチャでピックアップキャラが排出される（別途ガチャマスタ要）

## 備考

### 仕様書との対応

本マスタデータは以下の仕様書HTMLファイルに基づいて生成されました：

- `02_施策.html`: イベント基本設定、ボーナスキャラ、期間
- `03_降臨バトル.html`: 降臨バトルの詳細設定
- `04_ミッション.html`: ミッション達成条件と報酬
- `05_報酬一覧.html`: 報酬合計値の確認
- `100カノ交換所.html`: 交換所の設定

### データ生成時の想定

以下の項目は仕様書に明記がないため、既存イベントのパターンを参考に設定しました：

1. **降臨バトルの開催期間**: イベント期間の後半1週間（2026-02-23 〜 2026-03-02）と想定
2. **イベントSRキャラID**: chara_kim_00401（交換所報酬用の架空ID）
3. **赤メダルのアイテムID**: item_glo_00001（既存の汎用イベントアイテム）
4. **ガチャチケットID**: ticket_kim_10000（100カノ専用チケット・架空ID）

実際の仕様に合わせて、これらの値は調整が必要な場合があります。

### 注意事項

- **キャラクターデータ**: chara_kim_*のキャラクターマスタは別途作成が必要
- **ステージデータ**: quest_event_kim1_*のステージマスタは別途作成が必要
- **アセットキー**: 各asset_keyに対応するアセット（画像、音声等）は別途用意が必要
- **ガチャ設定**: ピックアップガチャA/Bの詳細な排出率設定は別途OprGacha系マスタで設定が必要
- **I18n対応**: 英語等の多言語対応が必要な場合は、各I18nファイルにレコード追加が必要

### 今後の作業

1. 不足しているマスタデータ（ガチャ、キャラクター、ステージ等）の作成
2. アセットキーに対応するリソースファイルの準備
3. テスト環境でのマスタデータ投入と動作確認
4. QA環境、本番環境へのデプロイ

---

**作成者**: GitHub Copilot (Claude Sonnet 4.5)  
**作成日**: 2024年12月24日  
**バージョン**: 1.0
