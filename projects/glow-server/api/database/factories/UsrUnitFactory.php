<?php

namespace Database\Factories;

use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnit as ModelsUsrUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Unit\Eloquent\Models\UsrUnit>
 */
class UsrUnitFactory extends Factory
{
    protected $model = UsrUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_unit_id' => fake()->uuid(),
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
            'battle_count' => 0,
            'is_new_encyclopedia' => EncyclopediaCollectStatus::IS_NEW->value,
            'last_reward_grade_level' => 0,
        ];
    }

    public function createAndConvert(array $attributes = [], ?UsrUnit $parent = null): ModelsUsrUnit
    {
        return ModelsUsrUnit::createFromRecord(
            (object) $this->create($attributes, $parent)->toArray(),
        );
    }

    public function createManyAndConvert(int|iterable|null $records = null): Collection
    {
        return $this->createMany($records)->map(
            fn (UsrUnit $model) => ModelsUsrUnit::createFromRecord((object) $model->toArray()),
        );
    }

    public function makeAndConvert(array $attributes = [], ?UsrUnit $parent = null): ModelsUsrUnit
    {
        return ModelsUsrUnit::createFromRecord(
            (object) $this->make($attributes, $parent)->toArray(),
        );
    }
}
