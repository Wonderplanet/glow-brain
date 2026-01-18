<?php

declare(strict_types=1);

namespace App\Facades;

use WonderPlanet\Domain\Common\Facades\BaseFacade;

//phpcs:disable
/**
 * 昇格機能のファサード
 *
 * PromotionServiceのメソッドを呼び出す
 *
 * @method static bool isPromotionDestinationEnvironment()
 * @method static array getHeaderActions(\App\Constants\AdmPromotionTagFunctionName $functionName, callable(string $environment, string $admPromotionTagId): void $importCallback)
 * @method static \Filament\Tables\Filters\SelectFilter getTagSelectFilter()
 * @method static \Filament\Forms\Components\Select getTagSelectForm(string $name = 'adm_promotion_tag_id', string $label = '昇格タグ')
 *
 * @see App\Services\PromotionService
 */
// phpcs:enable
class Promotion extends BaseFacade
{
    public const FACADE_ACCESSOR = 'promotion';
}
