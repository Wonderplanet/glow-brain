# vd_jig_normal_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

地獄楽の世界観を反映したノーマルブロックです。門神（Green属性・防御ロール）と極楽蝶（Green属性・攻撃ロール）という地獄楽固有の敵2種類に加え、ファントム（Colorless属性・攻撃ロール）が時間差で出現します。門神は高いノックバック数（2）を持ち壁となる役割、極楽蝶は速いノックバック数（4）と機動力で突破を狙う構成です。3波構成（0.25秒後・1.5秒後・3.0秒後）で合計18体が登場し、地獄楽作品ならではの「島」の雰囲気を活かした密度感ある戦闘体験を提供します。「最低15体以上」の要件を満たしつつ、作品世界観の敵キャラクターを前面に押し出した設計としています。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| enemy_jig_00001 | 門神 | 雑魚 | Green属性・防御ロール・ノックバック2 |
| enemy_jig_00401 | 極楽蝶 | 雑魚 | Green属性・攻撃ロール・ノックバック4 |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202509010)
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）
> ただし、今回のバッチでは release_key=202604010 で新規追加する

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| e_jig_00001_vd_Normal_Green | 門神 | Normal | Defense | Green | 3,500 | 50 | 31 | 0.21 | 2 | 1 | 150 |
| e_jig_00401_vd_Normal_Green | 極楽蝶 | Normal | Attack | Green | 3,000 | 100 | 32 | 0.24 | 4 | 1 | 100 |
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

各行独立ランダム抽選（12パターンから）の結果:

```mermaid
block-beta
  columns 3
  A["row=1 / koma1\n幅=0.33\neffect: None"]:1 B["row=1 / koma2\n幅=0.34\neffect: None"]:1 C["row=1 / koma3\n幅=0.33\neffect: None"]:1
  columns 2
  D["row=2 / koma1\n幅=0.50\neffect: None"]:1 E["row=2 / koma2\n幅=0.50\neffect: None"]:1
  columns 3
  F["row=3 / koma1\n幅=0.40\neffect: None"]:1 G["row=3 / koma2\n幅=0.20\neffect: None"]:1 H["row=3 / koma3\n幅=0.40\neffect: None"]:1
```

| row | height | 選択パターン | コマ数 | 各幅 | 幅合計 |
|-----|--------|------------|-------|------|--------|
| 1 | 0.33 | パターン7「3等分」 | 3コマ | 0.33, 0.34, 0.33 | 1.0 |
| 2 | 0.33 | パターン6「2等分」 | 2コマ | 0.50, 0.50 | 1.0 |
| 3 | 0.34 | パターン11「中央狭い」 | 3コマ | 0.40, 0.20, 0.40 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> W1["ElapsedTime 250ms\n門神 ×6\nDefault"]
  W1 --> W2["ElapsedTime 1500ms\n極楽蝶 ×6\nDefault"]
  W2 --> W3["ElapsedTime 3000ms\nファントム ×6\nDefault"]
  W3 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数 |
|------|-------------|---|---|---------|
| 1 | ElapsedTime 250ms | 門神 (e_jig_00001_vd_Normal_Green) | 6 | 6 |
| 2 | ElapsedTime 1500ms | 極楽蝶 (e_jig_00401_vd_Normal_Green) | 6 | 12 |
| 3 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 6 | 18 |

合計: **18体**（要件「最低15体以上」を満たす）

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波 | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|---|---|---------|---------|------|----------|----------|-------|
| 1 | 門神 | 3,500 | 1.0 | 3,500 | 50 | 1.0 | 50 |
| 2 | 極楽蝶 | 3,000 | 1.0 | 3,000 | 100 | 1.0 | 100 |
| 3 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |

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

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | 全敵キャラ（ノーマルブロックはボスなし、全行Default） |

---

### 敵キャラ召喚アニメーション

全キャラ `SummonEnemy` アクションによるElapsedTime時間差召喚。InitialSummonは使用しない（normalブロックはボスなし）。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 新規生成 | release_key=202604010 で追加（既存IDと同一値だが新バッチとして追加） |
| MstEnemyOutpost | 新規生成 | HP=100固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_jig_normal_00001 |
| MstKomaLine | 新規生成 | 3行固定（row1-3） |
| MstAutoPlayerSequence | 新規生成 | 3要素（計18体） |
| MstInGame | 新規生成 | stage_type=vd_normal、ボスなし |
