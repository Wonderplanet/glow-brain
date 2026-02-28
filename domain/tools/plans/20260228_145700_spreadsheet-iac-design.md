# Spreadsheet IaC (Infrastructure as Code) 設計ドキュメント

作成日: 2026-02-28

------------------------------------------------------------------------

# 1. 目的

本ドキュメントは、Google Spreadsheet / Excel を **Infrastructure as Code
(IaC)** として管理するための設計基盤を定義する。

目標：

-   スプレッドシートを「生成物」として扱う
-   ロジックをコードで管理する
-   非エンジニアは入力のみ編集可能
-   再生成前提の安定運用
-   差分確認可能な構造

------------------------------------------------------------------------

# 2. 基本思想

Spreadsheet を以下の4層で管理する。

  層                役割
  ----------------- ------------------------
  Formula Catalog   数式テンプレート定義
  Sheet Manifest    シート構造・領域定義
  Generator         xlsx生成エンジン
  Output            生成されたxlsxファイル

Spreadsheetは常に「生成物」とする。

------------------------------------------------------------------------

# 3. ディレクトリ構造例

    spreadsheet-iac/
    ├─ specs/
    │   ├─ reward.sheet.json
    │   └─ ranking.sheet.json
    ├─ formulas/
    │   ├─ reward.json
    │   └─ ranking.json
    ├─ generator/
    │   ├─ build.ts
    │   ├─ snapshot.ts
    │   └─ diff.ts
    ├─ snapshots/
    │   ├─ before.json
    │   └─ after.json
    ├─ reports/
    │   └─ diff.md
    └─ output/
        └─ reward.xlsx

------------------------------------------------------------------------

# 4. Region（領域）設計

1シート内に複数テーブルが存在する前提で、Region単位で管理する。

例：

reward sheet - summary_block (A1:F12) - input_block (A14:F200) -
calc_block (G14:K200) - view_block (A210:K260)

Manifest例：

``` json
{
  "name": "reward",
  "regions": [
    {
      "name": "input_block",
      "range": "A14:F200",
      "editable": true
    },
    {
      "name": "calc_block",
      "range": "G14:K200",
      "formulas": {
        "col": 7,
        "template": "reward_calc"
      }
    }
  ]
}
```

------------------------------------------------------------------------

# 5. Formula Catalog 設計

数式はR1C1形式で定義する。

例：

``` json
{
  "reward_calc": {
    "r1c1": "=IF(RC[-5]=\"A\", RC[-4]*2, RC[-4])",
    "description": "タイプAなら倍"
  }
}
```

A1参照は禁止。 R1C1のみ許可。

------------------------------------------------------------------------

# 6. ビルドフロー

1.  既存xlsxをsnapshot（数式をJSON化）
2.  manifest + formulaから新規xlsx生成
3.  生成後snapshot
4.  before/afterをdiff
5.  差分レポート生成

------------------------------------------------------------------------

# 7. Snapshot構造

``` json
{
  "sheets": [
    {
      "name": "reward",
      "cells": [
        {
          "row": 14,
          "col": 7,
          "r1c1": "=IF(RC[-5]=\"A\", RC[-4]*2, RC[-4])"
        }
      ]
    }
  ]
}
```

------------------------------------------------------------------------

# 8. Diff戦略

比較対象：

-   数式（R1C1）
-   値（定数セルのみ）
-   保護設定

レポート例：

## Changes

-   reward!G14 formula changed
-   ranking!D2 constant value changed (10 → 12)

------------------------------------------------------------------------

# 9. 非エンジニア運用方針

-   editable=true のRegionのみ編集可能
-   計算Regionは保護
-   定数変更はsettingsシートで管理
-   数式はGit経由でのみ変更

------------------------------------------------------------------------

# 10. 実装技術候補

## Node.js

-   exceljs
-   fast-json-patch（diff）

## Python

-   openpyxl
-   deepdiff

------------------------------------------------------------------------

# 11. 今後の拡張

-   QUERYテンプレート管理
-   シート依存関係グラフ生成
-   CI統合（PR時自動diff）
-   Drive API自動アップロード

------------------------------------------------------------------------

# 12. 結論

Spreadsheet IaCにより、

-   可読性向上
-   拡張性向上
-   保守性向上
-   差分追跡可能
-   再生成前提の安定構造

を実現できる。

本ドキュメントを基盤として実装を開始する。
