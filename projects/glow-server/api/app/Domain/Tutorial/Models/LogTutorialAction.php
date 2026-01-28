<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $tutorial_name
 */
class LogTutorialAction extends LogModel
{
    public function setTutorialName(string $tutorialName): void
    {
        $this->tutorial_name = $tutorialName;
    }
}
