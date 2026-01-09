<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Log\Models\Contracts\LogModelInterface;
use App\Domain\Resource\Log\Repositories\Contracts\LogModelRepositoryInterface;
use App\Infrastructure\LogModelManager;
use Illuminate\Support\Collection;

abstract class LogModelRepository implements LogModelRepositoryInterface
{
    // Reposistoryでの操作対象となるログテーブルのモデルクラスを定義する
    protected string $modelClass = '';

    public function __construct(
        protected LogModelManager $logModelManager,
    ) {
    }

    /**
     * @param Collection<LogModelInterface> $models
     */
    public function saveModels(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $insertValues = $models->map(
            function (LogModelInterface $model) {
                return $model->formatToInsert();
            }
        )->toArray();

        $this->modelClass::insert($insertValues);
    }

    /**
     * 指定した複数のモデルをログモデル管理へ追加する
     *
     * @param Collection $models
     */
    public function addModels(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $addModels = $models->filter(
            function (LogModelInterface $model) {
                return $this->isValidModel($model);
            }
        );

        $this->logModelManager->addModels($this::class, $addModels);
    }

    /**
     * addModelsの単一モデル版
     */
    public function addModel(LogModelInterface $model): void
    {
        $this->addModels(collect([$model]));
    }

    /**
     * 指定したモデルがこのRepositoryで扱うモデルクラスかどうかを確認する
     * true: 有効、false: 無効
     */
    protected function isValidModel(LogModelInterface $model): bool
    {
        if ($model instanceof $this->modelClass === false) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf(
                    'this model class is not %s. (model class: %s)',
                    $this->modelClass,
                    get_class($model)
                ),
            );
        }

        return true;
    }
}
