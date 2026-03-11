# vd_osh_normal_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

【推しの子】の世界観を反映したノーマルブロックです。ステージに登場するのは、B小町メンバーである星野ルビー・MEMちょ・有馬かな・黒川あかねの4名のプレイアブルキャラ（c_キャラ）と、共通敵のファントムです。

難易度設計としては、開幕にファントムを複数体出現させてプレイヤーの初動を促し、その後c_キャラが撃破トリガーや時間トリガーで1体ずつ登場する構成をとっています。c_キャラはプレイヤーキャラが敵として出現するため、世界観上同一キャラが複数体同時にフィールドに存在する状況を避け、それぞれ個別召喚で対処します。

合計7行のシーケンス構成で、ファントム3波+c_キャラ4種が各1体ずつ（FriendUnitDead累積カウントによる再出撃あり）登場し、最低15体以上の出現を保証しています。バトルのテンポは序盤に速く、中盤以降は手ごわいc_キャラが登場して緊張感を高める体験を目指しています。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| chara_osh_00201 | 星野 ルビー | 雑魚（c_キャラ） | Green属性・Attackロール。プレイアブルキャラが敵として出現 |
| chara_osh_00301 | MEMちょ | 雑魚（c_キャラ） | Green属性・Supportロール。プレイアブルキャラが敵として出現 |
| chara_osh_00401 | 有馬 かな | 雑魚（c_キャラ） | Green属性・Technicalロール。プレイアブルキャラが敵として出現 |
| chara_osh_00501 | 黒川 あかね | 雑魚（c_キャラ） | Green属性・Technicalロール。プレイアブルキャラが敵として出現 |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・Attackロール |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202512020, 202509010)
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）
> ただし、今回のバッチでは release_key=202604010 でoshノーマルブロック用として参照する

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| c_osh_00201_vd_Normal_Green | 星野 ルビー | Normal | Attack | Green | 50,000 | 300 | 30 | 0.22 | 3 | 5 | 500 |
| c_osh_00301_vd_Normal_Green | MEMちょ | Normal | Support | Green | 50,000 | 300 | 30 | 0.27 | 3 | 4 | 500 |
| c_osh_00401_vd_Normal_Green | 有馬 かな | Normal | Technical | Green | 50,000 | 300 | 32 | 0.24 | 3 | 4 | 500 |
| c_osh_00501_vd_Normal_Green | 黒川 あかね | Normal | Technical | Green | 10,000 | 300 | 30 | 0.26 | 3 | 4 | 500 |
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

各行独立ランダム抽選（12パターンから）の結果:

```mermaid
block-beta
  columns 4
  A["row=1 / koma1\n幅=0.25\neffect: None"]:1 B["row=1 / koma2\n幅=0.50\neffect: None"]:2 C["row=1 / koma3\n幅=0.25\neffect: None"]:1
  columns 2
  D["row=2 / koma1\n幅=0.50\neffect: None"]:1 E["row=2 / koma2\n幅=0.50\neffect: None"]:1
  columns 10
  F["row=3 / koma1\n幅=0.40\neffect: None"]:4 G["row=3 / koma2\n幅=0.20\neffect: None"]:2 H["row=3 / koma3\n幅=0.40\neffect: None"]:4
```

| row | height | 選択パターン | コマ数 | 各幅 | 幅合計 |
|-----|--------|------------|-------|------|--------|
| 1 | 0.33 | パターン9「中央広い」 | 3コマ | 0.25, 0.50, 0.25 | 1.0 |
| 2 | 0.33 | パターン6「2等分」 | 2コマ | 0.50, 0.50 | 1.0 |
| 3 | 0.34 | パターン11「中央狭い」 | 3コマ | 0.40, 0.20, 0.40 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> W1["ElapsedTime 250ms\nファントム ×5\nDefault"]
  W1 --> W2["ElapsedTime 1500ms\n星野ルビー ×1\nDefault"]
  W2 --> W3["FriendUnitDead 1\nMEMちょ ×1\nDefault"]
  W3 --> W4["ElapsedTime 3000ms\nファントム ×5\nDefault"]
  W4 --> W5["FriendUnitDead 3\n有馬かな ×1\nDefault"]
  W5 --> W6["FriendUnitDead 5\n黒川あかね ×1\nDefault"]
  W6 --> W7["ElapsedTime 5000ms\nファントム ×5\nDefault"]
  W7 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数 |
|------|-------------|---|---|---------|
| 1 | ElapsedTime 250ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 5 | 5 |
| 2 | ElapsedTime 1500ms | 星野 ルビー (c_osh_00201_vd_Normal_Green) | 1 | 6 |
| 3 | FriendUnitDead 1（累計1体撃破） | MEMちょ (c_osh_00301_vd_Normal_Green) | 1 | 7 |
| 4 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 5 | 12 |
| 5 | FriendUnitDead 3（累計3体撃破） | 有馬 かな (c_osh_00401_vd_Normal_Green) | 1 | 13 |
| 6 | FriendUnitDead 5（累計5体撃破） | 黒川 あかね (c_osh_00501_vd_Normal_Green) | 1 | 14 |
| 7 | ElapsedTime 5000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 5 | 19 |

合計: **19体**（要件「最低15体以上」を満たす）

> **c_キャラ召喚ガードレール確認**: chara_osh_00201/00301/00401/00501 はすべて c_ プレフィックスのため、同一トリガーで summon_count >= 2 かつ summon_interval = 0 の瞬間複数召喚を禁止。各行 summon_count=1 で個別召喚設計にしています。

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

MstAutoPlayerSequenceの `enemy_hp_coef` / `enemy_attack_coef` はすべてデフォルト値（1.0）を使用します。

| 波 | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|---|---|---------|---------|------|----------|----------|-------|
| 1 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 2 | 星野 ルビー | 50,000 | 1.0 | 50,000 | 300 | 1.0 | 300 |
| 3 | MEMちょ | 50,000 | 1.0 | 50,000 | 300 | 1.0 | 300 |
| 4 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 5 | 有馬 かな | 50,000 | 1.0 | 50,000 | 300 | 1.0 | 300 |
| 6 | 黒川 あかね | 10,000 | 1.0 | 10,000 | 300 | 1.0 | 300 |
| 7 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | （空） | VDの背景切り替えはゲームロジック側で管理 |
| フロア0以上 | koma_background_vd_00001 | クライアント側でフロア係数に応じて切り替え |
| フロア20以上 | koma_background_vd_00003 | 同上 |
| フロア40以上 | koma_background_vd_00005 | 同上 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | SSE_SBG_003_010 | ノーマルブロック用BGM |
| boss_bgm_asset_key | （空） | ノーマルブロックはボスBGMなし |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | 全敵キャラ（ノーマルブロックはボスなし、全行Default） |

---

### 敵キャラ召喚アニメーション

全キャラ `SummonEnemy` アクションによる ElapsedTime または FriendUnitDead トリガーでの召喚。InitialSummonは使用しない（normalブロックはボスなし）。

星野ルビー・MEMちょ・有馬かな・黒川あかねのc_キャラはそれぞれ1体ずつ個別に召喚されます。各c_キャラは同一トリガーでの複数体同時召喚（summon_count >= 2 かつ summon_interval = 0）を禁止しており、瞬間複数召喚は行いません。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照 | release_key=202512020 のosh系エントリを参照。ファントムは 202509010 |
| MstEnemyOutpost | 新規生成 | HP=100固定、is_damage_invalidation=空、id=vd_osh_normal_00001 |
| MstPage | 新規生成 | id=vd_osh_normal_00001 |
| MstKomaLine | 新規生成 | 3行固定（row=1〜3）、パターン9/6/11 |
| MstAutoPlayerSequence | 新規生成 | 7要素（合計19体、sequence_set_id=vd_osh_normal_00001） |
| MstInGame | 新規生成 | stage_type=vd_normal、content_type=Dungeon、ボスなし、release_key=202604010 |

---

## ID一覧

| テーブル | カラム | 値 |
|---------|--------|-----|
| MstInGame | id | vd_osh_normal_00001 |
| MstAutoPlayerSequence | sequence_set_id | vd_osh_normal_00001 |
| MstPage | id | vd_osh_normal_00001 |
| MstEnemyOutpost | id | vd_osh_normal_00001 |
| MstKomaLine | id（row1） | vd_osh_normal_00001_1 |
| MstKomaLine | id（row2） | vd_osh_normal_00001_2 |
| MstKomaLine | id（row3） | vd_osh_normal_00001_3 |
| MstAutoPlayerSequence | id（elem1） | vd_osh_normal_00001_1 |
| MstAutoPlayerSequence | id（elem2） | vd_osh_normal_00001_2 |
| MstAutoPlayerSequence | id（elem3） | vd_osh_normal_00001_3 |
| MstAutoPlayerSequence | id（elem4） | vd_osh_normal_00001_4 |
| MstAutoPlayerSequence | id（elem5） | vd_osh_normal_00001_5 |
| MstAutoPlayerSequence | id（elem6） | vd_osh_normal_00001_6 |
| MstAutoPlayerSequence | id（elem7） | vd_osh_normal_00001_7 |
