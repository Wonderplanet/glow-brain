# MstAttackElement 詳細説明

> CSVパス: `projects/glow-masterdata/MstAttackElement.csv`
> クライアントクラス: `MstAttackElementData.cs`（自動生成）

---

## 概要

MstAttackElement は**インゲーム向けユニットの攻撃1撃（エレメント）を定義するテーブル**。

1つの攻撃行動（`MstAttack`）は複数の「エレメント」から構成される。エレメントは攻撃の1つの「効果単位」であり、同一攻撃が多段ヒット・ダメージ＋バフ付与・自己バフなど複数効果を持つ場合は、sort_order 順に複数レコードで表現する。

各エレメントは次の軸で動作を決定する：
- **攻撃対象** (`target` / `target_type` / `target_colors` / `target_roles` / `max_target_count`) ─ 誰を攻撃するか
- **攻撃範囲** (`range_start_type` / `range_start_parameter` / `range_end_type` / `range_end_parameter`) ─ 攻撃が届く距離
- **ダメージ計算** (`damage_type` / `power_parameter_type` / `power_parameter` / `probability`) ─ どれくらいのダメージか
- **命中演出** (`hit_type` / `hit_parameter1` / `hit_parameter2` / `is_hit_stop`) ─ ヒット時の特殊挙動
- **状態効果** (`effect_type` / `effective_count` / `effective_duration` / `effect_parameter`) ─ バフ/デバフ付与

総レコード数は 4,220 件（2026-03-06 時点）。attack_type = `Direct`（直接攻撃）が大多数を占め、`Deck`（デッキ向け全体効果）や `PlaceItem`（設置アイテム型）は少数。

```
MstAttack（攻撃行動の器）
  └─ id = MstAttackElement.mst_attack_id
        ├─ sort_order 1: メイン攻撃（ダメージ）
        ├─ sort_order 2: 副次効果（自己バフ、追加ダメージ等）
        └─ sort_order N: ...

MstAttackElement ←── mst_special_role_level_up_attack_elements（スペシャルロールのレベルアップ補正）
```

---

## 全カラム一覧

| カラム名 | 型（DB） | クライアント型 | NULL許容 | デフォルト値 | 説明 |
|---------|---------|--------------|---------|------------|------|
| `ENABLE` | - | - | - | - | CSVの有効フラグ。`e` = 有効（常に `e` のみ使用） |
| `id` | varchar(255) | string | 不可 | - | エレメントID（主キー）。命名規則は後述 |
| `release_key` | bigint | - | 不可 | 1 | リリースキー。リリース管理に使用 |
| `mst_attack_id` | varchar(255) | string | 不可 | - | 所属する攻撃行動ID（MstAttack.id への外部キー） |
| `sort_order` | int | int | 不可 | - | 同一攻撃内での実行順。小さい値から順に処理される |
| `attack_delay` | int | int | 不可 | - | 攻撃が発生するまでのフレーム数（ディレイ）。通常は 0 |
| `attack_type` | varchar(255) | AttackType (enum) | 不可 | - | 攻撃の種別。後述のenum参照 |
| `range_start_type` | enum | AttackRangePointType (enum) | 不可 | - | 攻撃範囲の開始点の計算方式 |
| `range_start_parameter` | double(8,2) | float | 不可 | - | 攻撃範囲の開始値。range_start_type の単位で解釈 |
| `range_end_type` | enum | AttackRangePointType (enum) | 不可 | - | 攻撃範囲の終了点の計算方式 |
| `range_end_parameter` | double(8,2) | float | 不可 | - | 攻撃範囲の終了値。range_end_type の単位で解釈 |
| `max_target_count` | int | int | 不可 | - | 攻撃が同時に当たる最大ターゲット数 |
| `target` | varchar(255) | AttackTarget (enum) | 不可 | - | 攻撃対象の陣営。後述のenum参照 |
| `target_type` | enum | AttackTargetType (enum) | 不可 | - | 攻撃対象のオブジェクト種別 |
| `target_colors` | varchar(255) | string | 不可 | - | 対象を絞り込む属性（カラー）。`All` または属性名のカンマ区切り |
| `target_roles` | varchar(255) | string | 不可 | - | 対象を絞り込むロール。`All` またはロール名のカンマ区切り |
| `target_mst_series_ids` | varchar(255) | string | 不可 | "" | 対象を絞り込むシリーズID群。空文字 = 絞り込みなし |
| `target_mst_character_ids` | varchar(255) | string | 不可 | "" | 対象を絞り込む特定キャラクターID群。空文字 = 絞り込みなし |
| `damage_type` | varchar(255) | AttackDamageType (enum) | 不可 | `None` | ダメージの種類。後述のenum参照 |
| `hit_type` | varchar(255) | AttackHitType (enum) | 不可 | `Normal` | 命中時の特殊挙動（ノックバック・スタン等）。後述のenum参照 |
| `hit_parameter1` | int unsigned | int | 不可 | 0 | 命中効果値1。hit_type に応じた数値（ドレイン量等） |
| `hit_parameter2` | int unsigned | int | 不可 | 0 | 命中効果値2（予備パラメータ） |
| `hit_effect_id` | varchar(255) | string | 不可 | "" | 命中時の演出エフェクトID（MstAttackHitEffect.id を参照） |
| `is_hit_stop` | tinyint | bool | 不可 | 0 | ヒットストップ（命中時の一瞬の停止演出）を行うか |
| `probability` | int | int | 不可 | - | このエレメントが発動する確率（0〜100）。通常は 100 |
| `power_parameter_type` | enum | AttackPowerParameterType (enum) | 不可 | - | 攻撃力の計算方式。後述のenum参照 |
| `power_parameter` | int | int | 不可 | - | 攻撃力の数値。power_parameter_type に応じて解釈 |
| `effect_type` | varchar(255) | StateEffectType (enum) | 不可 | `None` | 付与する状態効果の種類。後述のenum参照 |
| `effective_count` | int | int | 不可 | - | 状態効果の発動回数。`-1` = 無制限（持続型） |
| `effective_duration` | int | int | 不可 | - | 状態効果の持続時間（フレーム数）。`0` = 効果なし |
| `effect_parameter` | double | float | 不可 | 0 | 状態効果の強度値（数値型） |
| `effect_value` | varchar(255) | string | 不可 | "" | 状態効果の強度値（文字列型）。effect_parameter の代替または補完 |
| `effect_trigger_roles` | varchar(255) | string | 不可 | "" | 状態効果を発動するロール条件。空文字 = 条件なし |
| `effect_trigger_colors` | varchar(255) | string | 不可 | "" | 状態効果を発動する属性条件。空文字 = 条件なし |

---

## 主要なenum / フラグの解説

### AttackType（attack_type）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `Direct` | 通常の直接攻撃。ユニットが射程内の敵に直接行動する | 4,195件（99.4%） |
| `PlaceItem` | 設置アイテム型の攻撃。フィールドにオブジェクトを置く形式 | 15件 |
| `Deck` | デッキ全体への効果型。通常ダメージを伴わずにバフを付与する特殊型 | 10件 |
| `BattleSide` | バトルサイド向け（現状CSVに使用例なし） | 0件 |

### AttackRangePointType（range_start_type / range_end_type）

| 値 | 説明 |
|----|------|
| `Distance` | ゲームワールド内の実際の距離（単位: ゲーム内距離単位）で範囲を指定 |
| `Koma` | コマ（マス）の番号で範囲を指定 |
| `KomaLine` | コマラインで範囲を指定 |
| `Page` | ページ単位で範囲を指定 |

### AttackTarget（target）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `Foe` | 敵陣営（プレイヤーに向かってくる敵キャラクターや施設）を対象 | 3,737件（88.6%） |
| `Friend` | 味方陣営（プレイヤーのユニット）を対象。回復・バフ用途 | 260件 |
| `Self` | 自分自身のみを対象。自己バフ専用 | 218件 |
| `FriendOnly` | 味方のみ（自身を除く）を対象 | 5件 |

### AttackTargetType（target_type）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `All` | キャラクターと拠点（アウトポスト）の両方を対象 | 3,840件（91.0%） |
| `Character` | キャラクターのみを対象 | 380件 |
| `OutpostAndDefenseTarget` | 拠点・防衛ターゲットのみを対象（DBスキーマでは `Outpost`） | 0件 |

### AttackDamageType（damage_type）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `Damage` | 通常ダメージ。最も一般的 | 3,195件（75.7%） |
| `None` | ダメージなし。バフ付与や演出のみの場合に使用 | 977件（23.2%） |
| `Heal` | 回復。味方ユニットのHPを回復する | 48件 |
| `PoisonDamage` | 毒ダメージ（実データ未使用） | 0件 |
| `BurnDamage` | 炎ダメージ（実データ未使用） | 0件 |
| `SlipDamage` | スリップダメージ（実データ未使用） | 0件 |
| `RushDamage` | ラッシュダメージ（実データ未使用） | 0件 |

### AttackHitType（hit_type）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `Normal` | 通常命中。特殊挙動なし | 3,434件（81.4%） |
| `ForcedKnockBack5` | 強制ノックバック（距離5）。命中した敵を大きく後退させる | 427件（10.1%） |
| `KnockBack1` | ノックバック（距離1）。小さなノックバック | 122件 |
| `Stun` | スタン。命中した敵を一定時間行動不能にする。hit_parameter1 がスタン時間 | 97件 |
| `KnockBack2` | ノックバック（距離2） | 50件 |
| `Drain` | ドレイン。ダメージを与えた量の一部を自身のHPとして回収。hit_parameter1 が吸収率 | 42件 |
| `Freeze` | 凍結。命中した敵を凍結状態にする | 33件 |
| `KnockBack3` | ノックバック（距離3） | 15件 |
| `AccumulatedDamageKnockBack` | 蓄積ダメージによるノックバック（実データ未使用） | 0件 |
| `ForcedKnockBack1/2/3` | 強制ノックバック（各距離）（実データ未使用） | 0件 |

### AttackPowerParameterType（power_parameter_type）

| 値 | 説明 | 実データ件数 |
|----|------|------------|
| `Percentage` | ユニットの基本攻撃力に対するパーセンテージ（100 = 基本攻撃力と同等） | 4,143件（98.2%） |
| `MaxHpPercentage` | ターゲットの最大HPに対するパーセンテージ。固定割合ダメージや回復に使用 | 44件 |
| `Fixed` | 固定値ダメージ・回復 | 33件 |

### StateEffectType（effect_type）: 主要な値のみ抜粋

| 値 | 数値 | 説明 | 実データ件数 |
|----|------|------|------------|
| `None` | 0 | 状態効果なし | 3,618件（85.7%） |
| `AttackPowerUp` | 1 | 攻撃力アップ | 261件 |
| `DamageCut` | 3 | ダメージカット | 136件 |
| `AttackPowerDown` | 2 | 攻撃力ダウン（敵対象のデバフ） | 91件 |
| `Poison` | 17 | 毒状態付与 | 40件 |
| `Burn` | 19 | 炎上状態付与 | 31件 |
| `Weakening` | 39 | 弱体化 | 10件 |
| `SpecialAttackCoolTimeShorten` | 35 | スペシャル攻撃のクールタイム短縮 | 8件 |
| `RushAttackPowerUp` | 29 | ラッシュ攻撃力アップ | 5件 |
| `Unbeatable` | 43 | 無敵状態付与 | 5件 |
| `SummonCoolTimeShorten` | 37 | 召喚クールタイム短縮 | 5件 |
| `RemoveBuff` | 41 | バフ解除 | 5件 |
| `RemoveDebuff` | 42 | デバフ解除 | 4件 |

> StateEffectType の完全一覧: `projects/glow-client/Assets/GLOW/Scripts/Runtime/Core/Domain/Constants/AutoGenerated/StateEffectType.cs`

---

## 命名規則 / IDの生成ルール

### id カラム

`{mst_attack_id}` と同一か、または `{mst_attack_id}_{サフィックス}` の形式。

```
# 単一エレメントの場合（id = mst_attack_id と一致）
chara_dan_00001_Normal_00000

# 複数エレメントの場合（サフィックスで区別）
chara_dan_00001_Special_00001       ← sort_order 1: メインダメージ
chara_dan_00001_Special_00001a      ← sort_order 2: 副次効果（自己バフ等）

# 多段ヒットの場合（連番サフィックス）
chara_chi_00002_Special_00001_01    ← sort_order 1: 1ヒット目
chara_chi_00002_Special_00001_06    ← sort_order 6: 6ヒット目
```

`mst_attack_id` の命名規則は MstAttack テーブルに従う（`{キャラID}_{攻撃種別}_{レベル番号}` 形式）。

### 採番パターン

| パターン | 例 | 用途 |
|---------|-----|------|
| `{mst_attack_id}` | `chara_dan_00001_Normal_00000` | 単一エレメント攻撃 |
| `{mst_attack_id}a` | `chara_dan_00001_Special_00001a` | 2番目のエレメント（副次効果） |
| `{mst_attack_id}b` | `chara_xxx_Special_00001b` | 3番目のエレメント |
| `{mst_attack_id}_{2桁連番}` | `chara_chi_00002_Special_00001_06` | 多段ヒット |

---

## 他テーブルとの連携

### このテーブルが参照するテーブル

| 参照先テーブル | カラム | 用途 |
|-------------|-------|------|
| `MstAttack` (`mst_attacks`) | `mst_attack_id` → `MstAttack.id` | エレメントが属する攻撃行動の定義（攻撃種別・アクションフレーム数・リリースキー等） |
| `MstAttackHitEffect` (`mst_attack_hit_effects`) | `hit_effect_id` → `MstAttackHitEffect.id` | 命中演出（擬音語・SE・弱点SEのアセットキー） |

### このテーブルを参照するテーブル

| 参照元テーブル | カラム | 用途 |
|-------------|-------|------|
| `mst_special_role_level_up_attack_elements` | `mst_attack_element_id` → `MstAttackElement.id` | スペシャルロールキャラのレベルアップに伴う攻撃パラメータの強化範囲（min/max）を定義 |

### テーブル連携図

```
MstUnit（ユニット定義）
  └─ mst_unit_id
       └─ MstAttack（攻撃行動）
              └─ id = MstAttackElement.mst_attack_id
                       ├─ hit_effect_id → MstAttackHitEffect（命中演出）
                       └─ （被参照）MstSpecialRoleLevelUpAttackElement（レベルアップ強化補正）
```

---

## 実データ例

### 例1: 通常の単発ダメージ攻撃（ノーマル攻撃）

```
id:                    chara_dan_00001_Normal_00000
mst_attack_id:         chara_dan_00001_Normal_00000
sort_order:            1
attack_type:           Direct
range_start_type:      Distance, range_start_parameter: 0
range_end_type:        Distance, range_end_parameter:   0.17
max_target_count:      1
target:                Foe, target_type: All, target_colors: All, target_roles: All
damage_type:           Damage
hit_type:              Normal
is_hit_stop:           1
probability:           100
power_parameter_type:  Percentage, power_parameter: 100.0
effect_type:           None
```

解説: 攻撃力100%のシンプルな単発ダメージ。ヒットストップあり。射程は Distance 0〜0.17。

---

### 例2: スペシャル攻撃（ダメージ＋自己ダメージカットバフ）

```
# sort_order 1: メインダメージ
id:                    chara_dan_00001_Special_00001
attack_type:           Direct, damage_type: None, hit_type: Normal
probability:           100, power_parameter: 0 (damage_type=Noneのためダメージなし)
effect_type:           None

# sort_order 2: 自己バフ
id:                    chara_dan_00001_Special_00001a
attack_type:           Direct
target:                Self, damage_type: None
power_parameter_type:  Percentage, power_parameter: 0.0
effect_type:           DamageCut
effective_count:       -1 (無制限)
effective_duration:    300
effect_parameter:      10 (ダメージカット10%)
```

解説: スペシャル攻撃のレベル1。メイン攻撃は別エレメント（MstAttack側）で定義し、このエレメントでは自己バフを付与する2段構成。

---

### 例3: デバフ付与攻撃（ダメージ＋敵の攻撃力ダウン）

```
id:                    chara_gom_00101_Special_00001
mst_attack_id:         chara_gom_00101_Special_00001
sort_order:            1
attack_type:           Direct, damage_type: Damage
range_end_type:        Distance, range_end_parameter: 0.32
max_target_count:      1
target:                Foe, target_type: All
hit_type:              Normal
probability:           100
power_parameter_type:  Percentage, power_parameter: 100.0
effect_type:           AttackPowerDown
effective_count:       -1, effective_duration: 300
effect_parameter:      10 (攻撃力10%ダウン)
```

解説: ダメージを与えながら命中した敵の攻撃力を300フレームにわたって10%ダウンさせるデバフ攻撃。

---

### 例4: ドレイン（ダメージ＋HP吸収）

```
id:                    chara_chi_00002_Special_00001_06
mst_attack_id:         chara_chi_00002_Special_00001
sort_order:            6
attack_type:           Direct, damage_type: Damage
target:                Foe, target_type: All
hit_type:              Drain, hit_parameter1: 15 (15%吸収)
probability:           100
power_parameter_type:  Percentage, power_parameter: 120.0
effect_type:           None
```

解説: 120%のダメージを与え、ダメージの15%分を自身のHPとして回収するドレイン攻撃。多段ヒットの6番目のエレメント。

---

### 例5: 回復型スペシャル（HP回復）

```
id:                    chara_spy_00001_Special_00001a
mst_attack_id:         chara_spy_00001_Special_00001
sort_order:            1
attack_type:           Direct, damage_type: Heal
target:                Friend, target_type: All
hit_type:              Normal
probability:           100
power_parameter_type:  MaxHpPercentage, power_parameter: 30.0 (最大HPの30%回復)
effect_type:           None
```

解説: 味方キャラクター全体を対象に、各キャラの最大HPの30%を回復するヒーラー型スペシャル攻撃。

---

### 例6: デッキ全体バフ（Deck型）

```
id:                    chara_sur_00401_Special_00001
mst_attack_id:         chara_sur_00401_Special_00001
sort_order:            1
attack_type:           Deck
target:                Friend, target_type: All
damage_type:           None, hit_type: Normal
probability:           100
power_parameter_type:  Percentage, power_parameter: 0.0
effect_type:           RushAttackPowerUp
effective_count:       -1, effective_duration: 0
effect_parameter:      3.5
```

解説: 個別のキャラを対象とせず、デッキ全体に対してラッシュ攻撃力3.5アップのバフを付与するDeck型攻撃。

---

## 設定時のポイント

### ダメージと状態効果の組み合わせ

- `damage_type = Damage` でダメージを与えながら `effect_type` でバフ/デバフを付与できる
- バフ付与のみの場合は `damage_type = None`、`power_parameter = 0` に設定する
- ダメージなしで状態効果のみを付与するサブエレメント（自己バフ等）では `target = Self` と組み合わせることが多い

### sort_order と複数エレメントの構成

- 同一の `mst_attack_id` に対して複数エレメントを定義する場合、`sort_order` を昇順に連番で割り振る
- サフィックスなしのID（`sort_order: 1`）がメインエレメント、`a`/`b` または `_01`/`_02` サフィックスが副次エレメントとなるケースが多い
- `sort_order` が同じ値を持つ別エレメントは定義しないこと（重複は想定外の動作を引き起こす）

### effective_count と effective_duration

- `effective_count = -1` は持続型（発動回数無制限）を意味する。多くのバフ/デバフで使用される
- `effective_duration = 0` の場合は持続時間なし（瞬時効果 or 効果なし）
- `effective_count = 0` かつ `effective_duration = 0` の場合は状態効果を実質的に無効化している状態（`effect_type = None` と同じ扱い）

### probability の使い方

- 通常は `probability = 100`（必中）
- 確率発動が必要な場合にのみ 100 未満の値を設定する
- `probability = 0` は実質的に発動しないエレメントであるため、設定ミスに注意

### target_colors / target_roles の書式

- 対象を絞り込まない場合は `All` を設定する（空文字は不可）
- 複数属性を指定する場合はカンマ区切り（例: `Red,Blue`）
- `target_mst_series_ids` / `target_mst_character_ids` は絞り込みが不要な場合は空文字（`""`）を設定する

### power_parameter_type の選択

| ユースケース | 推奨 power_parameter_type | 例 |
|-----------|------------------------|-----|
| 通常ダメージ（攻撃力倍率） | `Percentage` | `100` = 攻撃力100% |
| ボス向け割合ダメージ | `MaxHpPercentage` | `5` = 最大HPの5% |
| 回復（最大HP基準） | `MaxHpPercentage` | `30` = 最大HPの30%回復 |
| 固定値ダメージ・回復 | `Fixed` | `500` = 固定500ダメージ |

### hit_effect_id の設定

- `hit_effect_id` は `MstAttackHitEffect.id` を参照する（演出アセット・SEキーを管理するテーブル）
- ヒットエフェクトが不要な副次エレメント（バフ付与のみ等）では空文字 `""` を設定する
- 主要なダメージエレメントには適切な `hit_effect_id` を設定することで打撃感を演出できる

### よくあるミス

- `damage_type = Damage` なのに `power_parameter = 0` にするとノーダメージになる
- `effect_type` に効果を設定したが `effective_duration = 0` のままで効果が即座に消える
- `target = Foe` で回復（`damage_type = Heal`）を設定しても敵のHPは増えない（`target = Friend` を使うこと）
- Deck型（`attack_type = Deck`）では `range_*` の設定は無意味になる場合がある
