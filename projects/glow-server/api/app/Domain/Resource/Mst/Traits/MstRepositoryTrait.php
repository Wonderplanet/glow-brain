<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Traits;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

trait MstRepositoryTrait
{
    protected string $startGetterMethod = 'getStartAt';
    protected string $endGetterMethod = 'getEndAt';

    protected function setStartGetterMethod(string $startGetterMethod): void
    {
        $this->startGetterMethod = $startGetterMethod;
    }

    protected function setEndGetterMethod(string $endGetterMethod): void
    {
        $this->endGetterMethod = $endGetterMethod;
    }

    /**
     * エンティティが現在日時で有効なデータかどうかを判定する
     * true: 有効なデータ, false: 無効なデータ
     */
    protected function isActiveEntity(object $entity, CarbonImmutable $now): bool
    {
        return $now->between(
            $entity->{$this->startGetterMethod}(),
            $entity->{$this->endGetterMethod}(),
        );
    }

    /**
     * @param array<string, string> $conditions マスタデータ取得条件 ['カラム名' => '値']
     */
    public function throwMstNotFoundException(
        bool $isThrowError,
        string $modelClass,
        mixed $target,
        array|string $conditions
    ): void {
        if (!$isThrowError) {
            return;
        }

        if ($target === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    '%s record is not found. (%s)',
                    $this->getTableNameByModelClass($modelClass),
                    json_encode($conditions),
                ),
            );
        }
    }

    protected function getTableNameByModelClass(string $modelClass): string
    {
        return (new $modelClass())->getTable();
    }

    protected function filterWhereIn(Collection $entities, string $getterMethod, Collection $values): Collection
    {
        if ($values->isEmpty()) {
            return collect();
        }

        $targetValues = $values->mapWithKeys(
            function ($value) {
                return [$value => true];
            }
        );

        return $entities->filter(
            function ($entity) use ($getterMethod, $targetValues) {
                return $targetValues->has($entity->{$getterMethod}());
            }
        );
    }
}
