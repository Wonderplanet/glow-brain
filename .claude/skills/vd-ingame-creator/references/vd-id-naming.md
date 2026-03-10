# VD 専用 ID 命名規則

限界チャレンジ（VD）インゲームマスタデータで使用するIDの命名規則。

---

## MstInGame.id（インゲームID）

VDのIDプレフィックスは **`vd_`**（既存の `dungeon_` ではない）。

以下の4テーブルで同じIDを共有する:
- `MstAutoPlayerSequence.sequence_set_id`
- `MstPage.id`
- `MstEnemyOutpost.id`

### 命名パターン

| ブロック種別 | パターン | 例 |
|------------|---------|-----|
| bossブロック | `vd_{作品ID}_boss_{連番5桁}` | `vd_kai_boss_00001` |
| normalブロック | `vd_{作品ID}_normal_{連番5桁}` | `vd_kai_normal_00001` |

### 作品ID一覧

| 作品 | 作品ID |
|------|--------|
| SPY×FAMILY | `spy` |
| ダンダダン | `dan` |
| 姫様"拷問"の時間です | `gom` |
| チェンソーマン | `chi` |
| 株式会社マジルミエ | `mag` |
| 怪獣８号 | `kai` |
| 2.5次元の誘惑 | `yuw` |
| 魔都精兵のスレイブ | `sur` |
| サマータイムレンダ | `sum` |
| 地獄楽 | `jig` |
| タコピーの原罪 | `tak` |
| 【推しの子】 | `osh` |
| 幼稚園WARS | `you` |
| 100カノ | `kim` |
| ふつうの軽音部 | `hut` |
| あやかしトライアングル | `aya` |

### 連番ルール

- 1作品につき boss 1個（`boss_00001`）+ normal N個（`normal_00001`〜`normal_000XX`）
- 同一作品の追加ブロックは連番を増やす（`normal_00002`, `normal_00003` ...）

---

## MstEnemyStageParameter.id

### 命名パターン

```
{プレフィックス}_{キャラ略称}_{キャラ番号}_{インゲームID短縮形}_{character_unit_kind}_{color}
```

### インゲームID短縮形（VD共通）

```
{作品ID}_vd
```

> bossブロック・normalブロックで短縮形は共通

### プレフィックスの使い分け

| プレフィックス | 意味 | mst_enemy_character_id の形式 |
|--------------|------|-------------------------------|
| `c_` | プレイヤーキャラが敵として登場 | `chara_{シリーズ}_{番号}` |
| `e_` | 敵専用キャラのパラメータ | `enemy_{シリーズ}_{番号}` |

### 実例

| 役割 | 例 |
|------|-----|
| ボス（プレイヤーキャラ） | `c_kai_00201_kai_vd_Boss_Red` |
| 雑魚（敵キャラ） | `e_kai_00001_kai_vd_Normal_Colorless` |
| レアファントム | `e_kai_00201_kai_vd_Normal_Colorless`（ファントム用ID） |

---

## MstAutoPlayerSequence.id

### 命名パターン

```
{sequence_set_id}_{sequence_element_id}
```

### 実例

| 行 | 例 |
|----|-----|
| boss行1 | `vd_kai_boss_00001_1` |
| boss行2 | `vd_kai_boss_00001_2` |
| normal行1 | `vd_kai_normal_00001_1` |
| normal行2 | `vd_kai_normal_00001_2` |

---

## MstKomaLine.id

### 命名パターン

```
{mst_page_id}_{row番号}
```

### 実例

| 例 | 備考 |
|-----|------|
| `vd_kai_boss_00001_1` | bossブロック（1行のみ） |
| `vd_kai_normal_00001_1` | normalブロック フロア1 |
| `vd_kai_normal_00001_2` | normalブロック フロア2 |
| `vd_kai_normal_00001_3` | normalブロック フロア3 |

---

## 禁止パターン

| NG | 理由 |
|----|------|
| `dungeon_kai_boss_00001`（dungeon_ プレフィックス） | VDは `vd_` プレフィックスを使用する |
| スペース・大文字英字・ドット・スラッシュ | DB・API処理での問題 |
| `__` のダブルアンダースコア | `__NULL__` と混在するリスク |
| 日本語・全角文字 | 文字化けリスク |
