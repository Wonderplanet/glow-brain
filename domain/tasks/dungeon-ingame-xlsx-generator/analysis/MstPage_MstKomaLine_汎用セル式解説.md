# MstPage / MstKomaLine 投入用 汎用セル式 解説

## 概要

インゲーム設計書の「コマ設計」セクションのデータから、MstPage・MstKomaLine CSV に投入できる形式のデータを自動表示するための汎用セル式。

シートの種類（ストーリー・チャレンジ・高難度・降臨バトル・通常ブロック等）によって「■コマ設計」や「page_id」のセル位置が異なるため、**MATCH+INDIRECT** で行位置を動的に取得し、**LET** で変数化することで全シート共通の式を実現している。

---

## 配置場所

| 行 | 列 | 内容 |
|----|-----|------|
| **28** | EA | ラベル「★MstPage投入用▶」（固定テキスト） |
| **28** | EB〜ED | MstPageのカラムヘッダー（ENABLE / id / release_key） |
| **29** | EB〜ED | MstPageデータ式（3列） |
| **30** | EA | ラベル「★MstKomaLine投入用▶」（固定テキスト） |
| **30** | EB〜FR | MstKomaLineのカラムヘッダー（43列） |
| **31** | EB〜FR | MstKomaLine 1行目データ式 |
| **32** | EB〜FR | MstKomaLine 2行目データ式 |
| **33** | EB〜FR | MstKomaLine 3行目データ式 |
| **34** | EB〜FR | MstKomaLine 4行目データ式 |
| **35** | EB〜FR | MstKomaLine 5行目データ式 |

---

## 汎用化の仕組み：3つの動的参照変数

式の中で使われる変数は3つ。LET関数で定義されている。

| 変数名 | 計算式 | 意味 |
|--------|--------|------|
| `er` | `MATCH("敵ゲートID",E:E,0)+1` | E列で「敵ゲートID」を探し、その1行下（page_idのデータ行番号） |
| `rr` | `MATCH("リリースキー",N:N,0)+1` | N列で「リリースキー」を探し、その1行下（release_keyのデータ行番号） |
| `kr` | `MATCH("■コマ設計",B:B,0)+3` 〜 `+7` | B列で「■コマ設計」を探し、+3〜+7行目（コマ1〜5行目のデータ行番号） |

### シートタイプ別の実際の行番号

| シートタイプ | `er`（page_id行） | `rr`（release_key行） | `kr`（1行目） |
|------------|----------|------------|---------|
| ストーリー / チャレンジ1-2話 / 降臨バトル | 13 | 13 | 31 or 38 |
| チャレンジ3-4話 / 高難度 | 13 | 13 | 32 |
| 通常ブロック / ボスブロック | 14 | 14 | 19 |

> MATCH が自動でヘッダー行を探すため、式を変更せずにすべてのシートで動作する。

---

## 共通ルール：行データが空の場合は全列空白を返す

MstKomaLine の全列に共通して、**D列（行パターンID）が空 = その行はコマなし**として扱い、空文字 `""` を返す。

```
LET(kr, ..., IF(INDIRECT("D"&kr)="", "", [実際の値]))
```

つまり D列が空なら式は `""` を返し、空でなければ各フィールドの値を計算する。

---

## MstPage 用の式（29行目）

### EB29：ENABLE

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
  IF(INDIRECT("E"&er)="","","e"))
```

- `er` 行のE列（page_id）が空なら `""`、入力済みなら `"e"` を返す

---

### EC29：id（page_id）

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
  IF(INDIRECT("E"&er)="","",INDIRECT("E"&er)))
```

- `er` 行のE列の値をそのまま返す（例: `event_jig1_charaget01_00001`）

---

### ED29：release_key

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
     rr,MATCH("リリースキー",N:N,0)+1,
  IF(INDIRECT("E"&er)="","",TEXT(INDIRECT("N"&rr),"0")))
```

- `rr` 行のN列の値を整数テキストに変換して返す（例: `202601010`）
- `TEXT(値, "0")` で `.0` が付かない整数文字列にする

---

## MstKomaLine 用の式（31〜35行目）

### 行ごとの違い（`kr` のオフセットのみ）

| Excelの行 | コマ行 | `kr` の計算 |
|----------|-------|------------|
| 31 | 1行目 | `MATCH("■コマ設計",B:B,0)+3` |
| 32 | 2行目 | `MATCH("■コマ設計",B:B,0)+4` |
| 33 | 3行目 | `MATCH("■コマ設計",B:B,0)+5` |
| 34 | 4行目 | `MATCH("■コマ設計",B:B,0)+6` |
| 35 | 5行目 | `MATCH("■コマ設計",B:B,0)+7` |

行ごとに違うのは `kr` の `+数値` と、`id` のサフィックス（`_1`〜`_5`）と、`row` の固定値（`1`〜`5`）だけ。それ以外の式は全行共通。

---

### EB列：ENABLE

```
=LET(kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","","e"))
```

- D列（行パターンID）が空 → `""`
- D列に値あり → `"e"`

---

### EC列：id

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
     kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",INDIRECT("E"&er)&"_1"))
```

- page_id + `_1`（2行目なら `_2`、以降同様）
- 例: `event_jig1_charaget01_00001_1`

---

### ED列：mst_page_id

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
     kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",INDIRECT("E"&er)))
```

- page_idをそのまま返す（全行同じ値）

---

### EE列：row（コマ行番号）

```
=LET(kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",1))
```

- `1`〜`5` の固定値（31行目=1, 32行目=2, ...）

---

### EF列：height（コマ高さ）

```
=LET(kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",INDIRECT("H"&kr)))
```

- コマ設計の H列（コマ高さ）の値を返す（例: `0.55`）

---

### EG列：koma_line_layout_asset_key（行パターンID）

```
=LET(kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",INT(INDIRECT("D"&kr))))
```

- D列（行パターンID）を整数に変換して返す（例: `6.0` → `6`）

---

## コマ1（koma1）の式

### EH列：koma1_asset_key

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",IFERROR(INDIRECT("R"&kr),"")))
```

- R列（コマ背景 = 画像asset_key）の値を返す（例: `jig_00001`）
- R列がない（通常ブロック等）場合は `IFERROR` で `""` を返す

---

### EI列：koma1_width

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",INDIRECT("J"&kr)))
```

- J列（コマ幅1）の値を返す（行パターンシートからVLOOKUPで計算済みの値）
- 例: `0.5`（2等分パターンの場合）

---

### EJ列：koma1_back_ground_offset

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",IF(INDIRECT("J"&kr)=1,0,-1)))
```

- J列（コマ幅1）= 1（画面全体を使う1コマ）なら `0`、それ以外なら `-1`
- ⚠️ 近似値（0.2, 0.7 など特殊なオフセットは手動調整が必要）

---

### EK列：koma1_effect_type

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF(OR(INDIRECT("U"&kr)="",ISBLANK(INDIRECT("U"&kr))),"None",INDIRECT("U"&kr))))
```

- U列（コマ効果1）が空またはブランクなら `"None"`、値があればその値を返す
- 例: `"None"` / `"AttackPowerDown"`

---

### EL列：koma1_effect_parameter1

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF(OR(INDIRECT("U"&kr)="",ISBLANK(INDIRECT("U"&kr))),0,INDIRECT("Z"&kr))))
```

- コマ効果（U列）が空なら `0`、あれば Z列（効果時間）の値を返す

---

### EM列：koma1_effect_parameter2

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF(OR(INDIRECT("U"&kr)="",ISBLANK(INDIRECT("U"&kr))),0,INDIRECT("AB"&kr))))
```

- コマ効果が空なら `0`、あれば AB列（効果数値）の値を返す

---

### EN・EO・EP列：koma1_effect_target_side / colors / roles

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","","All"))
```

- コマがある（D列に値あり）なら常に `"All"` を返す（固定値）

---

## コマ2〜4（koma2 / koma3 / koma4）の式

コマ2〜4は「コマ幅が有効かどうか」のチェックが追加されている。

### コマ存在チェックのロジック

コマ2はL列（コマ幅2）、コマ3はN列（コマ幅3）、コマ4はP列（コマ幅4）を参照。

```
AND(INDIRECT("L"&kr)<>"", INDIRECT("L"&kr)<>"none",
    INDIRECT("L"&kr)<>"error", NOT(ISBLANK(INDIRECT("L"&kr))))
```

この条件が **True** → コマ2あり、**False** → コマ2なし

| L列の値 | コマ存在チェック結果 |
|---------|-------------|
| `0.5`（数値） | True（コマあり） |
| `"none"`（1コマ全体で使い切った） | False（コマなし） |
| `""`（空文字） | False（コマなし） |
| `"error"`（行パターンシート参照エラー） | False（コマなし） |

---

### EQ列：koma2_asset_key

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック], IFERROR(INDIRECT("R"&kr),""), "")))
```

- コマ2あり → R列（全コマ共通の背景画像）を返す
- コマ2なし → `""` を返す

---

### ER列：koma2_width

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック], INDIRECT("L"&kr), "")))
```

- コマ2あり → L列（コマ幅2）を返す
- コマ2なし → `""` を返す

---

### ES列：koma2_back_ground_offset

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック], IF(INDIRECT("L"&kr)=1,0,-1), "__NULL__")))
```

- コマ2あり → コマ幅2=1 なら `0`、それ以外 `-1`
- コマ2なし → `"__NULL__"` を返す（コマなし判定のCSV値）

---

### ET列：koma2_effect_type

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック],
    IF(OR(INDIRECT("AD"&kr)="",ISBLANK(INDIRECT("AD"&kr))),"None",INDIRECT("AD"&kr)),
  "None")))
```

- コマ2あり → AD列（コマ効果2）が空なら `"None"`、あればその値
- コマ2なし → `"None"` を返す（コマなしでもeffect_typeは`"None"`固定）

---

### EU列：koma2_effect_parameter1

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック],
    IF(OR(INDIRECT("AD"&kr)="",ISBLANK(INDIRECT("AD"&kr))),0,INDIRECT("AI"&kr)),
  "")))
```

- コマ2あり → コマ効果2が空なら `0`、あれば AI列（効果時間2）
- コマ2なし → `""`

---

### EV列：koma2_effect_parameter2

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック],
    IF(OR(INDIRECT("AD"&kr)="",ISBLANK(INDIRECT("AD"&kr))),0,INDIRECT("AK"&kr)),
  "")))
```

- コマ2あり → コマ効果2が空なら `0`、あれば AK列（効果数値2）
- コマ2なし → `""`

---

### EW列：koma2_effect_target_side

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック], "All", "__NULL__")))
```

- コマ2あり → `"All"`
- コマ2なし → `"__NULL__"`

---

### EX・EY列：koma2_effect_target_colors / roles

```
=LET(kr,..., IF(INDIRECT("D"&kr)="","",
  IF([コマ2存在チェック], "All", "")))
```

- コマ2あり → `"All"`
- コマ2なし → `""` （colors/rolesはnullの代わりに空文字）

> ⚠️ target_side だけ `"__NULL__"` で、colors と roles は `""` という仕様の違いに注意

---

## コマ3・コマ4の式（コマ2と対応関係）

コマ3はN列（コマ幅3）、コマ4はP列（コマ幅4）を参照。効果列の対応は以下の通り。

| コマ | 幅列 | 効果type列 | 効果parameter1列 | 効果parameter2列 |
|-----|------|-----------|----------------|----------------|
| koma1 | J列 | U列 | Z列 | AB列 |
| koma2 | L列 | AD列 | AI列 | AK列 |
| koma3 | N列 | AM列 | AR列 | AT列 |
| koma4 | P列 | AV列 | BA列 | BC列 |

コマ3・コマ4もコマ2と全く同じロジック。参照列が上記テーブルに従って変わるだけ。

---

## FR列：release_key（最終列）

```
=LET(er,MATCH("敵ゲートID",E:E,0)+1,
     rr,MATCH("リリースキー",N:N,0)+1,
     kr,MATCH("■コマ設計",B:B,0)+3,
  IF(INDIRECT("D"&kr)="","",TEXT(INDIRECT("N"&rr),"0")))
```

- D列が空なら `""` を返す（行全体がコマなし）
- 入力済みなら `rr` 行のN列（release_key）を整数テキストで返す
- 全コマ行（31〜35行目）で同じ値になる（ページ内で共通）

---

## コマなし時の各フィールドの値まとめ

| フィールド | コマあり | コマなし |
|----------|---------|---------|
| asset_key | R列の値（例: `jig_00001`） | `""` |
| width | J/L/N/P列の値 | `""` |
| back_ground_offset | `0` or `-1` | `"__NULL__"` |
| effect_type | `"None"` or コマ効果値 | `"None"` |
| effect_parameter1 | `0` or 効果時間値 | `""` |
| effect_parameter2 | `0` or 効果数値値 | `""` |
| effect_target_side | `"All"` | `"__NULL__"` |
| effect_target_colors | `"All"` | `""` |
| effect_target_roles | `"All"` | `""` |

---

## 既知の制限

### bg_offset の精度

`koma_back_ground_offset` は `IF(コマ幅=1, 0, -1)` の近似計算を使用。

ストーリー系コンテンツ（全コマで同じ背景を中央or左基準で配置）では正解CSVと完全一致するが、チャレンジ・高難度・降臨バトルの既存データには `0.2`, `0.3`, `0.7` など特殊なオフセット値が含まれており、この式では再現できない。

**対処法**: 設計書に「コマ背景オフセット」入力列を追加し、式で直接その値を参照するよう改修する。
