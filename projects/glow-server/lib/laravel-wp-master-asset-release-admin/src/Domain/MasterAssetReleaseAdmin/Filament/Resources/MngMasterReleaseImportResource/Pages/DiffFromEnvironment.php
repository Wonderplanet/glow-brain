<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages;

use CzProject\GitPhp\GitException;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GitOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\GitCommitService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\ImportDataDiffService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MasterDataImportService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;

/**
 * マスターデータインポートv2管理ツール 環境間インポート差分確認ページクラス
 * インポートページからのみ遷移可能
 * インポート画面での操作をもとに差分を表示する
 */
class DiffFromEnvironment extends CreateRecord
{
    use InteractsWithFormActions;

    protected static string $resource = MngMasterReleaseImportResource::class;

    // スプレッドシート取り込み差分表示画面と共通のテンプレート
    protected static string $view = 'view-master-asset-admin::filament.pages.mng-master-releases.diff';

    protected static bool $shouldRegisterNavigation = false; // 直接は遷移できないようにする
    protected static ?string $title = 'マスターデータ配信管理ダッシュボード 差分比較';

    public Collection|null $mngMasterReleasesByApplyOrPending = null;
    public Collection|null $applyMngMasterReleases = null;
    public array $internalEntities = [];
    public array $serverDbHashMap = [];
    public array $confirmDetails = [];
    public Collection $fromEnvironmentMasterReleaseList;
    public array $fromEnvironmentMngMasterReleaseVersion = [];
    public bool $isFirstImport = false;
    public array $releaseDiffData = [];

    private MngMasterReleaseService $mngMasterReleaseService;
    private GitCommitService $gitCommitService;
    private GitOperator $gitOperator;

    // GETで送信されてくるパラメータ
    public ?string $fromEnvironment = null;
    public ?string $importId = null;
    public bool $ifForceImportAll = false;

    /**
     * GETパラメータを受け取るLivewireの設定
     *
     * @var array
     */
    protected array $queryString = [
        'fromEnvironment',
        'ifForceImportAll',
    ];

    public function __construct()
    {
        $this->mngMasterReleaseService = app()->make(MngMasterReleaseService::class);
        $this->gitCommitService = app()->make(GitCommitService::class);
        $this->gitOperator = new GitOperator(config('wp_master_asset_release_admin.repositoryUrl'), config('wp_master_asset_release_admin.spreadSheetCsvDir'));
        $this->fromEnvironmentMasterReleaseList = collect();
    }

    /**
     * 画面遷移時に初回だけ起動
     *
     * @return void
     * @throws \CzProject\GitPhp\GitException
     */
    public function mount(): void
    {
        try {
            // `masterdata` のgitをクローンしてなければクローンする
            $this->gitCommitService->initialize();

            // 自環境の最新のマスターリリース情報と配信中/配信準備中のマスターリリース情報を取得
            $this->mngMasterReleasesByApplyOrPending = $this->mngMasterReleaseService
                ->getMngMasterReleasesByApplyOrPending();
            $this->applyMngMasterReleases = $this->mngMasterReleaseService->getMngMasterReleasesByApply();
            
            // インポート元環境のリリースバージョン情報を取得してセット
            $this->setFromEnvironmentData();

            // マスター差分データをセット
            $this->setMasterDiffData();
    
            // リリース情報の差分データをセット
            $this->setReleaseDiffData();
    
            // 確認モーダル表示データをセット
            $this->setConfirmDetails();
        } catch (GitException $e) {
            $this->gitOperator->sendGitErrorLogAndNotification($e);
            redirect()->to('/admin/mng-master-release-imports');
        }
    }

    /**
     * パンくずリストを設定
     *
     * @return string[]
     */
    public function getBreadcrumbs(): array
    {
        return [route(ImportMngMasterRelease::getRouteName()) => 'マスターデータ環境間インポート'];
    }

    /**
     * 使用するデータをテンプレートに渡す
     *
     * @return array
     */
    protected function getViewData(): array
    {
        // 表示用に配信中のリリースキー文言を生成
        $applyReleaseKeyStr = $this->getApplyReleaseKeys() === []
            ? 'なし'
            : implode(',', $this->getApplyReleaseKeys());

        return [
            'releaseDiffData' => $this->releaseDiffData,
            'applyReleaseKeyStr' => $applyReleaseKeyStr,
            'entities' => $this->internalEntities,
            'isFirstImport' => $this->isFirstImport,
            'isActiveExecButton' => true, // インポートボタンが常に押せる状態にしているが、条件があればボタン非活性ができるようにフラグを用意
        ];
    }

    /**
     * インポート元環境からデータを取得してパラメータをセット
     *
     * @return void
     * @throws \Exception
     */
    private function setFromEnvironmentData(): void
    {
        // 自環境の配信中/配信準備中のリリースキーをもとに、チェックした取り込み環境からリリース情報を取得
        $releaseKeys = $this->mngMasterReleasesByApplyOrPending->pluck('release_key')->toArray();
        $fromEnvironmentMasterReleaseList = $this->mngMasterReleaseService
            ->getEffectiveMasterReleaseListFromEnvironment($this->fromEnvironment, $releaseKeys);

        $fromEnvironment = $fromEnvironmentMasterReleaseList
            ->mapWithKeys(function ($masterRelease) {
                $versions = $masterRelease['mng_master_release_versions'];
                return [$masterRelease['release_key'] => [
                    'mng_master_release' => [
                        'release_key' => $masterRelease['release_key'],
                        'description' => $masterRelease['description'],
                        'enabled' => $masterRelease['enabled'],
                        'is_latest_version' => $masterRelease['is_latest_version'],
                    ],
                    'mng_master_release_versions' => $versions,
                ]];
            })
            // release_keyが一番最新の情報を取得
            ->sortKeysDesc()
            ->first();

        if (is_null($fromEnvironment)) {
            throw new \Exception("error: fromEnvironmentGitRevision is null fromEnvironment:{$this->fromEnvironment}");
        }

        $this->fromEnvironmentMasterReleaseList = $fromEnvironmentMasterReleaseList;
        $this->fromEnvironmentMngMasterReleaseVersion = $fromEnvironment['mng_master_release_versions'];
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
                    // リリース済みのrelease_keyと一致したら配信中とする
                    $status = '配信中';
                    $statusColor = 'color: deeppink';
                }
                // mng_master_releases.target_release_version_idと紐づくmng_master_release_versionsのデータを取得
                $mngMasterReleaseVersion = $release->mngMasterReleaseVersion;

                // 変更前のdata_hash取得
                $dataHash = is_null($mngMasterReleaseVersion)
                    ? '(設定なし)' // 配信準備中の場合は紐づくデータがないので空文字
                    : $mngMasterReleaseVersion->data_hash;
                // 変更前のgit_revision取得
                $gitRevision = is_null($mngMasterReleaseVersion)
                    ? '(設定なし)' // 配信準備中の場合は紐づくデータがないので(設定なし)とする
                    : $mngMasterReleaseVersion->git_revision;

                // 変更後のdata_hashとgit_revisionを取得
                $fromEnvironmentMngMasterReleaseVersion = $this
                    ->getFromEnvironmentMngMasterReleaseVersion($release->release_key);
                if (empty($fromEnvironmentMngMasterReleaseVersion)) {
                    // 一致するリリースキーのデータがインポート元環境にない場合、変更なしと表示する
                    $fromEnvironmentMngMasterReleaseVersion = [
                        'git_revision' => '(変更なし)',
                        'data_hash' => '(変更なし)',
                    ];
                }

                return [
                    'releaseKey' => $release->release_key,
                    'status' => $status,
                    'statusColor' => $statusColor,
                    'oldDataHash' => $dataHash,
                    'newDataHash' => $fromEnvironmentMngMasterReleaseVersion['data_hash'],
                    'description' => $release->description,
                    'oldGitRevision' => $gitRevision,
                    'newGitRevision' => $fromEnvironmentMngMasterReleaseVersion['git_revision'],
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
            // 自環境の最新リリースキーのgit_revisionと指定環境の最新リリースキーのgit_revisionで差分を取得する
            // git_revisionが取得できない場合は差分生成はしない
            $toGitRevision = $this->getGitRevisionFromSelfEnv();

            if (!blank($toGitRevision)) {
                // msterdata_csvのブランチの状態をリセット
                $this->gitOperator->resetToHead();
                // 自環境のブランチにチェックアウト
                $this->gitOperator->checkout($this->gitOperator->getGitBranch());
                // インポート元環境のコミットハッシュでgit checkoutを実行(ブランチ名はimportId)
                // ImportDataDiffServiceで、取り込み対象のmasterdata_csvのヘッダー(カラム)情報を取得するためチェックアウトしている
                $this->gitOperator->checkoutFromCommitHash($this->fromEnvironmentMngMasterReleaseVersion['git_revision'], $this->importId);

                // 差分データを取得
                $rawDiff = $this->getGitDiffData($toGitRevision, $this->fromEnvironmentMngMasterReleaseVersion['git_revision']);
                $diffs = (new ImportDataDiffService())->checkDiff($rawDiff);

                // 元のブランチに戻る
                $this->gitOperator->checkout($this->gitOperator->getGitBranch());
                // importIdのブランチを削除
                $this->gitOperator->deleteBranch($this->importId);

                // テーブル表示用にソート
                $importDataDiffEntityList = MasterDataImportUtility::sortDiffData($diffs);
            }
        } catch (GitException $e) {
            $this->gitOperator->sendGitErrorLogAndNotification($e);
            redirect()->to('/admin/mng-master-release-imports');
        } catch (\Exception $e) {
            // ログの残してインポート元環境選択画面にリダイレクト
            Log::error('', [$e]);
            Notification::make()
                ->title('差分表示生成でエラーが発生しました')
                ->body('管理者にお問い合わせください')
                ->color('danger')
                ->persistent()
                ->send();

            redirect()->to('/admin/mng-master-release-imports');
        }

        // MEMO livewireの仕様で独自クラスの配列をpublicなクラス変数に持たせるとエラーになるという
        //  現象が発生したのでクラスのパラメータを配列化して返しています
        $this->internalEntities = $importDataDiffEntityList;
    }

    /**
     * 自環境のmasterdata_csvの先頭のgitRevision(コミットハッシュ)を取得する
     *
     * @return string
     */
    private function getGitRevisionFromSelfEnv(): string
    {
        $gitRevision = $this->mngMasterReleasesByApplyOrPending
            ->mapWithKeys(function (MngMasterRelease $row) {
                // キーにリリースキー、値にgitRevisionを持つMapを生成
                // versionがない場合は値をnullにする
                /** @var MngMasterReleaseVersion $version */
                $version = $row->mngMasterReleaseVersion;
                return [
                    $row->release_key => $version?->git_revision,
                ];
            })
            // git_revisionがnullでないものだけでフィルタリング
            ->filter(fn($rowValue) => !is_null($rowValue))
            // release_keyが一番古いものを取得
            // 配信中のデータがあればそれが一番古いので配信中データが優先される
            // 配信中のデータがなければ配信準備中の古いデータでgit_revisionがあるデータを取得する
            ->sortKeys()
            ->first();

        if (is_null($gitRevision) || $this->ifForceImportAll) {
            // この時点で空の場合は未インポートなので、インポート元環境のgit_revisionのデータを全てインポートすることになる
            // この差分を表示すると全マスターの差分が全て新規データとして表示することになり、差分チェックをするのも現実的ではない
            // この場合は一旦仮で、差分確認は行わず対象revisionのデータを全てインポートするメッセージを表示するようにしている
            $this->isFirstImport = true;
            $gitRevision = '';
        }

        return $gitRevision;
    }

    /**
     * git diff を実行してマスターデータの差分情報を取得する
     *
     * @param string $toHash
     * @param string $fromHash
     * @return array
     * @throws \CzProject\GitPhp\GitException
     */
    private function getGitDiffData(string $toHash, string $fromHash): array
    {
        return $this->gitOperator->diffFromHash($toHash, $fromHash);
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

                // 変更後のdata_hashとgit_revisionデータを取得
                $fromEnvironmentMngMasterReleaseVersion = $this->getFromEnvironmentMngMasterReleaseVersion($row->release_key);
                if (empty($fromEnvironmentMngMasterReleaseVersion)) {
                    // 一致するリリースキーのデータがインポート元環境にない場合、変更なしと表示する
                    $fromEnvironmentMngMasterReleaseVersion = [
                        'git_revision' => '(変更なし)',
                        'data_hash' => '(変更なし)',
                    ];
                }

                return [
                    'isEnabled' => (bool) $row->enabled,
                    'title' => "{$row->release_key}:{$row->description}",
                    'modifyCount' => $modifyCount,
                    'deleteCount' => $deleteCount,
                    'newCount' => $newCount,
                    'beforeGitRevision' => $mngMasterReleaseVersion?->git_revision,
                    'afterGitRevision' => $fromEnvironmentMngMasterReleaseVersion['git_revision'],
                    'beforeDataHash' => $mngMasterReleaseVersion?->data_hash,
                    'afterDataHash' => $fromEnvironmentMngMasterReleaseVersion['data_hash'],
                ];
            })->toArray();
    }

    /**
     * リリース情報差分表示用にインポート元環境のリリース情報を取得する
     *
     * @param int $releaseKey
     * @return array
     */
    private function getFromEnvironmentMngMasterReleaseVersion(int $releaseKey): array
    {
        $fromEnvironmentMasterRelease = $this->fromEnvironmentMasterReleaseList
            // 指定したrelease_keyと一致するものを取得する
            ->first(fn ($envMasterRelease) => $envMasterRelease['release_key'] === $releaseKey);

        if (is_null($fromEnvironmentMasterRelease)) {
            return [];
        }
        return $fromEnvironmentMasterRelease['mng_master_release_versions'];
    }

    /**
     * 環境間インポート実行
     *
     * @return void
     * @throws \Throwable
     */
    public function import(): void
    {
        ini_set('max_execution_time', config('wp_master_asset_release_admin.master_data_import_timeout_seconds'));
        $admUser = auth()->user();
        try {
            $fromEnvironmentMasterReleaseVersionMap = $this->mngMasterReleasesByApplyOrPending
                // 自環境に設定したrelease_keyとインポート元環境のrelease_keyが一致するものでフィルタリング
                ->filter(fn(MngMasterRelease $row) => !is_null($this->fromEnvironmentMasterReleaseList->first(fn ($env) => $env['release_key'] === $row->release_key)))
                // リリースキーをキー、インポート対象のバージョン情報を値に保つマップを作成
                ->mapWithKeys(function (MngMasterRelease $row) {
                    $envRelease = $this->fromEnvironmentMasterReleaseList->first(fn ($env) => $env['release_key'] === $row->release_key);
                    return [
                        $row->release_key => $envRelease['mng_master_release_versions'],
                    ];
                })->toArray();

            /** @var MasterDataImportService $masterDataImportService */
            $masterDataImportService = app()->make(MasterDataImportService::class);
            $masterDataImportService->executeImportFromEnvironment(
                $this->importId,
                $this->fromEnvironment,
                $fromEnvironmentMasterReleaseVersionMap,
                $admUser?->id
            );
            Notification::make()
                ->title('マスターデータインポートが完了しました。')
                ->success()
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('マスターデータインポートに失敗しました。')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->persistent()
                ->send();
            Log::error('', [$e]);
        }

        redirect()->to('/admin/mng-master-releases');
    }
}
