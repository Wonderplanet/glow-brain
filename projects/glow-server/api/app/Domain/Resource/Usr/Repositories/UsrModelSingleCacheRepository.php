<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

/**
 * 1ユーザーあたりのレコード数が最大でも1つになるテーブルに対して、
 * UsrModelManagerを用いたキャッシュ機構を有効にする際に、親クラスとして利用するRepositoryクラス。
 */
class UsrModelSingleCacheRepository extends UsrModelCacheRepository
{
    /**
     * キャッシュの先頭のモデルを取得する。
     */
    protected function getFirstCache(): ?UsrModelInterface
    {
        return array_values(parent::getCache())[0] ?? null;
    }

    /**
     * キャッシュされたモデルを取得し、
     * モデルがない場合は、dbCallbackを実行し、DBから取得するメソッド
     *
     * 他人のデータを取得する場合は、毎回DBから取得する。
     */
    protected function cachedGetOne(string $usrUserId): mixed
    {
        $dbCallback = function () use ($usrUserId) {
            return $this->dbSelectOne($usrUserId);
        };

        // 他人のデータを取得する場合は、毎回DBから取得する。
        if ($this->isOwnUsrUserId($usrUserId) === false) {
            return $dbCallback();
        }

        $model = $this->getFirstCache();
        if (is_null($model) === false) {
            return $model;
        }

        // 1回でもDB取得を試した上で、キャッシュにデータがないなら、DBから取得しない。
        // 1ユーザーあたりのレコード数が1つであるテーブルであるため、DBにデータがないことが分かっているため。
        if ($this->isAllFetched()) {
            return null;
        }

        // DBからデータ取得する
        $model = $dbCallback();
        // 最大でも1レコードのみなので、今DBにある値は全て取得したとみなす。
        $this->markAllFetched($usrUserId);

        if (is_null($model)) {
            return null;
        }

        $this->syncModel($model);

        // 最新の変更を反映するために、キャッシュから取得する
        // DBから取得したものをそのまま返すと、もしキャッシュすでにある場合に、値がデグレする可能性があるため。
        return $this->getFirstCache();
    }

    protected function dbSelectOne(string $usrUserId): ?UsrModelInterface
    {
        return $this->modelClass::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
    }
}
