# MstAutoPlayerSequence - summon_position 設定仕様

## 概要

`summon_position` は、敵キャラクターが召喚される時の**画面上の横位置（float値）**を指定するパラメータです。

| 項目 | 内容 |
|------|------|
| **カラム名** | `summon_position` |
| **型** | `double`（DB）/ `float`（C# / PHP） |
| **コメント** | 召喚位置 |
| **デフォルト** | `0`（空白はシステム自動配置） |
| **Nullable** | false（空文字列が実質「指定なし」） |

---

## MstKomaLine との関係

**直接の関係はありません。**

| テーブル | 役割 |
|---------|------|
| `MstKomaLine` | コマの**視覚的レイアウト**（幅・高さ・背景オフセット等）を管理 |
| `summon_position` | **敵が召喚される横座標**を指定 |

それぞれ独立した概念を管理しており、参照関係もありません。

---

## 値の意味と設定範囲

フィールドの左側（敵陣）→ 右側（プレイヤー拠点）の相対位置で指定します。

| 範囲 | 位置 | 用途 |
|------|------|------|
| **空白** | システム自動決定 | 全体の約80%。雑魚の大量召喚等 |
| **0.3〜0.9** | 前方（敵陣側） | 敵方向から来る演出 |
| **1.3〜1.8** | 中間 | 標準的な配置 |
| **2.5〜2.9** | 後方（拠点寄り） | 脅威度の高い敵・緊迫感の演出 |
| **3.0〜3.9** | 最後方（拠点直近） | ボス・最強敵の特別感 |

### 実データ分布（MstAutoPlayerSequence.csv より）

| 値 | 件数 | 割合 |
|----|------|------|
| 空白 | 3,747 | 80.7% |
| 2.8 | 76 | 1.6% |
| 1.8 | 68 | 1.5% |
| 2.9 | 67 | 1.4% |
| 1.5 | 55 | 1.2% |
| 2.7 | 48 | 1.0% |
| 1.7 | 45 | 1.0% |
| 2.5 | 45 | 1.0% |
| 0.8 | 39 | 0.8% |
| 1.3 | 31 | 0.7% |

---

## 設定方法

### 空白でよいケース

```
SummonEnemy / e_xxx_00001 / summon_count=5
# summon_position は空白 → ランダム or デフォルト位置で出現
```

雑魚敵の大量召喚など、出現位置がゲーム性に影響しない場合は省略で問題ありません。

### 位置を明示指定するケース

#### 同タイミングで複数体を別位置に配置

```
elem_a: summon_position=2.7, summon_count=1
elem_b: summon_position=2.8, summon_count=1
elem_c: summon_position=2.9, summon_count=1
```

#### ボス敵を最後方に配置

```
elem_a: action_value=e_xxx_boss_00001, summon_position=3.5
```

#### 密集ウェーブ（小数点第2位で微調整）

```
# 3列ウェーブ演出（0.02単位で隙間なく密集配置）
elem_a: summon_position=2.84, summon_count=3, summon_interval=25
elem_b: summon_position=2.86, summon_count=3, summon_interval=50
elem_c: summon_position=2.88, summon_count=3, summon_interval=75
```

---

## 設定値の選択ガイド

| シチュエーション | 推奨値範囲 | 備考 |
|---------------|----------|------|
| 通常の雑魚敵 | 空白 | ランダム配置でOK |
| 敵陣側スポーン | 0.5〜0.9 | 敵方向から敵が来る演出 |
| 標準的な中間配置 | 1.3〜1.8 | バランスの取れた位置 |
| 後方・脅威度高い敵 | 2.5〜2.9 | 拠点に迫る緊迫感 |
| ボス・最強敵 | 3.0〜3.9 | 最後方の特別感 |
| 同時複数配置（密集） | 小数点第2位で微調整（例: 2.84, 2.86, 2.88） | 視覚的に隙間なく配置 |

---

## 技術的補足

### データフロー

```
DB（double） → PHP（float） → API（string） → C#（float / ObscuredFloat）
```

### C# クライアントの型定義

```csharp
// MstAutoPlayerSequenceData.cs
[DataMember(Name = "summonPosition")]
public float SummonPosition
{
    get => _obscured_summonPosition;
    set => _obscured_summonPosition = value;
}
private ObscuredFloat _obscured_summonPosition;
```

ドメインレイヤーでは `PageCoordV2` 型に変換され、キャラクターの画面表示位置に反映されます。

---

## 関連ドキュメント

- `specs/敵キャラ種別の識別方法.md`
- `.claude/skills/vd-masterdata-ingame-design-creator/references/MstAutoPlayerSequence_設計パターン集.md`
- `.claude/skills/vd-masterdata-ingame-design-creator/references/MstAutoPlayerSequence_具体例集.md`
