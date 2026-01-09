<?php

namespace Database\Seeders\Dummies;

use App\Models\GenericOprModel;

/**
 * oprテーブルのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyGenericOprSeeder"
 */
class DummyGenericOprSeeder extends DummyGenericSeeder
{
    protected string $tableName = GenericOprModel::class;
}
