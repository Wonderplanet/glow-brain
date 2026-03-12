# MstAutoPlayerSequence 具体例集

過去の本番データから「合計召喚数15体以上・単一グループ」の実例を抜粋。
設計書作成時の条件値・構成の参考として使用してください。

- [パターン A: e_キャラのみ出現](#パターン-a-e_キャラのみ出現)（A-1〜A-5）
- [パターン B: c_キャラも出現](#パターン-b-c_キャラも出現)（B-1〜B-5）
- [パターン N: Normalクエスト normal難易度](#パターン-n-normalクエスト-normal難易度)（N-1〜N-5）

> **読み方**: 各テーブルは `sequence_element_id` 昇順。同じ elem_id の行は同タイミングで発火（位置・キャラ違いの並列召喚）。

---

## パターン A: e_キャラのみ出現

### A-1. event_mag1_savage_00002
**合計召喚数**: 63体 / **要素数**: 11行
**特徴**: InitialSummon で位置を指定した開幕配置 → 時間経過で Bossオーラつきラッシュ → 拠点ダメージ契機で色違いボス登場

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 200 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 8 | 150 | - | Default |
| 2 | InitialSummon | 1 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 1 | 0 | 1.75 | Default |
| 3 | InitialSummon | 1 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 1 | 0 | 1.4 | Default |
| 4 | InitialSummon | 2 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 1 | 0 | 2.5 | Default |
| 5 | InitialSummon | 2 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 1 | 0 | 2.8 | Default |
| 6 | ElapsedTime | 4500 | 建造物寄生型の怪異 (e_mag_00301_siget_Normal_Yellow) | 5 | 700 | 0.65 | **Boss** |
| 17 | OutpostHpPercentage | 99 | 工事現場の怪異 (e_mag_00401_savage_Boss_Green) | 1 | 0 | - | Default |
| 18 | OutpostHpPercentage | 99 | 建造物寄生型の怪異 (e_mag_00301_aknget_Normal_Green) | 3 | 100 | 2.7 | **Boss** |
| 19 | OutpostHpPercentage | 99 | 建造物寄生型の怪異 (e_mag_00301_aknget_Normal_Green) | 20 | 1000 | - | Default |
| 23 | FriendUnitDead | 17 | 建造物寄生型の怪異 (e_mag_00301_aknget_Normal_Green) | 8 | 50 | - | Default |
| 24 | FriendUnitDead | 17 | 工事現場の怪異 (e_mag_00401_savage_Boss_Green) | 1 | 0 | - | Default |

**設計のポイント**:
- `InitialSummon` で同タイミングに複数の位置指定配置（position=1.4, 1.75, 2.5, 2.8）
- 拠点に初ダメージが入った瞬間（`OutpostHpPercentage=99`）に色違いキャラ＋大量召喚
- 17体倒された終盤に再度 `FriendUnitDead` で後続の波を追加

---

### A-2. veryhard_glo3_00003
**合計召喚数**: 131体（summon=99含む）/ **要素数**: 22行
**特徴**: 序盤は ElapsedTime、以降はほぼ FriendUnitDead で段階的に強化。big（大型）ユニットを節目で登場させる。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 400 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Colorless) | 4 | 600 | Default |
| 2 | ElapsedTime | 1200 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Colorless) | 2 | 1200 | Default |
| 3 | ElapsedTime | 2000 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 1 | 0 | Default |
| 4 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 2 | 1500 | Default |
| 5 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 1 | 0 | Default |
| 6 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 1 | 0 | Default |
| 7 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Colorless) | **99** | 800 | Default |
| 8 | FriendUnitDead | 5 | ボスファントム (e_glo_00101_general_ori3_vh3_Boss_Colorless) | 1 | 0 | **Boss** |
| 9 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Colorless) | 1 | 0 | Boss |
| 10 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Colorless) | 1 | 0 | Boss |
| 11 | FriendUnitDead | 8 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 3 | 200 | Default |
| 12 | FriendUnitDead | 8 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Colorless) | 1 | 0 | Default |
| 13 | FriendUnitDead | 12 | ボスファントム (e_glo_00101_general_ori3_vh3_Boss_Red) | 1 | 0 | Boss |
| 14 | FriendUnitDead | 12 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |
| 15 | FriendUnitDead | 12 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |
| 16 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 3 | 800 | Default |
| 17 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_ori3_vh3_Normal_Red) | 3 | 500 | Default |
| 18 | OutpostDamage | 1 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |
| 19 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Colorless) | 1 | 0 | Boss |
| 20 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |
| 21 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |
| 22 | FriendUnitDead | 10 | ファントム (e_glo_00001_general_ori3_vh3_big_Normal_Red) | 1 | 0 | Boss |

**設計のポイント**:
- `FriendUnitDead=5` で無限補充（count=99）スタート＋ Bossオーラ＋big ユニットを同タイミングで一気に展開
- `FriendUnitDead` の閾値を 3 → 5 → 8 → 9 → 10 → 12 → 15 と細かく刻み、倒すたびに新しい敵追加
- 拠点初ダメージ・HP50%でも別途 big ユニット追加

---

### A-3. veryhard_chi_00002
**合計召喚数**: 143体（summon=99含む）/ **要素数**: 21行
**特徴**: 開幕から ElapsedTime で 99 体の無限補充を即開始。色変更で段階的に強化、OutpostHpPercentage も活用。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 1 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 5 | 350 | Default |
| 2 | ElapsedTime | 700 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | **99** | 700 | Default |
| 3 | ElapsedTime | 1525 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 1 | 0 | Default |
| 4 | ElapsedTime | 1500 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 3 | 500 | Default |
| 5 | FriendUnitDead | 3 | コウモリの悪魔 (e_chi_00201_general_chi_vh_Normal_Yellow) | 1 | 0 | **Boss** |
| 6 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 5 | 350 | Default |
| 7 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 2 | 300 | Default |
| 8 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 5 | 350 | Default |
| 9 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 2 | 300 | Default |
| 10 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 1 | 0 | Default |
| 11 | FriendUnitDead | 10 | ファントム (e_glo_00001_general_chi_vh_big_Normal_Yellow) | 1 | 0 | **Boss** |
| 12 | FriendUnitDead | 10 | ファントム (e_glo_00001_general_chi_vh_big_Normal_Yellow) | 1 | 0 | Boss |
| 13 | FriendUnitDead | 11 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 5 | 350 | Default |
| 14 | FriendUnitDead | 12 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 1 | 0 | Default |
| 15 | FriendUnitDead | 14 | コウモリの悪魔 (e_chi_00201_general_chi_vh_Boss_Yellow) | 1 | 0 | Default |
| 16 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 1 | 0 | **Boss** |
| 17 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_chi_vh_Normal_Red) | 3 | 50 | Boss |
| 18 | OutpostHpPercentage | 70 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 1 | 0 | Default |
| 19 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_chi_vh_Normal_Green) | 1 | 0 | Boss |
| 20 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_chi_vh_big_Normal_Yellow) | 1 | 0 | Boss |
| 21 | ElapsedTime | 2500 | ファントム (e_glo_00001_general_chi_vh_Normal_Colorless) | 3 | 600 | Default |

**設計のポイント**:
- `ElapsedTime=700` で早々に count=99 の無限補充を仕込み、その後の FriendUnitDead は「追加の敵種」という構造
- `FriendUnitDead` の閾値 3/5/10/11/12/14/15 で細かくイベント追加
- 終盤 15体で Bossオーラつきの連続出現（elem 16・17 が同タイミング）

---

### A-4. veryhard_kai_00001
**合計召喚数**: 232体（summon=99含む）/ **要素数**: 20行
**特徴**: ElapsedTime は序盤のみ。FriendUnitDead4体から即 99 の無限補充。大型・Bossオーラを FriendUnitDead=10/13 で段階登場。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 150 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 3 | 350 | Default |
| 2 | ElapsedTime | 1000 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 3 | 500 | Default |
| 3 | ElapsedTime | 1025 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 3 | 500 | Default |
| 4 | ElapsedTime | 1100 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 1 | 0 | Default |
| 5 | FriendUnitDead | 4 | 蜘蛛の怪獣 (e_kai_00301_general_kai_vh_Normal_Green) | 1 | 0 | Default |
| 6 | FriendUnitDead | 4 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | **99** | 500 | Default |
| 7 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 5 | 500 | Default |
| 8 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 1 | 0 | Default |
| 9 | FriendUnitDead | 8 | 蜘蛛の怪獣 (e_kai_00301_general_kai_vh_Normal_Green) | 1 | 0 | Default |
| 10 | FriendUnitDead | 8 | 蜘蛛の怪獣 (e_kai_00301_general_kai_vh_Normal_Green) | 1 | 0 | Default |
| 11 | FriendUnitDead | 10 | ファントム (e_glo_00001_general_kai_vh_big_Normal_Red) | 1 | 0 | **Boss** |
| 12 | FriendUnitDead | 8 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 5 | 500 | Default |
| 13 | FriendUnitDead | 11 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | 1 | 0 | Default |
| 14 | FriendUnitDead | 13 | ファントム (e_glo_00001_general_kai_vh_big_Normal_Red) | 1 | 0 | **Boss** |
| 15 | FriendUnitDead | 13 | ファントム (e_glo_00001_general_kai_vh_big_Normal_Red) | 1 | 0 | Boss |
| 16 | FriendUnitDead | 13 | 蜘蛛の怪獣 (e_kai_00301_general_kai_vh_Boss_Green) | 1 | 0 | Default |
| 17 | FriendUnitDead | 13 | ファントム (e_glo_00001_general_kai_vh_big_Normal_Red) | 1 | 0 | Boss |
| 18 | FriendUnitDead | 13 | ファントム (e_glo_00001_general_kai_vh_big_Normal_Red) | 1 | 0 | Boss |
| 19 | FriendUnitDead | 13 | ファントム (e_glo_00001_general_kai_vh_Normal_Red) | **99** | 700 | Default |
| 20 | OutpostHpPercentage | 50 | 蜘蛛の怪獣 (e_kai_00301_general_kai_vh_Normal_Green) | 3 | 25 | Default |

**設計のポイント**:
- 4体倒れたら即 count=99 の無限補充開始（elem 6）
- 13体倒れた最終フェーズでは 5行が同タイミング発火（big×4体＋Bossユニット×1 + 2本目のcount=99）
- `OutpostHpPercentage=50` を独立したセーフティネットとして追加

---

### A-5. normal_chi_00001
**合計召喚数**: 264体（summon=99含む）/ **要素数**: 20行
**特徴**: ElapsedTime と FriendUnitDead を交互に使い、summon_position で位置を指定。OutpostDamage=1 で即座に99体の無限補充開始。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 250 | ゾンビ (e_chi_00101_general_Normal_Colorless) | 2 | 350 | - | Default |
| 2 | ElapsedTime | 700 | ゾンビ (e_chi_00101_general_Normal_Colorless) | 2 | 350 | - | Default |
| 3 | ElapsedTime | 1200 | ゾンビ (e_chi_00101_general_Normal_Colorless) | 1 | 0 | - | Default |
| 4 | FriendUnitDead | 3 | ゾンビ (e_chi_00101_general_Normal_Colorless) | 10 | 500 | - | Default |
| 5 | ElapsedTime | 1500 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 1 | 0 | - | Default |
| 6 | ElapsedTime | 1700 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 2 | 500 | - | Default |
| 7 | FriendUnitDead | 5 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 2 | 500 | - | Default |
| 8 | ElapsedTime | 2300 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 1 | 0 | - | Default |
| 9 | FriendUnitDead | 8 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 350 | 1.9 | Default |
| 10 | FriendUnitDead | 8 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 350 | 1.8 | Default |
| 11 | FriendUnitDead | 8 | ゾンビ (e_chi_00101_general_Normal_Colorless) | 20 | 500 | - | Default |
| 12 | ElapsedTime | 3000 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 1 | 0 | - | Default |
| 13 | FriendUnitDead | 12 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.83 | Default |
| 14 | FriendUnitDead | 12 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.86 | Default |
| 15 | FriendUnitDead | 12 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.88 | Default |
| 16 | ElapsedTime | 4500 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.83 | Default |
| 17 | ElapsedTime | 4600 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.86 | Default |
| 18 | ElapsedTime | 4700 | ゾンビ (e_chi_00101_general_Normal_Yellow) | 3 | 750 | 1.88 | Default |
| 19 | OutpostDamage | 1 | ゾンビ (e_chi_00101_general_Normal_Yellow) | **99** | 250 | 1.9 | Default |
| 20 | OutpostDamage | 1 | ゾンビ (e_chi_00101_general_Normal_Yellow) | **99** | 250 | 1.8 | Default |

**設計のポイント**:
- `FriendUnitDead=12` で同じ敵を 3行・position 違いで配置（1.83/1.86/1.88 の微妙な位置差）
- `OutpostDamage=1` で拠点に初ダメージが入ったら2本の count=99 が別位置で開始
- ElapsedTime と FriendUnitDead を交互に組み合わせ、どちらのトリガーでも敵が来る密な設計

---

## パターン B: c_キャラも出現

### B-1. event_jig1_savage_00003
**合計召喚数**: 22体 / **要素数**: 22行
**c_キャラ比率**: 12/22行（55%）
**特徴**: 主人公キャラ（c_jig）が中心の FriendUnitDead 型。メインキャラが繰り返し登場する演出重視の設計。

| elem | condition_type | condition_value | action_value | count | interval | aura | delay |
|------|---------------|----------------|--------------|-------|----------|------|-------|
| 1 | ElapsedTime | 1000 | **がらんの画眉丸** (c_jig_00001_jig1_savage_Boss_Colorless) | 1 | 0 | Default | - |
| 2 | FriendUnitDead | 1 | **がらんの画眉丸** (c_jig_00001_jig1_savage_Boss_Colorless) | 1 | 0 | Default | 2000 |
| 3 | ElapsedTime | 250 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | - |
| 4 | FriendUnitDead | 3 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 100 |
| 5 | FriendUnitDead | 4 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 300 |
| 6 | ElapsedTime | 500 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | - |
| 7 | FriendUnitDead | 6 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 50 |
| 8 | FriendUnitDead | 7 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 250 |
| 9 | FriendUnitDead | 8 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 500 |
| 10 | FriendUnitDead | 5 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 500 |
| 10 | FriendUnitDead | 15 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 1000 |
| 11 | FriendUnitDead | 10 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 500 |
| 12 | FriendUnitDead | 11 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 500 |
| 13 | FriendUnitDead | 12 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 1000 |
| 14 | FriendUnitDead | 13 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 1000 |
| 15 | FriendUnitDead | 14 | **賊王 亜左 弔兵衛** (c_jig_00401_jig1_savage_Normal_Colorless) | 1 | 0 | Default | 1000 |
| 16 | FriendUnitDead | 9 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 500 |
| 17 | FriendUnitDead | 16 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 500 |
| 18 | FriendUnitDead | 17 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 1000 |
| 19 | FriendUnitDead | 18 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 1000 |
| 20 | FriendUnitDead | 19 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 1000 |
| 21 | FriendUnitDead | 20 | 朱槿 (e_jig_00601_jig1_savage_Normal_Yellow) | 1 | 0 | Default | 1000 |

**設計のポイント**:
- c_キャラが主役。`FriendUnitDead` を 1体おきに細かく設定して「1体倒すたびに主人公が再登場」演出
- `action_delay` を 100〜2000ms に設定して登場に「ため」を作る
- 同じ elem_id=10 に FriendUnitDead=5 と FriendUnitDead=15 の2行（別の閾値を同一要素番号で管理する実例）

---

### B-2. event_you1_savage_00003
**合計召喚数**: 87体 / **要素数**: 22行
**c_キャラ比率**: 6/22行
**特徴**: `InitialSummon` でボス c_キャラを2種開幕配置。`EnterTargetKomaIndex` と `OutpostDamage` を組み合わせた多彩なトリガー構成。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | InitialSummon | 1 | **元殺し屋の新人教諭 リタ** (c_you_00001_you1_savage01_Boss_Red) | 1 | 0 | 1.65 | **Boss** |
| 2 | InitialSummon | 1 | **ハナ** (c_you_00301_you1_savage01_Boss_Green) | 1 | 0 | 1.75 | **Boss** |
| 3 | FriendUnitDead | 1 | **元殺し屋の新人教諭 リタ** (c_you_00001_you1_savage01_Boss_Red) | 1 | 0 | - | Boss |
| 4 | EnterTargetKomaIndex | 4 | **ダグ** (c_you_00201_you1_savage01_Boss_Green) | 1 | 0 | 1.55 | Boss |
| 5 | EnterTargetKomaIndex | 4 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 1 | 0 | 1.35 | Boss |
| 6 | EnterTargetKomaIndex | 4 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 1 | 0 | 2.45 | Default |
| 7 | EnterTargetKomaIndex | 4 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Green) | 1 | 0 | 2.55 | Default |
| 8 | OutpostDamage | 1 | **元殺し屋の新人教諭 リタ** (c_you_00001_you1_savage01_Boss_Red) | 1 | 0 | - | Boss |
| 9 | OutpostDamage | 1 | **ルーク** (c_you_00101_you1_savage01_Boss_Blue) | 1 | 0 | - | Boss |
| 10 | ElapsedTime | 300 | 不良系金髪イケメン (e_you_00001_you1_savage01_02_Normal_Colorless) | 12 | 1000 | - | Default |
| 11 | ElapsedTime | 800 | 不良系金髪イケメン (e_you_00001_you1_savage01_02_Normal_Colorless) | 10 | 1400 | - | Default |
| 12 | ElapsedTime | 900 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Colorless) | 10 | 1500 | - | Default |
| 13 | FriendUnitDead | 1 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 3 | 1000 | - | Default |
| 14 | FriendUnitDead | 1 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Green) | 3 | 1100 | - | Default |
| 15 | FriendUnitDead | 3 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 8 | 1500 | - | Default |
| 16 | FriendUnitDead | 3 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Green) | 8 | 1600 | - | Default |
| 17 | EnterTargetKomaIndex | 4 | 不良系金髪イケメン (e_you_00001_you1_savage01_02_Normal_Colorless) | 1 | 0 | 2.75 | Default |
| 18 | EnterTargetKomaIndex | 4 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 1 | 0 | 2.85 | Default |
| 19 | EnterTargetKomaIndex | 7 | 不良系金髪イケメン (e_you_00001_you1_savage01_Normal_Green) | 7 | 2000 | - | Default |
| 20 | EnterTargetKomaIndex | 7 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Green) | 7 | 2000 | - | Default |
| 21 | ElapsedTime | 12300 | 不良系金髪イケメン (e_you_00001_you1_savage01_02_Normal_Colorless) | 4 | 1300 | - | Default |
| 22 | ElapsedTime | 12400 | イケメンじゃない殺し屋 (e_you_00101_you1_savage01_Normal_Colorless) | 4 | 1400 | - | Default |

**設計のポイント**:
- 開幕から c_キャラ2種を Bossオーラつきで位置指定配置（elem 1・2 が InitialSummon=1）
- `EnterTargetKomaIndex=4/7` でコマ進行に連動した伏兵演出
- `OutpostDamage=1` で拠点初ダメージ時に c_キャラ2体が色違いで登場
- ElapsedTime=12300/12400 と後半にも時間トリガーを追加（長期戦対応）

---

### B-3. veryhard_glo3_00002
**合計召喚数**: 231体（summon=99含む）/ **要素数**: 23行
**c_キャラ比率**: 4/23行
**特徴**: FriendUnitDead5体で無限補充スタートと同時に c_キャラ(Bossオーラ)登場。FriendUnitDead 10/11 で追加の c_キャラが段階登場。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 400 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Colorless) | 4 | 600 | Default |
| 2 | ElapsedTime | 1200 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Colorless) | 2 | 1200 | Default |
| 3 | ElapsedTime | 2000 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 1 | 0 | Default |
| 4 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 2 | 1500 | Default |
| 5 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 1 | 0 | Default |
| 6 | FriendUnitDead | 5 | **東 日万凛** (c_sur_00201_general_ori3_vh2_Normal_Blue) | 1 | 0 | **Boss** |
| 7 | FriendUnitDead | 5 | **駿河 朱々** (c_sur_00301_general_ori3_vh2_Normal_Blue) | 1 | 0 | Boss |
| 8 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Colorless) | **99** | 800 | Default |
| 9 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | **99** | 1300 | Default |
| 10 | FriendUnitDead | 7 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 1 | 0 | Default |
| 11 | FriendUnitDead | 10 | **悪魔が恐れる悪魔 チェンソーマン** (c_chi_00002_general_ori3_vh2_Normal_Blue) | 1 | 0 | **Boss** |
| 12 | FriendUnitDead | 11 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 3 | 600 | Default |
| 13 | FriendUnitDead | 11 | **甘戸 めめ** (c_rik_00101_general_ori3_vh2_Boss_Blue) | 1 | 0 | **Boss** |
| 14 | FriendUnitDead | 11 | ファントム (e_glo_00001_general_ori3_vh2_big_Normal_Colorless) | 1 | 0 | Boss |
| 15 | FriendUnitDead | 11 | ファントム (e_glo_00001_general_ori3_vh2_big_Normal_Blue) | 1 | 0 | Boss |
| 16 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 2 | 50 | **Boss** |
| 17 | FriendUnitDead | 14 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Colorless) | 2 | 700 | Default |
| 18 | FriendUnitDead | 15 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 2 | 700 | Default |
| 19 | FriendUnitDead | 16 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 2 | 700 | Default |
| 20 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_ori3_vh2_big_Normal_Colorless) | 1 | 0 | Boss |
| 21 | OutpostHpPercentage | 50 | ファントム (e_glo_00001_general_ori3_vh2_big_Normal_Blue) | 1 | 0 | Boss |
| 22 | FriendUnitDead | 3 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 2 | 50 | Default |
| 23 | FriendUnitDead | 5 | ファントム (e_glo_00001_general_ori3_vh2_Normal_Blue) | 1 | 0 | Default |

**設計のポイント**:
- `FriendUnitDead=5` の同タイミングで c_キャラ2体＋2種の count=99 無限補充を一気に開始（elem 6/7/8/9）
- c_キャラは 5/10/11 体と3段階で別々の作品キャラが登場（クロスオーバー演出）
- 最終フェーズは FriendUnitDead=14/15/16 で毎体ごとに追加

---

### B-4. veryhard_glo4_00001
**合計召喚数**: 141体（summon=99含む）/ **要素数**: 23行
**c_キャラ比率**: 4/23行
**特徴**: FriendUnitDead=6 で c_キャラ2体同時登場。FriendUnitDead=19〜20 でラストの強力な c_キャラが登場。OutpostDamage も活用。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 1 | ファントム (e_glo_00001_general2_ori4_Normal_Colorless) | 6 | 500 | Default |
| 2 | ElapsedTime | 2100 | ファントム (e_glo_00001_general2_ori4_Normal_Colorless) | 3 | 600 | Default |
| 3 | ElapsedTime | 2200 | ファントム (e_glo_00001_general2_ori4_Normal_Colorless) | 1 | 0 | Default |
| 4 | ElapsedTime | 2300 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 3 | 800 | Default |
| 5 | FriendUnitDead | 4 | ファントム (e_glo_00001_general2_ori4_Normal_Colorless) | **99** | 700 | Default |
| 6 | FriendUnitDead | 4 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 1 | 0 | Default |
| 7 | FriendUnitDead | 4 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 2 | 35 | Default |
| 8 | FriendUnitDead | 6 | **影のウシオ 小舟 潮** (c_sum_00101_general_ori4_Normal_Green) | 1 | 0 | **Boss** |
| 9 | FriendUnitDead | 6 | **小舟 澪** (c_sum_00201_general_ori4_Normal_Green) | 1 | 0 | **Boss** |
| 10 | FriendUnitDead | 9 | ファントム (e_glo_00001_general2_ori4_Normal_Colorless) | 4 | 25 | Default |
| 11 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 2 | 50 | Default |
| 12 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 1 | 0 | Default |
| 13 | FriendUnitDead | 6 | ファントム (e_glo_00001_general2_ori4_big_Normal_Colorless) | 1 | 0 | **Boss** |
| 14 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_ori4_big_Normal_Green) | 1 | 0 | Boss |
| 15 | FriendUnitDead | 19 | ファントム (e_glo_00001_general2_ori4_big_Normal_Colorless) | 1 | 0 | Boss |
| 16 | FriendUnitDead | 20 | ファントム (e_glo_00001_general_ori4_big_Normal_Green) | 1 | 0 | Boss |
| 17 | FriendUnitDead | 8 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 3 | 50 | Default |
| 18 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 2 | 50 | Default |
| 19 | FriendUnitDead | 8 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 1 | 0 | Default |
| 20 | FriendUnitDead | 19 | **隠された英雄の姿 怪獣８号** (c_kai_00002_general_ori4_Boss_Green) | 1 | 0 | **Boss** |
| 21 | FriendUnitDead | 19 | **市川 レノ** (c_kai_00101_general_ori4_Normal_Green) | 1 | 0 | **Boss** |
| 22 | OutpostDamage | 1 | ファントム (e_glo_00001_general_ori4_Normal_Green) | 2 | 50 | Default |
| 23 | OutpostDamage | 1 | ファントム (e_glo_00001_general2_ori4_big_Normal_Colorless) | 3 | 50 | Boss |

**設計のポイント**:
- c_キャラが「序盤の強化」(elem 8/9, FriendUnitDead=6) と「終盤のフィナーレ」(elem 20/21, FriendUnitDead=19) の2段階で登場
- 序盤の c_キャラと同タイミング(FriendUnitDead=6)で big ユニットも追加（elem 13）
- `OutpostDamage=1` は拠点に初ダメージでグリーンの雑魚＋big ユニット追加（elem 22/23）

---

### B-5. veryhard_glo1_00002
**合計召喚数**: 55体 / **要素数**: 23行
**c_キャラ比率**: 4/23行（4作品クロスオーバー）
**特徴**: c_キャラが 4種類の作品から登場（gom/spy/aka）。InitialSummon + ElapsedTime + FriendUnitDead + OutpostHpPercentage を全て組み合わせた複雑構成。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 200 | **囚われの王女 姫様** (c_gom_00001_general_h_Boss_Yellow) | 1 | 0 | - | Default |
| 2 | FriendUnitDead | 1 | キュイ (e_gom_00301_general_n_Boss_Yellow) | 1 | 0 | - | Default |
| 3 | ElapsedTime | 950 | **クロル** (c_gom_00201_general_vh_Boss_Yellow) | 1 | 0 | - | Default |
| 4 | FriendUnitDead | 3 | **<いばら姫> ヨル** (c_spy_00201_general_vh_Boss_Yellow) | 1 | 0 | - | Default |
| 5 | FriendUnitDead | 3 | たこ焼きくん (e_gom_00401_general_vh_Boss_Yellow) | 1 | 0 | - | Default |
| 6 | OutpostHpPercentage | 99 | **佐々木** (c_aka_00001_general_vh_Boss_Yellow) | 1 | 0 | - | Default |
| 7 | FriendUnitDead | 1 | たこ焼き (e_gom_00402_general_vh_Normal_Yellow) | 3 | 250 | - | Default |
| 8 | InitialSummon | 1 | 密輸組織の残党 (e_spy_00001_general_vh_Normal_Yellow) | 1 | 0 | 0.6 | Default |
| 9 | InitialSummon | 1 | 密輸組織の残党 (e_spy_00001_general_vh_Normal_Yellow) | 1 | 0 | 1.3 | Default |
| 10 | InitialSummon | 1 | 密輸組織の残党 (e_spy_00001_general_vh_Normal_Yellow) | 1 | 0 | 1.5 | Default |
| 11 | ElapsedTime | 300 | あんぱん (e_gom_01001_general_vh_Normal_Yellow) | 3 | 150 | - | Default |
| 12 | ElapsedTime | 400 | トーストあんぱん (e_gom_01002_general_vh_Normal_Yellow) | 3 | 250 | - | Default |
| 13 | FriendUnitDead | 8 | あんぱん (e_gom_01001_general_n_Normal_Yellow) | 2 | 50 | 1.2 | Default |
| 14 | FriendUnitDead | 8 | トーストあんぱん (e_gom_01002_general_n_Normal_Yellow) | 3 | 50 | 1.4 | Default |
| 15 | FriendUnitDead | 10 | あんぱん (e_gom_01001_general_n_Normal_Yellow) | 3 | 150 | 1.7 | Default |
| 16 | FriendUnitDead | 10 | トーストあんぱん (e_gom_01002_general_n_Normal_Yellow) | 4 | 150 | 1.9 | Default |
| 17 | OutpostHpPercentage | 99 | たこ焼きくん (e_gom_00401_general_vh_Boss_Yellow) | 1 | 0 | 1.4 | Default |
| 18 | OutpostHpPercentage | 99 | たこ焼き (e_gom_00402_general_vh_Normal_Yellow) | 4 | 50 | - | Default |
| 19 | OutpostHpPercentage | 40 | たこ焼き (e_gom_00402_general_vh_Normal_Yellow) | 4 | 50 | - | Default |
| 20 | ElapsedTime | 3000 | あんぱん (e_gom_01001_general_n_Normal_Yellow) | 4 | 100 | - | Default |
| 21 | ElapsedTime | 3050 | トーストあんぱん (e_gom_01002_general_n_Normal_Yellow) | 4 | 100 | - | Default |
| 22 | FriendUnitDead | 1 | たこ焼き (e_gom_00402_general_vh_Normal_Yellow) | 5 | 450 | - | Default |
| 23 | FriendUnitDead | 4 | 密輸組織の残党 (e_spy_00001_general_vh_Normal_Yellow) | 3 | 50 | 2.5 | Default |

**設計のポイント**:
- c_キャラが ElapsedTime(200ms, 950ms) と FriendUnitDead(3体) と OutpostHpPercentage(99%) の3トリガーで登場
- 4作品（gom/spy/aka/gom2）のキャラが交互に登場するクロスオーバー設計
- `InitialSummon` で e_spy を3体位置指定して開幕配置しつつ、c_gom が ElapsedTime=200ms で早期登場

---

---

## パターン N: Normalクエスト normal難易度

### N-1. normal_mag_00005
**合計召喚数**: 432体（summon=99×4含む）/ **要素数**: 14行 / **c_キャラ**: なし
**特徴**: ElapsedTime で序盤を進め、FriendUnitDead=13体で Boss ユニット登場。14体で3色99無限補充を一斉スタート。OutpostDamage=1 もバックアップとして追加。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 150 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 2 | 50 | - | Default |
| 2 | ElapsedTime | 1000 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 1 | 0 | - | Default |
| 3 | ElapsedTime | 1050 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 1 | 0 | - | Default |
| 8 | ElapsedTime | 2000 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 1 | 0 | - | Default |
| 13 | ElapsedTime | 3000 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Red) | 1 | 0 | - | Default |
| 14 | FriendUnitDead | 13 | 建造物寄生型の怪異 (大) (e_mag_00201_general_Boss_Red) | 1 | 0 | - | Default |
| 15 | FriendUnitDead | 13 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 1 | 0 | 2.8 | Default |
| 16 | FriendUnitDead | 13 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | 1 | 0 | 2.9 | Default |
| 17 | FriendUnitDead | 15 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Blue) | 10 | 500 | 2.9 | Default |
| 18 | FriendUnitDead | 16 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Red) | 5 | 750 | 2.9 | Default |
| 19 | FriendUnitDead | 14 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Colorless) | **99** | 500 | - | Default |
| 20 | FriendUnitDead | 14 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Blue) | **99** | 750 | - | Default |
| 21 | FriendUnitDead | 14 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Red) | **99** | 1200 | - | Default |
| 22 | OutpostDamage | 1 | 建造物寄生型の怪異 (e_mag_00301_general_Normal_Red) | **99** | 750 | - | Default |

**設計のポイント**:
- `FriendUnitDead=13` で Bossユニット登場＋位置指定2体配置（elem 14/15/16 同タイミング）
- `FriendUnitDead=14` で3色99無限補充を一斉スタート（間隔500/750/1200ms でずらして密度を演出）
- `OutpostDamage=1` を独立したバックアップとして追加（elem 22）

---

### N-2. normal_sur_00003
**合計召喚数**: 275体（summon=99×2含む）/ **要素数**: 19行 / **c_キャラ**: なし
**特徴**: ElapsedTime と FriendUnitDead を交互に組み合わせ、後半は time=2500〜5700ms の幅広い時間帯に大量召喚。OutpostDamage=1 で2本の99無限補充。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 150 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 3 | 300 | - | Default |
| 2 | ElapsedTime | 1000 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 2 | 50 | - | Default |
| 3 | ElapsedTime | 1500 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 3 | 50 | - | Default |
| 4 | FriendUnitDead | 3 | 醜鬼 (e_sur_00101_general_Normal_Colorless) | 2 | 50 | - | Default |
| 5 | FriendUnitDead | 3 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 1 | 0 | - | Default |
| 6 | ElapsedTime | 2800 | 醜鬼 (e_sur_00101_general_Normal_Colorless) | 1 | 0 | - | Default |
| 7 | ElapsedTime | 2700 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 1 | 0 | - | Default |
| 8 | FriendUnitDead | 6 | 醜鬼 (e_sur_00101_general_Normal_Colorless) | 3 | 100 | - | Default |
| 9 | FriendUnitDead | 7 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 3 | 100 | - | Default |
| 10 | ElapsedTime | 3200 | 醜鬼 (e_sur_00101_general_Normal_Colorless) | 3 | 100 | 1.3 | Default |
| 11 | ElapsedTime | 3600 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 10 | 500 | - | Default |
| 12 | ElapsedTime | 3000 | 醜鬼 (e_sur_00101_general_Normal_Green) | 10 | 600 | - | Default |
| 13 | ElapsedTime | 4500 | 醜鬼 (e_sur_00101_general_Normal_Colorless) | 3 | 100 | 1.3 | Default |
| 14 | ElapsedTime | 4000 | 醜鬼 (e_sur_00101_general_Normal_Green) | 10 | 800 | - | Default |
| 15 | ElapsedTime | 5500 | 醜鬼 (e_sur_00101_general_Normal_Green) | 10 | 1200 | - | Default |
| 16 | ElapsedTime | 2500 | 醜鬼 (e_sur_00101_general_Normal_Green) | 10 | 1200 | - | Default |
| 17 | ElapsedTime | 5700 | 醜鬼 (e_sur_00101_general_Normal_Blue) | 2 | 50 | 1.3 | Default |
| 18 | OutpostDamage | 1 | 醜鬼 (e_sur_00101_general_Normal_Green) | **99** | 750 | - | Default |
| 19 | OutpostDamage | 1 | 醜鬼 (e_sur_00101_general_Normal_Green) | **99** | 1200 | - | Default |

**設計のポイント**:
- 2500〜5700ms の広い時間帯に count=10 の集中ウェーブを複数配置（elem 11/12/13/14/15/16）
- `FriendUnitDead` は3/6/7体と低め設定で序中盤に色変え追加
- `OutpostDamage=1` で2本の99補充（750ms/1200ms でずらして間隔に変化）

---

### N-3. normal_osh_00002
**合計召喚数**: 41体 / **要素数**: 19行 / **c_キャラ**: 1行（EnterTargetKomaIndex）
**特徴**: `InitialSummon=0` で9体を全て位置指定して開幕盤面を構築。コマ0到達で c_osh がボスとして登場。その後は FriendUnitDead 1体ごとに位置指定で追加。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 1 | 0 | 0.5 | Default |
| 2 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 1 | 0 | 0.8 | Default |
| 3 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 1.2 | Default |
| 4 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 1.3 | Default |
| 5 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 1.7 | Default |
| 6 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 1.8 | Default |
| 7 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 2.1 | Default |
| 8 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 2.3 | Default |
| 9 | InitialSummon | 0 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 1 | 0 | 2.7 | Default |
| 10 | EnterTargetKomaIndex | 0 | **B小町不動のセンター アイ** (c_osh_00001_general_osh_n_Boss_Colorless) | 1 | 0 | 1.5 | Default |
| 11 | FriendUnitDead | 1 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 5 | 500 | - | Default |
| 12 | FriendUnitDead | 2 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 3 | 250 | - | Default |
| 13 | FriendUnitDead | 3 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 5 | 500 | - | Default |
| 14 | FriendUnitDead | 4 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 3 | 250 | - | Default |
| 15 | FriendUnitDead | 5 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 3 | 250 | 1.7 | Default |
| 16 | FriendUnitDead | 6 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Green) | 3 | 250 | 1.8 | Default |
| 17 | FriendUnitDead | 7 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 3 | 250 | 2.1 | Default |
| 18 | FriendUnitDead | 8 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 3 | 250 | 2.3 | Default |
| 19 | FriendUnitDead | 9 | 推し活ファントム (e_glo_00002_general_osh_n_Normal_Colorless) | 3 | 250 | 2.7 | Default |

**設計のポイント**:
- `InitialSummon=0` で9行・9つの異なる position を指定 → 盤面を隙間なく埋める開幕演出
- `EnterTargetKomaIndex=0` （コマ0＝最初のコマ）到達で c_キャラがボスとして出迎える
- `FriendUnitDead` は 1/2/3/4/5/6/7/8/9 体と毎体ごとに追加（初期配置を倒しながら補充される構造）
- `FriendUnitDead` の position が InitialSummon の配置位置と対応（補充先が同じ場所）

---

### N-4. normal_osh_00003
**合計召喚数**: 69体（count=15×2含む）/ **要素数**: 16行 / **c_キャラ**: 2行
**特徴**: ElapsedTime=250ms で c_キャラが即登場。`EnterTargetKomaIndex` でコマ進行ごとに伏兵配置。`FriendUnitDead=9` で15体の大波を一度に出す。

| elem | condition_type | condition_value | action_value | count | interval | position | aura | delay |
|------|---------------|----------------|--------------|-------|----------|----------|------|-------|
| 1 | ElapsedTime | 250 | **B小町不動のセンター アイ** (c_osh_00001_general_osh_n_Boss_Green) | 1 | 0 | - | Default | - |
| 2 | EnterTargetKomaIndex | 0 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 2 | 100 | 1.1 | Default | 100 |
| 3 | EnterTargetKomaIndex | 1 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 150 | 1.5 | Default | - |
| 4 | EnterTargetKomaIndex | 2 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 2 | 250 | 3.3 | Default | - |
| 5 | EnterTargetKomaIndex | 3 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 50 | 3.5 | Default | - |
| 6 | EnterTargetKomaIndex | 4 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 500 | 3.7 | Default | - |
| 7 | EnterTargetKomaIndex | 5 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 5 | 150 | - | Default | - |
| 8 | EnterTargetKomaIndex | 5 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 50 | 0.8 | Default | - |
| 9 | FriendUnitDead | 1 | **B小町不動のセンター アイ** (c_osh_00001_general_osh_n_Normal_Colorless) | 1 | 0 | - | Default | 4500 |
| 10 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | **15** | 50 | 0.3 | Default | 50 |
| 11 | FriendUnitDead | 1 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 4 | 250 | - | Default | 500 |
| 12 | FriendUnitDead | 1 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 4 | 50 | - | Default | - |
| 13 | FriendUnitDead | 1 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 2 | 100 | 2.8 | Default | - |
| 14 | FriendUnitDead | 1 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 50 | - | Default | 2000 |
| 15 | FriendUnitDead | 9 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | **15** | 50 | - | Default | 50 |
| 16 | ElapsedTime | 4000 | ファントム (e_glo_00001_general_osh_n_Normal_Green) | 3 | 500 | - | Default | - |

**設計のポイント**:
- `EnterTargetKomaIndex=0〜5` でコマ進行に合わせた伏兵を順次配置（コマ0〜5全てに設定）
- `FriendUnitDead=1` の同タイミングに4行 → 1体倒れると計13体が一度に追加（delay=0/500/2000ms でずらし）
- `FriendUnitDead=9` で count=15 の大波が position 違いで2本（elem 10/15）
- c_キャラの2体目（elem 9）は `action_delay=4500ms` と長めの遅延で「間を置いてから再登場」演出

---

### N-5. normal_rik_00002
**合計召喚数**: 415体（summon=99×4含む）/ **要素数**: 16行 / **c_キャラ**: 16/16行（全て c_キャラ）
**特徴**: 全要素が `c_rik_00001` で構成、全て Bossオーラ付き。ElapsedTime で定期召喚 → FriendUnitDead でも追加 → 後半は ElapsedTime=5000〜5500ms と OutpostDamage=1 で 99×4本が同時稼働。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 1 | ElapsedTime | 200 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | - | **Boss** |
| 2 | ElapsedTime | 800 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | - | Boss |
| 3 | ElapsedTime | 1400 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | - | Boss |
| 4 | FriendUnitDead | 2 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 2 | 50 | - | Boss |
| 5 | ElapsedTime | 1700 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | - | Boss |
| 6 | FriendUnitDead | 5 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | - | Boss |
| 7 | FriendUnitDead | 5 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | 2.9 | Boss |
| 8 | FriendUnitDead | 5 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 1 | 0 | 2.8 | Boss |
| 9 | FriendUnitDead | 6 | **リコピン** (c_rik_00001_general_Boss_Red) | 1 | 0 | - | Boss |
| 10 | FriendUnitDead | 9 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 3 | 25 | 2.84 | Boss |
| 11 | FriendUnitDead | 9 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 3 | 50 | 2.86 | Boss |
| 12 | FriendUnitDead | 9 | **リコピン** (c_rik_00001_general_Normal_Colorless) | 3 | 75 | 2.88 | Boss |
| 13 | ElapsedTime | 5000 | **リコピン** (c_rik_00001_general_Normal_Colorless) | **99** | 1000 | 2.88 | Boss |
| 14 | ElapsedTime | 5500 | **リコピン** (c_rik_00001_general_Normal_Colorless) | **99** | 1000 | 2.9 | Boss |
| 15 | OutpostDamage | 1 | **リコピン** (c_rik_00001_general_Normal_Colorless) | **99** | 750 | - | Boss |
| 16 | OutpostDamage | 1 | **リコピン** (c_rik_00001_general_Normal_Colorless) | **99** | 750 | - | Boss |

**設計のポイント**:
- 全要素が同一の c_キャラで Bossオーラ付き → 「主人公が大量に押し寄せる」という世界観演出
- `FriendUnitDead=9` で 3体×3行・position 2.84/2.86/2.88 の微差配置（密集ウェーブ）
- `FriendUnitDead=5` で3行同タイミング（位置なし/2.9/2.8 の3パターン同時）
- 終盤は ElapsedTime(5000ms/5500ms) と OutpostDamage(=1) の計4本が99補充として同時稼働

---

## まとめ：設計ポイント早見表

| パターン | 合計体数 | c_キャラ | 主なトリガー | 特徴キーワード |
|---------|---------|---------|------------|--------------|
| A-1 event_mag1_savage_00002 | 63 | なし | InitialSummon+ElapsedTime+OutpostHpPercentage+FriendUnitDead | 位置指定開幕配置、色違いボス |
| A-2 veryhard_glo3_00003 | 131 | なし | ElapsedTime+FriendUnitDead+OutpostDamage+OutpostHpPercentage | big多用、FriendUnitDead5で無限補充+Bossオーラ同時展開 |
| A-3 veryhard_chi_00002 | 143 | なし | ElapsedTime+FriendUnitDead+OutpostHpPercentage | 開幕700msで無限補充スタート |
| A-4 veryhard_kai_00001 | 232 | なし | ElapsedTime+FriendUnitDead+OutpostHpPercentage | 4体で99無限補充、13体で5行同時展開 |
| A-5 normal_chi_00001 | 264 | なし | ElapsedTime+FriendUnitDead+OutpostDamage | position微調整、OutpostDamage=1で99×2本 |
| B-1 event_jig1_savage_00003 | 22 | 55% | ElapsedTime+FriendUnitDead | c_キャラ主役、1体ごとに再登場 |
| B-2 event_you1_savage_00003 | 87 | 27% | InitialSummon+EnterTargetKomaIndex+OutpostDamage+FriendUnitDead | 開幕Bossオーラ配置、コマ進行連動 |
| B-3 veryhard_glo3_00002 | 231 | 17% | ElapsedTime+FriendUnitDead+OutpostHpPercentage | 5体でc_キャラ+99×2同時展開 |
| B-4 veryhard_glo4_00001 | 141 | 17% | ElapsedTime+FriendUnitDead+OutpostDamage | c_キャラが序盤と終盤の2段階登場 |
| B-5 veryhard_glo1_00002 | 55 | 17% | InitialSummon+ElapsedTime+FriendUnitDead+OutpostHpPercentage | 4作品クロスオーバー、全トリガー混在 |
| N-1 normal_mag_00005 | 432 | なし | ElapsedTime+FriendUnitDead+OutpostDamage | 14体で3色99×3一斉スタート |
| N-2 normal_sur_00003 | 275 | なし | ElapsedTime+FriendUnitDead+OutpostDamage | 2500〜5700msに10体×複数波 |
| N-3 normal_osh_00002 | 41 | 5% | InitialSummon+EnterTargetKomaIndex+FriendUnitDead | 9体位置指定開幕、1体ごと補充 |
| N-4 normal_osh_00003 | 69 | 13% | ElapsedTime+EnterTargetKomaIndex+FriendUnitDead | コマ0〜5全連動、9体で15体大波 |
| N-5 normal_rik_00002 | 415 | 100% | ElapsedTime+FriendUnitDead+OutpostDamage | 全c_キャラ全Bossオーラ、99×4本同時 |
