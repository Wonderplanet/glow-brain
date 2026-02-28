# MstAutoPlayerSequence 設計書データ検証結果

## 概要

「【ストーリー】必ず生きて帰る」の MstAutoPlayerSequence データが設計書に全て記載されているかを検証。

| 項目 | 内容 |
|------|------|
| **検証日** | 2026-02-28 |
| **正解データ** | `projects/glow-masterdata/MstAutoPlayerSequence.csv` |
| **確認データ** | `dungeon-ingame-xlsx-generator/raw/検証用コピー_【ストーリー】必ず生きて帰る.xlsx` 「1話」シート EA39:FE44 |
| **対象 sequence_set_id** | `event_jig1_charaget01_00001` |

---

## 結果サマリー

| チェック項目 | 結果 |
|---|---|
| 行数（element_id の数） | **一致** ✓ |
| element_id の網羅性 | **一致** ✓（1〜5 の5行） |
| 主要データ（condition / action） | **全行一致** ✓ |
| カラム値の完全一致 | **2カラムに差分あり**（機能上は問題なし） |

---

## 各行のデータ比較

| element_id | condition_type | condition_value | action_value | 一致 |
|---|---|---|---|---|
| 1 | ElapsedTime | 300 | e_jig_00402_jig1_charaget01_Normal_Colorless | ✓ |
| 2 | FriendUnitDead | 1 | e_jig_00402_jig1_charaget01_Normal_Colorless | ✓ |
| 3 | OutpostHpPercentage | 99 | e_jig_00402_jig1_charaget01_Normal_Colorless | ✓ |
| 4 | ElapsedTime | 350 | e_jig_00401_jig1_charaget01_Normal_Green | ✓ |
| 5 | ElapsedTime | 99 | e_jig_00401_jig1_charaget01_Normal_Green | ✓ |

---

## 差分詳細（全5行で共通）

| カラム | CSV（正解） | XLSX設計書 | 備考 |
|---|---|---|---|
| `is_summon_unit_outpost_damage_invalidation` | `""` (空/NULL) | `False` | DBデフォルトがFalseであれば実質同値の可能性あり |
| `defeated_score` | `"0"` | `""` (空) | DBデフォルト値の0が自動設定されているか、設計書への記載漏れの可能性あり |

### 差分の解釈

- **`is_summon_unit_outpost_damage_invalidation`**
  設計書には `False` が記載されているが、CSVには空（NULL）で投入されている。
  DB側のデフォルト値が `False` であれば機能上の差異はないが、設計書→CSV変換時に `False` → 空へ変換するルールが存在している可能性がある。

- **`defeated_score`**
  CSVには `0` が投入されているが、設計書は空欄。
  DB側のデフォルト値 `0` が自動適用されているか、設計書への記載が省略されているものと考えられる。

---

## 結論

設計書（XLSX）の1話シート EA39:FE44 には、CSVに投入されている全データ（5行）が記載されていることを確認。

2カラムの差分は **デフォルト値の扱いによるもの** であり、インゲームの動作に影響する差分ではない可能性が高い。ただし、設計書のテンプレート設計の際に `defeated_score` の記載要否を確認しておくことを推奨する。
