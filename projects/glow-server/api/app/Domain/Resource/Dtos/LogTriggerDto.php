<?php

declare(strict_types=1);

namespace App\Domain\Resource\Dtos;

/**
 * リソース変動ログの変動経緯情報を保持するクラス
 */
class LogTriggerDto
{
    private string $triggerSource = '';
    private string $triggerValue = '';
    private string $triggerOption = '';

    public function __construct(
        string $triggerSource,
        string $triggerValue,
        string $triggerOption = '',
    ) {
        $this->triggerSource = $triggerSource;
        $this->triggerValue = $triggerValue;
        $this->triggerOption = $triggerOption;
    }

    public function getTriggerSource(): string
    {
        return $this->triggerSource;
    }

    public function getTriggerValue(): string
    {
        return $this->triggerValue;
    }

    public function getTriggerOption(): string
    {
        return $this->triggerOption;
    }

    public function setTriggerOption(string $triggerOption): void
    {
        $this->triggerOption = $triggerOption;
    }
}
