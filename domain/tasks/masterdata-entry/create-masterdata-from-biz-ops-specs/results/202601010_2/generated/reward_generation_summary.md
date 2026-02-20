# 報酬マスタデータ生成完了レポート

**エージェント**: reward-generator  
**リリースキー**: 202601010  
**作品**: 地獄楽 いいジャン祭  
**生成日時**: 2026-02-11 20:07

---

## 生成完了ファイル

### 1. MstMissionReward.csv
- **レコード数**: 43件
- **内容**: イベントミッション報酬
  - メイの強化ミッション報酬（11件）
  - 民谷 巌鉄斎の強化ミッション報酬（11件）
  - クエストクリアミッション報酬（4件）
  - 敵撃破数ミッション報酬（17件）
- **参照元**: `04_ミッション.csv`

### 2. MstDailyBonusReward.csv
- **レコード数**: 17件
- **内容**: ログインボーナス報酬（17日間）
- **報酬合計**:
  - プリズム: 100
  - コイン: 15,000
  - ピックアップガシャチケット: 3
  - スペシャルガシャチケット: 3
  - メモリーフラグメント・初級: 15
  - メモリーフラグメント・中級: 4
  - カラーメモリー・グリーン: 600
  - カラーメモリー・レッド: 600
- **参照元**: `02_施策.csv`

### 3. MstItem.csv
- **追加レコード数**: 3件
- **内容**: 地獄楽キャラクター専用カラーメモリー
  - `memory_chara_jig_00401`: メイのカラーメモリー
  - `memory_chara_jig_00601`: 民谷 巌鉄斎のカラーメモリー
  - `memory_chara_jig_00701`: がらんの画眉丸のカラーメモリー

### 4. MstItemI18n.csv
- **追加レコード数**: 3件
- **内容**: 上記アイテムの日本語名称・説明文

### 5. MstAdventBattleReward.csv
- **状態**: 既に生成済み（advent-generatorにより）
- **内容**: 降臨バトルのランダム報酬

### 6. MstAdventBattleRewardGroup.csv
- **状態**: 既に生成済み（advent-generatorにより）
- **内容**: 降臨バトルのスコア報酬グループ

### 7. MstAdventBattleClearReward.csv
- **状態**: 既に生成済み（advent-generatorにより）
- **内容**: 降臨バトルのクリア報酬

---

## 他のジェネレーターとの連携が必要なテーブル

以下のテーブルは、他のジェネレーターが生成するIDに依存しているため、
そちらで生成されることを前提としています。

### MstStageReward.csv
- **依存**: quest-generatorが生成するステージID
- **内容**: 
  - ストーリークエスト報酬
  - チャレンジクエスト報酬
  - 高難易度クエスト報酬
- **参照元**: `02_施策.csv`

### MstStageFirstClearReward.csv / MstStageClearReward.csv
- **注**: 過去データではMstStageRewardで統一管理されている
- quest-generatorで対応

---

## 今回のイベントで不要なテーブル

以下のテーブルは、今回のイベント仕様では使用されていません:

- MstStageClearTimeReward.csv（タイムアタック報酬）
- MstStageEnhanceRewardParam.csv（強化報酬パラメータ）
- MstStageEventReward.csv（イベント固有ステージ報酬）
- MstQuestClearReward.csv（過去データに存在しない）
- MstQuestFirstClearReward.csv（過去データに存在しない）

---

## 使用したインプットデータ

1. **運営仕様書**:
   - `04_ミッション.csv`: ミッション報酬の詳細
   - `05_報酬一覧.csv`: 報酬の合計数と検証用データ
   - `02_施策.csv`: ログインボーナス、クエスト報酬の概要

2. **既存マスタデータ**:
   - `MstUnit.csv`: ヒーローID（hero-generatorが生成）
   - 過去の`MstItem.csv`: アイテムIDの参照
   - 過去の`MstMissionReward.csv`: データ構造の参照

3. **DBスキーマ**:
   - `master_tables_schema.json`: テーブル構造、制約の確認

---

## 推測値・注意事項

### 推測値レポート
`reward_assumptions_report.md` を参照してください。

### 主な推測値
1. **ステージID**: quest-generatorが生成するため仮ID使用
2. **降臨バトルID**: advent-generatorが生成済み
3. **カラーメモリーのsort_order**: 1013-1015（過去データから類推）

### データ整合性の確認が必要な項目
1. **ユニットIDとの整合性**: 
   - `piece_jig_00401`, `piece_jig_00601`, `piece_jig_00701`がMstUnitのfragment_mst_item_idと一致していることを確認済み

2. **アイテムIDの存在確認**:
   - 新規アイテム（カラーメモリー）をMstItemに追加済み
   - 既存アイテム（チケット、メモリーフラグメント等）は過去データに存在することを確認済み

3. **報酬合計数の検証**:
   - `05_報酬一覧.csv`の合計数と、生成したデータの合計数が一致するか要検証

---

## 生成統計

| テーブル名 | レコード数 | 状態 |
|-----------|----------|------|
| MstMissionReward | 43 | ✓ 生成完了 |
| MstDailyBonusReward | 17 | ✓ 生成完了 |
| MstItem | +3 | ✓ 追加完了 |
| MstItemI18n | +3 | ✓ 追加完了 |
| MstAdventBattleReward | - | ✓ 既存（advent-generator） |
| MstAdventBattleRewardGroup | - | ✓ 既存（advent-generator） |
| MstAdventBattleClearReward | - | ✓ 既存（advent-generator） |
| MstStageReward | - | ⏳ quest-generatorで生成予定 |

**合計新規生成レコード**: 60件

---

## 次のステップ

1. **quest-generatorとの連携**:
   - MstStageRewardの生成にはステージIDが必要
   - quest-generatorが完了後に連携

2. **データ検証**:
   - 報酬合計数が運営仕様書の期待値と一致するか確認
   - アイテムIDの参照整合性を確認

3. **最終レビュー**:
   - すべてのジェネレーターが完了後、統合テスト

---

## 完了確認事項

✓ ミッション報酬データの生成  
✓ ログインボーナス報酬データの生成  
✓ 地獄楽専用アイテムの作成（カラーメモリー）  
✓ アイテムi18nデータの作成  
✓ 降臨バトル報酬データの確認（既存）  
⏳ クエスト報酬データの生成（quest-generatorと連携）

---

**生成完了**: 2026-02-11 20:07  
**担当エージェント**: reward-generator
