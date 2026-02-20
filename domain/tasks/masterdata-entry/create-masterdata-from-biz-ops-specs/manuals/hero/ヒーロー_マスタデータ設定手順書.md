# ヒーロー（ユニット） マスタデータ設定手順書

## 概要

ヒーロー（プレイアブルキャラクター）のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

ヒーローのマスタデータは、以下の13テーブル構成で作成します。

**ユニット基本情報**:
- **MstUnit** - ユニットの基本情報（ステータス、コスト、レアリティ等）
- **MstUnitI18n** - ユニット名・フレーバーテキスト（多言語対応）
- **MstUnitAbility** - ユニットとアビリティの紐付け
- **MstUnitSpecificRankUp** - イベント配布キャラ専用ランクアップ設定

**アビリティ情報**:
- **MstAbility** - アビリティ定義（体力条件、効果値等）
- **MstAbilityI18n** - アビリティ説明文（多言語対応）

**攻撃アクション情報**:
- **MstAttack** - 通常攻撃・必殺技の定義（グレード0~9）
- **MstAttackElement** - 攻撃の詳細設定（152要素）
- **MstAttackI18n** - 攻撃説明文（多言語対応）
- **MstSpecialAttackI18n** - 必殺技名（多言語対応）
- **MstSpeechBalloonI18n** - 吹き出しセリフ（多言語対応）

**エネミーキャラクター情報**:
- **MstEnemyCharacter** - 敵キャラクターの基本情報
- **MstEnemyCharacterI18n** - 敵キャラクター名（多言語対応）

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

ヒーロー基礎設計書から以下の情報を抽出します。

**必要情報**:
- キャラクターの基本情報（キャラ名、シリーズ、レアリティ）
- ロールタイプ（Technical、Support、Defense、Special、Attack）
- カラー（Red、Blue、Green、Yellow、Colorless）
- 基礎ステータス（HP、ATK、コスト、移動速度等）
- アビリティ（特性）の定義と効果値
- 通常攻撃・必殺技の設定
- グレード別の成長パラメータ
- 欠片アイテムID（ガチャ排出キャラの場合）
- ランクアップ素材（イベント配布キャラの場合）

### 2. MstUnit シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,fragment_mst_item_id,role_type,color,attack_range_type,unit_label,has_specific_rank_up,mst_series_id,asset_key,rarity,sort_order,summon_cost,summon_cool_time,special_attack_initial_cool_time,special_attack_cool_time,min_hp,max_hp,damage_knock_back_count,move_speed,well_distance,min_attack_power,max_attack_power,mst_unit_ability_id1,ability_unlock_rank1,mst_unit_ability_id2,ability_unlock_rank2,mst_unit_ability_id3,ability_unlock_rank3,is_encyclopedia_special_attack_position_right,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ユニットの一意識別子。命名規則: `chara_{series_id}_{連番5桁}` |
| **fragment_mst_item_id** | 欠片アイテムID。命名規則: `piece_{series_id}_{連番5桁}` (MstUnit.idのcharaをpieceに置換) |
| **role_type** | ロールタイプ。下記の「role_type設定一覧」を参照 |
| **color** | カラー属性。下記の「color設定一覧」を参照 |
| **attack_range_type** | 攻撃範囲タイプ。`Short`、`Long`、`None`（サポート系） |
| **unit_label** | ユニットラベル。下記の「unit_label設定一覧」を参照 |
| **has_specific_rank_up** | イベント配布キャラ専用ランクアップ有無。`0`: なし、`1`: あり |
| **mst_series_id** | シリーズID。例: `jig`（地獄楽）、`osh`（推しの子）、`kai`（怪獣8号） |
| **asset_key** | アセットキー。通常はMstUnit.idと同じ値 |
| **rarity** | レアリティ。下記の「rarity設定一覧」を参照 |
| **sort_order** | 表示順序。通常は `1` |
| **summon_cost** | 召喚コスト。レアリティに応じて設定 |
| **summon_cool_time** | 召喚クールタイム（ミリ秒）。レアリティに応じて設定 |
| **special_attack_initial_cool_time** | 必殺技初回クールタイム（ミリ秒） |
| **special_attack_cool_time** | 必殺技クールタイム（ミリ秒） |
| **min_hp** | 最小HP（グレード0） |
| **max_hp** | 最大HP（グレード9） |
| **damage_knock_back_count** | ノックバック耐性回数 |
| **move_speed** | 移動速度 |
| **well_distance** | ウェル距離。通常は `0.31` 程度（サポート系は適宜調整） |
| **min_attack_power** | 最小攻撃力（グレード0） |
| **max_attack_power** | 最大攻撃力（グレード9） |
| **mst_unit_ability_id1** | アビリティ1のID。MstUnitAbility.idと対応 |
| **ability_unlock_rank1** | アビリティ1の解放ランク。`0`: 初期から、`1`以上: ランクアップ後 |
| **mst_unit_ability_id2** | アビリティ2のID。存在しない場合は空欄 |
| **ability_unlock_rank2** | アビリティ2の解放ランク。存在しない場合は `0` |
| **mst_unit_ability_id3** | アビリティ3のID。存在しない場合は空欄 |
| **ability_unlock_rank3** | アビリティ3の解放ランク。存在しない場合は `0` |
| **is_encyclopedia_special_attack_position_right** | 図鑑での必殺技演出の位置。`0`: 左、`1`: 右 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 role_type設定一覧

ヒーローで使用可能なrole_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| role_type | 説明 | 特徴 |
|----------|------|------|
| **Technical** | テクニカル | バランス型、多様な戦術に対応 |
| **Support** | サポート | 味方の支援に特化 |
| **Defense** | ディフェンス | 耐久力に優れる |
| **Special** | スペシャル | 特殊な能力を持つ |
| **Attack** | アタック | 攻撃力に優れる |
| **Balance** | バランス | 全体的にバランスが良い |
| **Unique** | ユニーク | 独自の特性を持つ |
| **None** | なし | 未分類（通常は使用しない） |

**頻繁に使用されるrole_type**:
- Technical（最も使用頻度が高い）
- Support
- Defense
- Special
- Attack

#### 2.4 color設定一覧

| color | 説明 | 使用例 |
|-------|------|--------|
| **Colorless** | 無色 | 属性に依存しないユニット |
| **Red** | 赤 | 火属性、攻撃型 |
| **Blue** | 青 | 水属性、防御型 |
| **Yellow** | 黄 | 雷属性、速攻型 |
| **Green** | 緑 | 木属性、サポート型 |

#### 2.5 rarity設定一覧

| rarity | 説明 | レアリティ |
|--------|------|-----------|
| **UR** | Ultra Rare | 最高レアリティ |
| **SSR** | Super Super Rare | 非常に高レアリティ |
| **SR** | Super Rare | 高レアリティ |
| **R** | Rare | 中レアリティ |
| **N** | Normal | 通常レアリティ |

#### 2.6 unit_label設定一覧

| unit_label | 説明 | 入手方法 |
|-----------|------|----------|
| **PremiumUR** | プレミアムUR | ガチャ排出（最高レア） |
| **PremiumSSR** | プレミアムSSR | ガチャ排出（高レア） |
| **PremiumSR** | プレミアムSR | ガチャ排出（中レア） |
| **PremiumR** | プレミアムR | ガチャ排出（低レア） |
| **FestivalUR** | フェスティバルUR | 限定ガチャ排出 |
| **DropUR** | ドロップUR | イベント配布・ドロップ |
| **DropSSR** | ドロップSSR | イベント配布・ドロップ |
| **DropSR** | ドロップSR | イベント配布・ドロップ |
| **DropR** | ドロップR | イベント配布・ドロップ |

**重要**: `Premium*` はガチャ排出キャラ、`Drop*` はイベント配布キャラです。

#### 2.7 ID採番ルール

ヒーローのIDは、以下の形式で採番します。

```
chara_{series_id}_{連番5桁}
```

**パラメータ**:
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `連番5桁`: シリーズ内で001からゼロパディング

**採番例**:
```
chara_jig_00401   (地獄楽 キャラ401)
chara_osh_00601   (推しの子 キャラ601)
chara_kai_00701   (怪獣8号 キャラ701)
```

**fragment_mst_item_idの採番**:
```
piece_{series_id}_{連番5桁}
```

MstUnit.idの`chara`を`piece`に置換した形式です。

**採番例**:
```
piece_jig_00401   (地獄楽 キャラ401の欠片)
```

#### 2.8 作成例

```
ENABLE,id,fragment_mst_item_id,role_type,color,attack_range_type,unit_label,has_specific_rank_up,mst_series_id,asset_key,rarity,sort_order,summon_cost,summon_cool_time,special_attack_initial_cool_time,special_attack_cool_time,min_hp,max_hp,damage_knock_back_count,move_speed,well_distance,min_attack_power,max_attack_power,mst_unit_ability_id1,ability_unlock_rank1,mst_unit_ability_id2,ability_unlock_rank2,mst_unit_ability_id3,ability_unlock_rank3,is_encyclopedia_special_attack_position_right,release_key
e,chara_jig_00401,piece_jig_00401,Technical,Colorless,Short,PremiumUR,0,jig,chara_jig_00401,UR,1,1000,770,655,1140,2100,21000,3,30,0.31,2500,25000,ability_jig_00401_01,0,ability_jig_00401_02,4,,0,0,202601010
e,chara_jig_00501,piece_jig_00501,Support,Green,Short,PremiumSSR,0,jig,chara_jig_00501,SSR,1,605,425,395,1740,1160,11600,2,30,0.32,1200,12000,ability_jig_00501_01,0,,0,,0,0,202601010
e,chara_jig_00601,piece_jig_00601,Defense,Blue,Short,DropSR,1,jig,chara_jig_00601,SR,1,440,920,480,1195,2510,25100,1,35,0.21,880,8800,ability_jig_00601_01,2,,0,,0,0,202601010
```

#### 2.9 ステータス設定のポイント

- **min_hp / max_hp**: グレード0とグレード9の値を設定（max_hp = min_hp × 10が基本）
- **min_attack_power / max_attack_power**: グレード0とグレード9の値を設定（max_attack_power = min_attack_power × 10が基本）
- **summon_cost**: レアリティに応じて設定（UR: 1000程度、SSR: 605程度、SR: 440程度）
- **summon_cool_time**: レアリティに応じて設定（UR: 770程度、SSR: 425程度、SR: 920程度）
- **special_attack_initial_cool_time / special_attack_cool_time**: ユニットの性能に応じて調整

### 3. MstUnitI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,id,mst_unit_id,locale,unit_name,flavor_text,release_key
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_unit_id}_i18n_{locale}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **unit_name** | ユニット名 |
| **flavor_text** | フレーバーテキスト（キャラクター説明） |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 3.3 作成例

```
ENABLE,id,mst_unit_id,locale,unit_name,flavor_text,release_key
e,chara_jig_00401_i18n_ja,chara_jig_00401,ja,【賊王】亜左 弔兵衛,幕府の重罪人たちが収容された孤島「獄門島」の主。1000人以上の盗賊を束ねる、神出鬼没の「賊王」。,202601010
```

### 4. MstUnitAbility シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,mst_unit_id,mst_ability_id,ability_parameter1,ability_parameter2,ability_parameter3,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ユニットアビリティの一意識別子。命名規則: `ability_{series_id}_{連番5桁}_{アビリティ連番2桁}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **mst_ability_id** | アビリティID。MstAbility.idと対応 |
| **ability_parameter1** | アビリティパラメータ1。効果値（例: 攻撃力UP%） |
| **ability_parameter2** | アビリティパラメータ2。条件値（例: HP閾値%） |
| **ability_parameter3** | アビリティパラメータ3。追加条件（通常は空欄） |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 4.3 ID採番ルール

```
ability_{series_id}_{連番5桁}_{アビリティ連番2桁}
```

**パラメータ**:
- `series_id`: MstUnit.idと同じシリーズID
- `連番5桁`: MstUnit.idと同じ連番
- `アビリティ連番2桁`: 01から順番に採番

**採番例**:
```
ability_jig_00401_01   (chara_jig_00401のアビリティ1)
ability_jig_00401_02   (chara_jig_00401のアビリティ2)
```

#### 4.4 作成例

```
ENABLE,id,mst_unit_id,mst_ability_id,ability_parameter1,ability_parameter2,ability_parameter3,release_key
e,ability_jig_00401_01,chara_jig_00401,ability_attack_power_up_by_hp_percentage_less,50,30,,202601010
e,ability_jig_00401_02,chara_jig_00401,ability_damage_cut_by_hp_percentage_over,30,50,,202601010
```

上記の例:
- アビリティ1: HP30%以下の時、攻撃力50%UP
- アビリティ2: HP50%以上の時、ダメージ30%カット

### 5. MstAbility シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,ability_type,asset_key,release_key
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | アビリティの一意識別子。ability_typeと同じ値を使用 |
| **ability_type** | アビリティタイプ。下記の「ability_type設定一覧」を参照 |
| **asset_key** | アセットキー。通常は空欄 |
| **release_key** | リリースキー。例: `202601010` |

#### 5.3 ability_type設定一覧

現在定義されているability_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| ability_type | 説明 | パラメータ1 | パラメータ2 | パラメータ3 |
|-------------|------|------------|------------|------------|
| **ability_attack_power_up_by_hp_percentage_less** | HP低下時に攻撃力UP | 攻撃力UP% | HP閾値% | - |
| **ability_damage_cut_by_hp_percentage_over** | HP高維持時にダメージカット | ダメージカット% | HP閾値% | - |

**重要**: 新しいアビリティタイプを追加する場合は、命名規則に従ってください。

#### 5.4 作成例

```
ENABLE,id,ability_type,asset_key,release_key
e,ability_attack_power_up_by_hp_percentage_less,ability_attack_power_up_by_hp_percentage_less,,202601010
e,ability_damage_cut_by_hp_percentage_over,ability_damage_cut_by_hp_percentage_over,,202601010
```

### 6. MstAbilityI18n シートの作成

#### 6.1 シートスキーマ

```
ENABLE,id,mst_ability_id,locale,ability_name,ability_description,release_key
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_ability_id}_i18n_{locale}` |
| **mst_ability_id** | アビリティID。MstAbility.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **ability_name** | アビリティ名 |
| **ability_description** | アビリティ説明文。`{0}`、`{1}`、`{2}`でパラメータを参照 |
| **release_key** | リリースキー。MstAbilityと同じ値 |

#### 6.3 作成例

```
ENABLE,id,mst_ability_id,locale,ability_name,ability_description,release_key
e,ability_attack_power_up_by_hp_percentage_less_i18n_ja,ability_attack_power_up_by_hp_percentage_less,ja,低体力攻撃UP,体力{1}%以下時に攻撃を{0}%UP,202601010
e,ability_damage_cut_by_hp_percentage_over_i18n_ja,ability_damage_cut_by_hp_percentage_over,ja,高体力ダメージカット,体力{1}%以上時に被ダメージを{0}%カット,202601010
```

### 7. MstAttack シートの作成

#### 7.1 シートスキーマ

```
ENABLE,id,mst_unit_id,grade,attack_kind,action_frames,release_key
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 攻撃の一意識別子。命名規則: `attack_{series_id}_{連番5桁}_{attack_kind頭文字}_{grade}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **grade** | グレード。`0`～`9`（グレード0が初期、グレード9が最大強化） |
| **attack_kind** | 攻撃種別。下記の「attack_kind設定一覧」を参照 |
| **action_frames** | アクションフレーム数 |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 7.3 attack_kind設定一覧

| attack_kind | 説明 | IDの頭文字 |
|-----------|------|----------|
| **Normal** | 通常攻撃 | `N` |
| **Special** | 必殺技 | `S` |
| **Appearance** | 登場演出 | `A` |

#### 7.4 ID採番ルール

```
attack_{series_id}_{連番5桁}_{attack_kind頭文字}_{grade}
```

**パラメータ**:
- `series_id`: MstUnit.idと同じシリーズID
- `連番5桁`: MstUnit.idと同じ連番
- `attack_kind頭文字`: `N`（Normal）、`S`（Special）、`A`（Appearance）
- `grade`: グレード番号（0～9）

**採番例**:
```
attack_jig_00401_N_0   (chara_jig_00401の通常攻撃グレード0)
attack_jig_00401_S_0   (chara_jig_00401の必殺技グレード0)
```

#### 7.5 作成例

```
ENABLE,id,mst_unit_id,grade,attack_kind,action_frames,release_key
e,attack_jig_00401_N_0,chara_jig_00401,0,Normal,60,202601010
e,attack_jig_00401_S_0,chara_jig_00401,0,Special,120,202601010
```

### 8. MstAttackElement シートの作成

#### 8.1 シートスキーマ

MstAttackElementは152個のカラムを持つ非常に複雑なテーブルです。攻撃の詳細設定を定義します。

```
ENABLE,id,mst_attack_id,element_order,attack_type,damage_type,power_parameter,effect_type,...（他148カラム）
```

**重要**: このテーブルは非常に多くのカラムがあるため、設計書で指定された値のみを設定し、それ以外は空欄またはデフォルト値を使用してください。

#### 8.2 主要カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 攻撃要素の一意識別子。命名規則: `{mst_attack_id}_element_{element_order}` |
| **mst_attack_id** | 攻撃ID。MstAttack.idと対応 |
| **element_order** | 要素の順序。`0`から順番に採番 |
| **attack_type** | 攻撃タイプ。例: `Slash`、`Impact`、`Ranged` |
| **damage_type** | ダメージタイプ。例: `Physical`、`Magical`、`None` |
| **power_parameter** | 威力パラメータ |
| **effect_type** | 効果タイプ。例: `Damage`、`Heal`、`Buff`、`None` |

**重要**: MstAttackElementの詳細な設定は設計書に従ってください。全152カラムの詳細は省略します。

#### 8.3 作成例

```
ENABLE,id,mst_attack_id,element_order,attack_type,damage_type,power_parameter,effect_type,...
e,attack_jig_00401_N_0_element_0,attack_jig_00401_N_0,0,Slash,Physical,100,Damage,...
```

### 9. MstAttackI18n シートの作成

#### 9.1 シートスキーマ

```
ENABLE,id,mst_attack_id,locale,attack_description,release_key
```

#### 9.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_attack_id}_i18n_{locale}` |
| **mst_attack_id** | 攻撃ID。MstAttack.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **attack_description** | 攻撃説明文（多くの場合は空欄） |
| **release_key** | リリースキー。MstAttackと同じ値 |

#### 9.3 作成例

```
ENABLE,id,mst_attack_id,locale,attack_description,release_key
e,attack_jig_00401_N_0_i18n_ja,attack_jig_00401_N_0,ja,,202601010
```

### 10. MstSpecialAttackI18n シートの作成

#### 10.1 シートスキーマ

```
ENABLE,id,mst_unit_id,locale,special_attack_name,release_key
```

#### 10.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_unit_id}_special_attack_i18n_{locale}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **special_attack_name** | 必殺技名 |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 10.3 作成例

```
ENABLE,id,mst_unit_id,locale,special_attack_name,release_key
e,chara_jig_00401_special_attack_i18n_ja,chara_jig_00401,ja,盗賊王の剣戟,202601010
```

### 11. MstSpeechBalloonI18n シートの作成

#### 11.1 シートスキーマ

```
ENABLE,id,mst_unit_id,locale,speech_balloon_text,release_key
```

#### 11.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_unit_id}_speech_balloon_i18n_{locale}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **speech_balloon_text** | 吹き出しセリフ |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 11.3 作成例

```
ENABLE,id,mst_unit_id,locale,speech_balloon_text,release_key
e,chara_jig_00401_speech_balloon_i18n_ja,chara_jig_00401,ja,行くぜ！,202601010
```

### 12. MstUnitSpecificRankUp シートの作成（イベント配布キャラのみ）

**重要**: このシートは`has_specific_rank_up=1`のイベント配布キャラのみ作成します。ガチャ排出キャラ(`has_specific_rank_up=0`)の場合は作成不要です。

#### 12.1 シートスキーマ

```
ENABLE,id,mst_unit_id,rank,mst_item_id_1,amount_1,required_level,release_key
```

#### 12.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ランクアップの一意識別子。命名規則: `{mst_unit_id}_rank_{rank}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **rank** | ランク。`1`～`5` |
| **mst_item_id_1** | ランクアップ素材1のアイテムID。MstItem.idと対応 |
| **amount_1** | ランクアップ素材1の必要個数 |
| **required_level** | 必要レベル |
| **release_key** | リリースキー。MstUnitと同じ値 |

**注**: mst_item_id_2～5、amount_2～5のカラムも存在しますが、通常はmst_item_id_1とamount_1のみ使用します。

#### 12.3 作成例

```
ENABLE,id,mst_unit_id,rank,mst_item_id_1,amount_1,required_level,release_key
e,chara_jig_00601_rank_1,chara_jig_00601,1,item_rankup_common_01,10,5,202601010
e,chara_jig_00601_rank_2,chara_jig_00601,2,item_rankup_common_01,20,10,202601010
```

### 13. MstEnemyCharacter シートの作成

**重要**: 一部のヒーローはエネミーキャラクターとしても登場する場合があります。その場合のみ作成します。

#### 13.1 シートスキーマ

```
ENABLE,id,mst_unit_id,asset_key,release_key
```

#### 13.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | エネミーキャラクターの一意識別子。命名規則: `enemy_{series_id}_{連番5桁}` |
| **mst_unit_id** | ユニットID。MstUnit.idと対応 |
| **asset_key** | アセットキー。通常はMstEnemyCharacter.idと同じ値 |
| **release_key** | リリースキー。MstUnitと同じ値 |

#### 13.3 作成例

```
ENABLE,id,mst_unit_id,asset_key,release_key
e,enemy_jig_00401,chara_jig_00401,enemy_jig_00401,202601010
```

### 14. MstEnemyCharacterI18n シートの作成

#### 14.1 シートスキーマ

```
ENABLE,id,mst_enemy_character_id,locale,enemy_name,release_key
```

#### 14.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | I18nの一意識別子。命名規則: `{mst_enemy_character_id}_i18n_{locale}` |
| **mst_enemy_character_id** | エネミーキャラクターID。MstEnemyCharacter.idと対応 |
| **locale** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **enemy_name** | エネミー名 |
| **release_key** | リリースキー。MstEnemyCharacterと同じ値 |

#### 14.3 作成例

```
ENABLE,id,mst_enemy_character_id,locale,enemy_name,release_key
e,enemy_jig_00401_i18n_ja,enemy_jig_00401,ja,【賊王】亜左 弔兵衛,202601010
```

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstUnit.id: `chara_{series_id}_{連番5桁}`
  - MstUnit.fragment_mst_item_id: `piece_{series_id}_{連番5桁}`
  - MstUnitAbility.id: `ability_{series_id}_{連番5桁}_{アビリティ連番2桁}`
  - MstAttack.id: `attack_{series_id}_{連番5桁}_{attack_kind頭文字}_{grade}`
  - MstAbility.id: ability_typeと同じ値
  - I18n系テーブルのid: `{親テーブルid}_i18n_{locale}` または `{親テーブルid}_{種類}_i18n_{locale}`

- [ ] **リレーションの整合性**
  - `MstUnit.fragment_mst_item_id` が存在する（ガチャ排出キャラの場合）
  - `MstUnit.mst_unit_ability_id1/2/3` が `MstUnitAbility.id` に存在する
  - `MstUnitAbility.mst_unit_id` が `MstUnit.id` に存在する
  - `MstUnitAbility.mst_ability_id` が `MstAbility.id` に存在する
  - `MstAttack.mst_unit_id` が `MstUnit.id` に存在する
  - `MstAttackElement.mst_attack_id` が `MstAttack.id` に存在する
  - すべてのI18nテーブルの親IDが存在する

- [ ] **enum値の正確性**
  - role_type: Technical、Support、Defense、Special、Attack、Balance、Unique、None
  - color: Colorless、Red、Blue、Yellow、Green
  - rarity: UR、SSR、SR、R、N
  - unit_label: PremiumUR、PremiumSSR、PremiumSR、PremiumR、FestivalUR、DropUR、DropSSR、DropSR、DropR
  - attack_kind: Normal、Special、Appearance
  - 大文字小文字が正確に一致している

- [ ] **グレード設定の完全性**
  - MstAttackにグレード0～9の10レコードが存在する（Normal/Special両方）
  - MstAttackElementに対応するMstAttackの全レコードが存在する

- [ ] **数値の妥当性**
  - min_hp、max_hp、min_attack_power、max_attack_powerが正の整数である
  - max_hp ≒ min_hp × 10、max_attack_power ≒ min_attack_power × 10
  - summon_cost、summon_cool_time、special_attack_cool_timeが適切な範囲内

- [ ] **イベント配布キャラ専用設定**
  - `has_specific_rank_up=1` の場合、MstUnitSpecificRankUpが存在する
  - `has_specific_rank_up=0` の場合、MstUnitSpecificRankUpは存在しない

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - 他言語（en、zh-CN、zh-TW）も設定されている

- [ ] **フレーバーテキストの品質**
  - 誤字脱字がない
  - キャラクターの個性が表現されている

- [ ] **アビリティ設定の妥当性**
  - アビリティパラメータが適切な範囲内（0～100%程度）
  - 解放ランクが適切（0～5）

## 出力フォーマット

最終的な出力は以下の13シート構成で行います。

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

### MstAbility シート

| ENABLE | id | ability_type | asset_key | release_key |
|--------|----|-----------|---------|---------||
| e | ability_attack_power_up_by_hp_percentage_less | ability_attack_power_up_by_hp_percentage_less | | 202601010 |

### MstAbilityI18n シート

| ENABLE | id | mst_ability_id | locale | ability_name | ability_description | release_key |
|--------|----|--------------|----|-------------|---------------------|-------------|
| e | ability_attack_power_up_by_hp_percentage_less_i18n_ja | ability_attack_power_up_by_hp_percentage_less | ja | 低体力攻撃UP | 体力{1}%以下時に攻撃を{0}%UP | 202601010 |

### MstAttack シート

| ENABLE | id | mst_unit_id | grade | attack_kind | action_frames | release_key |
|--------|----|-----------|----|-----------|--------------|-------------|
| e | attack_jig_00401_N_0 | chara_jig_00401 | 0 | Normal | 60 | 202601010 |

### MstAttackElement シート

**注**: 152カラムのため詳細は省略。設計書に従って設定してください。

### MstAttackI18n シート

| ENABLE | id | mst_attack_id | locale | attack_description | release_key |
|--------|----|--------------|----|-------------------|-------------|
| e | attack_jig_00401_N_0_i18n_ja | attack_jig_00401_N_0 | ja | | 202601010 |

### MstSpecialAttackI18n シート

| ENABLE | id | mst_unit_id | locale | special_attack_name | release_key |
|--------|----|-----------|----|---------------------|-------------|
| e | chara_jig_00401_special_attack_i18n_ja | chara_jig_00401 | ja | 盗賊王の剣戟 | 202601010 |

### MstSpeechBalloonI18n シート

| ENABLE | id | mst_unit_id | locale | speech_balloon_text | release_key |
|--------|----|-----------|----|---------------------|-------------|
| e | chara_jig_00401_speech_balloon_i18n_ja | chara_jig_00401 | ja | 行くぜ！ | 202601010 |

### MstUnitSpecificRankUp シート（イベント配布キャラのみ）

| ENABLE | id | mst_unit_id | rank | mst_item_id_1 | amount_1 | required_level | release_key |
|--------|----|-----------|----|--------------|---------|---------------|-------------|
| e | chara_jig_00601_rank_1 | chara_jig_00601 | 1 | item_rankup_common_01 | 10 | 5 | 202601010 |

### MstEnemyCharacter シート（必要な場合のみ）

| ENABLE | id | mst_unit_id | asset_key | release_key |
|--------|----|-----------|---------|---------||
| e | enemy_jig_00401 | chara_jig_00401 | enemy_jig_00401 | 202601010 |

### MstEnemyCharacterI18n シート（必要な場合のみ）

| ENABLE | id | mst_enemy_character_id | locale | enemy_name | release_key |
|--------|----|-----------------------|----|-----------|-------------|
| e | enemy_jig_00401_i18n_ja | enemy_jig_00401 | ja | 【賊王】亜左 弔兵衛 | 202601010 |

## 重要なポイント

- **13テーブル構成**: ヒーローは非常に多くのテーブルに関連します
- **I18nは独立したシート**: 各I18nテーブルは独立したシートとして作成
- **グレード0～9の設定**: MstAttackは各attack_kindごとにグレード0～9の10レコードが必要
- **MstAttackElementの複雑性**: 152カラムの設定が必要（設計書に従う）
- **イベント配布キャラの特別処理**: `has_specific_rank_up=1`の場合のみMstUnitSpecificRankUpを作成
- **エネミーキャラクターの条件付き作成**: ヒーローが敵としても登場する場合のみMstEnemyCharacter系を作成
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
