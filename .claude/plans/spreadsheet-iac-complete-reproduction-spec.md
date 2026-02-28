# 📘 Spreadsheet IaC 完全再現基盤設計書（Python実装仕様）

## 既存スプレッドシートを100%コード化し、完全再生成可能にするための実装ドキュメント

作成日: 2026-02-28

------------------------------------------------------------------------

# 0. 🎯 本ドキュメントの目的

本書は、既存の Google Spreadsheet / Excel ファイルを元に、

-   構造
-   数式
-   レイアウト（色・罫線・フォント・結合・幅など）
-   条件付き書式
-   シート設定
-   保護設定

を **完全再現可能なコード（Python）として抽出・管理・再生成できる基盤**
を構築するための 最終実装仕様書である。

目標は以下の2点：

1.  既存スプシを完全コード化できること
2.  そのコードを編集することで、スプシを再生成・調整できること

------------------------------------------------------------------------

# 1. 🏗 全体アーキテクチャ

    既存Spreadsheet
            ↓
    Snapshot Engine（抽出）
            ↓
    IaC JSON構造
            ↓
    Git管理
            ↓
    Build Engine（再生成）
            ↓
    完全再現Spreadsheet

------------------------------------------------------------------------

# 2. 📂 ディレクトリ構成

    spreadsheet-iac/
    ├─ snapshot/
    │   └─ extract.py
    ├─ specs/
    │   ├─ sheets.json
    │   ├─ formulas.json
    │   ├─ layout.json
    │   └─ conditional_format.json
    ├─ builder/
    │   └─ build.py
    ├─ diff/
    │   └─ diff_engine.py
    ├─ output/
    ├─ snapshots/
    └─ reports/

------------------------------------------------------------------------

# 3. 🧱 管理対象要素（完全再現対象）

## 3.1 構造

-   シート名
-   シート順
-   シート表示/非表示
-   freeze panes
-   autofilter
-   tab color

## 3.2 セル単位情報

-   value
-   数式（R1C1形式）
-   number_format
-   背景色
-   フォント（name / size / bold / italic / color）
-   罫線（上下左右 style + color）
-   alignment（horizontal / vertical / wrap）
-   protection（locked / hidden）

## 3.3 シート単位情報

-   列幅
-   行高さ
-   結合セル範囲
-   条件付き書式ルール
-   データ検証（将来拡張）

------------------------------------------------------------------------

# 4. 📸 Snapshot Engine 設計

## 4.1 目的

既存スプレッドシートを読み取り、完全再現可能なJSON構造へ変換する。

## 4.2 抽出内容

``` json
{
  "sheets": [
    {
      "name": "reward",
      "index": 0,
      "freeze_panes": "A2",
      "column_widths": { "A": 18.0, "B": 12.0 },
      "row_heights": { "1": 28.0 },
      "merges": ["A1:F1"],
      "cells": [
        {
          "row": 1,
          "col": 1,
          "value": "タイトル",
          "r1c1": null,
          "number_format": "General",
          "style": {
            "fill": "1F2937",
            "font": {
              "name": "Calibri",
              "size": 11,
              "bold": true,
              "color": "FFFFFF"
            },
            "border": {
              "left": "thin",
              "right": "thin",
              "top": "thin",
              "bottom": "thin"
            },
            "alignment": {
              "horizontal": "center",
              "vertical": "center",
              "wrap": false
            }
          }
        }
      ],
      "conditional_formatting": [
        {
          "range": "C14:C200",
          "type": "cellIs",
          "operator": "lessThan",
          "formula": "0",
          "style": { "fill": "FEE2E2" }
        }
      ]
    }
  ]
}
```

------------------------------------------------------------------------

# 5. 🔁 Build Engine 設計

## 5.1 実装原則

-   Snapshot JSONを唯一の真実とする
-   JSONから100%復元可能にする
-   スプレッドシート直接編集禁止（再生成前提）

## 5.2 再生成順序

1.  Workbook生成
2.  シート作成（順序再現）
3.  列幅 / 行高さ適用
4.  セル値・数式適用
5.  スタイル適用
6.  結合適用
7.  freeze / autofilter適用
8.  条件付き書式適用
9.  保護適用

------------------------------------------------------------------------

# 6. 🎨 レイアウト完全再現仕様

## 6.1 背景色

-   RGB(6桁)保存
-   openpyxl PatternFillで復元

## 6.2 フォント

-   name
-   size
-   bold
-   italic
-   underline
-   color

## 6.3 罫線

-   各辺 individually 保存
-   style + color

## 6.4 結合

-   merge_cells配列として保存

## 6.5 列幅 / 行高さ

-   floatで保存
-   dimensionへ直接適用

------------------------------------------------------------------------

# 7. 🧪 Diff Engine 設計

## 比較対象

-   r1c1
-   value
-   style（fill/font/border/alignment）
-   merges
-   widths
-   heights
-   conditional rules

## 出力形式

Markdown形式レポート：

    ## Changes

    - reward!G14 formula changed
    - reward!A1 background changed
    - reward sheet merge range updated

------------------------------------------------------------------------

# 8. 🚀 運用フロー

1.  既存スプシをSnapshot
2.  JSONをGit管理
3.  JSON編集（調整）
4.  build実行
5.  再生成
6.  diff確認
7.  本番反映

------------------------------------------------------------------------

# 9. 🧰 使用ライブラリ

-   Python 3.11+
-   openpyxl
-   deepdiff
-   pydantic
-   pytest

Node.jsは不要。

------------------------------------------------------------------------

# 10. 🎯 到達状態

この基盤が完成すると：

-   既存スプシを完全コード化可能
-   レイアウトも完全再現可能
-   調整はコード差分で管理可能
-   CIで変更追跡可能
-   非エンジニアは生成物のみ操作

------------------------------------------------------------------------

# 11. ✅ 結論

本設計は、スプレッドシートを

「データ入力ツール」から
「UI付きアプリケーション定義」へ昇華させる基盤である。

既存スプシを100%再現できることを前提に、
その上で拡張・改善・標準化を進められる。

------------------------------------------------------------------------

# 以上
