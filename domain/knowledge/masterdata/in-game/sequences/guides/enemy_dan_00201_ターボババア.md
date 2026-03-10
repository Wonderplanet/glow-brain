# ターボババア（enemy_dan_00201）- MstAutoPlayerSequence 解説

作品: **dan**（MstSeries.id = `dan`）
敵キャラID: `enemy_dan_00201`

---

## 敵キャラ概要

> 神出鬼没で、全国各地で暴れ回ってた近代妖怪が、心霊スポットのトンネルで地縛霊と合体した存在。

- **種別**: ボスキャラクター（Boss形態が基本）
- **変身機能**: なし
- **出現カラー**: Colorless / Red / Yellow / Blue
- **最大HP**: 100,000（general_n_Boss_Colorless）

---

## 設計上の特徴

1. **作品を代表するボスキャラ** - danシリーズ全ステージ（Normal〜VeryHard・レイド・イベント）に幅広く登場する主要ボス
2. **高速移動タイプ** - `move_speed=75` はdanシリーズ最速クラス。最大HP・攻撃力と合わさって制圧力が高い
3. **初期配置ボスとしての演出** - `InitialSummon + ElapsedTime=3000` による「登場するが3秒後に動き出す」パターンで緊張感を演出
4. **単体登場が基本** - セルポ星人とは対照的に常に1体ずつの召喚。ただし高係数で圧倒的な強さを持つ
5. **難易度ごとの係数差が大きい**
   - Normal: enemy_hp_coef=0.45〜0.55 で抑制
   - Hard: enemy_hp_coef=2.8〜20 で大幅強化
   - VeryHard: enemy_hp_coef=10〜42 でさらに強化
6. **レイドにおけるAdventBoss役** - レイドではウェーブ後半のAdventBoss2/3として機能し、撃破スコア180〜500の主要得点源
7. **キャラゲットステージのメインターゲット** - `charaget02`系ではセルポ星人（charaget01系）とは別にターボババアが主役として独自ステージを持つ

---

## MstEnemyStageParameter 一覧

| ID | character_unit_kind | role_type | color | HP | attack_power | move_speed | attack_combo_cycle |
|----|----|----|----|----|----|----|---|
| e_dan_00201_general_n_Boss_Colorless | Boss | Attack | Colorless | 100,000 | 1,000 | 75 | 1 |
| e_dan_00201_general_n_Boss_Red | Boss | Attack | Red | 10,000 | 50 | 65 | 1 |
| e_dan_00201_general_vn_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 75 | 1 |
| e_dan_00201_mainquest_glo2_Boss_Blue | Boss | Attack | Blue | 30,000 | 400 | 55 | 1 |
| e_dan_00201_dan1_advent_Boss_Yellow | Boss | Technical | Yellow | 100,000 | 500 | 50 | 1 |
| e_dan_00201_dan1challenge_Boss_Blue | Boss | Attack | Blue | 10,000 | 100 | 55 | 1 |

### パラメータの特徴

- **speed (move_speed)** が最大75と高速。danシリーズの敵の中でも最速クラス。
- **HP100,000 + 攻撃力1,000** の最大形態（general_n_Boss_Colorless）は圧倒的な制圧力。
- イベント形態（dan1_advent）では HP100,000 + 攻撃力500 + Technical属性 Yellow で高難度設計。

---

## MstAutoPlayerSequence 召喚パターン分析

### 登場ステージ一覧

| ステージ種別 | sequence_set_id | 役割 |
|---|---|---|
| Normal | normal_dan_00001 | DarknessKomaClearedでボス出現 |
| Normal | normal_dan_00005 | 開幕ボスとして初期配置 |
| Normal | normal_dan_00006 | 時間経過で出現 |
| Hard | hard_dan_00001 | 拠点ダメージをきっかけにボス出現 |
| Hard | hard_dan_00005 | 開幕ボスとして初期配置 |
| Hard | hard_dan_00006 | 時間経過で出現 |
| VeryHard | veryhard_dan_00004 | 初期配置 + グループ移行後に複数回登場 |
| VeryHard | veryhard_dan_00005 | 時間経過で単発登場 |
| VeryHard | veryhard_dan_00006 | 高倍率で複数回登場 |
| イベント（チャレンジ） | event_dan1_challenge01_00001 | 拠点ダメージで出現 |
| イベント（キャラゲット） | event_dan1_charaget02_00001〜00004 | メインターゲットとして登場 |
| イベント（サベージ） | event_dan1_savage_00001 | 初期配置で登場 |
| レイド | raid_dan1_00001 | ウェーブ4・6でAdventBossとして出現 |
| メインクエスト（glo2） | hard/normal/veryhard_glo2_00001 | 時間経過でボス出現 |

---

### Normal難度（normal_dan_00001）のシーケンス

```
・DarknessKomaCleared = 2 → e_dan_00201_general_n_Boss_Colorless を1体召喚
・ElapsedTime = 500       → glo_00001系（敵）を5体召喚
```

**特徴**: 闇コマ2個クリアというシンプルなトリガーで序盤からボスが出現。HP100,000・攻撃力1,000の最強形態が登場するため、Normal難度にも関わらずインパクトが大きい。ただしHP係数（enemy_hp_coef=0.55, enemy_attack_coef=0.3）でスケーリングされ実際の難易度は抑制されている。

---

### Normal難度（normal_dan_00005）のシーケンス

```
・InitialSummon = 0 → e_dan_00201_general_n_Boss_Colorless を1体（位置0.5に配置）
  → move_start_condition_type: ElapsedTime, value: 3000
  （開始3秒後に移動開始）
```

**特徴**: 開幕からフィールド上に配置されているが、移動開始まで3秒の猶予がある。プレイヤーに準備の時間を与えつつ、確実にボスと対峙させる設計。

---

### Hard難度（hard_dan_00001）のシーケンス

```
[フェーズ1]
・ElapsedTime = 500  → glo_00001系を5体
・ElapsedTime = 5000 → glo_00001系を3体

[グループ切替: DarknessKomaCleared=2]
[フェーズ2（group1）]
・ElapsedTimeSinceSequenceGroupActivated = 0
    → c_dan_00001_general_h_Boss_Colorless を1体（HP係数45、セルポ星人ボス形態）
    → e_dan_00201_general_n_Boss_Colorless を1体（HP係数2.8、ターボババア）
    → e_dan_00001_general_h_Normal_Colorless を8体（HP係数4.9）
・ElapsedTimeSinceSequenceGroupActivated = 100 → glo_00001系10体
・OutpostHpPercentage = 80 → e_dan_00001 を4体追加
```

**特徴**: 闇コマ2個クリアでグループ移行すると同時にセルポ星人ボスとターボババアが**同時出現**。セルポ星人8体の大群も同時に展開される総攻撃フェーズ。ターボババアはここでは「強力な脇役ボス」の位置づけ。

---

### Hard難度（hard_dan_00006）のシーケンス

```
・ElapsedTime = 1500 → e_dan_00201_general_n_Boss_Red を1体（enemy_hp_coef=20, enemy_attack_coef=28）
```

**特徴**: 単発登場だが、HP係数20・攻撃係数28という高倍率設定。Red属性の弱いベース（HP10,000, 攻撃50）に高係数を掛け合わせることで、ステージ難易度を調整している。

---

### VeryHard難度（veryhard_dan_00004）のシーケンス

```
[フェーズ1]
・InitialSummon = 0 → e_dan_00201_general_n_Boss_Colorless を1体（位置0.5）
  → move_start_condition: ElapsedTime=3000 で移動開始

[グループ切替]
[フェーズ2（group1）]
・OutpostHpPercentage = 99 → e_dan_00201_general_vn_Normal_Colorless を1体（enemy_attack_coef=20）
・FriendUnitDead = 8       → e_dan_00201_general_vn_Normal_Colorless を1体（delay=500）
・FriendUnitDead = 9       → e_dan_00201_general_vn_Normal_Colorless を1体（delay=600）
```

**特徴**: 開幕に圧倒的な存在感で登場し、グループ移行後は拠点ダメージや仲間の撃破をトリガーに Normal 形態のターボババアが段階的に追加召喚される。終盤になるほど複数体が存在するタフな構造。

---

### VeryHard難度（veryhard_dan_00006）のシーケンス

```
・ElapsedTime = 1650      → e_dan_00201_general_n_Boss_Red を1体（enemy_hp_coef=35, enemy_attack_coef=30）
・OutpostDamage = 1       → e_dan_00201_general_n_Boss_Red を1体（Fall演出, hp係数=42, atk係数=30）
```

**特徴**: 2体のターボババアが異なるタイミングで出現する高難度設定。HP係数35〜42、攻撃係数30という極めて高い倍率で、実質的な強ボス2体戦となる。

---

### レイド（raid_dan1_00001）のシーケンス

```
[ウェーブ4（w4）]
・FriendUnitDead = 19 → e_dan_00201_dan1_advent_Boss_Yellow を1体
  → aura_type: AdventBoss2, defeated_score=180, delay=100

[ウェーブ6（w6）]
・ElapsedTimeSinceSequenceGroupActivated = 0 → e_dan_00201_dan1_advent_Boss_Yellow を1体
  → aura_type: AdventBoss3, defeated_score=500
```

**特徴**: レイドでは「AdventBoss」という特殊なオーラタイプで登場。AdventBoss2（ウェーブ4）→ AdventBoss3（ウェーブ6）と段階的に格が上がっていく。撃破スコアも180→500と大幅に上昇し、後半の主役ボスとして機能する。

---

### キャラゲットステージ（event_dan1_charaget02_00001〜00004）のシーケンス

```
[charaget02_00001]
・ElapsedTime = 200      → c_dan_00201_airaget_Boss_Colorless を1体（enemy_hp_coef=1, enemy_attack_coef=1.5）

[charaget02_00002]
・EnterTargetKomaIndex = 0 → c_dan_00201_airaget_Boss_Red を1体（enemy_hp_coef=1.5, enemy_attack_coef=2.2）

[charaget02_00004]
・ElapsedTime = 200      → c_dan_00101_airaget_Normal_Red を1体（Boss, HP係数2）
・ElapsedTime = 800      → c_dan_00201_airaget_Boss_Red を1体（Fall0演出, HP係数3）
```

**特徴**: キャラゲットステージではターボババアが**メインの対峙相手**として設計されている。`c_dan_00201_airaget_*`（キャラ獲得イベント専用のEnemyStageParameter）を使用し、難易度は抑えめ（coef=1〜3）だが段階的に強化される。

