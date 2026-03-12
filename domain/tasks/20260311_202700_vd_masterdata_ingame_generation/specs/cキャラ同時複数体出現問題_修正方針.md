# c_キャラ同時複数体出現問題 修正方針

## 背景

世界観遵守のルールとして、**c_キャラ（固有キャラクター）はフィールドに同時に2体以上出現してはいけない**。

## 問題の原因

`vd_osh_normal_00001` の MstAutoPlayerSequence を確認したところ、c_キャラの召喚条件が全て `ElapsedTime`（経過時間）のみで制御されていた。

```
elem 2: c_osh_00501  条件: ElapsedTime 3000ms
elem 3: c_osh_00201  条件: ElapsedTime 8000ms   ← 前のキャラが生きていても召喚される
elem 4: c_osh_00401  条件: ElapsedTime 13000ms
elem 5: c_osh_00301  条件: ElapsedTime 18000ms
```

`ElapsedTime` 条件は前のキャラが倒されているかどうかに関係なく時間経過で発火するため、
前のc_キャラが5,000ms以内に倒されなければ複数体が同時にフィールドに存在してしまう。

## 修正方針

### 基本方針

**c_キャラは直前のc_キャラが撃破されたことを条件として召喚する**（`FriendUnitDead` 条件を使用）。

初回のc_キャラのみ `ElapsedTime` でバトル開始から一定時間後に登場させ、
2体目以降は `FriendUnitDead` で前のキャラの撃破を待つ設計にする。

### 修正後のシーケンス設計

| elem | キャラ | condition_type | condition_value | 意図 |
|------|--------|----------------|-----------------|------|
| 1 | `e_glo_00001_vd_Normal_Colorless` | ElapsedTime | 250 | バトル開始直後にグロー召喚 |
| 2 | `c_osh_00501_vd_Normal_Green` | ElapsedTime | 3000 | 最初のc_キャラを時間で登場 |
| 3 | `c_osh_00201_vd_Normal_Green` | FriendUnitDead(c_osh_00501_vd_Normal_Green) | — | 前のc_キャラ撃破後に登場 |
| 4 | `c_osh_00401_vd_Normal_Green` | FriendUnitDead(c_osh_00201_vd_Normal_Green) | — | 前のc_キャラ撃破後に登場 |
| 5 | `c_osh_00301_vd_Normal_Green` | FriendUnitDead(c_osh_00401_vd_Normal_Green) | — | 前のc_キャラ撃破後に登場 |

> `FriendUnitDead` の `action_value` には撃破対象の `MstEnemyStageParameter` の ID を指定する。

### 他の VD ブロックへの展開

同様に `ElapsedTime` のみでc_キャラを連続召喚しているブロックは全て同じ修正が必要。
新規ブロック作成時はデフォルトでこの設計方針を採用すること。

## 修正対象ブロック（確認済み）

| ブロックID | 問題 | 状態 |
|-----------|------|------|
| `vd_osh_normal_00001` | c_キャラ4体が ElapsedTime のみで召喚 | 未修正 |

## 注意事項

- `e_glo_*`（グロー本体）は c_キャラではないため、この制約の対象外。
- `FriendUnitDead` の条件値は `MstEnemyStageParameter.id` の値を使う（キャラIDそのままではない）。
- 設計変更後は `design.md` → CSV 再生成 → `design.json` 更新 → xlsx 再生成の順で対応する。
