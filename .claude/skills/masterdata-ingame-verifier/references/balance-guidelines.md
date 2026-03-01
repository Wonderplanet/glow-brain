# バランスガイドライン（パラメータ基準値）

> ソース: `domain/knowledge/masterdata/in-game/エネミーステータスシート.md`

---

## ステージ種別別・推奨パラメータ範囲

### メインクエスト（Normal/Hard）

| 対象 | HP範囲（role別） | ATK範囲（role別） |
|------|-----------------|-----------------|
| 雑魚 Def | 5,600 〜 91,000 | 21 〜 700 |
| 雑魚 Atk/Tech | 1,050 〜 42,000 | 70 〜 1,960 |
| 雑魚 Sup | 2,450 〜 56,000 | 35 〜 1,050 |
| ボス Def | 56,000 〜 1,400,000 | 56 〜 1,120 |
| ボス Atk/Tech | 10,500 〜 840,000 | 140 〜 2,380 |
| ボス Sup | 24,500 〜 1,050,000 | 84 〜 1,400 |

### イベント（ストーリー/チャレンジ）

| 対象 | HP範囲 | ATK範囲 |
|------|--------|--------|
| 雑魚 Def | 5,600 〜 38,500 | 21 〜 420 |
| 雑魚 Atk/Tech | 1,050 〜 16,800 | 70 〜 840 |
| 雑魚 Sup | 2,450 〜 20,300 | 35 〜 490 |
| ボス Def | 56,000 〜 350,000 | 56 〜 490 |
| ボス Atk/Tech | 10,500 〜 175,000 | 140 〜 980 |
| ボス Sup | 24,500 〜 231,000 | 84 〜 700 |

---

## 汎用パラメータ範囲（全ステージ共通）

| カラム | 通常範囲 | WARNING閾値 | CRITICAL閾値 |
|--------|---------|------------|------------|
| `hp` (Normal) | 1,000 〜 100,000 | > 500,000 or < 100 | > 2,000,000 |
| `hp` (Boss) | 50,000 〜 1,400,000 | > 3,000,000 | — |
| `attack_power` | 21 〜 2,380 | > 3,800 | > 10,000 |
| `move_speed` | 5 〜 100 | < 5 or > 100 | < 1 or > 200 |
| `well_distance` | 0.05 〜 1.0 | < 0.05 or > 2.0 | > 5.0 |

---

## ステージ種別固有の固定値

### dungeon_boss（限界チャレンジ・ボスブロック）

| テーブル | カラム | 固定値 | 違反時の扱い |
|---------|-------|-------|------------|
| `MstEnemyOutpost` | `hp` | **1,000** | CRITICAL |
| `MstKomaLine` | 行数（DISTINCT line_number） | **1行** | CRITICAL |

### dungeon_normal（限界チャレンジ・通常ブロック）

| テーブル | カラム | 固定値 | 違反時の扱い |
|---------|-------|-------|------------|
| `MstEnemyOutpost` | `hp` | **100** | CRITICAL |
| `MstKomaLine` | 行数（DISTINCT line_number） | **3行** | CRITICAL |

### event_challenge（チャレンジクエスト）

| テーブル | カラム | 条件 | 違反時の扱い |
|---------|-------|------|------------|
| `MstInGameSpecialRule` | `rule_type` | `SpeedAttack` が存在する | WARNING |

### raid（レイドバトル）

| テーブル | カラム | 条件 | 違反時の扱い |
|---------|-------|------|------------|
| `MstEnemyOutpost` | `is_damage_invalidation` | `1` | WARNING |

---

## コマ配置の整合性基準

| チェック対象 | 期待値 | 許容誤差 |
|------------|--------|---------|
| 各行のコマ幅合計 `SUM(width)` | 1.000 | ±0.001（ROUND3桁） |
| height合計 `SUM(height)` | 1.00 | ±0.01（ROUND2桁） |

---

## シーケンス合理性基準

| 指標 | 目安範囲 | WARNING |
|------|---------|---------|
| SummonEnemy 行数（召喚総数） | 10 〜 30体 | < 3体 or > 60体 |
| ElapsedTime 条件の単調増加 | — | 逆転がある場合 WARNING |

---

## 既存データとのバランス比較基準

既存の `projects/glow-masterdata/MstEnemyStageParameter.csv` の同種ステージ（同 character_unit_kind + role_type）の集計値と比較:

| 乖離範囲 | 判定 |
|---------|------|
| 平均の 0.2倍 〜 5倍 | OK（正常範囲） |
| 平均の 5倍超 or 0.2倍未満 | WARNING（意図的かどうか確認） |

同種ステージが存在しない場合（新コンテンツ）は近似種別と比較し、NOTE として記録する。

---

## 実データ分布（参考）

> `projects/glow-masterdata/MstEnemyStageParameter.csv` より（2026-03-01時点）

- 総レコード数: 1,061件
- `character_unit_kind`: Normal=616, Boss=437, AdventBattleBoss=8
- `role_type`: Attack=589, Defense=225, Technical=190, Support=57
- HPの中央値: 10,000（Normal）/ 10,000（Boss）
- ATKの中央値: 300（Normal Attack/Technical）/ 100（Normal Defense）
- `normal_enemy_hp_coef=1.0` の割合: 約91%（509/560件）
