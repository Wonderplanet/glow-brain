<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\AthenaConstant;
use App\Constants\LogTablePageConstants;
use App\Constants\SystemConstants;
use App\Contracts\IAthenaModel;
use App\Entities\Athena\AthenaQueryResultEntity;
use App\Operators\AthenaOperator;
use App\Services\ConfigGetService;
use Carbon\CarbonImmutable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Athenaクエリを使用してデータを取得するためのトレイト
 */
trait AthenaQueryTrait
{
    use InteractsWithTable;

    /**
     * Athenaクエリ機能を含むテーブルレコード取得
     * Filamentのデフォルト実装をオーバーライドしてAthena機能を提供
     */
    public function getTableRecords(): Collection | Paginator | CursorPaginator
    {
        return $this->getTableRecordsWithAthena();
    }

    /**
     * Athenaクエリ機能を含むテーブルレコード取得
     */
    public function getTableRecordsWithAthena(): Paginator | CursorPaginator
    {
        $query = $this->getFilteredSortedTableQuery();
        $paginator = $this->paginateTableQuery($query);

        if ($this->shouldUseAthenaQuery()) {
            $this->forceFillPaginatorItemsWithAthenaResults($paginator);
        }

        return $paginator;
    }

    /**
     * Athenaクエリを使用すべきかを判定する
     */
    private function shouldUseAthenaQuery(): bool
    {
        // 環境チェック: 許可された環境のみでAthena使用可能
        $currentEnv = config('app.env');
        if (!in_array($currentEnv, AthenaConstant::ATHENA_ENABLED_ENVIRONMENTS, true)) {
            return false;
        }

        // 日付範囲フィルターの取得
        $createdAtRangeFilterValues = $this->getTableFilterState(LogTablePageConstants::CREATED_AT_RANGE);
        $startAt = $createdAtRangeFilterValues[LogTablePageConstants::CREATED_AT_RANGE_START_AT] ?? null;
        $endAt = $createdAtRangeFilterValues[LogTablePageConstants::CREATED_AT_RANGE_END_AT] ?? null;

        // 日付範囲が指定されていない場合はAthenaを使用しない
        if (is_null($startAt) || is_null($endAt)) {
            return false;
        }

        // 開始日が一定期間以上前の場合のみAthenaを使用
        $now = CarbonImmutable::now(SystemConstants::VIEW_TIMEZONE);
        $startAtCarbon = CarbonImmutable::parse($startAt, SystemConstants::VIEW_TIMEZONE);

        return $startAtCarbon->diffInDays($now) >= AthenaConstant::ATHENA_FALLBACK_BEFORE_DAYS;
    }

    /**
     * DBからデータを取得する代わりに、同じ条件でAthenaクエリを実行してログデータを取得し、ページネートデータを強制的に置き換える
     */
    private function forceFillPaginatorItemsWithAthenaResults(
        Paginator $paginator,
    ): void {
        $athenaOperator = app(AthenaOperator::class);
        $configGetService = app(ConfigGetService::class);
        $athenaConfig = $configGetService->getAthenaConfig();

        $query = $this->getFilteredSortedTableQuery();
        $tableName = $query->getModel()->getTable();

        $athenaQuery = clone $query;
        $athenaQuery->from($athenaConfig['database'] . '.' . $tableName);

        // created_atの範囲フィルターから、パーティション用のdt列のbetween条件を追加
        $createdAtRangeFilterValues = $this->getTableFilterState(LogTablePageConstants::CREATED_AT_RANGE) ?? [];
        $startAt = $createdAtRangeFilterValues[LogTablePageConstants::CREATED_AT_RANGE_START_AT] ?? null;
        $endAt = $createdAtRangeFilterValues[LogTablePageConstants::CREATED_AT_RANGE_END_AT] ?? null;

        if ($startAt !== null && $endAt !== null) {
            $startYmd = CarbonImmutable::parse($startAt, SystemConstants::VIEW_TIMEZONE)
                ->setTimezone(AthenaConstant::ATHENA_DATETIME_TIMEZONE)
                ->format(AthenaConstant::ATHENA_PARTITION_DATE_FORMAT);
            $endYmd = CarbonImmutable::parse($endAt, SystemConstants::VIEW_TIMEZONE)
                ->setTimezone(AthenaConstant::ATHENA_DATETIME_TIMEZONE)
                ->format(AthenaConstant::ATHENA_PARTITION_DATE_FORMAT);

            $athenaQuery->whereBetween(AthenaConstant::ATHENA_PARTITION_COLUMN, [$startYmd, $endYmd]);
        }

        $athenaQuery->setBindings(array_map(function ($binding) {
            if ($binding instanceof CarbonImmutable) {
                // Athenaでクエリするデータのタイムゾーンに合わせる
                return $binding->copy()->setTimezone(AthenaConstant::ATHENA_DATETIME_TIMEZONE)->toDateTimeString();
            }
            return $binding;
        }, $athenaQuery->getBindings()));

        // SQLをAthena用に変換
        $athenaSql = str_replace('`', '"', $athenaQuery->toRawSql());

        $athenaQueryResultEntity = $athenaOperator->executeQuery(
            $athenaConfig,
            $athenaSql,
        );
        if (is_null($athenaQueryResultEntity)) {
            // Athenaクエリ失敗時は何もしない
            return;
        }
        /** @var AthenaQueryResultEntity $athenaQueryResultEntity */
        $models = collect();
        foreach ($athenaQueryResultEntity->getRows() as $row) {
            $modelClass = $query->getModel()::class;

            // IAthenaModelを実装していることを確認
            if (!is_subclass_of($modelClass, IAthenaModel::class)) {
                throw new \InvalidArgumentException(
                    "Model {$modelClass} must implement " . IAthenaModel::class
                );
            }

            /** @var IAthenaModel $modelClass */
            $models->push($modelClass::createFromAthenaArray($row));
        }

        $paginator->setCollection($models);
    }
}
