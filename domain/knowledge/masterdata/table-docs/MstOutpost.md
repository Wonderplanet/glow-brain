# MstOutpost 詳細説明

> CSVパス: `projects/glow-masterdata/MstOutpost.csv`

---

## 概要

`MstOutpost` は**ゲームにおける「拠点（ゲート）」の基本設定テーブル**。インゲームでプレイヤーが強化できる拠点（アウトポスト）の存在期間とアセットを定義する。

このテーブルは拠点の「器」を定義するマスタで、拠点に紐付く強化項目の詳細は `MstOutpostEnhancement` と `MstOutpostEnhancementLevel` で管理される。DBスキーマ上は「ゲートの基本設定」とコメントされており、PvPなどのバトルで機能するゲートに対応する。

### ゲームプレイへの影響

- `start_at` / `end_at` により拠点の有効期間を制御する。期間外の拠点は使用できない
- `asset_key` で対応する3Dモデル・UIアセットを参照する

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `asset_key` | varchar(255) | 不可 | - | アセットキー（ゲートのビジュアルアセット識別子） |
| `start_at` | timestamp | 不可 | - | 拠点の開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | 拠点の終了日時（UTC） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 命名規則 / IDの生成ルール

- `id`: `outpost_{連番}` 形式（例: `outpost_1`）
- `asset_key`: ゲートのビジュアル識別子（例: `gate_01`）

---

## 他テーブルとの連携

```
MstOutpost
  └─ id → MstOutpostEnhancement.mst_outpost_id（強化項目の一覧）
       └─ id → MstOutpostEnhancementLevel.mst_outpost_enhancement_id（各強化レベルの設定）
```

---

## 実データ例

**パターン1: 通常拠点**

| ENABLE | id | asset_key | start_at | end_at | release_key |
|--------|-----|-----------|----------|--------|-------------|
| e | outpost_1 | gate_01 | 2024-05-01 00:00:00 | 2037-12-31 23:59:59 | 202509010 |

---

## 設定時のポイント

1. **end_atを遠い未来に設定する**: 現状の拠点は `2037-12-31` まで有効になっており、実質的に恒久的な設定として機能している
2. **asset_keyはクライアント側アセットと一致させる**: 指定したアセットキーがクライアントに存在しない場合、表示が崩れる
3. **start_at / end_atはUTC**: サーバー側はUTCで管理しているため、日本時間（JST = UTC+9）との変換に注意する
4. **拠点は現状1件のみ**: 実データでは `outpost_1` が唯一の拠点として運用されている
5. **新しい拠点を追加する場合**: 対応する `MstOutpostEnhancement` のレコードも合わせて作成する必要がある
