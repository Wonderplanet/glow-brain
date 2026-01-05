<?php

declare(strict_types=1);

namespace Filament\Pages;

use Filament\Notifications\Notification;
use Google\Service\Sheets\Sheet;
use Google\Service\Sheets\SheetProperties;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases\ImportFromSpreadSheet;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SpreadSheetFetchService;
use WonderPlanet\Tests\TestCase;

class ImportFromSpreadSheetTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @test
     */
    public function getTableData_マスタデータシート一覧データ生成チェック(): void
    {
        // Setup
        /** @var SheetProperties $properties */
        $properties = $this->app->make(SheetProperties::class);
        $properties->setSheetId(999);
        $properties->setTitle('MstTest');
        /** @var Sheet $sheet */
        $sheet = $this->app->make(Sheet::class);
        $sheet->setProperties($properties);
        $entity = new SpreadSheetCsvEntity('testId', 'MstTestName', $sheet, []);
        $sheets = [$entity];

        // 本処理を実行するとGoogleドライブから取得してしまうのでモックで作成
        $spreadSheetFetchServiceMock = \Mockery::mock(SpreadSheetFetchService::class);
        $spreadSheetFetchServiceMock->shouldReceive('getSpreadSheetList')->andReturn($sheets);
        $importFromSpreadSheet = $this->app->make(ImportFromSpreadSheet::class);
        $this->setPrivateProperty($importFromSpreadSheet, 'canCacheReset', false);
        $this->setPrivateProperty($importFromSpreadSheet, 'spreadSheetFetchService', $spreadSheetFetchServiceMock);

        // Exercise
        $actuals = $this->callMethod(
            $importFromSpreadSheet,
            'getTableData',
        );

        // Verify
        $this->assertEquals('testId_999', $actuals[0]['id']);
        $this->assertEquals('MstTestName', $actuals[0]['fileName']);
        $this->assertEquals('メモ欄表示は未実装', $actuals[0]['memo']);
        $this->assertEquals('MstTest', $actuals[0]['sheetName']);
        $this->assertEquals('https://docs.google.com/spreadsheets/d/testId/edit#gid=999', $actuals[0]['link']);
        $this->assertEquals('YYYY/MM/DD HH:ii:ss', $actuals[0]['lastUpdateAt']);
    }

    /**
     * @test
     * @dataProvider getTableDataErrorCheck
     */
    public function getTableData_エラー通知チェック($errorCode, $title, $body): void
    {
        // Setup
        // 本処理を実行するとGoogleドライブから取得してしまうのでモックで作成
        $spreadSheetFetchServiceMock = \Mockery::mock(SpreadSheetFetchService::class);
        $spreadSheetFetchServiceMock->shouldReceive('getSpreadSheetList')
            ->andThrow(\Exception::class, 'test', $errorCode);
        $importFromSpreadSheet = $this->app->make(ImportFromSpreadSheet::class);
        $this->setPrivateProperty($importFromSpreadSheet, 'canCacheReset', false);
        $this->setPrivateProperty($importFromSpreadSheet, 'spreadSheetFetchService', $spreadSheetFetchServiceMock);

        // Exercise
        $this->callMethod(
            $importFromSpreadSheet,
            'getTableData',
        );

        // Verify
        // 想定したエラー通知かチェック
        Notification::assertNotified(
            Notification::make()
                ->title($title)
                ->body($body)
                ->danger()
                ->color('danger')
                ->send()
        );
    }

    /**
     * @return array
     */
    private function getTableDataErrorCheck(): array
    {
        return [
            'エラーコード429' => [
                429,
                'マスターデータシート情報取得中にエラーが発生しました。',
                'エラーコード：429<br/>時間を置いて再度ブラウザにアクセスしてください。',
            ],
            'エラーコード503' => [
                503,
                'マスターデータシート情報取得中にエラーが発生しました。',
                'エラーコード：503<br/>時間を置いて再度ブラウザにアクセスしてください。',
            ],
            'その他のエラーコード' => [
                501,
                '不明なエラーです。',
                'エラーコード：501<br/>サーバー管理者にお問い合わせください。'
            ],
        ];
    }

    /**
     * @test
     */
    public function getRowspanDataByTableData_rowspan値生成チェック(): void
    {
        // Setup
        $tableData = [
            [
                'id' => 'testId_1',
                'fileName' => 'MstTestA',
                'memo' => 'メモ',
                'sheetName' => 'MstTestA',
                'link' => 'url',
                'lastUpdateAt' => 'YYYY/MM/DD HH:ii:ss',
            ],
            [
                'id' => 'testId_1',
                'fileName' => 'MstTestA',
                'memo' => 'メモ',
                'sheetName' => 'MstTestAA',
                'link' => 'url',
                'lastUpdateAt' => 'YYYY/MM/DD HH:ii:ss',
            ],
            [
                'id' => 'testId_1',
                'fileName' => 'MstTestB',
                'memo' => 'メモ',
                'sheetName' => 'MstTestB',
                'link' => 'url',
                'lastUpdateAt' => 'YYYY/MM/DD HH:ii:ss',
            ],
        ];
        $importFromSpreadSheet = $this->app->make(ImportFromSpreadSheet::class);

        // Exercise
        $actuals = $this->callMethod(
            $importFromSpreadSheet,
            'getRowspanDataByTableData',
            $tableData
        );

        // Verify
        // rowspanの指定値が一致するか
        $this->assertEquals(2, $actuals['MstTestA']);
        $this->assertEquals(1, $actuals['MstTestB']);
    }
}
