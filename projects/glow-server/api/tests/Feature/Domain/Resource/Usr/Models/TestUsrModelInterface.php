<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Resource\Usr\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface TestUsrModelInterface extends UsrModelInterface
{
    public function getTestId(): string;

    public function getTestIntValue(): int;

    public function setTestIntValue(int $testIntValue): void;

    public function subtractTestIntValue(int $subtractValue): void;

    public function addTestIntValue(int $addValue): void;

    public function isNew(): bool;
}
