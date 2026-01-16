# resource_type設定ルール

## 概要

`resource_type`は、ミッション報酬のリソースタイプを指定するカラムです。
このドキュメントでは、設定可能な値とその使用方法を説明します。

## データソース

resource_typeの定義は以下のファイルに記載されています。

- `projects/glow-server/api/database/schema/exports/master_tables_schema.json` - DBスキーマ定義
- `projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php` - サーバー実装

## 基本ルール

### 1. enum定義済みの値のみ設定可能

resource_typeには、`RewardType.php`に定義されているenum値のみ設定できます。
未定義の値を設定すると、データ投入エラーまたは報酬付与エラーが発生します。

### 2. resource_idの設定有無

resource_typeごとに、`resource_id`の設定が必要かどうかが決まっています。

- **resource_id必要**: Item, Emblem, Unit, Artwork
- **resource_id不要**: Coin, FreeDiamond, Stamina, Exp, PaidDiamond

`resource_id`が必要な場合は、対応するマスターテーブルのIDを設定します。
不要な場合は、空欄のままにしてください。

### 3. 大文字小文字の一致

enum値と完全一致する文字列を設定してください。
大文字小文字が異なると、正しく処理されません。

## 設定可能な値一覧

以下は、`RewardType.php`に定義されているすべてのresource_typeです。

---

### Coin

**説明**: コイン（ゲーム内通貨）

**resource_id**: 不要（空欄）

**resource_amount**: 付与するコインの数量

**設定例**:
```csv
resource_type,resource_id,resource_amount
Coin,,2000
```

**意味**: コイン2000枚を付与

**用途**:
- ミッション報酬の定番
- プレイヤーレベルアップ報酬
- イベント報酬

---

### FreeDiamond

**説明**: 無償プリズム（無償ジェム）

**resource_id**: 不要（空欄）

**resource_amount**: 付与する無償プリズムの数量

**設定例**:
```csv
resource_type,resource_id,resource_amount
FreeDiamond,,50
```

**意味**: 無償プリズム50個を付与

**用途**:
- 重要なミッション報酬
- イベント報酬
- ログインボーナス

**注意**: 有償プリズム（PaidDiamond）とは別の扱い

---

### Stamina

**説明**: スタミナ（行動力）

**resource_id**: 不要（空欄）

**resource_amount**: 付与するスタミナの数量

**設定例**:
```csv
resource_type,resource_id,resource_amount
Stamina,,100
```

**意味**: スタミナ100を付与

**用途**:
- ログインボーナス
- 期間限定キャンペーン
- レベルアップ報酬

---

### Item

**説明**: アイテム（ガシャチケット、素材など）

**resource_id**: **必要** - MstItem.id

**resource_amount**: 付与するアイテムの個数

**設定例**:
```csv
resource_type,resource_id,resource_amount
Item,ticket_glo_00002,1
```

**意味**: スペシャルガシャチケット（ID: ticket_glo_00002）を1枚付与

**用途**:
- ガシャチケット報酬
- 育成素材報酬
- イベントアイテム報酬

**resource_id設定時の注意**:
- MstItemテーブルに存在するIDを設定
- IDが存在しない場合、報酬付与エラーが発生
- アイテムIDの命名規則: `{カテゴリ}_{作品コード}_{連番}`
  - 例: `ticket_osh_10000`（推しの子のチケット）
  - 例: `item_glo_00001`（GLOWの汎用アイテム）

---

### Emblem

**説明**: エンブレム（称号）

**resource_id**: **必要** - MstEmblem.id

**resource_amount**: 付与するエンブレムの個数（通常1）

**設定例**:
```csv
resource_type,resource_id,resource_amount
Emblem,emblem_event_osh_00008,1
```

**意味**: イベントエンブレム（ID: emblem_event_osh_00008）を1個付与

**用途**:
- イベント達成報酬
- アチーブメント報酬
- 特別なミッション報酬

**resource_id設定時の注意**:
- MstEmblemテーブルに存在するIDを設定
- エンブレムは通常1個のみ付与（resource_amount=1）

---

### Exp

**説明**: 経験値（プレイヤー経験値）

**resource_id**: 不要（空欄）

**resource_amount**: 付与する経験値の数量

**設定例**:
```csv
resource_type,resource_id,resource_amount
Exp,,1000
```

**意味**: プレイヤー経験値1000を付与

**用途**:
- クエストクリア報酬
- 特別なミッション報酬

**注意**: ユニット経験値ではなく、プレイヤーレベルの経験値

---

### Unit

**説明**: ユニット（キャラクター）

**resource_id**: **必要** - MstUnit.id

**resource_amount**: 付与するユニットの体数（通常1）

**設定例**:
```csv
resource_type,resource_id,resource_amount
Unit,unit_osh_00001,1
```

**意味**: ユニット（ID: unit_osh_00001）を1体付与

**用途**:
- ガシャ報酬
- イベント特別報酬
- ログインボーナス（特別な場合）

**resource_id設定時の注意**:
- MstUnitテーブルに存在するIDを設定
- ユニットは通常1体のみ付与（resource_amount=1）

---

### PaidDiamond

**説明**: 有償プリズム（課金ジェム）

**resource_id**: 不要（空欄）

**resource_amount**: 付与する有償プリズムの数量

**設定例**:
```csv
resource_type,resource_id,resource_amount
PaidDiamond,,100
```

**意味**: 有償プリズム100個を付与

**用途**:
- 課金報酬
- 特別なキャンペーン報酬

**注意**:
- 無償プリズム（FreeDiamond）とは別の扱い
- ミッション報酬として設定することは稀

---

### Artwork

**説明**: 原画（コレクションアイテム）

**resource_id**: **必要** - MstArtwork.id

**resource_amount**: 付与する原画の個数（通常1）

**設定例**:
```csv
resource_type,resource_id,resource_amount
Artwork,artwork_osh_00001,1
```

**意味**: 原画（ID: artwork_osh_00001）を1個付与

**用途**:
- イベント達成報酬
- 特別なミッション報酬
- 図鑑コンプリート報酬

**resource_id設定時の注意**:
- MstArtworkテーブルに存在するIDを設定
- 原画は通常1個のみ付与（resource_amount=1）

---

## resource_idの参照先テーブル

resource_typeごとに、resource_idが参照するマスターテーブルが決まっています。

| resource_type | resource_id必要 | 参照先テーブル | 例 |
|--------------|----------------|--------------|-----|
| **Coin** | 不要 | - | - |
| **FreeDiamond** | 不要 | - | - |
| **Stamina** | 不要 | - | - |
| **Item** | **必要** | **MstItem** | ticket_glo_00002 |
| **Emblem** | **必要** | **MstEmblem** | emblem_event_osh_00008 |
| **Exp** | 不要 | - | - |
| **Unit** | **必要** | **MstUnit** | unit_osh_00001 |
| **PaidDiamond** | 不要 | - | - |
| **Artwork** | **必要** | **MstArtwork** | artwork_osh_00001 |

## 降臨バトルミッションの典型的な報酬パターン

降臨バトルミッションでは、以下のような報酬設定が一般的です。

### パターン1: 初回挑戦報酬

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_527,202512020,osh_00001_limited_term_1,Coin,,2000,1,降臨バトルに5回挑戦
```

**説明**: 比較的少額のコイン報酬

---

### パターン2: 中間達成報酬

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_528,202512020,osh_00001_limited_term_2,FreeDiamond,,20,1,降臨バトルに10回挑戦
```

**説明**: 無償プリズムで達成感を演出

---

### パターン3: 大量挑戦報酬

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_529,202512020,osh_00001_limited_term_3,Coin,,3000,1,降臨バトルに20回挑戦
```

**説明**: より多くのコインで報酬を増量

---

### パターン4: 最終達成報酬

```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_530,202512020,osh_00001_limited_term_4,FreeDiamond,,30,1,降臨バトルに25回挑戦
```

**説明**: 最も価値の高い報酬で達成を祝福

---

## 複数報酬の設定

1つのミッションに複数の報酬を設定する場合、`group_id`を同じにして、`sort_order`で順序を制御します。

**設定例**:
```csv
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_600,202512020,special_reward_1,Coin,,5000,1,コイン報酬
e,mission_reward_601,202512020,special_reward_1,FreeDiamond,,50,2,プリズム報酬
e,mission_reward_602,202512020,special_reward_1,Item,ticket_glo_00002,1,3,ガシャチケット報酬
```

**説明**: 1つのミッション（group_id=special_reward_1）に3つの報酬を設定

**表示順序**: sort_order順に表示（コイン → プリズム → チケット）

## データ検証チェックリスト

resource_type設定時は、以下を確認してください。

- [ ] `RewardType.php`に定義されている値である
- [ ] 大文字小文字が正確に一致している
- [ ] resource_id必要なタイプには、正しいIDを設定している
- [ ] resource_id不要なタイプには、空欄を設定している
- [ ] resource_idに設定したIDが、対応するマスターテーブルに存在する
- [ ] resource_amountが正の整数である
- [ ] 複数報酬の場合、group_idとsort_orderが正しく設定されている

## トラブルシューティング

### エラー: Invalid resource_type

**原因**: `RewardType.php`に定義されていない値を設定している

**対処**:
1. `RewardType.php`を確認
2. 正しいresource_typeに修正
3. タイプミスがないか確認（例: `Coins` → `Coin`）

---

### エラー: resource_idが不正

**原因1**: resource_id必要なタイプに空欄を設定している

**対処**:
1. 該当するマスターテーブルからIDを取得
2. resource_idに設定

**原因2**: resource_idに存在しないIDを設定している

**対処**:
1. 対応するマスターテーブルでIDの存在を確認
2. 正しいIDに修正
3. 必要に応じて、先にマスターデータを作成

---

### エラー: resource_idが不要なのに設定されている

**原因**: resource_id不要なタイプ（Coin, FreeDiamondなど）にIDを設定している

**対処**:
1. resource_idを空欄にする

---

### エラー: 報酬が付与されない

**原因**: group_idの対応関係が不正

**対処**:
1. `MstMissionLimitedTerm.mst_mission_reward_group_id`と`MstMissionReward.group_id`が一致しているか確認
2. 一致していない場合、修正

---

## 実装の確認方法

### サーバーコードでの定義確認

`RewardType.php`ファイルで、利用可能な値を確認できます。

```php
enum RewardType: string
{
    case COIN = 'Coin';
    case FREE_DIAMOND = 'FreeDiamond';
    case STAMINA = 'Stamina';
    case ITEM = 'Item';
    case EMBLEM = 'Emblem';
    case EXP = 'Exp';
    case UNIT = 'Unit';
    case PAID_DIAMOND = 'PaidDiamond';
    case ARTWORK = 'Artwork';
}
```

### resource_id必要判定の確認

`hasResourceId()`メソッドで、resource_id必要判定が実装されています。

```php
public function hasResourceId(): bool
{
    return match ($this) {
        self::COIN => false,
        self::FREE_DIAMOND => false,
        self::STAMINA => false,
        self::ITEM => true,
        self::EMBLEM => true,
        self::EXP => false,
        self::UNIT => true,
        self::PAID_DIAMOND => false,
        self::ARTWORK => true,
    };
}
```

## 参考資料

- `projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php` - resource_typeの定義
- `projects/glow-server/api/database/schema/exports/master_tables_schema.json` - DBスキーマ定義
- 各マスターテーブルCSV - resource_idの参照先

## 補足情報

### resource_amountの単位

resource_amountの単位は、resource_typeによって異なります。

| resource_type | 単位 | 例 |
|--------------|------|-----|
| Coin | 枚 | 2000枚 |
| FreeDiamond | 個 | 50個 |
| Stamina | ポイント | 100ポイント |
| Item | 個 | 1個 |
| Emblem | 個 | 1個 |
| Exp | ポイント | 1000ポイント |
| Unit | 体 | 1体 |
| PaidDiamond | 個 | 100個 |
| Artwork | 個 | 1個 |

### 報酬バランスのガイドライン

報酬の数量は、ミッションの難易度に応じて設定します。

**一般的なバランス**:
- **簡単なミッション**: Coin 1000〜3000、FreeDiamond 10〜30
- **中程度のミッション**: Coin 3000〜5000、FreeDiamond 30〜50
- **難しいミッション**: Coin 5000〜10000、FreeDiamond 50〜100、Item（ガシャチケットなど）

**注意**: 実際の報酬バランスは、ゲーム全体の経済バランスを考慮して設定してください。

## 更新履歴

- 2026-01-17: 初版作成
