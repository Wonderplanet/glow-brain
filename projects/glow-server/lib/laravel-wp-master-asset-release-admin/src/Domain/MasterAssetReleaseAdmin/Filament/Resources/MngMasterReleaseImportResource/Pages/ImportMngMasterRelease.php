<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

class ImportMngMasterRelease extends CreateRecord
{
    protected static string $resource = MngMasterReleaseImportResource::class;

    // 連続して作成ボタンを非表示
    protected static bool $canCreateAnother = false;

    protected static string $view = 'view-master-asset-admin::filament.resources.mng-master-release-import-resource.pages.import-mng-master-release';

    protected static ?string $title = 'マスターデータ配信管理ダッシュボード インポート';
    protected static ?string $breadcrumb = 'マスターデータインポート';

    // 指定したインポート元環境
    public ?string $fromEnvironment = null;

    // 自環境の配信中のマスターデータリリース情報
    public ?MngMasterRelease $mngMasterReleaseStatusApply = null;

    // 取り込み可能環境一覧
    public array $importableFromEnvironmentList = [];

    // 指定したインポート元環境のマスターデータリリース情報のコレクション
    public array $masterReleaseArrayFromEnvironment = [];

    // 自環境のマスターデータリリース情報のコレクション
    public Collection $mngMasterReleaseCollection;

    // インポート元環境のバージョンが最新でないものが含まれているか判別するフラグ
    public bool $hasNoLatestReleaseVersion = false;

    // インポート元環境からのデータ取得のレスポンスがエラーの場合に情報を保持
    public array $responseErrors = [];

    // バリデーションエラーメッセージ
    public string $validationErrorMessage = '';

    // 強制全データ取り込みフラグ
    public bool $isForceImportAll = false;

    /**
     * @var MngMasterReleaseService
     */
    private MngMasterReleaseService $service;

    public function __construct()
    {
        $this->service = app()->make(MngMasterReleaseService::class);
        $this->mngMasterReleaseCollection = collect();
    }

    /**
     * 画面遷移時に初回だけ起動
     *
     * @return void
     */
    public function mount(): void
    {
        // 自環境の配信中(最古)のマスターリリース情報と配信中/配信準備中のマスターリリース情報を取得
        $this->mngMasterReleaseStatusApply = $this->service->getOldestApplyMngMasterRelease();
        $this->mngMasterReleaseCollection = $this->service->getMngMasterReleasesByApplyOrPending();

        // 取り込み可能な環境一覧をセット
        $this->importableFromEnvironmentList = $this->service->getImportEnvironment();
    }

    /**
     * フォーム作成
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('fromEnvironment')
                    ->label('インポート元環境の選択')
                    ->required()
                    ->options($this->importableFromEnvironmentList)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->fromEnvironment = $this->importableFromEnvironmentList[$state];
                        $this->setTargetMasterReleases($this->fromEnvironment);
                    }),
                Forms\Components\Checkbox::make('forceImportAll')
                    ->label('差分確認しない')
                    ->default(false)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->isForceImportAll = $state;
                    }),
            ]);
    }

    /**
     * 使用するデータをテンプレートに渡す
     *
     * @return array
     */
    public function getViewData(): array
    {
        return [
            'fromEnvironment' => $this->fromEnvironment,
            'diffData' => $this->makeDiffData(),
            'hasNoLatestReleaseVersion' => $this->hasNoLatestReleaseVersion,
            'responseErrors' => $this->responseErrors,
            'validationErrorMessage' => $this->validationErrorMessage,
        ];
    }

    /**
     * リダイレクト設定
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('diff', [
            'importId' => now()->format('YmdHis'),
            'fromEnvironment' => $this->fromEnvironment,
            'ifForceImportAll' => $this->isForceImportAll,
        ]);
    }

    /**
     * 各ボタンのアクション
     *
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            Actions\Action::make('list')
                ->label('戻る')
                ->color('gray')
                ->url(self::getResource()::getUrl('index')),
            Actions\Action::make('confirm')
                ->label('次へ')
                ->action(fn () => $this->next())
                ->disabled(function () {
                    // 下記条件に当てはまる場合はボタンを無効化
                    if (empty($this->masterReleaseArrayFromEnvironment)) {
                        // インポート元環境の情報が空
                        return true;
                    }
                    if ($this->hasNoLatestReleaseVersion) {
                        // 取得情報内のいずれかが最新バージョンになってない
                        return true;
                    }
                    if (!empty($this->responseErrors)) {
                        // インポート元環境の情報取得apiでエラーになった
                        return true;
                    }
                    if ($this->validationErrorMessage !== '') {
                        // バリデーションエラーがある
                        return true;
                    }

                    // それ以外は有効化
                    return false;
                }),
        ];
    }

    /**
     * 確認画面遷移前のチェック
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function next(): void
    {
        // 入力値のバリデーション
        // バリデーションメッセージ設定
        $this->validate(
            messages: [
                'data.fromEnvironment.required' => 'インポート元環境の選択は必須です',
            ]
        )['data'];

        $this->redirect($this->getRedirectUrl());
    }

    /**
     * 取り込む対象環境のマスターデータリリース情報をセットする
     *
     * @param string|null $fromEnvironment // インポート元環境名
     *
     * @return void
     */
    private function setTargetMasterReleases(?string $fromEnvironment): void
    {
        ini_set('max_execution_time', 900);
        $this->responseErrors = []; // 初期化

        // インポート元の環境を指定していないときは表示させない
        if (is_null($fromEnvironment)) {
            $this->masterReleaseArrayFromEnvironment = [];
            return;
        }

        // 有効なリリース情報が見つからない場合は何も表示させない
        if ($this->mngMasterReleaseCollection->isEmpty()) {
            $this->masterReleaseArrayFromEnvironment = [];
            return;
        }

        // 自環境の配信中/配信準備中のリリースキーをもとに、選択した環境のリリース情報を取得
        $releaseKeys = $this->mngMasterReleaseCollection->pluck('release_key')->toArray();
        $effectiveMasterReleaseList = $this->service->getEffectiveMasterReleaseListFromEnvironment($fromEnvironment, $releaseKeys);
        $response = $effectiveMasterReleaseList->toArray();
        if (isset($response['error'])) {
            // apiの取得結果がerrorの場合は何も表示させない
            $this->masterReleaseArrayFromEnvironment = [];
            $this->responseErrors = [
                'error' => $response['error'],
                'status' => $response['status'],
            ];
            return;
        }

        // 自環境の配信中のリリースキー以上の値で、選択した環境のリリース情報をフィルタリング
        $releaseKey = $this->getReleaseKeyStatusApply();
        $filteredEffectiveMasterReleaseList = $effectiveMasterReleaseList
            ->filter(fn($masterRelease) => $masterRelease['release_key'] >= $releaseKey)->toArray();
        $this->masterReleaseArrayFromEnvironment = $filteredEffectiveMasterReleaseList;
    }

    /**
     * 自環境の配信中のリリースキーを取得する
     * 配信中のデータがなければ0を返す
     *
     * @return int
     */
    private function getReleaseKeyStatusApply(): int
    {
        return is_null($this->mngMasterReleaseStatusApply)
            ? 0
            : $this->mngMasterReleaseStatusApply->release_key;
    }

    /**
     * 自環境とインポート元環境のリリース情報差分表示データを生成する
     *
     * @return array
     */
    private function makeDiffData(): array
    {
        // 初期化
        $this->hasNoLatestReleaseVersion = false;
        $this->validationErrorMessage = '';

        if (empty($this->masterReleaseArrayFromEnvironment)) {
            // インポート元環境のマスターデータリリース情報がなければ空で返す
            return [];
        }

        $diffData = $this->mngMasterReleaseCollection
            ->sortByDesc(fn(MngMasterRelease $masterRelease) => $masterRelease->release_key)
            ->mapWithKeys(function (MngMasterRelease $masterRelease) {

            /** @var MngMasterReleaseVersion|null $versions */
            $versions = $masterRelease->mngMasterReleaseVersion;

            // 自環境と同じrelease_keyのデータを取得
            // なかった場合は画面上で一致するデータがないメッセージを表示する
            $filtered = array_filter($this->masterReleaseArrayFromEnvironment, function ($row) use ($masterRelease) {
                return $row['release_key'] === $masterRelease->release_key;
            });

            $filteredMasterRelease = [];
            if (!empty($filtered)) {
                // 対象リリースキーの情報を取得(対象リリースキーの情報は1件だけなので、先頭の要素を取得している)
                $shiftResult = array_shift($filtered);
                $filteredVersions = $shiftResult['mng_master_release_versions'];

                // enabled=trueなら配信中、falseなら配信準備中(文字色も変更)
                $status = $shiftResult['enabled'] ? '配信中' : '配信準備中';
                $style = $shiftResult['enabled'] ? 'color: deeppink' : 'color: darkolivegreen';
                if ($shiftResult['is_end_release']) {
                    // 配信終了フラグがtrueなら配信終了にして文字色を通常にする
                    $status = '配信終了';
                    $style = '';
                }

                $filteredMasterRelease = [
                    'release_key' => $shiftResult['release_key'],
                    'description' => $shiftResult['description'],
                    'status' => $status,
                    'style' => $style,
                    'git_revision' => $filteredVersions['git_revision'],
                    'is_latest_version' => $shiftResult['is_latest_version'],
                ];

                if ($shiftResult['is_latest_version'] === false) {
                    // インポート元環境のis_latest_versionがfalseだったらフラグを更新する
                    // 複数のリリースキー情報のうちひとつでも最新バージョンでなければインポートをさせないようにするため
                    $this->hasNoLatestReleaseVersion = true;
                }
            }

            $result = [
                'self' => [
                    'release_key' => $masterRelease->release_key,
                    'description' => $masterRelease->description,
                    'status' => $masterRelease->enabled ? '配信中' : '配信準備中',
                    'style' => $masterRelease->enabled ? 'color: deeppink' : 'color: darkolivegreen',
                    'git_revision' => is_null($versions) ? 'なし' : $versions->git_revision,
                ],
                'environment' => $filteredMasterRelease,
            ];

            return [
                $masterRelease->release_key => $result
            ];
        })->toArray();

        // diffDataを元に異常なデータがないか検証する
        $this->validationFromDiffEnvironments($diffData);

        return $diffData;
    }

    /**
     * 比較用に生成したデータを元に、異常なデータがないか検証する
     * 異常対象
     * 1.自環境と一致するリリースキーがインポート元環境に存在しない
     * 2.インポート元環境から取得したリリース情報にgit_revisionが設定されていない
     *
     * @param array $diffData
     * @return void
     */
    private function validationFromDiffEnvironments(array $diffData): void
    {
        // 検証用にリリースキーの昇順でソート
        sort($diffData);

        $environments = array_column($diffData, 'environment');
        $environmentCollection = collect($environments);

        $isAllEmptyWithEnvironments = true;
        foreach ($environmentCollection as $environment) {
            if (!empty($environment)) {
                // インポート元環境にリリース情報があればフラグを更新してループを抜ける
                $isAllEmptyWithEnvironments = false;
                break;
            }
        }

        if ($isAllEmptyWithEnvironments) {
            $this->validationErrorMessage = '一致するリリースキー情報がありません';
            return;
        }

        $noSetEnvironmentGitRevisionCount = 0;
        foreach ($environmentCollection as $environment) {
            if (!empty($environment) && $environment['git_revision'] === 'なし') {
                // インポート元環境の情報でgit_revisionの設定がなければインクリメント
                // 後のチェックに使う
                $noSetEnvironmentGitRevisionCount++;
            }
        }

        if ($noSetEnvironmentGitRevisionCount === $environmentCollection->count()) {
            // インポート元環境のリリース情報が全てgit_revisionが設定されてない
            $this->validationErrorMessage = "{$this->fromEnvironment}にgit_revisionが設定されていません";
        }
    }
}
