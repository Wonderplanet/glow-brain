---
name: masterdata-from-bizops-hero
description: ヒーローの運営仕様書からマスタデータCSVを作成するスキル。対象テーブル: 13個(MstUnit, MstUnitI18n, MstUnitAbility, MstAbility, MstAbilityI18n, MstAttack, MstAttackElement, MstAttackI18n, MstSpecialAttackI18n, MstSpeechBalloonI18n, MstUnitSpecificRankUp, MstEnemyCharacter, MstEnemyCharacterI18n)。プレイアブルキャラクターのマスタデータを精度高く作成します。
---

# ヒーロー マスタデータ作成スキル

## 概要

ヒーロー(プレイアブルキャラクター)の運営仕様書からマスタデータCSVを作成します。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の13テーブルを自動生成:

**ユニット基本情報**:
- **MstUnit** - ユニットの基本情報(ステータス、コスト、レアリティ等)
- **MstUnitI18n** - ユニット名・フレーバーテキスト(多言語対応)
- **MstUnitAbility** - ユニットとアビリティの紐付け
- **MstUnitSpecificRankUp** - イベント配布キャラ専用ランクアップ設定(条件付き)

**アビリティ情報**:
- **MstAbility** - アビリティ定義(体力条件、効果値等)
- **MstAbilityI18n** - アビリティ説明文(多言語対応)

**攻撃アクション情報**:
- **MstAttack** - 通常攻撃・必殺技の定義(グレード0～9)
- **MstAttackElement** - 攻撃の詳細設定(152要素)
- **MstAttackI18n** - 攻撃説明文(多言語対応)
- **MstSpecialAttackI18n** - 必殺技名(多言語対応)
- **MstSpeechBalloonI18n** - 吹き出しセリフ(多言語対応)

**エネミーキャラクター情報**(条件付き):
- **MstEnemyCharacter** - 敵キャラクターの基本情報(ヒーローが敵としても登場する場合のみ)
- **MstEnemyCharacterI18n** - 敵キャラクター名(多言語対応)

**重要**: MstUnitSpecificRankUpはhas_specific_rank_up=1の場合のみ、MstEnemyCharacter系はis_enemy_character=1の場合のみ作成します。

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **mst_series_id** | シリーズID(jig/osh/kai/glo) | `jig` |
| **mst_unit_id** | ユニットID | `chara_jig_00401` |
| **character_name** | キャラクター名 | `賊王 亜左 弔兵衛` |
| **unit_label** | ユニットラベル | `PremiumUR`(ガチャ)/`DropSR`(イベント) |
| **has_specific_rank_up** | イベント配布キャラフラグ | `0`(ガチャ排出)/`1`(イベント配布) |
| **is_enemy_character** | 敵キャラクターフラグ | `0`(ヒーローのみ)/`1`(敵としても登場) |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
ヒーローの運営仕様書からマスタデータを作成してください。

添付ファイル:
- ヒーロー基礎設計書_地獄楽_賊王亜左弔兵衛.xlsx

パラメータ:
- release_key: 202601010
- mst_series_id: jig
- mst_unit_id: chara_jig_00401
- character_name: 賊王 亜左 弔兵衛
- unit_label: PremiumUR
- has_specific_rank_up: 0
- is_enemy_character: 0
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- キャラクターの基本情報(キャラ名、シリーズ、レアリティ)
- ロールタイプ(Technical、Support、Defense、Special、Attack)
- カラー(Red、Blue、Green、Yellow、Colorless)
- 基礎ステータス(HP、ATK、コスト、移動速度等)
- アビリティ(特性)の定義と効果値
- 通常攻撃・必殺技の設定
- グレード別の成長パラメータ

**任意情報**:
- 欠片アイテムID(ガチャ排出キャラの場合、記載がない場合はMstUnit.idから自動生成)
- ランクアップ素材(イベント配布キャラの場合、記載がない場合は推測)
- 必殺技名・吹き出しセリフ(記載がない場合は推測)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、以下のテーブルを作成します:

1. **MstUnit** - ユニットの基本情報
2. **MstUnitI18n** - ユニット名・フレーバーテキスト
3. **MstUnitAbility** - ユニットとアビリティの紐付け
4. **MstAbility** - アビリティ定義
5. **MstAbilityI18n** - アビリティ説明文
6. **MstAttack** - 通常攻撃・必殺技(グレード0～9 × 2種類 = 20レコード)
7. **MstAttackElement** - 攻撃の詳細設定(各MstAttackに対応)
8. **MstAttackI18n** - 攻撃説明文
9. **MstSpecialAttackI18n** - 必殺技名
10. **MstSpeechBalloonI18n** - 吹き出しセリフ
11. **MstUnitSpecificRankUp** - イベント配布キャラ専用ランクアップ設定(has_specific_rank_up=1の場合のみ)
12. **MstEnemyCharacter** - 敵キャラクター基本情報(is_enemy_character=1の場合のみ)
13. **MstEnemyCharacterI18n** - 敵キャラクター名(is_enemy_character=1の場合のみ)

#### データ依存関係の自動管理

**重要**: 親テーブルを作成した際は、依存する子テーブルも自動的に生成してください。

**依存関係定義** (`config/table_dependencies.json` 参照):
```json
{
  "MstUnit": ["MstUnitI18n", "MstUnitAbility"]
}
```

**自動生成ロジック**:
1. **MstUnit**を作成 → **MstUnitI18n**を自動生成
   - id: `{parent_id}_i18n_{language}` (例: `chara_jig_00401_i18n_ja`)
   - mst_unit_id: `{parent_id}`
   - unit_name、flavor_textを運営仕様書から抽出

2. **MstUnit**を作成 → **MstUnitAbility**を自動生成
   - id: `ability_{series_id}_{連番5桁}_{アビリティ連番2桁}`
   - mst_unit_id: `{parent_id}`
   - アビリティ情報を運営仕様書から抽出

**実装の流れ**:
```
1. MstUnit作成
   ↓ (自動)
2. MstUnitI18n生成
   ↓ (自動)
3. MstUnitAbility生成
```

この自動生成により、親テーブル未生成による子テーブル欠落を防止できます。

#### ID採番ルール

ヒーローのIDは以下の形式で採番します:

```
MstUnit.id: chara_{series_id}_{連番5桁}
MstUnit.fragment_mst_item_id: piece_{series_id}_{連番5桁}
MstUnitAbility.id: ability_{series_id}_{連番5桁}_{アビリティ連番2桁}
MstAttack.id: attack_{series_id}_{連番5桁}_{attack_kind頭文字}_{grade}
MstEnemyCharacter.id: enemy_{series_id}_{連番5桁}
```

**例**:
```
chara_jig_00401 (地獄楽 キャラ401)
piece_jig_00401 (地獄楽 キャラ401の欠片)
ability_jig_00401_01 (chara_jig_00401のアビリティ1)
attack_jig_00401_N_0 (chara_jig_00401の通常攻撃グレード0)
attack_jig_00401_S_0 (chara_jig_00401の必殺技グレード0)
enemy_jig_00401 (地獄楽 敵キャラ401)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(role_type、color、rarity、unit_label、attack_kind等)
- [ ] グレード設定の完全性(MstAttackにグレード0～9の10レコードが存在する)
- [ ] 数値の妥当性(min_hp、max_hp、min_attack_power、max_attack_power)
- [ ] イベント配布キャラ専用設定(has_specific_rank_up=1の場合、MstUnitSpecificRankUpが存在する)

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `MstUnit.summon_cost`: レアリティからの推測値
- `MstUnit.summon_cool_time`: レアリティからの推測値
- `MstUnit.special_attack_initial_cool_time`: 設計書に記載なく推測
- `MstAttack.action_frames`: 設計書に記載なく推測
- `MstAttackElement.power_parameter`: 設計書に記載なく推測
- `MstUnitAbility.ability_parameter1/2/3`: 設計書に記載なく推測
- `MstSpecialAttackI18n.special_attack_name`: 設計書に記載なく推測
- `MstSpeechBalloonI18n.speech_balloon_text`: 設計書に記載なく推測

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 以下の13シート(条件付きを含む)を作成:
  1. MstUnit
  2. MstUnitI18n
  3. MstUnitAbility
  4. MstAbility
  5. MstAbilityI18n
  6. MstAttack(グレード0～9 × 通常攻撃・必殺技 = 20レコード)
  7. MstAttackElement(MstAttackの各レコードに対応)
  8. MstAttackI18n
  9. MstSpecialAttackI18n
  10. MstSpeechBalloonI18n
  11. MstUnitSpecificRankUp(has_specific_rank_up=1の場合のみ)
  12. MstEnemyCharacter(is_enemy_character=1の場合のみ)
  13. MstEnemyCharacterI18n(is_enemy_character=1の場合のみ)

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### MstUnit.summon_cost
- 値: 1000
- 理由: 設計書にコスト値の記載がなく、レアリティUR標準値を設定
- 確認事項: レアリティに応じた標準値か、他のURキャラと比較して確認してください

### MstSpecialAttackI18n.special_attack_name
- 値: 盗賊王の剣戟
- 理由: 設計書に必殺技名の記載がなく、キャラクター特性から推測
- 確認事項: キャラクター原作に沿った必殺技名か確認してください
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstUnit シート

| ENABLE | id | fragment_mst_item_id | role_type | color | attack_range_type | unit_label | has_specific_rank_up | mst_series_id | asset_key | rarity | sort_order | summon_cost | summon_cool_time | special_attack_initial_cool_time | special_attack_cool_time | min_hp | max_hp | damage_knock_back_count | move_speed | well_distance | min_attack_power | max_attack_power | mst_unit_ability_id1 | ability_unlock_rank1 | mst_unit_ability_id2 | ability_unlock_rank2 | mst_unit_ability_id3 | ability_unlock_rank3 | is_encyclopedia_special_attack_position_right | release_key |
|--------|----|--------------------|----------|-------|------------------|-----------|---------------------|--------------|----------|--------|-----------|------------|----------------|------------------------------|-------------------------|--------|--------|------------------------|-----------|--------------|----------------|----------------|---------------------|---------------------|---------------------|---------------------|---------------------|---------------------|---------------------------------------------|-------------|
| e | chara_jig_00401 | piece_jig_00401 | Technical | Colorless | Short | PremiumUR | 0 | jig | chara_jig_00401 | UR | 1 | 1000 | 770 | 655 | 1140 | 2100 | 21000 | 3 | 30 | 0.31 | 2500 | 25000 | ability_jig_00401_01 | 0 | ability_jig_00401_02 | 4 | | 0 | 0 | 202601010 |

### MstUnitI18n シート

| ENABLE | id | mst_unit_id | locale | unit_name | flavor_text | release_key |
|--------|----|-----------|----|-----------|------------|-------------|
| e | chara_jig_00401_i18n_ja | chara_jig_00401 | ja | 【賊王】亜左 弔兵衛 | 幕府の重罪人たちが収容された孤島「獄門島」の主。1000人以上の盗賊を束ねる、神出鬼没の「賊王」。 | 202601010 |

### MstUnitAbility シート

| ENABLE | id | mst_unit_id | mst_ability_id | ability_parameter1 | ability_parameter2 | ability_parameter3 | release_key |
|--------|----|-----------|--------------|--------------------|--------------------|--------------------|-------------|
| e | ability_jig_00401_01 | chara_jig_00401 | ability_attack_power_up_by_hp_percentage_less | 50 | 30 | | 202601010 |
| e | ability_jig_00401_02 | chara_jig_00401 | ability_damage_cut_by_hp_percentage_over | 30 | 50 | | 202601010 |

### 推測値レポート

#### MstUnit.summon_cost
- **値**: 1000
- **理由**: 設計書にコスト値の記載がなく、レアリティUR標準値を設定
- **確認事項**: レアリティに応じた標準値か、他のURキャラと比較して確認してください

#### MstSpecialAttackI18n.special_attack_name
- **値**: 盗賊王の剣戟
- **理由**: 設計書に必殺技名の記載がなく、キャラクター特性から推測
- **確認事項**: キャラクター原作に沿った必殺技名か確認してください

## 注意事項

### グレード設定について

MstAttackは以下のように作成してください:
- **通常攻撃(Normal)**: グレード0～9の10レコード
- **必殺技(Special)**: グレード0～9の10レコード
- **合計**: 20レコード

各MstAttackレコードに対して、MstAttackElementを作成してください。

### MstAttackElementの設定について

MstAttackElementは152カラムの非常に複雑なテーブルです。設計書に記載された値のみを設定し、それ以外は空欄またはデフォルト値を使用してください。

もし設計書に詳細が記載されていない場合は、以下の基本パターンを使用してください:
- **attack_type**: `Slash`(近接)、`Ranged`(遠距離)、`Impact`(衝撃)
- **damage_type**: `Physical`(物理)、`Magical`(魔法)、`None`(なし)
- **power_parameter**: レアリティに応じて設定(UR: 100程度、SSR: 80程度、SR: 60程度)
- **effect_type**: `Damage`(ダメージ)、`Heal`(回復)、`Buff`(強化)、`None`(なし)

### ステータス設定のポイント

- **min_hp / max_hp**: グレード0とグレード9の値を設定(max_hp = min_hp × 10が基本)
- **min_attack_power / max_attack_power**: グレード0とグレード9の値を設定(max_attack_power = min_attack_power × 10が基本)
- **summon_cost**: レアリティに応じて設定(UR: 1000程度、SSR: 605程度、SR: 440程度)
- **summon_cool_time**: レアリティに応じて設定(UR: 770程度、SSR: 425程度、SR: 920程度)

### イベント配布キャラについて

`has_specific_rank_up=1`の場合、MstUnitSpecificRankUpを作成してください:
- rank 1～5の5レコード
- 各ランクに必要なアイテムIDと個数、必要レベルを設定

ガチャ排出キャラ(`has_specific_rank_up=0`)の場合は作成不要です。

### エネミーキャラクターについて

ヒーローが敵キャラクターとしても登場する場合(`is_enemy_character=1`)、MstEnemyCharacterとMstEnemyCharacterI18nを作成してください。

通常のヒーローのみの場合(`is_enemy_character=0`)は作成不要です。

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `MstUnit.fragment_mst_item_id` → `MstItem.id`(ガチャ排出キャラの場合)
- `MstUnit.mst_unit_ability_id1/2/3` → `MstUnitAbility.id`
- `MstUnitAbility.mst_unit_id` → `MstUnit.id`
- `MstUnitAbility.mst_ability_id` → `MstAbility.id`
- `MstAttack.mst_unit_id` → `MstUnit.id`
- `MstAttackElement.mst_attack_id` → `MstAttack.id`
- すべてのI18nテーブルの親ID → 親テーブルのid

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: MstAttackElementの152カラムすべてを設定する必要がありますか?

**対処法**:
- 設計書に記載された値のみを設定してください
- それ以外は空欄またはデフォルト値を使用
- 主要カラム: attack_type、damage_type、power_parameter、effect_type

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid role_type: technical (expected: Technical)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `technical` → `Technical`, `ur` → `UR`

### Q3: グレード設定が不完全である

**原因**: MstAttackにグレード0～9の10レコードが存在しない

**対処法**:
- 通常攻撃(Normal): グレード0～9の10レコード
- 必殺技(Special): グレード0～9の10レコード
- 合計20レコードを必ず作成

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
