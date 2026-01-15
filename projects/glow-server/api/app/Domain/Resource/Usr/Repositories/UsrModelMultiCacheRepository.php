<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Illuminate\Support\Collection;

/**
 * 1ユーザーあたりのレコード数が2つ以上になるテーブルに対して、
 * UsrModelManagerを用いたキャッシュ機構を有効にする際に、親クラスとして利用するRepositoryクラス。
 *
 * EloquentModelを継承しているモデルクラスが対象
 */
class UsrModelMultiCacheRepository extends UsrModelCacheRepository
{
    /**
     * 複数レコードを取得したい場合に、キャッシュの確認も同時に行うメソッド。
     * cacheCallbackでフィルタリングを行い、キャッシュからデータを取得する。
     *
     * 複数レコード取得が期待されるが、フィルタリングによっては、取得できるレコード数が未知数であるため、
     * expectedCountを指定して、その数分のデータがキャッシュにあった場合に、DBから取得しないようにする。
     * 1つでもキャッシュにないデータがあった場合は、dbCallbackを実行し、DBから取得する。
     * expectedCountが未指定の場合は、DBから取得する。
     *
     * 取得できるデータ数がわからない場合は、expectedCountを未指定で問題ないですが、
     * その場合は、cachedGetAllを使った方が効率が良いことが想定されます。
     * DBから取得したのちに、キャッシュへの追加も行うため、子クラスRepositoryでのキャッシュ追加の記述(syncModels)が不要になるためです。
     *
     * 他人のデータを取得する場合は、毎回DBから取得する。
     *
     * @param string $usrUserId 取得したいデータを所持しているユーザーのID
     * @param callable $cacheCallback キャッシュからデータを取得するフィルタリング関数
     * @param int|null $expectedCount 取得したいデータの数。指定した場合は、その数分のデータがキャッシュにあった場合に、DBから取得しないようにする。
     * @param callable $dbCallback 対象のユーザーテーブルから全レコードを取得するDBクエリを実行する関数
     * @return Collection
     */
    protected function cachedGetMany(
        string $usrUserId,
        callable $cacheCallback,
        ?int $expectedCount,
        callable $dbCallback
    ): Collection {

        // 他人のデータを取得する場合は、毎回DBから取得する。
        if ($this->isOwnUsrUserId($usrUserId) === false) {
            return $dbCallback();
        }

        // キャッシュにデータがあれば、それを返す
        // ここでは、キャッシュから取得したモデルを直接操作はしないので、処理効率化のために、cloneせずに取得する
        $models = $cacheCallback(collect($this->getCache(isClone: false)));
        if (is_null($expectedCount) === false && count($models) === $expectedCount) {
            return $this->getCacheFilteredByModelKey($models);
        }

        // キャッシュになかったのでDBから取得する
        $models = $dbCallback();

        // キャッシュされていないデータをキャッシュに追加する
        $this->addModelsIfAbsent($models);

        // 最新の変更を反映するために、キャッシュから取得する
        // DBから取得したものをそのまま返すと、キャッシュにすでにあったモデルの値がデグレする可能性があるため。
        return $this->getCacheFilteredByModelKey(
            $cacheCallback(collect($this->getCache(isClone: false))),
        );
    }

    /**
     * 1つのモデルを取得したい場合に、キャッシュの確認も同時に行うメソッド。
     * 指定されたパラメータでフィルタリングを行い、キャッシュからデータを取得する。
     * キャッシュにデータがない場合は、dbCallbackを実行し、DBから取得する。
     * DBから取得したら、キャッシュ管理に追加する。
     *
     * 他人のデータを取得する場合は、毎回DBから取得する。
     *
     * @param string $usrUserId 取得したデータを所持しているユーザーのID
     * @param string $columnKey 取得したいデータのカラム名
     * @param mixed $columnValue 取得したいデータのカラム値
     * @param callable $dbCallback 対象のユーザーテーブルから最大でも1レコードを取得するDBクエリを実行する関数
     */
    protected function cachedGetOneWhere(
        string $usrUserId,
        string $columnKey,
        mixed $columnValue,
        callable $dbCallback
    ): mixed {

        $models = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function () use ($columnKey, $columnValue) {
                return collect($this->getCacheWhereIn($columnKey, [$columnValue]));
            },
            expectedCount: 1,
            dbCallback: function () use ($dbCallback) {
                $model = $dbCallback();
                if ($model instanceof Collection) {
                    return $model;
                }
                // cachedGetManyのdbCallbackの戻り値は、Collectionである必要があるため、Collectionに変換する
                if (is_null($model)) {
                    return collect();
                }
                return collect([$model]);
            },
        );

        // 取得を期待するデータ数は0か1個のみ。
        // 2個以上あった場合は想定しない挙動になっているため、エラーにする。
        if (count($models) >= 2) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf(
                    'Invalid parameter. Expected 0 or 1, but got %d.',
                    count($models),
                ),
            );
        }

        return $models->first();
    }

    /**
     * 指定したユーザーの全リソースを取得する。
     * すでに全取得済みの場合は、キャッシュを返す。
     * そうでない場合は、DBから取得し、キャッシュに追加する。
     *
     * 他人のデータを取得する場合は、毎回DBから取得する。
     *
     * @param string $usrUserId 取得したいデータを所持しているユーザーのID
     * @return Collection
     */
    protected function cachedGetAll(string $usrUserId): Collection
    {
        $dbCallback = function () use ($usrUserId) {
            return $this->dbSelectAll($usrUserId);
        };

        // 他人のデータを取得する場合は、毎回DBから取得する。
        if ($this->isOwnUsrUserId($usrUserId) === false) {
            return $dbCallback();
        }

        // 自身のデータを取得する際に、すでに全取得済みであれば、DBクエリを実行せずに、キャッシュを返す。
        if ($this->isAllFetched() === false) {
            $models = $dbCallback();
            // 全リソースを取得したとみなして、全取得済みフラグを立てる
            $this->markAllFetched($usrUserId);

            // キャッシュされていないデータをキャッシュに追加する
            $this->addModelsIfAbsent($models);
        }

        // 最新の変更を反映するために、キャッシュから取得する
        // DBから取得したものをそのまま返すと、もしキャッシュすでにある場合に、値がデグレする可能性があるため。
        return collect($this->getCache());
    }

    protected function dbSelectAll(string $usrUserId): Collection
    {
        return $this->modelClass::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
    }
}
