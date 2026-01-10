<?php

namespace App\Filament\Pages;

use App\Constants\MasterDataManagementDisplayOrder;
use App\Filament\Actions\ImportAction;
use Filament\Pages\Page;

class Import extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.import'; // カスタムページ
    protected static ?string $navigationGroup = 'マスター管理';
    protected static ?string $title = 'マスター投入';
    protected static ?string $slug = 'import';
    protected static ?int $navigationSort = MasterDataManagementDisplayOrder::IMPORT_DISPLAY_ORDER->value; // メニューの並び順

    protected ImportAction $action;

    private const NON_TARGET_SPREADSHEET_NAME = [
        '説明',
        '属性参照',
        '_ref_list',
        '_ref_name',
        '_ref_DebugLocalize',
        '_ref_releaseKey',
    ];

    // ナビゲーションパネルに表示しないようにする
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function __construct($id = null)
    {
        $this->action = new ImportAction('import');
    }

    protected function getViewData(): array
    {
        return [
            'title' => '設定',
            'gitBranch' => env('GIT_BRANCH'),
            'hash' => $this->action->getCurrentHash(),
            'tableData' => $this->getTableData(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->action->getActions();
    }

    private function getTableData(): array
    {
        $rows = [];
        $tableListEntities = $this->action->getSpreadSheetList();
        foreach ($tableListEntities as $tableListEntity) {
            $rows[] = [
                'id' => $tableListEntity->getFileId() . '_' . $tableListEntity->getSheetId(),
                'シート名' => $tableListEntity->getSheetName(),
                'リンク' => $tableListEntity->getUrl(),
            ];
        }

        return $rows;
    }
}
