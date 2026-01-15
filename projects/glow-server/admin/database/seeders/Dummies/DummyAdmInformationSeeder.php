<?php

namespace Database\Seeders\Dummies;

use App\Models\Adm\AdmInformation;
use Illuminate\Database\Seeder;

/**
 * お知らせ機能テスト用のダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyAdmInformationSeeder"
 */
class DummyAdmInformationSeeder extends Seeder
{
    public int $numberOfRecords = 30;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        AdmInformation::factory($this->numberOfRecords)->create([
            'html_json' => json_encode([
            "type" => "doc",
            "content" => [
                [
                "type" => "paragraph",
                "attrs" => [
                    "textAlign" => "start"
                ],
                "content" => [
                    [
                    "text" => "test",
                    "type" => "text"
                    ]
                ]
                ]
            ]
            ]),
        ]);
    }
}
