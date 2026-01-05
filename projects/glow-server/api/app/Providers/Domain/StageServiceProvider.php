<?php

namespace App\Providers\Domain;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class StageServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<string>
     */
    private $classes = [
        \App\Domain\Stage\Delegators\StageDelegator::class,
        \App\Domain\Stage\Delegators\StageTutorialDelegator::class,
    ];

    public function register(): void
    {
        array_map(array($this->app, 'singleton'), $this->classes);
    }

    /**
     * @return array<mixed>
     */
    public function provides(): array
    {
        return $this->classes;
    }
}
