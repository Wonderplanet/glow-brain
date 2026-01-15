<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases;

use App\Filament\Authorizable;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SpreadSheetFetchService;

/**
 * マスターデータインポートv2管理ツールページクラス
 * スプレッドシートからマスタデータシートの情報を取得/表示する
 */
class ImportFromSpreadSheet extends Page
{
    use Authorizable;

    private const NON_TARGET_SPREADSHEET_NAME = [
        '説明',
        '属性参照',
        '_ref_list',
        '_ref_name',
        '_ref_DebugLocalize',
        '_ref_releaseKey',
    ];
    private const TARGET_CERTAIN_REGEX_CHARACTERS_INCLUDED_SHEET_NAME = [
        '^Mst',
        '^Opr',
    ];

    protected static string $view = 'view-master-asset-admin::filament.pages.mng-master-releases.import-from-spread-sheet';
    protected static ?string $slug = 'mng-master-release-versions/import-from-spread-sheet'; // URLを別に付与

    protected static ?int $navigationSort = -997;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'マスターデータ取り込み';
    protected static ?string $title = 'マスターデータインポート/スプレッドシート';

    protected SpreadSheetFetchService $spreadSheetFetchService;

    protected bool $canCacheReset;

    public int $importId;

    public function __construct()
    {
        $this->spreadSheetFetchService = app()->make(SpreadSheetFetchService::class);
    }

    /**
     * 画面遷移時に初回だけ起動
     *
     * @param Request $request
     * @return void
     */
    public function mount(Request $request)
    {
        $this->canCacheReset = $request->query('reset', '');
        $this->importId = now()->format('YmdHis');
    }

    /**
     * パンくずリストを設定
     *
     * @return string[]
     */
    public function getBreadcrumbs(): array
    {
        return ['/admin/mng-master-releases' => 'マスターデータリリース一覧'];
    }

    /**
     * 使用するデータをテンプレートに渡す
     *
     * @return array|mixed[]
     */
    protected function getViewData(): array
    {
        $tableData = $this->getTableData();
        $rowspanData = $this->getRowspanDataByTableData($tableData);
        return [
            'tableData' => $tableData,
            'resetLink' => "{$this->getNavigationUrl()}?reset=true",
            'rowspanData' => $rowspanData,
            'diffLink' => Diff::makeUrl($this->importId),
        ];
    }

    /**
     * スプレッドシートのデータを取得してテーブルに表示するデータを生成する
     *
     * @return array
     */
    private function getTableData(): array
    {
        if ($this->canCacheReset === true) {
            $this->spreadSheetFetchService->deleteSheetsCache();
        }

        try {
            $spreadSheets = $this->spreadSheetFetchService->getSpreadSheetList();
        } catch(\Exception $e) {
            // エラーコードに合わせてエラー文言を設定
            $errorCode = $e->getCode();
            $body = "エラーコード：{$errorCode}<br/>";
            switch ($errorCode) {
                case 429:
                case 503:
                    $title = 'マスターデータシート情報取得中にエラーが発生しました。';
                    $body .= '時間を置いて再度ブラウザにアクセスしてください。';
                    break;
                default:
                    $title = '不明なエラーです。';
                    $body .= 'サーバー管理者にお問い合わせください。';
                    break;
            }
            Notification::make()
                ->title($title)
                ->body($body)
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
            return [];
        }

        $rows = [];
        /** @var SpreadSheetCsvEntity $entity */
        foreach ($spreadSheets as $entity) {
            // 対象外のスプレッドシートは表示しない
            if (in_array($entity->getTitle(), self::NON_TARGET_SPREADSHEET_NAME)) continue;
            // 正規表現のパターンにマッチするか確認
            $hasSheetName = false;
            foreach (self::TARGET_CERTAIN_REGEX_CHARACTERS_INCLUDED_SHEET_NAME as $target) {
                if (preg_match("/{$target}/", $entity->getTitle())) $hasSheetName = true;
            }
            if (!$hasSheetName) continue;

            $rows[] = [
                'id' => $entity->getFileId() . '_' . $entity->getSheetId(),
                'fileName' => $entity->getFileName(),
                'memo' => 'メモ欄表示は未実装', // TODO メモを取得するにはどうするか
                'sheetName' => $entity->getTitle(),
                'link' => $entity->getUrl(),
                'lastUpdateAt' => 'YYYY/MM/DD HH:ii:ss', // TODO どのデータを表示するか詳細を確認する
            ];
        }

        // fileNameのアルファベット順でソート
        usort($rows, function ($a, $b) {
            return strcmp($a['fileName'], $b['fileName']);
        });

        return $rows;
    }

    /**
     * tableDataをもとにblade側で指定するrowspanの値を取得する
     *
     * @param array $tableData
     * @return array<string, int>
     */
    private function getRowspanDataByTableData(array $tableData): array
    {
        // キーにファイル名、値にrowspanの値をもつ配列を生成して返す
        // tableDataが空なら空を返す
        $result = [];
        foreach ($tableData as $data) {
            // resultにfileNameがあれば値をインクリメント、なければ初期値を設定
            $result[$data['fileName']] = isset($result[$data['fileName']])
                ? $result[$data['fileName']] + 1
                : 1;
        }
        return $result;
    }
}
