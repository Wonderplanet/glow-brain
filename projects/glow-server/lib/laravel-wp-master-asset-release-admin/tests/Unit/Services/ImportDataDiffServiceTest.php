<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\ImportDataDiffEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\ImportDataDiffService;
use WonderPlanet\Tests\TestCase;

class ImportDataDiffServiceTest extends TestCase
{
    use ReflectionTrait;

    private ImportDataDiffService $importDataDiffService;

    public function setUp(): void
    {
        parent::setUp();

        $this->importDataDiffService = app(ImportDataDiffService::class);
    }

    /**
     * @test
     */
    public function parseDiff_git_diffの結果をパースできているかチェック(): void
    {
        // Exercise
        $actual = $this->callMethod(
            $this->importDataDiffService,
            'parseDiff',
            $this->getGitDiffData()
        );

        // Verify
        $this->assertEquals([
            'MstItem.csv' => [
                [
                    'old_columns' => ["ENABLE", "release_key", "id", "rarity", "item_key", "asset_key"],
                    'old' => [
                        ["e", "20230101", "1", "Common", "1", "dragon_fruit"],
                        ["e", "20230101", "2", "Common", "2", "passion_fruit"],
                        ["e", "202407", "evo_14", "Elite", "28", "piece_kiwi"],
                    ],
                    'new_columns' => ["ENABLE", "release_key", "id", "rarity", "item_key", "add_column"],
                    'new' => [
                        ["e", "20230101", "1", "Common", "1", "aaa",],
                        ["e", "20230101", "999", "Common", "999", "aaa"],
                        ["e", "202401", "evo_14", "Elite", "28", "aaa"],
                        ["e", "20230101", "ticket_test2", "Common", "20000", "aaa"],
                    ],
                ],
            ],
            'MstItemI18n.csv' => [
                1 => [
                    'old' => [
                        ["e", "20230101", "ja_2","2", "ja", "パッションフルーツ", "小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。"],
                    ],
                    'new' => [
                        ["e", "20230101", "ja_999","999", "ja", "パッションフルーツ", "小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。"],
                    ],
                ],
                2 => [
                    'old' => [
                        ["e", "20230101", "ja_evo_0","evo_0", "ja", "キウイのかけら", "キウイの覚醒に使用するアイテム。一定数集めると潜在能力を解放できる。"],
                    ],
                    'new' => [
                        ["e", "20230102", "ja_evo_0","evo_0", "ja", "キウイのかけら", "キウイの覚醒に使用するアイテム。一定数集めると潜在能力を解放できる。"],
                    ],
                ],
                3 => [
                    'old' => [
                        ["e", "20230101", "en_2","2", "en", "Passion fruit", "It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character"],
                    ],
                    'new' => [
                        ["e", "20230101", "en_999","999", "en", "Passion fruit", "It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character"],
                    ],
                ],
            ],
        ], $actual);
    }

    /**
     * @test
     */
    public function makeDiffData_差分データ生成チェック(): void
    {
        // Setup
        $parsedDiff = $this->callMethod(
            $this->importDataDiffService,
            'parseDiff',
            $this->getGitDiffData()
        );
        $headers = [
            'MstItem.csv' => ['ENABLE','release_key','id','rarity','item_key','asset_key'],
            'MstItemI18n.csv' => ['ENABLE','release_key','id','mst_item_id','language','name','description'],
        ];

        // Exercise
        $actuals = $this->callMethod(
            $this->importDataDiffService,
            'makeDiffData',
            $parsedDiff,
            $headers
        );

        // Verify
        // MstItem
        /** @var ImportDataDiffEntity $actualMstItemEntity */
        $actualMstItemEntity = collect($actuals)->first(fn (ImportDataDiffEntity $entity) => $entity->getSheetName() === 'MstItem');
        // ヘッダー(カラム行)チェック
        $this->assertEquals(
            [
                'id',
                'release_key',
                'rarity',
                'item_key',
                'asset_key',
                'add_column',
            ],
            $actualMstItemEntity->getHeader()
        );
        // 変更行チェック
        $modifyDataColl = collect($actualMstItemEntity->getModifyData());
        $modifyData1 = $modifyDataColl->first(fn ($row) => $row['beforeRow']['id'] === '1');
        $this->assertEquals([
            'id' => '1',
            'release_key' => '20230101',
            'rarity' => 'Common',
            'item_key' => '1',
            'asset_key' => 'dragon_fruit',
        ], $modifyData1['beforeRow']);
        $this->assertEquals([
            'add_column' => 'aaa',
        ], $modifyData1['modifyColumnMap']);
        $modifyData2 = $modifyDataColl->first(fn ($row) => $row['beforeRow']['id'] === 'evo_14');
        $this->assertEquals([
            'id' => 'evo_14',
            'release_key' => '202407',
            'rarity' => 'Elite',
            'item_key' => '28',
            'asset_key' => 'piece_kiwi',
        ], $modifyData2['beforeRow']);
        $this->assertEquals([
            'release_key' => '202401',
            'add_column' => 'aaa',
        ], $modifyData2['modifyColumnMap']);
        // 削除行チェック
        $deleteDataColl = collect($actualMstItemEntity->getDeleteData());
        $this->assertEquals(
            [
                'id' => '2',
                'release_key' => '20230101',
                'rarity' => 'Common',
                'item_key' => '2',
                'asset_key' => 'passion_fruit',
            ],
            $deleteDataColl->first()
        );
        // 新規追加行チェック
        $newDataColl = collect($actualMstItemEntity->getNewData());
        $newData1 = $newDataColl->first(fn ($row) => $row['id'] === '999');
        $this->assertEquals([
            'id' => '999',
            'release_key' => '20230101',
            'rarity' => 'Common',
            'item_key' => '999',
            'add_column' => 'aaa',
        ], $newData1);
        $newData2 = $newDataColl->first(fn ($row) => $row['id'] === 'ticket_test2');
        $this->assertEquals([
            'id' => 'ticket_test2',
            'release_key' => '20230101',
            'rarity' => 'Common',
            'item_key' => '20000',
            'add_column' => 'aaa',
        ], $newData2);

        // テーブル構造変更(カラム追加)チェック
        $this->assertEquals(
            ['add_column'],
            array_values($actualMstItemEntity->getStructureDiffAddData())
        );
        // テーブル構造変更(カラム削除)チェック
        $this->assertEquals(
            ['asset_key'],
            array_values($actualMstItemEntity->getStructureDiffDeleteData())
        );
        // リリースキーごとの変更行数をチェック
        $actualModifyRowCountMapByReleaseKey = $actualMstItemEntity->getModifyRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 1,
            202407 => 1,
        ], $actualModifyRowCountMapByReleaseKey);
        // リリースキーごとの新規追加行数をチェック
        $actualNewRowCountMapByReleaseKey = $actualMstItemEntity->getNewRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 2,
        ], $actualNewRowCountMapByReleaseKey);
        // リリースキーごとの削除行数をチェック
        $actualDeleteRowCountMapByReleaseKey = $actualMstItemEntity->getDeleteRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 1,
        ], $actualDeleteRowCountMapByReleaseKey);

        // MstItemI18n
        /** @var ImportDataDiffEntity $actualMstItemI18nEntity */
        $actualMstItemI18nEntity = collect($actuals)->first(fn (ImportDataDiffEntity $entity) => $entity->getSheetName() === 'MstItemI18n');

        // ヘッダー(カラム行)チェック
        $this->assertEquals(
            [
                'id',
                'release_key',
                'mst_item_id',
                'language',
                'name',
                'description',
            ],
            $actualMstItemI18nEntity->getHeader()
        );

        // 変更行チェック
        $modifyDataColl = collect($actualMstItemI18nEntity->getModifyData());
        $modifyData1 = $modifyDataColl->first(fn ($row) => $row['beforeRow']['id'] === 'ja_evo_0');
        $this->assertEquals([
            'id' => 'ja_evo_0',
            'release_key' => '20230101',
            'mst_item_id' => 'evo_0',
            'language' => 'ja',
            'name' => 'キウイのかけら',
            'description' => 'キウイの覚醒に使用するアイテム。一定数集めると潜在能力を解放できる。',
        ], $modifyData1['beforeRow']);
        $this->assertEquals([
            'release_key' => '20230102',
        ], $modifyData1['modifyColumnMap']);
        // 削除行チェック
        $deleteDataColl = collect($actualMstItemI18nEntity->getDeleteData());
        $deleteData1 = $deleteDataColl->first(fn ($row) => $row['id'] === 'ja_2');
        $this->assertEquals(
            [
                'id' => 'ja_2',
                'release_key' => '20230101',
                'mst_item_id' => '2',
                'language' => 'ja',
                'name' => 'パッションフルーツ',
                'description' => '小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。',
            ],
            $deleteData1
        );
        $deleteData2 = $deleteDataColl->first(fn ($row) => $row['id'] === 'en_2');
        $this->assertEquals(
            [
                'id' => 'en_2',
                'release_key' => '20230101',
                'mst_item_id' => '2',
                'language' => 'en',
                'name' => 'Passion fruit',
                'description' => 'It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character',
            ],
            $deleteData2
        );
        // 新規追加行チェック
        $newDataColl = collect($actualMstItemI18nEntity->getNewData());
        $newData1 = $newDataColl->first(fn ($row) => $row['id'] === 'ja_999');
        $this->assertEquals([
            'id' => 'ja_999',
            'release_key' => '20230101',
            'mst_item_id' => '999',
            'language' => 'ja',
            'name' => 'パッションフルーツ',
            'description' => '小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。',
        ], $newData1);
        $newData2 = $newDataColl->first(fn ($row) => $row['id'] === 'en_999');
        $this->assertEquals([
            'id' => 'en_999',
            'release_key' => '20230101',
            'mst_item_id' => '999',
            'language' => 'en',
            'name' => 'Passion fruit',
            'description' => 'It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character',
        ], $newData2);
        // テーブル構造変更(カラム追加)チェック
        $this->assertEmpty($actualMstItemI18nEntity->getStructureDiffAddData());
        // テーブル構造変更(カラム削除)チェック
        $this->assertEmpty($actualMstItemI18nEntity->getStructureDiffDeleteData());

        // リリースキーごとの変更行数をチェック
        $actualI18nModifyRowCountMapByReleaseKey = $actualMstItemI18nEntity->getModifyRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 1
        ], $actualI18nModifyRowCountMapByReleaseKey);
        // リリースキーごとの新規追加行数をチェック
        $actualI18nNewRowCountMapByReleaseKey = $actualMstItemI18nEntity->getNewRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 2,
        ], $actualI18nNewRowCountMapByReleaseKey);
        // リリースキーごとの削除行数をチェック
        $actualI18nDeleteRowCountMapByReleaseKey = $actualMstItemI18nEntity->getDeleteRowCountMapByReleaseKey();
        $this->assertEquals([
            20230101 => 2,
        ], $actualI18nDeleteRowCountMapByReleaseKey);
    }

    /**
     * GitOperator->diff()の結果として取得する
     *
     * @return array
     */
    private function getGitDiffData(): array
    {
        /**
         * 仮定するシート上の操作
         * カラム削除(asset_key)
         * カラム追加(add_column)
         * id=2を削除、id=999を追加(実質idを2->999に変更した状態だが、差分としてはid2削除、999を追加という見せ方)
         * id=ticket_test2を新規追加
         * id=evo_14のrelease_keyを変更
         * マスタではないファイルが存在する(test.csv)
         */
        return [
            // MstItem
            "diff --git a/MstItem.csv b/MstItem.csv",
            "index aa74891..a26131b 100644",
            "--- a/MstItem.csv",
            "+++ b/MstItem.csv",
            "@@ -1,34 +1,35 @@",
            "-ENABLE,release_key,id,rarity,item_key,asset_key",
            "-e,20230101,1,Common,1,dragon_fruit",
            "-e,20230101,2,Common,2,passion_fruit",
            "-e,202407,evo_14,Elite,28,piece_kiwi",
            "+ENABLE,release_key,id,rarity,item_key,add_column",
            "+e,20230101,1,Common,1,aaa",
            "+e,20230101,999,Common,999,aaa",
            "+e,202401,evo_14,Elite,28,aaa",
            "+e,20230101,ticket_test2,Common,20000,aaa",

            // MstItemI18n
            "diff --git a/MstItemI18n.csv b/MstItemI18n.csv",
            "index 86489b8..bd34019 100644",
            "--- a/MstItemI18n.csv",
            "+++ b/MstItemI18n.csv",
            "@@ -3 +3 @@ e,20230101,ja_1,1,ja,ドラコンフルーツ,色鮮やかで鱗のような外",
            "-e,20230101,ja_2,2,ja,パッションフルーツ,小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。",
            "+e,20230101,ja_999,999,ja,パッションフルーツ,小さな丸い果実で外皮は厚く、中にはジューシーで種がいくつか入ったゼリー状の果肉があり、酸味と甘みが特徴です。",
            "@@ -30 +30 @@ e,202401,ja_evo_14,evo_14,ja,クロマグロのかけら,クロマグロの覚",
            "-e,20230101,ja_evo_0,evo_0,ja,キウイのかけら,キウイの覚醒に使用するアイテム。一定数集めると潜在能力を解放できる。",
            "+e,20230102,ja_evo_0,evo_0,ja,キウイのかけら,キウイの覚醒に使用するアイテム。一定数集めると潜在能力を解放できる。",
            "@@ -36 +37 @@ e,20230101,en_1,1,en,'Dragon fruit','It is a tropical fruit with a brightly colo'",
            '-e,20230101,en_2,2,en,"Passion fruit","It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character"',
            '+e,20230101,en_999,999,en,"Passion fruit","It is a small round fruit with a thick outer skin and a juicy jelly-like pulp with several seeds inside, and is character"',

            // test.csv
            "diff --git a/test.csv b/test.csv",
            "--- a/test.csv",
            "+++ b/test.csv",
            "-e,1,202401",
            "-e,1,202402",
        ];
    }
}
