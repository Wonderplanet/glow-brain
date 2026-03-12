# MstEnemyStageParameter 設定パターン詳細

> 対象リリースキー: 202604010（VD限界チャレンジ用）
> 参照DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
> 参照C#実装: `projects/glow-client/Assets/GLOW/Scripts/Runtime/`

---

## 1. 概要

`MstEnemyStageParameter` は**インゲーム（バトル）で召喚される敵ユニットのステータス・挙動パラメータ**を定義するテーブル。
「どの敵キャラクターが、どのステージで、どんな強さで出てくるか」を1行1レコードで表す。

```
MstAutoPlayerSequence
  └─ action_type = SummonEnemy
       └─ action_value = MstEnemyStageParameter.id  ← ここで参照
                           ├─ HP / 攻撃力 / 移動速度
                           ├─ ロールタイプ / 影色
                           ├─ アビリティ
                           └─ 変身条件（オプション）
```

**MstAutoPlayerSequence との役割分担**:
- `MstAutoPlayerSequence` → 「いつ・どこに・何体出すか・HP倍率はいくらか」を定義
- `MstEnemyStageParameter` → 「その敵ユニットが実際にどんな能力を持つか」を定義

**重要な特徴**:
- 同じキャラ（`mst_enemy_character_id`）でも、ステージ難易度・役割（ボスか雑魚か）・色違いなど、用途ごとに別レコードを作成する
- プレイヤーキャラクターが敵として出てくる場合（`c_` プレフィックス）もこのテーブルで管理する

---

## 2. 全カラム一覧

| カラム名 (CSV) | DB カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|---|
| `ENABLE` | - | string | - | - | `e` = 有効（必須） |
| `release_key` | `release_key` | bigint | 必須 | 1 | リリースキー。VDは `202604010` |
| `id` | `id` | varchar(255) | 必須 | - | レコードID。命名規則は後述 |
| `mst_enemy_character_id` | `mst_enemy_character_id` | varchar(255) | 必須 | - | キャラクターID。`MstEnemyCharacter.id` を参照。見た目・アセットが決まる |
| `character_unit_kind` | `character_unit_kind` | varchar(255) | 必須 | Normal | 敵の種別。enum値は後述 |
| `role_type` | `role_type` | enum | 必須 | - | ロールタイプ。enum値は後述 |
| `color` | `color` | enum | 必須 | - | 影色（色相性に影響）。enum値は後述 |
| `sort_order` | `sort_order` | int | 必須 | - | 管理画面等での表示並び順 |
| `hp` | `hp` | int | 必須 | - | 基本HP。倍率と乗算される（最終HP計算は後述） |
| `damage_knock_back_count` | `damage_knock_back_count` | int | 必須 | - | ノックバック回数。HPを等分した区切りで後退する回数 |
| `move_speed` | `move_speed` | int | 必須 | - | 移動速度（実績値範囲: 5〜100）。大きいほど速い |
| `well_distance` | `well_distance` | double | 必須 | - | 索敵距離（実績値範囲: 0.11〜0.6）。この距離以内の敵を検知して攻撃 |
| `attack_power` | `attack_power` | int | 必須 | - | 攻撃力（実績値範囲: 10〜3800）|
| `attack_combo_cycle` | `attack_combo_cycle` | int | 必須 | - | 攻撃コンボサイクル数。`0` = 攻撃しない（移動専用ユニット等） |
| `mst_unit_ability_id1` | `mst_unit_ability_id1` | varchar(255) | NULL可 | （空） | アビリティID。`MstUnitAbility.id` を参照。空文字 = アビリティなし |
| `drop_battle_point` | `drop_battle_point` | int | 必須 | - | 撃破時のバトルポイント（リーダーP）獲得量 |
| `mstTransformationEnemyStageParameterId` | `mst_transformation_enemy_stage_parameter_id` | varchar(255) | 必須 | （空） | 変身後の敵パラメータID。空文字 = 変身なし |
| `transformationConditionType` | `transformation_condition_type` | varchar(255) | 必須 | None | 変身条件タイプ。enum値は後述 |
| `transformationConditionValue` | `transformation_condition_value` | varchar(255) | 必須 | （空） | 変身条件の値（条件タイプにより意味が変わる） |

> **CSVカラム名の注意**: `mstTransformationEnemyStageParameterId` / `transformationConditionType` / `transformationConditionValue` は CSV 上で **camelCase** 表記。DB スキーマでは snake_case。

---

## 3. character_unit_kind の全 enum 値

| 値 | 意味 | IsBoss 判定 | 備考 |
|---|---|---|---|
| `Normal` | 通常の雑魚敵。複数体まとめて出現することが多い | false | VD では最も多く使う種別 |
| `Boss` | ボス敵。HPが高く、aura演出が付くことが多い | **true** | MstAutoPlayerSequence の `aura_type` と組み合わせる |
| `AdventBattleBoss` | 降臨バトル専用ボス。Boss より格上の演出 | **true** | VD では通常使用しない（降臨バトル専用） |
| `Rare` | レア敵 | false | VD では通常使用しない |

**C# 実装から確認した IsBoss 判定**（`MstEnemyStageParameterModel.cs`）:

```csharp
public bool IsBoss => Kind is CharacterUnitKind.Boss or CharacterUnitKind.AdventBattleBoss;
public bool IsNormal => Kind == CharacterUnitKind.Normal;
```

`IsBoss = true` のとき、`CharacterUnitFactory.cs` でボス演出ロジックが適用される:
- 召喚時にノックバックアニメーション（`PlayBossAppearanceAnimation`）が再生される
- `GetSummoningRemainingTime` で `TickCount.Zero` が返り、通常雑魚の出現待ち時間が省略される

---

## 4. role_type の全 enum 値

| 値 | 説明 |
|---|---|
| `None` | ロールなし |
| `Attack` | 攻撃型 |
| `Balance` | バランス型 |
| `Defense` | 防御型 |
| `Support` | サポート型 |
| `Unique` | ユニーク型 |
| `Technical` | テクニカル型 |
| `Special` | スペシャル型 |

色相性ボーナスは `role_type` と `color` の組み合わせによって `MstUnitRoleBonus` から取得される（`EnemyStageParameterDataTranslator.cs` で `mstUnitRoleBonusData.ColorAdvantageAttackBonus` / `ColorAdvantageDefenseBonus` として参照）。

---

## 5. color の全 enum 値

| 値 | 内部値 | 説明 |
|---|---|---|
| `None` | 0 | 無属性（色相性計算なし） |
| `Colorless` | 1 | 無色 |
| `Red` | 2 | 赤 |
| `Blue` | 3 | 青 |
| `Yellow` | 4 | 黄 |
| `Green` | 5 | 緑 |

---

## 6. VD 固有の ID パターン

### 基本命名規則

```
{種別プレフィックス}_{作品ID}_{キャラID}_vd_{character_unit_kind}_{color}
```

| 構成要素 | 説明 | 例 |
|---|---|---|
| `種別プレフィックス` | `e_` または `c_`（後述） | `e_`, `c_` |
| `作品ID` | 3文字の作品コード | `kai`（怪獣8号）, `chi`, `sur` 等 |
| `キャラID` | 5桁の数字（MstEnemyCharacter ID の末尾） | `00101`, `00001` 等 |
| `vd` | VD固定の識別子 | `vd` |
| `character_unit_kind` | `Normal` または `Boss` | `Normal`, `Boss` |
| `color` | 色属性 | `Yellow`, `Blue`, `Red`, `Green`, `Colorless` |

### VD の ID 具体例

| id | mst_enemy_character_id | character_unit_kind | color |
|---|---|---|---|
| `e_kai_00101_vd_Normal_Yellow` | `enemy_kai_00101` | Normal | Yellow |
| `c_kai_00002_vd_Boss_Yellow` | `chara_kai_00002` | Boss | Yellow |
| `e_chi_00101_vd_Normal_Blue` | `enemy_chi_00101` | Normal | Blue |
| `c_chi_00002_vd_Boss_Blue` | `chara_chi_00002` | Boss | Blue |
| `e_sur_00101_vd_Normal_Red` | `enemy_sur_00101` | Normal | Red |
| `c_sum_00001_vd_Boss_Red` | `chara_sum_00001` | Boss | Red |

> **VD 専用の命名**: 汎用ステージでは `general` などの中間フィールドが入るが、VD では `vd` を固定で使用する。

---

## 7. `e_` と `c_` プレフィックスの使い分け

| プレフィックス | mst_enemy_character_id の形式 | 用途 |
|---|---|---|
| `e_` | `enemy_{作品ID}_{5桁番号}` | 敵専用キャラクターのパラメータ |
| `c_` | `chara_{作品ID}_{5桁番号}` | プレイヤーキャラクターを敵として使うパラメータ |

**C# 実装から確認した内容**（`CharacterUnitFactory.cs`）:

```csharp
var enemyCharacterModel = MstEnemyCharacterRepository.GetEnemyCharacter(stageParameter.MstEnemyCharacterId);
```

`e_` / `c_` の使い分けはプレフィックスの命名規則であり、クライアント内部では `MstEnemyCharacterRepository` に渡す `MstEnemyCharacterId` の値（`enemy_xxx` か `chara_xxx` か）で実際のアセットが決まる。

**c_ プレフィックスの具体的なユースケース（VD 実績値）**:

- プレイヤーが使えるキャラクターがボスとして登場するステージ（例: `c_kai_00002_vd_Boss_Yellow`）
- プレイヤーキャラクターが雑魚として複数体出てくるステージ（例: `c_yuw_00001_vd_Normal_Blue`）

**注意**: `c_` プレフィックスの id でも、`character_unit_kind` は `Normal`（雑魚）や `Boss` どちらでも設定可能。プレフィックスは「アセット種別」を表すのみで、「ボスか雑魚か」は `character_unit_kind` で決まる。

---

## 8. HP パラメータと倍率の関係

### 最終 HP 計算式

```
最終 HP = MstEnemyStageParameter.hp
        × MstInGame.normal_enemy_hp_coef  （ステージ全体倍率）
        × MstAutoPlayerSequence.enemy_hp_coef（シーケンス行ごとの倍率）
```

**C# 実装から確認した内容**（`CharacterUnitFactory.cs`）:

```csharp
var hp = stageParameter.Hp * unitCoef.StageHpCoef * unitCoef.InGameSequenceHpCoef;
```

- `StageHpCoef` = `MstInGame.normal_enemy_hp_coef` から来るステージ全体倍率
- `InGameSequenceHpCoef` = `MstAutoPlayerSequence.enemy_hp_coef` から来るシーケンス行ごとの倍率

同様に **攻撃力** と **移動速度** にも倍率が適用される:

```csharp
var attackPower = stageParameter.AttackPower.Value
                  * (decimal)unitCoef.StageAttackPowerCoef
                  * (decimal)unitCoef.InGameSequenceAttackPowerCoef;

var moveSpeed = stageParameter.UnitMoveSpeed.Value
                * unitCoef.StageUnitMoveSpeedCoef
                * unitCoef.InGameSequenceUnitMoveSpeedCoef;
```

### VD での HP 設定例

| ユニット種別 | HP の実績値範囲 | 設計方針 |
|---|---|---|
| Normal（雑魚） | 1,000 〜 50,000 | シーケンス側の `enemy_hp_coef` で調整する基準値を設定 |
| Boss | 10,000 〜 700,000 | ボスは HP 基準値を大きく設定し、倍率は 1.0 近くにするか、序盤〜終盤で倍率変化 |

> **設計ポイント**: `hp` は「基準値」として設定し、細かい強さ調整はシーケンス側の `enemy_hp_coef` で行うのが基本。同じ `MstEnemyStageParameter` を使い回し、シーケンスの倍率だけ変えることで難易度調整できる。

---

## 9. 変身機能の設定パターン

### 変身の仕組み

HPが条件を満たしたとき、別のパラメータセット（変身後レコード）に切り替わる機能。
「HP50%以下になると形態変化する」「一定時間後に強化形態になる」などの演出に使う。

**C# 実装から確認した内容**（`EnemyStageParameterDataTranslator.cs`）:

```csharp
var transformationParameter = string.IsNullOrEmpty(mstEnemyStageParameterData.MstTransformationEnemyStageParameterId)
    ? UnitTransformationParameter.Empty
    : new UnitTransformationParameter(
        new MasterDataId(mstEnemyStageParameterData.MstTransformationEnemyStageParameterId),
        mstEnemyStageParameterData.TransformationConditionType,
        new UnitTransformationConditionValue(mstEnemyStageParameterData.TransformationConditionValue));
```

`mstTransformationEnemyStageParameterId` が空文字の場合は変身なし（`UnitTransformationParameter.Empty`）として扱われる。

### transformationConditionType の全 enum 値

| 値 | condition_value の意味 | 発動条件 | VD での使用 |
|---|---|---|---|
| `None` | （空文字） | 変身しない | 大多数のレコードはこれ |
| `HpPercentage` | 0〜100 の整数（%）| HPが指定%以下になったとき変身 | ボスの形態変化に使用 |
| `StageTime` | TickCount（整数）| ステージ開始からの経過 tick 数が指定値以上になったとき変身 | VD では使用実績なし（要確認） |

**C# 実装から確認した内容**（`UnitTransformationParameter.cs`）:

```csharp
public InGameCommonConditionType GetCommonConditionType()
{
    return ConditionType switch
    {
        UnitTransformationConditionType.HpPercentage => InGameCommonConditionType.MyHpLessThanOrEqualPercentage,
        UnitTransformationConditionType.StageTime    => InGameCommonConditionType.StageTime,
        _                                            => InGameCommonConditionType.None
    };
}
```

- `HpPercentage` → `MyHpLessThanOrEqualPercentage`（HP が指定%以下）
- `StageTime` → `StageTime`（ステージ経過時間が指定 tick 以上、`StageTimeCommonConditionModel` 参照）

### HpPercentage を使った変身の設定例

```csv
# 変身前レコード
e,202604010,boss_pre_transform,chara_xxx_00001,Boss,Attack,Red,...
  mstTransformationEnemyStageParameterId: boss_post_transform
  transformationConditionType: HpPercentage
  transformationConditionValue: 30   ← HP 30% 以下で変身

# 変身後レコード
e,202604010,boss_post_transform,chara_xxx_00002,Boss,Attack,Red,...
  mstTransformationEnemyStageParameterId: （空）
  transformationConditionType: None
  transformationConditionValue: （空）
```

> **実績値**: `HpPercentage` 使用時は `30`（HP30%で変身）または `50`（HP50%で変身）または `1`（HP1%で変身）がよく使われる。

### 変身設定時の重要ルール

1. **変身先 ID は必ず事前に用意する**: `mstTransformationEnemyStageParameterId` で指定する ID が存在しないとエラーになる
2. **変身前・変身後は同じ CSV で一括作成**: 参照整合性を保つため、セットで登録する
3. **変身後レコードの transformationConditionType は必ず `None`**: チェーン変身は仕様上考慮しない（VD では不要）

---

## 10. ability との連携

### 設定方法

`mst_unit_ability_id1` に `MstUnitAbility.id` を設定する。空文字 = アビリティなし。

**C# 実装から確認した内容**（`CharacterUnitFactory.cs`）:

```csharp
var ability = UnitAbilityModelFactory.Create(stageParameter.Ability);
// 常時発動する特性の状態変化を付与する
if (ability.ArisesStateEffectOnceOnSummon)
{
    var effectModel = StateEffectModelFactory.Create(
        ability.StateEffectSourceId,
        ability.GetStateEffect(),
        false);
    stateEffects.Add(effectModel);
}
```

アビリティが設定されている場合、召喚時に状態変化が適用される（`ArisesStateEffectOnceOnSummon` が true のアビリティの場合）。

### VD での ability 設定状況

`vd_all/data/MstEnemyStageParameter.csv` を確認すると、VD のレコードは全て `mst_unit_ability_id1` が空（アビリティなし）である。VD では現時点でアビリティ付き敵は使用していない。

---

## 11. vd_all/data/MstEnemyStageParameter.csv の活用方法

**パス**: `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv`

このファイルは**全 VD 作品の敵パラメータを集約したマスターリスト**。新規ブロックを作成する際に以下の手順で活用する。

### 参照手順

#### ステップ 1: 使用したい作品の既存パラメータを確認する

```bash
# 例: 作品ID「kai」の全パラメータを確認
grep "kai" vd_all/data/MstEnemyStageParameter.csv
```

#### ステップ 2: 既存パラメータが使えるか判断する

同じ `mst_enemy_character_id`（キャラ）で、同じ `color` のパラメータが既にあれば再利用できる可能性がある。

| 条件 | 判断 |
|---|---|
| 同じ `id` のレコードが既に存在する | → そのまま参照可能（CSV に追加不要） |
| 同じキャラ・同じ色だが `id` が違う | → 用途が異なるため新規作成が必要 |
| 同じキャラ・異なる色 | → 新規レコード作成が必要 |

#### ステップ 3: 新規パラメータの場合

1. 新規レコードを個別ブロックの `generated/MstEnemyStageParameter.csv` に追加する
2. ID 命名規則（`e_{作品ID}_{キャラID}_vd_{character_unit_kind}_{color}`）に従う
3. 作業完了後、`vd_all/data/MstEnemyStageParameter.csv` にも同内容を追記する（マスターリストを常に最新に保つ）

---

## 12. 注意事項まとめ

### よくある落とし穴

#### (1) `e_` / `c_` プレフィックスの混在ミス

- **NG**: `mst_enemy_character_id` が `enemy_xxx_xxxxx` なのに id が `c_` で始まっている
- **NG**: `mst_enemy_character_id` が `chara_xxx_xxxxx` なのに id が `e_` で始まっている
- id のプレフィックスと `mst_enemy_character_id` の先頭（`enemy_` か `chara_` か）は必ず対応させること

**正しい対応**:
| id プレフィックス | mst_enemy_character_id の先頭 |
|---|---|
| `e_` | `enemy_` |
| `c_` | `chara_` |

#### (2) 変身条件の誤設定

- 変身前レコードの `transformationConditionType` を `None` にしてしまうと変身しない
- 変身後レコードの `transformationConditionType` を `None` 以外にすると、変身後にさらに変身しようとする（意図しない挙動）
- `mstTransformationEnemyStageParameterId` を設定したのに `transformationConditionType` を `None` にすると、変身先 ID が無視される

#### (3) damage_knock_back_count が 0 または空

- `vd_all/data/MstEnemyStageParameter.csv` の実データを見ると、空のレコードが存在する（例: `e_gom_00501_vd_Normal_Yellow`）
- DBスキーマ上は `NOT NULL` だが、実際のデータでは空文字が入っているケースがある（0として扱われる可能性）
- ノックバックが発生しないユニットには `0` か `1` を明示的に設定することを推奨

#### (4) attack_combo_cycle = 0 は攻撃しないユニット

- `0` を設定すると攻撃を一切しない移動専用ユニットになる
- `e_aya_00001_vd_Normal_Green` の実績（`attack_combo_cycle = 0`）が VD データに存在する

#### (5) drop_battle_point の設定

- VD の既存データでは `10` 〜 `1000` の範囲が多い
- バトルポイントが不要な場合は `0` も可能

#### (6) 同一 ID の重複登録禁止

- `vd_all/data/MstEnemyStageParameter.csv` に既存の ID と同じ ID で新規レコードを作ると重複エラーになる
- 作成前に必ず `vd_all/data/MstEnemyStageParameter.csv` で ID の重複確認を行うこと

#### (7) VD では AdventBattleBoss / Rare は使用しない

- `character_unit_kind` は `Normal` または `Boss` のみを使用する
- `AdventBattleBoss` は降臨バトル専用、`Rare` は別用途のため VD には不適切

---

## 13. VD 実データからのパラメータ傾向まとめ

`vd_all/data/MstEnemyStageParameter.csv`（release_key: 202604010）の統計:

| パラメータ | Normal の傾向 | Boss の傾向 |
|---|---|---|
| `hp` | 1,000 〜 50,000 | 10,000 〜 700,000 |
| `damage_knock_back_count` | 1〜3（または空） | 1〜4 |
| `move_speed` | 29〜100 | 25〜50 |
| `well_distance` | 0.11〜0.5 | 0.11〜0.45 |
| `attack_power` | 10〜2,500 | 50〜1,700 |
| `attack_combo_cycle` | 0〜5 | 4〜7 |
| `drop_battle_point` | 10〜500 | 100〜1,000 |

> 特に `move_speed` については、Normal が高速（最大 100）なのに対し、Boss は遅め（最大 50 程度）の傾向がある。ボスは強力だが遅い、という設計意図が見られる。
