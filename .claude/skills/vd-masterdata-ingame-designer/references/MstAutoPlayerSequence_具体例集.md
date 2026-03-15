# MstAutoPlayerSequence 具体例集

Normalクエスト normal難易度の過去データから実例を抜粋。
設計書作成時の条件値・構成の参考として使用してください。

- [N-1〜N-5: 召喚数多め（41〜432体）](#パターン-n-normalクエスト-normal難易度)
- [N-6〜N-10: 召喚数少なめ（10〜19体）](#n-6-normal_gom_00001)
- [N-11〜N-15: 召喚数少なめ追加（6〜36体）](#n-11-normal_dan_00001)
- [まとめ早見表](#まとめ設計ポイント早見表)

> **読み方**: 各テーブルは `sequence_element_id` 昇順。同じ elem_id の行は同タイミングで発火（位置・キャラ違いの並列召喚）。

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

### N-6. normal_gom_00001
**合計召喚数**: 10体 / **要素数**: 2行 / **c_キャラ**: なし
**特徴**: ElapsedTime のみ2行という最もシンプルな構成。2種類のトースト系キャラを異なるタイミングで5体ずつ召喚。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 250 | 割きトースト (e_gom_00502_general_n_Normal_Colorless) | 5 | 300 | Default |
| 2 | ElapsedTime | 800 | バタートースト (e_gom_00501_general_n_Normal_Colorless) | 5 | 25 | Default |

**設計のポイント**:
- 要素2行・トリガー1種類だけの最小構成
- 2種の敵を 250ms と 800ms の時間差で出し分け

---

### N-7. normal_spy_00003
**合計召喚数**: 12体 / **要素数**: 3行 / **c_キャラ**: なし
**特徴**: `InitialSummon` で1体を位置指定して開幕配置 → `ElapsedTime` で10体ラッシュ → `FriendUnitDead=2` でボスユニット追加という3段構成。

| elem | condition_type | condition_value | action_value | count | interval | position | aura | delay |
|------|---------------|----------------|--------------|-------|----------|----------|------|-------|
| 1 | ElapsedTime | 500 | 密輸組織の残党 (e_spy_00001_general_n_Normal_Colorless) | 10 | 1250 | - | Default | - |
| 2 | InitialSummon | 1 | 密輸組織の残党 (e_spy_00001_general_n_Normal_Colorless) | 1 | 0 | 1.6 | Default | - |
| 3 | FriendUnitDead | 2 | 密輸組織の残党 (e_spy_00001_general_n_Boss_Blue) | 1 | 0 | - | Default | 50 |

**設計のポイント**:
- `InitialSummon=1` で1体を position=1.6 に配置してから ElapsedTime=500ms で10体ラッシュ
- 2体倒されると同じキャラの Blue ボスバージョンが delay=50ms で登場（色違い強化演出）
- 3行で3トリガーを使う最小複合構成

---

### N-8. normal_glo4_00002
**合計召喚数**: 11体 / **要素数**: 11行 / **c_キャラ**: 5/11行（45%）
**特徴**: `Fall` アニメーションつきで敵が落下登場。FriendUnitDead=2/3 の同タイミングに c_キャラと e_キャラを複数行同時展開する密な構成。

| elem | condition_type | condition_value | action_value | count | interval | position | aura | anim |
|------|---------------|----------------|--------------|-------|----------|----------|------|------|
| 1 | ElapsedTime | 350 | **日比野 カフカ** (c_kai_00001_general_as4_Normal_Blue) | 1 | 0 | - | **Boss** | None |
| 2 | ElapsedTime | 375 | **網代 慎平** (c_sum_00001_general_as4_Normal_Blue) | 1 | 0 | - | **Boss** | None |
| 3 | FriendUnitDead | 2 | **四ノ宮 キコル** (c_kai_00301_general_as4_Normal_Blue) | 1 | 0 | 2.85 | **Boss** | **Fall** |
| 4 | FriendUnitDead | 2 | つらら (e_mag_00101_general_as4_Normal_Blue) | 1 | 0 | 2.9 | Default | **Fall** |
| 5 | FriendUnitDead | 2 | つらら (e_mag_00101_general_as4_Normal_Colorless) | 1 | 0 | 2.8 | Default | **Fall** |
| 6 | FriendUnitDead | 2 | つらら (e_mag_00101_general_as4_Normal_Colorless) | 1 | 0 | 2.75 | Default | **Fall** |
| 7 | FriendUnitDead | 3 | つらら (e_mag_00101_general_as4_Normal_Blue) | 1 | 0 | 2.9 | Default | **Fall** |
| 8 | FriendUnitDead | 3 | つらら (e_mag_00101_general_as4_Normal_Colorless) | 1 | 0 | 2.8 | Default | **Fall** |
| 9 | FriendUnitDead | 3 | つらら (e_mag_00101_general_as4_Normal_Colorless) | 1 | 0 | 2.75 | Default | **Fall** |
| 10 | FriendUnitDead | 3 | **越谷 仁美** (c_mag_00101_general_as4_Normal_Blue) | 1 | 0 | - | **Boss** | None |
| 11 | FriendUnitDead | 3 | **新人魔法少女 桜木 カナ** (c_mag_00001_general_as4_Boss_Blue) | 1 | 0 | - | Default | None |

**設計のポイント**:
- `FriendUnitDead=2` で c_キャラ(Boss)+e_キャラ×3体が Fall アニメで同時落下（elem 3〜6 の4行同タイミング）
- `FriendUnitDead=3` でも同様に5行同タイミング（e_キャラ×3 + c_キャラ×2）
- e_キャラ「つらら」は position 2.75/2.8/2.9 の微差配置で1体ずつ落下演出
- 合計11体でも11行という「1体ずつ丁寧に管理する」設計スタイル

---

### N-9. normal_dan_00006
**合計召喚数**: 14体 / **要素数**: 8行 / **c_キャラ**: 3行
**特徴**: `FriendUnitTransform=1`（フレンドユニット変身）というレアなトリガーを使用。変身後に敵が強化されるストーリー演出。

| elem | condition_type | condition_value | action_value | count | interval | aura | delay |
|------|---------------|----------------|--------------|-------|----------|------|-------|
| 1 | InitialSummon | 1 | セルポ星人 (e_dan_00001_general_n_trans_Normal_Red) | 1 | 0 | Default | - |
| 2 | ElapsedTime | 0 | **モモ** (c_dan_00101_general_n_Boss_Red) | 1 | 0 | Default | - |
| 3 | ElapsedTime | 750 | **オカルン** (c_dan_00001_general_n_Normal_Red) | 1 | 0 | **Boss** | - |
| 4 | ElapsedTime | 850 | ターボババア (e_dan_00201_general_n_Boss_Red) | 1 | 0 | Default | - |
| 5 | FriendUnitDead | 3 | **ターボババアの霊力 オカルン** (c_dan_00002_general_n_Boss_Red) | 1 | 0 | Default | 50 |
| 6 | FriendUnitTransform | 1 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Red) | 3 | 150 | Default | - |
| 7 | FriendUnitTransform | 1 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Red) | 3 | 300 | Default | 50 |
| 8 | FriendUnitTransform | 1 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Red) | 3 | 650 | Default | 100 |

**設計のポイント**:
- `FriendUnitTransform=1` でフレンドが変身した瞬間に敵3行（計9体）が間隔とdelayをずらして大量登場
- elem 6/7/8 は同じキャラを interval(150/300/650ms) と delay(0/50/100ms) で微妙にずらし「群れで迫ってくる」演出
- `InitialSummon=1` + `ElapsedTime=0ms` で「ゲーム開始と同時に」c_キャラとe_キャラが一斉に開幕配置

---

### N-10. normal_glo1_00001
**合計召喚数**: 19体 / **要素数**: 10行 / **c_キャラ**: 4/10行（40%）
**特徴**: 350〜1450ms の短時間帯に4種の作品キャラが次々と ElapsedTime で登場。FriendUnitDead は 1/2/3 体と低めで食べ物系キャラが追加されるユニークな設計。

| elem | condition_type | condition_value | action_value | count | interval | aura | delay |
|------|---------------|----------------|--------------|-------|----------|------|-------|
| 1 | ElapsedTime | 350 | **フランキー・フランクリン** (c_spy_00401_general_n_Boss_Colorless) | 1 | 0 | Default | - |
| 2 | ElapsedTime | 450 | グエン (e_spy_00101_general_n_Normal_Colorless) | 1 | 0 | Default | - |
| 3 | ElapsedTime | 1350 | **文蔵** (c_aka_00101_general_n_Boss_Red) | 1 | 0 | Default | - |
| 4 | FriendUnitDead | 2 | ラーメン (e_gom_00701_general_n_Boss_Colorless) | 1 | 0 | Default | 200 |
| 5 | FriendUnitDead | 2 | ライス (e_gom_00801_general_n_Normal_Colorless) | 3 | 750 | Default | 300 |
| 6 | FriendUnitDead | 2 | ライス (海苔) (e_gom_00901_general_n_Normal_Colorless) | 3 | 800 | Default | 350 |
| 7 | FriendUnitDead | 3 | ライス (海苔) (e_gom_00901_general_n_Normal_Colorless) | 2 | 400 | Default | - |
| 8 | FriendUnitDead | 1 | ライス (e_gom_00801_general_n_Normal_Colorless) | 5 | 1000 | Default | - |
| 9 | ElapsedTime | 1450 | **<黄昏> ロイド** (c_spy_00101_general_n_Boss_Red) | 1 | 0 | Default | - |
| 10 | FriendUnitDead | 2 | **トーチャー・トルチュール** (c_gom_00101_general_n_Boss_Red) | 1 | 0 | Default | 50 |

**設計のポイント**:
- 350ms → 450ms → 1350ms → 1450ms と1100ms内に4キャラが続けて登場する高密度開幕
- `FriendUnitDead=2` の同タイミングに4行（delay=200/300/350ms のずれ）：ラーメン1体→ライス3体→ライス(海苔)3体と順次追加
- `FriendUnitDead=1` で5体のライスを先行追加（elem 8）、複数の FriendUnitDead 閾値が交差する複雑な発火順

---

### N-11. normal_dan_00001
**合計召喚数**: 6体 / **要素数**: 2行 / **c_キャラ**: なし
**特徴**: `DarknessKomaCleared=2`（闇コマを2個クリアしたら）というレアトリガーでボスキャラを召喚。通常のElapsedTimeと組み合わせた2行構成。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 500 | ファントム (e_glo_00001_general_n_Normal_Colorless) | 5 | 1200 | Default |
| 2 | DarknessKomaCleared | 2 | ターボババア (e_dan_00201_general_n_Boss_Colorless) | 1 | 0 | Default |

**設計のポイント**:
- `DarknessKomaCleared=2` でプレイヤーが闇コマを2個クリアした時点でボス敵が追加召喚される「難易度自動調整」的な設計
- ElapsedTime=500ms の雑魚5体と独立して発火する非同期2トリガー構成

---

### N-12. normal_glo4_00001
**合計召喚数**: 6体 / **要素数**: 6行 / **c_キャラ**: 4/6行（67%）
**特徴**: 6体を6行で個別管理。`InitialSummon=2` で開幕配置し、`FriendUnitDead` 1/2/3体ごとに1行ずつ丁寧に展開。FriendUnitDead=3 では `Fall` アニメで2体同時落下。

| elem | condition_type | condition_value | action_value | count | interval | position | aura | anim |
|------|---------------|----------------|--------------|-------|----------|----------|------|------|
| 1 | InitialSummon | 2 | 小舟 澪の影 (包丁) (e_sum_00201_general_as4_Normal_Green) | 1 | 0 | 2.6 | Default | None |
| 2 | FriendUnitDead | 1 | 小舟 澪の影 (拳銃) (e_sum_00101_general_as4_Normal_Green) | 1 | 0 | - | Default | None |
| 3 | FriendUnitDead | 2 | **隠された英雄の姿 怪獣８号** (c_kai_00002_general_as4_Normal_Green) | 1 | 0 | - | **Boss** | None |
| 4 | FriendUnitDead | 2 | **市川 レノ** (c_kai_00101_general_as4_Normal_Green) | 1 | 0 | - | **Boss** | None |
| 5 | FriendUnitDead | 3 | **影のウシオ 小舟 潮** (c_sum_00101_general_as4_Boss_Green) | 1 | 0 | 2.9 | Default | **Fall** |
| 6 | FriendUnitDead | 3 | **小舟 澪** (c_sum_00201_general_as4_Normal_Green) | 1 | 0 | 2.8 | **Boss** | **Fall** |

**設計のポイント**:
- FriendUnitDead=1/2/3 と毎体倒すたびに新しい展開（1体→1体→2体同時→2体同時）
- `FriendUnitDead=2` で c_キャラ（怪獣8号系）2体同時 Bossオーラ登場
- `FriendUnitDead=3` で Fall アニメつき c_キャラ2体が position=2.9/2.8 に落下
- 6体しかいないのに6行・3トリガー(InitialSummon+FriendUnitDead2種)という「質重視」の設計

---

### N-13. normal_glo1_00003
**合計召喚数**: 12体 / **要素数**: 6行 / **c_キャラ**: なし
**特徴**: FriendUnitDead を一切使わず、`InitialSummon` と `ElapsedTime` のみで構成。難易度違い（vh/h）の同一キャラを時系列で切り替える設計。

| elem | condition_type | condition_value | action_value | count | interval | position | aura |
|------|---------------|----------------|--------------|-------|----------|----------|------|
| 4 | InitialSummon | 0 | ファントム (e_glo_00001_general_h_Normal_Blue) | 1 | 0 | 1.2 | Default |
| 5 | InitialSummon | 0 | ファントム (e_glo_00001_general_h_Normal_Blue) | 1 | 0 | 1.8 | Default |
| 1 | ElapsedTime | 300 | ボスファントム (e_glo_00101_general_n_Boss_Blue) | 1 | 0 | - | Default |
| 2 | ElapsedTime | 750 | ファントム (e_glo_00001_general_vh_Normal_Colorless) | 2 | 50 | - | Default |
| 3 | ElapsedTime | 1000 | ファントム (e_glo_00001_general_h_Normal_Blue) | 4 | 500 | - | Default |
| 6 | ElapsedTime | 3200 | ファントム (e_glo_00001_general_vh_Normal_Colorless) | 3 | 500 | - | Default |

**設計のポイント**:
- `InitialSummon=0` で h(ハード)難易度ファントム2体を position 指定して開幕配置
- ElapsedTime=300ms で n(ノーマル)ボスファントム → 750ms で vh(ベリーハード)→ 1000ms で h → 3200ms で再び vh と難易度を切り替えながら展開
- `FriendUnitDead` なし：倒した数に関係なく時間だけで全てが決まるシンプルな設計

---

### N-14. normal_dan_00004
**合計召喚数**: 12体 / **要素数**: 7行 / **c_キャラ**: 1/7行
**特徴**: ElapsedTime=400ms で開幕即 c_キャラ登場。その後は同一 e_キャラ（セルポ星人変身）を ElapsedTime と FriendUnitDead の両方で継続補充するシンプルな反復設計。

| elem | condition_type | condition_value | action_value | count | interval | aura |
|------|---------------|----------------|--------------|-------|----------|------|
| 1 | ElapsedTime | 400 | **ターボババアの霊力 オカルン** (c_dan_00002_general_n_Boss_Red) | 1 | 0 | Default |
| 2 | ElapsedTime | 1000 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 1 | 0 | Default |
| 3 | ElapsedTime | 1700 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 1 | 0 | Default |
| 4 | ElapsedTime | 2050 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 1 | 0 | Default |
| 5 | FriendUnitDead | 2 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 2 | 200 | Default |
| 6 | FriendUnitDead | 3 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 3 | 550 | Default |
| 7 | ElapsedTime | 2000 | セルポ星人 (変身) (e_dan_00101_general_n_Normal_Colorless) | 3 | 1000 | Default |

**設計のポイント**:
- `ElapsedTime=400ms` で c_キャラが開幕即登場（InitialSummon より少し遅い「開幕直後」演出）
- 同一の e_キャラを ElapsedTime（1000/1700/2000/2050ms）と FriendUnitDead（2/3体）の両方のトリガーで補充
- elem 7（ElapsedTime=2000ms・3体）と elem 4（ElapsedTime=2050ms・1体）は50ms差でほぼ同タイミング発火

---

### N-15. normal_glo2_00002
**合計召喚数**: 36体 / **要素数**: 7行 / **c_キャラ**: 6/7行（86%）
**特徴**: 3作品（jig/dan/tak）から6種の c_キャラが ElapsedTime で 450〜2500ms に1体ずつ順番に登場。背景の大量ファントムが ElapsedTime=400ms で先行展開。

| elem | condition_type | condition_value | action_value | count | interval | aura | delay |
|------|---------------|----------------|--------------|-------|----------|------|-------|
| 7 | ElapsedTime | 400 | ファントム (e_glo_00001_general_Normal_Colorless) | 30 | 750 | Default | - |
| 1 | ElapsedTime | 450 | **山田浅ェ門 佐切** (c_jig_00101_mainquest_glo2_Normal_Red) | 1 | 0 | **Boss** | - |
| 2 | ElapsedTime | 1300 | **オカルン** (c_dan_00001_mainquest_glo2_Normal_Red) | 1 | 0 | **Boss** | - |
| 6 | ElapsedTime | 1600 | **ハッピー星からの使者 タコピー** (c_tak_00001_mainquest_glo2_Normal_Red) | 1 | 0 | **Boss** | - |
| 3 | ElapsedTime | 1800 | **モモ** (c_dan_00101_mainquest_glo2_Normal_Red) | 1 | 0 | **Boss** | - |
| 4 | ElapsedTime | 2500 | **がらんの画眉丸** (c_jig_00001_mainquest_glo2_Normal_Red) | 1 | 0 | **Boss** | - |
| 5 | FriendUnitDead | 2 | **ターボババアの霊力 オカルン** (c_dan_00002_mainquest_glo2_Boss_Red) | 1 | 0 | Default | 200 |

**設計のポイント**:
- e_ファントム30体（interval=750ms）が背景で流れ続ける中、c_キャラが次々と個別登場する「ボス連続出現」演出
- c_キャラ6体全て Bossオーラつき・1体ずつ ElapsedTime で時間差登場（450→1300→1600→1800→2500ms）
- 3作品（ジグザグ→ダンダダン→タコピー→ダンダダン→ジグザグ→ダンダダン）がクロスオーバー

---

## まとめ：設計ポイント早見表

| パターン | 合計体数 | c_キャラ | 主なトリガー | 特徴キーワード |
|---------|---------|---------|------------|--------------|
| N-1 normal_mag_00005 | 432 | なし | ElapsedTime+FriendUnitDead+OutpostDamage | 14体で3色99×3一斉スタート |
| N-2 normal_sur_00003 | 275 | なし | ElapsedTime+FriendUnitDead+OutpostDamage | 2500〜5700msに10体×複数波 |
| N-3 normal_osh_00002 | 41 | 5% | InitialSummon+EnterTargetKomaIndex+FriendUnitDead | 9体位置指定開幕、1体ごと補充 |
| N-4 normal_osh_00003 | 69 | 13% | ElapsedTime+EnterTargetKomaIndex+FriendUnitDead | コマ0〜5全連動、9体で15体大波 |
| N-5 normal_rik_00002 | 415 | 100% | ElapsedTime+FriendUnitDead+OutpostDamage | 全c_キャラ全Bossオーラ、99×4本同時 |
| N-6 normal_gom_00001 | 10 | なし | ElapsedTime | 2行最小構成、2種キャラを時間差で5体ずつ |
| N-7 normal_spy_00003 | 12 | なし | InitialSummon+ElapsedTime+FriendUnitDead | 3行3トリガー、色違いボス登場 |
| N-8 normal_glo4_00002 | 11 | 45% | ElapsedTime+FriendUnitDead | Fall落下アニメ、同タイミング多行展開 |
| N-9 normal_dan_00006 | 14 | 38% | InitialSummon+ElapsedTime+FriendUnitDead+FriendUnitTransform | 変身トリガー後に敵強化、delay連打 |
| N-10 normal_glo1_00001 | 19 | 40% | ElapsedTime+FriendUnitDead | 1100ms以内に4キャラ登場、複数FriendUnitDead閾値交差 |
| N-11 normal_dan_00001 | 6 | なし | DarknessKomaCleared+ElapsedTime | 闇コマクリアトリガー、2行2トリガー |
| N-12 normal_glo4_00001 | 6 | 67% | InitialSummon+FriendUnitDead | 6体6行・Fall×2、FriendUnitDead毎体ごとに展開 |
| N-13 normal_glo1_00003 | 12 | なし | InitialSummon+ElapsedTime | FriendUnitDeadなし、難易度違いファントムを時系列に配置 |
| N-14 normal_dan_00004 | 12 | 8% | ElapsedTime+FriendUnitDead | 開幕即c_キャラ、同一e_キャラを時間+倒した数の両方で継続補充 |
| N-15 normal_glo2_00002 | 36 | 17% | ElapsedTime+FriendUnitDead | 5作品c_キャラ6種がElapsedTimeで1体ずつ順番登場 |
