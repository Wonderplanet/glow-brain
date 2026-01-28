<?php

namespace App\Tables\Columns;

use App\Models\Mst\IAssetImage;
use Filament\Actions\StaticAction;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;

class AssetImageColumn extends Column
{
    protected string $view = 'tables.columns.asset-image-column';

    private int $assetWidth = 50;

    public function setUp(): void
    {
        parent::setUp();

        $this->action(
            Action::make('previewImage')
                ->modalHeading('画像プレビュー')
                ->modalWidth(MaxWidth::TwoExtraLarge)
                ->modalContent(function (mixed $record) {
                    if (!($record instanceof IAssetImage)) {
                        return null;
                    }

                    return view('filament.common.modal-image-preview', [
                        'url' => $record->makeAssetPath(),
                    ]);
                })
                // 送信ボタンを削除
                ->modalSubmitAction(false)
                // キャンセルボタンを「閉じる」にリネーム
                ->modalCancelAction(fn (StaticAction $action) => $action->label('閉じる'))
                ,
            )
            ->tooltip('クリックで画像プレビュー');
    }

    public function makeAssetPath(): ?string
    {
        $record = $this->getRecord();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeAssetPath();
    }

    public function makeBgPath(): ?string
    {
        $record = $this->getRecord();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeBgPath();
    }

    public function assetWidth(int $width): self
    {
        $this->assetWidth = $width;
        return $this;
    }

    public function getAssetWidth(): int
    {
        return $this->assetWidth;
    }

    public function getAssetKey(): ?string
    {
        $record = $this->getRecord();

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->asset_key;
    }
}
