# 全体ワークフロー実行例

## 概要

このドキュメントは、`masterdata-from-bizops-all` 統合スキルの実行例を示します。

## 実行シナリオ

**リリース**: 202601010（地獄楽 いいジャン祭イベント）

**運営仕様書**:
- ガチャ設計書_地獄楽_いいジャン祭.xlsx
- ヒーロー設計書_地獄楽_新キャラ.xlsx
- ミッション設計書_地獄楽_イベントミッション.xlsx
- クエスト設計書_地獄楽_メインストーリー.xlsx
- アイテム設計書_地獄楽_新アイテム.xlsx
- イベント設計書_地獄楽_いいジャン祭.xlsx
- ショップ設計書_地獄楽_期間限定パック.xlsx
- 降臨バトル設計書_地獄楽_ボス討伐.xlsx

## 実行プロンプト

```
運営仕様書全体からマスタデータを一括作成してください。

添付ファイル:
- ガチャ設計書_地獄楽_いいジャン祭.xlsx
- ヒーロー設計書_地獄楽_新キャラ.xlsx
- ミッション設計書_地獄楽_イベントミッション.xlsx
- クエスト設計書_地獄楽_メインストーリー.xlsx
- アイテム設計書_地獄楽_新アイテム.xlsx
- イベント設計書_地獄楽_いいジャン祭.xlsx
- ショップ設計書_地獄楽_期間限定パック.xlsx
- 降臨バトル設計書_地獄楽_ボス討伐.xlsx

パラメータ:
- release_key: 202601010
```

## 実行フロー

### Step 1: 運営仕様書の解析

```
[解析開始]

ファイル名から機能カテゴリを推定中...
- ガチャ設計書_地獄楽_いいジャン祭.xlsx → gacha
- ヒーロー設計書_地獄楽_新キャラ.xlsx → hero
- ミッション設計書_地獄楽_イベントミッション.xlsx → mission
- クエスト設計書_地獄楽_メインストーリー.xlsx → quest-stage
- アイテム設計書_地獄楽_新アイテム.xlsx → item
- イベント設計書_地獄楽_いいジャン祭.xlsx → event-basic
- ショップ設計書_地獄楽_期間限定パック.xlsx → shop-pack
- 降臨バトル設計書_地獄楽_ボス討伐.xlsx → advent-battle

[解析完了] 8個の機能を特定しました
```

### Step 2: 必要な機能の特定

```
[機能特定]

特定された機能スキル:
1. masterdata-from-bizops-gacha
2. masterdata-from-bizops-hero
3. masterdata-from-bizops-mission
4. masterdata-from-bizops-quest-stage
5. masterdata-from-bizops-item
6. masterdata-from-bizops-event-basic
7. masterdata-from-bizops-shop-pack
8. masterdata-from-bizops-advent-battle

合計: 8個の機能スキル
```

### Step 3: 依存関係の解析

```
[依存関係解析]

依存関係マップ:
- item: [] (依存なし)
- hero: [] (依存なし)
- event-basic: [] (依存なし)
- gacha: [hero]
- quest-stage: [hero]
- mission: [item, hero] ← rewardスキルが必要
- shop-pack: [item]
- advent-battle: [item, hero, event-basic] ← rewardスキルが必要

注意: missionとadvent-battleは報酬設定を含むため、rewardスキルも必要です
追加機能スキル: masterdata-from-bizops-reward
```

### Step 4: 実行順序の決定

```
[実行順序決定]

トポロジカルソートの結果:
1. item (依存なし)
2. hero (依存なし)
3. event-basic (依存なし)
4. reward (item, heroに依存)
5. gacha (heroに依存)
6. quest-stage (heroに依存)
7. shop-pack (itemに依存)
8. mission (rewardに依存)
9. advent-battle (reward, event-basicに依存)

実行順序が決定しました
```

### Step 5: 各機能スキルの順次実行

#### 5.1 アイテムスキル実行

```
[1/9] masterdata-from-bizops-item 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: アイテム設計書_地獄楽_新アイテム.xlsx

実行結果:
✅ 成功
- 作成テーブル: 2個 (MstItem, MstItemI18n)
- 作成レコード: 5件
- 推測値: 2件 (High: 0, Medium: 1, Low: 1)

[1/9] masterdata-from-bizops-item 完了
```

#### 5.2 ヒーロースキル実行

```
[2/9] masterdata-from-bizops-hero 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ヒーロー設計書_地獄楽_新キャラ.xlsx

実行結果:
✅ 成功
- 作成テーブル: 13個 (MstUnit, MstUnitI18n, MstUnitAbility, ...)
- 作成レコード: 234件
- 推測値: 15件 (High: 3, Medium: 8, Low: 4)

[2/9] masterdata-from-bizops-hero 完了
```

#### 5.3 イベント基本設定スキル実行

```
[3/9] masterdata-from-bizops-event-basic 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: イベント設計書_地獄楽_いいジャン祭.xlsx

実行結果:
✅ 成功
- 作成テーブル: 3個 (MstEvent, MstEventI18n, MstHomeBanner)
- 作成レコード: 12件
- 推測値: 5件 (High: 1, Medium: 2, Low: 2)

[3/9] masterdata-from-bizops-event-basic 完了
```

#### 5.4 報酬スキル実行（汎用）

```
[4/9] masterdata-from-bizops-reward 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ミッション設計書、降臨バトル設計書から報酬部分を抽出

実行結果:
✅ 成功
- 作成テーブル: 2個 (MstMissionReward, MstAdventBattleReward)
- 作成レコード: 89件
- 推測値: 8件 (High: 2, Medium: 4, Low: 2)

[4/9] masterdata-from-bizops-reward 完了
```

#### 5.5 ガチャスキル実行

```
[5/9] masterdata-from-bizops-gacha 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ガチャ設計書_地獄楽_いいジャン祭.xlsx

実行結果:
✅ 成功
- 作成テーブル: 6個 (OprGacha, OprGachaI18n, OprGachaPrize, ...)
- 作成レコード: 56件
- 推測値: 12件 (High: 4, Medium: 5, Low: 3)

[5/9] masterdata-from-bizops-gacha 完了
```

#### 5.6 クエスト・ステージスキル実行

```
[6/9] masterdata-from-bizops-quest-stage 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: クエスト設計書_地獄楽_メインストーリー.xlsx

実行結果:
✅ 成功
- 作成テーブル: 10個 (MstQuest, MstStage, MstStageI18n, ...)
- 作成レコード: 345件
- 推測値: 23件 (High: 6, Medium: 11, Low: 6)

[6/9] masterdata-from-bizops-quest-stage 完了
```

#### 5.7 ショップ・パックスキル実行

```
[7/9] masterdata-from-bizops-shop-pack 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ショップ設計書_地獄楽_期間限定パック.xlsx

実行結果:
✅ 成功
- 作成テーブル: 7個 (MstStoreProduct, MstPack, MstPackContent, ...)
- 作成レコード: 34件
- 推測値: 9件 (High: 2, Medium: 5, Low: 2)

[7/9] masterdata-from-bizops-shop-pack 完了
```

#### 5.8 ミッションスキル実行

```
[8/9] masterdata-from-bizops-mission 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ミッション設計書_地獄楽_イベントミッション.xlsx

実行結果:
✅ 成功
- 作成テーブル: 8個 (MstMissionEvent, MstMissionEventI18n, ...)
- 作成レコード: 123件
- 推測値: 11件 (High: 3, Medium: 6, Low: 2)

[8/9] masterdata-from-bizops-mission 完了
```

#### 5.9 降臨バトルスキル実行

```
[9/9] masterdata-from-bizops-advent-battle 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: 降臨バトル設計書_地獄楽_ボス討伐.xlsx

実行結果:
✅ 成功
- 作成テーブル: 7個 (MstAdventBattle, MstAdventBattleRank, ...)
- 作成レコード: 78件
- 推測値: 14件 (High: 4, Medium: 7, Low: 3)

[9/9] masterdata-from-bizops-advent-battle 完了
```

### Step 6: データ整合性の全体チェック

```
[データ整合性チェック開始]

外部キー整合性チェック中...
✅ MstMissionReward.resource_id → MstItem.id: 整合
✅ MstMissionReward.resource_id → MstUnit.id: 整合
✅ OprGachaPrize.unit_id → MstUnit.id: 整合
✅ MstPackContent.item_id → MstItem.id: 整合
✅ MstAdventBattleReward.resource_id → MstItem.id: 整合
✅ MstAdventBattleReward.resource_id → MstUnit.id: 整合

ID採番の一貫性チェック中...
✅ リリースキーが全テーブルで統一されています (202601010)

必須カラムの存在チェック中...
✅ 全ての必須カラムが埋まっています

[データ整合性チェック完了] エラー: 0件
```

### Step 7: 推測値レポートの統合

```
[推測値レポート統合開始]

各機能スキルの推測値を収集中...
- item: 2件
- hero: 15件
- event-basic: 5件
- reward: 8件
- gacha: 12件
- quest-stage: 23件
- shop-pack: 9件
- mission: 11件
- advent-battle: 14件

合計: 99件
- High（要確認）: 25件
- Medium（推奨確認）: 49件
- Low（参考）: 25件

機能別にセクション分け中...
重要度でソート中...

[推測値レポート統合完了]
```

### Step 8: 全体レポートの出力

```
[全体レポート生成中...]

実行サマリー作成中...
統合推測値レポート作成中...
データ整合性チェック結果作成中...
次のステップ作成中...

[全体レポート生成完了]
```

## 最終レポート

```markdown
# マスタデータ一括作成 実行レポート

## 実行サマリー

### 実行日時
2026-01-10 14:30:00 ～ 2026-01-10 14:45:00（15分）

### 実行した機能スキル
1. ✅ masterdata-from-bizops-item（アイテム）
2. ✅ masterdata-from-bizops-hero（ヒーロー）
3. ✅ masterdata-from-bizops-event-basic（イベント基本設定）
4. ✅ masterdata-from-bizops-reward（報酬・汎用）
5. ✅ masterdata-from-bizops-gacha（ガチャ）
6. ✅ masterdata-from-bizops-quest-stage（クエスト・ステージ）
7. ✅ masterdata-from-bizops-shop-pack（ショップ・パック）
8. ✅ masterdata-from-bizops-mission（ミッション）
9. ✅ masterdata-from-bizops-advent-battle（降臨バトル）

### 作成したテーブル数
- 合計: 58テーブル
- アイテム: 2テーブル
- ヒーロー: 13テーブル
- イベント基本設定: 3テーブル
- 報酬: 2テーブル（汎用）
- ガチャ: 6テーブル
- クエスト・ステージ: 10テーブル
- ショップ・パック: 7テーブル
- ミッション: 8テーブル
- 降臨バトル: 7テーブル

### 作成したレコード数
- 合計: 976レコード
- アイテム: 5レコード
- ヒーロー: 234レコード
- イベント基本設定: 12レコード
- 報酬: 89レコード
- ガチャ: 56レコード
- クエスト・ステージ: 345レコード
- ショップ・パック: 34レコード
- ミッション: 123レコード
- 降臨バトル: 78レコード

## 統合推測値レポート

### 概要
- 総推測値数: 99件
- High（要確認）: 25件
- Medium（推奨確認）: 49件
- Low（参考）: 25件

### masterdata-from-bizops-gacha（ガチャ）

#### High
- OprGacha.display_size: 仮値 "Medium" を設定（運営仕様書に記載なし）
- OprGacha.strapi_uuid: 仮UUID "00000000-0000-0000-0000-000000000001" を設定
- OprGacha.banner_logo_url: 仮値 "path/to/default_logo.png" を設定
- OprGacha.display_order: 仮値 "1" を設定（運営仕様書に記載なし）

#### Medium
- OprGachaI18n.description: ガチャ名から説明文を自動生成
- OprGachaI18n.detail_description: ガチャ名から詳細説明文を自動生成
- OprGachaI18n.notes: デフォルト注意事項を設定
- OprGachaPrize.weight: 未記載のキャラは均等配分で設定
- OprGachaDisplayUnitI18n.description: キャラ名から説明文を自動生成

#### Low
- OprGacha.updated_at: 現在日時を設定
- OprGachaI18n.updated_at: 現在日時を設定
- OprGachaPrize.updated_at: 現在日時を設定

### masterdata-from-bizops-hero（ヒーロー）

#### High
- MstUnit.max_hp: レアリティから推測（SSR: 1000, SR: 800, R: 600）
- MstUnit.max_attack: レアリティから推測（SSR: 150, SR: 120, R: 90）
- MstAbility.cooldown_turn: デフォルト値 "3" を設定（運営仕様書に記載なし）

#### Medium
- MstUnitI18n.description: キャラ名から説明文を自動生成
- MstAbilityI18n.description: アビリティ名から説明文を自動生成
- MstAttackI18n.description: 攻撃名から説明文を自動生成
- MstUnit.cost: レアリティから推測（SSR: 20, SR: 15, R: 10）
- MstUnit.initial_rarity: max_rarityと同値を設定
- ...（省略）

#### Low
- MstUnit.updated_at: 現在日時を設定
- MstUnitI18n.updated_at: 現在日時を設定
- ...（省略）

### masterdata-from-bizops-quest-stage（クエスト・ステージ）

#### High
- MstStage.required_stamina: デフォルト値 "10" を設定（運営仕様書に記載なし）
- MstStage.difficulty: デフォルト値 "Normal" を設定（運営仕様書に記載なし）
- MstStage.enemy_level: ステージ順序から推測（stage_order * 5）
- MstStage.clear_exp: 敵レベルから推測（enemy_level * 10）
- MstStage.clear_coin: 敵レベルから推測（enemy_level * 100）
- MstStageClearTimeReward.clear_time_seconds: デフォルト値 "180" を設定

#### Medium
- MstQuestI18n.description: クエスト名から説明文を自動生成
- MstStageI18n.description: ステージ名から説明文を自動生成
- MstStage.background_image: デフォルト値 "path/to/default_bg.png" を設定
- MstStage.bgm: デフォルト値 "bgm_001" を設定
- ...（省略）

#### Low
- MstQuest.updated_at: 現在日時を設定
- MstStage.updated_at: 現在日時を設定
- ...（省略）

### （その他の機能スキルの推測値レポートも同様に記載）

## データ整合性チェック結果

### 外部キー整合性
✅ 全ての外部キーが整合しています

### ID採番の一貫性
✅ リリースキーが全テーブルで統一されています（202601010）

### 必須カラムの存在
✅ 全ての必須カラムが埋まっています

## 次のステップ

1. **推測値の確認・修正**
   - 統合推測値レポートを確認し、必要に応じて修正してください
   - High（要確認）の25件は必ず確認してください
   - 特に以下の項目は重要:
     - ガチャのdisplay_size、strapi_uuid、banner_logo_url
     - ヒーローのmax_hp、max_attack、cooldown_turn
     - ステージのrequired_stamina、difficulty、enemy_level

2. **masterdata-csv-validatorスキルでの検証**
   - 作成したCSVファイルをmasterdata-csv-validatorスキルで検証してください
   - DB投入前の最終チェックとして実施してください

3. **DB投入前の最終チェック**
   - リリースキーが正しいか（202601010）
   - ID採番ルールが守られているか
   - 外部キー整合性が保たれているか
   - 推測値の修正が完了しているか
```

## エラー発生時の例

### シナリオ: ガチャスキルでエラーが発生した場合

```
[5/9] masterdata-from-bizops-gacha 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ガチャ設計書_地獄楽_いいジャン祭.xlsx

実行結果:
❌ 失敗
エラー: OprGacha.opr_gacha_id が重複しています
詳細: opr_gacha_id "Pickup_jig_001" が既に存在します

[5/9] masterdata-from-bizops-gacha 失敗（スキップ）

依存先の確認中...
- gacha は他の機能に依存されていないため、次の機能スキルに進みます

[6/9] masterdata-from-bizops-quest-stage 実行中...
（次の機能スキルに進む）
```

### シナリオ: ヒーロースキルでエラーが発生した場合（依存先がある）

```
[2/9] masterdata-from-bizops-hero 実行中...

パラメータ:
- release_key: 202601010
- ソースファイル: ヒーロー設計書_地獄楽_新キャラ.xlsx

実行結果:
❌ 失敗
エラー: MstUnit.unit_id が不正です
詳細: unit_id "chara_jig_999" の形式が不正です（期待: chara_jig_NNNNN）

[2/9] masterdata-from-bizops-hero 失敗（スキップ）

依存先の確認中...
- gacha: hero に依存 → スキップします
- quest-stage: hero に依存 → スキップします
- mission: hero に依存 → スキップします
- advent-battle: hero に依存 → スキップします

[3/9] masterdata-from-bizops-event-basic 実行中...
（hero に依存しない機能スキルのみ実行）

[最終結果]
実行: 5個の機能スキル
成功: 4個
失敗: 1個（hero）
スキップ: 4個（gacha, quest-stage, mission, advent-battle）
```

## まとめ

このワークフロー実行例では、以下を実現しています:

1. **運営仕様書全体からの自動解析**: 8個のファイルから8個の機能を特定
2. **依存関係を考慮した実行順序**: トポロジカルソートで最適な順序を決定
3. **各機能スキルの順次実行**: 9個の機能スキルを順番に実行
4. **データ整合性チェック**: 外部キー、ID採番、必須カラムを自動検証
5. **推測値レポート統合**: 99件の推測値を機能別・重要度別に整理
6. **全体レポート出力**: 実行サマリー、推測値レポート、次のステップを提示

最終的に、**58テーブル、976レコード**のマスタデータを**15分**で一括作成しました。
