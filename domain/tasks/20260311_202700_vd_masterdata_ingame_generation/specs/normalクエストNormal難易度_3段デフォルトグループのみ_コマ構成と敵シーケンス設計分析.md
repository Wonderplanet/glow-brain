# normalクエスト（Normal難易度）3段・デフォルトグループのみ コマ構成と敵シーケンス設計分析

**分析日**: 2026-03-14
**分析対象**: MstKomaLine / MstAutoPlayerSequence / MstInGame
**絞り込み条件**:
- `id LIKE 'normal_%' AND ENABLE = 'e'`
- KomaLine行数 = 3（3段ページ構成のみ）
- MstAutoPlayerSequence に `SwitchSequenceGroup` が存在しない（デフォルトグループのみ）

**対象件数**: 47件

---

## 1. 分析サマリー

- **KomaLine段構成**: 全件3段固定。各段の列数・レイアウトに多様なパターンが存在
- **condition_type**: ElapsedTime（206件・51%）とFriendUnitDead（136件・34%）が二大主力。OutpostDamage（30件・7%）、InitialSummon（22件・5%）が続く
- **シーケンス要素数**: 3〜7要素が多いが、最大20要素まで存在。グループ切り替えなしでも複雑な設計が可能
- **コマエフェクト**: 大半はNone。AttackPowerDown・SlipDamage・Darkness が一部で使用

---

## 2. MstKomaLine 3段コマ構成の設計パターン

### 2-1. 段別レイアウト頻度（対象47件 × 3段 = 141行）

| 段 | 最多レイアウト | 2位 | 3位 | 傾向 |
|----|------------|-----|-----|------|
| 段1 | layout 3（0.4:0.6）9件 | layout 2（0.6:0.4）6件 | layout 5/6各5件 | 2コマが主流。3コマも1/3程度 |
| 段2 | layout 1（1コマ全幅）11件 | layout 6（0.5:0.5）6件 | layout 2（0.6:0.4）6件 | 1コマ全幅が最多 |
| 段3 | layout 1（1コマ全幅）9件 | layout 4（0.75:0.25）6件 | layout 2（0.6:0.4）6件 | 1コマ全幅が最多 |

### 2-2. 3段構成のよく見られる組み合わせパターン

#### パターンA: 「3コマ→1コマ→1〜2コマ」構成
段1が3コマ均等（7 or 9 layout）、段2が全幅1コマ（1 layout）、段3が1〜2コマ。

```
段1: [コマ] [コマ] [コマ]  ← 3列
段2: [   コマ（全幅）    ]  ← 1列
段3: [コマ] [コマ]         ← 2列
```

例: `normal_rik_00001`（7→1→2）、`normal_glo3_00001`（7→10→1）、`normal_osh_00002`（7→1→10）

#### パターンB: 「2コマ→1コマ全幅→2コマ」構成
段1が2コマ、段2が全幅1コマ（ボス演出に使用されやすい）、段3が2コマ。

```
段1: [コマ] [コマ]          ← 2列
段2: [   コマ（全幅）    ]   ← 1列
段3: [コマ] [コマ]           ← 2列
```

例: `normal_glo4_00001`（4→5→8）、`normal_chi_00006`（2→1→4）

#### パターンC: 「2コマ→2コマ→1コマ全幅」構成
末尾の段3が全幅1コマ（クライマックス演出）になる構成。

```
段1: [コマ] [コマ]          ← 2列
段2: [コマ] [コマ]          ← 2列
段3: [   コマ（全幅）    ]   ← 1列
```

例: `normal_aka_00002`（7→6→1）、`normal_glo3_00001`（7→10→1）

### 2-3. layout番号とコマ分割の対応（実データより）

| layout | コマ数 | koma1_width | koma2_width | koma3_width | 備考 |
|--------|-------|------------|------------|------------|------|
| 1 | 1 | 1.0 | - | - | 全幅1コマ（ボス演出や締め段に多用） |
| 2 | 2 | 0.6 | 0.4 | - | 左広め |
| 3 | 2 | 0.4 | 0.6 | - | 右広め |
| 4 | 2 | 0.75 | 0.25 | - | 左大・右小 |
| 5 | 2 | 0.25 | 0.75 | - | 左小・右大 |
| 6 | 2 | 0.5 | 0.5 | - | 均等2分割 |
| 7 | 3 | 0.33 | 0.34 | 0.33 | 均等3分割 |
| 8 | 3 | 0.5 | 0.25 | 0.25 | 左広め3分割 |
| 9 | 3 | 0.25 | 0.5 | 0.25 | 中央広め3分割 |
| 10 | 3 | 0.25 | 0.25 | 0.5 | 右広め3分割 |
| 11 | 3 | 0.4 | 0.2 | 0.4 | 中央狭め3分割 |
| 12 | 4 | 0.25 | 0.25 | 0.25 | 均等4分割（稀） |

---

## 3. MstAutoPlayerSequence condition_type別設計パターン

### 3-1. 使用条件種別（対象47件合計396要素）

| condition_type | 件数 | 割合 | 説明 |
|----------------|------|------|------|
| ElapsedTime | 206件 | 52% | 開始からの経過フレーム数（1f=1/60秒）で出現 |
| FriendUnitDead | 136件 | 34% | 累積撃破数で出現 |
| OutpostDamage | 30件 | 8% | 自軍拠点へのダメージ回数で出現 |
| InitialSummon | 22件 | 6% | 開始時に特定座標へ即配置 |
| EnterTargetKomaIndex | 1件 | <1% | 敵コマ進入回数で出現 |
| DarknessKomaCleared | 1件 | <1% | Darknessコマ解除数で出現 |

### 3-2. condition_type の組み合わせパターン（インゲーム単位）

| パターン名 | 使用条件 | 件数 | 特徴 |
|-----------|---------|------|------|
| **①純ElapsedTime型** | ElapsedTimeのみ | 6件 | 最シンプル。定時出現のみ |
| **②ElapsedTime+Initial型** | ElapsedTime + InitialSummon | 数件 | 初期配置（位置指定）あり |
| **③ElapsedTime+FriendDead型** | ElapsedTime + FriendUnitDead | 多数 | 定時+撃破連動の二段設計 |
| **④FriendDead+Initial型** | InitialSummon + FriendUnitDead | 数件 | 初期配置からの撃破連動 |
| **⑤OutpostDamage複合型** | 上記+OutpostDamage | 多数 | 3条件以上の複合設計 |
| **⑥InitialSummon主体型** | InitialSummon複数+FriendUnitDead | 少数 | 開幕に多数配置、撃破で追加 |

---

## 4. 具体例 6件の詳細解説

---

### 例①: `normal_aka_00002` — 3段・ElapsedTimeのみ・コマエフェクトあり

**概要**: 最もシンプルなElapsedTime単一トリガー構成。ただしコマにエフェクト付きで演出差別化。

#### MstKomaLine

| row | layout | koma1 | k1_w | k1_eff | koma2 | k2_w | k2_eff | koma3 | k3_w | k3_eff |
|-----|--------|-------|------|--------|-------|------|--------|-------|------|--------|
| 1 | 7 | glo_00001 | 0.33 | None | glo_00001 | 0.34 | None | glo_00001 | 0.34 | **AttackPowerDown** |
| 2 | 6 | aka_00001 | 0.50 | **AttackPowerDown** | aka_00001 | 0.50 | None | - | - | - |
| 3 | 1 | aka_00001 | 1.00 | None | - | - | - | - | - | - |

- 段1は `glo_00001`（作品外キャラ）の3コマ均等、右端に AttackPowerDown
- 段2は `aka_00001`（作品キャラ）の2コマ、左に AttackPowerDown
- 段3は作品キャラの全幅1コマ（クライマックス）

#### MstAutoPlayerSequence

| elem | action_value | condition_type | condition_value | hp_coef | atk_coef | count |
|------|--------------|----------------|----------------|---------|----------|-------|
| 1 | c_aka_00001_general_n_Boss_Red | ElapsedTime | 0 | 10.0 | 2.4 | 1 |
| 2 | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 250 | 3.5 | 3.4 | 10 |
| 3 | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 100 | 3.5 | 3.4 | 1 |
| 4 | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 2000 | 3.5 | 3.4 | 15 |

**設計ポイント**:
- ゲーム開始直後（0f）にcキャラ（ボス、HP×10）が先行出現
- 250f後に雑魚10体、さらに100f後に1体追加（計11体）
- 2000f後に雑魚15体の大波で締め
- 全ElapsedTimeのため操作に依存しない定時進行

---

### 例②: `normal_glo1_00003` — 3段・InitialSummon+ElapsedTime・SlipDamageエフェクト

**概要**: 開始時に特定位置へ初期配置し、その後ElapsedTimeで追加する組み合わせ型。コマにSlipDamage付き。

#### MstKomaLine

| row | layout | koma1 | k1_w | k1_eff | koma2 | k2_w | k2_eff | koma3 | k3_w | k3_eff |
|-----|--------|-------|------|--------|-------|------|--------|-------|------|--------|
| 1 | 9 | glo_00022 | 0.25 | None | glo_00022 | 0.50 | None | glo_00022 | 0.25 | **SlipDamage** |
| 2 | 6 | glo_00022 | 0.50 | None | glo_00022 | 0.50 | **SlipDamage** | - | - | - |
| 3 | 4 | glo_00017 | 0.75 | None | glo_00017 | 0.25 | None | - | - | - |

- 段1・段2の一部コマに **SlipDamage**（毎ターンHP減少）エフェクト
- 段1: 中央広め3コマ（9→25%:50%:25%）、段2: 均等2コマ、段3: 左広め2コマ（4→75%:25%）

#### MstAutoPlayerSequence

| elem | action_value | condition_type | condition_value | hp_coef | atk_coef | count | position |
|------|--------------|----------------|----------------|---------|----------|-------|----------|
| 4 | e_glo_00001_general_h_Normal_Blue | **InitialSummon** | 0 | 0.3 | 2.5 | 1 | 1.2 |
| 5 | e_glo_00001_general_h_Normal_Blue | **InitialSummon** | 0 | 0.3 | 2.5 | 1 | 1.8 |
| 1 | e_glo_00101_general_n_Boss_Blue | ElapsedTime | 300 | 5.5 | 12.0 | 1 | - |
| 2 | e_glo_00001_general_vh_Normal_Colorless | ElapsedTime | 750 | 0.9 | 0.5 | 2 | - |
| 3 | e_glo_00001_general_h_Normal_Blue | ElapsedTime | 1000 | 0.3 | 2.5 | 4 | - |
| 6 | e_glo_00001_general_vh_Normal_Colorless | ElapsedTime | 3200 | 0.9 | 0.5 | 3 | - |

**設計ポイント**:
- InitialSummon で位置1.2と1.8に敵を2体初期配置（開始直後から戦闘状態）
- 300f後にボス（HP×5.5・ATK×12）が出現
- 750f・1000f・3200fと段階的に追加出現
- 全6要素のシンプル設計ながら InitialSummon で開幕の緊張感を演出

---

### 例③: `normal_glo3_00001` — 3段・ElapsedTime+FriendUnitDead複合・複数キャラ混在

**概要**: ElapsedTimeで口火を切り、撃破数（FriendUnitDead）で複数種類のcキャラを Fall アニメーションで出現させる設計。

#### MstKomaLine

| row | layout | koma1 | k1_w | koma2 | k2_w | koma3 | k3_w |
|-----|--------|-------|------|-------|------|-------|------|
| 1 | 7 | glo_00015 | 0.33 | glo_00015 | 0.34 | glo_00015 | 0.33 |
| 2 | 10 | glo_00015 | 0.25 | glo_00015 | 0.25 | glo_00015 | 0.50 |
| 3 | 1 | glo_00015 | 1.00 | - | - | - | - |

- 段1: 均等3コマ（7）、段2: 右広め3コマ（10→25%:25%:50%）、段3: 全幅1コマ
- 全段同じアセット `glo_00015`

#### MstAutoPlayerSequence

| elem | action_value | condition_type | condition_value | count | position | animation |
|------|--------------|----------------|----------------|-------|----------|-----------|
| 1 | c_chi_00002_general_as_Normal_Yellow | ElapsedTime | 400 | 1 | - | None |
| 5 | e_glo_00001_general_rik_vh_Normal_Yellow | ElapsedTime | 200 | 99 | - | None |
| 2 | c_sur_00301_general_as_Normal_Yellow | FriendUnitDead | 1 | 1 | 2.8 | **Fall** |
| 3 | c_sur_00201_general_as_Normal_Yellow | FriendUnitDead | 1 | 1 | 2.85 | **Fall** |
| 4 | c_rik_00101_general_as_Boss_Yellow | FriendUnitDead | 1 | 1 | 2.9 | **Fall** |
| 6 | e_glo_00001_general_rik_vh_Normal_Yellow | FriendUnitDead | 4 | 99 | - | None |

**設計ポイント**:
- 400fにcキャラ1体出現、同時に200fから雑魚99体が出現（ほぼ無限）
- 1体倒すと3体のcキャラが位置2.8/2.85/2.9に Fall アニメーションで落下出現（同時3体）
- 4体倒した後も99体の雑魚が継続出現
- `c_`キャラ4種類（chi/sur×2/rik）が混在する演出豊かな設計

---

### 例④: `normal_glo4_00001` — 3段・InitialSummon+FriendUnitDeadのみ・ElapsedTimeなし

**概要**: ElapsedTimeを一切使わず、初期配置と撃破数だけで敵の出現を制御する特殊設計。

#### MstKomaLine

| row | layout | koma1 | k1_w | koma2 | k2_w | koma3 | k3_w |
|-----|--------|-------|------|-------|------|-------|------|
| 1 | 4 | glo_00016 | 0.75 | glo_00016 | 0.25 | - | - |
| 2 | 5 | glo_00016 | 0.25 | glo_00016 | 0.75 | - | - |
| 3 | 8 | glo_00016 | 0.50 | glo_00016 | 0.25 | glo_00016 | 0.25 |

- 段1: 左広め2コマ（4→75%:25%）、段2: 右広め2コマ（5→25%:75%）→ 左右対称の構造
- 段3: 左広め3コマ（8→50%:25%:25%）

#### MstAutoPlayerSequence

| elem | action_value | condition_type | condition_value | count |
|------|--------------|----------------|----------------|-------|
| 1 | e_sum_00201_general_as4_Normal_Green | **InitialSummon** | 2 | 1 |
| 2 | e_sum_00101_general_as4_Normal_Green | FriendUnitDead | 1 | 1 |
| 3 | c_kai_00002_general_as4_Normal_Green | FriendUnitDead | 2 | 1 |
| 4 | c_kai_00101_general_as4_Normal_Green | FriendUnitDead | 2 | 1 |
| 5 | c_kai_00002_general_as4_Boss_Green | FriendUnitDead | 4 | 1 |
| 6 | e_sum_00101_general_as4_Normal_Green | FriendUnitDead | 5 | 99 |

**設計ポイント**:
- 開始時（InitialSummon:2 = ページが2段目に到達したとき）に1体配置
- 以降は完全に撃破数のみでトリガー（倒す→次が出る→倒す→次が出る）
- `c_` キャラが段階的に強化されながら登場（kai_00002→kai_00101→kai_00002_Boss）
- 最後は99体の雑魚で無限化

---

### 例⑤: `normal_rik_00001` — 3段・ElapsedTime+FriendUnitDead+OutpostDamage複合・20要素

**概要**: 3種類のトリガーを組み合わせた高密度設計。20要素を持つ複雑なシーケンス。

#### MstKomaLine

| row | layout | koma1 | k1_w | koma2 | k2_w | koma3 | k3_w |
|-----|--------|-------|------|-------|------|-------|------|
| 1 | 7 | rik_00001 | 0.33 | rik_00001 | 0.34 | rik_00001 | 0.33 |
| 2 | 1 | rik_00001 | 1.00 | - | - | - | - |
| 3 | 2 | rik_00001 | 0.60 | rik_00001 | 0.40 | - | - |

- 段1: 均等3コマ（7）、段2: 全幅1コマ（1）、段3: 左広め2コマ（2）

#### MstAutoPlayerSequence（主要要素）

| elem | action_value | condition_type | condition_value | count | position | animation |
|------|--------------|----------------|----------------|-------|----------|-----------|
| 1 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 200 | 3 | - | None |
| 2 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 800 | 1 | - | None |
| 3 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 2 | 3 | - | None |
| 4 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 2 | 3 | - | None |
| 5 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 2800 | 1 | 2.9 | **Fall** |
| 6 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 2800 | 1 | 2.8 | **Fall** |
| 7 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 5 | 7 | - | None |
| 8 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 6 | 7 | 2.9 | **Fall** |
| 9 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 6 | 7 | 2.8 | **Fall** |
| 10 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 6000 | 1 | 2.9 | **Fall** |
| 11 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 6000 | 1 | 2.8 | **Fall** |
| 12 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 10 | 5 | 2.9 | **Fall** |
| 13〜15 | c_rik_00001_general_Normal_Colorless | FriendUnitDead | 10〜11 | 5 | 各位置 | **Fall** |
| 16〜18 | c_rik_00001_general_Normal_Colorless | ElapsedTime | 8500〜9000 | 99 | - | None |
| 19〜20 | c_rik_00001_general_Normal_Colorless | **OutpostDamage** | 1 | 99 | - | None |

**設計ポイント**:
- 序盤はElapsedTimeで定期出現（3体→1体）、撃破でFriendUnitDeadが発火（各3体×2）
- 中盤は ElapsedTime+Fall で特定位置から落下出現、FriendUnitDeadで7体ずつの大波
- 後半（6000f）以降はFallアニメーション付きで複数位置から集中出現
- 終盤（8500〜9000f）にElapsedTime 99体 × 3回で怒涛の大量出現
- **OutpostDamage:1** が2要素あり、拠点被弾時も99体が出現 → 二重の危機演出
- 全20要素でグループ切り替えなし。デフォルトグループのみで長丁場を設計

---

### 例⑥: `normal_osh_00002` — 3段・InitialSummon(9要素)+FriendUnitDead+EnterTargetKomaIndex

**概要**: 開幕に9箇所の位置指定配置を行い、その後撃破数+特殊トリガーで展開する密度の高い設計。

#### MstKomaLine

| row | layout | koma1 | k1_w | koma2 | k2_w | koma3 | k3_w |
|-----|--------|-------|------|-------|------|-------|------|
| 1 | 7 | osh_00001 | 0.33 | osh_00001 | 0.34 | osh_00001 | 0.33 |
| 2 | 1 | osh_00001 | 1.00 | - | - | - | - |
| 3 | 10 | osh_00001 | 0.25 | osh_00001 | 0.25 | osh_00001 | 0.50 |

- 段1: 均等3コマ、段2: 全幅1コマ、段3: 右広め3コマ（10→25%:25%:50%）

#### MstAutoPlayerSequence（主要要素）

| elem | action_value | condition_type | condition_value | count | position | animation |
|------|--------------|----------------|----------------|-------|----------|-----------|
| 1〜9 | e_glo_00002/e_glo_00002（Green） | **InitialSummon** | 0 | 1（各） | 0.5〜2.7（9箇所） | None |
| 10 | c_osh_00001_general_osh_n_Boss_Colorless | **EnterTargetKomaIndex** | 0 | 1 | 1.5 | Fall0 |
| 11 | e_glo_00002_Colorless | FriendUnitDead | 1 | 5 | - | None |
| 12 | e_glo_00002_Colorless | FriendUnitDead | 2 | 3 | - | None |
| 13〜14 | e_glo_00002_Green | FriendUnitDead | 3〜4 | 5〜3 | - | None |
| 15〜19 | e_glo_00002_Green/Colorless | FriendUnitDead | 5〜9 | 3 | 各位置 | Fall0 |

**設計ポイント**:
- **InitialSummon 9要素**: 0.5〜2.7の9箇所に開始時から敵を配置（2種類の敵が混在）
- **EnterTargetKomaIndex:0** → コマに敵が進入した時点でボス（HP×900・ATK×11）が Fall0 で落下出現（最重要トリガー）
- Colorless（軽量）→ Green（中量）と撃破が進むにつれ色が変わり強化
- Fall0アニメーションで後半の敵は落下演出付き

---

## 5. 全体パターン分類まとめ

| パターン | 代表ステージ | condition_type組み合わせ | 件数目安 |
|---------|------------|------------------------|---------|
| **①純ElapsedTime型** | normal_aka_00002 | ElapsedTimeのみ | 6件 |
| **②ElapsedTime+Initial型** | normal_glo1_00003 | ElapsedTime + InitialSummon | 数件 |
| **③ElapsedTime+FriendDead型** | normal_glo3_00001 | ElapsedTime + FriendUnitDead | 多数 |
| **④FriendDead+Initial型** | normal_glo4_00001 | InitialSummon + FriendUnitDead（ElapsedTimeなし） | 数件 |
| **⑤OutpostDamage複合型** | normal_rik_00001 | 上記+OutpostDamage（3条件複合） | 多数 |
| **⑥Initial密配置型** | normal_osh_00002 | InitialSummon×複数 + FriendUnitDead + 特殊 | 少数 |

---

## 6. VD設計（3段・デフォルトグループのみ）への示唆

1. **3段構成の王道**: 段1=複数コマ（2〜3列）・段2=全幅1コマまたは2コマ・段3=全幅1コマ or 複数コマ
2. **最もシンプルな設計**: ElapsedTimeのみ（3〜4要素）。VDノーマル序盤ブロックに適する
3. **FriendUnitDead は倒すたびに出現数を増やす表現に最適**（例: 1体→3体→7体→99体）
4. **OutpostDamage:1 で拠点被弾時の緊急出現演出** が可能（99体などで圧迫感を強調）
5. **InitialSummon** で開幕時の初期配置を実現。位置指定（summon_position）との組み合わせで布陣感を演出
6. **Fall / Fall0 アニメーション** を FriendUnitDead トリガーと組み合わせると、「倒したら上から落ちてくる」演出になる
7. **コマエフェクトは基本None**。作品キャラ固有演出（SlipDamage・AttackPowerDown等）が必要な場合のみ付与
