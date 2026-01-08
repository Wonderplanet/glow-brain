<?php

namespace App\Filament\Pages;

use App\Constants\AggregationDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\SystemConstants;
use App\Filament\Authorizable;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Utils\Excel\CollaboAggregation as ExcelCollaboAggregation;

/**
 * コラボ消費通貨の集計
 */
class CollaboAggregation extends Page
{
    use Authorizable;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.collabo-aggregation';

    protected static ?string $navigationGroup = NavigationGroups::AGGREGATION->value;
    protected static ?string $title = 'コラボ消費通貨集計';
    protected static ?int $navigationSort = AggregationDisplayOrder::COLLABO_AGGREGATION_DISPLAY_ORDER->value; // メニューの並び順

    // フォームパラメータ
    public string $startAt = '';
    public string $endAt = '';
    public string $gachaIds = '';
    public bool $isIncludeSandbox = false;

    /**
     * 入力フォーム
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form->schema([
            Fieldset::make()->schema([
                DateTimePicker::make('startAt')
                    ->label("開始日")
                    ->required(),
                DateTimePicker::make('endAt')
                    ->label('終了日')
                    ->required(),
            ])
                ->columns(2)
                ->label('期間'),
            // TODO: コラボ対象の入力・表示はプロダクトごとに調整
            //       gachaIdsなどの検索対象は、ログのtrigger_tyepとtrigger_idになる。
            //       この値はプロダクト側で決めて登録しているので、プロダクトで使用する際に調整すること
            Textarea::make('gachaIds')
                ->label('ガチャID')
                ->helperText('ガチャIDを入力してください。複数の場合は改行してください。(最大30件)')
                ->placeholder('ガチャID')
                ->rows(5)
                ->rules([
                    function() {
                        return function (string $attribute, $value, Closure $fail) {
                            // 入力できる最大件数を30件とする
                            $line = self::convertIdStrToArray($value);
                            if (count($line) > 30) {
                                $fail('30件を超えるIDは入力できません。');
                            }
                        };
                    },
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

        // 入力値のバリデーションチェック
        $this->validate();

        $endAt = new Carbon($this->endAt, SystemConstants::FORM_INPUT_TIMEZONE);

        // 終了日時年月の替相場情報があるかチェックし、なければ取得処理を実行する
        if (!$currencyAdminDelegator->existsScrapeForeignCurrencyRateByYearAndMonth($endAt->year, $endAt->month)) {
            // 外貨為替収集情報が存在しなければ収集処理を実行する
            try {
                Log::info('コラボ消費通貨集計 外貨為替収集開始');
                $currencyAdminDelegator->scrapeForeignCurrencyRate($endAt->year, $endAt->month);
                Log::info('コラボ消費通貨集計 外貨為替収集終了');
            } catch (\Exception $e) {
                // 集計処理自体は通す為ログだけ残す
                Log::error('コラボ消費通貨集計 外貨為替収集でエラー', [$e]);
            }
        }
    }

    /**
     * エクセルファイル生成
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function makeExcel(): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $excelObject = $this->makeExcelObject();

        // ファイルを作成してダウンロード
        return $excelObject->download();
    }

    /**
     * エクセルファイル生成用のオブジェクトを作成する
     *
     * @return ExcelCollaboAggregation
     */
    private function makeExcelObject(): ExcelCollaboAggregation
    {
        // 入力値のバリデーションチェック
        $this->validate();

        // TODO: trigger_typeとtrigger_idはプロダクト側で指定するので、
        //       プロダクトにあわせて検索条件を変国すること
        //       他に検索する条件があれば含める
        // idは改行で区切られているので、配列に変換する
        $searchTrigger = [
            [
                'type' => 'gacha',
                'ids' => self::convertIdStrToArray($this->gachaIds),
            ],
        ];

        // TODO: ガチャやパックの終了時刻はプロダクト管理となっているため、基盤側では把握できない
        //       そのため期間を別途入力させているが、プロダクト側で終了時刻を把握できるようになったら
        //       そこから自動的に算出した方が良い

        // startAt、endAtに入力される日付文字列はJSTと解釈してCarbonオブジェクトを作成する
        $startAt = new Carbon($this->startAt, SystemConstants::FORM_INPUT_TIMEZONE);
        $endAt = new Carbon($this->endAt, SystemConstants::FORM_INPUT_TIMEZONE);

        // バッファとして終了日+3日する
        // コンビニの遅延決済などで、終了後に決済される可能性もあるため
        $endAt->addDays(3);

        return app()->make(CurrencyAdminDelegator::class)
            ->makeExcelCollaboAggregation(
                $startAt,
                $endAt,
                $searchTrigger,
                $this->isIncludeSandbox
            );
    }

    /**
     * ID文字列から処理用の配列を作る
     *
     * - 重複を除去
     * - 空白行を除去
     *
     * @param string $idString
     * @return array
     */
    private static function convertIdStrToArray(string $idString): array
    {
        // 改行で区切られた文字列を配列に変換
        $idArray = explode("\n", $idString);

        // 重複を除去
        $idArray = array_unique($idArray);

        // 空白行を除去
        $idArray = array_filter($idArray, fn ($id) => $id !== '');

        // 配列のキーを消去して、連番にする
        $idArray = array_values($idArray);

        return $idArray;
    }
}
