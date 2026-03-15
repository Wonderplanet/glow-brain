<?php

namespace App\Tables\Columns;

use App\Models\Adm\AdmS3Object;
use App\Models\Mst\IAssetImage;
use Filament\Actions\StaticAction;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;

class AssetPreviewColumn extends Column
{
    protected string $view = 'tables.columns.asset-preview-column';

    private int $assetWidth = 50;

    public function setUp(): void
    {
        parent::setUp();

        $this->action(
            Action::make('previewAsset')
                ->modalHeading('プレビュー')
                ->modalWidth(MaxWidth::TwoExtraLarge)
                ->modalContent(function (mixed $record) {
                    if (!($record instanceof IAssetImage)) {
                        return null;
                    }

                    if ($this->isHtmlAsset($record)) {
                        return view('filament.common.modal-html-preview', [
                            'url' => $record->makeAssetPath(),
                        ]);
                    }

                    return view('filament.common.modal-image-preview', [
                        'url' => $record->makeAssetPath(),
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(fn (StaticAction $action) => $action->label('閉じる'))
                ->visible(fn (mixed $record) => $this->isPreviewableAssetForRecord($record))
        )
        ->tooltip(fn (mixed $record) => $this->isPreviewableAssetForRecord($record) ? 'クリックでプレビュー' : '');
    }

    public function makeAssetPath(): ?string
    {
        $record = $this->getRecord();
        if (!($record instanceof IAssetImage)) {
            return null;
        }
        return $record->makeAssetPath();
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

    public function getContentType(): ?string
    {
        $record = $this->getRecord();
        if ($record instanceof AdmS3Object) {
            return $record->content_type;
        }
        return null;
    }

    public function isHtmlContentType(): bool
    {
        return $this->checkContentTypeIsHtml($this->getContentType());
    }

    public function isImageContentType(): bool
    {
        return $this->checkContentTypeIsImage($this->getContentType());
    }

    public function getObjectName(): ?string
    {
        $record = $this->getRecord();
        if ($record instanceof AdmS3Object) {
            return $record->object_name;
        }
        return null;
    }

    private function checkContentTypeIsHtml(?string $contentType): bool
    {
        return $contentType !== null && str_contains($contentType, 'text/html');
    }

    private function checkContentTypeIsImage(?string $contentType): bool
    {
        return $contentType !== null && str_starts_with($contentType, 'image/');
    }

    private function isHtmlAsset(mixed $record): bool
    {
        if (!($record instanceof AdmS3Object)) {
            return false;
        }
        return $this->checkContentTypeIsHtml($record->content_type);
    }

    private function isPreviewableAssetForRecord(mixed $record): bool
    {
        if (!($record instanceof AdmS3Object)) {
            return false;
        }
        return $this->checkContentTypeIsImage($record->content_type)
            || $this->checkContentTypeIsHtml($record->content_type);
    }
}
