# vd_mag_boss_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

株式会社マジルミエの世界観を反映したボスブロックです。ボスとして「新人魔法少女 桜木 カナ」（Green属性・攻撃ロール）が敵ゲート前に降臨します。カナは駆け出しの魔法少女らしい積極的な姿勢でプレイヤーに挑みかかる設計です。ボスを倒すまで敵ゲートへのダメージが無効であるため、プレイヤーはボス撃破を最優先課題として戦略を組み立てる必要があります。ボス登場から0.5秒後に冷却系怪異（つらら）が2体出現してフィールドに圧力を加え、さらに3秒後にファントムが3体追加出現することで継続的な緊張感を演出します。フロア係数 1.00 を基準とした設計で、ボスの「1ダメージ受けたら進軍開始」仕様により、ゲート防衛への緊迫感を高めます。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| chara_mag_00001 | 新人魔法少女 桜木 カナ | ボス | Green属性・攻撃ロール |
| enemy_mag_00101 | つらら（冷却系怪異） | 雑魚 | Green属性・攻撃ロール |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv`
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| c_mag_00001_vd_Boss_Green | 新人魔法少女 桜木 カナ | Boss | Attack | Green | 320,000 | 1,200 | 45 | 0.4 | 2 | 5 | 10 |
| e_mag_00101_vd_Normal_Green | つらら（冷却系怪異） | Normal | Attack | Green | 20,000 | 400 | 100 | 0.11 | 1 | 1 | 10 |
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

ボスブロックは1行1コマ固定。

```mermaid
block-beta
  columns 1
  A["row=1 / koma1\n幅=1.00\neffect: None"]:1
```

| row | height | コマ数 | koma1_width | 幅合計 |
|-----|--------|-------|-------------|--------|
| 1 | 1.0 | 1コマ | 1.0 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> B1["InitialSummon\n新人魔法少女 桜木 カナ ×1\nsummon_position=1.7\nBoss オーラ"]
  B1 --> W1["ElapsedTime 500ms\nつらら ×2\nDefault"]
  W1 --> W2["ElapsedTime 3000ms\nファントム ×3\nDefault"]
  W2 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数/召喚位置 |
|------|-------------|---|---|-----------------|
| 1 | InitialSummon | 新人魔法少女 桜木 カナ (c_mag_00001_vd_Boss_Green) | 1 | 1 / summon_position=1.7 |
| 2 | ElapsedTime 500ms | つらら (e_mag_00101_vd_Normal_Green) | 2 | 3 |
| 3 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 3 | 6 |

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波/フェーズ | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|-----------|---|---------|---------|------|----------|----------|-------|
| InitialSummon | 新人魔法少女 桜木 カナ | 320,000 | 1.0 | 320,000 | 1,200 | 1.0 | 1,200 |
| ElapsedTime 500ms | つらら | 20,000 | 1.0 | 20,000 | 400 | 1.0 | 400 |
| ElapsedTime 3000ms | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | （空） | VDの背景切り替えはゲームロジック側で管理 |
| フロア0以上 | koma_background_vd_00002 | クライアント側でフロア係数に応じて切り替え |
| フロア20以上 | koma_background_vd_00004 | 同上 |
| フロア40以上 | koma_background_vd_00006 | 同上 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | SSE_SBG_003_004 | ボスブロック用BGM |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Boss | 新人魔法少女 桜木 カナ（InitialSummon時） |
| Default | つらら、ファントム（雑魚2種） |

---

### 敵キャラ召喚アニメーション

ボス（新人魔法少女 桜木 カナ）は `InitialSummon` で `summon_position=1.7`（ゲート付近）に配置。1ダメージ受けると進軍を開始する（`move_start_condition_type=Damage, move_start_condition_value=1`）。
雑魚キャラ（つらら・ファントム）は `SummonEnemy` アクションによるElapsedTime時間差召喚。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照 | generated/ファントムマスター/ の既存データ使用 |
| MstEnemyOutpost | 新規生成 | HP=1,000固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_mag_boss_00001 |
| MstKomaLine | 新規生成 | 1行固定（row=1, koma1_width=1.0） |
| MstAutoPlayerSequence | 新規生成 | 3要素（ボス1体+雑魚5体） |
| MstInGame | 新規生成 | ボスあり（boss_mst_enemy_stage_parameter_id=c_mag_00001_vd_Boss_Green） |
