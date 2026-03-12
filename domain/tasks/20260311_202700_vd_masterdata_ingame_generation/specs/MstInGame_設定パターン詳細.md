# MstInGame 設定パターン詳細

> 作成日: 2026-03-12
> 対象タスク: 限界チャレンジ（VD）インゲームマスタデータ生成
> リリースキー: 202604010

---

## 1. 概要

`MstInGame` は**バトルステージ1つ分のインゲーム設定を管理する中心テーブル**。

ステージに紐づく BGM・背景・タワーアセットの指定、敵タワー・防衛オブジェクトの参照、オートプレイヤーシーケンスの参照、そして通常敵・ボス敵のステータス倍率（HP・攻撃・スピード）を定義する。

VD（限界チャレンジ）では「上位テーブルである `MstDungeonBlock`（`mst_dungeon_blocks`）から `mst_in_game_id` で参照される」構造を取る。ブロック種別（Normal/Boss）は `MstDungeonBlock.block_type` で管理され、MstInGame 自体にブロック種別カラムは存在しない。

```
MstDungeonBlock（上位テーブル）
  ├─ block_type: enum('Normal','Rare','Boss')  ← ブロック種別はここで管理
  └─ mst_in_game_id → MstInGame.id
        ├─ mst_page_id                          → MstPage
        │    └─(1:N) MstKomaLine.mst_page_id
        ├─ mst_enemy_outpost_id                 → MstEnemyOutpost
        ├─ boss_mst_enemy_stage_parameter_id    → MstEnemyStageParameter（ボス敵パラメータ）
        └─ mst_auto_player_sequence_set_id      → MstAutoPlayerSequence.sequence_set_id
               └─ action_value（SummonEnemy時） → MstEnemyStageParameter.id
```

---

## 2. 全カラム一覧

### mst_in_games（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト | VD固定値 / 設定方針 |
|---------|----|---------|-----------|--------------------|
| `ENABLE` | string | - | - | `e`（常に `e` のみ） |
| `id` | varchar(255) | 不可 | - | `{block_id}`（例: `vd_kai_normal_00001`） |
| `release_key` | bigint | 不可 | `1` | `202604010` |
| `mst_auto_player_sequence_id` | varchar(255) | 不可 | `""` | **常に空文字**（レガシーカラム。詳細は後述） |
| `mst_auto_player_sequence_set_id` | varchar(255) | 不可 | - | `{block_id}`（MstInGame.id と同値） |
| `bgm_asset_key` | varchar(255) | 不可 | `""` | Normal: `SSE_SBG_003_010`、Boss: `SSE_SBG_003_004` |
| `boss_bgm_asset_key` | varchar(255) | 不可 | `""` | 常に空文字（VDはBGM切り替えなし） |
| `loop_background_asset_key` | varchar(255) | 不可 | `""` | Normal: 原則空文字、Boss: 作品IDに対応したキー（例: `kai_00001`）または空文字 |
| `player_outpost_asset_key` | varchar(255) | 不可 | `""` | 常に空文字（デフォルトアセット使用） |
| `mst_page_id` | varchar(255) | 不可 | `""` | `{block_id}`（MstPage.id と一致） |
| `mst_enemy_outpost_id` | varchar(255) | 不可 | `""` | `{block_id}`（MstEnemyOutpost.id と一致） |
| `mst_defense_target_id` | varchar(255) | **許容** | NULL | VDでは常に `__NULL__`（防衛オブジェクト不使用） |
| `boss_mst_enemy_stage_parameter_id` | varchar(255) | 不可 | `""` | Normal: 原則空文字（ただしgom/magは設定あり）、Boss: `c_{キャラID}_vd_Boss_{色}` |
| `boss_count` | int | **許容** | NULL | 常に `__NULL__`（未使用） |
| `normal_enemy_hp_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `normal_enemy_attack_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `normal_enemy_speed_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `boss_enemy_hp_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `boss_enemy_attack_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `boss_enemy_speed_coef` | decimal(10,2) | 不可 | - | `1.0`（VD全ブロック固定） |
| `result_tips.ja` | varchar(255) | 不可 | `""` | 空文字（VDでは未設定） |
| `description.ja` | text | 不可 | - | 空文字（VDでは未設定） |

### mst_in_games_i18n（多言語テーブル）

CSV上は `result_tips.ja` / `description.ja` として本体CSVにフラット化されて管理される。

| カラム名 | 型 | NULL許容 | デフォルト | VD固定値 |
|---------|----|---------|-----------|---------|
| `id` | varchar(255) | 不可 | - | `{block_id}_ja` などの一意ID |
| `release_key` | bigint | 不可 | `1` | `202604010` |
| `mst_in_game_id` | varchar(255) | 不可 | - | 対応する MstInGame.id |
| `language` | enum('ja') | 不可 | - | `ja`（現状唯一の対応言語） |
| `result_tips` | varchar(255) | 不可 | `""` | 空文字 |
| `description` | text | 不可 | - | 空文字 |

---

## 3. 他テーブルとのリレーション構造

```
MstInGame
│
├─[mst_page_id] → MstPage.id
│     └─(1:N) MstKomaLine.mst_page_id
│           （コマ行配置の設定）
│
├─[mst_enemy_outpost_id] → MstEnemyOutpost.id
│     （敵タワーのHP・アセット設定）
│
├─[mst_defense_target_id] → MstDefenseTarget.id  ★VDでは常にNULL
│     （防衛オブジェクト: VDでは不使用）
│
├─[boss_mst_enemy_stage_parameter_id] → MstEnemyStageParameter.id
│     （ボス敵のパラメータ: Normalブロックは原則空文字）
│
└─[mst_auto_player_sequence_set_id] → MstAutoPlayerSequence.sequence_set_id
      （同一ステージに属する全シーケンス行のグループキー）
      └─ SummonEnemy の action_value → MstEnemyStageParameter.id
```

### リレーション整合性ルール（重要）

| ルール | 内容 |
|--------|------|
| **mst_auto_player_sequence_set_id = MstInGame.id** | MstAutoPlayerSequence.sequence_set_id は必ず対応する MstInGame.id と同じ値を使用する |
| **mst_page_id の連携** | MstPage.id・MstKomaLine.mst_page_id・MstInGame.mst_page_id はすべて同じ値（block_id）を使用 |
| **SummonEnemy の action_value** | MstAutoPlayerSequence で action_type=SummonEnemy の場合、action_value は MstEnemyStageParameter.id を指定する |
| **boss_mst_enemy_stage_parameter_id の二重設定** | Bossブロックでは MstInGame.boss_mst_enemy_stage_parameter_id と MstAutoPlayerSequence の InitialSummon（action_value）に同じIDを設定する（二重設定が必須） |

---

## 4. content_type / stage_type について（注意事項）

### MstInGame 本体には content_type / stage_type カラムが存在しない

DBスキーマ（`mst_in_games`テーブル）および生成CSV（MstInGame.csv）のカラム定義に `content_type` / `stage_type` は**含まれない**。

ブロック種別（Normal/Boss）は上位テーブル `MstDungeonBlock.block_type`（enum: `'Normal'`, `'Rare'`, `'Boss'`）で管理される。

### design.md での content_type / stage_type 記載について

各ブロックの `design.md` にはMstInGameのテーブル設定値サマリとして以下のような記載がある場合がある:

| カラム | 値（Normal例） | 値（Boss例） |
|-------|--------------|-------------|
| content_type | `Dungeon` | `Dungeon` |
| stage_type | `vd_normal` | `vd_boss` |

これは設計意図を示す参考情報として記載されたものであり、実際のCSVカラムには存在しない。

### クライアント実装における InGameContentType

C# クライアントの `InGameContentType` enum には以下の値が存在する:

```csharp
public enum InGameContentType
{
    Stage,
    AdventBattle,
    Pvp,
    Dungeon,   // ← VD（限界チャレンジ）に対応
}
```

VD（限界チャレンジ）は `Dungeon` として識別される。

---

## 5. BGM・背景アセットの設定パターン

### BGMアセットキー（bgm_asset_key）

| ブロック種別 | bgm_asset_key | 備考 |
|------------|--------------|------|
| **Normalブロック** | `SSE_SBG_003_010` | VD Normalブロック固定BGM |
| **Bossブロック** | `SSE_SBG_003_004` | VD Bossブロック固定BGM |

### ボスBGMアセットキー（boss_bgm_asset_key）

VDでは **常に空文字**。BGM切り替えは行わない（すべてのブロックでボスBGM切り替えなし）。

### 背景アセットキー（loop_background_asset_key）

VDの背景はクライアント側のゲームロジックがフロア深度に応じて自動切り替えを行うため、CSV側の設定は最小限。

| ブロック種別 | 設定方針 | 実例 |
|------------|---------|------|
| **Normalブロック** | 原則**空文字** | kai, spy, aya, chi, gom, hut, kim, osh, sur, you, yuw = 空文字 |
| **Normalブロック（例外）** | 作品対応アセットキーを設定 | jig_normal: `jig_00002`、mag_normal: `mag_00004` |
| **Bossブロック** | 作品対応アセットキーを設定（または空文字） | dan_boss: `dan_00001`、kai_boss: `kai_00001`、mag_boss: `mag_00004`、tak_boss: `tak_00001`、osh_boss: `osh_00001`、mag: `mag_00004`、その他: 空文字 |

**クライアント側の背景切り替えロジック（フロア係数）:**
- フロア0以上: `koma_background_vd_00001`
- フロア20以上: `koma_background_vd_00003`
- フロア40以上: `koma_background_vd_00005`

### プレイヤータワーアセットキー（player_outpost_asset_key）

VDを含む全ブロックで**常に空文字**（デフォルトアセット使用）。

---

## 6. ステータス倍率（*_coef）の設定ガイド

### VDでの設定値

VD（限界チャレンジ）では**全ブロック・全倍率カラムを `1.0` に固定**する。

| カラム | VD設定値 | 意味 |
|--------|---------|------|
| `normal_enemy_hp_coef` | `1.0` | 通常敵HP倍率（基本値そのまま） |
| `normal_enemy_attack_coef` | `1.0` | 通常敵攻撃倍率（基本値そのまま） |
| `normal_enemy_speed_coef` | `1.0` | 通常敵移動速度倍率（基本値そのまま） |
| `boss_enemy_hp_coef` | `1.0` | ボス敵HP倍率（基本値そのまま） |
| `boss_enemy_attack_coef` | `1.0` | ボス敵攻撃倍率（基本値そのまま） |
| `boss_enemy_speed_coef` | `1.0` | ボス敵移動速度倍率（基本値そのまま） |

### 他コンテンツとの比較（参考）

| コンテンツ | normal_hp_coef | normal_atk_coef | 用途 |
|----------|--------------|----------------|------|
| normalステージ | 1.0 | 1.0 | 基準難易度 |
| hardステージ | 2.0 | 2.0〜3.0 | 高難易度 |
| VD（Normalブロック） | **1.0** | **1.0** | 敵パラメータで難易度調整 |
| VD（Bossブロック） | **1.0** | **1.0** | 敵パラメータで難易度調整 |

VDでは難易度調整をステータス倍率ではなく `MstEnemyStageParameter` の基礎ステータス値（hp / attack_power / move_speed）によって行う。

---

## 7. VD固有の設定値一覧

### VD Normalブロックの典型構成

```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,...
e,vd_{作品ID}_normal_{連番},,vd_{作品ID}_normal_{連番},SSE_SBG_003_010,,,,...
```

| カラム | 値 |
|-------|---|
| `ENABLE` | `e` |
| `id` | `vd_{作品ID}_normal_{連番}` |
| `mst_auto_player_sequence_id` | `""`（**常に空文字**・レガシーカラム） |
| `mst_auto_player_sequence_set_id` | `vd_{作品ID}_normal_{連番}` |
| `bgm_asset_key` | `SSE_SBG_003_010` |
| `boss_bgm_asset_key` | `""` |
| `loop_background_asset_key` | `""` または作品対応アセットキー |
| `player_outpost_asset_key` | `""` |
| `mst_page_id` | `vd_{作品ID}_normal_{連番}` |
| `mst_enemy_outpost_id` | `vd_{作品ID}_normal_{連番}` |
| `mst_defense_target_id` | `__NULL__` |
| `boss_mst_enemy_stage_parameter_id` | `""` |
| `boss_count` | `__NULL__` |
| `normal_enemy_hp_coef` | `1.0` |
| `normal_enemy_attack_coef` | `1.0` |
| `normal_enemy_speed_coef` | `1.0` |
| `boss_enemy_hp_coef` | `1.0` |
| `boss_enemy_attack_coef` | `1.0` |
| `boss_enemy_speed_coef` | `1.0` |
| `release_key` | `202604010` |
| `result_tips.ja` | `""` |
| `description.ja` | `""` |

**実データ例（vd_kai_normal_00001）:**
```csv
e,vd_kai_normal_00001,,vd_kai_normal_00001,SSE_SBG_003_010,,,,vd_kai_normal_00001,vd_kai_normal_00001,__NULL__,,__NULL__,1.0,1.0,1.0,1.0,1.0,1.0,202604010,,
```

### VD Bossブロックの典型構成

```
ENABLE,id,mst_auto_player_sequence_id,mst_auto_player_sequence_set_id,...
e,vd_{作品ID}_boss_{連番},,vd_{作品ID}_boss_{連番},SSE_SBG_003_004,,...
```

| カラム | 値 |
|-------|---|
| `ENABLE` | `e` |
| `id` | `vd_{作品ID}_boss_{連番}` |
| `mst_auto_player_sequence_id` | `""`（**常に空文字**・レガシーカラム） |
| `mst_auto_player_sequence_set_id` | `vd_{作品ID}_boss_{連番}` |
| `bgm_asset_key` | `SSE_SBG_003_004` |
| `boss_bgm_asset_key` | `""` |
| `loop_background_asset_key` | `""` または作品対応アセットキー（例: `kai_00001`, `dan_00001`） |
| `player_outpost_asset_key` | `""` |
| `mst_page_id` | `vd_{作品ID}_boss_{連番}` |
| `mst_enemy_outpost_id` | `vd_{作品ID}_boss_{連番}` |
| `mst_defense_target_id` | `__NULL__` |
| `boss_mst_enemy_stage_parameter_id` | `c_{キャラID}_vd_Boss_{色}` |
| `boss_count` | `__NULL__` |
| `normal_enemy_hp_coef` | `1.0` |
| `normal_enemy_attack_coef` | `1.0` |
| `normal_enemy_speed_coef` | `1.0` |
| `boss_enemy_hp_coef` | `1.0` |
| `boss_enemy_attack_coef` | `1.0` |
| `boss_enemy_speed_coef` | `1.0` |
| `release_key` | `202604010` |
| `result_tips.ja` | `""` |
| `description.ja` | `""` |

**実データ例（vd_kai_boss_00001）:**
```csv
e,vd_kai_boss_00001,vd_kai_boss_00001,vd_kai_boss_00001,SSE_SBG_003_004,,kai_00001,,vd_kai_boss_00001,vd_kai_boss_00001,__NULL__,c_kai_00002_vd_Boss_Yellow,__NULL__,1.0,1.0,1.0,1.0,1.0,1.0,202604010,,
```

### 全ブロック実データ一覧

#### Normalブロック

| ブロックID | bgm_asset_key | loop_background_asset_key | boss_mst_enemy_stage_parameter_id |
|-----------|--------------|--------------------------|----------------------------------|
| vd_aya_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_chi_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_dan_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_gom_normal_00001 | SSE_SBG_003_010 | （空） | c_gom_00001_vd_Boss_Yellow ※1 |
| vd_hut_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_jig_normal_00001 | SSE_SBG_003_010 | jig_00002 | （空） |
| vd_kai_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_kim_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_mag_normal_00001 | SSE_SBG_003_010 | mag_00004 | c_mag_00001_vd_Boss_Green ※1 |
| vd_osh_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_spy_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_sum_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_sur_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_tak_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_you_normal_00001 | SSE_SBG_003_010 | （空） | （空） |
| vd_yuw_normal_00001 | SSE_SBG_003_010 | （空） | （空） |

※1 gom・mag の Normalブロックにはボスパラメータが設定されている（設計意図による例外）

#### Bossブロック

| ブロックID | bgm_asset_key | loop_background_asset_key | boss_mst_enemy_stage_parameter_id |
|-----------|--------------|--------------------------|----------------------------------|
| vd_aya_boss_00001 | SSE_SBG_003_004 | （空） | c_aya_00101_vd_Boss_Green |
| vd_chi_boss_00001 | SSE_SBG_003_004 | （空） | c_chi_00002_vd_Boss_Blue |
| vd_dan_boss_00001 | SSE_SBG_003_004 | dan_00001 | c_dan_00301_vd_Boss_Red |
| vd_gom_boss_00001 | SSE_SBG_003_004 | （空） | c_gom_00001_vd_Boss_Yellow |
| vd_hut_boss_00001 | SSE_SBG_003_004 | （空） | c_hut_00101_vd_Boss_Yellow |
| vd_jig_boss_00001 | SSE_SBG_003_004 | （空） | c_jig_00601_vd_Boss_Green |
| vd_kai_boss_00001 | SSE_SBG_003_004 | kai_00001 | c_kai_00002_vd_Boss_Yellow |
| vd_kim_boss_00001 | SSE_SBG_003_004 | （空） | c_kim_00001_vd_Boss_Red |
| vd_mag_boss_00001 | SSE_SBG_003_004 | mag_00004 | c_mag_00001_vd_Boss_Green |
| vd_osh_boss_00001 | SSE_SBG_003_004 | osh_00001 | c_osh_00601_vd_Boss_Green |
| vd_spy_boss_00001 | SSE_SBG_003_004 | （空） | c_spy_00401_vd_Boss_Blue |
| vd_sum_boss_00001 | SSE_SBG_003_004 | （空） | c_sum_00001_vd_Boss_Red |
| vd_sur_boss_00001 | SSE_SBG_003_004 | （空） | c_sur_00801_vd_Boss_Red |
| vd_tak_boss_00001 | SSE_SBG_003_004 | tak_00001 | c_tak_00001_vd_Boss_Blue |
| vd_you_boss_00001 | SSE_SBG_003_004 | （空） | c_you_00201_vd_Boss_Green |
| vd_yuw_boss_00001 | SSE_SBG_003_004 | （空） | c_yuw_00601_vd_Boss_Blue |

---

## 8. IDの命名規則

### 基本パターン

```
vd_{作品ID}_{ブロック種別}_{連番4桁}
```

| 要素 | 説明 | 例 |
|------|------|---|
| `vd_` | 限界チャレンジを示すプレフィックス | - |
| `{作品ID}` | 作品を識別する短縮ID | `kai`, `spy`, `dan`, `mag`, `sur`, `hut`, `kim`, `jig`, `osh`, `gom`, `you`, `yuw`, `chi`, `aya`, `tak`, `sum` |
| `{ブロック種別}` | `normal` または `boss` | - |
| `{連番4桁}` | 同一作品・種別内での連番（ゼロ埋め4桁） | `00001`, `00002` |

### 全テーブルのID一貫性

1つのブロックでは **MstInGame.id と同じ値（block_id）を以下のカラムにすべて使用する**:

| テーブル | カラム | 値 |
|---------|-------|---|
| MstInGame | id | `{block_id}` |
| MstInGame | mst_auto_player_sequence_set_id | `{block_id}` |
| MstInGame | mst_page_id | `{block_id}` |
| MstInGame | mst_enemy_outpost_id | `{block_id}` |
| MstPage | id | `{block_id}` |
| MstEnemyOutpost | id | `{block_id}` |
| MstKomaLine | mst_page_id | `{block_id}` |
| MstAutoPlayerSequence | sequence_set_id | `{block_id}` |

MstKomaLine・MstAutoPlayerSequence の個別ID（行単位）は `{block_id}_{行番号}` の形式:
- MstKomaLine: `{block_id}_1`, `{block_id}_2`, `{block_id}_3`
- MstAutoPlayerSequence: `{block_id}_1`, `{block_id}_2`, ...

---

## 9. `mst_auto_player_sequence_id` と `mst_auto_player_sequence_set_id` の使い分け

### 重要: mst_auto_player_sequence_id はレガシーカラム

DBスキーマ・CSVカラムとして現在も存在するが、`mst_auto_player_sequence_id` は**レガシーカラムであり、VDを含む全ステージで空文字に設定すること**。

`export_csv.py`（CSV生成・検証スクリプト）には以下のバリデーションルールが定義されている:

```python
# ⑭ mst_in_games.mst_auto_player_sequence_id はレガシーカラムのため必ず空文字
cur = conn.execute("""
    SELECT id, mst_auto_player_sequence_id
    FROM mst_in_games
    WHERE mst_auto_player_sequence_id != '' AND mst_auto_player_sequence_id IS NOT NULL
""")
for row in cur.fetchall():
    errors.append(f"[Legacy] MstInGame[{row[0]}]: mst_auto_player_sequence_id='{row[1]}' が設定されています（レガシーカラムのため空文字にしてください）")
```

このバリデーションに違反するとCSV生成時にエラーになる。

### クライアント実装との対応

C# クライアントの `MstInGameData.cs`（自動生成クラス）を確認すると、`mst_auto_player_sequence_id` は**クライアントデータクラスに存在しない**（配信されない）:

```csharp
public class MstInGameData
{
    // mst_auto_player_sequence_id は存在しない
    public string MstAutoPlayerSequenceSetId { get; set; }  // ← これのみ使用
    // ...
}
```

クライアントは `mst_auto_player_sequence_set_id` のみを使ってシーケンスを取得する。

### mst_auto_player_sequence_set_id の役割

`mst_auto_player_sequence_set_id` は MstAutoPlayerSequence テーブルの `sequence_set_id` カラムに対応し、同一ステージに属する複数のシーケンス行をセット単位でグルーピングするキーとして機能する。

```
MstInGame.mst_auto_player_sequence_set_id = "vd_kai_boss_00001"
    ↓
MstAutoPlayerSequence WHERE sequence_set_id = "vd_kai_boss_00001"
    → elem_1: InitialSummon（ボス配置）
    → elem_2: ElapsedTime 1500ms（ファントム）
    → elem_3: ElapsedTime 2000ms（怪獣余獣）
    ...
```

### 旧バージョンCSVとの差異について

一部の生成済みブロック（vd_spy_normal_00001, vd_dan_normal_00001 等）では `mst_auto_player_sequence_id` にブロックIDが設定されているCSVが存在する。これはバリデーションルール追加前の旧バージョンスクリプトで生成されたものであり、最新バージョンでの再生成時には空文字に修正される。

---

## 10. 注意事項まとめ

### よくある落とし穴

| 注意事項 | 詳細 |
|----------|------|
| **mst_auto_player_sequence_id を設定してしまう** | レガシーカラムのため必ず空文字にすること。値を設定するとexport_csv.pyのバリデーションエラーになる |
| **mst_auto_player_sequence_set_id を MstInGame.id と異なる値にする** | sequence_set_id は必ず MstInGame.id（= block_id）と同値にすること |
| **mst_page_id・mst_enemy_outpost_id を block_id と異なる値にする** | すべて block_id と同値 |
| **Bossブロックで boss_mst_enemy_stage_parameter_id を設定せずにボスを出現させる** | MstInGame.boss_mst_enemy_stage_parameter_id の設定とMstAutoPlayerSequence（InitialSummon）への同IDの二重設定が必須 |
| **Normalブロックの bgm_asset_key に SSE_SBG_003_004（Boss用）を使う** | NormalはSSE_SBG_003_010、BossはSSE_SBG_003_004 |
| **boss_bgm_asset_key に値を設定する** | VDでは常に空文字（BGM切り替えなし） |
| **mst_defense_target_id を空文字にする** | VDでは防衛オブジェクト不使用のため `__NULL__`（NULL）を設定すること |
| **boss_count に値を設定する** | 現状未使用のため `__NULL__`（NULL）を設定すること |
| **release_key を誤る** | VDの全ブロックは `202604010` |
| **content_type / stage_type を CSV に追加しようとする** | これらのカラムは MstInGame テーブルに存在しない。ブロック種別は MstDungeonBlock で管理される |

### VD特有の制約まとめ

| 項目 | VD制約 |
|------|--------|
| ステータス倍率 | 全coefカラムを `1.0` に固定（難易度は敵パラメータで調整） |
| BGM切り替え | `boss_bgm_asset_key` は常に空文字（BGM切り替えなし） |
| 防衛オブジェクト | `mst_defense_target_id` は常に `__NULL__` |
| ブロック種別の扱い | MstInGame 側ではなく MstDungeonBlock.block_type で管理 |
| 背景 | クライアント側のフロア深度ロジックで自動切り替えするため `loop_background_asset_key` は基本空文字 |
