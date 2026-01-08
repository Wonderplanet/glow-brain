<?php

namespace App\Providers\Domain;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class UnitServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<string>
     */
    private $classes = [
        \App\Domain\Unit\Delegators\UnitDelegator::class,
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
