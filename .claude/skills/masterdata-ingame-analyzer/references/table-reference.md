# インゲームマスタデータ テーブルリファレンス

## 目次

1. [テーブルリレーション全体図](#1-テーブルリレーション全体図)
2. [MstInGame](#2-mstingame)
3. [MstAutoPlayerSequence](#3-mstautoplayersequence)
4. [MstEnemyStageParameter](#4-mstenemystageparameter)
5. [MstEnemyOutpost](#5-mstenemyoutpost)
6. [MstPage](#6-mstpage)
7. [MstKomaLine](#7-mstkomaline)
8. [MstAttack / MstAttackElement](#8-mstattack--mstattackelement)

---

## 1. テーブルリレーション全体図

```
MstInGame（バトルステージ中心テーブル）
  ├─ mst_page_id ─────────────────→ MstPage.id
  │                                      └─(1:N) MstKomaLine.mst_page_id
  ├─ mst_enemy_outpost_id ────────→ MstEnemyOutpost.id
  ├─ boss_mst_enemy_stage_parameter_id → MstEnemyStageParameter.id
  └─ mst_auto_player_sequence_set_id ──→ MstAutoPlayerSequence.sequence_set_id
                                              └─ action_value → MstEnemyStageParameter.id（SummonEnemy時）

MstEnemyStageParameter.id
  └─(1:N) 暗黙リレーション（DB外部キーなし）:
       MstAttack.mst_unit_id == MstEnemyStageParameter.id（クライアントが結合）
       ※ 1つのMstEnemyStageParameterに対し attack_kind 別で最大3件
         （Normal / Special / Appearance）
         └─(1:N) MstAttackElement.mst_attack_id
```

### 最終HP計算式

```
最終HP = MstEnemyStageParameter.hp
       × MstInGame.normal_enemy_hp_coef  （ステージ全体倍率）
       × MstAutoPlayerSequence.enemy_hp_coef（シーケンス行ごとの倍率）
```

---

## 2. MstInGame

**CSVパス**: `projects/glow-masterdata/MstInGame.csv`
**用途**: バトルステージ1つ分のインゲーム設定

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `ENABLE` | string | `e` = 有効 |
| `id` | varchar | ステージID（主キー）。命名: `{種別}_{キャラ}_{連番}` |
| `release_key` | bigint | リリースキー |
| `mst_auto_player_sequence_set_id` | varchar | MstAutoPlayerSequence.sequence_set_id |
| `mst_page_id` | varchar | MstPage.id |
| `mst_enemy_outpost_id` | varchar | MstEnemyOutpost.id |
| `mst_defense_target_id` | varchar | 防衛オブジェクトID（NULLなら使用なし） |
| `boss_mst_enemy_stage_parameter_id` | varchar | ボスID（MstEnemyStageParameter.id） |
| `normal_enemy_hp_coef` | decimal | 通常敵HP倍率（基準1.0） |
| `normal_enemy_attack_coef` | decimal | 通常敵攻撃力倍率（基準1.0） |
| `normal_enemy_speed_coef` | decimal | 通常敵速度倍率（基準1.0） |
| `boss_enemy_hp_coef` | decimal | ボスHP倍率（基準1.0） |
| `boss_enemy_attack_coef` | decimal | ボス攻撃力倍率（基準1.0） |
| `boss_enemy_speed_coef` | decimal | ボス速度倍率（基準1.0） |
| `bgm_asset_key` | varchar | 通常BGMアセットキー |
| `boss_bgm_asset_key` | varchar | ボスBGMアセットキー |
| `loop_background_asset_key` | varchar | 背景アセットキー |

### コンテンツ別IDパターン

| コンテンツ | IDパターン |
|-----------|-----------|
| Normalクエスト | `normal_{キャラ}_{5桁}` |
| Hardクエスト | `hard_{キャラ}_{5桁}` |
| VeryHardクエスト | `veryhard_{キャラ}_{5桁}` |
| ランクマッチ（PvP） | `pvp_{識別子}_{連番}` |
| 降臨バトル | `raid_{キャラ}_{5桁}` |
| イベント | `event_{キャラ}_{種別}_{5桁}` |
| 限界チャレンジ | `vd_{作品}_{種別}_{5桁}` |
| チュートリアル | `tutorial`, `tutorial_2` |

### 難易度別の倍率傾向

| 難易度 | normal_enemy_hp_coef | normal_enemy_attack_coef |
|--------|---------------------|------------------------|
| normal | 1.0 | 1.0 |
| hard | 2.0 | 2.0〜3.0 |
| veryhard | 上位を参考に調整 | 上位を参考に調整 |

---

## 3. MstAutoPlayerSequence

**CSVパス**: `projects/glow-masterdata/MstAutoPlayerSequence.csv`
**用途**: 敵の出現タイミング・種類・数・フェーズ制御

### 構造

```
sequence_set_id（= MstInGame.id）
  └─ sequence_group_id（フェーズ: 空=デフォルト, "w1", "w2"...）
        └─ sequence_element_id（行番号: 1, 2, 3... / グループ切替: groupchange_N）
```

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `ENABLE` | string | `e` = 有効 |
| `id` | string | `{sequence_set_id}_{sequence_element_id}` |
| `sequence_set_id` | string | **MstInGame.idと一致** |
| `sequence_group_id` | string | グループID（空=デフォルト） |
| `sequence_element_id` | string | グループ内の要素番号 |
| `condition_type` | enum | 発火条件 |
| `condition_value` | string | 条件の値 |
| `action_type` | enum | アクション種別 |
| `action_value` | string | SummonEnemy時はMstEnemyStageParameter.id |
| `summon_count` | int | 召喚数 |
| `summon_interval` | int | 複数召喚間隔（ms）。0=同時 |
| `summon_position` | float | 召喚X位置。1.7=砦付近 |
| `enemy_hp_coef` | float | HP倍率（MstInGaame倍率に乗算） |
| `enemy_attack_coef` | float | 攻撃力倍率 |
| `enemy_speed_coef` | float | 速度倍率 |
| `aura_type` | enum | 出現オーラ演出 |
| `death_type` | enum | 死亡演出 |
| `override_drop_battle_point` | int | バトルポイント上書き |
| `defeated_score` | int | 撃破スコア（降臨バトル用） |

### condition_type（発火条件）

| 値 | 説明 |
|----|------|
| `InitialSummon` | バトル開始時即発火 |
| `ElapsedTime` | バトル開始からN×100ms後 |
| `ElapsedTimeSinceSequenceGroupActivated` | グループ切替後からの経過時間 |
| `FriendUnitDead` | 指定sequence_element_idの敵が1体倒されたとき |
| `OutpostHpPercentage` | 敵砦HPがN%以下 |
| `EnterTargetKomaIndex` | 指定コマに到達 |

### action_type（アクション）

| 値 | action_value | 説明 |
|----|-------------|------|
| `SummonEnemy` | MstEnemyStageParameter.id | 敵を召喚（最頻出） |
| `SwitchSequenceGroup` | 切替先group_id | フェーズ切替 |
| `SummonPlayerCharacter` | MstUnit.id | プレイヤーキャラ自動召喚 |
| `SummonGimmickObject` | MstInGameGimmickObject.id | ギミック召喚 |

### aura_type（オーラ演出）

| 値 | 用途 |
|----|------|
| `Default` | 通常・雑魚 |
| `Boss` | イベントボス |
| `AdventBoss1` | 降臨wave1 |
| `AdventBoss2` | 降臨wave2〜3 |
| `AdventBoss3` | 降臨最終wave |

---

## 4. MstEnemyStageParameter

**CSVパス**: `projects/glow-masterdata/MstEnemyStageParameter.csv`
**用途**: 敵ユニットのステータス・挙動パラメータ

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `ENABLE` | string | `e` = 有効 |
| `id` | string | 命名: `{e/c}_{キャラID}_{コンテキスト}_{unit_kind}_{color}` |
| `release_key` | bigint | リリースキー |
| `mst_enemy_character_id` | string | キャラアセットID |
| `character_unit_kind` | string | 種別: `Normal`, `Boss`, `AdventBattleBoss` |
| `role_type` | enum | ロール: `None`, `Attack`, `Balance`, `Defense`, `Support`, `Unique`, `Technical`, `Special` |
| `color` | enum | 色: `None`, `Colorless`, `Red`, `Blue`, `Yellow`, `Green` |
| `hp` | int | 基本HP（最終HPは倍率乗算で決まる） |
| `damage_knock_back_count` | int | ノックバック回数 |
| `move_speed` | int | 移動速度（5〜100） |
| `well_distance` | float | 索敵距離（0.11〜0.6） |
| `attack_power` | int | 攻撃力 |
| `attack_combo_cycle` | int | コンボサイクル。0=攻撃しない |
| `mst_unit_ability_id1` | string | アビリティID（空=なし） |
| `drop_battle_point` | int | 撃破時バトルポイント |
| `mstTransformationEnemyStageParameterId` | string | 変身先ID（空=変身なし） |
| `transformationConditionType` | string | 変身条件: `None` / `HpPercentage` |
| `transformationConditionValue` | string | 変身条件値（HP%等） |

### IDプレフィックス

| プレフィックス | mst_enemy_character_id | 用途 |
|--------------|----------------------|------|
| `e_` | `enemy_xxx_xxxxx` | 敵専用キャラ |
| `c_` | `chara_xxx_xxxxx` | プレイヤーキャラが敵として登場 |

### VD専用敵IDパターン

```
e_{キャラID}_vd_{unit_kind}_{color}
c_{キャラID}_vd_{unit_kind}_{color}

例: e_kai_00101_vd_Normal_Yellow
    e_kai_00101_vd_Boss_Yellow
```

**注意**: VD専用敵（`_vd_` を含むID）には対応するMstAttackレコードが存在しない。

---

## 5. MstEnemyOutpost

**CSVパス**: `projects/glow-masterdata/MstEnemyOutpost.csv`
**用途**: 敵タワー（ゲート）のHP・ビジュアル定義

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `id` | varchar | 命名: `{difficulty}_{キャラ}_{5桁}` など |
| `hp` | int | ゲート最大HP |
| `is_damage_invalidation` | varchar | `1`=ダメージ無効（降臨バトル・強化クエスト等） |
| `outpost_asset_key` | varchar | 3Dモデルアセットキー |
| `artwork_asset_key` | varchar | アートワーク画像キー（outpost_asset_keyと排他） |
| `release_key` | int | リリースキー |

### HP規模感

| 種別 | HP目安 |
|------|-------|
| 通常クエスト(normal) | 5,000〜100,000 |
| ハード(hard) | 50,000〜200,000 |
| ベリーハード(veryhard) | 100,000〜300,000 |
| 降臨バトル | 1,000,000（固定、ダメージ無効） |
| VD(限界チャレンジ) | 100（固定） |

---

## 6. MstPage

**CSVパス**: `projects/glow-masterdata/MstPage.csv`
**用途**: フィールドページの識別子（MstKomaLineの親）

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `id` | varchar | ページID（命名: MstInGame.idと同じ値が多い） |
| `release_key` | int | リリースキー |

### 注意

MstPage単体ではフィールドとして機能しない。MstKomaLineを必ず紐付ける必要がある。

---

## 7. MstKomaLine

**CSVパス**: `projects/glow-masterdata/MstKomaLine.csv`
**用途**: フィールドのコマ（格子）配置定義

### 主要カラム

| カラム名 | 型 | 説明 |
|---------|----|----|
| `id` | varchar | `{mst_page_id}_{row番号}` |
| `mst_page_id` | varchar | 親ページID |
| `row` | int | 行番号（1〜N） |
| `height` | float | ライン高さ比率 |
| `koma1_asset_key` | varchar | コマ1のキャラ/オブジェクトアセット（必須） |
| `koma1_width` | float | コマ1の横幅比率（合計1.0になるように） |
| `koma1_effect_type` | varchar | コマ1のエフェクト種別 |
| `koma1_effect_parameter1` | int | エフェクトパラメータ1 |
| `koma2_*` 〜 `koma4_*` | 各種 | コマ2〜4（オプション、空なら未使用） |

### KomaEffectType（コマエフェクト）

| 値 | 説明 |
|----|------|
| `None` | エフェクトなし |
| `AttackPowerUp` | 攻撃力アップ |
| `AttackPowerDown` | 攻撃力ダウン |
| `MoveSpeedUp` | 移動速度アップ |
| `SlipDamage` | スリップダメージ |
| `Gust` | 吹き飛ばし |
| `Poison` | 毒 |
| `Darkness` | 暗闇 |
| `Burn` | 燃焼 |
| `Stun` | スタン |
| `Freeze` | 凍結 |
| `Weakening` | 弱体化 |

---

## 8. MstAttack / MstAttackElement

**CSVパス**: `projects/glow-masterdata/MstAttack.csv` / `MstAttackElement.csv`
**用途**: 敵の攻撃詳細定義（VD敵には存在しない）

### リレーション（暗黙）

```
MstEnemyStageParameter (1)
  └─(1:N) MstAttack （DB外部キーなし。クライアントが mst_unit_id で結合）
            ※ 1つのMstEnemyStageParameterに対し attack_kind 別で最大3件
            ├─ attack_kind = Normal      → 通常攻撃
            ├─ attack_kind = Special     → 必殺技
            └─ attack_kind = Appearance → 登場演出
                  └─(1:N) MstAttackElement.mst_attack_id
```

クライアント (`MasterDataRepository.cs`) のコード:
```csharp
var normalAttack     = GetMstAttackModel(id, AttackKind.Normal);
var specialAttack    = GetMstAttackModel(id, AttackKind.Special);
var appearanceAttack = GetMstAttackModel(id, AttackKind.Appearance);
// 見つからない場合は MstAttackModel.Empty が返る
```

### MstAttack 主要カラム

| カラム名 | 説明 |
|---------|------|
| `id` | レコードID |
| `mst_unit_id` | MstEnemyStageParameter.id に一致させる |
| `attack_kind` | `Normal` / `Special` / `Appearance` |

### 注意事項

- VD敵（`_vd_` を含むID）はMstAttackが0件 → クライアントは `AttackData.Empty` を使用
- 攻撃制御は `MstEnemyStageParameter.attack_power` + `attack_combo_cycle` で代替される
- VD敵用のMstAttack/MstAttackElementは作成不要
