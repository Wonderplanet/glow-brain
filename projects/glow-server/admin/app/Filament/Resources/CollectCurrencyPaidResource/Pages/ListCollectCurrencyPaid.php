<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectCurrencyPaidResource\Pages;

use App\Filament\Resources\CollectCurrencyPaidResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

/**
 * 有償一次通貨回収ツール - ユーザーID検索表示の実装
 */
class ListCollectCurrencyPaid extends Page
{
    use InteractsWithFormActions;

    protected static string $resource = CollectCurrencyPaidResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.list-collect-currency-paid';

    protected static ?string $title = '有償一次通貨回収';

    public array $userData = [];
    public string $userId = '';
    public string $productSubId = '';
    public string $billingPlatform = '';
    public string $triggerDetail = '';

    /**
     * GETパラメータで受け取る検索条件
     */
    protected array $queryString = [
        'userId',
    ];

    /**
     * このページで使用するフォームメソッド
     *
     * @return array
     */
    protected function getForms(): array
    {
        return [
            'searchForm',
        ];
    }

    /**
     * 検索用フォーム
     *
     * @param Form $form
     * @return Form
     */
    public function searchForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('userId')
                ->label(new HtmlString("<p class='text-2xl'>ユーザー検索</p>"))
                ->placeholder('ユーザーIDを入力')
                ->columnSpanFull(),
        ]);
    }

    /**
     * 検索用フォームで使用するアクションボタンを返す
     *
     * @return array
     */
    public function getSearchFormActions(): array
    {
        return [
            $this->searchButton(),
        ];
    }

    /**
     * 検索用ボタン
     *
     * @return Action
     */
    public function searchButton(): Action
    {
        return Action::make('searchButton')
            ->label('検索')
            ->action(fn () => $this->search());
    }

    /**
     * 検索条件を更新する
     *
     * @return void
     */
    public function search(): void
    {
        $this->dispatch(
            'searchUpdated',
            userId: $this->userId
        )
            ->to('CollectCurrencyPaid\CollectCurrencyPaidList');
    }
}
