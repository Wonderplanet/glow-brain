# refs/ ディレクトリについて

## MstEnemyStageParameter.csv

### 概要

`projects/glow-masterdata/MstEnemyStageParameter.csv` から、ファントムマスターに登場する敵キャラ用のレコードを抽出したファイル。

### 作成手順

1. `specs/敵キャラ出現インゲームID一覧.csv` の `MstEnemyStageParameterID` 列から対象IDを収集
2. 敵キャラIDごとに以下の優先順位で1レコードを選択
   - 第1優先: クエストタイプ=`normal` かつ 難易度=`Normal`
   - 第2優先: 難易度=`Normal`（クエストタイプ問わず）
   - 該当なし: 報告
3. 選択した `MstEnemyStageParameterID` にマッチする行を `MstEnemyStageParameter.csv` から抽出

### 結果

- 抽出件数: 47件（敵キャラID 47件、各1レコード）
- 第1優先で選択: 22件（normal + Normal）
- 第2優先で選択: 25件（event + Normal）
- 該当なし: 0件
