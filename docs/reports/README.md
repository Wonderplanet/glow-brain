# マスタデータレポート

このディレクトリには、GLOWゲームのマスタデータから抽出した各種レポートが格納されています。

## 📁 レポート一覧

### SPY×FAMILY 関連

1. **spy_family_summary.md** - 📊 概要版（推奨）
   - キャラクター一覧表
   - イベント概要
   - 統計サマリー
   - 手早く情報を確認したい場合に最適

2. **spy_family_characters_and_events_report.md** - 📖 詳細版
   - 全キャラクターの詳細情報（ステータス、アビリティ、説明文）
   - イベント情報の詳細（ミッション、ボーナスユニット、ステージ設定）
   - 完全な情報が必要な場合に参照

3. **spy_family_sql_analysis.md** - 🔍 SQL分析版（NEW）
   - DuckDBを使った複数テーブルのJOIN分析
   - キャラクター統計、イベント詳細、戦闘力ランキング
   - 実行可能なSQLクエリ例付き
   - データ分析の方法を学びたい場合に最適

## 📝 レポートの使い方

### クイックスタート
まずは概要版（`spy_family_summary.md`）をご覧ください。表形式でわかりやすくまとめています。

### 詳細情報が必要な場合
詳細版（`spy_family_characters_and_events_report.md`）で各キャラクターの完全な情報を確認できます。

### データ分析を学びたい場合
SQL分析版（`spy_family_sql_analysis.md`）でDuckDBを使ったJOIN分析の方法を学べます。実行可能なSQLクエリ例が含まれています。

## 🔍 データソース

これらのレポートは以下のマスタデータCSVから抽出されています：

```
projects/glow-masterdata/
├── MstSeries.csv          # 作品情報
├── MstSeriesI18n.csv      # 作品名（多言語）
├── MstUnit.csv            # キャラクター基本情報
├── MstUnitI18n.csv        # キャラクター名・説明（多言語）
├── MstEvent.csv           # イベント基本情報
├── MstEventI18n.csv       # イベント名・説明（多言語）
├── MstMissionEvent.csv    # イベントミッション
├── MstEventBonusUnit.csv  # イベントボーナスキャラ
└── MstStageEventSetting.csv # イベントステージ設定
```

## 📅 更新履歴

- 2026-01-09: SPY×FAMILYキャラクター・イベントレポート作成
