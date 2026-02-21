<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class TestUsrModelMultiCacheRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = TestMultiModel::class;

    protected function saveModels(Collection $models): void
    {
        // TestMultiModelモデルのDBテーブルはないため、string_valueに'saveModel'がセットしてあればDB保存できたとみなす
        foreach ($models as $model) {
            $model->string_value = 'saveModel';
        }
    }
}
