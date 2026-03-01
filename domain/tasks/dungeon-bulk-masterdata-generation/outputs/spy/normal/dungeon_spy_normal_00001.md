# dungeon_spy_normal_00001 詳細解説

> 生成日: 2026-03-01
> コンテンツ種別: dungeon（限界チャレンジ）/ ブロック種別: normal（通常ブロック）
> シリーズ: SPY×FAMILY（spy）

---

## 1. 概要

限界チャレンジ（dungeon）の SPY×FAMILY 通常ブロック。複数ブロックをつなげて1コンテンツを構成する「ブロック」単位のインゲーム。

| 項目 | 値 |
|------|-----|
| インゲームID | `dungeon_spy_normal_00001` |
| BGM | `SSE_SBG_003_002` |
| 背景アセット | `spy_00005` |
| 敵砦HP | **100**（dungeon_normal 固定値） |
| ダメージ無効 | なし（砦への通常ダメージ有効） |
| ボス | なし |
| 雑魚敵種類 | 2種類（enemy_spy_00001 / enemy_spy_00101） |
| 総召喚数 | 10体（敵A×6、敵B×4） |
| コマ行数 | **3行**（dungeon_normal 固定値） |

---

## 2. 関連テーブル設定

### MstInGame

| カラム | 値 |
|--------|-----|
| id | `dungeon_spy_normal_00001` |
| mst_auto_player_sequence_set_id | `dungeon_spy_normal_00001` |
| mst_page_id | `dungeon_spy_normal_00001` |
| mst_enemy_outpost_id | `dungeon_spy_normal_00001` |
| boss_mst_enemy_stage_parameter_id | （空） |
| boss_count | `0` |
| normal_enemy_hp_coef | `1` |
| normal_enemy_attack_coef | `1` |
| normal_enemy_speed_coef | `1` |
| bgm_asset_key | `SSE_SBG_003_002` |
| loop_background_asset_key | `spy_00005` |

> 全coef=1：MstEnemyStageParameterの値がそのまま最終値になる。

### MstEnemyOutpost

| カラム | 値 |
|--------|-----|
| id | `dungeon_spy_normal_00001` |
| hp | **100**（dungeon_normal 固定値） |
| is_damage_invalidation | （空）ダメージ有効 |

### MstPage + MstKomaLine（コマ構成）

dungeon_normal は **3行固定**。すべてのコマにエフェクトなし（`None`）。

| row | height | レイアウト | koma1 | koma1_width | koma2 | koma2_width | エフェクト |
|-----|--------|-----------|-------|------------|-------|------------|----------|
| 1 | 0.55 | 6（0.5/0.5） | spy_00005 | 0.5 | spy_00005 | 0.5 | None/None |
| 2 | 0.55 | 3（0.4/0.6） | spy_00005 | 0.4 | spy_00005 | 0.6 | None/None |
| 3 | 0.55 | 1（1.0） | spy_00005 | 1.0 | — | — | None |

- 行1〜3すべて `height=0.55`（既存 normal_spy 系と同パターン）
- コマ数は行ごとに 2コマ / 2コマ / 1コマ の構成

---

## 3. 使用する敵パラメータ一覧

### カラム解説

| カラム | 説明 |
|--------|------|
| `hp` | 基準HP（全coef=1のため最終HP = この値） |
| `attack_power` | 攻撃力（全coef=1のため最終値 = この値） |
| `move_speed` | 移動速度（35〜50が「普通」、50〜80が「速い」） |
| `well_distance` | 索敵距離（敵が攻撃を開始する射程） |
| `attack_combo_cycle` | 攻撃コンボ数（1=シンプル攻撃） |
| `mst_unit_ability_id1` | 必殺ワザ（空=なし） |
| `drop_battle_point` | 撃破時のバトルポイント |

### 全パラメータ表

| 識別子 | mst_enemy_character_id | HP | 攻撃力 | 速度 | 索敵距離 | コンボ | 必殺 | BP |
|--------|----------------------|-----|-------|-----|---------|------|------|-----|
| `e_spy_00001_spy_dungeon_Normal_Colorless` | enemy_spy_00001 | 1,000 | 5,000 | 45 | 0.35 | 1 | なし | 100 |
| `e_spy_00101_spy_dungeon_Normal_Colorless` | enemy_spy_00101 | 1,000 | 5,000 | 42 | 0.25 | 1 | なし | 100 |

### 特性解説

**敵A（enemy_spy_00001）**
- SPY×FAMILYで使用率1位（既存データで116回）の主力雑魚敵
- move_speed=45（普通〜少し速い）、well_distance=0.35（やや広い索敵）
- 本バッチで6体召喚（全体の60%）

**敵B（enemy_spy_00101）**
- SPY×FAMILYで使用率2位（29回）のサブ雑魚敵
- move_speed=42（少し速い）、well_distance=0.25（狭め索敵）
- 敵Aより索敵距離が短く、近づかないと攻撃しない
- 本バッチで4体召喚（全体の40%）

> ⚠️ attack_power=5,000 は既存通常ステージの敵（attack_power=50〜100）より大幅に高い設定。dungeon専用の難易度設計として意図的に高く設定されている。

---

## 4. グループ構造の全体フロー（Mermaid）

シーケンスはデフォルトグループのみ（グループ切り替えなし）のシンプル構成。

```mermaid
flowchart LR
    START([バトル開始]) --> seq1
    seq1["① ElapsedTime(500)\n敵A × 3体"]
    seq2["② ElapsedTime(2000)\n敵B × 2体"]
    seq3["③ FriendUnitDead(2)\n敵A × 2体"]
    seq4["④ ElapsedTime(5000)\n敵B × 2体"]
    seq5["⑤ FriendUnitDead(5)\n敵A × 1体"]
    END([バトル終了])

    seq1 --> seq2
    seq2 --> seq3
    seq3 --> seq4
    seq4 --> seq5
    seq5 --> END

    style START fill:#6b7280,color:#fff
    style seq1 fill:#6b7280,color:#fff
    style seq2 fill:#6b7280,color:#fff
    style seq3 fill:#6b7280,color:#fff
    style seq4 fill:#6b7280,color:#fff
    style seq5 fill:#6b7280,color:#fff
    style END fill:#374151,color:#fff
```

---

## 5. 全5行の詳細データ

### デフォルトグループ（sequence_group_id = 空）

| # | id | condition_type | condition_value | action_value | 体数 | HP倍率 | ATK倍率 | SPD倍率 |
|---|-----|---------------|----------------|-------------|------|-------|--------|--------|
| 1 | `dungeon_spy_normal_00001_1` | ElapsedTime | 500（0.5秒後） | e_spy_00001…Colorless | **3体** | ×1 | ×1 | ×1 |
| 2 | `dungeon_spy_normal_00001_2` | ElapsedTime | 2000（2.0秒後） | e_spy_00101…Colorless | **2体** | ×1 | ×1 | ×1 |
| 3 | `dungeon_spy_normal_00001_3` | FriendUnitDead | 2（累計2体撃破） | e_spy_00001…Colorless | **2体** | ×1 | ×1 | ×1 |
| 4 | `dungeon_spy_normal_00001_4` | ElapsedTime | 5000（5.0秒後） | e_spy_00101…Colorless | **2体** | ×1 | ×1 | ×1 |
| 5 | `dungeon_spy_normal_00001_5` | FriendUnitDead | 5（累計5体撃破） | e_spy_00001…Colorless | **1体** | ×1 | ×1 | ×1 |

**全行共通設定**: aura_type=`Default` / death_type=`Normal` / move_start_condition_type=`None`（召喚即移動）

---

## 6. グループ切り替えまとめ

グループ切り替えなし。単一のデフォルトグループのみで構成されたシンプル設計。

---

## 7. スコア体系

| 項目 | 値 |
|------|-----|
| defeated_score | 0（全行）|
| override_drop_battle_point | 空（MstEnemyStageParameterの値を使用）|
| 敵A撃破時BP | 100 |
| 敵B撃破時BP | 100 |
| 最大BP（全体撃破） | 1,000（10体 × 100BP）|

スコアアタック要素はなく、通常の砦破壊型バトルとして機能する。

---

## 8. この設定から読み取れる設計パターン

### 1. dungeon_normal の固定値を遵守
- 敵砦HP=100、コマ行数=3行 は dungeon_normal の仕様固定値
- 全coef=1 で MstEnemyStageParameter の値がそのまま最終ステータスになる

### 2. 時間トリガー × 撃破トリガーの組み合わせ
- 序盤（行1・2）: ElapsedTime で自動出現 → バトル開始直後から敵が押し寄せる
- 中盤（行3・4）: FriendUnitDead(2) と ElapsedTime(5000) の並行監視 → 撃破が進むと追加敵が出現
- 終盤（行5）: FriendUnitDead(5) → 最後の敵は5体倒してから登場

### 3. 2種の敵を交互に配置する設計
- 行1→行2→行3→行4→行5 の順に 敵A・敵B・敵A・敵B・敵A と交互に出現
- 索敵距離の異なる2種（0.35 vs 0.25）で攻撃タイミングに変化をもたせる

### 4. 雑魚敵のみで構成されたシンプルブロック
- ボスなし・グループ切り替えなし・ギミックなし
- dungeon_normal はフロア間のつなぎ役として機能するため、シンプルな構成が適切

### 5. SPY×FAMILY 主力敵2体を活用
- enemy_spy_00001（既存使用率1位）+ enemy_spy_00101（同2位）の組み合わせ
- 既存コンテンツで実績のある敵を使用しており、ビジュアル的な一貫性を確保

### 6. 高い攻撃力設定（dungeon専用難易度）
- attack_power=5,000 は通常ステージの敵（50〜100）の約50〜100倍
- 限界チャレンジのコンセプト「プレイヤーへの高い挑戦難易度」を反映
