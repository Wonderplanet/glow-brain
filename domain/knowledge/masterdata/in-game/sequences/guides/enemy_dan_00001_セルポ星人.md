# セルポ星人（enemy_dan_00001）- MstAutoPlayerSequence 解説

作品: **dan**（MstSeries.id = `dan`）
敵キャラID: `enemy_dan_00001`
変身後ID: `enemy_dan_00101`（セルポ星人 (変身)）

---

## 敵キャラ概要

> モモを誘拐した宇宙人。クローンで個体を増やす雄のみの種。生殖機能を取り戻すため、人間の女性を狙う。

- **種別**: 雑魚敵メイン（Normal / Boss 両形態あり）
- **変身機能**: あり（HP50%以下でenemy_dan_00101に変身）
- **出現カラー**: Colorless / Red / Blue / Green / Yellow

---

## 設計上の特徴

1. **量で攻める雑魚敵** - 通常は複数体〜大量（最大50体）で一斉召喚される
2. **変身による強化ギミック** - HP50%で変身（enemy_dan_00101）し、変身後に援軍召喚トリガーが発動する場合がある
3. **難易度ごとのトリガー差異**
   - Normal: `DarknessKomaCleared`（闇コマ消し）
   - Hard: `DarknessKomaCleared` → `FriendUnitDead`（仲間の死亡）
   - VeryHard: `InitialSummon` + `OutpostDamage` → `FriendUnitDead` 多段トリガー
4. **拠点ダメージ起点の大攻勢** - VeryHard以上では拠点に初めてダメージが入った瞬間にボス2体が即出現するデザイン
5. **多様なカラー・役割タイプ** - Colorless/Red/Blue/Green/Yellow の5色、Defense/Attack/Technical の3ロールをカバーし、ステージ・難易度ごとに使い分けられる

---

## MstEnemyStageParameter 一覧

以下はenemy_dan_00001が持つ代表的なパラメータバリエーションです。

| ID | character_unit_kind | role_type | color | HP | attack_power | move_speed | attack_combo_cycle | 変身先 |
|----|----|----|----|----|----|----|----|----|
| e_dan_00001_general_n_Normal_Red | Normal | Defense | Red | 10000 | 50 | 34 | 1 | なし |
| e_dan_00001_general_n_trans_Normal_Colorless | Normal | Attack | Colorless | 1000 | 50 | 34 | 1 | e_dan_00101_general_n_Normal_Colorless |
| e_dan_00001_general_n_trans_Normal_Red | Normal | Attack | Red | 1000 | 50 | 34 | 1 | e_dan_00101_general_n_Normal_Red |
| e_dan_00001_general_h_Normal_Colorless | Normal | Defense | Colorless | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_h_Normal_Red | Normal | Defense | Red | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_h_trans_Normal_Colorless | Normal | Attack | Colorless | 10000 | 100 | 34 | 1 | e_dan_00101_general_h_Normal_Colorless |
| e_dan_00001_general_h_trans_Normal_Red | Normal | Attack | Red | 10000 | 100 | 34 | 1 | e_dan_00101_general_h_Normal_Red |
| e_dan_00001_general_vh_Normal_Colorless | Normal | Attack | Colorless | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_vh_Normal_Green | Normal | Attack | Green | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_vh_Boss_Colorless | Boss | Attack | Colorless | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_vh_Boss_Red | Boss | Defense | Red | 10000 | 100 | 34 | 1 | なし |
| e_dan_00001_general_vh_trans_Boss_Blue | Boss | Technical | Blue | 10000 | 100 | 25 | 1 | e_dan_00101_general_vh_Boss_Blue |
| e_dan_00001_general_vh_trans_Normal_Blue | Normal | Technical | Blue | 10000 | 100 | 34 | 1 | e_dan_00101_general_vh_Normal_Blue |
| e_dan_00001_general_vh_trans_Normal_Green | Normal | Technical | Green | 10000 | 100 | 34 | 1 | e_dan_00101_general_vh_Normal_Green |
| e_dan_00001_general_vh_trans_Normal_Red | Normal | Attack | Red | 10000 | 100 | 34 | 1 | e_dan_00101_general_vh_Normal_Red |
| e_dan_00001_mainquest_glo2_Normal_Blue | Normal | Attack | Blue | 5000 | 200 | 25 | 1 | なし |
| e_dan_00001_dan1_advent_Normal_Colorless | Normal | Attack | Colorless | 30000 | 200 | 25 | 1 | なし |
| e_dan_00001_dan1_advent_Normal_Yellow | Normal | Technical | Yellow | 30000 | 200 | 30 | 1 | なし |
| e_dan_00001_dan1_advent_Boss_Colorless | Boss | Attack | Colorless | 10000 | 100 | 28 | 1 | なし |
| e_dan_00001_dan1_advent_trans1_Boss_Colorless | Boss | Attack | Colorless | 10000 | 100 | 28 | 1 | e_dan_00101_dan1_advent_trans1_Normal_Colorless |
| e_dan_00001_dan1challenge_Normal_Blue | Normal | Attack | Blue | 10000 | 100 | 28 | 1 | なし |
| e_dan_00001_dan1challenge_Normal_Green | Normal | Attack | Green | 10000 | 100 | 40 | 1 | なし |

### 変身条件

- 変身条件タイプ: `HpPercentage`（HP割合）
- 変身条件値: `50`（HP50%以下で変身）
- 変身後キャラ: `enemy_dan_00101`（セルポ星人 (変身)）

---

## MstAutoPlayerSequence 召喚パターン分析

### 登場ステージ一覧

| ステージ種別 | sequence_set_id | 出現回数（エントリ数） | 合計召喚数 |
|---|---|---|---|
| Normal | normal_dan_00002 | 6 | 15 |
| Normal | normal_dan_00003 | 1 | 1 |
| Normal | normal_dan_00006 | 2 | 2 |
| Hard | hard_dan_00001 | 3 | 13 |
| Hard | hard_dan_00002 | 10 | 28 |
| Hard | hard_dan_00003 | 1 | 1 |
| Hard | hard_dan_00004 | 4 | 4 |
| Hard | hard_dan_00005 | 4 | 5 |
| Hard | hard_dan_00006 | 3 | 5 |
| VeryHard | veryhard_dan_00001 | 25 | 44 |
| VeryHard | veryhard_dan_00002 | 6 | 11 |
| VeryHard | veryhard_dan_00003 | 8 | 24 |
| VeryHard | veryhard_dan_00004 | 7 | 11 |
| VeryHard | veryhard_dan_00005 | 4 | 4 |
| VeryHard | veryhard_dan_00006 | 6 | 11 |
| イベント（チャレンジ）| event_dan1_challenge01_00001 | 6 | 61 |
| イベント（チャレンジ）| event_dan1_challenge01_00002 | 4 | 73 |
| イベント（チャレンジ）| event_dan1_challenge01_00004 | 2 | 2 |
| イベント（キャラゲット）| event_dan1_charaget01_00001〜00007 | 複数 | 複数 |
| レイド | raid_dan1_00001 | 10 | 49 |
| メインクエスト（glo2）| hard_glo2_00001, normal_glo2_00001 他 | 複数 | 複数 |

---

### Normal難度（normal_dan_00002）のシーケンス構造

```
[フェーズ1]
・DarknessKomaCleared = 3 → e_dan_00001_general_n_Normal_Red を3体（位置1.6/1.7/1.8）同時召喚
・ElapsedTime = 500 → glo_00001系を3体補助召喚

[フェーズ2（グループ切替: FriendUnitDead=1）]
・ElapsedTimeSinceSequenceGroupActivated = 50   → 4体召喚
・ElapsedTimeSinceSequenceGroupActivated = 500  → 4体召喚
・ElapsedTimeSinceSequenceGroupActivated = 1500 → 4体召喚
```

**特徴**: 闇コマ3個クリアをトリガーに少数から呼び出し、最初の仲間が倒されるとグループ移行して波状攻撃。

---

### Hard難度（hard_dan_00002）のシーケンス構造

```
[フェーズ1]
・DarknessKomaCleared = 1 → 3体を位置指定（0.7/1.7/1.8）で個別召喚
  + FoeEnterSameKoma → 追加1体

[グループ切替: FriendUnitDead=1]
[フェーズ2]
・ElapsedTimeSinceSequenceGroupActivated = 0    → ボス登場（c_dan_00101 系）
・ElapsedTimeSinceSequenceGroupActivated = 500  → 3体召喚
・ElapsedTimeSinceSequenceGroupActivated = 750  → 3体召喚
・FriendUnitDead = 2 → 6体召喚
・FriendUnitDead = 3 → 5体召喚
・FriendUnitDead = 4 → 6体召喚
```

**特徴**: 闇コマ1個で起動し、最初の仲間が倒れると即ボス登場。以降は仲間が倒されるたびに増援を大量展開。

---

### VeryHard難度（veryhard_dan_00001）のシーケンス構造

```
[フェーズ1]
・InitialSummon    → e_dan_00001_general_vh_Normal_Colorless を1体（初期配置）
・ElapsedTime = 0  → e_dan_00001_general_h_Normal_Red を3体召喚

[グループ切替: OutpostDamage=1（拠点にダメージ）]
[フェーズ2（group1）]
・element_id=3: ElapsedTimeSinceSequenceGroupActivated=0
    → Boss_Colorless (HP係数35) を1体
    → Boss_Red (HP係数65) を2体（連続）
・element_id=4: Normal_Colorless を2体 + Red を1体（Fall演出）
・element_id=5: 3体以上を分散Fall
・element_id=6〜7: 継続召喚
・element_id=8: OutpostHpPercentage=60 → 追加強化召喚
・element_id=9〜12: FriendUnitDead = 4〜6 ごとに3〜6体の増援
```

**特徴**: 拠点に初めてダメージが入ることでグループ移行し、一気にボス2体が出現。以降は仲間の撃破数に応じて増援が切れ目なく続く長期持久戦の構造。

---

### VeryHard難度（veryhard_dan_00002）- 変身形態特化ステージ

```
・ElapsedTime = 300    → e_dan_00001_general_vh_trans_Boss_Blue (Fall4演出) 1体 ← 変身ボスとして登場
・ElapsedTime = 1000   → e_dan_00001_general_vh_trans_Normal_Blue を4体
・ElapsedTime = 4000   → e_dan_00001_general_vh_trans_Normal_Blue を3体
・EnterTargetKomaIndex = 3/5 → 指定マス進入で各1体（Fall4/Fall演出）
・OutpostDamage = 1    → 拠点ダメージで1体

・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を3体 ← 変身時に援軍
・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を50体（大量投入）
・FriendUnitTransform = 1 → e_dan_00101_general_vh_Normal_Blue を30体（Fall4演出）
```

**特徴**: このステージは変身前後の関係が核心。trans（変身あり）のセルポ星人が倒されるとHP50%で変身、さらに `FriendUnitTransform` というトリガーで変身した瞬間に変身後セルポ（enemy_dan_00101）が50体・30体と大量召喚される強力な連鎖構造。

---

### イベント（event_dan1_challenge01_00001）のシーケンス

```
・ElapsedTime = 200   → e_dan_00001_dan1challenge_Normal_Blue を3体
・ElapsedTime = 1000  → e_dan_00001_dan1challenge_Normal_Blue を50体
・OutpostHpPercentage = 99 → 5体 + ターボババア(e_dan_00201)を1体
・InitialSummon = 1/2  → 初期配置 各1体
```

**特徴**: チャレンジステージでは大量（50体）の召喚が特徴的。ターボババアと共演する場面も多い。

