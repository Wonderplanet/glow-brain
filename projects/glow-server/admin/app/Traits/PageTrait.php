<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Filament\Pages\Page のPageクラスを継承したページクラスで使用するトレイト
 *
 * ※ Filament\Resources\Pages のPageクラスを継承したクラスは対象外です
 */
trait PageTrait
{
    /**
     * Paginatorを引数とするコールバックを使って、ページネートされたレコードのデータに追加または変換を加える
     */
    public function augmentPaginatorWithCallback(
        callable $callback,
    ): Paginator | CursorPaginator {
        $query = $this->getFilteredSortedTableQuery();

        /**
         * @var Paginator | CursorPaginator $paginator
         */
        $paginator = $this->paginateTableQuery($query);

        $callback($paginator);

        return $paginator;
    }
}
