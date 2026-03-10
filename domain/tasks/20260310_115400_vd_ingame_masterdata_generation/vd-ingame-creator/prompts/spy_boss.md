# spy Bossブロック インゲームマスタデータ生成プロンプト

## 使用スキル

`/vd-ingame-creator`

---

## プロンプト本文

```
/vd-ingame-creator 作品ID spy の Bossブロック を作る。

---

### 出力先

/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-shuna/domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/

---

### 前提

限界チャレンジ(vd)は新規コンテンツのため、既存データはありません。
release_key は全て「202604010」で作成してください。

---

### 出現敵キャラ

#### 候補データ

domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/作品別登場キャラ一覧.csv

#### フィルタ条件

作品ID=spy AND データタイプ=ボスブロック登場

---

### 敵キャラステータス

下記の MstEnemyStageParameter の値をそのまま使ってください。

domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv
```

---

## 補足情報

| 項目 | 値 |
|------|-----|
| 作品ID | spy |
| ブロック種別 | Boss |
| release_key | 202604010 |
| 出力先 | `vd-ingame-creator/` |

### 対象敵キャラ（作品別登場キャラ一覧.csv より）

| 敵キャラID | 敵キャラ名 |
|------------|------------|
| chara_spy_00401 | フランキー・フランクリン |
