<?php

namespace App\Filament\Actions;

use App\Services\MasterData\GitCommitService;
use App\Services\MasterData\GitDiffService;
use App\Services\MasterData\MasterDataImportService;
use App\Services\MasterData\MasterDataImportStatusService;
use App\Services\MasterData\OprMasterReleaseControlAccessService;
use App\Services\MasterData\SpreadSheetFetchService;
use Closure;
use Filament\Actions\Action;

class MasterDataDiffAction extends Action
{
    public function getActions()
    {
        return [
            Action::make('import')
                ->label('データ取り込み')
                ->modalSubmitAction()
                ->action(Closure::fromCallable([$this, 'import']))
                ->failureNotification(fn ($e) => 'データ取り込みに失敗しました。'),
        ];
    }


    public function checkDiff(array $diff): array
    {
        // 取り込み操作中ならエラー
        $masterDataImportStatusService = new MasterDataImportStatusService();
        if ($masterDataImportStatusService->isImporting()) {
            throw new \Exception('既にインポートが実行中です。');
        }

        // DB適用中のスプレッドシートCSVファイルとのDiffを取るためハッシュ値を取得して差分を表示する
        $oprMasterReleaseControlAccessService = new OprMasterReleaseControlAccessService();
        $currentHash = $oprMasterReleaseControlAccessService->selectActiveOprMasterReleaseControl()->getGitRevision();

        // マスターデータGitをブランチの先頭に合わせてからスプシを取得する
        $gitCommitService = new GitCommitService();
        $gitCommitService->resetSpreadSheetCsv();
        $spreadSheetService = new SpreadSheetFetchService();
        $spreadSheetService->getAndWriteSpreadSheetCsv($diff);

        $masterDataDiffService = new GitDiffService();
        return $masterDataDiffService->checkDiff($currentHash);
    }

    public function import()
    {
        $masterDataImportService = new MasterDataImportService();
        $masterDataImportService->executeImport();

        redirect()->to('/admin/import')->with('flash_message', 'データ取り込みが完了しました。');
    }
}
