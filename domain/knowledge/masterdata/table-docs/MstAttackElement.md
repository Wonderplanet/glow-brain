# MstAttackElement 詳細説明

> CSVパス: `projects/glow-masterdata/MstAttackElement.csv`

---

## 概要

MstAttackElement は**攻撃の個別エレメント（実効果）**を管理するテーブル。1つの攻撃（MstAttack）に対して1件以上のエレメントが紐づき、`sort_order` の順に処理される。

各エレメントは「攻撃タイプ・対象・範囲・ダメージ種別・hit演出・状態効果（バフ/デバフ）」を1セットとして定義する。1つの攻撃で複数の効果（例: ダメージ + 毒付与）を実現する場合は、エレメントを複数レコード設定する。

```
MstAttack.id
  └─ MstAttackElement.mst_attack_id（N件紐づく）
       └─ sort_order 順に順次処理
```

総レコード数: **4,264件**（2026年3月時点）

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| `ENABLE` | varchar | ○ | `e` = 有効 |
| `release_key` | bigint | | リリースキー |
| `id` | varchar | ○ | エレメントID（命名規則あり。後述） |
| `mst_attack_id` | varchar | ○ | 親攻撃ID（MstAttack.id を参照） |
| `sort_order` | bigint | ○ | 同一 mst_attack_id 内での実行順序（昇順） |
| `attack_delay` | bigint | ○ | このエレメント発動までの追加ディレイ（フレーム） |
| `attack_type` | varchar | ○ | 攻撃タイプ（後述） |
| `range_start_type` | varchar | ○ | 範囲開始点の基準（後述） |
| `range_start_parameter` | bigint | ○ | 範囲開始パラメータ（range_start_type に応じた値） |
| `range_end_type` | varchar | ○ | 範囲終了点の基準（後述） |
| `range_end_parameter` | float | ○ | 範囲終了パラメータ |
| `max_target_count` | bigint | ○ | 最大ターゲット数（何体まで当たるか） |
| `target` | varchar | ○ | 攻撃対象の陣営（後述） |
| `target_type` | varchar | ○ | 対象の絞り込み種別（後述） |
| `target_colors` | varchar | | 対象カラー絞り込み（空欄 or `All` = 全色） |
| `target_roles` | varchar | | 対象ロール絞り込み（空欄 or `All` = 全ロール） |
| `target_mst_series_ids` | varchar | | 対象シリーズID絞り込み（空欄 = 全シリーズ） |
| `target_mst_character_ids` | varchar | | 対象キャラクターID絞り込み（空欄 = 全キャラ） |
| `damage_type` | varchar | ○ | ダメージ種別（後述） |
| `hit_type` | varchar | ○ | ヒット演出・ノックバック種別（後述） |
| `hit_parameter1` | bigint | | ヒット種別に応じた追加パラメータ1 |
| `hit_parameter2` | bigint | | ヒット種別に応じた追加パラメータ2 |
| `hit_effect_id` | varchar | | ヒットエフェクトID（MstAttackHitEffect.id を参照） |
| `is_hit_stop` | varchar | | ヒットストップ有無（`1` = あり / 空欄 = なし） |
| `probability` | bigint | ○ | 発動確率（1〜100。通常は100） |
| `power_parameter_type` | varchar | ○ | 威力パラメータの種類（後述） |
| `power_parameter` | float | ○ | 威力値（power_parameter_type に応じた解釈） |
| `effect_type` | varchar | ○ | 付与する状態効果（後述） |
| `effective_count` | bigint | | 状態効果の適用回数（`-1` = 無制限） |
| `effective_duration` | bigint | | 状態効果の持続フレーム数（`0` = 効果なし） |
| `effect_parameter` | varchar | | 状態効果のパラメータ（効果量など。数値文字列） |
| `effect_value` | varchar | | 状態効果の追加値（用途は effect_type による） |
| `effect_trigger_roles` | varchar | | 状態効果の発動トリガーロール（空欄 = 全ロール） |
| `effect_trigger_colors` | varchar | | 状態効果の発動トリガーカラー（空欄 = 全色） |

---

## attack_type（攻撃タイプ）

| 値 | 説明 |
|----|------|
| `Direct` | 直接攻撃。範囲内の対象に直接ダメージ・効果を与える（最も一般的） |
| `Deck` | デッキへの攻撃・効果。プレイヤーのデッキに作用する |
| `PlaceItem` | アイテム設置型。指定範囲にアイテム・ギミックを置く |
| `BattleSide` | バトルサイド（陣営全体）への攻撃 |
| `None` | 攻撃なし（状態効果のみ付与する場合など） |

---

## target / target_type（対象・対象種別）

### target（陣営）

| 値 | 説明 |
|----|------|
| `Foe` | 敵陣営（プレイヤーが敵なら敵キャラが対象） |
| `Friend` | 味方陣営（自分の陣営のキャラ） |
| `Self` | 自分自身のみ |
| `FriendOnly` | 友軍のみ（Friendとの違いは実装依存） |

### target_type（絞り込み）

| 値 | 説明 |
|----|------|
| `All` | 全ユニット（キャラ・アウトポスト含む） |
| `Character` | キャラクターのみ（アウトポスト除外） |
| `OutpostAndDefenseTarget` | アウトポストと防衛ターゲットのみ |

---

## damage_type（ダメージ種別）

| 値 | 説明 |
|----|------|
| `None` | ダメージなし（バフ・デバフ・回復以外の効果のみ） |
| `Damage` | 通常ダメージ |
| `PoisonDamage` | 毒ダメージ（毒耐性で軽減可能） |
| `BurnDamage` | 炎ダメージ（炎耐性で軽減可能） |
| `SlipDamage` | スリップダメージ（継続ダメージ） |
| `RushDamage` | ラッシュダメージ（特定条件でのダメージ） |
| `Heal` | 回復（HPを回復する） |

---

## hit_type（ヒット演出・ノックバック）

| 値 | 説明 |
|----|------|
| `Normal` | 通常ヒット（ノックバックなし） |
| `KnockBack1` | ノックバックレベル1（弱） |
| `KnockBack2` | ノックバックレベル2（中） |
| `KnockBack3` | ノックバックレベル3（強） |
| `ForcedKnockBack1` | 強制ノックバックレベル1 |
| `ForcedKnockBack2` | 強制ノックバックレベル2 |
| `ForcedKnockBack3` | 強制ノックバックレベル3 |
| `ForcedKnockBack5` | 強制ノックバックレベル5（最強） |
| `AccumulatedDamageKnockBack` | 蓄積ダメージでのノックバック |
| `Drain` | ドレイン（攻撃した分のHPを自分に回収） |
| `Stun` | スタン（対象を一時行動不能にする） |
| `Freeze` | 凍結（対象を凍結状態にする） |

---

## power_parameter_type（威力パラメータ種別）

| 値 | 説明 |
|----|------|
| `Percentage` | 攻撃力の割合（%）。`power_parameter=100` → ATKの100%ダメージ |
| `Fixed` | 固定値。`power_parameter` の値がそのまま適用される |
| `MaxHpPercentage` | 対象の最大HPに対する割合（%）。例: 回復量をMaxHPの10%にする |

---

## range_start_type / range_end_type（範囲指定基準）

| 値 | 説明 |
|----|------|
| `Distance` | 距離ベース（ユニットからの距離で範囲を指定） |
| `Koma` | コマ位置ベース（コマのグリッド位置で範囲を指定） |
| `KomaLine` | コマライン（行）ベース |
| `Page` | ページ（画面）ベース |

---

## effect_type（状態効果種別）

主要な値のみ抜粋（StateEffectType enum）:

| 値 | 説明 |
|----|------|
| `None` | 状態効果なし |
| `AttackPowerUp` | 攻撃力アップ |
| `AttackPowerDown` | 攻撃力ダウン |
| `DamageCut` | ダメージカット |
| `MoveSpeedUp` | 移動速度アップ |
| `MoveSpeedDown` | 移動速度ダウン |
| `Poison` | 毒（継続ダメージ付与） |
| `PoisonBlock` | 毒無効 |
| `PoisonDamageCut` | 毒ダメージカット |
| `Burn` | 炎（継続ダメージ付与） |
| `BurnBlock` | 炎無効 |
| `BurnDamageCut` | 炎ダメージカット |
| `Stun` | スタン付与 |
| `StunBlock` | スタン無効 |
| `Freeze` | 凍結付与 |
| `FreezeBlock` | 凍結無効 |
| `KnockBackBlock` | ノックバック無効 |
| `ForcedKnockBackBlock` | 強制ノックバック無効 |
| `Weakening` | 弱体化 |
| `WeakeningBlock` | 弱体化無効 |
| `Unbeatable` | 無敵 |
| `DamageCut` | ダメージカット |
| `RemoveBuff` | バフ除去 |
| `RemoveDebuff` | デバフ除去 |
| `Guts` | ガッツ（HPが0になっても1残る） |
| `SpecialAttackCoolTimeShorten` | スペシャル攻撃クールタイム短縮 |
| `SummonCoolTimeShorten` | 召喚クールタイム短縮 |
| `RushAttackPowerUp` | ラッシュ攻撃力アップ |

---

## 命名規則 / IDの生成ルール

### 基本パターン

```
# 1件目のエレメント（sort_order=1）はmst_attack_idと同じ
{mst_attack_id}

# 2件目以降は末尾にアルファベットを付与
{mst_attack_id}a
{mst_attack_id}b
...
```

### 現行データの例

| id | mst_attack_id | sort_order | 用途 |
|----|---------------|------------|------|
| `chara_dan_00001_Normal_00000` | `chara_dan_00001_Normal_00000` | 1 | 通常攻撃の主効果 |
| `chara_dan_00001_Special_00001` | `chara_dan_00001_Special_00001` | 1 | スペシャル攻撃の主効果 |
| `chara_dan_00001_Special_00001a` | `chara_dan_00001_Special_00001` | 2 | スペシャル攻撃の追加効果 |
| `chara_dan_00001_Special_00002a` | `chara_dan_00001_Special_00002` | 2 | グレード2の追加効果 |

---

## 他テーブルとの連携

### MstAttack との関係

| カラム | 参照先 | 説明 |
|--------|--------|------|
| `mst_attack_id` | `mst_attacks.id` | 親攻撃定義（フレーム・キラー設定） |

### MstAttackHitEffect との関係

| カラム | 参照先 | 説明 |
|--------|--------|------|
| `hit_effect_id` | `mst_attack_hit_effects.id` | ヒット時のエフェクト演出 |

---

## 実データ例

### パターン1: シンプルなダメージ攻撃（1エレメント）

```
id:                   chara_dan_00001_Normal_00000
mst_attack_id:        chara_dan_00001_Normal_00000
sort_order:           1
attack_type:          Direct
target:               Foe
target_type:          All
target_colors:        All
target_roles:         All
damage_type:          Damage
hit_type:             Normal
hit_effect_id:        dageki_1
is_hit_stop:          1
probability:          100
power_parameter_type: Percentage
power_parameter:      100.0
effect_type:          None
effective_count:      0
effective_duration:   0
effect_parameter:     0
range_start_type:     Distance
range_start_parameter: 0
range_end_type:       Distance
range_end_parameter:  0.17
max_target_count:     1
```

- ATKの100%ダメージを最大1体に与える基本近接攻撃
- `range_end_parameter=0.17` は近接距離の判定範囲

### パターン2: バフ付与（ダメージなし、2エレメント構成）

```
# エレメント1（主効果: 攻撃）
id:                   chara_dan_00001_Special_00001
damage_type:          None
power_parameter:      0.0
effect_type:          None

# エレメント2（追加効果: DamageCut付与）
id:                   chara_dan_00001_Special_00001a
sort_order:           2
target:               Self
damage_type:          None
effect_type:          DamageCut
effective_count:      -1
effective_duration:   300
effect_parameter:     10
```

- エレメント1で攻撃、エレメント2で自身にDamageCutバフを付与する複合攻撃
- `effective_count=-1` は無制限適用、`effective_duration=300` フレーム（5秒）持続

---

## 設定時のポイントと注意事項

### ポイント1: sort_order の重要性

- 同一 `mst_attack_id` 内で `sort_order` 順に処理される
- 最初のエレメントが「主攻撃」、以降が「追加効果」という構成が一般的
- `sort_order` の値は連番でなくてもよいが、重複は不可

### ポイント2: effect_type と damage_type の使い分け

- ダメージを与えながら状態効果も付与したい場合: `damage_type=Damage` + `effect_type=Poison`（など）を同じエレメントで設定可能
- 状態効果のみ（ダメージなし）の場合: `damage_type=None` + `effect_type={効果名}`

### ポイント3: power_parameter の解釈

| power_parameter_type | power_parameter の意味 |
|---------------------|----------------------|
| `Percentage` | ATKに対する割合（例: `100` = ATKの100%） |
| `Fixed` | 固定ダメージ値（ATKと無関係） |
| `MaxHpPercentage` | 対象MaxHPに対する割合（例: `10` = MaxHPの10%回復） |

### ポイント4: 回復エレメントの設定

- `damage_type=Heal` + `power_parameter_type=MaxHpPercentage` + `target=Self` で自己回復
- `target=Friend` にすると味方全体への回復効果になる

### ポイント5: range_start/range_end_parameter の単位

- `Distance` 基準の場合: ゲーム内の距離単位（0.17 ≈ 近接攻撃リーチ、0.5以上が遠距離目安）
- `Koma` 基準の場合: コマグリッドのインデックス単位
- `Page` 基準の場合: ページ（画面）インデックス

### ポイント6: 毒・炎ダメージの軽減システムとの対応

- `damage_type=PoisonDamage` → `effect_type=PoisonDamageCut` で軽減可能
- `damage_type=BurnDamage` → `effect_type=BurnDamageCut` で軽減可能
- VD設計時、対抗キャラの軽減属性に合わせて `damage_type` を選択することで対抗効果を実現する
