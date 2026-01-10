<?php

namespace App\Filament\Resources\IgnSettingResource\Pages;

use App\Filament\Resources\IgnSettingResource;
use App\Models\Mng\MngInGameNotice;
use App\Services\IgnService;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditIgnSetting extends EditRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = IgnSettingResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema());
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('更新')
            ->requiresConfirmation()
            ->action(fn () => $this->save())
            ->modalHeading('注意')
            ->modalDescription('IGN設定を更新しますか？')
            ->modalCancelActionLabel('続ける')
            ->modalSubmitActionLabel('更新')
            ->modalIcon('heroicon-o-exclamation-triangle');
    }

    protected function handleRecordUpdate($record, array $data): MngInGameNotice
    {
        /** @var IgnService $ignService */
        $ignService = app(IgnService::class);

        /** @var MngInGameNotice $mngInGameNotice */
        $mngInGameNotice = $record->getModel();

        $mngInGameNotice = $this->transaction(function () use ($ignService, $mngInGameNotice, $data) {
            return $ignService->updateIgn($mngInGameNotice, $data);
        });

        // キャッシュ削除
        $this->deleteMngInGameNoticeCache();

        return $mngInGameNotice;
    }

    protected function getFormSchema(): array
    {
        $schema = IgnSettingResource::getFormSchema();

        // TODO: 今後同じ処理が欲しい箇所があるかも。その場合は共通化する。
        // 指定したセクションの中にあるコンポーネントのラベル名を見つけて、そのコンポーネントの次の位置に画像プレビューを追加
        $targetSectionName = '掲載内容'; // ターゲットセクションヘッダー名
        $targetComponentName = 'バナー画像'; // ターゲットコンポーネントラベル名
        if ($this->record?->hasBanner()) {
            /** @var IgnService $ignService */
            $ignService = app(IgnService::class);

            $bannerUrl = $ignService->makeBannerUrl(
                $this->record?->banner_url
            );

            $imagePreviewSchema = ViewField::make('image_preview')
                ->view(
                    'components.image-preview',
                    [
                        'label' => '設定中のバナー画像',
                        'url' => $bannerUrl,
                    ],
                );

            foreach ($schema as $section) {
                /** @var \Filament\Forms\Components\Section $section */
                if ($section->getHeading() !== $targetSectionName) {
                    continue;
                }

                $childComponents = collect($section->getChildComponents());
                $labels = $childComponents->map(fn (Component $component) => $component->getLabel());
                $index = $labels->search($targetComponentName);
                if ($index === false) {
                    continue;
                }

                $childComponents->splice($index + 1, 0, [$imagePreviewSchema]);
                $section->schema($childComponents->toArray());

                break;
            }
        }

        return $schema;
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
