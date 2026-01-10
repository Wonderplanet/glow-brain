<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource\Pages;

use App\Traits\MngCacheDeleteTrait;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\AssetDiffType;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;

/**
 * アセットインポート確認画面
 */
class ConfirmMngAssetRelease extends CreateRecord
{
    use MngCacheDeleteTrait;

    protected static string $resource = MngAssetReleaseImportResource::class;

    protected static ?string $title = 'アセット環境間インポート確認画面';
    protected static ?string $breadcrumb = 'アセット環境間インポート確認';

    protected static string $view = 'view-master-asset-admin::filament.pages.mng-asset-release-imports.confirm';

    // GETで送信されてくるパラメータ
    public ?string $fromEnvironment = null;
    public ?int $releaseKeyIos = 0;
    public ?int $releaseKeyAndroid = 0;

    use DatabaseTransactionTrait;

    /**
     * GETパラメータを受け取るLivewireの設定
     *
     * @var array
     */
    protected array $queryString = [
        'releaseKeyIos',
        'releaseKeyAndroid',
        'fromEnvironment',
    ];

    /**
     * インポート元環境と操作中環境のファイル差分(viewで使用する)
     *
     * @var Collection
     */
    public Collection $diffAndroid;
    public Collection $diffIos;

    /**
     * インポート元環境と操作中環境のサイズ差分(viewで使用する)
     *
     * @var string
     */
    public string $diffSizeAndroid;
    public string $diffSizeIos;

    /**
     * 各環境のアセットバージョン情報(表示用)
     * @var Collection
     */
    public Collection $androidAssetVersionInfoOfFromEnvironment;
    public Collection $androidAssetVersionInfoOfEnvironment;
    public Collection $iosAssetVersionInfoOfFromEnvironment;
    public Collection $iosAssetVersionInfoOfEnvironment;

    /**
     * インポート元環境のmng_asset_release_versions情報
     * インポート実行時に使用
     * @var Collection
     */
    public Collection $androidReleaseVersionOfFromEnvironment;
    public Collection $iosReleaseVersionOfFromEnvironment;

    /**
     * インポート元環境と操作中環境のアセットの合計バイト数差分(viewで使用する)
     * @var string
     */
    public string $iosAssetTotalBytesFromEnvironment;
    public string $iosAssetTotalBytesEnvironment;
    public string $androidAssetTotalBytesFromEnvironment;
    public string $androidAssetTotalBytesEnvironment;

    private string $confirmModalView = 'view-master-asset-admin::filament.pages.mng-asset-release-imports.import-confirm-modal';

    /**
     * 差分件数情報
     * インポート実行時の確認モーダルで使用
     * @var Collection
     */
    public Collection $androidDiffCount;
    public Collection $iosDiffCount;

    /**
     * @var MngAssetReleaseService
     */
    private MngAssetReleaseService $mngAssetReleaseService;

    public function __construct() {
        $this->mngAssetReleaseService = app()->make(MngAssetReleaseService::class);
    }

    /**
     * 各ボタンのアクション
     *
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('戻る')
                ->color('gray')
                ->url(self::getResource()::getUrl('import')),
            Actions\Action::make('submit')
                ->label('環境間インポート実行')
                ->requiresConfirmation()
                ->modalHeading('必ず内容を確認してください')
                ->modalIconColor('danger')
                ->modalContent(function () {
                    return view($this->confirmModalView);
                })
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->modalSubmitActionLabel('実行')
                ->action(fn () => $this->importAsset()),
        ];
    }

    /**
     * フォーム作成
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        // 既に差分情報がある場合は余計なAPIコールをしないようにするためそのままformを返す
        if (isset($this->diffIos) || isset($this->diffAndroid)) {
            return $form;
        }
        // 差分情報をセットする
        $this->setDiff($this->fromEnvironment, env('APP_ENV'));
        return $form;
    }

    /**
     * 差分情報をセットする
     * @param string $fromEnvironment
     * @param string $environment
     *
     * @return void
     */
    private function setDiff(string $fromEnvironment, string $environment): void
    {
        try {
            // s3のconfig取得
            $configs = $this->mngAssetReleaseService->getAssetConfigNameBoth($environment, $fromEnvironment);

            // Androidのデータ取得：未選択以外の場合各種データを取得する
            if ($this->releaseKeyAndroid !== 0) {
                // インポート元環境の全アセットファイル取得(Android)
                $assetInfo = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo(
                    $fromEnvironment,
                    PlatformConstant::PLATFORM_ANDROID,
                    $this->releaseKeyAndroid
                );
                $this->androidAssetVersionInfoOfFromEnvironment = collect($assetInfo['asset_info']);
                $this->androidReleaseVersionOfFromEnvironment = collect($assetInfo['release_version']);
                $allFilesInputAndroid = $this->mngAssetReleaseService->getAllAssetFiles(
                    $configs['input'],
                    $assetInfo['path'],
                );
                // 操作中環境の全アセットファイル取得(Android)
                $assetInfo = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo(
                    $environment,
                    PlatformConstant::PLATFORM_ANDROID,
                    $this->releaseKeyAndroid
                );
                $this->androidAssetVersionInfoOfEnvironment = collect($assetInfo['asset_info']);
                $allFilesOutputAndroid = $assetInfo['path'] === '' ? collect([]) : $this->mngAssetReleaseService->getAllAssetFiles(
                    $configs['output'],
                    $assetInfo['path'],
                );
                // ファイル差分取得(Android)
                $this->diffAndroid = $this->fileDiff($allFilesInputAndroid, $allFilesOutputAndroid, PlatformConstant::PLATFORM_ANDROID);
                // サイズ差分取得(Android)
                $this->diffSizeAndroid = $this->sizeDiff($allFilesInputAndroid, $allFilesOutputAndroid, PlatformConstant::PLATFORM_ANDROID);
            }

            if ($this->releaseKeyIos !== 0) {
                // インポート元環境の全アセットファイル取得(iOS)
                $assetInfo = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo(
                    $fromEnvironment,
                    PlatformConstant::PLATFORM_IOS,
                    $this->releaseKeyIos
                );
                $this->iosAssetVersionInfoOfFromEnvironment = collect($assetInfo['asset_info']);
                $this->iosReleaseVersionOfFromEnvironment = collect($assetInfo['release_version']);
                $allFilesInputIos = $this->mngAssetReleaseService->getAllAssetFiles(
                    $configs['input'],
                    $assetInfo['path'],
                );
                // 操作中環境の全アセットファイル取得(iOS)
                $assetInfo = $this->mngAssetReleaseService->createAssetFileDirectoryPathAndGetAssetInfo(
                    $environment,
                    PlatformConstant::PLATFORM_IOS,
                    $this->releaseKeyIos
                );
                $this->iosAssetVersionInfoOfEnvironment = collect($assetInfo['asset_info']);
                $allFilesOutputIos = $assetInfo['path'] === '' ? collect([]) : $this->mngAssetReleaseService->getAllAssetFiles(
                    $configs['output'],
                    $assetInfo['path'],
                );
                // ファイル差分取得(iOS)
                $this->diffIos = $this->fileDiff($allFilesInputIos, $allFilesOutputIos, PlatformConstant::PLATFORM_IOS);
                // サイズ差分取得(iOS)
                $this->diffSizeIos = $this->sizeDiff($allFilesInputIos, $allFilesOutputIos, PlatformConstant::PLATFORM_IOS);
            }
        } catch (\Exception $e) {
            Log::error('', [$e]);
            Notification::make()
                ->title('アセット環境間インポートを実行できません。')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->color('danger')
                ->send();
            // インポート画面に戻る
            $this->redirect($this->getRedirectUrl('import'));
        }
    }

    /**
     * ファイル差分取得
     *
     * @param Collection $allFilesInput
     * @param Collection $allFilesOutput
     * @param int $platform
     *
     * @return Collection
     */
    private function fileDiff(Collection $allFilesInput, Collection $allFilesOutput, int $platform): Collection
    {
        $result = [];
        $deleteCount = 0;
        $newCount = 0;
        $changeCount = 0;

        // 操作中環境には存在して、インポート元の環境には存在しないファイル(今回のインポートで削除されるファイル)
        $deleteFiles = $allFilesOutput->diffKeys($allFilesInput);
        if ($deleteFiles->isNotEmpty()) {
            $result = $deleteFiles->map(function ($item) {
                return $item = [
                    'file' => $item['file'],
                    'size' => $item['size'],
                    'size_format' => $item['size_format'],
                    'diff_type' => AssetDiffType::DIFF_TYPE_DELETE,
                    'color' => 'color:firebrick',
                ];
            })->values();
            $deleteCount = count($result);
        }

        foreach ($allFilesInput as $fileData) {
            // インポート元のファイル情報
            $fileInput = $fileData['file'];
            $sizeInput = $fileData['size'];
            if (isset($allFilesOutput[$fileInput])) {
                // 操作中環境のファイル情報
                $sizeOutput = $allFilesOutput[$fileInput]['size'];
                // 変更されたファイル（インポート元にも同名のファイルが存在且つ、ファイルサイズが異なるため）
                if ($sizeInput !== $allFilesOutput[$fileInput]['size']) {
                    $result[] = [
                        'file' => $fileInput,
                        'size_format_output' =>  Number::fileSize($sizeOutput, 2),
                        'size_format_input' => Number::fileSize($sizeInput, 2),
                        'diff_type' => AssetDiffType::DIFF_TYPE_CHANGE,
                        'color' => 'color:darkorange',
                    ];
                    $changeCount++;
                }
            // 新規追加されるファイル（操作中環境には存在しないファイルのため）
            } else {
                $result[] = [
                    'file' => $fileInput,
                    'size' => $sizeInput,
                    'size_format' => Number::fileSize($sizeInput, 2),
                    'diff_type' => AssetDiffType::DIFF_TYPE_ADD,
                    'color' => 'color:darkgreen',
                ];
                $newCount++;
            }
        }

        // 差分件数情報をセットする
        $countArray = [
            'deleteCount' => $deleteCount,
            'newCount' => $newCount,
            'changeCount' => $changeCount,
        ];
        if ($platform === PlatformConstant::PLATFORM_IOS) {
            $this->iosDiffCount = collect($countArray);
        } elseif ($platform === PlatformConstant::PLATFORM_ANDROID) {
            $this->androidDiffCount = collect($countArray);
        }
        // ファイル名でソート
        return collect($result)->sortBy('file')->values();
    }

    /**
     * サイズ差分取得
     * @param Collection $allFilesInput // インポート元環境の全ファイル情報
     * @param Collection $allFilesOutPut // 自環境の全ファイル情報
     * @param int $platform
     * @return string
     */
    private function sizeDiff(Collection $allFilesInput, Collection $allFilesOutPut, int $platform): string
    {
        // インポート元環境の合計ファイルサイズを取得
        $totalSizeInput = $this->getTotalSize($allFilesInput);
        // 自環境の合計ファイルサイズを取得
        $totalSizeOutput = $this->getTotalSize($allFilesOutPut);
        // view用のstring生成
        // 自環境からインポート元環境のアセットファイルに置き換えるとファイルサイズがどれくらい変わるかを表示
        if ($platform === PlatformConstant::PLATFORM_IOS) {
            $this->iosAssetTotalBytesFromEnvironment = Number::fileSize($totalSizeInput, 2);
            $this->iosAssetTotalBytesEnvironment = Number::fileSize($totalSizeOutput, 2);
        } else {
            $this->androidAssetTotalBytesFromEnvironment = Number::fileSize($totalSizeInput, 2);
            $this->androidAssetTotalBytesEnvironment = Number::fileSize($totalSizeOutput, 2);
        }
        // サイズ差分を計算
        $diff = $totalSizeInput - $totalSizeOutput;
        $symbol = '+';
        if ($diff < 0) {
            $symbol = '-';
        }
        return $symbol . Number::fileSize(abs($diff), 2);
    }

    /**
     * 合計ファイルサイズの取得
     *
     * @param Collection $files
     *
     * @return int
     */
    private function getTotalSize(Collection $files): int
    {
        return $files->sum(function ($item) {
            return $item['size'];
        });
    }

    /**
     * アセットインポートを実行
     * @return void
     */
    public function importAsset(): void
    {
        // s3のconfig取得
        $fromEnvironment = $this->fromEnvironment;
        $environment = env('APP_ENV');
        $configs = $this->mngAssetReleaseService->getAssetConfigNameBoth($environment, $fromEnvironment);

        // アセットインポート
        try {
            if ($this->releaseKeyIos !== 0) {
                // アセットのインポート
                $this->mngAssetReleaseService->assetImport(
                    $configs['input'],
                    $configs['output'],
                    PlatformConstant::PLATFORM_IOS,
                    $this->iosReleaseVersionOfFromEnvironment['catalog_hash']
                );
            }

            if ($this->releaseKeyAndroid !== 0) {
                // アセットのインポート
                $this->mngAssetReleaseService->assetImport(
                    $configs['input'],
                    $configs['output'],
                    PlatformConstant::PLATFORM_ANDROID,
                    $this->androidReleaseVersionOfFromEnvironment['catalog_hash']
                );
            }
            // インポート成功用bodyメッセージ
            // トランザクションの開始
            $successBodyMessage = $this->transaction(function () {
                $successBodyMsg = "";
                // iOS
                if ($this->releaseKeyIos !== 0) {
                    // DB更新
                    $this->mngAssetReleaseService->insertReleaseVersionAndUpdateTargetId(
                        $this->releaseKeyIos,
                        PlatformConstant::PLATFORM_IOS,
                        $this->iosReleaseVersionOfFromEnvironment
                    );
                    $successBodyMsg = "iOSリリースキー : " . $this->releaseKeyIos;
                }
                // Android
                if ($this->releaseKeyAndroid !== 0) {
                    // DB更新
                    $this->mngAssetReleaseService->insertReleaseVersionAndUpdateTargetId(
                        $this->releaseKeyAndroid,
                        PlatformConstant::PLATFORM_ANDROID,
                        $this->androidReleaseVersionOfFromEnvironment
                    );
                    if ($successBodyMsg !== "") {
                        $successBodyMsg = $successBodyMsg . "<br>";
                    }
                    $successBodyMsg = $successBodyMsg .  "androidリリースキー : " . $this->releaseKeyAndroid;
                }
                return $successBodyMsg;
            }, [DBUtility::getMngConnName()]);

            // キャッシュを削除
            $this->deleteMngAssetReleaseVersionCache();

            // 更新成功時、成功通知を出す
            Notification::make()
                ->title('インポートが成功しました。')
                ->body($successBodyMessage)
                ->success()
                ->persistent()
                ->send();
            // 一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Log::error('', [$e]);
            Notification::make()
                ->title('アセット環境間インポートに失敗しました。')
                ->body('サーバー管理者にお問い合わせください。Error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->color('danger')
                ->send();
        }
    }

    /**
     * リダイレクト設定
     * @param string $page
     * @return string
     */
    protected function getRedirectUrl(string $page = 'list'): string
    {
        return $this->getResource()::getUrl($page);
    }
}
