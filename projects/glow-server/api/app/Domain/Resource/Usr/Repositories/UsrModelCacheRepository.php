<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Domain\Resource\Usr\Repositories\Contracts\UsrModelCacheRepositoryInterface;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * UsrModelManagerを用いたキャッシュ機構を、Repository単位で有効にする際に利用する、Repositoryの基底クラス。
 */
abstract class UsrModelCacheRepository extends UsrModelRepository implements UsrModelCacheRepositoryInterface
{
    public function __construct(
        protected UsrModelManager $usrModelManager,
    ) {
    }

    /**
     * 対象のRepositoryが担当するユーザーテーブルのモデルであるかどうかを確認する。
     * apiリクエストを送ったユーザー自身のデータのみを扱うことを保証するために用意。
     *
     * true: 有効、false: 無効
     */
    public function isValidModel(UsrModelInterface $model): bool
    {
        $isValidClass = $model instanceof $this->modelClass;
        $isValidUser = $this->isOwnUsrUserId($model->getUsrUserId());

        if ($isValidClass === false || $isValidUser === false) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf(
                    'this model class is invalid. (model class: %s, user id check: %s)',
                    get_class($model),
                    (string) $isValidUser ? 'true' : 'false',
                ),
            );
        }

        return true;
    }

    /**
     * UsrModelManagerでのみ使用するメソッド。それ以外の箇所では使用しないでください。
     */
    public function saveModelsByUsrModelManager(Collection $models): void
    {
        $this->saveModels($models);
    }

    /**
     * 対象のユーザーテーブルにある全てのリソースを取得したかどうかを取得する
     * true: 全て取得済み, false: 未取得または一部のみ取得
     */
    protected function isAllFetched(): bool
    {
        return $this->usrModelManager->getIsAllFetched($this::class);
    }

    /**
     * 対象のユーザーテーブルにある全てのリソースを取得したことを記録する。
     * apiリクエストを送ったユーザー本人の場合のみ、フラグを立てる。
     *
     * @param string $usrUserId DBクエリ実行の対象となったユーザーID
     */
    protected function markAllFetched(string $usrUserId): void
    {
        if ($this->isOwnUsrUserId($usrUserId)) {
            $this->usrModelManager->markAllFetched($this::class);
        }
    }

    /**
     * キャッシュへ複数モデルを追加または上書きするメソッド
     *
     * @param Collection<UsrModelInterface> $models
     * @return void
     */
    public function syncModels(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        // 担当するモデルのインスタンスでなければキャッシュに追加させない
        $targetModels = [];
        foreach ($models as $model) {
            if ($this->isValidModel($model)) {
                $targetModels[] = $model;
            }
        }

        $this->usrModelManager->syncModels($this::class, $targetModels);
    }

    // /**
    //  * 指定された複数モデルをキャッシュから削除するメソッド
    //  * DBからレコード削除した際に、キャッシュからも削除するために用意。
    //  */
    // public function syncDeleteModels(Collection $models): void
    // {
    //     if ($models->isEmpty()) {
    //         return;
    //     }

    //     $targetModels = $models->filter(function (UsrModelInterface $model) {
    //         return $this->isValidModel($model);
    //     });

    //     $this->usrModelManager->syncDeleteModels($this::class, $targetModels);
    // }

    // /**
    //  * syncDeleteModelsの引数を1モデルのみにしたメソッド
    //  */
    // public function syncDeleteModel(UsrModelInterface $model): void
    // {
    //     $this->syncDeleteModels(collect([$model]));
    // }

    /**
     * キャッシュを全て取得する
     *
     * キャッシュの中身をビジネスロジック側で直接操作しないために、クローンしてから返すのが基本。
     * キャッシュ操作の基盤処理の効率化のために、クローンせずに取得するパターンもあるので、オプションとしています。
     *
     * @property bool $isClone クローンしたモデルを取得するかどうか
     *   true: クローンしたモデルを取得する, false: クローンせずに取得する
     * @return array<string, UsrModelInterface>
     */
    public function getCache(bool $isClone = true): array
    {
        if ($isClone) {
            return $this->usrModelManager->getClonedModels($this::class);
        }

        return $this->usrModelManager->getModels($this::class);
    }

    /**
     * 指定したモデルキーに該当するモデルをキャッシュから取得する
     *
     * @param array<string> $modelKeys
     * @return array<string, UsrModelInterface>
     */
    protected function getCacheByModelKeys(array $modelKeys): array
    {
        return $this->usrModelManager->getClonedModelsByModelKeys($this::class, $modelKeys);
    }

    /**
     * キャッシュを指定したパラメータでフィルタリングしたモデルを取得する。
     * パラメータで指定できるのは、モデルのgetterで用意されているプロパティ1つのみで、
     * 指定された1つのプロパティに対してなら、複数の条件値を指定できる。（whereIn）
     * 例： $columnKey = 'mst_item_id', $columnValues = ['1', '2', '3']
     *
     * @param string $columnKey
     * @param array<mixed> $columnValues
     * @return array<string, UsrModelInterface>
     */
    public function getCacheWhereIn(string $columnKey, array $columnValues): array
    {
        $getterMethodName = 'get' . Str::studly($columnKey);
        $columnValues = array_fill_keys($columnValues, true);

        $result = [];
        foreach ($this->getCache() as $model) {
            if (isset($columnValues[$model->$getterMethodName()])) {
                $result[$model->makeModelKey()] = $model;
            }
        }

        return $result;
    }

    /**
     * 引数指定した$modelsと同じmodelKeyを持つモデルをキャッシュから取得し、キャッシュのモデルを反映したCollectionを返す。
     * キャッシュに存在するモデルと比較して、値がデグレしないために用意したメソッド。
     *
     * UsrModelクラスにあるmakeModelKey関数はデフォルトではidを返しています。
     * そのため、id以外のカラムをmodelKeyに指定したい場合は、UsrModelを継承したモデルクラスでmakeModelKey関数をオーバーライドしてください。
     * 引数の$modelsに、生成したばかりのモデルインスタンスを渡した場合、idが毎回生成されるuuidなので、
     * 同じインスタンスとして扱われないことがあります。
     * そのため、例えばDBスキーマのユニークキーなどを使うように、makeModelKeyをオーバーライドしてください。（例：UsrConditionPack）
     *
     * @param Collection<UsrModelInterface> $models
     * @return Collection<UsrModelInterface>
     */
    public function getCacheFilteredByModelKey(Collection $models): Collection
    {
        $modelKeys = [];
        foreach ($models as $model) {
            $modelKeys[] = $model->makeModelKey();
        }

        return collect($this->getCacheByModelKeys($modelKeys));
    }

    /**
     * APIリクエスト実行前と比較して、変更があったモデルのみを返す。
     * APIレスポンスで変更があったモデルのみを返したいときに使える。
     */
    public function getChangedModels(): Collection
    {
        return $this->usrModelManager->getChangedModels($this::class);
    }

    /**
     * 指定されたユーザーIDが、apiリクエストを送ったユーザー自身のIDかどうかを確認する。
     */
    public function isOwnUsrUserId(string $usrUserId): bool
    {
        return $usrUserId === $this->usrModelManager->getUsrUserId();
    }
}
