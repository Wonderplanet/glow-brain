# 密輸組織の残党（enemy_spy_00001）- MstAutoPlayerSequence 解説

作品: **spy**（MstSeries.id = `spy`）
敵キャラID: `enemy_spy_00001`

---

## 敵キャラ概要

> <黄昏>が襲撃した密輸組織の残党。奪われた美術品を取り戻すため、仕込んだ発信機を元に追跡したが返り討ちにあう。

- **種別**: 雑魚敵メイン（Normal / Boss 両形態あり）
- **変身機能**: なし
- **出現カラー**: Colorless / Blue / Yellow / Red / Green

---

## 設計上の特徴

1. **spyシリーズ最多登場の主役雑魚敵** - Normal〜VeryHard・サベージ・レイドまで全ステージタイプに登場
2. **量的圧倒がコアデザイン** - 単体では弱いが、10体・20体・99体規模での一斉召喚が基本戦術
3. **唯一のステージ5段階チェーン（normal_spy_00004）** - FriendUnitDead連鎖で1体→99体まで指数的にエスカレートする特殊構造
4. **サベージ用特化形態** - `move_speed=70`という高速形態（`spy1savage_Normal_Colorless`）でマス突破を狙う役割
5. **HP係数設計で幅広い難易度対応** - 通常形態（HP1,000）に対してHP係数1〜80まで適用し、Normal〜VeryHardで使い回す
6. **BossBlue格上げパターン** - Normal難度でも仲間2体が倒されると`e_spy_00001_general_n_Boss_Blue`が登場する「中ボス化」トリガー

---

## MstEnemyStageParameter 一覧

| ID | character_unit_kind | role_type | color | HP | attack_power | move_speed | knockback | attack_combo_cycle |
|----|----|----|----|----|----|----|----|----|
| e_spy_00001_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 50 | 34 | なし | 1 |
| e_spy_00001_general_n_Normal_Blue | Normal | Defense | Blue | 1,000 | 50 | 34 | なし | 1 |
| e_spy_00001_general_n_Boss_Blue | Boss | Attack | Blue | 10,000 | 50 | 34 | 2 | 1 |
| e_spy_00001_general_vh_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 50 | 34 | 2 | 1 |
| e_spy_00001_spy1challenge_Normal_Blue | Normal | Attack | Blue | 5,000 | 300 | 25 | 3 | 1 |
| e_spy_00001_spy1challenge_Normal_Green | Normal | Attack | Green | 5,000 | 300 | 25 | 3 | 1 |
| e_spy_00001_spy1challenge_Normal_Yellow | Normal | Attack | Yellow | 5,000 | 300 | 25 | 3 | 1 |
| e_spy_00001_spy1savage_Normal_Colorless | Normal | Attack | Colorless | 5,000 | 300 | **70** | 3 | 1 |
| e_spy_00001_damianget_Normal_Red | Normal | Attack | Red | 5,000 | 300 | 25 | 3 | 1 |
| e_spy_00001_frankyget_Normal_Colorless | Normal | Attack | Colorless | 5,000 | 300 | 25 | 3 | 1 |

### パラメータの特徴

- **通常形態（general）**: HP1,000・攻撃50 の軽量設計。役割は量で圧倒する「消耗戦型」。
- **イベント形態（challenge/savage/charaget）**: HP5,000・攻撃300 と通常の6倍攻撃力。
- **`spy1savage_Normal_Colorless`**: `move_speed=70` はspyシリーズ最高速クラス。サベージ用に特化した高速形態。
- **`general_n_Boss_Blue`**: 通常形態から格上げされた中ボス。HP10,000・knockback=2。

---

## MstAutoPlayerSequence 召喚パターン分析

### 登場ステージ一覧

| ステージ種別 | sequence_set_id | エントリ数 | 合計召喚数 |
|---|---|---|---|
| Normal | normal_spy_00003 | 3 | 12 |
| Normal | normal_spy_00004 | 6 | 108 |
| Normal | normal_spy_00005 | 4 | 18 |
| Normal | normal_spy_00006 | 6 | 16 |
| Hard | hard_spy_00003 | 6 | 26 |
| Hard | hard_spy_00004 | 9 | 18 |
| Hard | hard_spy_00005 | 5 | 14 |
| Hard | hard_spy_00006 | 6 | 14 |
| VeryHard | veryhard_spy_00002 | 10 | 32 |
| VeryHard | veryhard_spy_00003 | 5 | 22 |
| VeryHard | veryhard_spy_00006 | 6 | 14 |
| イベント（チャレンジ）| event_spy1_challenge01_00001〜00004 | 複数 | 複数 |
| イベント（サベージ）| event_spy1_savage_00001 | 6 | 60 |
| イベント（サベージ）| event_spy1_savage_00002 | 4 | 205 |
| レイド | raid_spy1_00001 | 8 | 254 |

---

### Normal難度（normal_spy_00003）のシーケンス

```
・ElapsedTime = 500       → e_spy_00001_general_n_Normal_Colorless を10体（間隔1250）
・InitialSummon = 1       → 1体（位置1.6、FoeEnterSameKoma で移動開始）
・FriendUnitDead = 2      → e_spy_00001_general_n_Boss_Blue を1体（delay=50）← ボス化
```

**特徴**: 序盤に大量召喚（10体）をぶつけ、仲間が2体倒されるとBoss形態（Blue）が後続に現れる2段構成。

---

### Normal難度（normal_spy_00004）- 5段階グループチェーン

```
[フェーズ1]
・ElapsedTime = 0    → e_spy_00001_general_n_Normal_Colorless を1体

[グループ切替チェーン: FriendUnitDead 毎に次グループへ]
  FriendUnitDead=1 → group1 へ:  2体（100フレーム後）
  FriendUnitDead=2 → group2 へ:  1体（100フレーム後）
  FriendUnitDead=3 → group3 へ:  2体（100フレーム後）
  FriendUnitDead=4 → group4 へ:  3体（100フレーム後）
  FriendUnitDead=5 → group5 へ:  99体（500フレーム後）← 最終段
```

**特徴**: spyシリーズ最も特徴的な構造の一つ。仲間が倒されるたびにフェーズが進み、最終的には**99体**の大量召喚が起動する。1体→2体→1体→2体→3体→**99体**という指数的エスカレート設計。

---

### Hard難度（hard_spy_00003）のシーケンス

```
・ElapsedTime = 200     → e_spy_00001_general_n_Normal_Colorless を10体（間隔1400）
・ElapsedTime = 500     → e_spy_00001_general_n_Normal_Colorless を10体（間隔800）
・InitialSummon = 1     → 2体を位置指定（FoeEnterSameKoma 起動）
・FriendUnitDead = 2    → e_spy_00001_general_n_Boss_Blue を1体（HP係数18、攻撃係数20）
・FriendUnitDead = 4    → e_spy_00001_general_n_Normal_Blue を3体（HP係数38.5）
```

**特徴**: 2種類の時間トリガーで計20体をほぼ連続召喚。仲間2体撃破でBoss形態出現、4体撃破でBlue形態（HP係数38.5で実質超高HP）が追加される。

---

### Hard難度（hard_spy_00004）のシーケンス

```
・ElapsedTime = 0  → 1体（通常開始）
・InitialSummon = 1 → 3体を位置指定（1.4/2.6/3.7、FoeEnterSameKoma 起動）

[仲間の死亡ごとに1体ずつ増援]
  FriendUnitDead=1 → 1体
  FriendUnitDead=2 → 1体
  FriendUnitDead=3 → 1体
  FriendUnitDead=4 → 1体
  FriendUnitDead=5 → 10体（最終増援）
```

**特徴**: 全て同一のenemy_sp_00001_general_n_Normal_Colorlessを使用。一定ペースで1体ずつ補充しながら、5体目倒されると一気に10体の増援を投入する。「粘り強い補充型」の設計。

---

### VeryHard難度（veryhard_spy_00002）のシーケンス

```
・ElapsedTime = 300    → e_spy_00001_general_n_Normal_Colorless を10体（HP係数28、攻撃係数14）
・ElapsedTime = 500    → 7体（HP係数28）
・InitialSummon = 1    → 2体を位置指定（FoeEnterSameKoma 起動）
・FriendUnitDead = 2   → e_spy_00001_general_n_Boss_Blue を1体（HP係数40、攻撃係数26）← Fall演出
・FriendUnitDead = 2   → e_spy_00001_general_n_Normal_Blue を3体（HP係数80、攻撃係数6）
・FriendUnitDead = 3   → Normal_Colorless を2体（delay=50）
・EnterTargetKomaIndex = 4 → Normal_Blue を3体
・EnterTargetKomaIndex = 5 → Normal_Colorless を3体 + Normal_Blue を1体（delay=50）
```

**特徴**: `enemy_hp_coef=80` のNormal_Blue形態が最大の脅威。通常形態HP1,000 × 80 = 実質HP80,000相当の超耐久型が3体同時に出現。マス進入も多様なトリガーとして活用される。

---

### イベント（event_spy1_savage_00002）のシーケンス

```
・ElapsedTime = 0      → e_spy_00001_spy1savage_Normal_Colorless を1体（Boss扱い、HP係数0.7、攻撃係数4）
・EnterTargetKomaIndex = 2 → e_spy_00001_damianget_Normal_Red を99体
  （deactivation: EnterTargetKomaIndex=3 まで有効）
・EnterTargetKomaIndex = 3 → e_spy_00001_damianget_Normal_Red を99体（無制限）
・FriendUnitDead = 5   → e_spy_00001_damianget_Normal_Red を6体（delay=270）
```

**特徴**: マス2に侵入された瞬間から最大**99体連続召喚**が開始し、マス3でも再起動する。マス3突破を絶対に許さない「壁」設計。合計召喚数205はspyシリーズ最大。

---

### レイド（raid_spy1_00001）のシーケンス

```
[プレフェーズ]
・ElapsedTime = 300    → e_spy_00001_spy1savage_Normal_Colorless を3体

[w1] ElapsedTimeSinceSequenceGroupActivated=50 → e_spy_00001_frankyget_Normal_Colorless を30体
[w2] → e_spy_00001_damianget_Normal_Red を30体（HP係数4）
[w3] → e_spy_00001_damianget_Normal_Red を30体（HP係数5）
[w4] → e_spy_00001_damianget_Normal_Red を30体（HP係数5、攻撃係数2）
[w5] → e_spy_00001_damianget_Normal_Red を30体（HP係数6）
[w6] → e_spy_00001_damianget_Normal_Red を99体 + 2体（Fall演出）
```

**特徴**: レイドでは全6ウェーブにわたって継続的に大量召喚（各30体）され、最終ウェーブでは**99体**に。ウェーブを重ねるごとにHP係数が上昇（4→5→5→6）し、着実に強化される長期消耗戦。

