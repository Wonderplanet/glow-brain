# 降臨バトルマスタデータ 推測値レポート

## 生成日時
2026-02-12

## リリースキー
202601010

## 降臨バトル名
まるで 悪夢を見ているようだ

---

## 推測値一覧

### 1. MstAdventBattle.id
- **値**: `quest_raid_jig1_00001`
- **信頼度**: High
- **推測根拠**: 地獄楽(jig)の最初の降臨バトルとして、標準的な命名規則に従って採番
- **データソース**: id_naming_rules + specification
- **確認事項**: 他の降臨バトルIDとの重複がないことを確認してください

### 2. MstAdventBattle.mst_event_id
- **値**: `event_jig_00001`
- **信頼度**: High
- **推測根拠**: 地獄楽いいジャン祭イベントの基本IDとして推定
- **データソース**: specification
- **確認事項**: MstEventテーブルに該当するIDが存在することを確認してください

### 3. MstAdventBattle.mst_in_game_id
- **値**: `raid_jig1_00001`
- **信頼度**: High
- **推測根拠**: 降臨バトルIDから自動生成(`quest_raid_` → `raid_`)
- **データソース**: id_naming_rules
- **確認事項**: MstInGameテーブルに該当するIDが存在することを確認してください

### 4. MstAdventBattle.asset_key
- **値**: `jig1_00001`
- **信頼度**: Medium
- **推測根拠**: 設計書にアセットキーの記載がなかったため、シリーズID(jig)と連番から推測
- **データソース**: past_data_pattern
- **確認事項**: 実際のアセットキーを確認し、正しい値に差し替えてください

### 5. MstAdventBattle.advent_battle_type
- **値**: `ScoreChallenge`
- **信頼度**: High
- **推測根拠**: 仕様書のスコア設定、ランク評価、ハイスコア報酬の記載から判断
- **データソース**: specification
- **確認事項**: バトルタイプが正しいことを確認してください

### 6. MstAdventBattle.initial_battle_point
- **値**: `500`
- **信頼度**: High
- **推測根拠**: 過去データ(202512010)の標準値500を使用
- **データソース**: past_data_pattern
- **確認事項**: イベント難易度に応じて調整が必要か確認してください

### 7. MstAdventBattle.score_additional_coef
- **値**: `0.07`
- **信頼度**: High
- **推測根拠**: 過去データ(202512010)の標準値0.07を使用
- **データソース**: past_data_pattern
- **確認事項**: スコア加算係数が適切か確認してください

### 8. MstAdventBattle.score_addition_target_mst_enemy_stage_parameter_id
- **値**: `test`
- **信頼度**: Low
- **推測根拠**: 過去データ(202512010)と同様の仮値を設定
- **データソース**: past_data_pattern
- **確認事項**: **必ず実際の敵ステージパラメータIDに差し替えてください**

### 9. MstAdventBattle.display_mst_unit_id1
- **値**: `enemy_jig_00601`
- **信頼度**: Medium
- **推測根拠**: 仕様書の「登場強敵キャラ: 朱槿」と特効キャラ「民谷 巌鉄斎(chara_jig_00601)」の関連性から、敵キャラID `enemy_jig_00601` を推測
- **データソース**: specification + inference
- **確認事項**: **実際のボス敵キャラIDを確認し、正しい値に差し替えてください**

### 10. MstAdventBattle.exp / coin
- **値**: `exp=100`, `coin=300`
- **信頼度**: High
- **推測根拠**: 仕様書に「獲得リーダーEXP: 100」と明記、コインは過去データの標準値300を使用
- **データソース**: specification + past_data_pattern
- **確認事項**: コイン獲得量が適切か確認してください

### 11. MstAdventBattleI18n.name (英語・中国語)
- **値**:
  - 英語: `A Nightmare Come True`
  - 簡体字中国語: `如同噩梦一般`
  - 繁体字中国語: `如同噩夢一般`
- **信頼度**: Medium
- **推測根拠**: 日本語タイトル「まるで 悪夢を見ているようだ」を機械翻訳
- **データソース**: machine_translation
- **確認事項**: **必ず翻訳チームによる正式な翻訳に差し替えてください**

### 12. MstAdventBattleI18n.boss_description
- **値**: `敵を倒して高スコア獲得!!` (日本語)
- **信頼度**: Medium
- **推測根拠**: 過去データの標準的なボス説明文を使用
- **データソース**: past_data_pattern
- **確認事項**: イベントに合わせた説明文に変更する必要があるか確認してください

### 13. MstAdventBattleClearReward のアイテムID
- **値**:
  - `colormemory_glo_00001` (カラーメモリー・レッド)
  - `colormemory_glo_00002` (カラーメモリー・グリーン)
  - `colormemory_glo_00003` (カラーメモリー・イエロー)
  - `colormemory_glo_00004` (カラーメモリー・ブルー)
  - `colormemory_glo_00005` (カラーメモリー・グレー)
- **信頼度**: Medium
- **推測根拠**: 仕様書に「カラーメモリー・レッド」等の記載はあるが、具体的なアイテムIDが不明のため推測
- **データソース**: specification + inference
- **確認事項**: **実際のカラーメモリーアイテムIDを確認し、正しい値に差し替えてください**

### 14. MstAdventBattleReward のアイテム/チケットID
- **値**:
  - プリズム: `prism_glo_00001`
  - メモリーフラグメント初級: `memoryfragment_glo_00001`
  - メモリーフラグメント中級: `memoryfragment_glo_00002`
  - メモリーフラグメント上級: `memoryfragment_glo_00003`
  - スペシャルガシャチケット: `ticket_glo_00002`
  - エンブレム: `emblem_jig_00001`
- **信頼度**: Medium
- **推測根拠**: 過去データ(202512010)から標準的なアイテムIDを参照、エンブレムは地獄楽イベント用として推測
- **データソース**: past_data_pattern + inference
- **確認事項**: **各アイテムIDとチケットIDが実際に存在することを確認し、必要に応じて差し替えてください**

### 15. MstEventBonusUnit.event_bonus_group_id
- **値**: `raid_jig1_00001`
- **信頼度**: High
- **推測根拠**: MstAdventBattle.idから自動生成(`quest_raid_jig1_00001` → `raid_jig1_00001`)
- **データソース**: id_naming_rules
- **確認事項**: MstAdventBattle.event_bonus_group_idと一致していることを確認してください

---

## データ整合性チェック結果

### ✅ 成功項目
- [x] ヘッダーの列順が正しい
- [x] すべてのIDが一意である
- [x] ID採番ルールに従っている
- [x] enum値が正確に一致している(advent_battle_type: ScoreChallenge, rank_type: Bronze/Silver/Gold/Master等)
- [x] 開催期間が妥当(start_at < end_at)
- [x] ランク設定が完全(全16段階)
- [x] クリア報酬のpercentage合計が100(20% × 5 = 100%)

### ⚠️ 要確認項目
- [ ] MstAdventBattle.score_addition_target_mst_enemy_stage_parameter_id が仮値(`test`)のため差し替え必須
- [ ] MstAdventBattle.display_mst_unit_id1 が推測値のため確認必須
- [ ] カラーメモリーのアイテムIDが推測値のため確認必須
- [ ] エンブレムIDが推測値のため確認必須
- [ ] 多言語翻訳が機械翻訳のため正式翻訳への差し替え必須

---

## 次のステップ

1. **必須確認事項**:
   - `score_addition_target_mst_enemy_stage_parameter_id` を実際の値に差し替え
   - `display_mst_unit_id1` (ボス敵キャラID)を確認
   - カラーメモリー、エンブレム、チケット等のアイテムIDを確認
   - 多言語翻訳を正式版に差し替え

2. **推奨確認事項**:
   - `asset_key` を実際のアセットキーに差し替え
   - イベント難易度に応じたパラメータ調整(BP、スコア係数)
   - ボス説明文をイベントに合わせた内容に変更

3. **検証**:
   - `masterdata-csv-validator` スキルで全テーブルを検証
   - MstEvent、MstInGame等の依存テーブルが存在することを確認
   - 外部キー整合性のチェック

---

## 生成ファイル一覧

1. **MstAdventBattle.csv** - 降臨バトル基本情報(1レコード)
2. **MstAdventBattleI18n.csv** - 降臨バトル多言語情報(4レコード: ja/en/zhHans/zhHant)
3. **MstAdventBattleRank.csv** - ランク評価設定(16レコード)
4. **MstAdventBattleClearReward.csv** - クリア報酬(5レコード)
5. **MstAdventBattleRewardGroup.csv** - 報酬グループ定義(37レコード)
6. **MstAdventBattleReward.csv** - 報酬詳細(100レコード)
7. **MstEventBonusUnit.csv** - 特効キャラ設定(7レコード)

**総レコード数**: 170レコード
