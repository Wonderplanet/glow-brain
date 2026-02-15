# ID採番パターン分析タスク

## 概要

GLOWマスタデータ（glow-masterdata直下の全CSVファイル）のID採番パターンを分析し、**レコード全体での通算連番情報がIDに含まれているテーブル**を洗い出すタスク。

## 背景・目的

### 問題点
- **MstMissionReward.id** は `mission_reward_連番` となっており、マスタデータ自動作成時に過去のデータの連番最大値を考慮して採番する必要がある
- これにより、マスタデータ自動作成には本来不要なコンテキストを入れる必要があり、その分、重要なレコード情報を生成する精度が下がる
- しかし、この連番情報には意味がないため、質を下げたり手間を増やしてAI出力を不安定にさせるほど重要ではない

### 解決策
1. ID採番ルールを変更する
2. 各テーブルごとの特性を分析し、適切なID採番ルールを提案する
3. 提案内容でGTRさんに確認を取る

### 期待される成果
- マスタデータ作成手順書に、作成単位でのルールを適用できるので、手順書作成のルールを定められる
- マスタデータ一発作成の精度向上が期待できる
- そのままコピペできる状態で生成できる

## フォルダ構造

```
id-numbering-analysis/
├── README.md                    # このファイル
├── scripts/                     # 分析スクリプト
│   ├── analyze_id_patterns.py   # ID採番パターン分析スクリプト
│   └── classify_id_patterns.py  # パターン分類スクリプト
└── results/                     # 分析結果CSV
    ├── id_pattern_analysis.csv                # 最初の分析結果（全パターン）
    ├── id_pattern_analysis_filtered.csv       # カテゴリ別連番を除外した結果
    └── id_pattern_analysis_classified.csv     # 最終的な分類済み結果 ★推奨
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

## 使い方

### 1. 分析スクリプトの実行

#### Step 1: ID採番パターン分析
```bash
# glow-brainリポジトリのルートディレクトリで実行
python3 domain/tasks/masterdata-entry/id-numbering-analysis/scripts/analyze_id_patterns.py
```

出力:
- `id_pattern_analysis.csv` - 全パターンの分析結果（カテゴリ別連番も含む）

#### Step 2: パターン分類
```bash
python3 domain/tasks/masterdata-entry/id-numbering-analysis/scripts/classify_id_patterns.py
```

出力:
- `id_pattern_analysis_classified.csv` - 分類済み結果（推奨）

### 2. 結果CSVの見方

**`id_pattern_analysis_classified.csv`** の列：

| 列名 | 説明 |
|------|------|
| テーブル | テーブル名（例: MstMissionReward） |
| 列 | 列名（ほとんどが `id`） |
| パターン | ID採番パターン（例: `mission_reward_[連番]`） |
| 説明 | パターンの詳細説明（範囲、件数など） |
| 分類 | パターンの分類（数字のみ/純粋/バージョン付き） |

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

### 推奨される新しいID採番ルール例

#### MstMissionRewardの場合
- **現在**: `mission_reward_1`, `mission_reward_2`, ...
- **提案**: `mission_reward_[group_id]_[連番]`
  - 例: `mission_reward_daily_bonus_reward_1_1_1`
  - group_idごとに連番を振ることで、過去の最大値を考慮する必要がなくなる

#### 一般的な提案
- **意味のあるプレフィックス + カテゴリ + 連番**
  - カテゴリごとに連番をリセット
  - 例: `mission_reward_[event_id]_[連番]`, `mission_reward_[achievement_group]_[連番]`

## GTRさんへの確認事項

この分析結果をもとに、以下を確認：

1. **通算連番を使用している53テーブルについて**:
   - どのテーブルのID採番ルールを変更すべきか？
   - 優先順位は妥当か？

2. **新しいID採番ルールの方針**:
   - カテゴリ別連番への変更は妥当か？
   - 他に考慮すべき要素はあるか？

3. **移行計画**:
   - 既存データのIDは変更する必要があるか？
   - 新規データのみ新ルールを適用するか？

## 関連ドキュメント

- `.ai-context/prompts/運営仕様書からマスタデータ作成の手順書.md` - マスタデータ作成手順書
- `.claude/skills/masterdata-csv-validator/SKILL.md` - マスタデータCSV検証スキル
- `マスタデータ/docs/ID割り振りルール.csv` - 現在のID採番ルール

## 履歴

- 2026-02-15: 初回分析実施、53テーブルで通算連番を検出
