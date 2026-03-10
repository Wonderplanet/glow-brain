# MstEnemyStageParameter — 降臨バトルのID採番ルール

調査日: 2026-03-10
データソース: `projects/glow-masterdata/MstEnemyStageParameter.csv`（降臨バトルレコード 124件）

---

## IDフォーマット

```
{prefix}_{作品ID}_{キャラID}_{イベントキー}_advent_{ユニット種別}_{属性}
```

### 各セグメント

| セグメント | 内容 | 値の例 |
|-----------|------|--------|
| **prefix** | キャラの種別区分 | `c`（プレイヤーキャラが敵登場）/ `e`（敵専用キャラ） |
| **作品ID** | 作品のローマ字先頭3文字（小文字） | `hut`, `kai`, `spy`, `gom`, `kim` |
| **キャラID** | 5桁の連番（MstEnemyCharacterと対応） | `00001`, `00101`, `00201` |
| **イベントキー** | インゲーム施策を特定するキー | `hut1`, `kai1`, `glo2`, `l05anniv` |
| **advent** | 降臨バトルを示す固定文字列 | `advent`（必ず含める） |
| **ユニット種別** | IDレベルでのユニット役割 | `Boss`, `Normal` |
| **属性** | カラー（属性・難易度差を表現） | `Red`, `Blue`, `Green`, `Yellow`, `Colorless` |

---

## prefixの決定ルール

`prefix` は `mst_enemy_character_id` の接頭語と対応している。

| prefix | mst_enemy_character_id | 意味 |
|--------|------------------------|------|
| `c_` | `chara_xxx_xxxxx` | プレイヤーキャラクターが敵として登場 |
| `e_` | `enemy_xxx_xxxxx` | 降臨バトル専用の敵キャラ |

---

## イベントキーの命名パターン

| パターン | 意味 | 例 |
|---------|------|-----|
| `{作品ID}{回数}` | その作品の降臨バトル第N回 | `hut1`（ハイキュー第1回）, `kai1`（怪獣8号第1回）, `kim1`, `spy1` |
| 複数作品横断キー | 複数作品・記念イベントなど | `glo2`（GLOWイベント）, `l05anniv`（5周年記念） |

---

## character_unit_kind の種類

降臨バトルで使用される `character_unit_kind` の実データ集計（124件中）：

| character_unit_kind | 件数 | 意味 |
|---------------------|------|------|
| `Boss` | 58件 | ボス敵（通常ボス・メインボス） |
| `Normal` | 58件 | 雑魚敵 |
| `AdventBattleBoss` | 8件 | 降臨バトル専用ボス（特別演出あり） |

### AdventBattleBoss について

- `e_` prefix（敵専用キャラ）のみに付与される
- 降臨バトルのメインボスに使用する最上位種別
- 通常の `Boss` よりも格上の演出が適用される
- `MstAutoPlayerSequence` の `aura_type=AdventBoss3` と組み合わせて使用

---

## IDのユニット種別セグメントと character_unit_kind の関係

> **注意**: IDの `_Boss_` / `_Normal_` セグメントと `character_unit_kind` カラムの値は**必ずしも一致しない**。

例：
- `c_ara_00001_glo2_advent_Normal_Red` → `character_unit_kind = Boss`（IDはNormalだがカラムはBoss）

IDのユニット種別セグメントはあくまで**ID識別のための文字列**であり、実際のゲーム挙動は `character_unit_kind` カラムで決まる。

---

## 具体的なID例

### プレイヤーキャラが敵登場（prefix = c）

```
c_hut_00001_hut1_advent_Boss_Colorless     # ハイキュー第1回 ボス 無属性
c_hut_00001_hut1_advent_Normal_Colorless   # ハイキュー第1回 雑魚 無属性
c_hut_00101_hut1_advent_Boss_Yellow        # ハイキュー第1回 ボス 黄属性
c_kim_00001_kim1_advent_Boss_Red           # キメツ第1回 ボス 赤属性
c_spy_00501_spy1_advent_Boss_Red           # スパイ第1回 ボス 赤属性
c_gom_00001_l05anniv_advent_Boss_Blue      # 5周年記念 ボス 青属性
```

### 敵専用キャラ（prefix = e）

```
e_dan_00001_dan1_advent_Boss_Colorless     # ダンダダン第1回 ボス 無属性
e_dan_00001_dan1_advent_Normal_Colorless   # ダンダダン第1回 雑魚 無属性
e_kai_00401_kai1_advent_Boss_Blue          # 怪獣8号第1回 ボス 青属性（AdventBattleBoss）
e_kai_00401_kai1_advent_Boss_Red           # 怪獣8号第1回 ボス 赤属性（AdventBattleBoss）
e_mag_00101_mag1_advent_Normal_Green       # マグ系第1回 雑魚 緑属性
e_you_00001_you1_advent_Boss_Colorless     # 第1回 ボス 無属性
```

---

## 採番の原則まとめ

1. **prefix** は参照する `mst_enemy_character_id` の接頭語（`chara_` → `c`、`enemy_` → `e`）で決まる
2. **イベントキー** は `{作品ID}{回数}` が基本（例：`hut1`、`kai1`）。複数作品横断・記念イベントは別途キーを用意
3. `advent` は降臨バトルであることを示す**固定文字列**で、必ずIDに含める
4. 同じキャラ×イベントでも**属性（color）ごとに別レコード**を作成する
5. `AdventBattleBoss` は `e_` prefixのキャラに対し、降臨バトルのメインボスに付与する
