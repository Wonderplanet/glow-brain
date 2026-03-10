# attack_combo_cycle（コンボサイクル）解説

作成日: 2026-03-10

---

## 概要

`attack_combo_cycle` は `MstEnemyStageParameter` に設定するパラメータで、
**敵ユニットが「必殺ワザ（スペシャル攻撃）」を出すまでの攻撃サイクル数**を定義する。

---

## 仕組み

敵ユニットは以下のサイクルで攻撃を繰り返す。

```
attack_combo_cycle = 7 の場合:

1回目: 通常攻撃
2回目: 通常攻撃
3回目: 通常攻撃
4回目: 通常攻撃
5回目: 通常攻撃
6回目: 通常攻撃
7回目: 必殺ワザ ← サイクル完了、リセットして1回目に戻る
```

### 攻撃判定ロジック（クライアント実装）

```
comboCount が comboCycle.Value に達した → SpecialAttack（必殺ワザ）
それ以外                               → NormalAttack（通常攻撃）
```

**ファイル**: `projects/glow-client/Assets/GLOW/Scripts/Runtime/Core/Domain/ValueObjects/InGame/AttackComboCycle.cs`

---

## 値の意味

| 値 | 意味 |
|---|---|
| `0` | 攻撃しない（移動専用ユニット） |
| `1` | 毎回必殺ワザ（最強・最危険） |
| `5` | 通常攻撃4回 → 必殺ワザ1回のサイクル |
| `7` | 通常攻撃6回 → 必殺ワザ1回のサイクル |

**値が小さいほど敵が強く、大きいほど弱い。**

---

## ゲームバランスへの使い分け

| キャラ種別 | 典型的な値 | 意図 |
|-----------|----------|------|
| 雑魚敵 | `1` | 常に必殺ワザ。複数体で一気に圧力をかける |
| ボス敵 | `5〜7` | パターンが読みやすく、プレイヤーが戦術を立てやすい |
| 移動専用 | `0` | 攻撃せず障害物として機能する |

---

## 注意点

- 敵キャラクターに必殺ワザが設定されていない場合（`SpecialAttack.IsEmpty()`）は、`attack_combo_cycle` の値に関わらず常に通常攻撃のみになる
- `attack_combo_cycle = 1` は「常に必殺ワザ」を意味するが、正確には「1回目＝必殺ワザ」のサイクルが毎回繰り返される

---

## 関連ファイル

| 種類 | パス |
|---|---|
| マスタデータ定義 | `projects/glow-masterdata/MstEnemyStageParameter.csv` |
| DBスキーマ | `projects/glow-server/api/database/schema/exports/master_tables_schema.json` |
| クライアント実装 | `projects/glow-client/Assets/GLOW/Scripts/Runtime/Core/Domain/ValueObjects/InGame/AttackComboCycle.cs` |
| クライアント実装 | `projects/glow-client/Assets/GLOW/Scripts/Runtime/Core/Domain/ValueObjects/InGame/AttackComboCount.cs` |
