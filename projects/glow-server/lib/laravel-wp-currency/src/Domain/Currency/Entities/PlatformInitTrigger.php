<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * 課金プラットフォーム側の初期登録で使用するTrigger
 */
class PlatformInitTrigger extends Trigger
{
    public function __construct()
    {
        $triggerType = Trigger::TRIGGER_TYPE_PF_INIT;
        parent::__construct($triggerType, '', '', '');
    }
}
