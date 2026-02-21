<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Resource\Constants\LogConstant;
use App\Domain\Resource\Log\Models\Contracts\LogModelInterface;
use App\Domain\Resource\Log\Repositories\Contracts\LogModelRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * ログデータモデルを管理するクラス。
 * このクラスのインスタンスは、APIリクエスト開始時に生成され、終了時に破棄されるため、1リクエスト中のみ有効です。
 * (AppServiceProviderで登録)
 */
class LogModelManager
{
    // /**
    //  * @var string
    //  */
    // private string $usrUserId = '';

    /**
     * @var Collection<string, Collection<LogModelInterface>>
     * key: repository class name string, value: Collection<LogModelInterface>
     */
    private Collection $models;

    private string $nginxRequestId;
    private string $requestId;
    private int $loggingNo;

    public function __construct(string $nginxRequestId, string $requestId)
    {
        $this->models = collect();

        $this->nginxRequestId = $nginxRequestId;
        $this->requestId = $requestId;
        $this->loggingNo = LogConstant::LOGGING_NO_INITAL_VALUE;
    }

    /**
     * @return Collection<LogModelInterface>
     */
    private function getModels(string $repositoryClass): Collection
    {
        return $this->models->get($repositoryClass, collect());
    }

    /**
     * @param Collection<LogModelInterface> $targetModels
     * @return void
     */
    public function addModels(string $repositoryClass, Collection $targetModels): void
    {
        if ($targetModels->isEmpty()) {
            return;
        }

        $repository = $this->getRepositoryClass($repositoryClass);
        if (is_null($repository)) {
            return;
        }

        $models = $this->getModels($repositoryClass);

        foreach ($targetModels as $targetModel) {
            $this->setLogging($targetModel);
            $models->put(
                $targetModel->makeModelKey(),
                $targetModel,
            );
        }

        $this->models->put($repositoryClass, $models);
    }

    private function setLogging(
        LogModelInterface $model,
    ): void {
        $model->setLogging(
            $this->loggingNo,
            $this->nginxRequestId,
            $this->requestId,
        );

        $this->loggingNo++;
    }

    /**
     * @return void
     */
    public function saveAll(): void
    {
        foreach ($this->models->keys() as $repositoryClass) {
            $this->saveModels($repositoryClass);
        }
    }

    /**
     * DB一括保存を実行する
     *
     * @return void
     */
    private function saveModels(string $repositoryClass): void
    {
        $repository = $this->getRepositoryClass($repositoryClass);
        if (is_null($repository)) {
            return;
        }

        $models = $this->getModels($repositoryClass);
        $needSaveModels = $models
            ->filter(function (LogModelInterface $model) {
                return $model->isChanged();
            });
        if ($needSaveModels->isEmpty()) {
            return;
        }

        // DB一括保存実行
        /** @var LogModelRepositoryInterface $repository */
        $repository->saveModels($needSaveModels);

        // DB更新後の各種ステータス変更
        foreach ($needSaveModels as $model) {
            // DBに対して重複更新しないように、更新後の状態をオリジナルとする(isDirty=falseにする)
            $model->syncOriginal();
            $models->put($model->makeModelKey(), $model);
        }
        // キャッシュ内容を更新
        $this->models->put($repositoryClass, $models);
    }

    private function getRepositoryClass(string $repositoryClass): ?LogModelRepositoryInterface
    {
        if (class_exists($repositoryClass) === false) {
            return null;
        }

        $repository = app()->make($repositoryClass);
        if ($repository instanceof LogModelRepositoryInterface === false) {
            return null;
        }

        return $repository;
    }
}
