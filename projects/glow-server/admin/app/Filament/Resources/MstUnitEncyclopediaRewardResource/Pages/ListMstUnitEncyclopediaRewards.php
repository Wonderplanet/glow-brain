<?php

namespace App\Filament\Resources\MstUnitEncyclopediaRewardResource\Pages;

use App\Filament\Resources\MstUnitEncyclopediaRewardResource;
use App\Traits\RewardInfoGetTrait;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class ListMstUnitEncyclopediaRewards extends ListRecords
{
    use RewardInfoGetTrait;

    protected static string $resource = MstUnitEncyclopediaRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    /**
     * デフォルトのページネーションクエリを実行して必要な分だけレコードを取得し
     * 必要な分だけの報酬情報を取得したのちに、ページ表示するデータに報酬情報を追加する
     */
    protected function paginateTableQuery(Builder $query): Paginator | CursorPaginator
    {
        $paginator = parent::paginateTableQuery($query);

        $this->addRewardInfoToPaginatedRecords(
            $paginator,
        );

        return $paginator;
    }
}
