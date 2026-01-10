<?php

namespace Database\Seeders\Dummies;

use App\Models\GenericLogModel;

/**
 * logテーブルのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyGenericLogSeeder"
 */
class DummyGenericLogSeeder extends DummyGenericSeeder
{
    protected string $tableName = GenericLogModel::class;
}
