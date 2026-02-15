# ID採番パターン分析・提案タスク

## 概要

GLOWマスタデータ（glow-masterdata直下の全CSVファイル）のID採番パターンを分析し、**レコード全体での通算連番情報がIDに含まれているテーブル**を洗い出し、**新しいID採番ルールを提案**するタスク。

## GTRさん確認用ファイル

**`results/id_pattern_proposal_summary.csv`** - 通算連番を使用している53テーブルに対する具体的な提案

このCSVファイルをGTRさんに共有して、ID採番ルール変更の承認を得てください。

## 分析結果サマリー

- **調査対象**: glow-masterdata直下の全CSVファイル 167個
- **通算連番を使用しているテーブル**: 53個

### 主要な提案例

#### MstMissionReward（問題の発端）
- **現在**: `mission_reward_[連番]`
- **提案**: `[group_id]`  
- **理由**: 既存のgroup_idをIDとして使用。通算連番は不要。

#### MstMissionLimitedTerm
- **現在**: `limited_term_[連番]`
- **提案**: `mission_limited_term_[作品ID]_[イベントID]_[連番]`
- **理由**: イベントごとに連番を振ることで、過去の最大値を考慮する必要がなくなる。

詳細はREADME全文を参照してください。

## ファイル構成

```
id-numbering-analysis/
├── README.md                                   # このファイル
├── scripts/                                    # 分析スクリプト
│   └── generate_id_proposal_simple.py          # 提案サマリー生成スクリプト
└── results/                                    # 分析結果・提案CSV
    ├── id_pattern_analysis.csv                 # 分析結果（全パターン165件）
    ├── id_pattern_analysis_classified.csv      # 分類済み結果（53件）
    ├── id_pattern_proposal.csv                 # 全テーブルの提案（165件）
    └── id_pattern_proposal_summary.csv         # 通算連番テーブルの提案（53件） ★GTRさん確認用
```

## 履歴

- 2026-02-15: 初回分析実施、53テーブルで通算連番を検出
- 2026-02-15: 新しいID採番ルールの提案資料を作成
