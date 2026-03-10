# グエン（enemy_spy_00101）- MstAutoPlayerSequence 解説

作品: **spy**（MstSeries.id = `spy`）
敵キャラID: `enemy_spy_00101`

---

## 敵キャラ概要

> 東国の外務大臣を失脚に追い込もうとした悪人、エドガーの部下。アーニャ・フォージャーの通信を受けてロイドたちの住居へ襲撃に来た。

- **種別**: ボスキャラクター（Normal形態から始まりBoss形態へ昇格するパターンが特徴）
- **変身機能**: なし
- **出現カラー**: Colorless / Blue / Green / Yellow / Red

---

## 設計上の特徴

1. **Normal→Boss昇格がコアパターン** - Normal形態で登場し、`FriendUnitDead=1` という最速トリガーでBoss形態に格上げされる構造が全難度を通じて一貫している
2. **単体登場が基本** - 密輸残党が大群で押すのに対し、グエンは1体ずつの登場。質で存在感を出すキャラデザイン
3. **係数設計の振り幅が大きい** - HP係数1.5（Normal）〜70（レイドw6）まで約47倍の幅があり、同一ベースパラメータのままステージ難易度を調整
4. **`veryhard_spy_00003`でのダブルボス同時出現** - 密輸残党Boss（HP係数40）と同時乱入する、spyシリーズ最も圧力の高い登場演出
5. **レイドにおける全ウェーブ皆勤ボス** - w2〜w6の5ウェーブにAdventBoss1→2と段階的に格上がりしながら登場し続けるレイドの主役
6. **サベージでの長時間リスポーン（delay=4000）** - 撃破後4000フレーム（≒67秒）後に復活するサベージ専用設計で、継続的な脅威感を演出
7. **チャレンジ最終ステージ（00004）でHP係数40** - 同ステージでユーリ（秘密警察キャラ）と共演し、spyシリーズストーリーと連動したスペシャルボス起用

---

## MstEnemyStageParameter 一覧

| ID | character_unit_kind | role_type | color | HP | attack_power | move_speed | knockback | attack_combo_cycle |
|----|----|----|----|----|----|----|----|----|
| e_spy_00101_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 50 | 31 | なし | 1 |
| e_spy_00101_general_n_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 50 | 31 | 2 | 1 |
| e_spy_00101_general_n_Boss_Blue | Boss | Attack | Blue | 10,000 | 50 | 31 | 2 | 1 |
| e_spy_00101_general_vh_Normal_Blue | Normal | Attack | Blue | 1,000 | 100 | 31 | なし | 1 |
| e_spy_00101_general_vh_Normal_Green | Normal | Attack | Green | 1,000 | 100 | 31 | なし | 1 |
| e_spy_00101_general_vh_Boss_Blue | Boss | Attack | Blue | 10,000 | 100 | 31 | 2 | 1 |
| e_spy_00101_general_vh_Boss_Green | Boss | Attack | Green | 10,000 | 100 | 31 | 2 | 1 |
| e_spy_00101_spy1challenge_Normal_Blue | Normal | Attack | Blue | 5,000 | 200 | 30 | 2 | 1 |
| e_spy_00101_spy1challenge_Normal_Green | Normal | Technical | Green | 5,000 | 200 | 30 | 2 | 1 |
| e_spy_00101_spy1challenge_Normal_Red | Normal | Attack | Red | 5,000 | 200 | 30 | 2 | 1 |
| e_spy_00101_spy1savage_Normal_Blue | Normal | Technical | Blue | 10,000 | 500 | 32 | 3 | 1 |
| e_spy_00101_damianget_Normal_Colorless | Normal | Attack | Colorless | 2,500 | 200 | 30 | 3 | 1 |
| e_spy_00101_frankyget_Normal_Yellow | Normal | Attack | Yellow | 2,500 | 200 | 30 | 3 | 1 |

### パラメータの特徴

- **通常形態（general_n）**: HP1,000〜10,000、全ロールが Attack 統一。Boss形態はknoback=2。
- **VeryHard形態（general_vh）**: 攻撃力が100に強化。Boss形態も2系統（Blue/Green）用意。
- **`spy1savage_Normal_Blue`**: Technical属性・攻撃500・HP10,000の高スペック形態。サベージでは`last_boss_trigger: Boss`扱い。
- **チャレンジ形態**: HP5,000・攻撃200・Normal扱いだが実質サブボスとして機能。
- **キャラゲット形態**: HP2,500・攻撃200 で通常とチャレンジの中間。

---

## MstAutoPlayerSequence 召喚パターン分析

### 登場ステージ一覧

| ステージ種別 | sequence_set_id | エントリ数 | 合計召喚数 |
|---|---|---|---|
| Normal | normal_spy_00001 | 1 | 1 |
| Normal | normal_spy_00002 | 2 | 2 |
| Normal | normal_spy_00006 | 1 | 1 |
| Hard | hard_spy_00001 | 1 | 1 |
| Hard | hard_spy_00002 | 2 | 2 |
| Hard | hard_spy_00006 | 1 | 1 |
| VeryHard | veryhard_spy_00001 | 2 | 2 |
| VeryHard | veryhard_spy_00003 | 2 | 2 |
| VeryHard | veryhard_spy_00006 | 1 | 1 |
| イベント（チャレンジ）| event_spy1_challenge01_00001〜00004 | 複数 | 複数 |
| イベント（サベージ）| event_spy1_savage_00001 | 2 | 2 |
| イベント（サベージ）| event_spy1_savage_00002 | 2 | 2 |
| レイド | raid_spy1_00001 | 5 | 5 |
| キャラゲット | event_spy1_charaget01_00002〜00003 他 | 複数 | 複数 |

---

### Normal難度（normal_spy_00001）のシーケンス

```
・ElapsedTime = 650 → e_spy_00101_general_n_Normal_Colorless を1体（HP係数1.5、攻撃係数2）
```

**特徴**: シンプルな時間経過トリガーで単発登場。Normal形態（HP1,000）をHP係数1.5で実質1,500相当に調整。spyシリーズ最初のステージでグエンを引き合わせる「顔見せ」的な登場。

---

### Normal難度（normal_spy_00002）- Normal→Boss昇格パターン

```
・InitialSummon = 1    → e_spy_00101_general_n_Normal_Colorless を1体（位置1.6、FoeEnterSameKoma 起動）
                          → HP係数1.5、攻撃係数2
・FriendUnitDead = 1   → e_spy_00101_general_n_Boss_Colorless を1体（delay=50）
                          → HP係数1.5、攻撃係数4
```

**特徴**: グエンのコアデザインを最もシンプルに体現したステージ。最初はNormal形態で登場し、1体倒されると同じグエンが今度はBoss形態（攻撃力2倍）で再登場する「格上げ演出」。

---

### Hard難度（hard_spy_00001）のシーケンス

```
・ElapsedTime = 400 → e_spy_00101_general_n_Normal_Colorless を1体（HP係数18、攻撃係数19）
```

**特徴**: 単発登場ながらHP係数18・攻撃係数19という高倍率設定。通常形態HP1,000 × 18 = 実質18,000、攻撃 × 19 = 実質950と、Normal難度のグエンとは別物の強さ。

---

### Hard難度（hard_spy_00002）- Normal→Boss昇格パターン（強化版）

```
・InitialSummon = 1    → e_spy_00101_general_n_Normal_Colorless を1体（位置1.6、FoeEnterSameKoma 起動）
                          → HP係数15、攻撃係数15
・FriendUnitDead = 1   → e_spy_00101_general_n_Boss_Colorless を1体（delay=50）
                          → HP係数18、攻撃係数20
```

**特徴**: `normal_spy_00002`と同じNormal→Boss昇格パターンを Hard に適用。係数だけ上がり（15/15 → 18/20）、構造的な演出は踏襲。

---

### VeryHard難度（veryhard_spy_00001）のシーケンス

```
・ElapsedTime = 0     → e_spy_00101_general_vh_Normal_Blue を1体（HP係数30、攻撃係数15）
・FriendUnitDead = 1  → e_spy_00101_general_vh_Boss_Blue を1体（HP係数42、攻撃係数15）
```

**特徴**: VeryHard形態（攻撃100）でのNormal→Boss昇格。`ElapsedTime=0`でゲーム開始直後に出現し、仲間が1体倒されると即座にBoss形態（HP係数42 = 実質HP420,000相当）に格上げ。最高難度の圧倒的な初登場演出。

---

### VeryHard難度（veryhard_spy_00003）- ダブルボス同時出現

```
・InitialSummon = 1   → e_spy_00101_general_vh_Normal_Blue を1体（位置1.5、EnterTargetKoma=2 で移動）
                          → HP係数30、攻撃係数11
・FriendUnitDead = 1  → e_spy_00101_general_vh_Boss_Blue を1体（Fall演出, 位置1.5）
                          → HP係数42、攻撃係数15（delay=100）
・FriendUnitDead = 1  → e_spy_00001_general_n_Boss_Blue を1体（同時）
                          → HP係数40、攻撃係数26（delay=100）← 密輸残党ボスも同時出現
```

**特徴**: グエン（Boss）と密輸残党（Boss形態）が**同時に出現**する。仲間1体撃破という同一トリガーで2体のボスが乱入してくる設計で、敵側のシナジー演出が際立つ最高難度ステージ。

---

### イベントチャレンジ（event_spy1_challenge01_00001）のシーケンス

```
・ElapsedTime = 1000   → e_spy_00101_spy1challenge_Normal_Green を1体（Boss扱い, HP係数17、攻撃係数1.5）
・FriendUnitDead = 3   → e_spy_00101_spy1challenge_Normal_Green を1体（Boss扱い, HP係数17）
```

**特徴**: `last_boss_trigger: Boss`かつHP係数17の強化グエン。密輸残党の大群（10体単位）に守られながら、3体撃破後に再登場するサブボス設計。

---

### イベントチャレンジ（event_spy1_challenge01_00002）のシーケンス

```
・FriendUnitDead = 3   → e_spy_00101_spy1challenge_Normal_Red を1体（Boss扱い, HP係数30, 攻撃係数2、delay=150）
```

**特徴**: 3体撃破が起動条件のHP係数30グエン。チャレンジ難易度の中で最初にRed属性が登場。

---

### イベントチャレンジ（event_spy1_challenge01_00004）- 最高難度チャレンジ

```
・ElapsedTime = 2300   → e_spy_00101_spy1challenge_Normal_Blue を1体（Boss扱い, HP係数20、攻撃係数4）
・FriendUnitDead = 3   → e_spy_00101_spy1challenge_Normal_Blue を1体（Boss扱い, HP係数40, delay=300）
```

**特徴**: HP係数40（実質HP200,000）のグエンが3体撃破後に登場。同ステージでユーリ・ブライア（c_spy_00501）も共演し、最高難度チャレンジの主役ボスとして機能。

---

### イベント（event_spy1_savage_00001）のシーケンス

```
・InitialSummon = 1   → e_spy_00101_spy1savage_Normal_Blue を1体（Boss扱い, 位置0.6）
                          → HP係数8、攻撃係数1.8、ElapsedTime=250 で移動開始
・FriendUnitDead = 1  → e_spy_00101_spy1savage_Normal_Blue を1体（HP係数8, delay=4000）
```

**特徴**: サベージではTechnical属性・攻撃500のハイスペック形態で`last_boss_trigger: Boss`として出現。4000フレーム（≒67秒）のdelay後に再登場するため、撃破しても非常に長い間隔を挟んで復活する設計。

---

### レイド（raid_spy1_00001）のシーケンス

```
[w2] ElapsedTimeSinceSequenceGroupActivated=70
    → e_spy_00101_spy1challenge_Normal_Red (aura_type: AdventBoss1, HP係数30, 攻撃係数3.5, delay=100)

[w3] → e_spy_00101_spy1challenge_Normal_Red (AdventBoss1, HP係数30, 攻撃係数3.5, delay=100)

[w4] → e_spy_00101_spy1challenge_Normal_Red (AdventBoss1, HP係数30, 攻撃係数3.5, delay=100)

[w5] → e_spy_00101_spy1challenge_Normal_Red (AdventBoss2, HP係数40, 攻撃係数4, delay=100)
                                              ← AdventBoss2 へ格上がり

[w6] → e_spy_00101_spy1challenge_Normal_Red (AdventBoss2, HP係数70, 攻撃係数5)
                                              ← HP係数が40→70 に大幅強化
```

**特徴**: レイドでは全6ウェーブのうちw2〜w6（5ウェーブ）に登場するレイド主役ボス。AdventBoss1（w2〜4）→ AdventBoss2（w5〜6）と段階的に格上がりし、HP係数も30→40→70と上昇。最終ウェーブでの係数70は密輸残党と並ぶ最高倍率。

