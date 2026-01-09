<?php

namespace App\Tables\Columns;

use App\Constants\DestinationInGamePath;
use App\Filament\Pages\MstExchangeDetail;
use App\Filament\Pages\OprProductDetail;
use App\Models\Mst\MstExchange;
use App\Models\Opr\OprProduct;
use App\Utils\StringUtil;
use Filament\Tables\Columns\Column;

/**
 * 画面遷移先情報カラム
 */
class DestinationInfoColumn extends Column
{
    protected string $view = 'tables.columns.destination-info-column';

    public function existPathDetail(): bool
    {
        return StringUtil::isSpecified($this->record->destination_path_detail);
    }

    public function getInGamePathEnum(): ?DestinationInGamePath
    {
        return DestinationInGamePath::tryfrom($this->record->destination_path);
    }

    public function showInGamePath(): string
    {
        $enum = $this->getInGamePathEnum();
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function showInGamePathDetail(): string
    {
        return match ($this->getInGamePathEnum()) {
            DestinationInGamePath::SHOP_PAID => $this->showOprProductInfo(),
            DestinationInGamePath::EXCHANGE => $this->showMstExchangeInfo(),
            // ここに随時追加していく
            default => '',
        };
    }

    private function showOprProductInfo(): string
    {
        $oprProductId = $this->record->destination_path_detail;

        $oprProduct = OprProduct::find($oprProductId);
        if ($oprProduct === null) {
            return '';
        }

        // TODO: これ用のInterfaceを作って実装させれば、共通化できるかも。そうすれば、テーブルごとにメソッドを作らなくて済む
        return $oprProduct->getProductInfoAttribute();
    }

    private function showMstExchangeInfo(): string
    {
        $mstExchangeId = $this->record->destination_path_detail;

        $mstExchange = MstExchange::find($mstExchangeId);
        if ($mstExchange === null) {
            return '';
        }

        return sprintf('[%s] %s', $mstExchange->id, $mstExchange->getName());
    }

    public function getInGamePathDetailLink(): string
    {
        return match ($this->getInGamePathEnum()) {
            DestinationInGamePath::SHOP_PAID => OprProductDetail::getUrl([
                'productSubId' => $this->record->destination_path_detail,
            ]),
            DestinationInGamePath::EXCHANGE => MstExchangeDetail::getUrl([
                'mstExchangeId' => $this->record->destination_path_detail,
            ]),
            // ここに随時追加していく
            default => '',
        };
    }
}
