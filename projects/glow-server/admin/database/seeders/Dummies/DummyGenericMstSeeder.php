<?php

namespace Database\Seeders\Dummies;

use App\Models\GenericMstModel;

/**
 * mstテーブルのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyGenericMstSeeder"
 */
class DummyGenericMstSeeder extends DummyGenericSeeder
{
    protected string $tableName = GenericMstModel::class;
}
