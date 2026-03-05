# dungeon-bulk-masterdata-generation

## 概要

限界チャレンジ(dungeon)の複数作品分のインゲームマスタデータを一括生成し、XLSX出力まで自動化するワークフローを確立する。

## 背景・経緯

一括で複数作品分のdungeonマスタデータを作りたいというニーズが発生。現状は作品ごとに個別対応しており、まとめて処理する方法がなかった。

## 解決したい問題

複数作品分をまとめて対応する方法がない。現在は `masterdata-ingame-creator` スキルで1作品ずつ手動実行しており、複数作品への一括対応ができていない。

## 期待する成果

1. 複数作品分のdungeonマスタデータを短時間で作成できる
2. 一括生成のワークフローが確立する（再利用可能な仕組み）
3. XLSX出力まで一括で自動化できる

## ネクストアクション候補

- マスタデータ投入（XLSX提出）

## 解決アイデア

`masterdata-ingame-creator` を複数回連続実行する仕組みを構築する。
作品リストを入力として受け取り、順番にマスタデータ生成 → 検証 → XLSX出力を繰り返す。

## フォルダ構造

```
dungeon-bulk-masterdata-generation/
├── README.md              # このファイル
└── next-actions.md        # ネクストアクション
```

（必要に応じて追加されるフォルダ）
```
├── inputs/                # 作品リスト・設計テキスト
├── outputs/               # 生成されたCSV・XLSX
├── analysis/              # 分析結果
└── scripts/               # バッチ実行スクリプト
```

## 作業ログ

- 2026-03-01 17:00: タスク作成

## 成果物

（今後追加）

## 関連ドキュメント

- `.claude/skills/masterdata-ingame-creator/` - インゲームマスタデータ作成スキル
- `.claude/skills/masterdata-ingame-verifier/` - 検証スキル
- `.claude/skills/masterdata-csv-to-xlsx/` - XLSX出力スキル
