<?php

namespace Database\Seeders\Dummies;

use App\Models\GenericUsrModel;

/**
 * usrテーブルのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyGenericUsrSeeder"
 */
class DummyGenericUsrSeeder extends DummyGenericSeeder
{
    protected string $tableName = GenericUsrModel::class;
}
