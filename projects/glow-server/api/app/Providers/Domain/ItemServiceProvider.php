<?php

namespace App\Providers\Domain;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ItemServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array<string>
     */
    private $classes = [
        \App\Domain\Item\Delegators\ItemDelegator::class,
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
