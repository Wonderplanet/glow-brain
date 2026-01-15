# アーキテクチャ: 報酬情報表示の設計思想

管理ツール(admin)における報酬情報表示機能の全体的なアーキテクチャと設計思想を説明します。

## 設計原則

1. **N+1問題の回避**: 報酬タイプごとに一括でデータを取得
2. **責任の分離**: DTO、Entity、Serviceで役割を明確に分離
3. **拡張性**: 新しい報酬タイプの追加が容易
4. **再利用性**: Traitによる共通機能の提供

## 主要なコンポーネント

### RewardDto (Data Transfer Object)

**役割**: 報酬の生データを保持する
**ファイル**: `admin/app/Dtos/RewardDto.php`

- `id`: レコードID
- `rewardType`: 報酬タイプ（例: 'Item', 'Unit', 'Coin'）
- `resourceId`: リソースID（例: mstItemId, mstUnitId）
- `amount`: 数量

Modelの `getRewardAttribute()` で生成される。

### RewardInfo (Entity)

**役割**: 表示用に整形された報酬情報を保持する
**ファイル**: `admin/app/Entities/RewardInfo.php`

RewardDtoに以下の情報を追加:
- `name`: 表示名（例: "火の剣"）
- `detailUrl`: 詳細ページへのURL
- `assetPath`: アイコン画像のパス
- `bgPath`: 背景画像のパス
- `rarity`: レアリティ

マスターデータとの紐付け済みで、ビューで直接使用可能。

### RewardInfoGetTrait

**役割**: 報酬情報取得の共通メソッドを提供
**ファイル**: `admin/app/Traits/RewardInfoGetTrait.php`

主要メソッド:
- `getRewardInfos()`: 報酬情報を一括取得
- `addRewardInfoToPaginatedRecords()`: ページネーション対応の報酬情報追加

### RewardInfoGetHandleService

**役割**: 報酬タイプ別のサービスを統括する
**ファイル**: `admin/app/Services/Reward/RewardInfoGetHandleService.php`

各報酬タイプのサービスクラスをDIで注入し、報酬タイプ→サービスのマッピングを保持。

### BaseRewardInfoGetService

**役割**: 各報酬タイプのサービスクラスの基底クラス
**ファイル**: `admin/app/Services/Reward/BaseRewardInfoGetService.php`

継承クラス（例）:
- `RewardItemInfoGetService`: アイテム報酬
- `RewardUnitInfoGetService`: ユニット報酬
- `RewardCoinInfoGetService`: コイン報酬

## データフロー

```
1. データベースからレコード取得
   ↓
2. Model::getRewardAttribute() → RewardDto生成
   ↓
3. RewardInfoGetTrait::getRewardInfos()
   ↓
4. RewardInfoGetHandleService
   ↓
5. 報酬タイプごとにグルーピング
   ↓
6. 各タイプのサービスでマスターデータを一括取得
   ↓
7. RewardInfo生成
   ↓
8. ビューで表示
```

## N+1問題の回避

### ❌ 非効率な方法

```php
// 各レコードごとにマスターデータを取得（N回クエリ実行）
foreach ($rewards as $reward) {
    $item = MstItem::find($reward->resource_id); // N回実行
}
```

### ✅ 効率的な方法

```php
// 報酬タイプごとにグルーピング → 各タイプで一括取得
$typeGrouped = $rewardDtos->groupBy('rewardType');

foreach ($typeGrouped as $type => $dtos) {
    $service = $this->getService($type);
    // RewardItemInfoGetServiceなら全mstItemIdを一度に取得
    $service->build($dtos);
}
```

**クエリ数の比較**:
- 非効率: 1 + N（100件なら101クエリ）
- 効率的: 1 + M（報酬タイプ数が5なら6クエリ）

## 拡張性

新しい報酬タイプ（例: TICKET）を追加する手順:

1. **RewardTypeにEnum追加**
2. **専用サービスクラス作成** (`RewardTicketInfoGetService`)
3. **RewardInfoGetHandleServiceに登録**

既存コードは自動的に新しい報酬タイプに対応します。

## まとめ

- **パフォーマンス**: N+1問題を回避し高速
- **保守性**: 責任が明確で変更が容易
- **拡張性**: 新しい報酬タイプの追加が簡単
- **再利用性**: Traitで共通機能を提供

詳細な実装方法は各パターン別ドキュメントを参照:
- [pattern-paginated-tables.md](pattern-paginated-tables.md)
- [pattern-detail-page-tables.md](pattern-detail-page-tables.md)
