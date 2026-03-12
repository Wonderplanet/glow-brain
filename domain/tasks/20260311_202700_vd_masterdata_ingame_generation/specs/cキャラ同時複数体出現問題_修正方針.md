# cキャラ同時複数体出現問題 修正方針

## 背景・ルール

世界観遵守のルールとして、**c_キャラ（固有キャラクター）はフィールドに同時に2体以上出現してはいけない**。
これはVD（限界チャレンジ）固有のルールとして定められている。

---

## 既存データの調査結果

`projects/glow-masterdata/MstAutoPlayerSequence.csv`（過去リリース済みデータ全件）を対象に、
c_キャラ召喚の条件設定傾向を分析した。

### c_キャラ召喚 条件タイプの全体分布

| condition_type | 件数 | 比率 |
|----------------|------|------|
| `FriendUnitDead` | 337 | 35.9% |
| `ElapsedTime` | 300 | 32.0% |
| `ElapsedTimeSinceSequenceGroupActivated` | 118 | 12.6% |
| `InitialSummon` | 64 | 6.8% |
| `EnterTargetKomaIndex` | 48 | 5.1% |
| `OutpostHpPercentage` | 36 | 3.8% |
| `OutpostDamage` | 22 | 2.3% |
| `DarknessKomaCleared` | 13 | 1.4% |

`FriendUnitDead` と `ElapsedTime` が2大条件タイプで、合計で全体の約68%を占める。

### コンテンツ種別ごとの傾向

| コンテンツ種別 | 主要条件タイプ | 傾向 |
|--------------|--------------|------|
| `charaget` | `ElapsedTime` >> `FriendUnitDead` | タイマー主体 |
| `challenge` | `ElapsedTime` ≈ `FriendUnitDead` | 混在 |
| `veryhard(main)` | `FriendUnitDead` >> `ElapsedTime` | 撃破チェーン主体 |
| `hard(main)` | `FriendUnitDead` > `ElapsedTime` | 撃破チェーン主体 |
| `normal(main)` | `FriendUnitDead` > `ElapsedTime` | 撃破チェーン主体 |
| `savage` | `FriendUnitDead` > `ElapsedTime` | 撃破チェーン主体 |
| `raid` | `ElapsedTimeSinceSequenceGroupActivated` >> | ループ構造特有 |
| `1day` | `ElapsedTime` のみ | 短時間演出用 |

### ElapsedTimeのみで複数c_キャラを召喚するケース（同時出現リスクあり）

リリース済みデータで、同一グループ内でc_キャラを2体以上 `ElapsedTime` のみで召喚しているケースが **39件**存在する。
これらを時間間隔（spread_ms）で分類すると、2つのパターンに分かれる。

#### パターンA: 演出的な同時登場（間隔≤500ms）

**意図**: バトル開始時に複数キャラが「ほぼ同時に」登場する演出。
前のキャラを倒す時間より召喚間隔が短いため、意図的な同時出現。

代表例:

| sequence_set_id | c_キャラ数 | 時間間隔 | 備考 |
|----------------|-----------|---------|------|
| `event_f05anniv_1day_00001` | 5 | 50〜350ms | 5体を連続演出で全員登場 |
| `event_you1_challenge_00002` | 2 | 0ms（同時） | 意図的に全く同じタイミング |
| `normal_aka_00003` | 2 | 50ms | ほぼ同時登場 |
| `raid_kim1_00001` | 2 | 50ms | ほぼ同時登場 |
| `event_jig1_savage_00001` | 2 | 150ms | 連続演出 |
| `event_osh1_charaget01_00001` | 2 | 100ms | 連続演出 |

→ このパターンは**意図的な演出設計**として許容されている実績がある。

#### パターンB: 長時間間隔（間隔>500ms）での ElapsedTime 連続召喚

**問題**: 間隔が長いため、前のc_キャラが倒されなければ複数体が**長時間フィールドに共存**する。

代表例:

| sequence_set_id | c_キャラ数 | 時間間隔 | リスク |
|----------------|-----------|---------|-------|
| `hard_gom_00006` | 2 | 3000ms | 3秒間同時存在の可能性 |
| `veryhard_gom_00006` | 2 | 3600ms | 3.6秒間同時存在の可能性 |
| `normal_gom_00006` | 2 | 3000ms | 3秒間同時存在の可能性 |
| `event_yuw1_challenge01_00002` | 2 | 2900ms | 2.9秒間同時存在の可能性 |
| `veryhard_jig_00006` | 3 | 最大5440ms | 最悪3体同時存在 |

→ これらは既存データ上の**設計問題**の可能性があるが、現時点では修正対象外。

---

## VD（限界チャレンジ）の問題

### 現状の問題（vd_osh_normal_00001 で確認）

```
elem 2: c_osh_00501  条件: ElapsedTime 3000ms
elem 3: c_osh_00201  条件: ElapsedTime 8000ms   ← 間隔 5000ms
elem 4: c_osh_00401  条件: ElapsedTime 13000ms  ← 間隔 5000ms
elem 5: c_osh_00301  条件: ElapsedTime 18000ms  ← 間隔 5000ms
```

5000ms（5秒）間隔のElapsedTimeのみの制御。前のc_キャラが5秒以内に倒されなければ、複数体が長時間共存する。
これはパターンBに該当し、世界観ルール（c_キャラは同時に1体のみ）に違反する。

---

## 修正方針

### 基本方針

**VDにおけるc_キャラは、直前のc_キャラが撃破されたことを条件として召喚する**（`FriendUnitDead` 条件）。

- 初回のc_キャラは `ElapsedTime` でバトル開始後の登場タイミングを制御する
- 2体目以降は `FriendUnitDead` で前のc_キャラの撃破を待ってから召喚する

この方式は、`veryhard(main)` や `savage` コンテンツで広く採用されている実績のある設計パターン。

### 修正後のシーケンス設計（vd_osh_normal_00001 の例）

| elem | キャラ | condition_type | condition_value | 意図 |
|------|--------|----------------|-----------------|------|
| 1 | `e_glo_00001_vd_Normal_Colorless` | `ElapsedTime` | 250 | バトル開始直後にグロー召喚 |
| 2 | `c_osh_00501_vd_Normal_Green` | `ElapsedTime` | 3000 | 最初のc_キャラを時間で登場 |
| 3 | `c_osh_00201_vd_Normal_Green` | `FriendUnitDead` | `c_osh_00501_vd_Normal_Green` | 前の撃破後に登場 |
| 4 | `c_osh_00401_vd_Normal_Green` | `FriendUnitDead` | `c_osh_00201_vd_Normal_Green` | 前の撃破後に登場 |
| 5 | `c_osh_00301_vd_Normal_Green` | `FriendUnitDead` | `c_osh_00401_vd_Normal_Green` | 前の撃破後に登場 |

> `FriendUnitDead` の `condition_value` には撃破対象の `MstEnemyStageParameter.id` を指定する。

### 許容パターン（適用不要）

以下のケースは世界観ルール違反ではなく、演出として許容する：

- **≤500ms の短時間連続召喚**: 「全員集合」演出など、意図的な同時登場
  - ただしVDで適用する場合は別途設計意図の確認が必要

---

## 新規VDブロック作成時のルール

1. c_キャラが複数体登場する場合、初回のみ `ElapsedTime`、2体目以降は `FriendUnitDead` を使う
2. `ElapsedTime` で複数c_キャラを召喚する場合は、間隔を **500ms以内** に収める（演出目的に限定）
3. `e_glo_*`（グロー本体）は c_キャラではないため、この制約の対象外

---

## 修正対象ブロック

| ブロックID | 問題 | 状態 |
|-----------|------|------|
| `vd_osh_normal_00001` | c_キャラ4体が ElapsedTime（5000ms間隔）のみで召喚 | 未修正 |

---

## 修正作業手順（修正着手時）

1. `design.md` のシーケンス定義を更新
2. `vd-masterdata-ingame-data-creator` スキルで CSV を再生成
3. `vd-masterdata-ingame-design-json-creator` スキルで `design.json` を更新
4. `vd-masterdata-ingame-xlsx-creator` スキルで `vd_all.xlsx` を再生成
