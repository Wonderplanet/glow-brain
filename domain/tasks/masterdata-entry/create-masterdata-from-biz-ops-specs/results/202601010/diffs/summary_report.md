# マスタデータ生成結果 精度評価レポート

リリースキー: **202601010**

## エグゼクティブサマリー

masterdata-from-bizops-allスキルを使用して生成したマスタデータの精度評価を実施しました。
**25個のCSVファイル**を対象に、生成結果と正解データの差分を比較しました。

### 主要な結果
- **全体精度**: -37.11%
- **完全一致ファイル**: 8/25 (32.0%)
- **差分ありファイル**: 17/25 (68.0%)
- **総行数（正解）**: 415
- **総差分行数**: 569 (追加: 282, 削除: 247, 変更: 40)

## 詳細統計

| 項目 | 値 |
|------|------|
| 比較対象ファイル数 | 25 |
| 差分のないファイル数 | 8 |
| 差分のあるファイル数 | 17 |
| 総行数（生成結果） | 380 |
| 総行数（正解データ） | 415 |
| 一致した行数 | 93 |
| 追加された行数 | 282 |
| 削除された行数 | 247 |
| 変更された行数 | 40 |
| 全体精度 | -37.11% |

## ファイル別の差分統計

| ファイル名 | 差分 | 生成行数 | 正解行数 | 追加 | 削除 | 変更 | 一致 |
|-----------|------|---------|---------|------|------|------|------|
| MstAdventBattle.csv | ✗ | 1 | 1 | 0 | 0 | 1 | 0 |
| MstAdventBattleClearReward.csv | ✗ | 6 | 5 | 0 | 1 | 0 | 5 |
| MstAdventBattleI18n.csv | ✓ | 1 | 1 | 0 | 0 | 0 | 1 |
| MstAdventBattleRank.csv | ✓ | 16 | 16 | 0 | 0 | 0 | 16 |
| MstAdventBattleReward.csv | ✗ | 102 | 120 | 120 | 102 | 0 | 0 |
| MstAdventBattleRewardGroup.csv | ✗ | 37 | 55 | 43 | 25 | 3 | 9 |
| MstEmblem.csv | ✓ | 7 | 7 | 0 | 0 | 0 | 7 |
| MstEmblemI18n.csv | ✓ | 7 | 7 | 0 | 0 | 0 | 7 |
| MstEvent.csv | ✓ | 1 | 1 | 0 | 0 | 0 | 1 |
| MstEventI18n.csv | ✗ | 1 | 1 | 0 | 0 | 1 | 0 |
| MstHomeBanner.csv | ✗ | 1 | 3 | 3 | 1 | 0 | 0 |
| MstItem.csv | ✓ | 6 | 6 | 0 | 0 | 0 | 6 |
| MstItemI18n.csv | ✓ | 6 | 6 | 0 | 0 | 0 | 6 |
| MstPack.csv | ✗ | 1 | 2 | 1 | 0 | 1 | 0 |
| MstPackContent.csv | ✗ | 3 | 7 | 4 | 0 | 0 | 3 |
| MstPackI18n.csv | ✗ | 1 | 2 | 1 | 0 | 0 | 1 |
| MstQuest.csv | ✗ | 5 | 5 | 0 | 0 | 2 | 3 |
| MstQuestI18n.csv | ✗ | 5 | 5 | 0 | 0 | 1 | 4 |
| MstStage.csv | ✗ | 24 | 20 | 8 | 12 | 11 | 1 |
| MstStageClearTimeReward.csv | ✓ | 21 | 21 | 0 | 0 | 0 | 21 |
| MstStageEventReward.csv | ✗ | 78 | 70 | 70 | 78 | 0 | 0 |
| MstStageEventSetting.csv | ✗ | 24 | 20 | 20 | 24 | 0 | 0 |
| MstStageI18n.csv | ✗ | 24 | 20 | 0 | 4 | 20 | 0 |
| MstStoreProduct.csv | ✗ | 1 | 7 | 6 | 0 | 0 | 1 |
| MstStoreProductI18n.csv | ✗ | 1 | 7 | 6 | 0 | 0 | 1 |

## 差分が多いテーブル トップ10

| 順位 | ファイル名 | 総差分数 | 追加 | 削除 | 変更 |
|------|-----------|---------|------|------|------|
| 1 | MstAdventBattleReward.csv | 222 | 120 | 102 | 0 |
| 2 | MstStageEventReward.csv | 148 | 70 | 78 | 0 |
| 3 | MstAdventBattleRewardGroup.csv | 71 | 43 | 25 | 3 |
| 4 | MstStageEventSetting.csv | 44 | 20 | 24 | 0 |
| 5 | MstStage.csv | 31 | 8 | 12 | 11 |
| 6 | MstStageI18n.csv | 24 | 0 | 4 | 20 |
| 7 | MstStoreProduct.csv | 6 | 6 | 0 | 0 |
| 8 | MstStoreProductI18n.csv | 6 | 6 | 0 | 0 |
| 9 | MstHomeBanner.csv | 4 | 3 | 1 | 0 |
| 10 | MstPackContent.csv | 4 | 4 | 0 | 0 |

## 完全一致ファイル

以下のファイルは正解データと完全に一致しています:

- ✓ MstAdventBattleI18n.csv
- ✓ MstAdventBattleRank.csv
- ✓ MstEmblem.csv
- ✓ MstEmblemI18n.csv
- ✓ MstEvent.csv
- ✓ MstItem.csv
- ✓ MstItemI18n.csv
- ✓ MstStageClearTimeReward.csv

## 差分の傾向分析

### 追加された行 (282件)

正解データに存在するが、生成結果には含まれていない行があります。
これは運営仕様書からの抽出漏れ、またはデータ生成ロジックの不足を示している可能性があります。

### 削除された行 (247件)

生成結果に含まれているが、正解データには存在しない行があります。
これは不要なデータの生成、または運営仕様書の解釈誤りを示している可能性があります。

### 変更された行 (40件)

同じIDを持つ行が存在しますが、カラムの値が異なります。
これはデータ変換ロジックの誤り、型変換の問題、またはデフォルト値の設定誤りを示している可能性があります。

## 改善の方向性

### 短期的な改善

1. **差分が多いテーブルの優先的な調査**
   - 上記トップ10のテーブルについて、個別の差分詳細レポートを確認
   - 差分の原因を特定し、運営仕様書の読み取りロジックやデータ生成ロジックを修正

2. **エラーケースの修正**
   - ヘッダー不一致やエラーが発生したファイルを優先的に修正

3. **変更された行の原因調査**
   - 型変換、デフォルト値、データフォーマットの問題を調査

### 中長期的な改善

1. **自動検証の強化**
   - masterdata-csv-validatorスキルの活用
   - スキーマとの整合性チェックの自動化

2. **運営仕様書の標準化**
   - 仕様書のフォーマットを統一し、機械的な読み取りを容易にする

3. **テストケースの拡充**
   - 今回の検証データセットをリグレッションテストに活用

## 個別ファイルの詳細レポート

各ファイルの詳細な差分は、以下のレポートを参照してください:

- [MstAdventBattle.csv](./MstAdventBattle_diff.md)
- [MstAdventBattleClearReward.csv](./MstAdventBattleClearReward_diff.md)
- [MstAdventBattleReward.csv](./MstAdventBattleReward_diff.md)
- [MstAdventBattleRewardGroup.csv](./MstAdventBattleRewardGroup_diff.md)
- [MstEventI18n.csv](./MstEventI18n_diff.md)
- [MstHomeBanner.csv](./MstHomeBanner_diff.md)
- [MstPack.csv](./MstPack_diff.md)
- [MstPackContent.csv](./MstPackContent_diff.md)
- [MstPackI18n.csv](./MstPackI18n_diff.md)
- [MstQuest.csv](./MstQuest_diff.md)
- [MstQuestI18n.csv](./MstQuestI18n_diff.md)
- [MstStage.csv](./MstStage_diff.md)
- [MstStageEventReward.csv](./MstStageEventReward_diff.md)
- [MstStageEventSetting.csv](./MstStageEventSetting_diff.md)
- [MstStageI18n.csv](./MstStageI18n_diff.md)
- [MstStoreProduct.csv](./MstStoreProduct_diff.md)
- [MstStoreProductI18n.csv](./MstStoreProductI18n_diff.md)
