# URキャラを持つ作品一覧（dungeon一括生成対象）

> 調査日: 2026-03-01
> 調査元: `projects/glow-masterdata/MstUnit.csv` × `MstSeries.csv` × `MstSeriesI18n.csv`
> 条件: `rarity = 'UR'` かつ `ENABLE = 'e'` のユニットを1体以上持つシリーズ

---

## 対象シリーズ一覧（15作品）

| # | series_id | 作品名 | URキャラ数 |
|---|-----------|--------|-----------|
| 1 | chi | チェンソーマン | 1 |
| 2 | dan | ダンダダン | 2 |
| 3 | gom | 姫様"拷問"の時間です | 1 |
| 4 | hut | ふつうの軽音部 | 1 |
| 5 | jig | 地獄楽 | 2 |
| 6 | kai | 怪獣８号 | 4 |
| 7 | kim | 君のことが大大大大大好きな100人の彼女 | 1 |
| 8 | mag | 株式会社マジルミエ | 2 |
| 9 | osh | 【推しの子】 | 2 |
| 10 | spy | SPY×FAMILY | 4 |
| 11 | sum | サマータイムレンダ | 1 |
| 12 | sur | 魔都精兵のスレイブ | 3 |
| 13 | tak | タコピーの原罪 | 1 |
| 14 | you | 幼稚園WARS | 1 |
| 15 | yuw | 2.5次元の誘惑 | 5 |

**合計: 15作品 / URキャラ合計: 31体**

---

## シリーズ別URキャラ詳細

### chi｜チェンソーマン（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_chi_00002 | PremiumUR | Yellow | Technical |

---

### dan｜ダンダダン（URキャラ 2体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_dan_00002 | PremiumUR | Blue | Attack |
| chara_dan_00202 | PremiumUR | Green | Attack |

---

### gom｜姫様"拷問"の時間です（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_gom_00001 | PremiumUR | Green | Defense |

---

### hut｜ふつうの軽音部（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_hut_00001 | PremiumUR | Green | Defense |

---

### jig｜地獄楽（URキャラ 2体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_jig_00001 | PremiumUR | Red | Technical |
| chara_jig_00401 | PremiumUR | Colorless | Technical |

---

### kai｜怪獣８号（URキャラ 4体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_kai_00002 | PremiumUR | Green | Attack |
| chara_kai_00102 | PremiumUR | Yellow | Technical |
| chara_kai_00201 | PremiumUR | Blue | Attack |
| chara_kai_00701 | FestivalUR | Yellow | Defense |

---

### kim｜君のことが大大大大大好きな100人の彼女（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_kim_00001 | PremiumUR | Blue | Defense |

---

### mag｜株式会社マジルミエ（URキャラ 2体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_mag_00001 | PremiumUR | Blue | Attack |
| chara_mag_00201 | PremiumUR | Red | Attack |

---

### osh｜【推しの子】（URキャラ 2体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_osh_00001 | FestivalUR | Red | Technical |
| chara_osh_00101 | PremiumUR | Colorless | Special |

---

### spy｜SPY×FAMILY（URキャラ 4体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_spy_00001 | PremiumUR | Colorless | Special |
| chara_spy_00101 | PremiumUR | Yellow | Attack |
| chara_spy_00201 | PremiumUR | Red | Attack |
| chara_spy_00501 | PremiumUR | Blue | Defense |

---

### sum｜サマータイムレンダ（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_sum_00101 | PremiumUR | Blue | Support |

---

### sur｜魔都精兵のスレイブ（URキャラ 3体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_sur_00101 | PremiumUR | Green | Attack |
| chara_sur_00501 | PremiumUR | Yellow | Technical |
| chara_sur_00901 | FestivalUR | Blue | Attack |

---

### tak｜タコピーの原罪（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_tak_00001 | PremiumUR | Yellow | Defense |

---

### you｜幼稚園WARS（URキャラ 1体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_you_00001 | PremiumUR | Red | Attack |

---

### yuw｜2.5次元の誘惑（URキャラ 5体）

| unit_id | unit_label | color | role_type |
|---------|-----------|-------|-----------|
| chara_yuw_00001 | PremiumUR | Yellow | Attack |
| chara_yuw_00101 | PremiumUR | Green | Technical |
| chara_yuw_00102 | FestivalUR | Green | Support |
| chara_yuw_00301 | PremiumUR | Blue | Support |
| chara_yuw_00401 | PremiumUR | Red | Defense |

---

## 備考

- **PremiumUR**: ガチャで通常排出されるUR
- **FestivalUR**: フェスガチャ限定のUR
- dungeon一括生成の際は、この15作品（series_id）を対象とする
