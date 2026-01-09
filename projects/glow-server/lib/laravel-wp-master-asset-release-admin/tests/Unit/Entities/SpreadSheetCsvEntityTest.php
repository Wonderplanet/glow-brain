<?php

namespace MasterAssetReleaseAdmin\Unit\Entities;

use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\SheetProperties;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetCsvEntity;
use WonderPlanet\Tests\TestCase;

class SpreadSheetCsvEntityTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @test
     * @dataProvider setDataArray
     */
    public function setData_有効なデータのみセットされているかチェック(array $data): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $releaseKey = 202301;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $releaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // TABLE行、ENABLE(カラム)行、有効なデータのみになっているか
        $this->assertCount(4, $actual);
        $this->assertEquals([
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
            ["e", 202301, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標"],
        ], $actual);
    }

    /**
     * @return array[]
     */
    private function setDataArray(): array
    {
        // MstAvatarテーブルと仮定
        return [
            'メモあり' => [
                [
                    ["テストメモ", "テストメモ2"],
                    ["memo"],
                    ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                    ["", 202301, 2, "dummy2", "Common", "ダミー2", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標"],
                    ["e", 202301, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標"],
                ]
            ],
            'メモなし' => [
                [
                    ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
                    ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
                    ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
                    ["", 202301, 2, "dummy2", "Common", "ダミー2", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標"],
                    ["e", 202301, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標"],
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function setData_i18nを含まないマスタシートの取り込みチェック(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $data = [
            ["テストメモ", "テストメモ2"],
            ["memo"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity"],
            ["e", 202301, 1, "dummy1", "Common"],
            ["", 202301, 2, "dummy2", "Common"],
            ["e", 202301, 3, "dummy3", "Common"],
        ];
        $releaseKey = 202301;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $releaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // ENABLE(カラム)行、有効なデータのみになっているか
        $this->assertCount(3, $actual);
        $this->assertEquals([
            ["ENABLE", "release_key", "id", "asset_key", "rarity"],
            ["e", 202301, 1, "dummy1", "Common"],
            ["e", 202301, 3, "dummy3", "Common"],
        ], $actual);
    }

    /**
     * @test
     */
    public function setData_メモ列を取り込まないようにするチェック_中間にメモ列がある(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $data = [
            ["memo", "", "", "", "", "レアリティメモ", "", "アイコンメモ", "", "", "", ""],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "", "MstAvatarI18n", "", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "", "name.ja", "", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "レアリティはcommon", "ダミー1", "アイコンはダミー", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標"],
            ["", 202301, 2, "dummy2", "Common", "レアリティはcommon", "ダミー2", "アイコンはダミー", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標"],
            ["e", 202301, 3, "dummy3", "Common", "レアリティはcommon", "ダミー3", "アイコンはダミー", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標"],
        ];
        $releaseKey = 202301;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $releaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // メモ列が除外された有効なデータのみになっているか
        $this->assertCount(4, $actual);
        // keyが歯抜けの状態で一致するか
        $this->assertEquals([
            [
                0 => "TABLE",
                1 => "MstAvatar",
                2 => "MstAvatar",
                3 => "MstAvatar",
                4 => "MstAvatar",
                6 => "MstAvatarI18n",
                8 => "MstAvatarI18n",
                9 => "MstAvatarI18n",
                10 => "MstAvatarI18n",
                11 => "MstAvatarI18n",
                12 => "MstAvatarI18n",
            ],
            [
                0 => "ENABLE",
                1 => "release_key",
                2 => "id",
                3 => "asset_key",
                4 => "rarity",
                6 => "name.ja",
                8 => "description.ja",
                9 => "name.en",
                10 => "description.en",
                11 => "name.zh-Hant",
                12 => "description.zh-Hant",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 1,
                3 => "dummy1",
                4 => "Common",
                6 => "ダミー1",
                8 => "ダミー1のアイコン",
                9 => "Dummy 1",
                10 => "Dummy 1 icon",
                11 => "虛擬1",
                12 => "虛擬1的圖標",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 3,
                3 => "dummy3",
                4 => "Common",
                6 => "ダミー3",
                8 => "ダミー3のアイコン",
                9 => "Dummy 3",
                10 => "Dummy 3 icon",
                11 => "虛擬3",
                12 => "虛擬3的圖標"
            ],
        ], $actual);
    }

    /**
     * @test
     */
    public function setData_メモ列を取り込まないようにするチェック_末尾にメモ列がある(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $data = [
            ["memo", "", "", "", "", "", "", "", "", "", "レアリティメモ", "アイコンメモ"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標", "レアリティはcommon", "アイコンはダミー"],
            ["", 202301, 2, "dummy2", "Common", "ダミー2", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標", "レアリティはcommon", "アイコンはダミー"],
            ["e", 202301, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標", "レアリティはcommon", "アイコンはダミー"],
        ];
        $releaseKey = 202301;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $releaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // メモ列が除外された有効なデータのみになっているか
        $this->assertCount(4, $actual);
        // keyも含めて一致するか
        $this->assertEquals([
            [
                0 => "TABLE",
                1 => "MstAvatar",
                2 => "MstAvatar",
                3 => "MstAvatar",
                4 => "MstAvatar",
                5 => "MstAvatarI18n",
                6 => "MstAvatarI18n",
                7 => "MstAvatarI18n",
                8 => "MstAvatarI18n",
                9 => "MstAvatarI18n",
                10 => "MstAvatarI18n",
            ],
            [
                0 => "ENABLE",
                1 => "release_key",
                2 => "id",
                3 => "asset_key",
                4 => "rarity",
                5 => "name.ja",
                6 => "description.ja",
                7 => "name.en",
                8 => "description.en",
                9 => "name.zh-Hant",
                10 => "description.zh-Hant",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 1,
                3 => "dummy1",
                4 => "Common",
                5 => "ダミー1",
                6 => "ダミー1のアイコン",
                7 => "Dummy 1",
                8 => "Dummy 1 icon",
                9 => "虛擬1",
                10 => "虛擬1的圖標",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 3,
                3 => "dummy3",
                4 => "Common",
                5 => "ダミー3",
                6 => "ダミー3のアイコン",
                7 => "Dummy 3",
                8 => "Dummy 3 icon",
                9 => "虛擬3",
                10 => "虛擬3的圖標"
            ],
        ], $actual);
    }

    /**
     * @test
     */
    public function setData_メモ列を取り込まないようにするチェック_中間と末尾にメモ列がある(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $data = [
            ["memo", "", "", "", "", "レアリティメモ", "", "アイコンメモ", "", "", "", "", "各レコードのメモ1", "各レコードのメモ2"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "", "MstAvatarI18n", "", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "", "name.ja", "", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "レアリティはcommon", "ダミー1", "アイコンはダミー", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標", "メモdummy1", "メモ2dummy1"],
            ["", 202301, 2, "dummy2", "Common", "レアリティはcommon", "ダミー2", "アイコンはダミー", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標", "メモdummy2", "メモ2dummy2"],
            ["e", 202301, 3, "dummy3", "Common", "レアリティはcommon", "ダミー3", "アイコンはダミー", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標", "メモdummy3", "メモ3dummy3"],
        ];
        $releaseKey = 202301;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $releaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // メモ列が除外された有効なデータのみになっているか
        $this->assertCount(4, $actual);
        // keyが歯抜けの状態で一致するか
        $this->assertEquals([
            [
                0 => "TABLE",
                1 => "MstAvatar",
                2 => "MstAvatar",
                3 => "MstAvatar",
                4 => "MstAvatar",
                6 => "MstAvatarI18n",
                8 => "MstAvatarI18n",
                9 => "MstAvatarI18n",
                10 => "MstAvatarI18n",
                11 => "MstAvatarI18n",
                12 => "MstAvatarI18n",
            ],
            [
                0 => "ENABLE",
                1 => "release_key",
                2 => "id",
                3 => "asset_key",
                4 => "rarity",
                6 => "name.ja",
                8 => "description.ja",
                9 => "name.en",
                10 => "description.en",
                11 => "name.zh-Hant",
                12 => "description.zh-Hant",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 1,
                3 => "dummy1",
                4 => "Common",
                6 => "ダミー1",
                8 => "ダミー1のアイコン",
                9 => "Dummy 1",
                10 => "Dummy 1 icon",
                11 => "虛擬1",
                12 => "虛擬1的圖標",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 3,
                3 => "dummy3",
                4 => "Common",
                6 => "ダミー3",
                8 => "ダミー3のアイコン",
                9 => "Dummy 3",
                10 => "Dummy 3 icon",
                11 => "虛擬3",
                12 => "虛擬3的圖標"
            ],
        ], $actual);
    }
    
    /**
     * @test
     */
    public function setData_自環境の最大リリースキーより大きいリリースキーがあれば取り込まないチェック(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $spreadSheetCsvEntity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $data = [
            ["memo", "", "", "", "", "", "", "", "", "", "レアリティメモ", "アイコンメモ"],
            ["TABLE", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatar", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n", "MstAvatarI18n"],
            ["ENABLE", "release_key", "id", "asset_key", "rarity", "name.ja", "description.ja", "name.en", "description.en", "name.zh-Hant", "description.zh-Hant"],
            ["e", 202301, 1, "dummy1", "Common", "ダミー1", "ダミー1のアイコン", "Dummy 1", "Dummy 1 icon", "虛擬1", "虛擬1的圖標", "レアリティはcommon", "アイコンはダミー"],
            ["e", 202302, 2, "dummy2", "Common", "ダミー2", "ダミー2のアイコン", "Dummy 2", "Dummy 2 icon", "虛擬2", "虛擬2的圖標", "レアリティはcommon", "アイコンはダミー"],
            ["e", 202303, 3, "dummy3", "Common", "ダミー3", "ダミー3のアイコン", "Dummy 3", "Dummy 3 icon", "虛擬3", "虛擬3的圖標", "レアリティはcommon", "アイコンはダミー"],
        ];
        $maxReleaseKey = 202302;

        // Exercise
        $spreadSheetCsvEntity->setData($data, $maxReleaseKey);
        $actual = $spreadSheetCsvEntity->getData();

        // Verify
        // メモ列が除外された有効なデータのみになっているか
        $this->assertCount(4, $actual);
        // keyも含めて一致するか
        $this->assertEquals([
            [
                0 => "TABLE",
                1 => "MstAvatar",
                2 => "MstAvatar",
                3 => "MstAvatar",
                4 => "MstAvatar",
                5 => "MstAvatarI18n",
                6 => "MstAvatarI18n",
                7 => "MstAvatarI18n",
                8 => "MstAvatarI18n",
                9 => "MstAvatarI18n",
                10 => "MstAvatarI18n",
            ],
            [
                0 => "ENABLE",
                1 => "release_key",
                2 => "id",
                3 => "asset_key",
                4 => "rarity",
                5 => "name.ja",
                6 => "description.ja",
                7 => "name.en",
                8 => "description.en",
                9 => "name.zh-Hant",
                10 => "description.zh-Hant",
            ],
            [
                0 => "e",
                1 => 202301,
                2 => 1,
                3 => "dummy1",
                4 => "Common",
                5 => "ダミー1",
                6 => "ダミー1のアイコン",
                7 => "Dummy 1",
                8 => "Dummy 1 icon",
                9 => "虛擬1",
                10 => "虛擬1的圖標",
            ],
            [
                0 => "e",
                1 => 202302,
                2 => 2,
                3 => "dummy2",
                4 => "Common",
                5 => "ダミー2",
                6 => "ダミー2のアイコン",
                7 => "Dummy 2",
                8 => "Dummy 2 icon",
                9 => "虛擬2",
                10 => "虛擬2的圖標"
            ],
        ], $actual);
    }
}
