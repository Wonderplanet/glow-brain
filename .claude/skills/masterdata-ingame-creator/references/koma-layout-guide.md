# MstPage / MstKomaLine 設計ガイド

コマ（バトルフィールドの地形）設計に関するガイド。
詳細仕様は以下を参照:
- `domain/knowledge/masterdata/table-docs/MstPage.md`
- `domain/knowledge/masterdata/table-docs/MstKomaLine.md`

---

## コマレイアウト基本構造

バトルフィールドは以下の3層で構成される:

```
MstPage（ページ）
  └─ MstKomaLine（コマライン）× N行
       └─ koma1 〜 koma4（各コマ）
```

### MstPage

最もシンプルなテーブル。IDのみ持つ。

```
ENABLE, id, release_key
e, event_kai1_charaget01_00001, {release_key}
```

- `id` は MstInGame.id と同一の値を使うことが多い
- MstPage単体ではフィールドとして機能しない → **必ずMstKomaLineを紐付ける**

---

## MstKomaLine の設計ルール

### 1. コマ幅の合計は1.0

1ライン内の全コマ幅の合計が **必ず 1.0** になること:

```
# 1コマの場合
koma1_width = 1.0

# 2コマの場合
koma1_width = 0.4, koma2_width = 0.6  （合計 = 1.0）

# 3コマの場合
koma1_width = 0.25, koma2_width = 0.5, koma3_width = 0.25  （合計 = 1.0）
```

### 2. koma1 は必須スロット

koma1 は必ず設定が必要。koma2〜4 はオプション（空欄可）。

### 3. row は上から順番に 1, 2, 3 ...

同一ページのコマラインは row 番号で縦位置が決まる。

### 4. height は行の高さの比率

全rowのheightの合計が1.0になるように設定する（慣例）:

```
row=1: height=0.55
row=2: height=0.45
（合計 = 1.0）
```

よく使う height の値:
- `0.45`, `0.52`, `0.55`, `0.6`

---

## KomaEffectType 全種別

| 効果名 | 説明 | parameter1 | parameter2 |
|-------|------|-----------|-----------|
| `None` | エフェクトなし（基本） | `0` | `0` |
| `AttackPowerUp` | 攻撃力アップ | 上昇率(%) 例: 10, 20, 50 | `0` |
| `AttackPowerDown` | 攻撃力ダウン | 低下率(%) 例: 15, 30, 50 | `0` |
| `MoveSpeedUp` | 移動速度アップ | 上昇率(%) | `0` |
| `SlipDamage` | スリップダメージ | ダメージ量 | `0` |
| `Gust` | 吹き飛ばし | 持続時間（ティック数） | 方向（TargetSideで決定） |
| `Poison` | 毒（持続状態効果） | 継続時間（TickCount換算） | 効果強度 |
| `Darkness` | 暗闇（視界妨害） | `0` | `0` |
| `Burn` | 燃焼（持続ダメージ） | ダメージ量（例: 100） | 継続時間（例: 1000） |
| `Stun` | スタン（行動不能） | 継続時間 | `0` |
| `Freeze` | 凍結（行動不能） | 継続時間 | `0` |
| `Weakening` | 弱体化 | 効果値 | `0` |

---

## KomaEffectTargetSide

| 値 | 説明 |
|----|------|
| `All` | プレイヤー・敵の両方 |
| `Player` | プレイヤー側のみ |
| `Enemy` | 敵側のみ |

**Gust エフェクトの方向:**
- `Enemy` → 右方向（敵を吹き飛ばす）
- `Player` または `All` → 左方向（プレイヤーを吹き飛ばす）

---

## koma_line_layout_asset_key の選び方

コマの視覚的配置パターンを示すアセット参照番号:

| コマ数 | width比率 | layout_asset_key |
|--------|----------|-----------------|
| 1コマ | 1.0 | `1` |
| 2コマ | 0.4/0.6 | `5` |
| 2コマ | 0.3/0.7 | `3` |
| 2コマ | 0.5/0.5 | `6` |
| 3コマ | 0.25/0.5/0.25 | `9` |

特殊値: `0.55`（特殊レイアウト）

**→ 実際の値は既存データから参照することを推奨:**
```sql
SELECT DISTINCT koma_line_layout_asset_key, koma1_width, koma2_width, koma3_width
FROM read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE)
WHERE koma2_asset_key IS NOT NULL AND koma3_asset_key IS NULL
LIMIT 10;
```

---

## アセットキーの選び方

### koma_asset_key（コマのキャラ/オブジェクトアセット）

コマ内に表示されるキャラクターや背景オブジェクトのアセットキー。
既存CSVから同シリーズのイベントを参照して選ぶ:

```sql
-- 同シリーズのKomaLineからアセットキーを確認
SELECT DISTINCT koma1_asset_key, koma2_asset_key
FROM read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE)
WHERE id LIKE '%kai1%'
LIMIT 10;
```

---

## よく使うパターン例

### パターン1: エフェクトなし（基本・2行構成）

```csv
ENABLE,id,mst_page_id,row,height,koma_line_layout_asset_key,koma1_asset_key,koma1_width,koma1_back_ground_offset,koma1_effect_type,koma1_effect_parameter1,koma1_effect_parameter2,koma1_effect_target_side,koma1_effect_target_colors,koma1_effect_target_roles,koma2_asset_key,...,release_key
e,event_kai1_charaget01_00001_1,event_kai1_charaget01_00001,1,0.55,1,kai_00004,1.0,1,None,0,,All,All,All,,,,,,,,,,,,,,{release_key}
e,event_kai1_charaget01_00001_2,event_kai1_charaget01_00001,2,0.45,5,kai_00005,0.4,1,None,0,,All,All,All,kai_00006,0.6,1,None,0,,All,All,All,,,,,,{release_key}
```

### パターン2: 攻撃力ダウンコマあり（2行・片方にエフェクト）

```
row=1: koma1（全幅）、エフェクト=None
row=2: koma1（0.25幅、None）+ koma2（0.75幅、AttackPowerDown 30%、Target=Enemy）
```

### パターン3: 暗黒コマ（Darkness）あり

```
row=1: koma1（全幅）、エフェクト=None
row=2: koma1（0.4幅、None）+ koma2（0.6幅、Darkness、Target=Enemy）
```

---

## 設定時の注意点

1. **エフェクトがNoneでも koma1_effect_target_side は設定する** → `All` を設定
2. **koma2以降を使わない場合はすべて空欄に** → koma2_asset_key 以下の全カラムを空に
3. **MstPageとMstKomaLineのrelease_keyは必ず一致させる**
4. **タブ文字に注意** → enum値の前後にタブが混入しないようにする
