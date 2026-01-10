<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Illuminate\Support\Collection;

/**
 * UsrModelを継承したユーザーテーブルのモデルを扱うRepositoryの基底クラス
 */
abstract class UsrModelRepository
{
    // Reposistoryでの操作対象となるユーザーテーブルのモデルクラスを定義する
    protected string $modelClass = '';

    /**
     * この関数は直接使わないでください。
     *
     * DB更新を実行したい場合は、syncModels関数を介して実行をお願いします。
     * キャッシュを有効化した際は、UsrModelManagerがDB更新の実行を担っているため、
     * 実装者がsaveModelsを実行することはないです。
     *
     * キャッシュ有効にしたかどうかに関わらず、DB一括更新ロジックはsaveModelsに実装してください。
     * これは、UseCaseやServiceからRepositoryの実装を見たときに、
     * キャッシュありなしで実装を変える必要がないようにするための対応になります。
     *
     * ここでは、指定した複数のモデルを1つずつDB更新する関数として用意しております。
     * 複数モデルを一括で効率よくDB更新したい場合は、子クラスで、この関数をオーバーライドしてください。
     *
     * @param Collection<UsrEloquentModel> $models
     */
    protected function saveModels(Collection $models): void
    {
        foreach ($models as $model) {
            if ($this->isValidModel($model) === false) {
                continue;
            }
            $model->save();
        }
    }

    /**
     * 指定した複数モデルを同期するメソッド
     * キャッシュ有効化Repositoryの場合は、キャッシュの内容を更新する。
     * キャッシュ無効化Repositoryの場合は、即時DB更新を実行する。
     *
     * @param Collection $models
     */
    abstract public function syncModels(Collection $models): void;

    /**
     * syncModelsの単一モデル版
     */
    public function syncModel(UsrModelInterface $model): void
    {
        $this->syncModels(collect([$model]));
    }

    /**
     * 指定したモデルがこのRepositoryで扱うモデルクラスかどうかを確認する
     * true: 有効、false: 無効
     */
    protected function isValidModel(UsrEloquentModel $model): bool
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
