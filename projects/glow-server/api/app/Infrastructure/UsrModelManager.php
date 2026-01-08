<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use App\Domain\Resource\Usr\Repositories\Contracts\UsrModelCacheRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * ユーザーデータモデルのキャッシュを管理するクラス。
 * このクラスのインスタンスは、APIリクエスト開始時に生成され、終了時に破棄されるため、1リクエスト中のみ有効です。
 * (AppServiceProviderで登録)
 */
class UsrModelManager
{
    /**
     * apiリクエストを送ったユーザーのID。
     * このIDを元に、本人のユーザーデータのみを扱うことを保証する。
     *
     * @var string
     */
    private string $usrUserId = '';

    /**
     * ユーザーデータモデルのキャッシュを格納する。
     * 1リクエスト中のsingletonインスタンスとして生成されるため、
     * キャッシュを有効化した全てのRepositoryのユーザーデータモデルをここに格納する。
     *
     * key: repository class name string
     * value: array<string, UsrModelInterface>
     *     key: model key string, value: UsrModelInterface
     *
     * @var array<string, array<string, UsrModelInterface>>
     */
    private array $models = [];

    /**
     * モデルの値に変更があり、DB保存が必要かどうかをRepositoryごとに管理する。
     * true: DB保存が必要, false: DB保存不要
     * 対象のRepositoryが操作するテーブルのモデルの内、1つでも変更があればtrueになる。
     *
     * key: repository class name string
     * value: bool
     *
     * @var array<string, bool>
     */
    private array $needSaves = [];

    /**
     * APIリクエスト実行前と比較して、値が変更されているモデルのキーを管理する。
     * UsrModelInterfaceのisChanged（EloquentModelのisDirty）は、DB保存後にfalseになる。
     * そのため、DB保存後にAPIリクエストの中で変更があったのかどうかがわからなくなる。
     * そこで、変更したかをDB更新後も保持できるように用意。
     *
     * @var array<string, array<string>>
     * key: repository class name string, value: model key string
     */
    private array $changedModelKeys = [];

    /**
     * DBから全データを取得済みかどうかを管理する。
     * true: 全データを取得済み, false または キーが存在しない: 未取得または一部のみ取得
     *
     * @var array<string, bool>
     * key: repository class name string, value: bool
     */
    private array $isAllFetcheds = [];

    public function __construct()
    {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    /**
     * SignUpUseCase内でユーザーを新規作成するため、作成するまでユーザーIDを取得できないため用意。
     * 基本的に上記のケース以外では、使用しないでください。
     */
    public function setUsrUserId(string $usrUserId): void
    {
        $this->usrUserId = $usrUserId;
    }

    /**
     * DBから全データを取得しているかどうかを返す
     * true: 全データを取得済み, false: 未取得または一部のみ取得
     */
    public function getIsAllFetched(string $repositoryClass): bool
    {
        return $this->isAllFetcheds[$repositoryClass] ?? false;
    }

    /**
     * DBから全データを取得済みであるとしてフラグを立てる
     */
    public function markAllFetched(string $repositoryClass): void
    {
        $this->isAllFetcheds[$repositoryClass] = true;
    }

    /**
     * apiリクエスト中で、値に変更があったモデルを管理するキーを取得する。
     *
     * @return array<string>
     */
    private function getChangedModelKeys(string $repositoryClass): array
    {
        return $this->changedModelKeys[$repositoryClass] ?? [];
    }

    /**
     * apiリクエスト中で、値に変更があったモデルを管理するために、キーを追加する。
     */
    private function addChangedModelKey(string $repositoryClass, string $modelKey): void
    {
        $this->changedModelKeys[$repositoryClass][$modelKey] = $modelKey;
    }

    /**
     * APIリクエスト実行前と比較して、変更があったモデルのみを返す。
     * APIレスポンスで変更があったモデルのみを返したいときに使える。
     *
     * キャッシュのモデルを直接操作しないようにするため、クローンしたモデルを返す。
     */
    public function getChangedModels(string $repositoryClass): Collection
    {
        return collect($this->getClonedModelsByModelKeys(
            $repositoryClass,
            $this->getChangedModelKeys($repositoryClass),
        ));
    }

    /**
     * 指定されたRepositoryのキャッシュ済みモデルを全て取得する。
     * ここではクローンはせず、そのまま返す。
     *
     * このメソッドをラップするメソッド側で、クローンなどを行い、ビジネスロジック側へ参照渡ししないようにしてください。
     *
     * @return array<string, UsrModelInterface> key: model key string, value: UsrModelInterface
     */
    public function getModels(string $repositoryClass): array
    {
        return $this->models[$repositoryClass] ?? [];
    }

    /**
     * 指定されたモデルキー配列に該当するモデルを取得し、クローンして参照を切ったのちに返す
     *
     * @param string $repositoryClass
     * @param array<string> $modelKeys
     * @return array<string, UsrModelInterface>
     */
    public function getClonedModelsByModelKeys(string $repositoryClass, array $modelKeys): array
    {
        if (empty($modelKeys)) {
            return [];
        }

        return $this->cloneModels(
            array_intersect_key($this->getModels($repositoryClass), array_flip($modelKeys))
        );
    }

    /**
     * 指定されたRepositoryのキャッシュ済みモデルを全て取得し、クローンして参照を切ったのちに返す。
     * @param string $repositoryClass
     * @return array<string, UsrModelInterface> key: model key
     */
    public function getClonedModels(string $repositoryClass): array
    {
        return $this->cloneModels($this->getModels($repositoryClass));
    }

    /**
     * 指定されたモデルをクローンして返す
     *
     * ビジネスロジック側での変更が、意図せずキャッシュに反映されないように、クローンして参照を切る。
     *
     * @param array<UsrModelInterface> $models
     * @return array<string, UsrModelInterface> $models
     * key: model key string, value: UsrModelInterface
     */
    private function cloneModels(array $models): array
    {
        $clones = [];
        foreach ($models as $modelKey => $model) {
            $clones[$modelKey] = clone $model;
        }

        return $clones;
    }

    /**
     * 指定されたクラスが、UsrModelManagerの処理対象として有効なリポジトリクラスかどうかを判定する。
     *
     * @param string $repositoryClass
     * @return bool true: 有効なリポジトリクラス, false: 無効なクラス
     */
    private function isValidRepository(string $repositoryClass): bool
    {
        // すでにキャッシュ追加済みのリポジトリクラスなら、有効なリポジトリクラスとする
        if (isset($this->models[$repositoryClass])) {
            return true;
        }

        return class_exists($repositoryClass)
            && in_array(UsrModelCacheRepositoryInterface::class, class_implements($repositoryClass));
    }

    /**
     * 指定したRepositoryのキャッシュに対して、指定したモデルを追加または上書きする。
     * 既にキャッシュ済みのモデルなら、上書きしたいモデルに変更がある場合のみ上書きする。
     *
     * 既にキャッシュ済みのモデルで、上書きしようとしているモデルに変更がない場合は何もしない。
     * 例： id:1のデータをDBから取得し、モデルの値を変え、キャッシュに追加し、
     *     その後、再度id:1のデータをDBから取得して、キャッシュに追加しようとしてしまった場合でも、
     *     DBから取得した状態では変更はない(isDirty=false)ため、キャッシュを上書きする必要がない。
     *
     * @param array<UsrModelInterface> $targetModels
     * @return void
     */
    public function syncModels(string $repositoryClass, array $targetModels): void
    {
        if (empty($targetModels) || $this->isValidRepository($repositoryClass) === false) {
            return;
        }

        $models = $this->getModels($repositoryClass);

        foreach ($targetModels as $targetModel) {
            // 参照渡しされているモデルをキャッシュに追加すると、ビジネスロジックでの変更が伝播してしまうので、クローンして参照を切る
            $targetModel = clone $targetModel;

            $modelKey = $targetModel->makeModelKey();
            $isExists = isset($models[$modelKey]);

            // 既にキャッシュ済みのモデルなら、上書きしようとしているモデルに変更がある場合のみ上書きする
            if ($isExists && $targetModel->isChanged() === false) {
                continue;
            }

            if ($targetModel->isChanged()) {
                // 変更がある場合は、DB更新が必要なため、DB更新フラグを立てる
                $this->needSaves[$repositoryClass] = true;

                // apiリクエスト前と比較して、変更があったモデルのキーを管理する
                $this->addChangedModelKey($repositoryClass, $modelKey);
            }

            $models[$modelKey] = $targetModel;
        }

        // キャッシュ内容の更新
        $this->models[$repositoryClass] = $models;
    }

    // /**
    //  * @deprecated 削除メソッドは不完全なので、必要になったら処理を見直して実装してください
    //  *
    //  * キャッシュから指定したモデルを削除する。
    //  * DBからレコード削除した際に、キャッシュからも削除するために用意。
    //  *
    //  * @param string $repositoryClass
    //  * @param Collection<UsrModelInterface> $targetModels
    //  * @return void
    //  */
    // public function syncDeleteModels(string $repositoryClass, Collection $targetModels): void
    // {
    //     if ($targetModels->isEmpty()) {
    //         return;
    //     }

    //     $models = $this->getModels($repositoryClass);

    //     foreach ($targetModels as $targetModel) {
    //         $modelKey = $targetModel->makeModelKey();
    //         $model = $models->get($modelKey);

    //         if (is_null($model)) {
    //             continue;
    //         }

    //         $models->forget($modelKey);
    //     }

    //     $this->models->put($repositoryClass, $models);
    // }

    /**
     * 変更したモデルがある かつ DB更新未実行 の全テーブルに対してDB一括更新を実行する
     *
     * @return void
     */
    public function saveAll(): void
    {
        foreach ($this->needSaves as $repositoryClass => $needSave) {
            if ($needSave === false) {
                continue;
            }

            $this->saveModels($repositoryClass);
        }
    }

    /**
     * 変更したモデルがある かつ DB更新未実行 のテーブルに対してDB一括更新を実行する
     *
     * @return void
     */
    private function saveModels(string $repositoryClass): void
    {
        $repository = app()->make($repositoryClass);
        if ($repository instanceof UsrModelCacheRepositoryInterface === false) {
            return;
        }

        // DB更新未実行のモデルを取得
        /** @var UsrModelCacheRepositoryInterface $repository */
        $cache = $this->getModels($repositoryClass);

        $needSaveModels = [];
        foreach ($cache as $modelKey => $model) {
            if ($model->isChanged()) {
                $needSaveModels[$modelKey] = $model;
            }
        }

        if (empty($needSaveModels)) {
            $this->needSaves[$repositoryClass] = false;
            return;
        }

        // DB一括更新実行
        /** @var UsrModelCacheRepositoryInterface $repository */
        $repository->saveModelsByUsrModelManager(collect($needSaveModels));

        // DB更新後の各種ステータス変更
        foreach ($needSaveModels as $model) {
            // DBに対して重複更新しないように、更新後の状態をオリジナルとする(isDirty=falseにする)
            $model->syncOriginal();
            $cache[$model->makeModelKey()] = $model;
        }
        // キャッシュ内容を更新
        $this->models[$repositoryClass] = $cache;
        // DB更新を実行して、DBとの差分がなくなったので、DB更新不要フラグを立てる
        $this->needSaves[$repositoryClass] = false;
    }
}
