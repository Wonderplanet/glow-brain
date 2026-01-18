<?php
namespace App\Filament\Actions;

use App\Entities\MasterData\SpreadSheetTableListEntity;
use App\Operators\GoogleSpreadSheetOperator;
use App\Services\ConfigGetService;
use App\Services\MasterData\GitCommitService;
use App\Services\MasterData\OprMasterReleaseControlAccessService;
use Filament\Actions\Action;

class ImportAction extends Action
{
    public function getActions()
    {
        // ページが開かれたタイミングでgitをクローンしてなければクローンする
        $service = new GitCommitService();
        $service->initialize();

        return []; // blade側で独自にボタンを追加するので空にする
    }

    /**
     * @return array<SpreadSheetTableListEntity>
     */
    public function getSpreadSheetList(): array
    {
        /** @var ConfigGetService $configGetService */
        $configGetService = app(ConfigGetService::class);
        $sheetListSheetId = $configGetService->getGoogleSpreadSheetListSheetId();
        $credentialPath = $configGetService->getGoogleCredentialPath();

        $operator = new GoogleSpreadSheetOperator($credentialPath);

        // TODO: リストという指定をなくすようリファクタ
        $listSheetValues = $operator->getSheetValues($sheetListSheetId, 'リスト') ?? [];

        $columns = $listSheetValues[0] ?? [];
        $data = array_slice($listSheetValues, 1);

        $values = [];
        foreach ($data as $row) {
            $values[] = array_combine($columns, $row);
        }

        $tableListEntities = [];
        foreach ($values as $value) {
            $tableListEntities[] = new SpreadSheetTableListEntity($value);
        }

        return $tableListEntities;
    }

    public function getCurrentHash(): string
    {
        $service = new OprMasterReleaseControlAccessService();
        $opr = $service->selectActiveOprMasterReleaseControl();
        $current = empty($opr) ? "" : $opr->getGitRevision();

        return $current;
    }
}
