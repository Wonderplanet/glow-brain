<?php

namespace App\Filament\Pages;

use App\Entities\MasterData\SpreadSheetRequestEntity;
use App\Filament\Actions\MasterDataDiffAction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\HtmlString;

class MasterDataDiff extends Page
{
    protected static string $view = 'filament.pages.diff'; // カスタムページ
    protected static bool $shouldRegisterNavigation = false; // 直接は遷移できないようにする
    protected static ?string $title = 'マスターデータ差分確認';
    protected static ?string $slug = 'master-data-diff';

    protected MasterDataDiffAction $action;

    public function __construct($id = null)
    {
        $this->action = new MasterDataDiffAction('master_data_diff');
    }

    protected function getHeaderActions(): array
    {
        return $this->action->getActions();
    }

    protected function getViewData(): array
    {
        // リクエストパラメータから対象のスプレッドシートのIDを取得
        $ids = [];
        foreach (Request::query('id', []) as $id) {
            $pos = strrpos($id, '_');
            if ($pos === false) continue;
            $ids[] = new SpreadSheetRequestEntity(substr($id, 0, $pos), substr($id, $pos + 1));
        }

        // データ取得しGit差分をチェック
        $entities = $this->action->checkDiff($ids);

        return [
            'header' => ['行数', new HtmlString('適用中<br />取り込みデータ')],
            'entities' => $entities,
        ];
    }
}
