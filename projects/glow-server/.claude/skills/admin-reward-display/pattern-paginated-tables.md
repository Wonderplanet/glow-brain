# パターン1: ページネーションテーブルでの報酬表示

FilamentのListRecordsページで、ページネーションされたテーブルに報酬情報を効率的に表示します。

## 使用場面

✅ **適している場合**:
- Filament ListRecordsページ
- ページネーションを使用
- 大量のレコードがある

❌ **適していない場合**:
- 詳細ページ内のテーブル → [pattern-detail-page-tables.md](pattern-detail-page-tables.md)

## 実装手順

### ステップ1: Traitのインポート

```php
<?php

namespace App\Filament\Resources\MstUnitEncyclopediaRewardResource\Pages;

use App\Traits\RewardInfoGetTrait;
use Filament\Resources\Pages\ListRecords;

class ListMstUnitEncyclopediaRewards extends ListRecords
{
    use RewardInfoGetTrait;  // ← 追加

    protected static string $resource = MstUnitEncyclopediaRewardResource::class;
}
```

### ステップ2: paginateTableQueryメソッドをオーバーライド

```php
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

protected function paginateTableQuery(Builder $query): Paginator
{
    // 1. デフォルトのページネーション処理
    $paginator = parent::paginateTableQuery($query);

    // 2. ページネーションされたレコードに報酬情報を追加
    $this->addRewardInfoToPaginatedRecords($paginator);

    return $paginator;
}
```

### ステップ3: Modelに報酬属性を追加

```php
<?php

namespace App\Models\Mst;

use App\Dtos\RewardDto;

class MstUnitEncyclopediaReward extends BaseMstUnitEncyclopediaReward
{
    public function getRewardAttribute()
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
```

### ステップ4: テーブルカラムで表示

```php
use App\Tables\Columns\RewardInfoColumn;
use Filament\Tables\Columns\TextColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('id')->label('ID'),
            TextColumn::make('resource_type')->label('報酬タイプ'),
            RewardInfoColumn::make('reward_info')->label('報酬情報'),  // ← 追加
        ]);
}
```

## 完全な実装例

**ファイル**: `admin/app/Filament/Resources/MstUnitEncyclopediaRewardResource/Pages/ListMstUnitEncyclopediaRewards.php`

```php
<?php

namespace App\Filament\Resources\MstUnitEncyclopediaRewardResource\Pages;

use App\Filament\Resources\MstUnitEncyclopediaRewardResource;
use App\Traits\RewardInfoGetTrait;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class ListMstUnitEncyclopediaRewards extends ListRecords
{
    use RewardInfoGetTrait;

    protected static string $resource = MstUnitEncyclopediaRewardResource::class;

    protected function paginateTableQuery(Builder $query): Paginator
    {
        $paginator = parent::paginateTableQuery($query);
        $this->addRewardInfoToPaginatedRecords($paginator);
        return $paginator;
    }
}
```

## カスタマイズ

### カスタム属性名を使う場合

```php
$this->addRewardInfoToPaginatedRecords(
    $paginator,
    'custom_reward',      // Modelの属性名
    'custom_reward_info'  // 保存先の属性名
);
```

テーブルカラムも変更:

```php
RewardInfoColumn::make('custom_reward_info')->label('報酬情報'),
```

### 複数の報酬カラムを持つ場合

```php
protected function paginateTableQuery(Builder $query): Paginator
{
    $paginator = parent::paginateTableQuery($query);

    // 通常報酬
    $this->addRewardInfoToPaginatedRecords($paginator, 'reward', 'reward_info');

    // ボーナス報酬
    $this->addRewardInfoToPaginatedRecords($paginator, 'bonus_reward', 'bonus_reward_info');

    return $paginator;
}
```

## 実装完了チェックリスト

- [ ] `RewardInfoGetTrait` をインポート
- [ ] `paginateTableQuery()` をオーバーライド
- [ ] `parent::paginateTableQuery()` を呼び出し
- [ ] `addRewardInfoToPaginatedRecords()` を呼び出し
- [ ] Modelに `getRewardAttribute()` を実装
- [ ] テーブルカラムで `RewardInfoColumn::make('reward_info')` を使用
- [ ] カラム名が一致していることを確認
- [ ] ブラウザで報酬情報が表示されることを確認
- [ ] ページネーションが正常に動作することを確認

## トラブルシューティング

よくある間違いと解決方法は **[common-troubleshooting.md](common-troubleshooting.md)** を参照してください。

---

**次のパターン**: [pattern-detail-page-tables.md](pattern-detail-page-tables.md)
