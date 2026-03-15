# MstUnitAbility 特性とインゲームコマ効果 網羅解説

**調査日**: 2026-03-15
**対象**: MstUnit キャラの全特性（ability）とインゲームコマ効果への影響

---

## 1. 特性の全体概要

特性は以下3テーブルの連携で管理される。

```
MstUnit.mst_unit_ability_id1〜3
  → MstUnitAbility.id（パラメータ保持）
    → MstAbility.ability_type（効果種別）
      → MstAbilityI18n（日本語説明）
```

- 1キャラあたり最大3つまで特性を装備可能（`mst_unit_ability_id1〜3`）
- `ability_unlock_rank{N}` で解放ランクを設定（0=最初から有効）
- **実装ファイル一覧は末尾の参考資料を参照**

---

## 2. 全 ability_type 一覧（15種類）

| ability_type | filter_title | description | パラメータ用法 |
|---|---|---|---|
| PoisonDamageCut | 毒ダメージ軽減 | 毒ダメージを{0}%軽減 | Param1=軽減率(%) |
| BurnDamageCut | 火傷ダメージ軽減 | 火傷ダメージを{0}%軽減 | Param1=軽減率(%) |
| Guts | 根性 | 撃破される時に{0}回だけ体力を1残して耐える | Param1=耐える回数, Param2=確率(%) |
| StunBlock | スタン攻撃無効化 | {0}%の確率でスタンを無効化 | Param1=無効化確率(%) |
| FreezeBlock | 氷結攻撃無効化 | {0}%の確率で氷結を無効化 | Param1=無効化確率(%) |
| WeakeningBlock | 弱体化無効化 | 弱体化を無効化 | なし（常に100%） |
| KnockBackBlock | ノックバック無効化 | ノックバック効果を無効化 | なし（常に発動） |
| SlipDamageKomaBlock | ダメージコマ無効化 | ダメージコマ効果を無効化 | なし（常に発動） |
| AttackPowerDownKomaBlock | 攻撃DOWNコマ無効化 | 攻撃DOWNコマ効果を無効化 | なし（常に発動） |
| GustKomaBlock | 突風コマ無効化 | 突風コマ効果を無効化 | なし（常に発動） |
| AttackPowerUpKomaBoost | 攻撃UPコマ効果UP | 攻撃UPコマにいる間、攻撃{0}%UP | Param1=攻撃UP率(%) |
| AttackPowerUpByHpPercentageOver | 体力条件強化(以上/攻撃) | 体力{1}%以上時に攻撃を{0}%UP | Param1=UP率(%), Param2=HP閾値(%) |
| AttackPowerUpByHpPercentageLess | 体力条件強化(以下/攻撃) | 体力{1}%以下時に攻撃を{0}%UP | Param1=UP率(%), Param2=HP閾値(%) |
| DamageCutByHpPercentageOver | 体力条件強化(以上/被ダメージ) | 体力{1}%以上時に被ダメージを{0}%カット | Param1=カット率(%), Param2=HP閾値(%) |
| DamageCutByHpPercentageLess | 体力条件強化(以下/被ダメージ) | 体力{1}%以下時に被ダメージを{0}%カット | Param1=カット率(%), Param2=HP閾値(%) |

> **実装済みだがCSV未使用の特性（クライアントコードのみ）**
> - `AttackPowerUpInNormalKoma`（通常コマで攻撃UP）
> - `MoveSpeedUpInNormalKoma`（通常コマで移動速度UP）
> - `DamageCutInNormalKoma`（通常コマでダメージカット）
> - `WindKomaBoost`（スキーマ定義のみ、未使用）

---

## 3. 発動タイプ別 詳細解説

### 3-A. 常時発動型（召喚時に状態効果付与→以降常に有効）

召喚直後に `StateEffect` として付与され、戦闘中ずっと有効になる。

#### PoisonDamageCut / BurnDamageCut（ダメージ軽減）

| 特性 | 対象コマ効果 | 効果 | 実績パラメータ範囲 |
|------|-------------|------|---------|
| PoisonDamageCut | KomaEffectType.Poison | 毒ダメージを Param1% 軽減 | 5〜15% |
| BurnDamageCut | KomaEffectType.Burn | 火傷ダメージを Param1% 軽減 | 10〜20% |

- どちらも使用キャラ数14で最多クラス

#### Guts（根性）

| 項目 | 内容 |
|------|------|
| 効果 | HPが0になる攻撃を受けてもHP=1で耐える |
| Param1 | 耐える回数（通常は1回） |
| Param2 | 発動確率（通常は100%） |
| 使用キャラ数 | 12 |

#### StunBlock / FreezeBlock（状態異常無効化）

| 特性 | 対象コマ効果 | Param1 | 実績範囲 |
|------|-------------|--------|---------|
| StunBlock | KomaEffectType.Stun | 無効化確率(%) | 25〜100% |
| FreezeBlock | KomaEffectType.Freeze | 無効化確率(%) | 20〜80% |

- 100%設定で完全無効化、50%設定で半分の確率で無効化

#### WeakeningBlock / KnockBackBlock（完全無効化）

| 特性 | 対象コマ効果 | 効果 | 使用キャラ数 |
|------|-------------|------|---------|
| WeakeningBlock | KomaEffectType.Weakening | 弱体化を完全無効化 | 1 |
| KnockBackBlock | ノックバック | ノックバックを完全無効化 | 6 |

---

### 3-B. コマ効果連動型（特定コマに乗っている間のみ発動）

ユニットが乗っているコマのコマ効果に応じて発動/解除される。

#### コマ無効化型（3種）

| 特性 | 対象 KomaEffectType | 効果 | 使用キャラ数 |
|------|-------------------|------|---------|
| SlipDamageKomaBlock | SlipDamage | ダメージコマの地形ダメージを無効化 | **16（最多）** |
| AttackPowerDownKomaBlock | AttackPowerDown | 攻撃DOWN効果を無効化 | 9 |
| GustKomaBlock | Gust | 突風による押し戻し効果を無効化 | 10 |

#### コマ効果強化型（1種）

| 特性 | 対象 KomaEffectType | 効果 | 使用キャラ数 |
|------|-------------------|------|---------|
| AttackPowerUpKomaBoost | AttackPowerUp | 攻撃UPコマの効果に Param1% を追加でブースト | 10 |

実装詳細：
- `TargetKomaEffectDictionary` に登録された特性が、対象コマ効果と照合される
- 発動：コマに乗っている間 → StateEffect 付与
- 解除：コマから離れた時 → StateEffect 解除

---

### 3-C. 条件付き発動型（HP閾値で発動/解除）

現在HPが指定条件を満たしている間のみ発動する。

| 特性 | 発動条件 | 効果 | 実績パラメータ |
|------|---------|------|---------|
| AttackPowerUpByHpPercentageOver | 現HP ≥ 最大HP × Param2% | 攻撃 +Param1% | 攻撃UP: 20〜35%, HP閾値: 50〜70% |
| AttackPowerUpByHpPercentageLess | 現HP ≤ 最大HP × Param2% | 攻撃 +Param1% | 攻撃UP: 20〜80%, HP閾値: 30〜80% |
| DamageCutByHpPercentageOver | 現HP ≥ 最大HP × Param2% | 被ダメージ -Param1% | カット: 25〜40%, HP閾値: 30〜70% |
| DamageCutByHpPercentageLess | 現HP ≤ 最大HP × Param2% | 被ダメージ -Param1% | カット: 40〜75%, HP閾値: 30〜50% |

- 毎フレーム判定されて StateEffect が付与/解除される
- `DamageCutByHpPercentageLess` + 75% カットのような設定は瀕死時に強力な防御力を発揮

---

## 4. コマ効果（KomaEffectType）と特性の対応一覧

| KomaEffectType | ブロック可能な特性 | ブースト可能な特性 | 軽減可能な特性 |
|---|---|---|---|
| None（通常コマ） | - | AttackPowerUpInNormalKoma等 | - |
| AttackPowerUp | - | AttackPowerUpKomaBoost | - |
| AttackPowerDown | AttackPowerDownKomaBlock | - | - |
| MoveSpeedUp | - | - | - |
| SlipDamage | SlipDamageKomaBlock | - | - |
| Gust | GustKomaBlock | - | - |
| Poison | - | - | PoisonDamageCut |
| Burn | - | - | BurnDamageCut |
| Stun | StunBlock（確率） | - | - |
| Freeze | FreezeBlock（確率） | - | - |
| Weakening | WeakeningBlock | - | - |
| ノックバック | KnockBackBlock | - | - |

---

## 5. 特性の使用状況サマリー

| mst_ability_id | 対応 ability_type | 使用キャラ数 | 代表的なパラメータ |
|---|---|---:|---|
| ability_slip_damage_koma_block | SlipDamageKomaBlock | **16** | - |
| ability_burn_damage_cut | BurnDamageCut | **14** | 20% |
| ability_poison_damage_cut | PoisonDamageCut | **14** | 10% |
| ability_guts | Guts | 12 | 1回, 100% |
| ability_StunBlock | StunBlock | 11 | 50%確率 |
| ability_attack_power_up_koma_boost | AttackPowerUpKomaBoost | 10 | 20% |
| ability_gust_koma_block | GustKomaBlock | 10 | - |
| ability_attack_power_down_koma_block | AttackPowerDownKomaBlock | 9 | - |
| ability_FreezeBlock | FreezeBlock | 9 | 50%確率 |
| ability_knockback_block | KnockBackBlock | 6 | - |
| ability_attack_power_up_by_hp_percentage_less | AttackPowerUpByHpPercentageLess | 4 | 80%, HP30% |
| ability_attack_power_up_by_hp_percentage_over | AttackPowerUpByHpPercentageOver | 3 | 35%, HP70% |
| ability_damage_cut_by_hp_percentage_over | DamageCutByHpPercentageOver | 3 | 40%, HP70% |
| ability_damage_cut_by_hp_percentage_less | DamageCutByHpPercentageLess | 2 | 75%, HP30% |
| ability_WeakeningBlock | WeakeningBlock | 1 | - |
| **合計** | | **124** | |

---

## 6. クライアント実装フロー

### 特性の発動処理

```
1. 召喚時
   UnitAbilityModelFactory.Create() で UnitAbilityModel 生成

2. 常時発動型
   CharacterUnit召喚 → StateEffect 付与 → 以降ずっと有効

3. コマ効果連動型
   毎フレーム判定: 乗っているコマのKomaEffectTypeを確認
   → 対象コマに乗っている → StateEffect 付与
   → 対象コマから離れた → StateEffect 解除

4. 条件付き発動型
   毎フレーム判定: CommonConditionModel で現在HP% を確認
   → 条件を満たしている → StateEffect 付与
   → 条件を外れた → StateEffect 解除
```

### UnitAbilityType → StateEffectType の変換マッピング

| UnitAbilityType | 生成される StateEffectType |
|---|---|
| SlipDamageKomaBlock | SlipDamageKomaBlock |
| AttackPowerDownKomaBlock | AttackPowerDownKomaBlock |
| GustKomaBlock | GustKomaBlock |
| AttackPowerUpKomaBoost | AttackPowerUpKomaBoost |
| KnockBackBlock | KnockBackBlock |
| Guts | Guts |
| StunBlock | StunBlock |
| FreezeBlock | FreezeBlock |
| WeakeningBlock | WeakeningBlock |
| PoisonDamageCut | PoisonDamageCut |
| BurnDamageCut | BurnDamageCut |
| AttackPowerUpByHpPercentageOver/Less | AttackPowerUpByHpPercentage |
| DamageCutByHpPercentageOver/Less | DamageCutByHpPercentage |

---

## 参考資料

### マスタデータファイル

| ファイル | 内容 |
|---|---|
| `projects/glow-masterdata/MstAbility.csv` | 特性マスタ（ID・型・asset_key） |
| `projects/glow-masterdata/MstAbilityI18n.csv` | 特性日本語説明 |
| `projects/glow-masterdata/MstUnitAbility.csv` | ユニット特性パラメータ |

### クライアント実装ファイル

| ファイル | 役割 |
|---|---|
| `...Domain/Constants/AutoGenerated/UnitAbilityType.cs` | 特性型 enum 定義 |
| `...Domain/Constants/AutoGenerated/StateEffectType.cs` | 状態効果型 enum 定義 |
| `...Domain/Constants/AutoGenerated/KomaEffectType.cs` | コマ効果型 enum 定義 |
| `...Scenes/InGame/Domain/Models/UnitAbilityModel.cs` | 特性の振る舞い定義 |
| `...Scenes/InGame/Domain/Battle/ModelFactories/UnitAbilityModelFactory.cs` | 特性生成ロジック |
| `...Scenes/InGame/Domain/Battle/UpdateProcess/UnitAbilityProcess.cs` | 特性発動処理 |
| `...Core/Data/Translators/MstUnitAbilityModelTranslator.cs` | マスタデータ→ドメイン変換 |
