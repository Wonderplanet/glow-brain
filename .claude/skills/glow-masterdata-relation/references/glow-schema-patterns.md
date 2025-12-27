# GLOWプロジェクト マスターデータスキーマパターン

## テーブル命名規則

### プレフィックス

| プレフィックス | 意味 | 例 |
|-------------|------|-----|
| `mst_*` | 固定マスターデータ | `mst_units`, `mst_stages`, `mst_quests` |
| `opr_*` | 運営施策・期間限定データ | `opr_gachas`, `opr_campaigns`, `opr_products` |

### サフィックス

| サフィックス | 意味 | 例 |
|-----------|------|-----|
| `*_i18n` | 多言語対応テーブル | `mst_units_i18n`, `mst_events_i18n` |

### DBスキーマとCSVファイル名の違い

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

---

## リレーション検出パターン

### 外部キー命名規則

外部キーは以下のパターンで命名される：

1. **`mst_<テーブル名>_id`** - マスターテーブルへの参照
   - 例: `mst_unit_id` → `mst_units.id`
   - 例: `mst_quest_id` → `mst_quests.id`
   - 例: `mst_in_game_id` → `mst_in_games.id`

2. **`opr_<テーブル名>_id`** - 運営テーブルへの参照
   - 例: `opr_gacha_id` → `opr_gachas.id`
   - 例: `opr_campaign_id` → `opr_campaigns.id`

3. **`prev_<カラム名>`** - 自己参照（前の要素）
   - 例: `prev_mst_stage_id` → `mst_stages.id`

4. **`<接頭辞>_mst_<テーブル名>_id`** - 特定用途の外部キー
   - 例: `boss_mst_enemy_stage_parameter_id` → `mst_enemy_stage_parameters.id`
   - 例: `display_mst_unit_id1` → `mst_units.id`

5. **番号付き外部キー** - 複数の同一種別の参照
   - 例: `mst_unit_ability_id1`, `mst_unit_ability_id2`, `mst_unit_ability_id3`
   - 例: `display_mst_unit_id1`, `display_mst_unit_id2`, `display_mst_unit_id3`

---

## 特殊なリレーションパターン

### 1. resource_type + resource_id パターン

報酬や参照リソースを柔軟に定義するパターン：

```
報酬テーブル (mst_*_rewards, opr_*_rewards)
  ├─ resource_type (enum: Coin, Item, Unit, CurrencyFree, etc.)
  ├─ resource_id (resource_typeに応じたマスターID)
  └─ resource_amount (個数)
```

**resource_typeの種類:**
- `Coin` → resource_id不要
- `Item` → resource_id = mst_items.id
- `Unit` → resource_id = mst_units.id
- `CurrencyFree`, `CurrencyPaid` → 無課金/課金通貨
- `Artwork` → resource_id = mst_artworks.id
- `ArtworkFragment` → resource_id = mst_artwork_fragments.id

### 2. group_id パターン

複数のレコードをグルーピングするパターン：

```
mst_mission_rewards
  ├─ group_id (グルーピング用ID)
  ├─ resource_type
  └─ resource_id

mst_missions
  └─ mst_mission_reward_group_id (group_idを参照)
```

同じgroup_idを持つ複数の報酬レコードをまとめて1つの報酬セットとして扱う。

### 3. 係数（Coefficient）パターン

基本パラメータに係数を掛けて調整するパターン：

```
mst_in_games
  ├─ boss_enemy_hp_coef (ボスHP係数)
  ├─ boss_enemy_attack_coef (ボス攻撃力係数)
  ├─ normal_enemy_hp_coef (通常敵HP係数)
  └─ normal_enemy_attack_coef (通常敵攻撃力係数)
```

基本パラメータ × 係数 = 実際のゲーム内パラメータ

### 4. 依存関係（Dependencies）パターン

前提条件を管理するパターン：

```
mst_mission_achievement_dependencies
  ├─ mst_mission_id (対象ミッション)
  └─ required_mst_mission_id (前提ミッション)
```

### 5. i18n（多言語）パターン

テキストデータを別テーブルで管理：

```
mst_quests (基本データ)
  └─ id

mst_quests_i18n (多言語テキスト)
  ├─ quest_id → mst_quests.id
  ├─ locale (ja, en, etc.)
  └─ name, description
```

---

## よくある機能領域とテーブル群

### クエスト・ステージ

```
mst_quests
  └─ mst_stages
      ├─ mst_in_games
      ├─ mst_stage_rewards
      └─ mst_stage_event_settings
```

### ガチャ

```
opr_gachas
  ├─ opr_gacha_prizes
  ├─ opr_gacha_uppers
  └─ opr_gacha_use_resources
```

### ユニット

```
mst_units
  ├─ mst_unit_abilities
  ├─ mst_unit_level_ups
  ├─ mst_unit_rank_ups
  └─ mst_unit_grade_ups
```

### ミッション

```
mst_mission_achievements
mst_mission_dailies
mst_mission_events
  └─ mst_mission_rewards (group_id経由)
```

### 降臨バトル

```
mst_advent_battles
  ├─ mst_advent_battle_reward_groups
  │   └─ mst_advent_battle_rewards
  ├─ mst_advent_battle_ranks
  └─ mst_advent_battle_clear_rewards
```

---

## カラム型の特徴

### 主キー

- **型**: `uuid` (UUID v4)
- **カラム名**: `id`

### タイムスタンプ

- `start_at`, `end_at` - 期間指定
- `start_date`, `end_date` - 日付のみ
- `created_at`, `updated_at` - レコードのタイムスタンプ

### enum型

多くのカラムでenum型を使用：
- `quest_type`, `difficulty`, `reward_category`, `resource_type`, `reset_type` など

### JSON型

複雑なデータ構造をJSONで格納：
- `party_status`, `discovered_enemies`, `max_score_party`, `opr_campaign_ids` など

---

## データベーススキーマファイル

**場所**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**構造**:
```json
{
  "databases": {
    "mst": {
      "tables": {
        "mst_quests": {
          "columns": {
            "id": { "type": "uuid", "nullable": false, ... },
            "mst_event_id": { "type": "uuid", "nullable": true, ... },
            ...
          }
        }
      }
    }
  }
}
```

**jqで取得する例**:
```bash
# テーブル一覧
jq '.databases.mst.tables | keys' master_tables_schema.json

# 特定テーブルのカラム一覧
jq '.databases.mst.tables.mst_quests.columns | keys' master_tables_schema.json

# カラムの詳細
jq '.databases.mst.tables.mst_quests.columns.mst_event_id' master_tables_schema.json
```
