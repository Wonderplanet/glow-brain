# キャラクター・ユニット追加 マスタデータ設定手順書

## 概要

新規ユニット（キャラクター）を追加する際の設定手順書。ユニット本体の能力値からアビリティ・攻撃・必殺ワザ名・アイテムまで全テーブルをカバーする。

- **report.md 対応セクション**: `### 4. 新規ユニット`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstItem | ユニットかけら（フラグメントアイテム） | 必須 |
| 2 | MstItemI18n | アイテム多言語名 | 必須 |
| 3 | MstAbility | アビリティ種別（既存参照可） | 条件付き必須 |
| 4 | MstAbilityI18n | アビリティ多言語名 | 条件付き必須 |
| 5 | MstUnitAbility | ユニット固有アビリティ設定 | 必須 |
| 6 | MstAttack | 通常攻撃・必殺ワザ性能 | 必須 |
| 7 | MstAttackI18n | 通常攻撃多言語名 | 任意 |
| 8 | MstSpecialAttackI18n | 必殺ワザ多言語名 | 必須 |
| 9 | MstAttackElement | 攻撃属性 | 条件付き必須 |
| 10 | MstUnit | ユニット本体 | 必須 |
| 11 | MstUnitI18n | ユニット多言語名・説明 | 必須 |
| 12 | MstSpeechBalloonI18n | ゲーム内吹き出しテキスト | 必須 |

---

## 前提条件・依存関係

- MstSeries が登録済みであること（`01_event.md` 参照）
- MstAbility は既存を再利用する場合が多い（新アビリティの場合のみ追加）
- MstItem は MstUnit より先に登録（MstUnit.fragment_mst_item_id が外部参照）

---

## report.md から読み取る情報チェックリスト

- [ ] ユニット一覧（ID・名前・レアリティ）
- [ ] 各ユニットの属性（color: Red/Yellow/Green/Blue）
- [ ] ロールタイプ（role_type: Attack/Technical/Defense）
- [ ] 攻撃範囲（attack_range_type: Short/Middle/Long）
- [ ] 必殺ワザ名
- [ ] アビリティ説明（既存アビリティか新規か判断）
- [ ] unit_label（PremiumUR/PremiumSSR/DropSR 等）

---

## テーブル別設定手順

### MstItem（ユニットかけら）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `piece_{series_id}_{連番5桁}` | `piece_you_00001` |
| item_type | 常に `UnitPiece` | `UnitPiece` |
| asset_key | id と同じ | `piece_you_00001` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, item_type, asset_key, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstItem.csv')
WHERE id LIKE 'piece_%';
```

---

### MstUnitAbility（ユニット固有アビリティ設定）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `ability_{series_id}_{unit連番5桁}_{スロット番号2桁}` | `ability_you_00001_01` |
| mst_ability_id | 使用するアビリティ種別 ID | `ability_attack_power_up_by_hp_percentage_less` |
| ability_parameter1 | アビリティ数値パラメータ1 | `55` |
| ability_parameter2 | アビリティ数値パラメータ2 | `30` |
| ability_parameter3 | アビリティ数値パラメータ3 | `0` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_ability_id, ability_parameter1, ability_parameter2, ability_parameter3
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstUnitAbility.csv');
```

---

### MstAttack（通常攻撃・必殺ワザ性能）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{unit_id}_{attack_kind}_{grade5桁}` | `chara_you_00001_Normal_00000` |
| mst_unit_id | 対象ユニット ID | `chara_you_00001` |
| unit_grade | ランク（0=初期, 1=ランク1, ...） | `0` |
| attack_kind | `Normal` or `Special` | `Special` |
| killer_colors | 特攻属性（Red/Yellow/Green/Blue, NULL=なし） | `Green` |
| killer_percentage | 特攻倍率（%） | `195` |
| action_frames | 攻撃アクションフレーム数 | `89` |
| attack_delay | 攻撃発生ディレイ | `0` |
| next_attack_interval | 次攻撃までのインターバル | `0` |
| asset_key | アセットキー（通常 NULL） | `NULL` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_unit_id, unit_grade, attack_kind, killer_colors, killer_percentage
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstAttack.csv')
ORDER BY mst_unit_id, unit_grade;
```

**よくある設定パターン**
- Normal 攻撃: unit_grade=0, attack_kind=Normal
- Special 攻撃: unit_grade=1以上, attack_kind=Special
- ランクアップごとに killer_percentage が上昇（例: 195, 202, ...）

---

### MstSpecialAttackI18n（必殺ワザ多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `82` |
| release_key | 今回のリリースキー | `202602015` |
| mst_unit_id | 対象ユニット ID | `chara_you_00001` |
| language | 言語コード（通常 `ja`） | `ja` |
| name | 必殺ワザ名 | `お遊戯の時間です` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_unit_id, language, name, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstSpecialAttackI18n.csv')
ORDER BY id;
```

---

### MstUnit（ユニット本体）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `chara_{series_id}_{連番5桁}` | `chara_you_00001` |
| fragment_mst_item_id | 対応するかけら ID（MstItem.id） | `piece_you_00001` |
| role_type | ロール（Attack/Technical/Defense） | `Attack` |
| color | 属性（Red/Yellow/Green/Blue） | `Red` |
| attack_range_type | 攻撃範囲（Short/Middle/Long） | `Middle` |
| unit_label | ラベル（PremiumUR/PremiumSSR/DropSR/DropSSR） | `PremiumUR` |
| has_specific_rank_up | 専用ランクアップあり（0 or 1） | `0` |
| mst_series_id | シリーズ ID | `you` |
| asset_key | id と同じ | `chara_you_00001` |
| rarity | レアリティ（UR/SSR/SR/R） | `UR` |
| sort_order | 表示順（通常 1） | `1` |
| summon_cost | 召喚コスト（UR:960, SSR:750, SR:540） | `960` |
| summon_cool_time | 召喚クールタイム | `740` |
| special_attack_initial_cool_time | 必殺ワザ初期CT | `355` |
| special_attack_cool_time | 必殺ワザCT | `890` |
| min_hp | 初期HP | `2020` |
| max_hp | 最大HP（通常 min_hp × 10） | `20200` |
| damage_knock_back_count | ノックバック回数 | `2` |
| move_speed | 移動速度 | `45` |
| well_distance | 射程距離 | `0.34` |
| min_attack_power | 初期攻撃力 | `4040` |
| max_attack_power | 最大攻撃力（通常 min × 10） | `40400` |
| mst_unit_ability_id1〜3 | アビリティ ID | `ability_you_00001_01` |
| ability_unlock_rank1〜3 | アビリティ解放ランク | `0` |
| is_encyclopedia_special_attack_position_right | 図鑑での必殺ワザ位置（0=左, 1=右） | `0` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, role_type, color, attack_range_type, unit_label, rarity,
       summon_cost, min_hp, max_hp, min_attack_power, max_attack_power, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstUnit.csv')
ORDER BY release_key DESC, id;
```

**よくある設定パターン（レアリティ別標準値）**

| レアリティ | summon_cost | min_hp | max_hp | min_attack_power |
|-----------|------------|--------|--------|-----------------|
| UR | 960 | 2020 | 20200 | 4040 |
| SSR | 750 | 1400 | 14000 | 2300 |
| SR | 540 | 970 | 9700 | 1790 |

---

### MstUnitI18n（ユニット多言語名・説明）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| id | `{mst_unit_id}_{language}` | `chara_you_00001_ja` |
| mst_unit_id | 対象ユニット ID | `chara_you_00001` |
| language | 言語コード | `ja` |
| name | キャラクター名（二つ名込み） | `元殺し屋の新人教諭 リタ` |
| description | キャラクター説明文 | （report.md の説明文） |
| release_key | 今回のリリースキー | `202602015` |

---

### MstSpeechBalloonI18n（ゲーム内吹き出しテキスト）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `222` |
| mst_unit_id | 対象ユニット ID | `chara_you_00001` |
| language | 言語コード | `ja` |
| condition_type | 表示タイミング（SpecialAttackCharge/Summon/...） | `SpecialAttackCharge` |
| balloon_type | 吹き出し形状（Maru/...） | `Maru` |
| side | 表示位置（Left/Right） | `Right` |
| duration | 表示時間（秒） | `0.5` |
| text | 吹き出しテキスト（改行は `\n`） | `私達はただ…\n\n子供を守る\n\nだけです` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_unit_id, condition_type, balloon_type, side, duration, text
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstSpeechBalloonI18n.csv')
ORDER BY mst_unit_id, condition_type;
```

---

## 検証方法

- MstUnit.fragment_mst_item_id → MstItem.id が存在するか
- MstUnit.mst_unit_ability_id1〜3 → MstUnitAbility.id が存在するか
- MstUnit.mst_series_id → MstSeries.id が存在するか
- MstAttack.mst_unit_id → MstUnit.id が存在するか
- max_hp = min_hp × 10、max_attack_power = min_attack_power × 10 であるか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/MstUnit.csv`
- ID採番ルール: `domain/knowledge/masterdata/ID割り振りルール.csv`（`masterdata-id-numbering` スキル）
