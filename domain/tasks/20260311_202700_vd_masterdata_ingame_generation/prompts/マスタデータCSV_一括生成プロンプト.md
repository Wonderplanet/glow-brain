# マスタデータCSV 一括生成プロンプト

## 目的

`vd-ingame-design-creator/` 配下のブロックフォルダを確認し、マスタデータCSVが未生成のブロックを対象に、スキルを使ってCSV生成とdesign.json生成を行ってください。

---

## 作業手順

### Step 1: 対象ブロックの特定

`vd-ingame-design-creator/` 配下のフォルダを確認し、`generated/` フォルダ以下にCSVが存在しないブロックをリストアップしてください。

- `vd_all/` は除外する
- 対象はブロック種別フォルダ（例: `vd_kai_normal_00001/`、`vd_kai_boss_00001/` など）のみ
- すでに `generated/` 配下にCSVが存在するブロックは作業完了済みとしてスキップする

### Step 2: CSVとdesign.jsonの一括生成

対象ブロックを **最大3つずつ並行して** スキルを実行してください。

- ユーザーへの確認は不要です
- 3ブロック分が完了したら、次の3ブロックに進んでください
- 各ブロックで以下の順番でスキルを実行してください

#### 実行順序（ブロックごと）

**① マスタデータCSV生成**

インゲーム設計書（`design.md`）をもとに、マスタデータCSVを生成します。

```
/vd-masterdata-ingame-data-creator ブロックID={ブロックID}
```

**② design.json生成**

①で生成したCSVとインゲーム設計書（`design.md`）をもとに、ブロック基礎設計シートへの入力データをまとめたJSONを生成します。

```
/vd-masterdata-ingame-design-json-creator ブロックID={ブロックID}
```

**引数の例**:
- `vd_kai_normal_00001` → ブロックID: `vd_kai_normal_00001`
- `vd_osh_boss_00001` → ブロックID: `vd_osh_boss_00001`

---

## 完了条件

全ての対象ブロックについて `generated/` 配下のCSVと `design.json` が生成されたら作業完了です。
