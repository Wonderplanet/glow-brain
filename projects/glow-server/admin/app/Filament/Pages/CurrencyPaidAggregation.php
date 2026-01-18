<?php

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
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceMultipleSheets;

class CurrencyPaidAggregation extends Page
{
    use Authorizable;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.currency-paid-aggregation';

    protected static ?string $navigationGroup = NavigationGroups::AGGREGATION->value;
    protected static ?string $title = '有償通貨残高集計';
    protected static ?int $navigationSort = AggregationDisplayOrder::CURRENCY_PAID_AGGREGATION_DISPLAY_ORDER->value; // メニューの並び順

    public string $year = '';
    public string $month = '';
    public array $checkboxes = [
        'isBalanceAggregation',
        'isPaidDetail',
        'isForeignCountry',
    ];
    public bool $isIncludeSandbox = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('year')
                            ->label('年')
                            ->placeholder('年を選択してください')
                            ->options(function () {
                                /** @var CurrencyAdminDelegator $currencyAdminDelegator */
                                $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
                                return $currencyAdminDelegator->getYearOptions();
                            })
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('month')
                            ->label('月')
                            ->placeholder('月を選択してください')
                            ->options(fn () => array_combine(range(1, 12), range(1, 12)))
                            ->required()
                            ->live(),
                    ]),
                Forms\Components\CheckboxList::make('checkboxes')
                    ->label('出力したくないデータはチェックを外してください')
                    ->options([
                        'isBalanceAggregation' => '日本累計',
                        'isPaidDetail' => '日本内訳',
                        'isForeignCountry' => '海外',
                    ])
                    ->columns(3)
                    ->live(),
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
                    || empty($this->month)
                    || empty($this->checkboxes);
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
                Log::info('有償通貨残高集計 外貨為替収集開始');
                $currencyAdminDelegator->scrapeForeignCurrencyRate($year, $month);
                Log::info('有償通貨残高集計 外貨為替収集終了');
            } catch (\Exception $e) {
                // 集計処理自体は通す為ログだけ残す
                Log::error('有償通貨残高集計 外貨為替収集でエラー', [$e]);
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
     * エクセルファイル生成
     *
     * @return CurrencyBalanceMultipleSheets
     */
    private function makeExcelObject(): CurrencyBalanceMultipleSheets
    {
        $outputBalanceAggregation = in_array('isBalanceAggregation', $this->checkboxes, true);
        $outputPaidDetail = in_array('isPaidDetail', $this->checkboxes, true);
        $outputForeignCountry = in_array('isForeignCountry', $this->checkboxes, true);

        return app()->make(CurrencyAdminDelegator::class)
            ->makeExcelCurrencyBalanceAggregation(
                $this->year,
                $this->month,
                $outputBalanceAggregation,
                $outputPaidDetail,
                $outputForeignCountry,
                $this->isIncludeSandbox
            );
    }
}
