<?php
namespace App\Filament\Actions;

use App\Services\MasterData\GitCommitService;
use App\Services\MasterData\MasterDataImportService;
use App\Services\MasterData\OprMasterReleaseControlAccessService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class GitApplyAction extends Action
{
    public function getActions()
    {
        // ページが開かれたタイミングでgitをクローンしてなければクローンする
        $service = new GitCommitService();
        $service->initialize();

        $branches = $service->getBranches();
        return [
            Action::make('apply')
                // ->label('ブランチかハッシュを指定して取り込む')
                ->label('ブランチを指定して取り込む')
                ->form([
                    Select::make('branch')
                        ->label('ブランチ名')
                        ->options($branches),
                    // TextInput::make('hash')->label('コミットハッシュ'),
                ])
                ->action(Closure::fromCallable(function ($data) use ($branches) {
                    $this->apply($data, $branches);
                    redirect()->to('/admin/git-apply')->with('flash_message', '適用が完了しました。');
                })),
        ];
    }

    public function getCurrentHash(): string
    {
        $service = new OprMasterReleaseControlAccessService();
        $opr = $service->selectActiveOprMasterReleaseControl();
        $current = empty($opr) ? "" : $opr->getHash();

        return $current;
    }

    public function apply($data, $branches): void
    {
        ini_set('max_execution_time', 300); // 5分

        // NOTE: hashでのワークフローは一回複雑になるので塞いでおく
        if (isset($data['hash'])) {
            // $service = new MasterDataImportService();
            // $service->executeApply($data['hash'], false);
            throw new \Exception('ブランチ名を指定してください。');
        } else if (isset($data['branch'])) {
            $service = new MasterDataImportService();
            $service->executeApply($branches[$data['branch']], true);
        } else {
            // throw new \Exception('ブランチ名かコミットハッシュを指定してください。');
            throw new \Exception('ブランチ名を指定してください。');
        }
    }

}
