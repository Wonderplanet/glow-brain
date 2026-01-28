<?php

declare(strict_types=1);

namespace App\Dtos;

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

    public function getLogTriggerKeyAttribute(): string
    {
        return $this->triggerSource . $this->triggerValue;
    }
}
