<?php

declare(strict_types=1);

namespace App\Entities;

class LogTrigger
{
    private string $triggerSource = '';
    private string $triggerValue = '';
    private string $triggerOption = '';

    private string $name = '';
    private string $link = '';

     public function __construct(
        string $triggerSource = '',
        string $triggerValue = '',
        string $triggerOption = '',
        string $name = '',
        string $link = ''
    ) {
        $this->triggerSource = $triggerSource;
        $this->triggerValue = $triggerValue;
        $this->triggerOption = $triggerOption;
        $this->name = $name;
        $this->link = $link;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
