# masterdata-ops-creator 検証手順書

`masterdata-ops-creator` スキルを過去リリースキーのデータで答え合わせ検証し、正解率をレポートする手順。

---

## 前提条件

以下が揃っていること（準備済み）:

```
verify/
├── past_tables/{key}/   ← 各リリースキーの past_tables（DBの状態スナップショット）
└── specs/{key}/         ← 各リリースキーの specs（スプシ一覧・パス情報）
```

| リリースキー | past_tables | specs |
|------------|-------------|-------|
| 202512020 | ✅ 155ファイル | ✅ 3ファイル |
| 202601010 | ✅ 155ファイル | ✅ 4ファイル |
| 202602015 | ✅ 159ファイル | ✅ 3ファイル |

---

## フォルダ構成（完成後）

```
verify/
├── VERIFICATION-GUIDE.md        ← この手順書
├── past_tables/{key}/           ← 参照用（gitignore）
├── specs/{key}/                 ← 参照用（gitignore）
├── generated/{key}/tables/      ← スキルの出力先（gitignore）
└── reports/{key}/               ← 検証レポート（gitignore）
    └── accuracy-report.md
```

---

## リリースキー別 インプット情報

### 202512020

| 項目 | 値 |
|------|-----|
| **スプシフォルダ①** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260101_推しの子/正月特別号！【推しの子】 いいジャン祭＋メインクエスト_仕様書` |
| **スプシフォルダ②** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20251226_年末年始/20251227_年末年始キャンペーン仕様書` |
| **past_tables** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202512020/` |
| **答えCSV数** | 93ファイル（インゲーム系含む） |

### 202601010

| 項目 | 値 |
|------|-----|
| **スプシフォルダ①** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書` |
| **スプシフォルダ②** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/01_GLOW_定常コンテンツ仕様書/ランクマッチ開催設計/GLOW_ランクマッチ開催仕様書` |
| **past_tables** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202601010/` |
| **答えCSV数** | 78ファイル（インゲーム系含む） |

### 202602015

| 項目 | 値 |
|------|-----|
| **スプシフォルダ①** | `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260202_幼稚園WARS いいジャン祭_仕様書` |
| **past_tables** | `domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202602015/` |
| **答えCSV数** | 74ファイル（インゲーム系含む） |

---

## フェーズ1: 検証セッションでCSV生成

リリースキーごとに以下を繰り返す。**1リリースキー = 1セッション**で実施する。

### 1-1. 検証セッションを起動

```bash
# プロジェクトルートから
claude --settings domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/verify-mode-settings.json
```

> このセッションでは `domain/raw-data/masterdata/**` と `projects/glow-masterdata/**` へのアクセスがブロックされる（Read・Bash 両方）。

### 1-2. スキルを呼び出す

```
/masterdata-ops-creator
```

スキル起動後、以下の情報を提供する（リリースキーに応じて変更）:

**202512020 の場合:**
```
リリースキー: 202512020
スプシフォルダ:
  - domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260101_推しの子/正月特別号！【推しの子】 いいジャン祭＋メインクエスト_仕様書
  - domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20251226_年末年始/20251227_年末年始キャンペーン仕様書

出力先: domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/generated/202512020/tables/

【重要】past_tables の参照先:
domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202512020/
（domain/raw-data/masterdata/ は今セッションではアクセス不可のため、上記パスを使用すること）
```

**202601010 の場合:**
```
リリースキー: 202601010
スプシフォルダ:
  - domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260116_地獄楽 いいジャン祭_仕様書
  - domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/01_GLOW_定常コンテンツ仕様書/ランクマッチ開催設計/GLOW_ランクマッチ開催仕様書

出力先: domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/generated/202601010/tables/

【重要】past_tables の参照先:
domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202601010/
（domain/raw-data/masterdata/ は今セッションではアクセス不可のため、上記パスを使用すること）
```

**202602015 の場合:**
```
リリースキー: 202602015
スプシフォルダ:
  - domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260202_幼稚園WARS いいジャン祭_仕様書

出力先: domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/generated/202602015/tables/

【重要】past_tables の参照先:
domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/202602015/
（domain/raw-data/masterdata/ は今セッションではアクセス不可のため、上記パスを使用すること）
```

### 1-3. セッション終了

CSV生成が完了したら検証セッションを終了する（`/exit` または Ctrl+C）。

---

## フェーズ2: 正解率の計算とレポート生成

検証セッション終了後、**通常モード**（ブロックなし）で実施する。

### 2-1. 通常セッションを起動

```bash
# --settings なし（通常モード）
claude
```

### 2-2. 正解率レポートを生成するよう依頼する

以下をそのままClaudeに渡す（リリースキー部分を変えて3回実施）:

```
下記リリースキーの検証レポートを作成してください。

リリースキー: 202512020

生成結果:   domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/generated/202512020/tables/
答えデータ: domain/raw-data/masterdata/released/202512020/tables/

レポート出力先: domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/reports/202512020/accuracy-report.md

評価観点:
1. テーブルカバレッジ: 生成すべきテーブルのうち何テーブル生成できたか（インゲーム系・Unit系・Quest系は対象外）
2. カラム一致率: 生成したテーブルごとにカラム名が答えと一致しているか
3. 行数比較: 生成行数と答え行数の差異
4. 値の正確性: キーカラム（ID、release_key、名称系）の一致状況をサンプリング確認

対象外テーブル（インゲーム系・スキル対象外）:
MstInGame*, MstEnemy*, MstAutoPlayer*, MstQuest*, MstStage*, MstUnit*, MstCharacter*, MstArtwork*, MstAbility*
```

### 2-3. レポートの確認

生成されたレポートを確認し、精度が低いテーブルがあれば原因を特定する。

---

## フェーズ3: 集計・改善サイクル

3リリースキー分のレポートが揃ったら、横断集計を行う。

```
3リリースキーの検証レポートを横断集計して、総合サマリーを作成してください。

対象レポート:
- domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/reports/202512020/accuracy-report.md
- domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/reports/202601010/accuracy-report.md
- domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/reports/202602015/accuracy-report.md

出力先: domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/reports/summary-report.md

以下を含めること:
- リリースキー別のテーブルカバレッジ・カラム一致率・行数一致率
- 全体の正解率スコア（100点満点換算）
- 精度が低かったテーブル TOP5 と原因仮説
- スキルの手順書・インプットに対する改善提案
```

---

## 評価基準（目安）

| スコア | 評価 | 判断 |
|--------|------|------|
| 90点以上 | 優秀 | スキルの実用水準に達している |
| 70〜89点 | 良好 | 一部手順書の補強が必要 |
| 50〜69点 | 要改善 | 特定機能の手順書を見直す |
| 50点未満 | 要再設計 | スキルのワークフロー自体を見直す |

---

## 注意事項

- **検証セッション中は答えファイルにアクセスしないこと**（ブロックされるが、回避を試みないこと）
- **スキルへの指示に past_tables のパスを明示的に含めること**（スキルのStep4でデフォルトパスを使おうとするとブロックされる）
- **1リリースキーずつ実施すること**（並行実施すると出力先が混在するリスクがある）
- **レポート生成は通常モードで行うこと**（答えファイルを参照するため）
