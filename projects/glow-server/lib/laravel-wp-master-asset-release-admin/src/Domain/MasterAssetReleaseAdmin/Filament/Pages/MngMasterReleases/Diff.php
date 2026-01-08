<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases;

use App\Exceptions\Handler;
use App\Services\OcarinaService;
use CzProject\GitPhp\GitException;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\ImportDataDiffEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetRequestEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GitOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\DatabaseCsvGenerateService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\GitCommitService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\ImportDataDiffService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MasterDataImportService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SerializeDataGenerateService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SheetSchemaExportService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SpreadSheetFetchService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;

/**
 * マスターデータインポートv2管理ツール 差分確認ページクラス
 * インポートページからのみ遷移可能
 * インポート画面での操作をもとに差分を表示する
 */
class Diff extends Page
{
    use InteractsWithFormActions;

    protected static string $view = 'view-master-asset-admin::filament.pages.mng-master-releases.diff';
    // importIdをワイルドカードとしたURLを設定
    protected static ?string $slug = 'mng-master-release-versions/import-from-spread-sheet/spread_sheet_import/{importId}/diff';

    protected static bool $shouldRegisterNavigation = false; // 直接は遷移できないようにする
    protected static ?string $title = 'マスターデータインポート/スプレッドシート/差分比較';

    public string $importId = '';
    public MngMasterRelease|null $latestReleasedMngMasterRelease = null;
    public Collection|null $mngMasterReleasesByApplyOrPending = null;
    public Collection|null $applyMngMasterReleases = null;
    public array $internalEntities = [];
    public array $masterDataHashMap = [];
    public array $masterDataI18nHashMap = [];
    public array $operationDataHashMap = [];
    public array $operationDataI18nHashMap = [];
    public array $serverDbHashMap = [];
    public array $dataHashMap = [];
    public array $confirmDetails = [];
    public array $targetTitles = [];
    public array $releaseDiffData = [];

    private MngMasterReleaseService $mngMasterReleaseService;
    private GitCommitService $gitCommitService;
    private SpreadSheetFetchService $spreadSheetService;
    private DatabaseCsvGenerateService $databaseCsvGenerateService;
    private SerializeDataGenerateService $serializeDataGenerateService;
    private GitOperator $gitOperator;
    private OcarinaService $ocarinaService;
    private SheetSchemaExportService $sheetSchemaExportService;

    public function __construct()
    {
        $this->mngMasterReleaseService = app()->make(MngMasterReleaseService::class);
        $this->gitCommitService = app()->make(GitCommitService::class);
        $this->spreadSheetService = app()->make(SpreadSheetFetchService::class);
        $this->databaseCsvGenerateService = app()->make(DatabaseCsvGenerateService::class);
        $this->serializeDataGenerateService = app()->make(SerializeDataGenerateService::class);
        $this->ocarinaService = app()->make(OcarinaService::class);
        $this->gitOperator = new GitOperator(config('wp_master_asset_release_admin.repositoryUrl'), config('wp_master_asset_release_admin.spreadSheetCsvDir'));

        $this->sheetSchemaExportService = app()->make(SheetSchemaExportService::class);
    }

    /**
     * importIdを含めたURLを生成
     *
     * @param string $importId
     * @return string
     */
    public static function makeUrl(string $importId): string
    {
        return "import-from-spread-sheet/spread_sheet_import/{$importId}/diff";
    }

    /**
     * 画面遷移時に初回だけ起動
     * @param Request $request
     * @return void
     */
    public function mount(Request $request): void
    {
        // URLのimportIdを取得
        $this->importId = $request->route('importId');
        $this->latestReleasedMngMasterRelease = $this->mngMasterReleaseService->getLatestReleasedMngMasterRelease();
        $this->applyMngMasterReleases = $this->mngMasterReleaseService->getMngMasterReleasesByApply();

        // `masterdata` のgitをクローンしてなければクローンする
        $this->gitCommitService->initialize();

        // 配信中|配信準備中のmng_master_releaseとmng_master_release_versionsを取得
        $this->mngMasterReleasesByApplyOrPending = $this->mngMasterReleaseService->getMngMasterReleasesByApplyOrPending();

        // チェックしたシートとマスターデータの差分を生成して取得
        $this->setMasterDiffData();

        // リリース情報の差分データをセット
        $this->setReleaseDiffData();

        // 確認モーダル表示データをセット
        $this->setConfirmDetails();
    }

    /**
     * パンくずリストを設定
     *
     * @return string[]
     */
    public function getBreadcrumbs(): array
    {
        return [route(ImportFromSpreadSheet::getRouteName()) => 'マスターデータインポート/スプレッドシート'];
    }

    /**
     * 使用するデータをテンプレートに渡す
     *
     * @return array
     */
    protected function getViewData(): array
    {
        $entities = [];
        // チェックしたマスターシートの中で差分がなかった場合はinternalEntitiesにデータがないので
        // 差分がないことを表示するためにチェックしたマスター名をもとに表示データを生成する
        foreach ($this->targetTitles as $title) {
            $filteredEntity = array_values(array_filter($this->internalEntities, function (array $entity) use ($title) {
                // チェックしたマスターメイト一致する差分データを取得
                return $entity['sheetName'] === $title;
            }));
            if (empty($filteredEntity)) {
                // 差分がなかった場合は空のEntityを生成して取得する
                $emptyEntity = new ImportDataDiffEntity(
                    $title,
                    [],
                    [],
                    [],
                    [],
                    [],
                    [],
                    [],
                );
                $filteredEntity = $emptyEntity->toArray();
            } else {
                // 二次元配列になっているので１次元配列に直す
                $filteredEntity = reset($filteredEntity);
            }
            $entities[$title] = $filteredEntity;
        }

        // 表示用に配信中のリリースキー文言を生成
        $applyReleaseKeyStr = $this->getApplyReleaseKeys() === []
            ? 'なし'
            : implode(',', $this->getApplyReleaseKeys());

        return [
            'releaseDiffData' => $this->releaseDiffData,
            'applyReleaseKeyStr' => $applyReleaseKeyStr,
            'entities' => $entities,
            'isActiveExecButton' => true, // インポートボタンが常に押せる状態にしているが、条件があればボタン非活性ができるようにフラグを用意
        ];
    }

    /**
     * 配信中の最新のrelease_keyを取得(nullの場合は0)
     *
     * @return int
     */
    private function getLatestApplyReleaseKey(): int
    {
        return $this->latestReleasedMngMasterRelease?->release_key ?? 0;
    }

    /**
     * 配信中のrelease_keyを取得
     *
     * @return array
     */
    private function getApplyReleaseKeys(): array
    {
        return is_null($this->applyMngMasterReleases)
            ? []
            : $this->applyMngMasterReleases->pluck('release_key')->toArray();
    }

    /**
     * 上部に表示する配信中/準備中のテーブル一覧表示用
     *
     * @return void
     */
    private function setReleaseDiffData(): void
    {
        $this->releaseDiffData = $this->mngMasterReleasesByApplyOrPending
            // 一覧表示用に加工
            ->map(function (MngMasterRelease $release) {
                $status = '配信準備中';
                $statusColor = 'color: darkolivegreen';
                if (in_array($release->release_key, $this->getApplyReleaseKeys(), true)) {
                    $status = '配信中';
                    $statusColor = 'color: deeppink';
                }
                // mng_master_releases.target_release_version_idと紐づくmng_master_release_versionsのデータを取得
                $mngMasterReleaseVersion = $release->mngMasterReleaseVersion;
                // data_hash取得
                $dataHash = is_null($mngMasterReleaseVersion)
                    ? '(設定なし)' // 配信準備中の場合は紐づくデータがないので(設定なし)とする
                    : $mngMasterReleaseVersion->data_hash;
                // git_revision取得
                $gitRevision = is_null($mngMasterReleaseVersion)
                    ? '(設定なし)' // 配信準備中の場合は紐づくデータがないので(設定なし)とする
                    : $mngMasterReleaseVersion->git_revision;

                return [
                    'releaseKey' => $release->release_key,
                    'status' => $status,
                    'statusColor' => $statusColor,
                    'oldDataHash' => $dataHash,
                    'newDataHash' => '新規データハッシュ',
                    'description' => $release->description,
                    'oldGitRevision' => $gitRevision,
                    'newGitRevision' => '新規リビジョン',
                ];
            })
            ->toArray();
    }

    /**
     * マスターデータの差分の詳細を取得する
     *
     * @return void
     */
    private function setMasterDiffData(): void
    {
        if (!empty($this->internalEntities)) {
            return;
        }

        $importDataDiffEntityList = [];
        try {
            $ids = [];
            foreach (\Illuminate\Support\Facades\Request::query('id', []) as $id) {
                $pos = strrpos($id, '_');
                if ($pos === false) continue;
                $ids[] = new SpreadSheetRequestEntity(
                    substr($id, 0, $pos),
                    '', // ここでファイル名を渡す必要はないので空文字を指定
                    substr($id, $pos + 1),
                );
            }

            // マスターデータGitをブランチの先頭に合わせてからスプシを取得する
            $this->gitCommitService->resetSpreadSheetCsv();

            // シートの更新内容をgit管理しているmasterdataのcsvに書き出して差分を作り、チェックしたマスターシート名を取得する
            // 現在登録しているリリースキーの最大リリースキーより大きいリリースキーは弾くため、最大リリースキーを取得する
            $maxReleaseKeyMasterRelease = $this->mngMasterReleasesByApplyOrPending
                ->sortByDesc('release_key')
                ->first();
            $this->targetTitles = $this->spreadSheetService->getAndWriteSpreadSheetCsv($maxReleaseKeyMasterRelease->release_key, $ids);

            // スキーマCSV出力（APCu キャッシュから生データを取得）
            $this->sheetSchemaExportService->exportSchemaFiles($ids);

            // 「git add -N .」を実行してUntracked filesもdiffの対象にする
            $this->gitOperator->addUntrackedFiles();

            // 差分データを取得
            $rawDiff = $this->getGitDiffData();
            $diffs = (new ImportDataDiffService())->checkDiff($rawDiff);

            // テーブル表示用にソート
            $importDataDiffEntityList = MasterDataImportUtility::sortDiffData($diffs);

        } catch (GitException $e) {
            $this->gitOperator->sendGitErrorLogAndNotification($e);
            redirect()->to('/admin/mng-master-release-versions/import-from-spread-sheet');
        } catch (\Exception $e) {
            // ログの残してスプレッドシート一覧にリダイレクト
            Log::error('', [$e]);
            Handler::sendPostSlack($e);
            redirect()->to('/admin/mng-master-release-versions/import-from-spread-sheet');
        }

        // MEMO livewireの仕様で独自クラスの配列をpublicなクラス変数に持たせるとエラーになるという
        //  現象が発生したのでクラスのパラメータを配列化して返しています
        $this->internalEntities = $importDataDiffEntityList;
    }

    /**
     * git diff を実行してマスターデータの差分情報を取得する
     *
     * @return array
     * @throws \CzProject\GitPhp\GitException
     */
    private function getGitDiffData(): array
    {
        // バリデーション用のディレクトリは除外した上でdiffを取得する
        $validationDirName = config('wp_master_asset_release_admin.validationDirName');
        $options = ['--', ":(exclude){$validationDirName}/*"];

        return $this->gitOperator->diff(null, $options);
    }

    /**
     * 確認モーダルで表示する詳細情報をセット
     *
     * @return void
     * @throws \Exception
     */
    private function setConfirmDetails(): void
    {
        if (!empty($this->confirmDetails)) {
            // 一度生成されていれば2回目以降は実行させない
            return;
        }

        // 確認モーダル表示のため必要なデータ生成を実行する
        $latestApplyReleaseKey = $this->getLatestApplyReleaseKey();
        $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity($latestApplyReleaseKey, $this->mngMasterReleasesByApplyOrPending);

        // データベース用のCSVを生成、ファイルのハッシュ値を取得
        $this->serverDbHashMap = $this->databaseCsvGenerateService->generateDatabaseCsv($mngMasterReleaseKeyEntity, $this->importId);

        // クライアント用のJsonをデータベース用CSVから生成
        [
            $this->masterDataHashMap,
            $this->masterDataI18nHashMap,
            $this->operationDataHashMap,
            $this->operationDataI18nHashMap,
        ] = $this->serializeDataGenerateService->generate($this->importId, $mngMasterReleaseKeyEntity);

        // 各release_keyごとのdata_hashのマップを取得
        $releaseKeys = $this->mngMasterReleasesByApplyOrPending
            ->map(fn (MngMasterRelease $row) => $row->release_key)->toArray();
        $this->dataHashMap = MasterDataImportUtility::generateDataHashMapByHashMap(
            $releaseKeys,
            $this->masterDataHashMap,
            $this->masterDataI18nHashMap,
            $this->operationDataHashMap,
            $this->operationDataI18nHashMap,
            $this->serverDbHashMap
        );

        // 各マスターデータの変更、新規追加、削除件数を累計
        // リリースキーごとに分類して累計する
        [
            $modifyRowCountMapFromAllTable,
            $newRowCountMapFromAllTable,
            $deleteRowCountMapFromAllTable,
        ] = MasterDataImportUtility::makeRowCountMapFromAllTables($this->internalEntities);

        // 確認モーダルに表示するパラメータを設定
        $this->confirmDetails = $this->mngMasterReleasesByApplyOrPending
            ->map(function (MngMasterRelease $row) use ($modifyRowCountMapFromAllTable, $newRowCountMapFromAllTable, $deleteRowCountMapFromAllTable) {
                /** @var MngMasterReleaseVersion $mngMasterReleaseVersion */
                $mngMasterReleaseVersion = $row->mngMasterReleaseVersion;

                $modifyCount = 0;
                $newCount = 0;
                $deleteCount = 0;
                // 全マスターテーブルから集計済みのマップをもとに、対象のリリースキーのデータなら加算していく
                // 例：$row->release_key=202401011の場合
                //  データに含めたいのは$releaseKey=202401011まで
                //  $releaseKey=202401012以上のデータは含めない
                foreach ($modifyRowCountMapFromAllTable as $releaseKey => $rowCount) {
                    if ($releaseKey <= $row->release_key) {
                        $modifyCount += $rowCount;
                    }
                }

                foreach ($newRowCountMapFromAllTable as $releaseKey => $rowCount) {
                    if ($releaseKey <= $row->release_key) {
                        $newCount += $rowCount;
                    }
                }

                foreach ($deleteRowCountMapFromAllTable as $releaseKey => $rowCount) {
                    if ($releaseKey <= $row->release_key) {
                        $deleteCount += $rowCount;
                    }
                }

                return [
                    'isEnabled' => (bool) $row->enabled,
                    'title' => "{$row->release_key}:{$row->description}",
                    'modifyCount' => $modifyCount,
                    'deleteCount' => $deleteCount,
                    'newCount' => $newCount,
                    'beforeGitRevision' => $mngMasterReleaseVersion?->git_revision,
                    'afterGitRevision' => '新規リビジョン', // スプレッドシートからの取得は常に `新規リビジョン` となる
                    'beforeDataHash' => $mngMasterReleaseVersion?->data_hash,
                    'afterDataHash' => $this->dataHashMap[$row->release_key],
                ];
            })->toArray();
    }

    /**
     * インポート実行
     *
     * @return void
     * @throws \Throwable
     */
    public function import(): void
    {
        ini_set('max_execution_time', config('wp_master_asset_release_admin.master_data_import_timeout_seconds'));
        $admUser = auth()->user();
        try {
            // MEMO Livewireの仕様で、特定のクラスやオブジェクトは直接プロパティに保持することができない
            // その為、MngMasterReleaseKeyEntity作成に必要なパラメータを渡して都度作成している
            $latestApplyReleaseKey = $this->getLatestApplyReleaseKey();
            $mngMasterReleaseKeyEntity = new MngMasterReleaseKeyEntity($latestApplyReleaseKey, $this->mngMasterReleasesByApplyOrPending);

            /** @var MasterDataImportService $masterDataImportService */
            $masterDataImportService = app()->make(MasterDataImportService::class);
            $masterDataImportService->executeImport(
                $this->importId,
                $mngMasterReleaseKeyEntity,
                $this->masterDataHashMap,
                $this->masterDataI18nHashMap,
                $this->operationDataHashMap,
                $this->operationDataI18nHashMap,
                $this->serverDbHashMap,
                $this->dataHashMap,
                $admUser?->id
            );

            // 整合性チェックを実行
            /** @var MasterDataDBOperator $masterDataDBOperator */
            $masterDataDBOperator = app()->make(MasterDataDBOperator::class);
            $masterDbNameParameter = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();
            $dbName = $masterDataDBOperator->getMasterDbName($masterDbNameParameter['releaseKey'], $this->serverDbHashMap);
            $this->ocarinaService->executeIntegrityCheck($dbName, $admUser);

            Notification::make()
                ->title('マスターデータインポートが完了しました。')
                ->success()
                ->send();
        } catch (GitException $e) {
            Handler::sendPostSlack($e);
            $this->gitOperator->sendGitErrorLogAndNotification($e);
            redirect()->to('/admin/mng-master-releases');
        } catch (\Exception $e) {
            Handler::sendPostSlack($e);
            Notification::make()
                ->title('マスターデータインポートに失敗しました。')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
        }

        redirect()->to('/admin/mng-master-releases');
    }
}
