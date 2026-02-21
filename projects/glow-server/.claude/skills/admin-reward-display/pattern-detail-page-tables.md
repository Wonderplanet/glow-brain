# パターン2: 詳細ページ内テーブルでの報酬表示

詳細ページ内のテーブルで、特定のレコードに関連する報酬情報を表示します。

## 使用場面

✅ **適している場合**:
- カスタム詳細ページ
- 特定のレコードに紐づく報酬を一覧表示
- ページネーションを使わない（または小規模な）テーブル

❌ **適していない場合**:
- ListRecordsページ → [pattern-paginated-tables.md](pattern-paginated-tables.md)

## 実装手順

### ステップ1: ページクラスの基本設定

```php
<?php

namespace App\Filament\Pages;

use App\Traits\RewardInfoGetTrait;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstMissionDailyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;  // ← 追加
    use InteractsWithTable;  // ← 追加

    protected static string $view = 'filament.pages.mst-mission-daily-detail';
    protected static ?string $title = 'デイリーミッション詳細';
}
```

### ステップ2: 報酬テーブルメソッドの実装

```php
use App\Models\Mst\MstMissionReward;
use App\Tables\Columns\RewardInfoColumn;
use Filament\Tables\Columns\TextColumn;

public function rewardTable(): ?Table
{
    // 1. メインレコードを取得
    $mstMissionDaily = $this->getMstModel();

    // 2. 関連する報酬レコードのクエリ
    $query = MstMissionReward::query()
        ->where('group_id', $mstMissionDaily->mst_mission_reward_group_id);

    // 3. RewardDtoコレクションを作成
    $rewardDtoList = MstMissionReward::query()
        ->where('group_id', $mstMissionDaily->mst_mission_reward_group_id)
        ->get()
        ->map(function (MstMissionReward $mstMissionReward) {
            return $mstMissionReward->reward;
        });

    // 4. RewardInfoコレクションを取得
    $rewardInfos = $this->getRewardInfos($rewardDtoList);

    // 5. テーブル定義
    return $this->getTable()
        ->heading('報酬情報')
        ->query($query)
        ->columns([
            TextColumn::make('resource_type')->label('報酬タイプ'),
            RewardInfoColumn::make('resource_id')
                ->label('報酬情報')
                ->getStateUsing(
                    function ($record) use ($rewardInfos) {
                        return $rewardInfos->get($record->id);
                    }
                ),
        ])
        ->paginated(false);
}
```

### ステップ3: Modelに報酬属性を追加

```php
<?php

namespace App\Models\Mst;

use App\Dtos\RewardDto;

class MstMissionReward extends BaseMstMissionReward
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

### ステップ4: Bladeテンプレートでテーブルを表示

**ファイル**: `admin/resources/views/filament/pages/mst-mission-daily-detail.blade.php`

```blade
<x-filament-panels::page>
    {{-- 基本情報 --}}
    {{ $this->infoList() }}

    {{-- 報酬情報テーブル --}}
    {{ $this->rewardTable() }}
</x-filament-panels::page>
```

## 完全な実装例

**ファイル**: `admin/app/Filament/Pages/MstMissionDailyDetail.php`

```php
<?php

namespace App\Filament\Pages;

use App\Models\Mst\MstMissionDaily;
use App\Models\Mst\MstMissionReward;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstMissionDailyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;

    protected static string $view = 'filament.pages.mst-mission-daily-detail';
    public string $mstMissionDailyId = '';

    protected function getMstModelByQuery(): ?MstMissionDaily
    {
        return MstMissionDaily::query()
            ->where('id', $this->mstMissionDailyId)
            ->first();
    }

    public function rewardTable(): ?Table
    {
        $mstMissionDaily = $this->getMstModel();
        $groupId = $mstMissionDaily->mst_mission_reward_group_id;

        $query = MstMissionReward::query()->where('group_id', $groupId);

        $rewardDtoList = MstMissionReward::query()
            ->where('group_id', $groupId)
            ->get()
            ->map(fn($r) => $r->reward);

        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        return $this->getTable()
            ->heading('報酬情報')
            ->query($query)
            ->columns([
                TextColumn::make('resource_type')->label('報酬タイプ'),
                RewardInfoColumn::make('resource_id')
                    ->label('報酬情報')
                    ->getStateUsing(fn($record) use ($rewardInfos) => $rewardInfos->get($record->id)),
            ])
            ->paginated(false);
    }
}
```

## 実装完了チェックリスト

- [ ] `HasTable` インターフェースを実装
- [ ] `RewardInfoGetTrait` をインポート
- [ ] `InteractsWithTable` トレイトを使用
- [ ] 報酬テーブルメソッドを実装
- [ ] `getRewardInfos()` で報酬情報を取得
- [ ] クエリとDtoリストのクエリ条件が一致
- [ ] `RewardInfoColumn` で `getStateUsing()` を使用
- [ ] `$record->id` でRewardInfoを取得
- [ ] Modelに `getRewardAttribute()` を実装
- [ ] Bladeテンプレートでテーブルをレンダリング
- [ ] ブラウザで報酬情報が表示されることを確認

## トラブルシューティング

よくある間違いと解決方法は **[common-troubleshooting.md](common-troubleshooting.md)** を参照してください。
