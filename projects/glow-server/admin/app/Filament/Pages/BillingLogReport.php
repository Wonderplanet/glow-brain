<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Constants\AggregationDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;
use WonderPlanet\Domain\Billing\Utils\Excel\BillingLogReport as ExcelBillingLogReport;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

class BillingLogReport extends Page
{
    use Authorizable;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.billing-log-report';

    protected static ?string $navigationGroup = NavigationGroups::AGGREGATION->value;
    protected static ?string $title = '課金ログレポート';
    protected static ?int $navigationSort = AggregationDisplayOrder::BILLDING_LOG_REPORT_DISPLAY_ORDER->value; // メニューの並び順

    public string $year = '';
    public string $month = '';
    public bool $isIncludeSandbox = false;

    public function form(Form $form): Form
    {
        $billingAdminDelegator = app()->make(BillingAdminDelegator::class);
        [$years, $monthsByYear] =  $billingAdminDelegator->getYearMonthOptions();

        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('year')
                            ->label('年')
                            ->placeholder('年を選択してください')
                            ->options(function () use ($years) {
                                if (empty($years)) {
                                    return ['取得可能なデータが存在しません'];
                                }
                                return $years;
                            })
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('month')
                            ->label('月')
                            ->placeholder('月を選択してください')
                            ->options(function () use ($monthsByYear) {
                                if (empty($monthsByYear)) {
                                    return ['取得可能なデータが存在しません'];
                                }
                                if (empty($this->year)) {
                                    // 指定年が空欄なら空配列を返す
                                    return [];
                                }
                                // 指定年に応じて選択できる月を返す
                                return $monthsByYear[$this->year];
                            })
                            ->required()
                            ->live(),
                    ]),
                Forms\Components\Checkbox::make('isIncludeSandbox')
                    ->visible(function () {
                        // 本番環境ならfalse(非表示)、本番以外ならtrue(表示)
                        return !App::environment('production');
                    })
                    ->label('サンドボックスデータを集計に含める場合はチェックしてください')
                    ->inline(false)
                    ->live(),
            ]);
    }

    /**
     * フォームで使用するアクションボタンを返す
     *
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [
            $this->aggregationButton(),
        ];
    }

    /**
     * レポート出力ボタン
     *
     * @return Action
     */
    public function aggregationButton(): Action
    {
        return Action::make('aggregationButton')
            ->label('レポート出力')
            ->requiresConfirmation()
            ->disabled(function () {
                return empty($this->year)
                    || empty($this->month);
            })
            ->action(function () {
                $this->scrapeForeignCurrencyRate();
                return $this->makeExcel();
            });
    }

    /**
     * 外貨為替情報がなければ収集処理を実行する
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function scrapeForeignCurrencyRate(): void
    {
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
        $year = (int) $this->year;
        $month = (int) $this->month;

        if (!$currencyAdminDelegator->existsScrapeForeignCurrencyRateByYearAndMonth($year, $month)) {
            // 外貨為替収集情報が存在しなければ収集処理を実行する
            try {
                Log::info('課金ログレポート 外貨為替収集開始');
                $currencyAdminDelegator->scrapeForeignCurrencyRate($year, $month);
                Log::info('課金ログレポート 外貨為替収集終了');
            } catch (\Exception $e) {
                // 集計処理自体は通す為ログだけ残す
                Log::error('課金ログレポート 外貨為替収集でエラー', [$e]);
            }
        }
    }

    /**
     * エクセルファイル生成
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function makeExcel(): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->makeExcelObject()
            ->download();
    }

    /**
     * エクセルファイル生成用オブジェクトを作成する
     *
     * @return ExcelBillingLogReport
     */
    private function makeExcelObject(): ExcelBillingLogReport
    {
        return app()->make(BillingAdminDelegator::class)
            ->makeExcelBillingLogReport(
                $this->year,
                $this->month,
                $this->isIncludeSandbox
            );
    }
}
