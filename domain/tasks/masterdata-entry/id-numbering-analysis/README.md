# ID採番パターン分析・提案タスク

## 概要

GLOWマスタデータ（glow-masterdata直下の全CSVファイル）のID採番パターンを分析し、**レコード全体での通算連番情報がIDに含まれているテーブル**を洗い出し、**新しいID採番ルールを提案**するタスク。

## 背景・目的

### 問題点
- **MstMissionReward.id** は `mission_reward_連番` となっており、マスタデータ自動作成時に過去のデータの連番最大値を考慮して採番する必要がある
- これにより、マスタデータ自動作成には本来不要なコンテキストを入れる必要があり、その分、重要なレコード情報を生成する精度が下がる
- しかし、この連番情報には意味がないため、質を下げたり手間を増やしてAI出力を不安定にさせるほど重要ではない

### 解決策
1. ID採番ルールを変更する
2. 各テーブルごとの特性を分析し、適切なID採番ルールを提案する
3. 提案内容でプランナーさんに確認を取る

### 期待される成果
- マスタデータ作成手順書に、作成単位でのルールを適用できるので、手順書作成のルールを定められる
- マスタデータ一発作成の精度向上が期待できる
- そのままコピペできる状態で生成できる

## フォルダ構造

```
id-numbering-analysis/
├── README.md                                   # このファイル
├── results/                                    # 分析結果・提案CSV
│   ├── id_pattern_analysis.csv                 # 最初の分析結果（全パターン165件）
│   ├── id_pattern_analysis_filtered.csv        # カテゴリ別連番を除外した結果（53件）
│   ├── id_pattern_analysis_classified.csv      # 分類済み結果（53件）
│   ├── id_pattern_proposal.csv                 # 全テーブルの提案（165件）
│   └── id_pattern_proposal_summary.csv         # 通算連番テーブルの提案（53件） ★プランナーさん確認用
└── scripts/                                    # 分析スクリプト
    ├── analyze_id_patterns.py                  # ID採番パターン分析スクリプト
    ├── classify_id_patterns.py                 # パターン分類スクリプト
    └── generate_id_proposal.py                 # ID採番ルール提案生成スクリプト
```

## 分析結果サマリー

### 調査対象
- **glow-masterdata直下の全CSVファイル**: 167個
- **通算連番を使用しているテーブル**: 53個

### パターン分類

| 分類 | 件数 | 説明 | 例 |
|------|------|------|-----|
| **数字のみの通算連番** | 36件 | プレフィックスなし、純粋に数字のみのID | `1`, `2`, `3`, ... |
| **純粋な通算連番（プレフィックス）** | 13件 | プレフィックスあり、バージョン番号なし | `mission_reward_1`, `mission_reward_2`, ... |
| **バージョン付き通算連番** | 4件 | プレフィックスにバージョン番号が含まれている | `achievement_2_1`, `comeback_1_1`, ... |

### 主要な発見

#### 1. MstMissionReward（問題の発端）
- **パターン**: `mission_reward_[連番]`
- **分類**: 純粋な通算連番（プレフィックス）
- **範囲**: 1～100（現在100件）
- **問題点**: マスタデータ自動作成時に過去の連番最大値を考慮する必要があり、精度低下の原因

#### 2. 通算連番を使用している主要テーブル

**純粋な通算連番（プレフィックス）- 最も問題となる可能性が高い（13件）**:
- MstMissionReward ← 問題の発端
- MstMissionLimitedTerm
- MstMissionDailyBonus
- MstNgWord, MstWhiteWord
- MstIdleIncentiveReward
- MstFragmentBox
- MstItemRarityTrade
- MstPvpDummy
- MstShopPass, MstShopPassEffect
- MstUnitEncyclopediaEffect, MstUnitEncyclopediaReward

**バージョン付き通算連番（4件）**:
- MstMissionAchievement (`achievement_2_[連番]`)
- MstComebackBonus (`comeback_1_[連番]`)
- MstDailyBonusReward (`comeback_reward_1_[連番]`)
- MstOutpostEnhancement (`enhance_1_[連番]`)

## 提案資料

### プランナーさん確認用ファイル

**`results/id_pattern_proposal_summary.csv`** - 通算連番を使用している53テーブルに対する具体的な提案

### 提案CSVの見方

**`id_pattern_proposal_summary.csv`** の列：

| 列名 | 説明 |
|------|------|
| テーブル | テーブル名（例: MstMissionReward） |
| 列 | 列名（ほとんどが `id`） |
| 現在のパターン | 現在のID採番パターン |
| 説明 | 現在のパターンの詳細説明 |
| **提案パターン** | **新しいID採番ルールの提案** |
| **提案理由** | **提案の理由と期待される効果** |

## 提案資料の主要な提案例

### MstMissionReward（問題の発端）
- **現在**: `mission_reward_[連番]`
- **提案**: `[group_id]`
- **理由**: 既存のgroup_idカラムの値をそのままIDとして使用。group_idは既に意味のある識別子（例: daily_bonus_reward_1_1）なので、通算連番は不要。

### MstMissionLimitedTerm
- **現在**: `limited_term_[連番]`
- **提案**: `mission_limited_term_[作品ID]_[イベントID]_[連番]`
- **理由**: 作品IDとイベントIDをベースにした採番。イベントごとに連番を振ることで、過去の最大値を考慮する必要がなくなる。

### MstMissionAchievement
- **現在**: `achievement_2_[連番]`（バージョン付き通算連番）
- **提案**: `mission_achievement_[criterion_type]_[連番]`
- **理由**: 達成条件タイプ（criterion_type）ごとに連番を振る。バージョン番号（achievement_2_）は削除し、条件タイプで分類することで、より意味のあるIDとする。

### MstUnitLevelUp
- **現在**: `[連番]`（数字のみの通算連番）
- **提案**: `unit_level_up_[level]`
- **理由**: レベルをIDとする。レベルごとに1つのレベルアップ情報が対応するため、レベルそのものをIDとして使用。

### MstItemRarityTrade
- **現在**: `rarity_trade_[連番]`
- **提案**: `item_rarity_trade_[from_rarity]_to_[to_rarity]`
- **理由**: 交換元と交換先のレアリティをIDに含める。レアリティの組み合わせで一意に決まるため、通算連番は不要。

## 次のアクション

### ID採番ルール変更の優先順位

1. **高優先度**: 純粋な通算連番（プレフィックス）13件
   - 特に `MstMissionReward`, `MstMissionLimitedTerm` などミッション系
   - これらは運営施策ごとに追加されるため、通算連番は不要

2. **中優先度**: 数字のみの通算連番 36件
   - プレフィックスがないため、IDの意味が不明確
   - ただし、テーブルの特性によっては通算連番が適切な場合もある

3. **低優先度**: バージョン付き通算連番 4件
   - バージョン情報は意味がある可能性があるため、慎重に判断

### 提案の方針

すべての提案は以下の方針に基づいています：

1. **既存カラムの活用**: group_id、event_id等の既存カラムをIDに含めることで、意味のあるIDを構成
2. **カテゴリー別連番**: 通算連番ではなく、カテゴリー（グループ、イベント、ティア等）ごとに連番を振る
3. **既存ID採番ルールとの整合性**: `domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv` に沿った形で提案
4. **自動生成の容易性**: マスタデータ自動作成時に過去の最大値を考慮する必要がないパターン

## プランナーさんへの確認事項

この提案資料（`results/id_pattern_proposal_summary.csv`）をもとに、以下を確認：

1. **提案されたID採番ルールについて**:
   - 各テーブルの提案パターンは妥当か？
   - 修正が必要な提案はあるか？

2. **優先順位について**:
   - どのテーブルから変更を開始すべきか？
   - 段階的な移行が必要か、一括変更か？

3. **移行計画**:
   - 既存データのIDは変更する必要があるか？
   - 新規データのみ新ルールを適用するか？
   - 移行期間中の互換性をどう保つか？

4. **影響範囲の確認**:
   - サーバー側のコード変更が必要か？
   - クライアント側のコード変更が必要か？
   - データ移行ツールの開発が必要か？

## 関連ドキュメント

- `.ai-context/prompts/運営仕様書からマスタデータ作成の手順書.md` - マスタデータ作成手順書
- `.claude/skills/masterdata-csv-validator/SKILL.md` - マスタデータCSV検証スキル
- `domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv` - 現在のID採番ルール
- `.claude/skills/masterdata-id-numbering/SKILL.md` - ID採番ルール支援スキル

## 履歴

- 2026-02-15: 初回分析実施、53テーブルで通算連番を検出
- 2026-02-15: 新しいID採番ルールの提案資料を作成（53テーブルに対する具体的な提案を生成）
