# MstAutoPlayerSequence - c_（プレイアブルキャラ）召喚パターン分析

> 対象: `action_value` が `c_` で始まり、Bossでないプレイアブルキャラを敵として召喚するレコード

---

## 1. 同時複数体出現はあるか？

**結論：「トリガー1つで同時に複数体を瞬間召喚」は存在しない。ただし「InitialSummon（ゲーム開始時）で複数体が同時配置」されるケースが4件ある。**

| ケース | 件数 |
|---|---|
| `summon_interval = 0` かつ `summon_count >= 2`（瞬間複数召喚） | **0件** |
| `InitialSummon` に複数のelement_idがある（開始時複数配置） | **4件** |

### InitialSummonで複数体同時配置の実例

| sequence_set_id | 同時配置数 | キャラ |
|---|---|---|
| `raid_osh1_00001` | 3体 | c_osh_00201 / c_osh_00301 / c_osh_00401（Red/Yellow/Colorless） |
| `event_kim1_challenge_00004` | 2体 | c_kim_00101（Green）/ c_kim_00201（Red） |
| `raid_l05anniv_00001` | 2体 | c_gom_00201（Blue）/ c_kai_00401（Blue） |
| `event_kim1_1day_00001` | 2体 | c_kim_00101（Colorless）/ c_kim_00201（Colorless） |

各elementの `summon_count=1` で、複数のelement_idが同時にInitialSummonとして設定されているパターン。

---

## 2. 撃破後の再出撃条件

**メインパターンは `FriendUnitDead`（累積撃破数トリガー）**

### condition_value（何体倒されたら次を召喚するか）の分布

| 撃破体数（condition_value） | 件数 | 代表的な使われ方 |
|---|---|---|
| 1体 | 46件 | 撃破即時再召喚（最基本パターン） |
| 2体 | 31件 | 2体倒されてから次が来る |
| 3〜5体 | 39件 | 中盤ステージの難度上昇 |
| 6〜10体 | 31件 | 高難度ステージ |
| 10体超 | 25件 | rikステージ専用の大量ザコ召喚 |

### rikステージの特殊な大量召喚構造（代表例：`normal_rik_00001`）

```
element 1    : ElapsedTime 200ms → summon_count=3, interval=200ms  （開幕3体を順次召喚）
element 2    : ElapsedTime 800ms → summon_count=1                   （追加1体）
element 3-4  : FriendUnitDead=2  → summon_count=3, interval=400ms  （2体倒すごとに3体ずつ）
element 7-9  : FriendUnitDead=5/6 → summon_count=7, interval=500ms （さらに7体ずつ）
element 12-15: FriendUnitDead=10/11 → summon_count=5               （後半ウェーブ）
element 16-18: ElapsedTime 8500ms+ → summon_count=99, interval=500ms（終盤無限召喚）
```

1体ずつ間隔を空けて出現させながら、累積撃破数に応じてウェーブを切り替えていく構造。

---

## 3. 再出撃条件の全種別まとめ

| condition_type | 件数 | 意味 |
|---|---|---|
| `FriendUnitDead` | 183件 | **累積撃破体数**が閾値に達したら召喚 |
| `ElapsedTime` | 161件 | 経過時間で追加召喚（撃破関係なく） |
| `ElapsedTimeSinceSequenceGroupActivated` | 35件 | グループ切り替え後の経過時間 |
| `InitialSummon` | 33件 | ゲーム開始時の初期配置 |
| `EnterTargetKomaIndex` | 10件 | 敵が指定マスに到達したら |
| `OutpostDamage` | 9件 | 拠点ダメージ発生時 |
| `OutpostHpPercentage` | 8件 | 拠点HPが指定%以下になったら |
| `DarknessKomaCleared` | 2件 | 暗闇コマクリア時 |

---

## 4. summon_count の分布

| summon_count | 件数 | 備考 |
|---|---|---|
| 1 | 371件 | 標準（1体ずつ） |
| 2〜10 | 47件 | 間隔付き連続召喚（summon_interval > 0） |
| 99 | 28件 | 実質無限ループ召喚（主にrikステージ） |

`summon_count >= 2` のケースは**すべて `summon_interval > 0`**（最小25ms〜最大3000ms）。
`summon_interval = 0` かつ `summon_count >= 2` は **0件**。

---

## まとめ

- **同時複数体（同一トリガーで複数体を同時召喚）は設計として存在しない**
- **フィールドに複数体いる状態を作るには、InitialSummonで複数のelement_idを使う**（4件実績あり）
- **撃破後の再出撃は `FriendUnitDead` の累積カウントで制御**されており、1体倒すごとに次を出す（`condition_value=1`）が基本、難易度に応じて閾値を上げていく構造
