# {{SERIES_ID}} {{BLOCK_TYPE}} インゲームマスタデータ生成プロンプト

## 使用スキル

`/vd-ingame-creator`

---

## プロンプト本文

```
/vd-ingame-creator 作品ID {{SERIES_ID}} の {{BLOCK_TYPE}} を作る。

---

### 出力先

/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-shuna/domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/

---

### 前提

限界チャレンジ(vd)は新規コンテンツのため、既存データはありません。
release_key は全て「{{RELEASE_KEY}}」で作成してください。

---

### 出現敵キャラ

#### 候補データ

domain/tasks/20260310_115400_vd_ingame_masterdata_generation/specs/作品別登場キャラ一覧.csv

#### フィルタ条件

作品ID={{SERIES_ID}} AND データタイプ={{BLOCK_TYPE_FILTER}}

---

### 敵キャラステータス

下記の MstEnemyStageParameter の値をそのまま使ってください。

domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv
```

---

## 補足情報

| 項目 | 値 |
|------|-----|
| 作品ID | {{SERIES_ID}} |
| ブロック種別 | {{BLOCK_TYPE}} |
| release_key | {{RELEASE_KEY}} |
| 出力先 | `vd-ingame-creator/` |

### 対象敵キャラ（作品別登場キャラ一覧.csv より）

※ 該当行を転記してください。

| 敵キャラID | 敵キャラ名 |
|------------|------------|
| （記入） | （記入） |

---

## プレースホルダー一覧

| プレースホルダー | 説明 | 例 |
|-----------------|------|-----|
| `{{SERIES_ID}}` | 作品ID | `dan`, `chi`, `gom` ... |
| `{{BLOCK_TYPE}}` | ブロック種別（日本語） | `Normalブロック` / `Bossブロック` |
| `{{BLOCK_TYPE_FILTER}}` | CSVフィルタ用データタイプ | `ノーマルブロック登場` / `ボスブロック登場` |
| `{{RELEASE_KEY}}` | release_key | `202604010` |
