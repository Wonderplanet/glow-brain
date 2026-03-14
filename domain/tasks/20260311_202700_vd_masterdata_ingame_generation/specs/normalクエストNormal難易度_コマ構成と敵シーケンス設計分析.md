# normalクエスト（Normal難易度）コマ構成と敵シーケンス設計分析

**分析日**: 2026-03-14
**分析対象**: MstKomaLine / MstAutoPlayerSequence / MstInGame
**絞り込み条件**: `id LIKE 'normal_%' AND ENABLE = 'e'`
**対象件数**: MstInGame 78件、MstKomaLine 231行、MstAutoPlayerSequence（SummonEnemy）648行

---

## 1. 分析サマリー

- **ページ段数**: 2段が20件（26%）、3段が53件（68%）、4段が8件（10%）
- **コマ列数**: 2列（最多）、3列、まれに4列（normal_osh_00001のみ）
- **condition_type**: ElapsedTime（経過時間）が最多（312件・48%）、FriendUnitDead（味方死亡数）が次点（192件・30%）
- **SwitchSequenceGroup**: 9件のインゲームで複数グループによる段階的難化シーケンスを採用
- **コマエフェクト**: 約95%が「None」（エフェクトなし）。有効エフェクトはAttackPowerDown・Darkness・SlipDamage・Gust・Poison・Burn等

---

## 2. MstKomaLine コマ構成の設計パターン

### 2-1. 段数別レイアウトの傾向

| 段数 | 件数 | 割合 |
|------|------|------|
| 2段  | 20件 | 26%  |
| 3段  | 53件 | 68%  |
| 4段  | 8件  | 10%  |

**最多は3段構成。** 4段はステージが長く高難度のコンテンツに使用される。

### 2-2. よく使われるレイアウトパターン（koma_line_layout_asset_key）

| layout値 | 件数 | 特徴 |
|----------|------|------|
| 1        | 45件 | 1コマ（全幅1.0）シングル |
| 2        | 34件 | 2コマ（0.6:0.4 分割） |
| 3        | 25件 | 2コマ（0.4:0.6 分割） |
| 6        | 25件 | 2コマ（0.5:0.5 均等） |
| 4        | 20件 | 2コマ（0.75:0.25 分割） |
| 9        | 17件 | 3コマ（0.25:0.5:0.25 均等3分割） |
| 5        | 16件 | 2コマ（0.25:0.75 分割） |
| 7        | 16件 | 3コマ（0.33:0.34:0.33 均等3分割） |
| 8        | 10件 | 3コマ（0.5:0.25:0.25 等） |
| 11       | 10件 | 3コマ（高さ0.75の大型コマ） |
| 10       | 8件  | 2〜3コマ（変則分割） |
| 12       | 5件  | 4コマ（0.25:0.25:0.25:0.25 均等4分割） |

### 2-3. コマエフェクト使用種類

全体の約95%が `None`（エフェクトなし）。有効エフェクトが付くのはレア。

| エフェクト種別 | 使用例 |
|--------------|--------|
| AttackPowerDown | 敵の攻撃力を下げる（dan系に多い） |
| Darkness | コマが暗くなる（dan系でDarknessKomaCleared条件と連携） |
| SlipDamage | 毎ターンHP減少 |
| Gust | 風エフェクト |
| Poison | 毒エフェクト |
| Burn | 炎エフェクト |
| AttackPowerUp | 攻撃力上昇（プレイヤー強化コマ） |

---

## 3. MstAutoPlayerSequence 敵出現設計のパターン

### 3-1. condition_type 別使用割合

| condition_type | 件数 | 割合 | 説明 |
|----------------|------|------|------|
| ElapsedTime | 312件 | 48% | ゲーム開始からの経過フレーム数（1/60秒）で出現 |
| FriendUnitDead | 192件 | 30% | 味方ユニット（敵）が倒された累積数で出現 |
| ElapsedTimeSinceSequenceGroupActivated | 49件 | 8% | グループ切り替え後の経過時間で出現 |
| InitialSummon | 38件 | 6% | ステージ開始と同時に特定位置に出現 |
| OutpostDamage | 37件 | 6% | 自軍拠点がダメージを受けた回数で出現 |
| EnterTargetKomaIndex | 10件 | 2% | 敵がコマに進入した回数で出現 |
| DarknessKomaCleared | 5件 | 1% | Darknessコマを解除した数で出現 |
| FriendUnitTransform | 3件 | <1% | 友軍ユニットが変身したタイミングで出現 |
| OutpostHpPercentage | 2件 | <1% | 自軍拠点のHP割合で出現 |

### 3-2. シーケンス要素数（SummonEnemy行数）の分布

| 要素数 | 件数 |
|--------|------|
| 1〜3   | 20件 |
| 4〜7   | 25件 |
| 8〜12  | 14件 |
| 13〜20 | 16件 |
| 21以上 |  3件 |

シンプルなステージは3〜7要素、高難度・長尺ステージは20要素超も存在。

---

## 4. 具体例5件の詳細解説

---

### 例①: `normal_aka_00001` — シンプル2段構成・時間トリガーのみ

**コンセプト**: 最もシンプルな構成。2段ページ、ElapsedTimeのみで敵出現。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma1_width | koma2 | koma2_width | koma3 | koma3_width |
|-----|--------|--------|-------|-------------|-------|-------------|-------|-------------|
| 1 | 0.55 | 2 | aka_00001 | 0.60 | aka_00001 | 0.40 | - | - |
| 2 | 0.55 | 7 | aka_00001 | 0.33 | aka_00001 | 0.34 | aka_00001 | 0.33 |

- **段1**: 2コマ（左60%:右40%）
- **段2**: 3コマ均等（33%:34%:33%）

#### MstAutoPlayerSequence（敵シーケンス）

| elem | action_type | action_value | condition_type | condition_value | hp_coef | atk_coef | summon_count |
|------|-------------|--------------|----------------|----------------|---------|----------|-------------|
| 1 | SummonEnemy | c_aka_00101_general_n_Boss_Colorless | ElapsedTime | 200 | 3.5 | 5.0 | 1 |
| 2 | SummonEnemy | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 350 | 3.5 | 3.4 | 5 |
| 3 | SummonEnemy | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 2000 | 3.5 | 3.4 | 3 |

**設計ポイント**:
- 序盤（200f）にcキャラ（フレンドユニット）1体が先行出現、HP・攻撃力倍率高め
- 350f後に雑魚5体が一気に出現
- 2000f後に追加3体で終盤を締める
- 全トリガーが ElapsedTime のみ → 操作によらない定時出現型

---

### 例②: `normal_chi_00001` — 2段・多段階FriendUnitDeadによるウェーブ設計

**コンセプト**: 倒した数に応じて次の敵が出現。色の切り替え（Colorless→Yellow）で難易度が段階的に上昇。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma1_width | koma2 | koma2_width |
|-----|--------|--------|-------|-------------|-------|-------------|
| 1 | 0.55 | 3 | glo_00016 | 0.40 | glo_00016 | 0.60 |
| 2 | 0.75 | 1 | glo_00016 | 1.00 | - | - |

- **段1**: 2コマ（左40%:右60%）
- **段2**: 高さ0.75の大型1コマ（全幅）← 特大ボスコマの表現

#### MstAutoPlayerSequence（主要シーケンス）

| elem | action_value | condition_type | condition_value | summon_count | summon_position |
|------|--------------|----------------|----------------|-------------|-----------------|
| 1 | e_chi_00101_general_Normal_Colorless | ElapsedTime | 250 | 2 | - |
| 2 | e_chi_00101_general_Normal_Colorless | ElapsedTime | 700 | 2 | - |
| 3 | e_chi_00101_general_Normal_Colorless | ElapsedTime | 1200 | 1 | - |
| 4 | e_chi_00101_general_Normal_Colorless | FriendUnitDead | 3 | 10 | - |
| 5 | e_chi_00101_general_Normal_Yellow | ElapsedTime | 1500 | 1 | - |
| 6 | e_chi_00101_general_Normal_Yellow | ElapsedTime | 1700 | 2 | - |
| 7 | e_chi_00101_general_Normal_Yellow | FriendUnitDead | 5 | 2 | - |
| 9〜10 | e_chi_00101_general_Normal_Yellow | FriendUnitDead | 8 | 3 | 1.9/1.8（Fall） |
| 11 | e_chi_00101_general_Normal_Colorless | FriendUnitDead | 8 | 20 | - |
| 19〜20 | e_chi_00101_general_Normal_Yellow | OutpostDamage | 1 | 99 | 1.9/1.8 |

**設計ポイント**:
- 序盤はColorless（無色）雑魚を時間トリガーで出現、合計5体程度
- 3体倒すと10体一斉増援（FriendUnitDead:3 → 10体）
- 1500f後からYellow（黄色）敵に切り替わり質的に強化
- 8体倒した後は Fall アニメーションで特定位置に落下出現
- 拠点ダメージを受けると99体の大量出現 → 防衛強度を高める

---

### 例③: `normal_dan_00001` — 2段・Darknessコマ連携設計

**コンセプト**: Darknessコマ（暗闇効果）の解除数を出現トリガーにした特殊設計。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma1_width | koma1_effect | koma2 | koma2_width | koma2_effect |
|-----|--------|--------|-------|-------------|-------------|-------|-------------|-------------|
| 1 | 0.55 | 5 | dan_00005 | 0.25 | None | dan_00005 | 0.75 | None |
| 2 | 0.55 | 1 | dan_00005 | 1.00 | **Darkness** | - | - | - |

- **段2の全体コマ**に `Darkness` エフェクトが付与 → 段2が暗闇状態になる

#### MstAutoPlayerSequence（敵シーケンス）

| elem | action_value | condition_type | condition_value | hp_coef | atk_coef | summon_count |
|------|--------------|----------------|----------------|---------|----------|-------------|
| 1 | e_dan_00201_general_n_Boss_Colorless | **DarknessKomaCleared** | 2 | 0.55 | 0.3 | 1 |
| 2 | e_glo_00001_general_n_Normal_Colorless | ElapsedTime | 500 | 9.5 | 8.0 | 5 |

**設計ポイント**:
- Darknessコマを**2個解除**するとボスが出現（DarknessKomaCleared:2）
- プレイヤーが積極的にコマを解除する行動を促すメカニクス
- ボスHP倍率0.55・攻撃倍率0.3（弱め設定）で Darkness 解除のボーナス的出現
- 500f後に強力な雑魚5体（HP:9.5倍、ATK:8倍）が追加出現し本番の難易度

---

### 例④: `normal_dan_00002` — 3段・SwitchSequenceGroup による段階強化

**コンセプト**: 味方死亡数に応じてグループを切り替え、出現数を段階的に増加させるウェーブシステム。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma1_effect | koma2 | koma2_effect | koma3 | koma3_effect |
|-----|--------|--------|-------|-------------|-------|-------------|-------|-------------|
| 1 | 0.55 | 2 | dan_00006 | None | dan_00006 | **Darkness** | - | - |
| 2 | 0.55 | 5 | dan_00006 | **Darkness** | dan_00006 | **Darkness** | - | - |
| 3 | 0.55 | 7 | dan_00006 | None | dan_00006 | None | dan_00006 | None |

- 段1・段2の一部コマに **Darkness** エフェクトが付与された混合レイアウト

#### MstAutoPlayerSequence（敵シーケンス）

**デフォルトグループ**:

| elem | action_value | condition_type | condition_value | summon_count | summon_position |
|------|--------------|----------------|----------------|-------------|-----------------|
| 1 | e_dan_00001_general_n_Normal_Red | DarknessKomaCleared | 3 | 1 | 1.7/1.8/1.6（3行同時） |
| 2 | e_glo_00001_general_n_Normal_Red | ElapsedTime | 500 | 3 | - |
| 6 | **SwitchSequenceGroup → group1** | FriendUnitDead | 1 | - | - |

**group1**（1体倒し後）:

| elem | action_value | condition_type | condition_value | summon_count |
|------|--------------|----------------|----------------|-------------|
| 3 | e_dan_00001_general_n_Normal_Red | ElapsedTimeSinceGroupActivated | 50 | 4 |
| 4 | e_dan_00001_general_n_Normal_Red | ElapsedTimeSinceGroupActivated | 500 | 4 |
| 5 | e_dan_00001_general_n_Normal_Red | ElapsedTimeSinceGroupActivated | 1500 | 4 |

**設計ポイント**:
- DarknessKomaCleared:3 → 特定位置（位置指定）に3箇所同時出現（包囲演出）
- 1体倒すと **SwitchSequenceGroup** でグループ切り替え → group1 へ
- group1では ElapsedTimeSinceGroupActivated で50→500→1500フレームと連続出現（合計12体）
- 段階的に出現数が増えることで「後半が難しい」体験を演出

---

### 例⑤: `normal_spy_00004` — 4段・5グループ連鎖による無限ウェーブ型

**コンセプト**: 1体倒すたびにグループが切り替わり、出現数が増加し続けるエンドレス型構成。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma1_width | koma2 | koma2_width | koma3 | koma3_width |
|-----|--------|--------|-------|-------------|-------|-------------|-------|-------------|
| 1 | 0.55 | 4 | spy_00002 | 0.75 | spy_00002 | 0.25 | - | - |
| 2 | 0.55 | 2 | spy_00002 | 0.60 | spy_00002 | 0.40 | - | - |
| 3 | 0.55 | 9 | spy_00002 | 0.25 | spy_00002 | 0.50 | spy_00002 | 0.25 |
| 4 | 0.55 | 1 | spy_00002 | 1.00 | - | - | - | - |

- 4段でバリエーション豊かなレイアウト（2コマ→2コマ→3コマ→1コマ）

#### MstAutoPlayerSequence（敵シーケンス）

| group | elem | action_value | condition_type | condition_value | summon_count |
|-------|------|--------------|----------------|----------------|-------------|
| default | 1 | e_spy_00001_general_n_Normal_Colorless | ElapsedTime | 0 | 1 |
| default | 7 | **SwitchSequenceGroup → group1** | FriendUnitDead | 1 | - |
| group1 | 2 | e_spy_00001_general_n_Normal_Colorless | ElapsedTimeSinceGroupActivated | 100 | 2 |
| group1 | 8 | **SwitchSequenceGroup → group2** | FriendUnitDead | 2 | - |
| group2 | 3 | e_spy_00001_general_n_Normal_Colorless | ElapsedTimeSinceGroupActivated | 100 | 1 |
| group2 | 9 | **SwitchSequenceGroup → group3** | FriendUnitDead | 3 | - |
| group3 | 4 | e_spy_00001_general_n_Normal_Colorless | ElapsedTimeSinceGroupActivated | 100 | 2 |
| group3 | 10 | **SwitchSequenceGroup → group4** | FriendUnitDead | 4 | - |
| group4 | 5 | e_spy_00001_general_n_Normal_Colorless | ElapsedTimeSinceGroupActivated | 100 | 3 |
| group4 | 11 | **SwitchSequenceGroup → group5** | FriendUnitDead | 5 | - |
| group5 | 6 | e_spy_00001_general_n_Normal_Colorless | ElapsedTimeSinceGroupActivated | 500 | 99 |

**設計ポイント**:
- 初回1体、倒すたびに次グループへ切り替え（1→2→1→2→3→99体）
- 最終グループ（group5）で summon_count=99 の大量召喚に移行
- 「倒せば倒すほど増える」圧迫感のある設計
- 4段構成で長いステージを演出、最終段は全幅1コマ（ボスコマ）

---

### 例⑥: `normal_osh_00001` — 4段・4コマ行・EnterTargetKomaIndex + グループ切り替え

**コンセプト**: コマへの進入回数・拠点HP割合など多彩なトリガーを組み合わせた高難度設計。

#### MstKomaLine（コマ構成）

| row | height | layout | koma1 | koma2 | koma3 | koma4（全コマ0.25ずつ） |
|-----|--------|--------|-------|-------|-------|------|
| 1 | 0.55 | 6 | osh_00001 | osh_00001 | - | - |
| 2 | 0.55 | 7 | osh_00001 | osh_00001 | osh_00001 | - |
| 3 | 0.55 | 6 | osh_00001 | osh_00001 | - | - |
| **4** | 0.55 | **12** | osh_00001 | osh_00001 | osh_00001 | **osh_00001** |

- **layout=12（4コマ均等）** を使用した唯一のパターン例（各0.25幅）
- 全段 `osh_00001` アセットで統一

#### MstAutoPlayerSequence（敵シーケンス）

| elem | action_value | condition_type | condition_value | hp_coef | atk_coef | summon_count |
|------|--------------|----------------|----------------|---------|----------|-------------|
| 1 | c_osh_00001_general_osh_n_Normal_Colorless | ElapsedTime | 200 | 70 | 10 | 1 |
| 2 | e_glo_00001_general_osh_n_Normal_Yellow | **EnterTargetKomaIndex** | 3 | 40 | 15 | 1 |
| 3×2 | e_glo_00001_general_osh_n_Boss_Yellow | FriendUnitDead | 2 | 600 | 25 | 1 |
| 4 | e_glo_00001_general_osh_n_Normal_Yellow | **OutpostHpPercentage** | 99 | 40 | 15 | 20 |
| groupchange_1 | SwitchSequenceGroup → w1 | FriendUnitDead | 3 | - | - | - |
| w1-6〜10 | e_glo_00001_general_osh_n_Normal_Yellow | ElapsedTimeSinceGroupActivated | 50〜3000 | 40 | 15 | 2〜10 |

**設計ポイント**:
- **EnterTargetKomaIndex:3** → コマに3回進入した時点で追加出現（侵攻促進）
- **OutpostHpPercentage:99** → 拠点がわずかでもダメージを受けると20体一斉出現（早期警戒）
- cキャラ（フレンドユニット）のHP倍率70、BOSSのHP倍率600と極端な設定
- w1グループでは ElapsedTimeSinceGroupActivated で50→500→1000→2000→3000フレームと波状出現

---

## 5. パターン別分類まとめ

| パターン | 代表例 | 特徴 |
|---------|--------|------|
| **定時出現型** | normal_aka_00001 | ElapsedTimeのみ。予測可能な安定設計 |
| **ウェーブ型（死亡カウント）** | normal_chi_00001 | FriendUnitDeadで次ウェーブ。倒せば倒すほど先に進む |
| **ギミック連携型** | normal_dan_00001 | DarknessKomaCleared等のコマ効果と連携 |
| **グループ切り替え型** | normal_dan_00002 | SwitchSequenceGroupで段階的難化 |
| **エンドレス型** | normal_spy_00004 | 多段グループ連鎖で無限増殖 |
| **複合型** | normal_osh_00001 | EnterTargetKomaIndex・OutpostHpPercentage等多様なトリガー |

---

## 6. VD設計への示唆

normalクエストNormal難易度のデータから、以下の知見をVD設計に活用できる:

1. **コマ段数は3段が主流**。VDも2〜4段を基本とし、4段は上位ブロック向けとする
2. **ElapsedTime + FriendUnitDead の組み合わせが基本**。初期に時間トリガー、後半に死亡カウントで波を演出
3. **SwitchSequenceGroup は段階難化に有効**。VDのノーマルブロックでも採用可能
4. **コマエフェクトは基本なし（None）**。特殊エフェクト（Darkness等）は特定の作品キャラ演出に限定
5. **summon_count=99 の使用**はエンドレス化（実質無限出現）として終盤演出に使う
6. **InitialSummon + summon_position** でステージ開始時の初期配置（特定座標に配置）が可能
