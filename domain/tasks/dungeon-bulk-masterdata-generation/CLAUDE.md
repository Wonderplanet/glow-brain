# CLAUDE.md - dungeon-bulk-masterdata-generation

このディレクトリでClaudeを起動した際のコンテキストです。

---

## このタスクの目的

**限界チャレンジ(dungeon)の全対象作品分のインゲームマスタデータを一括生成し、XLSX提出まで完了する。**

- 対象: URキャラを1体以上持つ15作品（`ur-series-list.md` 参照）
- 手段: `/masterdata-ingame-creator` を作品ごとに順番に実行する
- 最終成果物: 全作品分のXLSXファイル（マスタデータ投入用）

---

## 対象作品一覧（15作品）

| series_id | 作品名 | URキャラ数 | 生成状況 |
|-----------|--------|-----------|---------|
| chi | チェンソーマン | 1 | |
| dan | ダンダダン | 2 | |
| gom | 姫様"拷問"の時間です | 1 | |
| hut | ふつうの軽音部 | 1 | |
| jig | 地獄楽 | 2 | |
| kai | 怪獣８号 | 4 | |
| kim | 君のことが大大大大大好きな100人の彼女 | 1 | |
| mag | 株式会社マジルミエ | 2 | |
| osh | 【推しの子】 | 2 | |
| spy | SPY×FAMILY | 4 | ✅ 完了（normal_00001のみ） |
| sum | サマータイムレンダ | 1 | |
| sur | 魔都精兵のスレイブ | 3 | |
| tak | タコピーの原罪 | 1 | |
| you | 幼稚園WARS | 1 | |
| yuw | 2.5次元の誘惑 | 5 | |

> 詳細は `ur-series-list.md` を参照。

---

## 禁止事項

- `domain/tasks/masterdata-entry/in-game-tables/generated/` **以下のファイルを参照しない**
  - このディレクトリは別タスクの管理データであり、このタスクとは無関係

---

## dungeon マスタデータの仕様

### ブロック種別

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| **normal（通常ブロック）** | `dungeon_{series}_normal_00001` | 100（固定） | 3行（固定） | なし |
| **boss（ボスブロック）** | `dungeon_{series}_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### インゲームID命名規則

```
dungeon_{シリーズID}_{ブロック種別}_{連番5桁}
例: dungeon_spy_normal_00001, dungeon_spy_boss_00001
```

---

## 作業フロー（1作品ずつ実行）

### Step 1: 雑魚敵の確認

各作品の雑魚敵IDを確認してから設計テキストを作る。

```
参照先: domain/knowledge/masterdata/in-game/作品別雑魚敵使用状況調査.md
```

### Step 2: masterdata-ingame-creator を実行

```
/masterdata-ingame-creator
```

設計テキストに含める情報:
- コンテンツ種別: `dungeon（限界チャレンジ）`
- ブロック種別: `normal` or `boss`
- シリーズID（例: `spy`）
- 使用する雑魚敵ID（Step 1 で確認したもの）
- HP・攻撃力・速度のイメージ

### Step 3: 検証

```
/masterdata-ingame-verifier
```

### Step 4: XLSX出力

全作品分が揃ったら:

```
/masterdata-csv-to-xlsx
```

---

## マスタデータ出力先

`masterdata-ingame-creator` の出力先:
```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ}_{英語要約}/
  ├── design.md
  ├── {INGAME_ID}.md
  └── generated/
      ├── MstEnemyStageParameter.csv
      ├── MstEnemyOutpost.csv
      ├── MstPage.csv
      ├── MstKomaLine.csv
      ├── MstAutoPlayerSequence.csv
      └── MstInGame.csv
```

---

## SPY×FAMILY（spy）の参考情報

既に `dungeon_spy_normal_00001` が生成済み（参考として利用可）。
- 出力フォルダ: `domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/`
- 背景アセット: `spy_00005`
- BGM: `SSE_SBG_003_002`
- 雑魚敵: `enemy_spy_00001`（メイン）、`enemy_spy_00101`（サブ）

---

## 関連ファイル

- `ur-series-list.md` — 対象15作品の詳細一覧（URキャラ情報付き）
- `next-actions.md` — 実施手順
- `README.md` — タスク概要

## 関連スキル

- `/masterdata-ingame-creator` — CSVを1作品ずつ生成
- `/masterdata-ingame-verifier` — 生成CSVの品質検証
- `/masterdata-csv-to-xlsx` — CSV→XLSX変換・統合
- `/masterdata-id-numbering` — ID採番ルール確認
