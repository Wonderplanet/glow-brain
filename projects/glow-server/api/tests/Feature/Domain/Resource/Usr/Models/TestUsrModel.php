<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Resource\Usr\Models;

use App\Domain\Resource\Usr\Models\UsrModel;

class TestUsrModel extends UsrModel implements TestUsrModelInterface
{
    protected array $modelKeyColumns = ['usr_user_id', 'test_id'];

    public static function create(string $usrUserId, string $testId, int $testIntValue): TestUsrModel
    {
        return new self([
            'usr_user_id' => $usrUserId,
            'test_id' => $testId,
            'test_int_value' => $testIntValue,
            'test_string_nullable_value' => null,
        ]);
    }

    public function getTestId(): string
    {
        return $this->attributes['test_id'];
    }

    public function getTestIntValue(): int
    {
        return $this->attributes['test_int_value'];
    }

    public function setTestIntValue(int $testIntValue): void
    {
        $this->attributes['test_int_value'] = $testIntValue;
    }

    public function subtractTestIntValue(int $subtractValue): void
    {
        $this->attributes['test_int_value'] -= $subtractValue;
    }

    public function addTestIntValue(int $addValue): void
    {
        $this->attributes['test_int_value'] += $addValue;
    }

    public function getTestStringNullableValue(): ?string
    {
        return $this->attributes['test_string_nullable_value'];
    }

    public function setTestStringNullableValue(?string $testStringNullableValue): void
    {
        $this->attributes['test_string_nullable_value'] = $testStringNullableValue;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }
}
